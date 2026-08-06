# SMEDC API Documentation — حزمة الشركة

توثيق API رسمي مُستخرج من كود Laravel (Routes, Controllers, Form Requests, Policies, Middleware).

| البند | القيمة |
|-------|--------|
| **تاريخ التوليد** | 2026-07-16 17:47:46 |
| **Git commit** | `غير متاح` |
| **Production API** | `https://smeda.gov.sy/api/api` |
| **Local API** | `http://127.0.0.1:8000/api` |

## محتويات الحزمة

| الملف | الوصف |
|-------|--------|
| `API_DOCUMENTATION_AR.md` / `.html` | التوثيق العربي الكامل + جدول مراجعة الحماية |
| `openapi.yaml` | OpenAPI 3.1 (329 عملية API) |
| `index.html` + `swagger-ui/` | Swagger UI تفاعلي مع Authorize |
| `SMEDC_API.postman_collection.json` | Postman Collection |
| `SMEDC_* .postman_environment.json` | بيئات Local / Production |
| `API_AUDIT_REPORT_AR.md` | تقرير تدقيق |
| `ROUTE_COVERAGE_REPORT.md` | تغطية 100% |

## التوثيق العربي

افتح `API_DOCUMENTATION_AR.html` في المتصفح (offline).

## Swagger UI

> `file://` قد يمنع تحميل YAML. شغّل خادماً محلياً:

```bash
cd api-documentation-release
php -S 127.0.0.1:8080
```

ثم: **http://127.0.0.1:8080/index.html**

1. **Authorize** → أدخل Token Sanctum (مثال: `1|example_token_placeholder`)
2. جرّب الطلبات — يتطلب Laravel API على `http://127.0.0.1:8000`

## Postman

1. Import Collection + Environment (Local أو Production)
2. نفّذ `POST login` — يحفظ `access_token` تلقائياً
3. `access_token` **فارغ** افتراضياً — استخدم حسابات تجريبية فقط

| المتغير | Production |
|---------|------------|
| `base_url` | `https://smeda.gov.sy/api/api` |
| `access_token` | (فارغ) |

## رابط الإنتاج

```
https://smeda.gov.sy/api/api/login
```

المسار `/api/api/` **مقصود**: Laravel داخل `public_html/api/` + بادئة `api/` في routes.

## تحذير أمني

- لا تنشر Swagger/Postman على الإنternet دون حماية (VPN / Basic Auth)
- لا تضع Tokens أو كلمات مرور حقيقية
- للاستخدام الداخلي مع الشركة فقط

## التحقق من OpenAPI

- [Swagger Editor](https://editor.swagger.io) — استورد `openapi.yaml`
