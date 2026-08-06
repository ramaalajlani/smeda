# التشغيل المحلي

## الطريقة السريعة (Windows)

```bat
dev-start.bat
```

ثم افتح: **http://127.0.0.1:8080/login.php**

## يدوياً

```bash
# Backend (API)
php artisan serve

# Frontend (نافذة ثانية)
php -S 127.0.0.1:8080 -t front front/router.php
```

## أول مرة

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## حسابات التطوير

| البريد | كلمة المرور |
|--------|-------------|
| `general@system.com` | `12345678` |
| `governor.tartus@system.com` | `12345678` (محافظ طرطوس — احتياجات محافظة طرطوس فقط) |

## خريطة الاحتياجات

- من الهيدر: **إضافة احتياج** أو **خريطة الاحتياجات**
- الرابط المباشر: http://127.0.0.1:8080/services/gis/needs-map.php
- تسجيل احتياج جديد: http://127.0.0.1:8080/services/gis/need-create.php

## ملاحظات

- **الرابط الصحيح للفرونت:** `http://127.0.0.1:8080` (شغّل `dev-start.bat`)
- روابط تسجيل الدخول تُوجَّه تلقائياً إلى `:8080` إذا فتحت الموقع من Apache على `:80`
- على Apache: إن كان DocumentRoot = مجلد المشروع (`api2`) وليس `front`، توجد ملفات جسر في الجذر (`login.php`, `index.php`, …) تعيد توجيه الطلب إلى `front/`
- للإنتاج على Hostinger: اترك `front/config.php` → `'base_url' => null` (اكتشاف تلقائي)، أو حدّد `'base_url' => 'https://smeda.gov.sy'`
- Captcha معطّل محلياً (`CAPTCHA_ENABLED=false`)
- على الإنتاج: فعّل Captcha وأضف المفاتيح في `.env`
