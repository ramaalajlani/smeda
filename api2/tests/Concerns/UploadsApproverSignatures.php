<?php

namespace Tests\Concerns;

use App\Models\Certificate;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\TrainingKit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

trait UploadsApproverSignatures
{
    protected function uploadSignatureForUser(User $user, string $filename = 'signature.png'): void
    {
        Sanctum::actingAs($user);

        $this->post('/api/my-electronic-signature', [
            'signature' => UploadedFile::fake()->image($filename, 300, 100),
        ])->assertCreated();
    }

    protected function uploadSignaturesForApprovers(): void
    {
        foreach ([
            'center@system.com',
            'manager@system.com',
            'deputy@system.com',
            'general@system.com',
        ] as $email) {
            $user = User::query()->where('email', $email)->firstOrFail();
            $this->uploadSignatureForUser($user, str_replace(['@', '.'], '-', $email) . '.png');
        }
    }

    protected function issueCertificateViaHttp(): Certificate
    {
        $user = User::query()->where('email', 'center@system.com')->firstOrFail();
        Sanctum::actingAs($user);

        [$course, $trainee] = $this->prepareCompletedCourse($user);

        Certificate::query()
            ->where('training_course_id', $course->id)
            ->where('trainee_id', $trainee->id)
            ->delete();

        $course->trainees()->updateExistingPivot($trainee->id, [
            'attendance_status' => 'attended',
            'attended_hours' => 8,
            'result' => 'passed',
            'score' => 90,
        ]);

        $this->postJson('/api/certificates/issue', [
            'training_course_id' => $course->id,
            'trainee_id' => $trainee->id,
            'certificate_type' => 'attendance',
            'hours_awarded' => 8,
        ])->assertCreated();

        return Certificate::query()
            ->where('training_course_id', $course->id)
            ->where('trainee_id', $trainee->id)
            ->firstOrFail();
    }

    /** @return array{0: TrainingCourse, 1: \App\Models\Trainee} */
    protected function prepareCompletedCourse(User $user): array
    {
        $course = TrainingCourse::query()->firstOrFail();
        $course->update(['status' => 'completed', 'actual_hours' => $course->planned_hours ?: 10]);
        $course = $this->ensureCourseEligibleForCertificates($course, $user);

        return [$course, $course->trainees()->firstOrFail()];
    }

    protected function ensureCourseEligibleForCertificates(TrainingCourse $course, User $user): TrainingCourse
    {
        $course->load(['trainingCenter', 'trainer', 'trainingKit']);

        $center = TrainingCenter::query()->find($user->training_center_id)
            ?? $course->trainingCenter
            ?? TrainingCenter::query()->firstOrFail();

        $center->update([
            'accreditation_status' => 'approved',
            'supports_offline_training' => true,
            'is_active' => true,
            'accreditation_start_date' => now()->subMonths(6)->toDateString(),
            'accreditation_end_date' => now()->addYear()->toDateString(),
        ]);

        $trainer = $course->trainer;
        if (!$trainer || (int) $trainer->training_center_id !== (int) $center->id) {
            $trainer = Trainer::query()
                ->where('training_center_id', $center->id)
                ->first()
                ?? Trainer::query()->firstOrFail();

            $trainer->update(['training_center_id' => $center->id]);
        }

        $trainer->update([
            'can_train' => true,
            'has_tot' => true,
            'status' => 'active',
            'tot_issue_date' => now()->subMonths(6)->toDateString(),
            'tot_expiry_date' => now()->addYear()->toDateString(),
            'accreditation_start_date' => now()->subMonths(3)->toDateString(),
            'accreditation_end_date' => now()->addYear()->toDateString(),
        ]);

        $kitId = $course->training_kit_id ?? TrainingKit::query()->value('id');
        TrainingKit::query()->whereKey($kitId)->update([
            'is_active' => true,
            'status' => 'active',
        ]);

        $trainer->kits()->syncWithoutDetaching([
            $kitId => [
                'is_authorized' => true,
                'authorized_from' => now()->subMonths(2)->toDateString(),
                'authorized_to' => now()->addYear()->toDateString(),
                'notes' => 'test authorization',
            ],
        ]);

        $course->update([
            'training_center_id' => $center->id,
            'trainer_id' => $trainer->id,
            'training_kit_id' => $kitId,
            'delivery_mode' => 'offline',
            'approved_platform' => null,
            'status' => 'completed',
            'actual_hours' => $course->planned_hours ?: 10,
        ]);

        return $course->fresh(['trainingCenter', 'trainer', 'trainingKit']);
    }
}
