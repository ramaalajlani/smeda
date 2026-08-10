<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GovernorateResource;
use App\Http\Resources\TrainingProgramResource;
use App\Models\FundingApplication;
use App\Models\Governorate;
use App\Models\JobPosting;
use App\Models\Need;
use App\Models\TrainingProgram;
use App\Services\Finance\FundingMetricsService;
use App\Services\Needs\NeedCodeGenerator;
use App\Services\Needs\NeedDashboardService;
use App\Support\NeedStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Read-only public browse endpoints for guest-facing front pages.
 */
class PublicBrowseController extends Controller
{
    public function __construct(
        private NeedDashboardService $needsDashboard,
        private FundingMetricsService $fundingMetrics,
    ) {}

    public function governorates(): JsonResponse
    {
        $rows = Cache::remember('public:governorates:v1', 86400, fn () => Governorate::query()
            ->withCount('branches')
            ->orderBy('name_ar')
            ->get());

        return response()->json(['data' => GovernorateResource::collection($rows)]);
    }

    public function needsLookups(): JsonResponse
    {
        return response()->json(['data' => NeedController::legacyLookupsData()]);
    }

    public function needsMap(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->integer('limit', 200), 500));

        $query = Need::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', NeedStatus::APPROVED)
            ->with(['governorate:id,name_ar', 'sectors:id,code,name_ar']);

        $this->needsDashboard->applyFilters($query, $this->needFilters($request));

        $rows = $query->limit($limit + 1)->get([
            'id', 'need_code', 'title', 'need_owner_type', 'need_type', 'need_scope',
            'need_category', 'facility_type', 'facility_subtype', 'targeting_type',
            'sector', 'priority', 'status', 'source_platform', 'proposed_intervention',
            'governorate_id', 'district_name', 'beneficiaries_count', 'latitude', 'longitude',
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
            'need_category_label' => \App\Support\NeedTaxonomy::label(\App\Support\NeedTaxonomy::TYPE_CATEGORY, $n->need_category),
            'facility_type' => $n->facility_type,
            'facility_type_label' => \App\Support\NeedTaxonomy::label(\App\Support\NeedTaxonomy::TYPE_FACILITY, $n->facility_type),
            'facility_subtype' => $n->facility_subtype,
            'facility_subtype_label' => \App\Support\NeedTaxonomy::label(\App\Support\NeedTaxonomy::TYPE_FACILITY_SUBTYPE, $n->facility_subtype),
            'targeting_type' => $n->targeting_type,
            'targeting_type_label' => \App\Support\NeedTaxonomy::label(\App\Support\NeedTaxonomy::TYPE_TARGETING, $n->targeting_type),
            'sectors' => $n->sectors->map(fn ($s) => ['code' => $s->code, 'label' => $s->name_ar])->values()->all(),
            'sector' => $n->sector,
            'priority' => $n->priority,
            'status' => $n->status,
            'status_label' => NeedStatus::label($n->status),
            'source_platform' => $n->source_platform,
            'proposed_intervention' => $n->proposed_intervention,
            'governorate' => $n->governorate?->name_ar,
            'district_name' => $n->district_name,
            'beneficiaries_count' => $n->beneficiaries_count,
            'latitude' => $n->latitude,
            'longitude' => $n->longitude,
        ])->values()->all();

        return response()->json([
            'data' => $points,
            'meta' => [
                'returned' => count($points),
                'limit' => $limit,
                'truncated' => $truncated,
            ],
        ]);
    }

    public function trainingPrograms(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $programs = TrainingProgram::query()
            ->select(['id', 'name', 'code', 'description', 'status', 'is_active', 'created_at', 'updated_at'])
            ->where('is_active', true)
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')->toString()))
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TrainingProgramResource::collection($programs)->response();
    }

    public function financeCloud(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $rows = FundingApplication::query()
            ->select([
                'id',
                'application_number',
                'project_name',
                'project_sector',
                'financing_type',
                'requested_amount',
                'currency',
                'purpose',
                'status',
                'current_stage',
                'governorate_id',
                'branch_id',
            ])
            ->whereIn('status', ['approved', 'funded'])
            ->with([
                'governorate:id,name_ar',
                'branch:id,name',
            ])
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'data' => $rows->getCollection()->map(fn (FundingApplication $row) => [
                'id' => $row->id,
                'application_number' => $row->application_number,
                'project_name' => $row->project_name,
                'project_sector' => $row->project_sector,
                'financing_type' => $row->financing_type,
                'requested_amount' => $row->requested_amount,
                'currency' => $row->currency,
                'purpose' => $row->purpose,
                'status' => $row->status,
                'current_stage' => $row->current_stage,
                'governorate_name' => $row->governorate?->name_ar,
                'branch_name' => $row->branch?->name,
                'consultant_assignments' => [],
            ])->values()->all(),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function financeMetrics(): JsonResponse
    {
        $data = Cache::remember('public:finance-metrics:v1', 300, fn () => $this->fundingMetrics->publicMetrics());

        return response()->json(['data' => $data]);
    }

    public function jobPostings(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));
        $cacheKey = 'public:jobs:v1:' . md5(json_encode($request->only(['search', 'sector', 'city', 'page', 'per_page'])));

        $payload = Cache::remember($cacheKey, 120, function () use ($request, $perPage) {
            return JobPosting::query()
                ->published()
                ->when($request->filled('sector'), fn ($q) => $q->where('sector', $request->string('sector')->toString()))
                ->when($request->filled('city'), fn ($q) => $q->where('city', 'like', '%'.$request->string('city')->toString().'%'))
                ->search($request->input('search'))
                ->select([
                    'id', 'organization_name', 'title', 'city', 'governorate_id',
                    'employment_type', 'sector', 'description', 'status', 'created_at',
                ])
                ->with(['governorate:id,name_ar'])
                ->orderByDesc('id')
                ->paginate($perPage)
                ->appends($request->query());
        });

        return response()->json($payload);
    }

    public function storeGuestNeed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'summary' => ['nullable', 'string', 'max:500'],
            'need_owner_type' => ['nullable', 'in:citizen,state'],
            'need_scope' => ['nullable', 'in:individual,project,local,governorate,national,sectoral'],
            'need_complexity' => ['nullable', 'in:general,specific'],
            'need_type' => ['nullable', 'string', 'max:100'],
            'need_category' => ['nullable', 'string', 'max:100'],
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'district_name' => ['nullable', 'string', 'max:255'],
            'administrative_unit_name' => ['nullable', 'string', 'max:255'],
            'countryside_name' => ['nullable', 'string', 'max:255'],
            'locality_name' => ['nullable', 'string', 'max:255'],
            'village_or_neighborhood' => ['nullable', 'string', 'max:255'],
            'address_details' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:32,37.5'],
            'longitude' => ['required', 'numeric', 'between:35.4,42.5'],
            'location_source' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'string', 'max:100'],
            'economic_sector' => ['nullable', 'string', 'max:100'],
            'syrsic_section' => ['nullable', 'string', 'max:100'],
            'syrsic_division' => ['nullable', 'string', 'max:100'],
            'syrsic_group' => ['nullable', 'string', 'max:100'],
            'syrsic_class' => ['nullable', 'string', 'max:100'],
            'syrsic_activity' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'string', 'max:50'],
            'state_need_level' => ['nullable', 'string', 'max:100'],
            'citizen_need_profile' => ['nullable', 'string', 'max:100'],
            'responsible_entity' => ['nullable', 'string', 'max:255'],
            'applicant_name' => ['nullable', 'string', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:50'],
            'applicant_email' => ['nullable', 'email', 'max:255'],
            'applicant_type' => ['nullable', 'string', 'max:100'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'beneficiaries_count' => ['nullable', 'integer', 'min:0'],
            'expected_jobs_count' => ['nullable', 'integer', 'min:0'],
            'expected_projects_count' => ['nullable', 'integer', 'min:0'],
            'impact_level' => ['nullable', 'string', 'max:100'],
            'urgency_level' => ['nullable', 'string', 'max:100'],
            'expected_duration' => ['nullable', 'string', 'max:100'],
            'available_partners' => ['nullable', 'string'],
            'obstacles' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $need = Need::query()->create(array_merge($validated, [
            'need_code' => app(NeedCodeGenerator::class)->next(),
            'need_owner_type' => $validated['need_owner_type'] ?? 'citizen',
            'need_complexity' => $validated['need_complexity'] ?? 'specific',
            'source_platform' => 'other',
            'status' => NeedStatus::NEW,
            'approval_status' => NeedStatus::NEW,
            'is_mapped' => ! empty($validated['latitude']) && ! empty($validated['longitude']),
            'created_by' => null,
            'location_source' => $validated['location_source'] ?? 'map_click',
        ]));

        return response()->json([
            'message' => 'تم استلام احتياجك بنجاح. سيتم مراجعته من قِبَل الفريق المختص.',
            'need_code' => $need->need_code,
        ], 201);
    }

    /** @return array<string, mixed> */
    private function needFilters(Request $request): array
    {
        return array_filter([
            'governorate_id' => $request->input('governorate_id'),
            'sector' => $request->input('sector'),
            'sector_code' => $request->input('sector_code'),
            'priority' => $request->input('priority'),
            'status' => $request->input('status'),
            'need_category' => $request->input('need_category'),
            'facility_type' => $request->input('facility_type'),
            'facility_subtype' => $request->input('facility_subtype'),
            'targeting_type' => $request->input('targeting_type'),
            'district_name' => $request->input('district_name'),
        ], fn ($v) => $v !== null && $v !== '');
    }
}
