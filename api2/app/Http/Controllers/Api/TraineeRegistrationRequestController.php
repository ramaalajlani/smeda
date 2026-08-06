<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TraineeRegistrationRequestResource;
use App\Models\Trainee;
use App\Models\TraineeRegistrationRequest;
use App\Models\User;
use App\Services\Training\EntityCodeGenerator;
use App\Support\RegistrationApprovalLinker;
use App\Support\RegistrationBranchResolver;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TraineeRegistrationRequestController extends Controller
{
    public function __construct(private EntityCodeGenerator $codeGenerator) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('view_registration_requests')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض طلبات تسجيل المتدربين.',
            ], 403);
        }

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $rows = TraineeRegistrationRequest::query()
            ->with([
                'submittedBy:id,name,email',
                'reviewedBy:id,name,email',
                'approvedTrainee:id,name,trainee_code,national_id',
                'branch:id,name',
                'governorate:id,name_ar',
            ])
            ->tap(fn (Builder $query) => TrainingDataScope::scopeSubmittedRegistrationRequests($query, $user))
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('registration_mode'), function (Builder $query) use ($request) {
                $query->where('registration_mode', $request->string('registration_mode')->toString());
            })
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TraineeRegistrationRequestResource::collection($rows)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('create_trainee_registration_requests')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية إنشاء طلب تسجيل متدرب.',
            ], 403);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'registration_mode' => ['required', 'in:self,guardian,group'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_national_id' => ['nullable', 'string', 'max:100'],
            'group_name' => ['nullable', 'string', 'max:255'],
        ]);

        $row = TraineeRegistrationRequest::create(array_merge([
            'request_number' => 'TAR-' . now()->format('YmdHis') . '-' . mt_rand(100, 999),
            'full_name' => $validated['full_name'],
            'national_id' => $validated['national_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'education_level' => $validated['education_level'] ?? null,
            'registration_mode' => $validated['registration_mode'],
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'guardian_national_id' => $validated['guardian_national_id'] ?? null,
            'group_name' => $validated['group_name'] ?? null,
            'submitted_by_user_id' => $user->id,
            'status' => 'pending',
        ], RegistrationBranchResolver::fromUser($user)));

        $row->load([
            'submittedBy:id,name,email',
            'reviewedBy:id,name,email',
            'approvedTrainee:id,name,trainee_code,national_id',
        ]);

        return response()->json([
            'message' => 'تم إنشاء طلب تسجيل المتدرب بنجاح.',
            'data' => $row,
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $row = TraineeRegistrationRequest::query()
            ->with([
                'submittedBy:id,name,email',
                'reviewedBy:id,name,email',
                'approvedTrainee:id,name,trainee_code,national_id',
                'branch:id,name',
                'governorate:id,name_ar',
            ])
            ->findOrFail($id);

        $this->authorize('view', $row);

        return response()->json([
            'data' => new TraineeRegistrationRequestResource($row),
        ]);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $row = TraineeRegistrationRequest::query()->findOrFail($id);

        if (!$user || !$user->can('review', $row)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية مراجعة طلبات تسجيل المتدربين.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,cancelled'],
            'review_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'approved') {
            if ($row->isApproved() && $row->approved_trainee_id) {
                return response()->json([
                    'message' => 'تم اعتماد هذا الطلب مسبقاً ومرتبط بمتدرب.',
                    'data' => $row->load([
                        'submittedBy:id,name,email',
                        'reviewedBy:id,name,email',
                        'approvedTrainee:id,name,trainee_code,national_id',
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
            $approvedTraineeId = $row->approved_trainee_id;
            $approvedAt = null;
            $rejectedAt = null;

            if ($validated['status'] === 'approved') {
                if ($approvedTraineeId) {
                    $trainee = Trainee::query()->findOrFail($approvedTraineeId);
                } else {
                    $trainee = Trainee::create([
                        'name' => $row->full_name,
                        'trainee_code' => $this->codeGenerator->nextTraineeCode(),
                        'national_id' => $row->national_id,
                        'phone' => $row->phone,
                        'email' => $row->email,
                        'city' => $row->city,
                        'address' => $row->address,
                        'birth_date' => $row->birth_date,
                        'gender' => $row->gender,
                        'education_level' => $row->education_level,
                        'status' => 'active',
                        'notes' => 'Created from registration request #' . $row->request_number,
                    ]);

                    $approvedTraineeId = $trainee->id;
                }

                if ($row->submitted_by_user_id) {
                    $submitter = User::query()->find($row->submitted_by_user_id);

                    if ($submitter) {
                        RegistrationApprovalLinker::linkUserToTrainee(
                            $submitter,
                            Trainee::query()->findOrFail($approvedTraineeId)
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
                'approved_trainee_id' => $approvedTraineeId,
                'approved_at' => $approvedAt,
                'rejected_at' => $rejectedAt,
            ]);
        });

        $row->refresh()->load([
            'submittedBy:id,name,email',
            'reviewedBy:id,name,email',
            'approvedTrainee:id,name,trainee_code,national_id',
        ]);

        return response()->json([
            'message' => 'تمت مراجعة طلب تسجيل المتدرب بنجاح.',
            'data' => $row,
        ]);
    }
}