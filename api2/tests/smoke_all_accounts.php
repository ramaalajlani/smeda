<?php

/**
 * Full account smoke test: login + /me for every demo actor.
 * Usage: php tests/smoke_all_accounts.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$base = rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/');
$password = '12345678';

$accounts = [
    ['role' => 'admin', 'email' => 'admin@system.com'],
    ['role' => 'super_admin', 'email' => 'super.admin@system.com'],
    ['role' => 'general_director', 'email' => 'general@system.com'],
    ['role' => 'deputy_general_director', 'email' => 'deputy@system.com'],
    ['role' => 'deputy_director', 'email' => 'deputy@system.com'],
    ['role' => 'branch_manager', 'email' => 'branch.damascus@system.com'],
    ['role' => 'governor', 'email' => 'governor.tartus@system.com'],
    ['role' => 'finance_manager', 'email' => 'finance.manager@system.com'],
    ['role' => 'finance_officer', 'email' => 'finance.officer@system.com'],
    ['role' => 'data_entry', 'email' => 'data-entry.damascus@system.com'],
    ['role' => 'data_reviewer', 'email' => 'data-reviewer.damascus@system.com'],
    ['role' => 'center_user', 'email' => 'center@system.com'],
    ['role' => 'trainer_user', 'email' => 'trainer@system.com'],
    ['role' => 'trainee_user', 'email' => 'trainee@system.com'],
    ['role' => 'funding_partner', 'email' => 'funding.partner@system.com'],
    ['role' => 'consultant_office', 'email' => 'consultant.office@system.com'],
    ['role' => 'training_manager', 'email' => 'manager@system.com'],
    ['role' => 'project_services_manager', 'email' => 'projects@system.com'],
    ['role' => 'auditor', 'email' => 'auditor@system.com'],
    ['role' => 'media_manager', 'email' => 'media@system.com'],
    ['role' => 'incubator_manager', 'email' => 'incubator.manager@system.com'],
    ['role' => 'entrepreneur_manager', 'email' => 'entrepreneur.manager@system.com'],
    ['role' => 'system_admin', 'email' => 'system.admin@system.com'],
    ['role' => 'central_bank_admin', 'email' => 'central.bank@system.com'],
    ['role' => 'consultant_union_admin', 'email' => 'consultant.union@system.com'],
    ['role' => 'project_owner', 'email' => 'project.owner@system.com'],
    ['role' => 'branch_officer', 'email' => 'branch.officer.damascus@system.com'],
    ['role' => 'workforce_manager', 'email' => 'workforce@system.com'],
    ['role' => 'training_supervisor', 'email' => 'training.supervisor@system.com'],
    ['role' => 'incubator_mentor', 'email' => 'incubator.mentor@system.com'],
    ['role' => 'development_manager', 'email' => 'development@system.com'],
    ['role' => 'local_development_manager', 'email' => 'local.development@system.com'],
];

function httpJson(string $method, string $url, ?array $body = null, ?string $token = null): array
{
    $ch = curl_init($url);
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer '.$token;
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 20,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    $json = null;
    if (is_string($raw) && $raw !== '') {
        $json = json_decode($raw, true);
    }

    return ['status' => $status, 'json' => $json, 'error' => $err, 'raw' => $raw];
}

$passed = 0;
$failed = 0;
$rows = [];

foreach ($accounts as $account) {
    $email = $account['email'];
    $expectedRole = $account['role'];

    $user = App\Models\User::query()->where('email', $email)->first();
    if (!$user) {
        $failed++;
        $rows[] = [$expectedRole, $email, 'FAIL', 'user missing in DB'];
        continue;
    }

    $login = httpJson('POST', $base.'/api/login', [
        'email' => $email,
        'password' => $password,
        'device_name' => 'smoke-all-accounts',
    ]);

    if ($login['status'] !== 200 || empty($login['json']['token'])) {
        $failed++;
        $msg = $login['json']['message'] ?? ($login['error'] ?: 'login failed HTTP '.$login['status']);
        $rows[] = [$expectedRole, $email, 'FAIL', $msg];
        continue;
    }

    $token = $login['json']['token'];
    $roles = $login['json']['user']['roles'] ?? [];
    $hasRole = in_array($expectedRole, $roles, true);

    $me = httpJson('GET', $base.'/api/me', null, $token);
    $meOk = $me['status'] === 200 && !empty($me['json']['user']['id']);

    if ($hasRole && $meOk) {
        $passed++;
        $rows[] = [$expectedRole, $email, 'PASS', 'login+me ok; roles='.implode(',', $roles)];
    } else {
        $failed++;
        $detail = [];
        if (!$hasRole) {
            $detail[] = 'role missing (got: '.implode(',', $roles).')';
        }
        if (!$meOk) {
            $detail[] = '/me HTTP '.$me['status'];
        }
        $rows[] = [$expectedRole, $email, 'FAIL', implode(' | ', $detail)];
    }
}

echo "SMEDA account smoke test\n";
echo "API: {$base}/api\n";
echo str_repeat('-', 100)."\n";
printf("%-28s %-40s %-6s %s\n", 'ROLE', 'EMAIL', 'RESULT', 'DETAIL');
echo str_repeat('-', 100)."\n";
foreach ($rows as [$role, $email, $result, $detail]) {
    printf("%-28s %-40s %-6s %s\n", $role, $email, $result, $detail);
}
echo str_repeat('-', 100)."\n";
echo "PASSED: {$passed} / ".count($accounts)."\n";
echo "FAILED: {$failed}\n";

$reportPath = __DIR__.'/../storage/logs/account-smoke-report.md';
$md = "# Account Smoke Test Report\n\n";
$md .= '- Date: '.date('c')."\n";
$md .= "- API: `{$base}/api`\n";
$md .= "- Passed: **{$passed}** / ".count($accounts)."\n";
$md .= "- Failed: **{$failed}**\n\n";
$md .= "| Role | Email | Result | Detail |\n|---|---|---|---|\n";
foreach ($rows as [$role, $email, $result, $detail]) {
    $md .= '| `'.$role.'` | `'.$email.'` | '.$result.' | '.str_replace('|', '\\|', $detail)." |\n";
}
file_put_contents($reportPath, $md);
echo "Report: {$reportPath}\n";

exit($failed > 0 ? 1 : 0);
