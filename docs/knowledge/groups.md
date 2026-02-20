# Groups Tools (8 tools)

**Phase:** Core (always enabled)
**BuddyBoss API:** `/buddyboss/v1/groups`

---

## buddyboss_list_groups

List groups with filtering by status, search, user, or type.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Groups per page. Default: 20, max: 100. |
| `search` | string | No | Search groups by name or description. |
| `status` | string | No | Filter by status: `public`, `private`, `hidden`. |
| `user_id` | integer | No | Only groups this user belongs to. |
| `group_type` | string | No | Filter by group type slug. |

---

## buddyboss_get_group

Get group details by ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The group ID. |

---

## buddyboss_create_group

Create a new group (public, private, or hidden).

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Group name. |
| `description` | string | No | Group description. |
| `status` | string | No | Privacy status: `public`, `private`, `hidden`. Default: public. |
| `enable_forum` | boolean | No | Whether to enable a discussion forum. Default: false. |
| `creator_id` | integer | No | User ID of group creator. Defaults to authenticated user. |

---

## buddyboss_update_group

Update group name, description, or status.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The group ID to update. |
| `name` | string | No | New group name. |
| `description` | string | No | New group description. |
| `status` | string | No | New status: `public`, `private`, `hidden`. |

---

## buddyboss_delete_group

Permanently delete a group and all its data (members, activity, media). Cannot be undone.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The group ID to delete. |

---

## buddyboss_list_group_members

List members of a specific group. Can filter by role.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `group_id` | integer | **Yes** | The group ID. |
| `page` | integer | No | Page number. Default: 1. |
| `per_page` | integer | No | Members per page. Default: 20. |
| `roles` | string | No | Filter by role: `admin`, `mod`, `member`, `banned`. Comma-separated for multiple. |

---

## buddyboss_add_group_member

Add a user to a group with a specific role.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `group_id` | integer | **Yes** | The group ID. |
| `user_id` | integer | **Yes** | The user ID to add. |
| `role` | string | No | Member role: `admin`, `mod`, `member`. Default: member. |

---

## buddyboss_remove_group_member

Remove a user from a group.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `group_id` | integer | **Yes** | The group ID. |
| `user_id` | integer | **Yes** | The user ID to remove. |
