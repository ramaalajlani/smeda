<?php

declare(strict_types=1);

/**
 * SMEDC API Documentation Generator
 *
 * Usage (from api2 root or anywhere):
 *   php docs/api/generate_api_docs.php
 *
 * Bootstraps Laravel, introspects routes/controllers, writes docs/api/* atomically.
 */

$apiRoot = dirname(__DIR__, 2);
$docsDir = __DIR__;

require $apiRoot . '/vendor/autoload.php';

require $docsDir . '/lib/Support.php';
require $docsDir . '/lib/MethodInspector.php';
require $docsDir . '/lib/ApiDocGenerator.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require $apiRoot . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Optional overrides from environment / .env
$localApi = rtrim((string) env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/api';
$prodApi = (string) env('PRODUCTION_API_BASE_URL', 'https://smeda.gov.sy/api/api');
$prodBackend = (string) env('PRODUCTION_BACKEND_BASE_URL', 'https://smeda.gov.sy/api');

$gitCommit = 'غير متاح';
$gitOut = [];
$gitCode = 1;
exec('git -C ' . escapeshellarg($apiRoot) . ' rev-parse HEAD 2>&1', $gitOut, $gitCode);
if ($gitCode === 0 && isset($gitOut[0]) && preg_match('/^[a-f0-9]{7,40}$/i', trim($gitOut[0]))) {
    $gitCommit = trim($gitOut[0]);
}

$generator = new ApiDocs\ApiDocGenerator($apiRoot, $docsDir, [
    'local_api_base' => $localApi,
    'local_backend_base' => rtrim((string) env('APP_URL', 'http://127.0.0.1:8000'), '/'),
    'production_api_base' => $prodApi,
    'production_backend_base' => $prodBackend,
    'app_name' => 'SMEDC Authority API',
    'version' => '2.0.0',
    'git_commit' => $gitCommit,
]);

try {
    exit($generator->run());
} catch (Throwable $e) {
    fwrite(STDERR, "GENERATION FAILED: " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}
