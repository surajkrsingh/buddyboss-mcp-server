<?php
/**
 * Admin settings page template.
 *
 * Renders the MCP Server dashboard with a two-column layout:
 * main content on the left, info widgets on the right.
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

$site_url = esc_url( $endpoint_url );
$username = esc_html( $current_user->user_login );
?>
<div class="wrap bbmcp-settings">

	<div class="bbmcp-columns">

		<div class="bbmcp-main">

			<!-- Server Status -->
			<div class="bbmcp-card">
				<div class="bbmcp-card-header">
					<h2><?php esc_html_e( 'Server Status', 'buddyboss-mcp-server' ); ?></h2>
					<button type="button" class="bbmcp-test-btn" id="bbmcp-test-connection">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Test Connection', 'buddyboss-mcp-server' ); ?>
					</button>
				</div>
				<div class="bbmcp-card-body">
					<div class="bbmcp-endpoint-row">
						<div class="bbmcp-endpoint-label">
							<span class="dashicons dashicons-admin-links"></span>
							<?php esc_html_e( 'Endpoint', 'buddyboss-mcp-server' ); ?>
						</div>
						<div class="bbmcp-endpoint-url">
							<code id="bbmcp-endpoint-url"><?php echo esc_url( $endpoint_url ); ?></code>
							<button type="button" class="bbmcp-copy-inline bbmcp-copy-btn" data-target="bbmcp-endpoint-url">
								<span class="dashicons dashicons-admin-page"></span>
							</button>
						</div>
					</div>
					<div class="bbmcp-status-grid">
						<div class="bbmcp-status-item">
							<div class="bbmcp-status-icon">
								<span class="dashicons dashicons-admin-tools"></span>
							</div>
							<div>
								<span class="bbmcp-status-label"><?php esc_html_e( 'Active Tools', 'buddyboss-mcp-server' ); ?></span>
								<span class="bbmcp-status-value"><?php echo esc_html( $tool_count ); ?> <span class="bbmcp-value-suffix"><?php esc_html_e( 'Registered', 'buddyboss-mcp-server' ); ?></span></span>
							</div>
						</div>
						<div class="bbmcp-status-item">
							<div class="bbmcp-status-icon">
								<span class="dashicons dashicons-shield"></span>
							</div>
							<div>
								<span class="bbmcp-status-label"><?php esc_html_e( 'Protocol', 'buddyboss-mcp-server' ); ?></span>
								<span class="bbmcp-status-value">MCP 2025-11-25</span>
							</div>
						</div>
						<div class="bbmcp-status-item">
							<div class="bbmcp-status-icon">
								<span class="dashicons dashicons-randomize"></span>
							</div>
							<div>
								<span class="bbmcp-status-label"><?php esc_html_e( 'Transport', 'buddyboss-mcp-server' ); ?></span>
								<span class="bbmcp-status-value"><?php esc_html_e( 'Streamable HTTP', 'buddyboss-mcp-server' ); ?></span>
							</div>
						</div>
					</div>
					<div id="bbmcp-test-result" class="bbmcp-test-result"></div>
				</div>
			</div>

			<!-- Quick Setup Guide (Stepper) -->
			<div class="bbmcp-card">
				<div class="bbmcp-card-header">
					<h2><?php esc_html_e( 'Quick Setup Guide', 'buddyboss-mcp-server' ); ?></h2>
				</div>
				<div class="bbmcp-stepper">
					<div class="bbmcp-step">
						<div class="bbmcp-step-indicator">
							<div class="bbmcp-step-number">1</div>
						</div>
						<div class="bbmcp-step-content">
							<div class="bbmcp-step-header">
								<h3 class="bbmcp-step-title"><?php esc_html_e( 'Create Application Password', 'buddyboss-mcp-server' ); ?></h3>
								<a href="<?php echo esc_url( admin_url( 'profile.php#application-passwords-section' ) ); ?>" class="bbmcp-step-action"><?php esc_html_e( 'Go to Profile', 'buddyboss-mcp-server' ); ?></a>
							</div>
							<p class="bbmcp-step-desc">
								<?php esc_html_e( 'Navigate to your WordPress user profile settings and generate a new Application Password for MCP authentication.', 'buddyboss-mcp-server' ); ?>
							</p>
						</div>
					</div>

					<div class="bbmcp-step">
						<div class="bbmcp-step-indicator">
							<div class="bbmcp-step-number">2</div>
						</div>
						<div class="bbmcp-step-content">
							<h3 class="bbmcp-step-title"><?php esc_html_e( 'Securely Copy Password', 'buddyboss-mcp-server' ); ?></h3>
							<div class="bbmcp-step-warning">
								<span class="dashicons dashicons-warning"></span>
								<p>
									<?php
									printf(
										wp_kses(
											/* translators: The password display warning. */
											__( 'The password is <strong>shown only once</strong>. Copy it now and store it in a secure password manager.', 'buddyboss-mcp-server' ),
											array( 'strong' => array() )
										)
									);
									?>
								</p>
							</div>
						</div>
					</div>

					<div class="bbmcp-step">
						<div class="bbmcp-step-indicator">
							<div class="bbmcp-step-number">3</div>
						</div>
						<div class="bbmcp-step-content">
							<div class="bbmcp-step-header">
								<h3 class="bbmcp-step-title"><?php esc_html_e( 'Configure IDE', 'buddyboss-mcp-server' ); ?></h3>
								<a href="#bbmcp-connection-config" class="bbmcp-step-action"><?php esc_html_e( 'View Config Template', 'buddyboss-mcp-server' ); ?></a>
							</div>
							<p class="bbmcp-step-desc">
								<?php esc_html_e( 'Open your IDE settings and paste the server configuration details from the Connection Config section below.', 'buddyboss-mcp-server' ); ?>
							</p>
						</div>
					</div>

					<div class="bbmcp-step">
						<div class="bbmcp-step-indicator">
							<div class="bbmcp-step-number">4</div>
						</div>
						<div class="bbmcp-step-content">
							<h3 class="bbmcp-step-title"><?php esc_html_e( 'Finalize Credentials', 'buddyboss-mcp-server' ); ?></h3>
							<div class="bbmcp-step-username">
								<span class="bbmcp-username-label"><?php esc_html_e( 'Username:', 'buddyboss-mcp-server' ); ?></span>
								<code><?php echo esc_html( $current_user->user_login ); ?></code>
							</div>
							<p class="bbmcp-step-desc" style="margin-top: 8px;">
								<?php esc_html_e( 'Use your primary admin username with the generated application password.', 'buddyboss-mcp-server' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Connection Config -->
			<div class="bbmcp-card" id="bbmcp-connection-config">
				<div class="bbmcp-tabs">
					<div class="bbmcp-tab-bar">
						<button type="button" class="bbmcp-tab bbmcp-tab--active" data-tab="claude-code"><?php esc_html_e( 'Claude Code', 'buddyboss-mcp-server' ); ?></button>
						<button type="button" class="bbmcp-tab" data-tab="claude-desktop"><?php esc_html_e( 'Claude Desktop', 'buddyboss-mcp-server' ); ?></button>
						<button type="button" class="bbmcp-tab" data-tab="curl"><?php esc_html_e( 'cURL Debug', 'buddyboss-mcp-server' ); ?></button>
					</div>

					<!-- Claude Code — ~/.claude.json or project .mcp.json -->
					<div class="bbmcp-tab-panel bbmcp-tab-panel--active" id="tab-claude-code">
						<div class="bbmcp-tab-file-hint">
							<span class="dashicons dashicons-portfolio"></span>
							<code>~/.claude.json</code>
						</div>
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
						<div class="bbmcp-base64-helper">
							<?php esc_html_e( 'Generate Base64 credentials:', 'buddyboss-mcp-server' ); ?>
							<code>echo -n "<?php echo $username; // phpcs:ignore ?>:YOUR_APP_PASSWORD" | base64</code>
						</div>
					</div>

					<!-- Claude Desktop — npx mcp-remote (stdio transport) -->
					<div class="bbmcp-tab-panel" id="tab-claude-desktop">
						<div class="bbmcp-tab-file-hint">
							<span class="dashicons dashicons-portfolio"></span>
							<code>claude_desktop_config.json</code>
						</div>
						<div class="bbmcp-code-block">
							<button type="button" class="bbmcp-copy-btn" data-target="config-claude-desktop"><?php esc_html_e( 'Copy', 'buddyboss-mcp-server' ); ?></button>
							<pre id="config-claude-desktop">{
  "mcpServers": {
    "buddyboss": {
      "command": "npx",
      "args": [
        "mcp-remote",
        "<?php echo $site_url; // phpcs:ignore ?>",
        "--header",
        "Authorization: Basic YOUR_BASE64_CREDENTIALS"
      ]
    }
  }
}</pre>
						</div>
						<div class="bbmcp-base64-helper">
							<?php esc_html_e( 'Generate Base64 credentials:', 'buddyboss-mcp-server' ); ?>
							<code>echo -n "<?php echo $username; // phpcs:ignore ?>:YOUR_APP_PASSWORD" | base64</code>
						</div>
					</div>

					<!-- cURL Debug -->
					<div class="bbmcp-tab-panel" id="tab-curl">
						<div class="bbmcp-code-block">
							<button type="button" class="bbmcp-copy-btn" data-target="config-curl"><?php esc_html_e( 'Copy', 'buddyboss-mcp-server' ); ?></button>
							<pre id="config-curl">curl -X POST <?php echo $site_url; // phpcs:ignore ?> \
  -H "Content-Type: application/json" \
  -H "Authorization: Basic YOUR_BASE64_CREDENTIALS" \
  -d '{
    "jsonrpc": "2.0",
    "method": "initialize",
    "params": {
      "protocolVersion": "2025-11-25",
      "capabilities": {},
      "clientInfo": {"name": "curl-test", "version": "1.0.0"}
    },
    "id": 1
  }'</pre>
						</div>
					</div>
				</div>

				<div class="bbmcp-compat-note">
					<span class="dashicons dashicons-info-outline"></span>
					<p>
						<?php
						printf(
							wp_kses(
								/* translators: IDE compatibility note. */
								__( 'This configuration works with <strong>any IDE that supports MCP</strong> — including Cursor, VS Code, Antigravity, Windsurf, and others. Use the Claude Code config format with <code>mcpServers</code> key in your IDE\'s MCP settings file.', 'buddyboss-mcp-server' ),
								array(
									'strong' => array(),
									'code'   => array(),
								)
							)
						);
						?>
					</p>
				</div>

				<div class="bbmcp-security-tip">
					<span class="dashicons dashicons-info"></span>
					<p>
						<?php
						printf(
							wp_kses(
								/* translators: Security tip about credentials in config files. */
								__( '<strong>Security Tip:</strong> Never store passwords directly in config files — they are visible to your AI IDE. Use environment variables (e.g. <code>$ENV_APP_PASSWORD</code>) or a secrets manager, and reference them in your configuration instead.', 'buddyboss-mcp-server' ),
								array(
									'strong' => array(),
									'code'   => array(),
								)
							)
						);
						?>
					</p>
				</div>
			</div>

			<!-- Registered Tools -->
			<div class="bbmcp-card" id="bbmcp-tools-section">
				<div class="bbmcp-card-header">
					<h2><?php esc_html_e( 'Registered Tools', 'buddyboss-mcp-server' ); ?></h2>
					<span class="bbmcp-tools-count-badge"><?php echo esc_html( $tool_count ); ?> <?php esc_html_e( 'tools', 'buddyboss-mcp-server' ); ?></span>
				</div>
				<div class="bbmcp-tools-toolbar">
					<div class="bbmcp-tools-search-wrap">
						<input type="text" id="bbmcp-tool-search" class="bbmcp-tools-search" placeholder="<?php esc_attr_e( 'Search tools by name or description...', 'buddyboss-mcp-server' ); ?>" />
						<svg class="bbmcp-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.45 4.39l3.58 3.58a.75.75 0 1 1-1.06 1.06l-3.58-3.58A7 7 0 0 1 2 9Z" clip-rule="evenodd"/></svg>
					</div>
				</div>
				<?php
				$group_icons = array(
					'Members'       => 'dashicons-admin-users',
					'Groups'        => 'dashicons-groups',
					'Activity'      => 'dashicons-format-status',
					'Messages'      => 'dashicons-email-alt',
					'Friends'       => 'dashicons-buddicons-friends',
					'Notifications' => 'dashicons-bell',
					'XProfile'      => 'dashicons-id-alt',
				);
				$group_labels = array_keys( $this->tool_registry->get_grouped_definitions() );
				?>
				<div class="bbmcp-tool-filters">
					<button type="button" class="bbmcp-tool-filter bbmcp-tool-filter--active" data-group="all"><?php esc_html_e( 'All', 'buddyboss-mcp-server' ); ?></button>
					<?php foreach ( $group_labels as $label ) : ?>
						<button type="button" class="bbmcp-tool-filter" data-group="<?php echo esc_attr( sanitize_title( $label ) ); ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
				<div class="bbmcp-tools-list">
					<?php foreach ( $this->tool_registry->get_grouped_definitions() as $group_label => $tools ) : ?>
						<?php $group_slug = sanitize_title( $group_label ); ?>
						<?php foreach ( $tools as $tool ) : ?>
							<?php
							$schema     = isset( $tool['inputSchema'] ) ? $tool['inputSchema'] : array();
							$properties = array();
							if ( isset( $schema['properties'] ) ) {
								$properties = (array) $schema['properties'];
							}
							$required = isset( $schema['required'] ) ? $schema['required'] : array();

							// Build example JSON.
							$example_params = new \stdClass();
							foreach ( $properties as $param_name => $param_def ) {
								$type = isset( $param_def['type'] ) ? $param_def['type'] : 'string';
								if ( 'integer' === $type ) {
									$example_params->$param_name = 1;
								} elseif ( 'boolean' === $type ) {
									$example_params->$param_name = true;
								} else {
									$example_params->$param_name = 'value';
								}
							}
							$example_json = wp_json_encode(
								array(
									'tool'   => $tool['name'],
									'params' => $example_params,
								),
								JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
							);
							?>
							<div class="bbmcp-tool-card" data-group="<?php echo esc_attr( $group_slug ); ?>" data-name="<?php echo esc_attr( strtolower( $tool['name'] ) ); ?>" data-desc="<?php echo esc_attr( strtolower( $tool['description'] ) ); ?>">
								<div class="bbmcp-tool-card-header">
									<code class="bbmcp-tool-name-badge"><?php echo esc_html( $tool['name'] ); ?></code>
									<span class="bbmcp-tool-group-badge"><?php echo esc_html( $group_label ); ?></span>
								</div>
								<p class="bbmcp-tool-card-desc"><?php echo esc_html( $tool['description'] ); ?></p>
								<?php if ( ! empty( $properties ) ) : ?>
									<div class="bbmcp-tool-params">
										<div class="bbmcp-tool-params-label"><?php esc_html_e( 'Parameters', 'buddyboss-mcp-server' ); ?></div>
										<div class="bbmcp-tool-params-list">
											<?php foreach ( $properties as $param_name => $param_def ) : ?>
												<?php
												$param_type = isset( $param_def['type'] ) ? $param_def['type'] : 'string';
												$param_desc = isset( $param_def['description'] ) ? $param_def['description'] : '';
												$is_required = in_array( $param_name, $required, true );
												?>
												<div class="bbmcp-param-row">
													<div class="bbmcp-param-meta">
														<code class="bbmcp-param-name"><?php echo esc_html( $param_name ); ?></code>
														<span class="bbmcp-param-type bbmcp-param-type--<?php echo esc_attr( $param_type ); ?>"><?php echo esc_html( $param_type ); ?></span>
														<?php if ( $is_required ) : ?>
															<span class="bbmcp-param-required"><?php esc_html_e( 'required', 'buddyboss-mcp-server' ); ?></span>
														<?php endif; ?>
													</div>
													<?php if ( $param_desc ) : ?>
														<div class="bbmcp-param-desc"><?php echo esc_html( $param_desc ); ?></div>
													<?php endif; ?>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								<?php endif; ?>
								<div class="bbmcp-tool-example">
									<button type="button" class="bbmcp-tool-example-toggle">
										<span class="dashicons dashicons-editor-code"></span>
										<?php esc_html_e( 'Example', 'buddyboss-mcp-server' ); ?>
										<span class="dashicons dashicons-arrow-down-alt2 bbmcp-example-arrow"></span>
									</button>
									<div class="bbmcp-tool-example-code" style="display:none;">
										<pre><?php echo esc_html( $example_json ); ?></pre>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endforeach; ?>
					<div class="bbmcp-tools-empty" style="display:none;">
						<span class="dashicons dashicons-search"></span>
						<p><?php esc_html_e( 'No tools match your search.', 'buddyboss-mcp-server' ); ?></p>
					</div>
				</div>
			</div>

		</div>

		<div class="bbmcp-sidebar">

			<!-- Resource Links -->
			<div class="bbmcp-widget">
				<div class="bbmcp-widget-header">
					<h3><?php esc_html_e( 'Resource Links', 'buddyboss-mcp-server' ); ?></h3>
				</div>
				<ul class="bbmcp-resource-list">
					<li>
						<a href="<?php echo esc_url( admin_url( 'profile.php#application-passwords-section' ) ); ?>">
							<span class="dashicons dashicons-admin-network"></span>
							<?php esc_html_e( 'Manage Passwords', 'buddyboss-mcp-server' ); ?>
						</a>
					</li>
					<li>
						<a href="https://github.com/surajkrsingh/buddyboss-mcp-server" target="_blank" rel="noopener noreferrer">
							<span class="dashicons dashicons-editor-code"></span>
							<?php esc_html_e( 'GitHub Repo', 'buddyboss-mcp-server' ); ?>
						</a>
					</li>
					<li>
						<a href="https://github.com/surajkrsingh/buddyboss-mcp-server#readme" target="_blank" rel="noopener noreferrer">
							<span class="dashicons dashicons-media-document"></span>
							<?php esc_html_e( 'View Docs', 'buddyboss-mcp-server' ); ?>
						</a>
					</li>
					<li>
						<a href="https://github.com/surajkrsingh/buddyboss-mcp-server/issues" target="_blank" rel="noopener noreferrer">
							<span class="dashicons dashicons-editor-help"></span>
							<?php esc_html_e( 'Support', 'buddyboss-mcp-server' ); ?>
						</a>
					</li>
					<li>
						<a href="https://github.com/surajkrsingh/buddyboss-mcp-server/issues" target="_blank" rel="noopener noreferrer" class="bbmcp-link-danger">
							<span class="dashicons dashicons-sos"></span>
							<?php esc_html_e( 'Report Issue', 'buddyboss-mcp-server' ); ?>
						</a>
					</li>
				</ul>
			</div>

			<!-- Environment / System Info -->
			<div class="bbmcp-widget">
				<div class="bbmcp-widget-header">
					<h3><?php esc_html_e( 'Environment', 'buddyboss-mcp-server' ); ?></h3>
				</div>
				<div style="padding: 4px 16px;">
					<table class="bbmcp-sysinfo-table">
						<tr>
							<td><?php esc_html_e( 'WordPress', 'buddyboss-mcp-server' ); ?></td>
							<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'PHP Version', 'buddyboss-mcp-server' ); ?></td>
							<td><?php echo esc_html( PHP_VERSION ); ?></td>
						</tr>
						<?php if ( defined( 'BP_PLATFORM_VERSION' ) ) : ?>
							<tr>
								<td><?php esc_html_e( 'BuddyBoss', 'buddyboss-mcp-server' ); ?></td>
								<td><?php echo esc_html( BP_PLATFORM_VERSION ); ?></td>
							</tr>
						<?php endif; ?>
					</table>
				</div>
			</div>

			<!-- Integration Help -->
			<div class="bbmcp-help-widget">
				<div class="bbmcp-help-widget-icon">
					<span class="dashicons dashicons-lightbulb"></span>
				</div>
				<h3 class="bbmcp-help-widget-title"><?php esc_html_e( 'Integration Help', 'buddyboss-mcp-server' ); ?></h3>
				<p class="bbmcp-help-widget-desc">
					<?php esc_html_e( 'Connect your AI IDE to manage members, groups, activity, messages, and more through natural language.', 'buddyboss-mcp-server' ); ?>
				</p>
				<div class="bbmcp-help-widget-features">
					<span class="bbmcp-help-feature"><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Claude Code', 'buddyboss-mcp-server' ); ?></span>
					<span class="bbmcp-help-feature"><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Cursor', 'buddyboss-mcp-server' ); ?></span>
					<span class="bbmcp-help-feature"><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'VS Code', 'buddyboss-mcp-server' ); ?></span>
					<span class="bbmcp-help-feature"><span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Antigravity', 'buddyboss-mcp-server' ); ?></span>
				</div>
				<a href="https://github.com/surajkrsingh/buddyboss-mcp-server#readme" target="_blank" rel="noopener noreferrer" class="bbmcp-help-widget-btn">
					<?php esc_html_e( 'View Setup Guide', 'buddyboss-mcp-server' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
			</div>

		</div>

	</div>
</div>
