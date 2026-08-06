<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\TrainingCourse;
use App\Support\TrainingDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseGroupController extends Controller
{
    private function course(int $id, $user): TrainingCourse
    {
        return TrainingDataScope::scopeTrainingCourses(TrainingCourse::query(), $user)->findOrFail($id);
    }

    private function group(TrainingCourse $course, int $groupId): CourseGroup
    {
        return CourseGroup::where('training_course_id', $course->id)->findOrFail($groupId);
    }

    /** قائمة صفوف الدورة مع عدد المتدربين. */
    public function index(int $id, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());
        $groups = CourseGroup::where('training_course_id', $course->id)
            ->withCount('trainees')
            ->orderBy('id')
            ->get(['id', 'name', 'code', 'capacity', 'notes']);

        $ungrouped = $course->trainees()->wherePivotNull('course_group_id')->count();

        return response()->json([
            'data' => $groups,
            'meta' => ['course_id' => $course->id, 'ungrouped_count' => $ungrouped],
        ]);
    }

    public function store(int $id, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'code'     => ['nullable', 'string', 'max:60'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'notes'    => ['nullable', 'string'],
        ]);
        $group = CourseGroup::create(array_merge($data, ['training_course_id' => $course->id]));

        return response()->json(['message' => 'تم إنشاء الصف بنجاح.', 'data' => $group], 201);
    }

    public function destroy(int $id, int $groupId, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());
        $group = $this->group($course, $groupId);
        // فكّ ارتباط المتدربين دفعة واحدة ثم حذف الصف
        DB::table('training_course_trainee')
            ->where('training_course_id', $course->id)
            ->where('course_group_id', $group->id)
            ->update(['course_group_id' => null, 'updated_at' => now()]);
        $group->delete();

        return response()->json(['message' => 'تم حذف الصف.']);
    }

    /** متدربو صفّ محدّد. */
    public function trainees(int $id, int $groupId, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());
        $group = $this->group($course, $groupId);

        $rows = $group->trainees()
            ->select([
                'trainees.id',
                'trainees.name',
                'trainees.mother_name',
                'trainees.trainee_code',
                'trainees.gender',
                'trainees.birth_date',
            ])
            ->orderBy('trainees.name')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'mother_name' => $t->mother_name,
                'trainee_code' => $t->trainee_code,
                'gender' => $t->gender,
                'birth_date' => optional($t->birth_date)?->format('Y-m-d'),
                'result' => $t->pivot->result,
                'score' => $t->pivot->score,
                'attended_hours' => (int) $t->pivot->attended_hours,
            ]);

        return response()->json(['data' => $rows, 'meta' => ['group' => $group]]);
    }

    /** متدربو الدورة غير المنتسبين لأي صف (مرشّحون للإضافة). */
    public function ungrouped(int $id, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());
        $rows = $course->trainees()->wherePivotNull('course_group_id')
            ->select(['trainees.id', 'trainees.name', 'trainees.trainee_code'])
            ->orderBy('trainees.name')
            ->get(['trainees.id', 'trainees.name', 'trainees.trainee_code']);

        return response()->json(['data' => $rows]);
    }

    /** إسناد متدربين إلى صف (أو فكّهم بتمرير group_id فارغ). */
    public function assign(int $id, int $groupId, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());
        $group = $this->group($course, $groupId);
        $data = $request->validate([
            'trainee_ids'   => ['required', 'array', 'min:1'],
            'trainee_ids.*' => ['integer'],
        ]);

        $valid = $course->trainees()->pluck('trainees.id')->flip();
        DB::transaction(function () use ($data, $course, $group, $valid) {
            foreach ($data['trainee_ids'] as $tid) {
                if ($valid->has((int) $tid)) {
                    $course->trainees()->updateExistingPivot((int) $tid, ['course_group_id' => $group->id]);
                }
            }
        });

        return response()->json(['message' => 'تم إسناد المتدربين إلى الصف.']);
    }

    public function remove(int $id, int $groupId, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());
        $this->group($course, $groupId);
        $data = $request->validate([
            'trainee_ids'   => ['required', 'array', 'min:1'],
            'trainee_ids.*' => ['integer'],
        ]);
        foreach ($data['trainee_ids'] as $tid) {
            $course->trainees()->updateExistingPivot((int) $tid, ['course_group_id' => null]);
        }

        return response()->json(['message' => 'تم إخراج المتدربين من الصف.']);
    }
}
