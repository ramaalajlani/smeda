<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use App\Models\SessionAttendance;
use App\Models\TrainingCourse;
use App\Support\TrainingDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseSessionController extends Controller
{
    /** يحمّل الدورة ضمن نطاق صلاحية المستخدم فقط (عزل بيانات المركز/المدرب). */
    private function course(int $id, $user): TrainingCourse
    {
        return TrainingDataScope::scopeTrainingCourses(TrainingCourse::query(), $user)->findOrFail($id);
    }

    public function index(int $id, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());

        $groupId = $request->integer('group_id') ?: null;

        $sessions = CourseSession::query()
            ->where('training_course_id', $course->id)
            ->when($groupId, fn ($q) => $q->where('course_group_id', $groupId))
            ->withCount([
                'attendance as present_count' => fn ($q) => $q->where('status', 'present'),
                'attendance as absent_count'  => fn ($q) => $q->where('status', 'absent'),
            ])
            ->with('module:id,title')
            ->orderBy('session_date')
            ->orderBy('session_no')
            ->get();

        return response()->json([
            'data' => $sessions,
            'meta' => ['course_id' => $course->id, 'sessions_count' => $sessions->count()],
        ]);
    }

    public function store(int $id, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());

        $data = $request->validate([
            'session_date'      => ['required', 'date'],
            'course_group_id'   => ['nullable', 'integer'],
            'program_module_id' => ['nullable', 'integer'],
            'title'             => ['nullable', 'string', 'max:255'],
            'start_time'        => ['nullable', 'string', 'max:8'],
            'end_time'          => ['nullable', 'string', 'max:8'],
            'notes'             => ['nullable', 'string'],
        ]);

        // تحقّق أن الصف يتبع هذه الدورة
        $groupId = $data['course_group_id'] ?? null;
        if ($groupId && ! \App\Models\CourseGroup::where('training_course_id', $course->id)->where('id', $groupId)->exists()) {
            $groupId = null;
        }

        $nextNo = (int) CourseSession::where('training_course_id', $course->id)->max('session_no') + 1;

        $session = CourseSession::create([
            'training_course_id' => $course->id,
            'course_group_id'    => $groupId,
            'program_module_id'  => $data['program_module_id'] ?? null,
            'session_no'         => $nextNo,
            'session_date'       => $data['session_date'],
            'start_time'         => $data['start_time'] ?? null,
            'end_time'           => $data['end_time'] ?? null,
            'title'              => $data['title'] ?? null,
            'notes'              => $data['notes'] ?? null,
            'status'             => 'held',
        ]);

        return response()->json(['message' => 'تم إنشاء الجلسة بنجاح.', 'data' => $session], 201);
    }

    public function attendanceIndex(int $id, int $sessionId, Request $request): JsonResponse
    {
        $course  = $this->course($id, $request->user());
        $session = CourseSession::where('training_course_id', $course->id)->findOrFail($sessionId);

        // إذا كانت الجلسة تخصّ صفاً، نعرض متدربي ذلك الصف فقط
        $traineesRel = $course->trainees()
            ->select(['trainees.id', 'trainees.name', 'trainees.trainee_code'])
            ->orderBy('trainees.name');
        if ($session->course_group_id) {
            $traineesRel->wherePivot('course_group_id', $session->course_group_id);
        }
        $trainees = $traineesRel->get();

        $att = SessionAttendance::where('course_session_id', $session->id)->get()->keyBy('trainee_id');

        $data = $trainees->map(fn ($t) => [
            'trainee_id'       => $t->id,
            'name'             => $t->name,
            'trainee_code'     => $t->trainee_code,
            'status'           => $att->get($t->id)->status ?? 'present',
            'minutes_attended' => (int) ($att->get($t->id)->minutes_attended ?? 0),
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'session_id'   => $session->id,
                'session_date' => optional($session->session_date)->format('Y-m-d'),
                'session_no'   => $session->session_no,
            ],
        ]);
    }

    public function attendanceStore(int $id, int $sessionId, Request $request): JsonResponse
    {
        $course  = $this->course($id, $request->user());
        $session = CourseSession::where('training_course_id', $course->id)->findOrFail($sessionId);

        $data = $request->validate([
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.trainee_id'       => ['required', 'integer'],
            'items.*.status'           => ['required', 'in:present,absent,late,excused'],
            'items.*.minutes_attended' => ['nullable', 'integer', 'min:0'],
        ]);

        $validTraineeIds = $course->trainees()->pluck('trainees.id')->flip();
        $now = now();

        $rows = [];
        foreach ($data['items'] as $it) {
            $tid = (int) $it['trainee_id'];
            if (! $validTraineeIds->has($tid)) {
                continue;
            }
            $rows[] = [
                'course_session_id' => $session->id,
                'trainee_id' => $tid,
                'status' => $it['status'],
                'minutes_attended' => (int) ($it['minutes_attended'] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            SessionAttendance::upsert(
                $rows,
                ['course_session_id', 'trainee_id'],
                ['status', 'minutes_attended', 'updated_at']
            );
        }

        return response()->json(['message' => 'تم حفظ الحضور بنجاح.']);
    }
}
