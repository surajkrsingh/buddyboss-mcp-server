# BuddyBoss REST API — Research Findings

Research conducted February 2026. Based on inspection of the actual BuddyBoss Platform plugin installed locally.

---

## API Namespace

BuddyBoss uses: **`/wp-json/buddyboss/v1/`**

Discovery endpoint: `GET /wp-json/buddyboss/v1/` returns full route map.

---

## Complete Endpoint Inventory

Found **62 REST API endpoint classes** across BuddyBoss Platform + BuddyBoss Pro plugins.

### Source Locations

- **Platform:** `wp-content/plugins/buddyboss-platform/bp-*/classes/class-bp-rest-*-endpoint.php`
- **Pro:** `wp-content/plugins/buddyboss-platform-pro/includes/*/classes/class-bb-rest-*-endpoint.php`

---

## Endpoint Classes by Component

### Members (6 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Members_Endpoint` | `/members`, `/members/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Members_Details_Endpoint` | `/members/details` | GET |
| `BP_REST_Members_Actions_Endpoint` | `/members/me/block`, `/members/me/unblock` | POST |
| `BP_REST_Members_Permissions_Endpoint` | `/members/permissions` | GET |
| `BP_REST_Attachments_Member_Avatar_Endpoint` | `/members/{id}/avatar` | GET, POST, DELETE |
| `BP_REST_Attachments_Member_Cover_Endpoint` | `/members/{id}/cover` | GET, POST, DELETE |

### Groups (9 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Groups_Endpoint` | `/groups`, `/groups/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Groups_Details_Endpoint` | `/groups/details` | GET |
| `BP_REST_Groups_Types_Endpoint` | `/groups/types` | GET |
| `BP_REST_Group_Membership_Endpoint` | `/groups/{id}/members` | GET, POST, PUT, DELETE |
| `BP_REST_Group_Membership_Request_Endpoint` | `/groups/{id}/membership-requests` | GET, POST, PUT, DELETE |
| `BP_REST_Group_Invites_Endpoint` | `/groups/{id}/invites` | GET, POST, DELETE |
| `BP_REST_Group_Settings_Endpoint` | `/groups/{id}/settings` | GET, PUT |
| `BP_REST_Attachments_Group_Avatar_Endpoint` | `/groups/{id}/avatar` | GET, POST, DELETE |
| `BP_REST_Attachments_Group_Cover_Endpoint` | `/groups/{id}/cover` | GET, POST, DELETE |

### Activity (4 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Activity_Endpoint` | `/activity`, `/activity/{id}`, `/activity/{id}/favorite`, `/activity/{id}/pin`, `/activity/{id}/close-comments` | GET, POST, PUT, DELETE |
| `BP_REST_Activity_Comment_Endpoint` | `/activity/{id}/comment` | GET, POST, PUT, DELETE |
| `BP_REST_Activity_Details_Endpoint` | `/activity/details` | GET |
| `BP_REST_Activity_Link_Preview_Endpoint` | `/activity/link-preview` | GET, POST |

### Messages (3 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Messages_Endpoint` | `/messages`, `/messages/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Messages_Actions_Endpoint` | `/messages/{id}/star`, `/messages/{id}/read`, `/messages/{id}/unread` | PUT |
| `BP_REST_Group_Messages_Endpoint` | `/group-messages/{group_id}` | GET, POST |

### Friends (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Friends_Endpoint` | `/friends`, `/friends/{id}` | GET, POST, PUT, DELETE |

### Notifications (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Notifications_Endpoint` | `/notifications`, `/notifications/{id}` | GET, POST, PUT, DELETE |

### XProfile / Extended Profiles (7 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_XProfile_Field_Groups_Endpoint` | `/xprofile/groups` | GET, POST, PUT, DELETE |
| `BP_REST_XProfile_Fields_Endpoint` | `/xprofile/fields` | GET, POST, PUT, DELETE |
| `BP_REST_XProfile_Data_Endpoint` | `/xprofile/{field_id}/data/{user_id}` | GET, POST, PUT, DELETE |
| `BP_REST_XProfile_Update_Endpoint` | `/xprofile/update` | POST |
| `BP_REST_XProfile_Types_Endpoint` | `/xprofile/types` | GET |
| `BP_REST_XProfile_Repeater_Endpoint` | `/xprofile/repeater` | GET |
| `BP_REST_XProfile_Search_Form_Fields_Endpoint` | `/xprofile/search-form-fields` | GET |

### Media / Photos (3 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Media_Endpoint` | `/media`, `/media/{id}`, `/media/upload` | GET, POST, PUT, DELETE |
| `BP_REST_Media_Albums_Endpoint` | `/media/albums`, `/media/albums/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Media_Details_Endpoint` | `/media/details` | GET |

### Video (3 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Video_Endpoint` | `/video`, `/video/{id}`, `/video/upload` | GET, POST, PUT, DELETE |
| `BP_REST_Video_Details_Endpoint` | `/video/details` | GET |
| `BP_REST_Video_Poster_Endpoint` | `/video/poster` | POST |

### Documents (3 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Document_Endpoint` | `/document`, `/document/{id}`, `/document/upload` | GET, POST, PUT, DELETE |
| `BP_REST_Document_Folder_Endpoint` | `/document/folders`, `/document/folders/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Document_Details_Endpoint` | `/document/details` | GET |

### Forums — bbPress (6 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Forums_Endpoint` | `/forums`, `/forums/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Topics_Endpoint` | `/topics`, `/topics/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Topics_Actions_Endpoint` | `/topics/{id}/subscribe`, `/topics/{id}/favorite`, `/topics/{id}/stick` | PUT |
| `BP_REST_Reply_Endpoint` | `/reply`, `/reply/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Reply_Actions_Endpoint` | `/reply/{id}/subscribe` | PUT |
| `BB_REST_Forums_Link_Preview_Endpoint` | `/forums/link-preview` | POST |

### Moderation (2 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Moderation_Endpoint` | `/moderation`, `/moderation/{id}` | GET, POST, PUT, DELETE |
| `BP_REST_Moderation_Report_Endpoint` | `/moderation/report` | GET, POST |

### Reactions (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BB_REST_Reactions_Endpoint` | `/reactions`, `/user-reactions`, `/user-reactions/{id}` | GET, POST |

### Subscriptions (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BB_REST_Subscriptions_Endpoint` | `/subscriptions`, `/subscriptions/{id}` | GET, POST, DELETE |

### Invites (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Invites_Endpoint` | `/invites`, `/invites/{id}` | GET, POST, DELETE |

### LearnDash Integration (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_LearnDash_Courses_Endpoint` | `/learndash/courses`, `/learndash/courses/{id}` | GET |

### Settings (3 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Settings_Endpoint` | `/settings` | GET, PUT |
| `BP_REST_Account_Settings_Endpoint` | `/account-settings` | GET, PUT |
| `BP_REST_Account_Settings_Options_Endpoint` | `/account-settings/options` | GET |

### Signup (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Signup_Endpoint` | `/signup` | GET, POST |

### Mentions (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Mention_Endpoint` | `/mentions` | GET |

### Blogs (2 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Blogs_Endpoint` | `/blogs`, `/blogs/{id}` | GET |
| `BP_REST_Attachments_Blog_Avatar_Endpoint` | `/blogs/{id}/avatar` | GET, POST |

### Components (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BP_REST_Components_Endpoint` | `/components` | GET |

---

## BuddyBoss Pro Endpoints (5 classes)

### Polls (3 classes)

| Class | Route | Methods |
|-------|-------|---------|
| `BB_REST_Poll_Endpoint` | `/poll`, `/poll/{id}` | GET, POST, PUT, DELETE |
| `BB_REST_Poll_Option_Endpoint` | `/poll/option`, `/poll/option/{id}` | GET, POST, PUT, DELETE |
| `BB_REST_Poll_Option_Vote_Endpoint` | `/poll/vote`, `/poll/vote/{id}` | POST, DELETE |

### Pusher / Real-time (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BB_REST_Pusher_Endpoint` | `/pusher/data`, `/pusher/auth` | GET, PUT |

### Activity Feature Image (1 class)

| Class | Route | Methods |
|-------|-------|---------|
| `BB_REST_Activity_Post_Feature_Image_Endpoint` | `/activity/feature-image` | POST, DELETE |

---

## Privacy Levels

BuddyBoss extends WordPress privacy with these levels:

| Value | Meaning | Where Used |
|-------|---------|-----------|
| `public` | Everyone can see | Activity, Media, Video, Documents |
| `loggedin` | Logged-in users only | Activity, Media, Video, Documents |
| `onlyme` | Only the author | Activity, Media, Video, Documents |
| `friends` | Friends only | Activity, Media, Video, Documents |
| `grouponly` | Group members only | Group-scoped content |
| `message` | Message recipients only | Message attachments |

---

## Authentication Methods

| Method | Built-in | Notes |
|--------|----------|-------|
| Cookie / Nonce | Yes | Same-origin only (browser) |
| Application Passwords | Yes (WP 5.6+) | Basic Auth, HTTPS required |
| JWT | Plugin required | Token-based, popular for mobile |
| OAuth 2.0 | Plugin required | Enterprise use |

**Recommended for MCP:** Application Passwords — zero additional plugins, built into WordPress core.

---

## Key Differences from BuddyPress REST API

BuddyBoss adds these components not present in standard BuddyPress:

1. **Media/Albums** — Full photo management REST API
2. **Video** — Video upload and management
3. **Documents/Folders** — File sharing with folder organization
4. **Forums integration** — Deeper bbPress REST endpoints
5. **Moderation** — Block/report system
6. **Reactions** — Emoji reactions (beyond simple favorites)
7. **Invites** — Email invitation system
8. **Subscriptions** — Activity/topic subscriptions
9. **LearnDash** — LMS course integration
10. **Polls** (Pro) — Polling in activity feed
11. **Pusher** (Pro) — Real-time updates

---

## Reference: BuddyPress MCP (vapvarun)

The existing [BuddyPress MCP](https://github.com/vapvarun/buddypress-mcp) covers 36 tools:

| Component | Tools |
|-----------|-------|
| Activity | 6 (list, get, create, update, delete, favorite) |
| Members | 4 (list, get, update, delete) |
| Groups | 5 (list, get, create, update, delete) |
| Group Members | 3 (list, add, remove) |
| XProfile | 6 (list groups, get group, list fields, get field, get data, update data) |
| Friends | 3 (list, create, delete) |
| Messages | 4 (list, get, create, delete) |
| Notifications | 4 (list, get, update, delete) |
| Components | 1 (list) |

Our BuddyBoss MCP extends this to 65+ tools covering all BuddyBoss-specific features.
