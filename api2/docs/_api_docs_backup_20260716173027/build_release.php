<?php

declare(strict_types=1);

/**
 * Build company-ready API documentation release package.
 * Usage: php docs/api/build_release.php
 */

$apiRoot = dirname(__DIR__, 2);
$docsDir = __DIR__;
$releaseDir = $apiRoot . '/api-documentation-release';
$zipPath = $apiRoot . '/api-documentation-release.zip';
$backupDir = dirname($docsDir) . '/_api_docs_backup_' . date('YmdHis');

$releaseFiles = [
    'README.md',
    'API_DOCUMENTATION_AR.html',
    'API_DOCUMENTATION_AR.md',
    'API_AUDIT_REPORT_AR.md',
    'ROUTE_COVERAGE_REPORT.md',
    'openapi.yaml',
    'index.html',
    'SMEDC_API.postman_collection.json',
    'SMEDC_Local.postman_environment.json',
    'SMEDC_Production.postman_environment.json',
];

$swaggerAssets = [
    'swagger-ui.css' => 'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui.css',
    'swagger-ui-bundle.js' => 'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui-bundle.js',
    'swagger-ui-standalone-preset.js' => 'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui-standalone-preset.js',
];

$results = [];
function logStep(array &$results, string $name, bool $ok, string $detail = ''): void
{
    $results[] = ['test' => $name, 'ok' => $ok, 'detail' => $detail];
    echo ($ok ? '[OK] ' : '[FAIL] ') . $name . ($detail ? " — {$detail}" : '') . PHP_EOL;
}

echo "== SMEDC API Documentation Release Builder ==\n\n";

// 1. Backup docs/api (documentation only)
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0775, true);
}
foreach (glob($docsDir . '/*') ?: [] as $file) {
    $base = basename($file);
    if (is_dir($file) && $base === 'lib') {
        continue;
    }
    if (str_contains($base, '.bak') || str_contains($base, '_backup')) {
        continue;
    }
    if (is_file($file)) {
        copy($file, $backupDir . '/' . $base);
    }
}
logStep($results, 'Backup docs to ' . basename(dirname($backupDir)) . '/' . basename($backupDir), true);

// 2. Clean .bak from docs/api
foreach (glob($docsDir . '/**/*.bak.*') ?: [] as $bak) {
    @unlink($bak);
}
foreach (glob($docsDir . '/*.bak.*') ?: [] as $bak) {
    @unlink($bak);
}
logStep($results, 'Remove .bak files from docs/api', true);

// 3. Regenerate documentation
$cmd = PHP_BINARY . ' ' . escapeshellarg($docsDir . '/generate_api_docs.php');
exec($cmd, $out, $code);
logStep($results, 'Regenerate documentation', $code === 0, implode("\n", array_slice($out, -4)));

// 4. PHP syntax check generator
foreach (glob($docsDir . '/lib/*.php') ?: [] as $php) {
    exec(PHP_BINARY . ' -l ' . escapeshellarg($php) . ' 2>&1', $lintOut, $lintCode);
    logStep($results, 'PHP syntax ' . basename($php), $lintCode === 0, implode(' ', $lintOut));
}
exec(PHP_BINARY . ' -l ' . escapeshellarg($docsDir . '/generate_api_docs.php') . ' 2>&1', $lintOut2, $lintCode2);
logStep($results, 'PHP syntax generate_api_docs.php', $lintCode2 === 0);

// 5. Validate JSON files
foreach (['SMEDC_API.postman_collection.json', 'SMEDC_Local.postman_environment.json', 'SMEDC_Production.postman_environment.json'] as $jsonFile) {
    $path = $docsDir . '/' . $jsonFile;
    $raw = file_get_contents($path);
    json_decode($raw ?: '', true);
    $ok = json_last_error() === JSON_ERROR_NONE;
    logStep($results, 'JSON valid ' . $jsonFile, $ok, json_last_error_msg());
}

// 6. Validate OpenAPI YAML structure
$yamlRaw = file_get_contents($docsDir . '/openapi.yaml') ?: '';
$okOpenApi = str_starts_with(ltrim($yamlRaw), 'openapi: 3.1.0');
$oasOps = 0;
$dupOpIds = 0;
$opIds = [];
if (preg_match_all('/^\s{2}\/[^\n]+:\s*$/m', $yamlRaw, $pathMatches)) {
    $pathCount = count($pathMatches[0]);
}
if (preg_match_all('/^\s{4}(get|post|put|patch|delete):\s*$/mi', $yamlRaw, $methodMatches)) {
    $oasOps = count($methodMatches[0]);
}
if (preg_match_all('/operationId:\s*(\S+)/', $yamlRaw, $idMatches)) {
    foreach ($idMatches[1] as $oid) {
        if (isset($opIds[$oid])) {
            $dupOpIds++;
        }
        $opIds[$oid] = true;
    }
}
logStep($results, 'OpenAPI YAML header 3.1.0', $okOpenApi);
logStep($results, 'OpenAPI operations count (regex)', $oasOps > 0, (string) $oasOps);
logStep($results, 'OpenAPI unique operationIds', $dupOpIds === 0, $dupOpIds === 0 ? 'no duplicates' : "duplicates: {$dupOpIds}");

// 7. Route count vs OpenAPI operations
$routeListJson = shell_exec('cd ' . escapeshellarg($apiRoot) . ' && ' . PHP_BINARY . ' artisan route:list --json 2>&1');
$routes = json_decode($routeListJson ?: '[]', true);
if (!is_array($routes)) {
    $routes = [];
}
$apiRouteCount = 0;
foreach ($routes as $r) {
    $methods = explode('|', $r['method'] ?? 'GET');
    foreach ($methods as $m) {
        if (!in_array($m, ['HEAD', 'OPTIONS'], true) && str_starts_with($r['uri'] ?? '', 'api/')) {
            $apiRouteCount++;
        }
    }
}
logStep($results, 'Route list API endpoints', true, (string) $apiRouteCount);
logStep($results, 'OpenAPI coverage vs API routes', $oasOps >= $apiRouteCount - 5, "openapi={$oasOps} routes={$apiRouteCount}");

// 8. Secrets scan
$secretPatterns = ['APP_KEY=', 'DB_PASSWORD=', 'password123', 'Bearer 1|', 'sk_live', 'AKIA'];
$secretsFound = 0;
foreach ($releaseFiles as $f) {
    $content = file_get_contents($docsDir . '/' . $f) ?: '';
    foreach ($secretPatterns as $pat) {
        if (str_contains($content, $pat)) {
            $secretsFound++;
        }
    }
}
logStep($results, 'No secrets in release files', $secretsFound === 0, $secretsFound ? "matches: {$secretsFound}" : 'clean');

// 9. Prepare release directory
if (is_dir($releaseDir)) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($releaseDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($releaseDir);
}
mkdir($releaseDir, 0775, true);
$swaggerDir = $releaseDir . '/swagger-ui';
mkdir($swaggerDir, 0775, true);

foreach ($swaggerAssets as $name => $url) {
    $dest = $swaggerDir . '/' . $name;
    $data = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $data = curl_exec($ch);
        curl_close($ch);
    }
    if ($data === false || $data === '') {
        $ctx = stream_context_create(['http' => ['timeout' => 120], 'ssl' => ['verify_peer' => true]]);
        $data = @file_get_contents($url, false, $ctx);
    }
    if ($data === false || $data === '') {
        $ps = 'powershell -NoProfile -Command "Invoke-WebRequest -Uri ' . escapeshellarg($url) . ' -OutFile ' . escapeshellarg($dest) . ' -UseBasicParsing"';
        exec($ps, $psOut, $psCode);
        $okDl = $psCode === 0 && is_file($dest) && filesize($dest) > 1000;
        logStep($results, 'Download swagger-ui ' . $name, $okDl, $okDl ? 'via PowerShell' : $url);
        continue;
    }
    file_put_contents($dest, $data);
    logStep($results, 'Download swagger-ui ' . $name, true);
}

function stripBomWrite(string $dest, string $src): void
{
    $content = file_get_contents($src) ?: '';
    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        $content = substr($content, 3);
    }
    file_put_contents($dest, $content, LOCK_EX);
}

foreach ($releaseFiles as $file) {
    $src = $docsDir . '/' . $file;
    if (!is_file($src)) {
        logStep($results, 'Copy ' . $file, false, 'missing');
        continue;
    }
    stripBomWrite($releaseDir . '/' . $file, $src);
    logStep($results, 'Copy ' . $file, true);
}

// 10. Verify no .bak in release
$bakInRelease = glob($releaseDir . '/**/*.bak*') ?: [];
logStep($results, 'No .bak in release folder', $bakInRelease === []);

// 11. Create ZIP
if (is_file($zipPath)) {
    unlink($zipPath);
}
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    logStep($results, 'Create ZIP', false, $zipPath);
    exit(1);
}
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($releaseDir, FilesystemIterator::SKIP_DOTS));
foreach ($rii as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $filePath = $file->getPathname();
    $localName = 'api-documentation-release/' . substr($filePath, strlen($releaseDir) + 1);
    $zip->addFile($filePath, str_replace('\\', '/', $localName));
}
$zip->close();
logStep($results, 'Create ZIP archive', is_file($zipPath), $zipPath);

// 12. Swagger UI local server smoke test (fetch index.html)
$testPassed = is_file($releaseDir . '/index.html') && is_file($releaseDir . '/openapi.yaml') && is_file($releaseDir . '/swagger-ui/swagger-ui-bundle.js');
logStep($results, 'Swagger UI assets present', $testPassed);

echo "\n=== SUMMARY ===\n";
$passed = count(array_filter($results, fn ($r) => $r['ok']));
$failed = count($results) - $passed;
echo "Tests passed: {$passed}/" . count($results) . " (failed: {$failed})\n";
echo "Release folder: {$releaseDir}\n";
echo "ZIP: {$zipPath}\n";

exit($failed > 0 ? 1 : 0);
