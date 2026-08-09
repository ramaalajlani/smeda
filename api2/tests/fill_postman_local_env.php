<?php

/**
 * Fill SMEDA-Local Postman environment with concrete emails, passwords, and live tokens.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$base = rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/');
$password = '12345678';

$actors = [
    'email' => 'admin@system.com',
    'super_admin_email' => 'super.admin@system.com',
    'admin_email' => 'admin@system.com',
    'general_director_email' => 'general@system.com',
    'deputy_general_director_email' => 'deputy@system.com',
    'deputy_director_email' => 'deputy@system.com',
    'branch_manager_email' => 'branch.damascus@system.com',
    'governor_email' => 'governor.tartus@system.com',
    'finance_manager_email' => 'finance.manager@system.com',
    'finance_officer_email' => 'finance.officer@system.com',
    'data_entry_email' => 'data-entry.damascus@system.com',
    'data_reviewer_email' => 'data-reviewer.damascus@system.com',
    'center_user_email' => 'center@system.com',
    'trainer_email' => 'trainer@system.com',
    'trainee_email' => 'trainee@system.com',
    'funding_partner_email' => 'funding.partner@system.com',
    'consultant_office_email' => 'consultant.office@system.com',
    'training_manager_email' => 'manager@system.com',
    'psm_email' => 'projects@system.com',
    'auditor_email' => 'auditor@system.com',
    'media_manager_email' => 'media@system.com',
    'incubator_manager_email' => 'incubator.manager@system.com',
    'entrepreneur_manager_email' => 'entrepreneur.manager@system.com',
    'system_admin_email' => 'system.admin@system.com',
    'central_bank_email' => 'central.bank@system.com',
    'consultant_union_email' => 'consultant.union@system.com',
    'project_owner_email' => 'project.owner@system.com',
    'branch_officer_email' => 'branch.officer.damascus@system.com',
    'workforce_manager_email' => 'workforce@system.com',
    'training_supervisor_email' => 'training.supervisor@system.com',
    'incubator_mentor_email' => 'incubator.mentor@system.com',
    'development_manager_email' => 'development@system.com',
    'local_development_manager_email' => 'local.development@system.com',
];

$passwordKeys = [
    'password',
    'super_admin_password',
    'admin_password',
    'general_director_password',
    'deputy_general_director_password',
    'deputy_director_password',
    'branch_manager_password',
    'governor_password',
    'finance_manager_password',
    'finance_officer_password',
    'data_entry_password',
    'data_reviewer_password',
    'center_user_password',
    'trainer_password',
    'trainee_password',
    'funding_partner_password',
    'consultant_office_password',
    'training_manager_password',
    'psm_password',
    'auditor_password',
    'media_manager_password',
    'incubator_manager_password',
    'entrepreneur_manager_password',
    'system_admin_password',
    'central_bank_password',
    'consultant_union_password',
    'project_owner_password',
    'branch_officer_password',
    'workforce_manager_password',
    'training_supervisor_password',
    'incubator_mentor_password',
    'development_manager_password',
    'local_development_manager_password',
];

$tokenKeyByEmailVar = [
    'email' => 'token',
    'super_admin_email' => 'super_admin_token',
    'admin_email' => 'admin_token',
    'general_director_email' => 'general_director_token',
    'deputy_general_director_email' => 'deputy_general_director_token',
    'deputy_director_email' => 'deputy_director_token',
    'branch_manager_email' => 'branch_manager_token',
    'governor_email' => 'governor_token',
    'finance_manager_email' => 'finance_manager_token',
    'finance_officer_email' => 'finance_officer_token',
    'data_entry_email' => 'data_entry_token',
    'data_reviewer_email' => 'data_reviewer_token',
    'center_user_email' => 'center_user_token',
    'trainer_email' => 'trainer_token',
    'trainee_email' => 'trainee_token',
    'funding_partner_email' => 'funding_partner_token',
    'consultant_office_email' => 'consultant_office_token',
    'training_manager_email' => 'training_manager_token',
    'psm_email' => 'psm_token',
    'auditor_email' => 'auditor_token',
    'media_manager_email' => 'media_manager_token',
    'incubator_manager_email' => 'incubator_manager_token',
    'entrepreneur_manager_email' => 'entrepreneur_manager_token',
    'system_admin_email' => 'system_admin_token',
    'central_bank_email' => 'central_bank_token',
    'consultant_union_email' => 'consultant_union_token',
    'project_owner_email' => 'project_owner_token',
    'branch_officer_email' => 'branch_officer_token',
    'workforce_manager_email' => 'workforce_manager_token',
    'training_supervisor_email' => 'training_supervisor_token',
    'incubator_mentor_email' => 'incubator_mentor_token',
    'development_manager_email' => 'development_manager_token',
    'local_development_manager_email' => 'local_development_manager_token',
];

function httpJson(string $method, string $url, ?array $body = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json'],
        CURLOPT_TIMEOUT => 25,
        CURLOPT_POSTFIELDS => $body === null ? null : json_encode($body, JSON_UNESCAPED_UNICODE),
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'json' => json_decode((string) $raw, true)];
}

$values = [];
$values['base_url'] = 'http://127.0.0.1:8000';
$values['api_url'] = 'http://127.0.0.1:8000/api';
$values['token_type'] = 'Bearer';
$values['actor'] = 'admin';
$values['role'] = 'admin';

foreach ($actors as $key => $email) {
    $values[$key] = $email;
}
foreach ($passwordKeys as $key) {
    $values[$key] = $password;
}

$defaults = [
    'governorate_id' => '1',
    'branch_id' => '1',
    'center_id' => '1',
    'trainer_id' => '1',
    'trainee_id' => '1',
    'course_id' => '1',
    'certificate_id' => '1',
    'certificate_code' => 'SMEDA-DEMO-0001',
    'need_id' => '1',
    'application_id' => '1',
    'case_id' => '1',
    'finance_application_id' => '1',
    'kit_id' => '1',
    'loan_id' => '1',
    'news_id' => '1',
    'agreement_id' => '1',
    'incubator_id' => '1',
    'document_id' => '1',
    'id' => '1',
    'signature_code' => 'SIG-DEMO-001',
    'story_slug' => 'sample-story',
    'role_name' => 'branch_manager',
    'permission_name' => 'view_users',
    'user_id' => '1',
];
foreach ($defaults as $k => $v) {
    $values[$k] = $v;
}

$ok = 0;
$fail = 0;
foreach ($tokenKeyByEmailVar as $emailVar => $tokenKey) {
    $email = $actors[$emailVar];
    $login = httpJson('POST', $base.'/api/login', [
        'email' => $email,
        'password' => $password,
        'device_name' => 'postman-env-fill',
    ]);

    if (($login['status'] ?? 0) !== 200 || empty($login['json']['token'])) {
        $values[$tokenKey] = '';
        $fail++;
        fwrite(STDERR, "FAIL login {$email}\n");
        continue;
    }

    $token = $login['json']['token'];
    $values[$tokenKey] = $token;
    $ok++;

    if ($emailVar === 'email' || $emailVar === 'admin_email') {
        $values['token'] = $token;
        $user = $login['json']['user'] ?? [];
        if (!empty($user['id'])) {
            $values['user_id'] = (string) $user['id'];
        }
        if (!empty($user['governorate_id'])) {
            $values['governorate_id'] = (string) $user['governorate_id'];
        }
        if (!empty($user['branch_id'])) {
            $values['branch_id'] = (string) $user['branch_id'];
        }
        if (!empty($user['training_center_id'])) {
            $values['center_id'] = (string) $user['training_center_id'];
        }
        $roles = $user['roles'] ?? [];
        if ($roles) {
            $values['role'] = (string) $roles[0];
            $values['actor'] = (string) $roles[0];
        }
    }
}

$envValues = [];
foreach ($values as $key => $value) {
    $envValues[] = [
        'key' => $key,
        'value' => (string) $value,
        'type' => 'default',
        'enabled' => true,
    ];
}

$env = [
    'id' => 'smeda-local-filled',
    'name' => 'SMEDA Local',
    'values' => $envValues,
    '_postman_variable_scope' => 'environment',
];

$out = dirname(__DIR__, 2).'/postman/SMEDA-Local.postman_environment.json';
// authority2-V0/api2/tests -> authority2-V0/postman
$out = realpath(__DIR__.'/../../postman') ?: (__DIR__.'/../../postman');
$outFile = rtrim($out, '/\\').DIRECTORY_SEPARATOR.'SMEDA-Local.postman_environment.json';

file_put_contents($outFile, json_encode($env, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");

echo "Wrote {$outFile}\n";
echo "Tokens OK: {$ok}, FAIL: {$fail}\n";
echo "Main email={$values['email']} password filled, token length=".strlen($values['token'] ?? '')."\n";

exit($fail > 0 ? 1 : 0);
