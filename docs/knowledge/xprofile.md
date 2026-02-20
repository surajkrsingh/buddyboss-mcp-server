# Extended Profiles / XProfile Tools (5 tools)

**Phase:** Core (always enabled)
**BuddyBoss API:** `/buddyboss/v1/xprofile`

---

## buddyboss_list_xprofile_groups

List profile field groups.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `fetch_fields` | boolean | No | If true, include fields in the response. |

---

## buddyboss_list_xprofile_fields

List profile fields in a group.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `profile_group_id` | integer | No | Filter by profile group ID. |
| `fetch_field_data` | boolean | No | If true, include field data in the response. |

---

## buddyboss_get_xprofile_field

Get profile field details.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The profile field ID. |
| `fetch_field_data` | boolean | No | If true, include field data. |

---

## buddyboss_get_xprofile_data

Get a user's profile field data.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `field_id` | integer | **Yes** | The profile field ID. |
| `user_id` | integer | **Yes** | The user ID. |

---

## buddyboss_update_xprofile_data

Update a user's profile field data.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `field_id` | integer | **Yes** | The profile field ID. |
| `user_id` | integer | **Yes** | The user ID. |
| `value` | string | **Yes** | The new field value. |
