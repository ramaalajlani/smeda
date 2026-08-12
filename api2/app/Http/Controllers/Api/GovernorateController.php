<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Http\Resources\GovernorateResource;
use App\Models\Branch;
use App\Models\Governorate;
use App\Support\AccessControlGuard;
use App\Support\BranchDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GovernorateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user && (
                $user->can('view_governorates')
                || $user->can('finance.applications.create')
                || BranchDataScope::hasNationalReadAccess($user)
                || BranchDataScope::isBranchManager($user)
            ),
            403
        );

        $lite = $request->boolean('lite');
        $scopeKey = BranchDataScope::isBranchManager($user)
            ? 'branch:'.$user->governorate_id
            : 'all';
        $cacheKey = 'governorates:'.$scopeKey.':'.($lite ? 'lite' : 'full').':v2';

        $governorates = Cache::remember($cacheKey, 3600, function () use ($user, $lite) {
            $query = Governorate::query()
                ->when(BranchDataScope::isBranchManager($user), fn ($q) => $q->whereKey($user->governorate_id))
                ->orderBy('name_ar');

            if ($lite) {
                return $query->get(['id', 'name_ar', 'code']);
            }

            return $query->withCount('branches')->get();
        });

        if ($lite) {
            return response()->json([
                'data' => $governorates->map(fn ($g) => [
                    'id' => $g->id,
                    'name_ar' => $g->name_ar,
                    'code' => $g->code,
                ])->values(),
            ]);
        }

        return response()->json(['data' => GovernorateResource::collection($governorates)]);
    }
}
