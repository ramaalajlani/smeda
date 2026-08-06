<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingSupervisorResource;
use App\Models\TrainingSupervisor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingSupervisorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\TrainingCenter::class);

        $rows = TrainingSupervisor::query()
            ->select(['id', 'name', 'code', 'type', 'parent_id', 'branch_id', 'governorate_id', 'is_active'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')->toString()))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->with(['parent:id,name,code,type', 'branch:id,name', 'governorate:id,name_ar'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => TrainingSupervisorResource::collection($rows),
        ]);
    }
}
