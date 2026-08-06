<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\EntrepreneurProfile;
use App\Models\FundedLoan;
use App\Models\FundingApplication;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase3PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private Branch $aleppo;
    private Branch $damascus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Cache::flush();
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        $this->damascus = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();
    }

    private function createEntrepreneurProfile(array $overrides = []): EntrepreneurProfile
    {
        $user = User::factory()->create();

        $profile = new EntrepreneurProfile();
        $profile->forceFill(array_merge([
            'user_id' => $user->id,
            'full_name' => 'رائد اختبار',
            'project_name' => 'مشروع اختبار',
            'project_field' => 'software',
            'governorate' => 'دمشق',
            'readiness_stage' => 'mvp',
            'seeking_investment' => true,
            'status' => 'submitted',
        ], $overrides));
        $profile->save();

        return $profile;
    }

    private function createLoan(Branch $branch, string $status = 'defaulted'): FundedLoan
    {
        $owner = User::factory()->create([
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
        ]);

        $app = FundingApplication::query()->create([
            'application_number' => 'FND-P3-'.uniqid(),
            'applicant_user_id' => $owner->id,
            'applicant_name' => $owner->name,
            'phone' => '0999999999',
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
            'project_name' => 'مشروع P3',
            'requested_amount' => 500000,
            'status' => 'funded',
            'current_stage' => 'funded',
            'created_by' => $owner->id,
        ]);

        return FundedLoan::query()->create([
            'funding_application_id' => $app->id,
            'loan_number' => 'LN-P3-'.uniqid(),
            'approved_amount' => 500000,
            'status' => $status,
        ]);
    }

    public function test_authorized_user_can_export_entrepreneur_profiles_csv(): void
    {
        $this->createEntrepreneurProfile(['full_name' => 'أحمد CSV', 'governorate' => 'دمشق']);
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $response = $this->get('/api/entrepreneur/profiles/export?status=submitted');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $body = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
        $this->assertStringContainsString('الاسم', $body);
        $this->assertStringContainsString('أحمد CSV', $body);
    }

    public function test_entrepreneur_csv_export_respects_governorate_filter(): void
    {
        $this->createEntrepreneurProfile(['full_name' => 'دمشقي', 'governorate' => 'دمشق']);
        $this->createEntrepreneurProfile(['full_name' => 'حلبي', 'governorate' => 'حلب']);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $body = $this->get('/api/entrepreneur/profiles/export?governorate=دمشق')
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('دمشقي', $body);
        $this->assertStringNotContainsString('حلبي', $body);
    }

    public function test_unauthorized_user_cannot_export_entrepreneur_profiles(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'trainee@system.com')->firstOrFail());

        $this->get('/api/entrepreneur/profiles/export')->assertForbidden();
    }

    public function test_dashboard_returns_consistent_data_with_cache(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $first = $this->getJson('/api/dashboard')->assertOk()->json();
        $second = $this->getJson('/api/dashboard')->assertOk()->json();

        $this->assertSame($first['dashboard_role'] ?? null, $second['dashboard_role'] ?? null);
        $this->assertSame($first['users_total'] ?? null, $second['users_total'] ?? null);
    }

    public function test_dashboard_cache_keys_differ_between_national_and_branch_manager(): void
    {
        $general = User::query()->where('email', 'general@system.com')->firstOrFail();
        $branchManager = User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail();

        Sanctum::actingAs($general);
        $national = $this->getJson('/api/dashboard')->assertOk()->json();

        Sanctum::actingAs($branchManager);
        $branch = $this->getJson('/api/dashboard')->assertOk()->json();

        $this->assertNotSame($national['dashboard_role'] ?? null, $branch['dashboard_role'] ?? null);
    }

    public function test_finance_defaulted_stats_returns_counts_only(): void
    {
        $this->createLoan($this->aleppo, 'defaulted');
        $this->createLoan($this->damascus, 'defaulted');

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $response = $this->getJson('/api/finance/defaulted/stats')->assertOk();

        $response->assertJsonStructure(['data' => ['total', 'total_amount']]);
        $this->assertGreaterThanOrEqual(2, $response->json('data.total'));
    }

    public function test_finance_funded_list_is_paginated_and_searchable(): void
    {
        $loan = $this->createLoan($this->aleppo, 'active');
        $loan->update(['loan_number' => 'LN-UNIQUE-P3-XYZ']);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $paginated = $this->getJson('/api/finance/funded?per_page=20&page=1')->assertOk();
        $paginated->assertJsonStructure(['data', 'current_page', 'last_page', 'per_page', 'total']);
        $this->assertLessThanOrEqual(20, count($paginated->json('data')));

        $search = $this->getJson('/api/finance/funded?search=UNIQUE-P3-XYZ&per_page=20')->assertOk();
        $this->assertCount(1, $search->json('data'));
    }

    public function test_branch_manager_finance_defaulted_stats_are_scoped(): void
    {
        $this->createLoan($this->aleppo, 'defaulted');
        $this->createLoan($this->damascus, 'defaulted');

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());

        $stats = $this->getJson('/api/finance/defaulted/stats')->assertOk()->json('data');

        $this->assertSame(1, $stats['total']);
    }
}
