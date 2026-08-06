<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingCenterResource;
use App\Models\TrainingCenter;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingCenterController extends Controller
{
    /**
     * Display a paginated listing of training centers.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TrainingCenter::class);

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $query = TrainingCenter::query()
            ->select([
                'id',
                'name',
                'code',
                'city',
                'address',
                'phone',
                'email',
                'classification',
                'accreditation_status',
                'supervisor_id',
                'supports_offline_training',
                'supports_online_training',
                'accreditation_start_date',
                'accreditation_end_date',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->withCount([
                'trainers',
                'courses',
                'certificates',
            ])
            ->with([
                'supervisor:id,name,code,type,parent_id,is_active',
            ])
            ->when($request->boolean('with_platforms', false), function (Builder $query) {
                $query->with([
                    'platforms:id,training_center_id,platform_name,platform_url,status,approved_at,expires_at,notes',
                ]);
            })
            ->when($request->filled('supervisor_id'), function (Builder $query) use ($request) {
                $query->where('supervisor_id', (int) $request->integer('supervisor_id'));
            })
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('accreditation_status', $request->string('status')->toString());
            })
            ->when($request->filled('city'), function (Builder $query) use ($request) {
                $query->inCity($request->input('city'));
            })
            ->when($request->filled('classification'), function (Builder $query) use ($request) {
                $query->where('classification', $request->string('classification')->toString());
            })
            ->when($request->filled('is_active'), function (Builder $query) use ($request) {
                $query->where('is_active', $this->toBoolean($request->input('is_active')));
            })
            ->when($request->filled('supports_online_training'), function (Builder $query) use ($request) {
                $query->where('supports_online_training', $this->toBoolean($request->input('supports_online_training')));
            })
            ->when($request->filled('supports_offline_training'), function (Builder $query) use ($request) {
                $query->where('supports_offline_training', $this->toBoolean($request->input('supports_offline_training')));
            })
            ->when($request->filled('has_location'), function (Builder $query) {
                $query->whereNotNull('latitude')->whereNotNull('longitude');
            })
            ->search($request->input('search'))
            ->orderByDesc('id');

        $centers = TrainingDataScope::scopeTrainingCenters($query, $request->user())
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TrainingCenterResource::collection($centers)->additional([
            'meta' => [
                'filters' => [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'city' => $request->input('city'),
                    'classification' => $request->input('classification'),
                    'supervisor_id' => $request->input('supervisor_id'),
                    'is_active' => $request->input('is_active'),
                    'supports_online_training' => $request->input('supports_online_training'),
                    'supports_offline_training' => $request->input('supports_offline_training'),
                    'with_platforms' => $request->boolean('with_platforms', false),
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }

    /**
     * Display the specified training center.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $center = TrainingDataScope::scopeTrainingCenters(TrainingCenter::query(), $request->user())
            ->select([
                'id',
                'name',
                'code',
                'city',
                'address',
                'phone',
                'email',
                'classification',
                'accreditation_status',
                'supervisor_id',
                'supports_offline_training',
                'supports_online_training',
                'accreditation_start_date',
                'accreditation_end_date',
                'is_active',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->with([
                'platforms:id,training_center_id,platform_name,platform_url,status,approved_at,expires_at,notes',
                'supervisor:id,name,code,type,parent_id,is_active',
            ])
            ->withCount([
                'trainers',
                'courses',
                'certificates',
            ])
            ->findOrFail($id);

        $this->authorize('view', $center);

        return response()->json([
            'data' => new TrainingCenterResource($center),
        ]);
    }

    /**
     * Convert request value to boolean.
     */
    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}