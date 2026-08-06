<?php

declare(strict_types=1);

function base_name(string $class): string
{
    $parts = explode('\\', $class);

    return end($parts) ?: $class;
}

$jsonPath = __DIR__ . '/_routes_export.json';

if (!is_file($jsonPath)) {
    // Regenerate from artisan if missing
    $output = shell_exec('cd ' . escapeshellarg(dirname(__DIR__)) . ' && php artisan route:list --json 2>&1');
    if (!$output) {
        fwrite(STDERR, "Failed to run route:list\n");
        exit(1);
    }
    file_put_contents($jsonPath, $output);
}

$raw = file_get_contents($jsonPath);
// Strip BOM / stderr noise before JSON array
if ($raw !== false && !str_starts_with(trim($raw), '[')) {
    $start = strpos($raw, '[');
    if ($start !== false) {
        $raw = substr($raw, $start);
    }
}

$routes = json_decode($raw ?: '[]', true, 512, JSON_THROW_ON_ERROR);

function simplifyMiddleware(array $middleware): string
{
    $out = [];
    foreach ($middleware as $m) {
        if (str_contains($m, 'Authenticate:sanctum')) {
            $out[] = 'auth:sanctum';
            continue;
        }
        if (str_starts_with($m, 'Spatie\\Permission\\Middleware\\')) {
            $out[] = preg_replace('/^Spatie\\\\Permission\\\\Middleware\\\\(\w+):/', '$1:', $m) ?? $m;
            continue;
        }
        if (str_starts_with($m, 'Illuminate\\Routing\\Middleware\\ThrottleRequests')) {
            if (preg_match('/ThrottleRequests:(.+)$/', $m, $match)) {
                $out[] = 'throttle:' . $match[1];
            } else {
                $out[] = 'throttle';
            }
            continue;
        }
        if (str_contains($m, 'EnsureDashboardAccess')) {
            $out[] = 'dashboard.access';
            continue;
        }
        if (str_contains($m, 'ValidateSignature')) {
            $out[] = 'signed';
            continue;
        }
        $short = base_name($m);
        $out[] = $short;
    }

    return implode(', ', array_unique($out));
}

function authLabel(array $middleware, string $uri): string
{
    if ($uri === 'up') {
        return 'Public (health)';
    }
    if (in_array('signed', array_map(fn ($m) => str_contains($m, 'ValidateSignature') ? 'signed' : '', $middleware), true)) {
        return 'Signed URL (no token)';
    }
    foreach ($middleware as $m) {
        if (str_contains($m, 'Authenticate:sanctum')) {
            return 'Bearer Token (Sanctum)';
        }
    }

    return 'Public';
}

function groupKey(string $uri): string
{
    if ($uri === 'up') {
        return '00 — Health';
    }
    if (!str_starts_with($uri, 'api/')) {
        return '99 — Web (Print/Verify)';
    }
    $parts = explode('/', $uri);
    $seg = $parts[1] ?? 'root';
    if ($seg === 'public') {
        return '01 — Public Browse';
    }
    if (in_array($seg, ['register', 'login', 'logout', 'me'], true) || str_starts_with($uri, 'api/my-')) {
        return '02 — Authentication & Profile';
    }

    return match ($seg) {
        'dashboard' => '03 — Dashboard',
        'governorates', 'branches', 'agreements' => '04 — Organization',
        'finance' => '05 — Finance',
        'needs' => '06 — Needs (GIS)',
        'trainers', 'trainer-profiles', 'trainees', 'training-centers', 'training-supervisors',
        'training-kits', 'training-programs', 'training-courses', 'training-kit-nominations',
        'training-kit-public-requests', 'program-bank', 'certificates', 'verify-certificate',
        'map', 'registration-requests', 'workforces', 'workforce', 'signatures' => '07 — Training & Workforce',
        'admin' => '08 — Admin & RBAC',
        'consulting' => '09 — Consulting Marketplace',
        'notifications', 'inbox' => '10 — Notifications & Inbox',
        'incubators', 'incubation' => '11 — Incubation',
        'success-stories', 'news' => '12 — Content',
        'entrepreneur' => '13 — Entrepreneur Profiles',
        'locations' => '14 — Syria Locations',
        default => '15 — Other (' . $seg . ')',
    };
}

function actionShort(?string $action): string
{
    if (!$action) {
        return '—';
    }
    if (str_contains($action, '@')) {
        return $action;
    }

    return $action;
}

usort($routes, function ($a, $b) {
    $ga = groupKey($a['uri'] ?? '');
    $gb = groupKey($b['uri'] ?? '');
    if ($ga !== $gb) {
        return strcmp($ga, $gb);
    }

    return strcmp(($a['uri'] ?? '') . ($a['method'] ?? ''), ($b['uri'] ?? '') . ($b['method'] ?? ''));
});

$grouped = [];
foreach ($routes as $route) {
    $uri = $route['uri'] ?? '';
    $key = groupKey($uri);
    $grouped[$key][] = $route;
}

$apiCount = count(array_filter($routes, fn ($r) => str_starts_with($r['uri'] ?? '', 'api/')));
$webCount = count($routes) - $apiCount - (in_array(['uri' => 'up'], $routes, true) ? 0 : 0);

$date = date('Y-m-d');

ob_start();
echo <<<HEADER
<div dir="rtl" lang="ar">

# توثيق واجهات البرمجة (API Documentation)
## منصة SMEDC — الإصدار 2.0

| البند | القيمة |
|-------|--------|
| **تاريخ التوليد** | {$date} |
| **المصدر** | `php artisan route:list --json` (346 route) |
| **مسارات API** | {$apiCount} |
| **إطار العمل** | Laravel 12 |
| **المصادقة** | Laravel Sanctum (Bearer Token) |
| **Guard الصلاحيات** | `sanctum` (Spatie Permission) |

> **تنبيه:** هذه الوثيقة **مُولَّدة آلياً** من الراوتات المسجلة فعلياً في المشروع. أي endpoint غير موجود في الجداول أدناه **غير موجود** في الكود الحالي.

---

## 1. معلومات عامة

### 1.1 Base URL

| البيئة | Base URL | مثال endpoint |
|--------|----------|---------------|
| **Local (artisan serve)** | `http://127.0.0.1:8000` | `http://127.0.0.1:8000/api/login` |
| **Production (Hostinger)** | `https://smeda.gov.sy/api` | `https://smeda.gov.sy/api/api/login` |

> على Hostinger: مجلد `public_html/api/` هو مدخل Laravel، والراوتات مسجّلة تحت prefix `/api` — فيصبح المسار الكامل **`/api/api/...`**.

### 1.2 Headers

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

### 1.3 رموز الاستجابة الشائعة

| Code | المعنى |
|------|--------|
| 200 | نجاح |
| 201 | تم الإنشاء |
| 204 | نجاح بدون محتوى |
| 401 | غير مصادق |
| 403 | ممنوع (صلاحية/Policy) |
| 404 | غير موجود |
| 422 | خطأ Validation |
| 429 | تجاوز Rate Limit |
| 500 | خطأ خادم |

### 1.4 Health Check

| Method | URI | Auth |
|--------|-----|------|
| GET | `/up` | Public |

---

## 2. المصادقة — تفاصيل الطلبات

### POST `/api/register` (Public, throttle:register)

**Body:**
```json
{
  "name": "string (required, max:255)",
  "email": "string (required, email, unique)",
  "password": "string (required, min:8, confirmed)",
  "password_confirmation": "string (required)",
  "account_type": "string (required — see SelfRegistrationCatalog)",
  "device_name": "string (optional)"
}
```

**محظور في الطلب:** `role`, `roles`, `permissions`, `entity_type`, `training_center_id`, `trainer_id`, `trainee_id`, `is_active`

**Response 201:**
```json
{
  "message": "تم إنشاء الحساب بنجاح.",
  "token": "...",
  "token_type": "Bearer",
  "redirect_to_form": "center|trainer|trainee|...",
  "entity_pending_approval": true,
  "user": { "id": 1, "name": "...", "email": "...", "roles": [], "permissions": [] }
}
```

**المصدر:** `AuthController@register` — `app/Http/Controllers/Api/AuthController.php:29-93`

---

### POST `/api/login` (Public, throttle:login)

**Body:**
```json
{
  "email": "string (required, email)",
  "password": "string (required)",
  "device_name": "string (optional)"
}
```

**Response 200:**
```json
{
  "message": "تم تسجيل الدخول بنجاح.",
  "token": "...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "...",
    "email": "...",
    "phone": null,
    "entity_type": "...",
    "training_center_id": null,
    "trainer_id": null,
    "trainee_id": null,
    "governorate_id": null,
    "branch_id": null,
    "is_active": true,
    "last_login_at": "2026-07-15T12:00:00+00:00",
    "created_at": "...",
    "roles": ["..."],
    "permissions": ["..."]
  }
}
```

**Response 401:** بيانات خاطئة أو حساب معطّل (`is_active=false`)

---

### POST `/api/logout` (Bearer Token)

**Response 200:** `{ "message": "..." }`

---

### GET/PUT `/api/me` | POST `/api/me/change-password` (Bearer Token)

---

## 3. مرجع المسارات الكامل (من Route List)

HEADER;

foreach ($grouped as $groupName => $groupRoutes) {
    echo "\n### {$groupName}\n\n";
    echo "| # | Method | URI | Auth | Middleware | Controller |\n";
    echo "|---|--------|-----|------|------------|------------|\n";
    $i = 1;
    foreach ($groupRoutes as $route) {
        $method = $route['method'] ?? '—';
        $uri = '/' . ($route['uri'] ?? '');
        $middleware = simplifyMiddleware($route['middleware'] ?? []);
        $auth = authLabel($route['middleware'] ?? [], $route['uri'] ?? '');
        $action = actionShort($route['action'] ?? null);
        $methodEsc = str_replace('|', '\\|', $method);
        echo "| {$i} | `{$methodEsc}` | `{$uri}` | {$auth} | {$middleware} | `{$action}` | \n";
        $i++;
    }
}

echo <<<'FOOTER'

---

## 4. مسارات Web (طباعة وتحقق — خارج prefix /api)

هذه المسارات في `routes/web.php` — تُستخدم للطباعة PDF/HTML والتحقق العام.

| Method | URI | Auth | Middleware | Controller |
|--------|-----|------|------------|------------|
| GET | `/certificates/{id}/print` | Signed URL | signed, throttle | `CertificatePrintController@show` |
| GET | `/certificates/{id}/pdf` | Signed URL | signed, throttle | `CertificatePrintController@pdf` |
| GET | `/trainers/{id}/card` | Signed URL | signed, throttle | `TrainerPrintController@show` |
| GET | `/trainers/{id}/card/pdf` | Signed URL | signed, throttle | `TrainerPrintController@pdf` |
| GET | `/training-centers/{id}/certificate` | Signed URL | signed, throttle | `TrainingCenterPrintController@show` |
| GET | `/training-centers/{id}/certificate/pdf` | Signed URL | signed, throttle | `TrainingCenterPrintController@pdf` |
| GET | `/trainees/{id}/card` | Signed URL | signed, throttle | `TraineePrintController@show` |
| GET | `/trainees/{id}/card/pdf` | Signed URL | signed, throttle | `TraineePrintController@pdf` |
| GET | `/verify-certificate/{certificate_code}` | Public | throttle | `CertificatePrintController@publicView` |
| GET | `/certificates/verify` | Public | throttle | `CertificateController@verifyPage` |
| GET | `/certificates/{certificate_code}/print` | Public | throttle | `CertificatePrintController@showByCode` |
| GET | `/certificates/{certificate_code}/pdf` | Public | throttle | `CertificatePrintController@pdfByCode` |
| GET | `/certificates/{certificate_code}/qr` | Public | throttle | `CertificatePrintController@publicQrImage` |

> **Signed URL:** يتطلب query parameter `signature` صالحاً — يُولَّد من Laravel `URL::signedRoute()`.

---

## 5. Pagination & Filters (عام)

معظم endpoints القائمة (`index`) تدعم:

| Parameter | النوع | الوصف |
|-----------|-------|-------|
| `page` | integer | رقم الصفحة |
| `per_page` | integer | عدد العناصر (غالباً max 100) |
| `search` | string | بحث نصي (حيث مُطبَّق) |
| `status` | string | فلترة حسب الحالة |
| `branch_id` | integer | فلترة فرع (للأدوار الوطنية) |
| `governorate_id` | integer | فلترة محافظة |

---

## 6. account_type — أنواع التسجيل الذاتي

**المصدر:** `App\Support\SelfRegistrationCatalog::validationKeys()`

| account_type | الدور (role) | entity_type |
|--------------|--------------|-------------|
| `trainee` | trainee_user | trainee_user |
| `trainer` | trainer_user | trainer_user |
| `center` | center_user | center_user |
| `project_owner` | project_owner | project_owner |
| `incubation_applicant` | project_owner | project_owner |
| `entrepreneur_tech` | project_owner | project_owner |
| `entrepreneur` | project_owner (alias) | project_owner |
| `consultant` | consultant_office | consultant_office |
| `consulting_client` | trainee_user | consulting_client |
| `jobseeker` | trainee_user | job_seeker |
| `employer` | project_owner | project_owner |

---

## 7. ملاحظات دقة

1. **346 route** = API + Web + Health — الجدول في القسم 3 ي listing الكل من artisan.
2. **Policy إضافية:** بعض المسارات `auth:sanctum` فقط وتطبّق Policy داخل Controller (مثل `branches/{id}`, `registration-requests/*/show`).
3. **Rate limits:** register, login, map-public, admin-access, file-upload, certificate-verify — مُعرَّفة في `bootstrap/app.php` أو RouteServiceProvider.
4. **Frontend base:** `https://smeda.gov.sy/api/api` — `front/assets/js/core/config.js`.

---

*نهاية API Documentation v2.0 — مُولَّد من `docs/generate_api_doc.php`*

</div>
FOOTER;

$outPath = __DIR__ . '/API_Documentation_v2.0_ar.md';
file_put_contents($outPath, ob_get_clean() ?: '');
echo "Written: {$outPath}\n";
echo "Routes: " . count($routes) . " (API: {$apiCount})\n";
