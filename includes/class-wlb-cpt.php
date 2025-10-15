<?php
/**
 * Wishlist item custom post type.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers the wishlist item CPT.
 */
class WLB_CPT {
    /**
     * CPT slug.
     */
    const POST_TYPE = 'wlb_wishlist_item';

    /**
     * Register the CPT.
     */
    public static function register() {
        $labels = [
            'name'                  => _x( 'Wishlist Items', 'Post Type General Name', 'wishlist-block' ),
            'singular_name'         => _x( 'Wishlist Item', 'Post Type Singular Name', 'wishlist-block' ),
            'menu_name'             => __( 'Wishlist Items', 'wishlist-block' ),
            'name_admin_bar'        => __( 'Wishlist Item', 'wishlist-block' ),
            'add_new'               => __( 'Add New', 'wishlist-block' ),
            'add_new_item'          => __( 'Add New Item', 'wishlist-block' ),
            'new_item'              => __( 'New Item', 'wishlist-block' ),
            'edit_item'             => __( 'Edit Item', 'wishlist-block' ),
            'view_item'             => __( 'View Item', 'wishlist-block' ),
            'all_items'             => __( 'All Items', 'wishlist-block' ),
            'search_items'          => __( 'Search Items', 'wishlist-block' ),
            'not_found'             => __( 'No items found.', 'wishlist-block' ),
            'not_found_in_trash'    => __( 'No items found in Trash.', 'wishlist-block' ),
            'featured_image'        => __( 'Item Image', 'wishlist-block' ),
            'set_featured_image'    => __( 'Set item image', 'wishlist-block' ),
            'remove_featured_image' => __( 'Remove item image', 'wishlist-block' ),
            'use_featured_image'    => __( 'Use as item image', 'wishlist-block' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'has_archive'        => false,
            'exclude_from_search'=> true,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'supports'           => [ 'title', 'editor', 'thumbnail' ],
            'capability_type'    => 'post',
            'capabilities'       => self::get_capabilities(),
            'map_meta_cap'       => true,
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Returns capabilities array.
     *
     * @return array
     */
    public static function get_capabilities() {
        return [
            'edit_post'          => 'edit_wlb_wishlist_item',
            'read_post'          => 'read_wlb_wishlist_item',
            'delete_post'        => 'delete_wlb_wishlist_item',
            'edit_posts'         => 'edit_wlb_wishlist_items',
            'edit_others_posts'  => 'edit_others_wlb_wishlist_items',
            'publish_posts'      => 'publish_wlb_wishlist_items',
            'read_private_posts' => 'read_private_wlb_wishlist_items',
        ];
    }
}
