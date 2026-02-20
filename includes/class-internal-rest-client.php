<?php
/**
 * Internal REST Client — BuddyBoss API bridge.
 *
 * Wraps rest_do_request() to call BuddyBoss REST API endpoints
 * internally without HTTP overhead.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Internal_REST_Client' ) ) {

	/**
	 * Internal REST Client class.
	 *
	 * @since 1.0.0
	 */
	class Internal_REST_Client {

		/**
		 * Execute an internal REST API request.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method  HTTP method (GET, POST, PUT, PATCH, DELETE).
		 * @param string $route   REST route (e.g., '/buddyboss/v1/groups').
		 * @param array  $params  Request parameters.
		 * @param int    $user_id User ID to execute as.
		 * @return array|\WP_Error Response data or WP_Error.
		 */
		public function request( $method, $route, $params = array(), $user_id = 0 ) {
			$request = new \WP_REST_Request( strtoupper( $method ), $route );

			// Set parameters based on method.
			if ( 'GET' === strtoupper( $method ) ) {
				$request->set_query_params( $params );
			} else {
				$request->set_body_params( $params );
			}

			// Set content type for non-GET requests.
			if ( 'GET' !== strtoupper( $method ) ) {
				$request->set_header( 'Content-Type', 'application/json' );
			}

			// Switch user context if provided.
			$original_user_id = get_current_user_id();
			if ( $user_id > 0 && $user_id !== $original_user_id ) {
				wp_set_current_user( $user_id );
			}

			// Execute the internal request.
			$response = rest_do_request( $request );

			// Restore original user context.
			if ( $user_id > 0 && $user_id !== $original_user_id ) {
				wp_set_current_user( $original_user_id );
			}

			// Check for errors.
			if ( $response->is_error() ) {
				return $response->as_error();
			}

			return $response->get_data();
		}

		/**
		 * Send a GET request.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route   REST route.
		 * @param array  $params  Query parameters.
		 * @param int    $user_id User ID.
		 * @return array|\WP_Error
		 */
		public function get( $route, $params = array(), $user_id = 0 ) {
			return $this->request( 'GET', $route, $params, $user_id );
		}

		/**
		 * Send a POST request.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route   REST route.
		 * @param array  $params  Body parameters.
		 * @param int    $user_id User ID.
		 * @return array|\WP_Error
		 */
		public function post( $route, $params = array(), $user_id = 0 ) {
			return $this->request( 'POST', $route, $params, $user_id );
		}

		/**
		 * Send a PUT request.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route   REST route.
		 * @param array  $params  Body parameters.
		 * @param int    $user_id User ID.
		 * @return array|\WP_Error
		 */
		public function put( $route, $params = array(), $user_id = 0 ) {
			return $this->request( 'PUT', $route, $params, $user_id );
		}

		/**
		 * Send a PATCH request.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route   REST route.
		 * @param array  $params  Body parameters.
		 * @param int    $user_id User ID.
		 * @return array|\WP_Error
		 */
		public function patch( $route, $params = array(), $user_id = 0 ) {
			return $this->request( 'PATCH', $route, $params, $user_id );
		}

		/**
		 * Send a DELETE request.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route   REST route.
		 * @param array  $params  Body parameters.
		 * @param int    $user_id User ID.
		 * @return array|\WP_Error
		 */
		public function delete( $route, $params = array(), $user_id = 0 ) {
			return $this->request( 'DELETE', $route, $params, $user_id );
		}

		/**
		 * Format response data for MCP tool output.
		 *
		 * Converts response data to a JSON string suitable for MCP content.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $data Response data from REST API.
		 * @return string JSON-formatted string.
		 */
		public function format_response( $data ) {
			if ( is_wp_error( $data ) ) {
				return wp_json_encode(
					array(
						'error'   => true,
						'code'    => $data->get_error_code(),
						'message' => $data->get_error_message(),
					),
					JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
				);
			}

			return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		}
	}
}
