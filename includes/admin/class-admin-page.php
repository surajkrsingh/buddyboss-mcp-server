<?php
/**
 * Admin Page — MCP Server settings and connection info.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Admin\\Admin_Page' ) ) {

	/**
	 * Admin Page class.
	 *
	 * @since 1.0.0
	 */
	class Admin_Page {

		/**
		 * Tool registry instance.
		 *
		 * @var \BuddyBossMCP\Tool_Registry
		 * @since 1.0.0
		 */
		protected $tool_registry;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param \BuddyBossMCP\Tool_Registry $tool_registry Tool registry.
		 */
		public function __construct( $tool_registry ) {
			$this->tool_registry = $tool_registry;
		}

		/**
		 * Register admin menu.
		 *
		 * @since 1.0.0
		 */
		public function register_menu() {
			add_menu_page(
				__( 'BuddyBoss MCP Server', 'buddyboss-mcp-server' ),
				__( 'MCP Server', 'buddyboss-mcp-server' ),
				'manage_options',
				'buddyboss-mcp-server',
				array( $this, 'render_page' ),
				'dashicons-rest-api',
				100
			);
		}

		/**
		 * Enqueue admin assets.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook_suffix Admin page hook suffix.
		 */
		public function enqueue_assets( $hook_suffix ) {
			if ( 'toplevel_page_buddyboss-mcp-server' !== $hook_suffix ) {
				return;
			}

			wp_enqueue_style(
				'bbmcp-admin',
				BBMCP_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				BBMCP_VERSION
			);

			wp_enqueue_script(
				'bbmcp-admin',
				BBMCP_PLUGIN_URL . 'assets/js/admin.js',
				array(),
				BBMCP_VERSION,
				true
			);

			wp_localize_script(
				'bbmcp-admin',
				'bbmcpAdmin',
				array(
					'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'wp_rest' ),
					'endpoint' => rest_url( 'buddyboss-mcp/v1/mcp' ),
					'i18n'     => array(
						'copied'     => __( 'Copied!', 'buddyboss-mcp-server' ),
						'copyFailed' => __( 'Copy failed', 'buddyboss-mcp-server' ),
						'testing'    => __( 'Testing...', 'buddyboss-mcp-server' ),
						'success'    => __( 'Connection successful!', 'buddyboss-mcp-server' ),
						'failed'     => __( 'Connection failed', 'buddyboss-mcp-server' ),
					),
				)
			);
		}

		/**
		 * Render the settings page.
		 *
		 * @since 1.0.0
		 */
		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$endpoint_url = rest_url( 'buddyboss-mcp/v1/mcp' );
			$tool_count   = $this->tool_registry->count();
			$current_user = wp_get_current_user();

			include BBMCP_PLUGIN_DIR . 'templates/admin/settings-page.php';
		}
	}
}
