# Messages Tools (5 tools)

**Phase:** Core (always enabled)
**BuddyBoss API:** `/buddyboss/v1/messages`

---

## buddyboss_list_message_threads

List message threads (inbox, sentbox, starred).

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | No | Filter by user ID. |
| `box` | string | No | Mailbox: `inbox`, `sentbox`, `starred`. |
| `type` | string | No | Filter type: `all`, `read`, `unread`. |
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Threads per page. Default: 20. |

---

## buddyboss_get_message_thread

Get a thread with all its messages.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The thread ID. |

---

## buddyboss_send_message

Send a new message to one or more recipients.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `recipients` | string | **Yes** | Comma-separated user IDs. |
| `subject` | string | **Yes** | Message subject. |
| `message` | string | **Yes** | Message body text. |
| `sender_id` | integer | No | Sender user ID. Defaults to authenticated user. |

---

## buddyboss_delete_message_thread

Delete a message thread.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The thread ID. |

---

## buddyboss_mark_message_read

Mark a thread as read.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The thread ID. |
