<?php
/**
 * MCP Server — JSON-RPC 2.0 protocol handler.
 *
 * Handles initialize, tools/list, and tools/call methods.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\MCP_Server' ) ) {

	/**
	 * MCP Server class.
	 *
	 * Parses JSON-RPC 2.0 requests and routes them to the appropriate handler.
	 *
	 * @since 1.0.0
	 */
	class MCP_Server {

		/**
		 * Tool registry instance.
		 *
		 * @var Tool_Registry
		 * @since 1.0.0
		 */
		protected $tool_registry;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param Tool_Registry $registry Tool registry instance.
		 */
		public function __construct( Tool_Registry $registry ) {
			$this->tool_registry = $registry;
		}

		/**
		 * Handle a JSON-RPC 2.0 request.
		 *
		 * Parses the raw JSON body, validates the protocol version,
		 * and dispatches to the appropriate method handler. Per the
		 * JSON-RPC 2.0 spec, requests without an `id` are notifications
		 * and receive no response (returns null).
		 *
		 * @since 1.0.0
		 *
		 * @param string $request_body Raw JSON request body.
		 * @param int    $user_id      Authenticated WordPress user ID.
		 * @return array|null Response array, or null for notifications.
		 */
		public function handle_request( $request_body, $user_id ) {
			$request = json_decode( $request_body, true );

			if ( JSON_ERROR_NONE !== json_last_error() ) {
				return $this->error_response( -32700, 'Parse error: invalid JSON', null );
			}

			if ( ! isset( $request['jsonrpc'] ) || '2.0' !== $request['jsonrpc'] ) {
				return $this->error_response( -32600, 'Invalid Request: missing or wrong jsonrpc version', null );
			}

			$method = isset( $request['method'] ) ? $request['method'] : null;
			$params = isset( $request['params'] ) ? $request['params'] : array();
			$id     = isset( $request['id'] ) ? $request['id'] : null;

			if ( null === $id ) {
				return null;
			}

			if ( empty( $method ) ) {
				return $this->error_response( -32600, 'Invalid Request: missing method', $id );
			}

			return $this->dispatch_method( $method, $params, $id, $user_id );
		}

		/**
		 * Dispatch JSON-RPC method to handler.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method  Method name.
		 * @param array  $params  Method parameters.
		 * @param mixed  $id      Request ID.
		 * @param int    $user_id Authenticated user ID.
		 * @return array JSON-RPC response.
		 */
		protected function dispatch_method( $method, $params, $id, $user_id ) {
			switch ( $method ) {
				case 'initialize':
					return $this->handle_initialize( $params, $id );

				case 'tools/list':
					return $this->handle_tools_list( $params, $id );

				case 'tools/call':
					return $this->handle_tool_call( $params, $id, $user_id );

				case 'ping':
					return $this->success_response( new \stdClass(), $id );

				default:
					return $this->error_response( -32601, 'Method not found: ' . sanitize_text_field( $method ), $id );
			}
		}

		/**
		 * Handle initialize request.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Request params.
		 * @param mixed $id     Request ID.
		 * @return array JSON-RPC response.
		 */
		protected function handle_initialize( $params, $id ) {
			return $this->success_response(
				array(
					'protocolVersion' => '2025-11-25',
					'capabilities'    => array(
						'tools' => new \stdClass(),
					),
					'serverInfo'      => array(
						'name'    => 'buddyboss-mcp-server',
						'version' => BBMCP_VERSION,
					),
				),
				$id
			);
		}

		/**
		 * Handle tools/list request.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Request params (unused, reserved for cursor pagination).
		 * @param mixed $id     Request ID.
		 * @return array JSON-RPC response.
		 */
		protected function handle_tools_list( $params, $id ) {
			$tools = $this->tool_registry->get_tool_definitions();

			return $this->success_response(
				array(
					'tools' => $tools,
				),
				$id
			);
		}

		/**
		 * Handle tools/call request.
		 *
		 * Resolves the tool name from the registry and invokes the
		 * provider method. Validation errors (InvalidArgumentException)
		 * are returned as MCP tool errors rather than protocol errors
		 * so the AI client can self-correct.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params  Request params containing name and arguments.
		 * @param mixed $id      Request ID.
		 * @param int   $user_id Authenticated user ID.
		 * @return array JSON-RPC response.
		 */
		protected function handle_tool_call( $params, $id, $user_id ) {
			$tool_name = isset( $params['name'] ) ? $params['name'] : null;
			$arguments = isset( $params['arguments'] ) ? $params['arguments'] : array();

			if ( empty( $tool_name ) ) {
				return $this->error_response( -32602, 'Invalid params: missing tool name', $id );
			}

			$tool_name = sanitize_text_field( $tool_name );
			$tool_info = $this->tool_registry->get_tool( $tool_name );

			if ( null === $tool_info ) {
				return $this->error_response( -32602, 'Tool not found: ' . $tool_name, $id );
			}

			try {
				$provider = $tool_info['provider'];
				$method   = $tool_info['method'];
				$result   = $provider->$method( $arguments, $user_id );

				return $this->success_response(
					array(
						'content' => array(
							array(
								'type' => 'text',
								'text' => $result,
							),
						),
					),
					$id
				);
			} catch ( \InvalidArgumentException $e ) {
				// Validation errors are safe to show — they contain parameter names only.
				return $this->success_response(
					array(
						'content' => array(
							array(
								'type' => 'text',
								'text' => 'Validation error: ' . $e->getMessage(),
							),
						),
						'isError' => true,
					),
					$id
				);
			} catch ( \Exception $e ) {
				// Log full details server-side; return a generic message to the client.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log(
						sprintf(
							'[BuddyBoss MCP] Tool "%s" error: %s in %s:%d',
							$tool_name,
							$e->getMessage(),
							$e->getFile(),
							$e->getLine()
						)
					);
				}

				return $this->success_response(
					array(
						'content' => array(
							array(
								'type' => 'text',
								'text' => 'An unexpected error occurred while executing the tool.',
							),
						),
						'isError' => true,
					),
					$id
				);
			}
		}

		/**
		 * Build a JSON-RPC 2.0 success response.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $result Response data.
		 * @param mixed $id     Request ID.
		 * @return array
		 */
		protected function success_response( $result, $id ) {
			return array(
				'jsonrpc' => '2.0',
				'id'      => $id,
				'result'  => $result,
			);
		}

		/**
		 * Build a JSON-RPC 2.0 error response.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $code    Error code.
		 * @param string $message Error message.
		 * @param mixed  $id      Request ID.
		 * @return array
		 */
		protected function error_response( $code, $message, $id ) {
			return array(
				'jsonrpc' => '2.0',
				'id'      => $id,
				'error'   => array(
					'code'    => $code,
					'message' => $message,
				),
			);
		}
	}
}
