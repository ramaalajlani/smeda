<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingKitResource;
use App\Models\TrainingCategory;
use App\Models\TrainingKit;
use App\Services\Training\EntityCodeGenerator;
use App\Services\Training\TrainingKitFileService;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrainingKitController extends Controller
{
    public function __construct(
        private readonly EntityCodeGenerator $codeGenerator,
        private readonly TrainingKitFileService $fileService,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', TrainingKit::class);

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));
        $withTrainers = $request->boolean('with_trainers', false);
        $withCenters = $request->boolean('with_centers', false);
        $withPrograms = $request->boolean('with_programs', false);

        $kits = TrainingDataScope::scopeTrainingKits(TrainingKit::query(), $request->user())
            ->with([
                'trainingCategory:id,name_ar,slug',
                'trainingSubcategory:id,name_ar,slug',
                'creator:id,name',
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
            ->when($request->filled('workflow_status'), function (Builder $query) use ($request) {
                $query->where('workflow_status', $request->string('workflow_status')->toString());
            })
            ->when($request->filled('category_id'), function (Builder $query) use ($request) {
                $query->where('category_id', (int) $request->integer('category_id'));
            })
            ->when($request->filled('subcategory_id'), function (Builder $query) use ($request) {
                $query->where('subcategory_id', (int) $request->integer('subcategory_id'));
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
            ->when($request->boolean('has_training_bag_file'), fn (Builder $q) => $q->whereNotNull('training_bag_file_path'))
            ->when($request->boolean('has_promotional_file'), fn (Builder $q) => $q->whereNotNull('promotional_file_path'))
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TrainingKitResource::collection($kits)->additional([
            'meta' => [
                'filters' => $request->only([
                    'search', 'status', 'workflow_status', 'sector', 'category', 'category_id',
                    'subcategory_id', 'type', 'level', 'is_active', 'training_center_id', 'trainer_id',
                ]) + [
                    'with_trainers' => $withTrainers,
                    'with_centers' => $withCenters,
                    'with_programs' => $withPrograms,
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', TrainingKit::class);

        $user = $request->user();
        $validated = $this->validateKitPayload($request, null);
        $centerIds = $validated['center_ids'] ?? [];
        $trainerIds = $validated['trainer_ids'] ?? [];
        unset($validated['center_ids'], $validated['trainer_ids']);

        $validated = $this->applyWorkflowDefaults($validated, $user?->id);
        $validated['code'] = $validated['code'] ?? $this->codeGenerator->nextKitCode();

        if ($user?->isCenterUser() && $user->training_center_id) {
            $centerIds = [(int) $user->training_center_id];
        }

        $kit = DB::transaction(function () use ($validated, $centerIds, $trainerIds, $request) {
            $kit = TrainingKit::create($validated);
            $this->syncCenters($kit, $centerIds);
            $this->syncTrainers($kit, $trainerIds);
            $this->handleUploadedFiles($request, $kit);

            return $kit;
        });

        $kit->load($this->defaultRelations())->loadCount($this->defaultCounts());

        return response()->json([
            'message' => 'تم إضافة الحقيبة التدريبية بنجاح.',
            'data' => new TrainingKitResource($kit),
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $kit = $this->findScopedKit($id, $request)
            ->load($this->defaultRelations())
            ->loadCount($this->defaultCounts());

        $this->authorize('view', $kit);

        return response()->json([
            'data' => new TrainingKitResource($kit),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $kit = $this->findScopedKit($id, $request);
        $this->authorize('update', $kit);

        $user = $request->user();
        $validated = $this->validateKitPayload($request, $kit);
        $centerIds = array_key_exists('center_ids', $validated) ? ($validated['center_ids'] ?? []) : null;
        $trainerIds = array_key_exists('trainer_ids', $validated) ? ($validated['trainer_ids'] ?? []) : null;
        unset($validated['center_ids'], $validated['trainer_ids']);

        if (array_key_exists('is_active', $validated)) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }

        if (!empty($validated)) {
            $validated = $this->applyWorkflowDefaults($validated, $user?->id, $kit);
        }

        if ($user?->isCenterUser() && $user->training_center_id) {
            $centerIds = null;
        }

        DB::transaction(function () use ($kit, $validated, $centerIds, $trainerIds, $request) {
            if (!empty($validated)) {
                $kit->update($validated);
            }
            if (is_array($centerIds)) {
                $this->syncCenters($kit, $centerIds);
            }
            if (is_array($trainerIds)) {
                $this->syncTrainers($kit, $trainerIds);
            }
            $this->handleUploadedFiles($request, $kit);
        });

        $kit->refresh()->load($this->defaultRelations())->loadCount($this->defaultCounts());

        return response()->json([
            'message' => 'تم تحديث الحقيبة التدريبية بنجاح.',
            'data' => new TrainingKitResource($kit),
        ]);
    }

    public function uploadPromotionalFile(int $id, Request $request): JsonResponse
    {
        $kit = $this->findScopedKit($id, $request);
        $this->authorize('update', $kit);

        $request->validate([
            'promotional_file' => ['required', 'file', 'max:15360'],
        ], [
            'promotional_file.required' => 'الملف الترويجي مطلوب.',
        ]);

        $this->fileService->deletePromotionalFile($kit);
        $meta = $this->fileService->storePromotionalFile($request->file('promotional_file'), $kit);

        $kit->update([
            'promotional_file_path' => $meta['path'],
            'promotional_file_original_name' => $meta['original_name'],
            'promotional_file_mime' => $meta['mime'],
            'promotional_file_size' => $meta['size'],
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'تم رفع الملف الترويجي بنجاح.',
            'data' => new TrainingKitResource($kit->fresh()->load($this->defaultRelations())),
        ]);
    }

    public function uploadTrainingBagFile(int $id, Request $request): JsonResponse
    {
        $kit = $this->findScopedKit($id, $request);
        $this->authorize('update', $kit);

        $request->validate([
            'training_bag_file' => ['required', 'file', 'mimes:pdf', 'max:25600'],
        ], [
            'training_bag_file.required' => 'ملف الحقيبة التدريبية (PDF) مطلوب.',
            'training_bag_file.mimes' => 'ملف الحقيبة يجب أن يكون PDF.',
        ]);

        $this->fileService->deleteTrainingBagFile($kit);
        $meta = $this->fileService->storeTrainingBagFile($request->file('training_bag_file'), $kit);

        $kit->update([
            'training_bag_file_path' => $meta['path'],
            'training_bag_file_original_name' => $meta['original_name'],
            'training_bag_file_mime' => $meta['mime'],
            'training_bag_file_size' => $meta['size'],
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'تم رفع ملف الحقيبة التدريبية (PDF) بنجاح.',
            'data' => new TrainingKitResource($kit->fresh()->load($this->defaultRelations())),
        ]);
    }

    public function downloadPromotionalFile(int $id, Request $request): StreamedResponse|JsonResponse
    {
        $kit = $this->findScopedKit($id, $request);
        $this->authorize('downloadPromotionalFile', $kit);

        if (!$kit->hasPromotionalFile()) {
            return response()->json(['message' => 'لا يوجد ملف ترويجي لهذه الحقيبة.'], 404);
        }

        return $this->streamFile(
            $this->fileService->promotionalDisk(),
            $kit->promotional_file_path,
            $kit->promotional_file_original_name ?: 'promotional.pdf',
            $kit->promotional_file_mime ?: 'application/pdf'
        );
    }

    public function downloadTrainingBagFile(int $id, Request $request): StreamedResponse|JsonResponse
    {
        $kit = $this->findScopedKit($id, $request);
        $this->authorize('downloadTrainingBagFile', $kit);

        if (!$kit->hasTrainingBagFile()) {
            return response()->json(['message' => 'لا يوجد ملف حقيبة تدريبية لهذه الحقيبة.'], 404);
        }

        return $this->streamFile(
            $this->fileService->bagDisk(),
            $kit->training_bag_file_path,
            $kit->training_bag_file_original_name ?: 'training-bag.pdf',
            $kit->training_bag_file_mime ?: 'application/pdf'
        );
    }

    /** @return array<string, mixed> */
    private function validateKitPayload(Request $request, ?TrainingKit $kit): array
    {
        $isUpdate = $kit !== null;

        $validated = $request->validate([
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('training_kits', 'code')->ignore($kit?->id)->whereNull('deleted_at'),
            ],
            'sector' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:training_categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:training_categories,id'],
            'type' => ['nullable', 'string', 'max:100'],
            'material_code' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', Rule::in(array_merge(TrainingKit::LEVELS, ['مبتدئ', 'متوسط', 'متقدم']))],
            'hours' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'suggested_days' => ['nullable', 'integer', 'min:0', 'max:999'],
            'objective' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'prerequisites' => ['nullable', 'string'],
            'target_audience' => ['nullable', 'string'],
            'expected_outcomes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive,archived'],
            'workflow_status' => ['nullable', Rule::in(TrainingKit::WORKFLOW_STATUSES)],
            'is_active' => ['nullable', 'boolean'],
            'center_ids' => ['nullable', 'array'],
            'center_ids.*' => ['integer', 'exists:training_centers,id'],
            'trainer_ids' => ['nullable', 'array'],
            'trainer_ids.*' => ['integer', 'exists:trainers,id'],
            'promotional_file' => ['nullable', 'file', 'max:15360'],
            'training_bag_file' => ['nullable', 'file', 'mimes:pdf', 'max:25600'],
        ], [
            'name.required' => 'اسم الحقيبة مطلوب.',
            'training_bag_file.mimes' => 'ملف الحقيبة يجب أن يكون PDF.',
        ]);

        $this->validateCategoryRelations($validated);

        return $validated;
    }

    /** @param array<string, mixed> $validated */
    private function validateCategoryRelations(array $validated): void
    {
        if (!empty($validated['subcategory_id']) && !empty($validated['category_id'])) {
            $sub = TrainingCategory::find($validated['subcategory_id']);
            if ($sub && (int) $sub->parent_id !== (int) $validated['category_id']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'subcategory_id' => ['التصنيف الفرعي لا ينتمي للتصنيف الرئيسي المحدد.'],
                ]);
            }
        }
    }

    /** @param array<string, mixed> $validated */
    private function applyWorkflowDefaults(array $validated, ?int $userId, ?TrainingKit $existing = null): array
    {
        if ($userId) {
            $validated[$existing ? 'updated_by' : 'created_by'] = $userId;
            if (!$existing) {
                $validated['created_by'] = $userId;
            }
        }

        if (!isset($validated['workflow_status'])) {
            if (!$existing) {
                $validated['workflow_status'] = 'draft';
            }

            return $this->syncLegacyStatusFields($validated, $existing);
        }

        return $this->syncLegacyStatusFields($validated, $existing);
    }

    /** @param array<string, mixed> $validated */
    private function syncLegacyStatusFields(array $validated, ?TrainingKit $existing = null): array
    {
        $workflow = $validated['workflow_status'] ?? $existing?->workflow_status ?? 'draft';

        $map = [
            'published' => ['status' => 'active', 'is_active' => true],
            'inactive' => ['status' => 'inactive', 'is_active' => false],
            'archived' => ['status' => 'archived', 'is_active' => false],
            'draft' => ['status' => 'inactive', 'is_active' => false],
            'under_review' => ['status' => 'inactive', 'is_active' => false],
            'approved' => ['status' => 'inactive', 'is_active' => false],
        ];

        if (isset($map[$workflow])) {
            $validated = array_merge($validated, $map[$workflow]);
        }

        if ($workflow === 'published') {
            $validated['published_at'] = $validated['published_at'] ?? now();
        }

        if (!empty($validated['category_id'])) {
            $cat = TrainingCategory::find($validated['category_id']);
            if ($cat) {
                $validated['category'] = $cat->name_ar;
            }
        }

        if (!empty($validated['subcategory_id'])) {
            $sub = TrainingCategory::find($validated['subcategory_id']);
            if ($sub) {
                $validated['type'] = $sub->name_ar;
            }
        }

        return $validated;
    }

    private function handleUploadedFiles(Request $request, TrainingKit $kit): void
    {
        if ($request->hasFile('promotional_file')) {
            $this->fileService->deletePromotionalFile($kit);
            $meta = $this->fileService->storePromotionalFile($request->file('promotional_file'), $kit);
            $kit->update([
                'promotional_file_path' => $meta['path'],
                'promotional_file_original_name' => $meta['original_name'],
                'promotional_file_mime' => $meta['mime'],
                'promotional_file_size' => $meta['size'],
            ]);
        }

        if ($request->hasFile('training_bag_file')) {
            $this->fileService->deleteTrainingBagFile($kit);
            $meta = $this->fileService->storeTrainingBagFile($request->file('training_bag_file'), $kit);
            $kit->update([
                'training_bag_file_path' => $meta['path'],
                'training_bag_file_original_name' => $meta['original_name'],
                'training_bag_file_mime' => $meta['mime'],
                'training_bag_file_size' => $meta['size'],
            ]);
        }
    }

    private function findScopedKit(int $id, Request $request): TrainingKit
    {
        return TrainingDataScope::scopeTrainingKits(TrainingKit::query(), $request->user())
            ->findOrFail($id);
    }

    /** @return list<string> */
    private function defaultRelations(): array
    {
        return [
            'trainingCategory:id,name_ar,slug',
            'trainingSubcategory:id,name_ar,slug',
            'creator:id,name',
            'trainers:id,name,trainer_code,specialization,status,training_center_id',
            'centers:id,name,code,city,classification,accreditation_status',
            'programs:id,name,code,status',
        ];
    }

    /** @return list<string> */
    private function defaultCounts(): array
    {
        return ['trainers', 'centers', 'programs', 'courses', 'certificates'];
    }

    /** @param array<int, int|string> $centerIds */
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

    /** @param array<int, int|string> $trainerIds */
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

    private function streamFile(string $disk, string $path, string $downloadName, string $mime): StreamedResponse
    {
        if (!Storage::disk($disk)->exists($path)) {
            abort(404, 'الملف غير موجود.');
        }

        return Storage::disk($disk)->download($path, $downloadName, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
