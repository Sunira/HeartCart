<?php
/**
 * Plugin Name:       Wishlist Block
 * Plugin URI:        https://example.com/plugins/wishlist-block
 * Description:       Provides a Gutenberg block and REST API for managing customer wishlists.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            HeartCart
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wishlist-block
 * Domain Path:       /languages
 *
 * @package WishlistBlock
 */

define( 'WLB_VERSION', '1.0.0' );
define( 'WLB_PLUGIN_FILE', __FILE__ );
define( 'WLB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WLB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once WLB_PLUGIN_DIR . 'includes/class-wlb-plugin.php';

\WishlistBlock\WLB_Plugin::get_instance();
