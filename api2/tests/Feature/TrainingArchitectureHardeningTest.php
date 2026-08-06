<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrainingArchitectureHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_trainer_cannot_view_other_trainer_courses(): void
    {
        $trainerUser = User::query()->where('email', 'trainer@system.com')->firstOrFail();
        Sanctum::actingAs($trainerUser);

        $otherCourseId = TrainingCourse::query()
            ->where('trainer_id', '!=', $trainerUser->trainer_id)
            ->value('id');

        $this->assertNotNull($otherCourseId);
        $this->getJson('/api/training-courses/' . $otherCourseId)->assertNotFound();
    }

    public function test_center_cannot_create_course_for_other_center(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $otherCenterId = TrainingCenter::query()
            ->where('id', '!=', $user->training_center_id)
            ->value('id');

        $course = TrainingCourse::query()->firstOrFail();

        $this->postJson('/api/training-courses', [
            'training_center_id' => $otherCenterId,
            'trainer_id' => $course->trainer_id,
            'training_kit_id' => $course->training_kit_id,
            'title' => 'Unauthorized Course',
            'delivery_mode' => 'offline',
            'planned_hours' => 10,
            'capacity' => 10,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['training_center_id']);
    }

    public function test_issue_certificate_creates_audit_log(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $course = TrainingCourse::query()
            ->where('training_center_id', $user->training_center_id)
            ->where('status', 'completed')
            ->first();

        if (!$course) {
            $course = TrainingCourse::query()
                ->where('training_center_id', $user->training_center_id)
                ->firstOrFail();
            $course->update(['status' => 'completed', 'actual_hours' => $course->planned_hours]);
        }

        $trainee = $course->trainees()->first();
        if (!$trainee) {
            $this->markTestSkipped('No trainee enrolled in course.');
        }

        $course->trainees()->updateExistingPivot($trainee->id, [
            'attendance_status' => 'attended',
            'attended_hours' => 5,
        ]);

        $before = AuditLog::query()->where('action', 'certificate_issued')->count();

        Certificate::query()
            ->where('training_course_id', $course->id)
            ->where('trainee_id', $trainee->id)
            ->where('certificate_type', 'attendance')
            ->delete();

        $this->postJson('/api/certificates/issue', [
            'training_course_id' => $course->id,
            'trainee_id' => $trainee->id,
            'certificate_type' => 'attendance',
        ])->assertCreated();

        $this->assertSame($before + 1, AuditLog::query()->where('action', 'certificate_issued')->count());
    }

    public function test_cannot_issue_certificate_before_course_completion(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $course = TrainingCourse::query()
            ->where('training_center_id', $user->training_center_id)
            ->where('status', 'ongoing')
            ->first();

        if (!$course) {
            $course = TrainingCourse::query()
                ->where('training_center_id', $user->training_center_id)
                ->firstOrFail();
            $course->update(['status' => 'ongoing']);
        }

        $trainee = $course->trainees()->first();
        if (!$trainee) {
            $this->markTestSkipped('No trainee enrolled.');
        }

        $this->postJson('/api/certificates/issue', [
            'training_course_id' => $course->id,
            'trainee_id' => $trainee->id,
            'certificate_type' => 'attendance',
        ])->assertUnprocessable();
    }

    public function test_update_trainee_result_rejects_excessive_attended_hours(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $course = TrainingCourse::query()
            ->where('training_center_id', $user->training_center_id)
            ->where('status', 'ongoing')
            ->first();

        if (!$course) {
            $course = TrainingCourse::query()
                ->where('training_center_id', $user->training_center_id)
                ->firstOrFail();
            $course->update(['status' => 'ongoing']);
        }

        $trainee = $course->trainees()->first();
        if (!$trainee) {
            $this->markTestSkipped('No trainee enrolled.');
        }

        $this->patchJson('/api/training-courses/' . $course->id . '/trainees/' . $trainee->id, [
            'attended_hours' => $course->resolved_hours + 100,
        ])->assertUnprocessable();
    }

    public function test_certificate_approve_order_enforced(): void
    {
        $manager = User::query()->where('email', 'training_manager')->first();
        if (!$manager) {
            $manager = User::query()->where('email', 'admin@system.com')->firstOrFail();
        }

        Sanctum::actingAs($manager);

        $certificate = Certificate::query()->firstOrFail();
        $certificate->update(['status' => 'pending_center_approval']);

        $this->postJson('/api/certificates/' . $certificate->id . '/approve', [
            'approval_step' => 'deputy_director_approval',
            'decision' => 'approved',
        ])->assertUnprocessable();
    }
}
