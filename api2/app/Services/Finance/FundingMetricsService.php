<?php

namespace App\Services\Finance;

use App\Models\FundedLoan;
use App\Models\FundingApplication;
use App\Models\LoanPayment;
use App\Models\User;
use App\Support\AccessControlGuard;
use App\Support\BranchDataScope;
use App\Support\FinanceDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FundingMetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function metrics(User $user): array
    {
        $applications = $this->applicationQuery($user);
        $loans = $this->loanQuery($user);

        $totalApplications = (clone $applications)->count();
        $fundedApplications = (clone $applications)->where('status', 'funded')->count();
        $pendingApplications = (clone $applications)->whereIn('status', [
            'submitted', 'branch_review', 'needs_completion', 'consultant_review',
            'consultant_priced', 'funder_review', 'approved',
        ])->count();
        $defaultedLoans = (clone $loans)->where('status', 'defaulted')->count();
        $activeLoans = (clone $loans)->where('status', 'active')->count();

        $loanIds = (clone $loans)->pluck('id');
        $payments = LoanPayment::query()->whereIn('funded_loan_id', $loanIds);
        $totalDue = (float) (clone $payments)->sum('amount_due');
        $totalPaid = (float) (clone $payments)->sum('amount_paid');
        $repaymentRate = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100, 1) : 0;

        $scope = AccessControlGuard::isNationalAdministrator($user) || $user->hasRole('finance_manager')
            ? 'national'
            : (BranchDataScope::isBranchManager($user) ? 'branch' : 'scoped');

        if (BranchDataScope::isBranchManager($user) && !$user->hasPermissionTo('finance.metrics.national')) {
            $scope = 'branch';
        }

        return [
            'scope' => $scope,
            'total_applications' => $totalApplications,
            'funded_applications' => $fundedApplications,
            'pending_applications' => $pendingApplications,
            'active_loans' => $activeLoans,
            'defaulted_loans' => $defaultedLoans,
            'repayment_rate' => $repaymentRate,
            'total_funded_amount' => (float) (clone $loans)->sum('approved_amount'),
        ];
    }

    public function cloudApplications(User $user): Builder
    {
        return $this->applicationQuery($user)
            ->whereIn('status', ['approved', 'funded'])
            ->with([
                'branch:id,name',
                'governorate:id,name_ar',
                'details',
                'consultantAssignments:id,funding_application_id,consultant_office_id,status,price_offer_amount,price_offer_status',
                'partnerAssignments:id,funding_application_id,funding_partner_id,status,approved_amount',
            ]);
    }

    public function fundedLoans(User $user): Builder
    {
        return $this->loanQuery($user)
            ->whereIn('status', ['active', 'paid', 'restructured', 'closed'])
            ->with(['application', 'partner']);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function fundedStats(User $user, array $filters = []): array
    {
        $base = $this->fundedLoans($user);
        $this->applyLoanListFilters($base, $filters);

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count)
            ->all();

        return [
            'total' => array_sum($byStatus),
            'total_amount' => (float) (clone $base)->sum('approved_amount'),
            'by_status' => $byStatus,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function defaultedStats(User $user, array $filters = []): array
    {
        $base = $this->defaultedLoans($user);
        $this->applyLoanListFilters($base, $filters);

        $total = (clone $base)->count();

        return [
            'total' => $total,
            'total_amount' => (float) (clone $base)->sum('approved_amount'),
        ];
    }

    /**
     * @param  Builder<\App\Models\FundedLoan>  $query
     * @param  array<string, mixed>  $filters
     */
    public function applyLoanListFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.trim((string) $filters['search']).'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('loan_number', 'like', $term)
                    ->orWhereHas('application', fn (Builder $app) => $app->where('project_name', 'like', $term));
            });
        }
    }

    public function defaultedLoans(User $user): Builder
    {
        return $this->loanQuery($user)
            ->where('status', 'defaulted')
            ->with(['application', 'partner', 'payments']);
    }

    private function applicationQuery(User $user): Builder
    {
        return FinanceDataScope::scopeApplications(FundingApplication::query(), $user);
    }

    private function loanQuery(User $user): Builder
    {
        return FinanceDataScope::scopeLoans(FundedLoan::query(), $user);
    }

    /**
     * Aggregate finance counters for guest-facing pages (no user scope, no PII).
     *
     * @return array<string, mixed>
     */
    public function publicMetrics(): array
    {
        $applications = FundingApplication::query();
        $loans = FundedLoan::query();

        $totalApplications = (clone $applications)->count();
        $fundedApplications = (clone $applications)->where('status', 'funded')->count();
        $pendingApplications = (clone $applications)->whereIn('status', [
            'submitted', 'branch_review', 'needs_completion', 'consultant_review',
            'consultant_priced', 'funder_review', 'approved',
        ])->count();
        $defaultedLoans = (clone $loans)->where('status', 'defaulted')->count();
        $activeLoans = (clone $loans)->where('status', 'active')->count();

        $loanIds = (clone $loans)->pluck('id');
        $payments = LoanPayment::query()->whereIn('funded_loan_id', $loanIds);
        $totalDue = (float) (clone $payments)->sum('amount_due');
        $totalPaid = (float) (clone $payments)->sum('amount_paid');
        $repaymentRate = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100, 1) : 0;

        $statusBreakdown = FundingApplication::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count)
            ->all();

        return [
            'scope' => 'public',
            'total_applications' => $totalApplications,
            'funded_applications' => $fundedApplications,
            'pending_applications' => $pendingApplications,
            'active_loans' => $activeLoans,
            'defaulted_loans' => $defaultedLoans,
            'repayment_rate' => $repaymentRate,
            'total_funded_amount' => (float) (clone $loans)->sum('approved_amount'),
            'status_breakdown' => $statusBreakdown,
        ];
    }
}
