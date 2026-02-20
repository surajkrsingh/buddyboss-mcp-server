# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

BuddyBoss MCP Server is a WordPress plugin that exposes BuddyBoss Platform functionality via the Model Context Protocol (MCP). It implements a JSON-RPC 2.0 server over WordPress REST API, enabling AI-powered IDEs (Claude Desktop, Cursor, VS Code, Claude Code) to manage BuddyBoss communities through natural language.

## Architecture

The plugin follows a layered architecture with four key components:

**Request flow:** IDE → `mcp-remote` bridge → `POST /wp-json/buddyboss-mcp/v1/mcp` → `REST_Controller` → `MCP_Server` (JSON-RPC dispatch) → `Tool_Registry` → `Tool_Base` subclass → `Internal_REST_Client` → BuddyBoss REST API → response back up the chain.

- **`Plugin`** (`includes/class-plugin.php`) — Singleton entry point. Checks BuddyBoss dependency, loads files, wires components, registers hooks. Auto-enables Application Passwords on local environments (`.local`, `localhost`).
- **`MCP_Server`** (`includes/class-mcp-server.php`) — JSON-RPC 2.0 dispatcher. Handles `initialize`, `tools/list`, `tools/call`, and `ping` methods. Protocol version: `2025-03-26` (Streamable HTTP).
- **`REST_Controller`** (`includes/class-rest-controller.php`) — WordPress REST endpoint at `buddyboss-mcp/v1/mcp`. POST for JSON-RPC messages, GET for health check. Requires `manage_options` capability + Application Password auth.
- **`Tool_Registry`** (`includes/class-tool-registry.php`) — Discovers and indexes tools from providers. Extensible via `bbmcp_tool_providers` filter.
- **`Internal_REST_Client`** (`includes/class-internal-rest-client.php`) — Calls BuddyBoss REST API internally via `rest_do_request()` (no HTTP overhead). Handles user context switching.

## Adding New Tools

All tool providers live in `includes/tools/` and extend `Tool_Base`:

1. Create `includes/tools/class-{feature}-tools.php` with a class extending `Tools\Tool_Base`
2. Implement `register_tools()` returning an array of `$this->create_tool(name, description, properties, method, required)` calls
3. Each tool maps to a public method on the class: `method_name($args, $user_id) → string`
4. Register the provider in `Plugin::includes()` (require the file) and `Tool_Registry::register_providers()` (instantiate it)

Tool naming convention: `buddyboss_{action}_{resource}` (e.g., `buddyboss_list_groups`, `buddyboss_add_group_member`).

Helper methods from `Tool_Base`: `validate_required()`, `get_int()`, `get_string()`, `get_bool()`, and `$this->rest_client` for API calls.

## Constants

Defined in the main plugin file (`buddyboss-mcp-server.php`):
- `BBMCP_VERSION` — Current version (`1.0.0`)
- `BBMCP_PLUGIN_DIR` / `BBMCP_PLUGIN_URL` / `BBMCP_PLUGIN_FILE` / `BBMCP_PLUGIN_BASENAME`

## Namespace

All classes use the `BuddyBossMCP` namespace. Tools use `BuddyBossMCP\Tools`, admin uses `BuddyBossMCP\Admin`.

## Dependencies

- WordPress 5.6+ (Application Passwords)
- BuddyBoss Platform 2.0+ (must be active — plugin checks for `buddypress()` or `BuddyBoss_Platform` class)
- PHP 7.4+

## Testing Endpoints

```bash
# Health check
curl -u "user:app_password" https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp

# Initialize handshake
curl -X POST -u "user:app_password" -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}' \
  https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp

# List available tools
curl -X POST -u "user:app_password" -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":2}' \
  https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp
```

## IDE Configuration

Clients connect via `mcp-remote` (stdio-to-HTTP bridge) or native HTTP. Endpoint: `/wp-json/buddyboss-mcp/v1/mcp`. Auth: HTTP Basic with WordPress Application Passwords (Base64-encoded `username:app_password`).
