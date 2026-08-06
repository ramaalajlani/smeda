<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultingOffice;
use App\Models\ConsultingOfficeSpecialization;
use App\Models\ConsultingOfficeViolation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultingOfficeController extends Controller
{
    private function canManageOffices($user): bool
    {
        return $user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
            'consultant_union_admin', 'branch_manager', 'governor',
        ]);
    }

    private function hasNationalOfficeAccess($user): bool
    {
        return $user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director',
            'project_services_manager', 'consultant_union_admin',
        ]);
    }

    private function scopeForUser(Request $request)
    {
        $user = $request->user();
        $query = ConsultingOffice::query();

        if ($this->hasNationalOfficeAccess($user)) {
            return $query;
        }

        if ($user->hasRole(['branch_manager', 'branch_officer']) && $user->governorate_id) {
            return $query->where('governorate_id', $user->governorate_id);
        }

        if ($user->hasRole('governor') && $user->governorate_id) {
            return $query->where('governorate_id', $user->governorate_id);
        }

        return $query->where('status', 'active');
    }

    private function canViewOffice(Request $request, ConsultingOffice $office): bool
    {
        $user = $request->user();

        if ($this->hasNationalOfficeAccess($user)) {
            return true;
        }

        if ($user->hasRole(['branch_manager', 'branch_officer']) && $user->governorate_id) {
            return (int) $office->governorate_id === (int) $user->governorate_id;
        }

        if ($user->hasRole('governor') && $user->governorate_id) {
            return (int) $office->governorate_id === (int) $user->governorate_id;
        }

        return $office->status === 'active';
    }

    /* ── GET /consulting/offices ── */
    public function index(Request $request): JsonResponse
    {
        $rows = $this->scopeForUser($request)
            ->with(['governorate:id,name_ar', 'specializations'])
            ->when($request->filled('status'),        fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('governorate_id'),fn ($q) => $q->where('governorate_id', $request->integer('governorate_id')))
            ->when($request->filled('category_code'), fn ($q) => $q->whereHas('specializations', fn ($s) => $s->where('category_code', $request->string('category_code'))))
            ->orderByDesc('overall_rating')
            ->paginate(max(1, min($request->integer('per_page', 20), 100)));

        return response()->json($rows);
    }

    /* ── POST /consulting/offices ── */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canManageOffices($user)) {
            abort(403, 'ليس لديك صلاحية إضافة مكتب استشاري.');
        }

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'governorate_id'   => ['nullable', 'integer', 'exists:governorates,id'],
            'license_number'   => ['nullable', 'string', 'max:100'],
            'license_date'     => ['nullable', 'date'],
            'license_expiry'   => ['nullable', 'date'],
            'address'          => ['nullable', 'string'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'email'            => ['nullable', 'email', 'max:255'],
            'website'          => ['nullable', 'url', 'max:255'],
            'specializations'  => ['nullable', 'array'],
            'specializations.*' => ['string', 'max:10'],
            'bio'              => ['nullable', 'string'],
            'notes'            => ['nullable', 'string'],
            'accreditation_date' => ['nullable', 'date'],
        ]);

        $office = ConsultingOffice::create(array_merge(
            collect($validated)->except('specializations')->toArray(),
            ['status' => 'pending']
        ));

        foreach ($validated['specializations'] ?? [] as $spec) {
            $catCode = is_array($spec) ? ($spec['category_code'] ?? null) : $spec;
            if ($catCode) {
                $office->specializations()->create(array_merge(
                    ['category_code' => $catCode],
                    is_array($spec) ? collect($spec)->except('category_code')->toArray() : []
                ));
            }
        }

        return response()->json(['message' => 'تم تسجيل المكتب. بانتظار الاعتماد.', 'data' => $office], 201);
    }

    /* ── GET /consulting/offices/{id} ── */
    public function show(Request $request, int $id): JsonResponse
    {
        $office = ConsultingOffice::with([
            'governorate:id,name_ar',
            'specializations',
            'reviews' => fn ($q) => $q->where('is_published', true)->latest()->limit(10),
            'reviews.reviewer:id,name',
        ])->findOrFail($id);

        if (!$this->canViewOffice($request, $office)) {
            abort(403, 'ليس لديك صلاحية عرض هذا المكتب.');
        }

        return response()->json(['data' => $office]);
    }

    /* ── PUT /consulting/offices/{id} ── */
    public function update(Request $request, int $id): JsonResponse
    {
        $office = ConsultingOffice::findOrFail($id);

        $user = $request->user();
        $isAdmin = $this->canManageOffices($user);
        $isOwner = $office->user_id === $user->id;

        if (!$isAdmin && !$isOwner) abort(403);

        $validated = $request->validate([
            'name'           => ['sometimes', 'string', 'max:255'],
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email'],
            'address'        => ['nullable', 'string'],
            'website'        => ['nullable', 'url'],
            'bio'            => ['nullable', 'string'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_date'   => ['nullable', 'date'],
            'license_expiry' => ['nullable', 'date'],
            'notes'          => ['nullable', 'string'],
            'specializations' => ['nullable', 'array'],
            'specializations.*' => ['string', 'max:10'],
        ]);

        // فقط الأدمن يغيّر الحالة
        if ($isAdmin && $request->filled('status')) {
            $validated['status'] = $request->string('status')->toString();
            if ($validated['status'] === 'active' && !$office->accreditation_date) {
                $validated['accreditation_date'] = now()->toDateString();
            }
        }

        $specs = $validated['specializations'] ?? null;
        unset($validated['specializations']);

        $office->update($validated);

        if ($isAdmin && is_array($specs)) {
            $office->specializations()->delete();
            foreach ($specs as $catCode) {
                if ($catCode) {
                    $office->specializations()->create(['category_code' => $catCode]);
                }
            }
        }

        return response()->json([
            'message' => 'تم تحديث بيانات المكتب.',
            'data' => $office->fresh(['governorate:id,name_ar', 'specializations']),
        ]);
    }

    /* ── DELETE /consulting/offices/{id} ── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$this->canManageOffices($user)) {
            abort(403, 'ليس لديك صلاحية حذف المكتب.');
        }

        $office = ConsultingOffice::findOrFail($id);
        $office->delete();

        return response()->json(['message' => 'تم حذف المكتب الاستشاري.']);
    }

    /* ── POST /consulting/offices/{id}/activate ── */
    public function activate(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$this->canManageOffices($user)) {
            abort(403);
        }

        $office = ConsultingOffice::findOrFail($id);
        $office->update(['status' => 'active', 'accreditation_date' => now()->toDateString()]);

        return response()->json(['message' => 'تم تفعيل المكتب.', 'data' => $office]);
    }

    /* ── POST /consulting/offices/{id}/suspend ── */
    public function suspend(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$this->canManageOffices($user)) {
            abort(403);
        }

        $office = ConsultingOffice::findOrFail($id);
        $office->update(['status' => 'suspended']);

        return response()->json(['message' => 'تم إيقاف المكتب.']);
    }

    /* ── POST /consulting/offices/{id}/violations ── */
    public function addViolation(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
            'consultant_union_admin', 'branch_manager', 'branch_officer', 'governor',
        ])) {
            abort(403);
        }

        $office = ConsultingOffice::findOrFail($id);
        $validated = $request->validate([
            'violation_type' => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
        ]);

        $v = ConsultingOfficeViolation::create([
            'office_id'   => $office->id,
            'reported_by' => $user->id,
            ...$validated,
        ]);

        return response()->json(['message' => 'تم تسجيل المخالفة.', 'data' => $v], 201);
    }
}
