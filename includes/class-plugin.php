<?php
/**
 * Main plugin class.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

namespace BuddyBossMCP;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBossMCP\\Plugin' ) ) {

	/**
	 * Plugin singleton.
	 *
	 * Boots the MCP server, REST endpoint, tool registry, and admin page.
	 *
	 * @since 1.0.0
	 */
	final class Plugin {

		/**
		 * Singleton instance.
		 *
		 * @var Plugin|null
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * MCP server instance.
		 *
		 * @var MCP_Server
		 * @since 1.0.0
		 */
		protected $server;

		/**
		 * REST controller instance.
		 *
		 * @var REST_Controller
		 * @since 1.0.0
		 */
		protected $rest_controller;

		/**
		 * Tool registry instance.
		 *
		 * @var Tool_Registry
		 * @since 1.0.0
		 */
		protected $tool_registry;

		/**
		 * Admin page instance.
		 *
		 * @var Admin\Admin_Page
		 * @since 1.0.0
		 */
		protected $admin;

		/**
		 * Get singleton instance.
		 *
		 * @since 1.0.0
		 *
		 * @return Plugin
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			if ( ! $this->check_dependencies() ) {
				return;
			}

			$this->includes();
			$this->init_components();
			$this->setup_hooks();
		}

		/**
		 * Check required dependencies.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if all dependencies met.
		 */
		private function check_dependencies() {
			if ( ! function_exists( 'buddypress' ) && ! class_exists( 'BuddyBoss_Platform' ) ) {
				add_action( 'admin_notices', array( $this, 'buddyboss_missing_notice' ) );
				return false;
			}
			return true;
		}

		/**
		 * Include required files.
		 *
		 * Loads core classes, the tool base, admin page, and all
		 * tool providers for the MCP server.
		 *
		 * @since 1.0.0
		 */
		private function includes() {
			require_once BBMCP_PLUGIN_DIR . 'includes/class-mcp-server.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/class-rest-controller.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/class-tool-registry.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/class-internal-rest-client.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/tools/class-tool-base.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/admin/class-admin-page.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/tools/class-members-tools.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/tools/class-groups-tools.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/tools/class-activity-tools.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/tools/class-messages-tools.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/tools/class-friends-tools.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/tools/class-notifications-tools.php';
			require_once BBMCP_PLUGIN_DIR . 'includes/tools/class-xprofile-tools.php';
		}

		/**
		 * Initialize components.
		 *
		 * @since 1.0.0
		 */
		private function init_components() {
			$this->tool_registry   = new Tool_Registry();
			$this->server          = new MCP_Server( $this->tool_registry );
			$this->rest_controller = new REST_Controller( $this->server );
			$this->admin           = new Admin\Admin_Page( $this->tool_registry );
		}

		/**
		 * Register WordPress hooks.
		 *
		 * Sets up REST routes, admin menus, and enables Application Passwords
		 * on local/dev environments that lack HTTPS.
		 *
		 * @since 1.0.0
		 */
		private function setup_hooks() {
			add_action( 'rest_api_init', array( $this->rest_controller, 'register_routes' ) );
			add_action( 'admin_menu', array( $this->admin, 'register_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_assets' ) );

			if ( $this->is_local_environment() ) {
				add_filter( 'wp_is_application_passwords_available', '__return_true' );
			}
		}

		/**
		 * Check if running on a local development environment.
		 *
		 * Detects LocalWP (.local domains), wp-env, and other local setups.
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		private function is_local_environment() {
			$site_url = site_url();

			if ( false !== strpos( $site_url, '.local' ) ) {
				return true;
			}

			if ( false !== strpos( $site_url, 'localhost' ) || false !== strpos( $site_url, '127.0.0.1' ) ) {
				return true;
			}

			if ( function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type() ) {
				return true;
			}

			return false;
		}

		/**
		 * Display BuddyBoss missing notice.
		 *
		 * @since 1.0.0
		 */
		public function buddyboss_missing_notice() {
			?>
			<div class="notice notice-error">
				<p>
					<?php esc_html_e( 'BuddyBoss MCP Server requires BuddyBoss Platform to be installed and activated.', 'buddyboss-mcp-server' ); ?>
				</p>
			</div>
			<?php
		}

		/**
		 * Get the tool registry.
		 *
		 * @since 1.0.0
		 *
		 * @return Tool_Registry
		 */
		public function get_tool_registry() {
			return $this->tool_registry;
		}

		/**
		 * Prevent cloning.
		 *
		 * @since 1.0.0
		 */
		private function __clone() {}

		/**
		 * Prevent unserializing.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			throw new \Exception( 'Cannot unserialize singleton.' );
		}
	}
}
