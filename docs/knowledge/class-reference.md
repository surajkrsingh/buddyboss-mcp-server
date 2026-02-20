# Class Reference

Complete reference guide for all classes in the BuddyBoss MCP Server plugin.

## Overview

The plugin consists of **14 classes** organized into 4 layers:

1. **Core Layer** (5 classes) — Server, routing, and REST API infrastructure
2. **Admin Layer** (1 class) — WordPress admin interface
3. **Tool Base** (1 class) — Abstract foundation for tool providers
4. **Tool Providers** (7 classes) — BuddyBoss feature implementations

---

## Core Classes

### 1. `BuddyBossMCP\Plugin`

**File:** `includes/class-plugin.php`

**Purpose:** Singleton entry point that bootstraps the MCP server, REST endpoint, tool registry, and admin page.

**Key Responsibilities:**
- Checks for BuddyBoss Platform dependency
- Loads all required class files
- Initializes core components (server, registry, REST controller, admin)
- Registers WordPress hooks
- Auto-enables Application Passwords on local environments

**Usage:**
```php
// Get singleton instance
$plugin = \BuddyBossMCP\Plugin::instance();

// Access tool registry
$registry = $plugin->get_tool_registry();
```

**Dependencies:**
- Requires `buddypress()` function or `BuddyBoss_Platform` class
- Instantiates all other plugin classes

---

### 2. `BuddyBossMCP\MCP_Server`

**File:** `includes/class-mcp-server.php`

**Purpose:** JSON-RPC 2.0 dispatcher that handles initialize, tools/list, tools/call, and ping methods.

**Key Responsibilities:**
- Parses and validates JSON-RPC 2.0 requests
- Routes methods to appropriate handlers:
  - `initialize` — Returns server info and protocol version (2025-11-25)
  - `tools/list` — Returns all registered tool definitions
  - `tools/call` — Executes tool methods via registry
  - `ping` — Health check endpoint
- Handles validation errors vs. execution errors differently
- Logs errors server-side while sanitizing client responses

**Key Methods:**
- `handle_request($request_body, $user_id)` — Main entry point
- `dispatch_method($method, $params, $id, $user_id)` — Routes method calls
- `success_response($result, $id)` — Builds JSON-RPC success response
- `error_response($code, $message, $id)` — Builds JSON-RPC error response

**Protocol Compliance:**
- JSON-RPC 2.0 spec compliant
- MCP protocol version: `2025-11-25` (Streamable HTTP)
- Notifications (no `id`) return `null`

---

### 3. `BuddyBossMCP\REST_Controller`

**File:** `includes/class-rest-controller.php`

**Purpose:** WordPress REST API controller exposing `/wp-json/buddyboss-mcp/v1/mcp` endpoint with authentication and authorization.

**Key Responsibilities:**
- Registers REST routes (`POST` and `GET` on `/buddyboss-mcp/v1/mcp`)
- Handles authentication via WordPress Application Passwords
- Enforces `manage_options` capability requirement
- Routes POST requests to MCP server
- Provides health check via GET requests

**Key Methods:**
- `register_routes()` — Registers REST API endpoints
- `check_permissions($request)` — Validates authentication and capabilities
- `handle_post_request($request)` — Processes JSON-RPC messages
- `handle_get_request($request)` — Returns server status

**Security Features:**
- Application Password authentication required
- Administrator-level permissions (`manage_options`)
- Input sanitization at protocol layer

---

### 4. `BuddyBossMCP\Tool_Registry`

**File:** `includes/class-tool-registry.php`

**Purpose:** Discovers, registers, and indexes MCP tools from all tool providers for routing calls.

**Key Responsibilities:**
- Instantiates all tool provider classes
- Collects tool definitions from each provider
- Maintains flat tool map (name → provider + method)
- Groups tools by provider for admin display
- Routes tool calls to correct provider method
- Extensible via `bbmcp_tool_providers` filter

**Key Methods:**
- `register_providers()` — Instantiates all tool provider classes
- `load_tools()` — Collects and indexes tools from providers
- `get_tool_definitions()` — Returns all tools for `tools/list` response
- `get_grouped_definitions()` — Returns tools grouped by provider label
- `get_tool($name)` — Resolves tool name to provider and method
- `count()` — Returns total number of registered tools

**Extensibility:**
```php
// Add custom tool provider
add_filter('bbmcp_tool_providers', function($providers) {
    $providers[] = new My_Custom_Tools();
    return $providers;
});
```

---

### 5. `BuddyBossMCP\Internal_REST_Client`

**File:** `includes/class-internal-rest-client.php`

**Purpose:** Wraps `rest_do_request()` to call BuddyBoss REST APIs internally without HTTP overhead.

**Key Responsibilities:**
- Makes internal REST API calls using `rest_do_request()`
- Handles user context switching (temporarily sets current user)
- Supports all HTTP methods (GET, POST, PUT, PATCH, DELETE)
- Formats responses as JSON for MCP tool output
- Converts `WP_Error` objects to error JSON

**Key Methods:**
- `request($method, $route, $params, $user_id)` — Generic REST request
- `get($route, $params, $user_id)` — GET request
- `post($route, $params, $user_id)` — POST request
- `put($route, $params, $user_id)` — PUT request
- `patch($route, $params, $user_id)` — PATCH request
- `delete($route, $params, $user_id)` — DELETE request
- `format_response($data)` — Converts data to pretty-printed JSON

**Advantages:**
- Zero HTTP overhead (no network calls)
- Automatic WordPress authentication context
- Direct access to BuddyBoss REST API
- Safe user context switching with automatic restoration

---

## Admin Classes

### 6. `BuddyBossMCP\Admin\Admin_Page`

**File:** `includes/admin/class-admin-page.php`

**Purpose:** Renders the WordPress Admin settings page showing connection info, tools list, and status.

**Key Responsibilities:**
- Registers admin menu page under "MCP Server"
- Displays connection endpoint and authentication info
- Lists all available tools grouped by provider
- Shows real-time connection status via JavaScript
- Removes BuddyBoss admin header and notices for clean UI
- Enqueues admin CSS and JavaScript assets

**Key Methods:**
- `register_menu()` — Adds admin menu page
- `remove_admin_chrome()` — Cleans up admin UI
- `render_admin_header()` — Custom header bar with status indicator
- `enqueue_assets($hook_suffix)` — Loads CSS/JS with localized data
- `render_page()` — Renders main settings template

**Admin Page Features:**
- Connection endpoint URL with copy-to-clipboard
- Live server status indicator
- Tool count and grouped tool list
- Test connection button
- Application Password generation link

**Template:** `templates/admin/settings-page.php`

---

## Tool Base Class

### 7. `BuddyBossMCP\Tools\Tool_Base`

**File:** `includes/tools/class-tool-base.php`

**Purpose:** Abstract base class providing tool registration helpers and validation methods for all tool providers.

**Key Responsibilities:**
- Provides `Internal_REST_Client` instance to all tools
- Defines `register_tools()` abstract method
- Helper method `create_tool()` for consistent tool definitions
- Parameter validation and sanitization helpers
- Type-safe parameter accessors (int, string, bool)

**Key Methods:**
- `register_tools()` — Abstract method implemented by subclasses
- `create_tool($name, $description, $properties, $method, $required)` — Builds tool definition
- `validate_required($args, $required)` — Throws exception if required params missing
- `get_int($args, $key, $default)` — Gets integer parameter with default
- `get_string($args, $key, $default, $max_length)` — Gets sanitized string (max 1000 chars)
- `get_bool($args, $key, $default)` — Gets boolean parameter

**Protected Properties:**
- `$rest_client` — `Internal_REST_Client` instance for API calls

**String Sanitization:**
- Automatically applies `sanitize_text_field()`
- Truncates strings to `MAX_STRING_LENGTH` (1000 chars) by default
- Prevents oversized input attacks

**Tool Naming Convention:**
- Pattern: `buddyboss_{action}_{resource}`
- Examples: `buddyboss_list_groups`, `buddyboss_create_activity`, `buddyboss_update_member`

---

## Tool Provider Classes

All tool providers extend `Tool_Base` and implement `register_tools()` to return an array of tool definitions. Each tool maps to a public method that accepts `($args, $user_id)` and returns a JSON string.

### 8. `BuddyBossMCP\Tools\Activity_Tools`

**File:** `includes/tools/class-activity-tools.php`

**Purpose:** Provides 6 tools for managing BuddyBoss activity feed (list, get, create, update, delete, favorite).

**Tools:**
1. `buddyboss_list_activities` — List activity feed with filtering (user, group, component, type, scope)
2. `buddyboss_get_activity` — Get single activity post by ID
3. `buddyboss_create_activity` — Create new activity update or group post
4. `buddyboss_update_activity` — Update activity content
5. `buddyboss_delete_activity` — Permanently delete activity
6. `buddyboss_favorite_activity` — Toggle favorite status

**REST API Endpoint:** `/buddyboss/v1/activity`

**Key Features:**
- Supports activity search and pagination
- Filters by user, group, component (activity/groups/friends), type
- Scope options: just-me, friends, groups, favorites
- Comment display modes: threaded, stream, false
- Content sanitized with `wp_kses_post()`

---

### 9. `BuddyBossMCP\Tools\Members_Tools`

**File:** `includes/tools/class-members-tools.php`

**Purpose:** Provides 5 tools for managing BuddyBoss members (list, get, create, update, delete).

**Tools:**
1. `buddyboss_list_members` — List members with role/type filtering
2. `buddyboss_get_member` — Get member profile by ID
3. `buddyboss_create_member` — Create new member account
4. `buddyboss_update_member` — Update member profile (name, email, type)
5. `buddyboss_delete_member` — Delete member with optional post reassignment

**REST API Endpoint:** `/buddyboss/v1/members`

**Key Features:**
- Search members by name
- Filter by WordPress role or BuddyBoss member type
- Exclude specific user IDs
- Username sanitized with `sanitize_user()`
- Email sanitized with `sanitize_email()`
- Max 100 results per page

---

### 10. `BuddyBossMCP\Tools\Groups_Tools`

**File:** `includes/tools/class-groups-tools.php`

**Purpose:** Provides 8 tools for managing BuddyBoss groups and memberships (list, get, create, update, delete, list/add/remove members).

**Tools:**
1. `buddyboss_list_groups` — List groups with status/type filtering
2. `buddyboss_get_group` — Get group details by ID
3. `buddyboss_create_group` — Create new group with privacy settings
4. `buddyboss_update_group` — Update group name, description, status
5. `buddyboss_delete_group` — Permanently delete group and all data
6. `buddyboss_list_group_members` — List members with role filtering
7. `buddyboss_add_group_member` — Add user to group with role (admin/mod/member)
8. `buddyboss_remove_group_member` — Remove user from group

**REST API Endpoint:** `/buddyboss/v1/groups`

**Key Features:**
- Group privacy: public, private, hidden
- Optional forum enablement
- Member roles: admin, mod, member, banned
- Search by name or description
- Filter by user membership or group type
- Defaults `roles` to 'admin,mod,member' for list_group_members
- Requires explicit `creator_id` parameter for group creation

---

### 11. `BuddyBossMCP\Tools\Messages_Tools`

**File:** `includes/tools/class-messages-tools.php`

**Purpose:** Provides 5 tools for managing BuddyBoss private messages (list threads, get thread, send, delete, mark read).

**Tools:**
1. `buddyboss_list_message_threads` — List threads by mailbox and read status
2. `buddyboss_get_message_thread` — Get full thread with all messages
3. `buddyboss_send_message` — Send new message to one or more recipients
4. `buddyboss_delete_message_thread` — Delete thread permanently
5. `buddyboss_mark_message_read` — Mark thread as read

**REST API Endpoint:** `/buddyboss/v1/messages`

**Key Features:**
- Mailbox options: inbox, sentbox, starred
- Filter by read status: all, read, unread
- Recipients as comma-separated user IDs
- Message content sanitized with `wp_kses_post()`
- Subject and recipients sanitized with `sanitize_text_field()`

---

### 12. `BuddyBossMCP\Tools\Friends_Tools`

**File:** `includes/tools/class-friends-tools.php`

**Purpose:** Provides 4 tools for managing BuddyBoss friendships (list, add, remove, list requests).

**Tools:**
1. `buddyboss_list_friends` — List friendships with confirmation filtering
2. `buddyboss_add_friend` — Send friendship request (auto-accept with `force`)
3. `buddyboss_remove_friend` — Remove friendship by ID
4. `buddyboss_list_friend_requests` — List pending requests for user

**REST API Endpoint:** `/buddyboss/v1/friends`

**Key Features:**
- Filter by confirmation status (pending vs confirmed)
- Auto-accept friendship with `force: true` parameter
- Requires both `initiator_id` and `friend_id` to add friend
- Friendship ID required for removal (not user IDs)

---

### 13. `BuddyBossMCP\Tools\Notifications_Tools`

**File:** `includes/tools/class-notifications-tools.php`

**Purpose:** Provides 4 tools for managing BuddyBoss notifications (list, get, mark read, delete).

**Tools:**
1. `buddyboss_list_notifications` — List with read status and component filtering
2. `buddyboss_get_notification` — Get single notification by ID
3. `buddyboss_mark_notification_read` — Mark as read/unread (`is_new` flag)
4. `buddyboss_delete_notification` — Delete notification permanently

**REST API Endpoint:** `/buddyboss/v1/notifications`

**Key Features:**
- Filter by user ID and component (activity, groups, friends, messages)
- Filter by read status via `is_new` boolean
- Defaults `is_new` to `false` when marking as read
- Max 100 notifications per page

---

### 14. `BuddyBossMCP\Tools\XProfile_Tools`

**File:** `includes/tools/class-xprofile-tools.php`

**Purpose:** Provides 5 tools for managing BuddyBoss extended profile fields (list groups/fields, get field/data, update data).

**Tools:**
1. `buddyboss_list_xprofile_groups` — List profile field groups
2. `buddyboss_list_xprofile_fields` — List fields (optionally by group)
3. `buddyboss_get_xprofile_field` — Get field definition by ID
4. `buddyboss_get_xprofile_data` — Get user's data for specific field
5. `buddyboss_update_xprofile_data` — Update user's field value

**REST API Endpoint:** `/buddyboss/v1/xprofile`

**Key Features:**
- Optional `fetch_fields` for including fields in group list
- Optional `fetch_field_data` for including data in field responses
- Filter fields by `profile_group_id`
- Requires both `field_id` and `user_id` for data operations
- Value sanitized with `sanitize_text_field()`

---

## Class Hierarchy

```
Plugin (singleton)
├── MCP_Server
├── REST_Controller
├── Tool_Registry
│   └── Tool_Base (abstract)
│       ├── Activity_Tools
│       ├── Members_Tools
│       ├── Groups_Tools
│       ├── Messages_Tools
│       ├── Friends_Tools
│       ├── Notifications_Tools
│       └── XProfile_Tools
└── Admin_Page

Internal_REST_Client (utility, used by Tool_Base)
```

---

## Request Flow

```
IDE/Client
    ↓ HTTP POST (JSON-RPC 2.0)
REST_Controller::handle_post_request()
    ↓ check_permissions()
MCP_Server::handle_request()
    ↓ parse JSON, validate protocol
MCP_Server::dispatch_method()
    ↓ route to handler
MCP_Server::handle_tool_call()
    ↓ lookup tool in registry
Tool_Registry::get_tool()
    ↓ return provider + method
Tool_Provider::{method}()
    ↓ use Internal_REST_Client
Internal_REST_Client::request()
    ↓ rest_do_request()
BuddyBoss REST API
    ↓ response data
← JSON response back up the chain
```

---

## Extending the Plugin

### Adding a New Tool to Existing Provider

1. Open the appropriate tool provider class (e.g., `class-groups-tools.php`)
2. Add tool definition in `register_tools()`:
   ```php
   $this->create_tool(
       'buddyboss_my_new_tool',
       'Description of what it does',
       array(
           'param1' => array(
               'type' => 'integer',
               'description' => 'Parameter description',
           ),
       ),
       'my_tool_method',
       array('param1') // required params
   )
   ```
3. Implement the tool method:
   ```php
   public function my_tool_method($args, $user_id) {
       $this->validate_required($args, array('param1'));
       $response = $this->rest_client->get('/buddyboss/v1/endpoint', $args, $user_id);
       return $this->rest_client->format_response($response);
   }
   ```

### Creating a New Tool Provider

See `docs/extending-tools.md` for complete guide.

---

## Security Considerations

**Authentication:**
- WordPress Application Passwords (HTTP Basic Auth)
- Requires `manage_options` capability (administrator-level)
- Validated by `REST_Controller::check_permissions()`

**Input Validation:**
- Required parameters validated via `Tool_Base::validate_required()`
- Strings truncated to 1000 chars max
- Sanitized using `sanitize_text_field()`, `sanitize_email()`, `wp_kses_post()`
- Integer parameters cast with `absint()`

**Error Handling:**
- Validation errors (InvalidArgumentException) shown to client for self-correction
- Execution errors logged server-side, generic message returned to client
- Never exposes stack traces or sensitive data to client

**User Context:**
- Tools execute in authenticated admin's context
- `Internal_REST_Client` safely switches user context when needed
- Original user always restored via try/finally block

---

## Performance Optimizations

1. **Zero HTTP Overhead** — `rest_do_request()` bypasses HTTP layer entirely
2. **Singleton Pattern** — Plugin instance created once
3. **Registry Caching** — Tools loaded and indexed once at initialization
4. **Lazy Loading** — Tool providers only instantiated when needed

---

## Constants

Defined in `buddyboss-mcp-server.php`:

- `BBMCP_VERSION` — Plugin version (currently 1.0.1)
- `BBMCP_PLUGIN_DIR` — Absolute path to plugin directory
- `BBMCP_PLUGIN_URL` — URL to plugin directory
- `BBMCP_PLUGIN_FILE` — Absolute path to main plugin file
- `BBMCP_PLUGIN_BASENAME` — Plugin basename for WordPress hooks

---

## Filters and Hooks

**Filters:**
- `bbmcp_tool_providers` — Add custom tool providers
**Actions:**
- `rest_api_init` — REST route registration
- `admin_menu` — Admin page registration
- `admin_enqueue_scripts` — Admin asset loading

---

## Related Documentation

- [Architecture Overview](../architecture.md) — High-level system design
- [Extending Tools](../extending-tools.md) — Guide to adding new tools
- [MCP Protocol](../mcp-protocol.md) — JSON-RPC 2.0 and MCP spec details
- [Setup Guide](../setup-guide.md) — Installation and configuration
- [Tool Reference](../tool-reference.md) — Complete list of available tools

---

## Namespace Convention

All classes use the `BuddyBossMCP` namespace:

- Core classes: `BuddyBossMCP\`
- Admin classes: `BuddyBossMCP\Admin\`
- Tool classes: `BuddyBossMCP\Tools\`

---

## Total Statistics

- **14 classes** total
- **42+ MCP tools** across 7 providers
- **5 core infrastructure classes**
- **7 BuddyBoss feature domains** covered
- **1 admin interface class**
- **1 abstract base class** for consistency

---

## Quick Reference Table

| Class | File | Layer | Purpose |
|-------|------|-------|---------|
| `Plugin` | `class-plugin.php` | Core | Entry point and bootstrapper |
| `MCP_Server` | `class-mcp-server.php` | Core | JSON-RPC 2.0 dispatcher |
| `REST_Controller` | `class-rest-controller.php` | Core | WordPress REST endpoint |
| `Tool_Registry` | `class-tool-registry.php` | Core | Tool discovery and routing |
| `Internal_REST_Client` | `class-internal-rest-client.php` | Core | BuddyBoss API bridge |
| `Admin_Page` | `admin/class-admin-page.php` | Admin | Settings UI |
| `Tool_Base` | `tools/class-tool-base.php` | Base | Abstract tool foundation |
| `Activity_Tools` | `tools/class-activity-tools.php` | Provider | Activity feed management |
| `Members_Tools` | `tools/class-members-tools.php` | Provider | Member management |
| `Groups_Tools` | `tools/class-groups-tools.php` | Provider | Group management |
| `Messages_Tools` | `tools/class-messages-tools.php` | Provider | Private messaging |
| `Friends_Tools` | `tools/class-friends-tools.php` | Provider | Friendship management |
| `Notifications_Tools` | `tools/class-notifications-tools.php` | Provider | Notification management |
| `XProfile_Tools` | `tools/class-xprofile-tools.php` | Provider | Profile field management |
