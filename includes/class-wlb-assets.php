<?php
/**
 * Asset registration and enqueueing.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueues plugin assets.
 */
class WLB_Assets {
    /**
     * Register scripts and styles.
     */
    private static function register() {
        $asset_file = WLB_PLUGIN_DIR . 'build/index.asset.php';
        $deps       = [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch', 'wp-data' ];
        $version    = WLB_VERSION;

        if ( file_exists( $asset_file ) ) {
            $asset_data = include $asset_file;
            $deps       = isset( $asset_data['dependencies'] ) ? $asset_data['dependencies'] : $deps;
            $version    = isset( $asset_data['version'] ) ? $asset_data['version'] : $version;
        }

        wp_register_script( 'wlb-block-editor', WLB_PLUGIN_URL . 'build/index.js', $deps, $version, true );
        wp_register_style( 'wlb-block-editor', WLB_PLUGIN_URL . 'build/index.css', [ 'wp-edit-blocks' ], $version );
        wp_register_style( 'wlb-block-frontend', WLB_PLUGIN_URL . 'build/style-index.css', [], $version );

        wp_localize_script(
            'wlb-block-editor',
            'wlbSettings',
            [
                'root'      => esc_url_raw( rest_url( 'wishlist/v1' ) ),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'canManage' => current_user_can( 'edit_posts' ),
            ]
        );
    }

    /**
     * Enqueue editor assets.
     */
    public static function enqueue_editor_assets() {
        self::register();
        wp_enqueue_script( 'wlb-block-editor' );
        wp_enqueue_style( 'wlb-block-editor' );
    }

    /**
     * Enqueue frontend assets when block is present.
     */
    public static function enqueue_frontend_assets() {
        if ( has_block( 'wishlist-block/wishlist' ) ) {
            self::register();
            wp_enqueue_style( 'wlb-block-frontend' );
            wp_enqueue_script( 'wlb-block-editor' );
        }
    }
}
