# SMEDC API Documentation

توثيق API مُولَّد آلياً من كود Laravel مع استخراج Validation و Policies من Controllers.

## الملفات

| الملف | الوصف |
|-------|--------|
| `API_DOCUMENTATION_AR.md` | التوثيق العربي الكامل (RTL) |
| `API_DOCUMENTATION_AR.html` | نسخة HTML تفاعلية (offline) |
| `openapi.yaml` | OpenAPI 3.1 |
| `index.html` | عارض OpenAPI (Scalar-style offline) |
| `SMEDC_API.postman_collection.json` | Postman Collection |
| `SMEDC_Local.postman_environment.json` | بيئة محلية |
| `SMEDC_Production.postman_environment.json` | بيئة إنتاج |
| `API_AUDIT_REPORT_AR.md` | تقرير تدقيق |
| `ROUTE_COVERAGE_REPORT.md` | تغطية المسارات |
| `_endpoints.json` | بيانات خام للتكامل |

## التحديث

```bash
cd api2
php docs/api/generate_api_docs.php
```

## فتح HTML

افتح `API_DOCUMENTATION_AR.html` في المتصفح مباشرة (بدون خادم).

## Swagger / Scalar

افتح `index.html` بجانب `openapi.yaml` — يعمل offline.

## Postman

1. Import → `SMEDC_API.postman_collection.json`
2. Import Environment → `SMEDC_Local.postman_environment.json` أو Production
3. نفّذ Login — السكربت يحفظ `access_token` تلقائياً

## Base URL

| البيئة | `{{base_url}}` |
|--------|----------------|
| Local | `http://127.0.0.1:8000/api` |
| Production | `https://smeda.gov.sy/api/api` |

غيّر القيم في ملفات Postman Environment أو `.env` — **لا تُثبَّت أسراراً في Git**.

## التحقق من OpenAPI

- [Swagger Editor](https://editor.swagger.io) — الصق محتوى `openapi.yaml`
- أو: `npx @redocly/cli lint openapi.yaml` إن وُجد Node.js

## أمان

- **لا تنشر** `index.html` / Postman على Production دون حماية
- استخدم أمثلة وهمية للـ Tokens وكلمات المرور

## نسخة احتياطية

النسخة السابقة: `_backup_API_Documentation_v2.0_ar.md`
