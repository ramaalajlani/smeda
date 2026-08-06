<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\FundingApplication;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFinanceCloudExposureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_finance_cloud_does_not_expose_sensitive_fields(): void
    {
        $branch = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        $owner = User::factory()->create([
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
        ]);

        $app = FundingApplication::query()->create([
            'application_number' => 'FND-PUB-' . uniqid(),
            'applicant_user_id' => $owner->id,
            'applicant_name' => 'اسم حساس',
            'phone' => '0999888777',
            'email' => 'secret@example.com',
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
            'project_name' => 'مشروع سحابة عامة',
            'project_sector' => 'industrial',
            'financing_type' => 'capital',
            'requested_amount' => 9000000,
            'currency' => 'SYP',
            'purpose' => 'غرض عام',
            'description' => 'وصف داخلي حساس',
            'status' => 'approved',
            'current_stage' => 'approved',
            'created_by' => $owner->id,
        ]);

        $response = $this->getJson('/api/public/finance/cloud')->assertOk();
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertNotEmpty($json['data']);

        foreach ($json['data'] as $row) {
            $this->assertArrayNotHasKey('applicant_name', $row);
            $this->assertArrayNotHasKey('applicant_email', $row);
            $this->assertArrayNotHasKey('applicant_phone', $row);
            $this->assertArrayNotHasKey('phone', $row);
            $this->assertArrayNotHasKey('email', $row);
            $this->assertArrayNotHasKey('description', $row);
            $this->assertArrayNotHasKey('created_by', $row);
            $this->assertArrayNotHasKey('applicant_user_id', $row);
            $this->assertArrayNotHasKey('user_id', $row);
            $this->assertArrayNotHasKey('governorate', $row);
            $this->assertArrayNotHasKey('branch', $row);
            $this->assertArrayHasKey('consultant_assignments', $row);
            $this->assertSame([], $row['consultant_assignments']);
        }
    }
}
