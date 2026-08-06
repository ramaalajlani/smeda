# مدير المنصة والهرمية الوطنية

## الأدوار الوطنية

| الدور التقني | الاسم العربي | النطاق |
|--------------|-------------|--------|
| `general_director` | المدير العام | وصول وطني كامل |
| `admin` / `super_admin` | مدير نظام / مدير نظام كامل | توافق مع المدير العام |
| `deputy_general_director` | نائب المدير العام | قراءة/اعتماد وطني محدود |
| `branch_manager` | مدير فرع | مقيد بـ `branch_id` |
| `center_user` | مستخدم مركز | مقيد بمركز/فرع |
| `trainer_user` | مدرب | دوراته فقط |
| `trainee_user` | متدرب | شهاداته وطلباته |
| `auditor` | مدقق | قراءة فقط |
| `system_admin` | مدير نظام (صلاحيات) | إدارة المستخدمين والأدوار فقط |

> **ملاحظة:** الأسماء التقنية في قاعدة البيانات لا تُغيّر. الواجهة تعرض الاسم العربي عبر `APP_HELPERS.roleLabel()` (JS) و `App\Support\RoleLabel` (PHP).

راجع التفاصيل الكاملة في [national-branch-hierarchy.md](./national-branch-hierarchy.md).

## Backend

- `AccessControlGuard::NATIONAL_ADMIN_ROLES` = `['general_director', 'admin', 'super_admin']`
- `BranchDataScope` + `TrainingDataScope` — عزل الفروع لمدير الفرع
- `NationalDashboardService` — إحصائيات وطنية وفرعية

## Frontend

- `AppAuth.isNationalAdmin()` — المدير العام
- `AppAuth.isBranchManager()` — مدير الفرع
- سايدبار: `data-national-admin`, `data-branch-manager-only`

## حسابات التطوير

- `general@system.com` / `12345678` — المدير العام
- `admin@system.com` / `12345678` — admin + general_director
- `branch.damascus@system.com` / `12345678` — مدير فرع دمشق
- `branch.aleppo@system.com` / `12345678` — مدير فرع حلب

## الاختبارات

- `BranchIsolationTest.php` — عزل الفروع
- `PlatformAdminAccessTest.php` — صلاحيات المدير الوطني
