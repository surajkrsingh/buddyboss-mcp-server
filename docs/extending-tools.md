# Extending BuddyBoss MCP Server — Custom Tools Guide

Add your own MCP tools to expose any WordPress or BuddyBoss functionality to AI assistants.

---

## How It Works

The plugin uses a filter-based architecture. All tools extend `Tool_Base`, which provides:

- `$this->rest_client` — Internal REST client (`get`, `post`, `put`, `patch`, `delete`, `format_response`)
- `$this->create_tool()` — Helper to build MCP tool definitions
- `$this->validate_required()` — Parameter validation
- `$this->get_int()`, `$this->get_string()`, `$this->get_bool()` — Type-safe parameter extraction

---

## Quick Start

### 1. Create Your Tool Class

Create a file (e.g., in your theme or a custom plugin):

```php
<?php
namespace BuddyBossMCP\Tools;

class My_Custom_Tools extends Tool_Base {

    public function register_tools() {
        return array(
            $this->create_tool(
                'buddyboss_site_stats',
                'Get site statistics including total users, groups, and posts.',
                array(), // No parameters needed
                'get_site_stats'
            ),
        );
    }

    public function get_site_stats( $args, $user_id ) {
        $stats = array(
            'users'  => count_users(),
            'posts'  => wp_count_posts()->publish,
            'groups' => bp_get_total_group_count(),
        );

        return wp_json_encode( $stats, JSON_PRETTY_PRINT );
    }
}
```

### 2. Register It via Filter

In your theme's `functions.php` or a custom plugin:

```php
add_filter( 'bbmcp_tool_providers', function( $providers ) {
    require_once __DIR__ . '/class-my-custom-tools.php';
    $providers[] = new \BuddyBossMCP\Tools\My_Custom_Tools();
    return $providers;
} );
```

That's it. Your tool will appear in `tools/list` and be callable via `tools/call`.

---

## Detailed Example: WordPress Posts Tool

A complete example with multiple tools, required parameters, and different HTTP methods:

```php
<?php
namespace BuddyBossMCP\Tools;

class Posts_Tools extends Tool_Base {

    /**
     * Register all post-related tools.
     */
    public function register_tools() {
        return array(

            // List posts with filtering
            $this->create_tool(
                'buddyboss_list_posts',
                'List WordPress posts with optional filtering by status, category, or search query.',
                array(
                    'status'   => array(
                        'type'        => 'string',
                        'description' => 'Post status: publish, draft, pending. Default: publish.',
                    ),
                    'category' => array(
                        'type'        => 'string',
                        'description' => 'Category slug to filter by.',
                    ),
                    'search'   => array(
                        'type'        => 'string',
                        'description' => 'Search posts by keyword.',
                    ),
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => 'Results per page. Default: 10, max: 100.',
                    ),
                    'page'     => array(
                        'type'        => 'integer',
                        'description' => 'Page number. Default: 1.',
                    ),
                ),
                'list_posts'
            ),

            // Get a single post
            $this->create_tool(
                'buddyboss_get_post',
                'Get a single WordPress post by ID.',
                array(
                    'id' => array(
                        'type'        => 'integer',
                        'description' => 'The post ID.',
                    ),
                ),
                'get_post',
                array( 'id' ) // Required parameters
            ),

            // Create a new post
            $this->create_tool(
                'buddyboss_create_post',
                'Create a new WordPress post.',
                array(
                    'title'   => array(
                        'type'        => 'string',
                        'description' => 'Post title.',
                    ),
                    'content' => array(
                        'type'        => 'string',
                        'description' => 'Post content (HTML allowed).',
                    ),
                    'status'  => array(
                        'type'        => 'string',
                        'description' => 'Post status: publish, draft. Default: draft.',
                    ),
                ),
                'create_post',
                array( 'title', 'content' )
            ),

            // Delete a post
            $this->create_tool(
                'buddyboss_delete_post',
                'Delete a WordPress post by ID. Moves to trash by default.',
                array(
                    'id' => array(
                        'type'        => 'integer',
                        'description' => 'The post ID to delete.',
                    ),
                ),
                'delete_post',
                array( 'id' )
            ),
        );
    }

    /**
     * List posts.
     */
    public function list_posts( $args, $user_id ) {
        $params = array(
            'status'   => $this->get_string( $args, 'status', 'publish' ),
            'per_page' => min( $this->get_int( $args, 'per_page', 10 ), 100 ),
            'page'     => $this->get_int( $args, 'page', 1 ),
        );

        $category = $this->get_string( $args, 'category' );
        if ( $category ) {
            $params['categories'] = $category;
        }

        $search = $this->get_string( $args, 'search' );
        if ( $search ) {
            $params['search'] = $search;
        }

        $response = $this->rest_client->get( '/wp/v2/posts', $params, $user_id );

        return $this->rest_client->format_response( $response );
    }

    /**
     * Get a single post.
     */
    public function get_post( $args, $user_id ) {
        $this->validate_required( $args, array( 'id' ) );

        $post_id  = absint( $args['id'] );
        $response = $this->rest_client->get( '/wp/v2/posts/' . $post_id, array(), $user_id );

        return $this->rest_client->format_response( $response );
    }

    /**
     * Create a post.
     */
    public function create_post( $args, $user_id ) {
        $this->validate_required( $args, array( 'title', 'content' ) );

        $params = array(
            'title'   => sanitize_text_field( $args['title'] ),
            'content' => wp_kses_post( $args['content'] ),
            'status'  => $this->get_string( $args, 'status', 'draft' ),
        );

        $response = $this->rest_client->post( '/wp/v2/posts', $params, $user_id );

        return $this->rest_client->format_response( $response );
    }

    /**
     * Delete a post.
     */
    public function delete_post( $args, $user_id ) {
        $this->validate_required( $args, array( 'id' ) );

        $post_id  = absint( $args['id'] );
        $response = $this->rest_client->delete( '/wp/v2/posts/' . $post_id, array(), $user_id );

        return $this->rest_client->format_response( $response );
    }
}
```

Register it:

```php
add_filter( 'bbmcp_tool_providers', function( $providers ) {
    require_once __DIR__ . '/class-posts-tools.php';
    $providers[] = new \BuddyBossMCP\Tools\Posts_Tools();
    return $providers;
} );
```

---

## API Reference

### `Tool_Base` — Abstract Base Class

Every tool provider must extend this class and implement `register_tools()`.

#### `create_tool( $name, $description, $properties, $method, $required = array() )`

Builds a tool definition array.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | string | Unique tool name (convention: `buddyboss_{verb}_{resource}`) |
| `$description` | string | Human-readable description — this is what the AI reads to decide when to use the tool |
| `$properties` | array | JSON Schema properties for `inputSchema` (parameter definitions) |
| `$method` | string | Method name on your class to call when tool is invoked |
| `$required` | array | Parameter names that must be provided (optional) |

#### `validate_required( $args, $required )`

Throws `\InvalidArgumentException` if any required parameter is missing or empty.

#### Parameter Helpers

| Method | Returns | Description |
|--------|---------|-------------|
| `get_int( $args, $key, $default = 0 )` | `int` | Sanitized integer via `absint()` |
| `get_string( $args, $key, $default = '' )` | `string` | Sanitized string via `sanitize_text_field()` |
| `get_bool( $args, $key, $default = false )` | `bool` | Boolean via `filter_var( FILTER_VALIDATE_BOOLEAN )` |

### `Internal_REST_Client` — REST API Bridge

Available via `$this->rest_client` in all tool providers. Calls WordPress REST API internally with zero HTTP overhead.

| Method | Description |
|--------|-------------|
| `get( $route, $params, $user_id )` | GET request (params become query string) |
| `post( $route, $params, $user_id )` | POST request (params become body) |
| `put( $route, $params, $user_id )` | PUT request |
| `patch( $route, $params, $user_id )` | PATCH request |
| `delete( $route, $params, $user_id )` | DELETE request |
| `format_response( $data )` | Converts response/WP_Error to JSON string |

All methods return `array` on success or `\WP_Error` on failure. Always pass the result through `format_response()` before returning from your tool handler.

---

## JSON Schema Property Types

Use these types in the `$properties` array for `create_tool()`:

```php
// String parameter
'name' => array(
    'type'        => 'string',
    'description' => 'The group name.',
),

// Integer parameter
'per_page' => array(
    'type'        => 'integer',
    'description' => 'Results per page. Default: 20.',
),

// Boolean parameter
'force' => array(
    'type'        => 'boolean',
    'description' => 'Force delete without trash. Default: false.',
),

// Enum (string with fixed options)
'status' => array(
    'type'        => 'string',
    'description' => 'Filter by status. Options: public, private, hidden.',
    'enum'        => array( 'public', 'private', 'hidden' ),
),
```

---

## Tips

- **Tool naming:** Follow the `buddyboss_{verb}_{resource}` convention so tools group logically
- **Descriptions matter:** The AI reads descriptions to decide which tool to use — be specific and clear
- **Use `format_response()`:** Always return via `$this->rest_client->format_response()` for consistent error handling
- **Any REST endpoint works:** You can call `/wp/v2/*`, `/buddyboss/v1/*`, `/wc/v3/*`, or any registered REST route
- **Sanitize inputs:** Use the `get_int()`, `get_string()`, `get_bool()` helpers — they sanitize automatically
- **Validate required params:** Call `validate_required()` at the start of every handler that has required params
