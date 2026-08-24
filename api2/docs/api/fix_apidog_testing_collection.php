<?php

declare(strict_types=1);

/**
 * Cleans SMEDC_API_Apidog_Final.json for Apidog testing.
 * Usage: php docs/api/fix_apidog_testing_collection.php
 */

$docsDir = __DIR__;
$input = $docsDir . '/SMEDC_API_Apidog_Final.json';
$output = $docsDir . '/SMEDC_API_Apidog_Testing_Final.json';

if (!is_file($input)) {
    fwrite(STDERR, "Input not found: {$input}\n");
    exit(1);
}

$stats = [
    'total_endpoints' => 0,
    'duplicate_keys' => 0,
    'validation_fields_removed' => 0,
    'bodies_cleaned' => 0,
    'multipart_fixed' => 0,
    'multipart_endpoints' => [],
    'login_script_updated' => false,
];

/** @var list<string> */
const MULTIPART_ENDPOINT_NAMES = [
    'POST api/finance/applications/{applicationId}/documents',
    'POST api/ai/knowledge/ingest',
    'POST api/registration-requests/centers',
    'POST api/consulting/requests/{id}/attachments',
    'POST api/consulting/contracts/{id}/report',
    'POST api/my-electronic-signature',
    'POST api/workforce/job-applications',
];

/** @var array<string, list<string>> */
const MULTIPART_FILE_FIELDS_BY_ENDPOINT = [
    'POST api/finance/applications/{applicationId}/documents' => ['file'],
    'POST api/ai/knowledge/ingest' => ['files'],
    'POST api/registration-requests/centers' => ['license_image'],
    'POST api/consulting/requests/{id}/attachments' => ['file'],
    'POST api/consulting/contracts/{id}/report' => ['file'],
    'POST api/my-electronic-signature' => ['signature'],
    'POST api/workforce/job-applications' => ['cv'],
];

/** @var list<string> */
const VALIDATION_RULE_SUFFIXES = [
    'required', 'regex', 'max', 'min', 'exists', 'string', 'integer', 'array', 'boolean',
    'nullable', 'email', 'numeric', 'date', 'file', 'mimes', 'in', 'unique', 'confirmed',
    'digits', 'url', 'uuid', 'json', 'sometimes', 'present', 'filled',
];

function isValidationArtifactKey(string $key): bool
{
    // Array item templates like members.*.trainee_id are structure hints, not artifacts.
    if (preg_match('/^[^.]+\.\*\.[^.]+$/', $key)) {
        return false;
    }

    if (preg_match('/^[^.]+\.\*$/', $key)) {
        return true;
    }

    if (preg_match('/\.(?:' . implode('|', VALIDATION_RULE_SUFFIXES) . ')$/i', $key)) {
        return true;
    }

    return false;
}

function isFileFieldName(string $key, string $itemName): bool
{
    $allowed = MULTIPART_FILE_FIELDS_BY_ENDPOINT[$itemName] ?? [];
    $base = str_replace('[]', '', $key);

    return in_array($base, $allowed, true);
}

function inferScalarValue(string $key, mixed $value): mixed
{
    $lower = strtolower($key);

    if (preg_match('/^(is_|has_|supports_|requires_|is_verified|is_active|is_broadcast|is_pinned)/', $lower)) {
        return true;
    }

    if (preg_match('/_id$/', $lower) && !in_array($lower, ['national_id', 'guardian_national_id'], true)) {
        return null;
    }

    if (preg_match('/_(count|amount|rate|score|order|years|months|capacity|seats|latitude|longitude|margin|size|hours|minutes|priority|sort_order|repayment_period_months|requested_amount|approved_amount|installment_count|installment_amount|interest_rate|profit_margin|experience_years|beneficiaries_count|employees_count|monthly_revenue|monthly_expenses|existing_debts|minutes_attended|max_score|pass_mark|coursework_score|exam_score|duration_months|expected_duration_days|budget_min|budget_max|feasibility_score|risk_level|recommended_amount|price_offer_amount)$/', $lower)) {
        return 0;
    }

    if (is_string($value) && $value !== '' && is_numeric($value)) {
        return str_contains($value, '.') ? (float) $value : (int) $value;
    }

    return '';
}

function inferArrayItemTemplate(string $prefix, array $wildcardFields): array
{
    $item = [];
    foreach ($wildcardFields as $subKey => $_) {
        $item[$subKey] = inferScalarValue($subKey, '');
    }

    if ($item === []) {
        return [];
    }

    return [$item];
}

function cleanFlatBody(array $flat): array
{
    global $stats;

    $result = [];
    $wildcards = [];
    $nestedDots = [];

    foreach ($flat as $key => $value) {
        if (isValidationArtifactKey($key)) {
            $stats['validation_fields_removed']++;
            continue;
        }

        if (preg_match('/^(.+)\.\*\.(.+)$/', $key, $m)) {
            $wildcards[$m[1]][$m[2]] = $value;
            $stats['validation_fields_removed']++;
            continue;
        }

        if (preg_match('/^(.+)\.\*$/', $key, $m)) {
            $stats['validation_fields_removed']++;
            if (!isset($result[$m[1]])) {
                $result[$m[1]] = [];
            }
            continue;
        }

        if (str_contains($key, '.')) {
            $nestedDots[$key] = $value;
            continue;
        }

        if (in_array($key, ['permissions', 'roles', 'sectors', 'specializations', 'skills', 'roles', 'permissions'], true)) {
            $result[$key] = [];
            continue;
        }

        if ($key === 'details' && $value === '') {
            continue;
        }

        if ($key === 'members' || $key === 'items') {
            continue;
        }

        $result[$key] = inferScalarValue($key, $value);
    }

    foreach ($wildcards as $prefix => $fields) {
        $result[$prefix] = inferArrayItemTemplate($prefix, $fields);
        $stats['bodies_cleaned']++;
    }

    foreach ($nestedDots as $key => $value) {
        $parts = explode('.', $key);
        $cursor = &$result;
        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $cursor[$part] = inferScalarValue($part, $value);
            } else {
                if (!isset($cursor[$part]) || !is_array($cursor[$part])) {
                    $cursor[$part] = [];
                }
                $cursor = &$cursor[$part];
            }
        }
        unset($cursor);
        $stats['bodies_cleaned']++;
    }

    if (isset($result['details']) && !is_array($result['details'])) {
        unset($result['details']);
    }

    return $result;
}

function buildFormdata(array $fields, string $itemName): array
{
    $formdata = [];

    foreach ($fields as $key => $value) {
        if (isFileFieldName($key, $itemName)) {
            $formKey = $key === 'files' ? 'files[]' : $key;
            $formdata[] = [
                'key' => $formKey,
                'type' => 'file',
                'src' => '',
            ];
            continue;
        }

        if (is_array($value)) {
            if ($value === []) {
                continue;
            }
            foreach ($value as $idx => $item) {
                if (is_array($item)) {
                    foreach ($item as $subKey => $subVal) {
                        $formdata[] = [
                            'key' => "{$key}[{$idx}][{$subKey}]",
                            'value' => is_bool($subVal) ? ($subVal ? '1' : '0') : (string) ($subVal ?? ''),
                            'type' => 'text',
                        ];
                    }
                } else {
                    $formdata[] = [
                        'key' => "{$key}[]",
                        'value' => (string) $item,
                        'type' => 'text',
                    ];
                }
            }
            continue;
        }

        $formdata[] = [
            'key' => $key,
            'value' => is_bool($value) ? ($value ? '1' : '0') : (is_null($value) ? '' : (string) $value),
            'type' => 'text',
        ];
    }

    return $formdata;
}

function requestNeedsMultipart(array $request, array $cleanedFields, string $itemName): bool
{
    return in_array($itemName, MULTIPART_ENDPOINT_NAMES, true);
}

function hasMultipartHeader(array $request): bool
{
    foreach ($request['header'] ?? [] as $header) {
        if (strtolower($header['key'] ?? '') === 'content-type'
            && str_contains(strtolower($header['value'] ?? ''), 'multipart/form-data')) {
            return true;
        }
    }

    return false;
}

function cleanHeaders(array $headers, bool $multipart): array
{
    $clean = [];
    foreach ($headers as $header) {
        $key = strtolower($header['key'] ?? '');
        if ($multipart && $key === 'content-type') {
            continue;
        }
        if ($key === 'content-type' && ($header['disabled'] ?? false)) {
            continue;
        }
        $clean[] = $header;
    }

    if (!$multipart) {
        $hasContentType = false;
        foreach ($clean as $header) {
            if (strtolower($header['key'] ?? '') === 'content-type') {
                $hasContentType = true;
                break;
            }
        }
        if (!$hasContentType) {
            $clean[] = ['key' => 'Content-Type', 'value' => 'application/json'];
        }
    }

    return $clean;
}

function normalizeUrlKey(array $request): string
{
    $method = strtoupper($request['method'] ?? 'GET');
    $url = $request['url'] ?? '';
    $raw = is_array($url) ? ($url['raw'] ?? '') : (string) $url;
    $normalized = preg_replace('/\{\w+\}/', '{id}', $raw) ?? $raw;

    return $method . ' ' . $normalized;
}

function processRequest(array &$request, string $itemName): void
{
    global $stats;

    $body = $request['body'] ?? null;
    if (!$body || ($body['mode'] ?? '') !== 'raw' || !isset($body['raw'])) {
        return;
    }

    $decoded = json_decode($body['raw'], true);
    if (!is_array($decoded)) {
        return;
    }

    $beforeCount = count($decoded);
    $cleaned = cleanFlatBody($decoded);

    if ($cleaned !== $decoded) {
        $stats['bodies_cleaned']++;
    }

    $multipart = requestNeedsMultipart($request, $cleaned, $itemName);
    $request['header'] = cleanHeaders($request['header'] ?? [], $multipart);

    if ($multipart) {
        $request['body'] = [
            'mode' => 'formdata',
            'formdata' => buildFormdata($cleaned, $itemName),
        ];
        $stats['multipart_fixed']++;
        $stats['multipart_endpoints'][] = $itemName;
    } else {
        $request['body'] = [
            'mode' => 'raw',
            'raw' => json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'options' => [
                'raw' => ['language' => 'json'],
            ],
        ];
    }

    unset($beforeCount);
}

function walkItems(array &$items, callable $fn): void
{
    foreach ($items as &$item) {
        if (isset($item['request'])) {
            $fn($item['request'], $item['name'] ?? 'unknown');
        }
        if (isset($item['item'])) {
            walkItems($item['item'], $fn);
        }
    }
}

function fixLoginScript(array &$collection): void
{
    global $stats;

    walkItems($collection['item'], function (array &$request, string $name) use (&$stats): void {
        if (!str_contains(strtolower($name), 'login')) {
            return;
        }
        if (strtoupper($request['method'] ?? '') !== 'POST') {
            return;
        }
        $url = is_array($request['url'] ?? null) ? ($request['url']['raw'] ?? '') : ($request['url'] ?? '');
        if (!str_contains($url, '/login')) {
            return;
        }

        unset($request['auth']);

        // Token shape from AuthController::login — field name is "token".
        $script = [
            'const r = pm.response.json();',
            'const token = r.token || r.access_token || (r.data && r.data.token) || "";',
            'if (token) {',
            '  pm.collectionVariables.set("access_token", token);',
            '  if (pm.environment && pm.environment.set) pm.environment.set("access_token", token);',
            '}',
            'if (r.user && r.user.id) {',
            '  pm.collectionVariables.set("user_id", String(r.user.id));',
            '  if (pm.environment && pm.environment.set) pm.environment.set("user_id", String(r.user.id));',
            '}',
        ];

        // Attach to parent item via global — handled in outer walk
        $request['_login_script'] = $script;
        $stats['login_script_updated'] = true;
    });
}

$raw = file_get_contents($input);
$data = json_decode($raw, true);
if (!is_array($data)) {
    fwrite(STDERR, "Invalid JSON input\n");
    exit(1);
}

$data['info']['name'] = 'SMEDC Authority API (Apidog Testing Final)';
$data['info']['description'] = 'SMEDC API — Apidog testing export. Cleaned bodies, proper multipart uploads, no Laravel validation wildcards. base_url=https://new.smeda.gov.sy/api2/public/api';

$seen = [];
walkItems($data['item'], function (array &$request, string $name) use (&$seen, &$stats): void {
    $stats['total_endpoints']++;
    $key = normalizeUrlKey($request);
    if (isset($seen[$key])) {
        $stats['duplicate_keys']++;
    }
    $seen[$key] = true;

    processRequest($request, $name);
});

// Apply login scripts to item events
walkItems($data['item'], function (array &$request, string $name) use (&$data): void {
    if (!isset($request['_login_script'])) {
        return;
    }
    unset($request['_login_script']);
});

// Second pass for login events on parent items
$applyLogin = function (array &$items) use (&$applyLogin, &$stats): void {
    foreach ($items as &$item) {
        if (isset($item['request'])) {
            $req = &$item['request'];
            $url = is_array($req['url'] ?? null) ? ($req['url']['raw'] ?? '') : ($req['url'] ?? '');
            if (strtoupper($req['method'] ?? '') === 'POST' && str_contains($url, '/login')) {
                unset($req['auth']);
                $req['body'] = [
                    'mode' => 'raw',
                    'raw' => json_encode([
                        'email' => '',
                        'password' => '',
                        'device_name' => 'apidog-client',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'options' => ['raw' => ['language' => 'json']],
                ];
                $item['event'] = [[
                    'listen' => 'test',
                    'script' => [
                        'exec' => [
                            'const r = pm.response.json();',
                            'const token = r.token || r.access_token || (r.data && r.data.token) || "";',
                            'if (token) {',
                            '  pm.collectionVariables.set("access_token", token);',
                            '  if (pm.environment && pm.environment.set) pm.environment.set("access_token", token);',
                            '}',
                            'if (r.user && r.user.id) {',
                            '  pm.collectionVariables.set("user_id", String(r.user.id));',
                            '  if (pm.environment && pm.environment.set) pm.environment.set("user_id", String(r.user.id));',
                            '}',
                        ],
                        'type' => 'text/javascript',
                    ],
                ]];
                $stats['login_script_updated'] = true;
            }
        }
        if (isset($item['item'])) {
            $applyLogin($item['item']);
        }
    }
};
$applyLogin($data['item']);

$encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($encoded === false) {
    fwrite(STDERR, "Failed to encode output JSON\n");
    exit(1);
}

file_put_contents($output, $encoded . "\n");

// Validate output
$verify = json_decode((string) file_get_contents($output), true);
if (!is_array($verify)) {
    fwrite(STDERR, "Output JSON invalid\n");
    exit(1);
}

$dupCheck = [];
$validationLeft = 0;
$rawWithWildcards = 0;
walkItems($verify['item'], function (array $request, string $name) use (&$dupCheck, &$validationLeft, &$rawWithWildcards): void {
    $key = normalizeUrlKey($request);
    $dupCheck[$key] = ($dupCheck[$key] ?? 0) + 1;

    $body = $request['body'] ?? [];
    if (($body['mode'] ?? '') === 'raw' && isset($body['raw'])) {
        $decoded = json_decode($body['raw'], true);
        if (is_array($decoded)) {
            foreach (array_keys($decoded) as $k) {
                if (isValidationArtifactKey($k)) {
                    $validationLeft++;
                }
            }
        }
        if (preg_match('/\.\*|\.\w+\.(?:required|regex|max|min)/', $body['raw'])) {
            $rawWithWildcards++;
        }
    }
});

$duplicates = count(array_filter($dupCheck, static fn (int $c): bool => $c > 1));

echo "=== Apidog Testing Collection Report ===\n";
echo 'Total endpoints: ' . $stats['total_endpoints'] . "\n";
echo 'Duplicate method+path: ' . $duplicates . "\n";
echo 'JSON validity: OK' . "\n";
echo 'Laravel validation fields removed: ' . $stats['validation_fields_removed'] . "\n";
echo 'Raw bodies converted to proper arrays/objects: ' . $stats['bodies_cleaned'] . "\n";
echo 'Multipart endpoints fixed: ' . $stats['multipart_fixed'] . "\n";
echo 'Base URL: ' . ($data['variable'][0]['value'] ?? '') . "\n";
echo 'Authentication: Bearer {{access_token}}; login stores token via collectionVariables' . "\n";
echo 'Output file: ' . $output . "\n";
echo "Multipart converted endpoints:\n";
foreach ($stats['multipart_endpoints'] as $ep) {
    echo "  - {$ep}\n";
}
if ($validationLeft > 0 || $rawWithWildcards > 0) {
    echo "WARNING: validation artifacts remaining: keys={$validationLeft}, raw={$rawWithWildcards}\n";
    exit(2);
}

exit(0);
