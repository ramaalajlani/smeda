# نشر SMEDA على Hostinger — smeda.gov.sy

## هيكل الملفات على السيرفر

```
public_html/                    ← https://smeda.gov.sy
├── index.php                   ← من مجلد front/
├── login.php
├── config.php                  ← من deploy/hostinger/public_html/config.php
├── assets/
├── services/
├── .htaccess                   ← من deploy/hostinger/public_html/.htaccess
│
├── api/                        ← مدخل Laravel (موجود عندك)
│   ├── index.php               ← deploy/hostinger/public_html/api/index.php
│   └── .htaccess
│
└── api2/                       ← نواة Laravel (كامل المشروع بدون front/)
    ├── .env                    ← من env.production.template
    ├── .htaccess               ← من api2.htaccess.deny-all (مهم!)
    ├── app/
    ├── vendor/
    ├── storage/
    └── ...
```

**URLs:**
| الخدمة | الرابط |
|--------|--------|
| الواجهة | `https://smeda.gov.sy` |
| API | `https://smeda.gov.sy/api/api/...` |
| مثال login | `https://smeda.gov.sy/api/api/login` |
| health | `https://smeda.gov.sy/api/up` |

---

## خطوة 1 — تجهيز ZIP محلياً (Windows)

```powershell
cd C:\Users\LENOVO\Desktop\back_authority\authority2\api2
composer install --no-dev --optimize-autoloader
.\deploy\hostinger\build-upload.ps1
```

ستجد 3 ملفات في `deploy/hostinger/dist/`:
1. `smeda-front-*.zip` → محتويات `front/`
2. `smeda-api2-*.zip` → Laravel كامل
3. `smeda-api-entry-*.zip` → api/index.php + htaccess + config.php + env template

---

## خطوة 2 — الرفع عبر File Manager

### أ) الواجهة
1. افتح `public_html/` (فارغ حالياً)
2. ارفع وفك `smeda-front-*.zip` **مباشرة** داخل `public_html/` (ليس مجلد front/)
3. ارفع `config.php` و `.htaccess` من `smeda-api-entry-*.zip`

### ب) Laravel
1. أنشئ مجلد `public_html/api2/`
2. ارفع وفك `smeda-api2-*.zip` داخله
3. انسخ `env.production.template` → `api2/.env`
4. **مهم:** انسخ `api2.htaccess.deny-all` → `api2/.htaccess` (يمنع الوصول لـ .env)

### ج) مدخل API
1. في `public_html/api/` استبدل `index.php` و `.htaccess` بالملفات من `deploy/hostinger/public_html/api/`

---

## خطوة 3 — Terminal (hPanel → Advanced → SSH)

```bash
cd ~/domains/smeda.gov.sy/public_html/api2

# إن لم يكن vendor مرفوعاً:
composer install --no-dev --optimize-autoloader

chmod -R 775 storage bootstrap/cache

php artisan storage:link
php artisan migrate --force

# بيانات أولية (مرة واحدة)
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=NeedsGisAccountsSeeder --force

# مواقع سوريا (اختياري — ملف كبير)
php artisan syria-locations:import

php artisan config:cache
php artisan route:cache
```

---

## خطوة 4 — التحقق

| اختبار | النتيجة المتوقعة |
|--------|------------------|
| `https://smeda.gov.sy` | الصفحة الرئيسية |
| `https://smeda.gov.sy/login.php` | تسجيل الدخول |
| `https://smeda.gov.sy/api/up` | `{"status":"ok"}` أو 200 |
| `https://smeda.gov.sy/api/api/public/governorates` | JSON |

### حسابات pilot طرطوس (بعد NeedsGisAccountsSeeder)

| البريد | كلمة المرور |
|--------|-------------|
| `data-entry.tartus@system.com` | `12345678` |
| `branch.tartus@system.com` | `12345678` |
| `governor.tartus@system.com` | `12345678` |

---

## قاعدة البيانات

الإعدادات في `env.production.template`:
- DB: `u142331648_authority32`
- Host: `localhost`

إذا كانت DB موجودة مسبقاً بنفس البيانات → `migrate --force` فقط (بدون seed كامل).

---

## أمان

- [ ] `APP_DEBUG=false`
- [ ] `api2/.htaccess` يمنع الوصول المباشر
- [ ] لا ترفع `.env` إلى git
- [ ] غيّر كلمات مرور `@system.com` قبل pilot حقيقي
- [ ] فعّل CAPTCHA لاحقاً في `.env`

---

## استكشاف الأخطاء

### "Laravel path not found"
- تأكد `public_html/api2/vendor/autoload.php` موجود
- شغّل `composer install` على السيرفر

### API 404
- تحقق `.htaccess` في `public_html/api/`
- `mod_rewrite` مفعّل على Hostinger

### CORS / Login fails
- `CORS_ALLOWED_ORIGINS=https://smeda.gov.sy,https://www.smeda.gov.sy`
- `SANCTUM_STATEFUL_DOMAINS=smeda.gov.sy,www.smeda.gov.sy`

### الصور/المرفقات لا تظهر
```bash
php artisan storage:link
chmod -R 775 storage/app/public
```

---

## ملاحظة PHP

Hostinger: اختر **PHP 8.2+** من hPanel → PHP Configuration.
