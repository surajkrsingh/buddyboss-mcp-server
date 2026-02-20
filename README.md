# BuddyBoss MCP Server

A WordPress plugin that exposes your [BuddyBoss Platform](https://www.buddyboss.com/) as an [MCP (Model Context Protocol)](https://modelcontextprotocol.io/) server, enabling AI-powered IDEs to manage your BuddyBoss community through natural language.

> **Upload ZIP, activate, connect** — no Node.js, no Composer, no CLI required on the server.

---

## What Is This?

BuddyBoss MCP Server turns your WordPress site into an MCP-compatible endpoint. Once connected, you can ask your AI assistant things like:

- *"List all public groups with more than 5 members"*
- *"Create a private group called Design Team"*
- *"Add user #12 to the Architecture group as a moderator"*
- *"Show me the latest activity feed"*
- *"Send a message to all group admins"*

The AI translates your instructions into BuddyBoss API calls automatically.

---

## Supported AI Clients

| Client | Transport | Bridge Required |
|--------|-----------|-----------------|
| **Claude.ai** (Pro/Team/Enterprise) | HTTP POST | No |
| **Claude Code** (CLI) | HTTP POST | No |
| **Claude Desktop** | stdio | Yes — `mcp-remote` |
| **Cursor IDE** | HTTP or stdio | Depends on version |
| **VS Code** (MCP extension) | stdio | Yes — `mcp-remote` |

---

## Requirements

- **WordPress** 5.6+ (for Application Passwords)
- **BuddyBoss Platform** 2.0+ (must be active)
- **PHP** 7.4+
- **HTTPS** enabled (required for Application Passwords; localhost is exempt)

---

## Installation

1. Download the `buddyboss-mcp-server.zip` file
2. Go to **WordPress Admin > Plugins > Add New > Upload Plugin**
3. Upload the ZIP file, click **Install**, then **Activate**
4. Verify: **"MCP Server"** menu appears in the admin sidebar

---

## Setup

### Step 1: Create an Application Password

1. Go to **WordPress Admin > Users > Your Profile**
2. Scroll to the **"Application Passwords"** section
3. Enter a name: `Claude MCP` (or any descriptive label)
4. Click **"Add New Application Password"**
5. **Copy the generated password immediately** — it's shown only once
   - Format: `xxxx xxxx xxxx xxxx xxxx xxxx`
   - This is NOT your WordPress login password

### Step 2: Get Your Connection Config

1. Go to **WordPress Admin > MCP Server**
2. Copy the pre-generated config snippet for your IDE
3. Replace `YOUR_APP_PASSWORD` / `YOUR_BASE64_CREDENTIALS` with your actual credentials

### Step 3: Generate Base64 Credentials

```bash
echo -n "your-username:xxxx xxxx xxxx xxxx xxxx xxxx" | base64
```

This outputs a string like `eW91ci11c2VybmFtZTp4eHh4IHh4eHg=` — use this as `YOUR_BASE64_CREDENTIALS` in the configs below.

---

## IDE Connection Configs

### Claude Desktop

**File:** `~/Library/Application Support/Claude/claude_desktop_config.json` (macOS)
**File:** `%APPDATA%\Claude\claude_desktop_config.json` (Windows)

```json
{
  "mcpServers": {
    "buddyboss": {
      "command": "npx",
      "args": ["-y", "mcp-remote", "https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp"],
      "env": {
        "MCP_HEADERS": "Authorization:Basic YOUR_BASE64_CREDENTIALS"
      }
    }
  }
}
```

> Requires Node.js installed locally. The `mcp-remote` package bridges Claude Desktop's stdio transport to the HTTP endpoint.

### Cursor IDE

**File:** `.cursor/mcp.json` (project root) or Cursor global settings

```json
{
  "mcpServers": {
    "buddyboss": {
      "url": "https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp",
      "headers": {
        "Authorization": "Basic YOUR_BASE64_CREDENTIALS"
      }
    }
  }
}
```

### Claude Code (CLI)

**File:** `.mcp.json` (project root) or `~/.claude.json`

```json
{
  "mcpServers": {
    "buddyboss": {
      "type": "url",
      "url": "https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp",
      "headers": {
        "Authorization": "Basic YOUR_BASE64_CREDENTIALS"
      }
    }
  }
}
```

### Claude.ai (Pro/Team/Enterprise)

1. Go to **Claude.ai > Settings > Connectors**
2. Add **Custom Connector**
3. URL: `https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp`
4. Auth: **Basic Authentication**
5. Enter your username + application password

### VS Code (with MCP extension)

**File:** `.vscode/mcp.json`

```json
{
  "mcpServers": {
    "buddyboss": {
      "command": "npx",
      "args": ["-y", "mcp-remote", "https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp"],
      "env": {
        "MCP_HEADERS": "Authorization:Basic YOUR_BASE64_CREDENTIALS"
      }
    }
  }
}
```

---

## Available Tools (65)

All tools follow the naming convention: `buddyboss_{verb}_{resource}`

### Phase 1: Core Tools (37) — Always Enabled

#### Members (5 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_members` | List members with filtering by role, status, search, or member type |
| `buddyboss_get_member` | Get detailed member profile by ID |
| `buddyboss_create_member` | Create a new member account |
| `buddyboss_update_member` | Update member profile details |
| `buddyboss_delete_member` | Delete a member account |

#### Groups (8 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_groups` | List groups with filtering by status, search, user, or type |
| `buddyboss_get_group` | Get group details by ID |
| `buddyboss_create_group` | Create a new group (public, private, or hidden) |
| `buddyboss_update_group` | Update group name, description, or status |
| `buddyboss_delete_group` | Permanently delete a group and all its data |
| `buddyboss_list_group_members` | List members of a group, filterable by role |
| `buddyboss_add_group_member` | Add a user to a group as admin, mod, or member |
| `buddyboss_remove_group_member` | Remove a user from a group |

#### Activity (6 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_activities` | List activity feed with scope, user, group, and component filters |
| `buddyboss_get_activity` | Get a single activity post by ID |
| `buddyboss_create_activity` | Create a new activity post (user or group) |
| `buddyboss_update_activity` | Update activity content |
| `buddyboss_delete_activity` | Delete an activity post |
| `buddyboss_favorite_activity` | Toggle favorite on an activity |

#### Messages (5 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_message_threads` | List message threads (inbox, sentbox, starred) |
| `buddyboss_get_message_thread` | Get a thread with all its messages |
| `buddyboss_send_message` | Send a new message to one or more recipients |
| `buddyboss_delete_message_thread` | Delete a message thread |
| `buddyboss_mark_message_read` | Mark a thread as read |

#### Friends (4 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_friends` | List friendships for a user |
| `buddyboss_add_friend` | Send a friendship request (with optional auto-accept) |
| `buddyboss_remove_friend` | Remove a friendship |
| `buddyboss_list_friend_requests` | List pending friendship requests |

#### Notifications (4 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_notifications` | List notifications with filters |
| `buddyboss_get_notification` | Get notification by ID |
| `buddyboss_mark_notification_read` | Mark notification as read or unread |
| `buddyboss_delete_notification` | Delete a notification |

#### Extended Profiles / XProfile (5 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_xprofile_groups` | List profile field groups |
| `buddyboss_list_xprofile_fields` | List profile fields in a group |
| `buddyboss_get_xprofile_field` | Get profile field details |
| `buddyboss_get_xprofile_data` | Get a user's profile field data |
| `buddyboss_update_xprofile_data` | Update a user's profile field data |

### Phase 2: BuddyBoss Tools (18) — Toggleable

#### Media / Photos (5 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_media` | List photos/media with privacy and album filters |
| `buddyboss_get_media` | Get media by ID |
| `buddyboss_upload_media` | Upload a photo |
| `buddyboss_update_media` | Update media metadata or privacy |
| `buddyboss_delete_media` | Delete media |

#### Video (4 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_videos` | List videos |
| `buddyboss_get_video` | Get video by ID |
| `buddyboss_upload_video` | Upload a video |
| `buddyboss_delete_video` | Delete a video |

#### Documents (5 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_documents` | List documents with folder and group filters |
| `buddyboss_get_document` | Get document by ID |
| `buddyboss_upload_document` | Upload a document |
| `buddyboss_delete_document` | Delete a document |
| `buddyboss_list_document_folders` | List document folders |

#### Moderation (4 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_moderation_reports` | List reported content |
| `buddyboss_report_content` | Report content or a user |
| `buddyboss_block_member` | Block a member |
| `buddyboss_unblock_member` | Unblock a member |

### Phase 3: Advanced Tools (10) — Toggleable

#### Forums (6 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_forums` | List forums |
| `buddyboss_get_forum` | Get forum by ID |
| `buddyboss_create_topic` | Create a new forum topic |
| `buddyboss_get_topic` | Get topic by ID |
| `buddyboss_create_reply` | Reply to a topic |
| `buddyboss_list_replies` | List replies in a topic |

#### LearnDash Integration (4 tools)

| Tool | Description |
|------|-------------|
| `buddyboss_list_courses` | List LearnDash courses |
| `buddyboss_get_course` | Get course details |
| `buddyboss_enroll_user` | Enroll a user in a course |
| `buddyboss_get_course_progress` | Get user's course progress |

---

## Architecture

```
AI IDE (Claude / Cursor / VS Code)
  │
  ▼
POST /wp-json/buddyboss-mcp/v1/mcp
Authorization: Basic base64(username:app_password)
  │
  ▼
┌─────────────────────────────────────┐
│  REST_Controller                    │  WordPress REST API endpoint
│  • Validates Application Password   │
│  • Checks manage_options capability │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  MCP_Server                         │  JSON-RPC 2.0 protocol handler
│  • Routes: initialize, tools/list,  │
│    tools/call, ping                 │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Tool_Registry                      │  Finds & dispatches to tool provider
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Tool Provider (e.g. Groups_Tools)  │  Validates params, calls API
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Internal_REST_Client               │  rest_do_request() — zero HTTP overhead
└──────────────┬──────────────────────┘
               │
               ▼
        BuddyBoss REST API
```

### Key Design Decisions

| Decision | Choice | Why |
|----------|--------|-----|
| Language | Pure PHP 7.4+ | No Composer/Node.js needed on server — upload ZIP and activate |
| Transport | Streamable HTTP (POST + JSON) | Works with Claude.ai Connectors + `mcp-remote` bridge for Desktop |
| Protocol | JSON-RPC 2.0 (hand-rolled) | Simple for tools-only server (~200 lines), no experimental SDK dependencies |
| Auth | WordPress Application Passwords | Built into WP 5.6+, per-app revocable, HTTPS-enforced |
| Tool execution | `rest_do_request()` internal dispatch | Reuses BuddyBoss validation and permissions with zero HTTP overhead |
| Permission | `manage_options` capability | Admin-only MCP access |

---

## Protocol Details

The plugin implements [MCP specification 2024-11-05](https://spec.modelcontextprotocol.io/specification/2024-11-05/) over JSON-RPC 2.0.

### Supported Methods

| Method | Purpose |
|--------|---------|
| `initialize` | Handshake — returns server info and capabilities |
| `notifications/initialized` | Client confirms init (returns HTTP 202) |
| `tools/list` | Returns all available tool definitions |
| `tools/call` | Executes a specific tool with arguments |
| `ping` | Health check |

### Example: Tool Call

**Request:**
```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "buddyboss_list_groups",
    "arguments": {
      "status": "public",
      "per_page": 5
    }
  },
  "id": 1
}
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "[{\"id\": 1, \"name\": \"Foodie's Group\", \"status\": \"public\", ...}]"
      }
    ]
  }
}
```

### Error Codes

| Code | Message | When |
|------|---------|------|
| -32700 | Parse error | Invalid JSON received |
| -32600 | Invalid Request | Missing `jsonrpc: "2.0"` |
| -32601 | Method not found | Unknown method name |
| -32602 | Invalid params | Missing tool name or unknown tool |
| -32000 | Tool execution failed | Tool threw an exception |

---

## Testing

### Quick Test via curl

```bash
# Initialize handshake
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}' \
  | python3 -m json.tool

# List all tools
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":2}' \
  | python3 -m json.tool

# Call a tool
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"buddyboss_list_groups","arguments":{"per_page":3}},"id":3}' \
  | python3 -m json.tool

# Health check (GET)
curl -s -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp
```

### MCP Inspector

```bash
npx @modelcontextprotocol/inspector https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp
```

### WordPress Admin

Go to **MCP Server** in the admin sidebar and click **"Test Connection"**.

---

## File Structure

```
buddyboss-mcp-server/
├── buddyboss-mcp-server.php              # Main plugin file (bootstrap + constants)
├── includes/
│   ├── class-plugin.php                  # Singleton entry point, dependency checks
│   ├── class-mcp-server.php             # JSON-RPC 2.0 protocol handler
│   ├── class-rest-controller.php        # WordPress REST endpoint + auth
│   ├── class-tool-registry.php          # Tool discovery and dispatch
│   ├── class-internal-rest-client.php   # Internal REST API bridge (zero HTTP overhead)
│   ├── admin/
│   │   └── class-admin-page.php         # Settings page + connection config snippets
│   └── tools/
│       ├── class-tool-base.php          # Abstract base class for all tools
│       ├── class-groups-tools.php       # Groups CRUD + membership (8 tools)
│       ├── class-members-tools.php      # Members CRUD (5 tools)
│       ├── class-activity-tools.php     # Activity feed (6 tools)
│       ├── class-messages-tools.php     # Messages/threads (5 tools)
│       ├── class-friends-tools.php      # Friendships (4 tools)
│       ├── class-notifications-tools.php # Notifications (4 tools)
│       ├── class-xprofile-tools.php     # Profile fields (5 tools)
│       ├── class-media-tools.php        # Photos/albums (5 tools)
│       ├── class-video-tools.php        # Videos (4 tools)
│       ├── class-document-tools.php     # Documents/folders (5 tools)
│       ├── class-forums-tools.php       # Forums/topics/replies (6 tools)
│       ├── class-moderation-tools.php   # Moderation/blocking (4 tools)
│       └── class-learndash-tools.php    # LearnDash courses (4 tools)
├── templates/
│   └── admin/
│       └── settings-page.php            # Admin UI template
├── assets/
│   ├── css/admin.css                    # Admin page styles
│   └── js/admin.js                      # Copy-to-clipboard, test connection
└── docs/                                # Documentation
    ├── architecture.md
    ├── mcp-protocol.md
    ├── tool-reference.md
    ├── setup-guide.md
    └── api-research.md
```

---

## Extending with Custom Tools

You can add your own tool providers via the `bbmcp_tool_providers` filter:

```php
add_filter( 'bbmcp_tool_providers', function( $providers ) {
    require_once __DIR__ . '/class-my-custom-tools.php';
    $providers[] = new My_Custom_Tools();
    return $providers;
} );
```

Your tool class must extend `BuddyBossMCP\Tools\Tool_Base`:

```php
namespace BuddyBossMCP\Tools;

class My_Custom_Tools extends Tool_Base {

    public function register_tools() {
        return array(
            $this->create_tool(
                'buddyboss_my_tool',       // Tool name
                'Does something useful.',   // Description for AI
                array(                      // Input schema (JSON Schema)
                    'param1' => array(
                        'type'        => 'string',
                        'description' => 'A required parameter.',
                    ),
                ),
                'handle_my_tool',           // Method name on this class
                array( 'param1' )           // Required parameters
            ),
        );
    }

    public function handle_my_tool( $args, $user_id ) {
        $this->validate_required( $args, array( 'param1' ) );
        $value = $this->get_string( $args, 'param1' );

        // Use $this->rest_client to call BuddyBoss APIs
        $response = $this->rest_client->get( '/buddyboss/v1/some-endpoint', array(), $user_id );

        return $this->rest_client->format_response( $response );
    }
}
```

---

## Security

| Layer | Protection |
|-------|-----------|
| **Transport** | HTTPS required (Application Passwords enforce this) |
| **Authentication** | WordPress Application Passwords (Basic Auth) |
| **Authorization** | `manage_options` capability — admin-only access |
| **Input validation** | WordPress sanitization functions on all tool parameters |
| **Permissions** | `rest_do_request()` enforces BuddyBoss endpoint permissions |
| **Errors** | No sensitive data exposed in error messages |

### Best Practices

1. **Always use HTTPS** — Application Passwords transmit credentials in Base64 (not encrypted)
2. **Use a dedicated admin account** — Don't use your primary admin credentials
3. **One password per IDE** — Create separate Application Passwords for each tool
4. **Revoke unused passwords** — Remove passwords for tools you no longer use
5. **Monitor access** — Check WordPress access logs for MCP endpoint usage

---

## Troubleshooting

### "401 Unauthorized"
- Application Password is incorrect or expired
- Verify Base64 encoding: `echo -n "username:password" | base64`
- Make sure you're using the **Application Password**, not your login password
- Check if the user account is active

### "403 Forbidden"
- User doesn't have `manage_options` capability
- Only WordPress **Administrators** can access MCP
- Check user role in **Users > Edit User**

### "404 Not Found"
- Plugin is not activated
- Permalinks not flushed: go to **Settings > Permalinks > Save Changes**
- Wrong endpoint URL — should be `/wp-json/buddyboss-mcp/v1/mcp`

### "Application Passwords not available"
- Requires WordPress 5.6+
- Requires HTTPS (localhost is exempt for development)
- May be disabled by a security plugin — check its settings

### "BuddyBoss Platform not found"
- BuddyBoss Platform plugin must be **installed AND activated**
- Check **Plugins** page for "BuddyBoss Platform"

### Claude Desktop shows "Server disconnected"
- Ensure Node.js is installed: `node --version`
- Check `mcp-remote` works: `npx mcp-remote --version`
- Verify the URL is accessible from your machine
- Check firewall/VPN settings

### Tools return empty results
- Check if the BuddyBoss component is active (e.g., Groups, Activity)
- Go to **BuddyBoss > Settings > Components** and enable the relevant component

---

## Comparison with BuddyPress MCP

| Aspect | [BuddyPress MCP](https://github.com/vapvarun/buddypress-mcp) (Reference) | BuddyBoss MCP Server (This Plugin) |
|--------|--------------------------|-------------------------------|
| Installation | Clone repo, npm install, configure .env | Upload ZIP, activate |
| Runtime | Separate Node.js process | Runs inside WordPress |
| API access | External HTTP calls to WP REST API | Internal `rest_do_request()` — zero overhead |
| Auth | Environment variables | WordPress Application Passwords |
| Transport | stdio (local only) | HTTP (remote-capable) |
| Dependencies | `@modelcontextprotocol/sdk`, `node-fetch` | None |
| Tools | 36 (BuddyPress) | 65 (BuddyBoss-specific features included) |
| Target user | Developers | Site owners and developers |

---

## License

GPLv2 or later — [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Credits

- **[BuddyBoss](https://www.buddyboss.com/)** — Community platform for WordPress
- **[Model Context Protocol](https://modelcontextprotocol.io/)** — Open standard by Anthropic
- **[BuddyPress MCP](https://github.com/vapvarun/buddypress-mcp)** — Reference implementation by vapvarun
