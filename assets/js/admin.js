/**
 * BuddyBoss MCP Server — Admin scripts.
 *
 * Handles tab switching, copy-to-clipboard, and test connection.
 *
 * @package BuddyBossMCP
 * @since   1.0.0
 */

(function () {
	'use strict';

	/**
	 * Tab switching.
	 */
	document.querySelectorAll('.bbmcp-tab').forEach(function (tab) {
		tab.addEventListener('click', function () {
			// Deactivate all tabs.
			document.querySelectorAll('.bbmcp-tab').forEach(function (t) {
				t.classList.remove('bbmcp-tab--active');
			});
			document.querySelectorAll('.bbmcp-tab-content').forEach(function (c) {
				c.classList.remove('bbmcp-tab-content--active');
			});

			// Activate clicked tab.
			tab.classList.add('bbmcp-tab--active');
			var target = document.getElementById('tab-' + tab.getAttribute('data-tab'));
			if (target) {
				target.classList.add('bbmcp-tab-content--active');
			}
		});
	});

	/**
	 * Copy to clipboard.
	 */
	document.querySelectorAll('.bbmcp-copy-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var targetId = btn.getAttribute('data-target');
			var target = document.getElementById(targetId);

			if (!target) {
				return;
			}

			var text = target.textContent;

			navigator.clipboard.writeText(text).then(function () {
				var original = btn.textContent;
				btn.textContent = bbmcpAdmin.i18n.copied;
				btn.classList.add('bbmcp-copied');

				setTimeout(function () {
					btn.textContent = original;
					btn.classList.remove('bbmcp-copied');
				}, 2000);
			}).catch(function () {
				btn.textContent = bbmcpAdmin.i18n.copyFailed;
				setTimeout(function () {
					btn.textContent = 'Copy';
				}, 2000);
			});
		});
	});

	/**
	 * Test connection.
	 */
	var testBtn = document.getElementById('bbmcp-test-connection');
	var testResult = document.getElementById('bbmcp-test-result');

	if (testBtn) {
		testBtn.addEventListener('click', function () {
			testBtn.disabled = true;
			testResult.textContent = bbmcpAdmin.i18n.testing;
			testResult.className = 'bbmcp-test-result';

			fetch(bbmcpAdmin.endpoint, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': bbmcpAdmin.nonce,
				},
				credentials: 'same-origin',
				body: JSON.stringify({
					jsonrpc: '2.0',
					method: 'initialize',
					params: {
						protocolVersion: '2024-11-05',
						capabilities: {},
						clientInfo: { name: 'admin-test', version: '1.0.0' },
					},
					id: 1,
				}),
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (data) {
					if (data.result && data.result.serverInfo) {
						testResult.textContent =
							bbmcpAdmin.i18n.success +
							' (v' + data.result.serverInfo.version + ')';
						testResult.className = 'bbmcp-test-result bbmcp-success';
					} else if (data.error) {
						testResult.textContent =
							bbmcpAdmin.i18n.failed + ': ' + data.error.message;
						testResult.className = 'bbmcp-test-result bbmcp-error';
					}
				})
				.catch(function (err) {
					testResult.textContent =
						bbmcpAdmin.i18n.failed + ': ' + err.message;
					testResult.className = 'bbmcp-test-result bbmcp-error';
				})
				.finally(function () {
					testBtn.disabled = false;
				});
		});
	}
})();
