<div dir="rtl" lang="ar">

# توثيق واجهات البرمجة (API Documentation)
## منصة SMEDC — الإصدار 2.0

| البند | القيمة |
|-------|--------|
| **تاريخ التوليد** | 2026-07-15 |
| **المصدر** | `php artisan route:list --json` (346 route) |
| **مسارات API** | 329 |
| **إطار العمل** | Laravel 12 |
| **المصادقة** | Laravel Sanctum (Bearer Token) |
| **Guard الصلاحيات** | `sanctum` (Spatie Permission) |

> **تنبيه:** هذه الوثيقة **مُولَّدة آلياً** من الراوتات المسجلة فعلياً في المشروع. أي endpoint غير موجود في الجداول أدناه **غير موجود** في الكود الحالي.

---

## 1. معلومات عامة

### 1.1 Base URL

| البيئة | Base URL | مثال endpoint |
|--------|----------|---------------|
| **Local (artisan serve)** | `http://127.0.0.1:8000` | `http://127.0.0.1:8000/api/login` |
| **Production (Hostinger)** | `https://smeda.gov.sy/api` | `https://smeda.gov.sy/api/api/login` |

> على Hostinger: مجلد `public_html/api/` هو مدخل Laravel، والراوتات مسجّلة تحت prefix `/api` — فيصبح المسار الكامل **`/api/api/...`**.

### 1.2 Headers

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

### 1.3 رموز الاستجابة الشائعة

| Code | المعنى |
|------|--------|
| 200 | نجاح |
| 201 | تم الإنشاء |
| 204 | نجاح بدون محتوى |
| 401 | غير مصادق |
| 403 | ممنوع (صلاحية/Policy) |
| 404 | غير موجود |
| 422 | خطأ Validation |
| 429 | تجاوز Rate Limit |
| 500 | خطأ خادم |

### 1.4 Health Check

| Method | URI | Auth |
|--------|-----|------|
| GET | `/up` | Public |

---

## 2. المصادقة — تفاصيل الطلبات

### POST `/api/register` (Public, throttle:register)

**Body:**
```json
{
  "name": "string (required, max:255)",
  "email": "string (required, email, unique)",
  "password": "string (required, min:8, confirmed)",
  "password_confirmation": "string (required)",
  "account_type": "string (required — see SelfRegistrationCatalog)",
  "device_name": "string (optional)"
}
```

**محظور في الطلب:** `role`, `roles`, `permissions`, `entity_type`, `training_center_id`, `trainer_id`, `trainee_id`, `is_active`

**Response 201:**
```json
{
  "message": "تم إنشاء الحساب بنجاح.",
  "token": "...",
  "token_type": "Bearer",
  "redirect_to_form": "center|trainer|trainee|...",
  "entity_pending_approval": true,
  "user": { "id": 1, "name": "...", "email": "...", "roles": [], "permissions": [] }
}
```

**المصدر:** `AuthController@register` — `app/Http/Controllers/Api/AuthController.php:29-93`

---

### POST `/api/login` (Public, throttle:login)

**Body:**
```json
{
  "email": "string (required, email)",
  "password": "string (required)",
  "device_name": "string (optional)"
}
```

**Response 200:**
```json
{
  "message": "تم تسجيل الدخول بنجاح.",
  "token": "...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "...",
    "email": "...",
    "phone": null,
    "entity_type": "...",
    "training_center_id": null,
    "trainer_id": null,
    "trainee_id": null,
    "governorate_id": null,
    "branch_id": null,
    "is_active": true,
    "last_login_at": "2026-07-15T12:00:00+00:00",
    "created_at": "...",
    "roles": ["..."],
    "permissions": ["..."]
  }
}
```

**Response 401:** بيانات خاطئة أو حساب معطّل (`is_active=false`)

---

### POST `/api/logout` (Bearer Token)

**Response 200:** `{ "message": "..." }`

---

### GET/PUT `/api/me` | POST `/api/me/change-password` (Bearer Token)

---

## 3. مرجع المسارات الكامل (من Route List)

### 00 — Health

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/up` | Public (health) |  | `Closure` | 

### 01 — Public Browse

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/public/finance/cloud` | Public | api, throttle:map-public | `App\Http\Controllers\Api\PublicBrowseController@financeCloud` | 
| 2 | `GET\|HEAD` | `/api/public/finance/metrics` | Public | api, throttle:map-public | `App\Http\Controllers\Api\PublicBrowseController@financeMetrics` | 
| 3 | `GET\|HEAD` | `/api/public/governorates` | Public | api, throttle:map-public | `App\Http\Controllers\Api\PublicBrowseController@governorates` | 
| 4 | `GET\|HEAD` | `/api/public/job-postings` | Public | api, throttle:map-public | `App\Http\Controllers\Api\PublicBrowseController@jobPostings` | 
| 5 | `GET\|HEAD` | `/api/public/needs/lookups` | Public | api, throttle:map-public | `App\Http\Controllers\Api\PublicBrowseController@needsLookups` | 
| 6 | `GET\|HEAD` | `/api/public/needs/map` | Public | api, throttle:map-public | `App\Http\Controllers\Api\PublicBrowseController@needsMap` | 
| 7 | `POST` | `/api/public/needs` | Public | api, throttle:map-public, throttle:5,10 | `App\Http\Controllers\Api\PublicBrowseController@storeGuestNeed` | 
| 8 | `GET\|HEAD` | `/api/public/training-programs` | Public | api, throttle:map-public | `App\Http\Controllers\Api\PublicBrowseController@trainingPrograms` | 

### 02 — Authentication & Profile

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `POST` | `/api/login` | Public | api, throttle:login | `App\Http\Controllers\Api\AuthController@login` | 
| 2 | `POST` | `/api/logout` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AuthController@logout` | 
| 3 | `POST` | `/api/me/change-password` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AuthController@changeMyPassword` | 
| 4 | `GET\|HEAD` | `/api/me` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AuthController@me` | 
| 5 | `PUT` | `/api/me` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AuthController@updateMe` | 
| 6 | `GET\|HEAD` | `/api/my-electronic-signature/image` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\UserElectronicSignatureController@myImage` | 
| 7 | `DELETE` | `/api/my-electronic-signature` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\UserElectronicSignatureController@destroy` | 
| 8 | `GET\|HEAD` | `/api/my-electronic-signature` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\UserElectronicSignatureController@show` | 
| 9 | `POST` | `/api/my-electronic-signature` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:file-upload | `App\Http\Controllers\Api\UserElectronicSignatureController@store` | 
| 10 | `GET\|HEAD` | `/api/my-trainer-profile` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:view_trainer_profiles|edit_own_trainer_profile | `App\Http\Controllers\Api\TrainerProfileController@myProfile` | 
| 11 | `POST` | `/api/my-trainer-profile` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:edit_own_trainer_profile | `App\Http\Controllers\Api\TrainerProfileController@updateMyProfile` | 
| 12 | `POST` | `/api/register` | Public | api, throttle:register | `App\Http\Controllers\Api\AuthController@register` | 

### 03 — Dashboard

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/dashboard` | Bearer Token (Sanctum) | api, auth:sanctum, dashboard.access | `App\Http\Controllers\Api\DashboardController@index` | 

### 04 — Organization

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `POST` | `/api/agreements/{id}/approve` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AgreementController@approve` | 
| 2 | `GET\|HEAD` | `/api/agreements/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AgreementController@show` | 
| 3 | `PUT` | `/api/agreements/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AgreementController@update` | 
| 4 | `GET\|HEAD` | `/api/agreements` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AgreementController@index` | 
| 5 | `POST` | `/api/agreements` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\AgreementController@store` | 
| 6 | `GET\|HEAD` | `/api/branches/dashboard` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\BranchController@dashboard` | 
| 7 | `DELETE` | `/api/branches/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\BranchController@destroy` | 
| 8 | `GET\|HEAD` | `/api/branches/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\BranchController@show` | 
| 9 | `PUT` | `/api/branches/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\BranchController@update` | 
| 10 | `GET\|HEAD` | `/api/branches` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\BranchController@index` | 
| 11 | `POST` | `/api/branches` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\BranchController@store` | 
| 12 | `GET\|HEAD` | `/api/governorates` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\GovernorateController@index` | 

### 05 — Finance

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/finance/applications/{applicationId}/documents/{documentId}/download` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view | `App\Http\Controllers\Api\FundingDocumentController@download` | 
| 2 | `POST` | `/api/finance/applications/{applicationId}/documents` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view, throttle:file-upload | `App\Http\Controllers\Api\FundingDocumentController@store` | 
| 3 | `POST` | `/api/finance/applications/{id}/approve` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance.applications.approve|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@approve` | 
| 4 | `POST` | `/api/finance/applications/{id}/assign-consultant` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:branch_manager|finance_manager|finance.applications.assign_consultant|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@assignConsultant` | 
| 5 | `POST` | `/api/finance/applications/{id}/assign-partner` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|central_bank_admin|finance.applications.assign_partner|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@assignPartner` | 
| 6 | `POST` | `/api/finance/applications/{id}/branch-review` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:branch_manager|finance.applications.review_branch|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@branchReview` | 
| 7 | `POST` | `/api/finance/applications/{id}/create-loan` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance.loans.manage|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@createLoan` | 
| 8 | `POST` | `/api/finance/applications/{id}/reject` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:branch_manager|finance_manager|finance.applications.reject|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@reject` | 
| 9 | `POST` | `/api/finance/applications/{id}/request-completion` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:branch_manager|finance.applications.request_completion|finance_manager|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@requestCompletion` | 
| 10 | `POST` | `/api/finance/applications/{id}/submit` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:project_owner|finance.applications.submit|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@submit` | 
| 11 | `GET\|HEAD` | `/api/finance/applications/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view | `App\Http\Controllers\Api\FundingApplicationController@show` | 
| 12 | `PUT` | `/api/finance/applications/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:project_owner|finance.applications.update|branch_manager|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@update` | 
| 13 | `GET\|HEAD` | `/api/finance/applications` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view | `App\Http\Controllers\Api\FundingApplicationController@index` | 
| 14 | `POST` | `/api/finance/applications` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:project_owner|finance.applications.create|general_director|admin|super_admin|system_admin | `App\Http\Controllers\Api\FundingApplicationController@store` | 
| 15 | `GET\|HEAD` | `/api/finance/central-bank/dashboard` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|finance.central_bank.dashboard | `App\Http\Controllers\Api\FundingPartnerController@centralBankDashboard` | 
| 16 | `GET\|HEAD` | `/api/finance/cloud` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | `App\Http\Controllers\Api\FundingMetricsController@cloud` | 
| 17 | `POST` | `/api/finance/consultant-assignments/{id}/accept` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_office|finance.consultant_assignments.accept | `App\Http\Controllers\Api\FundingConsultantController@acceptAssignment` | 
| 18 | `POST` | `/api/finance/consultant-assignments/{id}/approve-price` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:branch_manager|finance_manager|general_director|admin|super_admin|system_admin|finance.consultants.approve_price | `App\Http\Controllers\Api\FundingConsultantController@approvePrice` | 
| 19 | `POST` | `/api/finance/consultant-assignments/{id}/price-offer` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_office|finance.consultant_assignments.submit_price|finance.consultants.submit_price | `App\Http\Controllers\Api\FundingConsultantController@priceOffer` | 
| 20 | `POST` | `/api/finance/consultant-assignments/{id}/reject` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_office|finance.consultant_assignments.reject | `App\Http\Controllers\Api\FundingConsultantController@rejectAssignment` | 
| 21 | `GET\|HEAD` | `/api/finance/consultant-assignments` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | `App\Http\Controllers\Api\FundingConsultantController@indexAssignments` | 
| 22 | `GET\|HEAD` | `/api/finance/consultant-office/dashboard` | Bearer Token (Sanctum) | api, auth:sanctum, RoleMiddleware:consultant_office | `App\Http\Controllers\Api\FundingConsultantController@officeDashboard` | 
| 23 | `POST` | `/api/finance/consultant-offices/{id}/activate` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.activate|finance.consultants.manage | `App\Http\Controllers\Api\FundingConsultantController@activateOffice` | 
| 24 | `POST` | `/api/finance/consultant-offices/{id}/approve` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.approve|finance.consultants.manage | `App\Http\Controllers\Api\FundingConsultantController@approveOffice` | 
| 25 | `GET\|HEAD` | `/api/finance/consultant-offices/{id}/assignments` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | `App\Http\Controllers\Api\FundingConsultantController@officeAssignments` | 
| 26 | `GET\|HEAD` | `/api/finance/consultant-offices/{id}/metrics` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | `App\Http\Controllers\Api\FundingConsultantController@officeMetrics` | 
| 27 | `GET\|HEAD` | `/api/finance/consultant-offices/{id}/reports` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | `App\Http\Controllers\Api\FundingConsultantController@officeReports` | 
| 28 | `POST` | `/api/finance/consultant-offices/{id}/suspend` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.suspend|finance.consultants.manage | `App\Http\Controllers\Api\FundingConsultantController@suspendOffice` | 
| 29 | `GET\|HEAD` | `/api/finance/consultant-offices/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | `App\Http\Controllers\Api\FundingConsultantController@showOffice` | 
| 30 | `PUT` | `/api/finance/consultant-offices/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.update|finance.consultants.manage | `App\Http\Controllers\Api\FundingConsultantController@updateOffice` | 
| 31 | `GET\|HEAD` | `/api/finance/consultant-offices` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all | `App\Http\Controllers\Api\FundingConsultantController@indexOffices` | 
| 32 | `POST` | `/api/finance/consultant-offices` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.create|finance.consultants.manage | `App\Http\Controllers\Api\FundingConsultantController@storeOffice` | 
| 33 | `POST` | `/api/finance/consultant-reports` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_office|general_director|admin|super_admin|system_admin|finance.consultant_reports.create|finance.consultants.submit_report | `App\Http\Controllers\Api\FundingConsultantController@storeReport` | 
| 34 | `GET\|HEAD` | `/api/finance/consultant-union/dashboard` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:consultant_union_admin|finance.consultant_union.dashboard | `App\Http\Controllers\Api\FundingConsultantController@unionDashboard` | 
| 35 | `GET\|HEAD` | `/api/finance/defaulted/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | `App\Http\Controllers\Api\FundingMetricsController@defaultedStats` | 
| 36 | `GET\|HEAD` | `/api/finance/defaulted` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | `App\Http\Controllers\Api\FundingMetricsController@defaulted` | 
| 37 | `GET\|HEAD` | `/api/finance/funded/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | `App\Http\Controllers\Api\FundingMetricsController@fundedStats` | 
| 38 | `GET\|HEAD` | `/api/finance/funded` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | `App\Http\Controllers\Api\FundingMetricsController@funded` | 
| 39 | `GET\|HEAD` | `/api/finance/funding-partner/dashboard` | Bearer Token (Sanctum) | api, auth:sanctum, RoleMiddleware:funding_partner | `App\Http\Controllers\Api\FundingPartnerController@partnerDashboard` | 
| 40 | `GET\|HEAD` | `/api/finance/loans/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own | `App\Http\Controllers\Api\FundedLoanController@stats` | 
| 41 | `POST` | `/api/finance/loans/{id}/close` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.close|finance.loans.manage | `App\Http\Controllers\Api\FundedLoanController@close` | 
| 42 | `POST` | `/api/finance/loans/{id}/mark-defaulted` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.defaulted|finance.loans.manage | `App\Http\Controllers\Api\FundedLoanController@markDefaulted` | 
| 43 | `GET\|HEAD` | `/api/finance/loans/{id}/payments` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own | `App\Http\Controllers\Api\FundedLoanController@payments` | 
| 44 | `POST` | `/api/finance/loans/{id}/payments` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.payments|finance.loans.manage | `App\Http\Controllers\Api\FundedLoanController@storePayment` | 
| 45 | `GET\|HEAD` | `/api/finance/loans/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own | `App\Http\Controllers\Api\FundedLoanController@show` | 
| 46 | `PUT` | `/api/finance/loans/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|funding_partner|general_director|admin|super_admin|system_admin|finance.loans.manage|finance.loans.update_own_status | `App\Http\Controllers\Api\FundedLoanController@update` | 
| 47 | `GET\|HEAD` | `/api/finance/loans` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own | `App\Http\Controllers\Api\FundedLoanController@index` | 
| 48 | `GET\|HEAD` | `/api/finance/manager/dashboard` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance_officer|general_director|admin|super_admin|system_admin|finance.metrics.view | `App\Http\Controllers\Api\FundingMetricsController@managerDashboard` | 
| 49 | `GET\|HEAD` | `/api/finance/metrics` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch | `App\Http\Controllers\Api\FundingMetricsController@metrics` | 
| 50 | `GET\|HEAD` | `/api/finance/my-consultant-assignments` | Bearer Token (Sanctum) | api, auth:sanctum, RoleMiddleware:consultant_office | `App\Http\Controllers\Api\FundingConsultantController@myAssignments` | 
| 51 | `GET\|HEAD` | `/api/finance/my-partner-assignments` | Bearer Token (Sanctum) | api, auth:sanctum, RoleMiddleware:funding_partner | `App\Http\Controllers\Api\FundingPartnerController@myAssignments` | 
| 52 | `POST` | `/api/finance/partner-assignments/{id}/decision` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:funding_partner|central_bank_admin|finance_manager|general_director|admin|super_admin|system_admin|finance.partner_assignments.decide|finance.partners.decide | `App\Http\Controllers\Api\FundingPartnerController@decision` | 
| 53 | `POST` | `/api/finance/partners/{id}/activate` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.activate|finance.partners.manage | `App\Http\Controllers\Api\FundingPartnerController@activatePartner` | 
| 54 | `POST` | `/api/finance/partners/{id}/approve` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.approve|finance.partners.manage | `App\Http\Controllers\Api\FundingPartnerController@approvePartner` | 
| 55 | `GET\|HEAD` | `/api/finance/partners/{id}/assignments` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | `App\Http\Controllers\Api\FundingPartnerController@partnerAssignments` | 
| 56 | `GET\|HEAD` | `/api/finance/partners/{id}/decisions` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | `App\Http\Controllers\Api\FundingPartnerController@partnerDecisions` | 
| 57 | `GET\|HEAD` | `/api/finance/partners/{id}/loans` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | `App\Http\Controllers\Api\FundingPartnerController@partnerLoans` | 
| 58 | `GET\|HEAD` | `/api/finance/partners/{id}/metrics` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | `App\Http\Controllers\Api\FundingPartnerController@partnerMetrics` | 
| 59 | `POST` | `/api/finance/partners/{id}/suspend` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.suspend|finance.partners.manage | `App\Http\Controllers\Api\FundingPartnerController@suspendPartner` | 
| 60 | `GET\|HEAD` | `/api/finance/partners/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | `App\Http\Controllers\Api\FundingPartnerController@show` | 
| 61 | `PUT` | `/api/finance/partners/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.update|finance.partners.manage | `App\Http\Controllers\Api\FundingPartnerController@update` | 
| 62 | `GET\|HEAD` | `/api/finance/partners` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all | `App\Http\Controllers\Api\FundingPartnerController@index` | 
| 63 | `POST` | `/api/finance/partners` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.create|finance.partners.manage | `App\Http\Controllers\Api\FundingPartnerController@store` | 
| 64 | `POST` | `/api/finance/records/{id}/approve` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|finance_manager|admin|super_admin|system_admin|manage_finance | `App\Http\Controllers\Api\FinancialRecordController@approve` | 
| 65 | `GET\|HEAD` | `/api/finance/records/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance | `App\Http\Controllers\Api\FinancialRecordController@show` | 
| 66 | `PUT` | `/api/finance/records/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|finance_manager|admin|super_admin|system_admin|manage_finance | `App\Http\Controllers\Api\FinancialRecordController@update` | 
| 67 | `GET\|HEAD` | `/api/finance/records` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance | `App\Http\Controllers\Api\FinancialRecordController@index` | 
| 68 | `POST` | `/api/finance/records` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|finance_manager|admin|super_admin|system_admin|manage_finance | `App\Http\Controllers\Api\FinancialRecordController@store` | 

### 06 — Needs (GIS)

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/needs/admin-units` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:needs.view|needs.view_all|needs.view_branch | `App\Http\Controllers\Api\NeedController@adminUnits` | 
| 2 | `GET\|HEAD` | `/api/needs/analytics` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NeedController@analytics` | 
| 3 | `GET\|HEAD` | `/api/needs/dashboard` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:needs.dashboard | `App\Http\Controllers\Api\NeedController@dashboard` | 
| 4 | `GET\|HEAD` | `/api/needs/export` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:needs.view|needs.view_all|needs.view_branch | `App\Http\Controllers\Api\NeedController@export` | 
| 5 | `GET\|HEAD` | `/api/needs/lookups` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:needs.view|needs.view_all|needs.view_branch | `App\Http\Controllers\Api\NeedController@lookups` | 
| 6 | `GET\|HEAD` | `/api/needs/map` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:needs.view|needs.view_all|needs.view_branch | `App\Http\Controllers\Api\NeedController@map` | 
| 7 | `GET\|HEAD` | `/api/needs/workspace/data-entry` | Bearer Token (Sanctum) | api, auth:sanctum, RoleMiddleware:data_entry | `App\Http\Controllers\Api\NeedController@dataEntryWorkspace` | 
| 8 | `GET\|HEAD` | `/api/needs/workspace/reviewer` | Bearer Token (Sanctum) | api, auth:sanctum, RoleMiddleware:data_reviewer | `App\Http\Controllers\Api\NeedController@reviewerWorkspace` | 
| 9 | `POST` | `/api/needs/{id}/approve` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NeedController@approve` | 
| 10 | `POST` | `/api/needs/{id}/classify` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NeedController@classify` | 
| 11 | `POST` | `/api/needs/{id}/reject` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NeedController@reject` | 
| 12 | `POST` | `/api/needs/{id}/resolve` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NeedController@resolve` | 
| 13 | `POST` | `/api/needs/{id}/return` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NeedController@returnForEdit` | 
| 14 | `POST` | `/api/needs/{id}/review` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NeedController@review` | 
| 15 | `GET\|HEAD` | `/api/needs/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:needs.view|needs.view_all|needs.view_branch | `App\Http\Controllers\Api\NeedController@show` | 
| 16 | `PUT` | `/api/needs/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NeedController@update` | 
| 17 | `GET\|HEAD` | `/api/needs` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:needs.view|needs.view_all|needs.view_branch | `App\Http\Controllers\Api\NeedController@index` | 
| 18 | `POST` | `/api/needs` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:needs.create|needs.create_citizen|needs.create_state | `App\Http\Controllers\Api\NeedController@store` | 

### 07 — Training & Workforce

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/certificates/code/{certificate_code}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_certificates | `App\Http\Controllers\Api\CertificateController@showByCode` | 
| 2 | `POST` | `/api/certificates/issue` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:issue_certificates | `App\Http\Controllers\Api\CertificateController@issue` | 
| 3 | `GET\|HEAD` | `/api/certificates/verify-page` | Public | api, throttle:verify-page | `App\Http\Controllers\Api\CertificateController@verifyPage` | 
| 4 | `POST` | `/api/certificates/verify` | Public | api, throttle:certificate-verify | `App\Http\Controllers\Api\CertificateController@verify` | 
| 5 | `POST` | `/api/certificates/{id}/approve` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:approve_center_certificates|approve_training_certificates|approve_deputy_certificates|approve_general_director_certificates | `App\Http\Controllers\Api\CertificateController@approve` | 
| 6 | `GET\|HEAD` | `/api/certificates/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_certificates | `App\Http\Controllers\Api\CertificateController@show` | 
| 7 | `GET\|HEAD` | `/api/certificates` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_certificates | `App\Http\Controllers\Api\CertificateController@index` | 
| 8 | `GET\|HEAD` | `/api/map/trainers` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_trainers, throttle:map-public | `App\Http\Controllers\Api\TrainingMapController@trainers` | 
| 9 | `GET\|HEAD` | `/api/map/training-centers` | Public | api, throttle:map-public | `App\Http\Controllers\Api\TrainingMapController@centers` | 
| 10 | `GET\|HEAD` | `/api/map/training-courses` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_courses, throttle:map-public | `App\Http\Controllers\Api\TrainingMapController@courses` | 
| 11 | `GET\|HEAD` | `/api/program-bank/reports` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.reports|program_bank.view|view_reports | `App\Http\Controllers\Api\ProgramBankController@reports` | 
| 12 | `GET\|HEAD` | `/api/program-bank/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs | `App\Http\Controllers\Api\ProgramBankController@stats` | 
| 13 | `POST` | `/api/program-bank/{id}/create-course` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs | `App\Http\Controllers\Api\ProgramBankController@createCourseFromProgram` | 
| 14 | `POST` | `/api/program-bank/{id}/duplicate` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs | `App\Http\Controllers\Api\ProgramBankController@duplicate` | 
| 15 | `PUT` | `/api/program-bank/{id}/modules/reorder` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@reorderModules` | 
| 16 | `DELETE` | `/api/program-bank/{id}/modules/{moduleId}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@destroyModule` | 
| 17 | `PUT` | `/api/program-bank/{id}/modules/{moduleId}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@updateModule` | 
| 18 | `POST` | `/api/program-bank/{id}/modules` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@storeModule` | 
| 19 | `DELETE` | `/api/program-bank/{id}/outcomes/{outcomeId}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@destroyOutcome` | 
| 20 | `PUT` | `/api/program-bank/{id}/outcomes/{outcomeId}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@updateOutcome` | 
| 21 | `POST` | `/api/program-bank/{id}/outcomes` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@storeOutcome` | 
| 22 | `PUT` | `/api/program-bank/{id}/service-links` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@syncServiceLinks` | 
| 23 | `POST` | `/api/program-bank/{id}/transition` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|deputy_director|program_bank.approve|manage_programs | `App\Http\Controllers\Api\ProgramBankController@transition` | 
| 24 | `DELETE` | `/api/program-bank/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.delete|manage_programs | `App\Http\Controllers\Api\ProgramBankController@destroy` | 
| 25 | `GET\|HEAD` | `/api/program-bank/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs | `App\Http\Controllers\Api\ProgramBankController@show` | 
| 26 | `PUT` | `/api/program-bank/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs | `App\Http\Controllers\Api\ProgramBankController@update` | 
| 27 | `GET\|HEAD` | `/api/program-bank` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs | `App\Http\Controllers\Api\ProgramBankController@index` | 
| 28 | `POST` | `/api/program-bank` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs | `App\Http\Controllers\Api\ProgramBankController@store` | 
| 29 | `POST` | `/api/registration-requests/centers/{id}/review` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:review_center_registration_requests | `App\Http\Controllers\Api\TrainingCenterRegistrationRequestController@review` | 
| 30 | `GET\|HEAD` | `/api/registration-requests/centers/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\TrainingCenterRegistrationRequestController@show` | 
| 31 | `GET\|HEAD` | `/api/registration-requests/centers` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_registration_requests | `App\Http\Controllers\Api\TrainingCenterRegistrationRequestController@index` | 
| 32 | `POST` | `/api/registration-requests/centers` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:create_center_registration_requests, throttle:registration-requests, throttle:file-upload | `App\Http\Controllers\Api\TrainingCenterRegistrationRequestController@store` | 
| 33 | `POST` | `/api/registration-requests/courses/{id}/cancel` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\CourseRegistrationRequestController@cancel` | 
| 34 | `POST` | `/api/registration-requests/courses/{id}/complete` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:complete_course_registration_requests | `App\Http\Controllers\Api\CourseRegistrationRequestController@complete` | 
| 35 | `POST` | `/api/registration-requests/courses/{id}/confirm-by-guardian` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:confirm_course_registration_requests | `App\Http\Controllers\Api\CourseRegistrationRequestController@confirmByGuardian` | 
| 36 | `GET\|HEAD` | `/api/registration-requests/courses/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\CourseRegistrationRequestController@show` | 
| 37 | `GET\|HEAD` | `/api/registration-requests/courses` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:view_registration_requests|create_course_registration_requests|confirm_course_registration_requests|complete_course_registration_requests | `App\Http\Controllers\Api\CourseRegistrationRequestController@index` | 
| 38 | `POST` | `/api/registration-requests/courses` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:create_course_registration_requests, throttle:registration-requests | `App\Http\Controllers\Api\CourseRegistrationRequestController@store` | 
| 39 | `POST` | `/api/registration-requests/trainees/{id}/review` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:review_trainee_registration_requests | `App\Http\Controllers\Api\TraineeRegistrationRequestController@review` | 
| 40 | `GET\|HEAD` | `/api/registration-requests/trainees/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\TraineeRegistrationRequestController@show` | 
| 41 | `GET\|HEAD` | `/api/registration-requests/trainees` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_registration_requests | `App\Http\Controllers\Api\TraineeRegistrationRequestController@index` | 
| 42 | `POST` | `/api/registration-requests/trainees` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:create_trainee_registration_requests, throttle:registration-requests | `App\Http\Controllers\Api\TraineeRegistrationRequestController@store` | 
| 43 | `POST` | `/api/registration-requests/trainers/{id}/review` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:review_trainer_registration_requests | `App\Http\Controllers\Api\TrainerRegistrationRequestController@review` | 
| 44 | `GET\|HEAD` | `/api/registration-requests/trainers/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\TrainerRegistrationRequestController@show` | 
| 45 | `GET\|HEAD` | `/api/registration-requests/trainers` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_registration_requests | `App\Http\Controllers\Api\TrainerRegistrationRequestController@index` | 
| 46 | `POST` | `/api/registration-requests/trainers` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:create_trainer_registration_requests, throttle:registration-requests | `App\Http\Controllers\Api\TrainerRegistrationRequestController@store` | 
| 47 | `GET\|HEAD` | `/api/signatures/verify/{code}` | Public | api, throttle:certificate-verify | `App\Http\Controllers\Api\ExecutiveSignatureController@verify` | 
| 48 | `GET\|HEAD` | `/api/trainees/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_trainees | `App\Http\Controllers\Api\TraineeController@show` | 
| 49 | `GET\|HEAD` | `/api/trainees` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_trainees | `App\Http\Controllers\Api\TraineeController@index` | 
| 50 | `GET\|HEAD` | `/api/trainer-profiles/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_trainer_profiles | `App\Http\Controllers\Api\TrainerProfileController@show` | 
| 51 | `GET\|HEAD` | `/api/trainers/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_trainers | `App\Http\Controllers\Api\TrainerController@show` | 
| 52 | `GET\|HEAD` | `/api/trainers` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_trainers | `App\Http\Controllers\Api\TrainerController@index` | 
| 53 | `GET\|HEAD` | `/api/training-centers/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_centers | `App\Http\Controllers\Api\TrainingCenterController@show` | 
| 54 | `GET\|HEAD` | `/api/training-centers` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_centers | `App\Http\Controllers\Api\TrainingCenterController@index` | 
| 55 | `POST` | `/api/training-courses/{id}/complete` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:manage_courses | `App\Http\Controllers\Api\TrainingCourseController@complete` | 
| 56 | `DELETE` | `/api/training-courses/{id}/trainees/{traineeId}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:manage_courses | `App\Http\Controllers\Api\TrainingCourseController@removeTrainee` | 
| 57 | `PATCH` | `/api/training-courses/{id}/trainees/{traineeId}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:manage_courses | `App\Http\Controllers\Api\TrainingCourseController@updateTrainee` | 
| 58 | `GET\|HEAD` | `/api/training-courses/{id}/trainees` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:view_courses|view_course_details | `App\Http\Controllers\Api\TrainingCourseController@trainees` | 
| 59 | `POST` | `/api/training-courses/{id}/trainees` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:manage_courses | `App\Http\Controllers\Api\TrainingCourseController@addTrainee` | 
| 60 | `GET\|HEAD` | `/api/training-courses/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:trainer_user|trainee_user|view_courses|view_course_details | `App\Http\Controllers\Api\TrainingCourseController@show` | 
| 61 | `PATCH` | `/api/training-courses/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:manage_courses | `App\Http\Controllers\Api\TrainingCourseController@update` | 
| 62 | `GET\|HEAD` | `/api/training-courses` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:trainer_user|trainee_user|view_courses | `App\Http\Controllers\Api\TrainingCourseController@index` | 
| 63 | `POST` | `/api/training-courses` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:manage_courses | `App\Http\Controllers\Api\TrainingCourseController@store` | 
| 64 | `POST` | `/api/training-kit-nominations/{id}/review` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:review_training_kit_nominations | `App\Http\Controllers\Api\TrainingKitNominationController@review` | 
| 65 | `GET\|HEAD` | `/api/training-kit-nominations/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:nominate_training_kits|review_training_kit_nominations | `App\Http\Controllers\Api\TrainingKitNominationController@show` | 
| 66 | `GET\|HEAD` | `/api/training-kit-nominations` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:nominate_training_kits|review_training_kit_nominations | `App\Http\Controllers\Api\TrainingKitNominationController@index` | 
| 67 | `POST` | `/api/training-kit-nominations` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:nominate_training_kits | `App\Http\Controllers\Api\TrainingKitNominationController@store` | 
| 68 | `POST` | `/api/training-kit-public-requests` | Public | api, throttle:training-kit-public | `App\Http\Controllers\Api\TrainingKitPublicRequestController@store` | 
| 69 | `GET\|HEAD` | `/api/training-kits/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_kits | `App\Http\Controllers\Api\TrainingKitController@show` | 
| 70 | `GET\|HEAD` | `/api/training-kits` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_kits | `App\Http\Controllers\Api\TrainingKitController@index` | 
| 71 | `GET\|HEAD` | `/api/training-programs/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_programs | `App\Http\Controllers\Api\TrainingProgramController@show` | 
| 72 | `GET\|HEAD` | `/api/training-programs` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_programs | `App\Http\Controllers\Api\TrainingProgramController@index` | 
| 73 | `GET\|HEAD` | `/api/training-supervisors` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:view_centers | `App\Http\Controllers\Api\TrainingSupervisorController@index` | 
| 74 | `GET\|HEAD` | `/api/verify-certificate/{certificate_code}` | Public | api, throttle:certificate-verify | `App\Http\Controllers\Api\CertificateController@verifyByCode` | 
| 75 | `PUT` | `/api/workforce/job-applications/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.applications.view | `App\Http\Controllers\Api\JobApplicationController@update` | 
| 76 | `GET\|HEAD` | `/api/workforce/job-applications` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.applications.view | `App\Http\Controllers\Api\JobApplicationController@index` | 
| 77 | `POST` | `/api/workforce/job-applications` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.applications.create | `App\Http\Controllers\Api\JobApplicationController@store` | 
| 78 | `GET\|HEAD` | `/api/workforce/job-postings/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.jobs.view | `App\Http\Controllers\Api\JobPostingController@show` | 
| 79 | `PUT` | `/api/workforce/job-postings/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.jobs.manage | `App\Http\Controllers\Api\JobPostingController@update` | 
| 80 | `GET\|HEAD` | `/api/workforce/job-postings` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.jobs.view | `App\Http\Controllers\Api\JobPostingController@index` | 
| 81 | `POST` | `/api/workforce/job-postings` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.jobs.create | `App\Http\Controllers\Api\JobPostingController@store` | 
| 82 | `PUT` | `/api/workforce/staff-training-requests/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.training_requests.view | `App\Http\Controllers\Api\StaffTrainingRequestController@update` | 
| 83 | `GET\|HEAD` | `/api/workforce/staff-training-requests` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.training_requests.view | `App\Http\Controllers\Api\StaffTrainingRequestController@index` | 
| 84 | `POST` | `/api/workforce/staff-training-requests` | Bearer Token (Sanctum) | api, auth:sanctum, PermissionMiddleware:workforce.training_requests.create | `App\Http\Controllers\Api\StaffTrainingRequestController@store` | 
| 85 | `POST` | `/api/workforces/enroll` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|training_manager | `App\Http\Controllers\Api\WorkforceController@enroll` | 
| 86 | `GET\|HEAD` | `/api/workforces/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager | `App\Http\Controllers\Api\WorkforceController@show` | 
| 87 | `GET\|HEAD` | `/api/workforces` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager | `App\Http\Controllers\Api\WorkforceController@index` | 

### 08 — Admin & RBAC

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/admin/access-summary` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\AccessSummaryController` | 
| 2 | `GET\|HEAD` | `/api/admin/activity-logs/export` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access | `App\Http\Controllers\Api\Admin\ActivityLogController@export` | 
| 3 | `GET\|HEAD` | `/api/admin/activity-logs/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access | `App\Http\Controllers\Api\Admin\ActivityLogController@show` | 
| 4 | `GET\|HEAD` | `/api/admin/activity-logs` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access | `App\Http\Controllers\Api\Admin\ActivityLogController@index` | 
| 5 | `GET\|HEAD` | `/api/admin/my-children` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@myChildren` | 
| 6 | `GET\|HEAD` | `/api/admin/my-delegatable` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@delegatableOptions` | 
| 7 | `DELETE` | `/api/admin/permissions/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\PermissionController@destroy` | 
| 8 | `GET\|HEAD` | `/api/admin/permissions/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\PermissionController@show` | 
| 9 | `PATCH` | `/api/admin/permissions/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\PermissionController@update` | 
| 10 | `GET\|HEAD` | `/api/admin/permissions` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\PermissionController@index` | 
| 11 | `POST` | `/api/admin/permissions` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\PermissionController@store` | 
| 12 | `DELETE` | `/api/admin/roles/{id}/permissions/{permissionId}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\RoleController@detachPermission` | 
| 13 | `POST` | `/api/admin/roles/{id}/permissions` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\RoleController@syncPermissions` | 
| 14 | `DELETE` | `/api/admin/roles/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\RoleController@destroy` | 
| 15 | `GET\|HEAD` | `/api/admin/roles/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\RoleController@show` | 
| 16 | `PATCH` | `/api/admin/roles/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\RoleController@update` | 
| 17 | `GET\|HEAD` | `/api/admin/roles` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\RoleController@index` | 
| 18 | `POST` | `/api/admin/roles` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\RoleController@store` | 
| 19 | `GET\|HEAD` | `/api/admin/users/{id}/access` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@show` | 
| 20 | `GET\|HEAD` | `/api/admin/users/{id}/activity-logs` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access | `App\Http\Controllers\Api\Admin\ActivityLogController@forUser` | 
| 21 | `POST` | `/api/admin/users/{id}/change-password` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@changePassword` | 
| 22 | `GET\|HEAD` | `/api/admin/users/{id}/children` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@childrenOf` | 
| 23 | `PATCH` | `/api/admin/users/{id}/parent` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@reassignParent` | 
| 24 | `POST` | `/api/admin/users/{id}/permissions/sync` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@syncPermissions` | 
| 25 | `DELETE` | `/api/admin/users/{id}/permissions/{permission}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@revokePermission` | 
| 26 | `POST` | `/api/admin/users/{id}/permissions` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@assignPermission` | 
| 27 | `POST` | `/api/admin/users/{id}/roles/sync` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@syncRoles` | 
| 28 | `DELETE` | `/api/admin/users/{id}/roles/{role}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@revokeRole` | 
| 29 | `POST` | `/api/admin/users/{id}/roles` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@assignRole` | 
| 30 | `PATCH` | `/api/admin/users/{id}/status` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@updateStatus` | 
| 31 | `GET\|HEAD` | `/api/admin/users/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@show` | 
| 32 | `PUT` | `/api/admin/users/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@update` | 
| 33 | `GET\|HEAD` | `/api/admin/users` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@index` | 
| 34 | `POST` | `/api/admin/users` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:admin-access, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director | `App\Http\Controllers\Api\Admin\UserAccessController@store` | 

### 09 — Consulting Marketplace

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/consulting/categories` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | `Closure` | 
| 2 | `POST` | `/api/consulting/contracts/{id}/approve-report` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingContractController@approveReport` | 
| 3 | `GET\|HEAD` | `/api/consulting/contracts/{id}/messages` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | `App\Http\Controllers\Api\ConsultingContractController@messages` | 
| 4 | `POST` | `/api/consulting/contracts/{id}/messages` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingContractController@sendMessage` | 
| 5 | `POST` | `/api/consulting/contracts/{id}/report` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office, throttle:file-upload | `App\Http\Controllers\Api\ConsultingContractController@uploadReport` | 
| 6 | `POST` | `/api/consulting/contracts/{id}/review` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingContractController@submitReview` | 
| 7 | `POST` | `/api/consulting/contracts/{id}/sign` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingContractController@sign` | 
| 8 | `GET\|HEAD` | `/api/consulting/contracts/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | `App\Http\Controllers\Api\ConsultingContractController@show` | 
| 9 | `POST` | `/api/consulting/offices/{id}/activate` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | `App\Http\Controllers\Api\ConsultingOfficeController@activate` | 
| 10 | `POST` | `/api/consulting/offices/{id}/suspend` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | `App\Http\Controllers\Api\ConsultingOfficeController@suspend` | 
| 11 | `POST` | `/api/consulting/offices/{id}/violations` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | `App\Http\Controllers\Api\ConsultingOfficeController@addViolation` | 
| 12 | `GET\|HEAD` | `/api/consulting/offices/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingOfficeController@show` | 
| 13 | `PUT` | `/api/consulting/offices/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | `App\Http\Controllers\Api\ConsultingOfficeController@update` | 
| 14 | `GET\|HEAD` | `/api/consulting/offices` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingOfficeController@index` | 
| 15 | `POST` | `/api/consulting/offices` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|consultant_union_admin|branch_manager|governor | `App\Http\Controllers\Api\ConsultingOfficeController@store` | 
| 16 | `GET\|HEAD` | `/api/consulting/requests/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | `App\Http\Controllers\Api\ConsultingRequestController@stats` | 
| 17 | `POST` | `/api/consulting/requests/{id}/accept-offer` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingRequestController@acceptOffer` | 
| 18 | `POST` | `/api/consulting/requests/{id}/attachments` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office, throttle:file-upload | `App\Http\Controllers\Api\ConsultingRequestController@uploadAttachment` | 
| 19 | `GET\|HEAD` | `/api/consulting/requests/{id}/offers` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | `App\Http\Controllers\Api\ConsultingOfferController@index` | 
| 20 | `POST` | `/api/consulting/requests/{id}/offers` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingOfferController@store` | 
| 21 | `POST` | `/api/consulting/requests/{id}/sort` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingRequestController@sort` | 
| 22 | `POST` | `/api/consulting/requests/{id}/submit` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingRequestController@submit` | 
| 23 | `POST` | `/api/consulting/requests/{id}/transfer` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingRequestController@transfer` | 
| 24 | `GET\|HEAD` | `/api/consulting/requests/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | `App\Http\Controllers\Api\ConsultingRequestController@show` | 
| 25 | `PUT` | `/api/consulting/requests/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office | `App\Http\Controllers\Api\ConsultingRequestController@update` | 
| 26 | `GET\|HEAD` | `/api/consulting/requests` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:admin|super_admin|system_admin|general_director|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office | `App\Http\Controllers\Api\ConsultingRequestController@index` | 
| 27 | `POST` | `/api/consulting/requests` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:project_owner|admin|super_admin|system_admin|general_director|branch_manager|governor | `App\Http\Controllers\Api\ConsultingRequestController@store` | 

### 10 — Notifications & Inbox

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/inbox/sent` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\InboxController@sent` | 
| 2 | `GET\|HEAD` | `/api/inbox/unread-count` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\InboxController@unreadCount` | 
| 3 | `GET\|HEAD` | `/api/inbox/users-list` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\InboxController@usersList` | 
| 4 | `POST` | `/api/inbox/{id}/reply` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\InboxController@reply` | 
| 5 | `DELETE` | `/api/inbox/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\InboxController@destroy` | 
| 6 | `GET\|HEAD` | `/api/inbox/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\InboxController@show` | 
| 7 | `GET\|HEAD` | `/api/inbox` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\InboxController@inbox` | 
| 8 | `POST` | `/api/inbox` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\InboxController@store` | 
| 9 | `POST` | `/api/notifications/read-all` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NotificationController@markAllRead` | 
| 10 | `GET\|HEAD` | `/api/notifications/summary` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NotificationController@summary` | 
| 11 | `POST` | `/api/notifications/{id}/read` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NotificationController@markRead` | 
| 12 | `DELETE` | `/api/notifications/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NotificationController@destroy` | 
| 13 | `GET\|HEAD` | `/api/notifications` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\NotificationController@index` | 

### 11 — Incubation

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `POST` | `/api/incubation/applications/{id}/review` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | `App\Http\Controllers\Api\IncubatorController@reviewApplication` | 
| 2 | `GET\|HEAD` | `/api/incubation/applications/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\IncubatorController@showApplication` | 
| 3 | `GET\|HEAD` | `/api/incubation/applications` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | `App\Http\Controllers\Api\IncubatorController@allApplications` | 
| 4 | `POST` | `/api/incubation/apply` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\IncubatorController@apply` | 
| 5 | `GET\|HEAD` | `/api/incubation/my-applications` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\IncubatorController@myApplications` | 
| 6 | `GET\|HEAD` | `/api/incubation/my-project` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\IncubatorController@myProject` | 
| 7 | `GET\|HEAD` | `/api/incubation/my-sessions` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor | `App\Http\Controllers\Api\IncubatorController@myMentoringSessions` | 
| 8 | `POST` | `/api/incubation/projects/{id}/reports` | Bearer Token (Sanctum) | api, auth:sanctum, throttle:incubation-report | `App\Http\Controllers\Api\IncubatorController@storeProgressReport` | 
| 9 | `POST` | `/api/incubation/projects/{id}/sessions` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor | `App\Http\Controllers\Api\IncubatorController@storeMentoringSession` | 
| 10 | `GET\|HEAD` | `/api/incubation/projects/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | `App\Http\Controllers\Api\IncubatorController@showProject` | 
| 11 | `PUT` | `/api/incubation/projects/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | `App\Http\Controllers\Api\IncubatorController@updateProject` | 
| 12 | `GET\|HEAD` | `/api/incubation/projects` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | `App\Http\Controllers\Api\IncubatorController@projects` | 
| 13 | `GET\|HEAD` | `/api/incubation/sessions` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | `App\Http\Controllers\Api\IncubatorController@indexMentoringSessions` | 
| 14 | `GET\|HEAD` | `/api/incubation/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | `App\Http\Controllers\Api\IncubatorController@stats` | 
| 15 | `GET\|HEAD` | `/api/incubators/{id}/applications` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | `App\Http\Controllers\Api\IncubatorController@applications` | 
| 16 | `POST` | `/api/incubators/{id}/programs` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | `App\Http\Controllers\Api\IncubatorController@storeProgram` | 
| 17 | `GET\|HEAD` | `/api/incubators/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view | `App\Http\Controllers\Api\IncubatorController@show` | 
| 18 | `PUT` | `/api/incubators/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | `App\Http\Controllers\Api\IncubatorController@update` | 
| 19 | `GET\|HEAD` | `/api/incubators` | Public | api, throttle:map-public | `App\Http\Controllers\Api\IncubatorController@index` | 
| 20 | `POST` | `/api/incubators` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage | `App\Http\Controllers\Api\IncubatorController@store` | 

### 12 — Content

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/news/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:media_manager|general_director|admin|super_admin|system_admin|news.manage | `App\Http\Controllers\Api\NewsController@stats` | 
| 2 | `DELETE` | `/api/news/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:media_manager|general_director|admin|super_admin|system_admin|news.manage | `App\Http\Controllers\Api\NewsController@destroy` | 
| 3 | `GET\|HEAD` | `/api/news/{id}` | Public | api, throttle:map-public | `App\Http\Controllers\Api\NewsController@show` | 
| 4 | `PUT` | `/api/news/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:media_manager|general_director|admin|super_admin|system_admin|news.manage | `App\Http\Controllers\Api\NewsController@update` | 
| 5 | `GET\|HEAD` | `/api/news` | Public | api, throttle:map-public | `App\Http\Controllers\Api\NewsController@index` | 
| 6 | `POST` | `/api/news` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:media_manager|general_director|admin|super_admin|system_admin|news.manage | `App\Http\Controllers\Api\NewsController@store` | 
| 7 | `GET\|HEAD` | `/api/success-stories/slug/{slug}` | Public | api, throttle:map-public | `App\Http\Controllers\Api\SuccessStoryController@showBySlug` | 
| 8 | `GET\|HEAD` | `/api/success-stories/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage | `App\Http\Controllers\Api\SuccessStoryController@stats` | 
| 9 | `DELETE` | `/api/success-stories/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage | `App\Http\Controllers\Api\SuccessStoryController@destroy` | 
| 10 | `GET\|HEAD` | `/api/success-stories/{id}` | Public | api, throttle:map-public | `App\Http\Controllers\Api\SuccessStoryController@show` | 
| 11 | `PUT` | `/api/success-stories/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage | `App\Http\Controllers\Api\SuccessStoryController@update` | 
| 12 | `GET\|HEAD` | `/api/success-stories` | Public | api, throttle:map-public | `App\Http\Controllers\Api\SuccessStoryController@index` | 
| 13 | `POST` | `/api/success-stories` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage | `App\Http\Controllers\Api\SuccessStoryController@store` | 

### 13 — Entrepreneur Profiles

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/entrepreneur/my-profile` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\EntrepreneurProfileController@myProfile` | 
| 2 | `PUT` | `/api/entrepreneur/profile/{id}` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\EntrepreneurProfileController@update` | 
| 3 | `POST` | `/api/entrepreneur/profile` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\EntrepreneurProfileController@store` | 
| 4 | `GET\|HEAD` | `/api/entrepreneur/profiles/export` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | `App\Http\Controllers\Api\EntrepreneurProfileController@export` | 
| 5 | `GET\|HEAD` | `/api/entrepreneur/profiles/public-stats` | Public | api, throttle:map-public | `App\Http\Controllers\Api\EntrepreneurProfileController@publicStats` | 
| 6 | `GET\|HEAD` | `/api/entrepreneur/profiles/stats` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | `App\Http\Controllers\Api\EntrepreneurProfileController@stats` | 
| 7 | `POST` | `/api/entrepreneur/profiles/{id}/review` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | `App\Http\Controllers\Api\EntrepreneurProfileController@review` | 
| 8 | `GET\|HEAD` | `/api/entrepreneur/profiles/{id}` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | `App\Http\Controllers\Api\EntrepreneurProfileController@show` | 
| 9 | `GET\|HEAD` | `/api/entrepreneur/profiles` | Bearer Token (Sanctum) | api, auth:sanctum, RoleOrPermissionMiddleware:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage | `App\Http\Controllers\Api\EntrepreneurProfileController@index` | 

### 14 — Syria Locations

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/locations/communities` | Public | api, throttle:60,1 | `App\Http\Controllers\Api\SyriaLocationController@communities` | 
| 2 | `GET\|HEAD` | `/api/locations/districts` | Public | api, throttle:60,1 | `App\Http\Controllers\Api\SyriaLocationController@districts` | 
| 3 | `GET\|HEAD` | `/api/locations/governorates` | Public | api, throttle:60,1 | `App\Http\Controllers\Api\SyriaLocationController@governorates` | 
| 4 | `GET\|HEAD` | `/api/locations/map` | Public | api, throttle:60,1 | `App\Http\Controllers\Api\SyriaLocationController@mapPoints` | 
| 5 | `GET\|HEAD` | `/api/locations/search` | Public | api, throttle:60,1 | `App\Http\Controllers\Api\SyriaLocationController@search` | 
| 6 | `GET\|HEAD` | `/api/locations/subdistricts` | Public | api, throttle:60,1 | `App\Http\Controllers\Api\SyriaLocationController@subdistricts` | 

### 15 — Other (electronic-signatures)

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/api/electronic-signatures/{id}/snapshot-image` | Bearer Token (Sanctum) | api, auth:sanctum | `App\Http\Controllers\Api\UserElectronicSignatureController@snapshotImage` | 

### 99 — Web (Print/Verify)

| # | Method | URI | Auth | Middleware | Controller |
|---|--------|-----|------|------------|------------|
| 1 | `GET\|HEAD` | `/certificates/verify` | Public | web, throttle:print-routes | `App\Http\Controllers\Api\CertificateController@verifyPage` | 
| 2 | `GET\|HEAD` | `/certificates/{certificate_code}/pdf` | Public | web, throttle:certificate-print-by-code | `App\Http\Controllers\CertificatePrintController@pdfByCode` | 
| 3 | `GET\|HEAD` | `/certificates/{certificate_code}/print` | Public | web, throttle:certificate-print-by-code | `App\Http\Controllers\CertificatePrintController@showByCode` | 
| 4 | `GET\|HEAD` | `/certificates/{certificate_code}/qr` | Public | web, throttle:certificate-print-by-code | `App\Http\Controllers\CertificatePrintController@publicQrImage` | 
| 5 | `GET\|HEAD` | `/certificates/{id}/pdf` | Signed URL (no token) | web, signed, throttle:print-routes | `App\Http\Controllers\CertificatePrintController@pdf` | 
| 6 | `GET\|HEAD` | `/certificates/{id}/print` | Signed URL (no token) | web, signed, throttle:print-routes | `App\Http\Controllers\CertificatePrintController@show` | 
| 7 | `GET\|HEAD` | `/sanctum/csrf-cookie` | Public | web | `Laravel\Sanctum\Http\Controllers\CsrfCookieController@show` | 
| 8 | `GET\|HEAD` | `/storage/{path}` | Public |  | `Closure` | 
| 9 | `PUT` | `/storage/{path}` | Public |  | `Closure` | 
| 10 | `GET\|HEAD` | `/trainees/{id}/card/pdf` | Signed URL (no token) | web, signed, throttle:print-routes | `App\Http\Controllers\TraineePrintController@pdf` | 
| 11 | `GET\|HEAD` | `/trainees/{id}/card` | Signed URL (no token) | web, signed, throttle:print-routes | `App\Http\Controllers\TraineePrintController@show` | 
| 12 | `GET\|HEAD` | `/trainers/{id}/card/pdf` | Signed URL (no token) | web, signed, throttle:print-routes | `App\Http\Controllers\TrainerPrintController@pdf` | 
| 13 | `GET\|HEAD` | `/trainers/{id}/card` | Signed URL (no token) | web, signed, throttle:print-routes | `App\Http\Controllers\TrainerPrintController@show` | 
| 14 | `GET\|HEAD` | `/training-centers/{id}/certificate/pdf` | Signed URL (no token) | web, signed, throttle:print-routes | `App\Http\Controllers\TrainingCenterPrintController@pdf` | 
| 15 | `GET\|HEAD` | `/training-centers/{id}/certificate` | Signed URL (no token) | web, signed, throttle:print-routes | `App\Http\Controllers\TrainingCenterPrintController@show` | 
| 16 | `GET\|HEAD` | `/verify-certificate/{certificate_code}` | Public | web, throttle:print-routes | `App\Http\Controllers\CertificatePrintController@publicView` | 

---

## 4. مسارات Web (طباعة وتحقق — خارج prefix /api)

هذه المسارات في `routes/web.php` — تُستخدم للطباعة PDF/HTML والتحقق العام.

| Method | URI | Auth | Middleware | Controller |
|--------|-----|------|------------|------------|
| GET | `/certificates/{id}/print` | Signed URL | signed, throttle | `CertificatePrintController@show` |
| GET | `/certificates/{id}/pdf` | Signed URL | signed, throttle | `CertificatePrintController@pdf` |
| GET | `/trainers/{id}/card` | Signed URL | signed, throttle | `TrainerPrintController@show` |
| GET | `/trainers/{id}/card/pdf` | Signed URL | signed, throttle | `TrainerPrintController@pdf` |
| GET | `/training-centers/{id}/certificate` | Signed URL | signed, throttle | `TrainingCenterPrintController@show` |
| GET | `/training-centers/{id}/certificate/pdf` | Signed URL | signed, throttle | `TrainingCenterPrintController@pdf` |
| GET | `/trainees/{id}/card` | Signed URL | signed, throttle | `TraineePrintController@show` |
| GET | `/trainees/{id}/card/pdf` | Signed URL | signed, throttle | `TraineePrintController@pdf` |
| GET | `/verify-certificate/{certificate_code}` | Public | throttle | `CertificatePrintController@publicView` |
| GET | `/certificates/verify` | Public | throttle | `CertificateController@verifyPage` |
| GET | `/certificates/{certificate_code}/print` | Public | throttle | `CertificatePrintController@showByCode` |
| GET | `/certificates/{certificate_code}/pdf` | Public | throttle | `CertificatePrintController@pdfByCode` |
| GET | `/certificates/{certificate_code}/qr` | Public | throttle | `CertificatePrintController@publicQrImage` |

> **Signed URL:** يتطلب query parameter `signature` صالحاً — يُولَّد من Laravel `URL::signedRoute()`.

---

## 5. Pagination & Filters (عام)

معظم endpoints القائمة (`index`) تدعم:

| Parameter | النوع | الوصف |
|-----------|-------|-------|
| `page` | integer | رقم الصفحة |
| `per_page` | integer | عدد العناصر (غالباً max 100) |
| `search` | string | بحث نصي (حيث مُطبَّق) |
| `status` | string | فلترة حسب الحالة |
| `branch_id` | integer | فلترة فرع (للأدوار الوطنية) |
| `governorate_id` | integer | فلترة محافظة |

---

## 6. account_type — أنواع التسجيل الذاتي

**المصدر:** `App\Support\SelfRegistrationCatalog::validationKeys()`

| account_type | الدور (role) | entity_type |
|--------------|--------------|-------------|
| `trainee` | trainee_user | trainee_user |
| `trainer` | trainer_user | trainer_user |
| `center` | center_user | center_user |
| `project_owner` | project_owner | project_owner |
| `incubation_applicant` | project_owner | project_owner |
| `entrepreneur_tech` | project_owner | project_owner |
| `entrepreneur` | project_owner (alias) | project_owner |
| `consultant` | consultant_office | consultant_office |
| `consulting_client` | trainee_user | consulting_client |
| `jobseeker` | trainee_user | job_seeker |
| `employer` | project_owner | project_owner |

---

## 7. ملاحظات دقة

1. **346 route** = API + Web + Health — الجدول في القسم 3 ي listing الكل من artisan.
2. **Policy إضافية:** بعض المسارات `auth:sanctum` فقط وتطبّق Policy داخل Controller (مثل `branches/{id}`, `registration-requests/*/show`).
3. **Rate limits:** register, login, map-public, admin-access, file-upload, certificate-verify — مُعرَّفة في `bootstrap/app.php` أو RouteServiceProvider.
4. **Frontend base:** `https://smeda.gov.sy/api/api` — `front/assets/js/core/config.js`.

---

*نهاية API Documentation v2.0 — مُولَّد من `docs/generate_api_doc.php`*

</div>