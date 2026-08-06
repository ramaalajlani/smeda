<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Support\SecureFileStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.applications.view')) {
            return response()->json(['message' => 'ليس لديك صلاحية عرض طلبات التوظيف.'], 403);
        }

        $rows = JobApplication::query()
            ->with(['jobPosting:id,title,organization_name', 'user:id,name,email'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('job_posting_id'), fn ($q) => $q->where('job_posting_id', $request->integer('job_posting_id')))
            ->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.applications.create')) {
            return response()->json(['message' => 'ليس لديك صلاحية تقديم طلب توظيف.'], 403);
        }

        $validated = $request->validate([
            'job_posting_id' => ['nullable', 'integer', 'exists:job_postings,id'],
            'applicant_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'string', 'max:50'],
            'summary' => ['nullable', 'string'],
            'cv' => ['nullable', 'file', 'max:5120'],
        ]);

        if (!empty($validated['job_posting_id'])) {
            $posting = JobPosting::query()->published()->find($validated['job_posting_id']);
            if (!$posting) {
                return response()->json(['message' => 'فرصة العمل غير متاحة للتقديم.'], 422);
            }
        }

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = SecureFileStorage::storeUploadedFile(
                $request->file('cv'),
                'workforce/cvs',
                'public',
                ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            );
        }

        $application = JobApplication::query()->create([
            'job_posting_id' => $validated['job_posting_id'] ?? null,
            'user_id' => $user->id,
            'applicant_name' => $validated['applicant_name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? $user->email,
            'specialty' => $validated['specialty'] ?? null,
            'city' => $validated['city'] ?? null,
            'experience_years' => $validated['experience_years'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'cv_path' => $cvPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تم إرسال طلب التوظيف بنجاح.',
            'data' => $application->load('jobPosting:id,title,organization_name'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasPermissionTo('workforce.applications.view')) {
            return response()->json(['message' => 'ليس لديك صلاحية مراجعة طلبات التوظيف.'], 403);
        }

        $application = JobApplication::query()->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,accepted,rejected'],
        ]);

        $application->update($validated);

        return response()->json([
            'message' => 'تم تحديث حالة الطلب.',
            'data' => $application->fresh(),
        ]);
    }
}
