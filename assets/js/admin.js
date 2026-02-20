/**
 * BuddyBoss MCP Server — Admin scripts.
 *
 * Handles tab switching, accordion toggle, copy-to-clipboard, and test connection.
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
			var tabBar = tab.closest('.bbmcp-tabs');

			tabBar.querySelectorAll('.bbmcp-tab').forEach(function (t) {
				t.classList.remove('bbmcp-tab--active');
			});
			tabBar.querySelectorAll('.bbmcp-tab-panel').forEach(function (p) {
				p.classList.remove('bbmcp-tab-panel--active');
			});

			tab.classList.add('bbmcp-tab--active');
			var panel = tabBar.querySelector('#tab-' + tab.getAttribute('data-tab'));
			if (panel) {
				panel.classList.add('bbmcp-tab-panel--active');
			}
		});
	});

	/**
	 * Accordion toggle.
	 */
	document.querySelectorAll('.bbmcp-accordion-header').forEach(function (header) {
		header.addEventListener('click', function () {
			var accordion = header.closest('.bbmcp-accordion-item');
			if (accordion) {
				accordion.classList.toggle('bbmcp-accordion-item--open');
			}
		});
	});

	/**
	 * Copy text to clipboard with fallback for non-HTTPS contexts.
	 */
	function copyToClipboard(text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text);
		}

		var textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.left = '-9999px';
		document.body.appendChild(textarea);
		textarea.select();

		return new Promise(function (resolve, reject) {
			if (document.execCommand('copy')) {
				resolve();
			} else {
				reject();
			}
			document.body.removeChild(textarea);
		});
	}

	/**
	 * Copy to clipboard.
	 */
	document.querySelectorAll('.bbmcp-copy-btn').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.stopPropagation();
			var targetId = btn.getAttribute('data-target');
			var target = document.getElementById(targetId);

			if (!target) {
				return;
			}

			var text = target.textContent;
			var original = btn.textContent;

			copyToClipboard(text).then(function () {
				btn.textContent = bbmcpAdmin.i18n.copied;
				btn.classList.add('bbmcp-copied');

				setTimeout(function () {
					btn.textContent = original;
					btn.classList.remove('bbmcp-copied');
				}, 2000);
			}).catch(function () {
				btn.textContent = bbmcpAdmin.i18n.copyFailed;
				setTimeout(function () {
					btn.textContent = original;
				}, 2000);
			});
		});
	});

	/**
	 * Update header status indicator.
	 */
	var headerStatus = document.getElementById('bbmcp-header-status');

	function updateHeaderStatus(success) {
		if (!headerStatus) {
			return;
		}
		if (success) {
			headerStatus.innerHTML =
				'<span class="bbmcp-header-status-dot bbmcp-header-status-dot--success"></span>' +
				bbmcpAdmin.i18n.connected;
			headerStatus.className = 'bbmcp-header-status bbmcp-header-status--success';
		} else {
			headerStatus.innerHTML =
				'<span class="bbmcp-header-status-dot bbmcp-header-status-dot--error"></span>' +
				bbmcpAdmin.i18n.disconnected;
			headerStatus.className = 'bbmcp-header-status bbmcp-header-status--error';
		}
	}

	/**
	 * MCP initialize request payload.
	 */
	var mcpPayload = JSON.stringify({
		jsonrpc: '2.0',
		method: 'initialize',
		params: {
			protocolVersion: '2024-11-05',
			capabilities: {},
			clientInfo: { name: 'admin-test', version: '1.0.0' },
		},
		id: 1,
	});

	/**
	 * Run connection check — updates header and optionally the card result.
	 *
	 * @param {boolean} silent If true, only update header (no card result).
	 */
	function runConnectionCheck(silent) {
		var testBtn = document.getElementById('bbmcp-test-connection');
		var testResult = document.getElementById('bbmcp-test-result');

		if (!silent && testBtn) {
			testBtn.disabled = true;
		}
		if (!silent && testResult) {
			testResult.textContent = bbmcpAdmin.i18n.testing;
			testResult.className = 'bbmcp-test-result';
		}

		fetch(bbmcpAdmin.endpoint, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': bbmcpAdmin.nonce,
			},
			credentials: 'same-origin',
			body: mcpPayload,
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (data.result && data.result.serverInfo) {
					updateHeaderStatus(true);
					if (!silent && testResult) {
						testResult.innerHTML =
							'<span class="bbmcp-result-badge bbmcp-result-badge--success">' +
							'<span class="dashicons dashicons-yes-alt"></span> ' +
							bbmcpAdmin.i18n.success +
							'</span>';
						testResult.className = 'bbmcp-test-result bbmcp-success';
					}
				} else if (data.error) {
					updateHeaderStatus(false);
					if (!silent && testResult) {
						testResult.innerHTML =
							'<span class="bbmcp-result-badge bbmcp-result-badge--error">' +
							'<span class="dashicons dashicons-dismiss"></span> ' +
							bbmcpAdmin.i18n.failed +
							'</span>' +
							'<span class="bbmcp-result-error-msg">' + data.error.message + '</span>';
						testResult.className = 'bbmcp-test-result bbmcp-error';
					}
				}
			})
			.catch(function (err) {
				updateHeaderStatus(false);
				if (!silent && testResult) {
					testResult.innerHTML =
						'<span class="bbmcp-result-badge bbmcp-result-badge--error">' +
						'<span class="dashicons dashicons-dismiss"></span> ' +
						bbmcpAdmin.i18n.failed +
						'</span>' +
						'<span class="bbmcp-result-error-msg">' + err.message + '</span>';
					testResult.className = 'bbmcp-test-result bbmcp-error';
				}
			})
			.finally(function () {
				if (!silent && testBtn) {
					testBtn.disabled = false;
				}
			});
	}

	/**
	 * Manual test connection button.
	 */
	var testBtn = document.getElementById('bbmcp-test-connection');
	if (testBtn) {
		testBtn.addEventListener('click', function () {
			runConnectionCheck(false);
		});
	}

	/**
	 * Auto-check connection on page load (silent — header only).
	 */
	runConnectionCheck(true);

	/**
	 * Tool search — filter cards by name or description.
	 */
	var toolSearch = document.getElementById('bbmcp-tool-search');
	var toolCards = document.querySelectorAll('.bbmcp-tool-card');
	var toolsEmpty = document.querySelector('.bbmcp-tools-empty');

	function filterTools() {
		var query = toolSearch ? toolSearch.value.toLowerCase().trim() : '';
		var activeFilter = document.querySelector('.bbmcp-tool-filter--active');
		var group = activeFilter ? activeFilter.getAttribute('data-group') : 'all';
		var visibleCount = 0;

		toolCards.forEach(function (card) {
			var matchesSearch =
				!query ||
				card.getAttribute('data-name').indexOf(query) !== -1 ||
				card.getAttribute('data-desc').indexOf(query) !== -1;
			var matchesGroup = group === 'all' || card.getAttribute('data-group') === group;

			if (matchesSearch && matchesGroup) {
				card.style.display = '';
				visibleCount++;
			} else {
				card.style.display = 'none';
			}
		});

		if (toolsEmpty) {
			toolsEmpty.style.display = visibleCount === 0 ? '' : 'none';
		}
	}

	if (toolSearch) {
		toolSearch.addEventListener('input', filterTools);
	}

	/**
	 * Tool category filter pills.
	 */
	document.querySelectorAll('.bbmcp-tool-filter').forEach(function (pill) {
		pill.addEventListener('click', function () {
			document.querySelectorAll('.bbmcp-tool-filter').forEach(function (p) {
				p.classList.remove('bbmcp-tool-filter--active');
			});
			pill.classList.add('bbmcp-tool-filter--active');
			filterTools();
		});
	});

	/**
	 * Tool example toggle.
	 */
	document.querySelectorAll('.bbmcp-tool-example-toggle').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var exampleCode = btn.nextElementSibling;
			var arrow = btn.querySelector('.bbmcp-example-arrow');
			if (exampleCode) {
				var isOpen = exampleCode.style.display !== 'none';
				exampleCode.style.display = isOpen ? 'none' : '';
				if (arrow) {
					arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
				}
			}
		});
	});
})();
