<?php

// TODO(refactor): Split this file into routes/api/{admin,finance,training,needs,incubation,news,entrepreneurs,inbox}.php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\SyriaLocationController;

use App\Http\Controllers\Api\DashboardController;

use App\Http\Controllers\Api\TrainerController;

use App\Http\Controllers\Api\TrainerProfileController;

use App\Http\Controllers\Api\TrainingKitNominationController;

use App\Http\Controllers\Api\TraineeController;

use App\Http\Controllers\Api\WorkforceController;

use App\Http\Controllers\Api\TrainingCenterController;
use App\Http\Controllers\Api\TrainingSupervisorController;

use App\Http\Controllers\Api\TrainingKitController;

use App\Http\Controllers\Api\TrainingProgramController;

use App\Http\Controllers\Api\TrainingCourseController;

use App\Http\Controllers\Api\TrainingMapController;

use App\Http\Controllers\Api\CertificateController;

use App\Http\Controllers\Api\TrainingCenterRegistrationRequestController;

use App\Http\Controllers\Api\TrainerRegistrationRequestController;

use App\Http\Controllers\Api\TraineeRegistrationRequestController;

use App\Http\Controllers\Api\CourseRegistrationRequestController;

use App\Http\Controllers\Api\IncubatorController;

use App\Http\Controllers\Api\SuccessStoryController;

use App\Http\Controllers\Api\NewsController;

use App\Http\Controllers\Api\EntrepreneurProfileController;

use App\Http\Controllers\Api\PublicBrowseController;



/*

|--------------------------------------------------------------------------

| Public Routes

|--------------------------------------------------------------------------

*/



Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:register');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');



Route::post('/certificates/verify', [CertificateController::class, 'verify'])
    ->middleware('throttle:certificate-verify');

Route::get('/verify-certificate/{certificate_code}', [CertificateController::class, 'verifyByCode'])
    ->middleware('throttle:certificate-verify')
    ->where('certificate_code', '[A-Za-z0-9\\-]+');

Route::get('/map/training-centers', [TrainingMapController::class, 'centers'])
    ->middleware('throttle:map-public');

Route::get('/signatures/verify/{code}', [\App\Http\Controllers\Api\ExecutiveSignatureController::class, 'verify'])
    ->middleware('throttle:certificate-verify')
    ->where('code', '[A-Z0-9\\-]+');

Route::post('/training-kit-public-requests', [\App\Http\Controllers\Api\TrainingKitPublicRequestController::class, 'store'])
    ->middleware('throttle:training-kit-public');

Route::get('/news', [NewsController::class, 'index'])
    ->middleware('throttle:map-public');
Route::get('/news/{id}', [NewsController::class, 'show'])
    ->whereNumber('id')
    ->middleware('throttle:map-public');

Route::get('/success-stories', [SuccessStoryController::class, 'index'])
    ->middleware('throttle:map-public');
Route::get('/success-stories/slug/{slug}', [SuccessStoryController::class, 'showBySlug'])
    ->middleware('throttle:map-public');
Route::get('/success-stories/{id}', [SuccessStoryController::class, 'show'])
    ->whereNumber('id')
    ->middleware('throttle:map-public');

Route::get('/incubators', [IncubatorController::class, 'index'])
    ->middleware('throttle:map-public');

Route::get('/entrepreneur/profiles/public-stats', [EntrepreneurProfileController::class, 'publicStats'])
    ->middleware('throttle:map-public');

Route::prefix('public')->middleware('throttle:map-public')->group(function () {
    Route::get('/governorates', [PublicBrowseController::class, 'governorates']);
    Route::get('/needs/lookups', [PublicBrowseController::class, 'needsLookups']);
    Route::get('/needs/map', [PublicBrowseController::class, 'needsMap']);
    Route::post('/needs', [PublicBrowseController::class, 'storeGuestNeed'])
        ->middleware('throttle:5,10');
    Route::get('/training-programs', [PublicBrowseController::class, 'trainingPrograms']);
    Route::get('/finance/cloud', [PublicBrowseController::class, 'financeCloud']);
    Route::get('/finance/metrics', [PublicBrowseController::class, 'financeMetrics']);
    Route::get('/job-postings', [PublicBrowseController::class, 'jobPostings']);
});



/*

|--------------------------------------------------------------------------

| Protected Routes

|--------------------------------------------------------------------------

*/



Route::middleware('auth:sanctum')->group(function () {

    /*

    |--------------------------------------------------------------------------

    | Auth

    |--------------------------------------------------------------------------

    */

    Route::get('/me',                    [AuthController::class, 'me']);
    Route::put('/me',                    [AuthController::class, 'updateMe']);
    Route::post('/me/change-password',   [AuthController::class, 'changeMyPassword']);

    Route::get('/my-electronic-signature', [\App\Http\Controllers\Api\UserElectronicSignatureController::class, 'show']);
    Route::post('/my-electronic-signature', [\App\Http\Controllers\Api\UserElectronicSignatureController::class, 'store'])
        ->middleware('throttle:file-upload');
    Route::delete('/my-electronic-signature', [\App\Http\Controllers\Api\UserElectronicSignatureController::class, 'destroy']);
    Route::get('/my-electronic-signature/image', [\App\Http\Controllers\Api\UserElectronicSignatureController::class, 'myImage']);
    Route::get('/electronic-signatures/{id}/snapshot-image', [\App\Http\Controllers\Api\UserElectronicSignatureController::class, 'snapshotImage'])
        ->whereNumber('id');

    Route::post('/logout', [AuthController::class, 'logout']);



    /*

    |--------------------------------------------------------------------------

    | Dashboard

    |--------------------------------------------------------------------------

    */

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('dashboard.access');

    Route::get('/governorates', [\App\Http\Controllers\Api\GovernorateController::class, 'index']);
    Route::get('/branches/dashboard', [\App\Http\Controllers\Api\BranchController::class, 'dashboard']);
    Route::get('/branches', [\App\Http\Controllers\Api\BranchController::class, 'index']);
    Route::post('/branches', [\App\Http\Controllers\Api\BranchController::class, 'store']);
    Route::get('/branches/{id}', [\App\Http\Controllers\Api\BranchController::class, 'show'])->whereNumber('id');
    Route::put('/branches/{id}', [\App\Http\Controllers\Api\BranchController::class, 'update'])->whereNumber('id');
    Route::delete('/branches/{id}', [\App\Http\Controllers\Api\BranchController::class, 'destroy'])->whereNumber('id');

    Route::get('/agreements', [\App\Http\Controllers\Api\AgreementController::class, 'index']);
    Route::post('/agreements', [\App\Http\Controllers\Api\AgreementController::class, 'store']);
    Route::get('/agreements/{id}', [\App\Http\Controllers\Api\AgreementController::class, 'show'])->whereNumber('id');
    Route::put('/agreements/{id}', [\App\Http\Controllers\Api\AgreementController::class, 'update'])->whereNumber('id');
    Route::post('/agreements/{id}/approve', [\App\Http\Controllers\Api\AgreementController::class, 'approve'])->whereNumber('id');

    $financeRecordsView = 'role_or_permission:general_director|deputy_general_director|branch_manager|finance_manager|finance_officer|central_bank_admin|auditor|admin|super_admin|system_admin|view_finance|manage_finance';
    $financeRecordsManage = 'role_or_permission:general_director|finance_manager|admin|super_admin|system_admin|manage_finance';

    Route::middleware($financeRecordsView)->group(function () {
        Route::get('/finance/records', [\App\Http\Controllers\Api\FinancialRecordController::class, 'index']);
        Route::get('/finance/records/{id}', [\App\Http\Controllers\Api\FinancialRecordController::class, 'show'])->whereNumber('id');
    });
    Route::post('/finance/records', [\App\Http\Controllers\Api\FinancialRecordController::class, 'store'])
        ->middleware($financeRecordsManage);
    Route::put('/finance/records/{id}', [\App\Http\Controllers\Api\FinancialRecordController::class, 'update'])->whereNumber('id')
        ->middleware($financeRecordsManage);
    Route::post('/finance/records/{id}/approve', [\App\Http\Controllers\Api\FinancialRecordController::class, 'approve'])->whereNumber('id')
        ->middleware($financeRecordsManage);

    $financeApplicationsView = 'role_or_permission:project_owner|consultant_office|funding_partner|finance_manager|finance_officer|central_bank_admin|consultant_union_admin|branch_manager|branch_officer|governor|general_director|deputy_general_director|deputy_director|auditor|admin|super_admin|system_admin|finance.applications.view';
    $financeConsultantsView = 'role_or_permission:consultant_union_admin|consultant_office|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.consultants.view|finance.consultants.view_all';
    $financePartnersView = 'role_or_permission:central_bank_admin|funding_partner|finance_manager|finance_officer|general_director|admin|super_admin|system_admin|auditor|finance.partners.view|finance.partners.view_all';
    $financeLoansView = 'role_or_permission:funding_partner|finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.loans.view|finance.loans.view_own';
    $financeMetricsView = 'role_or_permission:finance_manager|finance_officer|central_bank_admin|branch_manager|general_director|admin|super_admin|system_admin|auditor|finance.metrics.view|finance.metrics.national|finance.metrics.branch';

    Route::prefix('finance/applications')->group(function () use ($financeApplicationsView) {
        Route::middleware($financeApplicationsView)->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\FundingApplicationController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\FundingApplicationController::class, 'show'])->whereNumber('id');
            Route::get('/{applicationId}/documents/{documentId}/download', [\App\Http\Controllers\Api\FundingDocumentController::class, 'download'])->whereNumber(['applicationId', 'documentId']);
        });

        Route::post('/', [\App\Http\Controllers\Api\FundingApplicationController::class, 'store'])
            ->middleware('role_or_permission:project_owner|finance.applications.create|general_director|admin|super_admin|system_admin');

        Route::put('/{id}', [\App\Http\Controllers\Api\FundingApplicationController::class, 'update'])->whereNumber('id')
            ->middleware('role_or_permission:project_owner|finance.applications.update|branch_manager|general_director|admin|super_admin|system_admin');

        Route::post('/{id}/submit', [\App\Http\Controllers\Api\FundingApplicationController::class, 'submit'])->whereNumber('id')
            ->middleware('role_or_permission:project_owner|finance.applications.submit|general_director|admin|super_admin|system_admin');

        Route::post('/{id}/request-completion', [\App\Http\Controllers\Api\FundingApplicationController::class, 'requestCompletion'])->whereNumber('id')
            ->middleware('role_or_permission:branch_manager|finance.applications.request_completion|finance_manager|general_director|admin|super_admin|system_admin');

        Route::post('/{id}/branch-review', [\App\Http\Controllers\Api\FundingApplicationController::class, 'branchReview'])->whereNumber('id')
            ->middleware('role_or_permission:branch_manager|finance.applications.review_branch|general_director|admin|super_admin|system_admin');

        Route::post('/{id}/approve', [\App\Http\Controllers\Api\FundingApplicationController::class, 'approve'])->whereNumber('id')
            ->middleware('role_or_permission:finance_manager|finance.applications.approve|general_director|admin|super_admin|system_admin');

        Route::post('/{id}/reject', [\App\Http\Controllers\Api\FundingApplicationController::class, 'reject'])->whereNumber('id')
            ->middleware('role_or_permission:branch_manager|finance_manager|finance.applications.reject|general_director|admin|super_admin|system_admin');

        Route::post('/{id}/assign-consultant', [\App\Http\Controllers\Api\FundingApplicationController::class, 'assignConsultant'])->whereNumber('id')
            ->middleware('role_or_permission:branch_manager|finance_manager|finance.applications.assign_consultant|general_director|admin|super_admin|system_admin');

        Route::post('/{id}/assign-partner', [\App\Http\Controllers\Api\FundingApplicationController::class, 'assignPartner'])->whereNumber('id')
            ->middleware('role_or_permission:finance_manager|central_bank_admin|finance.applications.assign_partner|general_director|admin|super_admin|system_admin');

        Route::post('/{id}/create-loan', [\App\Http\Controllers\Api\FundingApplicationController::class, 'createLoan'])->whereNumber('id')
            ->middleware('role_or_permission:finance_manager|finance.loans.manage|general_director|admin|super_admin|system_admin');

        Route::post('/{applicationId}/documents', [\App\Http\Controllers\Api\FundingDocumentController::class, 'store'])
            ->whereNumber('applicationId')
            ->middleware([$financeApplicationsView, 'throttle:file-upload']);
    });

    Route::get('/finance/consultant-union/dashboard', [\App\Http\Controllers\Api\FundingConsultantController::class, 'unionDashboard'])
        ->middleware('role_or_permission:consultant_union_admin|finance.consultant_union.dashboard');

    Route::middleware($financeConsultantsView)->group(function () {
        Route::get('/finance/consultant-assignments', [\App\Http\Controllers\Api\FundingConsultantController::class, 'indexAssignments']);
        Route::get('/finance/consultant-offices', [\App\Http\Controllers\Api\FundingConsultantController::class, 'indexOffices']);
        Route::get('/finance/consultant-offices/{id}', [\App\Http\Controllers\Api\FundingConsultantController::class, 'showOffice'])->whereNumber('id');
        Route::get('/finance/consultant-offices/{id}/assignments', [\App\Http\Controllers\Api\FundingConsultantController::class, 'officeAssignments'])->whereNumber('id');
        Route::get('/finance/consultant-offices/{id}/reports', [\App\Http\Controllers\Api\FundingConsultantController::class, 'officeReports'])->whereNumber('id');
        Route::get('/finance/consultant-offices/{id}/metrics', [\App\Http\Controllers\Api\FundingConsultantController::class, 'officeMetrics'])->whereNumber('id');
    });
    Route::post('/finance/consultant-offices', [\App\Http\Controllers\Api\FundingConsultantController::class, 'storeOffice'])
        ->middleware('role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.create|finance.consultants.manage');
    Route::put('/finance/consultant-offices/{id}', [\App\Http\Controllers\Api\FundingConsultantController::class, 'updateOffice'])->whereNumber('id')
        ->middleware('role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.update|finance.consultants.manage');
    Route::post('/finance/consultant-offices/{id}/approve', [\App\Http\Controllers\Api\FundingConsultantController::class, 'approveOffice'])->whereNumber('id')
        ->middleware('role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.approve|finance.consultants.manage');
    Route::post('/finance/consultant-offices/{id}/activate', [\App\Http\Controllers\Api\FundingConsultantController::class, 'activateOffice'])->whereNumber('id')
        ->middleware('role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.activate|finance.consultants.manage');
    Route::post('/finance/consultant-offices/{id}/suspend', [\App\Http\Controllers\Api\FundingConsultantController::class, 'suspendOffice'])->whereNumber('id')
        ->middleware('role_or_permission:consultant_union_admin|general_director|admin|super_admin|system_admin|finance.consultants.suspend|finance.consultants.manage');

    Route::get('/finance/consultant-office/dashboard', [\App\Http\Controllers\Api\FundingConsultantController::class, 'officeDashboard'])
        ->middleware('role:consultant_office');
    Route::get('/finance/my-consultant-assignments', [\App\Http\Controllers\Api\FundingConsultantController::class, 'myAssignments'])
        ->middleware('role:consultant_office');
    Route::post('/finance/consultant-assignments/{id}/accept', [\App\Http\Controllers\Api\FundingConsultantController::class, 'acceptAssignment'])->whereNumber('id')
        ->middleware('role_or_permission:consultant_office|finance.consultant_assignments.accept');
    Route::post('/finance/consultant-assignments/{id}/reject', [\App\Http\Controllers\Api\FundingConsultantController::class, 'rejectAssignment'])->whereNumber('id')
        ->middleware('role_or_permission:consultant_office|finance.consultant_assignments.reject');
    Route::post('/finance/consultant-assignments/{id}/price-offer', [\App\Http\Controllers\Api\FundingConsultantController::class, 'priceOffer'])->whereNumber('id')
        ->middleware('role_or_permission:consultant_office|finance.consultant_assignments.submit_price|finance.consultants.submit_price');
    Route::post('/finance/consultant-assignments/{id}/approve-price', [\App\Http\Controllers\Api\FundingConsultantController::class, 'approvePrice'])->whereNumber('id')
        ->middleware('role_or_permission:branch_manager|finance_manager|general_director|admin|super_admin|system_admin|finance.consultants.approve_price');
    Route::post('/finance/consultant-reports', [\App\Http\Controllers\Api\FundingConsultantController::class, 'storeReport'])
        ->middleware('role_or_permission:consultant_office|general_director|admin|super_admin|system_admin|finance.consultant_reports.create|finance.consultants.submit_report');

    Route::get('/finance/central-bank/dashboard', [\App\Http\Controllers\Api\FundingPartnerController::class, 'centralBankDashboard'])
        ->middleware('role_or_permission:central_bank_admin|finance.central_bank.dashboard');
    Route::get('/finance/funding-partner/dashboard', [\App\Http\Controllers\Api\FundingPartnerController::class, 'partnerDashboard'])
        ->middleware('role:funding_partner');
    Route::middleware($financePartnersView)->group(function () {
        Route::get('/finance/partners', [\App\Http\Controllers\Api\FundingPartnerController::class, 'index']);
        Route::get('/finance/partners/{id}', [\App\Http\Controllers\Api\FundingPartnerController::class, 'show'])->whereNumber('id');
        Route::get('/finance/partners/{id}/assignments', [\App\Http\Controllers\Api\FundingPartnerController::class, 'partnerAssignments'])->whereNumber('id');
        Route::get('/finance/partners/{id}/decisions', [\App\Http\Controllers\Api\FundingPartnerController::class, 'partnerDecisions'])->whereNumber('id');
        Route::get('/finance/partners/{id}/loans', [\App\Http\Controllers\Api\FundingPartnerController::class, 'partnerLoans'])->whereNumber('id');
        Route::get('/finance/partners/{id}/metrics', [\App\Http\Controllers\Api\FundingPartnerController::class, 'partnerMetrics'])->whereNumber('id');
    });
    Route::post('/finance/partners', [\App\Http\Controllers\Api\FundingPartnerController::class, 'store'])
        ->middleware('role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.create|finance.partners.manage');
    Route::put('/finance/partners/{id}', [\App\Http\Controllers\Api\FundingPartnerController::class, 'update'])->whereNumber('id')
        ->middleware('role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.update|finance.partners.manage');
    Route::post('/finance/partners/{id}/approve', [\App\Http\Controllers\Api\FundingPartnerController::class, 'approvePartner'])->whereNumber('id')
        ->middleware('role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.approve|finance.partners.manage');
    Route::post('/finance/partners/{id}/activate', [\App\Http\Controllers\Api\FundingPartnerController::class, 'activatePartner'])->whereNumber('id')
        ->middleware('role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.activate|finance.partners.manage');
    Route::post('/finance/partners/{id}/suspend', [\App\Http\Controllers\Api\FundingPartnerController::class, 'suspendPartner'])->whereNumber('id')
        ->middleware('role_or_permission:central_bank_admin|general_director|admin|super_admin|system_admin|finance.partners.suspend|finance.partners.manage');

    Route::get('/finance/my-partner-assignments', [\App\Http\Controllers\Api\FundingPartnerController::class, 'myAssignments'])
        ->middleware('role:funding_partner');
    Route::post('/finance/partner-assignments/{id}/decision', [\App\Http\Controllers\Api\FundingPartnerController::class, 'decision'])->whereNumber('id')
        ->middleware('role_or_permission:funding_partner|central_bank_admin|finance_manager|general_director|admin|super_admin|system_admin|finance.partner_assignments.decide|finance.partners.decide');

    Route::middleware($financeLoansView)->group(function () {
        Route::get('/finance/loans/stats', [\App\Http\Controllers\Api\FundedLoanController::class, 'stats']);
        Route::get('/finance/loans', [\App\Http\Controllers\Api\FundedLoanController::class, 'index']);
        Route::get('/finance/loans/{id}', [\App\Http\Controllers\Api\FundedLoanController::class, 'show'])->whereNumber('id');
        Route::get('/finance/loans/{id}/payments', [\App\Http\Controllers\Api\FundedLoanController::class, 'payments'])->whereNumber('id');
    });
    Route::put('/finance/loans/{id}', [\App\Http\Controllers\Api\FundedLoanController::class, 'update'])->whereNumber('id')
        ->middleware('role_or_permission:finance_manager|funding_partner|general_director|admin|super_admin|system_admin|finance.loans.manage|finance.loans.update_own_status');
    Route::post('/finance/loans/{id}/payments', [\App\Http\Controllers\Api\FundedLoanController::class, 'storePayment'])->whereNumber('id')
        ->middleware('role_or_permission:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.payments|finance.loans.manage');
    Route::post('/finance/loans/{id}/mark-defaulted', [\App\Http\Controllers\Api\FundedLoanController::class, 'markDefaulted'])->whereNumber('id')
        ->middleware('role_or_permission:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.defaulted|finance.loans.manage');
    Route::post('/finance/loans/{id}/close', [\App\Http\Controllers\Api\FundedLoanController::class, 'close'])->whereNumber('id')
        ->middleware('role_or_permission:finance_manager|general_director|admin|super_admin|system_admin|finance.loans.close|finance.loans.manage');

    Route::middleware($financeMetricsView)->group(function () {
        Route::get('/finance/metrics', [\App\Http\Controllers\Api\FundingMetricsController::class, 'metrics']);
        Route::get('/finance/funded/stats', [\App\Http\Controllers\Api\FundingMetricsController::class, 'fundedStats']);
        Route::get('/finance/funded', [\App\Http\Controllers\Api\FundingMetricsController::class, 'funded']);
        Route::get('/finance/defaulted/stats', [\App\Http\Controllers\Api\FundingMetricsController::class, 'defaultedStats']);
        Route::get('/finance/defaulted', [\App\Http\Controllers\Api\FundingMetricsController::class, 'defaulted']);
        Route::get('/finance/cloud', [\App\Http\Controllers\Api\FundingMetricsController::class, 'cloud']);
    });
    Route::get('/finance/manager/dashboard', [\App\Http\Controllers\Api\FundingMetricsController::class, 'managerDashboard'])
        ->middleware('role_or_permission:finance_manager|finance_officer|general_director|admin|super_admin|system_admin|finance.metrics.view');

    $needsView = 'role_or_permission:needs.view|needs.view_all|needs.view_branch';
    $needsCreate = 'role_or_permission:needs.create|needs.create_citizen|needs.create_state';

    Route::middleware($needsView)->group(function () {
        Route::get('/needs', [\App\Http\Controllers\Api\NeedController::class, 'index']);
        Route::get('/needs/{id}', [\App\Http\Controllers\Api\NeedController::class, 'show'])->whereNumber('id');
        Route::get('/needs/map', [\App\Http\Controllers\Api\NeedController::class, 'map']);
        Route::get('/needs/lookups', [\App\Http\Controllers\Api\NeedController::class, 'lookups']);
        Route::get('/needs/admin-units', [\App\Http\Controllers\Api\NeedController::class, 'adminUnits']);
        Route::get('/needs/export', [\App\Http\Controllers\Api\NeedController::class, 'export']);
    });

    Route::post('/needs', [\App\Http\Controllers\Api\NeedController::class, 'store'])
        ->middleware($needsCreate);
    Route::get('/needs/dashboard', [\App\Http\Controllers\Api\NeedController::class, 'dashboard'])
        ->middleware('permission:needs.dashboard');
    Route::get('/needs/analytics', [\App\Http\Controllers\Api\NeedController::class, 'analytics']);
    Route::get('/needs/workspace/data-entry', [\App\Http\Controllers\Api\NeedController::class, 'dataEntryWorkspace'])
        ->middleware('role:data_entry');
    Route::get('/needs/workspace/reviewer', [\App\Http\Controllers\Api\NeedController::class, 'reviewerWorkspace'])
        ->middleware('role:data_reviewer');
    // إدارة القوائم المرجعية للاحتياجات (تفعيل/تعطيل/ترتيب — بدون حذف)
    Route::middleware('permission:needs.manage_lookups')->group(function () {
        Route::get('/needs/lookups/manage', [\App\Http\Controllers\Api\NeedLookupAdminController::class, 'index']);
        Route::post('/needs/lookups/manage', [\App\Http\Controllers\Api\NeedLookupAdminController::class, 'storeLookup']);
        Route::put('/needs/lookups/manage/{id}', [\App\Http\Controllers\Api\NeedLookupAdminController::class, 'updateLookup'])->whereNumber('id');
        Route::post('/needs/sectors', [\App\Http\Controllers\Api\NeedLookupAdminController::class, 'storeSector']);
        Route::put('/needs/sectors/{id}', [\App\Http\Controllers\Api\NeedLookupAdminController::class, 'updateSector'])->whereNumber('id');
    });

    Route::put('/needs/{id}', [\App\Http\Controllers\Api\NeedController::class, 'update'])->whereNumber('id');
    Route::post('/needs/{id}/review', [\App\Http\Controllers\Api\NeedController::class, 'review'])->whereNumber('id');
    Route::post('/needs/{id}/approve', [\App\Http\Controllers\Api\NeedController::class, 'approve'])->whereNumber('id');
    Route::post('/needs/{id}/reject', [\App\Http\Controllers\Api\NeedController::class, 'reject'])->whereNumber('id');
    Route::post('/needs/{id}/return', [\App\Http\Controllers\Api\NeedController::class, 'returnForEdit'])->whereNumber('id');
    Route::post('/needs/ai-suggest', [\App\Http\Controllers\Api\NeedController::class, 'aiSuggest']);
    Route::post('/needs/{id}/ai-suggest', [\App\Http\Controllers\Api\NeedController::class, 'aiSuggestForNeed'])->whereNumber('id');
    Route::post('/needs/{id}/classify', [\App\Http\Controllers\Api\NeedController::class, 'classify'])->whereNumber('id');
    Route::post('/needs/{id}/resolve', [\App\Http\Controllers\Api\NeedController::class, 'resolve'])->whereNumber('id');

    /* ═══ المستشار الذكي (بروكسي محادثة AI) ═══ */
    Route::middleware('throttle:ai-chat')->group(function () {
        Route::post('/ai/chat', [\App\Http\Controllers\Api\AiChatController::class, 'chat']);
        Route::post('/ai/chat/continue', [\App\Http\Controllers\Api\AiChatController::class, 'continueReply']);
        Route::post('/ai/chat/reset', [\App\Http\Controllers\Api\AiChatController::class, 'reset']);
        Route::post('/ai/isic4/classify', [\App\Http\Controllers\Api\AiChatController::class, 'isic4']);
        Route::get('/ai/config', [\App\Http\Controllers\Api\AiChatController::class, 'config']);
        Route::get('/ai/chat/history', [\App\Http\Controllers\Api\AiChatController::class, 'history']);
        Route::get('/ai/chat/history/{session}/messages', [\App\Http\Controllers\Api\AiChatController::class, 'historyMessages']);
        Route::post('/ai/chat/history/{session}/resume', [\App\Http\Controllers\Api\AiChatController::class, 'resume']);
        Route::get('/ai/knowledge/departments', [\App\Http\Controllers\Api\AiChatController::class, 'knowledgeDepartments']);
        Route::get('/ai/knowledge/{department}', [\App\Http\Controllers\Api\AiChatController::class, 'knowledgeItems'])
            ->where('department', '[A-Za-z0-9_-]+');
        Route::post('/ai/knowledge/ingest', [\App\Http\Controllers\Api\AiChatController::class, 'knowledgeIngest'])
            ->middleware('permission:manage_ai_knowledge');
    });



    /*

    |--------------------------------------------------------------------------

    | Trainers

    |--------------------------------------------------------------------------

    */

    Route::prefix('trainers')->middleware('permission:view_trainers')->group(function () {

        Route::get('/', [TrainerController::class, 'index']);

        Route::post('/', [TrainerController::class, 'store'])
            ->middleware('permission:manage_trainers');

        Route::get('/{id}', [TrainerController::class, 'show'])->whereNumber('id');

        Route::put('/{id}', [TrainerController::class, 'update'])
            ->middleware('permission:manage_trainers')
            ->whereNumber('id');

        Route::patch('/{id}', [TrainerController::class, 'update'])
            ->middleware('permission:manage_trainers')
            ->whereNumber('id');

    });



    /*

    |--------------------------------------------------------------------------

    | Trainer Profiles

    |--------------------------------------------------------------------------

    */

    Route::get('/trainer-profiles/{id}', [TrainerProfileController::class, 'show'])

        ->whereNumber('id')

        ->middleware('permission:view_trainer_profiles');



    Route::get('/my-trainer-profile', [TrainerProfileController::class, 'myProfile'])

        ->middleware('role_or_permission:view_trainer_profiles|edit_own_trainer_profile');



    Route::post('/my-trainer-profile', [TrainerProfileController::class, 'updateMyProfile'])

        ->middleware('permission:edit_own_trainer_profile');



    /*

    |--------------------------------------------------------------------------

    | Training Kit Nominations

    |--------------------------------------------------------------------------

    */

    Route::prefix('training-kit-nominations')->group(function () {

        Route::get('/', [TrainingKitNominationController::class, 'index'])

            ->middleware('role_or_permission:nominate_training_kits|review_training_kit_nominations');



        Route::post('/', [TrainingKitNominationController::class, 'store'])

            ->middleware('permission:nominate_training_kits');



        Route::get('/{id}', [TrainingKitNominationController::class, 'show'])

            ->whereNumber('id')

            ->middleware('role_or_permission:nominate_training_kits|review_training_kit_nominations');



        Route::post('/{id}/review', [TrainingKitNominationController::class, 'review'])

            ->whereNumber('id')

            ->middleware('permission:review_training_kit_nominations');

    });



    /*

    |--------------------------------------------------------------------------

    | Trainees

    |--------------------------------------------------------------------------

    */

    Route::prefix('trainees')->middleware('permission:view_trainees')->group(function () {

        Route::get('/', [TraineeController::class, 'index']);

        Route::post('/', [TraineeController::class, 'store'])
            ->middleware('permission:manage_trainees');

        Route::get('/{id}', [TraineeController::class, 'show'])->whereNumber('id');

        Route::put('/{id}', [TraineeController::class, 'update'])
            ->middleware('permission:manage_trainees')
            ->whereNumber('id');

        Route::patch('/{id}', [TraineeController::class, 'update'])
            ->middleware('permission:manage_trainees')
            ->whereNumber('id');

    });



    /*
    |--------------------------------------------------------------------------
    | Workforce — candidates + jobs platform
    |--------------------------------------------------------------------------
    */
    Route::prefix('workforces')->middleware('role_or_permission:general_director|deputy_general_director|branch_manager|auditor|admin|super_admin|system_admin|training_manager|development_manager|workforce_manager')->group(function () {
        Route::get('/', [WorkforceController::class, 'index']);
        Route::get('/{id}', [WorkforceController::class, 'show'])->whereNumber('id');
    });
    Route::post('/workforces/enroll', [WorkforceController::class, 'enroll'])
        ->middleware('role_or_permission:general_director|admin|super_admin|system_admin|training_manager');

    Route::prefix('workforce')->group(function () {
        Route::middleware('permission:workforce.jobs.view')->group(function () {
            Route::get('/job-postings', [\App\Http\Controllers\Api\JobPostingController::class, 'index']);
            Route::get('/job-postings/{id}', [\App\Http\Controllers\Api\JobPostingController::class, 'show'])->whereNumber('id');
        });
        Route::post('/job-postings', [\App\Http\Controllers\Api\JobPostingController::class, 'store'])
            ->middleware('permission:workforce.jobs.create');
        Route::put('/job-postings/{id}', [\App\Http\Controllers\Api\JobPostingController::class, 'update'])->whereNumber('id')
            ->middleware('permission:workforce.jobs.manage');

        Route::middleware('permission:workforce.applications.view')->group(function () {
            Route::get('/job-applications', [\App\Http\Controllers\Api\JobApplicationController::class, 'index']);
            Route::put('/job-applications/{id}', [\App\Http\Controllers\Api\JobApplicationController::class, 'update'])->whereNumber('id');
        });
        Route::post('/job-applications', [\App\Http\Controllers\Api\JobApplicationController::class, 'store'])
            ->middleware('permission:workforce.applications.create');

        Route::middleware('permission:workforce.training_requests.view')->group(function () {
            Route::get('/staff-training-requests', [\App\Http\Controllers\Api\StaffTrainingRequestController::class, 'index']);
            Route::put('/staff-training-requests/{id}', [\App\Http\Controllers\Api\StaffTrainingRequestController::class, 'update'])->whereNumber('id');
        });
        Route::post('/staff-training-requests', [\App\Http\Controllers\Api\StaffTrainingRequestController::class, 'store'])
            ->middleware('permission:workforce.training_requests.create');
    });



    /*

    |--------------------------------------------------------------------------

    | Training Centers

    |--------------------------------------------------------------------------

    */

    Route::prefix('training-centers')->middleware('permission:view_centers')->group(function () {

        Route::get('/', [TrainingCenterController::class, 'index']);

        Route::get('/{id}', [TrainingCenterController::class, 'show'])->whereNumber('id');

    });

    Route::get('/training-supervisors', [TrainingSupervisorController::class, 'index'])
        ->middleware('permission:view_centers');



    /*

    |--------------------------------------------------------------------------

    | Training Kits

    |--------------------------------------------------------------------------

    */

    Route::prefix('training-categories')->middleware('permission:view_kits')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\TrainingCategoryController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\TrainingCategoryController::class, 'store'])
            ->middleware('permission:manage_training_categories');
        Route::put('/{id}', [\App\Http\Controllers\Api\TrainingCategoryController::class, 'update'])
            ->middleware('permission:manage_training_categories')
            ->whereNumber('id');
        Route::delete('/{id}', [\App\Http\Controllers\Api\TrainingCategoryController::class, 'destroy'])
            ->middleware('permission:manage_training_categories')
            ->whereNumber('id');
    });

    Route::prefix('training-kits')->middleware('permission:view_kits')->group(function () {

        Route::get('/', [TrainingKitController::class, 'index']);

        Route::post('/', [TrainingKitController::class, 'store'])
            ->middleware('permission:manage_kits');

        Route::get('/{id}', [TrainingKitController::class, 'show'])->whereNumber('id');

        Route::put('/{id}', [TrainingKitController::class, 'update'])
            ->middleware('permission:manage_kits')
            ->whereNumber('id');

        Route::patch('/{id}', [TrainingKitController::class, 'update'])
            ->middleware('permission:manage_kits')
            ->whereNumber('id');

        Route::post('/{id}/promotional-file', [TrainingKitController::class, 'uploadPromotionalFile'])
            ->middleware(['permission:manage_kits', 'throttle:file-upload'])
            ->whereNumber('id');

        Route::post('/{id}/training-bag-file', [TrainingKitController::class, 'uploadTrainingBagFile'])
            ->middleware(['permission:manage_kits', 'throttle:file-upload'])
            ->whereNumber('id');

        Route::get('/{id}/promotional-file', [TrainingKitController::class, 'downloadPromotionalFile'])
            ->whereNumber('id');

        Route::get('/{id}/training-bag-file', [TrainingKitController::class, 'downloadTrainingBagFile'])
            ->whereNumber('id');

        // مواد الحقيبة (وحدات/محاور داخل الحقيبة)
        $km = \App\Http\Controllers\Api\KitMaterialController::class;
        Route::get('/{id}/materials', [$km, 'index'])->whereNumber('id');
        Route::post('/{id}/materials', [$km, 'store'])->whereNumber('id')->middleware('permission:manage_kits');
        Route::put('/{id}/materials/{materialId}', [$km, 'update'])->whereNumber('id')->whereNumber('materialId')->middleware('permission:manage_kits');
        Route::delete('/{id}/materials/{materialId}', [$km, 'destroy'])->whereNumber('id')->whereNumber('materialId')->middleware('permission:manage_kits');

    });



    /*

    |--------------------------------------------------------------------------

    | Training Programs

    |--------------------------------------------------------------------------

    */

    Route::prefix('training-programs')->middleware('permission:view_programs')->group(function () {

        Route::get('/', [TrainingProgramController::class, 'index']);

        Route::get('/{id}', [TrainingProgramController::class, 'show'])->whereNumber('id');

    });

    /*
    |--------------------------------------------------------------------------
    | Program Bank (بنك البرامج والحقائب التدريبية)
    |--------------------------------------------------------------------------
    */
    $programBankView = 'role_or_permission:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.view|view_programs|manage_programs';
    $programBankCreate = 'role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.create|manage_programs';
    $programBankUpdate = 'role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.update|manage_programs';
    $programBankDelete = 'role_or_permission:training_manager|general_director|admin|super_admin|system_admin|program_bank.delete|manage_programs';
    $programBankApprove = 'role_or_permission:training_manager|general_director|admin|super_admin|system_admin|deputy_director|program_bank.approve|manage_programs';
    $programBankReports = 'role_or_permission:training_manager|general_director|admin|super_admin|system_admin|auditor|program_bank.reports|program_bank.view|view_reports';

    Route::prefix('program-bank')->group(function () use ($programBankView, $programBankCreate, $programBankUpdate, $programBankDelete, $programBankApprove, $programBankReports) {

        // إحصائيات الداشبورد
        Route::get('/stats', [\App\Http\Controllers\Api\ProgramBankController::class, 'stats'])->middleware($programBankView);

        // تقارير
        Route::get('/reports', [\App\Http\Controllers\Api\ProgramBankController::class, 'reports'])->middleware($programBankReports);

        // CRUD البرامج
        Route::get('/',         [\App\Http\Controllers\Api\ProgramBankController::class, 'index'])->middleware($programBankView);
        Route::post('/',        [\App\Http\Controllers\Api\ProgramBankController::class, 'store'])->middleware($programBankCreate);
        Route::get('/{id}',     [\App\Http\Controllers\Api\ProgramBankController::class, 'show'])->whereNumber('id')->middleware($programBankView);
        Route::put('/{id}',     [\App\Http\Controllers\Api\ProgramBankController::class, 'update'])->whereNumber('id')->middleware($programBankUpdate);
        Route::delete('/{id}',  [\App\Http\Controllers\Api\ProgramBankController::class, 'destroy'])->whereNumber('id')->middleware($programBankDelete);
        Route::post('/{id}/duplicate', [\App\Http\Controllers\Api\ProgramBankController::class, 'duplicate'])->whereNumber('id')->middleware($programBankCreate);

        // سير اعتماد
        Route::post('/{id}/transition', [\App\Http\Controllers\Api\ProgramBankController::class, 'transition'])->whereNumber('id')->middleware($programBankApprove);

        // إنشاء دورة من برنامج
        Route::post('/{id}/create-course', [\App\Http\Controllers\Api\ProgramBankController::class, 'createCourseFromProgram'])->whereNumber('id')->middleware($programBankCreate);

        // المحاور
        Route::post('/{id}/modules',              [\App\Http\Controllers\Api\ProgramBankController::class, 'storeModule'])->whereNumber('id')->middleware($programBankUpdate);
        Route::put('/{id}/modules/{moduleId}',    [\App\Http\Controllers\Api\ProgramBankController::class, 'updateModule'])->whereNumber('id')->whereNumber('moduleId')->middleware($programBankUpdate);
        Route::delete('/{id}/modules/{moduleId}', [\App\Http\Controllers\Api\ProgramBankController::class, 'destroyModule'])->whereNumber('id')->whereNumber('moduleId')->middleware($programBankUpdate);
        Route::put('/{id}/modules/reorder',       [\App\Http\Controllers\Api\ProgramBankController::class, 'reorderModules'])->whereNumber('id')->middleware($programBankUpdate);

        // المخرجات
        Route::post('/{id}/outcomes',              [\App\Http\Controllers\Api\ProgramBankController::class, 'storeOutcome'])->whereNumber('id')->middleware($programBankUpdate);
        Route::put('/{id}/outcomes/{outcomeId}',   [\App\Http\Controllers\Api\ProgramBankController::class, 'updateOutcome'])->whereNumber('id')->whereNumber('outcomeId')->middleware($programBankUpdate);
        Route::delete('/{id}/outcomes/{outcomeId}',[\App\Http\Controllers\Api\ProgramBankController::class, 'destroyOutcome'])->whereNumber('id')->whereNumber('outcomeId')->middleware($programBankUpdate);

        // روابط الخدمات
        Route::put('/{id}/service-links', [\App\Http\Controllers\Api\ProgramBankController::class, 'syncServiceLinks'])->whereNumber('id')->middleware($programBankUpdate);

    });

    /*
    |--------------------------------------------------------------------------
    | Training Courses

    |--------------------------------------------------------------------------

    */

    Route::prefix('training-courses')->group(function () {

        Route::get('/', [TrainingCourseController::class, 'index'])

            ->middleware('role_or_permission:trainer_user|trainee_user|view_courses');



        Route::post('/', [TrainingCourseController::class, 'store'])

            ->middleware('permission:manage_courses');



        Route::get('/{id}/trainees', [TrainingCourseController::class, 'trainees'])

            ->whereNumber('id')

            ->middleware('role_or_permission:view_courses|view_course_details');



        Route::get('/{id}/modules', [TrainingCourseController::class, 'modules'])

            ->whereNumber('id')

            ->middleware('role_or_permission:view_courses|view_course_details');



        // --- الحضور لكل جلسة (م2) ---

        Route::get('/{id}/sessions', [\App\Http\Controllers\Api\CourseSessionController::class, 'index'])

            ->whereNumber('id')

            ->middleware('role_or_permission:view_courses|view_course_details');

        Route::post('/{id}/sessions', [\App\Http\Controllers\Api\CourseSessionController::class, 'store'])

            ->whereNumber('id')

            ->middleware('role_or_permission:manage_courses|manage_trainees|trainer_user');

        Route::get('/{id}/sessions/{sessionId}/attendance', [\App\Http\Controllers\Api\CourseSessionController::class, 'attendanceIndex'])

            ->whereNumber('id')->whereNumber('sessionId')

            ->middleware('role_or_permission:view_courses|view_course_details');

        Route::post('/{id}/sessions/{sessionId}/attendance', [\App\Http\Controllers\Api\CourseSessionController::class, 'attendanceStore'])

            ->whereNumber('id')->whereNumber('sessionId')

            ->middleware('role_or_permission:manage_courses|manage_trainees|trainer_user');



        // --- الدرجات لكل محور (م3) ---

        Route::get('/{id}/module-scores', [\App\Http\Controllers\Api\ModuleScoreController::class, 'index'])

            ->whereNumber('id')

            ->middleware('role_or_permission:view_courses|view_course_details');

        Route::post('/{id}/module-scores', [\App\Http\Controllers\Api\ModuleScoreController::class, 'store'])

            ->whereNumber('id')

            ->middleware('role_or_permission:manage_courses|manage_trainees|trainer_user');



        // --- إصدار الشهادات للناجحين دفعة واحدة (م4) ---

        Route::post('/{id}/issue-certificates', [CertificateController::class, 'issueForCourse'])

            ->whereNumber('id')

            ->middleware('permission:issue_certificates');



        // --- الصفوف/المجموعات داخل الدورة ---

        $cg = \App\Http\Controllers\Api\CourseGroupController::class;

        Route::get('/{id}/groups', [$cg, 'index'])->whereNumber('id')

            ->middleware('role_or_permission:view_courses|view_course_details');

        Route::get('/{id}/ungrouped-trainees', [$cg, 'ungrouped'])->whereNumber('id')

            ->middleware('role_or_permission:view_courses|view_course_details');

        Route::get('/{id}/groups/{groupId}/trainees', [$cg, 'trainees'])->whereNumber('id')->whereNumber('groupId')

            ->middleware('role_or_permission:view_courses|view_course_details');

        Route::post('/{id}/groups', [$cg, 'store'])->whereNumber('id')

            ->middleware('role_or_permission:manage_courses|manage_trainees|trainer_user');

        Route::delete('/{id}/groups/{groupId}', [$cg, 'destroy'])->whereNumber('id')->whereNumber('groupId')

            ->middleware('role_or_permission:manage_courses|manage_trainees|trainer_user');

        Route::post('/{id}/groups/{groupId}/assign', [$cg, 'assign'])->whereNumber('id')->whereNumber('groupId')

            ->middleware('role_or_permission:manage_courses|manage_trainees|trainer_user');

        Route::post('/{id}/groups/{groupId}/remove', [$cg, 'remove'])->whereNumber('id')->whereNumber('groupId')

            ->middleware('role_or_permission:manage_courses|manage_trainees|trainer_user');



        Route::post('/{id}/trainees', [TrainingCourseController::class, 'addTrainee'])

            ->whereNumber('id')

            ->middleware('permission:manage_courses');



        Route::patch('/{id}/trainees/{traineeId}', [TrainingCourseController::class, 'updateTrainee'])

            ->whereNumber('id')

            ->whereNumber('traineeId')

            ->middleware('permission:manage_courses');



        Route::delete('/{id}/trainees/{traineeId}', [TrainingCourseController::class, 'removeTrainee'])

            ->whereNumber('id')

            ->whereNumber('traineeId')

            ->middleware('permission:manage_courses');



        Route::post('/{id}/complete', [TrainingCourseController::class, 'complete'])

            ->whereNumber('id')

            ->middleware('permission:manage_courses');



        Route::get('/{id}', [TrainingCourseController::class, 'show'])

            ->whereNumber('id')

            ->middleware('role_or_permission:trainer_user|trainee_user|view_courses|view_course_details')

            ->name('api.courses.show');



        Route::patch('/{id}', [TrainingCourseController::class, 'update'])

            ->whereNumber('id')

            ->middleware('permission:manage_courses');

        Route::delete('/{id}', [TrainingCourseController::class, 'destroy'])

            ->whereNumber('id')

            ->middleware('permission:manage_courses');

    });



    /*

    |--------------------------------------------------------------------------

    | Training Map (internal)

    |--------------------------------------------------------------------------

    */

    Route::prefix('map')->group(function () {

        Route::get('/training-courses', [TrainingMapController::class, 'courses'])

            ->middleware(['permission:view_courses', 'throttle:map-public']);

        Route::get('/trainers', [TrainingMapController::class, 'trainers'])

            ->middleware(['permission:view_trainers', 'throttle:map-public']);

        Route::get('/trainees', [TrainingMapController::class, 'trainees'])

            ->middleware(['permission:view_trainees', 'throttle:map-public']);

    });



    /*

    |--------------------------------------------------------------------------

    | Certificates

    |--------------------------------------------------------------------------

    */

    Route::prefix('certificates')->group(function () {

        Route::post('/issue', [CertificateController::class, 'issue'])

            ->middleware('permission:issue_certificates');



        Route::post('/{id}/approve', [CertificateController::class, 'approve'])

            ->whereNumber('id')

            ->middleware('role_or_permission:approve_center_certificates|approve_training_certificates|approve_deputy_certificates|approve_general_director_certificates');



        Route::get('/', [CertificateController::class, 'index'])

            ->middleware('permission:view_certificates');



        Route::get('/code/{certificate_code}', [CertificateController::class, 'showByCode'])

            ->middleware('permission:view_certificates');



        Route::get('/{id}', [CertificateController::class, 'show'])

            ->whereNumber('id')

            ->middleware('permission:view_certificates');

    });



    /*

    |--------------------------------------------------------------------------

    | Registration Requests - Training Centers

    |--------------------------------------------------------------------------

    */

    Route::prefix('registration-requests/centers')->group(function () {

        Route::get('/', [TrainingCenterRegistrationRequestController::class, 'index'])

            ->middleware('permission:view_registration_requests');



        Route::post('/', [TrainingCenterRegistrationRequestController::class, 'store'])

            ->middleware(['permission:create_center_registration_requests', 'throttle:registration-requests', 'throttle:file-upload']);



        Route::get('/{id}', [TrainingCenterRegistrationRequestController::class, 'show'])

            ->whereNumber('id');



        Route::post('/{id}/review', [TrainingCenterRegistrationRequestController::class, 'review'])

            ->whereNumber('id')

            ->middleware('permission:review_center_registration_requests');

    });



    /*

    |--------------------------------------------------------------------------

    | Registration Requests - Trainers

    |--------------------------------------------------------------------------

    */

    Route::prefix('registration-requests/trainers')->group(function () {

        Route::get('/', [TrainerRegistrationRequestController::class, 'index'])

            ->middleware('permission:view_registration_requests');



        Route::post('/', [TrainerRegistrationRequestController::class, 'store'])

            ->middleware(['permission:create_trainer_registration_requests', 'throttle:registration-requests']);



        Route::get('/{id}', [TrainerRegistrationRequestController::class, 'show'])

            ->whereNumber('id');



        Route::post('/{id}/review', [TrainerRegistrationRequestController::class, 'review'])

            ->whereNumber('id')

            ->middleware('permission:review_trainer_registration_requests');

    });



    /*

    |--------------------------------------------------------------------------

    | Registration Requests - Trainees

    |--------------------------------------------------------------------------

    */

    Route::prefix('registration-requests/trainees')->group(function () {

        Route::get('/', [TraineeRegistrationRequestController::class, 'index'])

            ->middleware('permission:view_registration_requests');



        Route::post('/', [TraineeRegistrationRequestController::class, 'store'])

            ->middleware(['permission:create_trainee_registration_requests', 'throttle:registration-requests']);



        Route::get('/{id}', [TraineeRegistrationRequestController::class, 'show'])

            ->whereNumber('id');



        Route::post('/{id}/review', [TraineeRegistrationRequestController::class, 'review'])

            ->whereNumber('id')

            ->middleware('permission:review_trainee_registration_requests');

    });



    /*

    |--------------------------------------------------------------------------

    | Registration Requests - Courses

    |--------------------------------------------------------------------------

    */

    Route::prefix('registration-requests/courses')->group(function () {

        Route::get('/', [CourseRegistrationRequestController::class, 'index'])

            ->middleware('role_or_permission:view_registration_requests|create_course_registration_requests|confirm_course_registration_requests|complete_course_registration_requests');



        Route::post('/', [CourseRegistrationRequestController::class, 'store'])

            ->middleware(['permission:create_course_registration_requests', 'throttle:registration-requests']);



        Route::get('/{id}', [CourseRegistrationRequestController::class, 'show'])

            ->whereNumber('id');



        Route::post('/{id}/confirm-by-guardian', [CourseRegistrationRequestController::class, 'confirmByGuardian'])

            ->whereNumber('id')

            ->middleware('permission:confirm_course_registration_requests');



        Route::post('/{id}/complete', [CourseRegistrationRequestController::class, 'complete'])

            ->whereNumber('id')

            ->middleware('permission:complete_course_registration_requests');



        Route::post('/{id}/cancel', [CourseRegistrationRequestController::class, 'cancel'])

            ->whereNumber('id');

    });



    /*

    |--------------------------------------------------------------------------

    | Admin — Roles & Permissions Management

    |--------------------------------------------------------------------------

    */

    $adminAccess = 'role_or_permission:admin|super_admin|system_admin|general_director';
    $adminAuditView = 'role_or_permission:auditor|admin|super_admin|system_admin|general_director|view_audit|manage_user_access';

    Route::prefix('admin')->middleware('throttle:admin-access')->group(function () use ($adminAccess, $adminAuditView) {

        Route::middleware($adminAuditView)->group(function () {
            Route::get('/activity-logs', [\App\Http\Controllers\Api\Admin\ActivityLogController::class, 'index']);
            Route::get('/activity-logs/export', [\App\Http\Controllers\Api\Admin\ActivityLogController::class, 'export']);
            Route::get('/activity-logs/{id}', [\App\Http\Controllers\Api\Admin\ActivityLogController::class, 'show'])->whereNumber('id');
            Route::get('/users/{id}/activity-logs', [\App\Http\Controllers\Api\Admin\ActivityLogController::class, 'forUser'])->whereNumber('id');
        });

        Route::middleware($adminAccess)->group(function () {

        Route::get('/access-summary', \App\Http\Controllers\Api\Admin\AccessSummaryController::class);

        Route::prefix('users')->group(function () {

            Route::get('/', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'index']);

            Route::post('/', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'store']);

            Route::get('/{id}/access', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'show'])->whereNumber('id');

            // @deprecated Use GET /{id}/access — kept for backward compatibility with existing clients
            Route::get('/{id}', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'show'])->whereNumber('id');

            Route::put('/{id}', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'update'])->whereNumber('id');

            Route::post('/{id}/change-password', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'changePassword'])->whereNumber('id');

            Route::post('/{id}/roles/sync', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'syncRoles'])->whereNumber('id');

            Route::post('/{id}/permissions/sync', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'syncPermissions'])->whereNumber('id');

            Route::post('/{id}/roles', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'assignRole'])->whereNumber('id');

            Route::delete('/{id}/roles/{role}', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'revokeRole'])->whereNumber('id');

            Route::post('/{id}/permissions', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'assignPermission'])->whereNumber('id');

            Route::delete('/{id}/permissions/{permission}', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'revokePermission'])->whereNumber('id');

            Route::patch('/{id}/status', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'updateStatus'])->whereNumber('id');

            Route::patch('/{id}/parent', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'reassignParent'])->whereNumber('id');

            Route::get('/{id}/children', [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'childrenOf'])->whereNumber('id');

        });

        // Parent self-management
        Route::get('/my-children',          [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'myChildren']);
        Route::get('/my-delegatable',       [\App\Http\Controllers\Api\Admin\UserAccessController::class, 'delegatableOptions']);

        Route::prefix('roles')->group(function () {

            Route::get('/', [\App\Http\Controllers\Api\Admin\RoleController::class, 'index']);

            Route::post('/', [\App\Http\Controllers\Api\Admin\RoleController::class, 'store']);

            Route::get('/{id}', [\App\Http\Controllers\Api\Admin\RoleController::class, 'show'])->whereNumber('id');

            Route::patch('/{id}', [\App\Http\Controllers\Api\Admin\RoleController::class, 'update'])->whereNumber('id');

            Route::delete('/{id}', [\App\Http\Controllers\Api\Admin\RoleController::class, 'destroy'])->whereNumber('id');

            Route::post('/{id}/permissions', [\App\Http\Controllers\Api\Admin\RoleController::class, 'syncPermissions'])->whereNumber('id');

            Route::delete('/{id}/permissions/{permissionId}', [\App\Http\Controllers\Api\Admin\RoleController::class, 'detachPermission'])->whereNumber('id')->whereNumber('permissionId');

        });

        Route::prefix('permissions')->group(function () {

            Route::get('/', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'index']);

            Route::post('/', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'store']);

            Route::get('/{id}', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'show'])->whereNumber('id');

            Route::patch('/{id}', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'update'])->whereNumber('id');

            Route::delete('/{id}', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'destroy'])->whereNumber('id');

        });

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Consulting Module
    |--------------------------------------------------------------------------
    */
    $consultingOfficeBrowse = 'role_or_permission:admin|super_admin|system_admin|general_director|project_services_manager|consultant_union_admin|branch_manager|branch_officer|governor|project_owner|consultant_office';
    $consultingOfficeManage = 'role_or_permission:admin|super_admin|system_admin|general_director|project_services_manager|consultant_union_admin|branch_manager|governor';
    $consultingRequestAccess = 'role_or_permission:admin|super_admin|system_admin|general_director|project_services_manager|branch_manager|branch_officer|governor|project_owner|consultant_union_admin|consultant_office';
    $consultingRequestCreate = 'role_or_permission:project_owner|admin|super_admin|system_admin|general_director|project_services_manager|branch_manager|governor';
    $consultingRequestWorkflow = 'role_or_permission:admin|super_admin|system_admin|general_director|project_services_manager|branch_manager|branch_officer|governor|consultant_union_admin|project_owner|consultant_office';

    Route::get('/consulting/categories', fn () => response()->json(\App\Models\ConsultingCategory::where('is_active', true)->orderBy('sort_order')->get()))
        ->middleware($consultingRequestAccess);

    Route::get('/consulting/offices', [\App\Http\Controllers\Api\ConsultingOfficeController::class, 'index'])
        ->middleware($consultingOfficeBrowse);
    Route::get('/consulting/offices/{id}', [\App\Http\Controllers\Api\ConsultingOfficeController::class, 'show'])->whereNumber('id')
        ->middleware($consultingOfficeBrowse);

    Route::middleware($consultingOfficeManage)->group(function () {
        Route::post('/consulting/offices', [\App\Http\Controllers\Api\ConsultingOfficeController::class, 'store']);
        Route::put('/consulting/offices/{id}', [\App\Http\Controllers\Api\ConsultingOfficeController::class, 'update'])->whereNumber('id');
        Route::delete('/consulting/offices/{id}', [\App\Http\Controllers\Api\ConsultingOfficeController::class, 'destroy'])->whereNumber('id');
        Route::post('/consulting/offices/{id}/activate', [\App\Http\Controllers\Api\ConsultingOfficeController::class, 'activate'])->whereNumber('id');
        Route::post('/consulting/offices/{id}/suspend', [\App\Http\Controllers\Api\ConsultingOfficeController::class, 'suspend'])->whereNumber('id');
        Route::post('/consulting/offices/{id}/violations', [\App\Http\Controllers\Api\ConsultingOfficeController::class, 'addViolation'])->whereNumber('id');
    });

    Route::middleware($consultingRequestAccess)->group(function () {
        Route::get('/consulting/requests/stats', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'stats']);
        Route::get('/consulting/requests', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'index']);
        Route::get('/consulting/requests/{id}', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'show'])->whereNumber('id');
        Route::get('/consulting/requests/{id}/offers', [\App\Http\Controllers\Api\ConsultingOfferController::class, 'index'])->whereNumber('id');
    });

    Route::post('/consulting/requests', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'store'])
        ->middleware($consultingRequestCreate);

    Route::middleware($consultingRequestWorkflow)->group(function () {
        Route::put('/consulting/requests/{id}', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'update'])->whereNumber('id');
        Route::delete('/consulting/requests/{id}', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'destroy'])->whereNumber('id');
        Route::post('/consulting/requests/{id}/submit', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'submit'])->whereNumber('id');
        Route::post('/consulting/requests/{id}/sort', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'sort'])->whereNumber('id');
        Route::post('/consulting/requests/{id}/accept-offer', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'acceptOffer'])->whereNumber('id');
        Route::post('/consulting/requests/{id}/transfer', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'transfer'])->whereNumber('id');
        Route::post('/consulting/requests/{id}/attachments', [\App\Http\Controllers\Api\ConsultingRequestController::class, 'uploadAttachment'])->whereNumber('id')->middleware('throttle:file-upload');
        Route::post('/consulting/requests/{id}/offers', [\App\Http\Controllers\Api\ConsultingOfferController::class, 'store'])->whereNumber('id');
    });

    Route::middleware($consultingRequestAccess)->group(function () {
        Route::get('/consulting/contracts/{id}', [\App\Http\Controllers\Api\ConsultingContractController::class, 'show'])->whereNumber('id');
        Route::get('/consulting/contracts/{id}/messages', [\App\Http\Controllers\Api\ConsultingContractController::class, 'messages'])->whereNumber('id');
    });

    Route::middleware($consultingRequestWorkflow)->group(function () {
        Route::post('/consulting/contracts/{id}/sign', [\App\Http\Controllers\Api\ConsultingContractController::class, 'sign'])->whereNumber('id');
        Route::post('/consulting/contracts/{id}/messages', [\App\Http\Controllers\Api\ConsultingContractController::class, 'sendMessage'])->whereNumber('id');
        Route::post('/consulting/contracts/{id}/report', [\App\Http\Controllers\Api\ConsultingContractController::class, 'uploadReport'])->whereNumber('id')->middleware('throttle:file-upload');
        Route::post('/consulting/contracts/{id}/approve-report', [\App\Http\Controllers\Api\ConsultingContractController::class, 'approveReport'])->whereNumber('id');
        Route::post('/consulting/contracts/{id}/review', [\App\Http\Controllers\Api\ConsultingContractController::class, 'submitReview'])->whereNumber('id');
    });

    /* ═══ الإشعارات ═══ */
    Route::get('/notifications/summary',    [\App\Http\Controllers\Api\NotificationController::class, 'summary']);
    Route::get('/notifications',            [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/read-all',  [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead'])->whereNumber('id');
    Route::delete('/notifications/{id}',    [\App\Http\Controllers\Api\NotificationController::class, 'destroy'])->whereNumber('id');

    /* ═══ صندوق الرسائل الداخلية ═══ */
    Route::get('/inbox/unread-count',  [\App\Http\Controllers\Api\InboxController::class, 'unreadCount']);
    Route::get('/inbox/users-list',    [\App\Http\Controllers\Api\InboxController::class, 'usersList']);
    Route::get('/inbox/sent',          [\App\Http\Controllers\Api\InboxController::class, 'sent']);
    Route::get('/inbox',               [\App\Http\Controllers\Api\InboxController::class, 'inbox']);
    Route::post('/inbox',              [\App\Http\Controllers\Api\InboxController::class, 'store']);
    Route::get('/inbox/{id}',          [\App\Http\Controllers\Api\InboxController::class, 'show'])->whereNumber('id');
    Route::post('/inbox/{id}/reply',   [\App\Http\Controllers\Api\InboxController::class, 'reply'])->whereNumber('id');
    Route::delete('/inbox/{id}',       [\App\Http\Controllers\Api\InboxController::class, 'destroy'])->whereNumber('id');

    /* ═══════════════════════════════════════════════════════════════
    |  وحدة الحاضنات
    ═══════════════════════════════════════════════════════════════ */

    $incView   = 'role_or_permission:general_director|deputy_general_director|branch_manager|branch_officer|incubator_manager|incubator_mentor|admin|super_admin|system_admin|auditor|incubation.view';
    $incManage = 'role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|incubation.manage';

    // إحصائيات (للوحات)
    Route::get('/incubation/stats', [IncubatorController::class, 'stats'])->middleware($incView);

    // الحاضنات
    Route::post('/incubators',         [IncubatorController::class, 'store'])->middleware($incManage);
    Route::get('/incubators/{id}',     [IncubatorController::class, 'show'])->whereNumber('id')->middleware($incView);
    Route::put('/incubators/{id}',     [IncubatorController::class, 'update'])->whereNumber('id')->middleware($incManage);

    // البرامج
    Route::post('/incubators/{id}/programs', [IncubatorController::class, 'storeProgram'])->whereNumber('id')->middleware($incManage);

    // طلبات الانضمام
    Route::get('/incubation/applications',       [IncubatorController::class, 'allApplications'])->middleware($incView);
    Route::post('/incubation/apply',             [IncubatorController::class, 'apply']);
    Route::get('/incubation/my-applications',    [IncubatorController::class, 'myApplications']);
    Route::get('/incubation/applications/{id}',  [IncubatorController::class, 'showApplication'])->whereNumber('id');
    Route::post('/incubation/applications/{id}/review', [IncubatorController::class, 'reviewApplication'])->whereNumber('id')->middleware($incManage);
    Route::get('/incubators/{id}/applications',  [IncubatorController::class, 'applications'])->whereNumber('id')->middleware($incView);

    // المشاريع المحتضنة
    Route::get('/incubation/projects',       [IncubatorController::class, 'projects'])->middleware($incView);
    Route::get('/incubation/my-project',     [IncubatorController::class, 'myProject']);
    Route::get('/incubation/projects/{id}',  [IncubatorController::class, 'showProject'])->whereNumber('id')->middleware($incView);
    Route::put('/incubation/projects/{id}',  [IncubatorController::class, 'updateProject'])->whereNumber('id')->middleware($incManage);

    // جلسات الإرشاد
    Route::get('/incubation/sessions', [IncubatorController::class, 'indexMentoringSessions'])->middleware($incView);
    Route::post('/incubation/projects/{id}/sessions', [IncubatorController::class, 'storeMentoringSession'])->whereNumber('id')
        ->middleware('role_or_permission:incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor');
    Route::get('/incubation/my-sessions', [IncubatorController::class, 'myMentoringSessions'])
        ->middleware('role_or_permission:incubator_mentor|incubator_manager|admin|super_admin|system_admin|incubation.mentor');

    // تقارير التقدم
    Route::post('/incubation/projects/{id}/reports', [IncubatorController::class, 'storeProgressReport'])
        ->whereNumber('id')
        ->middleware('throttle:incubation-report');

    /* ═══════════════════════════════════════════════════════════════
       قصص النجاح
    ═══════════════════════════════════════════════════════════════ */

    $storyManage = 'role_or_permission:general_director|admin|super_admin|system_admin|incubator_manager|branch_manager|story.manage';

    Route::get('/success-stories/stats',       [SuccessStoryController::class, 'stats'])->middleware($storyManage);
    Route::post('/success-stories',            [SuccessStoryController::class, 'store'])->middleware($storyManage);
    Route::put('/success-stories/{id}',        [SuccessStoryController::class, 'update'])->whereNumber('id')->middleware($storyManage);
    Route::delete('/success-stories/{id}',     [SuccessStoryController::class, 'destroy'])->whereNumber('id')->middleware($storyManage);

    /* ═══════════════════════════════════════════════════════════════
       الأخبار
    ═══════════════════════════════════════════════════════════════ */

    $newsManage = 'role_or_permission:media_manager|general_director|admin|super_admin|system_admin|news.manage';

    Route::get('/news/stats', [NewsController::class, 'stats'])->middleware($newsManage);
    Route::post('/news',      [NewsController::class, 'store'])->middleware($newsManage);
    Route::put('/news/{id}',  [NewsController::class, 'update'])->whereNumber('id')->middleware($newsManage);
    Route::delete('/news/{id}',[NewsController::class, 'destroy'])->whereNumber('id')->middleware($newsManage);

    /* ═══════════════════════════════════════════════════════════════
       ملفات رواد الأعمال
    ═══════════════════════════════════════════════════════════════ */

    $entManage = 'role_or_permission:general_director|deputy_general_director|deputy_director|admin|super_admin|system_admin|branch_manager|incubator_manager|entrepreneur_manager|entrepreneur.manage';

    Route::get('/entrepreneur/my-profile',      [EntrepreneurProfileController::class, 'myProfile']);
    Route::post('/entrepreneur/profile',        [EntrepreneurProfileController::class, 'store']);
    Route::put('/entrepreneur/profile/{id}',    [EntrepreneurProfileController::class, 'update'])->whereNumber('id');
    Route::get('/entrepreneur/profiles',        [EntrepreneurProfileController::class, 'index'])->middleware($entManage);
    Route::get('/entrepreneur/profiles/export', [EntrepreneurProfileController::class, 'export'])->middleware($entManage);
    Route::get('/entrepreneur/profiles/stats',  [EntrepreneurProfileController::class, 'stats'])->middleware($entManage);
    Route::get('/entrepreneur/profiles/{id}',   [EntrepreneurProfileController::class, 'show'])->whereNumber('id')->middleware($entManage);
    Route::post('/entrepreneur/profiles/{id}/review', [EntrepreneurProfileController::class, 'review'])->whereNumber('id')->middleware($entManage);

});



/*

|--------------------------------------------------------------------------

| Public Verify Page for QR

|--------------------------------------------------------------------------

*/

Route::get('/certificates/verify-page', [CertificateController::class, 'verifyPage'])
    ->middleware('throttle:verify-page')
    ->name('api.certificates.verify-page');

/*
|--------------------------------------------------------------------------
| Syria Locations (public — بدون مصادقة)
|--------------------------------------------------------------------------
*/
Route::prefix('locations')->middleware('throttle:60,1')->group(function () {
    Route::get('/governorates',  [SyriaLocationController::class, 'governorates']);
    Route::get('/districts',     [SyriaLocationController::class, 'districts']);
    Route::get('/subdistricts',  [SyriaLocationController::class, 'subdistricts']);
    Route::get('/communities',   [SyriaLocationController::class, 'communities']);
    Route::get('/search',        [SyriaLocationController::class, 'search']);
    Route::get('/map',           [SyriaLocationController::class, 'mapPoints']);
});

