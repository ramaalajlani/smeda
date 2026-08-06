# API Documentation v1.0

## Project
SMEDC Integrated Services Platform - Laravel 11

## Base Information
- Base URL (Local example): `http://127.0.0.1:8091`
- API Prefix: `/api`
- Content Type: `application/json`
- Auth: `Bearer <sanctum_token>` for protected endpoints

---

## 1. Authentication

## 1.1 Register
- **Method:** `POST`
- **Path:** `/api/register`
- **Auth:** Public
- **Notes:** Throttled (`throttle:register`)

Request (example):
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "Secret123!",
  "password_confirmation": "Secret123!"
}
```

## 1.2 Login
- **Method:** `POST`
- **Path:** `/api/login`
- **Auth:** Public
- **Notes:** Throttled (`throttle:login`)

Request (example):
```json
{
  "email": "test@example.com",
  "password": "Secret123!"
}
```

Response (example):
```json
{
  "token": "1|xxxxxxxxxxxxxxxx",
  "user": {
    "id": 101,
    "name": "Test User",
    "email": "test@example.com"
  }
}
```

## 1.3 Logout
- **Method:** `POST`
- **Path:** `/api/logout`
- **Auth:** Required

---

## 2. User Profile

## 2.1 Get Current User
- `GET /api/me`
- Auth required

## 2.2 Update Current User
- `PUT /api/me`
- Auth required

## 2.3 Change Password
- `POST /api/me/change-password`
- Auth required

---

## 3. Notifications and Inbox

## 3.1 Notification Summary
- **Method:** `GET`
- **Path:** `/api/notifications/summary`
- **Auth:** Required
- **Purpose:** Return personal unread count + group unread count + latest notifications

Response (example):
```json
{
  "unread_count": 4,
  "group_unread_count": 19,
  "latest": [
    {
      "id": 901,
      "title": "New assignment",
      "is_read": false,
      "created_at": "2026-07-10T20:11:00Z"
    }
  ]
}
```

## 3.2 Notifications List
- `GET /api/notifications`

## 3.3 Mark Read (Single)
- `POST /api/notifications/{id}/read`

## 3.4 Mark All Read
- `POST /api/notifications/read-all`

## 3.5 Delete Notification
- `DELETE /api/notifications/{id}`

## 3.6 Inbox
- `GET /api/inbox/unread-count`
- `GET /api/inbox/users-list`
- `GET /api/inbox`
- `GET /api/inbox/sent`
- `POST /api/inbox`
- `GET /api/inbox/{id}`
- `POST /api/inbox/{id}/reply`
- `DELETE /api/inbox/{id}`

---

## 4. Needs Module API

## 4.1 Browse and Read
- `GET /api/needs`
- `GET /api/needs/{id}`
- `GET /api/needs/map`
- `GET /api/needs/lookups`
- `GET /api/needs/admin-units`
- `GET /api/needs/export`

## 4.2 Create and Workflow
- `POST /api/needs`
- `PUT /api/needs/{id}`
- `POST /api/needs/{id}/review`
- `POST /api/needs/{id}/approve`
- `POST /api/needs/{id}/reject`
- `POST /api/needs/{id}/return`
- `POST /api/needs/{id}/classify`
- `POST /api/needs/{id}/resolve`

## 4.3 Dashboard and Analytics
- `GET /api/needs/dashboard`
- `GET /api/needs/analytics`
- `GET /api/needs/workspace/data-entry`
- `GET /api/needs/workspace/reviewer`

---

## 5. Funding Module API

## 5.1 Funding Applications
- `GET /api/finance/applications`
- `GET /api/finance/applications/{id}`
- `POST /api/finance/applications`
- `PUT /api/finance/applications/{id}`
- `POST /api/finance/applications/{id}/submit`
- `POST /api/finance/applications/{id}/request-completion`
- `POST /api/finance/applications/{id}/branch-review`
- `POST /api/finance/applications/{id}/approve`
- `POST /api/finance/applications/{id}/reject`
- `POST /api/finance/applications/{id}/assign-consultant`
- `POST /api/finance/applications/{id}/assign-partner`
- `POST /api/finance/applications/{id}/create-loan`

## 5.2 Funding Documents
- `POST /api/finance/applications/{applicationId}/documents`
- `GET /api/finance/applications/{applicationId}/documents/{documentId}/download`

## 5.3 Consultant Union / Offices / Assignments
- `GET /api/finance/consultant-union/dashboard`
- `GET /api/finance/consultant-assignments`
- `GET /api/finance/consultant-offices`
- `GET /api/finance/consultant-offices/{id}`
- `POST /api/finance/consultant-offices`
- `PUT /api/finance/consultant-offices/{id}`
- `POST /api/finance/consultant-offices/{id}/approve`
- `POST /api/finance/consultant-offices/{id}/activate`
- `POST /api/finance/consultant-offices/{id}/suspend`
- `GET /api/finance/consultant-office/dashboard`
- `GET /api/finance/my-consultant-assignments`
- `POST /api/finance/consultant-assignments/{id}/accept`
- `POST /api/finance/consultant-assignments/{id}/reject`
- `POST /api/finance/consultant-assignments/{id}/price-offer`
- `POST /api/finance/consultant-assignments/{id}/approve-price`
- `POST /api/finance/consultant-reports`

## 5.4 Funding Partners
- `GET /api/finance/central-bank/dashboard`
- `GET /api/finance/funding-partner/dashboard`
- `GET /api/finance/partners`
- `GET /api/finance/partners/{id}`
- `POST /api/finance/partners`
- `PUT /api/finance/partners/{id}`
- `POST /api/finance/partners/{id}/approve`
- `POST /api/finance/partners/{id}/activate`
- `POST /api/finance/partners/{id}/suspend`
- `GET /api/finance/my-partner-assignments`
- `POST /api/finance/partner-assignments/{id}/decision`

## 5.5 Loans and Metrics
- `GET /api/finance/loans/stats`
- `GET /api/finance/loans`
- `GET /api/finance/loans/{id}`
- `GET /api/finance/loans/{id}/payments`
- `PUT /api/finance/loans/{id}`
- `POST /api/finance/loans/{id}/payments`
- `POST /api/finance/loans/{id}/mark-defaulted`
- `POST /api/finance/loans/{id}/close`
- `GET /api/finance/metrics`
- `GET /api/finance/funded/stats`
- `GET /api/finance/funded`
- `GET /api/finance/defaulted/stats`
- `GET /api/finance/defaulted`
- `GET /api/finance/cloud`
- `GET /api/finance/manager/dashboard`

---

## 6. Consulting Module API

## 6.1 Categories and Offices
- `GET /api/consulting/categories`
- `GET /api/consulting/offices`
- `GET /api/consulting/offices/{id}`
- `POST /api/consulting/offices`
- `PUT /api/consulting/offices/{id}`
- `POST /api/consulting/offices/{id}/activate`
- `POST /api/consulting/offices/{id}/suspend`
- `POST /api/consulting/offices/{id}/violations`

## 6.2 Requests and Offers
- `GET /api/consulting/requests/stats`
- `GET /api/consulting/requests`
- `GET /api/consulting/requests/{id}`
- `POST /api/consulting/requests`
- `PUT /api/consulting/requests/{id}`
- `POST /api/consulting/requests/{id}/submit`
- `POST /api/consulting/requests/{id}/sort`
- `POST /api/consulting/requests/{id}/accept-offer`
- `POST /api/consulting/requests/{id}/transfer`
- `POST /api/consulting/requests/{id}/attachments`
- `GET /api/consulting/requests/{id}/offers`
- `POST /api/consulting/requests/{id}/offers`

## 6.3 Contracts
- `GET /api/consulting/contracts/{id}`
- `GET /api/consulting/contracts/{id}/messages`
- `POST /api/consulting/contracts/{id}/sign`
- `POST /api/consulting/contracts/{id}/messages`
- `POST /api/consulting/contracts/{id}/report`
- `POST /api/consulting/contracts/{id}/approve-report`
- `POST /api/consulting/contracts/{id}/review`

---

## 7. Training and Certificates API

## 7.1 Training Core
- Trainers: `/api/trainers/*`, `/api/trainer-profiles/*`, `/api/my-trainer-profile`
- Training Kits/Nominations: `/api/training-kits/*`, `/api/training-kit-nominations/*`
- Trainees: `/api/trainees/*`
- Programs: `/api/training-programs/*`
- Program Bank: `/api/program-bank/*`
- Courses: `/api/training-courses/*`

## 7.2 Registration Requests
- Centers: `/api/registration-requests/centers/*`
- Trainers: `/api/registration-requests/trainers/*`
- Trainees: `/api/registration-requests/trainees/*`
- Courses: `/api/registration-requests/courses/*`

## 7.3 Certificates
- Public verify: `POST /api/certificates/verify`, `GET /api/verify-certificate/{certificate_code}`
- Protected issue/approve/index/show:
  - `POST /api/certificates/issue`
  - `POST /api/certificates/{id}/approve`
  - `GET /api/certificates`
  - `GET /api/certificates/{id}`
  - `GET /api/certificates/code/{certificate_code}`

---

## 8. Incubation API

- `GET /api/incubation/stats`
- `POST /api/incubators`
- `GET /api/incubators/{id}`
- `PUT /api/incubators/{id}`
- `POST /api/incubators/{id}/programs`
- `GET /api/incubation/applications`
- `POST /api/incubation/apply`
- `GET /api/incubation/my-applications`
- `GET /api/incubation/applications/{id}`
- `POST /api/incubation/applications/{id}/review`
- `GET /api/incubators/{id}/applications`
- `GET /api/incubation/projects`
- `GET /api/incubation/my-project`
- `GET /api/incubation/projects/{id}`
- `PUT /api/incubation/projects/{id}`
- `GET /api/incubation/sessions`
- `POST /api/incubation/projects/{id}/sessions`
- `GET /api/incubation/my-sessions`
- `POST /api/incubation/projects/{id}/reports`

---

## 9. Workforce API

- `GET /api/workforces`
- `GET /api/workforces/{id}`
- `POST /api/workforces/enroll`
- `GET /api/workforce/job-postings`
- `GET /api/workforce/job-postings/{id}`
- `POST /api/workforce/job-postings`
- `PUT /api/workforce/job-postings/{id}`
- `GET /api/workforce/job-applications`
- `POST /api/workforce/job-applications`
- `PUT /api/workforce/job-applications/{id}`
- `GET /api/workforce/staff-training-requests`
- `POST /api/workforce/staff-training-requests`
- `PUT /api/workforce/staff-training-requests/{id}`

---

## 10. Admin and Access Management API

## 10.1 Activity and Access Summary
- `GET /api/admin/activity-logs`
- `GET /api/admin/activity-logs/export`
- `GET /api/admin/activity-logs/{id}`
- `GET /api/admin/users/{id}/activity-logs`
- `GET /api/admin/access-summary`

## 10.2 Users
- `GET /api/admin/users`
- `POST /api/admin/users`
- `GET /api/admin/users/{id}/access`
- `PUT /api/admin/users/{id}`
- `POST /api/admin/users/{id}/change-password`
- `POST /api/admin/users/{id}/roles/sync`
- `POST /api/admin/users/{id}/permissions/sync`
- `POST /api/admin/users/{id}/roles`
- `DELETE /api/admin/users/{id}/roles/{role}`
- `POST /api/admin/users/{id}/permissions`
- `DELETE /api/admin/users/{id}/permissions/{permission}`
- `PATCH /api/admin/users/{id}/status`
- `PATCH /api/admin/users/{id}/parent`
- `GET /api/admin/users/{id}/children`
- `GET /api/admin/my-children`
- `GET /api/admin/my-delegatable`

## 10.3 Roles
- `GET /api/admin/roles`
- `POST /api/admin/roles`
- `GET /api/admin/roles/{id}`
- `PATCH /api/admin/roles/{id}`
- `DELETE /api/admin/roles/{id}`
- `POST /api/admin/roles/{id}/permissions`
- `DELETE /api/admin/roles/{id}/permissions/{permissionId}`

## 10.4 Permissions
- `GET /api/admin/permissions`
- `POST /api/admin/permissions`
- `GET /api/admin/permissions/{id}`
- `PATCH /api/admin/permissions/{id}`
- `DELETE /api/admin/permissions/{id}`

---

## 11. Public APIs (No Auth)

- News:
  - `GET /api/news`
  - `GET /api/news/{id}`
- Success stories:
  - `GET /api/success-stories`
  - `GET /api/success-stories/slug/{slug}`
  - `GET /api/success-stories/{id}`
- Incubators:
  - `GET /api/incubators`
- Map and locations:
  - `GET /api/map/training-centers`
  - `GET /api/locations/governorates|districts|subdistricts|communities|search|map`
- Public browse:
  - `GET /api/public/governorates`
  - `GET /api/public/needs/lookups`
  - `GET /api/public/needs/map`
  - `POST /api/public/needs`
  - `GET /api/public/training-programs`
  - `GET /api/public/finance/cloud`
  - `GET /api/public/finance/metrics`
  - `GET /api/public/job-postings`

---

## 12. Standard HTTP Status Codes

- `200 OK`: successful GET/update operation
- `201 Created`: successful create operation
- `204 No Content`: successful delete with no payload
- `401 Unauthorized`: missing/invalid authentication token
- `403 Forbidden`: authenticated but lacking policy/scope permission
- `404 Not Found`: resource absent or hidden by scope rule
- `422 Unprocessable Entity`: validation/business-rule failure
- `429 Too Many Requests`: throttling rule hit
- `500 Internal Server Error`: unexpected server failure

Error response (example):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "status": [
      "انتقال الحالة غير مسموح من submitted إلى approved."
    ]
  }
}
```

---

## 13. Security and Integration Notes

- Do not infer access rights from UI; backend is source of truth.
- For all `{id}` endpoints, expect strict scope and policy enforcement.
- Mutation endpoints may require both role and permission combos.
- For file upload endpoints, observe throttle and size/type constraints.

---

## 14. Testing Guidance for API Consumers

- Always test with:
  - one authorized user in-scope
  - one authenticated user out-of-scope
  - one unauthenticated request
- Validate transition endpoints with both allowed and denied state changes.
- Validate notification summary after creating/reading notifications.

---

## 15. Versioning

- This document is baseline for current route set in `routes/api.php`.
- Update this file whenever route signatures, auth rules, or response contracts change.

