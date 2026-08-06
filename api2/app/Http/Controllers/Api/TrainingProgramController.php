<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingProgramResource;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingProgramController extends Controller
{
    /**
     * Display a paginated listing of training programs.
     */
    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $programs = TrainingProgram::query()
            ->select([
                'id',
                'name',
                'code',
                'description',
                'status',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->withCount([
                'kits',
                'courses',
                'certificates',
            ])
            ->when($request->boolean('with_kits', false), function (Builder $query) {
                $query->with([
                    'kits:id,name,code,sector,category,type,level,hours,status',
                ]);
            })
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('is_active'), function (Builder $query) use ($request) {
                $query->where('is_active', $this->toBoolean($request->input('is_active')));
            })
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return TrainingProgramResource::collection($programs)->additional([
            'meta' => [
                'filters' => [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'is_active' => $request->input('is_active'),
                    'with_kits' => $request->boolean('with_kits', false),
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }

    /**
     * Display the specified training program.
     */
    public function show(int $id): JsonResponse
    {
        $program = TrainingProgram::query()
            ->select([
                'id',
                'name',
                'code',
                'description',
                'status',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->with([
                'kits:id,name,code,sector,category,type,level,hours,status',
            ])
            ->withCount([
                'kits',
                'courses',
                'certificates',
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => new TrainingProgramResource($program),
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