<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundingApplication;
use App\Models\FundingDocument;
use App\Services\AuditLogService;
use App\Support\FinanceDataScope;
use App\Support\SecureFileStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FundingDocumentController extends Controller
{
    /** @var list<string> */
    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function __construct(private AuditLogService $auditLog) {}

    public function store(Request $request, int $applicationId): JsonResponse
    {
        $application = FundingApplication::query()->findOrFail($applicationId);
        $this->authorize('update', $application);

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $validated['file'];
        $path = SecureFileStorage::storeUploadedFile(
            $file,
            'funding-documents/' . $application->id,
            'local',
            self::ALLOWED_MIMES
        );

        $document = FundingDocument::query()->create([
            'funding_application_id' => $application->id,
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'تم رفع المرفق.', 'data' => $document], 201);
    }

    public function download(Request $request, int $applicationId, int $documentId): StreamedResponse
    {
        $application = FundingApplication::query()->findOrFail($applicationId);
        $this->authorize('view', $application);

        if (!FinanceDataScope::canAccessApplication($request->user(), $application)) {
            abort(403);
        }

        $document = FundingDocument::query()
            ->where('funding_application_id', $application->id)
            ->findOrFail($documentId);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }
}
