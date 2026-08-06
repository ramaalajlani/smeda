<?php



namespace App\Services\Admin;



use App\Models\Agreement;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Certificate;
use App\Models\CourseRegistrationRequest;
use App\Models\FinancialRecord;
use App\Models\Governorate;

use App\Models\Trainee;

use App\Models\TraineeRegistrationRequest;

use App\Models\Trainer;

use App\Models\TrainerRegistrationRequest;

use App\Models\TrainingCenterRegistrationRequest;

use App\Models\TrainingCourse;

use App\Models\User;

use App\Support\BranchDataScope;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;



class NationalDashboardService

{

    public function nationalSummary(): array

    {

        $coursesByGov = $this->countsByGovernorate('training_courses');
        $trainersByGov = $this->countsByGovernorate('trainers');
        $traineesByGov = $this->countsByGovernorate('trainees');
        $certificatesByGov = $this->countsByGovernorate('certificates');
        $certificatesApprovedByGov = $this->countsByGovernorate('certificates', fn ($q) => $q->where('status', 'approved'));
        $pendingByGov = $this->pendingRequestsGroupedByGovernorate();

        $governorateStats = Governorate::query()

            ->with(['branches:id,governorate_id,name,code,is_active'])

            ->withCount('branches')

            ->orderBy('name_ar')

            ->get()

            ->map(fn (Governorate $gov) => [

                'id' => $gov->id,

                'name_ar' => $gov->name_ar,

                'code' => $gov->code,

                'branches_count' => $gov->branches_count,

                'courses_count' => (int) ($coursesByGov[$gov->id] ?? 0),

                'trainers_count' => (int) ($trainersByGov[$gov->id] ?? 0),

                'trainees_count' => (int) ($traineesByGov[$gov->id] ?? 0),

                'certificates_count' => (int) ($certificatesByGov[$gov->id] ?? 0),

                'certificates_approved' => (int) ($certificatesApprovedByGov[$gov->id] ?? 0),

                'registration_requests_pending' => (int) ($pendingByGov[$gov->id] ?? 0),

            ]);



        return [

            'governorates_count' => Governorate::query()->count(),

            'branches_count' => Branch::query()->count(),

            'branches_active' => Branch::query()->where('is_active', true)->count(),

            'courses_total' => TrainingCourse::query()->count(),

            'registration_requests_pending' => $this->pendingRegistrationRequestsTotal(),
            'agreements_count' => Agreement::query()->count(),
            'financial_records_count' => FinancialRecord::query()->count(),

            'governorate_stats' => $governorateStats,

            'comparison' => $governorateStats,

            'branch_activities' => AuditLog::query()

                ->with('user:id,name,email,branch_id')

                ->whereIn('module', ['users', 'training', 'certificates', 'registration_requests', 'branches', 'agreements', 'finance', 'audit'])

                ->latest('id')

                ->limit(15)

                ->get(['id', 'user_id', 'action', 'module', 'description', 'created_at']),

        ];

    }



    public function branchSummary(User $user): array

    {

        if (!BranchDataScope::isBranchManager($user) || !$user->branch_id) {

            return ['message' => 'لا توجد بيانات فرع مرتبطة بهذا الحساب.'];

        }



        $branchId = $user->branch_id;

        return array_merge([
            'branch_id' => $branchId,
            'branch_name' => $user->branch?->name,
            'governorate_name' => $user->governorate?->name_ar ?? $user->branch?->governorate?->name_ar,
        ], $this->branchMetrics($branchId));

    }

    /** @return array<string, int> */
    public function branchMetrics(int $branchId): array
    {
        return [
            'courses' => TrainingCourse::query()->where('branch_id', $branchId)->count(),
            'courses_active' => TrainingCourse::query()->where('branch_id', $branchId)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'courses_completed' => TrainingCourse::query()->where('branch_id', $branchId)->where('status', 'completed')->count(),
            'trainers' => Trainer::query()->where('branch_id', $branchId)->count(),
            'trainees' => Trainee::query()->where('branch_id', $branchId)->count(),
            'certificates_total' => Certificate::query()->where('branch_id', $branchId)->count(),
            'certificates_approved' => Certificate::query()->where('branch_id', $branchId)->where('status', 'approved')->count(),
            'certificates_pending' => Certificate::query()->where('branch_id', $branchId)->where('status', '!=', 'approved')->count(),
            'registration_requests_pending' => $this->pendingRegistrationRequestsForBranch($branchId),
            'financial_records_count' => FinancialRecord::query()->where('branch_id', $branchId)->count(),
        ];
    }



    private function pendingRegistrationRequestsTotal(): int

    {

        return TrainingCenterRegistrationRequest::query()->where('status', 'pending')->count()

            + TrainerRegistrationRequest::query()->where('status', 'pending')->count()

            + TraineeRegistrationRequest::query()->where('status', 'pending')->count()

            + CourseRegistrationRequest::query()->whereIn('status', ['submitted', 'guardian_confirmed'])->count();

    }



    private function pendingRegistrationRequestsForBranch(int $branchId): int

    {

        return TrainingCenterRegistrationRequest::query()->where('branch_id', $branchId)->where('status', 'pending')->count()

            + TrainerRegistrationRequest::query()->where('branch_id', $branchId)->where('status', 'pending')->count()

            + TraineeRegistrationRequest::query()->where('branch_id', $branchId)->where('status', 'pending')->count()

            + CourseRegistrationRequest::query()->where('branch_id', $branchId)->whereIn('status', ['submitted', 'guardian_confirmed'])->count();

    }



    private function pendingRequestsForGovernorate(int $governorateId): int

    {

        return TrainingCenterRegistrationRequest::query()->where('governorate_id', $governorateId)->where('status', 'pending')->count()

            + TrainerRegistrationRequest::query()->where('governorate_id', $governorateId)->where('status', 'pending')->count()

            + TraineeRegistrationRequest::query()->where('governorate_id', $governorateId)->where('status', 'pending')->count()

            + CourseRegistrationRequest::query()->where('governorate_id', $governorateId)->whereIn('status', ['submitted', 'guardian_confirmed'])->count();

    }

    /** @return array<int, int> */
    private function pendingRequestsGroupedByGovernorate(): array
    {
        $totals = [];

        foreach ([
            ['training_center_registration_requests', 'status', 'pending'],
            ['trainer_registration_requests', 'status', 'pending'],
            ['trainee_registration_requests', 'status', 'pending'],
        ] as [$table, $column, $value]) {
            foreach (
                DB::table($table)
                    ->select('governorate_id', DB::raw('COUNT(*) as aggregate'))
                    ->whereNotNull('governorate_id')
                    ->where($column, $value)
                    ->groupBy('governorate_id')
                    ->pluck('aggregate', 'governorate_id') as $govId => $count
            ) {
                $totals[(int) $govId] = ($totals[(int) $govId] ?? 0) + (int) $count;
            }
        }

        foreach (
            DB::table('course_registration_requests')
                ->select('governorate_id', DB::raw('COUNT(*) as aggregate'))
                ->whereNotNull('governorate_id')
                ->whereIn('status', ['submitted', 'guardian_confirmed'])
                ->groupBy('governorate_id')
                ->pluck('aggregate', 'governorate_id') as $govId => $count
        ) {
            $totals[(int) $govId] = ($totals[(int) $govId] ?? 0) + (int) $count;
        }

        return $totals;
    }

    /** @return array<int, int> */
    private function countsByGovernorate(string $table, ?callable $constraint = null): array
    {
        $query = DB::table($table)
            ->select('governorate_id', DB::raw('COUNT(*) as aggregate'))
            ->whereNotNull('governorate_id')
            ->groupBy('governorate_id');

        if ($constraint) {
            $constraint($query);
        }

        return $query->pluck('aggregate', 'governorate_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

}

