<?php
/**
 * Base REST controller.
 *
 * @package WishlistBlock
 */

namespace WishlistBlock\REST;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Base controller with shared helpers.
 */
abstract class WLB_REST_Controller extends WP_REST_Controller {
    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace = 'wishlist/v1';

    /**
     * Ensure nonce and capability.
     *
     * @param WP_REST_Request $request Request.
     * @return true|WP_Error
     */
    protected function check_permissions( WP_REST_Request $request ) {
        if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            return new WP_Error( 'wlb_invalid_nonce', __( 'Invalid nonce.', 'wishlist-block' ), [ 'status' => 403 ] );
        }

        if ( ! current_user_can( 'read' ) ) {
            return new WP_Error( 'wlb_forbidden', __( 'You do not have permission to manage the wishlist.', 'wishlist-block' ), [ 'status' => 403 ] );
        }

        return true;
    }

    /**
     * Prepare response with hooks.
     *
     * @param array            $data Data.
     * @param WP_REST_Request  $request Request.
     * @param int|string|array $context Context.
     *
     * @return WP_REST_Response
     */
    protected function prepare_response( array $data, WP_REST_Request $request, $context = 'view' ) {
        $response = rest_ensure_response( $data );

        /**
         * Filters the REST response for wishlist routes.
         *
         * @param WP_REST_Response $response Response.
         * @param WP_REST_Request  $request Request.
         * @param mixed            $context Context.
         */
        return apply_filters( 'wlb_rest_prepare_response', $response, $request, $context );
    }
}
