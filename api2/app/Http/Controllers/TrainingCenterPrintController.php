<?php

namespace App\Http\Controllers;

use App\Models\TrainingCenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class TrainingCenterPrintController extends Controller
{
    /**
     * عرض شهادة المركز HTML
     */
    public function show(int $id): View
    {
        $center = $this->getCenter($id);

        return view('training-centers.certificate', [
            'center' => $center,
            'isEligible' => $this->isEligibleCenter($center),
        ]);
    }

    /**
     * عرض شهادة المركز PDF
     */
    public function pdf(int $id): Response
    {
        $center = $this->getCenter($id);

        $pdf = Pdf::loadView('training-centers.certificate', [
            'center' => $center,
            'isEligible' => $this->isEligibleCenter($center),
        ])->setOptions([
            'isRemoteEnabled' => false, // Local assets only — prevents SSRF via remote URLs in PDF
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="training-center-certificate-' . $center->code . '.pdf"',
        ]);
    }

    /**
     * جلب بيانات المركز
     */
    private function getCenter(int $id): TrainingCenter
    {
        $center = TrainingCenter::query()
            ->with([
                'platforms:id,training_center_id,platform_name,platform_url,status,approved_at,expires_at,notes',
            ])
            ->withCount([
                'trainers',
                'courses',
                'certificates',
            ])
            ->findOrFail($id);

        abort_unless(
            $this->isEligibleCenter($center),
            403,
            'المركز التدريبي غير معتمد أو غير مؤهل لطباعة الشهادة.'
        );

        return $center;
    }

    /**
     * هل المركز مؤهل فعلياً؟
     */
    private function isEligibleCenter(TrainingCenter $center): bool
    {
        $isValid = property_exists($center, 'is_accreditation_valid')
            ? (bool) $center->is_accreditation_valid
            : true;

        return
            $center->accreditation_status === 'approved' &&
            (bool) $center->is_active &&
            $isValid;
    }
}