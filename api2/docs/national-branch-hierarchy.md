# النظام الهرمي الوطني — 14 محافظة سورية

## الأدوار والنطاق

| الدور | الكود | النطاق |
|-------|-------|--------|
| المدير العام | `general_director` | `FULL_PLATFORM_ACCESS` — كل المحافظات والفروع |
| مدير المنصة (توافق) | `admin` / `super_admin` | مكافئ للمدير العام |
| نائب المدير العام | `deputy_general_director` | قراءة وطنية + اعتماد محدود |
| مدير الفرع | `branch_manager` | `branch_id` + `governorate_id` فقط |

## المحافظات (14)

دمشق، ريف دمشق، حلب، حمص، حماة، اللاذقية، طرطوس، إدلب، درعا، السويداء، القنيطرة، دير الزور، الرقة، الحسكة.

كل محافظة لها فرع (`branches.code` مثل `BR-DAMASCUS`, `BR-ALEPPO`).

## الجداول والحقول

### جداول جديدة
- `governorates` — id, name_ar, name_en, code, is_active
- `branches` — id, governorate_id, name, code, is_active, manager_user_id, notes

### حقول جديدة
- `users`: governorate_id, branch_id
- `training_centers`, `trainers`, `trainees`, `training_courses`, `certificates`: governorate_id, branch_id

## آلية ربط المستخدم

```text
general_director  → governorate_id = null, branch_id = null
branch_manager    → governorate_id + branch_id (فرع المحافظة)
```

يُعيَّن عبر Seeder أو API إدارة المستخدمين (`UserAccessService`).

## عزل البيانات

- `BranchDataScope::applyBranchScope()` — يفلتر بـ `branch_id` لمدير الفرع
- `TrainingDataScope` — يطبّق عزل الفرع أولاً على: centers, trainers, trainees, courses, certificates
- المدير العام / النائب / المدقق / مدير التدريب — يتجاوزون العزل

## Routes

| Method | Path | الوصف |
|--------|------|-------|
| GET | `/api/governorates` | قائمة المحافظات |
| GET | `/api/branches` | قائمة الفروع (مدير الفرع يرى فرعه فقط) |
| GET | `/api/branches/{id}` | تفاصيل فرع (403 لفرع آخر) |
| GET | `/api/dashboard` | لوحة وطنية / فرع / حسب الدور |

## حسابات الاختبار

| البريد | كلمة المرور | الدور |
|--------|-------------|-------|
| `general@system.com` | `12345678` | general_director |
| `admin@system.com` | `12345678` | admin + general_director |
| `branch.damascus@system.com` | `12345678` | branch_manager (دمشق) |
| `branch.aleppo@system.com` | `12345678` | branch_manager (حلب) |
| `deputy@system.com` | `12345678` | deputy_general_director |

## اختبار المدير العام

```bash
php artisan test --filter=BranchIsolationTest
```

1. تسجيل الدخول: `POST /api/login` → `general@system.com`
2. `GET /api/governorates` → 14 محافظة
3. `GET /api/branches` → 14 فرع
4. `GET /api/dashboard` → `governorates_count`, `branches_count`, `governorate_stats`

## اختبار مدير الفرع

1. تسجيل الدخول: `branch.aleppo@system.com`
2. `GET /api/trainers` → مدربو حلب فقط
3. `GET /api/branches/{damascus_id}` → 403
4. `GET /api/dashboard` → `branch_id`, `branch_name`, إحصائيات الفرع

## الملفات الرئيسية

**Backend:** `BranchDataScope.php`, `TrainingDataScope.php`, `AccessControlGuard.php`, `NationalDashboardService.php`, `GovernorateController.php`, `BranchController.php`, `DashboardController.php`

**Models:** `Governorate.php`, `Branch.php` + حقول scope على نماذج التدريب

**Seeders:** `GovernorateBranchSeeder.php`, `BranchDataBackfillSeeder.php`, `RolePermissionSeeder.php`, `UserSeeder.php`

**Frontend:** `auth.js`, `access-control.js`, `admin-branches.php`, `dashboard.php` (سايدبار حسب الدور)

**Tests:** `BranchIsolationTest.php`, `PlatformAdminAccessTest.php`

## ما لم يُنفَّذ بعد

- واجهات الاتفاقيات (`manage_agreements`) والمالية (`view_finance`)
- UI تعيين branch/governorate عند إنشاء مستخدم
- عزل طلبات التسجيل حسب الفرع
