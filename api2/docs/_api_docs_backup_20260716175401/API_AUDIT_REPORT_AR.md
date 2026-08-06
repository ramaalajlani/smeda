# تقرير تدقيق API

> تاريخ: 2026-07-16 17:47:46

## إحصائيات

| المؤشر | العدد |
|--------|------:|
| total_endpoints | 346 |
| total_routes_raw | 346 |
| api_endpoints | 329 |
| web_endpoints | 17 |
| public_endpoints | 38 |
| protected_endpoints | 300 |
| signed_endpoints | 8 |
| controllers | 57 |
| form_requests | 28 |
| resources | 24 |
| policies | 26 |

### حسب HTTP Method
- **GET:** 185
- **POST:** 114
- **PUT:** 26
- **DELETE:** 15
- **PATCH:** 6

### حسب الوحدة
- **Admin:** 34
- **Agreements:** 5
- **Authentication:** 3
- **Branches:** 6
- **Certificate Verification:** 10
- **Certificates:** 7
- **Dashboard:** 1
- **Entrepreneur Profiles:** 9
- **Governorates:** 1
- **Health Check:** 1
- **Inbox:** 8
- **Incubators:** 6
- **Maps:** 3
- **Needs GIS:** 18
- **News:** 6
- **Notifications:** 5
- **Other Routes:** 138
- **Printing:** 4
- **Program Bank:** 18
- **Public APIs:** 8
- **Signatures:** 1
- **Success Stories:** 7
- **Trainees:** 2
- **Trainers:** 2
- **Training Centers:** 2
- **Training Courses:** 9
- **Training Kit Nominations:** 4
- **Training Kits:** 2
- **Training Programs:** 2
- **Training Requests:** 1
- **Training Supervisors:** 1
- **User Profile:** 9
- **Web (Print/Verify/Files):** 3
- **Workforce:** 10

## Production URL — `/api/api`

- **الحالة:** مُثبت من `front/assets/js/core/config.js` و `deploy/hostinger/public_html/config.php`
- **السبب:** مجلد النشر `public_html/api/` + بادئة Laravel `api/`
- **التوصية:** توثيق الرابط للمطورين؛ لا تغيير دون تنسيق Frontend و Hostinger

## المشكلات المكتشفة

| الخطورة | الملف | السطر | المشكلة | التوصية |
|---------|-------|------:|---------|---------|
| Informational | App\Http\Controllers\Api\UserElectronicSignatureController | - | GET api/electronic-signatures/{id}/snapshot-image محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AuthController | - | POST api/logout محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\DashboardController | - | GET api/dashboard محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\GovernorateController | - | GET api/governorates محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\FundingConsultantController | - | GET api/finance/consultant-office/dashboard محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\FundingConsultantController | - | GET api/finance/my-consultant-assignments محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\FundingPartnerController | - | GET api/finance/funding-partner/dashboard محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\FundingPartnerController | - | GET api/finance/my-partner-assignments محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\NeedController | - | GET api/needs/analytics محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\NeedController | - | GET api/needs/workspace/reviewer محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\NotificationController | - | GET api/notifications/summary محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\NotificationController | - | GET api/notifications محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\NotificationController | - | POST api/notifications/read-all محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\NotificationController | - | POST api/notifications/{id}/read محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\NotificationController | - | DELETE api/notifications/{id} محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\InboxController | - | GET api/inbox/unread-count محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\InboxController | - | GET api/inbox/users-list محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\InboxController | - | GET api/inbox/sent محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\InboxController | - | GET api/inbox محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\InboxController | - | POST api/inbox محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\InboxController | - | GET api/inbox/{id} محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\InboxController | - | POST api/inbox/{id}/reply محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\InboxController | - | DELETE api/inbox/{id} محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\IncubatorController | - | POST api/incubation/apply محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\IncubatorController | - | GET api/incubation/my-applications محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\IncubatorController | - | GET api/incubation/my-project محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\EntrepreneurProfileController | - | GET api/entrepreneur/my-profile محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\EntrepreneurProfileController | - | POST api/entrepreneur/profile محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\EntrepreneurProfileController | - | PUT api/entrepreneur/profile/{id} محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |

> إجمالي مسارات auth:sanctum فقط (معلوماتي): ~29

## Sanctum Token

- انتهاء افتراضي: 480 دقيقة (`config/sanctum.php`)
- Logout يحذف التوكن الحالي فقط (`AuthController::logout`)

## ملاحظات إضافية

- ازدواجية تسمية: `consulting_offices` (marketplace) مقابل `consultant_offices` (finance)
- `routes/api.php` يحتوي TODO لتقسيم الملف إلى ملفات فرعية
- Responses غير موحدة: بعض Controllers تُعيد paginator مباشرة وبعضها `{ message, data }`

## مراجعة الحماية — ملخص

- مسارات في جدول المراجعة: 304
- مسارات تحتاج مراجعة بشرية: 21

راجع `API_DOCUMENTATION_AR.md` → [مراجعة الحماية](#security-review) للجدول الكامل.