# Members Tools (5 tools)

**Phase:** Core (always enabled)
**BuddyBoss API:** `/buddyboss/v1/members`

---

## buddyboss_list_members

List members with filters.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Members per page. Default: 20, max: 100. |
| `search` | string | No | Search by name. |
| `role` | string | No | Filter by role. |
| `member_type` | string | No | Filter by member type. |
| `exclude` | string | No | Comma-separated user IDs to exclude. |

---

## buddyboss_get_member

Get detailed member profile by ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The member/user ID. |

---

## buddyboss_create_member

Create a new member account.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `username` | string | **Yes** | Login username. |
| `email` | string | **Yes** | Email address. |
| `password` | string | **Yes** | Account password. |
| `name` | string | No | Display name. |

---

## buddyboss_update_member

Update member profile details.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The member/user ID. |
| `name` | string | No | New display name. |
| `email` | string | No | New email address. |
| `member_type` | string | No | New member type. |

---

## buddyboss_delete_member

Delete a member account.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The member/user ID. |
| `reassign` | integer | No | User ID to reassign posts to. |
