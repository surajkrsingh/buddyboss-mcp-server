# Notifications Tools (4 tools)

**Phase:** Core (always enabled)
**BuddyBoss API:** `/buddyboss/v1/notifications`

---

## buddyboss_list_notifications

List notifications with filters.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | No | Filter by user ID. |
| `is_new` | boolean | No | If true, only unread notifications. |
| `component_name` | string | No | Filter by component name. |
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Notifications per page. Default: 20. |

---

## buddyboss_get_notification

Get notification by ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The notification ID. |

---

## buddyboss_mark_notification_read

Mark notification as read or unread.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The notification ID. |
| `is_new` | boolean | No | Set to false for read, true for unread. |

---

## buddyboss_delete_notification

Delete a notification.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The notification ID. |
