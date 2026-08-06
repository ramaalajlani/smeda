<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_governorates_is_accessible_without_auth(): void
    {
        $this->getJson('/api/public/governorates')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_public_needs_lookups_is_accessible_without_auth(): void
    {
        $this->getJson('/api/public/needs/lookups')
            ->assertOk()
            ->assertJsonStructure(['data' => ['sectors', 'status_codes']]);
    }

    public function test_public_needs_map_is_accessible_without_auth(): void
    {
        $this->getJson('/api/public/needs/map')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_public_training_programs_is_accessible_without_auth(): void
    {
        $this->getJson('/api/public/training-programs')->assertOk();
    }

    public function test_public_finance_cloud_is_accessible_without_auth(): void
    {
        $this->getJson('/api/public/finance/cloud')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_public_finance_metrics_is_accessible_without_auth(): void
    {
        $this->getJson('/api/public/finance/metrics')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'scope',
                    'total_applications',
                    'funded_applications',
                    'pending_applications',
                    'active_loans',
                    'defaulted_loans',
                    'repayment_rate',
                    'total_funded_amount',
                    'status_breakdown',
                ],
            ])
            ->assertJsonPath('data.scope', 'public');
    }
}
