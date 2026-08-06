<?php

namespace App\Services\Dashboard;

use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\ConsultantAssignment;
use App\Models\ConsultantOffice;
use App\Models\ConsultantReport;
use App\Models\ConsultingRequest;
use App\Models\CourseRegistrationRequest;
use App\Models\FundingApplication;
use App\Models\FundingPartner;
use App\Models\FundingPartnerAssignment;
use App\Models\FundedLoan;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Need;
use App\Models\Trainee;
use App\Models\TraineeRegistrationRequest;
use App\Models\Trainer;
use App\Models\TrainerRegistrationRequest;
use App\Models\TrainingCenter;
use App\Models\TrainingCenterRegistrationRequest;
use App\Models\TrainingCourse;
use App\Models\TrainingKitNomination;
use App\Models\User;
use App\Services\Admin\NationalDashboardService;
use App\Services\Finance\FundingMetricsService;
use App\Services\Needs\NeedDashboardService;
use App\Support\AccessControlGuard;
use App\Support\BranchDataScope;
use App\Support\FinanceDataScope;
use App\Support\NeedDataScope;
use App\Support\NeedStatus;
use Illuminate\Database\Eloquent\Builder;

class RoleDashboardService
{
    public function __construct(
        private NationalDashboardService $nationalDashboard,
        private NeedDashboardService $needDashboard,
        private FundingMetricsService $fundingMetrics,
    ) {}

    public function forUser(User $user): array
    {
        if ($user->hasRole('super_admin')) {
            return $this->wrap($user, 'super_admin', 'لوحة الإدارة العليا', $this->nationalAdmin($user));
        }

        if ($user->hasRole('admin')) {
            return $this->wrap($user, 'admin', 'لوحة الإدارة', $this->nationalAdmin($user));
        }

        if ($user->hasRole('general_director')) {
            return $this->wrap($user, 'general_director', 'لوحة المدير العام', $this->generalDirector($user));
        }

        if ($user->hasRole(['deputy_general_director', 'deputy_director'])) {
            return $this->wrap($user, 'deputy_executive', 'لوحة الإدارة التنفيذية', $this->deputyExecutive($user));
        }

        if ($user->hasRole('branch_manager')) {
            return $this->wrap($user, 'branch_manager', 'لوحة مدير الفرع', $this->branchManager($user));
        }

        if ($user->hasRole('governor')) {
            return $this->wrap($user, 'governor', 'لوحة المحافظ', $this->governor($user));
        }

        if ($user->hasRole('branch_officer')) {
            return $this->wrap($user, 'branch_officer', 'لوحة موظف الفرع', $this->branchOfficer($user));
        }

        if ($user->hasRole('system_admin')) {
            return $this->wrap($user, 'system_admin', 'إدارة النظام', [
                'message' => 'استخدم قسم إدارة النظام لإدارة المستخدمين والأدوار والصلاحيات.',
            ]);
        }

        if ($user->hasRole('training_manager')) {
            return $this->wrap($user, 'training_manager', 'لوحة إدارة التدريب', $this->trainingManager($user));
        }

        if ($user->hasRole('workforce_manager')) {
            return $this->wrap($user, 'workforce_manager', 'لوحة إدارة الوظائف', $this->workforceManagerDashboard($user));
        }

        if ($user->hasRole('center_user')) {
            return $this->wrap($user, 'center_user', 'لوحة المركز التدريبي', $this->centerUser($user));
        }

        if ($user->hasRole('trainer_user')) {
            return $this->wrap($user, 'trainer_user', 'لوحة المدرب', $this->trainerUser($user));
        }

        if ($user->hasRole('trainee_user')) {
            return $this->wrap($user, 'trainee_user', 'لوحة المتدرب', $this->traineeUser($user));
        }

        if ($user->hasRole('auditor')) {
            return $this->wrap($user, 'auditor', 'لوحة التدقيق', $this->auditor($user));
        }

        if ($user->hasRole('central_bank_admin')) {
            return $this->wrap($user, 'central_bank_admin', 'لوحة البنك المركزي', $this->centralBankAdmin($user));
        }

        if ($user->hasRole('funding_partner') && $user->funding_partner_id) {
            return $this->wrap($user, 'funding_partner', 'لوحة شريك التمويل', $this->fundingPartner($user));
        }

        if ($user->hasRole('consultant_union_admin')) {
            return $this->wrap($user, 'consultant_union_admin', 'لوحة نقابة الاستشاريين', $this->consultantUnionAdmin($user));
        }

        if ($user->hasRole('consultant_office') && $user->consultant_office_id) {
            return $this->wrap($user, 'consultant_office', 'لوحة المكتب الاستشاري', $this->consultantOffice($user));
        }

        if ($user->hasRole('finance_manager')) {
            return $this->wrap($user, 'finance_manager', 'لوحة المدير المالي', $this->financeManager($user));
        }

        if ($user->hasRole('finance_officer')) {
            return $this->wrap($user, 'finance_officer', 'لوحة الموظف المالي', $this->financeOfficer($user));
        }

        if ($user->hasRole('project_owner')) {
            return $this->wrap($user, 'project_owner', 'لوحة رائد الأعمال', $this->projectOwner($user));
        }

        if ($user->hasRole('data_entry')) {
            return $this->wrap($user, 'data_entry', 'لوحة إدخال البيانات', $this->needDashboard->dataEntryWorkspace($user));
        }

        if ($user->hasRole('data_reviewer')) {
            return $this->wrap($user, 'data_reviewer', 'لوحة مراجعة البيانات', $this->needDashboard->reviewerWorkspace($user));
        }

        if ($user->hasAnyRole(['development_manager', 'local_development_manager', 'project_services_manager'])) {
            return $this->wrap($user, 'development_manager', 'لوحة التنمية والاحتياجات', $this->developmentManager($user));
        }

        return $this->wrap($user, 'unknown', 'لوحة التحكم', [
            'message' => 'لا توجد لوحة بيانات مخصصة لهذا الدور حالياً.',
        ]);
    }

    private function wrap(User $user, string $role, string $title, array $data): array
    {
        return array_merge($data, [
            'dashboard_role' => $role,
            'dashboard_title' => $title,
            'operational_links' => $this->operationalLinks($user, $role),
            'recent_items' => $data['recent_items'] ?? $data['recent_activity'] ?? [],
        ]);
    }

    private function nationalAdmin(User $user): array
    {
        $data = array_merge([
            'scope' => 'national',
            'users_total' => User::count(),
            'users_active' => User::where('is_active', true)->count(),
            'users_inactive' => User::where('is_active', false)->count(),
            'centers' => TrainingCenter::count(),
            'trainers' => Trainer::count(),
            'trainees' => Trainee::count(),
            'courses' => TrainingCourse::count(),
            'courses_active' => TrainingCourse::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'courses_completed' => TrainingCourse::where('status', 'completed')->count(),
            'certificates_total' => Certificate::count(),
            'certificates_pending' => Certificate::where('status', '!=', 'approved')->count(),
            'certificates_approved' => Certificate::where('status', 'approved')->count(),
            'certificates_rejected' => Certificate::where('status', 'rejected')->count(),
            'funding_applications_total' => FundingApplication::count(),
            'consulting_requests_total' => ConsultingRequest::count(),
            'needs_total' => Need::count(),
            'job_postings_published' => JobPosting::where('status', 'published')->count(),
            'registration_requests_total' => $this->registrationRequestsTotal(),
            'recent_activity' => $this->recentActivity(null, 10),
        ], $this->nationalDashboard->nationalSummary());

        $data['analytics'] = $this->buildNationalAnalytics($data);

        return $data;
    }

    /**
     * بيانات مخططات لوحة المدير العام / الإدارة الوطنية (أسلوب لوحة مؤشرات).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildNationalAnalytics(array $data): array
    {
        $govStats = collect($data['governorate_stats'] ?? []);

        $needsByGov = Need::query()
            ->selectRaw('governorate_id, COUNT(*) as total')
            ->whereNotNull('governorate_id')
            ->groupBy('governorate_id')
            ->pluck('total', 'governorate_id');

        $fundingByGov = FundingApplication::query()
            ->selectRaw('governorate_id, COUNT(*) as total')
            ->whereNotNull('governorate_id')
            ->groupBy('governorate_id')
            ->pluck('total', 'governorate_id');

        $certByStatus = Certificate::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $fundingByStatus = FundingApplication::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $needsByStatus = Need::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'modules' => [
                ['key' => 'courses', 'label' => 'الدورات', 'value' => (int) ($data['courses'] ?? 0)],
                ['key' => 'funding', 'label' => 'التمويل', 'value' => (int) ($data['funding_applications_total'] ?? 0)],
                ['key' => 'needs', 'label' => 'الاحتياجات', 'value' => (int) ($data['needs_total'] ?? 0)],
                ['key' => 'consulting', 'label' => 'الاستشارات', 'value' => (int) ($data['consulting_requests_total'] ?? 0)],
                ['key' => 'jobs', 'label' => 'فرص العمل', 'value' => (int) ($data['job_postings_published'] ?? 0)],
                ['key' => 'certificates', 'label' => 'الشهادات', 'value' => (int) ($data['certificates_total'] ?? 0)],
            ],
            'certificates_pipeline' => [
                ['key' => 'approved', 'label' => 'معتمدة', 'value' => (int) ($data['certificates_approved'] ?? 0)],
                ['key' => 'pending', 'label' => 'قيد الاعتماد', 'value' => (int) ($data['certificates_pending'] ?? 0)],
                ['key' => 'rejected', 'label' => 'مرفوضة', 'value' => (int) ($data['certificates_rejected'] ?? 0)],
            ],
            'certificates_by_status' => $certByStatus->map(fn ($v, $k) => [
                'status' => (string) $k,
                'total' => (int) $v,
            ])->values()->all(),
            'funding_by_status' => $fundingByStatus->map(fn ($v, $k) => [
                'status' => (string) $k,
                'total' => (int) $v,
            ])->values()->all(),
            'needs_by_status' => $needsByStatus->map(fn ($v, $k) => [
                'status' => (string) $k,
                'total' => (int) $v,
            ])->values()->all(),
            'by_governorate' => $govStats->map(fn ($g) => [
                'id' => (int) ($g['id'] ?? 0),
                'name' => (string) ($g['name_ar'] ?? ''),
                'courses' => (int) ($g['courses_count'] ?? 0),
                'trainees' => (int) ($g['trainees_count'] ?? 0),
                'certificates' => (int) ($g['certificates_count'] ?? 0),
                'pending_requests' => (int) ($g['registration_requests_pending'] ?? 0),
                'needs' => (int) ($needsByGov[(int) ($g['id'] ?? 0)] ?? 0),
                'funding' => (int) ($fundingByGov[(int) ($g['id'] ?? 0)] ?? 0),
            ])->values()->all(),
        ];
    }

    private function generalDirector(User $user): array
    {
        $data = $this->nationalAdmin($user);
        $data['scope'] = 'national_executive';
        $data['certificates_pending_general_director'] = Certificate::where('status', 'pending_general_director_approval')->count();

        if (!$this->safeHasPermission($user, 'manage_user_access')) {
            unset($data['users_total'], $data['users_active'], $data['users_inactive']);
        }

        return $data;
    }

    private function deputyExecutive(User $user): array
    {
        $data = [
            'scope' => 'national_limited',
            'certificates_pending_deputy' => Certificate::where('status', 'pending_deputy_approval')->count(),
            'certificates_approved' => Certificate::where('status', 'approved')->count(),
            'certificates_rejected' => Certificate::where('status', 'rejected')->count(),
            'registration_requests_total' => $this->registrationRequestsTotal(),
        ];

        if ($this->safeHasPermission($user, 'finance.applications.view')) {
            $apps = FinanceDataScope::scopeApplications(FundingApplication::query(), $user);
            $data['funding_applications_total'] = (clone $apps)->count();
            $data['funding_pending'] = (clone $apps)->whereNotIn('status', ['approved', 'rejected', 'closed'])->count();
        }

        if ($this->safeHasPermission($user, 'needs.view') || $this->safeHasPermission($user, 'needs.view_all')) {
            $needStats = $this->needDashboard->stats($user);
            $data['needs_total'] = $needStats['total'] ?? 0;
            $data['needs_scope'] = $needStats['scope'] ?? 'limited';
        }

        if ($this->safeHasPermission($user, 'view_audit')) {
            $data['audit_logs_recent'] = AuditLog::query()->latest('id')->limit(5)->count();
            $data['recent_activity'] = $this->recentActivity(null, 5);
        }

        return $data;
    }

    private function branchManager(User $user): array
    {
        $branchId = $user->branch_id;
        $data = array_merge($this->nationalDashboard->branchSummary($user), [
            'scope' => 'branch',
            'funding_applications_total' => $branchId
                ? FinanceDataScope::scopeApplications(FundingApplication::query(), $user)->count()
                : 0,
            'consulting_requests_total' => $branchId
                ? $this->scopeConsulting(ConsultingRequest::query(), $user)->count()
                : 0,
            'needs_total' => $branchId
                ? NeedDataScope::scopeNeeds(Need::query(), $user)->count()
                : 0,
            'job_postings_published' => JobPosting::published()
                ->when($user->governorate_id, fn ($q) => $q->where('governorate_id', $user->governorate_id))
                ->count(),
            'recent_items' => $branchId
                ? $this->recentBranchItems($user, 8)
                : [],
        ]);

        return $data;
    }

    private function governor(User $user): array
    {
        $needStats = $this->needDashboard->stats($user);
        $needsBase = NeedDataScope::scopeNeeds(Need::query(), $user);

        return [
            'scope' => 'governorate',
            'governorate_name' => $user->governorate?->name_ar,
            'needs_total' => $needStats['total'] ?? 0,
            'needs_scope' => $needStats['scope'] ?? 'governorate',
            'needs_pending_review' => (clone $needsBase)->whereIn('status', [
                NeedStatus::PENDING_GOVERNORATE_REVIEW,
                NeedStatus::PENDING_BRANCH_APPROVAL,
            ])->count(),
            'needs_approved' => (clone $needsBase)->where('status', NeedStatus::APPROVED)->count(),
            'by_sector' => $needStats['by_sector'] ?? [],
            'by_priority' => $needStats['by_priority'] ?? [],
            'consulting_requests_total' => $this->scopeConsulting(ConsultingRequest::query(), $user)->count(),
            'funding_applications_total' => FinanceDataScope::scopeApplications(FundingApplication::query(), $user)->count(),
            'recent_items' => (clone $needsBase)->latest('id')->limit(8)->get([
                'id', 'need_code', 'title', 'status', 'priority', 'created_at',
            ]),
        ];
    }

    private function branchOfficer(User $user): array
    {
        $mine = FundingApplication::query()
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)->orWhere('applicant_user_id', $user->id);
            });

        $branchApps = FinanceDataScope::scopeApplications(FundingApplication::query(), $user);
        $needsBase = NeedDataScope::scopeNeeds(Need::query(), $user);

        return [
            'scope' => 'branch_officer',
            'branch_name' => $user->branch?->name,
            'governorate_name' => $user->governorate?->name_ar ?? $user->branch?->governorate?->name_ar,
            'my_applications_total' => (clone $mine)->count(),
            'my_applications_pending' => (clone $mine)->whereNotIn('status', ['approved', 'rejected', 'closed'])->count(),
            'my_applications_completed' => (clone $mine)->whereIn('status', ['approved', 'closed'])->count(),
            'branch_applications_visible' => (clone $branchApps)->count(),
            'needs_total' => (clone $needsBase)->count(),
            'consulting_requests_total' => $this->scopeConsulting(ConsultingRequest::query(), $user)->count(),
            'recent_items' => AuditLog::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->limit(8)
                ->get(['id', 'action', 'module', 'description', 'created_at']),
        ];
    }

    private function trainingManager(User $user): array
    {
        return [
            'scope' => 'training',
            'trainers' => Trainer::count(),
            'courses' => TrainingCourse::count(),
            'courses_active' => TrainingCourse::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'trainees' => Trainee::count(),
            'centers' => TrainingCenter::count(),
            'certificates_total' => Certificate::count(),
            'certificates_pending_training' => Certificate::where('status', 'pending_training_approval')->count(),
            'certificates_approved' => Certificate::where('status', 'approved')->count(),
            'certificates_rejected' => Certificate::where('status', 'rejected')->count(),
            'training_kit_nominations_pending' => TrainingKitNomination::where('status', 'pending')->count(),
            'center_registration_requests_pending' => $user->canReviewCenterRegistrationRequests()
                ? TrainingCenterRegistrationRequest::where('status', 'pending')->count() : 0,
            'trainer_registration_requests_pending' => $user->canReviewTrainerRegistrationRequests()
                ? TrainerRegistrationRequest::where('status', 'pending')->count() : 0,
            'trainee_registration_requests_pending' => $user->canReviewTraineeRegistrationRequests()
                ? TraineeRegistrationRequest::where('status', 'pending')->count() : 0,
            'course_registration_requests_need_completion' => $user->canCompleteCourseRegistrationRequests()
                ? CourseRegistrationRequest::where('status', 'guardian_confirmed')->count() : 0,
        ];
    }

    private function centerUser(User $user): array
    {
        $centerId = $user->training_center_id;

        return [
            'scope' => 'center',
            'courses' => $centerId ? TrainingCourse::where('training_center_id', $centerId)->count() : 0,
            'courses_active' => $centerId
                ? TrainingCourse::where('training_center_id', $centerId)->whereNotIn('status', ['completed', 'cancelled'])->count()
                : 0,
            'trainers' => $centerId ? Trainer::where('training_center_id', $centerId)->count() : 0,
            'certificates_total' => $centerId ? Certificate::where('training_center_id', $centerId)->count() : 0,
            'certificates_pending_center' => $centerId
                ? Certificate::where('training_center_id', $centerId)->where('status', 'pending_center_approval')->count()
                : 0,
            'certificates_approved' => $centerId
                ? Certificate::where('training_center_id', $centerId)->where('status', 'approved')->count()
                : 0,
            'trainer_registration_requests_pending' => $centerId
                ? TrainerRegistrationRequest::where('training_center_id', $centerId)->where('status', 'pending')->count()
                : 0,
            'course_registration_requests_my_count' => $centerId
                ? CourseRegistrationRequest::whereHas('trainingCourse', fn ($q) => $q->where('training_center_id', $centerId))->count()
                : 0,
        ];
    }

    private function trainerUser(User $user): array
    {
        $trainerId = $user->trainer_id;

        return [
            'scope' => 'trainer',
            'courses' => $trainerId ? TrainingCourse::where('trainer_id', $trainerId)->count() : 0,
            'courses_active' => $trainerId
                ? TrainingCourse::where('trainer_id', $trainerId)->whereNotIn('status', ['completed', 'cancelled'])->count()
                : 0,
            'certificates' => $trainerId ? Certificate::where('trainer_id', $trainerId)->count() : 0,
            'training_kit_nominations_my_count' => $trainerId
                ? TrainingKitNomination::where('trainer_id', $trainerId)->count() : 0,
            'trainer_registration_requests_pending' => $this->safeHasPermission($user, 'create_trainer_registration_requests')
                ? TrainerRegistrationRequest::where('submitted_by_user_id', $user->id)->where('status', 'pending')->count()
                : 0,
        ];
    }

    private function traineeUser(User $user): array
    {
        $traineeId = $user->trainee_id;

        return [
            'scope' => 'trainee',
            'certificates' => $traineeId ? Certificate::where('trainee_id', $traineeId)->count() : 0,
            'passed' => $traineeId ? Certificate::where('trainee_id', $traineeId)->where('result', 'passed')->count() : 0,
            'course_registration_requests_my_count' => $user->canCreateCourseRegistrationRequests()
                ? CourseRegistrationRequest::where('submitted_by_user_id', $user->id)->count() : 0,
            'course_registration_requests_need_confirmation' => $user->canConfirmCourseRegistrationRequests()
                ? CourseRegistrationRequest::where('submitted_by_user_id', $user->id)->where('status', 'submitted')->count()
                : 0,
            'trainee_registration_requests_pending' => TraineeRegistrationRequest::where('submitted_by_user_id', $user->id)
                ->where('status', 'pending')->count(),
        ];
    }

    private function auditor(User $user): array
    {
        $data = [
            'scope' => 'audit_readonly',
            'centers' => TrainingCenter::count(),
            'trainers' => Trainer::count(),
            'courses' => TrainingCourse::count(),
            'certificates' => Certificate::count(),
            'registration_requests_total' => $this->registrationRequestsTotal(),
        ];

        if ($this->safeHasPermission($user, 'finance.applications.view')) {
            $data['funding_applications_total'] = FundingApplication::count();
        }

        if ($this->safeHasPermission($user, 'view_audit')) {
            $data['audit_logs_total'] = AuditLog::count();
            $data['recent_activity'] = $this->recentActivity(null, 10);
        }

        if ($this->safeHasPermission($user, 'needs.view')) {
            $data['needs_total'] = Need::count();
        }

        return $data;
    }

    private function centralBankAdmin(User $user): array
    {
        $partners = FundingPartner::query();
        $assignments = FundingPartnerAssignment::query();

        return [
            'scope' => 'central_bank',
            'partners_total' => (clone $partners)->count(),
            'partners_active' => (clone $partners)->whereIn('status', ['approved', 'active'])->count(),
            'assignments_total' => (clone $assignments)->count(),
            'assignments_under_review' => (clone $assignments)->whereIn('status', ['sent', 'under_review'])->count(),
            'assignments_approved' => (clone $assignments)->where('status', 'approved')->count(),
            'assignments_rejected' => (clone $assignments)->where('status', 'rejected')->count(),
            'loans_total' => FinanceDataScope::scopeLoans(FundedLoan::query(), $user)->count(),
            'funding_applications_total' => FinanceDataScope::scopeApplications(FundingApplication::query(), $user)->count(),
        ];
    }

    private function fundingPartner(User $user): array
    {
        $assignments = FundingPartnerAssignment::query()
            ->where('funding_partner_id', $user->funding_partner_id);

        return [
            'scope' => 'funding_partner',
            'assignments_total' => (clone $assignments)->count(),
            'under_review' => (clone $assignments)->whereIn('status', ['sent', 'under_review'])->count(),
            'approved' => (clone $assignments)->where('status', 'approved')->count(),
            'rejected' => (clone $assignments)->where('status', 'rejected')->count(),
            'funded' => (clone $assignments)->where('status', 'funded')->count(),
            'loans_total' => FinanceDataScope::scopeLoans(FundedLoan::query(), $user)->count(),
            'recent_items' => (clone $assignments)->with('application:id,application_number,project_name,status')
                ->latest('id')->limit(8)->get(),
        ];
    }

    private function consultantUnionAdmin(User $user): array
    {
        return [
            'scope' => 'consultant_union',
            'offices_total' => ConsultantOffice::count(),
            'offices_pending' => ConsultantOffice::where('status', 'pending')->count(),
            'offices_active' => ConsultantOffice::whereIn('status', ['approved', 'active'])->count(),
            'assignments_total' => ConsultantAssignment::count(),
            'assignments_in_progress' => ConsultantAssignment::whereIn('status', ['assigned', 'accepted', 'in_progress'])->count(),
            'assignments_completed' => ConsultantAssignment::where('status', 'completed')->count(),
            'reports_total' => ConsultantReport::count(),
            'pending_price_offers' => ConsultantAssignment::where('price_offer_status', 'submitted')->count(),
        ];
    }

    private function consultantOffice(User $user): array
    {
        $assignments = ConsultantAssignment::query()
            ->where('consultant_office_id', $user->consultant_office_id);

        return [
            'scope' => 'consultant_office',
            'assignments_total' => (clone $assignments)->count(),
            'assignments_in_progress' => (clone $assignments)->whereIn('status', ['assigned', 'accepted', 'in_progress'])->count(),
            'assignments_completed' => (clone $assignments)->where('status', 'completed')->count(),
            'assignments_pending' => (clone $assignments)->where('status', 'assigned')->count(),
            'reports_total' => ConsultantReport::where('consultant_office_id', $user->consultant_office_id)->count(),
            'recent_items' => (clone $assignments)->with('application:id,application_number,project_name,status')
                ->latest('id')->limit(8)->get(),
        ];
    }

    private function financeManager(User $user): array
    {
        $apps = FinanceDataScope::scopeApplications(FundingApplication::query(), $user);

        return array_merge([
            'scope' => 'finance_manager',
            'funding_applications_total' => (clone $apps)->count(),
            'funding_pending_review' => (clone $apps)->whereNotIn('status', ['approved', 'rejected', 'closed'])->count(),
            'funding_needs_decision' => (clone $apps)->whereIn('current_stage', ['finance_review', 'funder_review'])->count(),
            'funding_completed' => (clone $apps)->whereIn('status', ['approved', 'closed'])->count(),
        ], $this->fundingMetrics->metrics($user));
    }

    private function financeOfficer(User $user): array
    {
        $apps = FinanceDataScope::scopeApplications(FundingApplication::query(), $user);

        return array_merge([
            'scope' => 'finance_officer',
            'my_tasks_total' => (clone $apps)->count(),
            'my_tasks_pending' => (clone $apps)->whereNotIn('status', ['approved', 'rejected', 'closed'])->count(),
            'my_tasks_completed' => (clone $apps)->whereIn('status', ['approved', 'closed'])->count(),
            'recent_items' => AuditLog::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->limit(8)
                ->get(['id', 'action', 'module', 'description', 'created_at']),
        ], $this->fundingMetrics->metrics($user));
    }

    private function projectOwner(User $user): array
    {
        $funding = FundingApplication::query()
            ->where(function ($q) use ($user) {
                $q->where('applicant_user_id', $user->id)->orWhere('created_by', $user->id);
            });
        $consulting = ConsultingRequest::query()->where('user_id', $user->id);

        return [
            'scope' => 'entrepreneur',
            'funding_applications_total' => (clone $funding)->count(),
            'funding_pending' => (clone $funding)->whereNotIn('status', ['approved', 'rejected', 'closed'])->count(),
            'funding_approved' => (clone $funding)->where('status', 'approved')->count(),
            'funding_rejected' => (clone $funding)->where('status', 'rejected')->count(),
            'consulting_requests_total' => (clone $consulting)->count(),
            'consulting_pending' => (clone $consulting)->whereNotIn('status', ['completed', 'cancelled', 'rejected'])->count(),
            'certificates_total' => $user->trainee_id
                ? Certificate::where('trainee_id', $user->trainee_id)->count() : 0,
            'recent_items' => (clone $funding)->latest('id')->limit(5)->get([
                'id', 'application_number', 'project_name', 'status', 'created_at',
            ]),
        ];
    }

    private function workforceManagerDashboard(User $user): array
    {
        return [
            'scope' => 'workforce',
            'jobs_published' => JobPosting::where('status', 'published')->count(),
            'jobs_pending_review' => JobPosting::where('status', 'pending')->count(),
            'jobs_closed' => JobPosting::where('status', 'closed')->count(),
            'applications_total' => JobApplication::count(),
            'candidates_total' => JobApplication::query()->distinct()->count('user_id'),
            'recent_items' => JobApplication::query()
                ->with('jobPosting:id,title')
                ->latest('id')
                ->limit(8)
                ->get(['id', 'job_posting_id', 'status', 'created_at']),
        ];
    }

    private function developmentManager(User $user): array
    {
        $needStats = $this->needDashboard->stats($user);
        $base = NeedDataScope::scopeNeeds(Need::query(), $user);

        return [
            'scope' => $needStats['scope'] ?? 'development',
            'needs_total' => $needStats['total'] ?? 0,
            'needs_approved' => (clone $base)->where('status', NeedStatus::APPROVED)->count(),
            'needs_pending' => (clone $base)->whereIn('status', [
                NeedStatus::PENDING_GOVERNORATE_REVIEW,
                NeedStatus::PENDING_BRANCH_APPROVAL,
            ])->count(),
            'high_priority' => (clone $base)->where('priority', 'عالية')->count(),
            'by_sector' => $needStats['by_sector'] ?? [],
            'by_governorate' => $needStats['by_governorate'] ?? [],
        ];
    }

    /** @return list<array{label: string, path: string, permission?: string, role?: string}> */
    private function operationalLinks(User $user, string $role): array
    {
        $links = match ($role) {
            'super_admin' => [
                ['label' => 'لوحة المنصات', 'path' => 'services/admin/super-admin-dashboard.php'],
                ['label' => 'إدارة المستخدمين', 'path' => 'services/admin/admin-users.php', 'permission' => 'manage_user_access'],
                ['label' => 'التمويل', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
                ['label' => 'الاستشارات', 'path' => 'services/consulting/consulting-requests-list.php'],
                ['label' => 'خريطة الاحتياجات', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.dashboard'],
                ['label' => 'Workforce', 'path' => 'services/workforce/jobs-list.php', 'permission' => 'workforce.jobs.view'],
            ],
            'admin' => [
                ['label' => 'إدارة المستخدمين', 'path' => 'services/admin/admin-users.php', 'permission' => 'manage_user_access'],
                ['label' => 'التمويل', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
                ['label' => 'الاستشارات', 'path' => 'services/consulting/consulting-requests-list.php'],
                ['label' => 'خريطة الاحتياجات', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.dashboard'],
                ['label' => 'التدريب', 'path' => 'services/training/training-courses-list.php', 'permission' => 'view_courses'],
            ],
            'general_director' => [
                ['label' => 'اعتماد الشهادات', 'path' => 'services/training/training-certificates-approve.php', 'permission' => 'approve_general_director_certificates'],
                ['label' => 'إدارة المستخدمين', 'path' => 'services/admin/admin-users.php', 'permission' => 'manage_user_access'],
                ['label' => 'التقارير', 'path' => 'services/admin/super-admin-dashboard.php'],
                ['label' => 'التمويل', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
                ['label' => 'الاستشارات', 'path' => 'services/consulting/consulting-requests-list.php'],
                ['label' => 'لوحة ريادة الأعمال', 'path' => 'services/admin/entrepreneur-manager-dashboard.php'],
                ['label' => 'خريطة الاحتياجات', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.dashboard'],
                ['label' => 'التدريب', 'path' => 'services/training/training-courses-list.php', 'permission' => 'view_courses'],
                ['label' => 'Workforce', 'path' => 'services/workforce/jobs-list.php', 'permission' => 'workforce.jobs.view'],
            ],
            'deputy_executive' => [
                ['label' => 'اعتماد الشهادات', 'path' => 'services/training/training-certificates-approve.php', 'permission' => 'approve_deputy_certificates'],
                ['label' => 'لوحة ريادة الأعمال', 'path' => 'services/admin/entrepreneur-manager-dashboard.php'],
                ['label' => 'التمويل', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
                ['label' => 'خريطة الاحتياجات', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.view'],
            ],
            'branch_officer' => [
                ['label' => 'طلباتي', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
                ['label' => 'الاستشارات', 'path' => 'services/consulting/consulting-requests-list.php'],
                ['label' => 'خريطة الاحتياجات', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.view'],
                ['label' => 'فرص العمل', 'path' => 'services/workforce/jobs-list.php', 'permission' => 'workforce.jobs.view'],
            ],
            'workforce_manager' => [
                ['label' => 'إدارة الوظائف', 'path' => 'services/workforce/jobs-list.php', 'permission' => 'workforce.jobs.view'],
                ['label' => 'نشر وظيفة', 'path' => 'services/workforce/job-post.php', 'permission' => 'workforce.jobs.create'],
                ['label' => 'المرشحون', 'path' => 'services/workforce/candidates-list.php', 'permission' => 'workforce.applications.view'],
            ],
            'branch_manager' => [
                ['label' => 'خريطة المحافظة', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.dashboard'],
                ['label' => 'طلبات التمويل', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
                ['label' => 'الاستشارات', 'path' => 'services/consulting/consulting-requests-list.php'],
                ['label' => 'التدريب المحلي', 'path' => 'services/training/training-courses-list.php', 'permission' => 'view_courses'],
            ],
            'governor' => [
                ['label' => 'خريطة الاحتياجات', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.dashboard'],
                ['label' => 'مراجعة الاحتياجات', 'path' => 'services/gis/needs-list.php', 'permission' => 'needs.view'],
                ['label' => 'لوحة الاحتياجات', 'path' => 'services/gis/needs-dashboard.php', 'permission' => 'needs.dashboard'],
            ],
            'funding_partner' => [
                ['label' => 'طلباتي التمويلية', 'path' => 'services/finance/my-partner-assignments.php'],
                ['label' => 'القروض الممولة', 'path' => 'services/finance/finance-funded.php', 'permission' => 'finance.loans.view_own'],
            ],
            'consultant_office' => [
                ['label' => 'مهامي الاستشارية', 'path' => 'services/finance/my-consultant-assignments.php'],
                ['label' => 'طلبات التمويل', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
            ],
            'finance_manager', 'finance_officer' => [
                ['label' => 'مراجعة التمويل', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
                ['label' => 'لوحة المالية', 'path' => 'services/finance/finance-manager-dashboard.php', 'permission' => 'finance.metrics.view'],
            ],
            'project_owner' => [
                ['label' => 'تقديم طلب تمويل', 'path' => 'services/finance/finance-apply.php', 'permission' => 'finance.applications.create'],
                ['label' => 'متابعة طلباتي', 'path' => 'services/finance/finance-applications-list.php', 'permission' => 'finance.applications.view'],
                ['label' => 'طلب استشارة', 'path' => 'services/consulting/consulting-request-create.php'],
                ['label' => 'ملفي', 'path' => 'my-profile.php'],
            ],
            'data_entry' => [
                ['label' => 'إضافة احتياج', 'path' => 'services/gis/need-create.php', 'permission' => 'needs.create'],
                ['label' => 'احتياجاتي', 'path' => 'services/gis/needs-list.php', 'permission' => 'needs.view'],
                ['label' => 'خريطة الاحتياجات', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.view'],
            ],
            'data_reviewer' => [
                ['label' => 'مراجعة الاحتياجات', 'path' => 'services/gis/needs-list.php', 'permission' => 'needs.review'],
                ['label' => 'خريطة النطاق', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.view'],
            ],
            'auditor' => [
                ['label' => 'سجل النشاط', 'path' => 'services/admin/admin-activity-logs.php', 'permission' => 'view_audit'],
                ['label' => 'التقارير', 'path' => 'dashboard.php', 'permission' => 'view_reports'],
            ],
            'training_manager' => [
                ['label' => 'الدورات', 'path' => 'services/training/training-courses-list.php', 'permission' => 'view_courses'],
                ['label' => 'المراكز', 'path' => 'services/training/training-centers-list.php', 'permission' => 'view_centers'],
                ['label' => 'الشهادات', 'path' => 'services/training/training-certificates-list.php', 'permission' => 'view_certificates'],
            ],
            'center_user' => [
                ['label' => 'تطبيق المركز', 'path' => 'services/training/center-app.php'],
                ['label' => 'المدربون', 'path' => 'services/training/center-trainers.php', 'permission' => 'view_trainers'],
                ['label' => 'المتدربون', 'path' => 'services/training/center-trainees-list.php', 'permission' => 'view_trainees'],
                ['label' => 'الحقائب', 'path' => 'services/training/center-kits.php', 'permission' => 'view_kits'],
            ],
            'trainer_user' => [
                ['label' => 'تطبيق المركز', 'path' => 'services/training/center-app.php'],
                ['label' => 'ملفي التدريبي', 'path' => 'services/training/my-trainer-profile.php', 'permission' => 'view_trainer_profiles'],
            ],
            'trainee_user' => [
                ['label' => 'دوراتي', 'path' => 'services/training/training-courses-list.php'],
                ['label' => 'شهاداتي', 'path' => 'services/training/training-certificates-list.php', 'permission' => 'view_certificates'],
            ],
            'central_bank_admin' => [
                ['label' => 'لوحة البنك المركزي', 'path' => 'services/finance/central-bank-dashboard.php', 'permission' => 'finance.central_bank.dashboard'],
                ['label' => 'شركاء التمويل', 'path' => 'services/finance/funding-partners.php', 'permission' => 'finance.partners.view_all'],
            ],
            'consultant_union_admin' => [
                ['label' => 'لوحة النقابة', 'path' => 'services/finance/consultant-union-dashboard.php', 'permission' => 'finance.consultant_union.dashboard'],
                ['label' => 'المكاتب الاستشارية', 'path' => 'services/finance/consultant-offices.php', 'permission' => 'finance.consultants.view_all'],
            ],
            'development_manager' => [
                ['label' => 'خريطة الاحتياجات', 'path' => 'services/gis/needs-map.php', 'permission' => 'needs.view_all'],
                ['label' => 'تقارير الاحتياجات', 'path' => 'services/gis/needs-dashboard.php', 'permission' => 'needs.dashboard'],
            ],
            default => [
                ['label' => 'ملفي', 'path' => 'my-profile.php'],
            ],
        };

        return array_values(array_filter($links, function (array $link) use ($user) {
            if (!empty($link['permission']) && !$this->safeHasPermission($user, $link['permission'])) {
                return false;
            }
            if (!empty($link['role']) && !$user->hasRole($link['role'])) {
                return false;
            }

            return true;
        }));
    }

    private function registrationRequestsTotal(): int
    {
        return TrainingCenterRegistrationRequest::count()
            + TrainerRegistrationRequest::count()
            + TraineeRegistrationRequest::count()
            + CourseRegistrationRequest::count();
    }

    private function recentActivity(?int $branchId, int $limit): \Illuminate\Support\Collection
    {
        return AuditLog::query()
            ->with('user:id,name,email')
            ->when($branchId, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('branch_id', $branchId)))
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'user_id', 'action', 'module', 'description', 'ip_address', 'created_at']);
    }

    private function recentBranchItems(User $user, int $limit): \Illuminate\Support\Collection
    {
        $items = collect();

        if ($this->safeHasPermission($user, 'finance.applications.view')) {
            $items = $items->merge(
                FinanceDataScope::scopeApplications(FundingApplication::query(), $user)
                    ->latest('id')->limit($limit)->get(['id', 'application_number', 'project_name', 'status', 'created_at'])
                    ->map(fn ($r) => ['type' => 'funding', 'label' => $r->project_name, 'status' => $r->status, 'at' => $r->created_at])
            );
        }

        return $items->take($limit)->values();
    }

    /** @param  Builder<ConsultingRequest>  $query */
    private function scopeConsulting(Builder $query, User $user): Builder
    {
        if (AccessControlGuard::isNationalAdministrator($user)) {
            return $query;
        }

        if ($user->hasRole(['branch_manager', 'branch_officer'])) {
            return $query->where(function ($q) use ($user) {
                if ($user->governorate_id) {
                    $q->where('governorate_id', $user->governorate_id);
                }
                if ($user->branch_id) {
                    $q->orWhere('branch_id', $user->branch_id);
                }
            });
        }

        if ($user->hasRole('governor') && $user->governorate_id) {
            return $query->where('governorate_id', $user->governorate_id);
        }

        if ($user->hasRole('consultant_office') && $user->consultant_office_id) {
            return $query->whereHas('offers', fn ($q) => $q->where('consultant_office_id', $user->consultant_office_id));
        }

        return $query->where('user_id', $user->id);
    }

    private function safeHasPermission(User $user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Throwable) {
            // Prevent dashboard hard-fail when a permission name is missing in DB.
            return false;
        }
    }
}
