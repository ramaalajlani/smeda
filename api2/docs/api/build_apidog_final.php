<?php

declare(strict_types=1);

/**
 * Build final Apidog import package (prefilled env, collection, test data, RBAC matrix).
 *
 * Usage: php docs/api/build_apidog_final.php
 */

$docsDir = __DIR__;
$projectRoot = dirname($docsDir, 2);

$sourceCollection = $docsDir . '/SMEDC_Apidog_Ready_ModuleBaseURL.postman_collection.json';
$sourceEnvironment = $docsDir . '/SMEDC_Prod_ModuleBaseURL.postman_environment.json';

$collectionOut = $docsDir . '/SMEDC_Apidog_Final.postman_collection.json';
$environmentOut = $docsDir . '/SMEDC_Production_Prefilled.postman_environment.json';
$actorsDataOut = $docsDir . '/SMEDC_Actors_TestData.json';
$rbacMatrixOut = $docsDir . '/SMEDC_RBAC_TestMatrix.json';

const MODULE_BASE_URL = 'https://new.smeda.gov.sy/api2/public/api';
const DEFAULT_PASSWORD = '12345678';

/** Canonical actor credentials (from UserSeeder / smoke_all_accounts.php). */
const ACTOR_CREDENTIALS = [
    'admin' => ['email' => 'admin@system.com', 'password' => '12345678'],
    'super_admin' => ['email' => 'super.admin@system.com', 'password' => '12345678'],
    'general_director' => ['email' => 'general@system.com', 'password' => '12345678'],
    'deputy_general_director' => ['email' => 'deputy@system.com', 'password' => '12345678'],
    'deputy_director' => ['email' => 'deputy@system.com', 'password' => '12345678'],
    'system_admin' => ['email' => 'system.admin@system.com', 'password' => '12345678'],
    'governor' => ['email' => 'governor.tartus@system.com', 'password' => '12345678'],
    'branch_manager' => ['email' => 'branch.damascus@system.com', 'password' => '12345678'],
    'branch_officer' => ['email' => 'branch.officer.damascus@system.com', 'password' => '12345678'],
    'workforce_manager' => ['email' => 'workforce@system.com', 'password' => '12345678'],
    'training_manager' => ['email' => 'manager@system.com', 'password' => '12345678'],
    'training_supervisor' => ['email' => 'training.supervisor@system.com', 'password' => '12345678'],
    'center_user' => ['email' => 'center@system.com', 'password' => '12345678'],
    'trainer_user' => ['email' => 'trainer@system.com', 'password' => '12345678'],
    'trainee_user' => ['email' => 'trainee@system.com', 'password' => '12345678'],
    'auditor' => ['email' => 'auditor@system.com', 'password' => '12345678'],
    'data_entry' => ['email' => 'data-entry.damascus@system.com', 'password' => '12345678'],
    'data_reviewer' => ['email' => 'data-reviewer.damascus@system.com', 'password' => '12345678'],
    'project_services_manager' => ['email' => 'projects@system.com', 'password' => '12345678'],
    'development_manager' => ['email' => 'development@system.com', 'password' => '12345678'],
    'local_development_manager' => ['email' => 'local.development@system.com', 'password' => '12345678'],
    'finance_manager' => ['email' => 'finance.manager@system.com', 'password' => '12345678'],
    'finance_officer' => ['email' => 'finance.officer@system.com', 'password' => '12345678'],
    'consultant_office' => ['email' => 'consultant.office@system.com', 'password' => '12345678'],
    'funding_partner' => ['email' => 'funding.partner@system.com', 'password' => '12345678'],
    'consultant_union_admin' => ['email' => 'consultant.union@system.com', 'password' => '12345678'],
    'central_bank_admin' => ['email' => 'central.bank@system.com', 'password' => '12345678'],
    'project_owner' => ['email' => 'project.owner@system.com', 'password' => '12345678'],
    'incubator_manager' => ['email' => 'incubator.manager@system.com', 'password' => '12345678'],
    'incubator_mentor' => ['email' => 'incubator.mentor@system.com', 'password' => '12345678'],
    'entrepreneur_manager' => ['email' => 'entrepreneur.manager@system.com', 'password' => '12345678'],
    'media_manager' => ['email' => 'media@system.com', 'password' => '12345678'],
];

const NATIONAL_ADMIN_ROLES = ['general_director', 'admin', 'super_admin'];

const COLLECTION_PREREQUEST = <<<'JS'
// Data-driven actor iteration (Apidog / Postman Collection Runner)
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

const actor = pm.environment.get('active_actor');

if (!actor) {
    throw new Error('active_actor is not set');
}

const email = pm.environment.get(`${actor}_email`);
const password = pm.environment.get(`${actor}_password`);
const actorToken = pm.environment.get(`${actor}_token`);

if (email) {
    pm.environment.set('email', email);
}

if (password) {
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

pm.test('Login returns token at $.token', function () {
    pm.expect(json).to.have.property('token');
    pm.expect(json.token).to.be.a('string').and.not.empty;
});
JS;

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

if (!is_file($sourceCollection) || !is_file($sourceEnvironment)) {
    fwrite(STDERR, "Source files missing. Expected ModuleBaseURL collection and environment.\n");
    exit(1);
}

$collection = json_decode((string) file_get_contents($sourceCollection), true);
$envTemplate = json_decode((string) file_get_contents($sourceEnvironment), true);

if (!is_array($collection) || !is_array($envTemplate)) {
    fwrite(STDERR, "Invalid source JSON\n");
    exit(1);
}

// --- Collection updates ---
$collection['info']['name'] = 'SMEDC Authority API (Apidog Final)';
$collection['info']['description'] = implode("\n", [
    'SMEDC API — ready for Apidog import.',
    'Module Base URL: ' . MODULE_BASE_URL,
    'Environment: SMEDC_Production_Prefilled.postman_environment.json',
    'Test data: SMEDC_Actors_TestData.json (32 actors, data-driven testing).',
    'RBAC matrix: SMEDC_RBAC_TestMatrix.json',
    'Set active_actor in environment (default: admin) or use Collection Runner with test data.',
    'Login stores token at $.token → token + {active_actor}_token.',
]);

$actorsList = array_keys(ACTOR_CREDENTIALS);
$collection['variable'] = [
    ['key' => 'user_id', 'value' => ''],
    [
        'key' => 'actors_list',
        'value' => implode(',', $actorsList),
        'description' => 'All 32 Spatie roles for automated actor testing',
    ],
];

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
    $raw = preg_replace('#^\{\{base_url\}\}/?#', '', $raw) ?? $raw;
    $raw = preg_replace('#^https?://[^/]+(?:/api2/public/api|/api/api|/api)?/?#', '', $raw) ?? $raw;

    return '/' . ltrim($raw, '/');
}

function isPublicPath(string $path): bool
{
    foreach (PUBLIC_PATH_PATTERNS as $pattern) {
        if (preg_match($pattern, $path)) {
            return true;
        }
    }

    return false;
}

function scriptToExec(string $script): array
{
    return array_values(array_filter(explode("\n", $script), static fn ($line) => $line !== '' || true));
}

$stats = [
    'endpoints' => 0,
    'duplicates' => 0,
    'public_no_auth' => 0,
    'protected_bearer' => 0,
    'base_url_refs' => 0,
    'full_urls' => 0,
];

$seen = [];
$endpointIndex = [];

walkItems($collection['item'], function (array &$item) use (&$seen, &$stats, &$endpointIndex): void {
    $request = &$item['request'];
    $stats['endpoints']++;

    $path = extractPath($request);
    $method = strtoupper($request['method'] ?? 'GET');
    $key = $method . ' ' . preg_replace('/\{[^}]+\}/', '{id}', $path);

    if (isset($seen[$key])) {
        $stats['duplicates']++;
    }
    $seen[$key] = true;

    $endpointIndex[] = ['method' => $method, 'path' => $path, 'key' => $key];

    $blob = json_encode($request);
    if ($blob && str_contains($blob, '{{base_url}}')) {
        $stats['base_url_refs']++;
    }
    if ($blob && str_contains($blob, MODULE_BASE_URL)) {
        $stats['full_urls']++;
    }

    if ($method === 'POST' && $path === '/login') {
        $item['event'] = [[
            'listen' => 'test',
            'script' => ['type' => 'text/javascript', 'exec' => scriptToExec(LOGIN_TEST_SCRIPT)],
        ]];
        $request['body'] = [
            'mode' => 'raw',
            'raw' => "{\n    \"email\": \"{{email}}\",\n    \"password\": \"{{password}}\",\n    \"device_name\": \"apidog-client\"\n}",
            'options' => ['raw' => ['language' => 'json']],
        ];
        unset($request['auth']);
        $stats['public_no_auth']++;

        return;
    }

    if (isPublicPath($path)) {
        unset($request['auth']);
        $stats['public_no_auth']++;
    } else {
        $request['auth'] = [
            'type' => 'bearer',
            'bearer' => [['key' => 'token', 'value' => '{{token}}', 'type' => 'string']],
        ];
        $stats['protected_bearer']++;
    }
});

$collection['event'] = [[
    'listen' => 'prerequest',
    'script' => ['type' => 'text/javascript', 'exec' => scriptToExec(COLLECTION_PREREQUEST)],
]];

// --- Prefilled environment ---
$prefilledEnv = [
    'id' => 'smedc-production-prefilled-env',
    'name' => 'SMEDC Production',
    'values' => [],
    '_postman_variable_scope' => 'environment',
    '_postman_exported_at' => gmdate('Y-m-d\TH:i:s\Z'),
    '_postman_exported_using' => 'SMEDC Apidog Final Builder',
];

$prefilledEnv['values'][] = [
    'key' => 'active_actor',
    'value' => 'admin',
    'type' => 'default',
    'enabled' => true,
];
$prefilledEnv['values'][] = ['key' => 'email', 'value' => '', 'type' => 'secret', 'enabled' => true];
$prefilledEnv['values'][] = ['key' => 'password', 'value' => '', 'type' => 'secret', 'enabled' => true];
$prefilledEnv['values'][] = ['key' => 'token', 'value' => '', 'type' => 'secret', 'enabled' => true];
$prefilledEnv['values'][] = ['key' => 'user_id', 'value' => '', 'type' => 'default', 'enabled' => true];

foreach (ACTOR_CREDENTIALS as $actor => $creds) {
    $prefilledEnv['values'][] = [
        'key' => "{$actor}_email",
        'value' => $creds['email'],
        'type' => 'secret',
        'enabled' => true,
    ];
    $prefilledEnv['values'][] = [
        'key' => "{$actor}_password",
        'value' => $creds['password'],
        'type' => 'secret',
        'enabled' => true,
    ];
    $prefilledEnv['values'][] = [
        'key' => "{$actor}_token",
        'value' => '',
        'type' => 'secret',
        'enabled' => true,
    ];
}

// --- Actors test data ---
$actorsTestData = array_map(
    static fn (string $actor): array => ['active_actor' => $actor],
    $actorsList
);

// --- RBAC matrix from Laravel routes + RolePermissionSeeder ---
$rbacMatrix = buildRbacMatrix($projectRoot, $endpointIndex, $actorsList);

// --- Login smoke test against remote API ---
$loginResults = testActorLogins($actorsList);

// --- Write outputs ---
writeJson($collectionOut, $collection);
writeJson($environmentOut, $prefilledEnv);
writeJson($actorsDataOut, $actorsTestData);
writeJson($rbacMatrixOut, $rbacMatrix);

// --- Validation ---
$validation = validateOutputs(
    $collectionOut,
    $environmentOut,
    $actorsDataOut,
    $rbacMatrixOut,
    count($actorsList),
    $loginResults
);

printReport($validation, $loginResults);

exit($validation['passed'] ? 0 : 2);

// ---------------------------------------------------------------------------

function writeJson(string $path, mixed $data): void
{
    file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
    );
}

function buildRbacMatrix(string $projectRoot, array $endpointIndex, array $actors): array
{
    $rolePermissions = loadRolePermissions($projectRoot);
    $routeMap = loadRouteMap($projectRoot);

    $entries = [];

    foreach ($endpointIndex as $ep) {
        $method = $ep['method'];
        $path = $ep['path'];
        $routeKey = $method . ' ' . normalizeRouteUri($path);
        $route = $routeMap[$routeKey] ?? findFuzzyRoute($routeMap, $method, $path);

        $public = isPublicPath($path);
        $middleware = $route['middleware'] ?? [];
        $permissions = $route['permissions'] ?? [];
        $roles = $route['roles'] ?? [];
        $roleOrPermissions = $route['role_or_permissions'] ?? [];
        $requiresAuth = !$public && ($route['requires_auth'] ?? true);

        $actorExpected = [];
        foreach ($actors as $actor) {
            $actorExpected[$actor] = computeExpectedAccess(
                $actor,
                $rolePermissions,
                $requiresAuth,
                $permissions,
                $roles,
                $roleOrPermissions,
                $route['authorizations'] ?? []
            );
        }

        $entries[] = [
            'key' => $ep['key'],
            'method' => $method,
            'path' => $path,
            'public' => $public,
            'requires_auth' => $requiresAuth,
            'middleware' => $middleware,
            'permissions' => $permissions,
            'roles' => $roles,
            'role_or_permissions' => $roleOrPermissions,
            'authorizations' => $route['authorizations'] ?? [],
            'expected_by_actor' => $actorExpected,
        ];
    }

    return [
        'meta' => [
            'generated_at' => gmdate('c'),
            'source' => 'Laravel routes + RolePermissionSeeder + middleware analysis',
            'actors_count' => count($actors),
            'endpoints_count' => count($entries),
            'notes' => [
                'allowed = middleware/RBAC permits access (actual status may be 200/201/204/422 depending on payload)',
                '401 = unauthenticated on protected route',
                '403 = authenticated but missing permission/role',
                'Policy-level ownership checks may still return 403 even when middleware passes',
            ],
        ],
        'actors' => $actors,
        'entries' => $entries,
    ];
}

function loadRolePermissions(string $projectRoot): array
{
    $map = [];

    try {
        require $projectRoot . '/vendor/autoload.php';
        $app = require $projectRoot . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        if (class_exists(Spatie\Permission\Models\Role::class)) {
            $roles = Spatie\Permission\Models\Role::query()
                ->where('guard_name', 'sanctum')
                ->with('permissions')
                ->get();

            foreach ($roles as $role) {
                $map[$role->name] = $role->permissions->pluck('name')->all();
            }
        }
    } catch (Throwable $e) {
        echo "DB role load failed ({$e->getMessage()}), using seeder snapshot...\n";
    }

    if ($map !== []) {
        return $map;
    }

    return loadRolePermissionsFromSeeder($projectRoot);
}

function loadRolePermissionsFromSeeder(string $projectRoot): array
{
    $seederPath = $projectRoot . '/database/seeders/RolePermissionSeeder.php';
    $code = (string) file_get_contents($seederPath);

    if (!preg_match('/\$permissions\s*=\s*\[(.*?)\];/s', $code, $permMatch)) {
        throw new RuntimeException('Cannot parse permissions from RolePermissionSeeder');
    }

    $permissions = eval('return [' . $permMatch[1] . '];');

    $needsNational = [
        'needs.view', 'needs.view_all', 'needs.create', 'needs.create_citizen', 'needs.create_state',
        'needs.update', 'needs.review', 'needs.approve', 'needs.reject', 'needs.return',
        'needs.classify', 'needs.resolve', 'needs.export', 'needs.dashboard', 'needs.map',
        'needs.manage_lookups', 'needs.manage_admin_units',
    ];
    $needsBranchOfficer = ['needs.view', 'needs.view_branch', 'needs.create', 'needs.create_citizen', 'needs.update', 'needs.map'];
    $financeBranchOfficer = ['finance.applications.view', 'finance.consultants.view'];
    $branchOfficerBase = [
        'view_branch_reports', 'view_governorates', 'view_branches',
        'view_trainers', 'view_trainer_profiles', 'view_centers',
        'view_courses', 'view_course_details', 'view_trainees',
        'view_certificates', 'view_registration_requests',
    ];
    $needsBranchManager = [
        'needs.view', 'needs.view_branch', 'needs.create', 'needs.create_citizen', 'needs.create_state',
        'needs.update', 'needs.review', 'needs.approve', 'needs.reject', 'needs.return',
        'needs.export', 'needs.dashboard', 'needs.map',
    ];
    $needsDataEntry = ['needs.view', 'needs.view_branch', 'needs.create', 'needs.create_citizen', 'needs.update', 'needs.map', 'view_governorates'];
    $needsDataReviewer = ['needs.view', 'needs.view_branch', 'needs.review', 'needs.return', 'needs.map', 'view_governorates'];
    $needsProjectServices = [
        'needs.view', 'needs.view_all', 'needs.create', 'needs.create_citizen', 'needs.create_state',
        'needs.update', 'needs.review', 'needs.approve', 'needs.reject', 'needs.return',
        'needs.classify', 'needs.resolve', 'needs.export', 'needs.dashboard', 'needs.map',
        'needs.manage_lookups', 'needs.manage_admin_units',
    ];
    $projectServicesTraining = [
        'view_trainers', 'manage_trainers', 'view_trainer_profiles', 'view_centers', 'manage_centers',
        'view_kits', 'manage_kits', 'review_training_kit_nominations', 'view_programs', 'manage_programs',
        'program_bank.view', 'program_bank.create', 'program_bank.update', 'program_bank.delete',
        'program_bank.approve', 'program_bank.reports', 'view_courses', 'manage_courses', 'view_course_details',
        'view_trainees', 'manage_trainees', 'view_certificates', 'issue_certificates', 'view_certificate_approvals',
        'approve_training_certificates', 'print_certificates', 'verify_certificates', 'view_reports',
        'view_registration_requests', 'review_center_registration_requests', 'review_trainer_registration_requests',
        'review_trainee_registration_requests',
    ];
    $projectServicesAdmin = [
        'view_users', 'manage_user_access', 'assign_roles', 'revoke_roles',
        'view_governorates', 'view_branches', 'manage_branches', 'manage_branch_managers',
        'view_national_reports', 'view_branch_reports',
    ];
    $needsDevelopment = ['needs.view', 'needs.view_all', 'needs.create_state', 'needs.classify', 'needs.dashboard', 'needs.map', 'needs.export'];
    $needsAuditor = ['needs.view', 'needs.view_all', 'needs.dashboard', 'needs.map'];
    $needsGovernor = [
        'needs.view', 'needs.view_branch', 'needs.view_state_only', 'needs.create_state', 'needs.update',
        'needs.dashboard', 'needs.map', 'needs.export',
    ];
    $workforceBasic = ['workforce.jobs.view', 'workforce.applications.create', 'workforce.training_requests.create'];
    $workforceEmployer = array_merge($workforceBasic, ['workforce.jobs.create']);
    $workforceManager = array_merge($workforceEmployer, ['workforce.jobs.manage', 'workforce.applications.view', 'workforce.training_requests.view']);
    $workforceManagerRole = array_merge($workforceManager, ['view_reports']);
    $incubationView = ['incubation.view'];
    $incubationManage = ['incubation.view', 'incubation.manage', 'story.manage'];
    $incubationMentor = ['incubation.view', 'incubation.mentor'];
    $entrepreneurManage = ['incubation.view', 'entrepreneur.manage', 'story.manage'];
    $mediaManage = ['news.manage', 'story.manage'];
    $financeViewOnly = [
        'finance.applications.view', 'finance.consultants.view', 'finance.partners.view',
        'finance.loans.view', 'finance.metrics.view',
    ];
    $financeBranchManager = [
        'finance.applications.view', 'finance.applications.review_branch', 'finance.applications.request_completion',
        'finance.applications.reject', 'finance.applications.assign_consultant', 'finance.consultants.view',
        'finance.consultants.approve_price', 'finance.loans.view', 'finance.metrics.view', 'finance.metrics.branch',
    ];
    $financeConsultantOffice = [
        'finance.applications.view', 'finance.consultants.view', 'finance.consultant_office.dashboard',
        'finance.consultant_assignments.view_own', 'finance.consultant_assignments.accept',
        'finance.consultant_assignments.reject', 'finance.consultant_assignments.submit_price',
        'finance.consultant_reports.create', 'finance.consultant_reports.view_own',
        'finance.consultants.submit_price', 'finance.consultants.submit_report',
    ];
    $financePartnerRole = [
        'finance.applications.view', 'finance.partners.view', 'finance.funding_partner.dashboard',
        'finance.partner_assignments.view_own', 'finance.partner_assignments.review',
        'finance.partner_assignments.decide', 'finance.partner_assignments.approve_amount',
        'finance.partners.review', 'finance.partners.decide', 'finance.loans.view', 'finance.loans.view_own',
    ];
    $consultantUnionAdmin = [
        'finance.consultant_union.dashboard', 'finance.consultants.view_all', 'finance.consultants.create',
        'finance.consultants.update', 'finance.consultants.approve', 'finance.consultants.activate',
        'finance.consultants.suspend', 'finance.consultants.monitor', 'finance.consultants.reports.view',
        'finance.consultants.price_offers.view', 'finance.applications.view', 'finance.consultants.view',
    ];
    $centralBankAdmin = [
        'finance.central_bank.dashboard', 'finance.partners.view_all', 'finance.partners.create',
        'finance.partners.update', 'finance.partners.approve', 'finance.partners.activate',
        'finance.partners.suspend', 'finance.partners.monitor', 'finance.partner_decisions.view_all',
        'finance.bank_metrics.view', 'finance.applications.view', 'finance.partners.view', 'finance.loans.view',
    ];
    $financeProjectOwner = [
        'finance.applications.view', 'finance.applications.create', 'finance.applications.update',
        'finance.applications.submit',
    ];
    $financeManagerRole = array_values(array_filter($permissions, static fn ($p) => str_starts_with($p, 'finance.')));
    $accessPermissions = [
        'manage_roles', 'view_roles', 'create_roles', 'update_roles', 'delete_roles',
        'manage_permissions', 'view_permissions', 'create_permissions', 'update_permissions', 'delete_permissions',
        'assign_roles', 'revoke_roles', 'assign_permissions', 'revoke_permissions',
        'view_users', 'manage_user_access',
    ];

    return [
        'general_director' => $permissions,
        'admin' => $permissions,
        'deputy_general_director' => $permissions,
        'deputy_director' => $permissions,
        'governor' => array_merge([
            'view_branch_reports', 'view_governorates', 'view_branches', 'view_national_reports',
            'view_trainers', 'view_trainer_profiles', 'view_centers', 'view_courses', 'view_course_details',
            'view_trainees', 'view_certificates', 'view_certificate_approvals', 'view_registration_requests', 'view_reports',
        ], $needsGovernor),
        'branch_manager' => array_merge([
            'view_branch_reports', 'view_governorates', 'view_branches', 'view_trainers', 'manage_trainers',
            'view_trainer_profiles', 'view_centers', 'manage_centers', 'view_courses', 'manage_courses',
            'view_course_details', 'view_trainees', 'manage_trainees', 'view_certificates', 'issue_certificates',
            'view_certificate_approvals', 'approve_center_certificates', 'print_certificates', 'verify_certificates',
            'view_registration_requests', 'review_center_registration_requests', 'review_trainer_registration_requests',
            'review_trainee_registration_requests', 'review_training_kit_nominations',
        ], $financeBranchManager, $needsBranchManager, $workforceManager),
        'branch_officer' => array_merge($branchOfficerBase, $financeBranchOfficer, $needsBranchOfficer, $workforceBasic),
        'workforce_manager' => $workforceManagerRole,
        'training_manager' => array_merge([
            'view_trainers', 'manage_trainers', 'view_trainer_profiles', 'view_centers', 'manage_centers',
            'view_kits', 'manage_kits', 'review_training_kit_nominations', 'view_programs', 'manage_programs',
            'program_bank.view', 'program_bank.create', 'program_bank.update', 'program_bank.delete',
            'program_bank.approve', 'program_bank.reports', 'view_courses', 'manage_courses', 'view_course_details',
            'view_trainees', 'manage_trainees', 'view_certificates', 'issue_certificates', 'view_certificate_approvals',
            'approve_training_certificates', 'print_certificates', 'verify_certificates', 'view_reports',
            'view_registration_requests', 'review_center_registration_requests', 'review_trainer_registration_requests',
            'review_trainee_registration_requests',
        ], $workforceManager),
        'training_supervisor' => array_merge([
            'view_trainers', 'view_trainer_profiles', 'view_centers', 'view_kits', 'view_programs', 'view_courses',
            'view_course_details', 'view_trainees', 'view_certificates', 'view_certificate_approvals',
            'print_certificates', 'verify_certificates', 'view_registration_requests',
            'review_center_registration_requests', 'review_trainer_registration_requests',
            'review_trainee_registration_requests', 'review_training_kit_nominations', 'view_reports',
        ], $workforceBasic),
        'center_user' => array_merge([
            'view_centers', 'manage_centers', 'view_kits', 'manage_kits', 'view_trainers', 'manage_trainers',
            'view_courses', 'manage_courses', 'view_course_details', 'view_trainees', 'manage_trainees',
            'view_certificates', 'issue_certificates', 'view_certificate_approvals', 'approve_center_certificates',
            'print_certificates', 'verify_certificates', 'view_registration_requests',
            'create_center_registration_requests', 'create_trainer_registration_requests',
            'create_trainee_registration_requests', 'complete_course_registration_requests',
        ], $workforceEmployer),
        'trainer_user' => [
            'view_courses', 'view_course_details', 'view_trainees', 'view_certificates', 'print_certificates',
            'view_trainer_profiles', 'edit_own_trainer_profile', 'nominate_training_kits',
            'create_trainer_registration_requests', 'create_course_registration_requests', 'confirm_course_registration_requests',
        ],
        'trainee_user' => array_merge([
            'view_courses', 'view_course_details', 'view_certificates', 'print_certificates',
            'create_trainee_registration_requests', 'create_course_registration_requests', 'confirm_course_registration_requests',
        ], $workforceBasic),
        'auditor' => array_merge([
            'view_trainers', 'view_trainer_profiles', 'view_centers', 'view_kits', 'view_programs', 'program_bank.view',
            'program_bank.reports', 'view_courses', 'view_course_details', 'view_trainees', 'view_certificates',
            'view_certificate_approvals', 'view_audit', 'view_reports', 'verify_certificates', 'view_registration_requests',
            'view_finance',
        ], $financeViewOnly, $needsAuditor),
        'data_entry' => $needsDataEntry,
        'data_reviewer' => $needsDataReviewer,
        'project_services_manager' => array_merge($needsProjectServices, $projectServicesTraining, $projectServicesAdmin, $workforceManager),
        'development_manager' => $needsDevelopment,
        'local_development_manager' => $needsDevelopment,
        'finance_manager' => $financeManagerRole,
        'finance_officer' => [
            'finance.applications.view', 'finance.applications.create', 'finance.applications.update',
            'finance.applications.review_branch', 'finance.consultants.view', 'finance.partners.view',
            'finance.loans.view', 'finance.metrics.view', 'finance.metrics.national',
        ],
        'consultant_office' => $financeConsultantOffice,
        'funding_partner' => $financePartnerRole,
        'consultant_union_admin' => $consultantUnionAdmin,
        'central_bank_admin' => $centralBankAdmin,
        'project_owner' => array_merge($financeProjectOwner, $workforceEmployer, $incubationView),
        'incubator_manager' => array_merge($incubationManage, ['view_reports', 'view_governorates', 'view_branches']),
        'incubator_mentor' => $incubationMentor,
        'entrepreneur_manager' => array_merge($entrepreneurManage, ['view_reports', 'view_governorates']),
        'media_manager' => array_merge($mediaManage, ['view_reports']),
        'super_admin' => $permissions,
        'system_admin' => $accessPermissions,
    ];
}

function loadRouteMap(string $projectRoot): array
{
    $map = [];

    try {
        if (!class_exists(Illuminate\Support\Facades\Route::class)) {
            require $projectRoot . '/vendor/autoload.php';
            $app = require $projectRoot . '/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();
        }

        $routes = Illuminate\Support\Facades\Route::getRoutes();
        foreach ($routes as $route) {
            foreach ($route->methods() as $httpMethod) {
                if (in_array($httpMethod, ['HEAD', 'OPTIONS'], true)) {
                    continue;
                }

                $uri = $route->uri();
                if (!str_starts_with($uri, 'api/')) {
                    continue;
                }

                $relPath = '/' . substr($uri, 4);
                $key = $httpMethod . ' ' . normalizeRouteUri($relPath);
                $middleware = $route->gatherMiddleware();

                $permissions = [];
                $roles = [];
                $roleOr = [];
                $requiresAuth = false;

                foreach ($middleware as $mw) {
                    if (is_string($mw)) {
                        if ($mw === 'auth:sanctum' || str_contains($mw, 'auth:sanctum')) {
                            $requiresAuth = true;
                        }
                        if (str_starts_with($mw, 'permission:')) {
                            $permissions = array_merge($permissions, explode('|', substr($mw, 11)));
                        }
                        if (str_starts_with($mw, 'role:')) {
                            $roles = array_merge($roles, explode('|', substr($mw, 5)));
                        }
                        if (str_starts_with($mw, 'role_or_permission:')) {
                            $roleOr = array_merge($roleOr, explode('|', substr($mw, 19)));
                        }
                    }
                }

                $authorizations = extractAuthorizations($route->getActionName(), $projectRoot);

                $map[$key] = [
                    'uri' => $relPath,
                    'middleware' => $middleware,
                    'permissions' => array_values(array_unique($permissions)),
                    'roles' => array_values(array_unique($roles)),
                    'role_or_permissions' => array_values(array_unique($roleOr)),
                    'requires_auth' => $requiresAuth,
                    'authorizations' => $authorizations,
                ];
            }
        }
    } catch (Throwable $e) {
        echo "Route load warning: {$e->getMessage()}\n";
    }

    return $map;
}

function extractAuthorizations(string $action, string $projectRoot): array
{
    if (!str_contains($action, '@') && !str_contains($action, '::')) {
        return [];
    }

    $separator = str_contains($action, '@') ? '@' : '::';
    [$controller, $method] = explode($separator, $action, 2);

    if (!class_exists($controller)) {
        return [];
    }

    $auth = [];
    try {
        $ref = new ReflectionMethod($controller, $method);
        $file = $ref->getFileName();
        $start = $ref->getStartLine();
        $end = min($ref->getEndLine(), $start + 40);
        if ($file && $start) {
            $lines = array_slice(file($file), $start - 1, $end - $start + 1);
            $chunk = implode('', $lines);
            if (preg_match_all("/authorize\s*\(\s*['\"]([^'\"]+)['\"]/", $chunk, $m)) {
                $auth = array_values(array_unique($m[1]));
            }
            if (preg_match_all('/\$this->authorize\s*\(\s*([^,)]+)/', $chunk, $m2)) {
                foreach ($m2[1] as $expr) {
                    $auth[] = trim($expr);
                }
            }
        }
    } catch (Throwable) {
        // ignore
    }

    return array_values(array_unique($auth));
}

function normalizeRouteUri(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $path = preg_replace('/\{[^}]+\}/', '{id}', $path) ?? $path;

    return rtrim($path, '/') ?: '/';
}

function findFuzzyRoute(array $routeMap, string $method, string $path): array
{
    $norm = normalizeRouteUri($path);
    $key = $method . ' ' . $norm;
    if (isset($routeMap[$key])) {
        return $routeMap[$key];
    }

    foreach ($routeMap as $rk => $route) {
        if (!str_starts_with($rk, $method . ' ')) {
            continue;
        }
        $routePath = normalizeRouteUri($route['uri']);
        if ($routePath === $norm) {
            return $route;
        }
    }

    return [
        'middleware' => [],
        'permissions' => [],
        'roles' => [],
        'role_or_permissions' => [],
        'requires_auth' => !isPublicPath($path),
        'authorizations' => [],
    ];
}

function computeExpectedAccess(
    string $actor,
    array $rolePermissions,
    bool $requiresAuth,
    array $permissions,
    array $roles,
    array $roleOrPermissions,
    array $authorizations
): array {
    if (!$requiresAuth) {
        return ['result' => 'allowed', 'http_status' => '200', 'reason' => 'public endpoint'];
    }

    $perms = array_flip($rolePermissions[$actor] ?? []);
    $hasNationalAdmin = in_array($actor, NATIONAL_ADMIN_ROLES, true);

    if ($permissions !== []) {
        $missing = [];
        foreach ($permissions as $p) {
            if (!isset($perms[$p]) && !$hasNationalAdmin) {
                $missing[] = $p;
            }
        }
        if ($missing !== [] && !$hasNationalAdmin) {
            return [
                'result' => 'forbidden',
                'http_status' => '403',
                'reason' => 'missing permission: ' . implode(', ', $missing),
            ];
        }

        return [
            'result' => 'allowed',
            'http_status' => '200',
            'reason' => $hasNationalAdmin ? 'national admin bypass' : 'has required permission(s)',
        ];
    }

    if ($roles !== []) {
        if (in_array($actor, $roles, true) || $hasNationalAdmin) {
            return ['result' => 'allowed', 'http_status' => '200', 'reason' => 'has required role'];
        }

        return ['result' => 'forbidden', 'http_status' => '403', 'reason' => 'missing role: ' . implode('|', $roles)];
    }

    if ($roleOrPermissions !== []) {
        foreach ($roleOrPermissions as $item) {
            if ($item === $actor || isset($perms[$item]) || $hasNationalAdmin) {
                return ['result' => 'allowed', 'http_status' => '200', 'reason' => 'role_or_permission match'];
            }
        }

        return [
            'result' => 'forbidden',
            'http_status' => '403',
            'reason' => 'missing role_or_permission: ' . implode('|', $roleOrPermissions),
        ];
    }

    $reason = 'auth:sanctum only';
    if ($authorizations !== []) {
        $reason .= '; policy checks: ' . implode(', ', $authorizations);
    }

    return ['result' => 'allowed', 'http_status' => '200', 'reason' => $reason];
}

function testActorLogins(array $actors): array
{
    $results = [];
    $url = MODULE_BASE_URL . '/login';

    foreach ($actors as $actor) {
        $creds = ACTOR_CREDENTIALS[$actor] ?? null;
        if (!$creds) {
            $results[$actor] = ['status' => 'LOGIN_FAILED', 'detail' => 'no credentials defined'];
            continue;
        }

        $response = httpPostJson($url, [
            'email' => $creds['email'],
            'password' => $creds['password'],
            'device_name' => 'apidog-final-builder',
        ]);

        if ($response['http'] === 200 && !empty($response['json']['token'])) {
            $results[$actor] = ['status' => 'LOGIN_OK', 'detail' => 'token received'];
        } else {
            $msg = $response['json']['message'] ?? ($response['error'] ?: 'HTTP ' . $response['http']);
            $results[$actor] = ['status' => 'LOGIN_FAILED', 'detail' => (string) $msg];
        }

        usleep(100_000);
    }

    return $results;
}

function httpPostJson(string $url, array $body): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $raw = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $json = null;
    if (is_string($raw) && $raw !== '') {
        $json = json_decode($raw, true);
    }

    return ['http' => $http, 'json' => $json, 'error' => $error, 'raw' => $raw];
}

function validateOutputs(
    string $collectionPath,
    string $envPath,
    string $actorsPath,
    string $rbacPath,
    int $expectedActors,
    array $loginResults
): array {
    $collection = json_decode((string) file_get_contents($collectionPath), true);
    $env = json_decode((string) file_get_contents($envPath), true);
    $actors = json_decode((string) file_get_contents($actorsPath), true);
    $rbac = json_decode((string) file_get_contents($rbacPath), true);

    $checks = [];
    $checks['json_valid'] = is_array($collection) && is_array($env) && is_array($actors) && is_array($rbac);

    $envKeys = [];
    foreach ($env['values'] ?? [] as $v) {
        $envKeys[$v['key']] = $v['value'];
    }

    $actorEmails = array_filter(array_keys($envKeys), static fn ($k) => str_ends_with($k, '_email'));
    $checks['actors_count'] = count($actorEmails) === $expectedActors;

    $checks['active_actor_admin'] = ($envKeys['active_actor'] ?? '') === 'admin';

    foreach (array_keys(ACTOR_CREDENTIALS) as $actor) {
        if (empty($envKeys["{$actor}_email"]) || empty($envKeys["{$actor}_password"])) {
            $checks["actor_{$actor}_creds"] = false;
        }
        if (!array_key_exists("{$actor}_token", $envKeys)) {
            $checks["actor_{$actor}_token"] = false;
        }
    }

    $stats = ['endpoints' => 0, 'duplicates' => 0, 'base_url' => 0, 'full_url' => 0];
    $seen = [];
    $loginOk = false;
    $loginUsesVars = false;
    $tokenScriptOk = false;
    $protectedBearer = 0;
    $publicNoAuth = 0;

    walkItems($collection['item'], function (array $item) use (&$stats, &$seen, &$loginOk, &$loginUsesVars, &$tokenScriptOk, &$protectedBearer, &$publicNoAuth): void {
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
            $loginUsesVars = str_contains($body, '{{email}}') && str_contains($body, '{{password}}');
            foreach ($item['event'] ?? [] as $ev) {
                if (($ev['listen'] ?? '') === 'test') {
                    $exec = implode("\n", $ev['script']['exec'] ?? []);
                    if (str_contains($exec, 'json.token') && str_contains($exec, '${actor}_token')) {
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

    $checks['endpoints_379'] = $stats['endpoints'] === 379;
    $checks['duplicates_0'] = $stats['duplicates'] === 0;
    $checks['no_base_url'] = $stats['base_url'] === 0;
    $checks['no_full_url'] = $stats['full_url'] === 0;
    $checks['login_path'] = $loginOk;
    $checks['login_vars'] = $loginUsesVars;
    $checks['token_script'] = $tokenScriptOk;
    $checks['prerequest_active_actor'] = str_contains($prerequest, 'active_actor') && str_contains($prerequest, 'throw new Error');
    $checks['actors_test_data'] = count($actors) === $expectedActors;
    $checks['rbac_entries'] = count($rbac['entries'] ?? []) === 379;

    $passed = !in_array(false, $checks, true)
        && $stats['endpoints'] === 379
        && $stats['duplicates'] === 0
        && $stats['base_url'] === 0
        && $stats['full_url'] === 0
        && $loginOk
        && $loginUsesVars
        && $tokenScriptOk;

    return [
        'passed' => $passed,
        'checks' => $checks,
        'stats' => $stats,
        'protected_bearer' => $protectedBearer,
        'public_no_auth' => $publicNoAuth,
        'login_results' => $loginResults,
    ];
}

function printReport(array $validation, array $loginResults): void
{
    echo "=== SMEDC Apidog Final Build ===\n\n";
    echo "Files:\n";
    echo "  - SMEDC_Apidog_Final.postman_collection.json\n";
    echo "  - SMEDC_Production_Prefilled.postman_environment.json\n";
    echo "  - SMEDC_Actors_TestData.json\n";
    echo "  - SMEDC_RBAC_TestMatrix.json\n\n";

    echo "Validation:\n";
    foreach ($validation['checks'] as $k => $v) {
        echo '  ' . ($v ? 'OK' : 'FAIL') . " {$k}\n";
    }
    echo "\nStats:\n";
    echo '  Endpoints: ' . $validation['stats']['endpoints'] . "\n";
    echo '  Duplicates: ' . $validation['stats']['duplicates'] . "\n";
    echo '  Protected Bearer: ' . $validation['protected_bearer'] . "\n";
    echo '  Public no auth: ' . $validation['public_no_auth'] . "\n\n";

    $ok = 0;
    $fail = 0;
    $failedActors = [];
    foreach ($loginResults as $actor => $r) {
        if ($r['status'] === 'LOGIN_OK') {
            $ok++;
        } else {
            $fail++;
            $failedActors[] = $actor;
        }
        echo "  {$actor}: {$r['status']} ({$r['detail']})\n";
    }

    echo "\nLogin summary: OK={$ok} FAILED={$fail}\n";
    if ($failedActors !== []) {
        echo 'Failed actors: ' . implode(', ', $failedActors) . "\n";
    }

    echo "\nBuild " . ($validation['passed'] ? 'PASSED' : 'FAILED') . "\n";
}
