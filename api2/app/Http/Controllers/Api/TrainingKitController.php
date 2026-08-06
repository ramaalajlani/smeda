<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingKitResource;
use App\Models\TrainingKit;
use App\Services\Training\EntityCodeGenerator;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TrainingKitController extends Controller
{
    public function __construct(
        private readonly EntityCodeGenerator $codeGenerator,
    ) {
    }

    /**
     * Display a paginated listing of training kits.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TrainingKit::class);

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));
        $withTrainers = $request->boolean('with_trainers', false);
        $withCenters = $request->boolean('with_centers', false);
        $withPrograms = $request->boolean('with_programs', false);

        $kits = TrainingDataScope::scopeTrainingKits(TrainingKit::query(), $request->user())
            ->select([
                'id',
                'name',
                'code',
                'sector',
                'category',
                'type',
                'material_code',
                'level',
                'hours',
                'objective',
                'description',
                'status',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->when($request->boolean('with_counts', true), function (Builder $query) {
                $query->withCount([
                    'trainers',
                    'centers',
                    'programs',
                    'courses',
                    'certificates',
                ]);
            })
            ->when($withTrainers, function (Builder $query) {
                $query->with([
                    'trainers:id,name,trainer_code,specialization,status,training_center_id',
                ]);
            })
            ->when($withCenters, function (Builder $query) {
                $query->with([
                    'centers:id,name,code,city,classification,accreditation_status',
                ]);
            })
            ->when($withPrograms, function (Builder $query) {
                $query->with([
                    'programs:id,name,code,status',
                ]);
            })
            ->when($request->filled('training_center_id'), function (Builder $query) use ($request) {
                $centerId = (int) $request->integer('training_center_id');
                $query->whereHas('centers', fn (Builder $c) => $c->whereKey($centerId));
            })
            ->when($request->filled('trainer_id'), function (Builder $query) use ($request) {
                $trainerId = (int) $request->integer('trainer_id');
                $query->whereHas('trainers', fn (Builder $t) => $t->whereKey($trainerId));
            })
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('sector'), function (Builder $query) use ($request) {
                $query->where('sector', $request->string('sector')->toString());
            })
            ->when($request->filled('category'), function (Builder $query) use ($request) {
                $query->where('category', $request->string('category')->toString());
            })
            ->when($request->filled('type'), function (Builder $query) use ($request) {
                $query->where('type', $request->string('type')->toString());
            })
            ->when($request->filled('level'), function (Builder $query) use ($request) {
                $query->where('level', $request->string('level')->toString());
            })
            ->when($request->filled('is_active'), function (Builder $query) use ($request) {
                $query->where('is_active', $this->toBoolean($request->input('is_active')));
            })
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TrainingKitResource::collection($kits)->additional([
            'meta' => [
                'filters' => [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'sector' => $request->input('sector'),
                    'category' => $request->input('category'),
                    'type' => $request->input('type'),
                    'level' => $request->input('level'),
                    'is_active' => $request->input('is_active'),
                    'training_center_id' => $request->input('training_center_id'),
                    'trainer_id' => $request->input('trainer_id'),
                    'with_trainers' => $withTrainers,
                    'with_centers' => $withCenters,
                    'with_programs' => $withPrograms,
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created training kit.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', TrainingKit::class);

        $user = $request->user();
        $validated = $this->validateKitPayload($request, null);
        $centerIds = $validated['center_ids'] ?? [];
        $trainerIds = $validated['trainer_ids'] ?? [];
        unset($validated['center_ids'], $validated['trainer_ids']);

        $validated['code'] = $validated['code'] ?? $this->codeGenerator->nextKitCode();
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['is_active'] = array_key_exists('is_active', $validated)
            ? (bool) $validated['is_active']
            : true;

        if ($user?->isCenterUser() && $user->training_center_id) {
            $centerIds = [(int) $user->training_center_id];
        }

        $kit = DB::transaction(function () use ($validated, $centerIds, $trainerIds) {
            $kit = TrainingKit::create($validated);
            $this->syncCenters($kit, $centerIds);
            $this->syncTrainers($kit, $trainerIds);

            return $kit;
        });

        $kit->load([
            'trainers:id,name,trainer_code,specialization,status,training_center_id',
            'centers:id,name,code,city',
        ])->loadCount(['trainers', 'centers', 'programs', 'courses', 'certificates']);

        return response()->json([
            'message' => 'تم إضافة الحقيبة التدريبية بنجاح.',
            'data' => new TrainingKitResource($kit),
        ], 201);
    }

    /**
     * Display the specified training kit.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $kit = TrainingDataScope::scopeTrainingKits(TrainingKit::query(), $request->user())
            ->select([
                'id',
                'name',
                'code',
                'sector',
                'category',
                'type',
                'material_code',
                'level',
                'hours',
                'objective',
                'description',
                'status',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->with([
                'trainers:id,name,trainer_code,specialization,status,training_center_id',
                'centers:id,name,code,city,classification,accreditation_status',
                'programs:id,name,code,status',
            ])
            ->withCount([
                'trainers',
                'centers',
                'programs',
                'courses',
                'certificates',
            ])
            ->findOrFail($id);

        $this->authorize('view', $kit);

        return response()->json([
            'data' => new TrainingKitResource($kit),
        ]);
    }

    /**
     * Update the specified training kit.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $kit = TrainingDataScope::scopeTrainingKits(TrainingKit::query(), $request->user())
            ->findOrFail($id);
        $this->authorize('update', $kit);

        $user = $request->user();
        $validated = $this->validateKitPayload($request, $kit);
        $centerIds = array_key_exists('center_ids', $validated) ? ($validated['center_ids'] ?? []) : null;
        $trainerIds = array_key_exists('trainer_ids', $validated) ? ($validated['trainer_ids'] ?? []) : null;
        unset($validated['center_ids'], $validated['trainer_ids']);

        if (array_key_exists('is_active', $validated)) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }

        // Center users cannot reassign kits away from their own center.
        if ($user?->isCenterUser() && $user->training_center_id) {
            $centerIds = null;
        }

        DB::transaction(function () use ($kit, $validated, $centerIds, $trainerIds) {
            if (!empty($validated)) {
                $kit->update($validated);
            }
            if (is_array($centerIds)) {
                $this->syncCenters($kit, $centerIds);
            }
            if (is_array($trainerIds)) {
                $this->syncTrainers($kit, $trainerIds);
            }
        });

        $kit->refresh()->load([
            'trainers:id,name,trainer_code,specialization,status,training_center_id',
            'centers:id,name,code,city',
        ])->loadCount(['trainers', 'centers', 'programs', 'courses', 'certificates']);

        return response()->json([
            'message' => 'تم تحديث الحقيبة التدريبية بنجاح.',
            'data' => new TrainingKitResource($kit),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateKitPayload(Request $request, ?TrainingKit $kit): array
    {
        $isUpdate = $kit !== null;

        return $request->validate([
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('training_kits', 'code')->ignore($kit?->id)->whereNull('deleted_at'),
            ],
            'sector' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'material_code' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'max:100'],
            'hours' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'objective' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive,archived'],
            'is_active' => ['nullable', 'boolean'],
            'center_ids' => ['nullable', 'array'],
            'center_ids.*' => ['integer', 'exists:training_centers,id'],
            'trainer_ids' => ['nullable', 'array'],
            'trainer_ids.*' => ['integer', 'exists:trainers,id'],
        ], [
            'name.required' => 'اسم الحقيبة مطلوب.',
        ]);
    }

    /**
     * @param  array<int, int|string>  $centerIds
     */
    private function syncCenters(TrainingKit $kit, array $centerIds): void
    {
        $sync = [];
        foreach ($centerIds as $centerId) {
            $sync[(int) $centerId] = [
                'is_assigned' => true,
                'assigned_from' => now()->toDateString(),
                'assigned_to' => null,
                'notes' => 'Assigned via kit management',
            ];
        }
        $kit->centers()->sync($sync);
    }

    /**
     * @param  array<int, int|string>  $trainerIds
     */
    private function syncTrainers(TrainingKit $kit, array $trainerIds): void
    {
        $sync = [];
        foreach ($trainerIds as $trainerId) {
            $sync[(int) $trainerId] = [
                'is_authorized' => true,
                'authorized_from' => now()->toDateString(),
                'authorized_to' => null,
                'notes' => 'Authorized via kit management',
            ];
        }
        $kit->trainers()->sync($sync);
    }

    /**
     * Convert request value to boolean.
     */
    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
