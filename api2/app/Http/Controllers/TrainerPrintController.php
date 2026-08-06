<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class TrainerPrintController extends Controller
{
    /**
     * عرض بطاقة المدرب HTML
     */
    public function show(int $id): View
    {
        $trainer = $this->getTrainer($id);

        return view('trainers.card', [
            'trainer' => $trainer,
            'isEligible' => $this->isEligibleTrainer($trainer),
        ]);
    }

    /**
     * عرض بطاقة المدرب PDF
     */
    public function pdf(int $id): Response
    {
        $trainer = $this->getTrainer($id);

        $pdf = Pdf::loadView('trainers.card', [
            'trainer' => $trainer,
            'isEligible' => $this->isEligibleTrainer($trainer),
        ])->setOptions([
            'isRemoteEnabled' => false, // Local assets only — prevents SSRF via remote URLs in PDF
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="trainer-card-' . $trainer->trainer_code . '.pdf"',
        ]);
    }

    /**
     * جلب بيانات المدرب
     */
    private function getTrainer(int $id): Trainer
    {
        $trainer = Trainer::query()
            ->with([
                'trainingCenter:id,name,code,city,classification,accreditation_status',
                'kits:id,name,code,sector,category,level,hours,status',
            ])
            ->findOrFail($id);

        abort_unless(
            $this->isEligibleTrainer($trainer),
            403,
            'المدرب غير معتمد أو غير مؤهل لطباعة البطاقة.'
        );

        return $trainer;
    }

    /**
     * هل المدرب مؤهل فعليًا للتدريب؟
     */
    private function isEligibleTrainer(Trainer $trainer): bool
    {
        if (method_exists($trainer, 'isEligibleTrainer')) {
            return (bool) $trainer->isEligibleTrainer();
        }

        $isTotValid = property_exists($trainer, 'is_tot_valid')
            ? (bool) $trainer->is_tot_valid
            : (bool) $trainer->has_tot;

        return
            (bool) $trainer->has_tot &&
            $isTotValid &&
            (bool) $trainer->can_train &&
            $trainer->status === 'active';
    }
}