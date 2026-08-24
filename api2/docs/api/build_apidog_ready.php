<?php

declare(strict_types=1);

/**
 * Build Apidog-ready Postman Collection + Production Environment.
 * Source: SMEDC_API_Apidog_Testing_Final.json (cleaned bodies, no endpoint changes).
 *
 * Usage: php docs/api/build_apidog_ready.php
 */

$docsDir = __DIR__;
$source = $docsDir . '/SMEDC_API_Apidog_Testing_Final.json';
$collectionOut = $docsDir . '/SMEDC_Apidog_Ready.postman_collection.json';
$environmentOut = $docsDir . '/SMEDC_Prod.postman_environment.json';

/** All Spatie roles from RolePermissionSeeder (verified in project). */
const ACTORS = [
    'general_director',
    'admin',
    'deputy_general_director',
    'governor',
    'branch_manager',
    'branch_officer',
    'workforce_manager',
    'training_manager',
    'training_supervisor',
    'deputy_director',
    'center_user',
    'trainer_user',
    'trainee_user',
    'auditor',
    'data_entry',
    'data_reviewer',
    'project_services_manager',
    'development_manager',
    'local_development_manager',
    'finance_manager',
    'finance_officer',
    'consultant_office',
    'funding_partner',
    'consultant_union_admin',
    'central_bank_admin',
    'project_owner',
    'incubator_manager',
    'incubator_mentor',
    'entrepreneur_manager',
    'media_manager',
    'super_admin',
    'system_admin',
];

/** Public API paths (no auth:sanctum) — matched after stripping base_url. */
const PUBLIC_PATH_PATTERNS = [
    '#^/login$#',
    '#^/register$#',
    '#^/certificates/verify$#',
    '#^/verify-certificate/#',
    '#^/map/training-centers$#',
    '#^/signatures/verify/#',
    '#^/training-kit-public-requests$#',
    '#^/news$#',
    '#^/news/\d+$#',
    '#^/success-stories#',
    '#^/incubators$#',
    '#^/entrepreneur/profiles/public-stats$#',
    '#^/public/#',
];

const PROD_BASE_URL = 'https://new.smeda.gov.sy/api2/public/api';

const COLLECTION_PREREQUEST = <<<'JS'
const actor = pm.environment.get('active_actor');
if (!actor) {
    return;
}

const email = pm.environment.get(`${actor}_email`);
const password = pm.environment.get(`${actor}_password`);
const actorToken = pm.environment.get(`${actor}_token`);

if (email !== undefined && email !== null) {
    pm.environment.set('email', email);
}
if (password !== undefined && password !== null) {
    pm.environment.set('password', password);
}
if (actorToken) {
    pm.environment.set('token', actorToken);
}
JS;

const LOGIN_TEST_SCRIPT = <<<'JS'
const actor = pm.environment.get('active_actor');
const json = pm.response.json();

// AuthController::login returns { token, token_type, user, message }
const token = json && json.token ? json.token : '';

if (actor && token) {
    pm.environment.set(`${actor}_token`, token);
    pm.environment.set('token', token);
}

if (json && json.user && json.user.id) {
    pm.environment.set('user_id', String(json.user.id));
}
JS;

if (!is_file($source)) {
    fwrite(STDERR, "Source missing: {$source}\nRun fix_apidog_testing_collection.php first.\n");
    exit(1);
}

$data = json_decode((string) file_get_contents($source), true);
if (!is_array($data)) {
    fwrite(STDERR, "Invalid source JSON\n");
    exit(1);
}

$stats = [
    'endpoints' => 0,
    'duplicates' => 0,
    'public_no_auth' => 0,
    'protected_bearer' => 0,
    'token_migrations' => 0,
];

function extractPathFromRequest(array $request): string
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

    $raw = preg_replace('#^\{\{base_url\}\}#', '', $raw) ?? $raw;
    $raw = preg_replace('#^https?://[^/]+/api(?:/api)?#', '', $raw) ?? $raw;

    if ($raw === '' || $raw[0] !== '/') {
        $raw = '/' . ltrim($raw, '/');
    }

    return $raw;
}

function isPublicPath(string $path, string $method): bool
{
    foreach (PUBLIC_PATH_PATTERNS as $pattern) {
        if (preg_match($pattern, $path)) {
            return true;
        }
    }

    return false;
}

function normalizeUrl(array &$request): void
{
    $url = $request['url'] ?? null;
    if (!is_array($url)) {
        return;
    }

    $path = $url['path'] ?? [];
    if (!is_array($path) || $path === []) {
        $raw = (string) ($url['raw'] ?? '');
        $raw = preg_replace('#^\{\{base_url\}\}/?#', '', $raw) ?? $raw;
        $path = array_values(array_filter(explode('/', ltrim($raw, '/')), static fn ($p) => $p !== ''));
    }

    $request['url'] = [
        'raw' => '{{base_url}}/' . implode('/', $path),
        'host' => ['{{base_url}}'],
        'path' => $path,
    ];
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

    normalizeUrl($request);

    $method = strtoupper($request['method'] ?? 'GET');
    $path = extractPathFromRequest($request);
    $key = $method . ' ' . preg_replace('/\{[^}]+\}/', '{id}', $path);
    if (isset($seen[$key])) {
        $stats['duplicates']++;
    }
    $seen[$key] = true;

    $isLogin = $method === 'POST' && preg_match('#/login$#', $path);
    $isPublic = isPublicPath($path, $method) || $isLogin;

    if ($isLogin) {
        unset($request['auth']);
        $request['body'] = [
            'mode' => 'raw',
            'raw' => json_encode([
                'email' => '{{email}}',
                'password' => '{{password}}',
                'device_name' => 'apidog-client',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'options' => ['raw' => ['language' => 'json']],
        ];
        $item['event'] = [[
            'listen' => 'test',
            'script' => [
                'exec' => array_map('trim', explode("\n", LOGIN_TEST_SCRIPT)),
                'type' => 'text/javascript',
            ],
        ]];
        return;
    }

    if ($isPublic) {
        unset($request['auth']);
        $stats['public_no_auth']++;
        return;
    }

    $request['auth'] = [
        'type' => 'bearer',
        'bearer' => [[
            'key' => 'token',
            'value' => '{{token}}',
            'type' => 'string',
        ]],
    ];
    $stats['protected_bearer']++;
});

$data['info']['name'] = 'SMEDC Authority API (Apidog Ready)';
$data['info']['description'] = 'SMEDC API for Apidog import. Use SMEDC_Prod.postman_environment.json. Set active_actor then run Login. Token path: response JSON $.token (AuthController::login).';

$data['variable'] = [
    ['key' => 'user_id', 'value' => ''],
];

$data['event'] = [[
    'listen' => 'prerequest',
    'script' => [
        'type' => 'text/javascript',
        'exec' => array_map('trim', explode("\n", COLLECTION_PREREQUEST)),
    ],
]];

$collectionJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($collectionJson === false) {
    fwrite(STDERR, "Failed to encode collection\n");
    exit(1);
}
file_put_contents($collectionOut, $collectionJson . "\n");

// Environment
$envValues = [
    ['key' => 'base_url', 'value' => PROD_BASE_URL, 'type' => 'default', 'enabled' => true],
    ['key' => 'active_actor', 'value' => 'admin', 'type' => 'default', 'enabled' => true],
    ['key' => 'email', 'value' => '', 'type' => 'secret', 'enabled' => true],
    ['key' => 'password', 'value' => '', 'type' => 'secret', 'enabled' => true],
    ['key' => 'token', 'value' => '', 'type' => 'secret', 'enabled' => true],
    ['key' => 'user_id', 'value' => '', 'type' => 'default', 'enabled' => true],
];

foreach (ACTORS as $actor) {
    $envValues[] = ['key' => "{$actor}_email", 'value' => '', 'type' => 'secret', 'enabled' => true];
    $envValues[] = ['key' => "{$actor}_password", 'value' => '', 'type' => 'secret', 'enabled' => true];
    $envValues[] = ['key' => "{$actor}_token", 'value' => '', 'type' => 'secret', 'enabled' => true];
}

$environment = [
    'id' => 'smedc-prod-env',
    'name' => 'SMEDC Production',
    'values' => $envValues,
    '_postman_variable_scope' => 'environment',
    '_postman_exported_at' => gmdate('Y-m-d\TH:i:s\Z'),
    '_postman_exported_using' => 'SMEDC Apidog Builder',
];

$envJson = json_encode($environment, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($envJson === false) {
    fwrite(STDERR, "Failed to encode environment\n");
    exit(1);
}
file_put_contents($environmentOut, $envJson . "\n");

// Validate
$verify = json_decode((string) file_get_contents($collectionOut), true);
if (!is_array($verify)) {
    fwrite(STDERR, "Collection validation failed\n");
    exit(1);
}

echo "=== SMEDC Apidog Ready Build ===\n";
echo "Collection: {$collectionOut}\n";
echo "Environment: {$environmentOut}\n";
echo 'Endpoints: ' . $stats['endpoints'] . "\n";
echo 'Duplicate method+path: ' . $stats['duplicates'] . "\n";
echo 'Actors: ' . count(ACTORS) . "\n";
echo 'Public (no auth): ' . $stats['public_no_auth'] . "\n";
echo 'Protected (Bearer {{token}}): ' . $stats['protected_bearer'] . "\n";
echo "Token JSON path: \$.token\n";
echo "Default active_actor: admin\n";
echo "Base URL (environment): " . PROD_BASE_URL . "\n";

if ($stats['duplicates'] > 0) {
    exit(2);
}

exit(0);
