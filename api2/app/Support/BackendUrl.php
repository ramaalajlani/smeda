<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Absolute URLs for Laravel web routes (verify/print/cards).
 * Must match the frontend BACKEND_BASE_URL (see includes/layout/paths.php).
 */
class BackendUrl
{
    public static function base(): string
    {
        $explicit = trim((string) env('BACKEND_URL', ''));
        if ($explicit !== '') {
            return rtrim($explicit, '/');
        }

        $configured = trim((string) config('app.backend_url', ''));
        if ($configured !== '' && $configured !== trim((string) config('app.url', ''))) {
            return rtrim($configured, '/');
        }

        $frontend = rtrim((string) env('FRONTEND_URL', ''), '/');
        if ($frontend !== '') {
            $host = parse_url($frontend, PHP_URL_HOST) ?: '';

            if (strcasecmp($host, 'new.smeda.gov.sy') === 0) {
                return $frontend . '/api2/public';
            }

            if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
                return 'http://127.0.0.1:8000';
            }

            return $frontend . '/api';
        }

        return rtrim((string) config('app.url', 'http://localhost'), '/');
    }

    public static function to(string $path): string
    {
        return self::base() . '/' . ltrim($path, '/');
    }

    public static function temporarySignedRoute(string $name, $expiration, array $parameters = []): string
    {
        $previousRoot = config('app.url');
        URL::forceRootUrl(self::base());

        try {
            return URL::temporarySignedRoute($name, $expiration, $parameters);
        } finally {
            URL::forceRootUrl($previousRoot);
        }
    }
}
