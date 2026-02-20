# Video Tools (4 tools)

**Phase:** BuddyBoss (toggleable)
**BuddyBoss API:** `/buddyboss/v1/video`

---

## buddyboss_list_videos

List videos.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Videos per page. Default: 20. |
| `user_id` | integer | No | Filter by user ID. |
| `group_id` | integer | No | Filter by group ID. |
| `activity_id` | integer | No | Filter by activity ID. |
| `privacy` | string | No | Filter by privacy level. |

---

## buddyboss_get_video

Get video by ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The video ID. |

---

## buddyboss_upload_video

Upload a video.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `file` | string | **Yes** | Base64-encoded file data or URL. |
| `activity_id` | integer | No | Associated activity ID. |
| `group_id` | integer | No | Group to associate video with. |
| `privacy` | string | No | Privacy level. |
| `content` | string | No | Caption text. |

---

## buddyboss_delete_video

Delete a video.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The video ID. |
