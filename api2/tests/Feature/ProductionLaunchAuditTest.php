<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Certificate;
use App\Models\Trainee;
use App\Models\TrainingCourse;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductionLaunchAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rate_limit_returns_429_after_threshold(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login', [
                'email' => 'rate-limit@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->postJson('/api/login', [
            'email' => 'rate-limit@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_trainee_user_cannot_access_foreign_certificate_by_id(): void
    {
        $this->seed(DatabaseSeeder::class);
        $branch = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();
        $course = TrainingCourse::query()->firstOrFail();

        $otherTrainee = Trainee::query()->create([
            'name' => 'Other Launch Trainee',
            'trainee_code' => 'TRN-LAUNCH-OTHER',
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
            'status' => 'active',
        ]);

        $foreignCertificate = Certificate::query()->create([
            'trainee_id' => $otherTrainee->id,
            'training_course_id' => $course->id,
            'training_center_id' => $course->training_center_id,
            'trainer_id' => $course->trainer_id,
            'training_kit_id' => $course->training_kit_id,
            'certificate_number' => 'CERT-LAUNCH-OTHER',
            'certificate_code' => 'CD-LAUNCH-OTHER',
            'certificate_type' => 'attendance',
            'result' => 'passed',
            'hours_awarded' => 8,
            'training_hours' => 8,
            'status' => 'approved',
            'branch_id' => $branch->id,
            'governorate_id' => $branch->governorate_id,
        ]);

        Sanctum::actingAs(User::query()->where('email', 'trainee@system.com')->firstOrFail());

        $this->getJson('/api/certificates/' . $foreignCertificate->id)->assertForbidden();
    }

    public function test_pending_certificate_public_print_returns_forbidden(): void
    {
        $this->seed(DatabaseSeeder::class);
        $course = TrainingCourse::query()->firstOrFail();
        $trainee = Trainee::query()->firstOrFail();

        $pending = Certificate::query()->create([
            'trainee_id' => $trainee->id,
            'training_course_id' => $course->id,
            'training_center_id' => $course->training_center_id,
            'trainer_id' => $course->trainer_id,
            'training_kit_id' => $course->training_kit_id,
            'certificate_number' => 'CERT-LAUNCH-PENDING',
            'certificate_code' => 'CD-LAUNCH-PENDING',
            'certificate_type' => 'attendance',
            'result' => 'passed',
            'hours_awarded' => 8,
            'training_hours' => 8,
            'status' => 'pending',
            'branch_id' => $course->branch_id,
            'governorate_id' => $course->governorate_id,
        ]);

        $this->get('/certificates/' . rawurlencode($pending->certificate_code) . '/print')
            ->assertForbidden();
    }
}
