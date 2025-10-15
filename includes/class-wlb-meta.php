<?php
/**
 * Meta fields for wishlist items.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles wishlist item meta registration.
 */
class WLB_Meta {
    /**
     * Register meta fields.
     */
    public static function register() {
        register_post_meta(
            WLB_CPT::POST_TYPE,
            'wlb_product_id',
            [
                'type'              => 'integer',
                'single'            => true,
                'show_in_rest'      => [
                    'schema' => [
                        'type' => 'integer',
                    ],
                ],
                'auth_callback'     => [ __CLASS__, 'auth_meta' ],
                'sanitize_callback' => 'absint',
            ]
        );

        register_post_meta(
            WLB_CPT::POST_TYPE,
            'wlb_priority',
            [
                'type'              => 'integer',
                'single'            => true,
                'show_in_rest'      => [
                    'schema' => [
                        'type' => 'integer',
                    ],
                ],
                'auth_callback'     => [ __CLASS__, 'auth_meta' ],
                'sanitize_callback' => 'absint',
                'default'           => 1,
            ]
        );

        register_post_meta(
            WLB_CPT::POST_TYPE,
            'wlb_user_id',
            [
                'type'              => 'integer',
                'single'            => true,
                'show_in_rest'      => [
                    'schema' => [
                        'type' => 'integer',
                    ],
                ],
                'auth_callback'     => [ __CLASS__, 'auth_meta' ],
                'sanitize_callback' => 'absint',
            ]
        );
    }

    /**
     * Authorization callback for meta operations.
     *
     * @param bool   $allowed Current status.
     * @param string $meta_key Meta key.
     * @param int    $post_id Post ID.
     * @param int    $user_id User ID.
     * @param string $cap Capability.
     * @param array  $caps Capabilities.
     * @return bool
     */
    public static function auth_meta( $allowed, $meta_key, $post_id, $user_id, $cap, $caps ) {
        unset( $allowed, $meta_key, $cap, $caps );

        $post = get_post( $post_id );
        if ( ! $post || WLB_CPT::POST_TYPE !== $post->post_type ) {
            return false;
        }

        $user_id = $user_id ?: get_current_user_id();
        if ( ! $user_id ) {
            return current_user_can( 'manage_options' );
        }

        return (int) $user_id === (int) get_post_meta( $post_id, 'wlb_user_id', true ) || current_user_can( 'edit_post', $post_id );
    }
}
