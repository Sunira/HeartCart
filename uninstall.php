<?php
/**
 * Uninstall script.
 *
 * @package WishlistBlock
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

require_once __DIR__ . '/wishlist-block.php';

\WishlistBlock\WLB_Uninstall::uninstall();
