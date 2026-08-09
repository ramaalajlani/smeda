# حزمة تسليم التيستنج — SMEDA API QA

موجهة لفريق Testing في الشركة.

## ماذا تتضمن الحزمة؟

| المكوّن | المسار | الفائدة |
|---------|--------|---------|
| Postman Collection | `postman/SMEDA-API.postman_collection.json` | كل الـ 368 API + مجلد لكل Actor |
| Environment محلي معبّأ | `postman/SMEDA-Local.postman_environment.json` | إيميلات + كلمات سر + توكنات |
| تقارير QA الآلية | `postman/qa-reports/` | نتائج Login + صلاحيات + سيناريوهات |
| سكربت التشغيل | `api2/tests/qa/run_qa_suite.php` | إعادة الاختبار بنقرة |
| تغطية المسارات | `postman/API-COVERAGE.md` | جدول كل Endpoint |

## تشغيل الاختبار الآلي

المتطلبات: MySQL شغّال + `php artisan serve` على `127.0.0.1:8000` + الحسابات من `UserSeeder`.

```bash
cd api2
php artisan db:seed --class=UserSeeder --force
php artisan serve --host=127.0.0.1 --port=8000
```

في طرفية ثانية:

```bash
cd api2
php tests/qa/run_qa_suite.php
```

المخرجات:
- `postman/qa-reports/QA-REPORT.html` ← افتحه بالمتصفح للعرض على الإدارة
- `postman/qa-reports/QA-REPORT.md`
- `postman/qa-reports/QA-REPORT.json` ← خام للتفاصيل

تحديث التوكنات في Postman:

```bash
php tests/fill_postman_local_env.php
```

## ماذا يغطي السكربت؟

### Phase 1 — Login (32 دور)
تسجيل دخول لكل حساب تجريبي + التحقق من التوكن.

### Phase 2 — مصفوفة صلاحيات (Allow / Deny)
Endpoints حرجة × كل الأدوار:

- `/api/me`
- `/api/dashboard`
- `/api/admin/users`
- Needs: list / create / dashboard
- Finance: applications list/create + central-bank dashboard
- Training courses list
- Certificates list + verify عام
- News public/manage
- Incubation stats
- Workforce jobs

لكل خلية: **Allow متوقع** أو **Deny متوقع** ومقارنة مع HTTP الفعلي  
(Allow = 200/201/204/404/422 — يعني تجاوز المصادقة/الصلاحية على الراوت)  
(Deny = 401/403)

### Phase 3 — سيناريوهات أعمال
1. إنشاء احتياج (`data_entry`) + عرضه (`data_reviewer`)
2. إنشاء طلب تمويل (`project_owner`) + عرضه (`finance_manager`)
3. قائمة الدورات (training_manager / center / trainee)
4. قائمة الشهادات + تحقق عام
5. رفض متعمد: trainee → admin users ، data_entry → central-bank dashboard

## حسابات تجريبية (محلي فقط)

كلمة المرور لجميع الحسابات التجريبية: `12345678`

أمثلة:
- `admin@system.com`
- `data-entry.damascus@system.com`
- `project.owner@system.com`
- `finance.manager@system.com`
- `central.bank@system.com`
- `manager@system.com` (training_manager)
- `center@system.com`

القائمة الكاملة في `ACCOUNT-SMOKE-REPORT.md` و Environment.

## ماذا يختبر فريق التيستنج يدوياً في Postman؟

بعد استيراد Collection + Local Environment:

1. اختيار Actor → `00 - Login as …` (أو استخدام التوكن المعبّأ)
2. سيناريو Need من مجلد Data Entry / PSM
3. سيناريو Finance من Project Owner ثم Finance Manager
4. Courses / Certificates من Training Manager و Center
5. التأكد أن الأدوار الضعيفة ترجع **403** على Admin/Finance الحساسة

## حدود الحزمة (مهم للإدارة)

- ليست تغطية 100% لكل 368 مسار × 32 دور (هذا آلاف الحالات).
- المصفوفة تغطي **أهم المسارات الأمنية والوظيفية**.
- إصدار شهادة كاملة (issue + موافقات متعددة) يعتمد على بيانات دورة/متدرب موجودة؛ السكربت يختبر القائمة والتحقق العام.
- بعض الـ 422 تعني «الصلاحية سمحت لكن البيانات ناقصة» وتُحسب Allow (مقصود).

## معيار القبول المقترح للتسليم

| البند | المعيار |
|------|---------|
| Login | 32/32 PASS |
| سيناريوهات S1–S5 | كلها PASS |
| مصفوفة الصلاحيات | Failures = 0 أو موثّقة كـ known issues |
| Postman | قابل للاستيراد والتشغيل على Local |

### آخر تشغيل مرجعي

انظر `qa-reports/QA-REPORT.md` بعد آخر تشغيل محلي.

Known issue موثّق في الكود: middleware لـ finance applications يذكر `governor` و`system_admin` لكن Policy/Controller قد ترجع 403 — المصفوفة تتبع السلوك الفعلي.

## إعادة توليد التقارير قبل اجتماع التيستنج

```bash
cd api2
php tests/qa/run_qa_suite.php
php tests/fill_postman_local_env.php
```

ثم أرسل مجلد:
- `postman/SMEDA-API.postman_collection.json`
- `postman/SMEDA-Local.postman_environment.json`
- `postman/qa-reports/`
- `postman/QA-HANDOFF.md` (هذا الملف)
