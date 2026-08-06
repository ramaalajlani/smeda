<?php

declare(strict_types=1);

namespace ApiDocs;

final class Support
{
    public static function baseName(string $class): string
    {
        $parts = explode('\\', $class);

        return end($parts) ?: $class;
    }

    public static function atomicWrite(string $path, string $content, bool $keepBackup = false): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $content = self::stripBom($content);
        $tmp = $path . '.tmp.' . getmypid();
        $bytes = self::writeUtf8NoBom($tmp, $content);
        if ($bytes === false) {
            throw new \RuntimeException("Failed to write temp file: {$tmp}");
        }
        if ($keepBackup && is_file($path)) {
            $backupDir = dirname($dir) . '/_generator-backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0775, true);
            }
            $base = basename($path);
            copy($path, $backupDir . '/' . $base . '.' . date('YmdHis') . '.bak');
        }
        if (!rename($tmp, $path)) {
            @unlink($tmp);
            throw new \RuntimeException("Failed to rename {$tmp} -> {$path}");
        }
    }

    public static function writeUtf8NoBom(string $path, string $content): int|false
    {
        return file_put_contents($path, $content, LOCK_EX);
    }

    public static function stripBom(string $raw): string
    {
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            return substr($raw, 3);
        }

        return $raw;
    }

    public static function simplifyMiddleware(array $middleware): array
    {
        $out = [];
        foreach ($middleware as $m) {
            if (str_contains($m, 'Authenticate:sanctum')) {
                $out[] = 'auth:sanctum';
                continue;
            }
            if (str_starts_with($m, 'Spatie\\Permission\\Middleware\\RoleMiddleware')) {
                $out[] = preg_replace('/^Spatie\\\\Permission\\\\Middleware\\\\RoleMiddleware:(.+)$/', 'role:$1', $m) ?? $m;
                continue;
            }
            if (str_starts_with($m, 'Spatie\\Permission\\Middleware\\PermissionMiddleware')) {
                $out[] = preg_replace('/^Spatie\\\\Permission\\\\Middleware\\\\PermissionMiddleware:(.+)$/', 'permission:$1', $m) ?? $m;
                continue;
            }
            if (str_starts_with($m, 'Spatie\\Permission\\Middleware\\RoleOrPermissionMiddleware')) {
                $out[] = preg_replace('/^Spatie\\\\Permission\\\\Middleware\\\\RoleOrPermissionMiddleware:(.+)$/', 'role_or_permission:$1', $m) ?? $m;
                continue;
            }
            if (str_contains($m, 'ThrottleRequests')) {
                if (preg_match('/ThrottleRequests(?::withRedis)?:(.+)$/', $m, $match)) {
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
            $out[] = self::baseName($m);
        }

        return array_values(array_unique($out));
    }

    public static function detectModule(string $uri): string
    {
        if ($uri === 'up') {
            return 'Health Check';
        }
        if (!str_starts_with($uri, 'api/')) {
            if (str_contains($uri, 'certificate') || str_contains($uri, 'verify')) {
                return 'Certificate Verification';
            }
            if (str_contains($uri, 'trainer') || str_contains($uri, 'trainee') || str_contains($uri, 'training-center')) {
                return 'Printing';
            }

            return 'Web (Print/Verify/Files)';
        }

        $seg = explode('/', $uri)[1] ?? 'root';

        $map = [
            'register' => 'Authentication',
            'login' => 'Authentication',
            'logout' => 'Authentication',
            'me' => 'User Profile',
            'public' => 'Public APIs',
            'dashboard' => 'Dashboard',
            'governorates' => 'Governorates',
            'branches' => 'Branches',
            'agreements' => 'Agreements',
            'financial-records' => 'Finance',
            'funding-applications' => 'Finance',
            'funding-partners' => 'Finance',
            'funded-loans' => 'Finance',
            'funding-metrics' => 'Finance',
            'consultant-offices' => 'Finance',
            'consultant-assignments' => 'Finance',
            'consulting-offices' => 'Consulting Marketplace',
            'consulting-requests' => 'Consulting Marketplace',
            'needs' => 'Needs GIS',
            'syria-locations' => 'Syria Locations',
            'training-centers' => 'Training Centers',
            'trainers' => 'Trainers',
            'trainees' => 'Trainees',
            'training-supervisors' => 'Training Supervisors',
            'training-kits' => 'Training Kits',
            'training-programs' => 'Training Programs',
            'training-courses' => 'Training Courses',
            'training-kit-nominations' => 'Training Kit Nominations',
            'training-center-registration-requests' => 'Registration Requests',
            'trainer-registration-requests' => 'Registration Requests',
            'trainee-registration-requests' => 'Registration Requests',
            'course-registration-requests' => 'Registration Requests',
            'program-bank' => 'Program Bank',
            'certificates' => 'Certificates',
            'signatures' => 'Signatures',
            'workforce' => 'Workforce',
            'incubators' => 'Incubators',
            'incubation-applications' => 'Incubation Applications',
            'entrepreneur' => 'Entrepreneur Profiles',
            'notifications' => 'Notifications',
            'inbox' => 'Inbox',
            'success-stories' => 'Success Stories',
            'news' => 'News',
            'admin' => 'Admin',
            'roles' => 'Roles',
            'permissions' => 'Permissions',
            'map' => 'Maps',
            'files' => 'File Uploads',
            'training-kit-public-requests' => 'Training Requests',
            'verify-certificate' => 'Certificate Verification',
        ];

        if (isset($map[$seg])) {
            return $map[$seg];
        }
        if (str_starts_with($uri, 'api/my-')) {
            return 'User Profile';
        }

        return 'Other Routes';
    }

    public static function authType(array $middleware, string $uri): string
    {
        if ($uri === 'up') {
            return 'Public';
        }
        foreach ($middleware as $m) {
            if ($m === 'signed') {
                return 'Signed URL';
            }
        }
        foreach ($middleware as $m) {
            if ($m === 'auth:sanctum') {
                return 'Bearer Token';
            }
        }

        return 'Public';
    }

    public static function fullUrl(string $uri, string $base): string
    {
        $base = rtrim($base, '/');
        if ($uri === 'up') {
            return $base . '/up';
        }
        if (str_starts_with($uri, 'api/')) {
            return $base . '/' . substr($uri, 4);
        }

        return $base . '/' . ltrim($uri, '/');
    }

    public static function yamlScalar(string $value): string
    {
        if ($value === '' || preg_match('/[\x00-\x1F\x7F:#\[\]{},&*!|>\'"%@`]/', $value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        if (in_array(strtolower($value), ['true', 'false', 'null', 'yes', 'no', '~'], true)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    public static function toYaml(mixed $data, int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        if (!is_array($data)) {
            if (is_bool($data)) {
                return $pad . ($data ? 'true' : 'false');
            }

            return $pad . self::yamlScalar((string) $data);
        }

        if ($data === []) {
            return $pad . '[]';
        }

        $isList = array_is_list($data);
        $lines = [];
        foreach ($data as $k => $v) {
            if ($isList) {
                if (is_array($v)) {
                    $nested = self::toYaml($v, $indent + 1);
                    $lines[] = $pad . '- ' . ltrim($nested, " \t");
                } elseif (is_bool($v)) {
                    $lines[] = $pad . '- ' . ($v ? 'true' : 'false');
                } else {
                    $lines[] = $pad . '- ' . self::yamlScalar((string) $v);
                }
                continue;
            }

            if (is_array($v)) {
                if ($v === []) {
                    $lines[] = $pad . $k . ': []';
                } else {
                    $lines[] = $pad . $k . ':';
                    $lines[] = self::toYaml($v, $indent + 1);
                }
            } elseif (is_bool($v)) {
                $lines[] = $pad . $k . ': ' . ($v ? 'true' : 'false');
            } elseif (is_int($v) || is_float($v)) {
                $lines[] = $pad . $k . ': ' . $v;
            } else {
                $lines[] = $pad . $k . ': ' . self::yamlScalar((string) $v);
            }
        }

        return implode("\n", $lines);
    }

    public static function dumpYaml(array $doc): string
    {
        $version = $doc['openapi'] ?? '3.1.0';
        unset($doc['openapi']);

        return 'openapi: ' . $version . "\n" . self::toYaml($doc);
    }

    public static function rulesToString(array $rules): string
    {
        $parts = [];
        foreach ($rules as $field => $rule) {
            if (is_array($rule)) {
                $ruleParts = [];
                foreach ($rule as $r) {
                    if (is_object($r)) {
                        $ruleParts[] = $r::class;
                    } else {
                        $ruleParts[] = (string) $r;
                    }
                }
                $parts[] = $field . ': ' . implode('|', $ruleParts);
            } else {
                $parts[] = $field . ': ' . (string) $rule;
            }
        }

        return implode('; ', $parts);
    }

    public static function parseRateLimit(string $throttle): ?string
    {
        $map = [
            'login' => '10 طلبات/دقيقة لكل (email|IP) — AppServiceProvider',
            'register' => '5 طلبات/دقيقة لكل IP',
            'certificate-verify' => '30 طلبات/دقيقة لكل IP',
            'print-routes' => '60 طلبات/دقيقة لكل IP',
            'certificate-print-by-code' => '20 طلبات/دقيقة لكل IP',
            'map-public' => '60 طلبات/دقيقة لكل IP',
            'registration-requests' => '10 طلبات/دقيقة لكل (user|guest|IP)',
            'file-upload' => '5 طلبات/دقيقة لكل (user|IP)',
            'verify-page' => '30 طلبات/دقيقة لكل IP',
            'admin-access' => '120 طلبات/دقيقة لكل admin user',
            'incubation-report' => '10 طلبات/دقيقة لكل user|IP',
            'training-kit-public' => '5 طلبات/دقيقة لكل IP',
        ];
        if (preg_match('/^throttle:(.+)$/', $throttle, $m)) {
            $key = $m[1];
            if (isset($map[$key])) {
                return $map[$key];
            }
            if (preg_match('/^(\d+),(\d+)$/', $key, $n)) {
                return "{$n[1]} طلبات كل {$n[2]} دقائق لكل IP";
            }

            return "throttle:{$key}";
        }

        return null;
    }

    /** @return array<string, list<string>> */
    public static function parseRolesFromSeeder(string $seederPath): array
    {
        $src = file_get_contents($seederPath);
        if ($src === false) {
            return [];
        }
        if (!preg_match('/\$roles\s*=\s*\[(.*)\];\s*\n\s*foreach\s*\(\s*\$roles/s', $src, $m)) {
            return [];
        }
        $block = $m[1];
        $roles = [];
        if (preg_match_all("/'([a-z_]+)'\s*=>\s*(?:array_merge\(|\[)/", $block, $matches)) {
            foreach ($matches[1] as $role) {
                $roles[$role] = [];
            }
        }

        return $roles;
    }

    public static function roleLabels(): array
    {
        return [
            'general_director' => 'المدير العام',
            'admin' => 'مسؤول النظام',
            'deputy_general_director' => 'نائب المدير العام',
            'governor' => 'محافظ',
            'branch_manager' => 'مدير فرع',
            'branch_officer' => 'موظف فرع',
            'workforce_manager' => 'مدير القوى العاملة',
            'training_manager' => 'مدير التدريب',
            'training_supervisor' => 'مشرف تدريب',
            'deputy_director' => 'نائب مدير',
            'center_user' => 'مستخدم مركز تدريبي',
            'trainer_user' => 'مدرب',
            'trainee_user' => 'متدرب',
            'auditor' => 'مدقق',
            'data_entry' => 'مدخل بيانات',
            'data_reviewer' => 'مدقق بيانات',
            'project_services_manager' => 'مدير خدمات المشاريع',
            'development_manager' => 'مدير التنمية',
            'local_development_manager' => 'مدير التنمية المحلية',
            'finance_manager' => 'مدير التمويل',
            'finance_officer' => 'موظف تمويل',
            'consultant_office' => 'مكتب استشاري',
            'funding_partner' => 'شريك تمويل',
            'consultant_union_admin' => 'إدارة نقابة الاقتصاديين',
            'central_bank_admin' => 'إدارة البنك المركزي',
            'project_owner' => 'صاحب مشروع',
            'incubator_manager' => 'مدير حاضنة',
            'incubator_mentor' => 'مرشد حاضنة',
            'entrepreneur_manager' => 'مدير ريادة الأعمال',
            'media_manager' => 'مدير إعلام',
            'super_admin' => 'مدير أعلى',
            'system_admin' => 'مدير صلاحيات النظام',
        ];
    }
}
