<?php
/**
 * Wishlist REST controller.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock\REST;

use WishlistBlock\WLB_CPT;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles wishlist item CRUD via REST.
 */
class WLB_REST_Items_Controller extends WLB_REST_Controller {
    /**
     * Rest base.
     *
     * @var string
     */
    protected $rest_base = 'items';

    /**
     * Register routes.
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( true ),
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>\d+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( false ),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
            ]
        );
    }

    /**
     * Permissions check.
     *
     * @param WP_REST_Request $request Request.
     * @return true|WP_Error
     */
    public function permissions_check( WP_REST_Request $request ) {
        return $this->check_permissions( $request );
    }

    /**
     * Get items.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_items( WP_REST_Request $request ) {
        $items = self::get_wishlist_items_for_current_user();

        /**
         * Filters the REST response data for wishlist items.
         *
         * @param array           $items Items.
         * @param WP_REST_Request $request Request.
         */
        $items = apply_filters( 'wlb_rest_items_response', $items, $request );

        return $this->prepare_response( [ 'items' => $items ], $request );
    }

    /**
     * Get single item.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function get_item( WP_REST_Request $request ) {
        $item = $this->prepare_item_for_response( get_post( (int) $request['id'] ), $request );

        if ( is_wp_error( $item ) ) {
            return $item;
        }

        return $this->prepare_response( [ 'item' => $item ], $request );
    }

    /**
     * Create item.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function create_item( WP_REST_Request $request ) {
        $post_id = wp_insert_post(
            [
                'post_type'    => WLB_CPT::POST_TYPE,
                'post_status'  => 'publish',
                'post_title'   => sanitize_text_field( $request['title'] ),
                'post_content' => wp_kses_post( $request['description'] ),
                'post_author'  => get_current_user_id(),
            ]
        );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        $this->save_meta( $post_id, $request );

        if ( ! isset( $request['userId'] ) ) {
            update_post_meta( $post_id, 'wlb_user_id', get_current_user_id() );
        }

        do_action( 'wlb_item_created', $post_id, $request );

        return $this->prepare_response( [ 'item' => $this->prepare_item_for_response( get_post( $post_id ), $request ) ], $request );
    }

    /**
     * Update item.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function update_item( WP_REST_Request $request ) {
        $post_id = (int) $request['id'];

        $update = wp_update_post(
            [
                'ID'           => $post_id,
                'post_title'   => sanitize_text_field( $request['title'] ),
                'post_content' => wp_kses_post( $request['description'] ),
            ]
        );

        if ( is_wp_error( $update ) ) {
            return $update;
        }

        $this->save_meta( $post_id, $request );

        do_action( 'wlb_item_updated', $post_id, $request );

        return $this->prepare_response( [ 'item' => $this->prepare_item_for_response( get_post( $post_id ), $request ) ], $request );
    }

    /**
     * Delete item.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function delete_item( WP_REST_Request $request ) {
        $post_id = (int) $request['id'];

        $result = wp_delete_post( $post_id, true );

        if ( ! $result ) {
            return new WP_Error( 'wlb_delete_failed', __( 'Unable to delete wishlist item.', 'wishlist-block' ), [ 'status' => 500 ] );
        }

        do_action( 'wlb_item_deleted', $post_id, $request );

        return $this->prepare_response( [ 'deleted' => true ], $request );
    }

    /**
     * Prepare item for response.
     *
     * @param \WP_Post|null   $post Post.
     * @param WP_REST_Request $request Request.
     * @return array|WP_Error
     */
    public function prepare_item_for_response( $post, WP_REST_Request $request ) {
        if ( ! $post || WLB_CPT::POST_TYPE !== $post->post_type ) {
            return new WP_Error( 'wlb_invalid_item', __( 'Wishlist item not found.', 'wishlist-block' ), [ 'status' => 404 ] );
        }

        if ( (int) $post->post_author !== get_current_user_id() && ! current_user_can( 'edit_post', $post->ID ) ) {
            return new WP_Error( 'wlb_forbidden', __( 'You cannot access this item.', 'wishlist-block' ), [ 'status' => 403 ] );
        }

        $item = [
            'id'          => (int) $post->ID,
            'title'       => $post->post_title,
            'description' => wp_kses_post( $post->post_content ),
            'productId'   => (int) get_post_meta( $post->ID, 'wlb_product_id', true ),
            'priority'    => (int) get_post_meta( $post->ID, 'wlb_priority', true ),
            'userId'      => (int) get_post_meta( $post->ID, 'wlb_user_id', true ),
            'categories'  => wp_get_post_terms( $post->ID, 'wlb_wishlist_category', [ 'fields' => 'ids' ] ),
        ];

        /**
         * Filters the wishlist item in REST response.
         *
         * @param array           $item Item data.
         * @param WP_REST_Request $request Request.
         */
        $item = apply_filters( 'wlb_rest_prepare_item', $item, $request );

        return $item;
    }

    /**
     * Save meta fields from request.
     *
     * @param int             $post_id Post ID.
     * @param WP_REST_Request $request Request.
     */
    protected function save_meta( $post_id, WP_REST_Request $request ) {
        if ( isset( $request['productId'] ) ) {
            update_post_meta( $post_id, 'wlb_product_id', absint( $request['productId'] ) );
        }

        if ( isset( $request['priority'] ) ) {
            update_post_meta( $post_id, 'wlb_priority', absint( $request['priority'] ) );
        }

        if ( isset( $request['userId'] ) ) {
            update_post_meta( $post_id, 'wlb_user_id', absint( $request['userId'] ) );
        }

        if ( isset( $request['categories'] ) && is_array( $request['categories'] ) ) {
            wp_set_post_terms( $post_id, array_map( 'absint', $request['categories'] ), 'wlb_wishlist_category', false );
        }
    }

    /**
     * Get item schema.
     *
     * @return array
     */
    public function get_item_schema() {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'wishlist_item',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'type'     => 'integer',
                    'readOnly' => true,
                ],
                'title'       => [
                    'type'     => 'string',
                    'required' => true,
                ],
                'description' => [
                    'type' => 'string',
                ],
                'productId'   => [
                    'type' => 'integer',
                ],
                'priority'    => [
                    'type'    => 'integer',
                    'default' => 1,
                ],
                'userId'      => [
                    'type' => 'integer',
                ],
                'categories'  => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'integer',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get collection params.
     *
     * @return array
     */
    public function get_collection_params() {
        $params = parent::get_collection_params();
        unset( $params['search'] );
        return $params;
    }

    /**
     * Helper to get current user's wishlist items.
     *
     * @return array
     */
    public static function get_wishlist_items_for_current_user() {
        $user_id = get_current_user_id();

        $posts = get_posts(
            [
                'post_type'      => WLB_CPT::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'author'         => $user_id,
            ]
        );

        $items = [];
        foreach ( $posts as $post ) {
            $items[] = [
                'id'          => (int) $post->ID,
                'title'       => $post->post_title,
                'description' => wp_strip_all_tags( $post->post_content ),
                'productId'   => (int) get_post_meta( $post->ID, 'wlb_product_id', true ),
                'priority'    => (int) get_post_meta( $post->ID, 'wlb_priority', true ),
                'userId'      => (int) get_post_meta( $post->ID, 'wlb_user_id', true ),
                'categories'  => wp_get_post_terms( $post->ID, 'wlb_wishlist_category', [ 'fields' => 'ids' ] ),
            ];
        }

        return $items;
    }
}
