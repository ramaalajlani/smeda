# تقرير دمج منصة GIS داخل منصة الهيئة Laravel

**التاريخ:** 16 يونيو 2026  
**النطاق:** دمج GIS / Needs Map / Citizen & State Needs كوحدة رسمية داخل `api2`  
**الحالة:** مكتمل — Backend + API + Frontend + اختبارات

---

## 1. الهدف

دمج منصة GIS PHP Native (Needs Map) داخل منصة الهيئة Laravel الموحّدة، بحيث:

- تستخدم نفس `users` والأدوار والصلاحيات.
- تتشارك `governorates` و `branches` والـ Dashboard.
- لا يوجد login أو PDO أو `config.php` منفصل لـ GIS.
- تبقى بيانات `gis_survey_responses` محفوظة وتُستورد عبر Artisan.

---

## 2. ما تم تحليله من GIS القديم

| الملف | الوظيفة |
|-------|---------|
| `auth.php` | أدوار + عزل محافظة |
| `need_action.php` | مسار: تدقيق → موافقة فرع → تصنيف |
| `map.php` / `dashboard.php` | خريطة ومؤشرات |
| `gis_survey_responses` | جدول الاحتياجات الأساسي |

**حالات العمل (Legacy → Slug):**

| عربي | Slug |
|------|------|
| بانتظار تدقيق بيانات المحافظة | `pending_governorate_review` |
| معاد للتعديل | `returned_for_edit` |
| بانتظار موافقة مدير الفرع | `pending_branch_approval` |
| موافق عليه | `approved` |
| مرفوض | `rejected` |
| مصنف | `classified` |

---

## 3. البنية الجديدة

```
منصة الهيئة Laravel
├── users / roles / permissions (موحّد)
├── training platform (لم يُمس)
├── finance platform (لم يُمس)
└── GIS / Needs Module
    ├── needs, need_action_logs, need_lookups, administrative_units
    ├── Services: Workflow, Dashboard, Export, Sync, Import
    ├── NeedPolicy + NeedDataScope
    ├── API /api/needs/*
    └── Frontend front/services/gis/*
```

---

## 4. قاعدة البيانات

**Migration:** `2026_06_23_100000_create_needs_module_tables.php`

- `needs` — السجل الرئيسي (citizen/state، موقع، SYRSIC، workflow).
- `need_action_logs` — سجل إجراءات كل احتياج.
- `need_lookups` — قوائm مرجعية.
- `administrative_units` — الوحدات الإدارية.

**لم يُحذف:** `gis_survey_responses` أو أي جدول legacy.

---

## 5. Models & Support

| Class | الدور |
|-------|-------|
| `Need` | النموذج الرئيسي + علاقات |
| `NeedActionLog` | تتبع الإجراءات |
| `NeedLookup` | Lookups |
| `AdministrativeUnit` | الوحدات الإدارية |
| `NeedStatus` | ثوابت + `LEGACY_MAP` |
| `NeedDataScope` | عزل وطني/فرع + IDOR |

---

## 6. Services

| Service | الوظيفة |
|---------|---------|
| `NeedWorkflowService` | create/update/review/approve/reject/return/classify/resolve |
| `NeedDashboardService` | KPIs + map points + filters |
| `NeedExportService` | CSV export |
| `NeedSyncService` | ربط training/finance (جاهز للتوسع) |
| `NeedCodeGenerator` | `NEED-YYYYMMDD-####` |

---

## 7. API Endpoints

كل المسارات تحت `auth:sanctum` في `routes/api.php`:

| Method | Path |
|--------|------|
| GET/POST | `/api/needs` |
| GET/PUT | `/api/needs/{id}` |
| POST | `/api/needs/{id}/review\|approve\|reject\|return\|classify\|resolve` |
| GET | `/api/needs/map\|dashboard\|lookups\|admin-units\|export` |

**Controller:** `App\Http\Controllers\Api\NeedController`

---

## 8. الصلاحيات والأدوار

صلاحيات `needs.*` في `RolePermissionSeeder`:

- `needs.view`, `needs.view_all`, `needs.view_branch`
- `needs.create`, `needs.create_citizen`, `needs.create_state`
- `needs.review`, `needs.approve`, `needs.reject`, `needs.return`
- `needs.classify`, `needs.resolve`, `needs.export`
- `needs.dashboard`, `needs.map`
- `needs.manage_lookups`, `needs.manage_admin_units`

**أدوار GIS:**

| Role | الصلاحيات |
|------|-----------|
| `data_entry` | إنشاء + تعديل فرع |
| `data_reviewer` | تدقيق + إعادة |
| `branch_manager` | موافقة/رفض فرع |
| `project_services_manager` | تصنيف وطني |
| `development_manager` | احتياجات دولة + تصنيف |
| `auditor` | قراءة + dashboard + map |
| `general_director` / `admin` | كامل |
| `deputy_general_director` | عرض وطني + dashboard + export |

---

## 9. Policy & AppServiceProvider

- `NeedPolicy` مسجّلة في `AppServiceProvider`.
- `AuditActionModule`: أي `need_*` → module `needs`.
- Activity Log يُسجّل `need_created`, `need_reviewed`, إلخ.

---

## 10. استيراد Legacy

```bash
php artisan needs:import-legacy-gis
```

- يقرأ `gis_survey_responses` إن وُجد.
- يتخطى المكرر عبر `source_platform=legacy_gis` + `source_record_id`.
- يربط المحافظة بـ `governorates.name_ar`.
- يربط `gis_users.email` → `users.email` لـ `created_by` إن أمكن.

---

## 11. Frontend

**المسار:** `front/services/gis/`

| صفحة | JS |
|------|-----|
| `needs-list.php` | `needs-list.js` |
| `needs-dashboard.php` | `needs-dashboard.js` |
| `needs-map.php` (Leaflet) | `needs-map.js` |
| `need-create.php` | `need-create.js` |
| `need-edit.php` | `need-edit.js` |
| `need-view.php` | `need-view.js` |
| `needs-lookups.php` | `needs-lookups.js` |
| `admin-units.php` | `admin-units.js` |

**مساعد:** `needs-platform.js`  
**Routes:** `routes.js` — مجموعة `needs*`  
**Sidebar:** قسم «خريطة الاحتياجات GIS» في `dashboard.php`

---

## 12. الاختبارات

**ملف:** `tests/Feature/NeedsModuleTest.php` — **18 سيناريو**

- عزل وطني/فرع + IDOR
- إنشاء data_entry + منع auditor
- workflow: review → approve → classify
- map / dashboard / export / lookups
- import بدون تكرار + audit log

---

## 13. ما لم يُمس (حسب المطلوب)

- منصة التدريب والشهادات
- منصة التمويل
- كود GIS القديم في `map/gis/` (لم يُحذف)
- `gis_survey_responses` (لم يُحذف)

---

## 14. أوامر التشغيل

```bash
cd api2
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder   # إن لزم
php artisan needs:import-legacy-gis                # مرة واحدة على DB فيها legacy
php artisan test --filter=NeedsModuleTest
php artisan test                                   # المجموعة الكاملة
```

---

## 15. مخاطر / ملاحظات

1. **Import:** شغّله مرة على كل بيئة؛ التكرار آمن (skip duplicates).
2. **الخريطة:** Leaflet من CDN — يحتاج اتصال إنترنت للعرض.
3. **الأدوار الجديدة** (`data_entry`, `data_reviewer`, …) تحتاج مستخدمين في `UserSeeder` أو Admin UI.
4. **`NeedSyncService`:** جاهز لربط training/finance لاحقاً عند الحاجة.

---

## 16. قائمة الملفات الرئيسية المضافة/المعدّلة

**Backend:** migrations, models, policies, services, controller, command, seeder, routes, AppServiceProvider, AuditActionModule  
**Frontend:** 8 صفحات PHP + 9 ملفات JS + sidebar + routes.js  
**Tests:** NeedsModuleTest.php  
**Docs:** هذا التقرير

---

## 17. الخلاصة

تم دمج GIS كوحدة Laravel رسمية ضمن نفس المنصة:

- مصادقة وصلاحيات موحّدة
- API كامل + workflow + export + map
- واجهات Frontend مربوطة بـ `/api/needs`
- استيراد legacy آمن بدون تكرار
- 18 اختبار Feature للوحدة

**الخطوة التالية المقترحة للإنتاج:** `migrate` → `needs:import-legacy-gis` → إنشاء مستخدمي GIS بالأدوار المناسبة → اختبار يدوي للخريطة والـ sidebar.
