<?php
/**
 * REST Controller — WordPress REST API endpoint for MCP.
 *
 * Exposes a single POST endpoint at /wp-json/buddyboss-mcp/v1/mcp
 * that accepts JSON-RPC 2.0 requests and routes them to the MCP server.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\REST_Controller' ) ) {

	/**
	 * REST Controller class.
	 *
	 * @since 1.0.0
	 */
	class REST_Controller extends \WP_REST_Controller {

		/**
		 * REST namespace.
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $namespace = 'buddyboss-mcp/v1';

		/**
		 * REST route base.
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $rest_base = 'mcp';

		/**
		 * MCP server instance.
		 *
		 * @var MCP_Server
		 * @since 1.0.0
		 */
		protected $mcp_server;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param MCP_Server $server MCP server instance.
		 */
		public function __construct( MCP_Server $server ) {
			$this->mcp_server = $server;
		}

		/**
		 * Register REST routes.
		 *
		 * @since 1.0.0
		 */
		public function register_routes() {
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => \WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'handle_post_request' ),
						'permission_callback' => array( $this, 'check_permissions' ),
					),
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'handle_get_request' ),
						'permission_callback' => array( $this, 'check_permissions' ),
					),
				)
			);
		}

		/**
		 * Check permissions for MCP access.
		 *
		 * WordPress Application Passwords handle authentication automatically
		 * via the Authorization header. We just check the user is logged in
		 * and has the manage_options capability.
		 *
		 * @since 1.0.0
		 *
		 * @param \WP_REST_Request $request REST request.
		 * @return bool|\WP_Error True if permitted, WP_Error otherwise.
		 */
		public function check_permissions( $request ) {
			if ( ! is_user_logged_in() ) {
				return new \WP_Error(
					'bbmcp_unauthorized',
					__( 'Authentication required. Use WordPress Application Passwords.', 'buddyboss-mcp-server' ),
					array( 'status' => 401 )
				);
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return new \WP_Error(
					'bbmcp_forbidden',
					__( 'Insufficient permissions. Administrator access required.', 'buddyboss-mcp-server' ),
					array( 'status' => 403 )
				);
			}

			return true;
		}

		/**
		 * Handle POST request (JSON-RPC message).
		 *
		 * Passes the raw body to the MCP server for processing.
		 * JSON-RPC notifications (no `id`) return null and receive
		 * a 202 Accepted response.
		 *
		 * @since 1.0.0
		 *
		 * @param \WP_REST_Request $request REST request.
		 * @return \WP_REST_Response
		 */
		public function handle_post_request( $request ) {
			$body    = $request->get_body();
			$user_id = get_current_user_id();

			$response = $this->mcp_server->handle_request( $body, $user_id );

			if ( null === $response ) {
				return new \WP_REST_Response( null, 202 );
			}

			return rest_ensure_response( $response );
		}

		/**
		 * Handle GET request (server info / health check).
		 *
		 * @since 1.0.0
		 *
		 * @param \WP_REST_Request $request REST request.
		 * @return \WP_REST_Response
		 */
		public function handle_get_request( $request ) {
			return rest_ensure_response(
				array(
					'name'    => 'buddyboss-mcp-server',
					'version' => BBMCP_VERSION,
					'status'  => 'active',
				)
			);
		}
	}
}
