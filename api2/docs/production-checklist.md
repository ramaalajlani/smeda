# Checklist الإطلاق على Hostinger / Production

## Laravel `.env`

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://smeda.gov.sy` (أو الدومين الفعلي)
- [ ] `APP_KEY` مُولّد (`php artisan key:generate`)

## قاعدة البيانات

- [ ] `DB_*` صحيحة على السيرفر
- [ ] `php artisan migrate` (**بدون** `--seed` على production)
- [ ] لا تشغّل `DatabaseSeeder` إذا يحتوي `12345678` أو حسابات تجريبية

## CAPTCHA

- [ ] `CAPTCHA_ENABLED=true`
- [ ] `CAPTCHA_DRIVER=turnstile` (أو `recaptcha`)
- [ ] `CAPTCHA_SITE_KEY=` و `CAPTCHA_SECRET_KEY=` من لوحة Cloudflare/Google

## Sanctum / Session / CORS

- [ ] `FRONTEND_URL=https://smeda.gov.sy` (في `.env` Laravel)
- [ ] `SANCTUM_STATEFUL_DOMAINS=smeda.gov.sy,www.smeda.gov.sy`
- [ ] `SESSION_DOMAIN=.smeda.gov.sy`
- [ ] `CORS_ALLOWED_ORIGINS=https://smeda.gov.sy`

## Frontend `front/assets/js/core/config.js`

- [ ] يكتشف **local** تلقائياً (`127.0.0.1:8000/api`) و**production** (`/api/api` على Hostinger)
- [ ] `API_BASE_URL` — انظر التعليق داخل الملف (قد يكون `/api/api` إذا Laravel داخل مجلد `/api`)
- [ ] `BACKEND_BASE_URL` — جذر Laravel بدون `/api` مكرر للطباعة
- [ ] `FRONTEND_BASE_URL` — دومين الواجهة
- [ ] `FORBIDDEN_PAGE` → `403.php` موجودة
- [ ] لا `localhost` في production

## Smoke Test

- [ ] راجع `docs/staging-smoke-test.md`

## Storage & Cache

- [ ] `php artisan storage:link`
- [ ] صلاحيات كتابة على `storage/` و `bootstrap/cache/` (755/775)

## SSL & Security

- [ ] HTTPS مفعّل
- [ ] Security headers (middleware موجود)
- [ ] Throttle على login/register/verify

## بعد النشر

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
php artisan test   # على CI أو بيئة staging
```

## تحقق يدوي

- [ ] login / register + CAPTCHA
- [ ] dashboard + صلاحيات
- [ ] دورة: إنشاء → نتائج → إكمال → شهادة → QR → verify → print
- [ ] Admin UI (admin role فقط)
- [ ] Workforce مخفي أو قيد التفعيل
