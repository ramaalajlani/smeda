<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgramModule;
use App\Models\TraineeModuleScore;
use App\Models\TrainingCourse;
use App\Support\TrainingDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleScoreController extends Controller
{
    /** يحمّل الدورة ضمن نطاق صلاحية المستخدم فقط (عزل بيانات المركز/المدرب). */
    private function course(int $id, $user): TrainingCourse
    {
        return TrainingDataScope::scopeTrainingCourses(TrainingCourse::query(), $user)->findOrFail($id);
    }

    /** درجات محور محدّد لكل المتدربين + قائمة المحاور. */
    public function index(int $id, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());

        $modules = $course->training_program_id
            ? ProgramModule::where('program_id', $course->training_program_id)
                ->orderBy('sort_order')
                ->get(['id', 'title', 'hours'])
            : collect();

        $moduleId = (int) $request->integer('module_id') ?: (int) ($modules->first()->id ?? 0);

        $groupId = $request->integer('group_id') ?: null;

        $traineesRel = $course->trainees()
            ->select(['trainees.id', 'trainees.name', 'trainees.trainee_code'])
            ->orderBy('trainees.name');
        if ($groupId) {
            $traineesRel->wherePivot('course_group_id', $groupId);
        }
        $trainees = $traineesRel->get();

        $scores = TraineeModuleScore::where('training_course_id', $course->id)
            ->where('program_module_id', $moduleId)
            ->get()
            ->keyBy('trainee_id');

        $rows = $trainees->map(fn ($t) => [
            'trainee_id'       => $t->id,
            'name'             => $t->name,
            'trainee_code'     => $t->trainee_code,
            'coursework_score' => $scores->get($t->id)->coursework_score ?? null,
            'exam_score'       => $scores->get($t->id)->exam_score ?? null,
            'score'            => $scores->get($t->id)->score ?? null,
            'result'           => $scores->get($t->id)->result ?? 'pending',
        ]);

        return response()->json([
            'data' => [
                'modules'            => $modules,
                'selected_module_id' => $moduleId,
                'trainees'           => $rows,
            ],
        ]);
    }

    /** حفظ درجات محور دفعة واحدة (أعمال السنة + الامتحان) وإعادة حساب النتيجة النهائية. */
    public function store(int $id, Request $request): JsonResponse
    {
        $course = $this->course($id, $request->user());

        $data = $request->validate([
            'program_module_id'         => ['required', 'integer'],
            'max_score'                 => ['nullable', 'numeric', 'min:1'],
            'pass_mark'                 => ['nullable', 'numeric', 'min:0'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.trainee_id'        => ['required', 'integer'],
            'items.*.coursework_score'  => ['nullable', 'numeric', 'min:0'],
            'items.*.exam_score'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $max  = (float) ($data['max_score'] ?? 100);
        $pass = (float) ($data['pass_mark'] ?? ($max * 0.6));

        $validTraineeIds = $course->trainees()->pluck('trainees.id')->flip();
        $now = now();

        DB::transaction(function () use ($data, $course, $max, $pass, $validTraineeIds, $now) {
            $rows = [];
            foreach ($data['items'] as $it) {
                $tid = (int) $it['trainee_id'];
                if (! $validTraineeIds->has($tid)) {
                    continue;
                }

                $cw   = isset($it['coursework_score']) && $it['coursework_score'] !== '' ? (float) $it['coursework_score'] : null;
                $exam = isset($it['exam_score']) && $it['exam_score'] !== '' ? (float) $it['exam_score'] : null;
                $total = ($cw === null && $exam === null) ? null : (float) ($cw ?? 0) + (float) ($exam ?? 0);
                $result = $total === null ? 'pending' : ($total >= $pass ? 'passed' : 'failed');

                $rows[] = [
                    'training_course_id' => $course->id,
                    'trainee_id'         => $tid,
                    'program_module_id'  => (int) $data['program_module_id'],
                    'coursework_score'   => $cw,
                    'exam_score'         => $exam,
                    'score'              => $total,
                    'max_score'          => $max,
                    'pass_mark'          => $pass,
                    'result'             => $result,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            if ($rows) {
                TraineeModuleScore::upsert(
                    $rows,
                    ['training_course_id', 'trainee_id', 'program_module_id'],
                    ['coursework_score', 'exam_score', 'score', 'max_score', 'pass_mark', 'result', 'updated_at']
                );
            }

            $this->recomputeFinals($course);
        });

        return response()->json(['message' => 'تم حفظ الدرجات بنجاح.']);
    }

    /** يحسب المعدّل النهائي لكل متدرب عبر كل المحاور ويكتب النتيجة في training_course_trainee. */
    private function recomputeFinals(TrainingCourse $course): void
    {
        $byTrainee = TraineeModuleScore::where('training_course_id', $course->id)
            ->get(['trainee_id', 'score', 'max_score'])
            ->groupBy('trainee_id');

        $scoreCases = [];
        $resultCases = [];
        $ids = [];

        foreach ($byTrainee as $traineeId => $scores) {
            $scored = $scores->filter(fn ($s) => $s->score !== null);
            if ($scored->isEmpty()) {
                continue;
            }

            $tid = (int) $traineeId;
            $pct = round($scored->avg(fn ($s) => $s->max_score > 0 ? ((float) $s->score / (float) $s->max_score * 100) : 0), 2);
            $result = $pct >= 60 ? 'passed' : 'failed';
            $ids[] = $tid;
            $scoreCases[] = 'WHEN ' . $tid . ' THEN ' . $pct;
            $resultCases[] = "WHEN {$tid} THEN '{$result}'";
        }

        if (! $ids) {
            return;
        }

        DB::table('training_course_trainee')
            ->where('training_course_id', $course->id)
            ->whereIn('trainee_id', $ids)
            ->update([
                'score' => DB::raw('CASE trainee_id ' . implode(' ', $scoreCases) . ' END'),
                'result' => DB::raw('CASE trainee_id ' . implode(' ', $resultCases) . ' END'),
                'updated_at' => now(),
            ]);
    }
}
