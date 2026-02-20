# Documents Tools (5 tools)

**Phase:** BuddyBoss (toggleable)
**BuddyBoss API:** `/buddyboss/v1/document`

---

## buddyboss_list_documents

List documents with folder and group filters.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Documents per page. Default: 20. |
| `user_id` | integer | No | Filter by user ID. |
| `folder_id` | integer | No | Filter by folder ID. |
| `group_id` | integer | No | Filter by group ID. |
| `privacy` | string | No | Filter by privacy level. |

---

## buddyboss_get_document

Get document by ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The document ID. |

---

## buddyboss_upload_document

Upload a document.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `file` | string | **Yes** | Base64-encoded file data or URL. |
| `folder_id` | integer | No | Folder to upload the document to. |
| `group_id` | integer | No | Group to associate document with. |
| `privacy` | string | No | Privacy level. |
| `content` | string | No | Document description. |

---

## buddyboss_delete_document

Delete a document.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The document ID. |

---

## buddyboss_list_document_folders

List document folders.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | No | Filter by user ID. |
| `group_id` | integer | No | Filter by group ID. |
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Folders per page. Default: 20. |
