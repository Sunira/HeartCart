<?php
/**
 * Main plugin bootstrap class.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin singleton.
 */
class WLB_Plugin {
    /**
     * Singleton instance.
     *
     * @var WLB_Plugin|null
     */
    private static $instance = null;

    /**
     * Get the singleton instance.
     *
     * @return WLB_Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->includes();
        $this->hooks();
    }

    /**
     * Include required files.
     */
    private function includes() {
        require_once WLB_PLUGIN_DIR . 'includes/class-wlb-utilities.php';
        require_once WLB_PLUGIN_DIR . 'includes/class-wlb-assets.php';
        require_once WLB_PLUGIN_DIR . 'includes/class-wlb-cpt.php';
        require_once WLB_PLUGIN_DIR . 'includes/class-wlb-taxonomy.php';
        require_once WLB_PLUGIN_DIR . 'includes/class-wlb-meta.php';
        require_once WLB_PLUGIN_DIR . 'includes/class-wlb-render.php';
        require_once WLB_PLUGIN_DIR . 'includes/class-wlb-privacy.php';
        require_once WLB_PLUGIN_DIR . 'includes/class-wlb-uninstall.php';
        require_once WLB_PLUGIN_DIR . 'includes/rest/class-wlb-rest-controller.php';
        require_once WLB_PLUGIN_DIR . 'includes/rest/class-wlb-rest-items-controller.php';
    }

    /**
     * Register hooks.
     */
    private function hooks() {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'init', [ $this, 'register_content_types' ] );
        add_action( 'init', [ __NAMESPACE__ . '\WLB_Taxonomy', 'register' ] );
        add_action( 'init', [ __NAMESPACE__ . '\WLB_Meta', 'register' ] );
        add_action( 'init', [ __NAMESPACE__ . '\WLB_Render', 'register_block' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        add_action( 'enqueue_block_editor_assets', [ __NAMESPACE__ . '\WLB_Assets', 'enqueue_editor_assets' ] );
        add_action( 'wp_enqueue_scripts', [ __NAMESPACE__ . '\WLB_Assets', 'enqueue_frontend_assets' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( WLB_PLUGIN_FILE ), [ $this, 'plugin_action_links' ] );
        add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
        add_filter( 'wp_privacy_personal_data_exporters', [ __NAMESPACE__ . '\WLB_Privacy', 'register_exporter' ] );
        add_filter( 'wp_privacy_personal_data_erasers', [ __NAMESPACE__ . '\WLB_Privacy', 'register_eraser' ] );
        add_action( 'admin_init', [ $this, 'add_privacy_policy_content' ] );

        register_uninstall_hook( WLB_PLUGIN_FILE, [ '\WishlistBlock\WLB_Uninstall', 'uninstall' ] );
    }

    /**
     * Load translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'wishlist-block', false, dirname( plugin_basename( WLB_PLUGIN_FILE ) ) . '/languages' );
    }

    /**
     * Register CPT.
     */
    public function register_content_types() {
        WLB_CPT::register();
    }

    /**
     * Register REST routes.
     */
    public function register_rest_routes() {
        $controller = new REST\WLB_REST_Items_Controller();
        $controller->register_routes();
    }

    /**
     * Adds privacy policy content.
     */
    public function add_privacy_policy_content() {
        if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
            $content = '<p>' . esc_html__( 'Wishlist Block stores wishlist items for logged in users and allows administrators to erase or export this data.', 'wishlist-block' ) . '</p>';
            wp_add_privacy_policy_content( __( 'Wishlist Block', 'wishlist-block' ), $content );
        }
    }

    /**
     * Plugin action links.
     *
     * @param array  $links Existing links.
     * @param string $file  Plugin file.
     * @return array
     */
    public function plugin_action_links( $links ) {
        $settings_link = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=wlb_wishlist_item&page=wlb-settings' ) ), esc_html__( 'Settings', 'wishlist-block' ) );
        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Plugin row meta.
     *
     * @param array  $links Links.
     * @param string $file  Plugin file.
     * @return array
     */
    public function plugin_row_meta( $links, $file ) {
        if ( plugin_basename( WLB_PLUGIN_FILE ) !== $file ) {
            return $links;
        }

        $links[] = sprintf( '<a href="%s" target="_blank" rel="noreferrer noopener">%s</a>', esc_url( 'https://example.com/docs/wishlist-block' ), esc_html__( 'Documentation', 'wishlist-block' ) );

        return $links;
    }
}
