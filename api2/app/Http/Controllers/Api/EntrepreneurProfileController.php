<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntrepreneurProfile;
use App\Services\Entrepreneur\EntrepreneurProfileExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntrepreneurProfileController extends Controller
{
    public function __construct(private EntrepreneurProfileExportService $exportService) {}
    /** @return array<string, string> */
    private function userProfileRules(bool $partial = false): array
    {
        $prefix = $partial ? 'sometimes|' : '';

        return [
            'full_name'                  => $prefix . 'required|string|max:255',
            'governorate'                => 'nullable|string',
            'phone'                      => 'nullable|string|max:20',
            'email'                      => 'nullable|email',
            'age'                        => 'nullable|integer|min:16|max:80',
            'education_level'            => 'nullable|string',
            'specialization'             => 'nullable|string',
            'project_name'               => $prefix . 'required|string|max:255',
            'project_field'              => 'nullable|string',
            'project_field_other'        => 'nullable|string',
            'founding_year'              => 'nullable|integer|min:2000|max:2030',
            'executive_summary'          => 'nullable|string',
            'elevator_pitch'             => 'nullable|string',
            'readiness_stage'            => 'nullable|string',
            'has_prototype'              => 'nullable|boolean',
            'tested_with_users'          => 'nullable|boolean',
            'testing_results'            => 'nullable|string',
            'problem_description'        => 'nullable|string',
            'target_customers'           => 'nullable|string',
            'differentiation'            => 'nullable|string',
            'competitive_advantages'     => 'nullable|array',
            'team_size_range'            => 'nullable|string',
            'team_roles'                 => 'nullable|array',
            'technologies'               => 'nullable|array',
            'market_validation_methods'  => 'nullable|array',
            'target_market'              => 'nullable|string',
            'current_users_range'        => 'nullable|string',
            'current_customers_range'    => 'nullable|string',
            'has_revenue'                => 'nullable|boolean',
            'revenue_sources'            => 'nullable|array',
            'funding_sources'            => 'nullable|array',
            'seeking_investment'         => 'nullable|boolean',
            'investment_needed_range'    => 'nullable|string',
            'challenges'                 => 'nullable|array',
            'jobs_3years_range'          => 'nullable|string',
            'scalability_outside_syria'  => 'nullable|string',
            'support_needed'             => 'nullable|array',
            'previous_participation'     => 'nullable|array',
            'additional_notes'           => 'nullable|string',
        ];
    }

    public function myProfile()
    {
        $profile = EntrepreneurProfile::where('user_id', Auth::id())->latest()->first();
        return response()->json($profile);
    }

    public function store(Request $request)
    {
        $data = $request->validate(array_merge($this->userProfileRules(), [
            'status' => 'nullable|in:draft,submitted',
        ]));

        $status = $data['status'] ?? 'draft';
        unset($data['status']);

        $existing = EntrepreneurProfile::where('user_id', Auth::id())
            ->whereIn('status', ['draft'])->latest()->first();

        if ($existing) {
            $existing->fill($data);
            $existing->status = $status;
            $existing->save();

            return response()->json($existing->fresh(), 200);
        }

        $profile = new EntrepreneurProfile();
        $profile->fill($data);
        $profile->user_id = Auth::id();
        $profile->status = $status;
        $profile->save();

        return response()->json($profile, 201);
    }

    public function update(Request $request, $id)
    {
        $profile = EntrepreneurProfile::where('user_id', Auth::id())->findOrFail($id);
        $data = $request->validate($this->userProfileRules(true));
        $profile->update($data);

        return response()->json($profile->fresh());
    }

    // للمشرفين
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'project_field', 'governorate', 'search']);

        return response()->json(
            $this->exportService->buildQuery($filters)
                ->with(['user:id,name', 'reviewer:id,name'])
                ->latest()
                ->paginate(max(1, min((int) $request->integer('per_page', 15), 100)))
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['status', 'project_field', 'governorate', 'search']);

        return $this->exportService->exportCsv($filters);
    }

    public function show($id)
    {
        $profile = EntrepreneurProfile::with(['user:id,name,email', 'reviewer:id,name'])->findOrFail($id);
        return response()->json($profile);
    }

    public function review(Request $request, $id)
    {
        $profile = EntrepreneurProfile::findOrFail($id);
        $data    = $request->validate([
            'status'         => 'required|in:approved,rejected,under_review',
            'reviewer_notes' => 'nullable|string',
        ]);

        $profile->forceFill(array_merge($data, [
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]))->save();

        return response()->json($profile->fresh());
    }

    public function stats()
    {
        return response()->json([
            'total'        => EntrepreneurProfile::count(),
            'submitted'    => EntrepreneurProfile::where('status', 'submitted')->count(),
            'under_review' => EntrepreneurProfile::where('status', 'under_review')->count(),
            'approved'     => EntrepreneurProfile::where('status', 'approved')->count(),
            'rejected'     => EntrepreneurProfile::where('status', 'rejected')->count(),
            'by_field'     => EntrepreneurProfile::selectRaw('project_field, count(*) as cnt')
                                ->groupBy('project_field')->pluck('cnt', 'project_field'),
            'seeking_investment' => EntrepreneurProfile::where('seeking_investment', true)->count(),
        ]);
    }

    /** Public aggregate stats for marketing pages (no auth). */
    public function publicStats()
    {
        return response()->json([
            'total' => EntrepreneurProfile::whereIn('status', ['submitted', 'under_review', 'approved'])->count(),
        'approved' => EntrepreneurProfile::where('status', 'approved')->count(),
        ]);
    }
}
