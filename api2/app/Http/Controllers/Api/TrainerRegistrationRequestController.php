<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainerRegistrationRequestResource;
use App\Models\Trainer;
use App\Models\TrainerRegistrationRequest;
use App\Models\User;
use App\Services\Training\EntityCodeGenerator;
use App\Support\RegistrationApprovalLinker;
use App\Support\RegistrationBranchResolver;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainerRegistrationRequestController extends Controller
{
    public function __construct(private EntityCodeGenerator $codeGenerator) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('view_registration_requests')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض طلبات تسجيل المدربين.',
            ], 403);
        }

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $rows = TrainerRegistrationRequest::query()
            ->with([
                'trainingCenter:id,name,code,city',
                'submittedBy:id,name,email',
                'reviewedBy:id,name,email',
                'approvedTrainer:id,name,trainer_code,training_center_id',
                'branch:id,name',
                'governorate:id,name_ar',
            ])
            ->tap(fn (Builder $query) => TrainingDataScope::scopeTrainerRegistrationRequests($query, $user))
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('training_center_id'), function (Builder $query) use ($request) {
                $query->where('training_center_id', $request->integer('training_center_id'));
            })
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TrainerRegistrationRequestResource::collection($rows)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('create_trainer_registration_requests')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية إنشاء طلب تسجيل مدرب.',
            ], 403);
        }

        $validated = $request->validate([
            'training_center_id' => ['nullable', 'integer', 'exists:training_centers,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'classification_requested' => ['nullable', 'string', 'max:100'],
            'has_tot' => ['nullable', 'boolean'],
            'tot_certificate_number' => ['nullable', 'string', 'max:100'],
            'tot_certificate_source' => ['nullable', 'string', 'max:255'],
            'tot_issue_date' => ['nullable', 'date'],
            'tot_expiry_date' => ['nullable', 'date'],
            'cv_file' => ['nullable', 'string', 'max:255'],
            'certificate_file' => ['nullable', 'string', 'max:255'],
        ]);

        $centerId = $validated['training_center_id'] ?? $user->training_center_id;
        $branchScope = RegistrationBranchResolver::fromTrainingCenter($centerId);
        if (!$branchScope['branch_id']) {
            $branchScope = RegistrationBranchResolver::fromUser($user);
        }

        $row = TrainerRegistrationRequest::create(array_merge([
            'request_number' => 'TRR-' . now()->format('YmdHis') . '-' . mt_rand(100, 999),
            'training_center_id' => $centerId,
            'full_name' => $validated['full_name'],
            'national_id' => $validated['national_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'classification_requested' => $validated['classification_requested'] ?? null,
            'has_tot' => $validated['has_tot'] ?? false,
            'tot_certificate_number' => $validated['tot_certificate_number'] ?? null,
            'tot_certificate_source' => $validated['tot_certificate_source'] ?? null,
            'tot_issue_date' => $validated['tot_issue_date'] ?? null,
            'tot_expiry_date' => $validated['tot_expiry_date'] ?? null,
            'cv_file' => $validated['cv_file'] ?? null,
            'certificate_file' => $validated['certificate_file'] ?? null,
            'submitted_by_user_id' => $user->id,
            'status' => 'pending',
        ], $branchScope));

        $row->load([
            'trainingCenter:id,name,code,city',
            'submittedBy:id,name,email',
            'reviewedBy:id,name,email',
            'approvedTrainer:id,name,trainer_code,training_center_id',
        ]);

        return response()->json([
            'message' => 'تم إنشاء طلب تسجيل المدرب بنجاح.',
            'data' => $row,
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $row = TrainerRegistrationRequest::query()
            ->with([
                'trainingCenter:id,name,code,city',
                'submittedBy:id,name,email',
                'reviewedBy:id,name,email',
                'approvedTrainer:id,name,trainer_code,training_center_id',
                'branch:id,name',
                'governorate:id,name_ar',
            ])
            ->findOrFail($id);

        $this->authorize('view', $row);

        return response()->json([
            'data' => new TrainerRegistrationRequestResource($row),
        ]);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $row = TrainerRegistrationRequest::query()->findOrFail($id);

        if (!$user || !$user->can('review', $row)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية مراجعة طلبات تسجيل المدربين.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,cancelled'],
            'review_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'approved') {
            if ($row->isApproved() && $row->approved_trainer_id) {
                return response()->json([
                    'message' => 'تم اعتماد هذا الطلب مسبقاً ومرتبط بمدرب.',
                    'data' => $row->load([
                        'trainingCenter:id,name,code,city',
                        'submittedBy:id,name,email',
                        'reviewedBy:id,name,email',
                        'approvedTrainer:id,name,trainer_code,training_center_id',
                    ]),
                ], 422);
            }
        }

        if (!$row->isPending()) {
            return response()->json([
                'message' => 'تمت معالجة هذا الطلب مسبقاً.',
            ], 422);
        }

        if ($validated['status'] === 'rejected' && $row->isApproved()) {
            return response()->json([
                'message' => 'لا يمكن رفض طلب معتمد مسبقاً.',
            ], 422);
        }

        DB::transaction(function () use ($row, $validated, $user) {
            $approvedTrainerId = $row->approved_trainer_id;
            $approvedAt = null;
            $rejectedAt = null;

            if ($validated['status'] === 'approved') {
                if ($approvedTrainerId) {
                    $trainer = Trainer::query()->findOrFail($approvedTrainerId);
                } else {
                    $trainer = Trainer::create([
                        'training_center_id' => $row->training_center_id,
                        'name' => $row->full_name,
                        'trainer_code' => $this->codeGenerator->nextTrainerCode(),
                        'phone' => $row->phone,
                        'email' => $row->email,
                        'specialization' => $row->specialization,
                        'classification' => $row->classification_requested,
                        'has_tot' => (bool) $row->has_tot,
                        'tot_certificate_number' => $row->tot_certificate_number,
                        'tot_certificate_source' => $row->tot_certificate_source,
                        'tot_issue_date' => $row->tot_issue_date,
                        'tot_expiry_date' => $row->tot_expiry_date,
                        'can_train' => true,
                        'can_evaluate' => false,
                        'status' => 'active',
                        'accreditation_start_date' => now()->toDateString(),
                        'accreditation_end_date' => now()->addYear()->toDateString(),
                        'bio' => null,
                        'notes' => 'Created from registration request #' . $row->request_number,
                    ]);

                    $approvedTrainerId = $trainer->id;
                }

                if ($row->submitted_by_user_id) {
                    $submitter = User::query()->find($row->submitted_by_user_id);

                    if ($submitter) {
                        RegistrationApprovalLinker::linkUserToTrainer(
                            $submitter,
                            Trainer::query()->findOrFail($approvedTrainerId)
                        );
                    }
                }

                $approvedAt = now();
            } elseif ($validated['status'] === 'rejected') {
                $rejectedAt = now();
            }

            $row->update([
                'status' => $validated['status'],
                'reviewed_by_user_id' => $user->id,
                'review_notes' => $validated['review_notes'] ?? null,
                'approved_trainer_id' => $approvedTrainerId,
                'approved_at' => $approvedAt,
                'rejected_at' => $rejectedAt,
            ]);
        });

        $row->refresh()->load([
            'trainingCenter:id,name,code,city',
            'submittedBy:id,name,email',
            'reviewedBy:id,name,email',
            'approvedTrainer:id,name,trainer_code,training_center_id',
        ]);

        return response()->json([
            'message' => 'تمت مراجعة طلب تسجيل المدرب بنجاح.',
            'data' => $row,
        ]);
    }
}