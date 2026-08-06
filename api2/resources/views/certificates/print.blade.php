<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة شهادة - {{ $certificate->certificate_code ?? $certificate->certificate_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('certificates.partials.styles')
</head>
<body>
    <div class="print-toolbar">
        <button onclick="window.print()">طباعة الشهادة</button>
    </div>

    <div class="certificate-wrap">
        @include('certificates.partials.document', [
            'qrImageSrc' => $qrImageSrc ?? null,
            'signatureImages' => $signatureImages ?? [],
        ])
    </div>
</body>
</html>
