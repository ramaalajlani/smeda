<?php

/**
 * SMEDA QA Suite — permission matrix + business scenarios for company testing handoff.
 *
 * Usage:
 *   php tests/qa/run_qa_suite.php
 *   php tests/qa/run_qa_suite.php --base=http://127.0.0.1:8000
 *
 * Outputs:
 *   postman/qa-reports/QA-REPORT.md
 *   postman/qa-reports/QA-REPORT.html
 *   postman/qa-reports/QA-REPORT.json
 */

require __DIR__.'/../Qa/QaHttp.php';
require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Tests\Qa\QaHttp;

$base = 'http://127.0.0.1:8000';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--base=')) {
        $base = substr($arg, 7);
    }
}

$http = new QaHttp($base);
$password = '12345678';

$accounts = [
    'admin' => 'admin@system.com',
    'super_admin' => 'super.admin@system.com',
    'general_director' => 'general@system.com',
    'deputy_general_director' => 'deputy@system.com',
    'deputy_director' => 'deputy@system.com',
    'branch_manager' => 'branch.damascus@system.com',
    'governor' => 'governor.tartus@system.com',
    'finance_manager' => 'finance.manager@system.com',
    'finance_officer' => 'finance.officer@system.com',
    'data_entry' => 'data-entry.damascus@system.com',
    'data_reviewer' => 'data-reviewer.damascus@system.com',
    'center_user' => 'center@system.com',
    'trainer_user' => 'trainer@system.com',
    'trainee_user' => 'trainee@system.com',
    'funding_partner' => 'funding.partner@system.com',
    'consultant_office' => 'consultant.office@system.com',
    'training_manager' => 'manager@system.com',
    'project_services_manager' => 'projects@system.com',
    'auditor' => 'auditor@system.com',
    'media_manager' => 'media@system.com',
    'incubator_manager' => 'incubator.manager@system.com',
    'entrepreneur_manager' => 'entrepreneur.manager@system.com',
    'system_admin' => 'system.admin@system.com',
    'central_bank_admin' => 'central.bank@system.com',
    'consultant_union_admin' => 'consultant.union@system.com',
    'project_owner' => 'project.owner@system.com',
    'branch_officer' => 'branch.officer.damascus@system.com',
    'workforce_manager' => 'workforce@system.com',
    'training_supervisor' => 'training.supervisor@system.com',
    'incubator_mentor' => 'incubator.mentor@system.com',
    'development_manager' => 'development@system.com',
    'local_development_manager' => 'local.development@system.com',
];

/**
 * Critical endpoints matrix.
 * expect: 'allow' | 'deny'
 * roles: list of role keys that SHOULD be allowed; everyone else deny (unless expect_public)
 */
$matrix = [
    [
        'id' => 'AUTH-ME',
        'method' => 'GET',
        'path' => '/api/me',
        'module' => 'Auth',
        'expect_all_authenticated' => true,
    ],
    [
        'id' => 'DASHBOARD',
        'method' => 'GET',
        'path' => '/api/dashboard',
        'module' => 'Dashboard',
        // Mirrors App\Support\DashboardAccess::MAIN_DASHBOARD_ROLES (+ national admins)
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'branch_manager', 'branch_officer', 'governor', 'training_manager', 'system_admin',
            'center_user', 'trainer_user', 'trainee_user', 'auditor', 'finance_manager', 'finance_officer',
            'funding_partner', 'consultant_office', 'consultant_union_admin', 'central_bank_admin',
            'data_entry', 'data_reviewer', 'development_manager', 'local_development_manager',
            'project_services_manager', 'project_owner', 'workforce_manager',
            'incubator_manager', 'incubator_mentor', 'media_manager', 'entrepreneur_manager',
        ],
    ],
    [
        'id' => 'ADMIN-USERS',
        'method' => 'GET',
        'path' => '/api/admin/users',
        'module' => 'Admin',
        'allow' => ['admin', 'super_admin', 'system_admin', 'general_director'],
    ],
    [
        'id' => 'NEEDS-LIST',
        'method' => 'GET',
        'path' => '/api/needs?per_page=5',
        'module' => 'GIS Needs',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'branch_manager', 'governor', 'data_entry', 'data_reviewer', 'project_services_manager',
            'development_manager', 'local_development_manager', 'auditor', 'branch_officer',
        ],
    ],
    [
        'id' => 'NEEDS-CREATE',
        'method' => 'POST',
        'path' => '/api/needs',
        'module' => 'GIS Needs',
        'body' => [
            'title' => 'QA Need '.uniqid(),
            'description' => 'Created by QA suite',
            'latitude' => 33.5138,
            'longitude' => 36.2765,
            'need_category' => 'service_gap',
            'targeting_type' => 'entrepreneurs',
            'sectors' => ['services'],
        ],
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'branch_manager', 'data_entry', 'project_services_manager', 'branch_officer',
            'governor', 'development_manager', 'local_development_manager',
        ],
    ],
    [
        'id' => 'NEEDS-DASHBOARD',
        'method' => 'GET',
        'path' => '/api/needs/dashboard',
        'module' => 'GIS Needs',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'branch_manager', 'governor', 'project_services_manager', 'development_manager',
            'local_development_manager', 'auditor',
        ],
    ],
    [
        'id' => 'FINANCE-APPS-LIST',
        'method' => 'GET',
        'path' => '/api/finance/applications?per_page=5',
        'module' => 'Finance',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'finance_manager', 'finance_officer', 'branch_manager', 'branch_officer',
            'central_bank_admin', 'consultant_union_admin', 'consultant_office', 'funding_partner',
            'project_owner', 'auditor',
            // Note: route middleware lists governor|system_admin but controller/policy returns 403 in practice
        ],
    ],
    [
        'id' => 'FINANCE-APPS-CREATE',
        'method' => 'POST',
        'path' => '/api/finance/applications',
        'module' => 'Finance',
        'body' => [
            'applicant_name' => 'QA Applicant',
            'project_name' => 'QA Finance Project '.uniqid(),
            'requested_amount' => 1500000,
            'project_size' => 'small',
            'business_stage' => 'startup',
            'financing_type' => 'capital',
            'governorate_id' => 1,
            'branch_id' => 1,
        ],
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'finance_manager', 'finance_officer', 'project_owner',
        ],
    ],
    [
        'id' => 'FINANCE-CB-DASH',
        'method' => 'GET',
        'path' => '/api/finance/central-bank/dashboard',
        'module' => 'Finance',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'finance_manager', 'central_bank_admin',
        ],
    ],
    [
        'id' => 'COURSES-LIST',
        'method' => 'GET',
        'path' => '/api/training-courses?per_page=5',
        'module' => 'Training',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'training_manager', 'training_supervisor', 'branch_manager', 'center_user', 'trainer_user',
            'trainee_user', 'auditor', 'governor', 'project_services_manager', 'branch_officer',
        ],
    ],
    [
        'id' => 'CERTS-LIST',
        'method' => 'GET',
        'path' => '/api/certificates?per_page=5',
        'module' => 'Certificates',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'training_manager', 'training_supervisor', 'branch_manager', 'branch_officer', 'center_user',
            'trainer_user', 'trainee_user', 'auditor', 'governor', 'project_services_manager',
        ],
    ],
    [
        'id' => 'CERTS-VERIFY-PUBLIC',
        'method' => 'POST',
        'path' => '/api/certificates/verify',
        'module' => 'Certificates',
        'body' => ['certificate_code' => 'SMEDA-QA-DOES-NOT-EXIST'],
        'public' => true,
        // public: no token; 404/422 still = reachable
    ],
    [
        'id' => 'NEWS-PUBLIC',
        'method' => 'GET',
        'path' => '/api/news',
        'module' => 'News',
        'public' => true,
    ],
    [
        'id' => 'NEWS-MANAGE',
        'method' => 'GET',
        'path' => '/api/news/stats',
        'module' => 'News',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'media_manager', 'system_admin',
        ],
    ],
    [
        'id' => 'INCUBATION-STATS',
        'method' => 'GET',
        'path' => '/api/incubation/stats',
        'module' => 'Incubation',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'incubator_manager', 'incubator_mentor', 'entrepreneur_manager', 'project_owner',
            'branch_manager', 'branch_officer', 'auditor', 'system_admin',
        ],
    ],
    [
        'id' => 'WORKFORCE-JOBS',
        'method' => 'GET',
        'path' => '/api/workforce/job-postings',
        'module' => 'Workforce',
        'allow' => [
            'admin', 'super_admin', 'general_director', 'deputy_general_director', 'deputy_director',
            'workforce_manager', 'branch_manager', 'branch_officer', 'center_user', 'trainee_user',
            'training_manager', 'training_supervisor', 'project_services_manager', 'project_owner',
        ],
    ],
];

$results = [
    'meta' => [
        'generated_at' => date('c'),
        'base_url' => $base,
        'suite' => 'SMEDA QA Permission Matrix + Scenarios',
        'password_note' => 'Local demo password used for all seeded accounts',
    ],
    'login' => [],
    'matrix' => [],
    'scenarios' => [],
    'summary' => [],
];

$tokens = [];
$loginPass = 0;
$loginFail = 0;

echo "== Phase 1: Login all actors ==\n";
foreach ($accounts as $role => $email) {
    $res = $http->request('POST', '/api/login', [
        'email' => $email,
        'password' => $password,
        'device_name' => 'qa-suite',
    ]);
    $ok = ($res['status'] === 200 && !empty($res['json']['token']));
    if ($ok) {
        $tokens[$role] = $res['json']['token'];
        $loginPass++;
        $status = 'PASS';
        $detail = 'token ok';
    } else {
        $loginFail++;
        $status = 'FAIL';
        $detail = $res['json']['message'] ?? ('HTTP '.$res['status']);
    }
    $results['login'][] = compact('role', 'email', 'status', 'detail') + ['http' => $res['status']];
    echo ($ok ? 'PASS' : 'FAIL')." login {$role}\n";
}

echo "\n== Phase 2: Permission matrix ==\n";
$matrixPass = 0;
$matrixFail = 0;

foreach ($matrix as $case) {
    if (!empty($case['public'])) {
        $res = $http->request($case['method'], $case['path'], $case['body'] ?? null, null);
        $allowed = QaHttp::isAllowedStatus($res['status']);
        $status = $allowed ? 'PASS' : 'FAIL';
        $allowed ? $matrixPass++ : $matrixFail++;
        $results['matrix'][] = [
            'id' => $case['id'],
            'module' => $case['module'],
            'role' => 'public',
            'method' => $case['method'],
            'path' => $case['path'],
            'expected' => 'allow',
            'http' => $res['status'],
            'status' => $status,
            'detail' => $allowed ? 'public reachable' : 'public blocked unexpectedly',
        ];
        echo "{$status} {$case['id']} public => {$res['status']}\n";
        continue;
    }

    $allowRoles = $case['allow'] ?? [];
    if (!empty($case['expect_all_authenticated'])) {
        $allowRoles = array_keys($accounts);
    }

    foreach ($accounts as $role => $email) {
        if (!isset($tokens[$role])) {
            $matrixFail++;
            $results['matrix'][] = [
                'id' => $case['id'],
                'module' => $case['module'],
                'role' => $role,
                'method' => $case['method'],
                'path' => $case['path'],
                'expected' => in_array($role, $allowRoles, true) ? 'allow' : 'deny',
                'http' => 0,
                'status' => 'FAIL',
                'detail' => 'no token',
            ];
            continue;
        }

        $expected = in_array($role, $allowRoles, true) ? 'allow' : 'deny';
        $res = $http->request($case['method'], $case['path'], $case['body'] ?? null, $tokens[$role]);
        $gotAllow = QaHttp::isAllowedStatus($res['status']);
        $gotDeny = QaHttp::isDeniedStatus($res['status']);

        $pass = ($expected === 'allow' && $gotAllow) || ($expected === 'deny' && $gotDeny);
        // Soft-pass: deny expected but got 404/422 on gated resource can happen; still treat unexpected 500 as fail
        if (!$pass && $expected === 'deny' && in_array($res['status'], [404, 422], true)) {
            // Some controllers authorize after route middleware — treat as unexpected allow
            $pass = false;
            $detail = 'expected deny, got '.$res['status'];
        } elseif ($pass) {
            $detail = $expected === 'allow' ? 'allowed as expected' : 'denied as expected';
        } elseif ($expected === 'allow' && $gotDeny) {
            $detail = 'expected allow, got '.$res['status'];
        } elseif ($expected === 'deny' && $gotAllow) {
            $detail = 'expected deny, got '.$res['status'].' (possible over-permission)';
        } else {
            $detail = 'unexpected HTTP '.$res['status'];
            $pass = false;
        }

        $status = $pass ? 'PASS' : 'FAIL';
        $pass ? $matrixPass++ : $matrixFail++;
        $results['matrix'][] = [
            'id' => $case['id'],
            'module' => $case['module'],
            'role' => $role,
            'method' => $case['method'],
            'path' => $case['path'],
            'expected' => $expected,
            'http' => $res['status'],
            'status' => $status,
            'detail' => $detail,
        ];
    }
    echo "done {$case['id']}\n";
}

echo "\n== Phase 3: Business scenarios ==\n";
$scenarioPass = 0;
$scenarioFail = 0;

function scenarioAssert(array &$results, string $name, bool $ok, string $detail, int &$pass, int &$fail, int $http = 0): void
{
    $status = $ok ? 'PASS' : 'FAIL';
    $ok ? $pass++ : $fail++;
    $results['scenarios'][] = [
        'scenario' => $name,
        'status' => $status,
        'http' => $http,
        'detail' => $detail,
    ];
    echo "{$status} {$name} — {$detail}\n";
}

// Scenario A: Create Need as data_entry
$needId = null;
if (!empty($tokens['data_entry'])) {
    $res = $http->request('POST', '/api/needs', [
        'title' => 'QA Scenario Need '.date('His'),
        'description' => 'سيناريو إنشاء احتياج للاختبار',
        'latitude' => 33.5138,
        'longitude' => 36.2765,
        'need_category' => 'service_gap',
        'targeting_type' => 'entrepreneurs',
        'sectors' => ['services'],
    ], $tokens['data_entry']);
    $needId = $res['json']['data']['id'] ?? $res['json']['id'] ?? null;
    scenarioAssert($results, 'S1 Create Need (data_entry)', in_array($res['status'], [200, 201], true) && $needId, $needId ? "need_id={$needId}" : ('HTTP '.$res['status'].' '.($res['json']['message'] ?? '')), $scenarioPass, $scenarioFail, $res['status']);

    if ($needId && !empty($tokens['data_reviewer'])) {
        $res2 = $http->request('GET', '/api/needs/'.$needId, null, $tokens['data_reviewer']);
        scenarioAssert($results, 'S1b View Need (data_reviewer)', QaHttp::isAllowedStatus($res2['status']), 'HTTP '.$res2['status'], $scenarioPass, $scenarioFail, $res2['status']);
    }
} else {
    scenarioAssert($results, 'S1 Create Need (data_entry)', false, 'missing token', $scenarioPass, $scenarioFail);
}

// Scenario B: Finance application as project_owner
$appId = null;
if (!empty($tokens['project_owner'])) {
    $res = $http->request('POST', '/api/finance/applications', [
        'applicant_name' => 'صاحب مشروع QA',
        'project_name' => 'مشروع تمويل QA '.date('His'),
        'requested_amount' => 2500000,
        'project_size' => 'small',
        'business_stage' => 'startup',
        'financing_type' => 'capital',
        'governorate_id' => 1,
        'branch_id' => 1,
    ], $tokens['project_owner']);
    $appId = $res['json']['data']['id'] ?? $res['json']['id'] ?? null;
    scenarioAssert($results, 'S2 Create Finance Application (project_owner)', in_array($res['status'], [200, 201], true) && $appId, $appId ? "application_id={$appId}" : ('HTTP '.$res['status'].' '.($res['json']['message'] ?? json_encode($res['json']['errors'] ?? []))), $scenarioPass, $scenarioFail, $res['status']);

    if ($appId && !empty($tokens['finance_manager'])) {
        $res2 = $http->request('GET', '/api/finance/applications/'.$appId, null, $tokens['finance_manager']);
        scenarioAssert($results, 'S2b View Finance App (finance_manager)', QaHttp::isAllowedStatus($res2['status']), 'HTTP '.$res2['status'], $scenarioPass, $scenarioFail, $res2['status']);
    }
} else {
    scenarioAssert($results, 'S2 Create Finance Application (project_owner)', false, 'missing token', $scenarioPass, $scenarioFail);
}

// Scenario C: Courses list as training_manager + center_user
if (!empty($tokens['training_manager'])) {
    $res = $http->request('GET', '/api/training-courses?per_page=5', null, $tokens['training_manager']);
    scenarioAssert($results, 'S3 List Courses (training_manager)', QaHttp::isAllowedStatus($res['status']), 'HTTP '.$res['status'], $scenarioPass, $scenarioFail, $res['status']);
}
if (!empty($tokens['center_user'])) {
    $res = $http->request('GET', '/api/training-courses?per_page=5', null, $tokens['center_user']);
    scenarioAssert($results, 'S3b List Courses (center_user)', QaHttp::isAllowedStatus($res['status']), 'HTTP '.$res['status'], $scenarioPass, $scenarioFail, $res['status']);
}
if (!empty($tokens['trainee_user'])) {
    $res = $http->request('GET', '/api/training-courses?per_page=5', null, $tokens['trainee_user']);
    scenarioAssert($results, 'S3c List Courses (trainee_user)', QaHttp::isAllowedStatus($res['status']), 'HTTP '.$res['status'], $scenarioPass, $scenarioFail, $res['status']);
}

// Scenario D: Certificates list + public verify
if (!empty($tokens['training_manager'])) {
    $res = $http->request('GET', '/api/certificates?per_page=5', null, $tokens['training_manager']);
    scenarioAssert($results, 'S4 List Certificates (training_manager)', QaHttp::isAllowedStatus($res['status']), 'HTTP '.$res['status'], $scenarioPass, $scenarioFail, $res['status']);
}
$res = $http->request('POST', '/api/certificates/verify', ['certificate_code' => 'INVALID-QA-CODE'], null);
scenarioAssert($results, 'S4b Public Certificate Verify', in_array($res['status'], [200, 404, 422], true), 'HTTP '.$res['status'].' (public endpoint reachable)', $scenarioPass, $scenarioFail, $res['status']);

// Scenario E: Deny checks — trainee cannot access admin users; data_entry cannot access finance CB dashboard
if (!empty($tokens['trainee_user'])) {
    $res = $http->request('GET', '/api/admin/users', null, $tokens['trainee_user']);
    scenarioAssert($results, 'S5 Deny admin users (trainee_user)', QaHttp::isDeniedStatus($res['status']), 'HTTP '.$res['status'], $scenarioPass, $scenarioFail, $res['status']);
}
if (!empty($tokens['data_entry'])) {
    $res = $http->request('GET', '/api/finance/central-bank/dashboard', null, $tokens['data_entry']);
    scenarioAssert($results, 'S5b Deny central-bank dashboard (data_entry)', QaHttp::isDeniedStatus($res['status']), 'HTTP '.$res['status'], $scenarioPass, $scenarioFail, $res['status']);
}

$results['summary'] = [
    'login_pass' => $loginPass,
    'login_fail' => $loginFail,
    'matrix_pass' => $matrixPass,
    'matrix_fail' => $matrixFail,
    'scenario_pass' => $scenarioPass,
    'scenario_fail' => $scenarioFail,
    'total_pass' => $loginPass + $matrixPass + $scenarioPass,
    'total_fail' => $loginFail + $matrixFail + $scenarioFail,
];

$outDir = realpath(__DIR__.'/../../../postman') ?: (__DIR__.'/../../../postman');
$reportDir = $outDir.DIRECTORY_SEPARATOR.'qa-reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0777, true);
}

file_put_contents($reportDir.'/QA-REPORT.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$md = "# SMEDA QA Report\n\n";
$md .= '- Generated: '.$results['meta']['generated_at']."\n";
$md .= '- API: `'.$base."`\n";
$md .= '- Login: **'.$loginPass.'** pass / **'.$loginFail."** fail\n";
$md .= '- Permission matrix: **'.$matrixPass.'** pass / **'.$matrixFail."** fail\n";
$md .= '- Scenarios: **'.$scenarioPass.'** pass / **'.$scenarioFail."** fail\n";
$md .= '- Total: **'.$results['summary']['total_pass'].'** pass / **'.$results['summary']['total_fail']."** fail\n\n";

$md .= "## Scenarios\n\n| Scenario | Result | HTTP | Detail |\n|---|---|---|---|\n";
foreach ($results['scenarios'] as $row) {
    $md .= '| '.$row['scenario'].' | '.$row['status'].' | '.$row['http'].' | '.str_replace('|', '\\|', $row['detail'])." |\n";
}

$md .= "\n## Login\n\n| Role | Email | Result | Detail |\n|---|---|---|---|\n";
foreach ($results['login'] as $row) {
    $md .= '| `'.$row['role'].'` | `'.$row['email'].'` | '.$row['status'].' | '.str_replace('|', '\\|', $row['detail'])." |\n";
}

$md .= "\n## Permission Matrix (failures only)\n\n";
$fails = array_filter($results['matrix'], fn ($r) => $r['status'] === 'FAIL');
if (!$fails) {
    $md .= "_No matrix failures._\n";
} else {
    $md .= "| ID | Role | Expected | HTTP | Detail |\n|---|---|---|---|---|\n";
    foreach ($fails as $row) {
        $md .= '| '.$row['id'].' | `'.$row['role'].'` | '.$row['expected'].' | '.$row['http'].' | '.str_replace('|', '\\|', $row['detail'])." |\n";
    }
}

$md .= "\n## Full Matrix CSV hint\nSee `QA-REPORT.json` → `matrix` for complete allow/deny results.\n";
file_put_contents($reportDir.'/QA-REPORT.md', $md);

$html = '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>SMEDA QA Report</title>
<style>
body{font-family:Tahoma,Arial,sans-serif;margin:24px;background:#f8fafc;color:#0f172a}
h1,h2{color:#0f766e}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin:12px 0}
.pass{color:#15803d;font-weight:700}.fail{color:#b91c1c;font-weight:700}
table{border-collapse:collapse;width:100%;font-size:13px}
th,td{border:1px solid #e2e8f0;padding:6px 8px;text-align:right}
th{background:#ecfdf5}
.badge{display:inline-block;padding:4px 10px;border-radius:999px;background:#ccfbf1;font-weight:700}
</style></head><body>';
$html .= '<h1>تقرير اختبارات SMEDA QA</h1>';
$html .= '<div class="card"><span class="badge">'.$base.'</span><p>التاريخ: '.htmlspecialchars($results['meta']['generated_at']).'</p>';
$html .= '<p>Login: <span class="pass">'.$loginPass.'</span> / Fail <span class="fail">'.$loginFail.'</span></p>';
$html .= '<p>Matrix: <span class="pass">'.$matrixPass.'</span> / Fail <span class="fail">'.$matrixFail.'</span></p>';
$html .= '<p>Scenarios: <span class="pass">'.$scenarioPass.'</span> / Fail <span class="fail">'.$scenarioFail.'</span></p></div>';

$html .= '<div class="card"><h2>السيناريوهات</h2><table><tr><th>السيناريو</th><th>النتيجة</th><th>HTTP</th><th>التفاصيل</th></tr>';
foreach ($results['scenarios'] as $row) {
    $cls = $row['status'] === 'PASS' ? 'pass' : 'fail';
    $html .= '<tr><td>'.htmlspecialchars($row['scenario']).'</td><td class="'.$cls.'">'.$row['status'].'</td><td>'.$row['http'].'</td><td>'.htmlspecialchars($row['detail']).'</td></tr>';
}
$html .= '</table></div>';

$html .= '<div class="card"><h2>إخفاقات مصفوفة الصلاحيات</h2>';
if (!$fails) {
    $html .= '<p class="pass">لا توجد إخفاقات في المصفوفة.</p>';
} else {
    $html .= '<table><tr><th>ID</th><th>الدور</th><th>المتوقع</th><th>HTTP</th><th>التفاصيل</th></tr>';
    foreach ($fails as $row) {
        $html .= '<tr><td>'.$row['id'].'</td><td>'.$row['role'].'</td><td>'.$row['expected'].'</td><td>'.$row['http'].'</td><td>'.htmlspecialchars($row['detail']).'</td></tr>';
    }
    $html .= '</table>';
}
$html .= '</div><p>التفاصيل الكاملة في QA-REPORT.json</p></body></html>';
file_put_contents($reportDir.'/QA-REPORT.html', $html);

echo "\n== SUMMARY ==\n";
echo "Login {$loginPass}/".($loginPass + $loginFail)."\n";
echo "Matrix {$matrixPass}/".($matrixPass + $matrixFail)."\n";
echo "Scenarios {$scenarioPass}/".($scenarioPass + $scenarioFail)."\n";
echo "Reports: {$reportDir}\n";

exit(($loginFail + $matrixFail + $scenarioFail) > 0 ? 1 : 0);
