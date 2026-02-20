# Media / Photos Tools (5 tools)

**Phase:** BuddyBoss (toggleable)
**BuddyBoss API:** `/buddyboss/v1/media`

---

## buddyboss_list_media

List photos/media with privacy and album filters.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Media per page. Default: 20, max: 10. |
| `user_id` | integer | No | Filter by user ID. |
| `album_id` | integer | No | Filter by album ID. |
| `group_id` | integer | No | Filter by group ID. |
| `privacy` | string | No | Filter by privacy: `public`, `loggedin`, `onlyme`, `friends`, `grouponly`. |

---

## buddyboss_get_media

Get media by ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The media ID. |

---

## buddyboss_upload_media

Upload a photo.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `file` | string | **Yes** | Base64-encoded file data or URL. |
| `activity_id` | integer | No | Associated activity ID. |
| `album_id` | integer | No | Album to add the media to. |
| `group_id` | integer | No | Group to associate media with. |
| `privacy` | string | No | Privacy level: `public`, `loggedin`, `onlyme`, `friends`, `grouponly`. |
| `content` | string | No | Caption text. |

---

## buddyboss_update_media

Update media metadata or privacy.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The media ID. |
| `privacy` | string | No | New privacy level. |
| `album_id` | integer | No | Move to a different album. |
| `content` | string | No | New caption text. |

---

## buddyboss_delete_media

Delete media.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The media ID. |
