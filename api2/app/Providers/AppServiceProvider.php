<?php

namespace App\Providers;

use App\Models\Agreement;
use App\Models\Branch;
use App\Models\Certificate;
use App\Models\ConsultantAssignment;
use App\Models\ConsultantOffice;
use App\Models\ConsultingRequest;
use App\Models\FinancialRecord;
use App\Models\FundedLoan;
use App\Models\FundingApplication;
use App\Models\FundingPartner;
use App\Models\IncubatedProject;
use App\Models\IncubationApplication;
use App\Models\Need;
use App\Models\CourseRegistrationRequest;
use App\Models\Trainee;
use App\Models\TraineeRegistrationRequest;
use App\Models\Trainer;
use App\Models\TrainerProfile;
use App\Models\TrainerRegistrationRequest;
use App\Models\TrainingCenter;
use App\Models\TrainingCenterRegistrationRequest;
use App\Models\TrainingCourse;
use App\Models\TrainingKit;
use App\Models\TrainingKitNomination;
use App\Models\User;
use App\Policies\AgreementPolicy;
use App\Policies\BranchPolicy;
use App\Policies\CertificatePolicy;
use App\Policies\CourseRegistrationRequestPolicy;
use App\Policies\ConsultantAssignmentPolicy;
use App\Policies\ConsultantOfficePolicy;
use App\Policies\ConsultingRequestPolicy;
use App\Policies\FundedLoanPolicy;
use App\Policies\FundingApplicationPolicy;
use App\Policies\FundingPartnerPolicy;
use App\Policies\IncubatedProjectPolicy;
use App\Policies\IncubationApplicationPolicy;
use App\Policies\NeedPolicy;
use App\Policies\FinancialRecordPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\TraineePolicy;
use App\Policies\TraineeRegistrationRequestPolicy;
use App\Policies\TrainerPolicy;
use App\Policies\TrainerProfilePolicy;
use App\Policies\TrainerRegistrationRequestPolicy;
use App\Policies\TrainingCenterPolicy;
use App\Policies\TrainingCenterRegistrationRequestPolicy;
use App\Policies\TrainingCoursePolicy;
use App\Policies\TrainingKitNominationPolicy;
use App\Policies\TrainingKitPolicy;
use App\Policies\UserAccessPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    protected array $policies = [
        TrainingCenter::class => TrainingCenterPolicy::class,
        Trainer::class => TrainerPolicy::class,
        Trainee::class => TraineePolicy::class,
        TrainingKit::class => TrainingKitPolicy::class,
        TrainingCourse::class => TrainingCoursePolicy::class,
        Certificate::class => CertificatePolicy::class,
        TrainingKitNomination::class => TrainingKitNominationPolicy::class,
        TrainerProfile::class => TrainerProfilePolicy::class,
        TrainingCenterRegistrationRequest::class => TrainingCenterRegistrationRequestPolicy::class,
        TrainerRegistrationRequest::class => TrainerRegistrationRequestPolicy::class,
        TraineeRegistrationRequest::class => TraineeRegistrationRequestPolicy::class,
        CourseRegistrationRequest::class => CourseRegistrationRequestPolicy::class,
        User::class => UserAccessPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Branch::class => BranchPolicy::class,
        Agreement::class => AgreementPolicy::class,
        FinancialRecord::class => FinancialRecordPolicy::class,
        FundingApplication::class => FundingApplicationPolicy::class,
        ConsultantOffice::class => ConsultantOfficePolicy::class,
        FundingPartner::class => FundingPartnerPolicy::class,
        FundedLoan::class => FundedLoanPolicy::class,
        ConsultantAssignment::class => ConsultantAssignmentPolicy::class,
        Need::class => NeedPolicy::class,
        IncubationApplication::class => IncubationApplicationPolicy::class,
        IncubatedProject::class => IncubatedProjectPolicy::class,
        ConsultingRequest::class => ConsultingRequestPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));

            return Limit::perMinute(10)->by($email . '|' . $request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('certificate-verify', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('print-routes', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('certificate-print-by-code', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('map-public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('registration-requests', function (Request $request) {
            $userId = (string) ($request->user()?->id ?? 'guest');

            return Limit::perMinute(10)->by($userId . '|' . $request->ip());
        });

        RateLimiter::for('file-upload', function (Request $request) {
            $userId = (string) ($request->user()?->id ?? 'guest');

            return Limit::perMinute(5)->by($userId . '|' . $request->ip());
        });

        RateLimiter::for('verify-page', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('admin-access', function (Request $request) {
            $userId = (string) ($request->user()?->id ?? $request->ip());

            return Limit::perMinute(120)->by('admin|' . $userId);
        });

        RateLimiter::for('incubation-report', function (Request $request) {
            $userId = (string) ($request->user()?->id ?? 'guest');

            return Limit::perMinute(10)->by('inc-report|' . $userId . '|' . $request->ip());
        });

        RateLimiter::for('training-kit-public', function (Request $request) {
            return Limit::perMinute(5)->by('tk-public|' . $request->ip());
        });

        RateLimiter::for('ai-chat', function (Request $request) {
            $userId = (string) ($request->user()?->id ?? 'guest');

            return Limit::perMinute(20)->by('ai-chat|' . $userId . '|' . $request->ip());
        });
    }
}
