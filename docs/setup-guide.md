# BuddyBoss MCP Server — Setup & Connection Guide

Complete guide to installing, configuring, and connecting the BuddyBoss MCP Server.

---

## Prerequisites

- **WordPress** 5.6+ (for Application Passwords)
- **BuddyBoss Platform** 2.0+ (must be installed and active)
- **PHP** 7.4+
- **HTTPS** enabled (required for Application Passwords; localhost is exempt)

---

## Step 1: Install & Activate the Plugin

1. Download `buddyboss-mcp-server.zip`
2. Go to **WordPress Admin > Plugins > Add New > Upload Plugin**
3. Upload the ZIP file, click **Install**, then **Activate**
4. Verify: **"MCP Server"** menu appears in the admin sidebar

---

## Step 2: Create an Application Password

1. Go to **WordPress Admin > Users > Your Profile**
2. Scroll to the **"Application Passwords"** section
3. Enter a name: `Claude MCP` (or any descriptive label)
4. Click **"Add New Application Password"**
5. **Copy the generated password immediately** — it is shown only once
   - Format: `xxxx xxxx xxxx xxxx xxxx xxxx`
   - This is NOT your WordPress login password

---

## Step 3: Generate Base64 Credentials

Open a terminal and run:

```bash
echo -n "YOUR_USERNAME:xxxx xxxx xxxx xxxx xxxx xxxx" | base64
```

Replace `YOUR_USERNAME` with your WordPress username and the `xxxx` portion with the Application Password from Step 2. This outputs a string like `eW91ci11c2VybmFtZTp4eHh4...` — you'll need this for the config below.

---

## Step 4: Add Connection Config

### Claude Code (CLI)

Create or edit `.mcp.json` in your project root (or `~/.claude.json` for global access):

```json
{
  "mcpServers": {
    "buddyboss": {
      "type": "http",
      "url": "https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp",
      "headers": {
        "Authorization": "Basic YOUR_BASE64_CREDENTIALS"
      }
    }
  }
}
```

Replace `yoursite.com` with your WordPress site URL and `YOUR_BASE64_CREDENTIALS` with the value from Step 3.

### WordPress Admin (Quick Copy)

You can also get a pre-filled config snippet from the plugin dashboard:

1. Go to **WordPress Admin > MCP Server**
2. Copy the config snippet shown for your IDE
3. Only the credentials need to be replaced

---

## Step 5: Verify the Connection

### Option A: From WordPress Admin

1. Go to **MCP Server** in the admin sidebar
2. Click the **"Test Connection"** button
3. A green checkmark confirms everything is working

### Option B: From Terminal (curl)

```bash
# Test initialize handshake
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "YOUR_USERNAME:YOUR_APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{"protocolVersion":"2025-11-25","capabilities":{},"clientInfo":{"name":"test","version":"1.0"}},"id":1}' \
  | python3 -m json.tool
```

Expected response:
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "protocolVersion": "2025-11-25",
    "capabilities": { "tools": {} },
    "serverInfo": { "name": "buddyboss-mcp-server", "version": "1.0.0" }
  }
}
```

```bash
# List all available tools
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "YOUR_USERNAME:YOUR_APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":2}' \
  | python3 -m json.tool
```

```bash
# Call a tool (list 3 members)
curl -s -X POST https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp \
  -u "YOUR_USERNAME:YOUR_APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"buddyboss_list_members","arguments":{"per_page":3}},"id":3}' \
  | python3 -m json.tool
```

### Option C: MCP Inspector

```bash
npx @modelcontextprotocol/inspector https://yoursite.com/wp-json/buddyboss-mcp/v1/mcp
```

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

## Security Best Practices

1. **Always use HTTPS** — Application Passwords transmit credentials in Base64 (not encrypted)
2. **Use a dedicated admin account** — Don't use your primary admin credentials
3. **One password per IDE** — Create separate Application Passwords for each tool
4. **Revoke unused passwords** — Remove passwords for tools you no longer use
5. **Monitor access** — Check WordPress access logs for MCP endpoint usage
6. **Restrict by IP** — Consider a security plugin to limit REST API access by IP
