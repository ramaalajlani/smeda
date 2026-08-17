<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingCategoryResource;
use App\Models\TrainingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrainingCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TrainingCategory::class);

        $rootsOnly = $request->boolean('roots_only', false);
        $withChildren = $request->boolean('with_children', true);

        $query = TrainingCategory::query()
            ->when($request->boolean('active_only', true), fn ($q) => $q->active())
            ->when($rootsOnly, fn ($q) => $q->roots())
            ->when($request->filled('parent_id'), fn ($q) => $q->where('parent_id', (int) $request->integer('parent_id')))
            ->search($request->input('search'))
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($withChildren && $rootsOnly) {
            $query->with(['activeChildren' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);
        }

        $rows = $query->get();

        return response()->json([
            'data' => TrainingCategoryResource::collection($rows),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', TrainingCategory::class);

        $validated = $this->validatePayload($request);
        $validated['slug'] = $this->resolveSlug($validated['slug'] ?? null, $validated['name_ar']);

        $category = TrainingCategory::create($validated);

        return response()->json([
            'message' => 'تم إضافة التصنيف بنجاح.',
            'data' => new TrainingCategoryResource($category->load('parent')),
        ], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $category = TrainingCategory::findOrFail($id);
        $this->authorize('update', $category);

        $validated = $this->validatePayload($request, $category);

        if (array_key_exists('slug', $validated) && $validated['slug']) {
            $validated['slug'] = $this->resolveSlug($validated['slug'], $validated['name_ar'] ?? $category->name_ar, $category->id);
        }

        if (isset($validated['parent_id']) && (int) $validated['parent_id'] === $category->id) {
            return response()->json(['message' => 'لا يمكن أن يكون التصنيف أباً لنفسه.'], 422);
        }

        $category->update($validated);

        return response()->json([
            'message' => 'تم تحديث التصنيف بنجاح.',
            'data' => new TrainingCategoryResource($category->fresh()->load(['parent', 'children'])),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = TrainingCategory::findOrFail($id);
        $this->authorize('delete', $category);

        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'لا يمكن حذف تصنيف يحتوي على تصنيفات فرعية. عطّله أو انقل الفروع أولاً.',
            ], 422);
        }

        if ($category->kitsAsCategory()->exists() || $category->kitsAsSubcategory()->exists()) {
            return response()->json([
                'message' => 'لا يمكن حذف تصنيف مرتبط بحقائب تدريبية. عطّله بدلاً من ذلك.',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'تم حذف التصنيف.']);
    }

    /** @return array<string, mixed> */
    private function validatePayload(Request $request, ?TrainingCategory $category = null): array
    {
        $isUpdate = $category !== null;

        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:training_categories,id'],
            'name_ar' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('training_categories', 'slug')->ignore($category?->id)->whereNull('deleted_at')],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name_ar.required' => 'اسم التصنيف بالعربي مطلوب.',
        ]);
    }

    private function resolveSlug(?string $slug, string $nameAr, ?int $ignoreId = null): string
    {
        $base = $slug !== null && $slug !== '' ? Str::slug($slug, '-', 'ar') : Str::slug($nameAr, '-', 'ar');
        if ($base === '') {
            $base = 'category-' . substr(md5($nameAr . microtime()), 0, 8);
        }

        $candidate = $base;
        $i = 1;
        while (TrainingCategory::withTrashed()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base . '-' . $i;
            $i++;
        }

        return $candidate;
    }
}
