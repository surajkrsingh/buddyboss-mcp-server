# Moderation Tools (4 tools)

**Phase:** BuddyBoss (toggleable)
**BuddyBoss API:** `/buddyboss/v1/moderation`

---

## buddyboss_list_moderation_reports

List reported content.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Reports per page. Default: 20. |

---

## buddyboss_report_content

Report content or a user.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `content_id` | integer | **Yes** | ID of the content to report. |
| `content_type` | string | **Yes** | Type of content: `activity`, `user`, `group`, `forum`, `topic`, `reply`. |
| `reason` | string | No | Reason for reporting. |

---

## buddyboss_block_member

Block a member.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | **Yes** | The user ID to block. |

---

## buddyboss_unblock_member

Unblock a member.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | **Yes** | The user ID to unblock. |
