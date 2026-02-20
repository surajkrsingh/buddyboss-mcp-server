# BuddyBoss MCP Server — Tool Reference

Complete list of 65 MCP tools organized by component and phase.

---

## Naming Convention

All tools follow: `buddyboss_{verb}_{resource}`

| Verb | Meaning |
|------|---------|
| `list` | Get collection with pagination/filtering |
| `get` | Get single item by ID |
| `create` | Create new item |
| `update` | Update existing item |
| `delete` | Delete item |
| `add` | Add relationship (e.g., add member to group) |
| `remove` | Remove relationship |

---

## Phase 1: Core Tools (37 tools) — Always Enabled

### Members (5 tools)

**BuddyBoss API:** `/buddyboss/v1/members`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_members` | List members with filters | `page` (int), `per_page` (int, default 20, max 100), `search` (string), `role` (string), `member_type` (string), `exclude` (string, comma-separated IDs) |
| `buddyboss_get_member` | Get member by ID | `id` (int, **required**) |
| `buddyboss_create_member` | Create new member | `username` (string, **required**), `email` (string, **required**), `password` (string, **required**), `name` (string) |
| `buddyboss_update_member` | Update member profile | `id` (int, **required**), `name` (string), `email` (string), `member_type` (string) |
| `buddyboss_delete_member` | Delete member account | `id` (int, **required**), `reassign` (int, user ID to reassign posts to) |

---

### Groups (8 tools)

**BuddyBoss API:** `/buddyboss/v1/groups`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_groups` | List groups with filters | `page` (int), `per_page` (int), `search` (string), `status` (string: public/private/hidden), `user_id` (int, groups user belongs to), `group_type` (string) |
| `buddyboss_get_group` | Get group by ID | `id` (int, **required**) |
| `buddyboss_create_group` | Create new group | `name` (string, **required**), `description` (string), `status` (string: public/private/hidden), `enable_forum` (bool), `creator_id` (int) |
| `buddyboss_update_group` | Update group | `id` (int, **required**), `name` (string), `description` (string), `status` (string) |
| `buddyboss_delete_group` | Delete group | `id` (int, **required**) |
| `buddyboss_list_group_members` | List members of a group | `group_id` (int, **required**), `page` (int), `per_page` (int), `roles` (string: admin/mod/member/banned) |
| `buddyboss_add_group_member` | Add member to group | `group_id` (int, **required**), `user_id` (int, **required**), `role` (string: admin/mod/member) |
| `buddyboss_remove_group_member` | Remove member from group | `group_id` (int, **required**), `user_id` (int, **required**) |

---

### Activity (6 tools)

**BuddyBoss API:** `/buddyboss/v1/activity`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_activities` | List activity feed | `page` (int), `per_page` (int), `search` (string), `user_id` (int), `group_id` (int), `component` (string), `type` (string), `scope` (string: just-me/friends/groups/favorites), `display_comments` (string: threaded/stream/false) |
| `buddyboss_get_activity` | Get activity by ID | `id` (int, **required**) |
| `buddyboss_create_activity` | Create activity post | `content` (string, **required**), `user_id` (int), `component` (string: activity/groups), `type` (string: activity_update/activity_comment), `primary_item_id` (int, group_id for group activity) |
| `buddyboss_update_activity` | Update activity post | `id` (int, **required**), `content` (string) |
| `buddyboss_delete_activity` | Delete activity post | `id` (int, **required**) |
| `buddyboss_favorite_activity` | Toggle favorite on activity | `id` (int, **required**) |

---

### Messages (5 tools)

**BuddyBoss API:** `/buddyboss/v1/messages`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_message_threads` | List message threads | `user_id` (int), `box` (string: inbox/sentbox/starred), `type` (string: all/read/unread), `page` (int), `per_page` (int) |
| `buddyboss_get_message_thread` | Get thread with messages | `id` (int, **required**, thread ID) |
| `buddyboss_send_message` | Send a new message | `recipients` (string, **required**, comma-separated user IDs), `subject` (string, **required**), `message` (string, **required**), `sender_id` (int) |
| `buddyboss_delete_message_thread` | Delete a thread | `id` (int, **required**, thread ID) |
| `buddyboss_mark_message_read` | Mark thread as read | `id` (int, **required**, thread ID) |

---

### Friends (4 tools)

**BuddyBoss API:** `/buddyboss/v1/friends`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_friends` | List friendships | `user_id` (int), `is_confirmed` (bool), `page` (int), `per_page` (int) |
| `buddyboss_add_friend` | Send friendship request | `initiator_id` (int, **required**), `friend_id` (int, **required**), `force` (bool, auto-accept) |
| `buddyboss_remove_friend` | Remove friendship | `id` (int, **required**, friendship ID) |
| `buddyboss_list_friend_requests` | List pending requests | `user_id` (int, **required**), `is_confirmed` (bool, default false) |

---

### Notifications (4 tools)

**BuddyBoss API:** `/buddyboss/v1/notifications`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_notifications` | List notifications | `user_id` (int), `is_new` (bool, unread only), `component_name` (string), `page` (int), `per_page` (int) |
| `buddyboss_get_notification` | Get notification by ID | `id` (int, **required**) |
| `buddyboss_mark_notification_read` | Mark as read/unread | `id` (int, **required**), `is_new` (bool, false = read) |
| `buddyboss_delete_notification` | Delete notification | `id` (int, **required**) |

---

### XProfile (5 tools)

**BuddyBoss API:** `/buddyboss/v1/xprofile`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_xprofile_groups` | List profile field groups | `fetch_fields` (bool, include fields in response) |
| `buddyboss_list_xprofile_fields` | List profile fields | `profile_group_id` (int), `fetch_field_data` (bool) |
| `buddyboss_get_xprofile_field` | Get field details | `id` (int, **required**), `fetch_field_data` (bool) |
| `buddyboss_get_xprofile_data` | Get user's field data | `field_id` (int, **required**), `user_id` (int, **required**) |
| `buddyboss_update_xprofile_data` | Update user's field data | `field_id` (int, **required**), `user_id` (int, **required**), `value` (string, **required**) |

---

## Phase 2: BuddyBoss Tools (18 tools) — Toggle-able

### Media / Photos (5 tools)

**BuddyBoss API:** `/buddyboss/v1/media`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_media` | List photos/media | `page` (int), `per_page` (int, max 10), `user_id` (int), `album_id` (int), `group_id` (int), `privacy` (string: public/loggedin/onlyme/friends/grouponly) |
| `buddyboss_get_media` | Get media by ID | `id` (int, **required**) |
| `buddyboss_upload_media` | Upload photo | `file` (string, **required**, base64 or URL), `activity_id` (int), `album_id` (int), `group_id` (int), `privacy` (string), `content` (string, caption) |
| `buddyboss_update_media` | Update media metadata | `id` (int, **required**), `privacy` (string), `album_id` (int), `content` (string) |
| `buddyboss_delete_media` | Delete media | `id` (int, **required**) |

---

### Video (4 tools)

**BuddyBoss API:** `/buddyboss/v1/video`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_videos` | List videos | `page` (int), `per_page` (int), `user_id` (int), `group_id` (int), `activity_id` (int), `privacy` (string) |
| `buddyboss_get_video` | Get video by ID | `id` (int, **required**) |
| `buddyboss_upload_video` | Upload video | `file` (string, **required**), `activity_id` (int), `group_id` (int), `privacy` (string), `content` (string) |
| `buddyboss_delete_video` | Delete video | `id` (int, **required**) |

---

### Documents (5 tools)

**BuddyBoss API:** `/buddyboss/v1/document`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_documents` | List documents | `page` (int), `per_page` (int), `user_id` (int), `folder_id` (int), `group_id` (int), `privacy` (string) |
| `buddyboss_get_document` | Get document by ID | `id` (int, **required**) |
| `buddyboss_upload_document` | Upload document | `file` (string, **required**), `folder_id` (int), `group_id` (int), `privacy` (string), `content` (string) |
| `buddyboss_delete_document` | Delete document | `id` (int, **required**) |
| `buddyboss_list_document_folders` | List document folders | `user_id` (int), `group_id` (int), `page` (int), `per_page` (int) |

---

### Moderation (4 tools)

**BuddyBoss API:** `/buddyboss/v1/moderation`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_moderation_reports` | List reported content | `page` (int), `per_page` (int) |
| `buddyboss_report_content` | Report content or user | `content_id` (int, **required**), `content_type` (string, **required**: activity/user/group/forum/topic/reply), `reason` (string) |
| `buddyboss_block_member` | Block a member | `user_id` (int, **required**) |
| `buddyboss_unblock_member` | Unblock a member | `user_id` (int, **required**) |

---

## Phase 3: Advanced Tools (10 tools) — Toggle-able

### Forums (6 tools)

**BuddyBoss API:** `/buddyboss/v1/forums`, `/buddyboss/v1/topics`, `/buddyboss/v1/reply`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_forums` | List forums | `page` (int), `per_page` (int), `search` (string), `parent` (int) |
| `buddyboss_get_forum` | Get forum by ID | `id` (int, **required**) |
| `buddyboss_create_topic` | Create forum topic | `forum_id` (int, **required**), `title` (string, **required**), `content` (string, **required**), `author_id` (int) |
| `buddyboss_get_topic` | Get topic by ID | `id` (int, **required**) |
| `buddyboss_create_reply` | Reply to topic | `topic_id` (int, **required**), `content` (string, **required**), `author_id` (int), `reply_to` (int, parent reply ID) |
| `buddyboss_list_replies` | List topic replies | `topic_id` (int, **required**), `page` (int), `per_page` (int) |

---

### LearnDash Integration (4 tools)

**BuddyBoss API:** `/buddyboss/v1/learndash`

| Tool Name | Description | Parameters |
|-----------|-------------|------------|
| `buddyboss_list_courses` | List LearnDash courses | `page` (int), `per_page` (int), `search` (string), `category` (int) |
| `buddyboss_get_course` | Get course details | `id` (int, **required**) |
| `buddyboss_enroll_user` | Enroll user in course | `course_id` (int, **required**), `user_id` (int, **required**) |
| `buddyboss_get_course_progress` | Get user's course progress | `course_id` (int, **required**), `user_id` (int, **required**) |

---

## Future Tools (Not in Initial Release)

These tools could be added in future versions based on demand:

| Component | Potential Tools | BuddyBoss API |
|-----------|----------------|---------------|
| **Reactions** | add_reaction, remove_reaction, list_reactions | `/buddyboss/v1/reactions` |
| **Invites** | send_invite, list_invites, delete_invite | `/buddyboss/v1/invites` |
| **Polls** (Pro) | create_poll, get_poll, vote, delete_poll | `/buddyboss/v1/poll` |
| **Subscriptions** | subscribe, unsubscribe, list_subscriptions | `/buddyboss/v1/subscriptions` |
| **Avatars** | get_avatar, upload_avatar, delete_avatar | `/buddyboss/v1/members/{id}/avatar` |
| **Covers** | get_cover, upload_cover, delete_cover | `/buddyboss/v1/members/{id}/cover` |
| **Group Invites** | send_group_invite, list_group_invites | `/buddyboss/v1/groups/{id}/invites` |
| **Group Settings** | get_group_settings, update_group_settings | `/buddyboss/v1/groups/{id}/settings` |
| **Zoom** | create_meeting, list_meetings | TBD |
| **Components** | list_components, toggle_component | `/buddyboss/v1/components` |
| **Settings** | get_settings, update_settings | `/buddyboss/v1/settings` |
