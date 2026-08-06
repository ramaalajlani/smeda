<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPostingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.jobs.view')) {
            return response()->json(['message' => 'ليس لديك صلاحية عرض فرص العمل.'], 403);
        }

        $canManage = $user->hasPermissionTo('workforce.jobs.manage');

        $rows = JobPosting::query()
            ->with(['governorate:id,name_ar', 'user:id,name,email'])
            ->when(!$canManage, fn ($q) => $q->published())
            ->when($request->filled('status') && $canManage, fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('sector'), fn ($q) => $q->where('sector', $request->string('sector')))
            ->when($request->filled('city'), fn ($q) => $q->where('city', 'like', '%'.$request->string('city').'%'))
            ->search($request->input('search'))
            ->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return response()->json($rows);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.jobs.view')) {
            return response()->json(['message' => 'ليس لديك صلاحية عرض فرص العمل.'], 403);
        }

        $posting = JobPosting::query()
            ->with(['governorate:id,name_ar', 'user:id,name,email'])
            ->findOrFail($id);

        if ($posting->status !== 'published' && !$user->hasPermissionTo('workforce.jobs.manage')) {
            return response()->json(['message' => 'فرصة العمل غير متاحة.'], 404);
        }

        return response()->json(['data' => $posting]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.jobs.create')) {
            return response()->json(['message' => 'ليس لديك صلاحية نشر فرص العمل.'], 403);
        }

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'employment_type' => ['required', 'in:full_time,part_time,contract,freelance'],
            'sector' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'skills' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'in:draft,published,closed'],
        ]);

        $posting = JobPosting::query()->create(array_merge($validated, [
            'user_id' => $user->id,
            'status' => $validated['status'] ?? 'published',
        ]));

        return response()->json([
            'message' => 'تم نشر فرصة العمل بنجاح.',
            'data' => $posting->load(['governorate:id,name_ar']),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.jobs.manage')) {
            return response()->json(['message' => 'ليس لديك صلاحية إدارة فرص العمل.'], 403);
        }

        $posting = JobPosting::query()->findOrFail($id);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:draft,published,closed'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $posting->update($validated);

        return response()->json([
            'message' => 'تم تحديث فرصة العمل.',
            'data' => $posting->fresh(),
        ]);
    }
}
