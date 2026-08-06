<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgramApprovalLog;
use App\Models\ProgramModule;
use App\Models\ProgramOutcome;
use App\Models\ProgramServiceLink;
use App\Models\TrainingCourse;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramExecution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProgramBankController extends Controller
{
    // ══════════════════════════════════════════
    // الإحصائيات الرئيسية للداشبورد
    // ══════════════════════════════════════════
    public function stats(): JsonResponse
    {
        $base = TrainingProgram::query();

        $stats = [
            'approved'              => (clone $base)->where('bank_status', 'approved')->count(),
            'under_review'          => (clone $base)->whereIn('bank_status', ['under_technical_review', 'under_admin_review'])->count(),
            'draft'                 => (clone $base)->where('bank_status', 'draft')->count(),
            'suspended'             => (clone $base)->where('bank_status', 'suspended')->count(),
            'total'                 => (clone $base)->count(),
            'needs_update'          => (clone $base)->where('bank_status', 'approved')
                                        ->where('updated_at', '<', now()->subMonths(6))->count(),
            'linked_needs_map'      => TrainingProgram::whereHas('serviceLinks', fn($q) => $q->where('service_type', 'needs_map'))->count(),
            'linked_finance'        => TrainingProgram::whereHas('serviceLinks', fn($q) => $q->where('service_type', 'finance'))->count(),
            'linked_incubators'     => TrainingProgram::whereHas('serviceLinks', fn($q) => $q->where('service_type', 'incubators'))->count(),
            'most_executed'         => TrainingProgramExecution::select('program_id', DB::raw('count(*) as total'))
                                        ->groupBy('program_id')->orderByDesc('total')
                                        ->with('program:id,name,title,code,bank_status')
                                        ->limit(5)->get(),
            'by_type'               => TrainingProgram::select('type', DB::raw('count(*) as total'))
                                        ->whereNotNull('type')->groupBy('type')->get(),
        ];

        return response()->json(['data' => $stats]);
    }

    // ══════════════════════════════════════════
    // قائمة البرامج
    // ══════════════════════════════════════════
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 20), 100);

        $programs = TrainingProgram::query()
            ->withCount(['modules', 'outcomes', 'courses as executions_count', 'kits'])
            ->when($request->filled('bank_status'), fn($q) => $q->where('bank_status', $request->input('bank_status')))
            ->when($request->filled('type'),        fn($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('level'),       fn($q) => $q->where('level', $request->input('level')))
            ->when($request->filled('sector'),      fn($q) => $q->where('sector', $request->input('sector')))
            ->when($request->filled('grants_certificate'), fn($q) => $q->where('grants_certificate', filter_var($request->input('grants_certificate'), FILTER_VALIDATE_BOOLEAN)))
            ->search($request->input('search'))
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $programs->map(fn($p) => $this->formatProgram($p)),
            'meta' => [
                'total'        => $programs->total(),
                'per_page'     => $programs->perPage(),
                'current_page' => $programs->currentPage(),
                'last_page'    => $programs->lastPage(),
            ],
        ]);
    }

    // ══════════════════════════════════════════
    // عرض برنامج واحد
    // ══════════════════════════════════════════
    public function show(int $id): JsonResponse
    {
        $program = TrainingProgram::with([
            'modules', 'outcomes', 'serviceLinks',
            'approvalLogs.user:id,name',
            'executions.course:id,title,start_date,status',
            'creator:id,name',
            'approver:id,name',
            'kits:id,name,code',
        ])->withCount(['modules', 'outcomes', 'courses as executions_count'])->findOrFail($id);

        return response()->json(['data' => $this->formatProgramFull($program)]);
    }

    // ══════════════════════════════════════════
    // إنشاء برنامج
    // ══════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'title'                  => 'nullable|string|max:255',
            'code'                   => 'required|string|max:50|unique:training_programs,code',
            'description'            => 'nullable|string',
            'type'                   => ['nullable', Rule::in(array_keys(TrainingProgram::$TYPE_LABELS))],
            'sector'                 => 'nullable|string|max:100',
            'target_audience'        => 'nullable|string|max:255',
            'level'                  => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'delivery_mode'          => ['nullable', Rule::in(['in_person', 'online', 'blended'])],
            'suggested_hours'        => 'nullable|integer|min:0|max:9999',
            'suggested_sessions'     => 'nullable|integer|min:0|max:999',
            'prerequisites'          => 'nullable|string',
            'outcomes_summary'       => 'nullable|string',
            'grants_certificate'     => 'boolean',
            'requires_final_exam'    => 'boolean',
            'requires_project'       => 'boolean',
            'min_attendance_percent' => 'integer|min:0|max:100',
            'passing_score'          => 'integer|min:0|max:100',
        ]);

        $validated['bank_status'] = 'draft';
        $validated['status']      = 'active';
        $validated['is_active']   = true;
        $validated['created_by']  = Auth::id();
        $validated['slug']        = Str::slug($validated['name'] . '-' . $validated['code']);

        $program = TrainingProgram::create($validated);

        $this->logAction($program->id, null, 'draft', 'created');

        return response()->json(['data' => $this->formatProgram($program), 'message' => 'تم إنشاء البرنامج بنجاح'], 201);
    }

    // ══════════════════════════════════════════
    // تعديل برنامج
    // ══════════════════════════════════════════
    public function update(Request $request, int $id): JsonResponse
    {
        $program = TrainingProgram::findOrFail($id);

        if (!in_array($program->bank_status, ['draft', 'under_technical_review'])) {
            return response()->json(['message' => 'لا يمكن تعديل برنامج بهذه الحالة'], 422);
        }

        $validated = $request->validate([
            'name'                   => 'sometimes|required|string|max:255',
            'title'                  => 'nullable|string|max:255',
            'code'                   => ['sometimes', 'required', 'string', 'max:50', Rule::unique('training_programs', 'code')->ignore($id)],
            'description'            => 'nullable|string',
            'type'                   => ['nullable', Rule::in(array_keys(TrainingProgram::$TYPE_LABELS))],
            'sector'                 => 'nullable|string|max:100',
            'target_audience'        => 'nullable|string|max:255',
            'level'                  => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'delivery_mode'          => ['nullable', Rule::in(['in_person', 'online', 'blended'])],
            'suggested_hours'        => 'nullable|integer|min:0|max:9999',
            'suggested_sessions'     => 'nullable|integer|min:0|max:999',
            'prerequisites'          => 'nullable|string',
            'outcomes_summary'       => 'nullable|string',
            'grants_certificate'     => 'boolean',
            'requires_final_exam'    => 'boolean',
            'requires_project'       => 'boolean',
            'min_attendance_percent' => 'integer|min:0|max:100',
            'passing_score'          => 'integer|min:0|max:100',
        ]);

        $program->update($validated);

        return response()->json(['data' => $this->formatProgram($program), 'message' => 'تم تحديث البرنامج بنجاح']);
    }

    // ══════════════════════════════════════════
    // حذف برنامج (مسودة فقط)
    // ══════════════════════════════════════════
    public function destroy(int $id): JsonResponse
    {
        $program = TrainingProgram::findOrFail($id);
        if ($program->bank_status !== 'draft') {
            return response()->json(['message' => 'يمكن حذف المسودات فقط'], 422);
        }
        $program->delete();
        return response()->json(['message' => 'تم حذف البرنامج']);
    }

    // ══════════════════════════════════════════
    // نسخ برنامج
    // ══════════════════════════════════════════
    public function duplicate(int $id): JsonResponse
    {
        $program = TrainingProgram::with(['modules', 'outcomes', 'serviceLinks'])->findOrFail($id);

        $newCode = $program->code . '-COPY-' . now()->format('His');

        $new = $program->replicate();
        $new->code        = $newCode;
        $new->slug        = Str::slug($program->name . '-' . $newCode);
        $new->bank_status = 'draft';
        $new->created_by  = Auth::id();
        $new->approved_by = null;
        $new->approved_at = null;
        $new->suspended_at = null;
        $new->archived_at  = null;
        $new->save();

        foreach ($program->modules as $mod) {
            $new->modules()->create($mod->only(['title','description','hours','sort_order','objectives','activities','required_tools','evaluation_method']));
        }
        foreach ($program->outcomes as $out) {
            $new->outcomes()->create($out->only(['title','description','sort_order']));
        }
        foreach ($program->serviceLinks as $lnk) {
            $new->serviceLinks()->create($lnk->only(['service_type','service_reference_id','notes']));
        }

        $this->logAction($new->id, null, 'draft', 'created', 'نسخة من برنامج #' . $id);

        return response()->json(['data' => $this->formatProgram($new), 'message' => 'تم نسخ البرنامج'], 201);
    }

    // ══════════════════════════════════════════
    // إرسال للاعتماد / الاعتماد / الإيقاف
    // ══════════════════════════════════════════
    public function transition(Request $request, int $id): JsonResponse
    {
        $program = TrainingProgram::findOrFail($id);
        $action  = $request->input('action');
        $notes   = $request->input('notes');

        $transitions = [
            'submit'           => ['from' => ['draft'],                       'to' => 'under_technical_review'],
            'technical_approve'=> ['from' => ['under_technical_review'],      'to' => 'under_admin_review'],
            'technical_reject' => ['from' => ['under_technical_review'],      'to' => 'draft'],
            'admin_approve'    => ['from' => ['under_admin_review'],          'to' => 'approved'],
            'admin_reject'     => ['from' => ['under_admin_review'],          'to' => 'under_technical_review'],
            'suspend'          => ['from' => ['approved'],                    'to' => 'suspended'],
            'reactivate'       => ['from' => ['suspended'],                   'to' => 'approved'],
            'archive'          => ['from' => ['approved', 'suspended'],       'to' => 'archived'],
        ];

        if (!isset($transitions[$action])) {
            return response()->json(['message' => 'إجراء غير صالح'], 422);
        }

        $t = $transitions[$action];
        if (!in_array($program->bank_status, $t['from'])) {
            return response()->json(['message' => 'لا يمكن تنفيذ هذا الإجراء على البرنامج بحالته الحالية'], 422);
        }

        $oldStatus = $program->bank_status;
        $program->bank_status = $t['to'];

        if ($t['to'] === 'approved') {
            $program->approved_by = Auth::id();
            $program->approved_at = now();
        }
        if ($t['to'] === 'suspended')  $program->suspended_at = now();
        if ($t['to'] === 'archived')   $program->archived_at  = now();

        $program->save();
        $this->logAction($program->id, $oldStatus, $t['to'], $action, $notes);

        return response()->json(['data' => $this->formatProgram($program), 'message' => 'تم تنفيذ الإجراء بنجاح']);
    }

    // ══════════════════════════════════════════
    // إدارة المحاور
    // ══════════════════════════════════════════
    public function storeModule(Request $request, int $id): JsonResponse
    {
        $program = TrainingProgram::findOrFail($id);
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'hours'             => 'nullable|integer|min:0',
            'sort_order'        => 'nullable|integer|min:0',
            'objectives'        => 'nullable|string',
            'activities'        => 'nullable|string',
            'required_tools'    => 'nullable|string',
            'evaluation_method' => 'nullable|string|max:255',
        ]);
        $validated['program_id'] = $id;
        $module = ProgramModule::create($validated);
        return response()->json(['data' => $module, 'message' => 'تم إضافة المحور'], 201);
    }

    public function updateModule(Request $request, int $id, int $moduleId): JsonResponse
    {
        $module = ProgramModule::where('program_id', $id)->findOrFail($moduleId);
        $module->update($request->only(['title','description','hours','sort_order','objectives','activities','required_tools','evaluation_method']));
        return response()->json(['data' => $module, 'message' => 'تم تحديث المحور']);
    }

    public function destroyModule(int $id, int $moduleId): JsonResponse
    {
        ProgramModule::where('program_id', $id)->findOrFail($moduleId)->delete();
        return response()->json(['message' => 'تم حذف المحور']);
    }

    public function reorderModules(Request $request, int $id): JsonResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        foreach ($request->input('order') as $sortOrder => $moduleId) {
            ProgramModule::where('program_id', $id)->where('id', $moduleId)->update(['sort_order' => $sortOrder]);
        }
        return response()->json(['message' => 'تم حفظ الترتيب']);
    }

    // ══════════════════════════════════════════
    // إدارة المخرجات
    // ══════════════════════════════════════════
    public function storeOutcome(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
        $validated['program_id'] = $id;
        $outcome = ProgramOutcome::create($validated);
        return response()->json(['data' => $outcome, 'message' => 'تم إضافة المخرج'], 201);
    }

    public function updateOutcome(Request $request, int $id, int $outcomeId): JsonResponse
    {
        $outcome = ProgramOutcome::where('program_id', $id)->findOrFail($outcomeId);
        $outcome->update($request->only(['title','description','sort_order']));
        return response()->json(['data' => $outcome, 'message' => 'تم تحديث المخرج']);
    }

    public function destroyOutcome(int $id, int $outcomeId): JsonResponse
    {
        ProgramOutcome::where('program_id', $id)->findOrFail($outcomeId)->delete();
        return response()->json(['message' => 'تم حذف المخرج']);
    }

    // ══════════════════════════════════════════
    // روابط الخدمات
    // ══════════════════════════════════════════
    public function syncServiceLinks(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'links'                 => 'required|array',
            'links.*.service_type'  => ['required', Rule::in(array_keys(ProgramServiceLink::$SERVICE_LABELS))],
            'links.*.notes'         => 'nullable|string',
        ]);

        ProgramServiceLink::where('program_id', $id)->delete();

        foreach ($request->input('links') as $link) {
            ProgramServiceLink::create([
                'program_id'   => $id,
                'service_type' => $link['service_type'],
                'notes'        => $link['notes'] ?? null,
            ]);
        }

        return response()->json(['message' => 'تم حفظ روابط الخدمات']);
    }

    // ══════════════════════════════════════════
    // إنشاء دورة من برنامج
    // ══════════════════════════════════════════
    public function createCourseFromProgram(Request $request, int $id): JsonResponse
    {
        $program = TrainingProgram::with(['modules', 'outcomes'])->findOrFail($id);

        if ($program->bank_status !== 'approved') {
            return response()->json(['message' => 'يمكن إنشاء دورات من البرامج المعتمدة فقط'], 422);
        }

        $request->validate([
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'training_center_id' => 'required|exists:training_centers,id',
            'trainer_id'       => 'nullable|exists:trainers,id',
            'capacity'         => 'nullable|integer|min:1|max:999',
            'location'         => 'nullable|string|max:255',
            'governorate'      => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $courseData = [
                'training_program_id' => $program->id,
                'title'               => $program->title ?: $program->name,
                'description'         => $program->description,
                'hours'               => $program->suggested_hours,
                'sessions'            => $program->suggested_sessions,
                'grants_certificate'  => $program->grants_certificate,
                'min_attendance_percent' => $program->min_attendance_percent,
                'passing_score'       => $program->passing_score,
                'delivery_mode'       => $program->delivery_mode,
                'status'              => 'upcoming',
                'start_date'          => $request->input('start_date'),
                'end_date'            => $request->input('end_date'),
                'training_center_id'  => $request->input('training_center_id'),
                'trainer_id'          => $request->input('trainer_id'),
                'capacity'            => $request->input('capacity', 30),
                'location'            => $request->input('location'),
                'governorate'         => $request->input('governorate'),
                'created_by'          => Auth::id(),
            ];

            // نأخذ فقط الأعمدة الموجودة في جدول training_courses
            $course = TrainingCourse::create(
                array_intersect_key($courseData, array_flip((new TrainingCourse)->getFillable()))
            );

            TrainingProgramExecution::create([
                'program_id' => $program->id,
                'course_id'  => $course->id,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'data'    => ['course_id' => $course->id],
                'message' => 'تم إنشاء الدورة بنجاح من البرنامج المعتمد',
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'فشل إنشاء الدورة: ' . $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════
    // تقارير
    // ══════════════════════════════════════════
    public function reports(Request $request): JsonResponse
    {
        $type = $request->input('type', 'by_status');

        $data = match ($type) {
            'by_status'    => TrainingProgram::select('bank_status', DB::raw('count(*) as total'))->groupBy('bank_status')->get(),
            'by_type'      => TrainingProgram::select('type', DB::raw('count(*) as total'))->whereNotNull('type')->groupBy('type')->get(),
            'most_executed'=> TrainingProgramExecution::select('program_id', DB::raw('count(*) as total'))
                                ->groupBy('program_id')->orderByDesc('total')
                                ->with('program:id,name,title,code')->limit(10)->get(),
            'approved_not_executed' => TrainingProgram::where('bank_status', 'approved')
                                ->doesntHave('executions')->orderByDesc('approved_at')->get(['id','name','title','code','approved_at']),
            'needs_update' => TrainingProgram::where('bank_status', 'approved')
                                ->where('updated_at', '<', now()->subMonths(6))
                                ->orderBy('updated_at')->get(['id','name','title','code','updated_at']),
            'linked_needs' => TrainingProgram::whereHas('serviceLinks', fn($q) => $q->where('service_type', 'needs_map'))
                                ->where('bank_status', 'approved')->get(['id','name','title','code']),
            default        => [],
        };

        return response()->json(['data' => $data, 'report_type' => $type]);
    }

    // ══════════════════════════════════════════
    // مساعدات خاصة
    // ══════════════════════════════════════════
    private function formatProgram(TrainingProgram $p): array
    {
        return [
            'id'                  => $p->id,
            'name'                => $p->name,
            'title'               => $p->title,
            'display_name'        => $p->title ?: $p->name,
            'code'                => $p->code,
            'description'         => $p->description,
            'type'                => $p->type,
            'type_label'          => $p->type_label,
            'sector'              => $p->sector,
            'target_audience'     => $p->target_audience,
            'level'               => $p->level,
            'level_label'         => $p->level_label,
            'delivery_mode'       => $p->delivery_mode,
            'delivery_label'      => $p->delivery_label,
            'suggested_hours'     => $p->suggested_hours,
            'suggested_sessions'  => $p->suggested_sessions,
            'grants_certificate'  => $p->grants_certificate,
            'requires_final_exam' => $p->requires_final_exam,
            'requires_project'    => $p->requires_project,
            'min_attendance_percent' => $p->min_attendance_percent,
            'passing_score'       => $p->passing_score,
            'bank_status'         => $p->bank_status,
            'bank_status_label'   => $p->bank_status_label,
            'modules_count'       => $p->modules_count ?? 0,
            'outcomes_count'      => $p->outcomes_count ?? 0,
            'executions_count'    => $p->executions_count ?? 0,
            'approved_at'         => $p->approved_at?->toDateString(),
            'updated_at'          => $p->updated_at?->toDateTimeString(),
            'created_at'          => $p->created_at?->toDateTimeString(),
        ];
    }

    private function formatProgramFull(TrainingProgram $p): array
    {
        $base = $this->formatProgram($p);
        return array_merge($base, [
            'prerequisites'   => $p->prerequisites,
            'outcomes_summary'=> $p->outcomes_summary,
            'modules'         => $p->modules,
            'outcomes'        => $p->outcomes,
            'service_links'   => $p->serviceLinks->map(fn($l) => [
                'id'           => $l->id,
                'service_type' => $l->service_type,
                'service_label'=> $l->service_label,
                'notes'        => $l->notes,
            ]),
            'approval_logs'   => $p->approvalLogs->map(fn($l) => [
                'action'      => $l->action,
                'from_status' => $l->from_status,
                'to_status'   => $l->to_status,
                'notes'       => $l->notes,
                'user'        => $l->user?->name,
                'date'        => $l->created_at?->toDateTimeString(),
            ]),
            'executions'      => $p->executions->map(fn($e) => [
                'course_id'    => $e->course_id,
                'course_title' => $e->course?->title,
                'start_date'   => $e->course?->start_date,
                'status'       => $e->course?->status,
                'created_at'   => $e->created_at?->toDateString(),
            ]),
            'creator'         => $p->creator?->name,
            'approver'        => $p->approver?->name,
        ]);
    }

    private function logAction(int $programId, ?string $from, string $to, string $action, ?string $notes = null): void
    {
        ProgramApprovalLog::create([
            'program_id'  => $programId,
            'from_status' => $from,
            'to_status'   => $to,
            'action'      => $action,
            'notes'       => $notes,
            'user_id'     => Auth::id(),
        ]);
    }
}
