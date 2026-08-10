<?php

namespace App\Services\Finance;

use App\Models\Branch;
use App\Models\ConsultantAssignment;
use App\Models\FundingApplication;
use App\Models\FundingApplicationDetail;
use App\Models\FundingPartnerAssignment;
use App\Models\FundedLoan;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Needs\NeedSyncService;
use App\Services\StatusHistoryService;
use App\Support\StatusTransitionValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FundingApplicationService
{
    public function __construct(
        private FundingApplicationNumberGenerator $numberGenerator,
        private AuditLogService $auditLog,
        private StatusTransitionValidator $statusValidator,
        private StatusHistoryService $statusHistory,
        private NeedSyncService $needSync,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data, ?Request $request = null): FundingApplication
    {
        $scope = $this->resolveScope($user, $data);

        $application = DB::transaction(function () use ($user, $data, $scope, $request): FundingApplication {
            $row = FundingApplication::query()->create([
                'application_number' => $this->numberGenerator->next(),
                'applicant_user_id' => $user->hasRole('project_owner') ? $user->id : ($data['applicant_user_id'] ?? $user->id),
                'applicant_name' => $data['applicant_name'],
                'national_id' => $data['national_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? $user->email,
                'governorate_id' => $scope['governorate_id'],
                'branch_id' => $scope['branch_id'],
                'project_name' => $data['project_name'],
                'project_type' => $data['project_type'] ?? null,
                'project_sector' => $data['project_sector'] ?? null,
                'project_size' => $data['project_size'] ?? 'small',
                'business_stage' => $data['business_stage'] ?? 'startup',
                'project_status' => $data['project_status'] ?? null,
                'requested_amount' => $data['requested_amount'],
                'currency' => $data['currency'] ?? 'SYP',
                'financing_type' => $data['financing_type'] ?? 'capital',
                'financing_mode' => $data['financing_mode'] ?? null,
                'repayment_period_months' => $data['repayment_period_months'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'current_stage' => 'draft',
                'created_by' => $user->id,
            ]);

            if (!empty($data['details']) && is_array($data['details'])) {
                FundingApplicationDetail::query()->create(array_merge(
                    ['funding_application_id' => $row->id],
                    $this->sanitizeDetails($data['details'])
                ));
            }

            $this->statusHistory->record($row, null, (string) $row->status, (int) $user->id);
            $this->auditLog->log('finance_application_created', $user, $row, null, $row->toArray(), null, $request, 'finance', 'إنشاء طلب تمويل');

            return $row;
        });

        return $application->fresh(['details', 'branch', 'governorate']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(FundingApplication $application, User $user, array $data, ?Request $request = null): FundingApplication
    {
        $before = $application->toArray();
        $application->update(array_merge(
            collect($data)->only([
                'applicant_name', 'national_id', 'phone', 'email', 'governorate_id', 'branch_id',
                'project_name', 'project_type',
                'project_sector', 'project_size', 'business_stage', 'project_status',
                'requested_amount', 'currency',
                'financing_type', 'financing_mode', 'repayment_period_months', 'purpose', 'description',
            ])->filter(fn ($v) => $v !== null)->all(),
            ['updated_by' => $user->id]
        ));

        if (!empty($data['details']) && is_array($data['details'])) {
            FundingApplicationDetail::query()->updateOrCreate(
                ['funding_application_id' => $application->id],
                $this->sanitizeDetails($data['details'])
            );
        }

        $action = in_array($application->status, ['draft', 'needs_completion'], true)
            ? 'finance_application_saved_draft'
            : 'finance_application_updated';
        $this->auditLog->log($action, $user, $application, $before, $application->fresh()->toArray(), null, $request, 'finance', 'تحديث طلب تمويل');

        return $application->fresh(['details', 'branch', 'governorate']);
    }

    public function submit(FundingApplication $application, User $user, ?Request $request = null): FundingApplication
    {
        return DB::transaction(function () use ($application, $user, $request): FundingApplication {
            $locked = FundingApplication::query()->whereKey($application->id)->lockForUpdate()->firstOrFail();

            foreach (['project_name', 'requested_amount', 'governorate_id', 'branch_id'] as $field) {
                if (blank($locked->{$field})) {
                    throw ValidationException::withMessages([
                        $field => ['الحقل مطلوب قبل الإرسال.'],
                    ]);
                }
            }

            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(FundingApplication::class, $from, 'submitted');

            $before = $locked->only(['status', 'current_stage', 'submitted_at']);
            $locked->update([
                'status' => 'submitted',
                'current_stage' => 'submitted',
                'submitted_at' => now(),
                'updated_by' => $user->id,
            ]);

            $this->statusHistory->record($locked, $from, 'submitted', (int) $user->id);
            $this->auditLog->log('finance_application_submitted', $user, $locked, $before, $locked->only(['status', 'current_stage', 'submitted_at']), null, $request, 'finance', 'إرسال طلب تمويل');

            $submitted = $locked->fresh(['governorate', 'branch']);
            $this->needSync->createFromFundingApplication($submitted, $user);

            return $submitted;
        });
    }

    public function branchReview(FundingApplication $application, User $user, string $decision, ?string $notes, ?Request $request = null): FundingApplication
    {
        $status = match ($decision) {
            'approve' => 'funder_review',
            'needs_completion' => 'needs_completion',
            'reject' => 'rejected',
            'review' => 'branch_review',
            default => throw ValidationException::withMessages(['decision' => ['قرار غير صالح.']]),
        };

        return DB::transaction(function () use ($application, $user, $status, $decision, $notes, $request): FundingApplication {
            $locked = FundingApplication::query()->whereKey($application->id)->lockForUpdate()->firstOrFail();
            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(FundingApplication::class, $from, $status);

            $before = $locked->only(['status', 'current_stage']);
            $locked->update([
                'status' => $status,
                'current_stage' => $status,
                'updated_by' => $user->id,
            ]);

            $this->statusHistory->record($locked, $from, $status, (int) $user->id, $notes);
            $action = $decision === 'needs_completion' ? 'finance_application_needs_completion' : 'finance_application_reviewed';
            $this->auditLog->log($action, $user, $locked, $before, $locked->only(['status', 'current_stage']), ['notes' => $notes], $request, 'finance', 'مراجعة فرع لطلب تمويل');

            return $locked->fresh();
        });
    }

    public function assignConsultant(FundingApplication $application, User $user, int $officeId, ?Request $request = null): ConsultantAssignment
    {
        return DB::transaction(function () use ($application, $user, $officeId, $request): ConsultantAssignment {
            $locked = FundingApplication::query()->whereKey($application->id)->lockForUpdate()->firstOrFail();
            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(FundingApplication::class, $from, 'consultant_review');

            $office = \App\Models\ConsultantOffice::query()->findOrFail($officeId);
            if (!$office->canReceiveAssignments()) {
                throw ValidationException::withMessages([
                    'consultant_office_id' => ['لا يمكن إحالة الطلب لمكتب استشاري غير معتمد أو غير نشط.'],
                ]);
            }

            $assignment = ConsultantAssignment::query()->create([
                'funding_application_id' => $locked->id,
                'consultant_office_id' => $officeId,
                'assigned_by' => $user->id,
                'assigned_at' => now(),
                'status' => 'assigned',
                'price_offer_status' => 'pending',
            ]);

            $locked->update(['status' => 'consultant_review', 'current_stage' => 'consultant_review', 'updated_by' => $user->id]);
            $this->statusHistory->record($locked, $from, 'consultant_review', (int) $user->id);
            $this->auditLog->log('finance_consultant_assigned', $user, $locked, null, ['consultant_office_id' => $officeId], null, $request, 'finance', 'إحالة طلب تمويل لمكتب استشاري');

            return $assignment;
        });
    }

    public function assignPartner(FundingApplication $application, User $user, int $partnerId, ?Request $request = null): FundingPartnerAssignment
    {
        return DB::transaction(function () use ($application, $user, $partnerId, $request): FundingPartnerAssignment {
            $locked = FundingApplication::query()->whereKey($application->id)->lockForUpdate()->firstOrFail();
            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(FundingApplication::class, $from, 'funder_review');

            $partner = \App\Models\FundingPartner::query()->findOrFail($partnerId);
            if (!$partner->canReceiveAssignments()) {
                throw ValidationException::withMessages([
                    'funding_partner_id' => ['لا يمكن إحالة الطلب لجهة تمويل غير معتمدة أو غير نشطة.'],
                ]);
            }

            $assignment = FundingPartnerAssignment::query()->create([
                'funding_application_id' => $locked->id,
                'funding_partner_id' => $partnerId,
                'assigned_by' => $user->id,
                'assigned_at' => now(),
                'status' => 'sent',
            ]);

            $locked->update(['status' => 'funder_review', 'current_stage' => 'funder_review', 'updated_by' => $user->id]);
            $this->statusHistory->record($locked, $from, 'funder_review', (int) $user->id);
            $this->auditLog->log('finance_partner_assigned', $user, $locked, null, ['funding_partner_id' => $partnerId], null, $request, 'finance', 'إحالة طلب تمويل لجهة تمويل');

            return $assignment;
        });
    }

    public function approve(FundingApplication $application, User $user, ?Request $request = null): FundingApplication
    {
        return DB::transaction(function () use ($application, $user, $request): FundingApplication {
            $locked = FundingApplication::query()->whereKey($application->id)->lockForUpdate()->firstOrFail();
            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(FundingApplication::class, $from, 'approved');

            $before = $locked->only(['status', 'current_stage']);
            $locked->update(['status' => 'approved', 'current_stage' => 'approved', 'updated_by' => $user->id]);
            $this->statusHistory->record($locked, $from, 'approved', (int) $user->id);
            $this->auditLog->log('finance_application_approved', $user, $locked, $before, $locked->only(['status', 'current_stage']), null, $request, 'finance', 'اعتماد طلب تمويل');

            return $locked->fresh();
        });
    }

    public function reject(FundingApplication $application, User $user, ?string $notes, ?Request $request = null): FundingApplication
    {
        return DB::transaction(function () use ($application, $user, $notes, $request): FundingApplication {
            $locked = FundingApplication::query()->whereKey($application->id)->lockForUpdate()->firstOrFail();
            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(FundingApplication::class, $from, 'rejected');

            $before = $locked->only(['status', 'current_stage']);
            $locked->update(['status' => 'rejected', 'current_stage' => 'rejected', 'updated_by' => $user->id]);
            $this->statusHistory->record($locked, $from, 'rejected', (int) $user->id, $notes);
            $this->auditLog->log('finance_application_rejected', $user, $locked, $before, $locked->only(['status', 'current_stage']), ['notes' => $notes], $request, 'finance', 'رفض طلب تمويل');

            return $locked->fresh();
        });
    }

    public function createLoan(FundingApplication $application, User $user, array $data, ?Request $request = null): FundedLoan
    {
        return DB::transaction(function () use ($application, $user, $data, $request): FundedLoan {
            $locked = FundingApplication::query()->whereKey($application->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'approved') {
                throw ValidationException::withMessages(['application' => ['يجب أن يكون الطلب معتمداً قبل إنشاء القرض.']]);
            }

            $from = (string) $locked->status;
            $this->statusValidator->assertAllowed(FundingApplication::class, $from, 'funded');

            $loan = FundedLoan::query()->create([
                'funding_application_id' => $locked->id,
                'funding_partner_id' => $data['funding_partner_id'] ?? null,
                'loan_number' => $this->nextLoanNumber(),
                'approved_amount' => $data['approved_amount'],
                'currency' => $data['currency'] ?? 'SYP',
                'interest_type' => $data['interest_type'] ?? 'interest',
                'interest_rate' => $data['interest_rate'] ?? null,
                'profit_margin' => $data['profit_margin'] ?? null,
                'installment_count' => $data['installment_count'] ?? 1,
                'installment_amount' => $data['installment_amount'] ?? null,
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'end_date' => $data['end_date'] ?? null,
                'status' => 'active',
            ]);

            $locked->update(['status' => 'funded', 'current_stage' => 'funded', 'updated_by' => $user->id]);
            $this->statusHistory->record($locked, $from, 'funded', (int) $user->id);
            $this->auditLog->log('finance_loan_created', $user, $loan, null, $loan->toArray(), null, $request, 'finance', 'إنشاء قرض ممول');

            return $loan;
        });
    }

    private function nextLoanNumber(): string
    {
        $prefix = 'LN-' . now()->format('Ymd');
        $query = FundedLoan::query()
            ->where('loan_number', 'like', $prefix . '-%')
            ->orderByDesc('id');

        if (DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        $last = $query->value('loan_number');
        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%06d', $prefix, $seq);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{governorate_id: int, branch_id: int}
     */
    private function resolveScope(User $user, array $data): array
    {
        if (!empty($data['branch_id']) && !empty($data['governorate_id'])) {
            $branch = Branch::query()->findOrFail((int) $data['branch_id']);
            if ((int) $branch->governorate_id !== (int) $data['governorate_id']) {
                throw ValidationException::withMessages(['branch_id' => ['الفرع لا يتبع المحافظة المختارة.']]);
            }

            return ['governorate_id' => (int) $data['governorate_id'], 'branch_id' => (int) $data['branch_id']];
        }

        if ($user->branch_id && $user->governorate_id) {
            return ['governorate_id' => (int) $user->governorate_id, 'branch_id' => (int) $user->branch_id];
        }

        $govId = (int) ($data['governorate_id'] ?? 0);
        if ($govId) {
            $branch = Branch::query()->where('governorate_id', $govId)->where('is_active', true)->first()
                ?? Branch::query()->where('governorate_id', $govId)->first();
            if (!$branch) {
                throw ValidationException::withMessages(['governorate_id' => ['لا يوجد فرع للمحافظة المحددة.']]);
            }

            return ['governorate_id' => $govId, 'branch_id' => (int) $branch->id];
        }

        throw ValidationException::withMessages(['governorate_id' => ['يجب تحديد المحافظة والفرع.']]);
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private function sanitizeDetails(array $details): array
    {
        $allowed = [
            'owner_experience', 'employees_count', 'monthly_revenue', 'monthly_expenses',
            'existing_debts', 'assets_description', 'market_description', 'challenges',
            'requested_support', 'notes', 'extra_data',
        ];

        $clean = collect($details)->only($allowed)->all();

        if (isset($clean['extra_data']) && is_array($clean['extra_data'])) {
            if (empty($clean['notes'])) {
                $clean['notes'] = json_encode($clean['extra_data'], JSON_UNESCAPED_UNICODE);
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('funding_application_details', 'extra_data')) {
                unset($clean['extra_data']);
            }
        }

        return $clean;
    }
}
