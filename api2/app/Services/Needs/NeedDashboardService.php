<?php

namespace App\Services\Needs;

use App\Models\Need;
use App\Support\NeedStatus;
use App\Support\NeedDataScope;
use App\Support\NeedTaxonomy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class NeedDashboardService
{
    public function stats(User $user): array
    {
        $base = NeedDataScope::scopeNeeds(Need::query(), $user);

        return [
            'scope' => NeedDataScope::hasNationalNeedsAccess($user) && !($user->branch_id && !$user->hasPermissionTo('needs.view_all'))
                ? 'national'
                : 'branch',
            'total' => (clone $base)->count(),
            'citizen' => (clone $base)->where('need_owner_type', 'citizen')->count(),
            'state' => (clone $base)->where('need_owner_type', 'state')->count(),
            'mapped' => (clone $base)->where('is_mapped', true)->count(),
            'by_governorate' => (function () use ($base) {
                $rows = (clone $base)->selectRaw('governorate_id, COUNT(*) as total')
                    ->groupBy('governorate_id')
                    ->get();
                $names = \App\Models\Governorate::query()
                    ->whereIn('id', $rows->pluck('governorate_id')->filter()->all())
                    ->pluck('name_ar', 'id');

                return $rows->map(fn ($r) => [
                    'governorate_id' => $r->governorate_id,
                    'name' => $names[$r->governorate_id] ?? null,
                    'total' => (int) $r->total,
                ]);
            })(),
            'by_sector' => (clone $base)->selectRaw('sector, COUNT(*) as total')
                ->whereNotNull('sector')->groupBy('sector')->orderByDesc('total')->limit(15)->get(),
            'by_priority' => (clone $base)->selectRaw('priority, COUNT(*) as total')
                ->groupBy('priority')->get(),
            'by_status' => (clone $base)->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')->get(),
            'by_source' => (clone $base)->selectRaw('source_platform, COUNT(*) as total')
                ->groupBy('source_platform')->get(),
            'by_intervention' => (clone $base)->selectRaw('proposed_intervention, COUNT(*) as total')
                ->whereNotNull('proposed_intervention')->groupBy('proposed_intervention')->get(),
            'by_facility_type' => (clone $base)->selectRaw('facility_type, COUNT(*) as total')
                ->whereNotNull('facility_type')->groupBy('facility_type')->orderByDesc('total')->get()
                ->map(fn ($r) => [
                    'facility_type' => $r->facility_type,
                    'label' => NeedTaxonomy::label(NeedTaxonomy::TYPE_FACILITY, $r->facility_type),
                    'total' => (int) $r->total,
                ])->values(),
            'by_targeting_type' => (clone $base)->selectRaw('targeting_type, COUNT(*) as total')
                ->whereNotNull('targeting_type')->groupBy('targeting_type')->orderByDesc('total')->get()
                ->map(fn ($r) => [
                    'targeting_type' => $r->targeting_type,
                    'label' => NeedTaxonomy::label(NeedTaxonomy::TYPE_TARGETING, $r->targeting_type),
                    'total' => (int) $r->total,
                ])->values(),
            'by_sector_ref' => (clone $base)
                ->join('need_need_sector', 'needs.id', '=', 'need_need_sector.need_id')
                ->join('need_sectors', 'need_sectors.id', '=', 'need_need_sector.need_sector_id')
                ->selectRaw('need_sectors.code as code, need_sectors.name_ar as label, COUNT(*) as total')
                ->groupBy('need_sectors.code', 'need_sectors.name_ar')
                ->orderByDesc('total')->get()
                ->map(fn ($r) => ['code' => $r->code, 'label' => $r->label, 'total' => (int) $r->total])->values(),
            'by_district' => (clone $base)->selectRaw('district_name, COUNT(*) as total')
                ->whereNotNull('district_name')->where('district_name', '!=', '')
                ->groupBy('district_name')->orderByDesc('total')->limit(20)->get()
                ->map(fn ($r) => ['district' => $r->district_name, 'total' => (int) $r->total])->values(),
            'facility_establishment_count' => (clone $base)->where('need_category', 'facility_establishment')->count(),
            'facility_development_count' => (clone $base)->where('need_category', 'facility_development')->count(),
        ];
    }

    public function dataEntryWorkspace(User $user): array
    {
        $row = NeedDataScope::scopeNeeds(Need::query(), $user)
            ->where('created_by', $user->id)
            ->selectRaw('
                COUNT(*) as my_total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as my_drafts,
                SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as my_pending,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as my_approved,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as my_returned
            ', [
                NeedStatus::NEW,
                NeedStatus::PENDING_GOVERNORATE_REVIEW,
                NeedStatus::PENDING_BRANCH_APPROVAL,
                NeedStatus::APPROVED,
                NeedStatus::RETURNED_FOR_EDIT,
            ])
            ->first();

        return [
            'scope' => 'data_entry',
            'my_total' => (int) ($row->my_total ?? 0),
            'my_drafts' => (int) ($row->my_drafts ?? 0),
            'my_pending' => (int) ($row->my_pending ?? 0),
            'my_approved' => (int) ($row->my_approved ?? 0),
            'my_returned' => (int) ($row->my_returned ?? 0),
        ];
    }

    public function reviewerWorkspace(User $user): array
    {
        $row = NeedDataScope::scopeNeeds(Need::query(), $user)
            ->selectRaw('
                SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as pending_review,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as returned,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected
            ', [
                NeedStatus::PENDING_GOVERNORATE_REVIEW,
                NeedStatus::PENDING_BRANCH_APPROVAL,
                NeedStatus::RETURNED_FOR_EDIT,
                NeedStatus::APPROVED,
                NeedStatus::REJECTED,
            ])
            ->first();

        return [
            'scope' => 'data_reviewer',
            'pending_review' => (int) ($row->pending_review ?? 0),
            'returned' => (int) ($row->returned ?? 0),
            'approved' => (int) ($row->approved ?? 0),
            'rejected' => (int) ($row->rejected ?? 0),
        ];
    }

    public function analytics(User $user): array
    {
        $role = $user->getRoleNames()->first() ?? '';

        if (in_array($role, ['data_entry'], true)) {
            return $this->analyticsDataEntry($user);
        }
        if (in_array($role, ['data_reviewer'], true)) {
            return $this->analyticsReviewer($user);
        }
        // governor, branch_manager, and roles with needs.dashboard
        return $this->analyticsManager($user);
    }

    private function analyticsManager(User $user): array
    {
        $base = NeedDataScope::scopeNeeds(Need::query(), $user);

        $byStatus = (clone $base)->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status')->toArray();

        $byPriority = (clone $base)->selectRaw('priority, COUNT(*) as total')
            ->whereNotNull('priority')->groupBy('priority')->pluck('total', 'priority')->toArray();

        $bySector = (clone $base)->selectRaw('sector, COUNT(*) as total')
            ->whereNotNull('sector')->groupBy('sector')->orderByDesc('total')->limit(10)
            ->pluck('total', 'sector')->toArray();

        $byGovRows = (clone $base)->selectRaw('governorate_id, COUNT(*) as total')
            ->groupBy('governorate_id')->orderByDesc('total')->limit(14)->get();
        $govNames = \App\Models\Governorate::query()
            ->whereIn('id', $byGovRows->pluck('governorate_id')->filter()->all())
            ->pluck('name_ar', 'id');
        $byGov = $byGovRows->map(fn ($r) => [
            'name' => $govNames[$r->governorate_id] ?? 'غير محدد',
            'total' => (int) $r->total,
        ])->values()->toArray();

        // last 6 months trend
        $byMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $byMonth[] = [
                'label' => $month->translatedFormat('M Y') ?: $month->format('Y-m'),
                'total' => (clone $base)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }

        $total   = (clone $base)->count();
        $pending = ($byStatus[NeedStatus::PENDING_GOVERNORATE_REVIEW] ?? 0)
                 + ($byStatus[NeedStatus::PENDING_BRANCH_APPROVAL] ?? 0);
        $approved = $byStatus[NeedStatus::APPROVED] ?? 0;
        $rejected = $byStatus[NeedStatus::REJECTED] ?? 0;

        return [
            'role_view'   => 'manager',
            'scope'       => NeedDataScope::hasNationalNeedsAccess($user) ? 'national' : 'branch',
            'kpis' => [
                'total'    => $total,
                'pending'  => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'mapped'   => (clone $base)->where('is_mapped', true)->count(),
                'citizen'  => (clone $base)->where('need_owner_type', 'citizen')->count(),
                'state'    => (clone $base)->where('need_owner_type', 'state')->count(),
                'resolved' => $byStatus[NeedStatus::RESOLVED] ?? 0,
            ],
            'by_status'   => $byStatus,
            'by_priority' => $byPriority,
            'by_sector'   => $bySector,
            'by_governorate' => $byGov,
            'by_month'    => $byMonth,
            'approval_rate' => $total > 0 ? round($approved / $total * 100) : 0,
        ];
    }

    private function analyticsDataEntry(User $user): array
    {
        $base = NeedDataScope::scopeNeeds(Need::query(), $user);
        $mine = (clone $base)->where('created_by', $user->id);

        $myTotal    = (clone $mine)->count();
        $myDrafts   = (clone $mine)->where('status', NeedStatus::NEW)->count();
        $myPending  = (clone $mine)->whereIn('status', [NeedStatus::PENDING_GOVERNORATE_REVIEW, NeedStatus::PENDING_BRANCH_APPROVAL])->count();
        $myApproved = (clone $mine)->where('status', NeedStatus::APPROVED)->count();
        $myReturned = (clone $mine)->where('status', NeedStatus::RETURNED_FOR_EDIT)->count();
        $myRejected = (clone $mine)->where('status', NeedStatus::REJECTED)->count();

        $byMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $byMonth[] = [
                'label' => $month->format('Y-m'),
                'total' => (clone $mine)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count(),
            ];
        }

        $recent = (clone $mine)->latest()->limit(8)->get(['id', 'need_code', 'title', 'status', 'created_at'])
            ->map(fn ($n) => [
                'id' => $n->id, 'need_code' => $n->need_code,
                'title' => $n->title, 'status' => NeedStatus::label($n->status),
                'created_at' => $n->created_at?->format('Y-m-d'),
            ])->values()->toArray();

        return [
            'role_view' => 'data_entry',
            'kpis' => [
                'my_total'    => $myTotal,
                'my_drafts'   => $myDrafts,
                'my_pending'  => $myPending,
                'my_approved' => $myApproved,
                'my_returned' => $myReturned,
                'my_rejected' => $myRejected,
            ],
            'by_status' => [
                NeedStatus::NEW => $myDrafts,
                NeedStatus::PENDING_GOVERNORATE_REVIEW => $myPending,
                NeedStatus::APPROVED => $myApproved,
                NeedStatus::RETURNED_FOR_EDIT => $myReturned,
                NeedStatus::REJECTED => $myRejected,
            ],
            'by_month'       => $byMonth,
            'approval_rate'  => $myTotal > 0 ? round($myApproved / $myTotal * 100) : 0,
            'recent'         => $recent,
        ];
    }

    private function analyticsReviewer(User $user): array
    {
        $base = NeedDataScope::scopeNeeds(Need::query(), $user);

        $pending  = (clone $base)->whereIn('status', [NeedStatus::PENDING_GOVERNORATE_REVIEW, NeedStatus::PENDING_BRANCH_APPROVAL])->count();
        $returned = (clone $base)->where('status', NeedStatus::RETURNED_FOR_EDIT)->count();
        $approved = (clone $base)->where('status', NeedStatus::APPROVED)->count();
        $rejected = (clone $base)->where('status', NeedStatus::REJECTED)->count();
        $total    = (clone $base)->count();

        $byMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $byMonth[] = [
                'label' => $month->format('Y-m'),
                'approved' => (clone $base)->where('status', NeedStatus::APPROVED)
                    ->whereYear('approved_at', $month->year)->whereMonth('approved_at', $month->month)->count(),
                'rejected' => (clone $base)->where('status', NeedStatus::REJECTED)
                    ->whereYear('rejected_at', $month->year)->whereMonth('rejected_at', $month->month)->count(),
            ];
        }

        $pendingItems = (clone $base)->whereIn('status', [NeedStatus::PENDING_GOVERNORATE_REVIEW, NeedStatus::PENDING_BRANCH_APPROVAL])
            ->latest()->limit(8)->get(['id', 'need_code', 'title', 'status', 'priority', 'created_at'])
            ->map(fn ($n) => [
                'id' => $n->id, 'need_code' => $n->need_code,
                'title' => $n->title, 'status' => NeedStatus::label($n->status),
                'priority' => $n->priority, 'created_at' => $n->created_at?->format('Y-m-d'),
            ])->values()->toArray();

        return [
            'role_view' => 'reviewer',
            'kpis' => [
                'pending_review' => $pending,
                'returned'       => $returned,
                'approved'       => $approved,
                'rejected'       => $rejected,
                'total_scope'    => $total,
            ],
            'by_status' => [
                'pending'  => $pending,
                'returned' => $returned,
                'approved' => $approved,
                'rejected' => $rejected,
            ],
            'by_month'      => $byMonth,
            'approval_rate' => ($approved + $rejected) > 0 ? round($approved / ($approved + $rejected) * 100) : 0,
            'pending_items' => $pendingItems,
        ];
    }

    public function mapPoints(User $user, array $filters = []): array
    {
        $limit = max(1, min((int) ($filters['limit'] ?? 300), 500));
        $withSectors = filter_var($filters['with_sectors'] ?? false, FILTER_VALIDATE_BOOLEAN);
        unset($filters['limit'], $filters['with_sectors']);

        $query = NeedDataScope::scopeNeeds(
            Need::query()->whereNotNull('latitude')->whereNotNull('longitude'),
            $user
        )->with(['governorate:id,name_ar']);

        if ($withSectors) {
            $query->with(['sectors:id,code,name_ar']);
        }

        $this->applyFilters($query, $filters);

        $rows = $query->limit($limit + 1)->get([
            'id', 'need_code', 'title', 'need_owner_type', 'need_type', 'need_scope',
            'need_category', 'facility_type', 'facility_subtype', 'targeting_type',
            'sector', 'priority', 'status', 'source_platform', 'proposed_intervention',
            'governorate_id', 'branch_id', 'district_name', 'beneficiaries_count',
            'latitude', 'longitude', 'applicant_name',
        ]);

        $truncated = $rows->count() > $limit;
        if ($truncated) {
            $rows = $rows->take($limit);
        }

        $points = $rows->map(fn (Need $n) => [
            'id' => $n->id,
            'need_code' => $n->need_code,
            'title' => $n->title,
            'need_owner_type' => $n->need_owner_type,
            'need_type' => $n->need_type,
            'need_scope' => $n->need_scope,
            'need_category' => $n->need_category,
            'facility_type' => $n->facility_type,
            'facility_subtype' => $n->facility_subtype,
            'targeting_type' => $n->targeting_type,
            'sectors' => $withSectors
                ? $n->sectors->map(fn ($s) => ['code' => $s->code, 'label' => $s->name_ar])->values()->all()
                : [],
            'sector' => $n->sector,
            'priority' => $n->priority,
            'status' => $n->status,
            'status_label' => \App\Support\NeedStatus::label($n->status),
            'source_platform' => $n->source_platform,
            'proposed_intervention' => $n->proposed_intervention,
            'governorate' => $n->governorate?->name_ar,
            'district_name' => $n->district_name,
            'beneficiaries_count' => $n->beneficiaries_count,
            'applicant_name' => $n->applicant_name,
            'latitude' => $n->latitude,
            'longitude' => $n->longitude,
        ])->values()->all();

        return [
            'points' => $points,
            'meta' => [
                'returned' => count($points),
                'limit' => $limit,
                'truncated' => $truncated,
            ],
        ];
    }

    /**
     * @param  Builder<Need>  $query
     * @param  array<string, mixed>  $filters
     */
    public function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['governorate_id'])) {
            $query->where('governorate_id', (int) $filters['governorate_id']);
        }
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }
        if (!empty($filters['sector'])) {
            $query->where('sector', $filters['sector']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['need_type'])) {
            $query->where('need_type', $filters['need_type']);
        }
        if (!empty($filters['need_category'])) {
            $query->where('need_category', $filters['need_category']);
        }
        if (!empty($filters['facility_type'])) {
            $query->where('facility_type', $filters['facility_type']);
        }
        if (!empty($filters['facility_subtype'])) {
            $query->where('facility_subtype', $filters['facility_subtype']);
        }
        if (!empty($filters['targeting_type'])) {
            $query->where('targeting_type', $filters['targeting_type']);
        }
        if (!empty($filters['district_name'])) {
            $query->where('district_name', $filters['district_name']);
        }
        if (!empty($filters['sector_code'])) {
            $query->whereHas('sectors', fn (Builder $q) => $q->where('code', $filters['sector_code']));
        }
        if (!empty($filters['need_owner_type'])) {
            $query->where('need_owner_type', $filters['need_owner_type']);
        }
        if (!empty($filters['need_scope'])) {
            $query->where('need_scope', $filters['need_scope']);
        }
        if (!empty($filters['source_platform'])) {
            $query->where('source_platform', $filters['source_platform']);
        }
        if (!empty($filters['proposed_intervention'])) {
            $query->where('proposed_intervention', $filters['proposed_intervention']);
        }
        if (!empty($filters['need_complexity'])) {
            $query->where('need_complexity', $filters['need_complexity']);
        }
        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            if ($q !== '') {
                $query->where(function (Builder $inner) use ($q) {
                    $inner->where('title', 'like', '%'.$q.'%')
                        ->orWhere('need_code', 'like', '%'.$q.'%')
                        ->orWhere('sector', 'like', '%'.$q.'%')
                        ->orWhere('description', 'like', '%'.$q.'%');
                });
            }
        }
    }
}
