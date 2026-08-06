# API Documentation v2.0

> **Generated from live routes** — `php artisan route:list --json` on 2026-07-15 13:09
> **Total routes:** 346 (API prefix + web + health)

## Document Control

| Item | Value |
|------|-------|
| Version | 2.0 |
| Framework | Laravel 12 |
| Auth | Laravel Sanctum (Bearer token) |
| Content-Type | `application/json` (unless noted) |
| Source | `routes/api.php`, `routes/web.php`, `bootstrap/app.php` |

---

## Base URLs

### Local development
| Purpose | URL |
|---------|-----|
| API root | `http://127.0.0.1:8000/api` |
| Example login | `POST http://127.0.0.1:8000/api/login` |
| Health | `GET http://127.0.0.1:8000/up` |

### Production (Hostinger — smeda.gov.sy)
| Purpose | URL |
|---------|-----|
| Frontend | `https://smeda.gov.sy` |
| API root (Laravel entry) | `https://smeda.gov.sy/api` |
| **Full API path** | `https://smeda.gov.sy/api/api/{endpoint}` |
| Example login | `POST https://smeda.gov.sy/api/api/login` |
| Health | `GET https://smeda.gov.sy/api/up` |

**Note:** On Hostinger, the folder `public_html/api/` is the Laravel front controller, and Laravel routes are registered under `/api/*`. The frontend therefore calls `{domain}/api/api/...`.

---

## Authentication

### Headers (protected endpoints)
```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

### Standard error responses
| HTTP | Meaning |
|------|---------|
| 401 | Missing/invalid token or wrong credentials |
| 403 | Authenticated but forbidden (role/permission/policy) |
| 404 | Resource not found |
| 422 | Validation error |
| 429 | Rate limit exceeded |
| 500 | Server error |

---

## Authentication Endpoints (detailed)

### POST `/api/register` — Public
- **Throttle:** `throttle:register`
- **Controller:** `Api\AuthController@register`

**Body (JSON):**
```json
{
  "name": "string (required, max 255)",
  "email": "string (required, unique)",
  "password": "string (required, min 8, confirmed)",
  "password_confirmation": "string (required)",
  "account_type": "string (required — see account types below)",
  "device_name": "string (optional, default front-web)"
}
```

**Prohibited fields (422 if sent):** `role`, `roles`, `permissions`, `entity_type`, `training_center_id`, `trainer_id`, `trainee_id`, `is_active`

**Success 201:**
```json
{
  "message": "تم إنشاء الحساب بنجاح.",
  "token": "1|...",
  "token_type": "Bearer",
  "redirect_to_form": "account_type_key",
  "entity_pending_approval": true,
  "user": { "id", "name", "email", "roles", "permissions", ... }
}
```

### POST `/api/login` — Public
- **Throttle:** `throttle:login`
- **Controller:** `Api\AuthController@login`

**Body:** `{ "email", "password", "device_name?" }`

**Success 200:** `{ "message", "token", "token_type": "Bearer", "user": {...} }`
**Failure 401:** `{ "message": "بيانات الدخول غير صحيحة." }`

### POST `/api/logout` — Protected
Revokes current token.

### GET `/api/me` — Protected
Returns `{ "user": { id, name, email, phone, roles[], permissions[], branch_id, ... } }`

### PUT `/api/me` — Protected
Body: `{ "name?", "phone?" }`

### POST `/api/me/change-password` — Protected
Body: `{ "current_password", "password", "password_confirmation" }` — min 8 chars

### Self-registration `account_type` values

Source: `App\Support\SelfRegistrationCatalog::validationKeys()`

| account_type | Role assigned | Notes |
|--------------|---------------|-------|
| `trainee` | trainee_user | Requires entity approval workflow |
| `trainer` | trainer_user | Requires entity approval workflow |
| `center` | center_user | Requires entity approval workflow |
| `project_owner` | project_owner | Redirect to finance apply |
| `incubation_applicant` | project_owner | Incubation application |
| `entrepreneur_tech` | project_owner | Tech entrepreneur survey |
| `consultant` | consultant_office | Finance/marketplace consulting office |
| `consulting_client` | trainee_user | Consulting request client |
| `jobseeker` | trainee_user | Workforce job seeker |
| `employer` | project_owner | Post job openings |
| `entrepreneur` | project_owner | Alias — normalized to project_owner |

---

## Pagination & Filtering (common query parameters)

Most list endpoints support Laravel pagination:

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default 1) |
| `per_page` | integer | Items per page (often default 15–50, max varies) |
| `search` | string | Text search where supported |
| `status` | string | Filter by status where supported |
| `branch_id` | integer | Filter by branch (scoped roles) |
| `governorate_id` | integer | Filter by governorate where supported |

**Typical paginated response shape:**
```json
{
  "data": [ ... ],
  "links": { "first", "last", "prev", "next" },
  "meta": { "current_page", "last_page", "per_page", "total" }
}
```

---

## Module Overview (quick index)

| Module | Prefix | Endpoints (approx.) |
|--------|--------|---------------------|
| Authentication & Profile | see section below | 6 |
| Dashboard | see section below | 1 |
| Inbox | see section below | 8 |
| Notifications | see section below | 5 |
| Public Browse | see section below | 8 |
| Admin (Users, Roles, Permissions, Audit) | see section below | 34 |
| Agreements | see section below | 5 |
| Branches & Governorates | see section below | 7 |
| Certificates | see section below | 8 |
| Electronic Signatures | see section below | 6 |
| Training Map | see section below | 3 |
| Program Bank | see section below | 18 |
| Registration Requests | see section below | 18 |
| Training | see section below | 28 |
| Workforce | see section below | 13 |
| Consulting Marketplace | see section below | 27 |
| Finance & Funding | see section below | 68 |
| Needs (GIS) | see section below | 18 |
| Entrepreneur Profiles | see section below | 9 |
| Incubation | see section below | 20 |
| News | see section below | 6 |
| Success Stories | see section below | 7 |
| Syria Locations | see section below | 6 |

---

## Complete Route Reference

All paths below use the **Laravel API prefix** `/api`. On production Hostinger prepend `{domain}/api` before each path (e.g. `/api/login` → `https://smeda.gov.sy/api/api/login`).

### 00 — Health

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/up` | Public | — | `Closure` |

### 01 — Authentication & Profile

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | POST | `/api/login` | Public | throttle:login | `Api\AuthController@login` |
| 2 | POST | `/api/logout` | Protected — Bearer token (auth:sanctum) | — | `Api\AuthController@logout` |
| 3 | POST | `/api/me/change-password` | Protected — Bearer token (auth:sanctum) | — | `Api\AuthController@changeMyPassword` |
| 4 | GET|HEAD | `/api/me` | Protected — Bearer token (auth:sanctum) | — | `Api\AuthController@me` |
| 5 | PUT | `/api/me` | Protected — Bearer token (auth:sanctum) | — | `Api\AuthController@updateMe` |
| 6 | POST | `/api/register` | Public | throttle:register | `Api\AuthController@register` |

### 02 — Dashboard

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/dashboard` | Protected — dashboard.access middleware | — | `Api\DashboardController@index` |

### 03 — Inbox

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/inbox/sent` | Protected — Bearer token (auth:sanctum) | — | `Api\InboxController@sent` |
| 2 | GET|HEAD | `/api/inbox/unread-count` | Protected — Bearer token (auth:sanctum) | — | `Api\InboxController@unreadCount` |
| 3 | GET|HEAD | `/api/inbox/users-list` | Protected — Bearer token (auth:sanctum) | — | `Api\InboxController@usersList` |
| 4 | POST | `/api/inbox/{id}/reply` | Protected — Bearer token (auth:sanctum) | — | `Api\InboxController@reply` |
| 5 | DELETE | `/api/inbox/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\InboxController@destroy` |
| 6 | GET|HEAD | `/api/inbox/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\InboxController@show` |
| 7 | GET|HEAD | `/api/inbox` | Protected — Bearer token (auth:sanctum) | — | `Api\InboxController@inbox` |
| 8 | POST | `/api/inbox` | Protected — Bearer token (auth:sanctum) | — | `Api\InboxController@store` |

### 04 — Notifications

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | POST | `/api/notifications/read-all` | Protected — Bearer token (auth:sanctum) | — | `Api\NotificationController@markAllRead` |
| 2 | GET|HEAD | `/api/notifications/summary` | Protected — Bearer token (auth:sanctum) | — | `Api\NotificationController@summary` |
| 3 | POST | `/api/notifications/{id}/read` | Protected — Bearer token (auth:sanctum) | — | `Api\NotificationController@markRead` |
| 4 | DELETE | `/api/notifications/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\NotificationController@destroy` |
| 5 | GET|HEAD | `/api/notifications` | Protected — Bearer token (auth:sanctum) | — | `Api\NotificationController@index` |

### 05 — Public Browse

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/public/finance/cloud` | Public | throttle:map-public | `Api\PublicBrowseController@financeCloud` |
| 2 | GET|HEAD | `/api/public/finance/metrics` | Public | throttle:map-public | `Api\PublicBrowseController@financeMetrics` |
| 3 | GET|HEAD | `/api/public/governorates` | Public | throttle:map-public | `Api\PublicBrowseController@governorates` |
| 4 | GET|HEAD | `/api/public/job-postings` | Public | throttle:map-public | `Api\PublicBrowseController@jobPostings` |
| 5 | GET|HEAD | `/api/public/needs/lookups` | Public | throttle:map-public | `Api\PublicBrowseController@needsLookups` |
| 6 | GET|HEAD | `/api/public/needs/map` | Public | throttle:map-public | `Api\PublicBrowseController@needsMap` |
| 7 | POST | `/api/public/needs` | Public | throttle:map-public | `Api\PublicBrowseController@storeGuestNeed` |
| 8 | GET|HEAD | `/api/public/training-programs` | Public | throttle:map-public | `Api\PublicBrowseController@trainingPrograms` |

### 10 — Admin (Users, Roles, Permissions, Audit)

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/admin/access-summary` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\AccessSummaryController` |
| 2 | GET|HEAD | `/api/admin/activity-logs/export` | Protected — role/permission: auditor OR admin OR super_admin OR system_admin OR general_director OR view_audit OR manage_user_access | throttle:admin-access | `Api\Admin\ActivityLogController@export` |
| 3 | GET|HEAD | `/api/admin/activity-logs/{id}` | Protected — role/permission: auditor OR admin OR super_admin OR system_admin OR general_director OR view_audit OR manage_user_access | throttle:admin-access | `Api\Admin\ActivityLogController@show` |
| 4 | GET|HEAD | `/api/admin/activity-logs` | Protected — role/permission: auditor OR admin OR super_admin OR system_admin OR general_director OR view_audit OR manage_user_access | throttle:admin-access | `Api\Admin\ActivityLogController@index` |
| 5 | GET|HEAD | `/api/admin/my-children` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@myChildren` |
| 6 | GET|HEAD | `/api/admin/my-delegatable` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@delegatableOptions` |
| 7 | DELETE | `/api/admin/permissions/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\PermissionController@destroy` |
| 8 | GET|HEAD | `/api/admin/permissions/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\PermissionController@show` |
| 9 | PATCH | `/api/admin/permissions/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\PermissionController@update` |
| 10 | GET|HEAD | `/api/admin/permissions` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\PermissionController@index` |
| 11 | POST | `/api/admin/permissions` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\PermissionController@store` |
| 12 | DELETE | `/api/admin/roles/{id}/permissions/{permissionId}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\RoleController@detachPermission` |
| 13 | POST | `/api/admin/roles/{id}/permissions` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\RoleController@syncPermissions` |
| 14 | DELETE | `/api/admin/roles/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\RoleController@destroy` |
| 15 | GET|HEAD | `/api/admin/roles/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\RoleController@show` |
| 16 | PATCH | `/api/admin/roles/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\RoleController@update` |
| 17 | GET|HEAD | `/api/admin/roles` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\RoleController@index` |
| 18 | POST | `/api/admin/roles` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\RoleController@store` |
| 19 | GET|HEAD | `/api/admin/users/{id}/access` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@show` |
| 20 | GET|HEAD | `/api/admin/users/{id}/activity-logs` | Protected — role/permission: auditor OR admin OR super_admin OR system_admin OR general_director OR view_audit OR manage_user_access | throttle:admin-access | `Api\Admin\ActivityLogController@forUser` |
| 21 | POST | `/api/admin/users/{id}/change-password` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@changePassword` |
| 22 | GET|HEAD | `/api/admin/users/{id}/children` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@childrenOf` |
| 23 | PATCH | `/api/admin/users/{id}/parent` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@reassignParent` |
| 24 | POST | `/api/admin/users/{id}/permissions/sync` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@syncPermissions` |
| 25 | DELETE | `/api/admin/users/{id}/permissions/{permission}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@revokePermission` |
| 26 | POST | `/api/admin/users/{id}/permissions` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@assignPermission` |
| 27 | POST | `/api/admin/users/{id}/roles/sync` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@syncRoles` |
| 28 | DELETE | `/api/admin/users/{id}/roles/{role}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@revokeRole` |
| 29 | POST | `/api/admin/users/{id}/roles` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@assignRole` |
| 30 | PATCH | `/api/admin/users/{id}/status` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@updateStatus` |
| 31 | GET|HEAD | `/api/admin/users/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@show` |
| 32 | PUT | `/api/admin/users/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@update` |
| 33 | GET|HEAD | `/api/admin/users` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@index` |
| 34 | POST | `/api/admin/users` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director | throttle:admin-access | `Api\Admin\UserAccessController@store` |

### 11 — Agreements

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | POST | `/api/agreements/{id}/approve` | Protected — Bearer token (auth:sanctum) | — | `Api\AgreementController@approve` |
| 2 | GET|HEAD | `/api/agreements/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\AgreementController@show` |
| 3 | PUT | `/api/agreements/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\AgreementController@update` |
| 4 | GET|HEAD | `/api/agreements` | Protected — Bearer token (auth:sanctum) | — | `Api\AgreementController@index` |
| 5 | POST | `/api/agreements` | Protected — Bearer token (auth:sanctum) | — | `Api\AgreementController@store` |

### 12 — Branches & Governorates

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/branches/dashboard` | Protected — Bearer token (auth:sanctum) | — | `Api\BranchController@dashboard` |
| 2 | DELETE | `/api/branches/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\BranchController@destroy` |
| 3 | GET|HEAD | `/api/branches/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\BranchController@show` |
| 4 | PUT | `/api/branches/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\BranchController@update` |
| 5 | GET|HEAD | `/api/branches` | Protected — Bearer token (auth:sanctum) | — | `Api\BranchController@index` |
| 6 | POST | `/api/branches` | Protected — Bearer token (auth:sanctum) | — | `Api\BranchController@store` |
| 7 | GET|HEAD | `/api/governorates` | Protected — Bearer token (auth:sanctum) | — | `Api\GovernorateController@index` |

### 20 — Certificates

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/certificates/code/{certificate_code}` | Protected — permission: view_certificates | — | `Api\CertificateController@showByCode` |
| 2 | POST | `/api/certificates/issue` | Protected — permission: issue_certificates | — | `Api\CertificateController@issue` |
| 3 | GET|HEAD | `/api/certificates/verify-page` | Public | throttle:verify-page | `Api\CertificateController@verifyPage` |
| 4 | POST | `/api/certificates/verify` | Public | throttle:certificate-verify | `Api\CertificateController@verify` |
| 5 | POST | `/api/certificates/{id}/approve` | Protected — role/permission: approve_center_certificates OR approve_training_certificates OR approve_deputy_certificates OR approve_general_director_certificates | — | `Api\CertificateController@approve` |
| 6 | GET|HEAD | `/api/certificates/{id}` | Protected — permission: view_certificates | — | `Api\CertificateController@show` |
| 7 | GET|HEAD | `/api/certificates` | Protected — permission: view_certificates | — | `Api\CertificateController@index` |
| 8 | GET|HEAD | `/api/verify-certificate/{certificate_code}` | Public | throttle:certificate-verify | `Api\CertificateController@verifyByCode` |

### 21 — Electronic Signatures

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/electronic-signatures/{id}/snapshot-image` | Protected — Bearer token (auth:sanctum) | — | `Api\UserElectronicSignatureController@snapshotImage` |
| 2 | GET|HEAD | `/api/my-electronic-signature/image` | Protected — Bearer token (auth:sanctum) | — | `Api\UserElectronicSignatureController@myImage` |
| 3 | DELETE | `/api/my-electronic-signature` | Protected — Bearer token (auth:sanctum) | — | `Api\UserElectronicSignatureController@destroy` |
| 4 | GET|HEAD | `/api/my-electronic-signature` | Protected — Bearer token (auth:sanctum) | — | `Api\UserElectronicSignatureController@show` |
| 5 | POST | `/api/my-electronic-signature` | Protected — Bearer token (auth:sanctum) | throttle:file-upload | `Api\UserElectronicSignatureController@store` |
| 6 | GET|HEAD | `/api/signatures/verify/{code}` | Public | throttle:certificate-verify | `Api\ExecutiveSignatureController@verify` |

### 22 — Training Map

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/map/trainers` | Protected — permission: view_trainers | throttle:map-public | `Api\TrainingMapController@trainers` |
| 2 | GET|HEAD | `/api/map/training-centers` | Public | throttle:map-public | `Api\TrainingMapController@centers` |
| 3 | GET|HEAD | `/api/map/training-courses` | Protected — permission: view_courses | throttle:map-public | `Api\TrainingMapController@courses` |

### 23 — Program Bank

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/program-bank/reports` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR program_bank.reports OR program_bank.view OR view_reports | — | `Api\ProgramBankController@reports` |
| 2 | GET|HEAD | `/api/program-bank/stats` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR program_bank.view OR view_programs OR manage_programs | — | `Api\ProgramBankController@stats` |
| 3 | POST | `/api/program-bank/{id}/create-course` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.create OR manage_programs | — | `Api\ProgramBankController@createCourseFromProgram` |
| 4 | POST | `/api/program-bank/{id}/duplicate` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.create OR manage_programs | — | `Api\ProgramBankController@duplicate` |
| 5 | PUT | `/api/program-bank/{id}/modules/reorder` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@reorderModules` |
| 6 | DELETE | `/api/program-bank/{id}/modules/{moduleId}` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@destroyModule` |
| 7 | PUT | `/api/program-bank/{id}/modules/{moduleId}` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@updateModule` |
| 8 | POST | `/api/program-bank/{id}/modules` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@storeModule` |
| 9 | DELETE | `/api/program-bank/{id}/outcomes/{outcomeId}` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@destroyOutcome` |
| 10 | PUT | `/api/program-bank/{id}/outcomes/{outcomeId}` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@updateOutcome` |
| 11 | POST | `/api/program-bank/{id}/outcomes` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@storeOutcome` |
| 12 | PUT | `/api/program-bank/{id}/service-links` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@syncServiceLinks` |
| 13 | POST | `/api/program-bank/{id}/transition` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR deputy_director OR program_bank.approve OR manage_programs | — | `Api\ProgramBankController@transition` |
| 14 | DELETE | `/api/program-bank/{id}` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.delete OR manage_programs | — | `Api\ProgramBankController@destroy` |
| 15 | GET|HEAD | `/api/program-bank/{id}` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR program_bank.view OR view_programs OR manage_programs | — | `Api\ProgramBankController@show` |
| 16 | PUT | `/api/program-bank/{id}` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.update OR manage_programs | — | `Api\ProgramBankController@update` |
| 17 | GET|HEAD | `/api/program-bank` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR program_bank.view OR view_programs OR manage_programs | — | `Api\ProgramBankController@index` |
| 18 | POST | `/api/program-bank` | Protected — role/permission: training_manager OR general_director OR admin OR super_admin OR system_admin OR program_bank.create OR manage_programs | — | `Api\ProgramBankController@store` |

### 24 — Registration Requests

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | POST | `/api/registration-requests/centers/{id}/review` | Protected — permission: review_center_registration_requests | — | `Api\TrainingCenterRegistrationRequestController@review` |
| 2 | GET|HEAD | `/api/registration-requests/centers/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\TrainingCenterRegistrationRequestController@show` |
| 3 | GET|HEAD | `/api/registration-requests/centers` | Protected — permission: view_registration_requests | — | `Api\TrainingCenterRegistrationRequestController@index` |
| 4 | POST | `/api/registration-requests/centers` | Protected — permission: create_center_registration_requests | throttle:registration-requests | `Api\TrainingCenterRegistrationRequestController@store` |
| 5 | POST | `/api/registration-requests/courses/{id}/cancel` | Protected — Bearer token (auth:sanctum) | — | `Api\CourseRegistrationRequestController@cancel` |
| 6 | POST | `/api/registration-requests/courses/{id}/complete` | Protected — permission: complete_course_registration_requests | — | `Api\CourseRegistrationRequestController@complete` |
| 7 | POST | `/api/registration-requests/courses/{id}/confirm-by-guardian` | Protected — permission: confirm_course_registration_requests | — | `Api\CourseRegistrationRequestController@confirmByGuardian` |
| 8 | GET|HEAD | `/api/registration-requests/courses/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\CourseRegistrationRequestController@show` |
| 9 | GET|HEAD | `/api/registration-requests/courses` | Protected — role/permission: view_registration_requests OR create_course_registration_requests OR confirm_course_registration_requests OR complete_course_registration_requests | — | `Api\CourseRegistrationRequestController@index` |
| 10 | POST | `/api/registration-requests/courses` | Protected — permission: create_course_registration_requests | throttle:registration-requests | `Api\CourseRegistrationRequestController@store` |
| 11 | POST | `/api/registration-requests/trainees/{id}/review` | Protected — permission: review_trainee_registration_requests | — | `Api\TraineeRegistrationRequestController@review` |
| 12 | GET|HEAD | `/api/registration-requests/trainees/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\TraineeRegistrationRequestController@show` |
| 13 | GET|HEAD | `/api/registration-requests/trainees` | Protected — permission: view_registration_requests | — | `Api\TraineeRegistrationRequestController@index` |
| 14 | POST | `/api/registration-requests/trainees` | Protected — permission: create_trainee_registration_requests | throttle:registration-requests | `Api\TraineeRegistrationRequestController@store` |
| 15 | POST | `/api/registration-requests/trainers/{id}/review` | Protected — permission: review_trainer_registration_requests | — | `Api\TrainerRegistrationRequestController@review` |
| 16 | GET|HEAD | `/api/registration-requests/trainers/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\TrainerRegistrationRequestController@show` |
| 17 | GET|HEAD | `/api/registration-requests/trainers` | Protected — permission: view_registration_requests | — | `Api\TrainerRegistrationRequestController@index` |
| 18 | POST | `/api/registration-requests/trainers` | Protected — permission: create_trainer_registration_requests | throttle:registration-requests | `Api\TrainerRegistrationRequestController@store` |

### 25 — Training

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/my-trainer-profile` | Protected — role/permission: view_trainer_profiles OR edit_own_trainer_profile | — | `Api\TrainerProfileController@myProfile` |
| 2 | POST | `/api/my-trainer-profile` | Protected — permission: edit_own_trainer_profile | — | `Api\TrainerProfileController@updateMyProfile` |
| 3 | GET|HEAD | `/api/trainees/{id}` | Protected — permission: view_trainees | — | `Api\TraineeController@show` |
| 4 | GET|HEAD | `/api/trainees` | Protected — permission: view_trainees | — | `Api\TraineeController@index` |
| 5 | GET|HEAD | `/api/trainer-profiles/{id}` | Protected — permission: view_trainer_profiles | — | `Api\TrainerProfileController@show` |
| 6 | GET|HEAD | `/api/trainers/{id}` | Protected — permission: view_trainers | — | `Api\TrainerController@show` |
| 7 | GET|HEAD | `/api/trainers` | Protected — permission: view_trainers | — | `Api\TrainerController@index` |
| 8 | GET|HEAD | `/api/training-centers/{id}` | Protected — permission: view_centers | — | `Api\TrainingCenterController@show` |
| 9 | GET|HEAD | `/api/training-centers` | Protected — permission: view_centers | — | `Api\TrainingCenterController@index` |
| 10 | POST | `/api/training-courses/{id}/complete` | Protected — permission: manage_courses | — | `Api\TrainingCourseController@complete` |
| 11 | DELETE | `/api/training-courses/{id}/trainees/{traineeId}` | Protected — permission: manage_courses | — | `Api\TrainingCourseController@removeTrainee` |
| 12 | PATCH | `/api/training-courses/{id}/trainees/{traineeId}` | Protected — permission: manage_courses | — | `Api\TrainingCourseController@updateTrainee` |
| 13 | GET|HEAD | `/api/training-courses/{id}/trainees` | Protected — role/permission: view_courses OR view_course_details | — | `Api\TrainingCourseController@trainees` |
| 14 | POST | `/api/training-courses/{id}/trainees` | Protected — permission: manage_courses | — | `Api\TrainingCourseController@addTrainee` |
| 15 | GET|HEAD | `/api/training-courses/{id}` | Protected — role/permission: trainer_user OR trainee_user OR view_courses OR view_course_details | — | `Api\TrainingCourseController@show` |
| 16 | PATCH | `/api/training-courses/{id}` | Protected — permission: manage_courses | — | `Api\TrainingCourseController@update` |
| 17 | GET|HEAD | `/api/training-courses` | Protected — role/permission: trainer_user OR trainee_user OR view_courses | — | `Api\TrainingCourseController@index` |
| 18 | POST | `/api/training-courses` | Protected — permission: manage_courses | — | `Api\TrainingCourseController@store` |
| 19 | POST | `/api/training-kit-nominations/{id}/review` | Protected — permission: review_training_kit_nominations | — | `Api\TrainingKitNominationController@review` |
| 20 | GET|HEAD | `/api/training-kit-nominations/{id}` | Protected — role/permission: nominate_training_kits OR review_training_kit_nominations | — | `Api\TrainingKitNominationController@show` |
| 21 | GET|HEAD | `/api/training-kit-nominations` | Protected — role/permission: nominate_training_kits OR review_training_kit_nominations | — | `Api\TrainingKitNominationController@index` |
| 22 | POST | `/api/training-kit-nominations` | Protected — permission: nominate_training_kits | — | `Api\TrainingKitNominationController@store` |
| 23 | POST | `/api/training-kit-public-requests` | Public | throttle:training-kit-public | `Api\TrainingKitPublicRequestController@store` |
| 24 | GET|HEAD | `/api/training-kits/{id}` | Protected — permission: view_kits | — | `Api\TrainingKitController@show` |
| 25 | GET|HEAD | `/api/training-kits` | Protected — permission: view_kits | — | `Api\TrainingKitController@index` |
| 26 | GET|HEAD | `/api/training-programs/{id}` | Protected — permission: view_programs | — | `Api\TrainingProgramController@show` |
| 27 | GET|HEAD | `/api/training-programs` | Protected — permission: view_programs | — | `Api\TrainingProgramController@index` |
| 28 | GET|HEAD | `/api/training-supervisors` | Protected — permission: view_centers | — | `Api\TrainingSupervisorController@index` |

### 26 — Workforce

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | PUT | `/api/workforce/job-applications/{id}` | Protected — permission: workforce.applications.view | — | `Api\JobApplicationController@update` |
| 2 | GET|HEAD | `/api/workforce/job-applications` | Protected — permission: workforce.applications.view | — | `Api\JobApplicationController@index` |
| 3 | POST | `/api/workforce/job-applications` | Protected — permission: workforce.applications.create | — | `Api\JobApplicationController@store` |
| 4 | GET|HEAD | `/api/workforce/job-postings/{id}` | Protected — permission: workforce.jobs.view | — | `Api\JobPostingController@show` |
| 5 | PUT | `/api/workforce/job-postings/{id}` | Protected — permission: workforce.jobs.manage | — | `Api\JobPostingController@update` |
| 6 | GET|HEAD | `/api/workforce/job-postings` | Protected — permission: workforce.jobs.view | — | `Api\JobPostingController@index` |
| 7 | POST | `/api/workforce/job-postings` | Protected — permission: workforce.jobs.create | — | `Api\JobPostingController@store` |
| 8 | PUT | `/api/workforce/staff-training-requests/{id}` | Protected — permission: workforce.training_requests.view | — | `Api\StaffTrainingRequestController@update` |
| 9 | GET|HEAD | `/api/workforce/staff-training-requests` | Protected — permission: workforce.training_requests.view | — | `Api\StaffTrainingRequestController@index` |
| 10 | POST | `/api/workforce/staff-training-requests` | Protected — permission: workforce.training_requests.create | — | `Api\StaffTrainingRequestController@store` |
| 11 | POST | `/api/workforces/enroll` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR training_manager | — | `Api\WorkforceController@enroll` |
| 12 | GET|HEAD | `/api/workforces/{id}` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR auditor OR admin OR super_admin OR system_admin OR training_manager OR development_manager OR workforce_manager | — | `Api\WorkforceController@show` |
| 13 | GET|HEAD | `/api/workforces` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR auditor OR admin OR super_admin OR system_admin OR training_manager OR development_manager OR workforce_manager | — | `Api\WorkforceController@index` |

### 30 — Consulting Marketplace

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/consulting/categories` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_union_admin OR consultant_office | — | `Closure` |
| 2 | POST | `/api/consulting/contracts/{id}/approve-report` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingContractController@approveReport` |
| 3 | GET|HEAD | `/api/consulting/contracts/{id}/messages` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_union_admin OR consultant_office | — | `Api\ConsultingContractController@messages` |
| 4 | POST | `/api/consulting/contracts/{id}/messages` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingContractController@sendMessage` |
| 5 | POST | `/api/consulting/contracts/{id}/report` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | throttle:file-upload | `Api\ConsultingContractController@uploadReport` |
| 6 | POST | `/api/consulting/contracts/{id}/review` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingContractController@submitReview` |
| 7 | POST | `/api/consulting/contracts/{id}/sign` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingContractController@sign` |
| 8 | GET|HEAD | `/api/consulting/contracts/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_union_admin OR consultant_office | — | `Api\ConsultingContractController@show` |
| 9 | POST | `/api/consulting/offices/{id}/activate` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR consultant_union_admin OR branch_manager OR governor | — | `Api\ConsultingOfficeController@activate` |
| 10 | POST | `/api/consulting/offices/{id}/suspend` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR consultant_union_admin OR branch_manager OR governor | — | `Api\ConsultingOfficeController@suspend` |
| 11 | POST | `/api/consulting/offices/{id}/violations` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR consultant_union_admin OR branch_manager OR governor | — | `Api\ConsultingOfficeController@addViolation` |
| 12 | GET|HEAD | `/api/consulting/offices/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR consultant_union_admin OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_office | — | `Api\ConsultingOfficeController@show` |
| 13 | PUT | `/api/consulting/offices/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR consultant_union_admin OR branch_manager OR governor | — | `Api\ConsultingOfficeController@update` |
| 14 | GET|HEAD | `/api/consulting/offices` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR consultant_union_admin OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_office | — | `Api\ConsultingOfficeController@index` |
| 15 | POST | `/api/consulting/offices` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR consultant_union_admin OR branch_manager OR governor | — | `Api\ConsultingOfficeController@store` |
| 16 | GET|HEAD | `/api/consulting/requests/stats` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_union_admin OR consultant_office | — | `Api\ConsultingRequestController@stats` |
| 17 | POST | `/api/consulting/requests/{id}/accept-offer` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingRequestController@acceptOffer` |
| 18 | POST | `/api/consulting/requests/{id}/attachments` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | throttle:file-upload | `Api\ConsultingRequestController@uploadAttachment` |
| 19 | GET|HEAD | `/api/consulting/requests/{id}/offers` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_union_admin OR consultant_office | — | `Api\ConsultingOfferController@index` |
| 20 | POST | `/api/consulting/requests/{id}/offers` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingOfferController@store` |
| 21 | POST | `/api/consulting/requests/{id}/sort` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingRequestController@sort` |
| 22 | POST | `/api/consulting/requests/{id}/submit` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingRequestController@submit` |
| 23 | POST | `/api/consulting/requests/{id}/transfer` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingRequestController@transfer` |
| 24 | GET|HEAD | `/api/consulting/requests/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_union_admin OR consultant_office | — | `Api\ConsultingRequestController@show` |
| 25 | PUT | `/api/consulting/requests/{id}` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR consultant_union_admin OR project_owner OR consultant_office | — | `Api\ConsultingRequestController@update` |
| 26 | GET|HEAD | `/api/consulting/requests` | Protected — role/permission: admin OR super_admin OR system_admin OR general_director OR branch_manager OR branch_officer OR governor OR project_owner OR consultant_union_admin OR consultant_office | — | `Api\ConsultingRequestController@index` |
| 27 | POST | `/api/consulting/requests` | Protected — role/permission: project_owner OR admin OR super_admin OR system_admin OR general_director OR branch_manager OR governor | — | `Api\ConsultingRequestController@store` |

### 31 — Finance & Funding

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/finance/applications/{applicationId}/documents/{documentId}/download` | Protected — role/permission: project_owner OR consultant_office OR funding_partner OR finance_manager OR finance_officer OR central_bank_admin OR consultant_union_admin OR branch_manager OR branch_officer OR governor OR general_director OR deputy_general_director OR deputy_director OR auditor OR admin OR super_admin OR system_admin OR finance.applications.view | — | `Api\FundingDocumentController@download` |
| 2 | POST | `/api/finance/applications/{applicationId}/documents` | Protected — role/permission: project_owner OR consultant_office OR funding_partner OR finance_manager OR finance_officer OR central_bank_admin OR consultant_union_admin OR branch_manager OR branch_officer OR governor OR general_director OR deputy_general_director OR deputy_director OR auditor OR admin OR super_admin OR system_admin OR finance.applications.view | throttle:file-upload | `Api\FundingDocumentController@store` |
| 3 | POST | `/api/finance/applications/{id}/approve` | Protected — role/permission: finance_manager OR finance.applications.approve OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@approve` |
| 4 | POST | `/api/finance/applications/{id}/assign-consultant` | Protected — role/permission: branch_manager OR finance_manager OR finance.applications.assign_consultant OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@assignConsultant` |
| 5 | POST | `/api/finance/applications/{id}/assign-partner` | Protected — role/permission: finance_manager OR central_bank_admin OR finance.applications.assign_partner OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@assignPartner` |
| 6 | POST | `/api/finance/applications/{id}/branch-review` | Protected — role/permission: branch_manager OR finance.applications.review_branch OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@branchReview` |
| 7 | POST | `/api/finance/applications/{id}/create-loan` | Protected — role/permission: finance_manager OR finance.loans.manage OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@createLoan` |
| 8 | POST | `/api/finance/applications/{id}/reject` | Protected — role/permission: branch_manager OR finance_manager OR finance.applications.reject OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@reject` |
| 9 | POST | `/api/finance/applications/{id}/request-completion` | Protected — role/permission: branch_manager OR finance.applications.request_completion OR finance_manager OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@requestCompletion` |
| 10 | POST | `/api/finance/applications/{id}/submit` | Protected — role/permission: project_owner OR finance.applications.submit OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@submit` |
| 11 | GET|HEAD | `/api/finance/applications/{id}` | Protected — role/permission: project_owner OR consultant_office OR funding_partner OR finance_manager OR finance_officer OR central_bank_admin OR consultant_union_admin OR branch_manager OR branch_officer OR governor OR general_director OR deputy_general_director OR deputy_director OR auditor OR admin OR super_admin OR system_admin OR finance.applications.view | — | `Api\FundingApplicationController@show` |
| 12 | PUT | `/api/finance/applications/{id}` | Protected — role/permission: project_owner OR finance.applications.update OR branch_manager OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@update` |
| 13 | GET|HEAD | `/api/finance/applications` | Protected — role/permission: project_owner OR consultant_office OR funding_partner OR finance_manager OR finance_officer OR central_bank_admin OR consultant_union_admin OR branch_manager OR branch_officer OR governor OR general_director OR deputy_general_director OR deputy_director OR auditor OR admin OR super_admin OR system_admin OR finance.applications.view | — | `Api\FundingApplicationController@index` |
| 14 | POST | `/api/finance/applications` | Protected — role/permission: project_owner OR finance.applications.create OR general_director OR admin OR super_admin OR system_admin | — | `Api\FundingApplicationController@store` |
| 15 | GET|HEAD | `/api/finance/central-bank/dashboard` | Protected — role/permission: central_bank_admin OR finance.central_bank.dashboard | — | `Api\FundingPartnerController@centralBankDashboard` |
| 16 | GET|HEAD | `/api/finance/cloud` | Protected — role/permission: finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.metrics.view OR finance.metrics.national OR finance.metrics.branch | — | `Api\FundingMetricsController@cloud` |
| 17 | POST | `/api/finance/consultant-assignments/{id}/accept` | Protected — role/permission: consultant_office OR finance.consultant_assignments.accept | — | `Api\FundingConsultantController@acceptAssignment` |
| 18 | POST | `/api/finance/consultant-assignments/{id}/approve-price` | Protected — role/permission: branch_manager OR finance_manager OR general_director OR admin OR super_admin OR system_admin OR finance.consultants.approve_price | — | `Api\FundingConsultantController@approvePrice` |
| 19 | POST | `/api/finance/consultant-assignments/{id}/price-offer` | Protected — role/permission: consultant_office OR finance.consultant_assignments.submit_price OR finance.consultants.submit_price | — | `Api\FundingConsultantController@priceOffer` |
| 20 | POST | `/api/finance/consultant-assignments/{id}/reject` | Protected — role/permission: consultant_office OR finance.consultant_assignments.reject | — | `Api\FundingConsultantController@rejectAssignment` |
| 21 | GET|HEAD | `/api/finance/consultant-assignments` | Protected — role/permission: consultant_union_admin OR consultant_office OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.consultants.view OR finance.consultants.view_all | — | `Api\FundingConsultantController@indexAssignments` |
| 22 | GET|HEAD | `/api/finance/consultant-office/dashboard` | Protected — role: consultant_office | — | `Api\FundingConsultantController@officeDashboard` |
| 23 | POST | `/api/finance/consultant-offices/{id}/activate` | Protected — role/permission: consultant_union_admin OR general_director OR admin OR super_admin OR system_admin OR finance.consultants.activate OR finance.consultants.manage | — | `Api\FundingConsultantController@activateOffice` |
| 24 | POST | `/api/finance/consultant-offices/{id}/approve` | Protected — role/permission: consultant_union_admin OR general_director OR admin OR super_admin OR system_admin OR finance.consultants.approve OR finance.consultants.manage | — | `Api\FundingConsultantController@approveOffice` |
| 25 | GET|HEAD | `/api/finance/consultant-offices/{id}/assignments` | Protected — role/permission: consultant_union_admin OR consultant_office OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.consultants.view OR finance.consultants.view_all | — | `Api\FundingConsultantController@officeAssignments` |
| 26 | GET|HEAD | `/api/finance/consultant-offices/{id}/metrics` | Protected — role/permission: consultant_union_admin OR consultant_office OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.consultants.view OR finance.consultants.view_all | — | `Api\FundingConsultantController@officeMetrics` |
| 27 | GET|HEAD | `/api/finance/consultant-offices/{id}/reports` | Protected — role/permission: consultant_union_admin OR consultant_office OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.consultants.view OR finance.consultants.view_all | — | `Api\FundingConsultantController@officeReports` |
| 28 | POST | `/api/finance/consultant-offices/{id}/suspend` | Protected — role/permission: consultant_union_admin OR general_director OR admin OR super_admin OR system_admin OR finance.consultants.suspend OR finance.consultants.manage | — | `Api\FundingConsultantController@suspendOffice` |
| 29 | GET|HEAD | `/api/finance/consultant-offices/{id}` | Protected — role/permission: consultant_union_admin OR consultant_office OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.consultants.view OR finance.consultants.view_all | — | `Api\FundingConsultantController@showOffice` |
| 30 | PUT | `/api/finance/consultant-offices/{id}` | Protected — role/permission: consultant_union_admin OR general_director OR admin OR super_admin OR system_admin OR finance.consultants.update OR finance.consultants.manage | — | `Api\FundingConsultantController@updateOffice` |
| 31 | GET|HEAD | `/api/finance/consultant-offices` | Protected — role/permission: consultant_union_admin OR consultant_office OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.consultants.view OR finance.consultants.view_all | — | `Api\FundingConsultantController@indexOffices` |
| 32 | POST | `/api/finance/consultant-offices` | Protected — role/permission: consultant_union_admin OR general_director OR admin OR super_admin OR system_admin OR finance.consultants.create OR finance.consultants.manage | — | `Api\FundingConsultantController@storeOffice` |
| 33 | POST | `/api/finance/consultant-reports` | Protected — role/permission: consultant_office OR general_director OR admin OR super_admin OR system_admin OR finance.consultant_reports.create OR finance.consultants.submit_report | — | `Api\FundingConsultantController@storeReport` |
| 34 | GET|HEAD | `/api/finance/consultant-union/dashboard` | Protected — role/permission: consultant_union_admin OR finance.consultant_union.dashboard | — | `Api\FundingConsultantController@unionDashboard` |
| 35 | GET|HEAD | `/api/finance/defaulted/stats` | Protected — role/permission: finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.metrics.view OR finance.metrics.national OR finance.metrics.branch | — | `Api\FundingMetricsController@defaultedStats` |
| 36 | GET|HEAD | `/api/finance/defaulted` | Protected — role/permission: finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.metrics.view OR finance.metrics.national OR finance.metrics.branch | — | `Api\FundingMetricsController@defaulted` |
| 37 | GET|HEAD | `/api/finance/funded/stats` | Protected — role/permission: finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.metrics.view OR finance.metrics.national OR finance.metrics.branch | — | `Api\FundingMetricsController@fundedStats` |
| 38 | GET|HEAD | `/api/finance/funded` | Protected — role/permission: finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.metrics.view OR finance.metrics.national OR finance.metrics.branch | — | `Api\FundingMetricsController@funded` |
| 39 | GET|HEAD | `/api/finance/funding-partner/dashboard` | Protected — role: funding_partner | — | `Api\FundingPartnerController@partnerDashboard` |
| 40 | GET|HEAD | `/api/finance/loans/stats` | Protected — role/permission: funding_partner OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.loans.view OR finance.loans.view_own | — | `Api\FundedLoanController@stats` |
| 41 | POST | `/api/finance/loans/{id}/close` | Protected — role/permission: finance_manager OR general_director OR admin OR super_admin OR system_admin OR finance.loans.close OR finance.loans.manage | — | `Api\FundedLoanController@close` |
| 42 | POST | `/api/finance/loans/{id}/mark-defaulted` | Protected — role/permission: finance_manager OR general_director OR admin OR super_admin OR system_admin OR finance.loans.defaulted OR finance.loans.manage | — | `Api\FundedLoanController@markDefaulted` |
| 43 | GET|HEAD | `/api/finance/loans/{id}/payments` | Protected — role/permission: funding_partner OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.loans.view OR finance.loans.view_own | — | `Api\FundedLoanController@payments` |
| 44 | POST | `/api/finance/loans/{id}/payments` | Protected — role/permission: finance_manager OR general_director OR admin OR super_admin OR system_admin OR finance.loans.payments OR finance.loans.manage | — | `Api\FundedLoanController@storePayment` |
| 45 | GET|HEAD | `/api/finance/loans/{id}` | Protected — role/permission: funding_partner OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.loans.view OR finance.loans.view_own | — | `Api\FundedLoanController@show` |
| 46 | PUT | `/api/finance/loans/{id}` | Protected — role/permission: finance_manager OR funding_partner OR general_director OR admin OR super_admin OR system_admin OR finance.loans.manage OR finance.loans.update_own_status | — | `Api\FundedLoanController@update` |
| 47 | GET|HEAD | `/api/finance/loans` | Protected — role/permission: funding_partner OR finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.loans.view OR finance.loans.view_own | — | `Api\FundedLoanController@index` |
| 48 | GET|HEAD | `/api/finance/manager/dashboard` | Protected — role/permission: finance_manager OR finance_officer OR general_director OR admin OR super_admin OR system_admin OR finance.metrics.view | — | `Api\FundingMetricsController@managerDashboard` |
| 49 | GET|HEAD | `/api/finance/metrics` | Protected — role/permission: finance_manager OR finance_officer OR central_bank_admin OR branch_manager OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.metrics.view OR finance.metrics.national OR finance.metrics.branch | — | `Api\FundingMetricsController@metrics` |
| 50 | GET|HEAD | `/api/finance/my-consultant-assignments` | Protected — role: consultant_office | — | `Api\FundingConsultantController@myAssignments` |
| 51 | GET|HEAD | `/api/finance/my-partner-assignments` | Protected — role: funding_partner | — | `Api\FundingPartnerController@myAssignments` |
| 52 | POST | `/api/finance/partner-assignments/{id}/decision` | Protected — role/permission: funding_partner OR central_bank_admin OR finance_manager OR general_director OR admin OR super_admin OR system_admin OR finance.partner_assignments.decide OR finance.partners.decide | — | `Api\FundingPartnerController@decision` |
| 53 | POST | `/api/finance/partners/{id}/activate` | Protected — role/permission: central_bank_admin OR general_director OR admin OR super_admin OR system_admin OR finance.partners.activate OR finance.partners.manage | — | `Api\FundingPartnerController@activatePartner` |
| 54 | POST | `/api/finance/partners/{id}/approve` | Protected — role/permission: central_bank_admin OR general_director OR admin OR super_admin OR system_admin OR finance.partners.approve OR finance.partners.manage | — | `Api\FundingPartnerController@approvePartner` |
| 55 | GET|HEAD | `/api/finance/partners/{id}/assignments` | Protected — role/permission: central_bank_admin OR funding_partner OR finance_manager OR finance_officer OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.partners.view OR finance.partners.view_all | — | `Api\FundingPartnerController@partnerAssignments` |
| 56 | GET|HEAD | `/api/finance/partners/{id}/decisions` | Protected — role/permission: central_bank_admin OR funding_partner OR finance_manager OR finance_officer OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.partners.view OR finance.partners.view_all | — | `Api\FundingPartnerController@partnerDecisions` |
| 57 | GET|HEAD | `/api/finance/partners/{id}/loans` | Protected — role/permission: central_bank_admin OR funding_partner OR finance_manager OR finance_officer OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.partners.view OR finance.partners.view_all | — | `Api\FundingPartnerController@partnerLoans` |
| 58 | GET|HEAD | `/api/finance/partners/{id}/metrics` | Protected — role/permission: central_bank_admin OR funding_partner OR finance_manager OR finance_officer OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.partners.view OR finance.partners.view_all | — | `Api\FundingPartnerController@partnerMetrics` |
| 59 | POST | `/api/finance/partners/{id}/suspend` | Protected — role/permission: central_bank_admin OR general_director OR admin OR super_admin OR system_admin OR finance.partners.suspend OR finance.partners.manage | — | `Api\FundingPartnerController@suspendPartner` |
| 60 | GET|HEAD | `/api/finance/partners/{id}` | Protected — role/permission: central_bank_admin OR funding_partner OR finance_manager OR finance_officer OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.partners.view OR finance.partners.view_all | — | `Api\FundingPartnerController@show` |
| 61 | PUT | `/api/finance/partners/{id}` | Protected — role/permission: central_bank_admin OR general_director OR admin OR super_admin OR system_admin OR finance.partners.update OR finance.partners.manage | — | `Api\FundingPartnerController@update` |
| 62 | GET|HEAD | `/api/finance/partners` | Protected — role/permission: central_bank_admin OR funding_partner OR finance_manager OR finance_officer OR general_director OR admin OR super_admin OR system_admin OR auditor OR finance.partners.view OR finance.partners.view_all | — | `Api\FundingPartnerController@index` |
| 63 | POST | `/api/finance/partners` | Protected — role/permission: central_bank_admin OR general_director OR admin OR super_admin OR system_admin OR finance.partners.create OR finance.partners.manage | — | `Api\FundingPartnerController@store` |
| 64 | POST | `/api/finance/records/{id}/approve` | Protected — role/permission: general_director OR finance_manager OR admin OR super_admin OR system_admin OR manage_finance | — | `Api\FinancialRecordController@approve` |
| 65 | GET|HEAD | `/api/finance/records/{id}` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR finance_manager OR finance_officer OR central_bank_admin OR auditor OR admin OR super_admin OR system_admin OR view_finance OR manage_finance | — | `Api\FinancialRecordController@show` |
| 66 | PUT | `/api/finance/records/{id}` | Protected — role/permission: general_director OR finance_manager OR admin OR super_admin OR system_admin OR manage_finance | — | `Api\FinancialRecordController@update` |
| 67 | GET|HEAD | `/api/finance/records` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR finance_manager OR finance_officer OR central_bank_admin OR auditor OR admin OR super_admin OR system_admin OR view_finance OR manage_finance | — | `Api\FinancialRecordController@index` |
| 68 | POST | `/api/finance/records` | Protected — role/permission: general_director OR finance_manager OR admin OR super_admin OR system_admin OR manage_finance | — | `Api\FinancialRecordController@store` |

### 32 — Needs (GIS)

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/needs/admin-units` | Protected — role/permission: needs.view OR needs.view_all OR needs.view_branch | — | `Api\NeedController@adminUnits` |
| 2 | GET|HEAD | `/api/needs/analytics` | Protected — Bearer token (auth:sanctum) | — | `Api\NeedController@analytics` |
| 3 | GET|HEAD | `/api/needs/dashboard` | Protected — permission: needs.dashboard | — | `Api\NeedController@dashboard` |
| 4 | GET|HEAD | `/api/needs/export` | Protected — role/permission: needs.view OR needs.view_all OR needs.view_branch | — | `Api\NeedController@export` |
| 5 | GET|HEAD | `/api/needs/lookups` | Protected — role/permission: needs.view OR needs.view_all OR needs.view_branch | — | `Api\NeedController@lookups` |
| 6 | GET|HEAD | `/api/needs/map` | Protected — role/permission: needs.view OR needs.view_all OR needs.view_branch | — | `Api\NeedController@map` |
| 7 | GET|HEAD | `/api/needs/workspace/data-entry` | Protected — role: data_entry | — | `Api\NeedController@dataEntryWorkspace` |
| 8 | GET|HEAD | `/api/needs/workspace/reviewer` | Protected — role: data_reviewer | — | `Api\NeedController@reviewerWorkspace` |
| 9 | POST | `/api/needs/{id}/approve` | Protected — Bearer token (auth:sanctum) | — | `Api\NeedController@approve` |
| 10 | POST | `/api/needs/{id}/classify` | Protected — Bearer token (auth:sanctum) | — | `Api\NeedController@classify` |
| 11 | POST | `/api/needs/{id}/reject` | Protected — Bearer token (auth:sanctum) | — | `Api\NeedController@reject` |
| 12 | POST | `/api/needs/{id}/resolve` | Protected — Bearer token (auth:sanctum) | — | `Api\NeedController@resolve` |
| 13 | POST | `/api/needs/{id}/return` | Protected — Bearer token (auth:sanctum) | — | `Api\NeedController@returnForEdit` |
| 14 | POST | `/api/needs/{id}/review` | Protected — Bearer token (auth:sanctum) | — | `Api\NeedController@review` |
| 15 | GET|HEAD | `/api/needs/{id}` | Protected — role/permission: needs.view OR needs.view_all OR needs.view_branch | — | `Api\NeedController@show` |
| 16 | PUT | `/api/needs/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\NeedController@update` |
| 17 | GET|HEAD | `/api/needs` | Protected — role/permission: needs.view OR needs.view_all OR needs.view_branch | — | `Api\NeedController@index` |
| 18 | POST | `/api/needs` | Protected — role/permission: needs.create OR needs.create_citizen OR needs.create_state | — | `Api\NeedController@store` |

### 40 — Entrepreneur Profiles

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/entrepreneur/my-profile` | Protected — Bearer token (auth:sanctum) | — | `Api\EntrepreneurProfileController@myProfile` |
| 2 | PUT | `/api/entrepreneur/profile/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\EntrepreneurProfileController@update` |
| 3 | POST | `/api/entrepreneur/profile` | Protected — Bearer token (auth:sanctum) | — | `Api\EntrepreneurProfileController@store` |
| 4 | GET|HEAD | `/api/entrepreneur/profiles/export` | Protected — role/permission: general_director OR deputy_general_director OR deputy_director OR admin OR super_admin OR system_admin OR branch_manager OR incubator_manager OR entrepreneur_manager OR entrepreneur.manage | — | `Api\EntrepreneurProfileController@export` |
| 5 | GET|HEAD | `/api/entrepreneur/profiles/public-stats` | Public | throttle:map-public | `Api\EntrepreneurProfileController@publicStats` |
| 6 | GET|HEAD | `/api/entrepreneur/profiles/stats` | Protected — role/permission: general_director OR deputy_general_director OR deputy_director OR admin OR super_admin OR system_admin OR branch_manager OR incubator_manager OR entrepreneur_manager OR entrepreneur.manage | — | `Api\EntrepreneurProfileController@stats` |
| 7 | POST | `/api/entrepreneur/profiles/{id}/review` | Protected — role/permission: general_director OR deputy_general_director OR deputy_director OR admin OR super_admin OR system_admin OR branch_manager OR incubator_manager OR entrepreneur_manager OR entrepreneur.manage | — | `Api\EntrepreneurProfileController@review` |
| 8 | GET|HEAD | `/api/entrepreneur/profiles/{id}` | Protected — role/permission: general_director OR deputy_general_director OR deputy_director OR admin OR super_admin OR system_admin OR branch_manager OR incubator_manager OR entrepreneur_manager OR entrepreneur.manage | — | `Api\EntrepreneurProfileController@show` |
| 9 | GET|HEAD | `/api/entrepreneur/profiles` | Protected — role/permission: general_director OR deputy_general_director OR deputy_director OR admin OR super_admin OR system_admin OR branch_manager OR incubator_manager OR entrepreneur_manager OR entrepreneur.manage | — | `Api\EntrepreneurProfileController@index` |

### 41 — Incubation

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | POST | `/api/incubation/applications/{id}/review` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR incubation.manage | — | `Api\IncubatorController@reviewApplication` |
| 2 | GET|HEAD | `/api/incubation/applications/{id}` | Protected — Bearer token (auth:sanctum) | — | `Api\IncubatorController@showApplication` |
| 3 | GET|HEAD | `/api/incubation/applications` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR branch_officer OR incubator_manager OR incubator_mentor OR admin OR super_admin OR system_admin OR auditor OR incubation.view | — | `Api\IncubatorController@allApplications` |
| 4 | POST | `/api/incubation/apply` | Protected — Bearer token (auth:sanctum) | — | `Api\IncubatorController@apply` |
| 5 | GET|HEAD | `/api/incubation/my-applications` | Protected — Bearer token (auth:sanctum) | — | `Api\IncubatorController@myApplications` |
| 6 | GET|HEAD | `/api/incubation/my-project` | Protected — Bearer token (auth:sanctum) | — | `Api\IncubatorController@myProject` |
| 7 | GET|HEAD | `/api/incubation/my-sessions` | Protected — role/permission: incubator_mentor OR incubator_manager OR admin OR super_admin OR system_admin OR incubation.mentor | — | `Api\IncubatorController@myMentoringSessions` |
| 8 | POST | `/api/incubation/projects/{id}/reports` | Protected — Bearer token (auth:sanctum) | throttle:incubation-report | `Api\IncubatorController@storeProgressReport` |
| 9 | POST | `/api/incubation/projects/{id}/sessions` | Protected — role/permission: incubator_mentor OR incubator_manager OR admin OR super_admin OR system_admin OR incubation.mentor | — | `Api\IncubatorController@storeMentoringSession` |
| 10 | GET|HEAD | `/api/incubation/projects/{id}` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR branch_officer OR incubator_manager OR incubator_mentor OR admin OR super_admin OR system_admin OR auditor OR incubation.view | — | `Api\IncubatorController@showProject` |
| 11 | PUT | `/api/incubation/projects/{id}` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR incubation.manage | — | `Api\IncubatorController@updateProject` |
| 12 | GET|HEAD | `/api/incubation/projects` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR branch_officer OR incubator_manager OR incubator_mentor OR admin OR super_admin OR system_admin OR auditor OR incubation.view | — | `Api\IncubatorController@projects` |
| 13 | GET|HEAD | `/api/incubation/sessions` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR branch_officer OR incubator_manager OR incubator_mentor OR admin OR super_admin OR system_admin OR auditor OR incubation.view | — | `Api\IncubatorController@indexMentoringSessions` |
| 14 | GET|HEAD | `/api/incubation/stats` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR branch_officer OR incubator_manager OR incubator_mentor OR admin OR super_admin OR system_admin OR auditor OR incubation.view | — | `Api\IncubatorController@stats` |
| 15 | GET|HEAD | `/api/incubators/{id}/applications` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR branch_officer OR incubator_manager OR incubator_mentor OR admin OR super_admin OR system_admin OR auditor OR incubation.view | — | `Api\IncubatorController@applications` |
| 16 | POST | `/api/incubators/{id}/programs` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR incubation.manage | — | `Api\IncubatorController@storeProgram` |
| 17 | GET|HEAD | `/api/incubators/{id}` | Protected — role/permission: general_director OR deputy_general_director OR branch_manager OR branch_officer OR incubator_manager OR incubator_mentor OR admin OR super_admin OR system_admin OR auditor OR incubation.view | — | `Api\IncubatorController@show` |
| 18 | PUT | `/api/incubators/{id}` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR incubation.manage | — | `Api\IncubatorController@update` |
| 19 | GET|HEAD | `/api/incubators` | Public | throttle:map-public | `Api\IncubatorController@index` |
| 20 | POST | `/api/incubators` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR incubation.manage | — | `Api\IncubatorController@store` |

### 42 — News

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/news/stats` | Protected — role/permission: media_manager OR general_director OR admin OR super_admin OR system_admin OR news.manage | — | `Api\NewsController@stats` |
| 2 | DELETE | `/api/news/{id}` | Protected — role/permission: media_manager OR general_director OR admin OR super_admin OR system_admin OR news.manage | — | `Api\NewsController@destroy` |
| 3 | GET|HEAD | `/api/news/{id}` | Public | throttle:map-public | `Api\NewsController@show` |
| 4 | PUT | `/api/news/{id}` | Protected — role/permission: media_manager OR general_director OR admin OR super_admin OR system_admin OR news.manage | — | `Api\NewsController@update` |
| 5 | GET|HEAD | `/api/news` | Public | throttle:map-public | `Api\NewsController@index` |
| 6 | POST | `/api/news` | Protected — role/permission: media_manager OR general_director OR admin OR super_admin OR system_admin OR news.manage | — | `Api\NewsController@store` |

### 43 — Success Stories

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/success-stories/slug/{slug}` | Public | throttle:map-public | `Api\SuccessStoryController@showBySlug` |
| 2 | GET|HEAD | `/api/success-stories/stats` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR branch_manager OR story.manage | — | `Api\SuccessStoryController@stats` |
| 3 | DELETE | `/api/success-stories/{id}` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR branch_manager OR story.manage | — | `Api\SuccessStoryController@destroy` |
| 4 | GET|HEAD | `/api/success-stories/{id}` | Public | throttle:map-public | `Api\SuccessStoryController@show` |
| 5 | PUT | `/api/success-stories/{id}` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR branch_manager OR story.manage | — | `Api\SuccessStoryController@update` |
| 6 | GET|HEAD | `/api/success-stories` | Public | throttle:map-public | `Api\SuccessStoryController@index` |
| 7 | POST | `/api/success-stories` | Protected — role/permission: general_director OR admin OR super_admin OR system_admin OR incubator_manager OR branch_manager OR story.manage | — | `Api\SuccessStoryController@store` |

### 50 — Syria Locations

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/api/locations/communities` | Public | throttle:60,1 | `Api\SyriaLocationController@communities` |
| 2 | GET|HEAD | `/api/locations/districts` | Public | throttle:60,1 | `Api\SyriaLocationController@districts` |
| 3 | GET|HEAD | `/api/locations/governorates` | Public | throttle:60,1 | `Api\SyriaLocationController@governorates` |
| 4 | GET|HEAD | `/api/locations/map` | Public | throttle:60,1 | `Api\SyriaLocationController@mapPoints` |
| 5 | GET|HEAD | `/api/locations/search` | Public | throttle:60,1 | `Api\SyriaLocationController@search` |
| 6 | GET|HEAD | `/api/locations/subdistricts` | Public | throttle:60,1 | `Api\SyriaLocationController@subdistricts` |

### 99 — Web (non-API prefix)

| # | Method | Path | Auth | Throttle | Controller |
|---|--------|------|------|----------|------------|
| 1 | GET|HEAD | `/certificates/verify` | Public | throttle:print-routes | `Api\CertificateController@verifyPage` |
| 2 | GET|HEAD | `/certificates/{certificate_code}/pdf` | Public | throttle:certificate-print-by-code | `CertificatePrintController@pdfByCode` |
| 3 | GET|HEAD | `/certificates/{certificate_code}/print` | Public | throttle:certificate-print-by-code | `CertificatePrintController@showByCode` |
| 4 | GET|HEAD | `/certificates/{certificate_code}/qr` | Public | throttle:certificate-print-by-code | `CertificatePrintController@publicQrImage` |
| 5 | GET|HEAD | `/certificates/{id}/pdf` | Signed URL (public, signature required) | throttle:print-routes | `CertificatePrintController@pdf` |
| 6 | GET|HEAD | `/certificates/{id}/print` | Signed URL (public, signature required) | throttle:print-routes | `CertificatePrintController@show` |
| 7 | GET|HEAD | `/sanctum/csrf-cookie` | Public | — | `Laravel\Sanctum\Http\Controllers\CsrfCookieController@show` |
| 8 | GET|HEAD | `/storage/{path}` | Public | — | `Closure` |
| 9 | PUT | `/storage/{path}` | Public | — | `Closure` |
| 10 | GET|HEAD | `/trainees/{id}/card/pdf` | Signed URL (public, signature required) | throttle:print-routes | `TraineePrintController@pdf` |
| 11 | GET|HEAD | `/trainees/{id}/card` | Signed URL (public, signature required) | throttle:print-routes | `TraineePrintController@show` |
| 12 | GET|HEAD | `/trainers/{id}/card/pdf` | Signed URL (public, signature required) | throttle:print-routes | `TrainerPrintController@pdf` |
| 13 | GET|HEAD | `/trainers/{id}/card` | Signed URL (public, signature required) | throttle:print-routes | `TrainerPrintController@show` |
| 14 | GET|HEAD | `/training-centers/{id}/certificate/pdf` | Signed URL (public, signature required) | throttle:print-routes | `TrainingCenterPrintController@pdf` |
| 15 | GET|HEAD | `/training-centers/{id}/certificate` | Signed URL (public, signature required) | throttle:print-routes | `TrainingCenterPrintController@show` |
| 16 | GET|HEAD | `/verify-certificate/{certificate_code}` | Public | throttle:print-routes | `CertificatePrintController@publicView` |

---

## Web Routes (Print & Verify — not under `/api`)

These routes are registered in `routes/web.php`. On Hostinger they are served via the same Laravel entry (`/api/...` path depends on deployment; typically use signed URLs returned by API resources).

| Method | Path | Auth | Controller |
|--------|------|------|------------|
| GET|HEAD | `/sanctum/csrf-cookie` | Public | `Laravel\Sanctum\Http\Controllers\CsrfCookieController@show` |
| GET|HEAD | `/certificates/{id}/print` | Signed URL (public, signature required) | `CertificatePrintController@show` |
| GET|HEAD | `/certificates/{id}/pdf` | Signed URL (public, signature required) | `CertificatePrintController@pdf` |
| GET|HEAD | `/trainers/{id}/card` | Signed URL (public, signature required) | `TrainerPrintController@show` |
| GET|HEAD | `/trainers/{id}/card/pdf` | Signed URL (public, signature required) | `TrainerPrintController@pdf` |
| GET|HEAD | `/training-centers/{id}/certificate` | Signed URL (public, signature required) | `TrainingCenterPrintController@show` |
| GET|HEAD | `/training-centers/{id}/certificate/pdf` | Signed URL (public, signature required) | `TrainingCenterPrintController@pdf` |
| GET|HEAD | `/trainees/{id}/card` | Signed URL (public, signature required) | `TraineePrintController@show` |
| GET|HEAD | `/trainees/{id}/card/pdf` | Signed URL (public, signature required) | `TraineePrintController@pdf` |
| GET|HEAD | `/verify-certificate/{certificate_code}` | Public | `CertificatePrintController@publicView` |
| GET|HEAD | `/certificates/verify` | Public | `Api\CertificateController@verifyPage` |
| GET|HEAD | `/certificates/{certificate_code}/print` | Public | `CertificatePrintController@showByCode` |
| GET|HEAD | `/certificates/{certificate_code}/pdf` | Public | `CertificatePrintController@pdfByCode` |
| GET|HEAD | `/certificates/{certificate_code}/qr` | Public | `CertificatePrintController@publicQrImage` |
| GET|HEAD | `/storage/{path}` | Public | `Closure` |
| PUT | `/storage/{path}` | Public | `Closure` |

---

## Health Check

| Method | Path | Auth |
|--------|------|------|
| GET | `/up` | Public |

---

*End of API Documentation v2.0*