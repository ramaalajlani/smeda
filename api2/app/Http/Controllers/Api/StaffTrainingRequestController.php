<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaffTrainingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffTrainingRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.training_requests.view')) {
            return response()->json(['message' => 'ليس لديك صلاحية عرض طلبات تدريب الكوادر.'], 403);
        }

        $rows = StaffTrainingRequest::query()
            ->with('user:id,name,email')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.training_requests.create')) {
            return response()->json(['message' => 'ليس لديك صلاحية إرسال طلب تدريب كوادر.'], 403);
        }

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'employees_count' => ['required', 'integer', 'min:1', 'max:10000'],
            'training_field' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
        ]);

        $row = StaffTrainingRequest::query()->create(array_merge($validated, [
            'user_id' => $user->id,
            'status' => 'pending',
        ]));

        return response()->json([
            'message' => 'تم إرسال طلب تدريب الكوادر بنجاح.',
            'data' => $row,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.training_requests.view')) {
            return response()->json(['message' => 'ليس لديك صلاحية إدارة طلبات تدريب الكوادر.'], 403);
        }

        $row = StaffTrainingRequest::query()->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,scheduled,closed'],
        ]);

        $row->update($validated);

        return response()->json([
            'message' => 'تم تحديث حالة الطلب.',
            'data' => $row->fresh(),
        ]);
    }
}
