<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incubator;
use App\Models\IncubationApplication;
use App\Models\IncubatedProject;
use App\Models\MentoringSession;
use App\Models\IncubationProgressReport;
use App\Models\IncubationProgram;
use App\Support\BranchDataScope;
use App\Support\IncubationAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class IncubatorController extends Controller
{
    // ── Incubators ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $q = Incubator::with(['governorate', 'branch', 'manager'])
            ->withCount(['applications', 'projects']);

        if (!IncubationAccess::hasViewAccess($request->user())) {
            $q->where('status', 'active');
        }

        if ($request->status)  $q->where('status', $request->status);
        if ($request->sector)  $q->where('sector', $request->sector);

        $branchId = $this->resolveBranchFilter($request);
        if ($branchId) {
            $q->where('branch_id', $branchId);
        }

        $q->orderBy('name');

        if ($request->filled('page') || $request->filled('per_page')) {
            $perPage = max(1, min((int) $request->integer('per_page', 50), 100));

            return response()->json($q->paginate($perPage));
        }

        return response()->json($q->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|unique:incubators,code',
            'description'    => 'nullable|string',
            'sector'         => 'nullable|string',
            'location'       => 'nullable|string',
            'governorate_id' => 'nullable|exists:governorates,id',
            'branch_id'      => 'nullable|exists:branches,id',
            'manager_user_id'=> 'nullable|exists:users,id',
            'phone'          => 'nullable|string',
            'email'          => 'nullable|email',
            'capacity'       => 'nullable|integer|min:1',
        ]);
        $data['created_by'] = Auth::id();
        $incubator = Incubator::create($data);
        return response()->json($incubator->load(['governorate', 'branch', 'manager']), 201);
    }

    public function show($id)
    {
        $incubator = Incubator::with([
            'governorate', 'branch', 'manager',
            'programs', 'projects.owner', 'projects.mentor',
        ])->withCount(['applications', 'projects'])->findOrFail($id);

        return response()->json($incubator);
    }

    public function update(Request $request, $id)
    {
        $incubator = Incubator::findOrFail($id);
        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'description'     => 'nullable|string',
            'sector'          => 'nullable|string',
            'location'        => 'nullable|string',
            'governorate_id'  => 'nullable|exists:governorates,id',
            'branch_id'       => 'nullable|exists:branches,id',
            'manager_user_id' => 'nullable|exists:users,id',
            'phone'           => 'nullable|string',
            'email'           => 'nullable|email',
            'capacity'        => 'nullable|integer|min:1',
            'status'          => 'nullable|in:active,inactive,suspended',
        ]);
        $incubator->update($data);
        return response()->json($incubator->fresh(['governorate', 'branch', 'manager']));
    }

    // ── Programs ─────────────────────────────────────────────────────────────

    public function storeProgram(Request $request, $incubatorId)
    {
        $incubator = Incubator::findOrFail($incubatorId);
        $data = $request->validate([
            'name'            => 'required|string',
            'description'     => 'nullable|string',
            'duration_months' => 'nullable|integer|min:1',
            'seats'           => 'nullable|integer|min:1',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'requirements'    => 'nullable|string',
        ]);
        $data['incubator_id'] = $incubator->id;
        $program = IncubationProgram::create($data);
        return response()->json($program, 201);
    }

    // ── Applications ─────────────────────────────────────────────────────────

    public function applications(Request $request, $incubatorId)
    {
        Incubator::findOrFail($incubatorId);
        $q = IncubationApplication::with(['applicant', 'program'])
            ->where('incubator_id', $incubatorId);

        if ($request->status) $q->where('status', $request->status);

        return response()->json($q->latest()->paginate(20));
    }

    public function allApplications(Request $request)
    {
        $q = IncubationApplication::with(['applicant', 'incubator', 'program'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->incubator_id, fn($q) => $q->where('incubator_id', $request->incubator_id));

        $branchId = $this->resolveBranchFilter($request);
        if ($branchId) {
            $q->whereHas('incubator', fn ($inc) => $inc->where('branch_id', $branchId));
        }

        return response()->json($q->latest()->paginate(20));
    }

    public function apply(Request $request)
    {
        $data = $request->validate([
            // أساسي
            'incubator_id'         => 'required|exists:incubators,id',
            'program_id'           => 'nullable|exists:incubation_programs,id',
            'project_name'         => 'required|string|max:255',
            'project_sector'       => 'nullable|string',
            'business_stage'       => 'required|in:idea,pre_seed,seed,early,growth',
            'project_description'  => 'required|string',
            'problem_statement'    => 'nullable|string',
            'target_market'        => 'nullable|string',
            'team_size'            => 'nullable|integer|min:1|max:500',
            'has_prototype'        => 'nullable|boolean',
            'has_revenue'          => 'nullable|boolean',
            // مشترك إضافي
            'funding_needed'       => 'nullable|numeric|min:0',
            'funding_stage'        => 'nullable|in:bootstrapped,seeking,funded',
            'expected_jobs'        => 'nullable|integer|min:0',
            'competitive_advantage'=> 'nullable|string',
            // تقني (اختياري — يُحفظ فقط إذا أُرسل)
            'tech_readiness_level' => 'nullable|integer|min:1|max:9',
            'revenue_model'        => 'nullable|in:saas,b2b,b2c,marketplace,hardware,freemium,other',
            'demo_url'             => 'nullable|url',
            'github_url'           => 'nullable|url',
            'has_ip'               => 'nullable|boolean',
            'ip_description'       => 'nullable|string',
            'tech_stack'           => 'nullable|array',
            'tech_stack.*'         => 'string|max:60',
            'target_platform'      => 'nullable|in:web,mobile,desktop,saas,api,embedded,other',
        ]);
        $data['applicant_user_id'] = Auth::id();

        $existing = IncubationApplication::where('incubator_id', $data['incubator_id'])
            ->where('applicant_user_id', Auth::id())
            ->whereIn('status', ['pending', 'under_review', 'accepted'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'لديك طلب قائم لهذه الحاضنة'], 422);
        }

        $app = IncubationApplication::create($data);
        return response()->json($app->load(['incubator', 'applicant']), 201);
    }

    public function showApplication($id)
    {
        $app = IncubationApplication::with(['incubator', 'program', 'applicant', 'reviewer', 'project'])->findOrFail($id);
        $this->authorize('view', $app);

        if (Gate::allows('viewSensitive', $app)) {
            return response()->json($app);
        }

        return response()->json($this->redactApplication($app));
    }

    public function myApplications(Request $request)
    {
        $apps = IncubationApplication::with(['incubator', 'program', 'project'])
            ->where('applicant_user_id', Auth::id())
            ->latest()->get();
        return response()->json($apps);
    }

    public function reviewApplication(Request $request, $id)
    {
        $app = IncubationApplication::with('incubator')->findOrFail($id);
        $this->authorize('review', $app);
        $data = $request->validate([
            'status'         => 'required|in:accepted,rejected,under_review',
            'reviewer_notes' => 'nullable|string',
        ]);

        $app->update([
            ...$data,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($data['status'] === 'accepted') {
            IncubatedProject::firstOrCreate(
                ['application_id' => $app->id],
                [
                    'incubator_id'   => $app->incubator_id,
                    'owner_user_id'  => $app->applicant_user_id,
                    'project_name'   => $app->project_name,
                    'start_date'     => now()->toDateString(),
                    'stage'          => 'seed',
                ]
            );
        }

        return response()->json($app->fresh(['incubator', 'applicant', 'project']));
    }

    // ── Projects ─────────────────────────────────────────────────────────────

    public function projects(Request $request)
    {
        $q = IncubatedProject::with(['incubator', 'owner', 'mentor'])
            ->withCount(['mentoringSessions', 'progressReports']);

        if ($request->status)      $q->where('status', $request->status);
        if ($request->incubator_id) $q->where('incubator_id', $request->incubator_id);

        $branchId = $this->resolveBranchFilter($request);
        if ($branchId) {
            $q->whereHas('incubator', fn ($inc) => $inc->where('branch_id', $branchId));
        }

        return response()->json($q->latest()->paginate(20));
    }

    public function showProject($id)
    {
        $project = IncubatedProject::with([
            'incubator', 'owner', 'mentor', 'application',
            'mentoringSessions.mentor', 'progressReports',
        ])->findOrFail($id);
        $this->authorize('view', $project);

        return response()->json($project);
    }

    public function myProject(Request $request)
    {
        $project = IncubatedProject::with(['incubator', 'mentor', 'mentoringSessions', 'progressReports'])
            ->where('owner_user_id', Auth::id())
            ->where('status', 'active')
            ->first();
        return response()->json($project);
    }

    public function updateProject(Request $request, $id)
    {
        $project = IncubatedProject::with('incubator')->findOrFail($id);
        $this->authorize('update', $project);
        $data = $request->validate([
            'stage'             => 'nullable|in:seed,early,growth,exit',
            'status'            => 'nullable|in:active,graduated,withdrawn,terminated',
            'mentor_user_id'    => 'nullable|exists:users,id',
            'expected_end_date' => 'nullable|date',
            'actual_end_date'   => 'nullable|date',
            'current_revenue'   => 'nullable|numeric|min:0',
            'current_employees' => 'nullable|integer|min:0',
            'notes'             => 'nullable|string',
        ]);
        $project->update($data);
        return response()->json($project->fresh(['incubator', 'owner', 'mentor']));
    }

    // ── Mentoring Sessions ───────────────────────────────────────────────────

    public function indexMentoringSessions(Request $request)
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $q = MentoringSession::with(['project:id,name,project_name,incubator_id', 'mentor:id,name'])
            ->when($request->filled('incubator_id'), fn ($q) => $q->whereHas(
                'project',
                fn ($p) => $p->where('incubator_id', $request->integer('incubator_id'))
            ))
            ->latest('session_date');

        return response()->json($q->paginate($perPage));
    }

    public function storeMentoringSession(Request $request, $projectId)
    {
        IncubatedProject::findOrFail($projectId);
        $data = $request->validate([
            'session_date'     => 'required|date',
            'duration_minutes' => 'nullable|integer|min:15',
            'topic'            => 'required|string|max:255',
            'notes'            => 'nullable|string',
            'action_items'     => 'nullable|string',
            'rating'           => 'nullable|integer|min:1|max:5',
            'status'           => 'nullable|in:scheduled,completed,cancelled',
        ]);
        $data['project_id']      = $projectId;
        $data['mentor_user_id']  = Auth::id();
        $session = MentoringSession::create($data);
        return response()->json($session->load('mentor'), 201);
    }

    public function myMentoringSessions(Request $request)
    {
        $sessions = MentoringSession::with(['project.owner', 'project.incubator'])
            ->where('mentor_user_id', Auth::id())
            ->latest('session_date')->get();
        return response()->json($sessions);
    }

    // ── Progress Reports ─────────────────────────────────────────────────────

    public function storeProgressReport(Request $request, $projectId)
    {
        $project = IncubatedProject::with('incubator')->findOrFail($projectId);
        $this->authorize('submitProgressReport', $project);

        $data = $request->validate([
            'period_type'    => 'required|in:monthly,quarterly',
            'period_label'   => 'required|string|max:50',
            'revenue'        => 'nullable|numeric|min:0',
            'employees'      => 'nullable|integer|min:0',
            'customers'      => 'nullable|integer|min:0',
            'achievements'   => 'nullable|string',
            'challenges'     => 'nullable|string',
            'next_steps'     => 'nullable|string',
            'overall_rating' => 'nullable|integer|min:1|max:10',
        ]);
        $data['project_id']    = $projectId;
        $data['submitted_by']  = Auth::id();
        $report = IncubationProgressReport::create($data);
        return response()->json($report, 201);
    }

    // ── Dashboard Stats ───────────────────────────────────────────────────────

    public function stats(Request $request)
    {
        $branchId = $this->resolveBranchFilter($request);

        $incubatorsQ = Incubator::query();
        if ($branchId) {
            $incubatorsQ->where('branch_id', $branchId);
        }

        $incubatorIds = (clone $incubatorsQ)->pluck('id');
        $now = now();

        return response()->json([
            'incubators_total'   => (clone $incubatorsQ)->count(),
            'incubators_active'  => (clone $incubatorsQ)->where('status', 'active')->count(),
            'applications_total' => IncubationApplication::whereIn('incubator_id', $incubatorIds)->count(),
            'applications_pending' => IncubationApplication::whereIn('incubator_id', $incubatorIds)->where('status', 'pending')->count(),
            'projects_active'    => IncubatedProject::whereIn('incubator_id', $incubatorIds)->where('status', 'active')->count(),
            'projects_graduated' => IncubatedProject::whereIn('incubator_id', $incubatorIds)->where('status', 'graduated')->count(),
            'graduated'          => IncubatedProject::whereIn('incubator_id', $incubatorIds)->where('status', 'graduated')->count(),
            'sessions_total'     => MentoringSession::whereHas('project', fn($q) => $q->whereIn('incubator_id', $incubatorIds))->count(),
            'sessions_this_month' => MentoringSession::whereHas('project', fn($q) => $q->whereIn('incubator_id', $incubatorIds))
                ->whereYear('session_date', $now->year)
                ->whereMonth('session_date', $now->month)
                ->count(),
            'reports' => IncubationProgressReport::whereHas('project', fn($q) => $q->whereIn('incubator_id', $incubatorIds))->count(),
            'active_programs' => IncubationProgram::whereIn('incubator_id', $incubatorIds)->where('status', 'active')->count(),
            'total_revenue' => (float) IncubatedProject::whereIn('incubator_id', $incubatorIds)->sum('current_revenue'),
        ]);
    }

    private function resolveBranchFilter(Request $request): ?int
    {
        $user = $request->user();

        if (BranchDataScope::isBranchManager($user)) {
            return $user->branch_id ? (int) $user->branch_id : null;
        }

        if ($request->filled('branch_id')) {
            return $request->integer('branch_id');
        }

        return null;
    }

    private function redactApplication(IncubationApplication $app): array
    {
        $data = $app->toArray();
        unset($data['reviewer_notes']);

        if ($app->relationLoaded('applicant') && $app->applicant) {
            $data['applicant'] = [
                'id' => $app->applicant->id,
                'name' => $app->applicant->name,
            ];
        }

        return $data;
    }
}
