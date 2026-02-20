# MCP Protocol — Implementation Details

Technical reference for the JSON-RPC 2.0 protocol implementation used in the BuddyBoss MCP Server.

---

## Protocol Overview

The Model Context Protocol (MCP) uses JSON-RPC 2.0 over HTTP. Our implementation uses Streamable HTTP transport — a single POST endpoint that accepts JSON-RPC messages and returns JSON responses.

**Endpoint:** `POST /wp-json/buddyboss-mcp/v1/mcp`

**Spec version:** `2025-11-25` (stable — Streamable HTTP)

---

## Message Format

### Request (Client → Server)

```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "buddyboss_list_members",
    "arguments": {
      "per_page": 10,
      "search": "john"
    }
  },
  "id": 1
}
```

### Response (Server → Client)

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "[{\"id\": 1, \"name\": \"John Doe\", ...}]"
      }
    ]
  }
}
```

### Error Response

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "error": {
    "code": -32602,
    "message": "Tool not found: buddyboss_nonexistent"
  }
}
```

### Notification (no response expected)

```json
{
  "jsonrpc": "2.0",
  "method": "notifications/initialized"
}
```

Server returns HTTP 202 Accepted with empty body for notifications.

---

## Supported Methods

### 1. `initialize`

**Purpose:** Handshake — client announces itself, server returns capabilities.

**Request:**
```json
{
  "jsonrpc": "2.0",
  "method": "initialize",
  "params": {
    "protocolVersion": "2025-11-25",
    "capabilities": {},
    "clientInfo": {
      "name": "claude-desktop",
      "version": "1.0.0"
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
    "protocolVersion": "2025-11-25",
    "capabilities": {
      "tools": {}
    },
    "serverInfo": {
      "name": "buddyboss-mcp-server",
      "version": "1.0.0"
    }
  }
}
```

**Notes:**
- Server only advertises `tools` capability (no resources, no prompts)
- Protocol version must match — server returns its supported version

---

### 2. `notifications/initialized`

**Purpose:** Client confirms initialization is complete.

**Request:**
```json
{
  "jsonrpc": "2.0",
  "method": "notifications/initialized"
}
```

**Response:** HTTP 202 Accepted, empty body (notifications have no `id`, no response).

---

### 3. `tools/list`

**Purpose:** Client requests available tool definitions.

**Request:**
```json
{
  "jsonrpc": "2.0",
  "method": "tools/list",
  "id": 2
}
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "result": {
    "tools": [
      {
        "name": "buddyboss_list_members",
        "description": "List all members with optional filtering by role, status, or search query.",
        "inputSchema": {
          "type": "object",
          "properties": {
            "page": {
              "type": "integer",
              "description": "Page number for pagination (default: 1)"
            },
            "per_page": {
              "type": "integer",
              "description": "Members per page (default: 20, max: 100)"
            },
            "search": {
              "type": "string",
              "description": "Search members by name or username"
            }
          }
        }
      }
    ]
  }
}
```

**Notes:**
- Returns ALL enabled tools (across all active phases)
- `inputSchema` follows JSON Schema draft-07
- Required fields marked with `"required": true` in property definition

---

### 4. `tools/call`

**Purpose:** Execute a specific tool with arguments.

**Request:**
```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "buddyboss_get_member",
    "arguments": {
      "id": 42
    }
  },
  "id": 3
}
```

**Success Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "{\"id\": 42, \"name\": \"Jane Smith\", \"email\": \"jane@example.com\", ...}"
      }
    ]
  }
}
```

**Tool Error Response (tool found but execution failed):**
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "Error: Member with ID 99999 not found"
      }
    ],
    "isError": true
  }
}
```

**Protocol Error (tool not found):**
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "error": {
    "code": -32602,
    "message": "Tool not found: buddyboss_nonexistent"
  }
}
```

**Notes:**
- `result.content` is always an array of content blocks
- Content block types: `text` (always), `image` (future), `resource` (future)
- `isError: true` indicates tool-level error (not protocol error)
- Protocol errors use JSON-RPC `error` field (not `result`)

---

## Error Codes

### Standard JSON-RPC 2.0

| Code | Message | When |
|------|---------|------|
| -32700 | Parse error | Invalid JSON received |
| -32600 | Invalid Request | Missing `jsonrpc: "2.0"` |
| -32601 | Method not found | Unknown method name |
| -32602 | Invalid params | Missing tool name, unknown tool |
| -32603 | Internal error | Unexpected server error |

### Custom Server Errors

| Code | Message | When |
|------|---------|------|
| -32000 | Tool execution failed | Tool threw exception |
| -32002 | BuddyBoss not available | BB Platform not active |

---

## PHP Implementation Pattern

### MCP Server Core (~200 lines)

```php
class MCP_Server {
    protected $tool_registry;

    public function handle_request( $request_body, $user_id ) {
        $request = json_decode( $request_body, true );

        // Validate JSON
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return $this->error_response( -32700, 'Parse error', null );
        }

        // Validate JSON-RPC
        if ( ! isset( $request['jsonrpc'] ) || '2.0' !== $request['jsonrpc'] ) {
            return $this->error_response( -32600, 'Invalid Request', null );
        }

        $method = $request['method'] ?? null;
        $params = $request['params'] ?? array();
        $id     = $request['id'] ?? null;

        // Notifications (no id) — return null for 202 response
        if ( null === $id ) {
            return null;
        }

        // Route to handler
        switch ( $method ) {
            case 'initialize':
                return $this->handle_initialize( $id );
            case 'tools/list':
                return $this->handle_tools_list( $id );
            case 'tools/call':
                return $this->handle_tool_call( $params, $id, $user_id );
            default:
                return $this->error_response( -32601, 'Method not found', $id );
        }
    }
}
```

### REST Controller

```php
class REST_Controller extends WP_REST_Controller {

    public function register_routes() {
        register_rest_route( 'buddyboss-mcp/v1', '/mcp', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'handle_request' ),
            'permission_callback' => array( $this, 'check_permissions' ),
        ) );
    }

    public function handle_request( $request ) {
        $body    = $request->get_body();
        $user_id = get_current_user_id();

        $response = $this->mcp_server->handle_request( $body, $user_id );

        // Notification (no response)
        if ( null === $response ) {
            return new WP_REST_Response( null, 202 );
        }

        return rest_ensure_response( $response );
    }

    public function check_permissions( $request ) {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_forbidden', 'Authentication required.', array( 'status' => 401 ) );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
        }
        return true;
    }
}
```

### Tool Definition Schema

```php
// Tool definition as returned by tools/list
array(
    'name'        => 'buddyboss_list_members',
    'description' => 'List all members with optional filtering.',
    'inputSchema' => array(
        'type'       => 'object',
        'properties' => array(
            'page' => array(
                'type'        => 'integer',
                'description' => 'Page number (default: 1)',
            ),
            'search' => array(
                'type'        => 'string',
                'description' => 'Search by name',
            ),
        ),
    ),
)
```

---

## Transport Details

### Streamable HTTP

Our implementation uses the simplest form of [Streamable HTTP](https://modelcontextprotocol.io/specification/2025-11-25/basic/transports) — the current recommended MCP transport:

- **Single POST endpoint** — All JSON-RPC messages go to one URL
- **JSON responses only** — No streaming needed for our use case
- **Stateless** — Each request is self-contained (auth via Application Passwords), no `Mcp-Session-Id` header needed

This is valid per the MCP spec — SSE streaming and sessions are optional features of Streamable HTTP.

### What We Don't Implement (and Why)

| Feature | Status | Reason |
|---------|--------|--------|
| Session management | Skipped | Stateless auth via Application Passwords |
| GET endpoint | Skipped | No server-to-client notifications needed |
| DELETE endpoint | Skipped | No sessions to terminate |
| Resources | Skipped | Tools are sufficient for our use case |
| Prompts | Skipped | Tools are sufficient for our use case |
| Sampling | Skipped | Not needed |

---

## Client Compatibility

### How Different Clients Connect

| Client | Transport | Auth | Bridge Needed |
|--------|-----------|------|---------------|
| Claude.ai (Pro/Team) | HTTP POST directly | Basic Auth header | No |
| Claude Desktop | stdio (local) | N/A | Yes — `mcp-remote` |
| Cursor | HTTP or stdio | Basic Auth or bridge | Depends on version |
| Claude Code | HTTP POST | Basic Auth header | No |
| VS Code (MCP ext) | stdio (local) | N/A | Yes — `mcp-remote` |

### mcp-remote Bridge

For stdio-based clients, the `mcp-remote` npm package bridges stdio ↔ HTTP:

```
Claude Desktop ←stdio→ mcp-remote ←HTTP→ WordPress MCP endpoint
```

Config:
```json
{
  "command": "npx",
  "args": ["-y", "mcp-remote", "https://site.com/wp-json/buddyboss-mcp/v1/mcp"],
  "env": {
    "MCP_HEADERS": "Authorization:Basic BASE64"
  }
}
```

---

## Testing the Protocol

### Using MCP Inspector

```bash
npx @modelcontextprotocol/inspector https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp
```

### Minimal curl Test Sequence

```bash
# 1. Initialize
curl -s -X POST $URL -u "$USER:$PASS" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{"protocolVersion":"2025-11-25","capabilities":{},"clientInfo":{"name":"test","version":"1.0"}},"id":1}'

# 2. Initialized notification
curl -s -X POST $URL -u "$USER:$PASS" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"notifications/initialized"}'

# 3. List tools
curl -s -X POST $URL -u "$USER:$PASS" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":2}'

# 4. Call a tool
curl -s -X POST $URL -u "$USER:$PASS" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"buddyboss_list_members","arguments":{"per_page":3}},"id":3}'
```

---

## References

- [MCP Specification (2025-11-25)](https://modelcontextprotocol.io/specification/2025-11-25)
- [MCP Transports — Streamable HTTP](https://modelcontextprotocol.io/specification/2025-11-25/basic/transports)
- [JSON-RPC 2.0 Specification](https://www.jsonrpc.org/specification)
- [BuddyPress MCP Reference](https://github.com/vapvarun/buddypress-mcp)
- [WordPress Application Passwords](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/)
