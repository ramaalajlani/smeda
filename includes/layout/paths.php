<?php

/**
 * Front-end absolute URL helpers.
 * Use front_url() for navigation/auth links (login, dashboard, …).
 * Keep $basePath for relative assets (css/js/images).
 */

if (!function_exists('front_config')) {
    function front_config(): array
    {
        static $config = null;

        if ($config !== null) {
            return $config;
        }

        $config = [];
        $configFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config.php';

        if (is_file($configFile)) {
            $loaded = include $configFile;
            if (is_array($loaded)) {
                $config = $loaded;
            }
        }

        return $config;
    }
}

if (!function_exists('front_is_local_dev')) {
    /**
     * بيئة التطوير المحلية عبر dev-start.bat: الواجهة على 127.0.0.1:8080
     * والـ API/الباك-إند على منفذ منفصل 127.0.0.1:8000.
     * لا تتحقق في الإنتاج (نطاق حقيقي على 80/443)، فلا تكسره.
     */
    function front_is_local_dev(): bool
    {
        $host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
        $port = (int) ($_SERVER['SERVER_PORT'] ?? 80);

        return in_array($host, ['127.0.0.1', 'localhost', '::1'], true) && $port === 8080;
    }
}

if (!function_exists('resolve_front_base_url')) {
    function resolve_front_base_url(): string
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $override = trim((string) (front_config()['base_url'] ?? ''));
        if ($override !== '') {
            return $cached = rtrim($override, '/');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        $hostName = preg_replace('/:\d+$/', '', $host);
        $port = (int) ($_SERVER['SERVER_PORT'] ?? 80);

        $frontDir = realpath(dirname(__DIR__, 2));
        $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';

        if ($frontDir && $docRoot && str_starts_with(strtolower($frontDir), strtolower($docRoot))) {
            $suffix = str_replace('\\', '/', substr($frontDir, strlen($docRoot)));
            $suffix = $suffix === '' ? '' : '/'.trim($suffix, '/');

            return $cached = $scheme.'://'.$host.$suffix;
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? '';
        $scriptDir = str_replace('\\', '/', dirname(realpath($scriptFile) ?: $scriptFile));
        $frontDirNorm = str_replace('\\', '/', $frontDir ?: '');

        if ($frontDirNorm !== '' && str_starts_with($scriptDir, $frontDirNorm)) {
            $relative = trim(str_replace($frontDirNorm, '', $scriptDir), '/');
            $depth = $relative === '' ? 0 : substr_count($relative, '/') + 1;
            $webDir = $scriptName;

            for ($i = 0; $i < $depth; $i++) {
                $webDir = dirname($webDir);
            }

            $webDir = rtrim(str_replace('\\', '/', $webDir), '/');
            if ($webDir === '.' || $webDir === '/') {
                $webDir = '';
            }

            $stdPort = ($scheme === 'https') ? 443 : 80;
            $portPart = ($port !== $stdPort && !str_contains($host, ':')) ? ':'.$port : '';
            $authority = str_contains($host, ':') ? $host : $hostName.$portPart;

            return $cached = $scheme.'://'.$authority.$webDir;
        }

        // Local dev: Apache/XAMPP on :80 but frontend runs on :8080
        if (in_array($hostName, ['127.0.0.1', 'localhost', '::1'], true) && $port === 80) {
            return $cached = 'http://127.0.0.1:8080';
        }

        $stdPort = ($scheme === 'https') ? 443 : 80;
        $portPart = ($port !== $stdPort && !str_contains($host, ':')) ? ':'.$port : '';
        $authority = str_contains($host, ':') ? $host : $hostName.$portPart;

        return $cached = $scheme.'://'.$authority;
    }
}

if (!function_exists('resolve_api_base_url')) {
    function resolve_api_base_url(): string
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $override = trim((string) (front_config()['api_base_url'] ?? ''));
        if ($override !== '') {
            return $cached = rtrim($override, '/');
        }

        if (front_is_local_dev()) {
            return $cached = 'http://127.0.0.1:8000/api';
        }

        return $cached = resolve_front_base_url().'/api/api';
    }
}

if (!function_exists('resolve_backend_base_url')) {
    function resolve_backend_base_url(): string
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $override = trim((string) (front_config()['backend_base_url'] ?? ''));
        if ($override !== '') {
            return $cached = rtrim($override, '/');
        }

        if (front_is_local_dev()) {
            return $cached = 'http://127.0.0.1:8000';
        }

        return $cached = resolve_front_base_url().'/api';
    }
}

if (!function_exists('front_url')) {
    function front_url(string $path = ''): string
    {
        $base = resolve_front_base_url();
        $path = ltrim(str_replace('\\', '/', $path), '/');

        return $path === '' ? $base : $base.'/'.$path;
    }
}
