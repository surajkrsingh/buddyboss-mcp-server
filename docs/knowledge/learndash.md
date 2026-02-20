# LearnDash Integration Tools (4 tools)

**Phase:** Advanced (toggleable)
**BuddyBoss API:** `/buddyboss/v1/learndash`

---

## buddyboss_list_courses

List LearnDash courses.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number for pagination. Default: 1. |
| `per_page` | integer | No | Courses per page. Default: 20. |
| `search` | string | No | Search courses by title. |
| `category` | integer | No | Filter by category ID. |

---

## buddyboss_get_course

Get course details.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | The course ID. |

---

## buddyboss_enroll_user

Enroll a user in a course.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_id` | integer | **Yes** | The course ID. |
| `user_id` | integer | **Yes** | The user ID to enroll. |

---

## buddyboss_get_course_progress

Get a user's course progress.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `course_id` | integer | **Yes** | The course ID. |
| `user_id` | integer | **Yes** | The user ID. |
