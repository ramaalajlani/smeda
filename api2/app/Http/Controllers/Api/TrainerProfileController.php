<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainerProfileResource;
use App\Models\Trainer;
use App\Models\TrainerProfile;
use App\Models\TrainerRegistrationRequest;
use App\Models\User;
use App\Support\RegistrationApprovalLinker;
use App\Support\TrainingDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainerProfileController extends Controller
{
    /**
     * عرض الملف المهني لمدرب محدد
     */
    public function show(Request $request, int $trainerId): JsonResponse
    {
        $user = $request->user();

        $trainer = TrainingDataScope::scopeTrainers(
            Trainer::query()->with([
                'trainingCenter:id,name,code,city',
                'profile',
            ]),
            $user
        )->findOrFail($trainerId);

        $profile = $this->resolveProfileForAuthorization($trainer);
        $this->authorize('view', $profile);

        if (!$trainer->profile) {
            return response()->json([
                'data' => TrainerProfileResource::emptyForTrainer($trainer),
            ]);
        }

        $trainer->profile->load([
            'trainer.trainingCenter:id,name,code,city',
        ]);

        return response()->json([
            'data' => new TrainerProfileResource($trainer->profile),
        ]);
    }

    /**
     * عرض الملف المهني الخاص بالمستخدم الحالي
     */
    public function myProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (
            !$user ||
            !method_exists($user, 'hasPermissionTo') ||
            (
                !$user->hasPermissionTo('view_trainer_profiles') &&
                !$user->hasPermissionTo('edit_own_trainer_profile')
            )
        ) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض الملف المهني.',
            ], 403);
        }

        $resolvedTrainer = $this->resolveTrainerForUser($user);
        if (!$resolvedTrainer) {
            return response()->json([
                'message' => 'لا يوجد حساب مدرب مرتبط بالمستخدم الحالي. تم إنشاء الحساب لكن يلزم اعتماد وربط ملف المدرب.',
            ], 403);
        }

        $trainer = $resolvedTrainer->load([
            'trainingCenter:id,name,code,city',
            'profile',
        ]);

        $profile = $this->resolveProfileForAuthorization($trainer);
        $this->authorize('view', $profile);

        if (!$trainer->profile) {
            return response()->json([
                'data' => TrainerProfileResource::emptyForTrainer($trainer),
            ]);
        }

        $trainer->profile->load([
            'trainer.trainingCenter:id,name,code,city',
        ]);

        return response()->json([
            'data' => new TrainerProfileResource($trainer->profile),
        ]);
    }

    /**
     * تعديل الملف المهني الخاص بالمستخدم الحالي
     */
    public function updateMyProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('updateOwn', TrainerProfile::class);

        $resolvedTrainer = $this->resolveTrainerForUser($user);
        if (!$resolvedTrainer) {
            return response()->json([
                'message' => 'لا يوجد حساب مدرب مرتبط بالمستخدم الحالي. تم إنشاء الحساب لكن يلزم اعتماد وربط ملف المدرب.',
            ], 403);
        }

        $validated = $request->validate([
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'skills' => ['nullable', 'string'],
            'special_interests' => ['nullable', 'string'],
            'linkedin_summary' => ['nullable', 'string'],
            'visibility' => ['nullable', 'in:internal,public'],
        ]);

        $trainer = $resolvedTrainer->load([
            'trainingCenter:id,name,code,city',
            'profile',
        ]);

        $profile = TrainerProfile::query()->firstOrCreate(
            ['trainer_id' => $trainer->id],
            [
                'headline' => null,
                'bio' => null,
                'experience_years' => 0,
                'skills' => null,
                'special_interests' => null,
                'linkedin_summary' => null,
                'cv_file' => null,
                'profile_image' => null,
                'visibility' => 'internal',
            ]
        );

        $profile->update([
            'headline' => $validated['headline'] ?? $profile->headline,
            'bio' => $validated['bio'] ?? $profile->bio,
            'experience_years' => $validated['experience_years'] ?? $profile->experience_years,
            'skills' => $validated['skills'] ?? $profile->skills,
            'special_interests' => $validated['special_interests'] ?? $profile->special_interests,
            'linkedin_summary' => $validated['linkedin_summary'] ?? $profile->linkedin_summary,
            'visibility' => $validated['visibility'] ?? $profile->visibility,
        ]);

        $profile->load([
            'trainer.trainingCenter:id,name,code,city',
        ]);

        return response()->json([
            'message' => 'تم تحديث الملف المهني بنجاح.',
            'data' => new TrainerProfileResource($profile),
        ]);
    }

    private function resolveProfileForAuthorization(Trainer $trainer): TrainerProfile
    {
        if ($trainer->profile) {
            return $trainer->profile;
        }

        $profile = new TrainerProfile([
            'trainer_id' => $trainer->id,
            'visibility' => 'internal',
        ]);
        $profile->setRelation('trainer', $trainer);

        return $profile;
    }

    private function resolveTrainerForUser(User $user): ?Trainer
    {
        if ($user->trainer_id) {
            $existing = Trainer::query()->find($user->trainer_id);
            if ($existing) {
                return $existing;
            }
        }

        $approvedRequest = TrainerRegistrationRequest::query()
            ->where('submitted_by_user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('approved_trainer_id')
            ->orderByDesc('id')
            ->first();

        if ($approvedRequest?->approved_trainer_id) {
            $trainer = Trainer::query()->find($approvedRequest->approved_trainer_id);
            if ($trainer) {
                RegistrationApprovalLinker::linkUserToTrainer($user, $trainer);
                return $trainer;
            }
        }

        if (!blank($user->email)) {
            $trainerByEmail = Trainer::query()
                ->where('email', $user->email)
                ->orderByDesc('id')
                ->first();

            if ($trainerByEmail) {
                RegistrationApprovalLinker::linkUserToTrainer($user, $trainerByEmail);
                return $trainerByEmail;
            }
        }

        return null;
    }
}
