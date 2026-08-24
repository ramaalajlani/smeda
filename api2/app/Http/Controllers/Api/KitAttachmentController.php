<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainingKit;
use App\Models\TrainingKitAttachment;
use App\Services\Training\TrainingKitFileService;
use App\Support\TrainingDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KitAttachmentController extends Controller
{
    public function __construct(
        private readonly TrainingKitFileService $fileService,
    ) {
    }

    public function index(int $kitId, Request $request): JsonResponse
    {
        $kit = $this->findScopedKit($kitId, $request);
        $this->authorize('view', $kit);

        $attachments = $kit->attachments()
            ->with('uploader:id,name')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $attachments->map(fn (TrainingKitAttachment $a) => $this->serializeAttachment($a)),
            'meta' => [
                'kit_id' => $kit->id,
                'kit_name' => $kit->name,
                'count' => $attachments->count(),
                'max_allowed' => TrainingKitFileService::MAX_ATTACHMENTS_PER_KIT,
            ],
        ]);
    }

    public function store(int $kitId, Request $request): JsonResponse
    {
        $kit = $this->findScopedKit($kitId, $request);
        $this->authorize('update', $kit);

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'max:25600'],
            'titles' => ['nullable', 'array'],
            'titles.*' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ], [
            'files.required' => 'يجب اختيار ملف واحد على الأقل.',
            'files.max' => 'يمكن رفع 10 ملفات في المرة الواحدة.',
        ]);

        $currentCount = $kit->attachments()->count();
        $incomingCount = count($request->file('files', []));
        if ($currentCount + $incomingCount > TrainingKitFileService::MAX_ATTACHMENTS_PER_KIT) {
            return response()->json([
                'message' => 'تجاوزت الحد الأقصى للمرفقات (' . TrainingKitFileService::MAX_ATTACHMENTS_PER_KIT . ' ملف لكل حقيبة).',
            ], 422);
        }

        $titles = $validated['titles'] ?? [];
        $defaultTitle = $validated['title'] ?? null;
        $nextSort = (int) $kit->attachments()->max('sort_order') + 1;
        $created = [];

        foreach ($request->file('files') as $index => $file) {
            $meta = $this->fileService->storeAttachment($file, $kit);
            $title = $titles[$index] ?? ($index === 0 ? $defaultTitle : null);

            $attachment = TrainingKitAttachment::create([
                'training_kit_id' => $kit->id,
                'uploaded_by' => $request->user()?->id,
                'title' => filled($title) ? $title : null,
                'original_name' => $meta['original_name'],
                'path' => $meta['path'],
                'mime' => $meta['mime'],
                'size' => $meta['size'],
                'sort_order' => $nextSort++,
            ]);

            $attachment->load('uploader:id,name');
            $created[] = $this->serializeAttachment($attachment);
        }

        return response()->json([
            'message' => count($created) === 1 ? 'تم رفع المرفق بنجاح.' : 'تم رفع المرفقات بنجاح.',
            'data' => $created,
        ], 201);
    }

    public function update(int $kitId, int $attachmentId, Request $request): JsonResponse
    {
        $kit = $this->findScopedKit($kitId, $request);
        $this->authorize('update', $kit);

        $attachment = $this->findAttachment($kit, $attachmentId);
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $attachment->update(array_filter($validated, fn ($v) => $v !== null));

        $attachment->load('uploader:id,name');

        return response()->json([
            'message' => 'تم تحديث المرفق.',
            'data' => $this->serializeAttachment($attachment),
        ]);
    }

    public function destroy(int $kitId, int $attachmentId, Request $request): JsonResponse
    {
        $kit = $this->findScopedKit($kitId, $request);
        $this->authorize('update', $kit);

        $attachment = $this->findAttachment($kit, $attachmentId);
        $this->fileService->deleteAttachment($attachment);
        $attachment->delete();

        return response()->json(['message' => 'تم حذف المرفق.']);
    }

    public function download(int $kitId, int $attachmentId, Request $request): StreamedResponse|JsonResponse
    {
        $kit = $this->findScopedKit($kitId, $request);
        $this->authorize('view', $kit);

        $attachment = $this->findAttachment($kit, $attachmentId);

        if (!Storage::disk($this->fileService->attachmentDisk())->exists($attachment->path)) {
            return response()->json(['message' => 'الملف غير موجود.'], 404);
        }

        return Storage::disk($this->fileService->attachmentDisk())->download(
            $attachment->path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    private function findScopedKit(int $id, Request $request): TrainingKit
    {
        return TrainingDataScope::scopeTrainingKits(TrainingKit::query(), $request->user())
            ->findOrFail($id);
    }

    private function findAttachment(TrainingKit $kit, int $attachmentId): TrainingKitAttachment
    {
        return TrainingKitAttachment::where('training_kit_id', $kit->id)->findOrFail($attachmentId);
    }

    /** @return array<string, mixed> */
    private function serializeAttachment(TrainingKitAttachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'title' => $attachment->title,
            'display_name' => $attachment->displayName(),
            'original_name' => $attachment->original_name,
            'mime' => $attachment->mime,
            'size' => $attachment->size,
            'sort_order' => $attachment->sort_order,
            'uploader' => $attachment->relationLoaded('uploader') && $attachment->uploader
                ? ['id' => $attachment->uploader->id, 'name' => $attachment->uploader->name]
                : null,
            'created_at' => optional($attachment->created_at)?->format('Y-m-d H:i:s'),
        ];
    }
}
