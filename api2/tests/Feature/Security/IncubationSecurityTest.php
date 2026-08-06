<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\IncubatedProject;
use App\Models\IncubationApplication;
use App\Models\Incubator;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncubationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->branch = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
        Permission::findOrCreate('incubation.view', 'sanctum');
        Permission::findOrCreate('incubation.manage', 'sanctum');
    }

    private function userWithRole(string $role, array $extra = []): User
    {
        $user = User::factory()->create(array_merge([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
        ], $extra));
        $user->assignRole(Role::findByName($role, 'sanctum'));

        return $user;
    }

    private function createProjectForOwner(User $owner): IncubatedProject
    {
        $incubator = Incubator::query()->create([
            'name' => 'حاضنة اختبار',
            'code' => 'TST-' . uniqid(),
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
            'status' => 'active',
            'capacity' => 10,
        ]);

        $application = IncubationApplication::query()->create([
            'incubator_id' => $incubator->id,
            'applicant_user_id' => $owner->id,
            'project_name' => 'مشروع اختبار',
            'business_stage' => 'seed',
            'project_description' => 'وصف',
            'status' => 'accepted',
        ]);

        return IncubatedProject::query()->create([
            'application_id' => $application->id,
            'incubator_id' => $incubator->id,
            'owner_user_id' => $owner->id,
            'project_name' => 'مشروع اختبار',
            'start_date' => now()->toDateString(),
            'stage' => 'seed',
            'status' => 'active',
        ]);
    }

    private function reportPayload(): array
    {
        return [
            'period_type' => 'monthly',
            'period_label' => 'Jun-2026',
            'revenue' => 1000,
            'employees' => 2,
        ];
    }

    public function test_unauthenticated_user_cannot_submit_progress_report(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProjectForOwner($owner);

        $this->postJson("/api/incubation/projects/{$project->id}/reports", $this->reportPayload())
            ->assertUnauthorized();
    }

    public function test_user_cannot_report_on_someone_else_project(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
        ]);
        $intruder = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
        ]);
        $project = $this->createProjectForOwner($owner);

        Sanctum::actingAs($intruder);

        $this->postJson("/api/incubation/projects/{$project->id}/reports", $this->reportPayload())
            ->assertForbidden();
    }

    public function test_project_owner_can_submit_progress_report(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
        ]);
        $project = $this->createProjectForOwner($owner);

        Sanctum::actingAs($owner);

        $this->postJson("/api/incubation/projects/{$project->id}/reports", $this->reportPayload())
            ->assertCreated()
            ->assertJsonPath('period_label', 'Jun-2026');
    }

    public function test_incubator_manager_can_submit_progress_report(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
        ]);
        $manager = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
        ]);
        $manager->givePermissionTo('incubation.manage');
        $project = $this->createProjectForOwner($owner);

        Sanctum::actingAs($manager);

        $this->postJson("/api/incubation/projects/{$project->id}/reports", $this->reportPayload())
            ->assertCreated();
    }

    public function test_applicant_can_view_own_application_with_sensitive_data(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
            'email' => 'owner@example.com',
        ]);
        $project = $this->createProjectForOwner($owner);
        $application = $project->application;

        Sanctum::actingAs($owner);

        $this->getJson("/api/incubation/applications/{$application->id}")
            ->assertOk()
            ->assertJsonPath('applicant.email', 'owner@example.com');
    }

    public function test_other_user_cannot_view_application(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
        ]);
        $other = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
        ]);
        $project = $this->createProjectForOwner($owner);

        Sanctum::actingAs($other);

        $this->getJson("/api/incubation/applications/{$project->application_id}")
            ->assertForbidden();
    }

    public function test_auditor_with_view_permission_sees_redacted_application(): void
    {
        $owner = User::factory()->create([
            'branch_id' => $this->branch->id,
            'governorate_id' => $this->branch->governorate_id,
            'email' => 'owner-secret@example.com',
        ]);
        $auditor = $this->userWithRole('auditor');
        $project = $this->createProjectForOwner($owner);

        Sanctum::actingAs($auditor);

        $response = $this->getJson("/api/incubation/applications/{$project->application_id}")
            ->assertOk();

        $this->assertArrayNotHasKey('email', $response->json('applicant') ?? []);
        $this->assertSame('owner-secret@example.com', $owner->fresh()->email);
    }
}
