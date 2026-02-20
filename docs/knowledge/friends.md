# Friends Tools (4 tools)

**Phase:** Core (always enabled)
**BuddyBoss API:** `/buddyboss/v1/friends`

---

## buddyboss_list_friends

List friendships for a user.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | No | Filter by user ID. |
| `is_confirmed` | boolean | No | Filter by confirmation status. |
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Friends per page. Default: 20. |

---

## buddyboss_add_friend

Send a friendship request (with optional auto-accept).

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `initiator_id` | integer | **Yes** | User ID of the person sending the request. |
| `friend_id` | integer | **Yes** | User ID of the person receiving the request. |
| `force` | boolean | No | If true, auto-accept the friendship. |

---

## buddyboss_remove_friend

Remove a friendship.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The friendship ID. |

---

## buddyboss_list_friend_requests

List pending friendship requests.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | **Yes** | User ID to list requests for. |
| `is_confirmed` | boolean | No | Filter by confirmation status. Default: false (pending only). |
