<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FundingApplicationResource;
use App\Models\FundingApplication;
use App\Services\Finance\FundingMetricsService;
use App\Support\FinanceDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FundingMetricsController extends Controller
{
    public function __construct(private FundingMetricsService $metrics) {}

    public function metrics(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermissionTo('finance.metrics.view')
            && !FinanceDataScope::hasNationalFinanceAccess($user)
            && !$user->hasRole('branch_manager')) {
            abort(403);
        }

        if ($user->hasRole('branch_manager') && !$user->hasPermissionTo('finance.metrics.branch')) {
            abort(403);
        }

        return response()->json(['data' => $this->metrics->metrics($user)]);
    }

    public function funded(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermissionTo('finance.loans.view') && !FinanceDataScope::hasNationalFinanceAccess($user)) {
            abort(403);
        }

        $filters = $request->only(['search', 'status']);
        $rows = $this->metrics->fundedLoans($user);
        $this->metrics->applyLoanListFilters($rows, $filters);

        $paginated = $rows->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return response()->json($paginated);
    }

    public function fundedStats(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermissionTo('finance.loans.view') && !FinanceDataScope::hasNationalFinanceAccess($user)) {
            abort(403);
        }

        return response()->json([
            'data' => $this->metrics->fundedStats($user, $request->only(['search', 'status'])),
        ]);
    }

    public function defaulted(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermissionTo('finance.loans.view') && !FinanceDataScope::hasNationalFinanceAccess($user)) {
            abort(403);
        }

        $filters = $request->only(['search']);
        $rows = $this->metrics->defaultedLoans($user);
        $this->metrics->applyLoanListFilters($rows, $filters);

        $paginated = $rows->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return response()->json($paginated);
    }

    public function defaultedStats(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermissionTo('finance.loans.view') && !FinanceDataScope::hasNationalFinanceAccess($user)) {
            abort(403);
        }

        return response()->json([
            'data' => $this->metrics->defaultedStats($user, $request->only(['search'])),
        ]);
    }

    public function cloud(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermissionTo('finance.applications.view')
            && !FinanceDataScope::hasNationalFinanceAccess($user)
            && !$user->hasRole(['consultant_office', 'funding_partner', 'branch_manager'])) {
            abort(403);
        }

        $rows = $this->metrics->cloudApplications($user)
            ->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return FundingApplicationResource::collection($rows)->response();
    }

    public function managerDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasAnyRole(['finance_manager', 'finance_officer'])
            && !FinanceDataScope::hasNationalFinanceAccess($user)
            && !\App\Support\AccessControlGuard::isNationalAdministrator($user)) {
            abort(403, 'غير مصرح بلوحة المالية.');
        }

        if ($user->hasRole('finance_officer') && !$user->hasPermissionTo('finance.metrics.view')) {
            abort(403);
        }

        $data = $this->metrics->metrics($user);

        if ($user->hasRole('finance_officer') && !$user->hasPermissionTo('finance.metrics.national')) {
            $data['scope'] = 'officer';
        }

        return response()->json(['data' => $data]);
    }
}
