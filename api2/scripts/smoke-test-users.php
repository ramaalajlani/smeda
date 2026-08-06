<?php

/**
 * Dev-only smoke test fixtures. Run: php scripts/smoke-test-users.php
 */

use App\Models\Branch;
use App\Models\ConsultantAssignment;
use App\Models\ConsultantOffice;
use App\Models\FundingApplication;
use App\Models\FundingPartner;
use App\Models\FundingPartnerAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (!app()->environment(['local', 'testing'])) {
    fwrite(STDERR, "Refused: smoke fixtures only allowed in local/testing.\n");
    exit(1);
}

$app->make(DatabaseSeeder::class)->run();

$general = User::query()->where('email', 'general@system.com')->firstOrFail();
$branch = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
$password = bcrypt('12345678');

$partner = FundingPartner::query()->updateOrCreate(
    ['name' => 'Smoke Test Bank'],
    ['status' => 'active', 'created_by' => $general->id]
);

$partnerUser = User::query()->updateOrCreate(
    ['email' => 'smoke-funding-partner@system.com'],
    [
        'name' => 'Smoke Funding Partner',
        'password' => $password,
        'entity_type' => 'funding_partner',
        'funding_partner_id' => $partner->id,
        'governorate_id' => $branch->governorate_id,
        'branch_id' => $branch->id,
        'is_active' => true,
    ]
);
$partnerUser->syncRoles(['funding_partner']);

$office = ConsultantOffice::query()->updateOrCreate(
    ['name' => 'Smoke Consultant Office'],
    ['status' => 'active', 'created_by' => $general->id]
);

$consultantUser = User::query()->updateOrCreate(
    ['email' => 'smoke-consultant@system.com'],
    [
        'name' => 'Smoke Consultant Office',
        'password' => $password,
        'entity_type' => 'consultant_office',
        'consultant_office_id' => $office->id,
        'governorate_id' => $branch->governorate_id,
        'branch_id' => $branch->id,
        'is_active' => true,
    ]
);
$consultantUser->syncRoles(['consultant_office']);

$dataEntryUser = User::query()->updateOrCreate(
    ['email' => 'smoke-data-entry@system.com'],
    [
        'name' => 'Smoke Data Entry',
        'password' => $password,
        'entity_type' => 'data_entry',
        'governorate_id' => $branch->governorate_id,
        'branch_id' => $branch->id,
        'is_active' => true,
    ]
);
$dataEntryUser->syncRoles(['data_entry']);

$appForPartner = FundingApplication::query()->updateOrCreate(
    ['application_number' => 'SMOKE-PARTNER-001'],
    [
        'applicant_user_id' => $general->id,
        'applicant_name' => 'Smoke Applicant',
        'phone' => '0999000001',
        'governorate_id' => $branch->governorate_id,
        'branch_id' => $branch->id,
        'project_name' => 'Smoke Partner Project',
        'requested_amount' => 1000000,
        'status' => 'funder_review',
        'current_stage' => 'funder_review',
        'created_by' => $general->id,
    ]
);

$partnerAssignment = FundingPartnerAssignment::query()->updateOrCreate(
    [
        'funding_application_id' => $appForPartner->id,
        'funding_partner_id' => $partner->id,
    ],
    [
        'assigned_by' => $general->id,
        'assigned_at' => now(),
        'status' => 'assigned',
    ]
);

$appForConsultant = FundingApplication::query()->updateOrCreate(
    ['application_number' => 'SMOKE-CONSULT-001'],
    [
        'applicant_user_id' => $general->id,
        'applicant_name' => 'Smoke Applicant 2',
        'phone' => '0999000002',
        'governorate_id' => $branch->governorate_id,
        'branch_id' => $branch->id,
        'project_name' => 'Smoke Consultant Project',
        'requested_amount' => 2000000,
        'status' => 'consultant_review',
        'current_stage' => 'consultant_review',
        'created_by' => $general->id,
    ]
);

$consultantAssignment = ConsultantAssignment::query()->updateOrCreate(
    [
        'funding_application_id' => $appForConsultant->id,
        'consultant_office_id' => $office->id,
    ],
    [
        'assigned_by' => $general->id,
        'assigned_at' => now(),
        'status' => 'accepted',
    ]
);

echo json_encode([
    'password' => '12345678',
    'funding_partner' => [
        'email' => $partnerUser->email,
        'assignment_id' => $partnerAssignment->id,
    ],
    'consultant_office' => [
        'email' => $consultantUser->email,
        'assignment_id' => $consultantAssignment->id,
    ],
    'data_entry' => [
        'email' => $dataEntryUser->email,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
