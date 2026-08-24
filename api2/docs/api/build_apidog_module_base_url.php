<?php

declare(strict_types=1);

/**
 * Build Apidog import files with relative paths only (Module Base URL in Apidog).
 * Source: SMEDC_Apidog_Ready.postman_collection.json + SMEDC_Prod.postman_environment.json
 *
 * Usage: php docs/api/build_apidog_module_base_url.php
 */

$docsDir = __DIR__;
$sourceCollection = $docsDir . '/SMEDC_Apidog_Ready.postman_collection.json';
$sourceEnvironment = $docsDir . '/SMEDC_Prod.postman_environment.json';
$collectionOut = $docsDir . '/SMEDC_Apidog_Ready_ModuleBaseURL.postman_collection.json';
$environmentOut = $docsDir . '/SMEDC_Prod_ModuleBaseURL.postman_environment.json';

const PROD_BASE_URL = 'https://new.smeda.gov.sy/api2/public/api';

if (!is_file($sourceCollection) || !is_file($sourceEnvironment)) {
    fwrite(STDERR, "Run build_apidog_ready.php first.\n");
    exit(1);
}

$data = json_decode((string) file_get_contents($sourceCollection), true);
$env = json_decode((string) file_get_contents($sourceEnvironment), true);

if (!is_array($data) || !is_array($env)) {
    fwrite(STDERR, "Invalid source JSON\n");
    exit(1);
}

$stats = [
    'endpoints' => 0,
    'duplicates' => 0,
    'public_no_auth' => 0,
    'protected_bearer' => 0,
    'login_ok' => false,
    'token_script_ok' => false,
    'prerequest_ok' => false,
];

function extractRelativePath(array $request): string
{
    $url = $request['url'] ?? '';
    if (is_array($url)) {
        $path = $url['path'] ?? [];
        if (is_array($path) && $path !== []) {
            return '/' . implode('/', $path);
        }
        $raw = (string) ($url['raw'] ?? '');
    } else {
        $raw = (string) $url;
    }

    $raw = preg_replace('#^\{\{base_url\}\}/?#', '', $raw) ?? $raw;
    $raw = preg_replace('#^https?://[^/]+(?:/api2/public/api|/api/api|/api)?/?#', '', $raw) ?? $raw;
    $raw = '/' . ltrim($raw, '/');

    return $raw;
}

function toRelativeUrl(array &$request): string
{
    $path = extractRelativePath($request);
    $segments = array_values(array_filter(explode('/', ltrim($path, '/')), static fn ($s) => $s !== ''));

    $request['url'] = [
        'raw' => $path,
        'path' => $segments,
    ];

    return $path;
}

function walkItems(array &$items, callable $fn): void
{
    foreach ($items as &$item) {
        if (isset($item['request'])) {
            $fn($item);
        }
        if (isset($item['item'])) {
            walkItems($item['item'], $fn);
        }
    }
}

$seen = [];

walkItems($data['item'], function (array &$item) use (&$seen, &$stats): void {
    $request = &$item['request'];
    $stats['endpoints']++;

    $path = toRelativeUrl($request);
    $method = strtoupper($request['method'] ?? 'GET');
    $key = $method . ' ' . preg_replace('/\{[^}]+\}/', '{id}', $path);

    if (isset($seen[$key])) {
        $stats['duplicates']++;
    }
    $seen[$key] = true;

    if ($method === 'POST' && $path === '/login') {
        $stats['login_ok'] = ($request['url']['raw'] ?? '') === '/login';
        foreach ($item['event'] ?? [] as $event) {
            if (($event['listen'] ?? '') === 'test') {
                $exec = implode("\n", $event['script']['exec'] ?? []);
                if (str_contains($exec, 'json.token') && str_contains($exec, '${actor}_token')) {
                    $stats['token_script_ok'] = true;
                }
            }
        }
    }

    if (isset($request['auth']['type']) && $request['auth']['type'] === 'bearer') {
        $token = $request['auth']['bearer'][0]['value'] ?? '';
        if ($token === '{{token}}') {
            $stats['protected_bearer']++;
        }
    } elseif (!isset($request['auth'])) {
        $stats['public_no_auth']++;
    }
});

foreach ($data['event'] ?? [] as $event) {
    if (($event['listen'] ?? '') === 'prerequest') {
        $exec = implode("\n", $event['script']['exec'] ?? []);
        if (str_contains($exec, 'active_actor')
            && str_contains($exec, '${actor}_email')
            && !str_contains($exec, 'base_url')) {
            $stats['prerequest_ok'] = true;
        }
    }
}

$data['info']['name'] = 'SMEDC Authority API (Apidog Module Base URL)';
$data['info']['description'] = 'SMEDC API for Apidog. Set Module Base URL to https://new.smeda.gov.sy/api2/public/api. Import SMEDC_Prod_ModuleBaseURL.postman_environment.json. Paths are relative (e.g. /login). Token: $.token.';

if (isset($data['variable'])) {
    $data['variable'] = array_values(array_filter(
        $data['variable'],
        static fn ($v) => ($v['key'] ?? '') !== 'base_url'
    ));
}

$env['name'] = 'SMEDC Production (Module Base URL)';
$env['id'] = 'smedc-prod-module-base-url-env';
$env['values'] = array_values(array_filter(
    $env['values'] ?? [],
    static fn ($v) => ($v['key'] ?? '') !== 'base_url'
));
$env['_postman_exported_at'] = gmdate('Y-m-d\TH:i:s\Z');

file_put_contents(
    $collectionOut,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);
file_put_contents(
    $environmentOut,
    json_encode($env, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);

// Final validation scan
$collectionText = (string) file_get_contents($collectionOut);
$envText = (string) file_get_contents($environmentOut);

$baseUrlInCollection = substr_count($collectionText, '{{base_url}}');
$baseUrlInEnv = substr_count($envText, 'base_url');
$prodUrlInCollectionRequests = 0;

$verifyData = json_decode($collectionText, true);
$verifyItems = $verifyData['item'] ?? [];
walkItems($verifyItems, function (array $item) use (&$prodUrlInCollectionRequests): void {
    $req = $item['request'] ?? [];
    $blob = json_encode($req['url'] ?? '');
    if ($blob && str_contains($blob, PROD_BASE_URL)) {
        $prodUrlInCollectionRequests++;
    }
});

$descOccurrences = substr_count($collectionText, PROD_BASE_URL);

echo "=== Module Base URL Build ===\n";
echo "Collection: {$collectionOut}\n";
echo "Environment: {$environmentOut}\n";
echo 'Endpoints: ' . $stats['endpoints'] . "\n";
echo 'Duplicates: ' . $stats['duplicates'] . "\n";
echo '{{base_url}} in collection: ' . $baseUrlInCollection . "\n";
echo 'base_url in environment: ' . ($baseUrlInEnv > 0 ? 'yes' : 'no') . "\n";
echo 'Full prod URL in request URLs: ' . $prodUrlInCollectionRequests . "\n";
echo 'Full prod URL in collection (incl. description): ' . $descOccurrences . "\n";
echo 'Login /login: ' . ($stats['login_ok'] ? 'yes' : 'no') . "\n";
echo '$.token script: ' . ($stats['token_script_ok'] ? 'yes' : 'no') . "\n";
echo 'Actor prerequest: ' . ($stats['prerequest_ok'] ? 'yes' : 'no') . "\n";
echo 'Protected Bearer {{token}}: ' . $stats['protected_bearer'] . "\n";
echo 'Public no auth: ' . $stats['public_no_auth'] . "\n";
echo 'Actor env vars kept: ' . count(array_filter($env['values'], static fn ($v) => str_ends_with($v['key'], '_email'))) . " actors\n";

if ($stats['endpoints'] !== 379 || $stats['duplicates'] > 0 || $baseUrlInCollection > 0 || $prodUrlInCollectionRequests > 0) {
    exit(2);
}

exit(0);
