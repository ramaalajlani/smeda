# Smoke Test — Staging قبل النشر

قائمة تحقق يدوية مختصرة. استخدم حسابات التطوير أو حسابات staging المكافئة.

**كلمة المرور الافتراضية للتطوير:** `12345678`

| الدور | البريد |
|-------|--------|
| المدير العام | `general@system.com` |
| نائب المدير العام | `deputy@system.com` |
| مدير فرع دمشق | `branch.damascus@system.com` |
| مدير فرع حلب | `branch.aleppo@system.com` |
| مدقق | `auditor@system.com` |
| مدرب | `trainer@system.com` |
| متدرب | `trainee@system.com` |

---

## general_director

1. **Login** — `POST /api/login` → 200 + token
2. `GET /api/governorates` → **200** — 14 محافظة
3. `GET /api/branches` → **200** — كل الفروع
4. `GET /api/dashboard` → **200** — `governorates_count=14`, `agreements_count`, `financial_records_count`
5. `POST /api/agreements` (national) → **201**
6. `POST /api/finance/records` → **201**
7. `GET /api/admin/activity-logs/export?format=csv` → **200** — ملف CSV

---

## branch_manager (دمشق)

1. **Login** — `branch.damascus@system.com`
2. `GET /api/dashboard` → **200** — `branch_id` = فرع دمشق
3. `GET /api/trainers` → **200** — مدربو دمشق فقط (لا مدربي حلب)
4. `GET /api/branches/{aleppo_id}` → **403**
5. `POST /api/branches` → **403**
6. `GET /api/finance/records` → **200** — سجلات فرع دمشق فقط
7. `POST /api/finance/records` → **403**
8. `GET /api/agreements` → **200** — لا اتفاقيات `scope_type=national`

---

## deputy_general_director

1. `GET /api/governorates` → **200**
2. `GET /api/branches` → **200**
3. `GET /api/admin/users` → **403**
4. `POST /api/agreements` → **403** (بدون `manage_agreements`)

---

## auditor

1. `GET /api/training-courses` → **200**
2. `GET /api/finance/records` → **200**
3. `GET /api/admin/activity-logs` → **200**
4. `POST /api/training-courses` → **403**
5. `PUT /api/finance/records/{id}` → **403**
6. `DELETE /api/branches/{id}` → **403**

---

## trainer_user

1. `GET /api/training-courses` → **200** — دوراته فقط
2. `GET /api/training-courses/{other_trainer_course_id}` → **403**
3. `GET /api/admin/users` → **403**

---

## trainee_user

1. `GET /api/dashboard` → **200** — بيانات محدودة
2. `GET /api/certificates` → **200** — شهاداته فقط
3. `GET /api/admin/users` → **403**

---

## تحقق Resources (اختياري)

- `GET /api/training-courses` — كل عنصر يحتوي: `branch_id`, `branch_name`, `governorate_id`, `governorate_name`
- `GET /api/certificates` — نفس الحقول
- `GET /api/registration-requests/trainers` — نفس الحقول

---

## CORS / بيئة الإنتاج

قبل النشر راجع `.env`:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-api-domain`
- `FRONTEND_URL=https://your-frontend-domain`
- `CORS_ALLOWED_ORIGINS=https://your-frontend-domain` (بدون `*`)
- `SANCTUM_STATEFUL_DOMAINS=your-frontend-domain`
- `SESSION_DOMAIN=.your-domain` (إذا cookie-based)
