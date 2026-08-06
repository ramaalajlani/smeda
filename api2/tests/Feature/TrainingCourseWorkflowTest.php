<?php

namespace Tests\Feature;

use App\Models\TrainingCourse;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrainingCourseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_center_user_can_create_training_course(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $template = TrainingCourse::query()
            ->when($user->training_center_id, fn ($q) => $q->where('training_center_id', $user->training_center_id))
            ->first();

        if (!$template) {
            $template = TrainingCourse::query()->firstOrFail();
            $user->forceFill(['training_center_id' => $template->training_center_id])->save();
        }

        $this->postJson('/api/training-courses', [
            'training_center_id' => $template->training_center_id,
            'trainer_id' => $template->trainer_id,
            'training_kit_id' => $template->training_kit_id,
            'title' => 'Workflow Test Course',
            'delivery_mode' => 'offline',
            'planned_hours' => 12,
            'capacity' => 15,
            'status' => 'scheduled',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Workflow Test Course');
    }

    public function test_center_user_can_update_trainee_result(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $course = TrainingCourse::query()
            ->when($user->training_center_id, fn ($q) => $q->where('training_center_id', $user->training_center_id))
            ->first();

        if (!$course) {
            $course = TrainingCourse::query()->firstOrFail();
            $user->forceFill(['training_center_id' => $course->training_center_id])->save();
        }

        $trainee = $course->trainees()->firstOrFail();

        \App\Models\Certificate::query()
            ->where('training_course_id', $course->id)
            ->where('trainee_id', $trainee->id)
            ->where('status', 'approved')
            ->delete();

        $this->patchJson("/api/training-courses/{$course->id}/trainees/{$trainee->id}", [
            'attendance_status' => 'attended',
            'result' => 'passed',
            'attended_hours' => min(10, (int) ($course->planned_hours ?: 10)),
            'score' => 85,
        ])->assertOk();
    }

    public function test_center_user_can_complete_training_course(): void
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $template = TrainingCourse::query()->firstOrFail();
        if ($user->training_center_id !== $template->training_center_id) {
            $user->forceFill(['training_center_id' => $template->training_center_id])->save();
        }

        $create = $this->postJson('/api/training-courses', [
            'training_center_id' => $template->training_center_id,
            'trainer_id' => $template->trainer_id,
            'training_kit_id' => $template->training_kit_id,
            'title' => 'Course To Complete',
            'delivery_mode' => 'offline',
            'planned_hours' => 8,
            'capacity' => 10,
            'status' => 'ongoing',
        ])->assertCreated();

        $courseId = $create->json('data.id');
        $traineeId = \App\Models\Trainee::query()->value('id');

        $this->postJson("/api/training-courses/{$courseId}/trainees", [
            'trainee_id' => $traineeId,
        ])->assertCreated();

        $this->postJson("/api/training-courses/{$courseId}/complete", [])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_trainee_cannot_create_training_course(): void
    {
        $user = User::query()->where('email', 'trainee@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        $template = TrainingCourse::query()->firstOrFail();

        $this->postJson('/api/training-courses', [
            'training_center_id' => $template->training_center_id,
            'trainer_id' => $template->trainer_id,
            'training_kit_id' => $template->training_kit_id,
            'title' => 'Blocked Course',
            'delivery_mode' => 'offline',
            'planned_hours' => 8,
            'capacity' => 10,
        ])->assertForbidden();
    }
}
