<?php

declare(strict_types=1);

/**
 * Build Global-ready Apidog package (Globals + Clean Environment + Collection).
 *
 * Usage: php docs/api/build_apidog_global_ready.php
 */

$docsDir = __DIR__;
$sourceCollection = $docsDir . '/SMEDC_Apidog_Final.postman_collection.json';
$sourcePrefilledEnv = $docsDir . '/SMEDC_Production_Prefilled.postman_environment.json';

$collectionOut = $docsDir . '/SMEDC_Apidog_Final_GlobalReady.postman_collection.json';
$globalsOut = $docsDir . '/SMEDC_Project_Globals.postman_globals.json';
$environmentOut = $docsDir . '/SMEDC_Production_Clean.postman_environment.json';

const MODULE_BASE_URL = 'https://new.smeda.gov.sy/api2/public/api';

const ACTORS = [
    'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
    'system_admin', 'governor', 'branch_manager', 'branch_officer', 'workforce_manager',
    'training_manager', 'training_supervisor', 'center_user', 'trainer_user', 'trainee_user',
    'auditor', 'data_entry', 'data_reviewer', 'project_services_manager', 'development_manager',
    'local_development_manager', 'finance_manager', 'finance_officer', 'consultant_office',
    'funding_partner', 'consultant_union_admin', 'central_bank_admin', 'project_owner',
    'incubator_manager', 'incubator_mentor', 'entrepreneur_manager', 'media_manager',
];

const LOGIN_OK_ACTORS = [
    'general_director', 'deputy_general_director', 'deputy_director', 'governor',
    'branch_manager', 'training_manager', 'center_user', 'trainer_user', 'trainee_user',
    'auditor', 'data_entry', 'data_reviewer', 'finance_manager',
];

const COLLECTION_PREREQUEST = <<<'JS'
// Data-driven actor iteration (Collection Runner / Apidog test data)
try {
    if (typeof pm.iterationData !== 'undefined' && pm.iterationData) {
        const data = pm.iterationData.toObject ? pm.iterationData.toObject() : {};
        if (data.active_actor) {
            pm.environment.set('active_actor', data.active_actor);
        }
    }
} catch (e) {
    // iterationData not available outside runner
}

const actor =
    pm.environment.get('active_actor') ||
    pm.globals.get('active_actor');

if (!actor) {
    throw new Error('active_actor is not configured');
}

const email =
    pm.environment.get(`${actor}_email`) ||
    pm.globals.get(`${actor}_email`);

const password =
    pm.environment.get(`${actor}_password`) ||
    pm.globals.get(`${actor}_password`);

const actorToken =
    pm.environment.get(`${actor}_token`);

if (!email) {
    throw new Error(`Email not configured for actor: ${actor}`);
}

if (!password) {
    throw new Error(`Password not configured for actor: ${actor}`);
}

pm.environment.set('email', email);
pm.environment.set('password', password);

if (actorToken) {
    pm.environment.set('token', actorToken);
}
JS;

const LOGIN_TEST_SCRIPT = <<<'JS'
const actor =
    pm.environment.get('active_actor') ||
    pm.globals.get('active_actor');

const json = pm.response.json();
const token = json && json.token ? json.token : '';

if (actor && token) {
    pm.environment.set(`${actor}_token`, token);
    pm.environment.set('token', token);
}

if (json && json.user && json.user.id) {
    pm.environment.set('user_id', String(json.user.id));
}

pm.test('Login returns token at $.token', function () {
    pm.expect(json).to.have.property('token');
    pm.expect(json.token).to.be.a('string').and.not.empty;
});
JS;

if (!is_file($sourceCollection) || !is_file($sourcePrefilledEnv)) {
    fwrite(STDERR, "Source files missing.\n");
    exit(1);
}

$collection = json_decode((string) file_get_contents($sourceCollection), true);
$prefilled = json_decode((string) file_get_contents($sourcePrefilledEnv), true);

if (!is_array($collection) || !is_array($prefilled)) {
    fwrite(STDERR, "Invalid source JSON.\n");
    exit(1);
}

// Extract credentials from prefilled environment
$credentials = [];
foreach ($prefilled['values'] ?? [] as $entry) {
    $key = $entry['key'] ?? '';
    if (preg_match('/^(.+)_(email|password)$/', $key, $m)) {
        $credentials[$m[1]][$m[2]] = $entry['value'] ?? '';
    }
}

// --- Globals ---
$globalsValues = [
    globalVar('active_actor', 'general_director', 'default'),
];

foreach (ACTORS as $actor) {
    $email = $credentials[$actor]['email'] ?? '';
    $password = $credentials[$actor]['password'] ?? '';
    if ($email === '' || $password === '') {
        fwrite(STDERR, "Missing credentials for actor: {$actor}\n");
        exit(1);
    }

    $globalsValues[] = globalVar("{$actor}_email", $email, 'secret');
    $globalsValues[] = globalVar("{$actor}_password", $password, 'secret');
    $status = in_array($actor, LOGIN_OK_ACTORS, true) ? 'LOGIN_OK' : 'LOGIN_FAILED';
    $globalsValues[] = globalVar("{$actor}_login_status", $status, 'default');
}

$globals = [
    'id' => 'smedc-project-globals',
    'name' => 'SMEDC Project Globals',
    'values' => $globalsValues,
    '_postman_variable_scope' => 'globals',
    '_postman_exported_at' => gmdate('Y-m-d\TH:i:s\Z'),
    '_postman_exported_using' => 'SMEDC Apidog Global Builder',
];

// --- Clean Environment (runtime only) ---
$envValues = [
    envVar('email', '', 'secret'),
    envVar('password', '', 'secret'),
    envVar('token', '', 'secret'),
    envVar('user_id', '', 'default'),
];

foreach (ACTORS as $actor) {
    $envValues[] = envVar("{$actor}_token", '', 'secret');
}

$environment = [
    'id' => 'smedc-production-clean-env',
    'name' => 'SMEDC Production',
    'values' => $envValues,
    '_postman_variable_scope' => 'environment',
    '_postman_exported_at' => gmdate('Y-m-d\TH:i:s\Z'),
    '_postman_exported_using' => 'SMEDC Apidog Global Builder',
];

// --- Collection updates ---
$collection['info']['name'] = 'SMEDC Authority API (Apidog Global Ready)';
$collection['info']['description'] = implode("\n", [
    'SMEDC API — Global-ready for Apidog/Postman.',
    'Import order: 1) Collection  2) SMEDC_Project_Globals.postman_globals.json  3) SMEDC_Production_Clean.postman_environment.json',
    'Module Base URL: ' . MODULE_BASE_URL,
    'Credentials live in Project Globals. Tokens live in Environment only (after POST /login).',
    'Default active_actor: general_director (in Globals).',
]);

$collection['event'] = [[
    'listen' => 'prerequest',
    'script' => ['type' => 'text/javascript', 'exec' => scriptToExec(COLLECTION_PREREQUEST)],
]];

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

walkItems($collection['item'], function (array &$item): void {
    $request = &$item['request'];
    $path = extractPath($request);
    $method = strtoupper($request['method'] ?? 'GET');

    if ($method === 'POST' && $path === '/login') {
        $item['event'] = [[
            'listen' => 'test',
            'script' => ['type' => 'text/javascript', 'exec' => scriptToExec(LOGIN_TEST_SCRIPT)],
        ]];
    }
});

writeJson($globalsOut, $globals);
writeJson($environmentOut, $environment);
writeJson($collectionOut, $collection);

$validation = validate($collectionOut, $globalsOut, $environmentOut);
$loginOk = testGeneralDirectorLogin();

printReport($validation, $loginOk);

exit($validation['passed'] && $loginOk ? 0 : 2);

// ---------------------------------------------------------------------------

function globalVar(string $key, string $value, string $type): array
{
    return ['key' => $key, 'value' => $value, 'type' => $type, 'enabled' => true];
}

function envVar(string $key, string $value, string $type): array
{
    return ['key' => $key, 'value' => $value, 'type' => $type, 'enabled' => true];
}

function scriptToExec(string $script): array
{
    return array_values(explode("\n", str_replace("\r\n", "\n", $script)));
}

function extractPath(array $request): string
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

    return '/' . ltrim(preg_replace('#^\{\{base_url\}\}/?#', '', $raw) ?? $raw, '/');
}

function writeJson(string $path, mixed $data): void
{
    file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
    );
}

function validate(string $collectionPath, string $globalsPath, string $envPath): array
{
    $collection = json_decode((string) file_get_contents($collectionPath), true);
    $globals = json_decode((string) file_get_contents($globalsPath), true);
    $env = json_decode((string) file_get_contents($envPath), true);

    $globalKeys = [];
    foreach ($globals['values'] ?? [] as $v) {
        $globalKeys[$v['key']] = $v['value'] ?? '';
    }
    $envKeys = [];
    foreach ($env['values'] ?? [] as $v) {
        $envKeys[$v['key']] = $v['value'] ?? '';
    }

    $stats = ['endpoints' => 0, 'duplicates' => 0, 'base_url' => 0, 'full_url' => 0];
    $seen = [];
    $protectedBearer = 0;
    $publicNoAuth = 0;
    $loginOk = false;
    $loginVars = false;
    $tokenScriptOk = false;

    walkItems($collection['item'], function (array $item) use (&$stats, &$seen, &$protectedBearer, &$publicNoAuth, &$loginOk, &$loginVars, &$tokenScriptOk): void {
        $request = $item['request'] ?? [];
        $stats['endpoints']++;
        $path = extractPath($request);
        $method = strtoupper($request['method'] ?? 'GET');
        $key = $method . ' ' . preg_replace('/\{[^}]+\}/', '{id}', $path);
        if (isset($seen[$key])) {
            $stats['duplicates']++;
        }
        $seen[$key] = true;

        $blob = json_encode($request);
        if ($blob && str_contains($blob, '{{base_url}}')) {
            $stats['base_url']++;
        }
        if ($blob && str_contains($blob, MODULE_BASE_URL)) {
            $stats['full_url']++;
        }

        if ($method === 'POST' && $path === '/login') {
            $loginOk = ($request['url']['raw'] ?? '') === '/login';
            $body = $request['body']['raw'] ?? '';
            $loginVars = str_contains($body, '{{email}}') && str_contains($body, '{{password}}');
            foreach ($item['event'] ?? [] as $ev) {
                if (($ev['listen'] ?? '') === 'test') {
                    $exec = implode("\n", $ev['script']['exec'] ?? []);
                    if (str_contains($exec, 'pm.globals.get') && str_contains($exec, '${actor}_token')) {
                        $tokenScriptOk = true;
                    }
                }
            }
        }

        if (($request['auth']['type'] ?? '') === 'bearer' && ($request['auth']['bearer'][0]['value'] ?? '') === '{{token}}') {
            $protectedBearer++;
        } elseif (!isset($request['auth'])) {
            $publicNoAuth++;
        }
    });

    $prerequest = '';
    foreach ($collection['event'] ?? [] as $ev) {
        if (($ev['listen'] ?? '') === 'prerequest') {
            $prerequest = implode("\n", $ev['script']['exec'] ?? []);
        }
    }

    $hasGlobalToken = isset($globalKeys['token']);
    $hasActorTokensInGlobals = false;
    foreach (array_keys($globalKeys) as $k) {
        if (str_ends_with($k, '_token')) {
            $hasActorTokensInGlobals = true;
        }
    }

    $hasCredentialsInEnv = false;
    foreach (array_keys($envKeys) as $k) {
        if (str_ends_with($k, '_email') || str_ends_with($k, '_password')) {
            $hasCredentialsInEnv = true;
        }
    }

    $checks = [
        'json_valid' => is_array($collection) && is_array($globals) && is_array($env),
        'globals_scope' => ($globals['_postman_variable_scope'] ?? '') === 'globals',
        'env_scope' => ($env['_postman_variable_scope'] ?? '') === 'environment',
        'active_actor_global' => ($globalKeys['active_actor'] ?? null) === 'general_director',
        'general_director_creds' => ($globalKeys['general_director_email'] ?? '') === 'general@system.com'
            && ($globalKeys['general_director_password'] ?? '') === '12345678',
        'no_token_in_globals' => !$hasGlobalToken && !$hasActorTokensInGlobals,
        'no_credentials_in_env' => !$hasCredentialsInEnv && !isset($envKeys['active_actor']),
        'prerequest_global_fallback' => str_contains($prerequest, 'pm.globals.get')
            && str_contains($prerequest, 'pm.environment.get(`${actor}_token`)'),
        'endpoints_379' => $stats['endpoints'] === 379,
        'duplicates_0' => $stats['duplicates'] === 0,
        'no_base_url' => $stats['base_url'] === 0,
        'no_full_url' => $stats['full_url'] === 0,
        'login_ok' => $loginOk && $loginVars && $tokenScriptOk,
    ];

    $passed = !in_array(false, $checks, true);

    return [
        'passed' => $passed,
        'checks' => $checks,
        'stats' => $stats,
        'protected_bearer' => $protectedBearer,
        'public_no_auth' => $publicNoAuth,
    ];
}

function testGeneralDirectorLogin(): bool
{
    $ch = curl_init(MODULE_BASE_URL . '/login');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode([
            'email' => 'general@system.com',
            'password' => '12345678',
            'device_name' => 'apidog-global-ready-verify',
        ]),
        CURLOPT_TIMEOUT => 25,
    ]);
    $raw = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode((string) $raw, true);

    return $http === 200 && !empty($json['token']);
}

function printReport(array $validation, bool $loginOk): void
{
    echo "=== SMEDC Global Ready Build ===\n";
    foreach ($validation['checks'] as $k => $v) {
        echo ($v ? 'OK' : 'FAIL') . " {$k}\n";
    }
    echo 'Endpoints: ' . $validation['stats']['endpoints'] . "\n";
    echo 'Duplicates: ' . $validation['stats']['duplicates'] . "\n";
    echo 'Login general_director: ' . ($loginOk ? 'LOGIN_OK' : 'LOGIN_FAILED') . "\n";
    echo 'Build: ' . ($validation['passed'] && $loginOk ? 'PASSED' : 'FAILED') . "\n";
}
