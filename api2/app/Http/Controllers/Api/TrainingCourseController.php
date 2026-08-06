<?php



namespace App\Http\Controllers\Api;



use App\DTOs\Training\StoreTrainingCourseData;

use App\DTOs\Training\UpdateCourseTraineeResultData;

use App\DTOs\Training\UpdateTrainingCourseData;

use App\Http\Controllers\Controller;

use App\Http\Requests\Training\AddCourseTraineeRequest;

use App\Http\Requests\Training\CompleteTrainingCourseRequest;

use App\Http\Requests\Training\StoreTrainingCourseRequest;

use App\Http\Requests\Training\UpdateCourseTraineeResultRequest;

use App\Http\Requests\Training\UpdateTrainingCourseRequest;

use App\Http\Resources\TrainingCourseResource;

use App\Models\Trainee;

use App\Models\TrainingCourse;

use App\Services\Training\CourseTraineeService;

use App\Services\Training\TrainingCourseService;

use App\Support\TrainingDataScope;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;



class TrainingCourseController extends Controller

{

    public function __construct(

        private TrainingCourseService $courseService,

        private CourseTraineeService $traineeService,

    ) {}



    public function index(Request $request)

    {

        $this->authorize('viewAny', TrainingCourse::class);



        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));



        $query = TrainingCourse::query()

            ->select([

                'id', 'training_center_id', 'trainer_id', 'training_kit_id', 'training_program_id',

                'course_code', 'title', 'delivery_mode', 'approved_platform', 'start_date', 'end_date',

                'planned_hours', 'actual_hours', 'capacity', 'status', 'notes', 'branch_id', 'governorate_id', 'created_at', 'updated_at',

            ])

            ->with($this->courseService->defaultRelations())

            ->withCount(['trainees', 'certificates'])

            ->when($request->boolean('with_trainees', false), function (Builder $query) {

                $query->with(['trainees:id,name,trainee_code,status']);

            })

            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')->toString()))

            ->when($request->filled('delivery_mode'), fn (Builder $q) => $q->where('delivery_mode', $request->string('delivery_mode')->toString()))

            ->when($request->filled('training_center_id'), fn (Builder $q) => $q->where('training_center_id', (int) $request->training_center_id))

            ->when($request->filled('trainer_id'), fn (Builder $q) => $q->where('trainer_id', (int) $request->trainer_id))

            ->when($request->filled('training_kit_id'), fn (Builder $q) => $q->where('training_kit_id', (int) $request->training_kit_id))

            ->when($request->filled('training_program_id'), fn (Builder $q) => $q->where('training_program_id', (int) $request->training_program_id))

            ->when($request->filled('start_date_from'), function (Builder $query) use ($request) {

                $date = $request->date('start_date_from');

                if ($date) {

                    $query->whereDate('start_date', '>=', $date->format('Y-m-d'));

                }

            })

            ->when($request->filled('start_date_to'), function (Builder $query) use ($request) {

                $date = $request->date('start_date_to');

                if ($date) {

                    $query->whereDate('start_date', '<=', $date->format('Y-m-d'));

                }

            })

            ->search($request->input('search'))

            ->orderByDesc('id');



        $courses = TrainingDataScope::scopeTrainingCourses($query, $request->user())

            ->cursorPaginate($perPage)

            ->appends($request->query());



        return TrainingCourseResource::collection($courses)->additional([

            'meta' => [

                'filters' => [

                    'search' => $request->input('search'),

                    'status' => $request->input('status'),

                    'delivery_mode' => $request->input('delivery_mode'),

                    'training_center_id' => $request->input('training_center_id'),

                    'trainer_id' => $request->input('trainer_id'),

                    'training_kit_id' => $request->input('training_kit_id'),

                    'training_program_id' => $request->input('training_program_id'),

                    'start_date_from' => $request->input('start_date_from'),

                    'start_date_to' => $request->input('start_date_to'),

                    'with_trainees' => $request->boolean('with_trainees', false),

                    'per_page' => $perPage,

                ],

            ],

        ]);

    }



    public function store(StoreTrainingCourseRequest $request): JsonResponse

    {

        $course = $this->courseService->createCourse(

            StoreTrainingCourseData::fromRequest($request),

            $request->user()

        );



        return response()->json([

            'message' => 'تم إنشاء الدورة التدريبية بنجاح.',

            'data' => new TrainingCourseResource($course),

        ], 201);

    }



    public function show(int $id, Request $request): JsonResponse
    {
        $course = $this->findScopedCourseOrFail($id, $request->user(), true, $request);

        $this->authorize('view', $course);

        return response()->json([
            'data' => new TrainingCourseResource($course),
        ]);
    }



    public function update(UpdateTrainingCourseRequest $request, int $id): JsonResponse

    {

        $course = $this->findScopedCourseOrFail($id, $request->user());



        $course = $this->courseService->updateCourse(

            $course,

            UpdateTrainingCourseData::fromRequest($request),

            $request->user()

        );



        return response()->json([

            'message' => 'تم تحديث الدورة التدريبية بنجاح.',

            'data' => new TrainingCourseResource($course),

        ]);

    }



    public function complete(CompleteTrainingCourseRequest $request, int $id): JsonResponse

    {

        $course = $this->findScopedCourseOrFail($id, $request->user());

        $course = $this->courseService->completeCourse($course, $request->user());



        return response()->json([

            'message' => 'تم إكمال الدورة التدريبية بنجاح.',

            'data' => new TrainingCourseResource($course),

        ]);

    }



    public function trainees(int $id, Request $request): JsonResponse

    {

        $course = $this->findScopedCourseOrFail($id, $request->user());

        $this->authorize('view', $course);



        $trainees = $course->trainees()

            ->select(['trainees.id', 'trainees.name', 'trainees.trainee_code', 'trainees.status'])

            ->orderBy('trainees.name')

            ->get();

        $certifiedTraineeIds = $course->certificates()
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->pluck('trainee_id')
            ->flip();

        // نسبة الحضور لكل متدرب (من جلسات م2): حاضر أو متأخر = حضور.
        $totalSessions = \App\Models\CourseSession::where('training_course_id', $course->id)->count();
        $attendedCounts = $totalSessions > 0
            ? \App\Models\SessionAttendance::query()
                ->join('course_sessions', 'course_sessions.id', '=', 'session_attendance.course_session_id')
                ->where('course_sessions.training_course_id', $course->id)
                ->whereIn('session_attendance.status', ['present', 'late'])
                ->selectRaw('session_attendance.trainee_id, COUNT(*) as c')
                ->groupBy('session_attendance.trainee_id')
                ->pluck('c', 'session_attendance.trainee_id')
            : collect();

        $formatted = $trainees->map(function (Trainee $trainee) use ($course, $certifiedTraineeIds, $totalSessions, $attendedCounts) {
            $row = $this->traineeService->formatCourseTrainee(
                $trainee,
                $course,
                $certifiedTraineeIds->has($trainee->id)
            );
            $row['sessions_total'] = $totalSessions;
            $row['attendance_rate'] = $totalSessions > 0
                ? (int) round(((int) ($attendedCounts[$trainee->id] ?? 0) / $totalSessions) * 100)
                : null;

            return $row;
        });



        return response()->json([

            'data' => $formatted,

            'meta' => [

                'course_id' => $course->id,

                'course_code' => $course->course_code,

                'trainees_count' => $trainees->count(),

                'capacity' => $course->capacity,

            ],

        ]);

    }



    public function addTrainee(AddCourseTraineeRequest $request, int $id): JsonResponse

    {

        $course = $this->findScopedCourseOrFail($id, $request->user());

        $validated = $request->validated();



        $trainee = $this->traineeService->addTrainee(

            $course,

            (int) $validated['trainee_id'],

            $validated['notes'] ?? null,

            $request->user()

        );



        return response()->json([

            'message' => 'تم إضافة المتدرب إلى الدورة بنجاح.',

            'data' => $this->traineeService->formatCourseTrainee($trainee, $course),

        ], 201);

    }



    public function updateTrainee(UpdateCourseTraineeResultRequest $request, int $id, int $traineeId): JsonResponse

    {

        $course = $this->findScopedCourseOrFail($id, $request->user());



        $trainee = $this->traineeService->updateResult(

            $course,

            $traineeId,

            UpdateCourseTraineeResultData::fromRequest($request),

            $request->user()

        );



        return response()->json([

            'message' => 'تم تحديث بيانات المتدرب داخل الدورة بنجاح.',

            'data' => $this->traineeService->formatCourseTrainee($trainee, $course),

        ]);

    }



    public function removeTrainee(Request $request, int $id, int $traineeId): JsonResponse

    {

        $course = $this->findScopedCourseOrFail($id, $request->user());

        $this->authorize('deleteTrainee', $course);



        $this->traineeService->removeTrainee($course, $traineeId, $request->user());



        return response()->json([

            'message' => 'تم إزالة المتدرب من الدورة بنجاح.',

        ]);

    }



    public function destroy(int $id, Request $request): JsonResponse

    {

        $course = $this->findScopedCourseOrFail($id, $request->user());

        $course->delete();

        return response()->json(['message' => 'تم حذف الدورة بنجاح.']);

    }



    public function modules(int $id, Request $request): JsonResponse

    {

        $course = $this->findScopedCourseOrFail($id, $request->user());

        $this->authorize('view', $course);



        $modules = collect();

        if ($course->training_program_id) {

            $modules = \App\Models\ProgramModule::query()

                ->where('program_id', $course->training_program_id)

                ->orderBy('sort_order')

                ->get(['id', 'program_id', 'title', 'description', 'hours', 'sort_order', 'evaluation_method']);

        }



        return response()->json([

            'data' => $modules,

            'meta' => [

                'course_id' => $course->id,

                'course_code' => $course->course_code,

                'modules_count' => $modules->count(),

            ],

        ]);

    }



    private function findScopedCourseOrFail(int $id, $user, bool $withDetails = false, ?Request $request = null): TrainingCourse
    {
        $query = TrainingDataScope::scopeTrainingCourses(TrainingCourse::query(), $user)
            ->select([
                'id', 'training_center_id', 'trainer_id', 'training_kit_id', 'training_program_id',
                'course_code', 'title', 'delivery_mode', 'approved_platform', 'start_date', 'end_date',
                'planned_hours', 'actual_hours', 'capacity', 'status', 'notes', 'branch_id', 'governorate_id', 'created_at', 'updated_at',
            ]);

        if ($withDetails) {
            $includeRaw = (string) ($request?->query('include', '') ?? '');
            $include = collect($includeRaw === '' ? [] : explode(',', $includeRaw))
                ->map(fn ($v) => trim(strtolower($v)))
                ->filter()
                ->values();

            // Default lean: base relations + counts. Pass ?include=trainees,certificates when needed.
            $wantTrainees = $include->contains('trainees') || (bool) $request?->boolean('with_trainees');
            $wantCerts = $include->contains('certificates') || (bool) $request?->boolean('with_certificates');

            $with = [
                'trainingCenter:id,name,code,city,address,phone,email,classification,accreditation_status',
                'trainer:id,name,trainer_code,phone,email,specialization,classification,has_tot,can_train,status',
                'trainingKit:id,name,code,sector,category,type,material_code,level,hours,objective,description,status',
                'trainingProgram:id,name,code,description,status,is_active',
            ];

            if ($wantTrainees) {
                $with[] = 'trainees:id,name,trainee_code,status';
            }
            if ($wantCerts) {
                $with[] = 'certificates:id,trainee_id,training_course_id,certificate_number,certificate_code,reference_number,verification_code,certificate_type,result,score,hours_awarded,status,issue_date,is_verified,qr_code_path,certificate_file_path';
                $with[] = 'certificates.trainee:id,name,trainee_code';
            }

            $query->with($with)->withCount(['trainees', 'certificates']);
        }

        return $query->findOrFail($id);
    }
}
