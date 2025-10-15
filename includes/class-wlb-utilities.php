<?php
/**
 * Utility helpers.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Static utility methods.
 */
class WLB_Utilities {
    /**
     * Get sanitized request value.
     *
     * @param string $key Key.
     * @param mixed  $default Default.
     * @param string $filter Filter type.
     * @return mixed
     */
    public static function get_request_var( $key, $default = null, $filter = 'sanitize_text_field' ) {
        $value = isset( $_REQUEST[ $key ] ) ? wp_unslash( $_REQUEST[ $key ] ) : $default; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if ( is_callable( $filter ) ) {
            return call_user_func( $filter, $value );
        }

        return $value;
    }

    /**
     * Ensure user has capability to manage wishlist.
     *
     * @return bool
     */
    public static function current_user_can_manage() {
        return current_user_can( 'read' );
    }
}
