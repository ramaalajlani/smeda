<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\Training\CertificateService;
use App\Support\CertificatePublicPayload;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CertificatePrintController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    public function show(int $id): View
    {
        $certificate = $this->getPrintableCertificateById($id);

        return $this->renderPrintView($certificate);
    }

    public function pdf(int $id): Response
    {
        $certificate = $this->getPrintableCertificateById($id);

        return $this->renderPdfResponse($certificate);
    }

    public function showByCode(string $certificateCode): View
    {
        $certificate = $this->getPrintableCertificateByCode($certificateCode, publicStrict: true);

        return $this->renderPrintView($certificate);
    }

    public function pdfByCode(string $certificateCode): Response
    {
        $certificate = $this->getPrintableCertificateByCode($certificateCode, publicStrict: true);

        return $this->renderPdfResponse($certificate);
    }

    public function publicView(string $certificateCode): View
    {
        $certificate = $this->loadCertificateByCode($certificateCode, publicPrint: true);

        abort_unless($certificate, 404, 'الشهادة غير موجودة.');

        $isApproved = $certificate->status === 'approved' && (bool) $certificate->is_verified;

        return view('certificates.public-view', [
            'certificate' => $certificate,
            'publicData' => CertificatePublicPayload::forVerifyPage($certificate, $isApproved),
            'isApproved' => $isApproved,
            'qrImageSrc' => $isApproved ? $this->resolveQrImageSrc($certificate) : null,
        ]);
    }

    public function publicQrImage(string $certificateCode): Response
    {
        $certificate = $this->loadCertificateByCode($certificateCode, publicPrint: true);
        abort_unless($certificate, 404, 'الشهادة غير موجودة.');

        $this->ensurePrintable($certificate);
        $this->ensureQrCodeGenerated($certificate);
        $certificate->refresh();

        $fullPath = $this->resolveQrAbsolutePath($certificate->qr_code_path);
        abort_unless($fullPath && is_file($fullPath), 404, 'رمز QR غير متاح.');

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        $content = file_get_contents($fullPath);
        abort_if($content === false || $content === '', 404, 'رمز QR غير متاح.');

        return response($content, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function renderPrintView(Certificate $certificate): View
    {
        return view('certificates.print', [
            'certificate' => $certificate,
            'qrImageSrc' => $this->resolveQrImageSrc($certificate),
            'signatureImages' => $this->resolveApprovalSignatureImages($certificate),
        ]);
    }

    private function renderPdfResponse(Certificate $certificate): Response
    {
        $pdf = Pdf::loadView('certificates.print', [
            'certificate' => $certificate,
            'qrImageSrc' => $this->resolveQrImageSrc($certificate),
            'signatureImages' => $this->resolveApprovalSignatureImages($certificate),
        ])->setOptions([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
        ]);

        $fileName = $certificate->certificate_code ?: $certificate->certificate_number;

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="certificate-' . $fileName . '.pdf"',
        ]);
    }

    private function getPrintableCertificateById(int $id): Certificate
    {
        $certificate = $this->loadCertificateRelations(
            Certificate::query()->findOrFail($id)
        );

        $this->ensurePrintable($certificate, publicStrict: false);

        return $certificate;
    }

    private function getPrintableCertificateByCode(string $certificateCode, bool $publicStrict = true): Certificate
    {
        $certificate = $this->loadCertificateByCode($certificateCode, publicPrint: true);

        abort_unless($certificate, 404, 'الشهادة غير موجودة.');

        $this->ensurePrintable($certificate, publicStrict: $publicStrict);

        return $certificate;
    }

    private function loadCertificateByCode(string $certificateCode, bool $publicPrint = false): ?Certificate
    {
        $certificate = Certificate::query()
            ->where('certificate_code', $certificateCode)
            ->first();

        return $certificate ? $this->loadCertificateRelations($certificate, $publicPrint) : null;
    }

    private function loadCertificateRelations(Certificate $certificate, bool $publicPrint = false): Certificate
    {
        $traineeFields = $publicPrint
            ? 'id,name,trainee_code'
            : 'id,name,trainee_code,national_id,status';

        return $certificate->load([
            "trainee:{$traineeFields}",
            'trainingCenter:id,name,code,city,address,phone,email',
            'trainer:id,name,trainer_code,phone,email',
            'trainingKit:id,name,code,level,hours',
            'trainingProgram:id,name,code,description,status',
            'trainingCourse:id,course_code,title,delivery_mode,start_date,end_date,planned_hours,actual_hours,status',
            'approvals:id,certificate_id,approved_by,approval_step,decision,decision_at,notes',
            'approvals.electronicSignature:id,signable_type,signable_id,role_key,signer_name,signer_title,verification_code,signed_at,signature_image_path,signature_image_hash',
            'approvals.approver:id,name',
        ]);
    }

    private function ensurePrintable(Certificate $certificate, bool $publicStrict = true): void
    {
        if ($publicStrict) {
            abort_unless(
                $certificate->status === 'approved' && (bool) $certificate->is_verified,
                403,
                'الشهادة غير معتمدة للطباعة.'
            );

            return;
        }

        // روابط الطباعة الموقّعة (من لوحة المركز/الإدارة): متاحة بعد الإصدار وقبل الاعتماد النهائي.
        abort_unless(
            !in_array((string) $certificate->status, ['rejected', 'revoked', 'cancelled'], true),
            403,
            'الشهادة غير متاحة للطباعة.'
        );
    }

    private function resolveQrImageSrc(Certificate $certificate): ?string
    {
        $this->ensureQrCodeGenerated($certificate);
        $certificate->refresh();

        if (!$certificate->qr_code_path) {
            return null;
        }

        $fullPath = $this->resolveQrAbsolutePath($certificate->qr_code_path);
        if (!$fullPath || !is_file($fullPath)) {
            return null;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $content = file_get_contents($fullPath);

        if ($content === false || $content === '') {
            return null;
        }

        $mime = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }

    private function resolveQrAbsolutePath(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }

        $publicPath = public_path(ltrim($relativePath, '/'));
        if (is_file($publicPath)) {
            return $publicPath;
        }

        $normalized = ltrim($relativePath, '/');
        if (str_starts_with($normalized, 'storage/')) {
            $storageRelative = substr($normalized, strlen('storage/'));
            $storagePath = storage_path('app/public/' . ltrim((string) $storageRelative, '/'));
            if (is_file($storagePath)) {
                return $storagePath;
            }
        }

        return null;
    }

    private function ensureQrCodeGenerated(Certificate $certificate): void
    {
        if (!$certificate->certificate_code) {
            return;
        }

        $missingQrPath = !$certificate->qr_code_path;
        $missingQrFile = !$missingQrPath && !$this->resolveQrAbsolutePath($certificate->qr_code_path);

        if ($missingQrPath || $missingQrFile) {
            $this->certificateService->generateQrForCertificate($certificate);
        }
    }

    /** @return array<string, string> */
    private function resolveApprovalSignatureImages(Certificate $certificate): array
    {
        $images = [];

        foreach ($certificate->approvals ?? [] as $approval) {
            $sig = $approval->electronicSignature;
            if (!$sig?->signature_image_path) {
                continue;
            }

            if (!Storage::disk('local')->exists($sig->signature_image_path)) {
                continue;
            }

            $content = Storage::disk('local')->get($sig->signature_image_path);
            if ($content === false || $content === '') {
                continue;
            }

            $extension = strtolower(pathinfo($sig->signature_image_path, PATHINFO_EXTENSION));
            $mime = match ($extension) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };

            $images[$approval->approval_step] = 'data:' . $mime . ';base64,' . base64_encode($content);
        }

        return $images;
    }
}
