# BuddyBoss MCP Server — Setup & Connection Guide

## Prerequisites

- WordPress 5.6+ (for Application Passwords)
- BuddyBoss Platform 2.0+ (active)
- HTTPS enabled (required for Application Passwords; localhost exempt)
- PHP 7.4+

---

## Step 1: Install & Activate Plugin

1. Download `buddyboss-mcp-server.zip`
2. WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file → Install → Activate
4. Verify: "MCP Server" menu appears in admin sidebar

---

## Step 2: Create Application Password

1. WordPress Admin → Users → Your Profile
2. Scroll to "Application Passwords" section
3. Enter name: `Claude MCP` (or any descriptive name)
4. Click "Add New Application Password"
5. **Copy the generated password immediately** (shown only once)
   - Format: `xxxx xxxx xxxx xxxx xxxx xxxx`
   - This is NOT your WordPress login password

---

## Step 3: Get Connection Config

1. WordPress Admin → MCP Server
2. Copy the pre-generated config snippet for your IDE
3. Replace `YOUR_APP_PASSWORD` with the password from Step 2

---

## Connection Config (Claude Code CLI)

File: `.mcp.json` (project root) or `~/.claude.json`

```json
{
  "mcpServers": {
    "buddyboss": {
      "type": "http",
      "url": "https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp",
      "headers": {
        "Authorization": "Basic BASE64_CREDENTIALS"
      }
    }
  }
}
```

**Generate BASE64_CREDENTIALS:**
```bash
echo -n "YOUR_USERNAME:YOUR_APP_PASSWORD" | base64
```

---

## Step 4: Verify Connection

### Quick Test via curl

```bash
# Test initialize
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "YOUR_USERNAME:YOUR_APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{"protocolVersion":"2025-03-26","capabilities":{},"clientInfo":{"name":"test","version":"1.0"}},"id":1}' \
  | python3 -m json.tool

# Expected response:
# {
#   "jsonrpc": "2.0",
#   "id": 1,
#   "result": {
#     "protocolVersion": "2025-03-26",
#     "capabilities": {"tools": {}},
#     "serverInfo": {"name": "buddyboss-mcp-server", "version": "1.0.0"}
#   }
# }
```

```bash
# Test tools/list
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "YOUR_USERNAME:YOUR_APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":2}' \
  | python3 -m json.tool

# Expected: Array of 65 tool definitions
```

```bash
# Test tool call
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "YOUR_USERNAME:YOUR_APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"buddyboss_list_members","arguments":{"per_page":3}},"id":3}' \
  | python3 -m json.tool

# Expected: List of 3 members
```

### Test from WordPress Admin

1. Go to MCP Server settings page
2. Click "Test Connection" button
3. Should show green checkmark + server info

---

## Troubleshooting

### "401 Unauthorized"
- Application Password is incorrect or expired
- Check Base64 encoding: `echo -n "username:password" | base64`
- Ensure using Application Password, NOT login password
- Check if user account is active

### "403 Forbidden"
- User doesn't have `manage_options` capability
- Only WordPress administrators can access MCP
- Check user role in Users → Edit User

### "404 Not Found"
- Plugin not activated
- Permalinks not flushed: Settings → Permalinks → Save Changes
- Wrong endpoint URL (should be `/wp-json/buddyboss-mcp/v1/mcp`)

### "Application Passwords not available"
- Requires WordPress 5.6+
- Requires HTTPS (or localhost for development)
- May be disabled by security plugin — check plugin settings

### "BuddyBoss Platform not found"
- BuddyBoss Platform plugin must be installed AND activated
- Check Plugins page for "BuddyBoss Platform"

### Claude Desktop shows "Server disconnected"
- Ensure `mcp-remote` is installed: `npx mcp-remote --version`
- Check Node.js is installed: `node --version`
- Verify the URL is accessible from your machine
- Check firewall/VPN settings

### Tools return empty results
- Check if the BuddyBoss component is active (e.g., Activity, Groups)
- Go to BuddyBoss → Settings → Components
- Ensure the relevant component is enabled

---

## Security Best Practices

1. **Always use HTTPS** — Application Passwords transmit in plain text over HTTP
2. **Use a dedicated admin account** — Don't use your primary admin account
3. **One password per IDE** — Create separate Application Passwords for each tool
4. **Revoke unused passwords** — Remove passwords for tools you no longer use
5. **Monitor access** — Check WordPress access logs for MCP endpoint usage
6. **Restrict by IP** — Consider a security plugin to limit REST API access by IP
