<?php
/**
 * Block registration and render callbacks.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles dynamic block registration.
 */
class WLB_Render {
    /**
     * Register block type.
     */
    public static function register_block() {
        register_block_type( WLB_PLUGIN_DIR . 'block.json', [
            'render_callback' => [ __CLASS__, 'render_block' ],
        ] );
    }

    /**
     * Render callback for the block.
     *
     * @param array  $attributes Block attributes.
     * @param string $content Saved content.
     *
     * @return string
     */
    public static function render_block( $attributes, $content ) {
        unset( $attributes, $content );

        $items = REST\WLB_REST_Items_Controller::get_wishlist_items_for_current_user();

        if ( empty( $items ) ) {
            return '<div class="wlb-wishlist wlb-wishlist--empty" role="status">' . esc_html__( 'Your wishlist is empty.', 'wishlist-block' ) . '</div>';
        }

        $grid = '<div class="wlb-wishlist" role="list">';
        foreach ( $items as $item ) {
            $grid .= sprintf(
                '<article class="wlb-wishlist__item" role="listitem" aria-label="%1$s"><h3 class="wlb-wishlist__title">%1$s</h3><p class="wlb-wishlist__description">%2$s</p></article>',
                esc_html( $item['title'] ),
                esc_html( $item['description'] )
            );
        }

        $grid .= '</div>';

        /**
         * Fires after the wishlist block is rendered.
         *
         * @param array $items Wishlist items.
         */
        do_action( 'wlb_rendered_wishlist', $items );

        return $grid;
    }
}
