<?php

namespace App\Services\Admin;

use App\Models\Branch;
use App\Models\Certificate;
use App\Models\ConsultingRequest;
use App\Models\FundingApplication;
use App\Models\IncubatedProject;
use App\Models\IncubationApplication;
use App\Models\IncubationProgram;
use App\Models\IncubationProgressReport;
use App\Models\Incubator;
use App\Models\MentoringSession;
use App\Models\Need;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Support\BranchDataScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BranchDashboardService
{
    public function __construct(private NationalDashboardService $nationalDashboard) {}

    public function resolveBranchId(User $user, ?int $requestedBranchId): int
    {
        if (BranchDataScope::isBranchManager($user)) {
            if (!$user->branch_id) {
                abort(422, 'لا يوجد فرع مرتبط بهذا الحساب.');
            }

            if ($requestedBranchId && (int) $requestedBranchId !== (int) $user->branch_id) {
                abort(403, 'غير مصرح بعرض بيانات فرع آخر.');
            }

            return (int) $user->branch_id;
        }

        if (!$requestedBranchId) {
            abort(422, 'branch_id مطلوب.');
        }

        Branch::query()->findOrFail($requestedBranchId);

        return $requestedBranchId;
    }

    public function canViewNationalOverview(User $user): bool
    {
        return BranchDataScope::hasNationalReadAccess($user)
            || $user->hasRole(['admin', 'super_admin', 'system_admin']);
    }

    /** @return list<array<string, mixed>> */
    public function branchesOverview(): array
    {
        return Cache::remember('branch_dashboard:overview:v1', 120, fn () => $this->buildBranchesOverview());
    }

    /** @return list<array<string, mixed>> */
    private function buildBranchesOverview(): array
    {
        $branches = Branch::query()
            ->with(['governorate:id,name_ar,code'])
            ->orderBy('name')
            ->get();

        if ($branches->isEmpty()) {
            return [];
        }

        $branchIds = $branches->pluck('id');

        $incubatorCounts = Incubator::query()
            ->select('branch_id', DB::raw('COUNT(*) as aggregate'))
            ->whereIn('branch_id', $branchIds)
            ->groupBy('branch_id')
            ->pluck('aggregate', 'branch_id');

        $projectCounts = IncubatedProject::query()
            ->join('incubators', 'incubated_projects.incubator_id', '=', 'incubators.id')
            ->whereIn('incubators.branch_id', $branchIds)
            ->where('incubated_projects.status', 'active')
            ->select('incubators.branch_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('incubators.branch_id')
            ->pluck('aggregate', 'branch_id');

        $courseCounts = TrainingCourse::query()
            ->select('branch_id', DB::raw('COUNT(*) as aggregate'))
            ->whereIn('branch_id', $branchIds)
            ->groupBy('branch_id')
            ->pluck('aggregate', 'branch_id');

        $trainerCounts = Trainer::query()
            ->select('branch_id', DB::raw('COUNT(*) as aggregate'))
            ->whereIn('branch_id', $branchIds)
            ->groupBy('branch_id')
            ->pluck('aggregate', 'branch_id');

        return $branches->map(fn (Branch $branch) => [
            'id' => $branch->id,
            'name' => $branch->name,
            'code' => $branch->code,
            'governorate_name' => $branch->governorate?->name_ar,
            'governorate_code' => $branch->governorate?->code,
            'is_active' => (bool) $branch->is_active,
            'metrics' => [
                'incubators' => (int) ($incubatorCounts[$branch->id] ?? 0),
                'projects_active' => (int) ($projectCounts[$branch->id] ?? 0),
                'courses' => (int) ($courseCounts[$branch->id] ?? 0),
                'trainers' => (int) ($trainerCounts[$branch->id] ?? 0),
            ],
        ])->values()->all();
    }

    /** @return array<string, mixed> */
    public function detail(int $branchId): array
    {
        $branch = Branch::query()
            ->with(['governorate:id,name_ar,code', 'manager:id,name,email'])
            ->findOrFail($branchId);

        $incubatorIds = Incubator::query()->where('branch_id', $branchId)->pluck('id');
        $training = $this->nationalDashboard->branchMetrics($branchId);
        $incubation = $this->incubationMetrics($branchId, $incubatorIds);

        return [
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'governorate_name' => $branch->governorate?->name_ar,
                'governorate_code' => $branch->governorate?->code,
                'is_active' => (bool) $branch->is_active,
                'manager' => $branch->manager ? [
                    'id' => $branch->manager->id,
                    'name' => $branch->manager->name,
                    'email' => $branch->manager->email,
                ] : null,
            ],
            'kpis' => array_merge($training, $incubation, [
                'consulting_requests_total' => ConsultingRequest::query()->where('branch_id', $branchId)->count(),
                'funding_applications_total' => FundingApplication::query()->where('branch_id', $branchId)->count(),
                'needs_total' => Need::query()->where('branch_id', $branchId)->count(),
            ]),
            'incubators' => Incubator::query()
                ->where('branch_id', $branchId)
                ->withCount(['projects as active_projects_count' => fn ($q) => $q->where('status', 'active')])
                ->orderBy('name')
                ->limit(8)
                ->get(['id', 'name', 'sector', 'status', 'governorate_id'])
                ->map(fn (Incubator $inc) => [
                    'id' => $inc->id,
                    'name' => $inc->name,
                    'sector' => $inc->sector,
                    'status' => $inc->status,
                    'active_projects_count' => $inc->active_projects_count,
                ])
                ->values()
                ->all(),
            'pending_applications' => IncubationApplication::query()
                ->with(['applicant:id,name', 'incubator:id,name'])
                ->whereIn('incubator_id', $incubatorIds)
                ->where('status', 'pending')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (IncubationApplication $app) => [
                    'id' => $app->id,
                    'project_name' => $app->project_name,
                    'status' => $app->status,
                    'applicant' => $app->applicant ? ['id' => $app->applicant->id, 'name' => $app->applicant->name] : null,
                    'incubator' => $app->incubator ? ['id' => $app->incubator->id, 'name' => $app->incubator->name] : null,
                ])
                ->values()
                ->all(),
            'active_projects' => IncubatedProject::query()
                ->with(['incubator:id,name'])
                ->whereIn('incubator_id', $incubatorIds)
                ->where('status', 'active')
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (IncubatedProject $project) => [
                    'id' => $project->id,
                    'name' => $project->project_name,
                    'project_name' => $project->project_name,
                    'stage' => $project->stage,
                    'status' => $project->status,
                    'revenue' => $project->current_revenue,
                    'employees_count' => $project->current_employees,
                    'incubator' => $project->incubator ? ['id' => $project->incubator->id, 'name' => $project->incubator->name] : null,
                ])
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, int|float> */
    private function incubationMetrics(int $branchId, $incubatorIds): array
    {
        $incubatorsQ = Incubator::query()->where('branch_id', $branchId);
        $now = Carbon::now();

        return [
            'incubators_total' => (clone $incubatorsQ)->count(),
            'incubators_active' => (clone $incubatorsQ)->where('status', 'active')->count(),
            'applications_pending' => IncubationApplication::query()
                ->whereIn('incubator_id', $incubatorIds)
                ->where('status', 'pending')
                ->count(),
            'projects_active' => IncubatedProject::query()
                ->whereIn('incubator_id', $incubatorIds)
                ->where('status', 'active')
                ->count(),
            'graduated' => IncubatedProject::query()
                ->whereIn('incubator_id', $incubatorIds)
                ->where('status', 'graduated')
                ->count(),
            'sessions_this_month' => MentoringSession::query()
                ->whereHas('project', fn ($q) => $q->whereIn('incubator_id', $incubatorIds))
                ->whereYear('session_date', $now->year)
                ->whereMonth('session_date', $now->month)
                ->count(),
            'reports' => IncubationProgressReport::query()
                ->whereHas('project', fn ($q) => $q->whereIn('incubator_id', $incubatorIds))
                ->count(),
            'active_programs' => IncubationProgram::query()
                ->whereIn('incubator_id', $incubatorIds)
                ->where('status', 'active')
                ->count(),
            'total_revenue' => (float) IncubatedProject::query()
                ->whereIn('incubator_id', $incubatorIds)
                ->sum('current_revenue'),
        ];
    }
}
