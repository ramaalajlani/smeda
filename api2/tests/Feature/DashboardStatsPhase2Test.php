<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ConsultingRequest;
use App\Models\FundedLoan;
use App\Models\FundingApplication;
use App\Models\Need;
use App\Models\User;
use App\Support\NeedStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardStatsPhase2Test extends TestCase
{
    use RefreshDatabase;

    private Branch $aleppo;
    private Branch $damascus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        $this->damascus = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();
    }

    public function test_general_director_can_fetch_consulting_stats(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $response = $this->getJson('/api/consulting/requests/stats')->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'total',
                'completed',
                'in_progress',
                'pending',
                'rejected',
                'by_status',
                'by_category',
                'recent',
                'pending_actions',
            ],
        ]);

        $this->assertGreaterThanOrEqual(0, $response->json('data.total'));
    }

    public function test_branch_manager_consulting_stats_are_branch_scoped(): void
    {
        $nationalTotal = ConsultingRequest::query()->count();
        $damascusTotal = ConsultingRequest::query()
            ->where(function ($q) {
                $q->where('branch_id', $this->damascus->id)
                    ->orWhere('governorate_id', $this->damascus->governorate_id);
            })
            ->count();

        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());

        $response = $this->getJson('/api/consulting/requests/stats')->assertOk();
        $statsTotal = (int) $response->json('data.total');

        $this->assertSame($damascusTotal, $statsTotal);
        if ($nationalTotal > $damascusTotal) {
            $this->assertLessThan($nationalTotal, $statsTotal);
        }
    }

    public function test_general_director_can_fetch_finance_loans_stats(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]);

        $app = FundingApplication::query()->create([
            'application_number' => 'FND-STATS-' . uniqid(),
            'applicant_user_id' => $owner->id,
            'applicant_name' => 'مختبر',
            'phone' => '0999999999',
            'governorate_id' => $this->aleppo->governorate_id,
            'branch_id' => $this->aleppo->id,
            'project_name' => 'مشروع إحصائيات',
            'requested_amount' => 1000000,
            'status' => 'funded',
            'current_stage' => 'funded',
            'created_by' => $owner->id,
        ]);

        FundedLoan::query()->create([
            'funding_application_id' => $app->id,
            'loan_number' => 'LN-STATS-' . uniqid(),
            'approved_amount' => 1000000,
            'status' => 'active',
        ]);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $response = $this->getJson('/api/finance/loans/stats')->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'total',
                'active',
                'defaulted',
                'closed',
                'paid',
                'total_funded_amount',
                'by_status',
            ],
        ]);

        $this->assertGreaterThanOrEqual(1, $response->json('data.total'));
    }

    public function test_finance_loans_index_supports_pagination_and_search(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]);

        $app = FundingApplication::query()->create([
            'application_number' => 'FND-PAGE-' . uniqid(),
            'applicant_user_id' => $owner->id,
            'applicant_name' => $owner->name,
            'phone' => '0999999999',
            'governorate_id' => $this->aleppo->governorate_id,
            'branch_id' => $this->aleppo->id,
            'project_name' => 'مشروع بحث فريد XYZ',
            'requested_amount' => 500000,
            'status' => 'funded',
            'current_stage' => 'funded',
            'created_by' => $owner->id,
        ]);

        $loan = FundedLoan::query()->create([
            'funding_application_id' => $app->id,
            'loan_number' => 'LN-SEARCH-XYZ-' . uniqid(),
            'approved_amount' => 500000,
            'status' => 'active',
        ]);

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->getJson('/api/finance/loans?per_page=20&page=1')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'per_page', 'total']);

        $search = $this->getJson('/api/finance/loans?search=XYZ&per_page=20')->assertOk();
        $this->assertCount(1, $search->json('data'));
        $this->assertSame($loan->id, $search->json('data.0.id'));
    }

    private function createNeed(Branch $branch, array $overrides = []): Need
    {
        $creator = User::factory()->create([
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
        ]);

        return Need::query()->create(array_merge([
            'need_code' => 'NEED-STATS-' . uniqid(),
            'title' => 'احتياج خريطة',
            'need_owner_type' => 'citizen',
            'need_scope' => 'individual',
            'source_platform' => 'gis',
            'governorate_id' => $branch->governorate_id,
            'branch_id' => $branch->id,
            'status' => NeedStatus::APPROVED,
            'approval_status' => NeedStatus::APPROVED,
            'priority' => 'متوسطة',
            'latitude' => 33.5,
            'longitude' => 36.3,
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ], $overrides));
    }

    public function test_needs_map_returns_truncation_meta(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        for ($i = 0; $i < 5; $i++) {
            $this->createNeed($this->damascus, [
                'need_code' => 'NEED-MAP-' . $i . '-' . uniqid(),
                'latitude' => 33.5 + ($i * 0.001),
                'longitude' => 36.3 + ($i * 0.001),
            ]);
        }

        $response = $this->getJson('/api/needs/map?limit=2')->assertOk();

        $response->assertJsonPath('meta.limit', 2);
        $response->assertJsonPath('meta.truncated', true);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_needs_search_requires_minimum_two_characters(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());

        $this->createNeed($this->damascus, [
            'title' => 'احتياج اختبار خاص',
            'need_code' => 'NEED-SEARCH-' . uniqid(),
        ]);

        $short = $this->getJson('/api/needs?q=a&per_page=20')->assertOk();
        $long = $this->getJson('/api/needs?q=اختبار&per_page=20')->assertOk();

        $this->assertGreaterThanOrEqual(
            count($long->json('data')),
            count($short->json('data'))
        );
    }

    public function test_admin_users_index_returns_pagination_meta(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@system.com')->firstOrFail());

        $response = $this->getJson('/api/admin/users?per_page=5&page=1')->assertOk();

        $response->assertJsonStructure([
            'data',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $this->assertLessThanOrEqual(5, count($response->json('data')));
        $this->assertSame(5, $response->json('meta.per_page'));
    }
}
