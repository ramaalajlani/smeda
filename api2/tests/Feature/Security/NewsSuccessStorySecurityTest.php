<?php

namespace Tests\Feature\Security;

use App\Models\News;
use App\Models\SuccessStory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewsSuccessStorySecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Permission::findOrCreate('news.manage', 'sanctum');
        Permission::findOrCreate('story.manage', 'sanctum');
    }

    public function test_public_news_index_shows_only_published(): void
    {
        News::query()->create([
            'title' => 'منشور',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'status' => 'published',
            'published_at' => now(),
        ]);
        News::query()->create([
            'title' => 'مسودة',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/news')->assertOk();
        $titles = collect($response->json('data'))->pluck('title');

        $this->assertTrue($titles->contains('منشور'));
        $this->assertFalse($titles->contains('مسودة'));
    }

    public function test_public_news_index_works_without_token(): void
    {
        News::query()->create([
            'title' => 'خبر عام',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->getJson('/api/news')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'خبر عام');
    }

    public function test_status_draft_query_does_not_expose_drafts_for_regular_user(): void
    {
        $user = User::factory()->create();
        News::query()->create([
            'title' => 'مسودة مخفية',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'status' => 'draft',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/news?status=draft')->assertOk();
        $titles = collect($response->json('data'))->pluck('title');

        $this->assertFalse($titles->contains('مسودة مخفية'));
    }

    public function test_public_news_show_draft_returns_404(): void
    {
        $user = User::factory()->create();
        $draft = News::query()->create([
            'title' => 'مسودة',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'status' => 'draft',
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/news/{$draft->id}")->assertNotFound();
    }

    public function test_media_manager_can_see_draft_news(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(Role::findByName('admin', 'sanctum'));
        $draft = News::query()->create([
            'title' => 'مسودة إدارية',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'status' => 'draft',
        ]);

        Sanctum::actingAs($manager);

        $this->getJson("/api/news/{$draft->id}")
            ->assertOk()
            ->assertJsonPath('title', 'مسودة إدارية');
    }

    public function test_success_stories_index_shows_only_published_by_default(): void
    {
        SuccessStory::query()->create([
            'title' => 'قصة منشورة',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'hero_name' => 'بطل',
            'status' => 'published',
            'published_at' => now(),
        ]);
        SuccessStory::query()->create([
            'title' => 'قصة مسودة',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'hero_name' => 'بطل',
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/success-stories')->assertOk();
        $titles = collect($response->json('data'))->pluck('title');

        $this->assertTrue($titles->contains('قصة منشورة'));
        $this->assertFalse($titles->contains('قصة مسودة'));
    }

    public function test_success_stories_public_index_works_without_token(): void
    {
        SuccessStory::query()->create([
            'title' => 'قصة للزائر',
            'summary' => 'ملخص',
            'body' => 'محتوى',
            'hero_name' => 'بطل',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->getJson('/api/success-stories')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'قصة للزائر');
    }

    public function test_success_stories_stats_requires_manage_permission(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/success-stories/stats')->assertForbidden();
    }

    public function test_success_stories_stats_accessible_for_manager(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findByName('admin', 'sanctum'));

        Sanctum::actingAs($admin);

        $this->getJson('/api/success-stories/stats')->assertOk();
    }
}
