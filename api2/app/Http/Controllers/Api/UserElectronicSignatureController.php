<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Signing\StoreUserElectronicSignatureRequest;
use App\Models\DocumentElectronicSignature;
use App\Services\Signing\UserElectronicSignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserElectronicSignatureController extends Controller
{
    public function __construct(private UserElectronicSignatureService $signatures) {}

    /** GET /my-electronic-signature */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($this->signatures->canManageOwnSignature($user), 403);

        $active = $this->signatures->activeForUser($user);

        return response()->json([
            'data' => $active ? $this->transform($active) : null,
            'can_upload' => true,
        ]);
    }

    /** POST /my-electronic-signature */
    public function store(StoreUserElectronicSignatureRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($this->signatures->canManageOwnSignature($user), 403);

        $record = $this->signatures->upload($user, $request->file('signature'), $user);

        return response()->json([
            'message' => 'تم حفظ التوقيع الإلكتروني بنجاح.',
            'data' => $this->transform($record),
        ], 201);
    }

    /** DELETE /my-electronic-signature */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($this->signatures->canManageOwnSignature($user), 403);

        $this->signatures->deactivate($user);

        return response()->json([
            'message' => 'تم تعطيل التوقيع الإلكتروني.',
        ]);
    }

    /** GET /my-electronic-signature/image */
    public function myImage(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($this->signatures->canManageOwnSignature($user), 403);

        $active = $this->signatures->activeForUser($user);
        abort_unless($active && Storage::disk('local')->exists($active->signature_path), 404);

        return Storage::disk('local')->response(
            $active->signature_path,
            $active->original_name,
            ['Content-Type' => $active->mime_type]
        );
    }

    /** GET /electronic-signatures/{id}/snapshot-image */
    public function snapshotImage(Request $request, int $id): StreamedResponse
    {
        $record = DocumentElectronicSignature::query()->findOrFail($id);
        abort_unless($record->signature_image_path, 404);
        abort_unless(Storage::disk('local')->exists($record->signature_image_path), 404);

        $this->authorizeSnapshotView($request->user(), $record);

        $mime = match (strtolower(pathinfo($record->signature_image_path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return Storage::disk('local')->response(
            $record->signature_image_path,
            'signature-snapshot.' . pathinfo($record->signature_image_path, PATHINFO_EXTENSION),
            ['Content-Type' => $mime]
        );
    }

    /** @return array<string, mixed> */
    private function transform($record): array
    {
        return [
            'id' => $record->id,
            'user_id' => $record->user_id,
            'original_name' => $record->original_name,
            'mime_type' => $record->mime_type,
            'file_size' => $record->file_size,
            'file_hash' => $record->file_hash,
            'is_active' => (bool) $record->is_active,
            'created_at' => optional($record->created_at)->toIso8601String(),
            'image_url' => url('/api/my-electronic-signature/image'),
        ];
    }

    private function authorizeSnapshotView($user, DocumentElectronicSignature $record): void
    {
        if (!$user) {
            abort(403);
        }

        if ((int) $record->signed_by_user_id === (int) $user->id) {
            return;
        }

        if ($user->hasRole(['admin', 'super_admin', 'general_director'])) {
            return;
        }

        if ($user->hasAnyPermission([
            'approve_center_certificates',
            'approve_training_certificates',
            'approve_deputy_certificates',
            'approve_general_director_certificates',
            'print_certificates',
        ])) {
            return;
        }

        abort(403);
    }
}
