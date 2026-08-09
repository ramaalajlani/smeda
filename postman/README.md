# SMEDA API — Postman

Professional Postman package for the Laravel backend in `api2/`.

## Files

| File | Purpose |
|------|---------|
| `SMEDA-API.postman_collection.json` | Full collection (auth, public, actors, full catalog) |
| `SMEDA-Local.postman_environment.json` | Local environment |
| `SMEDA-Production.postman_environment.json` | Production environment |
| `API-COVERAGE.md` | Route-by-route coverage table |
| `generate-collection.mjs` | Regenerator (optional) |

## 1. Import Collection

Postman → **Import** → select `SMEDA-API.postman_collection.json`.

## 2. Import Environment

Import `SMEDA-Local.postman_environment.json` (or Production).

## 3. Select Environment

Top-right environment dropdown → **SMEDA Local**.

## 4. Credentials

`SMEDA-Local.postman_environment.json` is **pre-filled** (no empty placeholders):

- every actor email
- every password (local demo)
- live Sanctum token for each actor (`token`, `admin_token`, `central_bank_token`, …)
- `api_url` = `http://127.0.0.1:8000/api`

Import/re-import that file, select **SMEDA Local**, and run requests directly.

Refresh tokens anytime:

```bash
cd api2
php tests/fill_postman_local_env.php
```

Production env keeps placeholders only — do not put real production secrets in git.

### Local demo accounts (from seeders)

Default demo password used by `UserSeeder` / GIS seeders: `12345678` (local/dev only).

| Email | Typical role |
|-------|----------------|
| admin@system.com | admin + general_director |
| general@system.com | general_director |
| deputy@system.com | deputy_director / deputy_general_director |
| branch.damascus@system.com | branch_manager |
| governor.tartus@system.com | governor |
| manager@system.com | training_manager |
| center@system.com | center_user |
| trainer@system.com | trainer_user |
| trainee@system.com | trainee_user |
| auditor@system.com | auditor |
| projects@system.com | project_services_manager |
| data-entry.damascus@system.com | data_entry |
| data-reviewer.damascus@system.com | data_reviewer |
| central.bank@system.com | central_bank_admin |
| finance.manager@system.com | finance_manager |

## 5. Login and token

Run **00 - Authentication → Login** (or an actor’s `00 - Login as …`).

Tests save `token`, `token_type`, `user_id`, `role`, `actor`, `governorate_id`, `branch_id`, center/trainer/trainee ids when present.

Collection auth is **Bearer {{token}}**.

## 6. Test a specific Actor

1. Set that actor’s email/password variables.
2. Run `00 - Login as <role>` inside the actor folder.
3. Run requests under that folder only (they are filtered by route middleware vs seeded permissions).

## 7. Collection Runner

Runner → select folder (e.g. `07 - Branch Manager`) → run. Ensure Login is first.

## 8. Change base_url

| Env | base_url | api_url |
|-----|----------|---------|
| Local | `http://127.0.0.1:8000` | `{{base_url}}/api` |
| Production | `https://smeda.gov.sy/api` | `{{base_url}}/api` → final `https://smeda.gov.sy/api/api` |

Production path matches Hostinger layout documented in `docs/api/README.md` / deploy notes (`/api` public folder + Laravel `/api` prefix). Adjust if your host differs (e.g. `https://new.smeda.gov.sy/api2/public`).

## 9. File uploads

Requests that accept files use **form-data** with a `file` field. Choose a local file in Postman. Server rate-limits uploads (`throttle:file-upload`).

## 10. Tests

Collection-level tests check status code set, JSON content-type, and response time < 5s.

Login/create scripts persist IDs into the environment when present.

## 11. Roles in project (32)

`general_director`, `admin`, `deputy_general_director`, `governor`, `branch_manager`, `branch_officer`, `workforce_manager`, `training_manager`, `training_supervisor`, `deputy_director`, `center_user`, `trainer_user`, `trainee_user`, `auditor`, `data_entry`, `data_reviewer`, `project_services_manager`, `development_manager`, `local_development_manager`, `finance_manager`, `finance_officer`, `consultant_office`, `funding_partner`, `consultant_union_admin`, `central_bank_admin`, `project_owner`, `incubator_manager`, `incubator_mentor`, `entrepreneur_manager`, `media_manager`, `super_admin`, `system_admin`.

Source: `database/seeders/RolePermissionSeeder.php` (no `RolesAndPermissionsSeeder.php`).

## 12. Scope notes

- **National full:** `general_director`, `admin`, `super_admin`, deputies — all permissions.
- **Branch:** `branch_manager` / officers / data_* scoped by `branch_id`.
- **Governor:** `governorate_id` for needs and consulting patterns.
- **Center/Trainer/Trainee:** entity ids on the user.
- **system_admin:** access-admin permissions only.

Exact enforcement: Spatie middleware on routes + Policies + `AccessControlGuard` / data scopes.

## 13. Not added / limitations

- `routes/web.php` signed print/PDF/QR pages (not JSON API).
- No forgot/reset password or refresh-token endpoints exist in API.
- Actor folders filter by **route middleware** vs **seeded role permissions**. Routes that are `auth:sanctum` only appear under all actors; Policies may still deny.
- Bodies are representative examples — always confirm against FormRequest/controller validation for production tests.

## Regenerate

```bash
cd api2
php artisan route:list --path=api --json > ../postman/_routes_raw.json
cd ../postman
node generate-collection.mjs
```

## Counts (this generation)

- Discovered endpoints: see `API-COVERAGE.md`
- Actor folders: 32
- Postman request items: 3964
