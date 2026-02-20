# Activity Tools (6 tools)

**Phase:** Core (always enabled)
**BuddyBoss API:** `/buddyboss/v1/activity`

---

## buddyboss_list_activities

List activity feed with optional filtering by user, group, component, type, or scope.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Activities per page. Default: 20, max: 100. |
| `search` | string | No | Search activities by content. |
| `user_id` | integer | No | Filter by user ID. |
| `group_id` | integer | No | Filter by group ID. |
| `component` | string | No | Filter by component (e.g., `activity`, `groups`, `friends`). |
| `type` | string | No | Filter by activity type (e.g., `activity_update`, `activity_comment`). |
| `scope` | string | No | Activity scope: `just-me`, `friends`, `groups`, `favorites`. |
| `display_comments` | string | No | How to display comments: `threaded`, `stream`, `false`. |

---

## buddyboss_get_activity

Get detailed information about a specific activity post by its ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The activity ID. |

---

## buddyboss_create_activity

Create a new activity post. Can be a user activity update or a group activity post.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `content` | string | **Yes** | The activity content/text. |
| `user_id` | integer | No | User ID of the activity author. Defaults to authenticated user. |
| `component` | string | No | Activity component: `activity`, `groups`. Default: activity. |
| `type` | string | No | Activity type: `activity_update`, `activity_comment`. Default: activity_update. |
| `primary_item_id` | integer | No | Primary item ID (e.g., group_id for group activity). |

---

## buddyboss_update_activity

Update the content of an existing activity post.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The activity ID to update. |
| `content` | string | No | New activity content. |

---

## buddyboss_delete_activity

Permanently delete an activity post. Cannot be undone.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The activity ID to delete. |

---

## buddyboss_favorite_activity

Toggle favorite status on an activity post. If already favorited, it will be unfavorited.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The activity ID to favorite/unfavorite. |
