<?php
/**
 * Uninstall helper.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles uninstall cleanup.
 */
class WLB_Uninstall {
    /**
     * Run uninstall cleanup.
     */
    public static function uninstall() {
        if ( 'yes' !== get_option( 'wlb_delete_data_on_uninstall', 'no' ) ) {
            return;
        }

        $items = get_posts(
            [
                'post_type'      => WLB_CPT::POST_TYPE,
                'post_status'    => 'any',
                'posts_per_page' => -1,
            ]
        );

        foreach ( $items as $item ) {
            wp_delete_post( $item->ID, true );
        }
    }
}
