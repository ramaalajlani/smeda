# إصلاح قاعدة بيانات MySQL (خطأ 1932)

## الأعراض

```
SQLSTATE[42S02]: Base table or view not found: 1932
Table 'authority3.migrations' doesn't exist in engine
```

هذا الخطأ **غالبًا تلف في محرك InnoDB/MySQL محلي** وليس bug في migrations Laravel.

---

## قبل أي إجراء

1. راجع `.env`: `DB_DATABASE`, `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`.
2. **لا تعدّل ملفات migrations** بسبب هذا الخطأ.
3. **لا تشغّل `migrate --seed` على production** إذا كان `DatabaseSeeder` يحتوي مستخدمين تجريبيين بكلمات مرور مثل `12345678`.

---

## الحل الأول — قاعدة التطوير يمكن حذفها

```sql
DROP DATABASE authority3;
CREATE DATABASE authority3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
cd api2
php artisan migrate --seed
```

> `--seed` للتطوير فقط. على production استخدم `php artisan migrate` بدون seed.

---

## الحل الثاني — لا نريد حذف القاعدة بالكامل

```sql
DROP TABLE IF EXISTS migrations;
```

```bash
cd api2
php artisan migrate:install
php artisan migrate
```

إذا استمر الخطأ 1932، أعد تشغيل خدمة MySQL أو أصلح جداول `.ibd` التالفة عبر phpMyAdmin/Hostinger.

---

## التحقق

```bash
php artisan migrate --pretend
php artisan test
```

إذا فشل `--pretend` بسبب engine فقط → **مشكلة بيئة**.  
إذا نجح `--pretend` وفشل migrate → راجع SQL error المحدد.
