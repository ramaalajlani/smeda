<?php

namespace Tests\Feature;

use Database\Seeders\SyriaLocationsTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SyriaLocationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SyriaLocationsTestSeeder::class);
        Cache::flush();
    }

    public function test_search_without_query_does_not_return_random_rows(): void
    {
        $this->getJson('/api/locations/search')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q']);

        $this->getJson('/api/locations/search?q=')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q']);

        $this->getJson('/api/locations/search?q=a')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    public function test_search_finds_governorate_and_community_names(): void
    {
        $this->getJson('/api/locations/search?q=' . urlencode('حلب'))
            ->assertOk()
            ->assertJsonFragment(['gov_name_ar' => 'حلب']);

        $this->getJson('/api/locations/search?q=' . urlencode('تجمع'))
            ->assertOk()
            ->assertJsonFragment(['name_ar' => 'تجمع تجريبي ١']);
    }

    public function test_map_points_returns_expected_structure_with_cache(): void
    {
        $first = $this->getJson('/api/locations/map?gov=SY03&limit=10')->assertOk();
        $first->assertJsonStructure(['data', 'total']);
        $this->assertSame($first->json('total'), count($first->json('data')));

        $second = $this->getJson('/api/locations/map?gov=SY03&limit=10')->assertOk();
        $this->assertSame($first->json('data'), $second->json('data'));
        $this->assertSame($first->json('total'), $second->json('total'));
    }
}
