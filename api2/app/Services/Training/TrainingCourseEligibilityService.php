<?php

namespace App\Services\Training;

use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingKit;
use App\Models\User;
use App\Support\TrainingDataScope;

class TrainingCourseEligibilityService
{
    public function validateCenterForCourse(int $centerId, string $deliveryMode): ?string
    {
        $center = TrainingCenter::query()->find($centerId);

        if (!$center || !$center->is_active) {
            return 'المركز التدريبي غير موجود أو غير نشط.';
        }

        if (!$center->isEligibleCenter($deliveryMode)) {
            return 'المركز التدريبي غير مؤهل لإقامة دورة بهذا النمط (' . $deliveryMode . ').';
        }

        return null;
    }

    public function validateTrainerForCourse(int $trainerId, int $centerId, int $kitId): ?string
    {
        $trainer = Trainer::query()->with('kits')->find($trainerId);

        if (!$trainer || $trainer->status !== 'active') {
            return 'المدرب غير موجود أو غير نشط.';
        }

        if ((int) $trainer->training_center_id !== (int) $centerId) {
            return 'المدرب لا ينتمي إلى المركز التدريبي المحدد.';
        }

        if (!$trainer->isEligibleTrainer()) {
            return 'المدرب غير مؤهل للتدريب حسب شروط النظام.';
        }

        if (!$trainer->isAuthorizedForKit($kitId)) {
            return 'المدرب غير مخول لتدريب هذه الحقيبة التدريبية.';
        }

        return null;
    }

    public function validateKitForCourse(int $kitId): ?string
    {
        $kit = TrainingKit::query()->find($kitId);

        if (!$kit || !$kit->is_active || $kit->status !== 'active') {
            return 'الحقيبة التدريبية غير موجودة أو غير فعالة.';
        }

        return null;
    }

    public function assertCenterUserOwnsCenter(?User $user, int $centerId): ?string
    {
        if ($user?->isCenterUser() && (int) $centerId !== (int) $user->training_center_id) {
            return 'لا يمكنك إنشاء دورة لمركز تدريبي آخر.';
        }

        return null;
    }
}
