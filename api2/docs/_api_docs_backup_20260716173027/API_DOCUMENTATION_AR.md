<div dir="rtl" lang="ar">

# توثيق API — منصة الهيئة (SMEDC)

> **الإصدار:** 2.0.0 | **تاريخ التوليد:** 2026-07-16 17:29:24 | **المصدر:** كود Laravel الفعلي

## فهرس المحتويات

- [الرابط الأساسي Base URL](#base-url)
- [المصادقة Sanctum](#authentication)
- [الأدوار والصلاحيات](#roles-permissions)
- [Pagination](#pagination)
- [الوحدات والمسارات](#modules)
- [Web — طباعة وتحقق](#web-routes)
- [النطاق الجغرافي](#geographic-scope)
- [GIS والخريطة](#gis-map)
- [الملفات والطباعة](#files-print)
- [مراجعة الحماية](#security-review)
- [ملحق الحالات والقيم](#appendix)


---

<a id="base-url"></a>
## الرابط الأساسي (Base URL)

### Local

| البيئة | API Base | مثال |
|--------|----------|------|
| `php artisan serve` | `http://127.0.0.1:8000/api` | `POST http://127.0.0.1:8000/api/login` |

**المصدر:** `front/assets/js/core/config.js` (سطر 38-39) و `bootstrap/app.php` (prefix `api`).

### Production (Hostinger — smeda.gov.sy)

| المكوّن | الرابط | الدليل |
|---------|--------|--------|
| Frontend | `https://smeda.gov.sy` | `deploy/hostinger/public_html/config.php` |
| Laravel entry | `https://smeda.gov.sy/api/` | `deploy/hostinger/public_html/api/index.php` |
| **API Base** | **`https://smeda.gov.sy/api/api`** | `config.php` → `api_base_url` + `config.js` سطر 42 |

> **تنبيه:** المسار `/api/api/...` **ليس خطأ إعداد** في هذا المشروع: المجلد الفرعي `public_html/api/` + بادئة Laravel `/api` يُنتجان `/api/api/login` للمسار الداخلي `api/login`.

### Web / Print / Signed URLs (Production)

| النوع | Base | مثال |
|-------|------|------|
| Backend (طباعة، PDF، QR) | `https://smeda.gov.sy/api` | `GET https://smeda.gov.sy/api/certificates/{code}/print` |

**المصدر:** `config.js` → `BACKEND_BASE_URL` = `${frontendBase}/api`


---

<a id="authentication"></a>
## المصادقة (Laravel Sanctum)

### Headers

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
Content-Type: application/json
```

### Token Expiration

- **المدة:** 480 دقيقة (افتراضي من `config/sanctum.php` → `SANCTUM_TOKEN_EXPIRATION`)
- عند انتهاء الصلاحية: استجابة **401** من middleware `auth:sanctum`

### POST /api/register

| الحقل | النوع | مطلوب | Validation |
|-------|-------|------:|------------|
| name | string | نعم | required, max:255 |
| email | email | نعم | required, unique:users |
| password | string | نعم | required, confirmed, min:8 |
| password_confirmation | string | نعم | مع password |
| account_type | string | نعم | Rule::in(SelfRegistrationCatalog::validationKeys()) |
| device_name | string | لا | nullable, max:255 |
| role, roles, permissions, entity_type, training_center_id, trainer_id, trainee_id, is_active | — | **محظور** | prohibited |

**account_type المسموحة:** trainee, trainer, center, project_owner, incubation_applicant, entrepreneur_tech, consultant, consulting_client, jobseeker, employer, entrepreneur (يُحوَّل إلى project_owner)

**Rate limit:** 5 طلبات/دقيقة/IP (`throttle:register`)

**Response 201:**
```json
{
  "message": "تم إنشاء الحساب بنجاح.",
  "token": "1|example_token",
  "token_type": "Bearer",
  "redirect_to_form": "trainee",
  "entity_pending_approval": true,
  "user": { "id": 1, "name": "...", "roles": ["trainee_user"] }
}
```

### POST /api/login

| الحقل | مطلوب | ملاحظات |
|-------|------:|---------|
| email | نعم | |
| password | نعم | |
| device_name | لا | افتراضي `front-web` |

- الحساب المعطل (`is_active=false`): فشل المصادقة → **401**
- **Rate limit:** 10/دقيقة لكل email|IP

### POST /api/logout

- يحذف **التوكن الحالي فقط** (`currentAccessToken()->delete()`)
- يتطلب Bearer Token

### GET /api/me | PUT /api/me | POST /api/me/change-password

راجع الأقسام التفصيلية في الوحدة **User Profile**.


---

<a id="roles-permissions"></a>
## الأدوار والصلاحيات

| Role | الاسم العربي |
|------|-------------|
| `general_director` | المدير العام |
| `admin` | مسؤول النظام |
| `deputy_general_director` | نائب المدير العام |
| `governor` | محافظ |
| `branch_manager` | مدير فرع |
| `branch_officer` | موظف فرع |
| `workforce_manager` | مدير القوى العاملة |
| `training_manager` | مدير التدريب |
| `training_supervisor` | مشرف تدريب |
| `deputy_director` | نائب مدير |
| `center_user` | مستخدم مركز تدريبي |
| `trainer_user` | مدرب |
| `trainee_user` | متدرب |
| `auditor` | مدقق |
| `data_entry` | مدخل بيانات |
| `data_reviewer` | مدقق بيانات |
| `project_services_manager` | مدير خدمات المشاريع |
| `development_manager` | مدير التنمية |
| `local_development_manager` | مدير التنمية المحلية |
| `finance_manager` | مدير التمويل |
| `finance_officer` | موظف تمويل |
| `consultant_office` | مكتب استشاري |
| `funding_partner` | شريك تمويل |
| `consultant_union_admin` | إدارة نقابة الاقتصاديين |
| `central_bank_admin` | إدارة البنك المركزي |
| `project_owner` | صاحب مشروع |
| `incubator_manager` | مدير حاضنة |
| `incubator_mentor` | مرشد حاضنة |
| `entrepreneur_manager` | مدير ريادة الأعمال |
| `media_manager` | مدير إعلام |
| `super_admin` | مدير أعلى |
| `system_admin` | مدير صلاحيات النظام |

**المصدر:** `database/seeders/RolePermissionSeeder.php` — 32 دوراً و 165 صلاحية تقريباً.

> مصفوفة الصلاحيات التفصيلية لكل Endpoint مذكورة في قسم كل مسار (Policy / Permission / Role).

---

<a id="pagination"></a>
## Pagination

النمط الافتراضي: **Laravel LengthAwarePaginator** عبر `paginate()`.

| Parameter | الافتراضي | الحد الأقصى | المصدر |
|-----------|----------:|------------:|--------|
| `page` | 1 | — | Laravel |
| `per_page` | 20 (أو 25/30/50 حسب Controller) | 100 (أغلب القوائم) | Controller |

**مثال استجابة:**
```json
{
  "current_page": 1,
  "data": [],
  "first_page_url": "...",
  "from": 1,
  "last_page": 10,
  "last_page_url": "...",
  "links": [],
  "next_page_url": "...",
  "path": "...",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 200
}
```

> **ملاحظة:** بعض Controllers تُعيد `response()->json($paginator)` مباشرة دون غلاف `data/meta` إضافي.


---

<a id="modules"></a>
## الوحدات — جدول مختصر

| الوحدة | عدد Endpoints |
|--------|--------------:|
| [Admin](#module-Admin) | 34 |
| [Agreements](#module-Agreements) | 5 |
| [Authentication](#module-Authentication) | 3 |
| [Branches](#module-Branches) | 6 |
| [Certificate Verification](#module-Certificate-Verification) | 10 |
| [Certificates](#module-Certificates) | 7 |
| [Dashboard](#module-Dashboard) | 1 |
| [Entrepreneur Profiles](#module-Entrepreneur-Profiles) | 9 |
| [Governorates](#module-Governorates) | 1 |
| [Health Check](#module-Health-Check) | 1 |
| [Inbox](#module-Inbox) | 8 |
| [Incubators](#module-Incubators) | 6 |
| [Maps](#module-Maps) | 3 |
| [Needs GIS](#module-Needs-GIS) | 18 |
| [News](#module-News) | 6 |
| [Notifications](#module-Notifications) | 5 |
| [Other Routes](#module-Other-Routes) | 138 |
| [Printing](#module-Printing) | 4 |
| [Program Bank](#module-Program-Bank) | 18 |
| [Public APIs](#module-Public-APIs) | 8 |
| [Signatures](#module-Signatures) | 1 |
| [Success Stories](#module-Success-Stories) | 7 |
| [Trainees](#module-Trainees) | 2 |
| [Trainers](#module-Trainers) | 2 |
| [Training Centers](#module-Training-Centers) | 2 |
| [Training Courses](#module-Training-Courses) | 9 |
| [Training Kit Nominations](#module-Training-Kit-Nominations) | 4 |
| [Training Kits](#module-Training-Kits) | 2 |
| [Training Programs](#module-Training-Programs) | 2 |
| [Training Requests](#module-Training-Requests) | 1 |
| [Training Supervisors](#module-Training-Supervisors) | 1 |
| [User Profile](#module-User-Profile) | 9 |
| [Web (Print/Verify/Files)](#module-Web-(Print/Verify/Files)) | 3 |
| [Workforce](#module-Workforce) | 10 |

---

<a id="module-Admin"></a>
## وحدة: Admin


### GET `api/admin/activity-logs`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ActivityLogController::index` |
| Controller | `App\Http\Controllers\Api\Admin\ActivityLogController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/activity-logs` |
| Production URL | `https://smeda.gov.sy/api/api/admin/activity-logs` |
| Permission | `auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access` |
| API Resource | `AuditLogResource::collection` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=25 max=100 |
| `search` | mixed | request->only |
| `user_id` | mixed | request->only |
| `action` | mixed | request->only |
| `module` | mixed | request->only |
| `entity_type` | mixed | request->only |
| `email` | mixed | request->only |
| `ip` | mixed | request->only |
| `date_from` | mixed | request->only |
| `date_to` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/activity-logs/export`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ActivityLogController::export` |
| Controller | `App\Http\Controllers\Api\Admin\ActivityLogController` |
| Method | `export` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/activity-logs/export` |
| Production URL | `https://smeda.gov.sy/api/api/admin/activity-logs/export` |
| Permission | `auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `search` | mixed | request->only |
| `user_id` | mixed | request->only |
| `action` | mixed | request->only |
| `module` | mixed | request->only |
| `entity_type` | mixed | request->only |
| `email` | mixed | request->only |
| `ip` | mixed | request->only |
| `date_from` | mixed | request->only |
| `date_to` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/activity-logs/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ActivityLogController::show` |
| Controller | `App\Http\Controllers\Api\Admin\ActivityLogController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/activity-logs/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/activity-logs/{id}` |
| Permission | `auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access` |
| API Resource | `AuditLogResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/users/{id}/activity-logs`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ActivityLogController::forUser` |
| Controller | `App\Http\Controllers\Api\Admin\ActivityLogController` |
| Method | `forUser` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/activity-logs` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/activity-logs` |
| Permission | `auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access` |
| API Resource | `AuditLogResource::collection` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=25 max=100 |
| `search` | mixed | request->only |
| `action` | mixed | request->only |
| `module` | mixed | request->only |
| `date_from` | mixed | request->only |
| `date_to` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/access-summary`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `Closure::-` |
| Controller | `غير محدد` |
| Method | `غير محدد` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/access-summary` |
| Production URL | `https://smeda.gov.sy/api/api/admin/access-summary` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/users`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::index` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `viewAny @ User::class` |
| API Resource | `UserAccessResource::collection` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `search` | mixed | request->only |
| `role` | mixed | request->only |
| `is_active` | mixed | request->only |
| `training_center_id` | mixed | request->only |
| `training_supervisor_id` | mixed | request->only |
| `created_from` | mixed | request->only |
| `created_to` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/users`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::store` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\StoreAdminUserRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:255` |
| `email` | `required`, `email`, `max:255`, `unique:users,email` |
| `phone` | `nullable`, `string`, `max:30` |
| `password` | `required`, `confirmed`, `Illuminate\Validation\Rules\Password` |
| `role` | `required`, `string`, `Illuminate\Validation\Rules\Exists` |
| `is_active` | `sometimes`, `boolean` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id`, `required_if:role,branch_manager` |
| `branch_id` | `nullable`, `integer`, `exists:branches,id`, `required_if:role,branch_manager` |
| `training_center_id` | `nullable`, `integer`, `exists:training_centers,id` |
| `training_supervisor_id` | `nullable`, `integer`, `exists:training_supervisors,id` |
| `trainer_id` | `nullable`, `integer`, `exists:trainers,id` |
| `trainee_id` | `nullable`, `integer`, `exists:trainees,id` |
| `permissions` | `sometimes`, `array` |
| `permissions.*` | `string`, `Illuminate\Validation\Rules\Exists` |

```json
{
    "name": "...",
    "email": "...",
    "phone": "...",
    "password": "...",
    "role": "...",
    "is_active": "...",
    "governorate_id": "...",
    "branch_id": "...",
    "training_center_id": "...",
    "training_supervisor_id": "...",
    "trainer_id": "...",
    "trainee_id": "...",
    "permissions": "...",
    "permissions.*": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/users/{id}/access`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::show` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/access` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/access` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `viewAccess @ $target` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/users/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::show` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `viewAccess @ $target` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/admin/users/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::update` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\UpdateAdminUserRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `sometimes`, `string`, `max:255` |
| `email` | `sometimes`, `email`, `max:255`, `Illuminate\Validation\Rules\Unique` |
| `phone` | `sometimes`, `nullable`, `string`, `max:30` |
| `is_active` | `sometimes`, `boolean` |
| `governorate_id` | `sometimes`, `nullable`, `integer`, `exists:governorates,id` |
| `branch_id` | `sometimes`, `nullable`, `integer`, `exists:branches,id` |
| `training_center_id` | `sometimes`, `nullable`, `integer`, `exists:training_centers,id` |
| `training_supervisor_id` | `sometimes`, `nullable`, `integer`, `exists:training_supervisors,id` |
| `trainer_id` | `sometimes`, `nullable`, `integer`, `exists:trainers,id` |
| `trainee_id` | `sometimes`, `nullable`, `integer`, `exists:trainees,id` |
| `password` | `prohibited` |
| `password_confirmation` | `prohibited` |

```json
{
    "name": "...",
    "email": "...",
    "phone": "...",
    "is_active": "...",
    "governorate_id": "...",
    "branch_id": "...",
    "training_center_id": "...",
    "training_supervisor_id": "...",
    "trainer_id": "...",
    "trainee_id": "...",
    "password": "...",
    "password_confirmation": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/users/{id}/change-password`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::changePassword` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `changePassword` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/change-password` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/change-password` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\ChangeUserPasswordRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `password` | `required`, `confirmed`, `Illuminate\Validation\Rules\Password` |

```json
{
    "password": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/users/{id}/roles/sync`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::syncRoles` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `syncRoles` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/roles/sync` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/roles/sync` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\SyncUserRolesRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `roles` | `required`, `array`, `min:1` |
| `roles.*` | `string`, `Illuminate\Validation\Rules\Exists` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `branch_id` | `nullable`, `integer`, `exists:branches,id` |

```json
{
    "roles": "...",
    "roles.*": "...",
    "governorate_id": "...",
    "branch_id": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/users/{id}/permissions/sync`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::syncPermissions` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `syncPermissions` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/permissions/sync` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/permissions/sync` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\SyncUserPermissionsRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `permissions` | `present`, `array` |
| `permissions.*` | `string`, `Illuminate\Validation\Rules\Exists` |

```json
{
    "permissions": "...",
    "permissions.*": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/users/{id}/roles`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::assignRole` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `assignRole` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/roles` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/roles` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\AssignUserRoleRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `role` | `required`, `string`, `max:100`, `Illuminate\Validation\Rules\Exists` |

```json
{
    "role": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/admin/users/{id}/roles/{role}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::revokeRole` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `revokeRole` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/roles/{role}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/roles/{role}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\RevokeUserRoleRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `role` | نعم | معامل مسار |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `role` | `sometimes`, `string`, `max:100` |

**Status Codes المحتملة:** 200, 204, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/users/{id}/permissions`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::assignPermission` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `assignPermission` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/permissions` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/permissions` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\AssignUserPermissionRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `permission` | `required`, `string`, `max:100`, `Illuminate\Validation\Rules\Exists` |

```json
{
    "permission": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/admin/users/{id}/permissions/{permission}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::revokePermission` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `revokePermission` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/permissions/{permission}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/permissions/{permission}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\RevokeUserPermissionRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `permission` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PATCH `api/admin/users/{id}/status`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::updateStatus` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `updateStatus` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/status` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/status` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\UpdateUserStatusRequest` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `is_active` | boolean | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `is_active` | `required`, `boolean` |

```json
{
    "is_active": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PATCH `api/admin/users/{id}/parent`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::reassignParent` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `reassignParent` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/parent` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/parent` |
| Permission | `admin|super_admin|system_admin|general_director` |
| API Resource | `UserAccessResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `parent_user_id` | mixed | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `parent_user_id` | `nullable`, `integer`, `exists:users,id` |

```json
{
    "parent_user_id": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/users/{id}/children`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::childrenOf` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `childrenOf` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/users/{id}/children` |
| Production URL | `https://smeda.gov.sy/api/api/admin/users/{id}/children` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `viewAny @ User::class` |
| API Resource | `UserAccessResource::collection` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `search` | mixed | request->only |
| `is_active` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/my-children`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::myChildren` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `myChildren` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/my-children` |
| Production URL | `https://smeda.gov.sy/api/api/admin/my-children` |
| Permission | `admin|super_admin|system_admin|general_director` |
| API Resource | `UserAccessResource::collection` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `search` | mixed | request->only |
| `is_active` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/my-delegatable`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserAccessController::delegatableOptions` |
| Controller | `App\Http\Controllers\Api\Admin\UserAccessController` |
| Method | `delegatableOptions` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/my-delegatable` |
| Production URL | `https://smeda.gov.sy/api/api/admin/my-delegatable` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/roles`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `RoleController::index` |
| Controller | `App\Http\Controllers\Api\Admin\RoleController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/roles` |
| Production URL | `https://smeda.gov.sy/api/api/admin/roles` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `viewAny @ Role::class` |
| API Resource | `RoleResource::collection` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/roles`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `RoleController::store` |
| Controller | `App\Http\Controllers\Api\Admin\RoleController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/roles` |
| Production URL | `https://smeda.gov.sy/api/api/admin/roles` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\StoreRoleRequest` |
| API Resource | `RoleResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:100`, `regex:/^[a-z][a-z0-9_]*$/`, `Illuminate\Validation\Rules\Unique`, `Illuminate\Validation\Rules\NotIn` |

```json
{
    "name": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/roles/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `RoleController::show` |
| Controller | `App\Http\Controllers\Api\Admin\RoleController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/roles/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/roles/{id}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `view @ $role` |
| API Resource | `RoleResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PATCH `api/admin/roles/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `RoleController::update` |
| Controller | `App\Http\Controllers\Api\Admin\RoleController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/roles/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/roles/{id}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\UpdateRoleRequest` |
| API Resource | `RoleResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:100`, `regex:/^[a-z][a-z0-9_]*$/`, `Illuminate\Validation\Rules\Unique`, `Illuminate\Validation\Rules\NotIn` |

```json
{
    "name": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/admin/roles/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `RoleController::destroy` |
| Controller | `App\Http\Controllers\Api\Admin\RoleController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/roles/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/roles/{id}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `delete @ $role` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/roles/{id}/permissions`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `RoleController::syncPermissions` |
| Controller | `App\Http\Controllers\Api\Admin\RoleController` |
| Method | `syncPermissions` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/roles/{id}/permissions` |
| Production URL | `https://smeda.gov.sy/api/api/admin/roles/{id}/permissions` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\SyncRolePermissionsRequest` |
| API Resource | `RoleResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `permissions` | `required`, `array`, `min:0` |
| `permissions.*` | `string`, `max:100`, `Illuminate\Validation\Rules\Exists` |

```json
{
    "permissions": "...",
    "permissions.*": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/admin/roles/{id}/permissions/{permissionId}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `RoleController::detachPermission` |
| Controller | `App\Http\Controllers\Api\Admin\RoleController` |
| Method | `detachPermission` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/roles/{id}/permissions/{permissionId}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/roles/{id}/permissions/{permissionId}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `syncPermissions @ $role` |
| API Resource | `RoleResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `permissionId` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/permissions`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PermissionController::index` |
| Controller | `App\Http\Controllers\Api\Admin\PermissionController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/permissions` |
| Production URL | `https://smeda.gov.sy/api/api/admin/permissions` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `viewAny @ Permission::class` |
| API Resource | `PermissionResource::collection` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `grouped` | boolean | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/admin/permissions`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PermissionController::store` |
| Controller | `App\Http\Controllers\Api\Admin\PermissionController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/permissions` |
| Production URL | `https://smeda.gov.sy/api/api/admin/permissions` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\StorePermissionRequest` |
| API Resource | `PermissionResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:100`, `regex:/^[a-z][a-z0-9_]*$/`, `Illuminate\Validation\Rules\Unique` |

```json
{
    "name": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/admin/permissions/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PermissionController::show` |
| Controller | `App\Http\Controllers\Api\Admin\PermissionController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/permissions/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/permissions/{id}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `view @ $permission` |
| API Resource | `PermissionResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PATCH `api/admin/permissions/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PermissionController::update` |
| Controller | `App\Http\Controllers\Api\Admin\PermissionController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/permissions/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/permissions/{id}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Form Request | `App\Http\Requests\Admin\UpdatePermissionRequest` |
| API Resource | `PermissionResource` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:100`, `regex:/^[a-z][a-z0-9_]*$/`, `Illuminate\Validation\Rules\Unique` |

```json
{
    "name": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/admin/permissions/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PermissionController::destroy` |
| Controller | `App\Http\Controllers\Api\Admin\PermissionController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:admin-access, role_or_permission:admin|super_admin|system_admin|general_director` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/admin/permissions/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/admin/permissions/{id}` |
| Permission | `admin|super_admin|system_admin|general_director` |
| Policy / authorize() | `delete @ $permission` |
| Rate Limit | 120 طلبات/دقيقة لكل admin user |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Agreements"></a>
## وحدة: Agreements


### GET `api/agreements`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AgreementController::index` |
| Controller | `App\Http\Controllers\Api\AgreementController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/agreements` |
| Production URL | `https://smeda.gov.sy/api/api/agreements` |
| Policy / authorize() | `viewAny @ Agreement::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |
| `scope_type` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/agreements`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AgreementController::store` |
| Controller | `App\Http\Controllers\Api\AgreementController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/agreements` |
| Production URL | `https://smeda.gov.sy/api/api/agreements` |
| Policy / authorize() | `create @ Agreement::class` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/agreements/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AgreementController::show` |
| Controller | `App\Http\Controllers\Api\AgreementController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/agreements/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/agreements/{id}` |
| Policy / authorize() | `view @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/agreements/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AgreementController::update` |
| Controller | `App\Http\Controllers\Api\AgreementController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/agreements/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/agreements/{id}` |
| Policy / authorize() | `update @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/agreements/{id}/approve`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AgreementController::approve` |
| Controller | `App\Http\Controllers\Api\AgreementController` |
| Method | `approve` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/agreements/{id}/approve` |
| Production URL | `https://smeda.gov.sy/api/api/agreements/{id}/approve` |
| Policy / authorize() | `approve @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Authentication"></a>
## وحدة: Authentication


### POST `api/register`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AuthController::register` |
| Controller | `App\Http\Controllers\Api\AuthController` |
| Method | `register` |
| Route Name | `—` |
| Middleware | `api, throttle:register` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/register` |
| Production URL | `https://smeda.gov.sy/api/api/register` |
| Rate Limit | 5 طلبات/دقيقة لكل IP |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:255` |
| `email` | `required`, `email`, `max:255`, `unique:users,email` |
| `password` | `required`, `confirmed` |
| `account_type` | `required` |
| `device_name` | `nullable`, `string`, `max:255` |
| `role` | `prohibited` |
| `roles` | `prohibited` |
| `permissions` | `prohibited` |
| `entity_type` | `prohibited` |
| `training_center_id` | `prohibited` |
| `trainer_id` | `prohibited` |
| `trainee_id` | `prohibited` |
| `is_active` | `prohibited` |

```json
{
    "name": "...",
    "email": "...",
    "password": "...",
    "account_type": "...",
    "device_name": "...",
    "role": "...",
    "roles": "...",
    "permissions": "...",
    "entity_type": "...",
    "training_center_id": "...",
    "trainer_id": "...",
    "trainee_id": "...",
    "is_active": "..."
}
```


**Status Codes المحتملة:** 200, 201, 404, 422, 429, 500


### POST `api/login`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AuthController::login` |
| Controller | `App\Http\Controllers\Api\AuthController` |
| Method | `login` |
| Route Name | `—` |
| Middleware | `api, throttle:login` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/login` |
| Production URL | `https://smeda.gov.sy/api/api/login` |
| Rate Limit | 10 طلبات/دقيقة لكل (email|IP) — AppServiceProvider |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `email` | `required`, `email` |
| `password` | `required`, `string` |
| `device_name` | `nullable`, `string`, `max:255` |

```json
{
    "email": "...",
    "password": "...",
    "device_name": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 404, 422, 429, 500


### POST `api/logout`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AuthController::logout` |
| Controller | `App\Http\Controllers\Api\AuthController` |
| Method | `logout` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/logout` |
| Production URL | `https://smeda.gov.sy/api/api/logout` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Branches"></a>
## وحدة: Branches


### GET `api/branches/dashboard`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `BranchController::dashboard` |
| Controller | `App\Http\Controllers\Api\BranchController` |
| Method | `dashboard` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/branches/dashboard` |
| Production URL | `https://smeda.gov.sy/api/api/branches/dashboard` |
| Policy / authorize() | `viewAny @ Branch::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `branch_id` | filled filter | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/branches`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `BranchController::index` |
| Controller | `App\Http\Controllers\Api\BranchController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/branches` |
| Production URL | `https://smeda.gov.sy/api/api/branches` |
| Policy / authorize() | `viewAny @ Branch::class` |
| API Resource | `BranchResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `governorate_id` | filled filter | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/branches`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `BranchController::store` |
| Controller | `App\Http\Controllers\Api\BranchController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/branches` |
| Production URL | `https://smeda.gov.sy/api/api/branches` |
| Policy / authorize() | `create @ Branch::class` |
| API Resource | `BranchResource` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:255` |
| `code` | `required`, `string`, `max:50`, `branches`, `code` |
| `governorate_id` | `required`, `integer`, `exists:governorates,id` |
| `manager_user_id` | `nullable`, `integer`, `exists:users,id` |
| `is_active` | `sometimes`, `boolean` |
| `notes` | `nullable`, `string` |

```json
{
    "name": "...",
    "code": "...",
    "governorate_id": "...",
    "manager_user_id": "...",
    "is_active": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/branches/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `BranchController::show` |
| Controller | `App\Http\Controllers\Api\BranchController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/branches/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/branches/{id}` |
| Policy / authorize() | `view @ $branch` |
| API Resource | `BranchResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/branches/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `BranchController::update` |
| Controller | `App\Http\Controllers\Api\BranchController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/branches/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/branches/{id}` |
| Policy / authorize() | `update @ $branch` |
| API Resource | `BranchResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `sometimes`, `string`, `max:255` |
| `code` | `sometimes`, `string`, `max:50`, `branches`, `code` |
| `governorate_id` | `sometimes`, `integer`, `exists:governorates,id` |
| `manager_user_id` | `nullable`, `integer`, `exists:users,id` |
| `is_active` | `sometimes`, `boolean` |
| `notes` | `nullable`, `string` |

```json
{
    "name": "...",
    "code": "...",
    "governorate_id": "...",
    "manager_user_id": "...",
    "is_active": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/branches/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `BranchController::destroy` |
| Controller | `App\Http\Controllers\Api\BranchController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/branches/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/branches/{id}` |
| Policy / authorize() | `delete @ $branch` |
| API Resource | `BranchResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Certificate-Verification"></a>
## وحدة: Certificate Verification


### GET `api/verify-certificate/{certificate_code}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::verifyByCode` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `verifyByCode` |
| Route Name | `—` |
| Middleware | `api, throttle:certificate-verify` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/verify-certificate/{certificate_code}` |
| Production URL | `https://smeda.gov.sy/api/api/verify-certificate/{certificate_code}` |
| Rate Limit | 30 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 403, 404, 429, 500


### GET `certificates/{id}/print`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::show` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `show` |
| Route Name | `certificates.print` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{id}/print` |
| Production URL | `https://smeda.gov.sy/api/certificates/{id}/print` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/{id}/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::pdf` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `pdf` |
| Route Name | `certificates.pdf` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{id}/pdf` |
| Production URL | `https://smeda.gov.sy/api/certificates/{id}/pdf` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `training-centers/{id}/certificate`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterPrintController::show` |
| Controller | `App\Http\Controllers\TrainingCenterPrintController` |
| Method | `show` |
| Route Name | `training-centers.certificate` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/training-centers/{id}/certificate` |
| Production URL | `https://smeda.gov.sy/api/training-centers/{id}/certificate` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `training-centers/{id}/certificate/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterPrintController::pdf` |
| Controller | `App\Http\Controllers\TrainingCenterPrintController` |
| Method | `pdf` |
| Route Name | `training-centers.certificate.pdf` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/training-centers/{id}/certificate/pdf` |
| Production URL | `https://smeda.gov.sy/api/training-centers/{id}/certificate/pdf` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `verify-certificate/{certificate_code}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::publicView` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `publicView` |
| Route Name | `certificates.verify-code` |
| Middleware | `web, throttle:print-routes` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/verify-certificate/{certificate_code}` |
| Production URL | `https://smeda.gov.sy/api/verify-certificate/{certificate_code}` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/verify`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::verifyPage` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `verifyPage` |
| Route Name | `certificates.verify` |
| Middleware | `web, throttle:print-routes` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/verify` |
| Production URL | `https://smeda.gov.sy/api/certificates/verify` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/{certificate_code}/print`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::showByCode` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `showByCode` |
| Route Name | `certificates.print-by-code` |
| Middleware | `web, throttle:certificate-print-by-code` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{certificate_code}/print` |
| Production URL | `https://smeda.gov.sy/api/certificates/{certificate_code}/print` |
| Rate Limit | 20 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/{certificate_code}/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::pdfByCode` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `pdfByCode` |
| Route Name | `certificates.pdf-by-code` |
| Middleware | `web, throttle:certificate-print-by-code` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{certificate_code}/pdf` |
| Production URL | `https://smeda.gov.sy/api/certificates/{certificate_code}/pdf` |
| Rate Limit | 20 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/{certificate_code}/qr`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::publicQrImage` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `publicQrImage` |
| Route Name | `certificates.qr-by-code` |
| Middleware | `web, throttle:certificate-print-by-code` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{certificate_code}/qr` |
| Production URL | `https://smeda.gov.sy/api/certificates/{certificate_code}/qr` |
| Rate Limit | 20 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


---

<a id="module-Certificates"></a>
## وحدة: Certificates


### POST `api/certificates/verify`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::verify` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `verify` |
| Route Name | `—` |
| Middleware | `api, throttle:certificate-verify` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/certificates/verify` |
| Production URL | `https://smeda.gov.sy/api/api/certificates/verify` |
| Form Request | `App\Http\Requests\Training\VerifyCertificateRequest` |
| Rate Limit | 30 طلبات/دقيقة لكل IP |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `value` | `required`, `string` |
| `type` | `required`, `in:certificate_number,certificate_code,reference_number,verification_code` |

```json
{
    "value": "...",
    "type": "..."
}
```


**Status Codes المحتملة:** 200, 201, 403, 404, 422, 429, 500


### POST `api/certificates/issue`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::issue` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `issue` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:issue_certificates` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/certificates/issue` |
| Production URL | `https://smeda.gov.sy/api/api/certificates/issue` |
| Permission | `issue_certificates` |
| Form Request | `App\Http\Requests\Training\IssueCertificateRequest` |
| API Resource | `CertificateResource` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `training_course_id` | `required`, `integer`, `exists:training_courses,id` |
| `trainee_id` | `required`, `integer`, `exists:trainees,id` |
| `certificate_type` | `required`, `in:attendance,completion,pass` |
| `result` | `nullable`, `in:pending,passed,failed,review` |
| `score` | `nullable`, `numeric`, `min:0`, `max:100` |
| `hours_awarded` | `nullable`, `integer`, `min:0` |
| `notes` | `nullable`, `string` |
| `issued_by` | `prohibited` |
| `approved_by` | `prohibited` |
| `reviewed_by` | `prohibited` |

```json
{
    "training_course_id": "...",
    "trainee_id": "...",
    "certificate_type": "...",
    "result": "...",
    "score": "...",
    "hours_awarded": "...",
    "notes": "...",
    "issued_by": "...",
    "approved_by": "...",
    "reviewed_by": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/certificates/{id}/approve`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::approve` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `approve` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:approve_center_certificates|approve_training_certificates|approve_deputy_certificates|approve_general_director_certificates` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/certificates/{id}/approve` |
| Production URL | `https://smeda.gov.sy/api/api/certificates/{id}/approve` |
| Permission | `approve_center_certificates|approve_training_certificates|approve_deputy_certificates|approve_general_director_certificates` |
| Policy / authorize() | `approve @ [$certificate, $approvalStep]` |
| Form Request | `App\Http\Requests\Training\ApproveCertificateRequest` |
| API Resource | `CertificateResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `approval_step` | `required`, `in:center_approval,training_manager_approval,deputy_director_approval,general_director_approval` |
| `decision` | `required`, `in:approved,rejected` |
| `notes` | `nullable`, `string` |
| `approved_by` | `prohibited` |
| `issued_by` | `prohibited` |
| `reviewed_by` | `prohibited` |

```json
{
    "approval_step": "...",
    "decision": "...",
    "notes": "...",
    "approved_by": "...",
    "issued_by": "...",
    "reviewed_by": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/certificates`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::index` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_certificates` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/certificates` |
| Production URL | `https://smeda.gov.sy/api/api/certificates` |
| Permission | `view_certificates` |
| Policy / authorize() | `viewAny @ Certificate::class` |
| API Resource | `CertificateResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `training_center_id` | mixed | controller |
| `trainer_id` | mixed | controller |
| `training_kit_id` | mixed | controller |
| `training_program_id` | mixed | controller |
| `training_course_id` | mixed | controller |
| `trainee_id` | mixed | controller |
| `status` | mixed | controller |
| `certificate_type` | mixed | controller |
| `result` | mixed | controller |
| `with_approvals` | boolean | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/certificates/code/{certificate_code}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::showByCode` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `showByCode` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_certificates` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/certificates/code/{certificate_code}` |
| Production URL | `https://smeda.gov.sy/api/api/certificates/code/{certificate_code}` |
| Permission | `view_certificates` |
| Policy / authorize() | `view @ $certificate` |
| API Resource | `CertificateResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/certificates/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::show` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_certificates` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/certificates/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/certificates/{id}` |
| Permission | `view_certificates` |
| Policy / authorize() | `view @ $certificate` |
| API Resource | `CertificateResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/certificates/verify-page`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::verifyPage` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `verifyPage` |
| Route Name | `api.certificates.verify-page` |
| Middleware | `api, throttle:verify-page` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/certificates/verify-page` |
| Production URL | `https://smeda.gov.sy/api/api/certificates/verify-page` |
| Rate Limit | 30 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


---

<a id="module-Dashboard"></a>
## وحدة: Dashboard


### GET `api/dashboard`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `DashboardController::index` |
| Controller | `App\Http\Controllers\Api\DashboardController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, dashboard.access` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/dashboard` |
| Production URL | `https://smeda.gov.sy/api/api/dashboard` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Entrepreneur-Profiles"></a>
## وحدة: Entrepreneur Profiles


### GET `api/entrepreneur/profiles/public-stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::publicStats` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `publicStats` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/profiles/public-stats` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/profiles/public-stats` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/entrepreneur/my-profile`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::myProfile` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `myProfile` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/my-profile` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/my-profile` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/entrepreneur/profile`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::store` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/profile` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/profile` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/entrepreneur/profile/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::update` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/profile/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/profile/{id}` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/entrepreneur/profiles`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::index` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/profiles` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/profiles` |
| Permission | `general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=15 max=100 |
| `status` | mixed | request->only |
| `project_field` | mixed | request->only |
| `governorate` | mixed | request->only |
| `search` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/entrepreneur/profiles/export`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::export` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `export` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/profiles/export` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/profiles/export` |
| Permission | `general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `status` | mixed | request->only |
| `project_field` | mixed | request->only |
| `governorate` | mixed | request->only |
| `search` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/entrepreneur/profiles/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::stats` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `stats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/profiles/stats` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/profiles/stats` |
| Permission | `general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/entrepreneur/profiles/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::show` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/profiles/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/profiles/{id}` |
| Permission | `general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/entrepreneur/profiles/{id}/review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `EntrepreneurProfileController::review` |
| Controller | `App\Http\Controllers\Api\EntrepreneurProfileController` |
| Method | `review` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/entrepreneur/profiles/{id}/review` |
| Production URL | `https://smeda.gov.sy/api/api/entrepreneur/profiles/{id}/review` |
| Permission | `general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `required|in:approved,rejected,under_review` |
| `reviewer_notes` | `nullable|string` |

```json
{
    "status": "...",
    "reviewer_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Governorates"></a>
## وحدة: Governorates


### GET `api/governorates`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `GovernorateController::index` |
| Controller | `App\Http\Controllers\Api\GovernorateController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/governorates` |
| Production URL | `https://smeda.gov.sy/api/api/governorates` |
| API Resource | `GovernorateResource::collection` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Health-Check"></a>
## وحدة: Health Check


### GET `up`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `Closure::-` |
| Controller | `غير محدد` |
| Method | `غير محدد` |
| Route Name | `—` |
| Middleware | `` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/up` |
| Production URL | `https://smeda.gov.sy/api/up` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 500


---

<a id="module-Inbox"></a>
## وحدة: Inbox


### GET `api/inbox/unread-count`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `InboxController::unreadCount` |
| Controller | `App\Http\Controllers\Api\InboxController` |
| Method | `unreadCount` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/inbox/unread-count` |
| Production URL | `https://smeda.gov.sy/api/api/inbox/unread-count` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/inbox/users-list`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `InboxController::usersList` |
| Controller | `App\Http\Controllers\Api\InboxController` |
| Method | `usersList` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/inbox/users-list` |
| Production URL | `https://smeda.gov.sy/api/api/inbox/users-list` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `q` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/inbox/sent`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `InboxController::sent` |
| Controller | `App\Http\Controllers\Api\InboxController` |
| Method | `sent` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/inbox/sent` |
| Production URL | `https://smeda.gov.sy/api/api/inbox/sent` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/inbox`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `InboxController::inbox` |
| Controller | `App\Http\Controllers\Api\InboxController` |
| Method | `inbox` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/inbox` |
| Production URL | `https://smeda.gov.sy/api/api/inbox` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `priority` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/inbox`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `InboxController::store` |
| Controller | `App\Http\Controllers\Api\InboxController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/inbox` |
| Production URL | `https://smeda.gov.sy/api/api/inbox` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `subject` | `required`, `string`, `max:255` |
| `body` | `required`, `string` |
| `recipient_id` | `nullable`, `integer`, `exists:users,id` |
| `is_broadcast` | `sometimes`, `boolean` |
| `broadcast_role` | `nullable`, `string`, `max:60` |
| `requires_reply` | `sometimes`, `boolean` |
| `priority` | `sometimes`, `in:normal,high,urgent` |
| `parent_id` | `nullable`, `integer`, `exists:inbox_messages,id` |

```json
{
    "subject": "...",
    "body": "...",
    "recipient_id": "...",
    "is_broadcast": "...",
    "broadcast_role": "...",
    "requires_reply": "...",
    "priority": "...",
    "parent_id": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/inbox/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `InboxController::show` |
| Controller | `App\Http\Controllers\Api\InboxController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/inbox/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/inbox/{id}` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/inbox/{id}/reply`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `InboxController::reply` |
| Controller | `App\Http\Controllers\Api\InboxController` |
| Method | `reply` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/inbox/{id}/reply` |
| Production URL | `https://smeda.gov.sy/api/api/inbox/{id}/reply` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `body` | `required`, `string`, `max:10000` |
| `subject` | `sometimes`, `string`, `max:255` |

```json
{
    "body": "...",
    "subject": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/inbox/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `InboxController::destroy` |
| Controller | `App\Http\Controllers\Api\InboxController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/inbox/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/inbox/{id}` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Incubators"></a>
## وحدة: Incubators


### GET `api/incubators`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::index` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubators` |
| Production URL | `https://smeda.gov.sy/api/api/incubators` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=50 max=100 |
| `page` | filled filter | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 404, 429, 500


### POST `api/incubators`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::store` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubators` |
| Production URL | `https://smeda.gov.sy/api/api/incubators` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required|string|max:255` |
| `code` | `required|string|unique:incubators,code` |
| `description` | `nullable|string` |
| `sector` | `nullable|string` |
| `location` | `nullable|string` |
| `governorate_id` | `nullable|exists:governorates,id` |
| `branch_id` | `nullable|exists:branches,id` |
| `manager_user_id` | `nullable|exists:users,id` |
| `phone` | `nullable|string` |
| `email` | `nullable|email` |
| `capacity` | `nullable|integer|min:1` |

```json
{
    "name": "...",
    "code": "...",
    "description": "...",
    "sector": "...",
    "location": "...",
    "governorate_id": "...",
    "branch_id": "...",
    "manager_user_id": "...",
    "phone": "...",
    "email": "...",
    "capacity": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubators/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::show` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubators/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/incubators/{id}` |
| Permission | `general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/incubators/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::update` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubators/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/incubators/{id}` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `sometimes|string|max:255` |
| `description` | `nullable|string` |
| `sector` | `nullable|string` |
| `location` | `nullable|string` |
| `governorate_id` | `nullable|exists:governorates,id` |
| `branch_id` | `nullable|exists:branches,id` |
| `manager_user_id` | `nullable|exists:users,id` |
| `phone` | `nullable|string` |
| `email` | `nullable|email` |
| `capacity` | `nullable|integer|min:1` |
| `status` | `nullable|in:active,inactive,suspended` |

```json
{
    "name": "...",
    "description": "...",
    "sector": "...",
    "location": "...",
    "governorate_id": "...",
    "branch_id": "...",
    "manager_user_id": "...",
    "phone": "...",
    "email": "...",
    "capacity": "...",
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/incubators/{id}/programs`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::storeProgram` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `storeProgram` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubators/{id}/programs` |
| Production URL | `https://smeda.gov.sy/api/api/incubators/{id}/programs` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required|string` |
| `description` | `nullable|string` |
| `duration_months` | `nullable|integer|min:1` |
| `seats` | `nullable|integer|min:1` |
| `start_date` | `nullable|date` |
| `end_date` | `nullable|date|after_or_equal:start_date` |
| `requirements` | `nullable|string` |

```json
{
    "name": "...",
    "description": "...",
    "duration_months": "...",
    "seats": "...",
    "start_date": "...",
    "end_date": "...",
    "requirements": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubators/{id}/applications`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::applications` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `applications` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubators/{id}/applications` |
| Production URL | `https://smeda.gov.sy/api/api/incubators/{id}/applications` |
| Permission | `general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Maps"></a>
## وحدة: Maps


### GET `api/map/training-centers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingMapController::centers` |
| Controller | `App\Http\Controllers\Api\TrainingMapController` |
| Method | `centers` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/map/training-centers` |
| Production URL | `https://smeda.gov.sy/api/api/map/training-centers` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/map/training-courses`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingMapController::courses` |
| Controller | `App\Http\Controllers\Api\TrainingMapController` |
| Method | `courses` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_courses, throttle:map-public` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/map/training-courses` |
| Production URL | `https://smeda.gov.sy/api/api/map/training-courses` |
| Permission | `view_courses` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/map/trainers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingMapController::trainers` |
| Controller | `App\Http\Controllers\Api\TrainingMapController` |
| Method | `trainers` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_trainers, throttle:map-public` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/map/trainers` |
| Production URL | `https://smeda.gov.sy/api/api/map/trainers` |
| Permission | `view_trainers` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Needs-GIS"></a>
## وحدة: Needs GIS


### GET `api/needs`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::index` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:needs.view|needs.view_all|needs.view_branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs` |
| Production URL | `https://smeda.gov.sy/api/api/needs` |
| Permission | `needs.view|needs.view_all|needs.view_branch` |
| Policy / authorize() | `viewAny @ Need::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `q` | string | search |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::show` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:needs.view|needs.view_all|needs.view_branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/needs/{id}` |
| Permission | `needs.view|needs.view_all|needs.view_branch` |
| Policy / authorize() | `view @ $need` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/map`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::map` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `map` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:needs.view|needs.view_all|needs.view_branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/map` |
| Production URL | `https://smeda.gov.sy/api/api/needs/map` |
| Permission | `needs.view|needs.view_all|needs.view_branch` |
| Policy / authorize() | `map @ Need::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `limit` | filled filter | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/lookups`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::lookups` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `lookups` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:needs.view|needs.view_all|needs.view_branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/lookups` |
| Production URL | `https://smeda.gov.sy/api/api/needs/lookups` |
| Permission | `needs.view|needs.view_all|needs.view_branch` |
| Policy / authorize() | `viewAny @ Need::class` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/admin-units`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::adminUnits` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `adminUnits` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:needs.view|needs.view_all|needs.view_branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/admin-units` |
| Production URL | `https://smeda.gov.sy/api/api/needs/admin-units` |
| Permission | `needs.view|needs.view_all|needs.view_branch` |
| Policy / authorize() | `viewAny @ Need::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `governorate_id` | filled filter | controller |
| `branch_id` | filled filter | controller |
| `per_page` | integer | pagination default=25 max=100 |
| `district_name` | string | controller |
| `is_active` | boolean | controller |
| `q` | string | search |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/export`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::export` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `export` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:needs.view|needs.view_all|needs.view_branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/export` |
| Production URL | `https://smeda.gov.sy/api/api/needs/export` |
| Permission | `needs.view|needs.view_all|needs.view_branch` |
| Policy / authorize() | `export @ Need::class` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/needs`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::store` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:needs.create|needs.create_citizen|needs.create_state` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs` |
| Production URL | `https://smeda.gov.sy/api/api/needs` |
| Permission | `needs.create|needs.create_citizen|needs.create_state` |
| Policy / authorize() | `create @ Need::class` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/dashboard`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::dashboard` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `dashboard` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:needs.dashboard` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/dashboard` |
| Production URL | `https://smeda.gov.sy/api/api/needs/dashboard` |
| Permission | `needs.dashboard` |
| Policy / authorize() | `dashboard @ Need::class` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/analytics`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::analytics` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `analytics` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/analytics` |
| Production URL | `https://smeda.gov.sy/api/api/needs/analytics` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/workspace/data-entry`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::dataEntryWorkspace` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `dataEntryWorkspace` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role:data_entry` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/workspace/data-entry` |
| Production URL | `https://smeda.gov.sy/api/api/needs/workspace/data-entry` |
| Role | `data_entry` |
| Policy / authorize() | `create @ Need::class` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/needs/workspace/reviewer`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::reviewerWorkspace` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `reviewerWorkspace` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role:data_reviewer` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/workspace/reviewer` |
| Production URL | `https://smeda.gov.sy/api/api/needs/workspace/reviewer` |
| Role | `data_reviewer` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/needs/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::update` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/needs/{id}` |
| Policy / authorize() | `update @ $need` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/needs/{id}/review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::review` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `review` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/{id}/review` |
| Production URL | `https://smeda.gov.sy/api/api/needs/{id}/review` |
| Policy / authorize() | `review @ $need` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `note` | `nullable`, `string` |

```json
{
    "note": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/needs/{id}/approve`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::approve` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `approve` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/{id}/approve` |
| Production URL | `https://smeda.gov.sy/api/api/needs/{id}/approve` |
| Policy / authorize() | `approve @ $need` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `note` | `nullable`, `string` |

```json
{
    "note": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/needs/{id}/reject`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::reject` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `reject` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/{id}/reject` |
| Production URL | `https://smeda.gov.sy/api/api/needs/{id}/reject` |
| Policy / authorize() | `reject @ $need` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `rejection_reason` | `required`, `string` |

```json
{
    "rejection_reason": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/needs/{id}/return`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::returnForEdit` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `returnForEdit` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/{id}/return` |
| Production URL | `https://smeda.gov.sy/api/api/needs/{id}/return` |
| Policy / authorize() | `returnForEdit @ $need` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `return_reason` | `required`, `string` |

```json
{
    "return_reason": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/needs/{id}/classify`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::classify` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `classify` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/{id}/classify` |
| Production URL | `https://smeda.gov.sy/api/api/needs/{id}/classify` |
| Policy / authorize() | `classify @ $need` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `proposed_intervention` | `required`, `string`, `max:100` |
| `note` | `nullable`, `string` |

```json
{
    "proposed_intervention": "...",
    "note": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/needs/{id}/resolve`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NeedController::resolve` |
| Controller | `App\Http\Controllers\Api\NeedController` |
| Method | `resolve` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/needs/{id}/resolve` |
| Production URL | `https://smeda.gov.sy/api/api/needs/{id}/resolve` |
| Policy / authorize() | `resolve @ $need` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `note` | `nullable`, `string` |

```json
{
    "note": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-News"></a>
## وحدة: News


### GET `api/news`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NewsController::index` |
| Controller | `App\Http\Controllers\Api\NewsController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/news` |
| Production URL | `https://smeda.gov.sy/api/api/news` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `status` | filled filter | controller |
| `per_page` | integer | pagination |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/news/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NewsController::show` |
| Controller | `App\Http\Controllers\Api\NewsController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/news/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/news/{id}` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/news/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NewsController::stats` |
| Controller | `App\Http\Controllers\Api\NewsController` |
| Method | `stats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:media_manager|general_director|admin|super_admin|system_admin|news.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/news/stats` |
| Production URL | `https://smeda.gov.sy/api/api/news/stats` |
| Permission | `media_manager|general_director|admin|super_admin|system_admin|news.manage` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/news`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NewsController::store` |
| Controller | `App\Http\Controllers\Api\NewsController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:media_manager|general_director|admin|super_admin|system_admin|news.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/news` |
| Production URL | `https://smeda.gov.sy/api/api/news` |
| Permission | `media_manager|general_director|admin|super_admin|system_admin|news.manage` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `title` | `required|string|max:255` |
| `summary` | `required|string` |
| `body` | `required|string` |
| `image_url` | `nullable|url` |
| `category` | `nullable|string` |
| `branch_id` | `nullable|exists:branches,id` |
| `status` | `nullable|in:draft,published,archived` |
| `is_pinned` | `nullable|boolean` |

```json
{
    "title": "...",
    "summary": "...",
    "body": "...",
    "image_url": "...",
    "category": "...",
    "branch_id": "...",
    "status": "...",
    "is_pinned": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/news/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NewsController::update` |
| Controller | `App\Http\Controllers\Api\NewsController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:media_manager|general_director|admin|super_admin|system_admin|news.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/news/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/news/{id}` |
| Permission | `media_manager|general_director|admin|super_admin|system_admin|news.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `title` | `sometimes|string|max:255` |
| `summary` | `sometimes|string` |
| `body` | `sometimes|string` |
| `image_url` | `nullable|url` |
| `category` | `nullable|string` |
| `branch_id` | `nullable|exists:branches,id` |
| `status` | `nullable|in:draft,published,archived` |
| `is_pinned` | `nullable|boolean` |

```json
{
    "title": "...",
    "summary": "...",
    "body": "...",
    "image_url": "...",
    "category": "...",
    "branch_id": "...",
    "status": "...",
    "is_pinned": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/news/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NewsController::destroy` |
| Controller | `App\Http\Controllers\Api\NewsController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:media_manager|general_director|admin|super_admin|system_admin|news.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/news/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/news/{id}` |
| Permission | `media_manager|general_director|admin|super_admin|system_admin|news.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Notifications"></a>
## وحدة: Notifications


### GET `api/notifications/summary`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NotificationController::summary` |
| Controller | `App\Http\Controllers\Api\NotificationController` |
| Method | `summary` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/notifications/summary` |
| Production URL | `https://smeda.gov.sy/api/api/notifications/summary` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/notifications`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NotificationController::index` |
| Controller | `App\Http\Controllers\Api\NotificationController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/notifications` |
| Production URL | `https://smeda.gov.sy/api/api/notifications` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=30 max=100 |
| `unread_only` | filled filter | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/notifications/read-all`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NotificationController::markAllRead` |
| Controller | `App\Http\Controllers\Api\NotificationController` |
| Method | `markAllRead` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/notifications/read-all` |
| Production URL | `https://smeda.gov.sy/api/api/notifications/read-all` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/notifications/{id}/read`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NotificationController::markRead` |
| Controller | `App\Http\Controllers\Api\NotificationController` |
| Method | `markRead` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/notifications/{id}/read` |
| Production URL | `https://smeda.gov.sy/api/api/notifications/{id}/read` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/notifications/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `NotificationController::destroy` |
| Controller | `App\Http\Controllers\Api\NotificationController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/notifications/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/notifications/{id}` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Other-Routes"></a>
## وحدة: Other Routes


### GET `api/electronic-signatures/{id}/snapshot-image`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserElectronicSignatureController::snapshotImage` |
| Controller | `App\Http\Controllers\Api\UserElectronicSignatureController` |
| Method | `snapshotImage` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/electronic-signatures/{id}/snapshot-image` |
| Production URL | `https://smeda.gov.sy/api/api/electronic-signatures/{id}/snapshot-image` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/records`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FinancialRecordController::index` |
| Controller | `App\Http\Controllers\Api\FinancialRecordController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/records` |
| Production URL | `https://smeda.gov.sy/api/api/finance/records` |
| Permission | `general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance` |
| Policy / authorize() | `viewAny @ FinancialRecord::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |
| `record_type` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/records/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FinancialRecordController::show` |
| Controller | `App\Http\Controllers\Api\FinancialRecordController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/records/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/records/{id}` |
| Permission | `general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance` |
| Policy / authorize() | `view @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/records`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FinancialRecordController::store` |
| Controller | `App\Http\Controllers\Api\FinancialRecordController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|finance_manager|admin|super_admin|system_admin|manage_finance` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/records` |
| Production URL | `https://smeda.gov.sy/api/api/finance/records` |
| Permission | `general_director|finance_manager|admin|super_admin|system_admin|manage_finance` |
| Policy / authorize() | `create @ FinancialRecord::class` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `record_type` | `required`, `in:funding,payment,commitment,revenue` |
| `title` | `required`, `string`, `max:255` |
| `amount` | `required`, `numeric`, `min:0` |
| `currency` | `nullable`, `string`, `max:8` |
| `status` | `nullable`, `string`, `max:50` |
| `branch_id` | `nullable`, `integer`, `exists:branches,id` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `notes` | `nullable`, `string` |

```json
{
    "record_type": "...",
    "title": "...",
    "amount": "...",
    "currency": "...",
    "status": "...",
    "branch_id": "...",
    "governorate_id": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/finance/records/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FinancialRecordController::update` |
| Controller | `App\Http\Controllers\Api\FinancialRecordController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|finance_manager|admin|super_admin|system_admin|manage_finance` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/records/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/records/{id}` |
| Permission | `general_director|finance_manager|admin|super_admin|system_admin|manage_finance` |
| Policy / authorize() | `update @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `record_type` | `sometimes`, `in:funding,payment,commitment,revenue` |
| `title` | `sometimes`, `string`, `max:255` |
| `amount` | `sometimes`, `numeric`, `min:0` |
| `currency` | `sometimes`, `string`, `max:8` |
| `status` | `sometimes`, `string`, `max:50` |
| `branch_id` | `sometimes`, `nullable`, `integer`, `exists:branches,id` |
| `governorate_id` | `sometimes`, `nullable`, `integer`, `exists:governorates,id` |
| `notes` | `sometimes`, `nullable`, `string` |

```json
{
    "record_type": "...",
    "title": "...",
    "amount": "...",
    "currency": "...",
    "status": "...",
    "branch_id": "...",
    "governorate_id": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/records/{id}/approve`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FinancialRecordController::approve` |
| Controller | `App\Http\Controllers\Api\FinancialRecordController` |
| Method | `approve` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|finance_manager|admin|super_admin|system_admin|manage_finance` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/records/{id}/approve` |
| Production URL | `https://smeda.gov.sy/api/api/finance/records/{id}/approve` |
| Permission | `general_director|finance_manager|admin|super_admin|system_admin|manage_finance` |
| Policy / authorize() | `approve @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/applications`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::index` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications` |
| Permission | `project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view` |
| Policy / authorize() | `viewAny @ FundingApplication::class` |
| API Resource | `FundingApplicationResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `branch_id` | filled filter | controller |
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/applications/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::show` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}` |
| Permission | `project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view` |
| Policy / authorize() | `view @ $application` |
| API Resource | `FundingApplicationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/applications/{applicationId}/documents/{documentId}/download`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingDocumentController::download` |
| Controller | `App\Http\Controllers\Api\FundingDocumentController` |
| Method | `download` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{applicationId}/documents/{documentId}/download` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{applicationId}/documents/{documentId}/download` |
| Permission | `project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view` |
| Policy / authorize() | `view @ $application` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `applicationId` | نعم | معامل مسار |
| `documentId` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::store` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:project_owner|finance.applications.create|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications` |
| Permission | `project_owner|finance.applications.create|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `create @ FundingApplication::class` |
| API Resource | `FundingApplicationResource` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `applicant_name` | `required`, `string`, `max:255` |
| `national_id` | `nullable`, `string`, `max:50` |
| `phone` | `nullable`, `string`, `max:50` |
| `email` | `nullable`, `email`, `max:255` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `branch_id` | `nullable`, `integer`, `exists:branches,id` |
| `project_name` | `required`, `string`, `max:255` |
| `project_type` | `nullable`, `string`, `max:100` |
| `project_sector` | `nullable`, `string`, `max:100` |
| `project_size` | `nullable`, `in:micro,small,medium` |
| `business_stage` | `nullable`, `in:idea,startup,existing,expansion` |
| `requested_amount` | `required`, `numeric`, `min:0` |
| `currency` | `nullable`, `string`, `max:8` |
| `financing_type` | `nullable`, `in:capital,working_capital,mixed` |
| `repayment_period_months` | `nullable`, `integer`, `min:1` |
| `purpose` | `nullable`, `string` |
| `description` | `nullable`, `string` |
| `details` | `nullable`, `array` |

```json
{
    "applicant_name": "...",
    "national_id": "...",
    "phone": "...",
    "email": "...",
    "governorate_id": "...",
    "branch_id": "...",
    "project_name": "...",
    "project_type": "...",
    "project_sector": "...",
    "project_size": "...",
    "business_stage": "...",
    "requested_amount": "...",
    "currency": "...",
    "financing_type": "...",
    "repayment_period_months": "...",
    "purpose": "...",
    "description": "...",
    "details": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/finance/applications/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::update` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:project_owner|finance.applications.update|branch_manager|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}` |
| Permission | `project_owner|finance.applications.update|branch_manager|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `update @ $application` |
| API Resource | `FundingApplicationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `applicant_name` | `sometimes`, `string`, `max:255` |
| `national_id` | `nullable`, `string`, `max:50` |
| `phone` | `nullable`, `string`, `max:50` |
| `email` | `nullable`, `email`, `max:255` |
| `project_name` | `sometimes`, `string`, `max:255` |
| `project_type` | `nullable`, `string`, `max:100` |
| `project_sector` | `nullable`, `string`, `max:100` |
| `project_size` | `nullable`, `in:micro,small,medium` |
| `business_stage` | `nullable`, `in:idea,startup,existing,expansion` |
| `requested_amount` | `sometimes`, `numeric`, `min:0` |
| `currency` | `nullable`, `string`, `max:8` |
| `financing_type` | `nullable`, `in:capital,working_capital,mixed` |
| `repayment_period_months` | `nullable`, `integer`, `min:1` |
| `purpose` | `nullable`, `string` |
| `description` | `nullable`, `string` |
| `details` | `nullable`, `array` |

```json
{
    "applicant_name": "...",
    "national_id": "...",
    "phone": "...",
    "email": "...",
    "project_name": "...",
    "project_type": "...",
    "project_sector": "...",
    "project_size": "...",
    "business_stage": "...",
    "requested_amount": "...",
    "currency": "...",
    "financing_type": "...",
    "repayment_period_months": "...",
    "purpose": "...",
    "description": "...",
    "details": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{id}/submit`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::submit` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `submit` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:project_owner|finance.applications.submit|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}/submit` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}/submit` |
| Permission | `project_owner|finance.applications.submit|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `submit @ $application` |
| API Resource | `FundingApplicationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{id}/request-completion`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::requestCompletion` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `requestCompletion` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:branch_manager|finance.applications.request_completion|finance_manager|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}/request-completion` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}/request-completion` |
| Permission | `branch_manager|finance.applications.request_completion|finance_manager|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `reviewBranch @ $application` |
| API Resource | `FundingApplicationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `notes` | `nullable`, `string` |

```json
{
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{id}/branch-review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::branchReview` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `branchReview` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:branch_manager|finance.applications.review_branch|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}/branch-review` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}/branch-review` |
| Permission | `branch_manager|finance.applications.review_branch|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `reviewBranch @ $application` |
| API Resource | `FundingApplicationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `decision` | `required`, `in:approve,needs_completion,reject,review` |
| `notes` | `nullable`, `string` |

```json
{
    "decision": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{id}/approve`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::approve` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `approve` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance.applications.approve|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}/approve` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}/approve` |
| Permission | `finance_manager|finance.applications.approve|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `approve @ $application` |
| API Resource | `FundingApplicationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{id}/reject`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::reject` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `reject` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:branch_manager|finance_manager|finance.applications.reject|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}/reject` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}/reject` |
| Permission | `branch_manager|finance_manager|finance.applications.reject|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `reject @ $application` |
| API Resource | `FundingApplicationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `notes` | `nullable`, `string` |

```json
{
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{id}/assign-consultant`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::assignConsultant` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `assignConsultant` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:branch_manager|finance_manager|finance.applications.assign_consultant|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}/assign-consultant` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}/assign-consultant` |
| Permission | `branch_manager|finance_manager|finance.applications.assign_consultant|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `assignConsultant @ $application` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `consultant_office_id` | `required`, `integer`, `exists:consultant_offices,id` |

```json
{
    "consultant_office_id": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{id}/assign-partner`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::assignPartner` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `assignPartner` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|central_bank_admin|finance.applications.assign_partner|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}/assign-partner` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}/assign-partner` |
| Permission | `finance_manager|central_bank_admin|finance.applications.assign_partner|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `assignPartner @ $application` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `funding_partner_id` | `required`, `integer`, `exists:funding_partners,id` |

```json
{
    "funding_partner_id": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{id}/create-loan`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingApplicationController::createLoan` |
| Controller | `App\Http\Controllers\Api\FundingApplicationController` |
| Method | `createLoan` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance.loans.manage|general_director|admin|super_admin|system_admin` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{id}/create-loan` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{id}/create-loan` |
| Permission | `finance_manager|finance.loans.manage|general_director|admin|super_admin|system_admin` |
| Policy / authorize() | `view @ $application`, `create @ FundedLoan::class` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `funding_partner_id` | `nullable`, `integer`, `exists:funding_partners,id` |
| `approved_amount` | `required`, `numeric`, `min:0` |
| `currency` | `nullable`, `string`, `max:8` |
| `interest_type` | `nullable`, `in:interest,free,profit_margin` |
| `interest_rate` | `nullable`, `numeric`, `min:0` |
| `profit_margin` | `nullable`, `numeric`, `min:0` |
| `installment_count` | `nullable`, `integer`, `min:1` |
| `installment_amount` | `nullable`, `numeric`, `min:0` |
| `start_date` | `nullable`, `date` |
| `end_date` | `nullable`, `date` |

```json
{
    "funding_partner_id": "...",
    "approved_amount": "...",
    "currency": "...",
    "interest_type": "...",
    "interest_rate": "...",
    "profit_margin": "...",
    "installment_count": "...",
    "installment_amount": "...",
    "start_date": "...",
    "end_date": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/applications/{applicationId}/documents`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingDocumentController::store` |
| Controller | `App\Http\Controllers\Api\FundingDocumentController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view, throttle:file-upload` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/applications/{applicationId}/documents` |
| Production URL | `https://smeda.gov.sy/api/api/finance/applications/{applicationId}/documents` |
| Permission | `project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view` |
| Policy / authorize() | `update @ $application` |
| Rate Limit | 5 طلبات/دقيقة لكل (user|IP) |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `applicationId` | نعم | معامل مسار |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `document_type` | `required`, `string`, `max:100` |
| `file` | `required`, `file`, `max:10240` |

```json
{
    "document_type": "...",
    "file": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/consultant-union/dashboard`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::unionDashboard` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `unionDashboard` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|finance.consultant_union.dashboard` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-union/dashboard` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-union/dashboard` |
| Permission | `consultant_union_admin|finance.consultant_union.dashboard` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/consultant-assignments`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::indexAssignments` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `indexAssignments` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-assignments` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-assignments` |
| Permission | `consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=30 max=100 |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/consultant-offices`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::indexOffices` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `indexOffices` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices` |
| Permission | `consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| Policy / authorize() | `viewAny @ ConsultantOffice::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=50 max=100 |
| `status` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/consultant-offices/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::showOffice` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `showOffice` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices/{id}` |
| Permission | `consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| Policy / authorize() | `view @ $office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/consultant-offices/{id}/assignments`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::officeAssignments` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `officeAssignments` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices/{id}/assignments` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices/{id}/assignments` |
| Permission | `consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| Policy / authorize() | `view @ $office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/consultant-offices/{id}/reports`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::officeReports` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `officeReports` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices/{id}/reports` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices/{id}/reports` |
| Permission | `consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| Policy / authorize() | `view @ $office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/consultant-offices/{id}/metrics`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::officeMetrics` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `officeMetrics` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices/{id}/metrics` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices/{id}/metrics` |
| Permission | `consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all` |
| Policy / authorize() | `monitor @ ConsultantOffice::class`, `view @ $office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-offices`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::storeOffice` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `storeOffice` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.create|finance.consultants.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices` |
| Permission | `consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.create|finance.consultants.manage` |
| Policy / authorize() | `create @ ConsultantOffice::class` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:255` |
| `license_number` | `nullable`, `string`, `max:100` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `branch_id` | `nullable`, `integer`, `exists:branches,id` |
| `specialization` | `nullable`, `string`, `max:255` |
| `sectors` | `nullable`, `array` |
| `contact_person` | `nullable`, `string`, `max:255` |
| `phone` | `nullable`, `string`, `max:50` |
| `email` | `nullable`, `email`, `max:255` |
| `address` | `nullable`, `string` |
| `status` | `nullable`, `in:pending,approved,active,inactive,suspended,rejected` |

```json
{
    "name": "...",
    "license_number": "...",
    "governorate_id": "...",
    "branch_id": "...",
    "specialization": "...",
    "sectors": "...",
    "contact_person": "...",
    "phone": "...",
    "email": "...",
    "address": "...",
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/finance/consultant-offices/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::updateOffice` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `updateOffice` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.update|finance.consultants.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices/{id}` |
| Permission | `consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.update|finance.consultants.manage` |
| Policy / authorize() | `update @ $office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `sometimes`, `string`, `max:255` |
| `license_number` | `nullable`, `string`, `max:100` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `branch_id` | `nullable`, `integer`, `exists:branches,id` |
| `specialization` | `nullable`, `string`, `max:255` |
| `sectors` | `nullable`, `array` |
| `contact_person` | `nullable`, `string`, `max:255` |
| `phone` | `nullable`, `string`, `max:50` |
| `email` | `nullable`, `email`, `max:255` |
| `address` | `nullable`, `string` |
| `status` | `nullable`, `in:pending,approved,active,inactive,suspended,rejected` |

```json
{
    "name": "...",
    "license_number": "...",
    "governorate_id": "...",
    "branch_id": "...",
    "specialization": "...",
    "sectors": "...",
    "contact_person": "...",
    "phone": "...",
    "email": "...",
    "address": "...",
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-offices/{id}/approve`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::approveOffice` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `approveOffice` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.approve|finance.consultants.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices/{id}/approve` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices/{id}/approve` |
| Permission | `consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.approve|finance.consultants.manage` |
| Policy / authorize() | `approve @ $office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-offices/{id}/activate`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::activateOffice` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `activateOffice` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.activate|finance.consultants.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices/{id}/activate` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices/{id}/activate` |
| Permission | `consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.activate|finance.consultants.manage` |
| Policy / authorize() | `activate @ $office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-offices/{id}/suspend`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::suspendOffice` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `suspendOffice` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.suspend|finance.consultants.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-offices/{id}/suspend` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-offices/{id}/suspend` |
| Permission | `consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.suspend|finance.consultants.manage` |
| Policy / authorize() | `suspend @ $office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/consultant-office/dashboard`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::officeDashboard` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `officeDashboard` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role:consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-office/dashboard` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-office/dashboard` |
| Role | `consultant_office` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/my-consultant-assignments`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::myAssignments` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `myAssignments` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role:consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/my-consultant-assignments` |
| Production URL | `https://smeda.gov.sy/api/api/finance/my-consultant-assignments` |
| Role | `consultant_office` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-assignments/{id}/accept`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::acceptAssignment` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `acceptAssignment` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_office|finance.consultant_assignments.accept` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-assignments/{id}/accept` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-assignments/{id}/accept` |
| Permission | `consultant_office|finance.consultant_assignments.accept` |
| Policy / authorize() | `accept @ $assignment` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-assignments/{id}/reject`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::rejectAssignment` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `rejectAssignment` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_office|finance.consultant_assignments.reject` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-assignments/{id}/reject` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-assignments/{id}/reject` |
| Permission | `consultant_office|finance.consultant_assignments.reject` |
| Policy / authorize() | `accept @ $assignment` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `notes` | `nullable`, `string` |

```json
{
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-assignments/{id}/price-offer`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::priceOffer` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `priceOffer` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_office|finance.consultant_assignments.submit_price|finance.consultants.submit_price` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-assignments/{id}/price-offer` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-assignments/{id}/price-offer` |
| Permission | `consultant_office|finance.consultant_assignments.submit_price|finance.consultants.submit_price` |
| Policy / authorize() | `submitPrice @ $assignment` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `price_offer_amount` | `required`, `numeric`, `min:0` |
| `price_offer_currency` | `nullable`, `string`, `max:8` |
| `consultant_notes` | `nullable`, `string` |

```json
{
    "price_offer_amount": "...",
    "price_offer_currency": "...",
    "consultant_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-assignments/{id}/approve-price`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::approvePrice` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `approvePrice` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:branch_manager|finance_manager|general_director|admin|super_admin|system_admin|finance.consultants.approve_price` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-assignments/{id}/approve-price` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-assignments/{id}/approve-price` |
| Permission | `branch_manager|finance_manager|general_director|admin|super_admin|system_admin|finance.consultants.approve_price` |
| Policy / authorize() | `approvePrice @ $assignment` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/consultant-reports`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingConsultantController::storeReport` |
| Controller | `App\Http\Controllers\Api\FundingConsultantController` |
| Method | `storeReport` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:consultant_office|general_director|admin|super_admin|system_admin|finance.consultant_reports.create|finance.consultants.submit_report` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/consultant-reports` |
| Production URL | `https://smeda.gov.sy/api/api/finance/consultant-reports` |
| Permission | `consultant_office|general_director|admin|super_admin|system_admin|finance.consultant_reports.create|finance.consultants.submit_report` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `funding_application_id` | `required`, `integer`, `exists:funding_applications,id` |
| `consultant_office_id` | `required`, `integer`, `exists:consultant_offices,id` |
| `feasibility_score` | `nullable`, `numeric`, `min:0`, `max:100` |
| `risk_level` | `nullable`, `in:low,medium,high` |
| `recommended_amount` | `nullable`, `numeric`, `min:0` |
| `recommendation` | `required`, `in:approve,reject,needs_adjustment` |
| `report_summary` | `nullable`, `string` |
| `strengths` | `nullable`, `string` |
| `weaknesses` | `nullable`, `string` |
| `conditions` | `nullable`, `string` |

```json
{
    "funding_application_id": "...",
    "consultant_office_id": "...",
    "feasibility_score": "...",
    "risk_level": "...",
    "recommended_amount": "...",
    "recommendation": "...",
    "report_summary": "...",
    "strengths": "...",
    "weaknesses": "...",
    "conditions": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/central-bank/dashboard`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::centralBankDashboard` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `centralBankDashboard` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|finance.central_bank.dashboard` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/central-bank/dashboard` |
| Production URL | `https://smeda.gov.sy/api/api/finance/central-bank/dashboard` |
| Permission | `central_bank_admin|finance.central_bank.dashboard` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/funding-partner/dashboard`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::partnerDashboard` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `partnerDashboard` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role:funding_partner` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/funding-partner/dashboard` |
| Production URL | `https://smeda.gov.sy/api/api/finance/funding-partner/dashboard` |
| Role | `funding_partner` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/partners`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::index` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners` |
| Permission | `central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| Policy / authorize() | `viewAny @ FundingPartner::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=50 max=100 |
| `status` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/partners/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::show` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}` |
| Permission | `central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| Policy / authorize() | `view @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/partners/{id}/assignments`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::partnerAssignments` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `partnerAssignments` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}/assignments` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}/assignments` |
| Permission | `central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| Policy / authorize() | `view @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/partners/{id}/decisions`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::partnerDecisions` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `partnerDecisions` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}/decisions` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}/decisions` |
| Permission | `central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| Policy / authorize() | `view @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/partners/{id}/loans`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::partnerLoans` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `partnerLoans` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}/loans` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}/loans` |
| Permission | `central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| Policy / authorize() | `view @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/partners/{id}/metrics`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::partnerMetrics` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `partnerMetrics` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}/metrics` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}/metrics` |
| Permission | `central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all` |
| Policy / authorize() | `monitor @ FundingPartner::class`, `view @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/partners`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::store` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.create|finance.partners.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners` |
| Permission | `central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.create|finance.partners.manage` |
| Policy / authorize() | `create @ FundingPartner::class` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:255` |
| `partner_type` | `nullable`, `in:bank,fund,guarantee_company,donor,other` |
| `license_number` | `nullable`, `string`, `max:100` |
| `contact_person` | `nullable`, `string`, `max:255` |
| `phone` | `nullable`, `string`, `max:50` |
| `email` | `nullable`, `email`, `max:255` |
| `status` | `nullable`, `in:pending,approved,active,inactive,suspended,rejected` |

```json
{
    "name": "...",
    "partner_type": "...",
    "license_number": "...",
    "contact_person": "...",
    "phone": "...",
    "email": "...",
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/finance/partners/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::update` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.update|finance.partners.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}` |
| Permission | `central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.update|finance.partners.manage` |
| Policy / authorize() | `update @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `sometimes`, `string`, `max:255` |
| `partner_type` | `nullable`, `in:bank,fund,guarantee_company,donor,other` |
| `license_number` | `nullable`, `string`, `max:100` |
| `contact_person` | `nullable`, `string`, `max:255` |
| `phone` | `nullable`, `string`, `max:50` |
| `email` | `nullable`, `email`, `max:255` |
| `status` | `nullable`, `in:pending,approved,active,inactive,suspended,rejected` |

```json
{
    "name": "...",
    "partner_type": "...",
    "license_number": "...",
    "contact_person": "...",
    "phone": "...",
    "email": "...",
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/partners/{id}/approve`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::approvePartner` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `approvePartner` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.approve|finance.partners.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}/approve` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}/approve` |
| Permission | `central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.approve|finance.partners.manage` |
| Policy / authorize() | `approve @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/partners/{id}/activate`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::activatePartner` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `activatePartner` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.activate|finance.partners.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}/activate` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}/activate` |
| Permission | `central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.activate|finance.partners.manage` |
| Policy / authorize() | `activate @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/partners/{id}/suspend`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::suspendPartner` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `suspendPartner` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.suspend|finance.partners.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partners/{id}/suspend` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partners/{id}/suspend` |
| Permission | `central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.suspend|finance.partners.manage` |
| Policy / authorize() | `suspend @ $partner` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/my-partner-assignments`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::myAssignments` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `myAssignments` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role:funding_partner` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/my-partner-assignments` |
| Production URL | `https://smeda.gov.sy/api/api/finance/my-partner-assignments` |
| Role | `funding_partner` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/partner-assignments/{id}/decision`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingPartnerController::decision` |
| Controller | `App\Http\Controllers\Api\FundingPartnerController` |
| Method | `decision` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:funding_partner|central_bank_admin|finance_manager|general_director|admin|super_admin|system_admin|finance.partner_assignments.decide|finance.partners.decide` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/partner-assignments/{id}/decision` |
| Production URL | `https://smeda.gov.sy/api/api/finance/partner-assignments/{id}/decision` |
| Permission | `funding_partner|central_bank_admin|finance_manager|general_director|admin|super_admin|system_admin|finance.partner_assignments.decide|finance.partners.decide` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `decision` | `required`, `in:approved,rejected,under_review,funded` |
| `approved_amount` | `nullable`, `numeric`, `min:0` |
| `approved_currency` | `nullable`, `string`, `max:8` |
| `decision_notes` | `nullable`, `string` |

```json
{
    "decision": "...",
    "approved_amount": "...",
    "approved_currency": "...",
    "decision_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/loans/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundedLoanController::stats` |
| Controller | `App\Http\Controllers\Api\FundedLoanController` |
| Method | `stats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/loans/stats` |
| Production URL | `https://smeda.gov.sy/api/api/finance/loans/stats` |
| Permission | `funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own` |
| Policy / authorize() | `viewAny @ FundedLoan::class` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/loans`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundedLoanController::index` |
| Controller | `App\Http\Controllers\Api\FundedLoanController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/loans` |
| Production URL | `https://smeda.gov.sy/api/api/finance/loans` |
| Permission | `funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own` |
| Policy / authorize() | `viewAny @ FundedLoan::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `funding_partner_id` | filled filter | controller |
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |
| `search` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/loans/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundedLoanController::show` |
| Controller | `App\Http\Controllers\Api\FundedLoanController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/loans/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/loans/{id}` |
| Permission | `funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own` |
| Policy / authorize() | `view @ $loan` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/loans/{id}/payments`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundedLoanController::payments` |
| Controller | `App\Http\Controllers\Api\FundedLoanController` |
| Method | `payments` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/loans/{id}/payments` |
| Production URL | `https://smeda.gov.sy/api/api/finance/loans/{id}/payments` |
| Permission | `funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own` |
| Policy / authorize() | `view @ $loan` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/finance/loans/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundedLoanController::update` |
| Controller | `App\Http\Controllers\Api\FundedLoanController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|funding_partner|general_director|admin|super_admin|system_admin|finance.loans.manage|finance.loans.update_own_status` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/loans/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/finance/loans/{id}` |
| Permission | `finance_manager|funding_partner|general_director|admin|super_admin|system_admin|finance.loans.manage|finance.loans.update_own_status` |
| Policy / authorize() | `update @ $loan` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `installment_count` | `sometimes`, `integer`, `min:1` |
| `installment_amount` | `nullable`, `numeric`, `min:0` |
| `end_date` | `nullable`, `date` |
| `status` | `sometimes`, `in:active,paid,defaulted,restructured,closed` |

```json
{
    "installment_count": "...",
    "installment_amount": "...",
    "end_date": "...",
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/loans/{id}/payments`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundedLoanController::storePayment` |
| Controller | `App\Http\Controllers\Api\FundedLoanController` |
| Method | `storePayment` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.payments|finance.loans.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/loans/{id}/payments` |
| Production URL | `https://smeda.gov.sy/api/api/finance/loans/{id}/payments` |
| Permission | `finance_manager|general_director|admin|super_admin|system_admin|finance.loans.payments|finance.loans.manage` |
| Policy / authorize() | `recordPayment @ $loan` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `due_date` | `required`, `date` |
| `paid_date` | `nullable`, `date` |
| `amount_due` | `required`, `numeric`, `min:0` |
| `amount_paid` | `nullable`, `numeric`, `min:0` |
| `status` | `nullable`, `in:pending,paid,late,partial,defaulted` |
| `notes` | `nullable`, `string` |

```json
{
    "due_date": "...",
    "paid_date": "...",
    "amount_due": "...",
    "amount_paid": "...",
    "status": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/loans/{id}/mark-defaulted`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundedLoanController::markDefaulted` |
| Controller | `App\Http\Controllers\Api\FundedLoanController` |
| Method | `markDefaulted` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.defaulted|finance.loans.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/loans/{id}/mark-defaulted` |
| Production URL | `https://smeda.gov.sy/api/api/finance/loans/{id}/mark-defaulted` |
| Permission | `finance_manager|general_director|admin|super_admin|system_admin|finance.loans.defaulted|finance.loans.manage` |
| Policy / authorize() | `markDefaulted @ $loan` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/finance/loans/{id}/close`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundedLoanController::close` |
| Controller | `App\Http\Controllers\Api\FundedLoanController` |
| Method | `close` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.close|finance.loans.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/loans/{id}/close` |
| Production URL | `https://smeda.gov.sy/api/api/finance/loans/{id}/close` |
| Permission | `finance_manager|general_director|admin|super_admin|system_admin|finance.loans.close|finance.loans.manage` |
| Policy / authorize() | `update @ $loan` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/metrics`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingMetricsController::metrics` |
| Controller | `App\Http\Controllers\Api\FundingMetricsController` |
| Method | `metrics` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/metrics` |
| Production URL | `https://smeda.gov.sy/api/api/finance/metrics` |
| Permission | `finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/funded/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingMetricsController::fundedStats` |
| Controller | `App\Http\Controllers\Api\FundingMetricsController` |
| Method | `fundedStats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/funded/stats` |
| Production URL | `https://smeda.gov.sy/api/api/finance/funded/stats` |
| Permission | `finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `search` | mixed | request->only |
| `status` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/funded`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingMetricsController::funded` |
| Controller | `App\Http\Controllers\Api\FundingMetricsController` |
| Method | `funded` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/funded` |
| Production URL | `https://smeda.gov.sy/api/api/finance/funded` |
| Permission | `finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `search` | mixed | request->only |
| `status` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/defaulted/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingMetricsController::defaultedStats` |
| Controller | `App\Http\Controllers\Api\FundingMetricsController` |
| Method | `defaultedStats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/defaulted/stats` |
| Production URL | `https://smeda.gov.sy/api/api/finance/defaulted/stats` |
| Permission | `finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `search` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/defaulted`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingMetricsController::defaulted` |
| Controller | `App\Http\Controllers\Api\FundingMetricsController` |
| Method | `defaulted` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/defaulted` |
| Production URL | `https://smeda.gov.sy/api/api/finance/defaulted` |
| Permission | `finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `search` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/cloud`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingMetricsController::cloud` |
| Controller | `App\Http\Controllers\Api\FundingMetricsController` |
| Method | `cloud` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/cloud` |
| Production URL | `https://smeda.gov.sy/api/api/finance/cloud` |
| Permission | `finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch` |
| API Resource | `FundingApplicationResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/finance/manager/dashboard`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `FundingMetricsController::managerDashboard` |
| Controller | `App\Http\Controllers\Api\FundingMetricsController` |
| Method | `managerDashboard` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:finance_manager|finance_officer|general_director|admin|super_admin|system_admin|finance.metrics.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/finance/manager/dashboard` |
| Production URL | `https://smeda.gov.sy/api/api/finance/manager/dashboard` |
| Permission | `finance_manager|finance_officer|general_director|admin|super_admin|system_admin|finance.metrics.view` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/trainer-profiles/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerProfileController::show` |
| Controller | `App\Http\Controllers\Api\TrainerProfileController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_trainer_profiles` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/trainer-profiles/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/trainer-profiles/{id}` |
| Permission | `view_trainer_profiles` |
| Policy / authorize() | `view @ $profile` |
| API Resource | `TrainerProfileResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/workforces`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `WorkforceController::index` |
| Controller | `App\Http\Controllers\Api\WorkforceController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforces` |
| Production URL | `https://smeda.gov.sy/api/api/workforces` |
| Permission | `general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager` |
| API Resource | `WorkforceResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | mixed | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/workforces/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `WorkforceController::show` |
| Controller | `App\Http\Controllers\Api\WorkforceController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforces/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/workforces/{id}` |
| Permission | `general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager` |
| API Resource | `WorkforceResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/workforces/enroll`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `WorkforceController::enroll` |
| Controller | `App\Http\Controllers\Api\WorkforceController` |
| Method | `enroll` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|training_manager` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforces/enroll` |
| Production URL | `https://smeda.gov.sy/api/api/workforces/enroll` |
| Permission | `general_director|admin|super_admin|system_admin|training_manager` |
| API Resource | `WorkforceResource` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `trainee_id` | `required`, `integer`, `exists:trainees,id` |
| `notes` | `nullable`, `string` |

```json
{
    "trainee_id": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 409, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/registration-requests/centers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterRegistrationRequestController::index` |
| Controller | `App\Http\Controllers\Api\TrainingCenterRegistrationRequestController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/centers` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/centers` |
| Permission | `view_registration_requests` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `status` | string | controller |
| `submitted_by_user_id` | filled filter | controller |
| `search` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/centers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterRegistrationRequestController::store` |
| Controller | `App\Http\Controllers\Api\TrainingCenterRegistrationRequestController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:create_center_registration_requests, throttle:registration-requests, throttle:file-upload` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/centers` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/centers` |
| Permission | `create_center_registration_requests` |
| Rate Limit | 10 طلبات/دقيقة لكل (user|guest|IP); 5 طلبات/دقيقة لكل (user|IP) |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `center_name` | `required`, `string`, `max:255` |
| `city` | `required`, `string`, `max:255` |
| `address` | `required`, `string`, `max:1000` |
| `phone` | `required`, `string`, `max:50` |
| `email` | `nullable`, `email`, `max:255` |
| `classification_requested` | `nullable`, `string`, `max:100` |
| `supports_online_training` | `required`, `boolean` |
| `supports_offline_training` | `required`, `boolean` |
| `latitude` | `required`, `numeric`, `between:-90,90` |
| `longitude` | `required`, `numeric`, `between:-180,180` |
| `license_number` | `required`, `string`, `max:255` |
| `license_issue_date` | `required`, `date` |
| `license_issued_by` | `required`, `string`, `max:255` |
| `license_image` | `required`, `file`, `mimes:jpg,jpeg,png,pdf,webp`, `max:5120` |
| `notes` | `nullable`, `string` |

```json
{
    "center_name": "...",
    "city": "...",
    "address": "...",
    "phone": "...",
    "email": "...",
    "classification_requested": "...",
    "supports_online_training": "...",
    "supports_offline_training": "...",
    "latitude": "...",
    "longitude": "...",
    "license_number": "...",
    "license_issue_date": "...",
    "license_issued_by": "...",
    "license_image": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/registration-requests/centers/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterRegistrationRequestController::show` |
| Controller | `App\Http\Controllers\Api\TrainingCenterRegistrationRequestController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/centers/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/centers/{id}` |
| Policy / authorize() | `view @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/centers/{id}/review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterRegistrationRequestController::review` |
| Controller | `App\Http\Controllers\Api\TrainingCenterRegistrationRequestController` |
| Method | `review` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:review_center_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/centers/{id}/review` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/centers/{id}/review` |
| Permission | `review_center_registration_requests` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `required`, `in:approved,rejected,under_review` |
| `decision_notes` | `nullable`, `string` |

```json
{
    "status": "...",
    "decision_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/registration-requests/trainers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerRegistrationRequestController::index` |
| Controller | `App\Http\Controllers\Api\TrainerRegistrationRequestController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/trainers` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/trainers` |
| Permission | `view_registration_requests` |
| API Resource | `TrainerRegistrationRequestResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `training_center_id` | filled filter | controller |
| `status` | string | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/trainers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerRegistrationRequestController::store` |
| Controller | `App\Http\Controllers\Api\TrainerRegistrationRequestController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:create_trainer_registration_requests, throttle:registration-requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/trainers` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/trainers` |
| Permission | `create_trainer_registration_requests` |
| Rate Limit | 10 طلبات/دقيقة لكل (user|guest|IP) |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `training_center_id` | `nullable`, `integer`, `exists:training_centers,id` |
| `full_name` | `required`, `string`, `max:255` |
| `national_id` | `nullable`, `string`, `max:100` |
| `phone` | `nullable`, `string`, `max:30` |
| `email` | `nullable`, `email`, `max:255` |
| `specialization` | `nullable`, `string`, `max:150` |
| `classification_requested` | `nullable`, `string`, `max:100` |
| `has_tot` | `nullable`, `boolean` |
| `tot_certificate_number` | `nullable`, `string`, `max:100` |
| `tot_certificate_source` | `nullable`, `string`, `max:255` |
| `tot_issue_date` | `nullable`, `date` |
| `tot_expiry_date` | `nullable`, `date` |
| `cv_file` | `nullable`, `string`, `max:255` |
| `certificate_file` | `nullable`, `string`, `max:255` |

```json
{
    "training_center_id": "...",
    "full_name": "...",
    "national_id": "...",
    "phone": "...",
    "email": "...",
    "specialization": "...",
    "classification_requested": "...",
    "has_tot": "...",
    "tot_certificate_number": "...",
    "tot_certificate_source": "...",
    "tot_issue_date": "...",
    "tot_expiry_date": "...",
    "cv_file": "...",
    "certificate_file": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500, 999


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/registration-requests/trainers/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerRegistrationRequestController::show` |
| Controller | `App\Http\Controllers\Api\TrainerRegistrationRequestController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/trainers/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/trainers/{id}` |
| Policy / authorize() | `view @ $row` |
| API Resource | `TrainerRegistrationRequestResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/trainers/{id}/review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerRegistrationRequestController::review` |
| Controller | `App\Http\Controllers\Api\TrainerRegistrationRequestController` |
| Method | `review` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:review_trainer_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/trainers/{id}/review` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/trainers/{id}/review` |
| Permission | `review_trainer_registration_requests` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `required`, `in:approved,rejected,cancelled` |
| `review_notes` | `nullable`, `string` |

```json
{
    "status": "...",
    "review_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/registration-requests/trainees`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineeRegistrationRequestController::index` |
| Controller | `App\Http\Controllers\Api\TraineeRegistrationRequestController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/trainees` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/trainees` |
| Permission | `view_registration_requests` |
| API Resource | `TraineeRegistrationRequestResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |
| `registration_mode` | string | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/trainees`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineeRegistrationRequestController::store` |
| Controller | `App\Http\Controllers\Api\TraineeRegistrationRequestController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:create_trainee_registration_requests, throttle:registration-requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/trainees` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/trainees` |
| Permission | `create_trainee_registration_requests` |
| Rate Limit | 10 طلبات/دقيقة لكل (user|guest|IP) |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `full_name` | `required`, `string`, `max:255` |
| `national_id` | `nullable`, `string`, `max:100` |
| `phone` | `nullable`, `string`, `max:30` |
| `email` | `nullable`, `email`, `max:255` |
| `city` | `nullable`, `string`, `max:100` |
| `address` | `nullable`, `string`, `max:255` |
| `birth_date` | `nullable`, `date` |
| `gender` | `nullable`, `in:male,female` |
| `education_level` | `nullable`, `string`, `max:100` |
| `registration_mode` | `required`, `in:self,guardian,group` |
| `guardian_name` | `nullable`, `string`, `max:255` |
| `guardian_phone` | `nullable`, `string`, `max:30` |
| `guardian_national_id` | `nullable`, `string`, `max:100` |
| `group_name` | `nullable`, `string`, `max:255` |

```json
{
    "full_name": "...",
    "national_id": "...",
    "phone": "...",
    "email": "...",
    "city": "...",
    "address": "...",
    "birth_date": "...",
    "gender": "...",
    "education_level": "...",
    "registration_mode": "...",
    "guardian_name": "...",
    "guardian_phone": "...",
    "guardian_national_id": "...",
    "group_name": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500, 999


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/registration-requests/trainees/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineeRegistrationRequestController::show` |
| Controller | `App\Http\Controllers\Api\TraineeRegistrationRequestController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/trainees/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/trainees/{id}` |
| Policy / authorize() | `view @ $row` |
| API Resource | `TraineeRegistrationRequestResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/trainees/{id}/review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineeRegistrationRequestController::review` |
| Controller | `App\Http\Controllers\Api\TraineeRegistrationRequestController` |
| Method | `review` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:review_trainee_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/trainees/{id}/review` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/trainees/{id}/review` |
| Permission | `review_trainee_registration_requests` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `required`, `in:approved,rejected,cancelled` |
| `review_notes` | `nullable`, `string` |

```json
{
    "status": "...",
    "review_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/registration-requests/courses`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CourseRegistrationRequestController::index` |
| Controller | `App\Http\Controllers\Api\CourseRegistrationRequestController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:view_registration_requests|create_course_registration_requests|confirm_course_registration_requests|complete_course_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/courses` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/courses` |
| Permission | `view_registration_requests|create_course_registration_requests|confirm_course_registration_requests|complete_course_registration_requests` |
| API Resource | `CourseRegistrationRequestResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `training_course_id` | filled filter | controller |
| `status` | string | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/courses`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CourseRegistrationRequestController::store` |
| Controller | `App\Http\Controllers\Api\CourseRegistrationRequestController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:create_course_registration_requests, throttle:registration-requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/courses` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/courses` |
| Permission | `create_course_registration_requests` |
| Rate Limit | 10 طلبات/دقيقة لكل (user|guest|IP) |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `training_course_id` | `required`, `integer`, `exists:training_courses,id` |
| `registration_mode` | `required`, `in:self,guardian_with_dependents,group_batch` |
| `submitted_by_type` | `nullable`, `string`, `max:50` |
| `applicant_name` | `required`, `string`, `max:255` |
| `applicant_phone` | `nullable`, `string`, `max:30` |
| `applicant_email` | `nullable`, `email`, `max:255` |
| `guardian_name` | `nullable`, `string`, `max:255` |
| `guardian_phone` | `nullable`, `string`, `max:30` |
| `guardian_national_id` | `nullable`, `string`, `max:100` |
| `notes` | `nullable`, `string` |
| `members` | `required`, `array`, `min:1` |
| `members.*.trainee_id` | `nullable`, `integer`, `exists:trainees,id` |
| `members.*.full_name` | `required`, `string`, `max:255` |
| `members.*.national_id` | `nullable`, `string`, `max:100` |
| `members.*.phone` | `nullable`, `string`, `max:30` |
| `members.*.email` | `nullable`, `email`, `max:255` |
| `members.*.birth_date` | `nullable`, `date` |
| `members.*.gender` | `nullable`, `in:male,female` |
| `members.*.education_level` | `nullable`, `string`, `max:100` |
| `members.*.relation_type` | `required`, `in:self,son,daughter,dependent,member` |
| `members.*.notes` | `nullable`, `string` |

```json
{
    "training_course_id": "...",
    "registration_mode": "...",
    "submitted_by_type": "...",
    "applicant_name": "...",
    "applicant_phone": "...",
    "applicant_email": "...",
    "guardian_name": "...",
    "guardian_phone": "...",
    "guardian_national_id": "...",
    "notes": "...",
    "members": "...",
    "members.*.trainee_id": "...",
    "members.*.full_name": "...",
    "members.*.national_id": "...",
    "members.*.phone": "...",
    "members.*.email": "...",
    "members.*.birth_date": "...",
    "members.*.gender": "...",
    "members.*.education_level": "...",
    "members.*.relation_type": "...",
    "members.*.notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500, 999


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/registration-requests/courses/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CourseRegistrationRequestController::show` |
| Controller | `App\Http\Controllers\Api\CourseRegistrationRequestController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/courses/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/courses/{id}` |
| Policy / authorize() | `view @ $row` |
| API Resource | `CourseRegistrationRequestResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/courses/{id}/confirm-by-guardian`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CourseRegistrationRequestController::confirmByGuardian` |
| Controller | `App\Http\Controllers\Api\CourseRegistrationRequestController` |
| Method | `confirmByGuardian` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:confirm_course_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/courses/{id}/confirm-by-guardian` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/courses/{id}/confirm-by-guardian` |
| Permission | `confirm_course_registration_requests` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/courses/{id}/complete`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CourseRegistrationRequestController::complete` |
| Controller | `App\Http\Controllers\Api\CourseRegistrationRequestController` |
| Method | `complete` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:complete_course_registration_requests` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/courses/{id}/complete` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/courses/{id}/complete` |
| Permission | `complete_course_registration_requests` |
| Policy / authorize() | `complete @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/registration-requests/courses/{id}/cancel`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CourseRegistrationRequestController::cancel` |
| Controller | `App\Http\Controllers\Api\CourseRegistrationRequestController` |
| Method | `cancel` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/registration-requests/courses/{id}/cancel` |
| Production URL | `https://smeda.gov.sy/api/api/registration-requests/courses/{id}/cancel` |
| Policy / authorize() | `cancel @ $row` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/categories`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `Closure::-` |
| Controller | `غير محدد` |
| Method | `غير محدد` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/categories` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/categories` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/offices`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfficeController::index` |
| Controller | `App\Http\Controllers\Api\ConsultingOfficeController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/offices` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/offices` |
| Permission | `admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `governorate_id` | filled filter | controller |
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |
| `category_code` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/offices/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfficeController::show` |
| Controller | `App\Http\Controllers\Api\ConsultingOfficeController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/offices/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/offices/{id}` |
| Permission | `admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/offices`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfficeController::store` |
| Controller | `App\Http\Controllers\Api\ConsultingOfficeController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/offices` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/offices` |
| Permission | `admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required`, `string`, `max:255` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `license_number` | `nullable`, `string`, `max:100` |
| `license_date` | `nullable`, `date` |
| `license_expiry` | `nullable`, `date` |
| `address` | `nullable`, `string` |
| `phone` | `nullable`, `string`, `max:30` |
| `email` | `nullable`, `email`, `max:255` |
| `website` | `nullable`, `url`, `max:255` |
| `specializations` | `nullable`, `array` |
| `specializations.*` | `string`, `max:10` |
| `bio` | `nullable`, `string` |
| `notes` | `nullable`, `string` |
| `accreditation_date` | `nullable`, `date` |

```json
{
    "name": "...",
    "governorate_id": "...",
    "license_number": "...",
    "license_date": "...",
    "license_expiry": "...",
    "address": "...",
    "phone": "...",
    "email": "...",
    "website": "...",
    "specializations": "...",
    "specializations.*": "...",
    "bio": "...",
    "notes": "...",
    "accreditation_date": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/consulting/offices/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfficeController::update` |
| Controller | `App\Http\Controllers\Api\ConsultingOfficeController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/offices/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/offices/{id}` |
| Permission | `admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `status` | string | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `sometimes`, `string`, `max:255` |
| `phone` | `nullable`, `string`, `max:30` |
| `email` | `nullable`, `email` |
| `address` | `nullable`, `string` |
| `website` | `nullable`, `url` |
| `license_number` | `nullable`, `string`, `max:100` |
| `license_date` | `nullable`, `date` |
| `license_expiry` | `nullable`, `date` |
| `notes` | `nullable`, `string` |

```json
{
    "name": "...",
    "phone": "...",
    "email": "...",
    "address": "...",
    "website": "...",
    "license_number": "...",
    "license_date": "...",
    "license_expiry": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/offices/{id}/activate`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfficeController::activate` |
| Controller | `App\Http\Controllers\Api\ConsultingOfficeController` |
| Method | `activate` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/offices/{id}/activate` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/offices/{id}/activate` |
| Permission | `admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/offices/{id}/suspend`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfficeController::suspend` |
| Controller | `App\Http\Controllers\Api\ConsultingOfficeController` |
| Method | `suspend` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/offices/{id}/suspend` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/offices/{id}/suspend` |
| Permission | `admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/offices/{id}/violations`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfficeController::addViolation` |
| Controller | `App\Http\Controllers\Api\ConsultingOfficeController` |
| Method | `addViolation` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/offices/{id}/violations` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/offices/{id}/violations` |
| Permission | `admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `violation_type` | `required`, `string`, `max:100` |
| `description` | `nullable`, `string` |

```json
{
    "violation_type": "...",
    "description": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/requests/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::stats` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `stats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/stats` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/stats` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| Policy / authorize() | `viewAny @ ConsultingRequest::class` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/requests`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::index` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| Policy / authorize() | `viewAny @ ConsultingRequest::class` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `governorate_id` | filled filter | controller |
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |
| `category_code` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/requests/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::show` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| Policy / authorize() | `view @ $req` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/requests/{id}/offers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfferController::index` |
| Controller | `App\Http\Controllers\Api\ConsultingOfferController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}/offers` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}/offers` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| Policy / authorize() | `view @ $req` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/requests`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::store` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:project_owner|admin|super_admin|system_admin|general_director|branch_manager|governor` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests` |
| Permission | `project_owner|admin|super_admin|system_admin|general_director|branch_manager|governor` |
| Policy / authorize() | `create @ ConsultingRequest::class` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `category_code` | `required`, `string`, `max:10`, `exists:consulting_categories,code` |
| `request_type` | `required`, `in:new_project,existing,financing,classification` |
| `title` | `required`, `string`, `max:255` |
| `description` | `required`, `string` |
| `project_name` | `nullable`, `string`, `max:255` |
| `economic_activity` | `nullable`, `string`, `max:255` |
| `isic4_code` | `nullable`, `string`, `max:10` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `budget_min` | `nullable`, `numeric`, `min:0` |
| `budget_max` | `nullable`, `numeric`, `min:0` |
| `expected_duration_days` | `nullable`, `integer`, `min:1` |

```json
{
    "category_code": "...",
    "request_type": "...",
    "title": "...",
    "description": "...",
    "project_name": "...",
    "economic_activity": "...",
    "isic4_code": "...",
    "governorate_id": "...",
    "budget_min": "...",
    "budget_max": "...",
    "expected_duration_days": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/consulting/requests/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::update` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| Policy / authorize() | `update @ $req` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `category_code` | `sometimes`, `string`, `max:10` |
| `request_type` | `sometimes`, `in:new_project,existing,financing,classification` |
| `title` | `sometimes`, `string`, `max:255` |
| `description` | `sometimes`, `string` |
| `project_name` | `nullable`, `string`, `max:255` |
| `economic_activity` | `nullable`, `string`, `max:255` |
| `isic4_code` | `nullable`, `string`, `max:10` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `budget_min` | `nullable`, `numeric`, `min:0` |
| `budget_max` | `nullable`, `numeric`, `min:0` |
| `expected_duration_days` | `nullable`, `integer`, `min:1` |

```json
{
    "category_code": "...",
    "request_type": "...",
    "title": "...",
    "description": "...",
    "project_name": "...",
    "economic_activity": "...",
    "isic4_code": "...",
    "governorate_id": "...",
    "budget_min": "...",
    "budget_max": "...",
    "expected_duration_days": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/requests/{id}/submit`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::submit` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `submit` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}/submit` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}/submit` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| Policy / authorize() | `update @ $req` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/requests/{id}/sort`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::sort` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `sort` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}/sort` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}/sort` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| Policy / authorize() | `sort @ $req` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `action` | `required`, `in:approve,needs_info` |
| `branch_notes` | `nullable`, `string` |

```json
{
    "action": "...",
    "branch_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/requests/{id}/accept-offer`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::acceptOffer` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `acceptOffer` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}/accept-offer` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}/accept-offer` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| Policy / authorize() | `acceptOffer @ $req` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `offer_id` | `required`, `integer`, `exists:consulting_offers,id` |

```json
{
    "offer_id": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/requests/{id}/transfer`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::transfer` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `transfer` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}/transfer` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}/transfer` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| Policy / authorize() | `transfer @ $req` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `target` | `required`, `in:financing,training,incubation,gis` |

```json
{
    "target": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/requests/{id}/attachments`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingRequestController::uploadAttachment` |
| Controller | `App\Http\Controllers\Api\ConsultingRequestController` |
| Method | `uploadAttachment` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office, throttle:file-upload` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}/attachments` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}/attachments` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| Policy / authorize() | `update @ $req` |
| Rate Limit | 5 طلبات/دقيقة لكل (user|IP) |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `stage` | mixed | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `file` | `required`, `file`, `max:10240`, `mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls` |
| `stage` | `nullable`, `in:request,execution,report` |

```json
{
    "file": "...",
    "stage": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/requests/{id}/offers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingOfferController::store` |
| Controller | `App\Http\Controllers\Api\ConsultingOfferController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/requests/{id}/offers` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/requests/{id}/offers` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `methodology_text` | `required`, `string` |
| `proposed_duration_days` | `required`, `integer`, `min:1` |
| `price` | `required`, `numeric`, `min:0` |
| `sample_attachments` | `nullable`, `string` |

```json
{
    "methodology_text": "...",
    "proposed_duration_days": "...",
    "price": "...",
    "sample_attachments": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/contracts/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingContractController::show` |
| Controller | `App\Http\Controllers\Api\ConsultingContractController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/contracts/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/contracts/{id}` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/consulting/contracts/{id}/messages`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingContractController::messages` |
| Controller | `App\Http\Controllers\Api\ConsultingContractController` |
| Method | `messages` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/contracts/{id}/messages` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/contracts/{id}/messages` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/contracts/{id}/sign`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingContractController::sign` |
| Controller | `App\Http\Controllers\Api\ConsultingContractController` |
| Method | `sign` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/contracts/{id}/sign` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/contracts/{id}/sign` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/contracts/{id}/messages`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingContractController::sendMessage` |
| Controller | `App\Http\Controllers\Api\ConsultingContractController` |
| Method | `sendMessage` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/contracts/{id}/messages` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/contracts/{id}/messages` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `message_text` | `required`, `string` |
| `attachment_path` | `nullable`, `string` |

```json
{
    "message_text": "...",
    "attachment_path": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/contracts/{id}/report`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingContractController::uploadReport` |
| Controller | `App\Http\Controllers\Api\ConsultingContractController` |
| Method | `uploadReport` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office, throttle:file-upload` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/contracts/{id}/report` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/contracts/{id}/report` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| Rate Limit | 5 طلبات/دقيقة لكل (user|IP) |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `recommendation_type` | mixed | controller |
| `recommendation_details` | mixed | controller |
| `isic4_recommendation` | mixed | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `file` | `required`, `file`, `mimes:pdf`, `max:20480` |
| `recommendation_type` | `nullable`, `in:none,financing,training,incubation,gis,license,activity_change` |
| `recommendation_details` | `nullable`, `string` |
| `isic4_recommendation` | `nullable`, `string`, `max:10` |

```json
{
    "file": "...",
    "recommendation_type": "...",
    "recommendation_details": "...",
    "isic4_recommendation": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/contracts/{id}/approve-report`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingContractController::approveReport` |
| Controller | `App\Http\Controllers\Api\ConsultingContractController` |
| Method | `approveReport` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/contracts/{id}/approve-report` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/contracts/{id}/approve-report` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `action` | `required`, `in:approve,return` |
| `reviewer_notes` | `nullable`, `string` |

```json
{
    "action": "...",
    "reviewer_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/consulting/contracts/{id}/review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ConsultingContractController::submitReview` |
| Controller | `App\Http\Controllers\Api\ConsultingContractController` |
| Method | `submitReview` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/consulting/contracts/{id}/review` |
| Production URL | `https://smeda.gov.sy/api/api/consulting/contracts/{id}/review` |
| Permission | `admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `overall_rating` | `required`, `integer`, `min:1`, `max:5` |
| `quality_rating` | `nullable`, `integer`, `min:1`, `max:5` |
| `time_rating` | `nullable`, `integer`, `min:1`, `max:5` |
| `communication_rating` | `nullable`, `integer`, `min:1`, `max:5` |
| `comment` | `nullable`, `string` |

```json
{
    "overall_rating": "...",
    "quality_rating": "...",
    "time_rating": "...",
    "communication_rating": "...",
    "comment": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::stats` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `stats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/stats` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/stats` |
| Permission | `general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/applications`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::allApplications` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `allApplications` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/applications` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/applications` |
| Permission | `general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/incubation/apply`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::apply` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `apply` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/apply` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/apply` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `incubator_id` | `required|exists:incubators,id` |
| `program_id` | `nullable|exists:incubation_programs,id` |
| `project_name` | `required|string|max:255` |
| `project_sector` | `nullable|string` |
| `business_stage` | `required|in:idea,pre_seed,seed,early,growth` |
| `project_description` | `required|string` |
| `problem_statement` | `nullable|string` |
| `target_market` | `nullable|string` |
| `team_size` | `nullable|integer|min:1|max:500` |
| `has_prototype` | `nullable|boolean` |
| `has_revenue` | `nullable|boolean` |
| `funding_needed` | `nullable|numeric|min:0` |
| `funding_stage` | `nullable|in:bootstrapped,seeking,funded` |
| `expected_jobs` | `nullable|integer|min:0` |
| `competitive_advantage` | `nullable|string` |
| `tech_readiness_level` | `nullable|integer|min:1|max:9` |
| `revenue_model` | `nullable|in:saas,b2b,b2c,marketplace,hardware,freemium,other` |
| `demo_url` | `nullable|url` |
| `github_url` | `nullable|url` |
| `has_ip` | `nullable|boolean` |
| `ip_description` | `nullable|string` |
| `tech_stack` | `nullable|array` |
| `tech_stack.*` | `string|max:60` |
| `target_platform` | `nullable|in:web,mobile,desktop,saas,api,embedded,other` |

```json
{
    "incubator_id": "...",
    "program_id": "...",
    "project_name": "...",
    "project_sector": "...",
    "business_stage": "...",
    "project_description": "...",
    "problem_statement": "...",
    "target_market": "...",
    "team_size": "...",
    "has_prototype": "...",
    "has_revenue": "...",
    "funding_needed": "...",
    "funding_stage": "...",
    "expected_jobs": "...",
    "competitive_advantage": "...",
    "tech_readiness_level": "...",
    "revenue_model": "...",
    "demo_url": "...",
    "github_url": "...",
    "has_ip": "...",
    "ip_description": "...",
    "tech_stack": "...",
    "tech_stack.*": "...",
    "target_platform": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/my-applications`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::myApplications` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `myApplications` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/my-applications` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/my-applications` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/applications/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::showApplication` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `showApplication` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/applications/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/applications/{id}` |
| Policy / authorize() | `view @ $app` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/incubation/applications/{id}/review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::reviewApplication` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `reviewApplication` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/applications/{id}/review` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/applications/{id}/review` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |
| Policy / authorize() | `review @ $app` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `required|in:accepted,rejected,under_review` |
| `reviewer_notes` | `nullable|string` |

```json
{
    "status": "...",
    "reviewer_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/projects`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::projects` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `projects` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/projects` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/projects` |
| Permission | `general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/my-project`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::myProject` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `myProject` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/my-project` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/my-project` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/projects/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::showProject` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `showProject` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/projects/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/projects/{id}` |
| Permission | `general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |
| Policy / authorize() | `view @ $project` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/incubation/projects/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::updateProject` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `updateProject` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/projects/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/projects/{id}` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage` |
| Policy / authorize() | `update @ $project` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `stage` | `nullable|in:seed,early,growth,exit` |
| `status` | `nullable|in:active,graduated,withdrawn,terminated` |
| `mentor_user_id` | `nullable|exists:users,id` |
| `expected_end_date` | `nullable|date` |
| `actual_end_date` | `nullable|date` |
| `current_revenue` | `nullable|numeric|min:0` |
| `current_employees` | `nullable|integer|min:0` |
| `notes` | `nullable|string` |

```json
{
    "stage": "...",
    "status": "...",
    "mentor_user_id": "...",
    "expected_end_date": "...",
    "actual_end_date": "...",
    "current_revenue": "...",
    "current_employees": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/sessions`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::indexMentoringSessions` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `indexMentoringSessions` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/sessions` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/sessions` |
| Permission | `general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `incubator_id` | filled filter | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/incubation/projects/{id}/sessions`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::storeMentoringSession` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `storeMentoringSession` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/projects/{id}/sessions` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/projects/{id}/sessions` |
| Permission | `incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `session_date` | `required|date` |
| `duration_minutes` | `nullable|integer|min:15` |
| `topic` | `required|string|max:255` |
| `notes` | `nullable|string` |
| `action_items` | `nullable|string` |
| `rating` | `nullable|integer|min:1|max:5` |
| `status` | `nullable|in:scheduled,completed,cancelled` |

```json
{
    "session_date": "...",
    "duration_minutes": "...",
    "topic": "...",
    "notes": "...",
    "action_items": "...",
    "rating": "...",
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/incubation/my-sessions`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::myMentoringSessions` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `myMentoringSessions` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/my-sessions` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/my-sessions` |
| Permission | `incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/incubation/projects/{id}/reports`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `IncubatorController::storeProgressReport` |
| Controller | `App\Http\Controllers\Api\IncubatorController` |
| Method | `storeProgressReport` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:incubation-report` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/incubation/projects/{id}/reports` |
| Production URL | `https://smeda.gov.sy/api/api/incubation/projects/{id}/reports` |
| Policy / authorize() | `submitProgressReport @ $project` |
| Rate Limit | 10 طلبات/دقيقة لكل user|IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `period_type` | `required|in:monthly,quarterly` |
| `period_label` | `required|string|max:50` |
| `revenue` | `nullable|numeric|min:0` |
| `employees` | `nullable|integer|min:0` |
| `customers` | `nullable|integer|min:0` |
| `achievements` | `nullable|string` |
| `challenges` | `nullable|string` |
| `next_steps` | `nullable|string` |
| `overall_rating` | `nullable|integer|min:1|max:10` |

```json
{
    "period_type": "...",
    "period_label": "...",
    "revenue": "...",
    "employees": "...",
    "customers": "...",
    "achievements": "...",
    "challenges": "...",
    "next_steps": "...",
    "overall_rating": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/locations/governorates`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SyriaLocationController::governorates` |
| Controller | `App\Http\Controllers\Api\SyriaLocationController` |
| Method | `governorates` |
| Route Name | `—` |
| Middleware | `api, throttle:60,1` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/locations/governorates` |
| Production URL | `https://smeda.gov.sy/api/api/locations/governorates` |
| Rate Limit | 60 طلبات كل 1 دقائق لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/locations/districts`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SyriaLocationController::districts` |
| Controller | `App\Http\Controllers\Api\SyriaLocationController` |
| Method | `districts` |
| Route Name | `—` |
| Middleware | `api, throttle:60,1` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/locations/districts` |
| Production URL | `https://smeda.gov.sy/api/api/locations/districts` |
| Rate Limit | 60 طلبات كل 1 دقائق لكل IP |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `gov` | `required|string|max:10` |

**Status Codes المحتملة:** 200, 404, 422, 429, 500


### GET `api/locations/subdistricts`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SyriaLocationController::subdistricts` |
| Controller | `App\Http\Controllers\Api\SyriaLocationController` |
| Method | `subdistricts` |
| Route Name | `—` |
| Middleware | `api, throttle:60,1` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/locations/subdistricts` |
| Production URL | `https://smeda.gov.sy/api/api/locations/subdistricts` |
| Rate Limit | 60 طلبات كل 1 دقائق لكل IP |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `district` | `required|string|max:10` |

**Status Codes المحتملة:** 200, 404, 422, 429, 500


### GET `api/locations/communities`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SyriaLocationController::communities` |
| Controller | `App\Http\Controllers\Api\SyriaLocationController` |
| Method | `communities` |
| Route Name | `—` |
| Middleware | `api, throttle:60,1` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/locations/communities` |
| Production URL | `https://smeda.gov.sy/api/api/locations/communities` |
| Rate Limit | 60 طلبات كل 1 دقائق لكل IP |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `subdistrict` | `required|string|max:10` |

**Status Codes المحتملة:** 200, 404, 422, 429, 500


### GET `api/locations/search`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SyriaLocationController::search` |
| Controller | `App\Http\Controllers\Api\SyriaLocationController` |
| Method | `search` |
| Route Name | `—` |
| Middleware | `api, throttle:60,1` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/locations/search` |
| Production URL | `https://smeda.gov.sy/api/api/locations/search` |
| Rate Limit | 60 طلبات كل 1 دقائق لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `limit` | integer | controller |
| `lat` | mixed | controller |
| `lng` | mixed | controller |
| `near` | boolean | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `lat` | `required`, `numeric`, `between:-90,90` |
| `lng` | `required`, `numeric`, `between:-180,180` |
| `q` | `required`, `string`, `min:2`, `max:120` |

**Status Codes المحتملة:** 200, 404, 422, 429, 500


### GET `api/locations/map`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SyriaLocationController::mapPoints` |
| Controller | `App\Http\Controllers\Api\SyriaLocationController` |
| Method | `mapPoints` |
| Route Name | `—` |
| Middleware | `api, throttle:60,1` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/locations/map` |
| Production URL | `https://smeda.gov.sy/api/api/locations/map` |
| Rate Limit | 60 طلبات كل 1 دقائق لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `limit` | integer | controller |
| `gov` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


---

<a id="module-Printing"></a>
## وحدة: Printing


### GET `trainers/{id}/card`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerPrintController::show` |
| Controller | `App\Http\Controllers\TrainerPrintController` |
| Method | `show` |
| Route Name | `trainers.card` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/trainers/{id}/card` |
| Production URL | `https://smeda.gov.sy/api/trainers/{id}/card` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `trainers/{id}/card/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerPrintController::pdf` |
| Controller | `App\Http\Controllers\TrainerPrintController` |
| Method | `pdf` |
| Route Name | `trainers.card.pdf` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/trainers/{id}/card/pdf` |
| Production URL | `https://smeda.gov.sy/api/trainers/{id}/card/pdf` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `trainees/{id}/card`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineePrintController::show` |
| Controller | `App\Http\Controllers\TraineePrintController` |
| Method | `show` |
| Route Name | `trainees.card` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/trainees/{id}/card` |
| Production URL | `https://smeda.gov.sy/api/trainees/{id}/card` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `trainees/{id}/card/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineePrintController::pdf` |
| Controller | `App\Http\Controllers\TraineePrintController` |
| Method | `pdf` |
| Route Name | `trainees.card.pdf` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/trainees/{id}/card/pdf` |
| Production URL | `https://smeda.gov.sy/api/trainees/{id}/card/pdf` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


---

<a id="module-Program-Bank"></a>
## وحدة: Program Bank


### GET `api/program-bank/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::stats` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `stats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/stats` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/stats` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/program-bank/reports`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::reports` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `reports` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.reports|program_bank.view|view_reports` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/reports` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/reports` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.reports|program_bank.view|view_reports` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `type` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/program-bank`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::index` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `bank_status` | mixed | controller |
| `type` | mixed | controller |
| `level` | mixed | controller |
| `sector` | mixed | controller |
| `grants_certificate` | mixed | controller |
| `per_page` | integer | pagination default=20 max=100 |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/program-bank`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::store` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `required|string|max:255` |
| `title` | `nullable|string|max:255` |
| `code` | `required|string|max:50|unique:training_programs,code` |
| `description` | `nullable|string` |
| `type` | `nullable` |
| `sector` | `nullable|string|max:100` |
| `target_audience` | `nullable|string|max:255` |
| `level` | `nullable`, `beginner`, `intermediate`, `advanced` |

```json
{
    "name": "...",
    "title": "...",
    "code": "...",
    "description": "...",
    "type": "...",
    "sector": "...",
    "target_audience": "...",
    "level": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/program-bank/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::show` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/program-bank/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::update` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `sometimes|required|string|max:255` |
| `title` | `nullable|string|max:255` |
| `code` | `sometimes`, `required`, `string`, `max:50`, `training_programs`, `code` |
| `description` | `nullable|string` |
| `type` | `nullable` |
| `sector` | `nullable|string|max:100` |
| `target_audience` | `nullable|string|max:255` |
| `level` | `nullable`, `beginner`, `intermediate`, `advanced` |

```json
{
    "name": "...",
    "title": "...",
    "code": "...",
    "description": "...",
    "type": "...",
    "sector": "...",
    "target_audience": "...",
    "level": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/program-bank/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::destroy` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.delete|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.delete|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/program-bank/{id}/duplicate`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::duplicate` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `duplicate` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/duplicate` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/duplicate` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/program-bank/{id}/transition`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::transition` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `transition` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|deputy_director|program_bank.approve|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/transition` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/transition` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|deputy_director|program_bank.approve|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `action` | mixed | controller |
| `notes` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/program-bank/{id}/create-course`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::createCourseFromProgram` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `createCourseFromProgram` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/create-course` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/create-course` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `start_date` | mixed | controller |
| `end_date` | mixed | controller |
| `training_center_id` | mixed | controller |
| `trainer_id` | mixed | controller |
| `capacity` | mixed | controller |
| `location` | mixed | controller |
| `governorate` | mixed | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `start_date` | `required|date` |
| `end_date` | `required|date|after:start_date` |
| `training_center_id` | `required|exists:training_centers,id` |
| `trainer_id` | `nullable|exists:trainers,id` |
| `capacity` | `nullable|integer|min:1|max:999` |
| `location` | `nullable|string|max:255` |
| `governorate` | `nullable|string|max:100` |

```json
{
    "start_date": "...",
    "end_date": "...",
    "training_center_id": "...",
    "trainer_id": "...",
    "capacity": "...",
    "location": "...",
    "governorate": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/program-bank/{id}/modules`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::storeModule` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `storeModule` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/modules` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/modules` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `title` | `required|string|max:255` |
| `description` | `nullable|string` |
| `hours` | `nullable|integer|min:0` |
| `sort_order` | `nullable|integer|min:0` |
| `objectives` | `nullable|string` |
| `activities` | `nullable|string` |
| `required_tools` | `nullable|string` |
| `evaluation_method` | `nullable|string|max:255` |

```json
{
    "title": "...",
    "description": "...",
    "hours": "...",
    "sort_order": "...",
    "objectives": "...",
    "activities": "...",
    "required_tools": "...",
    "evaluation_method": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/program-bank/{id}/modules/{moduleId}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::updateModule` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `updateModule` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/modules/{moduleId}` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/modules/{moduleId}` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `moduleId` | نعم | معامل مسار |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `title` | mixed | request->only |
| `description` | mixed | request->only |
| `hours` | mixed | request->only |
| `sort_order` | mixed | request->only |
| `objectives` | mixed | request->only |
| `activities` | mixed | request->only |
| `required_tools` | mixed | request->only |
| `evaluation_method` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/program-bank/{id}/modules/{moduleId}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::destroyModule` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `destroyModule` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/modules/{moduleId}` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/modules/{moduleId}` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `moduleId` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/program-bank/{id}/modules/reorder`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::reorderModules` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `reorderModules` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/modules/reorder` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/modules/reorder` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `order` | mixed | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `order` | `required|array` |
| `order.*` | `integer` |

```json
{
    "order": "...",
    "order.*": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/program-bank/{id}/outcomes`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::storeOutcome` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `storeOutcome` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/outcomes` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/outcomes` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `title` | `required|string|max:255` |
| `description` | `nullable|string` |
| `sort_order` | `nullable|integer|min:0` |

```json
{
    "title": "...",
    "description": "...",
    "sort_order": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/program-bank/{id}/outcomes/{outcomeId}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::updateOutcome` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `updateOutcome` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/outcomes/{outcomeId}` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/outcomes/{outcomeId}` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `outcomeId` | نعم | معامل مسار |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `title` | mixed | request->only |
| `description` | mixed | request->only |
| `sort_order` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/program-bank/{id}/outcomes/{outcomeId}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::destroyOutcome` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `destroyOutcome` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/outcomes/{outcomeId}` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/outcomes/{outcomeId}` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `outcomeId` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/program-bank/{id}/service-links`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ProgramBankController::syncServiceLinks` |
| Controller | `App\Http\Controllers\Api\ProgramBankController` |
| Method | `syncServiceLinks` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/program-bank/{id}/service-links` |
| Production URL | `https://smeda.gov.sy/api/api/program-bank/{id}/service-links` |
| Permission | `training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `links` | mixed | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `links` | `required|array` |
| `links.*.service_type` | `required` |
| `links.*.notes` | `nullable|string` |

```json
{
    "links": "...",
    "links.*.service_type": "...",
    "links.*.notes": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Public-APIs"></a>
## وحدة: Public APIs


### GET `api/public/governorates`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PublicBrowseController::governorates` |
| Controller | `App\Http\Controllers\Api\PublicBrowseController` |
| Method | `governorates` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/public/governorates` |
| Production URL | `https://smeda.gov.sy/api/api/public/governorates` |
| API Resource | `GovernorateResource::collection` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/public/needs/lookups`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PublicBrowseController::needsLookups` |
| Controller | `App\Http\Controllers\Api\PublicBrowseController` |
| Method | `needsLookups` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/public/needs/lookups` |
| Production URL | `https://smeda.gov.sy/api/api/public/needs/lookups` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/public/needs/map`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PublicBrowseController::needsMap` |
| Controller | `App\Http\Controllers\Api\PublicBrowseController` |
| Method | `needsMap` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/public/needs/map` |
| Production URL | `https://smeda.gov.sy/api/api/public/needs/map` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `limit` | integer | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### POST `api/public/needs`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PublicBrowseController::storeGuestNeed` |
| Controller | `App\Http\Controllers\Api\PublicBrowseController` |
| Method | `storeGuestNeed` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public, throttle:5,10` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/public/needs` |
| Production URL | `https://smeda.gov.sy/api/api/public/needs` |
| Rate Limit | 60 طلبات/دقيقة لكل IP; 5 طلبات كل 10 دقائق لكل IP |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `title` | `required`, `string`, `max:255` |
| `description` | `nullable`, `string` |
| `summary` | `nullable`, `string`, `max:500` |
| `need_owner_type` | `nullable`, `in:citizen,state` |
| `need_scope` | `nullable`, `in:individual,project,local,governorate,national,sectoral` |
| `need_complexity` | `nullable`, `in:general,specific` |
| `need_type` | `nullable`, `string`, `max:100` |
| `need_category` | `nullable`, `string`, `max:100` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `district_name` | `nullable`, `string`, `max:255` |
| `administrative_unit_name` | `nullable`, `string`, `max:255` |
| `countryside_name` | `nullable`, `string`, `max:255` |
| `locality_name` | `nullable`, `string`, `max:255` |
| `village_or_neighborhood` | `nullable`, `string`, `max:255` |
| `address_details` | `nullable`, `string` |
| `latitude` | `required`, `numeric`, `between:32,37.5` |
| `longitude` | `required`, `numeric`, `between:35.4,42.5` |
| `location_source` | `nullable`, `string`, `max:100` |
| `sector` | `nullable`, `string`, `max:100` |
| `economic_sector` | `nullable`, `string`, `max:100` |
| `syrsic_section` | `nullable`, `string`, `max:100` |
| `syrsic_division` | `nullable`, `string`, `max:100` |
| `syrsic_group` | `nullable`, `string`, `max:100` |
| `syrsic_class` | `nullable`, `string`, `max:100` |
| `syrsic_activity` | `nullable`, `string`, `max:100` |
| `priority` | `nullable`, `string`, `max:50` |
| `state_need_level` | `nullable`, `string`, `max:100` |
| `citizen_need_profile` | `nullable`, `string`, `max:100` |
| `responsible_entity` | `nullable`, `string`, `max:255` |
| `applicant_name` | `nullable`, `string`, `max:255` |
| `applicant_phone` | `nullable`, `string`, `max:50` |
| `applicant_email` | `nullable`, `email`, `max:255` |
| `applicant_type` | `nullable`, `string`, `max:100` |
| `organization_name` | `nullable`, `string`, `max:255` |
| `beneficiaries_count` | `nullable`, `integer`, `min:0` |
| `expected_jobs_count` | `nullable`, `integer`, `min:0` |
| `expected_projects_count` | `nullable`, `integer`, `min:0` |
| `impact_level` | `nullable`, `string`, `max:100` |
| `urgency_level` | `nullable`, `string`, `max:100` |
| `expected_duration` | `nullable`, `string`, `max:100` |
| `available_partners` | `nullable`, `string` |
| `obstacles` | `nullable`, `string` |
| `requirements` | `nullable`, `string` |
| `notes` | `nullable`, `string` |

```json
{
    "title": "...",
    "description": "...",
    "summary": "...",
    "need_owner_type": "...",
    "need_scope": "...",
    "need_complexity": "...",
    "need_type": "...",
    "need_category": "...",
    "governorate_id": "...",
    "district_name": "...",
    "administrative_unit_name": "...",
    "countryside_name": "...",
    "locality_name": "...",
    "village_or_neighborhood": "...",
    "address_details": "...",
    "latitude": "...",
    "longitude": "...",
    "location_source": "...",
    "sector": "...",
    "economic_sector": "...",
    "syrsic_section": "...",
    "syrsic_division": "...",
    "syrsic_group": "...",
    "syrsic_class": "...",
    "syrsic_activity": "...",
    "priority": "...",
    "state_need_level": "...",
    "citizen_need_profile": "...",
    "responsible_entity": "...",
    "applicant_name": "...",
    "applicant_phone": "...",
    "applicant_email": "...",
    "applicant_type": "...",
    "organization_name": "...",
    "beneficiaries_count": "...",
    "expected_jobs_count": "...",
    "expected_projects_count": "...",
    "impact_level": "...",
    "urgency_level": "...",
    "expected_duration": "...",
    "available_partners": "...",
    "obstacles": "...",
    "requirements": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 404, 422, 429, 500


### GET `api/public/training-programs`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PublicBrowseController::trainingPrograms` |
| Controller | `App\Http\Controllers\Api\PublicBrowseController` |
| Method | `trainingPrograms` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/public/training-programs` |
| Production URL | `https://smeda.gov.sy/api/api/public/training-programs` |
| API Resource | `TrainingProgramResource::collection` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 404, 429, 500


### GET `api/public/finance/cloud`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PublicBrowseController::financeCloud` |
| Controller | `App\Http\Controllers\Api\PublicBrowseController` |
| Method | `financeCloud` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/public/finance/cloud` |
| Production URL | `https://smeda.gov.sy/api/api/public/finance/cloud` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 404, 429, 500


### GET `api/public/finance/metrics`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PublicBrowseController::financeMetrics` |
| Controller | `App\Http\Controllers\Api\PublicBrowseController` |
| Method | `financeMetrics` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/public/finance/metrics` |
| Production URL | `https://smeda.gov.sy/api/api/public/finance/metrics` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/public/job-postings`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `PublicBrowseController::jobPostings` |
| Controller | `App\Http\Controllers\Api\PublicBrowseController` |
| Method | `jobPostings` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/public/job-postings` |
| Production URL | `https://smeda.gov.sy/api/api/public/job-postings` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `sector` | mixed | request->only |
| `city` | mixed | request->only |
| `search` | mixed | request->only |
| `page` | mixed | request->only |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 404, 429, 500


---

<a id="module-Signatures"></a>
## وحدة: Signatures


### GET `api/signatures/verify/{code}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `ExecutiveSignatureController::verify` |
| Controller | `App\Http\Controllers\Api\ExecutiveSignatureController` |
| Method | `verify` |
| Route Name | `—` |
| Middleware | `api, throttle:certificate-verify` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/signatures/verify/{code}` |
| Production URL | `https://smeda.gov.sy/api/api/signatures/verify/{code}` |
| Rate Limit | 30 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `code` | نعم | رمز التحقق |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


---

<a id="module-Success-Stories"></a>
## وحدة: Success Stories


### GET `api/success-stories`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SuccessStoryController::index` |
| Controller | `App\Http\Controllers\Api\SuccessStoryController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/success-stories` |
| Production URL | `https://smeda.gov.sy/api/api/success-stories` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `status` | filled filter | controller |
| `per_page` | integer | pagination default=1 max=2 |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/success-stories/slug/{slug}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SuccessStoryController::showBySlug` |
| Controller | `App\Http\Controllers\Api\SuccessStoryController` |
| Method | `showBySlug` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/success-stories/slug/{slug}` |
| Production URL | `https://smeda.gov.sy/api/api/success-stories/slug/{slug}` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `slug` | نعم | المعرّف النصي (slug) |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/success-stories/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SuccessStoryController::show` |
| Controller | `App\Http\Controllers\Api\SuccessStoryController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, throttle:map-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/success-stories/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/success-stories/{id}` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `api/success-stories/stats`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SuccessStoryController::stats` |
| Controller | `App\Http\Controllers\Api\SuccessStoryController` |
| Method | `stats` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/success-stories/stats` |
| Production URL | `https://smeda.gov.sy/api/api/success-stories/stats` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/success-stories`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SuccessStoryController::store` |
| Controller | `App\Http\Controllers\Api\SuccessStoryController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/success-stories` |
| Production URL | `https://smeda.gov.sy/api/api/success-stories` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `title` | `required|string|max:255` |
| `summary` | `required|string` |
| `body` | `required|string` |
| `hero_name` | `required|string|max:255` |
| `hero_title` | `nullable|string|max:255` |
| `hero_photo_url` | `nullable|url` |
| `project_name` | `nullable|string|max:255` |
| `sector` | `nullable|string` |
| `incubated_project_id` | `nullable|exists:incubated_projects,id` |
| `incubator_id` | `nullable|exists:incubators,id` |
| `branch_id` | `nullable|exists:branches,id` |
| `revenue_achieved` | `nullable|numeric|min:0` |
| `jobs_created` | `nullable|integer|min:0` |
| `years_in_incubator` | `nullable|integer|min:0` |
| `current_stage` | `nullable|string|max:255` |
| `featured_quote` | `nullable|string` |
| `cover_image_url` | `nullable|url` |
| `video_url` | `nullable|url` |
| `gallery` | `nullable|array` |
| `gallery.*` | `url` |
| `status` | `nullable|in:draft,published,archived` |
| `is_featured` | `nullable|boolean` |

```json
{
    "title": "...",
    "summary": "...",
    "body": "...",
    "hero_name": "...",
    "hero_title": "...",
    "hero_photo_url": "...",
    "project_name": "...",
    "sector": "...",
    "incubated_project_id": "...",
    "incubator_id": "...",
    "branch_id": "...",
    "revenue_achieved": "...",
    "jobs_created": "...",
    "years_in_incubator": "...",
    "current_stage": "...",
    "featured_quote": "...",
    "cover_image_url": "...",
    "video_url": "...",
    "gallery": "...",
    "gallery.*": "...",
    "status": "...",
    "is_featured": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/success-stories/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SuccessStoryController::update` |
| Controller | `App\Http\Controllers\Api\SuccessStoryController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/success-stories/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/success-stories/{id}` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `title` | `sometimes|string|max:255` |
| `summary` | `sometimes|string` |
| `body` | `sometimes|string` |
| `hero_name` | `sometimes|string|max:255` |
| `hero_title` | `nullable|string|max:255` |
| `hero_photo_url` | `nullable|url` |
| `project_name` | `nullable|string|max:255` |
| `sector` | `nullable|string` |
| `incubated_project_id` | `nullable|exists:incubated_projects,id` |
| `incubator_id` | `nullable|exists:incubators,id` |
| `branch_id` | `nullable|exists:branches,id` |
| `revenue_achieved` | `nullable|numeric|min:0` |
| `jobs_created` | `nullable|integer|min:0` |
| `years_in_incubator` | `nullable|integer|min:0` |
| `current_stage` | `nullable|string|max:255` |
| `featured_quote` | `nullable|string` |
| `cover_image_url` | `nullable|url` |
| `video_url` | `nullable|url` |
| `gallery` | `nullable|array` |
| `status` | `nullable|in:draft,published,archived` |
| `is_featured` | `nullable|boolean` |

```json
{
    "title": "...",
    "summary": "...",
    "body": "...",
    "hero_name": "...",
    "hero_title": "...",
    "hero_photo_url": "...",
    "project_name": "...",
    "sector": "...",
    "incubated_project_id": "...",
    "incubator_id": "...",
    "branch_id": "...",
    "revenue_achieved": "...",
    "jobs_created": "...",
    "years_in_incubator": "...",
    "current_stage": "...",
    "featured_quote": "...",
    "cover_image_url": "...",
    "video_url": "...",
    "gallery": "...",
    "status": "...",
    "is_featured": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/success-stories/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `SuccessStoryController::destroy` |
| Controller | `App\Http\Controllers\Api\SuccessStoryController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/success-stories/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/success-stories/{id}` |
| Permission | `general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Trainees"></a>
## وحدة: Trainees


### GET `api/trainees`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineeController::index` |
| Controller | `App\Http\Controllers\Api\TraineeController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_trainees` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/trainees` |
| Production URL | `https://smeda.gov.sy/api/api/trainees` |
| Permission | `view_trainees` |
| Policy / authorize() | `viewAny @ Trainee::class` |
| API Resource | `TraineeResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | mixed | controller |
| `city` | mixed | controller |
| `gender` | mixed | controller |
| `education_level` | mixed | controller |
| `has_location` | filled filter | controller |
| `search` | mixed | controller |
| `with_courses` | boolean | controller |
| `with_certificates` | boolean | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/trainees/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineeController::show` |
| Controller | `App\Http\Controllers\Api\TraineeController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_trainees` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/trainees/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/trainees/{id}` |
| Permission | `view_trainees` |
| Policy / authorize() | `view @ $trainee` |
| API Resource | `TraineeResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Trainers"></a>
## وحدة: Trainers


### GET `api/trainers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerController::index` |
| Controller | `App\Http\Controllers\Api\TrainerController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_trainers` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/trainers` |
| Production URL | `https://smeda.gov.sy/api/api/trainers` |
| Permission | `view_trainers` |
| Policy / authorize() | `viewAny @ Trainer::class` |
| API Resource | `TrainerResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `training_center_id` | mixed | controller |
| `status` | mixed | controller |
| `has_tot` | mixed | controller |
| `can_train` | mixed | controller |
| `can_evaluate` | mixed | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/trainers/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerController::show` |
| Controller | `App\Http\Controllers\Api\TrainerController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_trainers` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/trainers/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/trainers/{id}` |
| Permission | `view_trainers` |
| Policy / authorize() | `view @ $trainer` |
| API Resource | `TrainerResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Training-Centers"></a>
## وحدة: Training Centers


### GET `api/training-centers`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterController::index` |
| Controller | `App\Http\Controllers\Api\TrainingCenterController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_centers` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-centers` |
| Production URL | `https://smeda.gov.sy/api/api/training-centers` |
| Permission | `view_centers` |
| Policy / authorize() | `viewAny @ TrainingCenter::class` |
| API Resource | `TrainingCenterResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `supervisor_id` | mixed | controller |
| `status` | mixed | controller |
| `city` | mixed | controller |
| `classification` | mixed | controller |
| `is_active` | mixed | controller |
| `supports_online_training` | mixed | controller |
| `supports_offline_training` | mixed | controller |
| `has_location` | filled filter | controller |
| `with_platforms` | boolean | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/training-centers/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterController::show` |
| Controller | `App\Http\Controllers\Api\TrainingCenterController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_centers` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-centers/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/training-centers/{id}` |
| Permission | `view_centers` |
| Policy / authorize() | `view @ $center` |
| API Resource | `TrainingCenterResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Training-Courses"></a>
## وحدة: Training Courses


### GET `api/training-courses`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::index` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:trainer_user|trainee_user|view_courses` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses` |
| Permission | `trainer_user|trainee_user|view_courses` |
| Policy / authorize() | `viewAny @ TrainingCourse::class` |
| API Resource | `TrainingCourseResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | mixed | controller |
| `delivery_mode` | mixed | controller |
| `training_center_id` | mixed | controller |
| `trainer_id` | mixed | controller |
| `training_kit_id` | mixed | controller |
| `training_program_id` | mixed | controller |
| `start_date_from` | mixed | controller |
| `start_date_to` | mixed | controller |
| `with_trainees` | boolean | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/training-courses`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::store` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:manage_courses` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses` |
| Permission | `manage_courses` |
| Form Request | `App\Http\Requests\Training\StoreTrainingCourseRequest` |
| API Resource | `TrainingCourseResource` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `training_center_id` | `required`, `integer`, `exists:training_centers,id` |
| `trainer_id` | `required`, `integer`, `exists:trainers,id` |
| `training_kit_id` | `required`, `integer`, `exists:training_kits,id` |
| `training_program_id` | `nullable`, `integer`, `exists:training_programs,id` |
| `title` | `required`, `string`, `max:255` |
| `delivery_mode` | `required`, `in:online,offline` |
| `approved_platform` | `nullable`, `string`, `max:150` |
| `start_date` | `nullable`, `date` |
| `end_date` | `nullable`, `date`, `after_or_equal:start_date` |
| `planned_hours` | `required`, `integer`, `min:1` |
| `actual_hours` | `nullable`, `integer`, `min:0` |
| `capacity` | `required`, `integer`, `min:1` |
| `status` | `nullable`, `in:draft,scheduled,ongoing` |
| `notes` | `nullable`, `string` |
| `venue_name` | `nullable`, `string`, `max:255` |
| `governorate` | `nullable`, `string`, `max:100` |
| `city` | `nullable`, `string`, `max:100` |
| `district` | `nullable`, `string`, `max:100` |
| `address` | `nullable`, `string`, `max:1000` |
| `latitude` | `nullable`, `numeric`, `between:-90,90` |
| `longitude` | `nullable`, `numeric`, `between:-180,180` |
| `location_visibility` | `nullable`, `in:public,internal,private` |
| `online_platform` | `nullable`, `string`, `max:150` |
| `online_url` | `nullable`, `url`, `max:500` |

```json
{
    "training_center_id": "...",
    "trainer_id": "...",
    "training_kit_id": "...",
    "training_program_id": "...",
    "title": "...",
    "delivery_mode": "...",
    "approved_platform": "...",
    "start_date": "...",
    "end_date": "...",
    "planned_hours": "...",
    "actual_hours": "...",
    "capacity": "...",
    "status": "...",
    "notes": "...",
    "venue_name": "...",
    "governorate": "...",
    "city": "...",
    "district": "...",
    "address": "...",
    "latitude": "...",
    "longitude": "...",
    "location_visibility": "...",
    "online_platform": "...",
    "online_url": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/training-courses/{id}/trainees`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::trainees` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `trainees` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:view_courses|view_course_details` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses/{id}/trainees` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses/{id}/trainees` |
| Permission | `view_courses|view_course_details` |
| Policy / authorize() | `view @ $course` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/training-courses/{id}/trainees`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::addTrainee` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `addTrainee` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:manage_courses` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses/{id}/trainees` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses/{id}/trainees` |
| Permission | `manage_courses` |
| Form Request | `App\Http\Requests\Training\AddCourseTraineeRequest` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `trainee_id` | `required`, `integer`, `exists:trainees,id` |
| `notes` | `nullable`, `string` |

```json
{
    "trainee_id": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PATCH `api/training-courses/{id}/trainees/{traineeId}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::updateTrainee` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `updateTrainee` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:manage_courses` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses/{id}/trainees/{traineeId}` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses/{id}/trainees/{traineeId}` |
| Permission | `manage_courses` |
| Form Request | `App\Http\Requests\Training\UpdateCourseTraineeResultRequest` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `traineeId` | نعم | معامل مسار |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `attendance_status` | `sometimes`, `in:registered,attended,absent,withdrawn,completed` |
| `result` | `sometimes`, `in:pending,passed,failed,attendance_only` |
| `score` | `nullable`, `numeric`, `min:0`, `max:100` |
| `attended_hours` | `sometimes`, `integer`, `min:0` |
| `notes` | `nullable`, `string` |

```json
{
    "attendance_status": "...",
    "result": "...",
    "score": "...",
    "attended_hours": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/training-courses/{id}/trainees/{traineeId}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::removeTrainee` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `removeTrainee` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:manage_courses` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses/{id}/trainees/{traineeId}` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses/{id}/trainees/{traineeId}` |
| Permission | `manage_courses` |
| Policy / authorize() | `deleteTrainee @ $course` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |
| `traineeId` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/training-courses/{id}/complete`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::complete` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `complete` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:manage_courses` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses/{id}/complete` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses/{id}/complete` |
| Permission | `manage_courses` |
| Form Request | `App\Http\Requests\Training\CompleteTrainingCourseRequest` |
| API Resource | `TrainingCourseResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/training-courses/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::show` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `show` |
| Route Name | `api.courses.show` |
| Middleware | `api, auth:sanctum, role_or_permission:trainer_user|trainee_user|view_courses|view_course_details` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses/{id}` |
| Permission | `trainer_user|trainee_user|view_courses|view_course_details` |
| Policy / authorize() | `view @ $course` |
| API Resource | `TrainingCourseResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PATCH `api/training-courses/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCourseController::update` |
| Controller | `App\Http\Controllers\Api\TrainingCourseController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:manage_courses` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-courses/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/training-courses/{id}` |
| Permission | `manage_courses` |
| Form Request | `App\Http\Requests\Training\UpdateTrainingCourseRequest` |
| API Resource | `TrainingCourseResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `training_center_id` | `sometimes`, `integer`, `exists:training_centers,id` |
| `trainer_id` | `sometimes`, `integer`, `exists:trainers,id` |
| `training_kit_id` | `sometimes`, `integer`, `exists:training_kits,id` |
| `training_program_id` | `nullable`, `integer`, `exists:training_programs,id` |
| `title` | `sometimes`, `string`, `max:255` |
| `delivery_mode` | `sometimes`, `in:online,offline` |
| `approved_platform` | `nullable`, `string`, `max:150` |
| `start_date` | `nullable`, `date` |
| `end_date` | `nullable`, `date` |
| `planned_hours` | `sometimes`, `integer`, `min:1` |
| `actual_hours` | `nullable`, `integer`, `min:0` |
| `capacity` | `sometimes`, `integer`, `min:1` |
| `status` | `sometimes`, `in:draft,scheduled,ongoing` |
| `notes` | `nullable`, `string` |
| `venue_name` | `sometimes`, `string`, `max:255` |
| `governorate` | `sometimes`, `string`, `max:100` |
| `city` | `sometimes`, `string`, `max:100` |
| `district` | `sometimes`, `string`, `max:100` |
| `address` | `sometimes`, `string`, `max:1000` |
| `latitude` | `sometimes`, `numeric`, `between:-90,90` |
| `longitude` | `sometimes`, `numeric`, `between:-180,180` |
| `location_visibility` | `sometimes`, `in:public,internal,private` |
| `online_platform` | `sometimes`, `string`, `max:150` |
| `online_url` | `sometimes`, `url`, `max:500` |

```json
{
    "training_center_id": "...",
    "trainer_id": "...",
    "training_kit_id": "...",
    "training_program_id": "...",
    "title": "...",
    "delivery_mode": "...",
    "approved_platform": "...",
    "start_date": "...",
    "end_date": "...",
    "planned_hours": "...",
    "actual_hours": "...",
    "capacity": "...",
    "status": "...",
    "notes": "...",
    "venue_name": "...",
    "governorate": "...",
    "city": "...",
    "district": "...",
    "address": "...",
    "latitude": "...",
    "longitude": "...",
    "location_visibility": "...",
    "online_platform": "...",
    "online_url": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Training-Kit-Nominations"></a>
## وحدة: Training Kit Nominations


### GET `api/training-kit-nominations`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingKitNominationController::index` |
| Controller | `App\Http\Controllers\Api\TrainingKitNominationController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:nominate_training_kits|review_training_kit_nominations` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-kit-nominations` |
| Production URL | `https://smeda.gov.sy/api/api/training-kit-nominations` |
| Permission | `nominate_training_kits|review_training_kit_nominations` |
| API Resource | `TrainingKitNominationResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `trainer_id` | mixed | controller |
| `status` | mixed | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/training-kit-nominations`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingKitNominationController::store` |
| Controller | `App\Http\Controllers\Api\TrainingKitNominationController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:nominate_training_kits` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-kit-nominations` |
| Production URL | `https://smeda.gov.sy/api/api/training-kit-nominations` |
| Permission | `nominate_training_kits` |
| API Resource | `TrainingKitNominationResource` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `training_kit_id` | `nullable`, `integer`, `exists:training_kits,id` |
| `proposed_name` | `nullable`, `string`, `max:255` |
| `description` | `required`, `string` |
| `sector` | `required`, `string`, `max:255` |
| `category` | `required`, `string`, `max:255` |
| `hours` | `required`, `integer`, `min:1`, `max:500` |

```json
{
    "training_kit_id": "...",
    "proposed_name": "...",
    "description": "...",
    "sector": "...",
    "category": "...",
    "hours": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/training-kit-nominations/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingKitNominationController::show` |
| Controller | `App\Http\Controllers\Api\TrainingKitNominationController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:nominate_training_kits|review_training_kit_nominations` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-kit-nominations/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/training-kit-nominations/{id}` |
| Permission | `nominate_training_kits|review_training_kit_nominations` |
| API Resource | `TrainingKitNominationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/training-kit-nominations/{id}/review`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingKitNominationController::review` |
| Controller | `App\Http\Controllers\Api\TrainingKitNominationController` |
| Method | `review` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:review_training_kit_nominations` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-kit-nominations/{id}/review` |
| Production URL | `https://smeda.gov.sy/api/api/training-kit-nominations/{id}/review` |
| Permission | `review_training_kit_nominations` |
| API Resource | `TrainingKitNominationResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `required`, `in:under_review,approved,rejected` |
| `decision_notes` | `nullable`, `string` |

```json
{
    "status": "...",
    "decision_notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Training-Kits"></a>
## وحدة: Training Kits


### GET `api/training-kits`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingKitController::index` |
| Controller | `App\Http\Controllers\Api\TrainingKitController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_kits` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-kits` |
| Production URL | `https://smeda.gov.sy/api/api/training-kits` |
| Permission | `view_kits` |
| API Resource | `TrainingKitResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | mixed | controller |
| `sector` | mixed | controller |
| `category` | mixed | controller |
| `type` | mixed | controller |
| `level` | mixed | controller |
| `is_active` | mixed | controller |
| `with_trainers` | boolean | controller |
| `with_programs` | boolean | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/training-kits/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingKitController::show` |
| Controller | `App\Http\Controllers\Api\TrainingKitController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_kits` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-kits/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/training-kits/{id}` |
| Permission | `view_kits` |
| API Resource | `TrainingKitResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Training-Programs"></a>
## وحدة: Training Programs


### GET `api/training-programs`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingProgramController::index` |
| Controller | `App\Http\Controllers\Api\TrainingProgramController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-programs` |
| Production URL | `https://smeda.gov.sy/api/api/training-programs` |
| Permission | `view_programs` |
| API Resource | `TrainingProgramResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | mixed | controller |
| `is_active` | mixed | controller |
| `with_kits` | boolean | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/training-programs/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingProgramController::show` |
| Controller | `App\Http\Controllers\Api\TrainingProgramController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_programs` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-programs/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/training-programs/{id}` |
| Permission | `view_programs` |
| API Resource | `TrainingProgramResource` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Training-Requests"></a>
## وحدة: Training Requests


### POST `api/training-kit-public-requests`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingKitPublicRequestController::store` |
| Controller | `App\Http\Controllers\Api\TrainingKitPublicRequestController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, throttle:training-kit-public` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-kit-public-requests` |
| Production URL | `https://smeda.gov.sy/api/api/training-kit-public-requests` |
| Rate Limit | 5 طلبات/دقيقة لكل IP |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `applicant_name` | `required`, `string`, `max:255` |
| `applicant_email` | `required`, `email`, `max:255` |
| `proposed_name` | `required`, `string`, `max:255` |
| `city` | `nullable`, `string`, `max:255` |
| `notes` | `nullable`, `string`, `max:5000` |

```json
{
    "applicant_name": "...",
    "applicant_email": "...",
    "proposed_name": "...",
    "city": "...",
    "notes": "..."
}
```


**Status Codes المحتملة:** 200, 201, 404, 422, 429, 500


---

<a id="module-Training-Supervisors"></a>
## وحدة: Training Supervisors


### GET `api/training-supervisors`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingSupervisorController::index` |
| Controller | `App\Http\Controllers\Api\TrainingSupervisorController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:view_centers` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/training-supervisors` |
| Production URL | `https://smeda.gov.sy/api/api/training-supervisors` |
| Permission | `view_centers` |
| Policy / authorize() | `viewAny @ \App\Models\TrainingCenter::class` |
| API Resource | `TrainingSupervisorResource::collection` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `type` | string | controller |
| `is_active` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-User-Profile"></a>
## وحدة: User Profile


### GET `api/me`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AuthController::me` |
| Controller | `App\Http\Controllers\Api\AuthController` |
| Method | `me` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/me` |
| Production URL | `https://smeda.gov.sy/api/api/me` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/me`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AuthController::updateMe` |
| Controller | `App\Http\Controllers\Api\AuthController` |
| Method | `updateMe` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/me` |
| Production URL | `https://smeda.gov.sy/api/api/me` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `name` | `sometimes`, `string`, `max:255` |
| `phone` | `sometimes`, `nullable`, `string`, `max:30` |

```json
{
    "name": "...",
    "phone": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/me/change-password`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `AuthController::changeMyPassword` |
| Controller | `App\Http\Controllers\Api\AuthController` |
| Method | `changeMyPassword` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/me/change-password` |
| Production URL | `https://smeda.gov.sy/api/api/me/change-password` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `password` | string | controller |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `current_password` | `required`, `string`, `current_password` |
| `password` | `required`, `confirmed` |

```json
{
    "current_password": "...",
    "password": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/my-electronic-signature`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserElectronicSignatureController::show` |
| Controller | `App\Http\Controllers\Api\UserElectronicSignatureController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/my-electronic-signature` |
| Production URL | `https://smeda.gov.sy/api/api/my-electronic-signature` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/my-electronic-signature`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserElectronicSignatureController::store` |
| Controller | `App\Http\Controllers\Api\UserElectronicSignatureController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, throttle:file-upload` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/my-electronic-signature` |
| Production URL | `https://smeda.gov.sy/api/api/my-electronic-signature` |
| Form Request | `App\Http\Requests\Signing\StoreUserElectronicSignatureRequest` |
| Rate Limit | 5 طلبات/دقيقة لكل (user|IP) |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `signature` | `required`, `file`, `max:2048`, `mimes:png,jpg,jpeg,webp` |

```json
{
    "signature": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 429, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### DELETE `api/my-electronic-signature`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserElectronicSignatureController::destroy` |
| Controller | `App\Http\Controllers\Api\UserElectronicSignatureController` |
| Method | `destroy` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/my-electronic-signature` |
| Production URL | `https://smeda.gov.sy/api/api/my-electronic-signature` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 204, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/my-electronic-signature/image`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `UserElectronicSignatureController::myImage` |
| Controller | `App\Http\Controllers\Api\UserElectronicSignatureController` |
| Method | `myImage` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/my-electronic-signature/image` |
| Production URL | `https://smeda.gov.sy/api/api/my-electronic-signature/image` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/my-trainer-profile`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerProfileController::myProfile` |
| Controller | `App\Http\Controllers\Api\TrainerProfileController` |
| Method | `myProfile` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, role_or_permission:view_trainer_profiles|edit_own_trainer_profile` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/my-trainer-profile` |
| Production URL | `https://smeda.gov.sy/api/api/my-trainer-profile` |
| Permission | `view_trainer_profiles|edit_own_trainer_profile` |
| Policy / authorize() | `view @ $profile` |
| API Resource | `TrainerProfileResource` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/my-trainer-profile`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerProfileController::updateMyProfile` |
| Controller | `App\Http\Controllers\Api\TrainerProfileController` |
| Method | `updateMyProfile` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:edit_own_trainer_profile` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/my-trainer-profile` |
| Production URL | `https://smeda.gov.sy/api/api/my-trainer-profile` |
| Permission | `edit_own_trainer_profile` |
| Policy / authorize() | `updateOwn @ TrainerProfile::class` |
| API Resource | `TrainerProfileResource` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `headline` | `nullable`, `string`, `max:255` |
| `bio` | `nullable`, `string` |
| `experience_years` | `nullable`, `integer`, `min:0`, `max:60` |
| `skills` | `nullable`, `string` |
| `special_interests` | `nullable`, `string` |
| `linkedin_summary` | `nullable`, `string` |
| `visibility` | `nullable`, `in:internal,public` |

```json
{
    "headline": "...",
    "bio": "...",
    "experience_years": "...",
    "skills": "...",
    "special_interests": "...",
    "linkedin_summary": "...",
    "visibility": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="module-Web-(Print/Verify/Files)"></a>
## وحدة: Web (Print/Verify/Files)


### GET `sanctum/csrf-cookie`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CsrfCookieController::show` |
| Controller | `Laravel\Sanctum\Http\Controllers\CsrfCookieController` |
| Method | `show` |
| Route Name | `sanctum.csrf-cookie` |
| Middleware | `web` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/sanctum/csrf-cookie` |
| Production URL | `https://smeda.gov.sy/api/sanctum/csrf-cookie` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 500


### GET `storage/{path}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `Closure::-` |
| Controller | `غير محدد` |
| Method | `غير محدد` |
| Route Name | `storage.local` |
| Middleware | `` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/storage/{path}` |
| Production URL | `https://smeda.gov.sy/api/storage/{path}` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `path` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 500


### PUT `storage/{path}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `Closure::-` |
| Controller | `غير محدد` |
| Method | `غير محدد` |
| Route Name | `storage.local.upload` |
| Middleware | `` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/storage/{path}` |
| Production URL | `https://smeda.gov.sy/api/storage/{path}` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `path` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 500


---

<a id="module-Workforce"></a>
## وحدة: Workforce


### GET `api/workforce/job-postings`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `JobPostingController::index` |
| Controller | `App\Http\Controllers\Api\JobPostingController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.jobs.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/job-postings` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/job-postings` |
| Permission | `workforce.jobs.view` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |
| `sector` | string | controller |
| `city` | string | controller |
| `search` | mixed | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/workforce/job-postings/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `JobPostingController::show` |
| Controller | `App\Http\Controllers\Api\JobPostingController` |
| Method | `show` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.jobs.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/job-postings/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/job-postings/{id}` |
| Permission | `workforce.jobs.view` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/workforce/job-postings`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `JobPostingController::store` |
| Controller | `App\Http\Controllers\Api\JobPostingController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.jobs.create` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/job-postings` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/job-postings` |
| Permission | `workforce.jobs.create` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `organization_name` | `required`, `string`, `max:255` |
| `title` | `required`, `string`, `max:255` |
| `city` | `nullable`, `string`, `max:255` |
| `governorate_id` | `nullable`, `integer`, `exists:governorates,id` |
| `employment_type` | `required`, `in:full_time,part_time,contract,freelance` |
| `sector` | `nullable`, `string`, `max:255` |
| `description` | `nullable`, `string` |
| `skills` | `nullable`, `string` |
| `contact_email` | `nullable`, `email`, `max:255` |
| `contact_phone` | `nullable`, `string`, `max:30` |
| `status` | `nullable`, `in:draft,published,closed` |

```json
{
    "organization_name": "...",
    "title": "...",
    "city": "...",
    "governorate_id": "...",
    "employment_type": "...",
    "sector": "...",
    "description": "...",
    "skills": "...",
    "contact_email": "...",
    "contact_phone": "...",
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/workforce/job-postings/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `JobPostingController::update` |
| Controller | `App\Http\Controllers\Api\JobPostingController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.jobs.manage` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/job-postings/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/job-postings/{id}` |
| Permission | `workforce.jobs.manage` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `sometimes`, `in:draft,published,closed` |
| `title` | `sometimes`, `string`, `max:255` |
| `description` | `sometimes`, `nullable`, `string` |

```json
{
    "status": "...",
    "title": "...",
    "description": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/workforce/job-applications`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `JobApplicationController::index` |
| Controller | `App\Http\Controllers\Api\JobApplicationController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.applications.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/job-applications` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/job-applications` |
| Permission | `workforce.applications.view` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `job_posting_id` | filled filter | controller |
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/workforce/job-applications/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `JobApplicationController::update` |
| Controller | `App\Http\Controllers\Api\JobApplicationController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.applications.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/job-applications/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/job-applications/{id}` |
| Permission | `workforce.applications.view` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `required`, `in:pending,reviewed,accepted,rejected` |

```json
{
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/workforce/job-applications`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `JobApplicationController::store` |
| Controller | `App\Http\Controllers\Api\JobApplicationController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.applications.create` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/job-applications` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/job-applications` |
| Permission | `workforce.applications.create` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `job_posting_id` | `nullable`, `integer`, `exists:job_postings,id` |
| `applicant_name` | `required`, `string`, `max:255` |
| `phone` | `nullable`, `string`, `max:30` |
| `email` | `nullable`, `email`, `max:255` |
| `specialty` | `nullable`, `string`, `max:255` |
| `city` | `nullable`, `string`, `max:255` |
| `experience_years` | `nullable`, `string`, `max:50` |
| `summary` | `nullable`, `string` |
| `cv` | `nullable`, `file`, `max:5120` |

```json
{
    "job_posting_id": "...",
    "applicant_name": "...",
    "phone": "...",
    "email": "...",
    "specialty": "...",
    "city": "...",
    "experience_years": "...",
    "summary": "...",
    "cv": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### GET `api/workforce/staff-training-requests`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `StaffTrainingRequestController::index` |
| Controller | `App\Http\Controllers\Api\StaffTrainingRequestController` |
| Method | `index` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.training_requests.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/staff-training-requests` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/staff-training-requests` |
| Permission | `workforce.training_requests.view` |

**Query Parameters:**

| Parameter | Type | ملاحظات |
|-----------|------|---------|
| `per_page` | integer | pagination default=20 max=100 |
| `status` | string | controller |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 100, 200, 401, 403, 404, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### PUT `api/workforce/staff-training-requests/{id}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `StaffTrainingRequestController::update` |
| Controller | `App\Http\Controllers\Api\StaffTrainingRequestController` |
| Method | `update` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.training_requests.view` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/staff-training-requests/{id}` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/staff-training-requests/{id}` |
| Permission | `workforce.training_requests.view` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `status` | `required`, `in:pending,reviewed,scheduled,closed` |

```json
{
    "status": "..."
}
```


**Status Codes المحتملة:** 200, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


### POST `api/workforce/staff-training-requests`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `StaffTrainingRequestController::store` |
| Controller | `App\Http\Controllers\Api\StaffTrainingRequestController` |
| Method | `store` |
| Route Name | `—` |
| Middleware | `api, auth:sanctum, permission:workforce.training_requests.create` |
| المصادقة | **Bearer Token** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/api/workforce/staff-training-requests` |
| Production URL | `https://smeda.gov.sy/api/api/workforce/staff-training-requests` |
| Permission | `workforce.training_requests.create` |

**Request Body / Validation:**

| Field | Rules |
|-------|-------|
| `organization_name` | `required`, `string`, `max:255` |
| `employees_count` | `required`, `integer`, `min:1`, `max:10000` |
| `training_field` | `nullable`, `string`, `max:255` |
| `city` | `nullable`, `string`, `max:255` |
| `details` | `nullable`, `string` |

```json
{
    "organization_name": "...",
    "employees_count": "...",
    "training_field": "...",
    "city": "...",
    "details": "..."
}
```


**Status Codes المحتملة:** 200, 201, 401, 403, 404, 422, 500


**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.


---

<a id="web-routes"></a>
## Web — طباعة، تحقق، QR، Signed URLs

> هذه المسارات تُخدم عبر `BACKEND_BASE_URL` وليس `API_BASE_URL`.

### GET `sanctum/csrf-cookie`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CsrfCookieController::show` |
| Controller | `Laravel\Sanctum\Http\Controllers\CsrfCookieController` |
| Method | `show` |
| Route Name | `sanctum.csrf-cookie` |
| Middleware | `web` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/sanctum/csrf-cookie` |
| Production URL | `https://smeda.gov.sy/api/sanctum/csrf-cookie` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 500


### GET `up`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `Closure::-` |
| Controller | `غير محدد` |
| Method | `غير محدد` |
| Route Name | `—` |
| Middleware | `` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/up` |
| Production URL | `https://smeda.gov.sy/api/up` |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 500


### GET `certificates/{id}/print`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::show` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `show` |
| Route Name | `certificates.print` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{id}/print` |
| Production URL | `https://smeda.gov.sy/api/certificates/{id}/print` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/{id}/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::pdf` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `pdf` |
| Route Name | `certificates.pdf` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{id}/pdf` |
| Production URL | `https://smeda.gov.sy/api/certificates/{id}/pdf` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `trainers/{id}/card`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerPrintController::show` |
| Controller | `App\Http\Controllers\TrainerPrintController` |
| Method | `show` |
| Route Name | `trainers.card` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/trainers/{id}/card` |
| Production URL | `https://smeda.gov.sy/api/trainers/{id}/card` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `trainers/{id}/card/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainerPrintController::pdf` |
| Controller | `App\Http\Controllers\TrainerPrintController` |
| Method | `pdf` |
| Route Name | `trainers.card.pdf` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/trainers/{id}/card/pdf` |
| Production URL | `https://smeda.gov.sy/api/trainers/{id}/card/pdf` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `training-centers/{id}/certificate`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterPrintController::show` |
| Controller | `App\Http\Controllers\TrainingCenterPrintController` |
| Method | `show` |
| Route Name | `training-centers.certificate` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/training-centers/{id}/certificate` |
| Production URL | `https://smeda.gov.sy/api/training-centers/{id}/certificate` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `training-centers/{id}/certificate/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TrainingCenterPrintController::pdf` |
| Controller | `App\Http\Controllers\TrainingCenterPrintController` |
| Method | `pdf` |
| Route Name | `training-centers.certificate.pdf` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/training-centers/{id}/certificate/pdf` |
| Production URL | `https://smeda.gov.sy/api/training-centers/{id}/certificate/pdf` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `trainees/{id}/card`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineePrintController::show` |
| Controller | `App\Http\Controllers\TraineePrintController` |
| Method | `show` |
| Route Name | `trainees.card` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/trainees/{id}/card` |
| Production URL | `https://smeda.gov.sy/api/trainees/{id}/card` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `trainees/{id}/card/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `TraineePrintController::pdf` |
| Controller | `App\Http\Controllers\TraineePrintController` |
| Method | `pdf` |
| Route Name | `trainees.card.pdf` |
| Middleware | `web, signed, throttle:print-routes` |
| المصادقة | **Signed URL** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/trainees/{id}/card/pdf` |
| Production URL | `https://smeda.gov.sy/api/trainees/{id}/card/pdf` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `id` | نعم | معرف رقمي للسجل |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `verify-certificate/{certificate_code}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::publicView` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `publicView` |
| Route Name | `certificates.verify-code` |
| Middleware | `web, throttle:print-routes` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/verify-certificate/{certificate_code}` |
| Production URL | `https://smeda.gov.sy/api/verify-certificate/{certificate_code}` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/verify`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificateController::verifyPage` |
| Controller | `App\Http\Controllers\Api\CertificateController` |
| Method | `verifyPage` |
| Route Name | `certificates.verify` |
| Middleware | `web, throttle:print-routes` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/verify` |
| Production URL | `https://smeda.gov.sy/api/certificates/verify` |
| Rate Limit | 60 طلبات/دقيقة لكل IP |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/{certificate_code}/print`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::showByCode` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `showByCode` |
| Route Name | `certificates.print-by-code` |
| Middleware | `web, throttle:certificate-print-by-code` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{certificate_code}/print` |
| Production URL | `https://smeda.gov.sy/api/certificates/{certificate_code}/print` |
| Rate Limit | 20 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/{certificate_code}/pdf`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::pdfByCode` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `pdfByCode` |
| Route Name | `certificates.pdf-by-code` |
| Middleware | `web, throttle:certificate-print-by-code` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{certificate_code}/pdf` |
| Production URL | `https://smeda.gov.sy/api/certificates/{certificate_code}/pdf` |
| Rate Limit | 20 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `certificates/{certificate_code}/qr`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `CertificatePrintController::publicQrImage` |
| Controller | `App\Http\Controllers\CertificatePrintController` |
| Method | `publicQrImage` |
| Route Name | `certificates.qr-by-code` |
| Middleware | `web, throttle:certificate-print-by-code` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/certificates/{certificate_code}/qr` |
| Production URL | `https://smeda.gov.sy/api/certificates/{certificate_code}/qr` |
| Rate Limit | 20 طلبات/دقيقة لكل IP |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `certificate_code` | نعم | رمز الشهادة المركب |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 429, 500


### GET `storage/{path}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `Closure::-` |
| Controller | `غير محدد` |
| Method | `غير محدد` |
| Route Name | `storage.local` |
| Middleware | `` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/storage/{path}` |
| Production URL | `https://smeda.gov.sy/api/storage/{path}` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `path` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 500


### PUT `storage/{path}`

| البند | القيمة |
|------|--------|
| الوصف | Endpoint من `Closure::-` |
| Controller | `غير محدد` |
| Method | `غير محدد` |
| Route Name | `storage.local.upload` |
| Middleware | `` |
| المصادقة | **Public** |
| الحالة | فعال |
| Local URL | `http://127.0.0.1:8000/storage/{path}` |
| Production URL | `https://smeda.gov.sy/api/storage/{path}` |

**Path Parameters:**

| Parameter | Required | Description |
|-----------|:--------:|-------------|
| `path` | نعم | معامل مسار |

**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `$request->validate()` في method body المستخرج).


**Status Codes المحتملة:** 200, 404, 500


---

<a id="geographic-scope"></a>
## النطاق الجغرافي (Governorate / Branch)

| الدور | النطاق | المصدر |
|-------|--------|--------|
| general_director, admin, super_admin | وطني — جميع المحافظات والفروع | `NeedDataScope::hasNationalNeedsAccess()` |
| development_manager, project_services_manager | وطني عند امتلاك `needs.view_all` | `NeedDataScope::NATIONAL_VIEW_ROLES` |
| governor | محافظة المستخدم (`governorate_id`) | `NeedDataScope::scopeNeeds()` |
| branch_manager | فرع المستخدم (`branch_id`) | `NeedDataScope` + `BranchDataScope` |
| branch_officer, data_entry, data_reviewer | فرع المستخدم | `branch_id` على User |
| center_user | مركز تدريبي مرتبط (`training_center_id`) | `TrainingDataScope` |
| trainer_user | سجلات المدرب المرتبط (`trainer_id`) | Policies + scope |
| trainee_user | سجلات المتدرب (`trainee_id`) | Policies + scope |
| الزائر / Public | سجلات عامة معتمدة فقط | `public/*` endpoints |

> تفاصيل Policy لكل Endpoint في قسم المسار (`authorize()` / Permission).


---

<a id="gis-map"></a>
## GIS والخريطة

### Public — GET /api/public/needs/map

- **المصادقة:** Public (`throttle:map-public` — 60/دقيقة/IP)
- **Controller:** `PublicBrowseController::needsMap`
- **فلاتر:** تُستخرج من method body — راجع قسم Endpoint التفصيلي
- **الظهور:** احتياجات **معتمدة** للعامة؛ السجلات الداخلية عبر `/api/needs/map` للمصادقين

### Authenticated — GET /api/needs/map

- **المصادقة:** Bearer Token + `needs.map` permission
- **النطاق:** `NeedDataScope::scopeNeeds()` حسب الدور والفرع/المحافظة
- **فلاتر إضافية:** عبر `NeedDashboardService::applyFilters()` — governorate_id, branch_id, status, sector, need_type, priority, lat/lng bounds


---

<a id="files-print"></a>
## الملفات والطباعة والشهادات

### رفع الملفات (multipart/form-data)

| Endpoint | الحقل | التخزين | Rate Limit |
|----------|-------|---------|------------|
| POST /api/me/signature | signature | `UserElectronicSignatureController` | file-upload (5/min) |
| POST /api/funding-applications/{id}/documents | file | `SecureFileStorage` | file-upload |
| POST /api/training-center-registration-requests | license_image | `SecureFileStorage` | file-upload |
| POST /api/consulting/requests/{id}/attachments | file | public disk | file-upload |
| POST /api/consulting/contracts/{id}/report | file | `consulting/reports/{id}` | file-upload |

> قواعد الامتداد والحجم: راجع Validation في Controller/Form Request لكل Endpoint.

### Signed URLs (طباعة بالمعرف الرقمي)

| Route | الاسم | الصلاحية |
|-------|-------|----------|
| GET /certificates/{id}/print | certificates.print | Signed — **24 ساعة** (`SignedPrintUrl::EXPIRATION_HOURS`) |
| GET /certificates/{id}/pdf | certificates.pdf | Signed |
| GET /trainers/{id}/card | trainers.card | Signed |
| GET /trainees/{id}/card | trainees.card | Signed |
| GET /training-centers/{id}/certificate | training-centers.certificate | Signed |

**التوليد:** `App\Support\SignedPrintUrl` عبر `URL::temporarySignedRoute()`.

### Public بالـ certificate_code (بدون Signed URL)

| Route | الوصف |
|-------|--------|
| GET /verify-certificate/{certificate_code} | عرض عام + QR |
| GET /certificates/{certificate_code}/print | طباعة بالرمز |
| GET /certificates/{certificate_code}/pdf | PDF بالرمز |
| GET /certificates/{certificate_code}/qr | صورة QR |
| GET /api/verify-certificate/{certificate_code} | API تحقق JSON |


---

<a id="security-review"></a>
## مراجعة الحماية والصلاحيات

> مسارات auth:sanctum فقط **ليست ثغرة تلقائياً** إذا وُجد `authorize()` أو فحص ملكية داخل Controller.

| Route | Authentication | Permission/Role | Policy/authorize | Ownership/Scope | النتيجة | التوصية |
|-------|----------------|---------------|-----------------|-----------------|---------|---------|
| `GET api/incubators` | Public | — | — | — | ⚠️ Public | مراجعة: مسار حساس بدون Bearer Token |
| `GET api/entrepreneur/profiles/public-stats` | Public | — | — | — | ⚠️ Public | مراجعة: مسار حساس بدون Bearer Token |
| `GET api/public/finance/cloud` | Public | — | — | — | ⚠️ Public | مراجعة: مسار حساس بدون Bearer Token |
| `GET api/public/finance/metrics` | Public | — | — | — | ⚠️ Public | مراجعة: مسار حساس بدون Bearer Token |
| `GET api/me` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `PUT api/me` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `POST api/me/change-password` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/my-electronic-signature` | Bearer Token | — | — | abort_unless() ownership/role gate; canManageOwnSignature() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/my-electronic-signature` | Bearer Token | — | — | abort_unless() ownership/role gate; canManageOwnSignature() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `DELETE api/my-electronic-signature` | Bearer Token | — | — | abort_unless() ownership/role gate; canManageOwnSignature() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/my-electronic-signature/image` | Bearer Token | — | — | abort_unless() ownership/role gate; canManageOwnSignature() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/electronic-signatures/{id}/snapshot-image` | Bearer Token | — | — | abort_unless() ownership/role gate; authorizeSnapshotView() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/logout` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/dashboard` | Bearer Token | — | — | DashboardAccess::assert | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/governorates` | Bearer Token | — | — | abort_unless() ownership/role gate; BranchDataScope filter | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/branches/dashboard` | Bearer Token | — | viewAny @ Branch::class | Policy authorize(); BranchDataScope filter | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/branches` | Bearer Token | — | viewAny @ Branch::class | Policy authorize(); BranchDataScope filter | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/branches` | Bearer Token | — | create @ Branch::class | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/branches/{id}` | Bearer Token | — | view @ $branch | Policy authorize(); BranchDataScope filter | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `PUT api/branches/{id}` | Bearer Token | — | update @ $branch | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `DELETE api/branches/{id}` | Bearer Token | — | delete @ $branch | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/agreements` | Bearer Token | — | viewAny @ Agreement::class | Policy authorize(); BranchDataScope filter | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/agreements` | Bearer Token | — | create @ Agreement::class | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/agreements/{id}` | Bearer Token | — | view @ $row | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `PUT api/agreements/{id}` | Bearer Token | — | update @ $row | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/agreements/{id}/approve` | Bearer Token | — | approve @ $row | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/finance/records` | Bearer Token | general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance | viewAny @ FinancialRecord::class | Policy authorize(); BranchDataScope filter | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/records/{id}` | Bearer Token | general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance | view @ $row | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/records` | Bearer Token | general_director|finance_manager|admin|super_admin|system_admin|manage_finance | create @ FinancialRecord::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/finance/records/{id}` | Bearer Token | general_director|finance_manager|admin|super_admin|system_admin|manage_finance | update @ $row | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/records/{id}/approve` | Bearer Token | general_director|finance_manager|admin|super_admin|system_admin|manage_finance | approve @ $row | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/applications` | Bearer Token | project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view | viewAny @ FundingApplication::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/applications/{id}` | Bearer Token | project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view | view @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/applications/{applicationId}/documents/{documentId}/download` | Bearer Token | project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view | view @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications` | Bearer Token | project_owner|finance.applications.create|general_director|admin|super_admin|system_admin | create @ FundingApplication::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/finance/applications/{id}` | Bearer Token | project_owner|finance.applications.update|branch_manager|general_director|admin|super_admin|system_admin | update @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{id}/submit` | Bearer Token | project_owner|finance.applications.submit|general_director|admin|super_admin|system_admin | submit @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{id}/request-completion` | Bearer Token | branch_manager|finance.applications.request_completion|finance_manager|general_director|admin|super_admin|system_admin | reviewBranch @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{id}/branch-review` | Bearer Token | branch_manager|finance.applications.review_branch|general_director|admin|super_admin|system_admin | reviewBranch @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{id}/approve` | Bearer Token | finance_manager|finance.applications.approve|general_director|admin|super_admin|system_admin | approve @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{id}/reject` | Bearer Token | branch_manager|finance_manager|finance.applications.reject|general_director|admin|super_admin|system_admin | reject @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{id}/assign-consultant` | Bearer Token | branch_manager|finance_manager|finance.applications.assign_consultant|general_director|admin|super_admin|system_admin | assignConsultant @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{id}/assign-partner` | Bearer Token | finance_manager|central_bank_admin|finance.applications.assign_partner|general_director|admin|super_admin|system_admin | assignPartner @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{id}/create-loan` | Bearer Token | finance_manager|finance.loans.manage|general_director|admin|super_admin|system_admin | view @ $application; create @ FundedLoan::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/applications/{applicationId}/documents` | Bearer Token | project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view | update @ $application | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/consultant-union/dashboard` | Bearer Token | consultant_union_admin|finance.consultant_union.dashboard | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/consultant-assignments` | Bearer Token | consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/consultant-offices` | Bearer Token | consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | viewAny @ ConsultantOffice::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/consultant-offices/{id}` | Bearer Token | consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | view @ $office | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/consultant-offices/{id}/assignments` | Bearer Token | consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | view @ $office | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/consultant-offices/{id}/reports` | Bearer Token | consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | view @ $office | Policy authorize(); hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/consultant-offices/{id}/metrics` | Bearer Token | consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | monitor @ ConsultantOffice::class; view @ $office | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-offices` | Bearer Token | consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.create|finance.consultants.manage | create @ ConsultantOffice::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/finance/consultant-offices/{id}` | Bearer Token | consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.update|finance.consultants.manage | update @ $office | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-offices/{id}/approve` | Bearer Token | consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.approve|finance.consultants.manage | approve @ $office | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-offices/{id}/activate` | Bearer Token | consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.activate|finance.consultants.manage | activate @ $office | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-offices/{id}/suspend` | Bearer Token | consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.suspend|finance.consultants.manage | suspend @ $office | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/consultant-office/dashboard` | Bearer Token | role:consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/my-consultant-assignments` | Bearer Token | role:consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-assignments/{id}/accept` | Bearer Token | consultant_office|finance.consultant_assignments.accept | accept @ $assignment | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-assignments/{id}/reject` | Bearer Token | consultant_office|finance.consultant_assignments.reject | accept @ $assignment | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-assignments/{id}/price-offer` | Bearer Token | consultant_office|finance.consultant_assignments.submit_price|finance.consultants.submit_price | submitPrice @ $assignment | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-assignments/{id}/approve-price` | Bearer Token | branch_manager|finance_manager|general_director|admin|super_admin|system_admin|finance.consultants.approve_price | approvePrice @ $assignment | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/consultant-reports` | Bearer Token | consultant_office|general_director|admin|super_admin|system_admin|finance.consultant_reports.create|finance.consultants.submit_report | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/central-bank/dashboard` | Bearer Token | central_bank_admin|finance.central_bank.dashboard | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/funding-partner/dashboard` | Bearer Token | role:funding_partner | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/partners` | Bearer Token | central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | viewAny @ FundingPartner::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/partners/{id}` | Bearer Token | central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | view @ $partner | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/partners/{id}/assignments` | Bearer Token | central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | view @ $partner | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/partners/{id}/decisions` | Bearer Token | central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | view @ $partner | Policy authorize(); hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/partners/{id}/loans` | Bearer Token | central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | view @ $partner | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/partners/{id}/metrics` | Bearer Token | central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | monitor @ FundingPartner::class; view @ $partner | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/partners` | Bearer Token | central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.create|finance.partners.manage | create @ FundingPartner::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/finance/partners/{id}` | Bearer Token | central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.update|finance.partners.manage | update @ $partner | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/partners/{id}/approve` | Bearer Token | central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.approve|finance.partners.manage | approve @ $partner | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/partners/{id}/activate` | Bearer Token | central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.activate|finance.partners.manage | activate @ $partner | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/partners/{id}/suspend` | Bearer Token | central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.suspend|finance.partners.manage | suspend @ $partner | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/my-partner-assignments` | Bearer Token | role:funding_partner | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/partner-assignments/{id}/decision` | Bearer Token | funding_partner|central_bank_admin|finance_manager|general_director|admin|super_admin|system_admin|finance.partner_assignments.decide|finance.partners.decide | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/loans/stats` | Bearer Token | funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own | viewAny @ FundedLoan::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/loans` | Bearer Token | funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own | viewAny @ FundedLoan::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/loans/{id}` | Bearer Token | funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own | view @ $loan | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/loans/{id}/payments` | Bearer Token | funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own | view @ $loan | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/finance/loans/{id}` | Bearer Token | finance_manager|funding_partner|general_director|admin|super_admin|system_admin|finance.loans.manage|finance.loans.update_own_status | update @ $loan | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/loans/{id}/payments` | Bearer Token | finance_manager|general_director|admin|super_admin|system_admin|finance.loans.payments|finance.loans.manage | recordPayment @ $loan | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/loans/{id}/mark-defaulted` | Bearer Token | finance_manager|general_director|admin|super_admin|system_admin|finance.loans.defaulted|finance.loans.manage | markDefaulted @ $loan | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/finance/loans/{id}/close` | Bearer Token | finance_manager|general_director|admin|super_admin|system_admin|finance.loans.close|finance.loans.manage | update @ $loan | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/metrics` | Bearer Token | finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/funded/stats` | Bearer Token | finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/funded` | Bearer Token | finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/defaulted/stats` | Bearer Token | finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/defaulted` | Bearer Token | finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/cloud` | Bearer Token | finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/finance/manager/dashboard` | Bearer Token | finance_manager|finance_officer|general_director|admin|super_admin|system_admin|finance.metrics.view | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs` | Bearer Token | needs.view|needs.view_all|needs.view_branch | viewAny @ Need::class | Policy authorize(); NeedDataScope geographic filter | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs/{id}` | Bearer Token | needs.view|needs.view_all|needs.view_branch | view @ $need | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs/map` | Bearer Token | needs.view|needs.view_all|needs.view_branch | map @ Need::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs/lookups` | Bearer Token | needs.view|needs.view_all|needs.view_branch | viewAny @ Need::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs/admin-units` | Bearer Token | needs.view|needs.view_all|needs.view_branch | viewAny @ Need::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs/export` | Bearer Token | needs.view|needs.view_all|needs.view_branch | export @ Need::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/needs` | Bearer Token | needs.create|needs.create_citizen|needs.create_state | create @ Need::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs/dashboard` | Bearer Token | needs.dashboard | dashboard @ Need::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs/analytics` | Bearer Token | — | — | hasPermissionTo() inline | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/needs/workspace/data-entry` | Bearer Token | role:data_entry | create @ Need::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/needs/workspace/reviewer` | Bearer Token | role:data_reviewer | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/needs/{id}` | Bearer Token | — | update @ $need | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/needs/{id}/review` | Bearer Token | — | review @ $need | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/needs/{id}/approve` | Bearer Token | — | approve @ $need | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/needs/{id}/reject` | Bearer Token | — | reject @ $need | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/needs/{id}/return` | Bearer Token | — | returnForEdit @ $need | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/needs/{id}/classify` | Bearer Token | — | classify @ $need | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/needs/{id}/resolve` | Bearer Token | — | resolve @ $need | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/trainers` | Bearer Token | view_trainers | viewAny @ Trainer::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/trainers/{id}` | Bearer Token | view_trainers | view @ $trainer | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/trainer-profiles/{id}` | Bearer Token | view_trainer_profiles | view @ $profile | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/my-trainer-profile` | Bearer Token | view_trainer_profiles|edit_own_trainer_profile | view @ $profile | Policy authorize(); hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/my-trainer-profile` | Bearer Token | edit_own_trainer_profile | updateOwn @ TrainerProfile::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-kit-nominations` | Bearer Token | nominate_training_kits|review_training_kit_nominations | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/training-kit-nominations` | Bearer Token | nominate_training_kits | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-kit-nominations/{id}` | Bearer Token | nominate_training_kits|review_training_kit_nominations | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/training-kit-nominations/{id}/review` | Bearer Token | review_training_kit_nominations | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/trainees` | Bearer Token | view_trainees | viewAny @ Trainee::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/trainees/{id}` | Bearer Token | view_trainees | view @ $trainee | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/workforces` | Bearer Token | general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/workforces/{id}` | Bearer Token | general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/workforces/enroll` | Bearer Token | general_director|admin|super_admin|system_admin|training_manager | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/workforce/job-postings` | Bearer Token | workforce.jobs.view | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/workforce/job-postings/{id}` | Bearer Token | workforce.jobs.view | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/workforce/job-postings` | Bearer Token | workforce.jobs.create | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/workforce/job-postings/{id}` | Bearer Token | workforce.jobs.manage | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/workforce/job-applications` | Bearer Token | workforce.applications.view | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/workforce/job-applications/{id}` | Bearer Token | workforce.applications.view | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/workforce/job-applications` | Bearer Token | workforce.applications.create | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/workforce/staff-training-requests` | Bearer Token | workforce.training_requests.view | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/workforce/staff-training-requests/{id}` | Bearer Token | workforce.training_requests.view | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/workforce/staff-training-requests` | Bearer Token | workforce.training_requests.create | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-centers` | Bearer Token | view_centers | viewAny @ TrainingCenter::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-centers/{id}` | Bearer Token | view_centers | view @ $center | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-supervisors` | Bearer Token | view_centers | viewAny @ \App\Models\TrainingCenter::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-kits` | Bearer Token | view_kits | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-kits/{id}` | Bearer Token | view_kits | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-programs` | Bearer Token | view_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-programs/{id}` | Bearer Token | view_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/program-bank/stats` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/program-bank/reports` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.reports|program_bank.view|view_reports | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/program-bank` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/program-bank` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/program-bank/{id}` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/program-bank/{id}` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/program-bank/{id}` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.delete|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/program-bank/{id}/duplicate` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/program-bank/{id}/transition` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|deputy_director|program_bank.approve|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/program-bank/{id}/create-course` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/program-bank/{id}/modules` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/program-bank/{id}/modules/{moduleId}` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/program-bank/{id}/modules/{moduleId}` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/program-bank/{id}/modules/reorder` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/program-bank/{id}/outcomes` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/program-bank/{id}/outcomes/{outcomeId}` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/program-bank/{id}/outcomes/{outcomeId}` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/program-bank/{id}/service-links` | Bearer Token | training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-courses` | Bearer Token | trainer_user|trainee_user|view_courses | viewAny @ TrainingCourse::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/training-courses` | Bearer Token | manage_courses | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-courses/{id}/trainees` | Bearer Token | view_courses|view_course_details | view @ $course | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/training-courses/{id}/trainees` | Bearer Token | manage_courses | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PATCH api/training-courses/{id}/trainees/{traineeId}` | Bearer Token | manage_courses | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/training-courses/{id}/trainees/{traineeId}` | Bearer Token | manage_courses | deleteTrainee @ $course | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/training-courses/{id}/complete` | Bearer Token | manage_courses | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/training-courses/{id}` | Bearer Token | trainer_user|trainee_user|view_courses|view_course_details | view @ $course | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PATCH api/training-courses/{id}` | Bearer Token | manage_courses | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/map/training-courses` | Bearer Token | view_courses | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/map/trainers` | Bearer Token | view_trainers | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/certificates/issue` | Bearer Token | issue_certificates | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/certificates/{id}/approve` | Bearer Token | approve_center_certificates|approve_training_certificates|approve_deputy_certificates|approve_general_director_certificates | approve @ [$certificate, $approvalStep] | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/certificates` | Bearer Token | view_certificates | viewAny @ Certificate::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/certificates/code/{certificate_code}` | Bearer Token | view_certificates | view @ $certificate | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/certificates/{id}` | Bearer Token | view_certificates | view @ $certificate | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/registration-requests/centers` | Bearer Token | view_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/registration-requests/centers` | Bearer Token | create_center_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/registration-requests/centers/{id}` | Bearer Token | — | view @ $row | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/registration-requests/centers/{id}/review` | Bearer Token | review_center_registration_requests | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/registration-requests/trainers` | Bearer Token | view_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/registration-requests/trainers` | Bearer Token | create_trainer_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/registration-requests/trainers/{id}` | Bearer Token | — | view @ $row | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/registration-requests/trainers/{id}/review` | Bearer Token | review_trainer_registration_requests | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/registration-requests/trainees` | Bearer Token | view_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/registration-requests/trainees` | Bearer Token | create_trainee_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/registration-requests/trainees/{id}` | Bearer Token | — | view @ $row | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/registration-requests/trainees/{id}/review` | Bearer Token | review_trainee_registration_requests | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/registration-requests/courses` | Bearer Token | view_registration_requests|create_course_registration_requests|confirm_course_registration_requests|complete_course_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/registration-requests/courses` | Bearer Token | create_course_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/registration-requests/courses/{id}` | Bearer Token | — | view @ $row | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/registration-requests/courses/{id}/confirm-by-guardian` | Bearer Token | confirm_course_registration_requests | — | hasPermissionTo() inline | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/registration-requests/courses/{id}/complete` | Bearer Token | complete_course_registration_requests | complete @ $row | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/registration-requests/courses/{id}/cancel` | Bearer Token | — | cancel @ $row | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/admin/activity-logs` | Bearer Token | auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/activity-logs/export` | Bearer Token | auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/activity-logs/{id}` | Bearer Token | auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/users/{id}/activity-logs` | Bearer Token | auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/access-summary` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/users` | Bearer Token | admin|super_admin|system_admin|general_director | viewAny @ User::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/users` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/users/{id}/access` | Bearer Token | admin|super_admin|system_admin|general_director | viewAccess @ $target | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/users/{id}` | Bearer Token | admin|super_admin|system_admin|general_director | viewAccess @ $target | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/admin/users/{id}` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/users/{id}/change-password` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/users/{id}/roles/sync` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/users/{id}/permissions/sync` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/users/{id}/roles` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/admin/users/{id}/roles/{role}` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/users/{id}/permissions` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/admin/users/{id}/permissions/{permission}` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PATCH api/admin/users/{id}/status` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PATCH api/admin/users/{id}/parent` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/users/{id}/children` | Bearer Token | admin|super_admin|system_admin|general_director | viewAny @ User::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/my-children` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/my-delegatable` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/roles` | Bearer Token | admin|super_admin|system_admin|general_director | viewAny @ Role::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/roles` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/roles/{id}` | Bearer Token | admin|super_admin|system_admin|general_director | view @ $role | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PATCH api/admin/roles/{id}` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/admin/roles/{id}` | Bearer Token | admin|super_admin|system_admin|general_director | delete @ $role | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/roles/{id}/permissions` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/admin/roles/{id}/permissions/{permissionId}` | Bearer Token | admin|super_admin|system_admin|general_director | syncPermissions @ $role | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/permissions` | Bearer Token | admin|super_admin|system_admin|general_director | viewAny @ Permission::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/admin/permissions` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/admin/permissions/{id}` | Bearer Token | admin|super_admin|system_admin|general_director | view @ $permission | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PATCH api/admin/permissions/{id}` | Bearer Token | admin|super_admin|system_admin|general_director | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/admin/permissions/{id}` | Bearer Token | admin|super_admin|system_admin|general_director | delete @ $permission | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/categories` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/offices` | Bearer Token | admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/offices/{id}` | Bearer Token | admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/offices` | Bearer Token | admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/consulting/offices/{id}` | Bearer Token | admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/offices/{id}/activate` | Bearer Token | admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/offices/{id}/suspend` | Bearer Token | admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/offices/{id}/violations` | Bearer Token | admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/requests/stats` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | viewAny @ ConsultingRequest::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/requests` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | viewAny @ ConsultingRequest::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/requests/{id}` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | view @ $req | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/requests/{id}/offers` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | view @ $req | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/requests` | Bearer Token | project_owner|admin|super_admin|system_admin|general_director|branch_manager|governor | create @ ConsultingRequest::class | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/consulting/requests/{id}` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | update @ $req | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/requests/{id}/submit` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | update @ $req | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/requests/{id}/sort` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | sort @ $req | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/requests/{id}/accept-offer` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | acceptOffer @ $req | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/requests/{id}/transfer` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | transfer @ $req | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/requests/{id}/attachments` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | update @ $req | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/requests/{id}/offers` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/contracts/{id}` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/consulting/contracts/{id}/messages` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/contracts/{id}/sign` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/contracts/{id}/messages` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/contracts/{id}/report` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/contracts/{id}/approve-report` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/consulting/contracts/{id}/review` | Bearer Token | admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/notifications/summary` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/notifications` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `POST api/notifications/read-all` | Bearer Token | — | — | user_id = current user | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/notifications/{id}/read` | Bearer Token | — | — | user_id = current user | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `DELETE api/notifications/{id}` | Bearer Token | — | — | user_id = current user | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/inbox/unread-count` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/inbox/users-list` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/inbox/sent` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/inbox` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `POST api/inbox` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/inbox/{id}` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `POST api/inbox/{id}/reply` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `DELETE api/inbox/{id}` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/incubation/stats` | Bearer Token | general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/incubators` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/incubators/{id}` | Bearer Token | general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/incubators/{id}` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/incubators/{id}/programs` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/incubation/applications` | Bearer Token | general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/incubation/apply` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/incubation/my-applications` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/incubation/applications/{id}` | Bearer Token | — | view @ $app | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/incubation/applications/{id}/review` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | review @ $app | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/incubators/{id}/applications` | Bearer Token | general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/incubation/projects` | Bearer Token | general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/incubation/my-project` | Bearer Token | — | — | — | ⚠️ auth:sanctum فقط | مراجعة يدوية — لا permission middleware ولا authorize() مستخرج |
| `GET api/incubation/projects/{id}` | Bearer Token | general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | view @ $project | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/incubation/projects/{id}` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | update @ $project | Policy authorize() | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/incubation/sessions` | Bearer Token | general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/incubation/projects/{id}/sessions` | Bearer Token | incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/incubation/my-sessions` | Bearer Token | incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/incubation/projects/{id}/reports` | Bearer Token | — | submitProgressReport @ $project | Policy authorize() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/success-stories/stats` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/success-stories` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/success-stories/{id}` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/success-stories/{id}` | Bearer Token | general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/news/stats` | Bearer Token | media_manager|general_director|admin|super_admin|system_admin|news.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/news` | Bearer Token | media_manager|general_director|admin|super_admin|system_admin|news.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `PUT api/news/{id}` | Bearer Token | media_manager|general_director|admin|super_admin|system_admin|news.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `DELETE api/news/{id}` | Bearer Token | media_manager|general_director|admin|super_admin|system_admin|news.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/entrepreneur/my-profile` | Bearer Token | — | — | user_id = Auth::id() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `POST api/entrepreneur/profile` | Bearer Token | — | — | user_id = Auth::id() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `PUT api/entrepreneur/profile/{id}` | Bearer Token | — | — | user_id = Auth::id() | ✅ Bearer + فحص داخلي | مقبول — authorize()/ownership داخل Controller |
| `GET api/entrepreneur/profiles` | Bearer Token | general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/entrepreneur/profiles/export` | Bearer Token | general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/entrepreneur/profiles/stats` | Bearer Token | general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `GET api/entrepreneur/profiles/{id}` | Bearer Token | general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |
| `POST api/entrepreneur/profiles/{id}/review` | Bearer Token | general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | — | — | ✅ محمي | لا إجراء — التحقق موجود في Controller/Policy |

---

<a id="appendix"></a>
## ملحق — القيم الثابتة

### Need Status (`App\Support\NeedStatus`)

| القيمة | المعنى |
|--------|--------|
| new | مسودة |
| pending_governorate_review | بانتظار تدقيق بيانات المحافظة |
| returned_for_edit | معاد للتعديل |
| pending_branch_approval | بانتظار موافقة مدير الفرع |
| approved | موافق عليه |
| rejected | مرفوض |
| classified | مصنف |
| in_progress | قيد المعالجة |
| resolved | تم الحل |
| archived | مؤرشف |

### Certificate Type (`App\Support\CertificateType`)

| القيمة | المعنى |
|--------|--------|
| attendance | شهادة حضور |
| pass | شهادة اجتياز (completion alias مقبول) |

### account_type — Self Registration

| account_type | الدور الناتج | entity_type |
|--------------|-------------|-------------|
| trainee | trainee_user | trainee_user |
| trainer | trainer_user | trainer_user |
| center | center_user | center_user |
| project_owner | project_owner | project_owner |
| consultant | consultant_office | consultant_office |
| consulting_client | trainee_user | consulting_client |
| jobseeker | trainee_user | job_seeker |
| employer | project_owner | project_owner |

**المصدر:** `app/Support/SelfRegistrationCatalog.php`


</div>