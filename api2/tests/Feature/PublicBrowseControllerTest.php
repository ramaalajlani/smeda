<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBrowseControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<string> */
    private function publicEndpoints(): array
    {
        return [
            '/api/public/governorates',
            '/api/public/needs/lookups',
            '/api/public/needs/map',
            '/api/public/training-programs',
            '/api/public/finance/cloud',
            '/api/public/finance/metrics',
        ];
    }

    public function test_guest_can_access_public_endpoints_without_token(): void
    {
        foreach ($this->publicEndpoints() as $uri) {
            $this->getJson($uri)->assertOk();
        }
    }

    public function test_public_finance_metrics_returns_public_scope_and_empty_safe_payload(): void
    {
        $this->getJson('/api/public/finance/metrics')
            ->assertOk()
            ->assertJsonPath('data.scope', 'public')
            ->assertJsonPath('data.total_applications', 0)
            ->assertJsonPath('data.funded_applications', 0)
            ->assertJsonPath('data.status_breakdown', []);
    }

    public function test_public_finance_cloud_does_not_expose_sensitive_applicant_fields(): void
    {
        $response = $this->getJson('/api/public/finance/cloud')->assertOk();
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        foreach ($json['data'] as $row) {
            $this->assertArrayNotHasKey('applicant_name', $row);
            $this->assertArrayNotHasKey('applicant_email', $row);
            $this->assertArrayNotHasKey('applicant_phone', $row);
            $this->assertArrayNotHasKey('created_by', $row);
        }
    }

    public function test_protected_admin_endpoints_remain_forbidden_for_guest(): void
    {
        $this->getJson('/api/admin/access-summary')->assertUnauthorized();
        $this->getJson('/api/dashboard')->assertUnauthorized();
        $this->getJson('/api/finance/metrics')->assertUnauthorized();
    }
}
