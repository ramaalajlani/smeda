<?php

namespace App\Http\Controllers;

use App\Models\TrainingCenter;
use App\Support\PrintSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Throwable;

class TrainingCenterPrintController extends Controller
{
    /**
     * عرض شهادة المركز HTML
     */
    public function show(int $id): View
    {
        $center = $this->loadCenter($id);

        return view('training-centers.certificate', $this->viewData($center));
    }

    /**
     * عرض شهادة المركز PDF
     */
    public function pdf(int $id): Response
    {
        $center = $this->loadCenter($id);

        abort_unless(
            $this->isEligibleCenter($center),
            403,
            'المركز التدريبي غير معتمد أو غير مؤهل لطباعة الشهادة.'
        );

        $pdf = Pdf::loadView('training-centers.certificate', $this->viewData($center))
            ->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="training-center-certificate-' . ($center->code ?: $center->id) . '.pdf"',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(TrainingCenter $center): array
    {
        return [
            'center' => $center,
            'isEligible' => $this->isEligibleCenter($center),
            'accreditationStart' => PrintSupport::formatDate($center->accreditation_start_date),
            'accreditationEnd' => PrintSupport::formatDate($center->accreditation_end_date),
        ];
    }

    private function loadCenter(int $id): TrainingCenter
    {
        try {
            return TrainingCenter::query()
                ->with([
                    'platforms:id,training_center_id,platform_name,platform_url,status,approved_at,expires_at,notes',
                ])
                ->withCount([
                    'trainers',
                    'courses',
                    'certificates',
                ])
                ->findOrFail($id);
        } catch (Throwable $e) {
            report($e);

            abort(404, 'المركز التدريبي غير موجود.');
        }
    }

    private function isEligibleCenter(TrainingCenter $center): bool
    {
        return $center->accreditation_status === 'approved'
            && (bool) $center->is_active
            && (bool) $center->is_accreditation_valid;
    }
}
