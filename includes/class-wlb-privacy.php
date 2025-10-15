<?php
/**
 * Privacy helpers.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Adds privacy policy content and exporters/erasers.
 */
class WLB_Privacy {
    /**
     * Registers exporter.
     *
     * @param array $exporters Exporters.
     * @return array
     */
    public static function register_exporter( $exporters ) {
        $exporters['wishlist-block'] = [
            'exporter_friendly_name' => __( 'Wishlist Block Data', 'wishlist-block' ),
            'callback'               => [ __CLASS__, 'exporter_callback' ],
        ];

        return $exporters;
    }

    /**
     * Registers eraser.
     *
     * @param array $erasers Erasers.
     * @return array
     */
    public static function register_eraser( $erasers ) {
        $erasers['wishlist-block'] = [
            'eraser_friendly_name' => __( 'Wishlist Block Data', 'wishlist-block' ),
            'callback'             => [ __CLASS__, 'eraser_callback' ],
        ];

        return $erasers;
    }

    /**
     * Privacy exporter.
     *
     * @param string $email Email.
     * @param int    $page Page.
     * @return array
     */
    public static function exporter_callback( $email, $page = 1 ) {
        $items = get_posts(
            [
                'post_type'      => WLB_CPT::POST_TYPE,
                'posts_per_page' => 100,
                'paged'          => $page,
                'author_email'   => sanitize_email( $email ),
            ]
        );

        $data = [];
        foreach ( $items as $item ) {
            $data[] = [
                'name'  => $item->post_title,
                'value' => $item->post_content,
            ];
        }

        return [
            'data' => $data,
            'done' => count( $items ) < 100,
        ];
    }

    /**
     * Privacy eraser.
     *
     * @param string $email Email.
     * @param int    $page Page.
     * @return array
     */
    public static function eraser_callback( $email, $page = 1 ) {
        $items = get_posts(
            [
                'post_type'      => WLB_CPT::POST_TYPE,
                'posts_per_page' => 100,
                'paged'          => $page,
                'author_email'   => sanitize_email( $email ),
            ]
        );

        $items_removed = 0;
        foreach ( $items as $item ) {
            wp_delete_post( $item->ID, true );
            $items_removed++;
        }

        return [
            'items_removed' => $items_removed > 0,
            'items_retained'=> false,
            'messages'      => [],
            'done'          => count( $items ) < 100,
        ];
    }
}
