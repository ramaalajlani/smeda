<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KitMaterial;
use App\Models\TrainingKit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KitMaterialController extends Controller
{
    public function index(int $kitId, Request $request): JsonResponse
    {
        $kit = TrainingKit::findOrFail($kitId);
        $materials = KitMaterial::where('training_kit_id', $kit->id)
            ->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'title', 'hours', 'sort_order', 'objectives', 'evaluation_method', 'notes']);

        return response()->json([
            'data' => $materials,
            'meta' => ['kit_id' => $kit->id, 'kit_name' => $kit->name, 'count' => $materials->count()],
        ]);
    }

    public function store(int $kitId, Request $request): JsonResponse
    {
        $kit = TrainingKit::findOrFail($kitId);
        $data = $this->validatePayload($request);
        $data['training_kit_id'] = $kit->id;
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = (int) KitMaterial::where('training_kit_id', $kit->id)->max('sort_order') + 1;
        }
        $material = KitMaterial::create($data);

        return response()->json(['message' => 'تمت إضافة المادة بنجاح.', 'data' => $material], 201);
    }

    public function update(int $kitId, int $materialId, Request $request): JsonResponse
    {
        $material = KitMaterial::where('training_kit_id', $kitId)->findOrFail($materialId);
        $material->update($this->validatePayload($request));

        return response()->json(['message' => 'تم تحديث المادة بنجاح.', 'data' => $material]);
    }

    public function destroy(int $kitId, int $materialId): JsonResponse
    {
        $material = KitMaterial::where('training_kit_id', $kitId)->findOrFail($materialId);
        $material->delete();

        return response()->json(['message' => 'تم حذف المادة.']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'hours'             => ['nullable', 'integer', 'min:0', 'max:10000'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'objectives'        => ['nullable', 'string'],
            'evaluation_method' => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string'],
        ], ['title.required' => 'عنوان المادة مطلوب.']);
    }
}
