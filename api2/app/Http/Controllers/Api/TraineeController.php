<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TraineeResource;
use App\Models\Trainee;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Services\Training\EntityCodeGenerator;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TraineeController extends Controller
{
    public function __construct(
        private readonly EntityCodeGenerator $codeGenerator,
    ) {
    }

    /**
     * Display a paginated listing of trainees.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Trainee::class);

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $query = Trainee::query()
            ->select([
                'id',
                'name',
                'mother_name',
                'trainee_code',
                'national_id',
                'phone',
                'email',
                'city',
                'address',
                'birth_date',
                'gender',
                'education_level',
                'status',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->withCount([
                'courses',
                'certificates',
            ])
            ->when($request->boolean('with_courses', false), function (Builder $query) {
                $query->with([
                    'courses:id,course_code,title,delivery_mode,start_date,end_date,status',
                ]);
            })
            ->when($request->boolean('with_certificates', false), function (Builder $query) {
                $query->with([
                    'certificates:id,trainee_id,certificate_number,reference_number,verification_code,certificate_type,result,score,hours_awarded,status,issue_date,is_verified',
                ]);
            })
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('city'), function (Builder $query) use ($request) {
                $query->where('city', 'like', '%' . $request->string('city')->toString() . '%');
            })
            ->when($request->filled('gender'), function (Builder $query) use ($request) {
                $query->where('gender', $request->string('gender')->toString());
            })
            ->when($request->filled('education_level'), function (Builder $query) use ($request) {
                $query->where('education_level', $request->string('education_level')->toString());
            })
            ->when($request->filled('has_location'), function (Builder $query) {
                $query->whereNotNull('latitude')->whereNotNull('longitude');
            })
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $value = trim((string) $request->input('search'));

                $query->where(function (Builder $q) use ($value) {
                    $q->where('name', 'like', "%{$value}%")
                        ->orWhere('trainee_code', 'like', "%{$value}%")
                        ->orWhere('national_id', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('city', 'like', "%{$value}%");
                });
            })
            ->orderByDesc('id');

        $trainees = TrainingDataScope::scopeTrainees($query, $request->user())
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TraineeResource::collection($trainees)->additional([
            'meta' => [
                'filters' => [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'city' => $request->input('city'),
                    'gender' => $request->input('gender'),
                    'education_level' => $request->input('education_level'),
                    'with_courses' => $request->boolean('with_courses', false),
                    'with_certificates' => $request->boolean('with_certificates', false),
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created trainee.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Trainee::class);

        $user = $request->user();
        $validated = $this->validateTraineePayload($request, null);
        $courseId = isset($validated['training_course_id']) ? (int) $validated['training_course_id'] : null;
        unset($validated['training_course_id']);

        $validated['notes'] = $this->applyCenterMarker($user, $validated['notes'] ?? null);
        if ($user?->isCenterUser() && $user->training_center_id) {
            $validated['owned_training_center_id'] = (int) $user->training_center_id;
        }
        $validated['trainee_code'] = $validated['trainee_code'] ?? $this->codeGenerator->nextTraineeCode();
        $validated['status'] = $validated['status'] ?? 'active';

        $trainee = DB::transaction(function () use ($validated, $courseId, $user) {
            $trainee = Trainee::create($validated);

            if ($courseId) {
                $this->attachToCourse($trainee, $courseId, $user);
            }

            return $trainee;
        });

        $trainee->load([
            'courses:id,course_code,title,delivery_mode,start_date,end_date,status',
        ])->loadCount(['courses', 'certificates']);

        return response()->json([
            'message' => 'تم إضافة المتدرب بنجاح.',
            'data' => new TraineeResource($trainee),
        ], 201);
    }

    /**
     * Display the specified trainee.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $trainee = TrainingDataScope::scopeTrainees(Trainee::query(), $request->user())
            ->select([
                'id',
                'name',
                'mother_name',
                'trainee_code',
                'national_id',
                'phone',
                'email',
                'city',
                'address',
                'birth_date',
                'gender',
                'education_level',
                'status',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->with([
                'courses:id,course_code,title,delivery_mode,start_date,end_date,status',
                'certificates:id,trainee_id,certificate_number,reference_number,verification_code,certificate_type,result,score,hours_awarded,status,issue_date,is_verified',
            ])
            ->withCount([
                'courses',
                'certificates',
            ])
            ->findOrFail($id);

        $this->authorize('view', $trainee);

        return response()->json([
            'data' => new TraineeResource($trainee),
        ]);
    }

    /**
     * Update the specified trainee.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $trainee = TrainingDataScope::scopeTrainees(Trainee::query(), $request->user())->findOrFail($id);
        $this->authorize('update', $trainee);

        $user = $request->user();
        $validated = $this->validateTraineePayload($request, $trainee);
        $courseId = array_key_exists('training_course_id', $validated)
            ? ($validated['training_course_id'] !== null ? (int) $validated['training_course_id'] : null)
            : null;
        unset($validated['training_course_id']);

        if (array_key_exists('notes', $validated)) {
            $validated['notes'] = $this->applyCenterMarker($user, $validated['notes']);
        } elseif ($user?->isCenterUser() && $user->training_center_id) {
            $validated['notes'] = $this->applyCenterMarker($user, $trainee->notes);
        }

        if ($user?->isCenterUser() && $user->training_center_id && ! $trainee->owned_training_center_id) {
            $validated['owned_training_center_id'] = (int) $user->training_center_id;
        }

        DB::transaction(function () use ($trainee, $validated, $courseId, $user) {
            $trainee->update($validated);

            if ($courseId) {
                $this->attachToCourse($trainee, $courseId, $user);
            }
        });

        $trainee->refresh()->load([
            'courses:id,course_code,title,delivery_mode,start_date,end_date,status',
        ])->loadCount(['courses', 'certificates']);

        return response()->json([
            'message' => 'تم تحديث بيانات المتدرب بنجاح.',
            'data' => new TraineeResource($trainee),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTraineePayload(Request $request, ?Trainee $trainee): array
    {
        $isUpdate = $trainee !== null;

        return $request->validate([
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'trainee_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('trainees', 'trainee_code')->ignore($trainee?->id)->whereNull('deleted_at'),
            ],
            'national_id' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive,blocked'],
            'notes' => ['nullable', 'string'],
            'training_course_id' => ['nullable', 'integer', 'exists:training_courses,id'],
        ], [
            'name.required' => 'الاسم الثلاثي للمتدرب مطلوب.',
        ]);
    }

    private function applyCenterMarker(?User $user, ?string $notes): ?string
    {
        if (!$user?->isCenterUser() || !$user->training_center_id) {
            return $notes;
        }

        $marker = '[center:' . $user->training_center_id . ']';
        $clean = trim((string) preg_replace('/^\[center:\d+\]\s*/', '', (string) $notes));

        return trim($marker . ($clean !== '' ? ' ' . $clean : ''));
    }

    private function attachToCourse(Trainee $trainee, int $courseId, ?User $user): void
    {
        $course = TrainingDataScope::scopeTrainingCourses(
            TrainingCourse::query()->whereKey($courseId),
            $user
        )->firstOrFail();

        if ($course->trainees()->where('trainees.id', $trainee->id)->exists()) {
            return;
        }

        $course->trainees()->attach($trainee->id, [
            'attendance_status' => 'registered',
            'result' => 'pending',
            'score' => null,
            'attended_hours' => 0,
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
