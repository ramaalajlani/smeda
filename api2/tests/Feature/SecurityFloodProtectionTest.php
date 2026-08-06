<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use Tests\TestCase;

class SecurityFloodProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_map_centers_has_security_headers(): void
    {
        $response = $this->getJson('/api/map/training-centers');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_map_centers_respects_limit_parameter(): void
    {
        $response = $this->getJson('/api/map/training-centers?limit=1');

        $response->assertOk();
        $this->assertLessThanOrEqual(1, count($response->json('data')));
        $response->assertJsonPath('meta.limit', 1);
    }

    public function test_center_registration_rejects_php_upload(): void
    {
        Storage::fake('public');
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('license.php', 100, 'application/x-php');

        $this->post('/api/registration-requests/centers', [
            'center_name' => 'Test Center Flood',
            'city' => 'Riyadh',
            'address' => 'Street 1',
            'phone' => '0500000000',
            'supports_online_training' => false,
            'supports_offline_training' => true,
            'latitude' => 24.7,
            'longitude' => 46.7,
            'license_number' => 'LIC-123',
            'license_issue_date' => '2024-01-01',
            'license_issued_by' => 'MOE',
            'license_image' => $file,
        ], ['Accept' => 'application/json'])->assertUnprocessable();
    }

    public function test_registration_index_clamps_per_page(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/registration-requests/centers?per_page=9999');

        $response->assertOk();
        $this->assertLessThanOrEqual(100, $response->json('per_page'));
    }
}
