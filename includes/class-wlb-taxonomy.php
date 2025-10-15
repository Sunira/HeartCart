<?php
/**
 * Wishlist taxonomies.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers the wishlist taxonomies.
 */
class WLB_Taxonomy {
    /**
     * Register taxonomies.
     */
    public static function register() {
        $labels = [
            'name'          => _x( 'Wishlist Categories', 'taxonomy general name', 'wishlist-block' ),
            'singular_name' => _x( 'Wishlist Category', 'taxonomy singular name', 'wishlist-block' ),
            'search_items'  => __( 'Search Categories', 'wishlist-block' ),
            'all_items'     => __( 'All Categories', 'wishlist-block' ),
            'edit_item'     => __( 'Edit Category', 'wishlist-block' ),
            'update_item'   => __( 'Update Category', 'wishlist-block' ),
            'add_new_item'  => __( 'Add New Category', 'wishlist-block' ),
            'new_item_name' => __( 'New Category Name', 'wishlist-block' ),
            'menu_name'     => __( 'Categories', 'wishlist-block' ),
        ];

        register_taxonomy(
            'wlb_wishlist_category',
            [ WLB_CPT::POST_TYPE ],
            [
                'hierarchical'      => true,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => false,
                'show_in_rest'      => true,
            ]
        );
    }
}
