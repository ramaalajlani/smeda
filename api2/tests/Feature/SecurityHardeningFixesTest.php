<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Support\SignedPrintUrl;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityHardeningFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_unsigned_print_routes_are_forbidden(): void
    {
        $trainee = Trainee::query()->firstOrFail();

        $this->get('/trainees/' . $trainee->id . '/card')->assertForbidden();
        $this->get('/certificates/1/print')->assertForbidden();
        $this->get('/trainers/1/card')->assertForbidden();
    }

    public function test_signed_print_routes_are_accessible_for_eligible_trainee(): void
    {
        $trainee = Trainee::query()->firstOrFail();

        $certificate = Certificate::query()->where('trainee_id', $trainee->id)->first()
            ?? Certificate::query()->firstOrFail();

        $certificate->update([
            'trainee_id' => $trainee->id,
            'status' => 'approved',
            'is_verified' => true,
        ]);

        $url = SignedPrintUrl::traineeCard($trainee->id);

        $this->get($url)->assertOk();
    }

    public function test_map_training_centers_return_signed_print_links(): void
    {
        $response = $this->getJson('/api/map/training-centers');

        $response->assertOk();

        $link = collect($response->json('data'))->first()['link'] ?? null;
        $this->assertNotNull($link);
        $this->assertStringContainsString('signature=', $link);
        $this->assertStringContainsString('expires=', $link);
    }

    public function test_center_user_cannot_view_trainer_profile_from_other_center(): void
    {
        $centerUser = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($centerUser);

        $otherTrainerId = Trainer::query()
            ->where('training_center_id', '!=', $centerUser->training_center_id)
            ->value('id');

        if (!$otherTrainerId) {
            $otherCenterId = TrainingCenter::query()
                ->where('id', '!=', $centerUser->training_center_id)
                ->value('id');

            $otherTrainerId = Trainer::query()->create([
                'training_center_id' => $otherCenterId,
                'name' => 'Other Center Trainer',
                'trainer_code' => 'TRN-OTHER-CENTER',
                'status' => 'active',
            ])->id;
        }

        $response = $this->getJson('/api/trainer-profiles/' . $otherTrainerId);
        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_map_training_courses_endpoint_does_not_crash(): void
    {
        $user = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/map/training-courses');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'meta' => ['count', 'limit']]);
    }

    public function test_trainer_user_cannot_view_other_trainer_profile(): void
    {
        $trainerUser = User::query()->where('email', 'trainer@system.com')->firstOrFail();
        Sanctum::actingAs($trainerUser);

        $otherTrainerId = Trainer::query()
            ->where('id', '!=', $trainerUser->trainer_id)
            ->value('id');

        $this->assertNotNull($otherTrainerId);

        $this->getJson('/api/trainer-profiles/' . $otherTrainerId)->assertNotFound();
    }

    public function test_trainer_profile_show_does_not_create_profile_on_get(): void
    {
        $admin = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $trainer = Trainer::query()->doesntHave('profile')->first();
        if (!$trainer) {
            $trainer = Trainer::query()->firstOrFail();
            $trainer->profile?->delete();
        }

        $before = $trainer->fresh()->profile;
        $this->assertNull($before);

        $this->getJson('/api/trainer-profiles/' . $trainer->id)->assertOk();

        $this->assertNull($trainer->fresh()->profile);
    }

    public function test_center_user_cannot_approve_certificate_outside_scope(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $certificate = Certificate::query()
            ->where('training_center_id', '!=', $user->training_center_id)
            ->firstOrFail();

        $certificate->update(['status' => 'pending_center_approval']);

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'center_approval',
            'decision' => 'approved',
        ])->assertNotFound();
    }

    public function test_entity_code_generator_produces_unique_trainee_codes(): void
    {
        $generator = app(\App\Services\Training\EntityCodeGenerator::class);

        $first = $generator->nextTraineeCode();
        Trainee::query()->create([
            'name' => 'Code Gen Test',
            'trainee_code' => $first,
            'status' => 'active',
        ]);
        $second = $generator->nextTraineeCode();

        $this->assertNotSame($first, $second);
    }

    public function test_system_admin_gets_dashboard_data(): void
    {
        $user = User::query()->create([
            'name' => 'System Admin Test',
            'email' => 'sysadmin-test@example.com',
            'password' => bcrypt('password'),
            'entity_type' => 'system_admin',
            'is_active' => true,
        ]);
        $user->assignRole('system_admin');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonPath(
            'message',
            'استخدم قسم إدارة النظام لإدارة المستخدمين والأدوار والصلاحيات.'
        );
    }

    public function test_training_course_resource_returns_signed_certificate_urls(): void
    {
        $user = User::query()->where('email', 'admin@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $course = TrainingCourse::query()->has('certificates')->firstOrFail();

        $response = $this->getJson('/api/training-courses/' . $course->id);

        $response->assertOk();

        $printUrl = collect($response->json('data.certificates'))->first()['printable_url'] ?? null;
        $this->assertNotNull($printUrl);
        $this->assertStringContainsString('signature=', $printUrl);
        $this->assertStringContainsString('expires=', $printUrl);
    }

    public function test_signed_print_urls_are_returned_in_certificate_resource(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $certificate = Certificate::query()
            ->where('training_center_id', $user->training_center_id)
            ->first();

        if (!$certificate) {
            $course = TrainingCourse::query()
                ->where('training_center_id', $user->training_center_id)
                ->first()
                ?? TrainingCourse::query()->firstOrFail();

            if ((int) $course->training_center_id !== (int) $user->training_center_id) {
                $course->update(['training_center_id' => $user->training_center_id]);
            }

            $trainee = Trainee::query()->firstOrFail();

            $certificate = Certificate::query()->create([
                'trainee_id' => $trainee->id,
                'training_course_id' => $course->id,
                'training_center_id' => $user->training_center_id,
                'certificate_number' => 'CERT-SEC-HARDEN-1',
                'certificate_code' => 'SEC-HARDEN-CODE-1',
                'certificate_type' => 'attendance',
                'result' => 'passed',
                'hours_awarded' => 8,
                'training_hours' => 8,
                'status' => 'approved',
                'is_verified' => true,
            ]);
        }

        $response = $this->getJson('/api/certificates/' . $certificate->id);

        $response->assertOk();
        $printUrl = $response->json('data.printable_url');
        $this->assertStringContainsString('signature=', $printUrl);
        $this->assertStringContainsString('expires=', $printUrl);
    }
}
