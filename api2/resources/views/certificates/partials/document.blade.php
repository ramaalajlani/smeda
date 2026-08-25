@php
    use App\Support\CertificateType;

    $isAttendance = CertificateType::isAttendance($certificate->certificate_type);
    $trainingTitle = $certificate->trainingCourse?->title ?? $certificate->trainingKit?->name ?? '—';
    $courseCode = $certificate->course_code ?? $certificate->trainingCourse?->course_code ?? '—';
    $trainingHours = $certificate->resolvedTrainingHours();
    $typeLabel = CertificateType::label($certificate->certificate_type);
    $issueDate = optional($certificate->issued_at ?? $certificate->issue_date)->format('Y-m-d');
    $certificateCode = $certificate->certificate_code ?? $certificate->certificate_number ?? '—';

    $approvalByStep = $certificate->approvals?->keyBy('approval_step') ?? collect();
    $centerApproval = $approvalByStep->get('center_approval');
    $generalApproval = $approvalByStep->get('general_director_approval');
    $signatureImages = $signatureImages ?? [];
    $isSimpleCenterFlow = str_contains((string) ($certificate->notes ?? ''), 'simple_center_flow=1');
@endphp

<div class="certificate">
    <div class="header">
        <div class="gov">الهيئة / الجهة الإدارية المختصة بالتدريب</div>
        <div class="title">{{ $typeLabel }}</div>
    </div>

    <div class="certificate-body">
        @if($isAttendance)
            <p>تشهد الجهة المنظمة بأن المتدرب/ة</p>
        @else
            <p>تشهد الجهة المنظمة بأن المتدرب/ة</p>
        @endif

        <div class="name">{{ $certificate->trainee?->name ?? '—' }}</div>

        @if($isAttendance)
            <p>
                قد حضر/ت التدريب بعنوان
                <span class="highlight">{{ $trainingTitle }}</span>
            </p>
        @else
            <p>
                قد اجتاز/ت بنجاح التدريب بعنوان
                <span class="highlight">{{ $trainingTitle }}</span>
            </p>
        @endif

        <p>كود الدورة: <span class="highlight">{{ $courseCode }}</span></p>

        <p>
            وذلك بعدد ساعات تدريبية قدرها
            <span class="highlight">{{ $trainingHours }}</span>
            ساعة تدريبية.
        </p>

        @if(!$isAttendance && !is_null($certificate->score))
            <p>
                وبعلامة
                <span class="highlight">{{ $certificate->score }}</span>
            </p>
        @endif
    </div>

    <table class="meta-table" role="presentation">
        <tr>
            <td class="meta-card">
                <span class="label">رمز المركز</span>
                <span class="value">{{ $certificate->center_code ?? $certificate->trainingCenter?->code ?? '—' }}</span>
            </td>
            <td class="meta-card">
                <span class="label">رمز المدرب</span>
                <span class="value">{{ $certificate->trainer_code ?? $certificate->trainer?->trainer_code ?? '—' }}</span>
            </td>
            <td class="meta-card">
                <span class="label">رمز الحقيبة</span>
                <span class="value">{{ $certificate->kit_code ?? $certificate->trainingKit?->code ?? '—' }}</span>
            </td>
            <td class="meta-card">
                <span class="label">رمز الدورة</span>
                <span class="value">{{ $courseCode }}</span>
            </td>
        </tr>
        <tr>
            <td class="meta-card">
                <span class="label">رمز المتدرب</span>
                <span class="value">{{ $certificate->trainee_code ?? $certificate->trainee?->trainee_code ?? '—' }}</span>
            </td>
            <td class="meta-card">
                <span class="label">رقم الشهادة</span>
                <span class="value">{{ $certificateCode }}</span>
            </td>
            <td class="meta-card">
                <span class="label">تاريخ الإصدار</span>
                <span class="value">{{ $issueDate }}</span>
            </td>
            <td class="meta-card">
                <span class="label">المركز التدريبي</span>
                <span class="value">{{ $certificate->trainingCenter?->name ?? '—' }}</span>
            </td>
        </tr>
    </table>

    <table class="footer-table" role="presentation">
        <tr>
            <td class="sign-box" width="50%">
                @if($centerApproval?->decision === 'approved' && ($centerApproval->electronicSignature || $centerApproval->approver))
                    @php $cs = $centerApproval->electronicSignature; @endphp
                    @if(!empty($signatureImages['center_approval']))
                        <img src="{{ $signatureImages['center_approval'] }}" alt="توقيع المركز" class="sign-image">
                    @endif
                    <div class="esign-badge">{{ $cs ? 'توقيع إلكتروني' : 'معتمد' }}</div>
                    <div class="sign-name">{{ $cs?->signer_name ?? $centerApproval->approver?->name ?? '—' }}</div>
                    <div class="sign-title-small">{{ $cs?->signer_title ?? 'اعتماد المركز التدريبي' }}</div>
                    <div class="sign-date">{{ optional($cs?->signed_at ?? $centerApproval->decision_at)->format('Y-m-d H:i') }}</div>
                    @if($cs?->verification_code)
                        <div class="esign-code">{{ $cs->verification_code }}</div>
                    @endif
                @else
                    <div class="sign-line"></div>
                @endif
                <div class="sign-title">اعتماد المركز التدريبي</div>
            </td>

            <td class="sign-box" width="50%">
                @if($generalApproval?->decision === 'approved' && $generalApproval->electronicSignature)
                    @php $gs = $generalApproval->electronicSignature; @endphp
                    @if(!empty($signatureImages['general_director_approval']))
                        <img src="{{ $signatureImages['general_director_approval'] }}" alt="توقيع المدير العام" class="sign-image">
                    @endif
                    <div class="esign-badge">توقيع إلكتروني</div>
                    <div class="sign-name">{{ $gs->signer_name }}</div>
                    <div class="sign-title-small">{{ $gs->signer_title }}</div>
                    <div class="sign-date">{{ optional($gs->signed_at)->format('Y-m-d H:i') }}</div>
                    <div class="esign-code">{{ $gs->verification_code }}</div>
                @elseif($generalApproval?->decision === 'approved' && ($generalApproval->approver || $isSimpleCenterFlow))
                    <div class="esign-badge">معتمد</div>
                    <div class="sign-name">{{ $generalApproval->approver?->name ?? $certificate->trainingCenter?->name ?? '—' }}</div>
                    <div class="sign-title-small">اعتماد إداري</div>
                    <div class="sign-date">{{ optional($generalApproval->decision_at)->format('Y-m-d H:i') }}</div>
                @else
                    <div class="sign-line"></div>
                @endif
                <div class="sign-title">المدير العام</div>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:center;padding-top:12px;">
                <div class="qr-box" style="display:inline-block;max-width:180px;">
                    @if(!empty($qrImageSrc))
                        <img src="{{ $qrImageSrc }}" alt="QR Code">
                    @endif
                    <div class="qr-title">مسح للتحقق من الشهادة</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="status-note">
        @if($isSimpleCenterFlow)
            شهادة تدريبية صادرة من المركز — يمكن التحقق منها عبر رمز QR.
        @else
            هذه الشهادة معتمدة وموثقة رقمياً. رمز التوقيع الإلكتروني (ESIG) غير قابل للتزوير ويمكن التحقق منه عبر المنصة.
        @endif
    </div>
</div>
