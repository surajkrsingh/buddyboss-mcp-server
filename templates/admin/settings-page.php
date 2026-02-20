<?php
/**
 * Admin settings page template.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 *
 * @var string $endpoint_url MCP endpoint URL.
 * @var int    $tool_count   Number of registered tools.
 * @var object $current_user Current WordPress user.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap bbmcp-settings">
	<h1><?php esc_html_e( 'BuddyBoss MCP Server', 'buddyboss-mcp-server' ); ?></h1>

	<!-- Server Status -->
	<div class="bbmcp-card">
		<h2><?php esc_html_e( 'Server Status', 'buddyboss-mcp-server' ); ?></h2>
		<table class="bbmcp-status-table">
			<tr>
				<td><?php esc_html_e( 'Status', 'buddyboss-mcp-server' ); ?></td>
				<td><span class="bbmcp-badge bbmcp-badge--active"><?php esc_html_e( 'Active', 'buddyboss-mcp-server' ); ?></span></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Endpoint', 'buddyboss-mcp-server' ); ?></td>
				<td><code><?php echo esc_url( $endpoint_url ); ?></code></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Tools Available', 'buddyboss-mcp-server' ); ?></td>
				<td><strong><?php echo esc_html( $tool_count ); ?></strong></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Protocol', 'buddyboss-mcp-server' ); ?></td>
				<td>JSON-RPC 2.0 / MCP 2024-11-05</td>
			</tr>
		</table>
		<p>
			<button type="button" class="button bbmcp-test-btn" id="bbmcp-test-connection">
				<?php esc_html_e( 'Test Connection', 'buddyboss-mcp-server' ); ?>
			</button>
			<span id="bbmcp-test-result" class="bbmcp-test-result"></span>
		</p>
	</div>

	<!-- Setup Instructions -->
	<div class="bbmcp-card">
		<h2><?php esc_html_e( 'Quick Setup', 'buddyboss-mcp-server' ); ?></h2>
		<ol class="bbmcp-setup-steps">
			<li>
				<?php
				printf(
					/* translators: %s: Link to user profile page. */
					wp_kses(
						__( 'Go to <a href="%s">your profile</a> and create an <strong>Application Password</strong>.', 'buddyboss-mcp-server' ),
						array(
							'a'      => array( 'href' => array() ),
							'strong' => array(),
						)
					),
					esc_url( admin_url( 'profile.php#application-passwords-section' ) )
				);
				?>
			</li>
			<li><?php esc_html_e( 'Copy the generated password (shown only once).', 'buddyboss-mcp-server' ); ?></li>
			<li><?php esc_html_e( 'Copy the connection config below and paste it into your IDE settings.', 'buddyboss-mcp-server' ); ?></li>
			<li>
				<?php
				printf(
					/* translators: %s: username. */
					esc_html__( 'Replace YOUR_APP_PASSWORD with the password from step 2. Your username is: %s', 'buddyboss-mcp-server' ),
					'<code>' . esc_html( $current_user->user_login ) . '</code>'
				);
				?>
			</li>
		</ol>
	</div>

	<!-- Connection Configs -->
	<div class="bbmcp-card">
		<h2><?php esc_html_e( 'Connection Configs', 'buddyboss-mcp-server' ); ?></h2>

		<?php
		$site_url = esc_url( $endpoint_url );
		$username = esc_html( $current_user->user_login );
		?>

		<!-- Claude Code (CLI) -->
		<h3><?php esc_html_e( 'Claude Code (CLI)', 'buddyboss-mcp-server' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Add to .mcp.json in your project root or ~/.claude.json for global config.', 'buddyboss-mcp-server' ); ?></p>
		<div class="bbmcp-code-block">
			<button type="button" class="bbmcp-copy-btn" data-target="config-claude-code"><?php esc_html_e( 'Copy', 'buddyboss-mcp-server' ); ?></button>
			<pre id="config-claude-code">{
  "mcpServers": {
    "buddyboss": {
      "type": "http",
      "url": "<?php echo $site_url; // phpcs:ignore ?>",
      "headers": {
        "Authorization": "Basic YOUR_BASE64_CREDENTIALS"
      }
    }
  }
}</pre>
		</div>
		<p class="description">
			<?php esc_html_e( 'Generate Base64 credentials:', 'buddyboss-mcp-server' ); ?>
			<code>echo -n "<?php echo $username; // phpcs:ignore ?>:YOUR_APP_PASSWORD" | base64</code>
		</p>
	</div>

	<!-- Available Tools -->
	<div class="bbmcp-card">
		<h2><?php esc_html_e( 'Registered Tools', 'buddyboss-mcp-server' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tool Name', 'buddyboss-mcp-server' ); ?></th>
					<th><?php esc_html_e( 'Description', 'buddyboss-mcp-server' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $this->tool_registry->get_tool_definitions() as $tool ) : ?>
					<tr>
						<td><code><?php echo esc_html( $tool['name'] ); ?></code></td>
						<td><?php echo esc_html( $tool['description'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
