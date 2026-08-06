<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التحقق من الشهادة - {{ $publicData['certificate_code'] ?? '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('certificates.partials.styles')
</head>
<body>
    <div class="print-toolbar">
        @if($isApproved && !empty($publicData['print_url']))
            <a class="btn-print" href="{{ $publicData['print_url'] }}" target="_blank">طباعة الشهادة</a>
        @endif
        <button onclick="window.print()">طباعة الصفحة</button>
    </div>

    <div class="verify-banner {{ $isApproved ? '' : 'pending' }}">
        <h1>التحقق من الشهادة التدريبية</h1>
        @if($isApproved)
            <p>تم التحقق من الشهادة بنجاح. البيانات أدناه مطابقة للسجل الرسمي.</p>
        @else
            <p>الشهادة موجودة في النظام لكنها غير معتمدة نهائياً للعرض العام حالياً.</p>
        @endif

        <div class="verify-grid">
            <div class="verify-item">
                <small>حالة الشهادة</small>
                <strong>{{ $publicData['status'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>نوع الشهادة</small>
                <strong>{{ $publicData['certificate_type_label'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>اسم المتدرب</small>
                <strong>{{ $publicData['trainee_name'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>اسم التدريب</small>
                <strong>{{ $publicData['course_title'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>كود الدورة</small>
                <strong>{{ $publicData['course_code'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>عدد ساعات التدريب</small>
                <strong>{{ $publicData['training_hours'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>رمز المركز</small>
                <strong>{{ $publicData['center_code'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>رمز المدرب</small>
                <strong>{{ $publicData['trainer_code'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>رمز الحقيبة</small>
                <strong>{{ $publicData['kit_code'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>رمز المتدرب</small>
                <strong>{{ $publicData['trainee_code'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>رقم الشهادة</small>
                <strong>{{ $publicData['certificate_code'] ?? $publicData['certificate_number'] ?? '—' }}</strong>
            </div>
            <div class="verify-item">
                <small>تاريخ الإصدار</small>
                <strong>{{ $publicData['issued_at'] ?? '—' }}</strong>
            </div>
        </div>
    </div>

    @if($isApproved)
        <div class="certificate-wrap">
            @include('certificates.partials.document', ['qrImageSrc' => $qrImageSrc ?? null])
        </div>
    @endif
</body>
</html>
