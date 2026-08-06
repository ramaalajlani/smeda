<?php



namespace App\Http\Controllers\Api;



use App\DTOs\Training\ApproveCertificateData;

use App\DTOs\Training\IssueCertificateData;

use App\Http\Controllers\Controller;

use App\Http\Requests\Training\ApproveCertificateRequest;

use App\Http\Requests\Training\IssueCertificateRequest;

use App\Http\Requests\Training\VerifyCertificateRequest;

use App\Http\Resources\CertificateResource;

use App\Models\Certificate;

use App\Services\AuditLogService;

use App\Services\Training\CertificateApprovalService;

use App\Services\Training\CertificateService;

use App\Support\TrainingDataScope;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;



class CertificateController extends Controller

{

    public function __construct(

        private CertificateService $certificateService,

        private CertificateApprovalService $approvalService,

        private AuditLogService $auditLog,

    ) {}



    public function index(Request $request)

    {

        $this->authorize('viewAny', Certificate::class);



        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));



        $query = Certificate::query()

            ->select([

                'id', 'trainee_id', 'training_center_id', 'trainer_id', 'training_kit_id',

                'training_program_id', 'training_course_id', 'certificate_number', 'certificate_code', 'reference_number',

                'verification_code', 'certificate_type', 'result', 'score', 'hours_awarded',

                'status', 'issue_date', 'certificate_date', 'is_verified', 'verified_at',

                'qr_code_path', 'certificate_file_path', 'notes', 'branch_id', 'governorate_id', 'created_at', 'updated_at',

            ])

            ->with([

                'trainee:id,name,trainee_code,national_id,status',

                'trainingCenter:id,name,code,city',

                'trainer:id,name,trainer_code',

                'trainingKit:id,name,code,level,hours',

                'trainingProgram:id,name,code',

                'trainingCourse:id,course_code,title,delivery_mode,approved_platform,start_date,end_date',

                'branch:id,name',

                'governorate:id,name_ar',

            ])

            ->when($request->boolean('with_approvals', false), function (Builder $query) {

                $query->with([
                    'approvals:id,certificate_id,approved_by,approval_step,decision,decision_at,notes',
                    'approvals.electronicSignature:id,signable_type,signable_id,role_key,signer_name,signer_title,verification_code,signed_at,signature_image_path,signature_image_hash',
                ]);

            })

            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')->toString()))

            ->when($request->filled('certificate_type'), fn (Builder $q) => $q->where('certificate_type', $request->string('certificate_type')->toString()))

            ->when($request->filled('result'), fn (Builder $q) => $q->where('result', $request->string('result')->toString()))

            ->when($request->filled('training_center_id'), fn (Builder $q) => $q->where('training_center_id', $request->integer('training_center_id')))

            ->when($request->filled('trainer_id'), fn (Builder $q) => $q->where('trainer_id', $request->integer('trainer_id')))

            ->when($request->filled('training_kit_id'), fn (Builder $q) => $q->where('training_kit_id', $request->integer('training_kit_id')))

            ->when($request->filled('training_program_id'), fn (Builder $q) => $q->where('training_program_id', $request->integer('training_program_id')))

            ->when($request->filled('training_course_id'), fn (Builder $q) => $q->where('training_course_id', $request->integer('training_course_id')))

            ->when($request->filled('trainee_id'), fn (Builder $q) => $q->where('trainee_id', $request->integer('trainee_id')))

            ->search($request->input('search'))

            ->orderByDesc('id');



        $certificates = TrainingDataScope::scopeCertificates($query, $request->user())

            ->cursorPaginate($perPage)

            ->appends($request->query());



        return CertificateResource::collection($certificates)->additional([

            'meta' => [

                'filters' => [

                    'search' => $request->input('search'),

                    'status' => $request->input('status'),

                    'certificate_type' => $request->input('certificate_type'),

                    'result' => $request->input('result'),

                    'training_center_id' => $request->input('training_center_id'),

                    'trainer_id' => $request->input('trainer_id'),

                    'training_kit_id' => $request->input('training_kit_id'),

                    'training_program_id' => $request->input('training_program_id'),

                    'training_course_id' => $request->input('training_course_id'),

                    'trainee_id' => $request->input('trainee_id'),

                    'with_approvals' => $request->boolean('with_approvals', false),

                    'per_page' => $perPage,

                ],

            ],

        ]);

    }



    public function show(int $id, Request $request): JsonResponse

    {

        $certificate = TrainingDataScope::scopeCertificates(Certificate::query(), $request->user())

            ->with([

                'trainee:id,name,trainee_code,national_id,status',

                'trainingCenter:id,name,code,city,address,phone,email',

                'trainer:id,name,trainer_code,phone,email',

                'trainingKit:id,name,code,level,hours',

                'trainingProgram:id,name,code,description,status',

                'trainingCourse:id,course_code,title,delivery_mode,approved_platform,start_date,end_date,planned_hours,actual_hours,status',

                'approvals:id,certificate_id,approved_by,approval_step,decision,decision_at,notes',
                'approvals.electronicSignature:id,signable_type,signable_id,role_key,signer_name,signer_title,verification_code,signed_at,signature_image_path,signature_image_hash',

            ])

            ->findOrFail($id);



        $this->authorize('view', $certificate);



        return response()->json([

            'data' => new CertificateResource($certificate),

        ]);

    }



    public function showByCode(string $certificate_code, Request $request): JsonResponse

    {

        $certificate = TrainingDataScope::scopeCertificates(Certificate::query(), $request->user())

            ->with([

                'trainee:id,name,trainee_code,national_id,status',

                'trainingCenter:id,name,code,city,address,phone,email',

                'trainer:id,name,trainer_code,phone,email',

                'trainingKit:id,name,code,level,hours',

                'trainingProgram:id,name,code,description,status',

                'trainingCourse:id,course_code,title,delivery_mode,approved_platform,start_date,end_date,planned_hours,actual_hours,status',

                'approvals:id,certificate_id,approved_by,approval_step,decision,decision_at,notes',
                'approvals.electronicSignature:id,signable_type,signable_id,role_key,signer_name,signer_title,verification_code,signed_at,signature_image_path,signature_image_hash',

            ])

            ->where('certificate_code', $certificate_code)

            ->firstOrFail();



        $this->authorize('view', $certificate);



        return response()->json([

            'data' => new CertificateResource($certificate),

        ]);

    }



    public function verifyByCode(string $certificate_code, Request $request): JsonResponse

    {

        $certificate = Certificate::query()

            ->with([

                'trainee:id,name',

                'trainingCenter:id,name,code',

                'trainer:id,name,trainer_code',

                'trainingKit:id,name,code',

                'trainingCourse:id,title,course_code',

            ])

            ->where('certificate_code', $certificate_code)

            ->first();



        if (!$certificate) {

            return response()->json([

                'message' => 'لم يتم العثور على الشهادة.',

                'data' => null,

            ], 404);

        }



        $this->auditLog->log('certificate_verified', null, $certificate, null, null, [

            'type' => 'certificate_code',

        ], $request);



        if ($certificate->status !== 'approved' || !(bool) $certificate->is_verified) {

            return response()->json([

                'message' => 'الشهادة موجودة لكنها غير معتمدة للتحقق العام.',

                'data' => $this->certificateService->safeVerifyPayload($certificate, false),

            ], 403);

        }



        return response()->json([

            'message' => 'تم العثور على الشهادة.',

            'data' => $this->certificateService->safeVerifyPayload($certificate, true),

        ]);

    }



    public function issue(IssueCertificateRequest $request): JsonResponse

    {

        $certificate = $this->certificateService->issueCertificate(

            IssueCertificateData::fromRequest($request),

            $request->user()

        );



        return response()->json([

            'message' => 'تم إصدار الشهادة بنجاح.',

            'data' => new CertificateResource($certificate),

        ], 201);

    }

    /** إصدار شهادات «اجتياز» دفعة واحدة لكل المتدربين الناجحين في الدورة (م4). */
    public function issueForCourse(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $course = TrainingDataScope::scopeTrainingCourses(\App\Models\TrainingCourse::query(), $user)->findOrFail($id);
        $type = \App\Support\CertificateType::COMPLETION;

        if (! $course->canIssueCertificates()) {
            return response()->json([
                'message' => 'الدورة غير مؤهلة لإصدار الشهادات (يجب أن تكون مكتملة والمركز والمدرب والحقيبة مؤهلين).',
            ], 422);
        }

        $issued = 0;
        $skipped = 0;
        $failed = [];

        foreach ($course->trainees()->get() as $trainee) {
            $pivot = $trainee->pivot;

            if (($pivot->result ?? null) !== 'passed') { $skipped++; continue; }
            if ($course->hasCertificateForTrainee($trainee->id, $type)) { $skipped++; continue; }

            try {
                $this->certificateService->issueCertificate(new IssueCertificateData(
                    trainingCourseId: $course->id,
                    traineeId: (int) $trainee->id,
                    certificateType: $type,
                    result: 'passed',
                    score: $pivot->score !== null ? (float) $pivot->score : null,
                    hoursAwarded: null,
                    notes: null,
                ), $user);
                $issued++;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $failed[] = ['trainee' => $trainee->name, 'reason' => collect($e->errors())->flatten()->first()];
            } catch (\Throwable $e) {
                $failed[] = ['trainee' => $trainee->name, 'reason' => 'خطأ غير متوقع أثناء الإصدار'];
            }
        }

        return response()->json([
            'message' => "تم إصدار {$issued} شهادة" . ($skipped ? " — وتخطّي {$skipped}" : '') . (count($failed) ? " — وفشل " . count($failed) : '') . '.',
            'data' => ['issued' => $issued, 'skipped' => $skipped, 'failed' => $failed],
        ]);
    }



    public function approve(ApproveCertificateRequest $request, int $id): JsonResponse

    {

        $certificate = TrainingDataScope::scopeCertificates(Certificate::query(), $request->user())

            ->findOrFail($id);

        $approvalStep = $request->validated()['approval_step'];
        $this->authorize('approve', [$certificate, $approvalStep]);

        $certificate = $this->approvalService->approve(

            $certificate,

            ApproveCertificateData::fromRequest($request),

            $request->user()

        );



        return response()->json([

            'message' => 'تم تحديث اعتماد الشهادة بنجاح.',

            'data' => new CertificateResource($certificate),

        ]);

    }



    public function verify(VerifyCertificateRequest $request): JsonResponse

    {

        $validated = $request->validated();



        $certificate = Certificate::query()

            ->with([

                'trainee:id,name',

                'trainingCenter:id,name',

                'trainer:id,name',

                'trainingKit:id,name',

                'trainingCourse:id,title',

            ])

            ->where($validated['type'], $validated['value'])

            ->first();



        if (!$certificate) {

            return response()->json([

                'message' => 'لم يتم العثور على الشهادة.',

                'data' => null,

            ], 404);

        }



        $this->auditLog->log('certificate_verified', null, $certificate, null, null, [
            'type' => $validated['type'],
        ], $request);



        if ($certificate->status !== 'approved' || !(bool) $certificate->is_verified) {

            return response()->json([

                'message' => 'الشهادة موجودة لكنها غير معتمدة للتحقق العام.',

                'data' => $this->certificateService->safeVerifyPayload($certificate, false),

            ], 403);

        }



        return response()->json([

            'message' => 'تم العثور على الشهادة.',

            'data' => $this->certificateService->safeVerifyPayload($certificate, true),

        ]);

    }



    public function verifyPage(Request $request)

    {

        $code = $request->query('code');

        $hash = $request->query('hash');



        if (!$code || !$hash) {

            abort(404, 'رابط التحقق غير مكتمل.');

        }



        $certificate = Certificate::query()

            ->with([

                'trainee:id,name',

                'trainingCenter:id,name',

                'trainer:id,name',

                'trainingKit:id,name',

                'trainingCourse:id,title',

            ])

            ->where('verification_code', $code)

            ->first();



        if (!$certificate) {

            abort(404, 'الشهادة غير موجودة.');

        }



        if ($certificate->status !== 'approved' || !(bool) $certificate->is_verified) {

            return view('certificates.verify', [

                'certificate' => null,

                'publicData' => $this->certificateService->safeVerifyPayload($certificate, false),

                'isApproved' => false,

                'isValidHash' => false,

            ]);

        }



        $expectedHash = $this->certificateService->buildAntiFakeHash($certificate);

        $isValidHash = hash_equals($expectedHash, (string) $hash);



        return view('certificates.verify', [

            'certificate' => null,

            'publicData' => \App\Support\CertificatePublicPayload::forVerifyPage($certificate, $isValidHash),

            'isApproved' => true,

            'isValidHash' => $isValidHash,

        ]);

    }

}

