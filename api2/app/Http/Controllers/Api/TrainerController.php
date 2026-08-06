<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainerResource;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Services\Training\EntityCodeGenerator;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TrainerController extends Controller
{
    public function __construct(
        private readonly EntityCodeGenerator $codeGenerator,
    ) {
    }

    /**
     * Display a paginated listing of trainers.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Trainer::class);

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $query = Trainer::query()
            ->select([
                'id',
                'training_center_id',
                'name',
                'trainer_code',
                'phone',
                'email',
                'specialization',
                'classification',
                'has_tot',
                'tot_certificate_number',
                'tot_certificate_source',
                'tot_issue_date',
                'tot_expiry_date',
                'can_train',
                'can_evaluate',
                'status',
                'accreditation_start_date',
                'accreditation_end_date',
                'created_at',
                'updated_at',
            ])
            ->with([
                'trainingCenter:id,name,code,city,classification,accreditation_status',
                'profile:id,trainer_id,headline,bio,experience_years,skills,special_interests,linkedin_summary,cv_file,profile_image,visibility',
            ])
            ->withCount(['kits', 'courses'])
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('training_center_id'), function (Builder $query) use ($request) {
                $query->forCenter($request->integer('training_center_id'));
            })
            ->when($request->filled('has_tot'), function (Builder $query) use ($request) {
                $query->where('has_tot', $this->toBoolean($request->input('has_tot')));
            })
            ->when($request->filled('can_train'), function (Builder $query) use ($request) {
                $query->where('can_train', $this->toBoolean($request->input('can_train')));
            })
            ->when($request->filled('can_evaluate'), function (Builder $query) use ($request) {
                $query->where('can_evaluate', $this->toBoolean($request->input('can_evaluate')));
            })
            ->search($request->input('search'))
            ->orderByDesc('id');

        $trainers = TrainingDataScope::scopeTrainers($query, $request->user())
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TrainerResource::collection($trainers)->additional([
            'meta' => [
                'filters' => [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'training_center_id' => $request->input('training_center_id'),
                    'has_tot' => $request->input('has_tot'),
                    'can_train' => $request->input('can_train'),
                    'can_evaluate' => $request->input('can_evaluate'),
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created trainer for the center.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Trainer::class);

        $user = $request->user();
        $validated = $this->validateTrainerPayload($request, null);

        if ($user?->isCenterUser() && $user->training_center_id) {
            $validated['training_center_id'] = (int) $user->training_center_id;
        }

        $this->assertCenterAccessible($user, (int) $validated['training_center_id']);

        $trainer = DB::transaction(function () use ($validated) {
            $kitIds = $validated['kit_ids'] ?? [];
            unset($validated['kit_ids']);

            $validated['trainer_code'] = $validated['trainer_code'] ?? $this->codeGenerator->nextTrainerCode();
            $validated['can_train'] = array_key_exists('can_train', $validated) ? (bool) $validated['can_train'] : true;
            $validated['can_evaluate'] = array_key_exists('can_evaluate', $validated) ? (bool) $validated['can_evaluate'] : false;
            $validated['status'] = $validated['status'] ?? 'active';
            $validated['has_tot'] = (bool) ($validated['has_tot'] ?? false);

            $trainer = Trainer::create($validated);

            if (!empty($kitIds)) {
                $sync = [];
                foreach ($kitIds as $kitId) {
                    $sync[(int) $kitId] = [
                        'is_authorized' => true,
                        'authorized_from' => now()->toDateString(),
                        'authorized_to' => null,
                        'notes' => 'Authorized via center trainer management',
                    ];
                }
                $trainer->kits()->sync($sync);
            }

            return $trainer;
        });

        $trainer->load([
            'trainingCenter:id,name,code,city,classification,accreditation_status',
            'kits:id,name,code,sector,category,level,hours,status',
        ])->loadCount(['kits', 'courses']);

        return response()->json([
            'message' => 'تم إضافة المدرب بنجاح.',
            'data' => new TrainerResource($trainer),
        ], 201);
    }

    /**
     * Display the specified trainer.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $trainer = TrainingDataScope::scopeTrainers(Trainer::query(), $request->user())
            ->select([
                'id',
                'training_center_id',
                'name',
                'trainer_code',
                'phone',
                'email',
                'specialization',
                'classification',
                'has_tot',
                'tot_certificate_number',
                'tot_certificate_source',
                'tot_issue_date',
                'tot_expiry_date',
                'can_train',
                'can_evaluate',
                'status',
                'accreditation_start_date',
                'accreditation_end_date',
                'bio',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->with([
                'trainingCenter:id,name,code,city,address,phone,email,classification,accreditation_status',
                'profile:id,trainer_id,headline,bio,experience_years,skills,special_interests,linkedin_summary,cv_file,profile_image,visibility',
                'kits:id,name,code,sector,category,level,hours,status',
            ])
            ->withCount(['kits', 'courses'])
            ->findOrFail($id);

        $this->authorize('view', $trainer);

        return response()->json([
            'data' => new TrainerResource($trainer),
        ]);
    }

    /**
     * Update the specified trainer.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $trainer = TrainingDataScope::scopeTrainers(Trainer::query(), $request->user())->findOrFail($id);
        $this->authorize('update', $trainer);

        $user = $request->user();
        $validated = $this->validateTrainerPayload($request, $trainer);

        if ($user?->isCenterUser() && $user->training_center_id) {
            $validated['training_center_id'] = (int) $user->training_center_id;
        }

        if (isset($validated['training_center_id'])) {
            $this->assertCenterAccessible($user, (int) $validated['training_center_id']);
        }

        DB::transaction(function () use ($trainer, $validated) {
            $kitIds = $validated['kit_ids'] ?? null;
            unset($validated['kit_ids']);

            $trainer->update($validated);

            if (is_array($kitIds)) {
                $sync = [];
                foreach ($kitIds as $kitId) {
                    $sync[(int) $kitId] = [
                        'is_authorized' => true,
                        'authorized_from' => now()->toDateString(),
                        'authorized_to' => null,
                        'notes' => 'Authorized via center trainer management',
                    ];
                }
                $trainer->kits()->sync($sync);
            }
        });

        $trainer->refresh()->load([
            'trainingCenter:id,name,code,city,classification,accreditation_status',
            'kits:id,name,code,sector,category,level,hours,status',
        ])->loadCount(['kits', 'courses']);

        return response()->json([
            'message' => 'تم تحديث بيانات المدرب بنجاح.',
            'data' => new TrainerResource($trainer),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTrainerPayload(Request $request, ?Trainer $trainer): array
    {
        $isUpdate = $trainer !== null;

        return $request->validate([
            'training_center_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:training_centers,id'],
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'trainer_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('trainers', 'trainer_code')->ignore($trainer?->id)->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'classification' => ['nullable', 'string', 'max:100'],
            'has_tot' => ['nullable', 'boolean'],
            'tot_certificate_number' => ['nullable', 'string', 'max:100'],
            'tot_certificate_source' => ['nullable', 'string', 'max:255'],
            'tot_issue_date' => ['nullable', 'date'],
            'tot_expiry_date' => ['nullable', 'date', 'after_or_equal:tot_issue_date'],
            'can_train' => ['nullable', 'boolean'],
            'can_evaluate' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,inactive,suspended,pending'],
            'accreditation_start_date' => ['nullable', 'date'],
            'accreditation_end_date' => ['nullable', 'date', 'after_or_equal:accreditation_start_date'],
            'bio' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'kit_ids' => ['nullable', 'array'],
            'kit_ids.*' => ['integer', 'exists:training_kits,id'],
        ], [
            'name.required' => 'اسم المدرب مطلوب.',
            'training_center_id.required' => 'المركز التدريبي مطلوب.',
        ]);
    }

    private function assertCenterAccessible(?\App\Models\User $user, int $centerId): void
    {
        $exists = TrainingDataScope::scopeTrainingCenters(
            TrainingCenter::query()->whereKey($centerId),
            $user
        )->exists();

        abort_unless($exists, 403, 'لا يمكنك إدارة مدربين خارج نطاق مركزك.');
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
