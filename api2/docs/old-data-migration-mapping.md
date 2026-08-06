# خطة Mapping — نقل البيانات القديمة

> **مهم:** لا تنفّذ النقل النهائي قبل مراجعة هذا الملف وتشغيل `--dry-run`.

## 1. مصادر البيانات

| الملف | قاعدة مؤقتة | الوصف |
|-------|-------------|--------|
| `u142331648_authority3 (2).sql` | `old_authority3` | منصة التدريب القديمة (Laravel) |
| `u142331648_entrep_db.sql` | `old_entrep` | منصة رواد الأعمال / الاستبيانات |

## 2. جداول authority3 القديمة (35 جدول)

```
cache, cache_locks, certificates, certificate_approvals,
course_registration_requests, course_registration_request_members,
failed_jobs, jobs, job_batches, migrations, model_has_permissions,
model_has_roles, password_reset_tokens, permissions, personal_access_tokens,
roles, role_has_permissions, sessions, trainees, trainee_registration_requests,
trainers, trainer_profiles, trainer_registration_requests, trainer_training_kit,
training_centers, training_center_platforms, training_center_registration_requests,
training_courses, training_course_trainee, training_kits, training_kit_nominations,
training_programs, training_program_training_kit, users, workforces
```

### بيانات حقيقية تقريباً
- **users:** ~67 (بما فيها seed + مستخدمين حقيقيين)
- **certificates, training_*, registration_***: بيانات إنتاج
- **لا يوجد:** governorates, branches, finance, needs (مضافة في النظام الجديد)

## 3. جداول entrep_db القديمة (8 جداول)

```
communities, community_members, community_messages, community_reads,
entrepreneur_messages, entrepreneur_surveys, entrepreneur_survey_files,
login_attempts, users
```

### بيانات حقيقية
- **users:** آلاف رواد أعمال
- **entrepreneur_surveys:** مئات الاستبيانات (JSON غني)
- **entrepreneur_survey_files:** آلاف المرفقات

---

## 4. مقارنة: القديم ↔ الجديد

### 4.1 جداول متطابقة (authority3 → authority3 الجديد)

| القديم | الجديد | ملاحظة |
|--------|--------|--------|
| users | users | + phone, governorate_id, branch_id |
| training_centers | training_centers | + governorate_id, branch_id |
| trainers | trainers | + governorate_id, branch_id |
| trainees | trainees | + governorate_id, branch_id |
| training_kits | training_kits | متطابق |
| training_programs | training_programs | متطابق |
| training_courses | training_courses | + governorate_id, branch_id |
| certificates | certificates | + certificate_code, security_hash |
| *_registration_requests | *_registration_requests | + governorate_id, branch_id |
| roles/permissions/* | roles/permissions/* | Spatie — دمج بدون تكرار |

### 4.2 جداول بأسماء/بنية مختلفة (entrep → جديد)

| القديم | الجديد | Mapping |
|--------|--------|---------|
| entrep.users.full_name | users.name | |
| entrep.users.phone | users.phone | |
| entrep.users.role | model_has_roles | admin→admin, entrepreneur→trainee_user |
| entrep.users.governorate | users.governorate_id | مطابقة بالاسم العربي |
| entrepreneur_surveys | funding_applications | transform |
| entrepreneur_surveys.data_json | funding_application_details.notes | |
| entrepreneur_surveys.project_* | funding_applications.* | |
| entrepreneur_survey_files | funding_documents | file_path كما هو |
| entrepreneur_surveys | needs | نسخة GIS للمشاريع |

### 4.3 أعمدة جديدة مطلوبة — قيم افتراضية

| العمود | القيمة الافتراضية |
|--------|-------------------|
| governorate_id | مطابقة من اسم المحافظة أو GOV-DAMASCUS |
| branch_id | فرع المحافظة أو BR-DAMASCUS |
| created_by | 1 (System Admin) |
| is_active | 1 |
| status (funding) | submitted |
| source_platform (needs) | finance |

### 4.4 جداول غير مطابقة (unmapped)

| الجدول | التوصية |
|--------|---------|
| communities | أرشفة — لا API في النظام الجديد |
| community_messages | أرشفة |
| entrepreneur_messages | أرشفة |
| login_attempts | تجاهل |
| cache/jobs/sessions | تجاهل |

---

## 5. Mapping تفصيلي للأعمدة

### users (authority3)
```
old.id                    → ID mapping (لا يُستخدم مباشرة)
old.name                  → users.name
old.email                 → users.email (dedupe)
old.password              → users.password (bcrypt $2y$12 — متوافق)
old.entity_type           → users.entity_type
old.training_center_id    → users.training_center_id (via ID map)
old.trainer_id            → users.trainer_id (via ID map)
old.trainee_id            → users.trainee_id (via ID map)
old.parent_user_id        → users.parent_user_id (via ID map)
old.is_active             → users.is_active
```

### users (entrep)
```
old.full_name   → users.name
old.email       → users.email (dedupe — قد يتقاطع مع authority3)
old.phone       → users.phone (dedupe ثانوي)
old.password    → users.password ($2y$10 — متوافق مع Laravel)
old.role        → Spatie role
old.governorate → users.governorate_id (by name_ar)
(default)       → entity_type = entrepreneur
```

### entrepreneur_surveys → funding_applications
```
old.user_id         → applicant_user_id (ID map)
old.full_name       → applicant_name
old.phone           → phone
old.email           → email
old.governorate     → governorate_id + branch_id
old.project_name    → project_name
old.project_type    → project_size (micro/small)
old.sector          → project_sector
old.project_stage   → business_stage (idea/startup/existing)
old.direct_jobs     → funding_application_details.employees_count
old.data_json       → funding_application_details.notes
(generated)         → application_number = FA-LEG-{id}
(data_json.finance) → requested_amount
```

### entrepreneur_survey_files → funding_documents
```
old.survey_id      → funding_application_id (ID map)
old.field_name     → document_type
old.original_name  → original_name (بدون تغيير)
old.file_path      → file_path (بدون تغيير)
old.mime_type      → mime_type
old.file_size      → size
(default)          → uploaded_by = 1
```

### certificates (authority3 → new)
```
old.*               → نفس الأعمدة
old.verification_code → certificate_code (إذا فارغ)
(default)           → security_hash = null
(default)           → governorate_id, branch_id من المركز
```

---

## 6. ترتيب النقل

1. **مطابقة** governorates/branches (موجودة من seed — لا تُستبدل)
2. **users** authority3 (تخطي seed emails)
3. **users** entrep (dedupe email)
4. **training_centers** → platforms → trainers → profiles → trainees
5. **kits, programs, courses, pivots, workforces**
6. **registration requests**
7. **certificates + approvals**
8. **entrepreneur_surveys** → funding_applications + details + needs
9. **entrepreneur_survey_files** → funding_documents
10. **model_has_roles** (authority3)

---

## 7. تجنب التكرار

| الكيان | مفتاح المطابقة |
|--------|----------------|
| users | email → phone |
| training_centers | code |
| trainers | trainer_code |
| trainees | trainee_code, national_id |
| certificates | certificate_number |
| funding_applications | application_number |
| funding_documents | application_id + original_name + document_type |

---

## 8. كلمات المرور

- **authority3:** bcrypt `$2y$12` — تُنقل كما هي ✅
- **entrep:** bcrypt `$2y$10` — تُنقل كما هي ✅
- إذا hash غير معروف — **لا** نُولّد كلمة عشوائية — يُذكر في التقرير لاستخدام reset password

---

## 9. المرفقات

- `file_path` و `original_name` تُحفظ كما هي
- إذا الملف غير موجود على القرص → يُسجّل في `missing_files` بالتقرير

---

## 10. أوامر التشغيل

```bash
# 1. Backup القاعدة الجديدة
mysqldump -u root authority3 > backup_authority3_before_import.sql

# 2. استيراد SQL القديم إلى قواعد مؤقتة
mysql -u root -e "CREATE DATABASE IF NOT EXISTS old_authority3;"
mysql -u root -e "CREATE DATABASE IF NOT EXISTS old_entrep;"
mysql -u root old_authority3 < "u142331648_authority3 (2).sql"
mysql -u root old_entrep < u142331648_entrep_db.sql

# 3. Migration جداول التتبع
php artisan migrate

# 4. تحليل SQL فقط (بدون DB)
php artisan old-data:import --analyze-sql

# 5. Dry run
php artisan old-data:import --dry-run

# 6. النقل الفعلي (بعد المراجعة)
php artisan old-data:import --backup
```

---

## 11. التحقق بعد النقل

- [ ] `SELECT COUNT(*) FROM users` — مقارنة مع القديم
- [ ] تسجيل دخول مستخدم authority3 قديم
- [ ] تسجيل دخول رائد أعمال entrep
- [ ] عرض شهادات + دورات
- [ ] عرض طلبات التمويل + المرفقات
- [ ] `legacy_import_id_mappings` — تحقق من ID maps
