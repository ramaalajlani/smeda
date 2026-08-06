<?php

declare(strict_types=1);

/**
 * Build SMEDC API Documentation release package for company delivery.
 * Usage: php docs/api/build_release.php
 */

$apiRoot = dirname(__DIR__, 2);
$docsDir = __DIR__;
$packageName = 'SMEDC_API_Documentation_v2.0';
$releaseDir = $apiRoot . '/' . $packageName;
$zipPath = $apiRoot . '/' . $packageName . '.zip';
$backupDir = dirname($docsDir) . '/_api_docs_backup_' . date('YmdHis');
$swaggerSourceDir = $docsDir . '/swagger-ui';
$swaggerRequired = [
    'swagger-ui.css',
    'swagger-ui-bundle.js',
    'swagger-ui-standalone-preset.js',
];

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

$results = [];
function logStep(array &$results, string $name, bool $ok, string $detail = ''): void
{
    $results[] = ['test' => $name, 'ok' => $ok, 'detail' => $detail];
    echo ($ok ? '[OK] ' : '[FAIL] ') . $name . ($detail ? " — {$detail}" : '') . PHP_EOL;
}

function stripBom(string $content): string
{
    return str_starts_with($content, "\xEF\xBB\xBF") ? substr($content, 3) : $content;
}

function stripBomWrite(string $dest, string $src): void
{
    file_put_contents($dest, stripBom(file_get_contents($src) ?: ''), LOCK_EX);
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    @rmdir($dir);
}

function rcopy(string $src, string $dest): void
{
    if (!is_dir($src)) {
        throw new RuntimeException("Missing directory: {$src}");
    }
    if (!is_dir($dest)) {
        mkdir($dest, 0775, true);
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        $target = $dest . DIRECTORY_SEPARATOR . $it->getSubPathName();
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0775, true);
            }
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

echo "== {$packageName} Release Builder ==\n\n";

// 1. Backup docs/api (files only, not lib regeneration artifacts in zip)
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0775, true);
}
foreach (glob($docsDir . '/*') ?: [] as $file) {
    $base = basename($file);
    if (is_dir($file) && in_array($base, ['lib', 'swagger-ui'], true)) {
        continue;
    }
    if (str_contains($base, '.bak') || str_contains($base, '_backup')) {
        continue;
    }
    if (is_file($file)) {
        copy($file, $backupDir . '/' . $base);
    }
}
logStep($results, 'Backup docs/api', true, basename($backupDir));

// 2. Clean .bak from docs/api
foreach (array_merge(glob($docsDir . '/*.bak.*') ?: [], glob($docsDir . '/**/*.bak.*') ?: []) as $bak) {
    @unlink($bak);
}
logStep($results, 'Remove .bak from docs/api', true);

// 3. Fix BOM in _routes_snapshot.json (internal, not shipped)
$snapshot = $docsDir . '/_routes_snapshot.json';
if (is_file($snapshot)) {
    $raw = file_get_contents($snapshot) ?: '';
    $clean = stripBom($raw);
    if (!str_starts_with(trim($clean), '[')) {
        $start = strpos($clean, '[');
        if ($start !== false) {
            $clean = substr($clean, $start);
        }
    }
    file_put_contents($snapshot, $clean, LOCK_EX);
    json_decode($clean, true);
    logStep($results, 'Fix BOM _routes_snapshot.json', json_last_error() === JSON_ERROR_NONE, json_last_error_msg());
} else {
    logStep($results, 'Fix BOM _routes_snapshot.json', true, 'snapshot optional');
}

// 4. Verify local swagger-ui (offline, no CDN)
$swaggerOk = true;
foreach ($swaggerRequired as $file) {
    $path = $swaggerSourceDir . '/' . $file;
    $ok = is_file($path) && filesize($path) > 1000;
    $swaggerOk = $swaggerOk && $ok;
    logStep($results, 'swagger-ui local ' . $file, $ok, $ok ? (string) filesize($path) . ' bytes' : 'missing');
}
if (!$swaggerOk) {
    fwrite(STDERR, "ERROR: docs/api/swagger-ui incomplete. Copy swagger-ui-dist files locally.\n");
    exit(1);
}

// 5. Regenerate documentation
$cmd = PHP_BINARY . ' ' . escapeshellarg($docsDir . '/generate_api_docs.php');
exec($cmd, $out, $code);
logStep($results, 'Regenerate documentation', $code === 0, implode(' | ', array_slice($out, -3)));

// 6. PHP syntax
foreach (array_merge(glob($docsDir . '/lib/*.php') ?: [], [$docsDir . '/generate_api_docs.php']) as $php) {
    exec(PHP_BINARY . ' -l ' . escapeshellarg($php) . ' 2>&1', $lintOut, $lintCode);
    logStep($results, 'PHP syntax ' . basename($php), $lintCode === 0);
}

// 7. JSON validation (release files in docs/api)
foreach (['SMEDC_API.postman_collection.json', 'SMEDC_Local.postman_environment.json', 'SMEDC_Production.postman_environment.json'] as $jsonFile) {
    $raw = stripBom(file_get_contents($docsDir . '/' . $jsonFile) ?: '');
    json_decode($raw, true);
    logStep($results, 'JSON valid ' . $jsonFile, json_last_error() === JSON_ERROR_NONE, json_last_error_msg());
}

// 8. OpenAPI validation
$yamlRaw = stripBom(file_get_contents($docsDir . '/openapi.yaml') ?: '');
$okOpenApi = str_starts_with(ltrim($yamlRaw), 'openapi: 3.1.0');
$oasOps = 0;
$dupOpIds = 0;
$opIds = [];
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
logStep($results, 'OpenAPI 3.1.0 header', $okOpenApi);
logStep($results, 'OpenAPI operations = 329', $oasOps === 329, (string) $oasOps);
logStep($results, 'OpenAPI unique operationIds', $dupOpIds === 0, $dupOpIds ? "duplicates: {$dupOpIds}" : 'none');

// 9. Route count
$routeListJson = shell_exec('cd ' . escapeshellarg($apiRoot) . ' && ' . PHP_BINARY . ' artisan route:list --json 2>&1');
$routes = json_decode(stripBom($routeListJson ?: '[]'), true);
$apiRouteCount = 0;
if (is_array($routes)) {
    foreach ($routes as $r) {
        foreach (explode('|', $r['method'] ?? 'GET') as $m) {
            if (!in_array($m, ['HEAD', 'OPTIONS'], true) && str_starts_with($r['uri'] ?? '', 'api/')) {
                $apiRouteCount++;
            }
        }
    }
}
logStep($results, 'Laravel API routes', $apiRouteCount === 329, (string) $apiRouteCount);
logStep($results, 'OpenAPI coverage 100%', $oasOps === $apiRouteCount);

// 10. Secrets scan
$secretPatterns = ['APP_KEY=', 'DB_PASSWORD=', 'Bearer 1|', 'sk_live', 'AKIA'];
$secretsFound = 0;
foreach ($releaseFiles as $f) {
    $content = file_get_contents($docsDir . '/' . $f) ?: '';
    foreach ($secretPatterns as $pat) {
        if (str_contains($content, $pat)) {
            $secretsFound++;
        }
    }
}
logStep($results, 'No secrets in release files', $secretsFound === 0);

// 11. Build release folder
rrmdir($releaseDir);
mkdir($releaseDir, 0775, true);

foreach ($releaseFiles as $file) {
    $src = $docsDir . '/' . $file;
    if (!is_file($src)) {
        logStep($results, 'Copy ' . $file, false, 'missing');
        exit(1);
    }
    stripBomWrite($releaseDir . '/' . $file, $src);
    logStep($results, 'Copy ' . $file, true);
}

rcopy($swaggerSourceDir, $releaseDir . '/swagger-ui');
foreach ($swaggerRequired as $file) {
    $p = $releaseDir . '/swagger-ui/' . $file;
    logStep($results, 'Release swagger-ui/' . $file, is_file($p) && filesize($p) > 1000);
}

// 12. ZIP with root folder SMEDC_API_Documentation_v2.0/
if (is_file($zipPath)) {
    unlink($zipPath);
}
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    logStep($results, 'Create ZIP', false, $zipPath);
    exit(1);
}
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($releaseDir, FilesystemIterator::SKIP_DOTS));
$zipFiles = 0;
foreach ($rii as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $filePath = $file->getPathname();
    $relative = substr($filePath, strlen($releaseDir) + 1);
    $zip->addFile($filePath, $packageName . '/' . str_replace('\\', '/', $relative));
    $zipFiles++;
}
$zip->close();
logStep($results, 'Create ZIP ' . basename($zipPath), is_file($zipPath), "{$zipFiles} files");

// 13. Verify ZIP contains swagger-ui
$zipCheck = new ZipArchive();
$zipCheck->open($zipPath);
$hasSwagger = false;
for ($i = 0; $i < $zipCheck->numFiles; $i++) {
    $name = $zipCheck->getNameIndex($i);
    if (str_contains($name, 'swagger-ui/swagger-ui-bundle.js')) {
        $hasSwagger = true;
        break;
    }
}
$zipCheck->close();
logStep($results, 'ZIP contains swagger-ui/', $hasSwagger);

// 14. Independent extract + HTTP smoke test
$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'smedc_api_doc_test_' . getmypid();
rrmdir($testRoot);
mkdir($testRoot, 0775, true);
$zipTest = new ZipArchive();
$zipTest->open($zipPath);
$zipTest->extractTo($testRoot);
$zipTest->close();
$extractedDir = $testRoot . DIRECTORY_SEPARATOR . $packageName;
$indexOk = is_file($extractedDir . '/index.html');
$yamlOk = is_file($extractedDir . '/openapi.yaml');
$swaggerBundleOk = is_file($extractedDir . '/swagger-ui/swagger-ui-bundle.js');
logStep($results, 'Extract ZIP independent path', is_dir($extractedDir));
logStep($results, 'Extracted index.html', $indexOk);
logStep($results, 'Extracted openapi.yaml', $yamlOk);
logStep($results, 'Extracted swagger-ui bundle', $swaggerBundleOk);

$serverProc = proc_open(
    PHP_BINARY . ' -S 127.0.0.1:8099 -t ' . escapeshellarg($extractedDir),
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $pipes,
    $extractedDir
);
if (is_resource($serverProc)) {
    usleep(500000);
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $indexResp = @file_get_contents('http://127.0.0.1:8099/index.html', false, $ctx);
    $yamlResp = @file_get_contents('http://127.0.0.1:8099/openapi.yaml', false, $ctx);
    $jsResp = @file_get_contents('http://127.0.0.1:8099/swagger-ui/swagger-ui-bundle.js', false, $ctx);
    logStep($results, 'HTTP index.html 200', $indexResp !== false && strlen($indexResp) > 500);
    logStep($results, 'HTTP openapi.yaml 200', $yamlResp !== false && str_starts_with($yamlResp, 'openapi: 3.1.0'));
    logStep($results, 'HTTP swagger-ui-bundle.js 200', $jsResp !== false && strlen($jsResp) > 100000);
    proc_terminate($serverProc);
    proc_close($serverProc);
} else {
    logStep($results, 'HTTP smoke test server', false);
}

rrmdir($testRoot);

echo "\n=== SUMMARY ===\n";
$passed = count(array_filter($results, fn ($r) => $r['ok']));
$failed = count($results) - $passed;
echo "Tests passed: {$passed}/" . count($results) . " (failed: {$failed})\n";
echo "Release folder: {$releaseDir}\n";
echo "ZIP: {$zipPath}\n";

exit($failed > 0 ? 1 : 0);
