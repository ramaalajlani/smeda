<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificatePrintController;
use App\Http\Controllers\TrainerPrintController;
use App\Http\Controllers\TrainingCenterPrintController;
use App\Http\Controllers\TraineePrintController;
use App\Http\Controllers\Api\CertificateController;

/*
|--------------------------------------------------------------------------
| Print routes — require signed URLs (legacy by numeric id)
| Must be registered before certificate_code routes so numeric ids stay signed.
|--------------------------------------------------------------------------
*/
Route::middleware(['signed', 'throttle:print-routes'])->group(function () {
    Route::get('/certificates/{id}/print', [CertificatePrintController::class, 'show'])
        ->whereNumber('id')
        ->name('certificates.print');

    Route::get('/certificates/{id}/pdf', [CertificatePrintController::class, 'pdf'])
        ->whereNumber('id')
        ->name('certificates.pdf');

    Route::get('/trainers/{id}/card', [TrainerPrintController::class, 'show'])
        ->whereNumber('id')
        ->name('trainers.card');

    Route::get('/trainers/{id}/card/pdf', [TrainerPrintController::class, 'pdf'])
        ->whereNumber('id')
        ->name('trainers.card.pdf');

    Route::get('/training-centers/{id}/certificate', [TrainingCenterPrintController::class, 'show'])
        ->whereNumber('id')
        ->name('training-centers.certificate');

    Route::get('/training-centers/{id}/certificate/pdf', [TrainingCenterPrintController::class, 'pdf'])
        ->whereNumber('id')
        ->name('training-centers.certificate.pdf');

    Route::get('/trainees/{id}/card', [TraineePrintController::class, 'show'])
        ->whereNumber('id')
        ->name('trainees.card');

    Route::get('/trainees/{id}/card/pdf', [TraineePrintController::class, 'pdf'])
        ->whereNumber('id')
        ->name('trainees.card.pdf');
});

/*
|--------------------------------------------------------------------------
| Public certificate view / verify (QR) and print by composite certificate_code
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:print-routes')->group(function () {
    Route::get('/verify-certificate/{certificate_code}', [CertificatePrintController::class, 'publicView'])
        ->where('certificate_code', '[A-Za-z0-9\-]+')
        ->name('certificates.verify-code');

    Route::get('/certificates/verify', [CertificateController::class, 'verifyPage'])
        ->name('certificates.verify');
});

Route::middleware('throttle:certificate-print-by-code')->group(function () {
    Route::get('/certificates/{certificate_code}/print', [CertificatePrintController::class, 'showByCode'])
        ->where('certificate_code', '[A-Za-z0-9\-]+')
        ->name('certificates.print-by-code');

    Route::get('/certificates/{certificate_code}/pdf', [CertificatePrintController::class, 'pdfByCode'])
        ->where('certificate_code', '[A-Za-z0-9\-]+')
        ->name('certificates.pdf-by-code');

    Route::get('/certificates/{certificate_code}/qr', [CertificatePrintController::class, 'publicQrImage'])
        ->where('certificate_code', '[A-Za-z0-9\-]+')
        ->name('certificates.qr-by-code');
});
