<?php

namespace App\Http\Controllers;

use App\Models\Trainee;
use App\Support\SignedPrintUrl;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class TraineePrintController extends Controller
{
    /**
     * عرض بطاقة المتدرب HTML
     */
    public function show(int $id): View
    {
        $trainee = $this->getTrainee($id);
        $latestCertificate = $this->getLatestCertificate($trainee);

        return view('trainees.card', [
            'trainee' => $trainee,
            'latestCertificate' => $latestCertificate,
            'certificatePrintUrl' => $latestCertificate
                ? SignedPrintUrl::certificatePrint($latestCertificate->id)
                : null,
            'certificatePdfUrl' => $latestCertificate
                ? SignedPrintUrl::certificatePdf($latestCertificate->id)
                : null,
        ]);
    }

    /**
     * عرض بطاقة المتدرب PDF
     */
    public function pdf(int $id): Response
    {
        $trainee = $this->getTrainee($id);
        $latestCertificate = $this->getLatestCertificate($trainee);

        $pdf = Pdf::loadView('trainees.card', [
            'trainee' => $trainee,
            'latestCertificate' => $latestCertificate,
            'certificatePrintUrl' => $latestCertificate
                ? SignedPrintUrl::certificatePrint($latestCertificate->id)
                : null,
            'certificatePdfUrl' => $latestCertificate
                ? SignedPrintUrl::certificatePdf($latestCertificate->id)
                : null,
        ])->setOptions([
            'isRemoteEnabled' => false, // Local assets only — prevents SSRF via remote URLs in PDF
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="trainee-card-' . $trainee->trainee_code . '.pdf"',
        ]);
    }

    /**
     * جلب المتدرب
     */
    private function getTrainee(int $id): Trainee
    {
        $trainee = Trainee::query()
            ->with([
                'courses:id,course_code,title,delivery_mode,start_date,end_date,status',
                'certificates:id,trainee_id,certificate_number,certificate_type,result,score,hours_awarded,status,issue_date,is_verified',
            ])
            ->withCount([
                'courses',
                'certificates',
            ])
            ->findOrFail($id);

        abort_unless(
            $this->hasApprovedCertificate($trainee),
            403,
            'لا يمكن طباعة بطاقة متدرب بدون شهادة معتمدة.'
        );

        return $trainee;
    }

    private function hasApprovedCertificate(Trainee $trainee): bool
    {
        return $trainee->certificates()
            ->where('status', 'approved')
            ->where('is_verified', true)
            ->exists();
    }

    /**
     * آخر شهادة للمتدرب
     */
    private function getLatestCertificate(Trainee $trainee)
    {
        return $trainee->certificates
            ->where('status', 'approved')
            ->where('is_verified', true)
            ->sortByDesc('id')
            ->first();
    }
}