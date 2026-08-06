<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseRegistrationRequestResource;
use App\Models\CourseRegistrationRequest;
use App\Models\CourseRegistrationRequestMember;
use App\Models\Trainee;
use App\Models\TrainingCourse;
use App\Services\Training\EntityCodeGenerator;
use App\Support\ActiveBranchGuard;
use App\Support\RegistrationBranchResolver;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseRegistrationRequestController extends Controller
{
    public function __construct(private EntityCodeGenerator $codeGenerator) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $canListCourseRequests = $user
            && (
                $user->hasPermissionTo('view_registration_requests')
                || $user->hasPermissionTo('create_course_registration_requests')
                || $user->hasPermissionTo('confirm_course_registration_requests')
                || $user->hasPermissionTo('complete_course_registration_requests')
            );

        if (!$canListCourseRequests) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض طلبات التسجيل بالدورات.',
            ], 403);
        }

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $rows = CourseRegistrationRequest::query()
            ->with([
                'trainingCourse:id,course_code,title,delivery_mode,status,capacity',
                'submittedBy:id,name,email',
                'members',
                'branch:id,name',
                'governorate:id,name_ar',
            ])
            ->tap(fn (Builder $query) => TrainingDataScope::scopeCourseRegistrationRequests($query, $user))
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('training_course_id'), function (Builder $query) use ($request) {
                $query->where('training_course_id', $request->integer('training_course_id'));
            })
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return CourseRegistrationRequestResource::collection($rows)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('create_course_registration_requests')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية إنشاء طلب تسجيل دورة.',
            ], 403);
        }

        $validated = $request->validate([
            'training_course_id' => ['required', 'integer', 'exists:training_courses,id'],
            'registration_mode' => ['required', 'in:self,guardian_with_dependents,group_batch'],
            'submitted_by_type' => ['nullable', 'string', 'max:50'],
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:30'],
            'applicant_email' => ['nullable', 'email', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_national_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'members' => ['required', 'array', 'min:1'],
            'members.*.trainee_id' => ['nullable', 'integer', 'exists:trainees,id'],
            'members.*.full_name' => ['required', 'string', 'max:255'],
            'members.*.national_id' => ['nullable', 'string', 'max:100'],
            'members.*.phone' => ['nullable', 'string', 'max:30'],
            'members.*.email' => ['nullable', 'email', 'max:255'],
            'members.*.birth_date' => ['nullable', 'date'],
            'members.*.gender' => ['nullable', 'in:male,female'],
            'members.*.education_level' => ['nullable', 'string', 'max:100'],
            'members.*.relation_type' => ['required', 'in:self,son,daughter,dependent,member'],
            'members.*.notes' => ['nullable', 'string'],
        ]);

        $course = TrainingCourse::query()->withCount('trainees')->findOrFail($validated['training_course_id']);

        ActiveBranchGuard::assertCourseBranchActive($course->branch_id);

        if (!$course->hasAvailableCapacity()) {
            return response()->json([
                'message' => 'لا توجد سعة متاحة في هذه الدورة.',
            ], 422);
        }

        $requestRow = DB::transaction(function () use ($validated, $user, $course) {
            $row = CourseRegistrationRequest::create(array_merge([
                'request_number' => 'CRR-' . now()->format('YmdHis') . '-' . mt_rand(100, 999),
                'training_course_id' => $validated['training_course_id'],
                'registration_mode' => $validated['registration_mode'],
                'submitted_by_user_id' => $user->id,
                'submitted_by_type' => $validated['submitted_by_type'] ?? $user->entity_type,
                'applicant_name' => $validated['applicant_name'],
                'applicant_phone' => $validated['applicant_phone'] ?? null,
                'applicant_email' => $validated['applicant_email'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'guardian_phone' => $validated['guardian_phone'] ?? null,
                'guardian_national_id' => $validated['guardian_national_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'submitted',
            ], RegistrationBranchResolver::fromTrainingCourse($course->id)));

            foreach ($validated['members'] as $member) {
                CourseRegistrationRequestMember::create([
                    'course_registration_request_id' => $row->id,
                    'trainee_id' => $member['trainee_id'] ?? null,
                    'full_name' => $member['full_name'],
                    'national_id' => $member['national_id'] ?? null,
                    'phone' => $member['phone'] ?? null,
                    'email' => $member['email'] ?? null,
                    'birth_date' => $member['birth_date'] ?? null,
                    'gender' => $member['gender'] ?? null,
                    'education_level' => $member['education_level'] ?? null,
                    'relation_type' => $member['relation_type'],
                    'status' => 'pending',
                    'notes' => $member['notes'] ?? null,
                ]);
            }

            return $row;
        });

        $requestRow->load([
            'trainingCourse:id,course_code,title,delivery_mode,status,capacity',
            'submittedBy:id,name,email',
            'members',
        ]);

        return response()->json([
            'message' => 'تم إنشاء طلب التسجيل في الدورة بنجاح.',
            'data' => $requestRow,
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $row = CourseRegistrationRequest::query()
            ->with([
                'trainingCourse:id,course_code,title,delivery_mode,status,capacity,training_center_id',
                'submittedBy:id,name,email',
                'members.trainee:id,name,trainee_code,national_id',
                'branch:id,name',
                'governorate:id,name_ar',
            ])
            ->findOrFail($id);

        $this->authorize('view', $row);

        return response()->json([
            'data' => new CourseRegistrationRequestResource($row),
        ]);
    }

    public function confirmByGuardian(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('confirm_course_registration_requests')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية تأكيد طلب التسجيل.',
            ], 403);
        }

        $row = CourseRegistrationRequest::query()
            ->with(['trainingCourse', 'members'])
            ->findOrFail($id);

        if (!$row->isSubmitted()) {
            return response()->json([
                'message' => 'لا يمكن تأكيد هذا الطلب في حالته الحالية.',
            ], 422);
        }

        if ((int) $row->submitted_by_user_id !== (int) $user->id) {
            return response()->json([
                'message' => 'لا يمكنك تأكيد طلب لا يخصك.',
            ], 403);
        }

        $row->update([
            'status' => 'guardian_confirmed',
            'guardian_confirmed_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم تأكيد الطلب من ولي الأمر بنجاح.',
            'data' => $row->fresh()->load([
                'trainingCourse:id,course_code,title,delivery_mode,status,capacity',
                'submittedBy:id,name,email',
                'members',
            ]),
        ]);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $row = CourseRegistrationRequest::query()
            ->with(['trainingCourse', 'members'])
            ->findOrFail($id);

        $this->authorize('complete', $row);

        if (!$row->isGuardianConfirmed()) {
            return response()->json([
                'message' => 'يجب تأكيد الطلب أولاً قبل إكماله.',
            ], 422);
        }

        $course = $row->trainingCourse()->withCount('trainees')->firstOrFail();

        DB::transaction(function () use ($row, $course) {
            foreach ($row->members as $member) {
                $trainee = null;

                if ($member->trainee_id) {
                    $trainee = Trainee::find($member->trainee_id);
                }

                if (!$trainee && !empty($member->national_id)) {
                    $trainee = Trainee::query()
                        ->where('national_id', $member->national_id)
                        ->first();
                }

                if (!$trainee) {
                    $trainee = Trainee::create([
                        'name' => $member->full_name,
                        'trainee_code' => $this->codeGenerator->nextTraineeCode(),
                        'national_id' => $member->national_id,
                        'phone' => $member->phone,
                        'email' => $member->email,
                        'city' => null,
                        'address' => null,
                        'birth_date' => $member->birth_date,
                        'gender' => $member->gender,
                        'education_level' => $member->education_level,
                        'status' => 'active',
                        'notes' => 'Created from course registration request #' . $row->request_number,
                    ]);
                }

                if (!$course->hasTrainee($trainee->id)) {
                    $course->trainees()->attach($trainee->id, [
                        'attendance_status' => 'registered',
                        'result' => 'pending',
                        'score' => null,
                        'attended_hours' => 0,
                        'notes' => 'Registered from course registration request #' . $row->request_number,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $member->update([
                    'trainee_id' => $trainee->id,
                    'status' => 'registered',
                ]);
            }

            $row->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'تم إكمال الطلب وتسجيل الأعضاء في الدورة بنجاح.',
            'data' => $row->fresh()->load([
                'trainingCourse:id,course_code,title,delivery_mode,status,capacity',
                'submittedBy:id,name,email',
                'members.trainee:id,name,trainee_code,national_id',
            ]),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'غير مصرح لك بتنفيذ هذا الإجراء.',
            ], 403);
        }

        $row = CourseRegistrationRequest::query()->findOrFail($id);

        $this->authorize('cancel', $row);

        if ($row->isCompleted()) {
            return response()->json([
                'message' => 'لا يمكن إلغاء طلب مكتمل.',
            ], 422);
        }

        $row->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'تم إلغاء الطلب بنجاح.',
            'data' => $row,
        ]);
    }
}