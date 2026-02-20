<?php
/**
 * Plugin Name: BuddyBoss MCP Server
 * Plugin URI:  https://developer.buddyboss.com/
 * Description: Exposes an MCP (Model Context Protocol) server endpoint so AI-powered IDEs can manage your BuddyBoss platform via natural language.
 * Version:     1.0.0
 * Author:      BuddyBoss
 * Author URI:  https://www.buddyboss.com/
 * Text Domain: buddyboss-mcp-server
 * Domain Path: /languages
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.6
 * Requires PHP: 7.4
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin constants.
 */
define( 'BBMCP_VERSION', '1.0.0' );
define( 'BBMCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BBMCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BBMCP_PLUGIN_FILE', __FILE__ );
define( 'BBMCP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load the plugin after all plugins are loaded.
 *
 * Verifies the minimum PHP version (7.4) before bootstrapping
 * the Plugin singleton.
 *
 * @since 1.0.0
 */
function bbmcp_init() {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		add_action( 'admin_notices', 'bbmcp_php_version_notice' );
		return;
	}

	require_once BBMCP_PLUGIN_DIR . 'includes/class-plugin.php';
	BuddyBossMCP\Plugin::instance();
}
add_action( 'plugins_loaded', 'bbmcp_init', 20 );

/**
 * Display PHP version notice.
 *
 * @since 1.0.0
 */
function bbmcp_php_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: Required PHP version. */
				esc_html__( 'BuddyBoss MCP Server requires PHP %s or higher. Please upgrade your PHP version.', 'buddyboss-mcp-server' ),
				'7.4'
			);
			?>
		</p>
	</div>
	<?php
}
