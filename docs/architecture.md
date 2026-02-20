# BuddyBoss MCP Server — Architecture Document

## Overview

A self-contained WordPress plugin that exposes an MCP (Model Context Protocol) server endpoint, allowing AI-powered IDEs (Claude Desktop, Cursor, VS Code, Claude Code) to manage a BuddyBoss platform via natural language.

**Key Principle:** Upload ZIP, activate, connect — no Node.js, no Composer, no CLI required.

---

## Architecture Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Language | Pure PHP 7.4+ | No Composer/Node.js — upload zip & activate |
| MCP Transport | Streamable HTTP (POST only, JSON responses) | Works with Claude.ai Connectors + `mcp-remote` bridge for Desktop |
| Protocol | Implement JSON-RPC 2.0 directly | Simple for tools-only server (~200 lines), avoids experimental SDK deps |
| Auth | WordPress Application Passwords | Built-in since WP 5.6, per-app revocable, HTTPS-enforced |
| Tool execution | `rest_do_request()` internal dispatch | Reuses BuddyBoss validation/permissions, zero HTTP overhead |
| Permission | `manage_options` capability required | Admin-only MCP access |

---

## Why Not Node.js?

The reference [BuddyPress MCP](https://github.com/vapvarun/buddypress-mcp) is a standalone Node.js app using stdio transport. We chose a different approach:

| Aspect | Node.js (Reference) | PHP Plugin (Our Approach) |
|--------|---------------------|--------------------------|
| Installation | Clone repo, npm install, configure .env | Upload ZIP, activate |
| Runtime | Separate Node.js process | Runs inside WordPress |
| API access | HTTP calls to WP REST API | Internal `rest_do_request()` — zero overhead |
| Auth | Env vars with credentials | WordPress Application Passwords |
| Transport | stdio (local only) | HTTP (remote-capable) |
| Dependencies | @modelcontextprotocol/sdk, node-fetch | None |
| Target user | Developers | Site owners & developers |

---

## Plugin Location

```
wp-content/plugins/buddyboss-mcp-server/
```

Standalone plugin, separate from any other projects.

---

## File Structure

```
buddyboss-mcp-server/
├── buddyboss-mcp-server.php            # Main plugin file (bootstrap + constants)
├── uninstall.php                       # Cleanup on uninstall
├── includes/
│   ├── class-plugin.php                # Singleton, dependency checks, hook setup
│   ├── class-mcp-server.php            # JSON-RPC 2.0 handler
│   ├── class-rest-controller.php       # WP REST endpoint + auth
│   ├── class-tool-registry.php         # Tool registration, discovery, phase toggling
│   ├── class-internal-rest-client.php  # rest_do_request() wrapper for BuddyBoss API
│   ├── admin/
│   │   └── class-admin-page.php        # Settings page + connection snippets
│   └── tools/
│       ├── class-tool-base.php         # Abstract base class
│       ├── class-members-tools.php     # Members CRUD (5 tools)
│       ├── class-groups-tools.php      # Groups CRUD + membership (8 tools)
│       ├── class-activity-tools.php    # Activity CRUD + comments (6 tools)
│       ├── class-messages-tools.php    # Messages/threads (5 tools)
│       ├── class-friends-tools.php     # Friendships (4 tools)
│       ├── class-notifications-tools.php # Notifications (4 tools)
│       ├── class-xprofile-tools.php    # Profile fields & data (5 tools)
│       ├── class-media-tools.php       # Photos & albums (5 tools)
│       ├── class-video-tools.php       # Videos (4 tools)
│       ├── class-document-tools.php    # Documents & folders (5 tools)
│       ├── class-forums-tools.php      # Forums/topics/replies (6 tools)
│       ├── class-moderation-tools.php  # Moderation/blocking (4 tools)
│       └── class-learndash-tools.php   # LearnDash courses (4 tools)
├── templates/
│   └── admin/
│       └── settings-page.php           # Admin settings page template
├── assets/
│   ├── css/admin.css                   # Admin page styles
│   └── js/admin.js                     # Copy-to-clipboard, test connection
└── docs/                              # This documentation
```

---

## Core Class Architecture

### Request Flow

```
IDE (Claude Desktop / Cursor / Claude Code)
  │
  ▼
POST /wp-json/buddyboss-mcp/v1/mcp
Authorization: Basic base64(username:app_password)
Content-Type: application/json
Body: {"jsonrpc":"2.0","method":"tools/call","params":{...},"id":1}
  │
  ▼
┌─────────────────────────────────┐
│  REST_Controller                │  ← WordPress REST API endpoint
│  - Validates Application Password│
│  - Checks manage_options cap    │
│  - Passes body to MCP_Server   │
└──────────────┬──────────────────┘
               │
               ▼
┌─────────────────────────────────┐
│  MCP_Server                     │  ← JSON-RPC 2.0 protocol handler
│  - Parses JSON-RPC request      │
│  - Routes: initialize,          │
│    tools/list, tools/call       │
│  - Returns JSON-RPC response    │
└──────────────┬──────────────────┘
               │ (tools/call)
               ▼
┌─────────────────────────────────┐
│  Tool_Registry                  │  ← Finds matching tool
│  - Looks up tool by name        │
│  - Returns tool provider + method│
└──────────────┬──────────────────┘
               │
               ▼
┌─────────────────────────────────┐
│  Tool Provider (e.g. Members)   │  ← Executes the tool
│  - Validates/sanitizes params   │
│  - Calls Internal_REST_Client   │
│  - Formats response             │
└──────────────┬──────────────────┘
               │
               ▼
┌─────────────────────────────────┐
│  Internal_REST_Client           │  ← Dispatches to BuddyBoss
│  - rest_do_request()            │
│  - No HTTP overhead             │
│  - Inherits user permissions    │
└─────────────────────────────────┘
               │
               ▼
BuddyBoss REST API endpoint
(e.g. /buddyboss/v1/members)
```

### Class Responsibilities

#### 1. `Plugin` (class-plugin.php)
- Singleton pattern
- Checks BuddyBoss Platform is active (dependency)
- Includes all class files
- Initializes components: MCP_Server, REST_Controller, Tool_Registry, Admin_Page
- Registers WordPress hooks

#### 2. `MCP_Server` (class-mcp-server.php)
- Receives raw JSON request body
- Parses and validates JSON-RPC 2.0 format
- Routes to method handlers:
  - `initialize` → server info + capabilities
  - `notifications/initialized` → acknowledgment (202)
  - `tools/list` → all tool definitions from registry
  - `tools/call` → executes tool, returns result
- Formats JSON-RPC responses and errors
- Standard error codes: -32700 (parse), -32600 (invalid request), -32601 (method not found), -32602 (invalid params), -32000 (tool error)

#### 3. `REST_Controller` (class-rest-controller.php)
- Extends `WP_REST_Controller`
- Registers route: `POST /buddyboss-mcp/v1/mcp`
- Permission callback: `is_user_logged_in()` + `manage_options`
- WordPress Application Passwords handle Basic Auth automatically
- Passes request body + user ID to MCP_Server
- Returns response via `rest_ensure_response()`

#### 4. `Tool_Registry` (class-tool-registry.php)
- Loads all tool provider classes
- Collects tool definitions from `register_tools()` methods
- Phase-based toggling via `bbmcp_enabled_phases` wp_option
- `get_all_tools()` → array for tools/list response
- `get_tool($name)` → provider + method for tools/call execution

#### 5. `Internal_REST_Client` (class-internal-rest-client.php)
- Wraps `rest_do_request()` for clean API:
  ```php
  $client->get( '/buddyboss/v1/members', array( 'per_page' => 10 ) );
  $client->post( '/buddyboss/v1/groups', array( 'name' => 'Test' ) );
  ```
- Handles user context switching
- Extracts errors from WP_REST_Response
- Formats data as JSON string for MCP content

#### 6. `Tool_Base` (class-tool-base.php)
- Abstract base for all tool providers
- `register_tools()` → returns tool definitions array
- `create_tool()` → helper to build tool definition with JSON Schema
- `validate_required()` → checks required parameters
- Initializes `Internal_REST_Client` instance

#### 7. `Admin_Page` (class-admin-page.php)
- WordPress admin menu page under top-level "MCP Server"
- Shows server status, endpoint URL, tool count
- Auto-generates IDE config snippets with site URL pre-filled
- Phase toggle checkboxes (Core / BuddyBoss / Advanced)
- Test connection AJAX button

---

## Tool Provider Pattern

Each tool provider class follows this pattern:

```php
class Members_Tools extends Tool_Base {

    public function get_phase() {
        return 'core'; // or 'buddyboss' or 'advanced'
    }

    public function register_tools() {
        return array(
            $this->create_tool(
                'buddyboss_list_members',           // Tool name
                'List members with filtering.',      // Description
                array(                               // Input schema
                    'search' => array(
                        'type'        => 'string',
                        'description' => 'Search by name',
                    ),
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => 'Results per page (default: 20)',
                    ),
                ),
                'list_members'                       // Method to call
            ),
            // ... more tools
        );
    }

    public function list_members( $args, $user_id ) {
        $params = array(
            'per_page' => isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 20,
        );
        if ( isset( $args['search'] ) ) {
            $params['search'] = sanitize_text_field( $args['search'] );
        }

        $response = $this->rest_client->get( '/buddyboss/v1/members', $params, $user_id );
        return $this->rest_client->format_response( $response );
    }
}
```

**Key conventions:**
- Tool names: `buddyboss_{verb}_{resource}` (e.g., `buddyboss_list_members`, `buddyboss_create_group`)
- All inputs sanitized with WordPress functions
- All API calls via `$this->rest_client`
- Responses formatted as JSON strings for MCP content

---

## Authentication Flow

```
1. Admin creates Application Password:
   WP Admin → Users → Profile → Application Passwords
   Name: "Claude MCP" → Generate → Copy password

2. IDE config uses Basic Auth:
   Authorization: Basic base64("admin:xxxx xxxx xxxx xxxx")

3. WordPress validates automatically:
   WP core intercepts Authorization header
   → Validates against wp_application_passwords user meta
   → Sets current user if valid
   → Returns 401 if invalid

4. REST_Controller checks capability:
   → is_user_logged_in() ✓
   → current_user_can('manage_options') ✓
   → Proceeds to MCP_Server
```

---

## Phase System

Tools are organized into 3 phases that site owners can toggle:

| Phase | Tools | Default |
|-------|-------|---------|
| **Core** | Members, Groups, Activity, Messages, Friends, Notifications, XProfile (37 tools) | Always enabled |
| **BuddyBoss** | Media, Video, Documents, Moderation (18 tools) | Enabled |
| **Advanced** | Forums, LearnDash (10 tools) | Disabled |

Stored in: `bbmcp_enabled_phases` wp_option (array of phase strings).

---

## Security

| Layer | Protection |
|-------|-----------|
| Transport | HTTPS required (Application Passwords enforce this) |
| Authentication | WordPress Application Passwords (Basic Auth) |
| Authorization | `manage_options` capability check |
| Input | WordPress sanitization functions on all tool params |
| Permissions | `rest_do_request()` enforces BuddyBoss endpoint permissions |
| Rate limiting | Transient-based, 200 requests/hour per user (configurable) |
| Errors | No sensitive data exposed in error messages |
| Uninstall | Clean removal of all options, transients, sessions |

---

## Implementation Order

1. **Plugin Scaffold + MCP Protocol Core** — Bootstrap, MCP_Server, REST_Controller, Internal_REST_Client, Tool_Registry, Tool_Base
2. **Core Tool Providers** — Members, Groups, Activity (19 tools) — test end-to-end
3. **Remaining Core Tools** — Messages, Friends, Notifications, XProfile (18 tools)
4. **Settings Page** — Admin UI, connection snippets, phase toggles, test button
5. **BuddyBoss-Specific Tools** — Media, Video, Document, Moderation (18 tools)
6. **Advanced Tools** — Forums, LearnDash (10 tools)
7. **Polish** — Rate limiting, uninstall cleanup, readme.txt
