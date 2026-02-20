<?php
/**
 * Tool Base — abstract base for all tool providers.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Tools;

use BuddyBossMCP\Internal_REST_Client;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tools\\Tool_Base' ) ) {

	/**
	 * Abstract tool base class.
	 *
	 * Every tool provider (Members, Groups, Activity, etc.) extends this class.
	 *
	 * @since 1.0.0
	 */
	abstract class Tool_Base {

		/**
		 * Internal REST client.
		 *
		 * @var Internal_REST_Client
		 * @since 1.0.0
		 */
		protected $rest_client;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->rest_client = new Internal_REST_Client();
		}

		/**
		 * Register tools provided by this class.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		abstract public function register_tools();

		/**
		 * Create a tool definition.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name        Tool name (e.g., 'buddyboss_list_groups').
		 * @param string $description Human-readable description.
		 * @param array  $properties  JSON Schema properties for inputSchema.
		 * @param string $method      Method name on this class to call.
		 * @param array  $required    Optional. Array of required property names.
		 * @return array Tool definition.
		 */
		protected function create_tool( $name, $description, $properties, $method, $required = array() ) {
			$input_schema = array(
				'type'       => 'object',
				'properties' => ! empty( $properties ) ? $properties : new \stdClass(),
			);

			if ( ! empty( $required ) ) {
				$input_schema['required'] = $required;
			}

			return array(
				'name'        => $name,
				'description' => $description,
				'inputSchema' => $input_schema,
				'provider'    => $this,
				'method'      => $method,
			);
		}

		/**
		 * Validate that required parameters are present.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args     Provided arguments.
		 * @param array $required Required parameter names.
		 * @throws \InvalidArgumentException If a required parameter is missing.
		 */
		protected function validate_required( $args, $required ) {
			foreach ( $required as $param ) {
				if ( ! isset( $args[ $param ] ) || '' === $args[ $param ] ) {
					throw new \InvalidArgumentException(
						sprintf( 'Missing required parameter: %s', $param )
					);
				}
			}
		}

		/**
		 * Get an integer parameter with default.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $args    Arguments.
		 * @param string $key     Parameter key.
		 * @param int    $default Default value.
		 * @return int
		 */
		protected function get_int( $args, $key, $default = 0 ) {
			return isset( $args[ $key ] ) ? absint( $args[ $key ] ) : $default;
		}

		/**
		 * Maximum allowed string parameter length.
		 *
		 * @var int
		 * @since 1.0.0
		 */
		const MAX_STRING_LENGTH = 1000;

		/**
		 * Get a string parameter with default.
		 *
		 * Sanitizes and truncates the value to prevent
		 * oversized input from degrading performance.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $args       Arguments.
		 * @param string $key        Parameter key.
		 * @param string $default    Default value.
		 * @param int    $max_length Maximum allowed length. Default 1000.
		 * @return string
		 */
		protected function get_string( $args, $key, $default = '', $max_length = 0 ) {
			if ( ! isset( $args[ $key ] ) ) {
				return $default;
			}

			$value = sanitize_text_field( $args[ $key ] );
			$limit = $max_length > 0 ? $max_length : self::MAX_STRING_LENGTH;

			if ( mb_strlen( $value ) > $limit ) {
				$value = mb_substr( $value, 0, $limit );
			}

			return $value;
		}

		/**
		 * Get a boolean parameter with default.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $args    Arguments.
		 * @param string $key     Parameter key.
		 * @param bool   $default Default value.
		 * @return bool
		 */
		protected function get_bool( $args, $key, $default = false ) {
			if ( ! isset( $args[ $key ] ) ) {
				return $default;
			}
			return filter_var( $args[ $key ], FILTER_VALIDATE_BOOLEAN );
		}
	}
}
