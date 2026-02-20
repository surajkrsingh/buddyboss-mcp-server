# Forums Tools (6 tools)

**Phase:** Advanced (toggleable)
**BuddyBoss API:** `/buddyboss/v1/forums`, `/buddyboss/v1/topics`, `/buddyboss/v1/reply`

---

## buddyboss_list_forums

List forums.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Forums per page. Default: 20. |
| `search` | string | No | Search forums by title. |
| `parent` | integer | No | Filter by parent forum ID. |

---

## buddyboss_get_forum

Get forum by ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The forum ID. |

---

## buddyboss_create_topic

Create a new forum topic.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `forum_id` | integer | **Yes** | The forum to create the topic in. |
| `title` | string | **Yes** | Topic title. |
| `content` | string | **Yes** | Topic content/body. |
| `author_id` | integer | No | Author user ID. Defaults to authenticated user. |

---

## buddyboss_get_topic

Get topic by ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The topic ID. |

---

## buddyboss_create_reply

Reply to a topic.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `topic_id` | integer | **Yes** | The topic to reply to. |
| `content` | string | **Yes** | Reply content/body. |
| `author_id` | integer | No | Author user ID. Defaults to authenticated user. |
| `reply_to` | integer | No | Parent reply ID for nested replies. |

---

## buddyboss_list_replies

List replies in a topic.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `topic_id` | integer | **Yes** | The topic ID. |
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Replies per page. Default: 20. |
