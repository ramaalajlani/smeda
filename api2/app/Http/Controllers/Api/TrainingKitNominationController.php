<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingKitNominationResource;
use App\Models\Trainer;
use App\Models\TrainerRegistrationRequest;
use App\Models\TrainingKitNomination;
use App\Models\User;
use App\Support\RegistrationApprovalLinker;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingKitNominationController extends Controller
{
    /**
     * Display a listing of kit nominations.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (
            !$user ||
            !method_exists($user, 'hasPermissionTo')
        ) {
            return response()->json([
                'message' => 'غير مصرح لك بعرض ترشيحات الحقائب.',
            ], 403);
        }

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $query = TrainingKitNomination::query()
            ->select([
                'id',
                'trainer_id',
                'training_kit_id',
                'proposed_name',
                'description',
                'sector',
                'category',
                'hours',
                'status',
                'decision_notes',
                'decided_at',
                'created_at',
                'updated_at',
            ])
            ->with([
                'trainer:id,name,trainer_code,specialization,classification,status,has_tot,tot_expiry_date,can_train,accreditation_end_date',
                'trainingKit:id,name,code,sector,category,level,hours,status',
            ]);

        // إذا كان مدربًا فقط (بدون مراجعة)، يجب أن يكون مرتبطًا بمدرب
        if ($user->hasPermissionTo('nominate_training_kits') && !$user->hasPermissionTo('review_training_kit_nominations')) {
            $resolvedTrainer = $this->resolveTrainerForUser($user);
            if (!$resolvedTrainer) {
                return response()->json([
                    'message' => 'لا يوجد حساب مدرب مرتبط بالمستخدم الحالي. تم تسجيل حسابك، لكن يلزم اعتماد ملف المدرب أو ربطه من الإدارة.',
                ], 403);
            }
        }

        // إذا عنده صلاحية مراجعة يرى الجميع
        if (
            !$user->hasPermissionTo('nominate_training_kits') &&
            !$user->hasPermissionTo('review_training_kit_nominations')
        ) {
            return response()->json([
                'message' => 'ليس لديك صلاحية للوصول إلى ترشيحات الحقائب.',
            ], 403);
        }

        $query = TrainingDataScope::scopeKitNominations($query, $user);

        $nominations = $query
            ->when($request->filled('status'), function (Builder $q) use ($request) {
                $q->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('trainer_id'), function (Builder $q) use ($request, $user) {
                if ($user && method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('review_training_kit_nominations')) {
                    $q->where('trainer_id', $request->integer('trainer_id'));
                }
            })
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $value = trim((string) $request->input('search'));

                if ($value !== '') {
                    $q->where(function (Builder $nested) use ($value) {
                        $nested->where('proposed_name', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%")
                            ->orWhere('sector', 'like', "%{$value}%")
                            ->orWhere('category', 'like', "%{$value}%")
                            ->orWhereHas('trainer', function (Builder $trainerQuery) use ($value) {
                                $trainerQuery->where('name', 'like', "%{$value}%")
                                    ->orWhere('trainer_code', 'like', "%{$value}%");
                            })
                            ->orWhereHas('trainingKit', function (Builder $kitQuery) use ($value) {
                                $kitQuery->where('name', 'like', "%{$value}%")
                                    ->orWhere('code', 'like', "%{$value}%");
                            });
                    });
                }
            })
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TrainingKitNominationResource::collection($nominations)->additional([
            'meta' => [
                'filters' => [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'trainer_id' => $request->input('trainer_id'),
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created nomination.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (
            !$user ||
            !method_exists($user, 'hasPermissionTo') ||
            !$user->hasPermissionTo('nominate_training_kits')
        ) {
            return response()->json([
                'message' => 'ليس لديك صلاحية ترشيح حقيبة تدريبية.',
            ], 403);
        }

        $resolvedTrainer = $this->resolveTrainerForUser($user);
        if (!$resolvedTrainer) {
            return response()->json([
                'message' => 'لا يوجد حساب مدرب مرتبط بالمستخدم الحالي. تم تسجيل حسابك، لكن يلزم اعتماد ملف المدرب أو ربطه من الإدارة.',
            ], 403);
        }

        $trainer = $resolvedTrainer->load([
            'trainingCenter.platforms',
            'profile',
        ]);

        // Nominations should be allowed for approved active trainers.
        // Do not block by advanced accreditation factors (e.g. ToT expiry)
        // which are handled in course/certificate workflows.
        if ($trainer->status !== 'active' || !(bool) $trainer->can_train) {
            return response()->json([
                'message' => 'المدرب غير مؤهل حاليًا لترشيح حقيبة تدريبية.',
            ], 403);
        }

        $validated = $request->validate([
            'training_kit_id' => ['nullable', 'integer', 'exists:training_kits,id'],
            'proposed_name' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'sector' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'hours' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        if (
            empty($validated['training_kit_id']) &&
            empty($validated['proposed_name'])
        ) {
            return response()->json([
                'message' => 'يجب تحديد حقيبة موجودة أو كتابة اسم حقيبة مقترحة.',
            ], 422);
        }

        $nomination = TrainingKitNomination::create([
            'trainer_id' => $trainer->id,
            'training_kit_id' => $validated['training_kit_id'] ?? null,
            'proposed_name' => $validated['proposed_name'] ?? null,
            'description' => $validated['description'],
            'sector' => $validated['sector'],
            'category' => $validated['category'],
            'hours' => $validated['hours'],
            'status' => 'pending',
            'decision_notes' => null,
            'decided_at' => null,
        ]);

        $nomination->load([
            'trainer:id,name,trainer_code,specialization,classification,status,has_tot,tot_expiry_date,can_train,accreditation_end_date',
            'trainingKit:id,name,code,sector,category,level,hours,status',
        ]);

        return response()->json([
            'message' => 'تم إرسال ترشيح الحقيبة بنجاح.',
            'data' => new TrainingKitNominationResource($nomination),
        ], 201);
    }

    /**
     * Display the specified nomination.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user();

        if (
            !$user ||
            !method_exists($user, 'hasPermissionTo')
        ) {
            return response()->json([
                'message' => 'غير مصرح لك بعرض هذا الترشيح.',
            ], 403);
        }

        $nomination = TrainingDataScope::scopeKitNominations(TrainingKitNomination::query(), $user)
            ->with([
                'trainer:id,name,trainer_code,specialization,classification,status,has_tot,tot_expiry_date,can_train,accreditation_end_date',
                'trainingKit:id,name,code,sector,category,level,hours,status',
            ])
            ->findOrFail($id);

        $canReview = $user->hasPermissionTo('review_training_kit_nominations');
        $isOwner = $user->trainer_id && (int) $user->trainer_id === (int) $nomination->trainer_id;

        if (!$canReview && !$isOwner) {
            return response()->json([
                'message' => 'ليس لديك صلاحية الوصول إلى هذا الترشيح.',
            ], 403);
        }

        return response()->json([
            'data' => new TrainingKitNominationResource($nomination),
        ]);
    }

    /**
     * Review nomination (approve / reject / under review).
     */
    public function review(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (
            !$user ||
            !method_exists($user, 'hasPermissionTo') ||
            !$user->hasPermissionTo('review_training_kit_nominations')
        ) {
            return response()->json([
                'message' => 'ليس لديك صلاحية مراجعة ترشيحات الحقائب.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:under_review,approved,rejected'],
            'decision_notes' => ['nullable', 'string'],
        ]);

        $nomination = TrainingDataScope::scopeKitNominations(TrainingKitNomination::query(), $user)
            ->findOrFail($id);

        $nomination->update([
            'status' => $validated['status'],
            'decision_notes' => $validated['decision_notes'] ?? null,
            'decided_at' => now(),
        ]);

        $nomination->load([
            'trainer:id,name,trainer_code,specialization,classification,status,has_tot,tot_expiry_date,can_train,accreditation_end_date',
            'trainingKit:id,name,code,sector,category,level,hours,status',
        ]);

        return response()->json([
            'message' => 'تم تحديث حالة ترشيح الحقيبة بنجاح.',
            'data' => new TrainingKitNominationResource($nomination),
        ]);
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