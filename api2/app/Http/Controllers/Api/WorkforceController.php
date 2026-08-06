<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkforceResource;
use App\Models\Trainee;
use App\Models\User;
use App\Models\Workforce;
use App\Support\TrainingDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkforceController extends Controller
{
    private function canViewWorkforces(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (TrainingDataScope::hasBroadTrainingReadAccess($user)) {
            return true;
        }

        return $user->hasRole('workforce_manager')
            || $user->hasPermissionTo('workforce.applications.view');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$this->canViewWorkforces($user)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض القوى العاملة.',
            ], 403);
        }

        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $rows = Workforce::query()
            ->select([
                'id',
                'trainee_id',
                'workforce_code',
                'status',
                'joined_at',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->with([
                'trainee:id,name,trainee_code,national_id,phone,email,city,status',
            ])
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->appends($request->query());

        return WorkforceResource::collection($rows)->additional([
            'meta' => [
                'filters' => [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }


    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$this->canViewWorkforces($user)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض بيانات القوى العاملة.',
            ], 403);
        }

        $row = Workforce::query()
            ->with([
                'trainee:id,name,trainee_code,national_id,phone,email,city,address,birth_date,gender,education_level,status,notes',
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => new WorkforceResource($row),
        ]);
    }

    /**
     * Enroll trainee into workforce.
     */
    public function enroll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !TrainingDataScope::hasUnrestrictedTrainingAccess($user)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية إدخال متدربين إلى القوى العاملة.',
            ], 403);
        }

        $validated = $request->validate([
            'trainee_id' => ['required', 'integer', 'exists:trainees,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $trainee = Trainee::query()
            ->with([
                'certificates:id,trainee_id,certificate_number,status,is_verified',
                'workforce:id,trainee_id,workforce_code,status',
            ])
            ->findOrFail($validated['trainee_id']);

        if ($trainee->workforce) {
            return response()->json([
                'message' => 'المتدرب موجود مسبقاً ضمن القوى العاملة.',
                'data' => new WorkforceResource($trainee->workforce->load('trainee')),
            ], 409);
        }

        $hasApprovedVerifiedCertificate = $trainee->certificates->contains(function ($certificate) {
            return $certificate->status === 'approved' && (bool) $certificate->is_verified;
        });

        if (!$hasApprovedVerifiedCertificate) {
            return response()->json([
                'message' => 'لا يمكن إدخال المتدرب إلى القوى العاملة بدون شهادة معتمدة وموثقة.',
            ], 422);
        }

        $workforce = DB::transaction(function () use ($trainee, $validated) {
            return Workforce::create([
                'trainee_id' => $trainee->id,
                'workforce_code' => 'WF-' . str_pad((string) $trainee->id, 6, '0', STR_PAD_LEFT),
                'status' => 'active',
                'joined_at' => now()->toDateString(),
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        $workforce->load('trainee');

        return response()->json([
            'message' => 'تم إدخال المتدرب إلى القوى العاملة بنجاح.',
            'data' => new WorkforceResource($workforce),
        ], 201);
    }
}