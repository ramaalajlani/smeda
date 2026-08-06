<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainingCenter;
use App\Models\TrainingCenterRegistrationRequest;
use App\Models\User;
use App\Services\Training\EntityCodeGenerator;
use App\Support\ApiErrorResponse;
use App\Support\PaginationLimiter;
use App\Support\RegistrationApprovalLinker;
use App\Support\SecureFileStorage;
use App\Support\RegistrationBranchResolver;
use App\Support\TrainingDataScope;
use App\Support\TrainingSupervisorResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TrainingCenterRegistrationRequestController extends Controller
{
    public function __construct(
        private EntityCodeGenerator $codeGenerator,
        private TrainingSupervisorResolver $supervisorResolver,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('view_registration_requests')) {
            return response()->json([
                'message' => 'غير مصرح لك بعرض طلبات تسجيل المراكز.',
            ], 403);
        }

        $query = TrainingCenterRegistrationRequest::query()
            ->with([
                'submittedBy:id,name,email',
                'reviewedBy:id,name,email',
                'branch:id,name',
                'governorate:id,name_ar',
            ])
            ->tap(fn ($q) => TrainingDataScope::scopeSubmittedRegistrationRequests($q, $user))
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('submitted_by_user_id')) {
            $query->where('submitted_by_user_id', (int) $request->submitted_by_user_id);
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        $rows = $query->paginate(PaginationLimiter::perPage($request));

        $rows->getCollection()->transform(function (TrainingCenterRegistrationRequest $item) {
            return $this->transformItem($item);
        });

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('create_center_registration_requests')) {
            return response()->json([
                'message' => 'غير مصرح لك بإرسال طلب تسجيل مركز.',
            ], 403);
        }

        $validated = $request->validate([
            'center_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],

            'classification_requested' => ['nullable', 'string', 'max:100'],

            'supports_online_training' => ['required', 'boolean'],
            'supports_offline_training' => ['required', 'boolean'],

            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            'license_number' => ['required', 'string', 'max:255'],
            'license_issue_date' => ['required', 'date'],
            'license_issued_by' => ['required', 'string', 'max:255'],

            'license_image' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],

            'notes' => ['nullable', 'string'],
        ]);

        $existingApprovedCenter = TrainingCenter::query()
            ->where('name', $validated['center_name'])
            ->where('city', $validated['city'])
            ->first();

        if ($existingApprovedCenter) {
            return response()->json([
                'message' => 'يوجد مركز تدريبي مسجل مسبقاً بنفس الاسم والمدينة.',
            ], 422);
        }

        $existingPendingRequest = TrainingCenterRegistrationRequest::query()
            ->where('center_name', $validated['center_name'])
            ->where('city', $validated['city'])
            ->whereIn('status', ['pending', 'under_review'])
            ->first();

        if ($existingPendingRequest) {
            return response()->json([
                'message' => 'يوجد طلب تسجيل سابق لنفس المركز وما زال قيد المعالجة.',
            ], 422);
        }

        DB::beginTransaction();

        $licenseImagePath = null;

        try {
            if ($request->hasFile('license_image')) {
                $licenseImagePath = SecureFileStorage::storeUploadedFile(
                    $request->file('license_image'),
                    'registration-requests/centers/licenses',
                    'public',
                    ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']
                );
            }

            $row = TrainingCenterRegistrationRequest::create(array_merge([
                'request_number' => $this->generateRequestNumber(),
                'submitted_by_user_id' => $user->id,

                'center_name' => $validated['center_name'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,

                'classification_requested' => $validated['classification_requested'] ?? null,
                'supports_online_training' => (bool) $validated['supports_online_training'],
                'supports_offline_training' => (bool) $validated['supports_offline_training'],

                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],

                'license_number' => $validated['license_number'],
                'license_issue_date' => $validated['license_issue_date'],
                'license_issued_by' => $validated['license_issued_by'],
                'license_image_path' => $licenseImagePath,

                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ], RegistrationBranchResolver::fromUser($user)));

            DB::commit();

            $row->load([
                'submittedBy:id,name,email',
                'reviewedBy:id,name,email',
            ]);

            return response()->json([
                'message' => 'تم إرسال طلب تسجيل المركز بنجاح.',
                'data' => $this->transformItem($row),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($licenseImagePath && Storage::disk('public')->exists($licenseImagePath)) {
                Storage::disk('public')->delete($licenseImagePath);
            }

            return response()->json(
                ApiErrorResponse::payload($e, 'حدث خطأ غير متوقع، يرجى المحاولة لاحقاً.', 'training_center_registration.store_failed'),
                500
            );
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $row = TrainingCenterRegistrationRequest::query()
            ->with([
                'submittedBy:id,name,email',
                'reviewedBy:id,name,email',
                'branch:id,name',
                'governorate:id,name_ar',
            ])
            ->find($id);

        if (!$row) {
            return response()->json([
                'message' => 'طلب تسجيل المركز غير موجود.',
            ], 404);
        }

        $this->authorize('view', $row);

        return response()->json([
            'data' => $this->transformItem($row),
        ]);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $row = TrainingCenterRegistrationRequest::query()->find($id);

        if (!$row) {
            return response()->json([
                'message' => 'طلب تسجيل المركز غير موجود.',
            ], 404);
        }

        if (!$user || !$user->can('review', $row)) {
            return response()->json([
                'message' => 'غير مصرح لك بمراجعة طلبات تسجيل المراكز.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,under_review'],
            'decision_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'approved') {
            if ($row->isApproved() && $row->approved_training_center_id) {
                return response()->json([
                    'message' => 'تم اعتماد هذا الطلب مسبقاً ومرتبط بمركز تدريبي.',
                    'data' => $this->transformItem($row->load([
                        'submittedBy:id,name,email',
                        'reviewedBy:id,name,email',
                    ])),
                ], 422);
            }

            if (!$row->isPending() && !$row->isUnderReview()) {
                return response()->json([
                    'message' => 'لا يمكن اعتماد هذا الطلب في حالته الحالية.',
                ], 422);
            }
        }

        if ($validated['status'] === 'rejected' && $row->isApproved()) {
            return response()->json([
                'message' => 'لا يمكن رفض طلب معتمد مسبقاً.',
            ], 422);
        }

        DB::transaction(function () use ($row, $validated, $user) {
            $approvedCenterId = $row->approved_training_center_id;

            if ($validated['status'] === 'approved') {
                if ($approvedCenterId) {
                    $center = TrainingCenter::query()->findOrFail($approvedCenterId);
                } else {
                    $center = TrainingCenter::create([
                        'name' => $row->center_name,
                        'code' => $this->codeGenerator->nextCenterCode(),
                        'city' => $row->city,
                        'address' => $row->address,
                        'phone' => $row->phone,
                        'email' => $row->email,
                        'classification' => $row->classification_requested,
                        'accreditation_status' => 'approved',
                        'supports_offline_training' => (bool) $row->supports_offline_training,
                        'supports_online_training' => (bool) $row->supports_online_training,
                        'accreditation_start_date' => now()->toDateString(),
                        'accreditation_end_date' => now()->addYear()->toDateString(),
                        'latitude' => $row->latitude,
                        'longitude' => $row->longitude,
                        'location_visibility' => 'public',
                        'license_number' => $row->license_number,
                        'license_issue_date' => $row->license_issue_date,
                        'license_expiry_date' => null,
                        'license_issued_by' => $row->license_issued_by,
                        'license_image_path' => $row->license_image_path,
                        'supervisor_id' => $this->supervisorResolver->resolveForScope(
                            $row->branch_id ? (int) $row->branch_id : null,
                            $row->governorate_id ? (int) $row->governorate_id : null
                        ),
                        'is_active' => true,
                        'notes' => $this->buildCenterNotesFromRequest($row),
                    ]);

                    $approvedCenterId = $center->id;
                }

                if ($row->submitted_by_user_id) {
                    $submitter = User::query()->find($row->submitted_by_user_id);

                    if ($submitter) {
                        RegistrationApprovalLinker::linkUserToCenter(
                            $submitter,
                            TrainingCenter::query()->findOrFail($approvedCenterId)
                        );
                    }
                }
            }

            $row->update([
                'status' => $validated['status'],
                'decision_notes' => $validated['decision_notes'] ?? null,
                'review_notes' => $validated['decision_notes'] ?? null,
                'reviewed_by_user_id' => $user->id,
                'reviewed_at' => now(),
                'approved_training_center_id' => $validated['status'] === 'approved' ? $approvedCenterId : $row->approved_training_center_id,
                'approved_at' => $validated['status'] === 'approved' ? now() : $row->approved_at,
                'rejected_at' => $validated['status'] === 'rejected' ? now() : null,
            ]);
        });

        $row->refresh()->load([
            'submittedBy:id,name,email',
            'reviewedBy:id,name,email',
            'approvedTrainingCenter:id,name,code,city',
        ]);

        return response()->json([
            'message' => 'تم تحديث حالة طلب تسجيل المركز بنجاح.',
            'data' => $this->transformItem($row),
        ]);
    }

    private function generateRequestNumber(): string
    {
        return 'TCRR-' . now()->format('YmdHis') . '-' . random_int(100, 999);
    }

    private function buildCenterNotesFromRequest(TrainingCenterRegistrationRequest $row): ?string
    {
        $parts = array_filter([
            $row->notes,
            'Created from registration request #' . $row->request_number,
        ]);

        return $parts ? implode("\n", $parts) : null;
    }

    private function transformItem(TrainingCenterRegistrationRequest $item): array
    {
        return [
            'id' => $item->id,
            'request_number' => $item->request_number,

            'submitted_by_user_id' => $item->submitted_by_user_id,
            'reviewed_by_user_id' => $item->reviewed_by_user_id,
            'approved_training_center_id' => $item->approved_training_center_id,

            'center_name' => $item->center_name,
            'city' => $item->city,
            'address' => $item->address,
            'phone' => $item->phone,
            'email' => $item->email,

            'classification_requested' => $item->classification_requested,
            'supports_online_training' => (bool) $item->supports_online_training,
            'supports_offline_training' => (bool) $item->supports_offline_training,

            'latitude' => $item->latitude,
            'longitude' => $item->longitude,

            'license_number' => $item->license_number,
            'license_issue_date' => $item->license_issue_date?->format('Y-m-d'),
            'license_issued_by' => $item->license_issued_by,
            'license_image_path' => $item->license_image_path,
            'license_image_url' => $item->license_image_url,

            'notes' => $item->notes,
            'decision_notes' => $item->decision_notes ?? $item->review_notes,
            'review_notes' => $item->review_notes,

            'status' => $item->status,

            'branch_id' => $item->branch_id,
            'branch_name' => $item->branch?->name,
            'governorate_id' => $item->governorate_id,
            'governorate_name' => $item->governorate?->name_ar,

            'reviewed_at' => $item->reviewed_at?->toDateTimeString(),
            'approved_at' => $item->approved_at?->toDateTimeString(),
            'rejected_at' => $item->rejected_at?->toDateTimeString(),

            'created_at' => $item->created_at?->toDateTimeString(),
            'updated_at' => $item->updated_at?->toDateTimeString(),

            'submitted_by' => $item->submittedBy ? [
                'id' => $item->submittedBy->id,
                'name' => $item->submittedBy->name,
                'email' => $item->submittedBy->email,
            ] : null,

            'reviewed_by' => $item->reviewedBy ? [
                'id' => $item->reviewedBy->id,
                'name' => $item->reviewedBy->name,
                'email' => $item->reviewedBy->email,
            ] : null,
        ];
    }
}