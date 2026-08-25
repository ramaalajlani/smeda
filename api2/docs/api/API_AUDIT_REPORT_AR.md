# تقرير تدقيق API

> تاريخ: 2026-08-16 05:48:03

## إحصائيات

| المؤشر | العدد |
|--------|------:|
| total_endpoints | 396 |
| total_routes_raw | 396 |
| api_endpoints | 379 |
| web_endpoints | 17 |
| public_endpoints | 38 |
| protected_endpoints | 350 |
| signed_endpoints | 8 |
| controllers | 63 |
| form_requests | 28 |
| resources | 25 |
| policies | 27 |

### حسب HTTP Method
- **GET:** 200
- **POST:** 135
- **PUT:** 32
- **DELETE:** 20
- **PATCH:** 9

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
- **Maps:** 4
- **Needs GIS:** 25
- **News:** 6
- **Notifications:** 5
- **Other Routes:** 151
- **Printing:** 4
- **Program Bank:** 18
- **Public APIs:** 8
- **Signatures:** 1
- **Success Stories:** 7
- **Trainees:** 5
- **Trainers:** 5
- **Training Centers:** 2
- **Training Courses:** 25
- **Training Kit Nominations:** 4
- **Training Kits:** 9
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
| Informational | App\Http\Controllers\Api\AiChatController | - | POST api/ai/chat محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | POST api/ai/chat/continue محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | POST api/ai/chat/reset محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | POST api/ai/isic4/classify محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | GET api/ai/config محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | GET api/ai/chat/history محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | GET api/ai/chat/history/{session}/messages محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | POST api/ai/chat/history/{session}/resume محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | GET api/ai/knowledge/departments محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
| Informational | App\Http\Controllers\Api\AiChatController | - | GET api/ai/knowledge/{department} محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج | التحقق يدوياً من Policy داخل Controller |
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

> إجمالي مسارات auth:sanctum فقط (معلوماتي): ~39

## Sanctum Token

- انتهاء افتراضي: 480 دقيقة (`config/sanctum.php`)
- Logout يحذف التوكن الحالي فقط (`AuthController::logout`)

## ملاحظات إضافية

- ازدواجية تسمية: `consulting_offices` (marketplace) مقابل `consultant_offices` (finance)
- `routes/api.php` يحتوي TODO لتقسيم الملف إلى ملفات فرعية
- Responses غير موحدة: بعض Controllers تُعيد paginator مباشرة وبعضها `{ message, data }`

## مراجعة الحماية — ملخص

- مسارات في جدول المراجعة: 354
- مسارات تحتاج مراجعة بشرية: 31

راجع `API_DOCUMENTATION_AR.md` → [مراجعة الحماية](#security-review) للجدول الكامل.