<?php
/**
 * Tool Registry — discovers and manages MCP tools.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Tool_Registry' ) ) {

	/**
	 * Tool Registry class.
	 *
	 * Loads tool providers, collects tool definitions, and routes
	 * tool calls to the correct provider method.
	 *
	 * @since 1.0.0
	 */
	class Tool_Registry {

		/**
		 * Registered tool providers.
		 *
		 * @var Tools\Tool_Base[]
		 * @since 1.0.0
		 */
		protected $providers = array();

		/**
		 * Flattened tool map: name => array( provider, method, definition ).
		 *
		 * @var array
		 * @since 1.0.0
		 */
		protected $tools = array();

		/**
		 * Tool definitions for tools/list response (without internal data).
		 *
		 * @var array
		 * @since 1.0.0
		 */
		protected $definitions = array();

		/**
		 * Tool definitions grouped by provider label.
		 *
		 * @var array
		 * @since 1.0.0
		 */
		protected $grouped_definitions = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->register_providers();
			$this->load_tools();
		}

		/**
		 * Register all tool providers.
		 *
		 * @since 1.0.0
		 */
		protected function register_providers() {
			$this->providers[] = new Tools\Members_Tools();
			$this->providers[] = new Tools\Groups_Tools();
			$this->providers[] = new Tools\Activity_Tools();
			$this->providers[] = new Tools\Messages_Tools();
			$this->providers[] = new Tools\Friends_Tools();
			$this->providers[] = new Tools\Notifications_Tools();
			$this->providers[] = new Tools\XProfile_Tools();

			/**
			 * Filter the list of tool providers.
			 *
			 * Allows other plugins to register additional tool providers.
			 *
			 * @since 1.0.0
			 *
			 * @param Tools\Tool_Base[] $providers Array of tool provider instances.
			 */
			$this->providers = apply_filters( 'bbmcp_tool_providers', $this->providers );
		}

		/**
		 * Load tool definitions from all providers.
		 *
		 * @since 1.0.0
		 */
		protected function load_tools() {
			foreach ( $this->providers as $provider ) {
				$provider_tools = $provider->register_tools();

				$class = get_class( $provider );
				$short = substr( strrchr( $class, '\\' ), 1 );
				$label = str_replace( array( '_Tools', '_' ), array( '', ' ' ), $short );

				$group_defs = array();

				foreach ( $provider_tools as $tool ) {
					$name = $tool['name'];

					$this->tools[ $name ] = array(
						'provider' => $tool['provider'],
						'method'   => $tool['method'],
					);

					$def = array(
						'name'        => $tool['name'],
						'description' => $tool['description'],
						'inputSchema' => $tool['inputSchema'],
					);

					$this->definitions[] = $def;
					$group_defs[]        = $def;
				}

				$this->grouped_definitions[ $label ] = $group_defs;
			}
		}

		/**
		 * Get all tool definitions for tools/list response.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of tool definitions.
		 */
		public function get_tool_definitions() {
			return $this->definitions;
		}

		/**
		 * Get tool definitions grouped by provider label.
		 *
		 * @since 1.0.0
		 *
		 * @return array Associative array keyed by group label.
		 */
		public function get_grouped_definitions() {
			return $this->grouped_definitions;
		}

		/**
		 * Get a tool by name for execution.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Tool name.
		 * @return array|null Array with provider and method, or null if not found.
		 */
		public function get_tool( $name ) {
			if ( ! isset( $this->tools[ $name ] ) ) {
				return null;
			}
			return $this->tools[ $name ];
		}

		/**
		 * Get total number of registered tools.
		 *
		 * @since 1.0.0
		 *
		 * @return int
		 */
		public function count() {
			return count( $this->definitions );
		}

		/**
		 * Get registered providers.
		 *
		 * @since 1.0.0
		 *
		 * @return Tools\Tool_Base[]
		 */
		public function get_providers() {
			return $this->providers;
		}
	}
}
