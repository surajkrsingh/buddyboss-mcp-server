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
		 * Admin screen hook suffix returned by add_menu_page().
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $hook_suffix = '';

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
		 * Stores the hook suffix so we can conditionally clean up
		 * the admin chrome (notices, headers) on this page only.
		 *
		 * @since 1.0.0
		 */
		public function register_menu() {
			$this->hook_suffix = add_menu_page(
				__( 'BuddyBoss MCP Server', 'buddyboss-mcp-server' ),
				__( 'MCP Server', 'buddyboss-mcp-server' ),
				'manage_options',
				'bbmcp-server',
				array( $this, 'render_page' ),
				'dashicons-rest-api',
				100
			);

			if ( $this->hook_suffix ) {
				add_action( 'load-' . $this->hook_suffix, array( $this, 'remove_admin_chrome' ) );
			}
		}

		/**
		 * Remove BuddyBoss header and all admin notices on this page.
		 *
		 * Hooked to `load-{$hook_suffix}` so it only fires when
		 * the MCP Server settings page is being rendered.
		 *
		 * @since 1.0.0
		 */
		public function remove_admin_chrome() {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
			remove_all_actions( 'network_admin_notices' );
			remove_all_actions( 'user_admin_notices' );

			if ( function_exists( 'bb_render_admin_header' ) ) {
				remove_action( 'in_admin_header', 'bb_render_admin_header', 999 );
			}

			add_action( 'in_admin_header', array( $this, 'render_admin_header' ) );
		}

		/**
		 * Render a slim admin header bar for the MCP Server page.
		 *
		 * Outputs a full-width header inside the `in_admin_header` hook,
		 * sitting above the page content with title, subtitle, and badges.
		 *
		 * @since 1.0.0
		 */
		public function render_admin_header() {
			?>
			<div class="bbmcp-admin-header">
				<div class="bbmcp-admin-header-inner">
					<div class="bbmcp-admin-header-left">
						<span class="bbmcp-admin-header-icon"><span class="dashicons dashicons-rest-api"></span></span>
						<h1 class="bbmcp-admin-header-title"><?php esc_html_e( 'BuddyBoss MCP Server', 'buddyboss-mcp-server' ); ?></h1>
						<div class="bbmcp-admin-header-badges">
							<span class="bbmcp-version-badge">v<?php echo esc_html( BBMCP_VERSION ); ?></span>
						</div>
					</div>
					<div class="bbmcp-admin-header-right">
						<span id="bbmcp-header-status" class="bbmcp-header-status"></span>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Enqueue admin assets.
		 *
		 * Loads the admin stylesheet (with dashicons dependency for
		 * sidebar widget icons) and the admin script with localized
		 * endpoint and i18n data.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook_suffix Admin page hook suffix.
		 */
		public function enqueue_assets( $hook_suffix ) {
			if ( 'toplevel_page_bbmcp-server' !== $hook_suffix ) {
				return;
			}

			wp_enqueue_style(
				'bbmcp-admin',
				BBMCP_PLUGIN_URL . 'assets/css/admin.css',
				array( 'dashicons' ),
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
						'copied'       => __( 'Copied!', 'buddyboss-mcp-server' ),
						'copyFailed'   => __( 'Copy failed', 'buddyboss-mcp-server' ),
						'testing'      => __( 'Testing...', 'buddyboss-mcp-server' ),
						'success'      => __( 'Connection successful', 'buddyboss-mcp-server' ),
						'failed'       => __( 'Connection failed', 'buddyboss-mcp-server' ),
						'connected'    => __( 'Connected', 'buddyboss-mcp-server' ),
						'disconnected' => __( 'Disconnected', 'buddyboss-mcp-server' ),
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
