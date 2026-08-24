<?php

declare(strict_types=1);

/**
 * Build SMEDC_Apidog_Final_WithSamples.postman_collection.json
 * from SMEDC_Apidog_Final.postman_collection.json + Laravel validation rules.
 *
 * Does NOT call production. Read-only code analysis + local JSON transform.
 *
 * Usage: php docs/api/build_apidog_with_samples.php
 */

$docsDir = __DIR__;
$apiRoot = dirname($docsDir, 2);

require $apiRoot . '/vendor/autoload.php';
require $docsDir . '/lib/Support.php';
require $docsDir . '/lib/MethodInspector.php';
require $docsDir . '/lib/SampleValueGenerator.php';
require $docsDir . '/lib/ValidationRuleEnricher.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require $apiRoot . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sourceCollection = $docsDir . '/SMEDC_Apidog_Final.postman_collection.json';
$endpointsFile = $docsDir . '/_endpoints.json';
$rbacFile = $docsDir . '/SMEDC_RBAC_TestMatrix.json';
$outputCollection = $docsDir . '/SMEDC_Apidog_Final_WithSamples.postman_collection.json';
$reportFile = $docsDir . '/SMEDC_Apidog_WithSamples_Report.json';

if (!is_file($sourceCollection) || !is_file($endpointsFile)) {
    fwrite(STDERR, "Missing source collection or _endpoints.json\n");
    exit(1);
}

/** @var list<string> */
const MULTIPART_ENDPOINTS = [
    'POST api/finance/applications/{applicationId}/documents',
    'POST api/ai/knowledge/ingest',
    'POST api/registration-requests/centers',
    'POST api/consulting/requests/{id}/attachments',
    'POST api/consulting/contracts/{id}/report',
    'POST api/my-electronic-signature',
    'POST api/workforce/job-applications',
];

/** @var array<string, list<string>> */
const MULTIPART_FILE_FIELDS = [
    'POST api/finance/applications/{applicationId}/documents' => ['file'],
    'POST api/ai/knowledge/ingest' => ['files'],
    'POST api/registration-requests/centers' => ['license_image'],
    'POST api/consulting/requests/{id}/attachments' => ['file'],
    'POST api/consulting/contracts/{id}/report' => ['file'],
    'POST api/my-electronic-signature' => ['signature'],
    'POST api/workforce/job-applications' => ['cv'],
];

/** @var array<string, string> */
const PATH_ID_VAR_HINTS = [
    'users' => 'user_id',
    'trainers' => 'trainer_id',
    'trainees' => 'trainee_id',
    'training-centers' => 'training_center_id',
    'branches' => 'branch_id',
    'governorates' => 'governorate_id',
    'certificates' => 'certificate_id',
    'funding-applications' => 'funding_application_id',
    'finance/applications' => 'funding_application_id',
    'courses' => 'course_id',
    'programs' => 'program_id',
    'roles' => 'role_id',
    'permissions' => 'permission_id',
    'needs' => 'need_id',
    'incubators' => 'incubator_id',
    'news' => 'news_id',
    'agreements' => 'agreement_id',
    'consulting/requests' => 'consulting_request_id',
    'consulting/contracts' => 'consulting_contract_id',
    'workforce/job-postings' => 'job_posting_id',
    'training-programs' => 'training_program_id',
    'training-courses' => 'training_course_id',
    'training-kits' => 'training_kit_id',
    'funding-partners' => 'funding_partner_id',
    'financial-records' => 'financial_record_id',
    'success-stories' => 'success_story_id',
    'staff-training-requests' => 'staff_training_request_id',
];

$collection = json_decode((string) file_get_contents($sourceCollection), true);
$endpointsRaw = json_decode((string) file_get_contents($endpointsFile), true);
$rbac = is_file($rbacFile) ? json_decode((string) file_get_contents($rbacFile), true) : null;

if (!is_array($collection) || !is_array($endpointsRaw)) {
    fwrite(STDERR, "Invalid JSON input\n");
    exit(1);
}

$endpointByKey = [];
foreach ($endpointsRaw as $ep) {
    if (!empty($ep['key'])) {
        $endpointByKey[$ep['key']] = $ep;
    }
}

$rbacByKey = [];
if (is_array($rbac) && isset($rbac['entries']) && is_array($rbac['entries'])) {
    foreach ($rbac['entries'] as $entry) {
        if (!empty($entry['key'])) {
            $rbacByKey[$entry['key']] = $entry;
        }
    }
}

$generator = new ApiDocs\SampleValueGenerator();
$enricher = new ApiDocs\ValidationRuleEnricher($apiRoot);

$stats = [
    'total_endpoints' => 0,
    'bodies_incomplete_before' => 0,
    'bodies_filled' => 0,
    'bodies_unchanged' => 0,
    'multipart_configured' => 0,
    'path_vars_updated' => 0,
    'id_capture_scripts_added' => 0,
    'mutation_endpoints' => 0,
    'production_requests_sent' => 0,
    'unresolved_fields' => [],
    'new_variables' => [],
    'actor_specific_endpoints' => [],
];

function normalizeValidationRules(array $rules): array
{
    $out = [];
    foreach ($rules as $field => $fieldRules) {
        if (!is_array($fieldRules)) {
            continue;
        }
        $normalized = [];
        foreach ($fieldRules as $rule) {
            $ruleStr = (string) $rule;
            if (str_contains($ruleStr, '|')) {
                foreach (explode('|', $ruleStr) as $part) {
                    $part = trim($part);
                    if ($part !== '') {
                        $normalized[] = $part;
                    }
                }
            } else {
                $normalized[] = $ruleStr;
            }
        }
        $out[$field] = $normalized;
    }

    return $out;
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

function extractPathFromRequest(array $request): string
{
    $url = $request['url'] ?? '';
    if (is_array($url)) {
        $raw = (string) ($url['raw'] ?? '');
        if ($raw === '' && isset($url['path']) && is_array($url['path'])) {
            $raw = '/' . implode('/', $url['path']);
        }
    } else {
        $raw = (string) $url;
    }

    return '/' . ltrim($raw, '/');
}

function classifySafety(string $method, string $path): string
{
    $method = strtoupper($method);
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return 'READ_ONLY';
    }
    if ($method === 'DELETE') {
        return 'DELETE';
    }
    if (in_array($method, ['PUT', 'PATCH'], true)) {
        return 'UPDATE';
    }
    if ($method === 'POST') {
        if (preg_match('#/(approve|reject|submit|publish|sync|verify|decision|activate|deactivate|cancel|complete|archive|restore|assign|unassign|mark-read|toggle|reset-password|change-password|logout|login|ingest|export|import|bulk|batch|send|dispatch|revoke|terminate|close|reopen|fund|disburse|return|forward|escalate|acknowledge|confirm|decline|withdraw|reconcile|notify|refresh|regenerate|rotate|invalidate|lock|unlock|suspend|unsuspend|ban|unban)(/|$)#i', $path)) {
            return 'ACTION';
        }

        return 'CREATE';
    }

    return 'READ_ONLY';
}

function isBodyIncomplete(?array $body): bool
{
    if ($body === null || $body === []) {
        return false;
    }

    foreach ($body as $value) {
        if ($value === '' || $value === null) {
            return true;
        }
        if ($value === [] && !is_int($value)) {
            return true;
        }
        if (is_array($value)) {
            if ($value === []) {
                return true;
            }
            foreach ($value as $v) {
                if ($v === '' || $v === null || $v === []) {
                    return true;
                }
            }
        }
    }

    return false;
}

function decodeBodyRaw(?array $bodySection): ?array
{
    if (!isset($bodySection['raw']) || !is_string($bodySection['raw'])) {
        return null;
    }
    $decoded = json_decode($bodySection['raw'], true);

    return is_array($decoded) ? $decoded : null;
}

function recommendedActors(?array $rbacEntry, array $endpoint): array
{
    $preferred = [];
    if (is_array($rbacEntry) && isset($rbacEntry['expected_by_actor']) && is_array($rbacEntry['expected_by_actor'])) {
        foreach ($rbacEntry['expected_by_actor'] as $actor => $info) {
            if (($info['result'] ?? '') === 'allowed') {
                $preferred[] = $actor;
            }
        }
    }

    $nonAdmin = array_values(array_filter($preferred, static fn ($a) => !in_array($a, [
        'admin', 'super_admin', 'general_director',
    ], true)));

    if ($nonAdmin !== []) {
        return $nonAdmin;
    }

    return $preferred;
}

function buildDescription(string $safety, array $actors, array $endpoint, array $unresolvedForEndpoint): string
{
    $lines = [
        'Safety: ' . $safety,
    ];

    if ($actors !== []) {
        $lines[] = 'Recommended actor(s): ' . implode(', ', array_slice($actors, 0, 5))
            . (count($actors) > 5 ? ' (+' . (count($actors) - 5) . ' more)' : '');
    }

    if (!empty($endpoint['permissions'])) {
        $lines[] = 'Permissions: ' . implode(', ', $endpoint['permissions']);
    }
    if (!empty($endpoint['roles'])) {
        $lines[] = 'Roles: ' . implode(', ', $endpoint['roles']);
    }

    if ($unresolvedForEndpoint !== []) {
        $lines[] = 'Unresolved sample fields: ' . implode('; ', $unresolvedForEndpoint);
    }

    return implode("\n", $lines);
}

function idVarForPath(string $path): string
{
    foreach (PATH_ID_VAR_HINTS as $segment => $var) {
        if (str_contains($path, '/' . $segment . '/')) {
            return $var;
        }
    }

    return 'last_created_id';
}

function buildIdCaptureScript(string $path): array
{
    $var = idVarForPath($path);

    return [
        'try {',
        '    const json = pm.response.json();',
        '    const candidates = [',
        '        json && json.id,',
        '        json && json.data && json.data.id,',
        '        json && json.data && json.data.data && json.data.data.id,',
        '        json && json.user && json.user.id,',
        '        json && json.trainer && json.trainer.id,',
        '        json && json.trainee && json.trainee.id,',
        '    ].filter(v => v !== undefined && v !== null);',
        '    if (candidates.length) {',
        '        pm.environment.set("' . $var . '", String(candidates[0]));',
        '    }',
        '} catch (e) {',
        '    // non-JSON response',
        '}',
    ];
}

function replacePathVariables(array &$request, ApiDocs\SampleValueGenerator $generator, string $uri, int &$pathVarCount): void
{
    if (!isset($request['url']) || !is_array($request['url'])) {
        return;
    }

    $path = $request['url']['path'] ?? null;
    if (!is_array($path)) {
        return;
    }

    $changed = false;
    foreach ($path as $i => $segment) {
        if (preg_match('/^\{([^}]+)\}$/', (string) $segment, $m)) {
            $var = $generator->pathParamVariable($m[1], $uri);
            $varName = trim($var, '{}');
            $newSegment = '{{' . $varName . '}}';
            if ($path[$i] !== $newSegment) {
                $path[$i] = $newSegment;
                $changed = true;
            }
        }
    }

    if ($changed) {
        $request['url']['path'] = $path;
        $request['url']['raw'] = '/' . implode('/', $path);
        $pathVarCount++;
    }
}

function buildMultipartBody(string $itemName, array $sampleBody, array $fileFields): array
{
    $formdata = [];
    foreach ($sampleBody as $key => $value) {
        if (in_array($key, $fileFields, true)) {
            continue;
        }
        $formdata[] = [
            'key' => $key,
            'value' => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
            'type' => 'text',
        ];
    }
    foreach ($fileFields as $field) {
        $formdata[] = [
            'key' => $field,
            'description' => 'Attach a real file when testing locally — not auto-filled.',
            'type' => 'file',
            'src' => [],
        ];
    }

    return [
        'mode' => 'formdata',
        'formdata' => $formdata,
    ];
}

$pathVarUpdates = 0;

walkItems($collection['item'], function (array &$item) use (
    &$stats,
    &$pathVarUpdates,
    $endpointByKey,
    $rbacByKey,
    $generator,
    $enricher
): void {
    $stats['total_endpoints']++;
    $request = &$item['request'];
    $itemName = (string) ($item['name'] ?? '');
    $method = strtoupper((string) ($request['method'] ?? 'GET'));
    $path = extractPathFromRequest($request);
    $safety = classifySafety($method, $path);

    if (in_array($safety, ['CREATE', 'UPDATE', 'DELETE', 'ACTION'], true)) {
        $stats['mutation_endpoints']++;
    }

    $endpoint = $endpointByKey[$itemName] ?? null;
    $rbacKey = $method . ' ' . $path;
    $rbacEntry = $rbacByKey[$rbacKey] ?? null;
    $actors = $endpoint ? recommendedActors($rbacEntry, $endpoint) : [];

    if ($actors !== [] && !in_array('admin', $actors, true) && count($actors) <= 6) {
        $stats['actor_specific_endpoints'][] = [
            'key' => $itemName,
            'actors' => $actors,
        ];
    } elseif ($actors !== [] && count(array_diff($actors, ['admin', 'super_admin', 'general_director'])) > 0) {
        $specific = array_values(array_diff($actors, ['admin', 'super_admin', 'general_director']));
        if ($specific !== []) {
            $stats['actor_specific_endpoints'][] = [
                'key' => $itemName,
                'actors' => $specific,
            ];
        }
    }

    $endpointUnresolved = [];

    if ($endpoint && !empty($endpoint['uri'])) {
        replacePathVariables($request, $generator, 'api/' . ltrim($path, '/'), $pathVarUpdates);
    }

    // Preserve login automation exactly.
    if ($method === 'POST' && $path === '/login') {
        $request['description'] = buildDescription('ACTION', $actors, $endpoint ?? [], []);
        return;
    }

    if (!$endpoint || empty($endpoint['validation_rules']) || !is_array($endpoint['validation_rules'])) {
        $request['description'] = buildDescription($safety, $actors, $endpoint ?? [], []);
        return;
    }

    if (!in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        $request['description'] = buildDescription($safety, $actors, $endpoint, []);
        return;
    }

    $rules = $endpoint['validation_rules'];
    if (!empty($endpoint['controller']) && !empty($endpoint['action'])) {
        $rules = $enricher->enrich($endpoint['controller'], $endpoint['action'], $rules);
    }
    $rules = normalizeValidationRules($rules);

    $beforeBody = decodeBodyRaw($request['body'] ?? null);
    if ($beforeBody !== null && isBodyIncomplete($beforeBody)) {
        $stats['bodies_incomplete_before']++;
    }

    $unresolvedBefore = count($generator->unresolvedFields());
    $sampleBody = $generator->buildBody($rules, !empty($endpoint['has_file_upload']));
    $allUnresolved = $generator->unresolvedFields();
    $endpointUnresolved = array_slice($allUnresolved, $unresolvedBefore);

    if ($sampleBody !== []) {
        if (in_array($itemName, MULTIPART_ENDPOINTS, true)) {
            $fileFields = MULTIPART_FILE_FIELDS[$itemName] ?? [];
            $request['body'] = buildMultipartBody($itemName, $sampleBody, $fileFields);
            $request['header'] = array_values(array_filter(
                $request['header'] ?? [],
                static fn ($h) => strtolower($h['key'] ?? '') !== 'content-type'
            ));
            $stats['multipart_configured']++;
        } else {
            $request['body'] = [
                'mode' => 'raw',
                'raw' => json_encode($sampleBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'options' => ['raw' => ['language' => 'json']],
            ];
        }
        $stats['bodies_filled']++;
    } else {
        $stats['bodies_unchanged']++;
    }

    if ($method === 'POST' && $safety === 'CREATE' && $path !== '/register' && !str_contains($path, '/login')) {
        $capture = buildIdCaptureScript($path);
        $events = $item['event'] ?? [];
        $hasTest = false;
        foreach ($events as &$ev) {
            if (($ev['listen'] ?? '') === 'test') {
                $existing = $ev['script']['exec'] ?? [];
                $ev['script']['exec'] = array_merge($existing, ['', '// Auto-capture created resource id'], $capture);
                $hasTest = true;
            }
        }
        unset($ev);
        if (!$hasTest) {
            $events[] = [
                'listen' => 'test',
                'script' => [
                    'type' => 'text/javascript',
                    'exec' => $capture,
                ],
            ];
        }
        $item['event'] = $events;
        $stats['id_capture_scripts_added']++;
    }

    $request['description'] = buildDescription($safety, $actors, $endpoint, $endpointUnresolved);
    if ($endpointUnresolved !== []) {
        $stats['unresolved_fields'] = array_merge($stats['unresolved_fields'], $endpointUnresolved);
    }
});

$stats['path_vars_updated'] = $pathVarUpdates;

// Collection variables from generator helper
$newVars = $generator->collectionVariables();
$existing = array_column($collection['variable'] ?? [], 'key');
foreach ($newVars as $var) {
    if (!in_array($var, $existing, true)) {
        $collection['variable'][] = ['key' => $var, 'value' => ''];
        $stats['new_variables'][] = $var;
    }
}

$collection['info']['name'] = 'SMEDC Authority API (Apidog Final With Samples)';
$collection['info']['description'] = implode("\n", [
    $collection['info']['description'] ?? '',
    '',
    '--- WithSamples build ' . date('c') . ' ---',
    'Sample bodies derived from Laravel FormRequest/validation rules (no guessed enums).',
    'Safety tags in each request description: READ_ONLY | CREATE | UPDATE | DELETE | ACTION.',
    'Recommended actors documented per endpoint — set active_actor before protected routes.',
    'File upload fields left empty with description; attach real files when testing locally.',
    'No mutation requests were executed against production during generation.',
]);

$stats['unresolved_fields'] = array_values(array_unique($stats['unresolved_fields']));
$stats['new_variables'] = array_values(array_unique($stats['new_variables']));
$stats['actor_specific_endpoints'] = array_values($stats['actor_specific_endpoints']);

ApiDocs\Support::atomicWrite(
    $outputCollection,
    json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);
ApiDocs\Support::atomicWrite(
    $reportFile,
    json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);

@unlink($docsDir . '/tmp_test_rules.php');

echo "=== SMEDC Apidog WithSamples ===\n";
echo "Output: {$outputCollection}\n";
echo "Report: {$reportFile}\n";
echo "Total endpoints: {$stats['total_endpoints']}\n";
echo "Bodies incomplete before: {$stats['bodies_incomplete_before']}\n";
echo "Bodies filled: {$stats['bodies_filled']}\n";
echo "Unresolved fields: " . count($stats['unresolved_fields']) . "\n";
echo "New variables: " . count($stats['new_variables']) . "\n";
echo "Actor-specific endpoints: " . count($stats['actor_specific_endpoints']) . "\n";
echo "Mutation endpoints (tagged, NOT executed): {$stats['mutation_endpoints']}\n";
echo "Production requests sent: {$stats['production_requests_sent']}\n";

exit(0);
