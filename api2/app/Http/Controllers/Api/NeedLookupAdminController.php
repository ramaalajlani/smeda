<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Need;
use App\Models\NeedLookup;
use App\Models\NeedSector;
use App\Support\NeedTaxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * إدارة القوائم المرجعية لوحدة الاحتياجات (needs.manage_lookups):
 * تفعيل / تعطيل / ترتيب / إضافة قيم — بدون حذف نهائي حفاظاً على
 * القيم المستخدمة في احتياجات قديمة.
 */
class NeedLookupAdminController extends Controller
{
    private const MANAGED_TYPES = [
        NeedTaxonomy::TYPE_CATEGORY,
        NeedTaxonomy::TYPE_FACILITY,
        NeedTaxonomy::TYPE_FACILITY_SUBTYPE,
        NeedTaxonomy::TYPE_TARGETING,
    ];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manageLookups', Need::class);

        $lookups = NeedLookup::query()
            ->whereIn('lookup_type', self::MANAGED_TYPES)
            ->orderBy('lookup_type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('lookup_type');

        $sectors = NeedSector::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => [
                'lookups' => $lookups,
                'sectors' => $sectors,
            ],
        ]);
    }

    public function storeLookup(Request $request): JsonResponse
    {
        $this->authorize('manageLookups', Need::class);

        $validated = $request->validate([
            'lookup_type' => ['required', Rule::in(self::MANAGED_TYPES)],
            'value' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'value.regex' => 'القيمة يجب أن تكون بأحرف لاتينية صغيرة وأرقام وشرطة سفلية فقط.',
        ]);

        $lookup = NeedLookup::query()->firstOrCreate(
            ['lookup_type' => $validated['lookup_type'], 'value' => $validated['value']],
            [
                'label' => $validated['label'],
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => true,
            ],
        );

        NeedTaxonomy::flushCache();

        return response()->json([
            'message' => $lookup->wasRecentlyCreated ? 'تمت إضافة القيمة.' : 'القيمة موجودة مسبقاً.',
            'data' => $lookup,
        ], $lookup->wasRecentlyCreated ? 201 : 200);
    }

    public function updateLookup(Request $request, int $id): JsonResponse
    {
        $this->authorize('manageLookups', Need::class);

        $lookup = NeedLookup::query()
            ->whereIn('lookup_type', self::MANAGED_TYPES)
            ->findOrFail($id);

        $validated = $request->validate([
            'label' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $lookup->update($validated);

        NeedTaxonomy::flushCache();

        return response()->json(['message' => 'تم تحديث القيمة.', 'data' => $lookup->fresh()]);
    }

    public function storeSector(Request $request): JsonResponse
    {
        $this->authorize('manageLookups', Need::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'name_ar' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'code.regex' => 'الكود يجب أن يكون بأحرف لاتينية صغيرة وأرقام وشرطة سفلية فقط.',
        ]);

        $sector = NeedSector::query()->firstOrCreate(
            ['code' => $validated['code']],
            [
                'name_ar' => $validated['name_ar'],
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => true,
            ],
        );

        NeedTaxonomy::flushCache();

        return response()->json([
            'message' => $sector->wasRecentlyCreated ? 'تمت إضافة القطاع.' : 'القطاع موجود مسبقاً.',
            'data' => $sector,
        ], $sector->wasRecentlyCreated ? 201 : 200);
    }

    public function updateSector(Request $request, int $id): JsonResponse
    {
        $this->authorize('manageLookups', Need::class);

        $sector = NeedSector::query()->findOrFail($id);

        $validated = $request->validate([
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $sector->update($validated);

        NeedTaxonomy::flushCache();

        return response()->json(['message' => 'تم تحديث القطاع.', 'data' => $sector->fresh()]);
    }
}
