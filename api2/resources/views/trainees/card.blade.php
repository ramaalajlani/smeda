<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بطاقة متدرب</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            background: #f4f6f9;
            color: #111827;
            direction: rtl;
        }

        * {
            box-sizing: border-box;
        }

        .print-toolbar {
            padding: 16px;
            text-align: center;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .print-toolbar button {
            background: #0f766e;
            color: #fff;
            border: 0;
            padding: 12px 22px;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
            font-family: DejaVu Sans, sans-serif;
        }

        .card-wrap {
            padding: 24px;
        }

        .trainee-card {
            max-width: 920px;
            margin: 0 auto;
            background: #ffffff;
            border: 8px solid #0f766e;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
            position: relative;
            overflow: hidden;
        }

        .trainee-card::before,
        .trainee-card::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(15, 118, 110, .06);
        }

        .trainee-card::before {
            top: -40px;
            right: -40px;
        }

        .trainee-card::after {
            bottom: -50px;
            left: -50px;
        }

        .header,
        .body,
        .status-box,
        .meta-grid,
        .certificate-box,
        .footer-note {
            position: relative;
            z-index: 2;
        }

        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .gov {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .title {
            font-size: 34px;
            font-weight: 800;
            color: #0f766e;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 17px;
            color: #475569;
        }

        .body {
            text-align: center;
            margin-bottom: 24px;
        }

        .trainee-name {
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
        }

        .trainee-code {
            font-size: 18px;
            color: #475569;
            font-weight: 700;
        }

        .status-box {
            text-align: center;
            margin: 24px 0;
            padding: 16px 18px;
            border-radius: 18px;
            font-weight: 800;
            font-size: 16px;
            line-height: 1.9;
            background: rgba(15, 118, 110, .06);
            border: 1px solid rgba(15, 118, 110, .16);
            color: #0f766e;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 20px;
        }

        .meta-card {
            border: 1px solid #dbe4ea;
            border-radius: 16px;
            padding: 14px;
            background: #f8fafc;
            text-align: center;
        }

        .meta-card .label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .meta-card .value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
            line-height: 1.8;
        }

        .certificate-box {
            margin-top: 26px;
            border: 1px solid #dbe4ea;
            border-radius: 18px;
            padding: 18px;
            background: #f8fafc;
        }

        .certificate-title {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 14px;
            color: #0f172a;
            text-align: center;
        }

        .certificate-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .certificate-actions {
            margin-top: 18px;
            text-align: center;
        }

        .certificate-actions a {
            display: inline-block;
            margin: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .btn-print {
            background: #198754;
            color: #fff;
        }

        .btn-pdf {
            background: #0d6efd;
            color: #fff;
        }

        .footer-note {
            margin-top: 28px;
            text-align: center;
            font-size: 14px;
            color: #475569;
            line-height: 1.9;
        }

        @media (max-width: 767px) {
            .meta-grid,
            .certificate-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .print-toolbar {
                display: none;
            }

            .card-wrap {
                padding: 0;
            }

            .trainee-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button onclick="window.print()">طباعة بطاقة المتدرب</button>
    </div>

    <div class="card-wrap">
        <div class="trainee-card">
            <div class="header">
                <div class="gov">الهيئة / الجهة الإدارية المختصة بالتدريب</div>
                <div class="title">بطاقة متدرب</div>
                <div class="subtitle">بطاقة تعريف إدارية للمتدرب داخل النظام</div>
            </div>

            <div class="body">
                <div class="trainee-name">{{ $trainee->name ?? '—' }}</div>
                <div class="trainee-code">رقم المتدرب: {{ $trainee->trainee_code ?? '—' }}</div>
            </div>

            <div class="status-box">
                هذا السجل يخص متدربًا مسجلًا ضمن النظام التدريبي، وتعرض البطاقة بياناته الإدارية وآخر شهادة مرتبطة به إن وجدت.
            </div>

            <div class="meta-grid">
                <div class="meta-card">
                    <div class="label">الرقم الوطني</div>
                    <div class="value">{{ $trainee->national_id ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">رقم الهاتف</div>
                    <div class="value">{{ $trainee->phone ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">البريد الإلكتروني</div>
                    <div class="value">{{ $trainee->email ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">المدينة</div>
                    <div class="value">{{ $trainee->city ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">العنوان</div>
                    <div class="value">{{ $trainee->address ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">تاريخ الميلاد</div>
                    <div class="value">{{ optional($trainee->birth_date)->format('Y-m-d') ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">الجنس</div>
                    <div class="value">{{ $trainee->gender ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">المستوى التعليمي</div>
                    <div class="value">{{ $trainee->education_level ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">الحالة</div>
                    <div class="value">{{ $trainee->status ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">عدد الدورات</div>
                    <div class="value">{{ $trainee->courses_count ?? 0 }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">عدد الشهادات</div>
                    <div class="value">{{ $trainee->certificates_count ?? 0 }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">ملاحظات</div>
                    <div class="value">{{ $trainee->notes ?? '—' }}</div>
                </div>
            </div>

            <div class="certificate-box">
                <div class="certificate-title">آخر شهادة مرتبطة بالمتدرب</div>

                @if($latestCertificate)
                    <div class="certificate-grid">
                        <div class="meta-card">
                            <div class="label">رقم الشهادة</div>
                            <div class="value">{{ $latestCertificate->certificate_number ?? '—' }}</div>
                        </div>

                        <div class="meta-card">
                            <div class="label">الرقم المرجعي</div>
                            <div class="value">{{ $latestCertificate->reference_number ?? '—' }}</div>
                        </div>

                        <div class="meta-card">
                            <div class="label">نوع الشهادة</div>
                            <div class="value">
                                @if(($latestCertificate->certificate_type ?? null) === 'attendance')
                                    شهادة حضور
                                @elseif(($latestCertificate->certificate_type ?? null) === 'pass')
                                    شهادة اجتياز
                                @else
                                    {{ $latestCertificate->certificate_type ?? '—' }}
                                @endif
                            </div>
                        </div>

                        <div class="meta-card">
                            <div class="label">النتيجة</div>
                            <div class="value">
                                @if(($latestCertificate->result ?? null) === 'passed')
                                    ناجح
                                @elseif(($latestCertificate->result ?? null) === 'failed')
                                    راسب
                                @elseif(($latestCertificate->result ?? null) === 'review')
                                    مراجعة
                                @elseif(($latestCertificate->result ?? null) === 'pending')
                                    قيد الانتظار
                                @else
                                    {{ $latestCertificate->result ?? '—' }}
                                @endif
                            </div>
                        </div>

                        <div class="meta-card">
                            <div class="label">العلامة</div>
                            <div class="value">{{ $latestCertificate->score ?? '—' }}</div>
                        </div>

                        <div class="meta-card">
                            <div class="label">الساعات</div>
                            <div class="value">{{ $latestCertificate->hours_awarded ?? '—' }}</div>
                        </div>

                        <div class="meta-card">
                            <div class="label">حالة الشهادة</div>
                            <div class="value">{{ $latestCertificate->status ?? '—' }}</div>
                        </div>

                        <div class="meta-card">
                            <div class="label">تاريخ الإصدار</div>
                            <div class="value">{{ optional($latestCertificate->issue_date)->format('Y-m-d') ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="certificate-actions">
                        @if(!empty($certificatePrintUrl))
                            <a href="{{ $certificatePrintUrl }}" target="_blank" rel="noopener noreferrer" class="btn-print">
                                فتح الشهادة
                            </a>
                        @else
                            <button type="button" class="btn-print" disabled title="رابط الطباعة غير متوفر">
                                فتح الشهادة
                            </button>
                        @endif

                        @if(!empty($certificatePdfUrl))
                            <a href="{{ $certificatePdfUrl }}" target="_blank" rel="noopener noreferrer" class="btn-pdf">
                                PDF الشهادة
                            </a>
                        @else
                            <button type="button" class="btn-pdf" disabled title="رابط PDF غير متوفر">
                                PDF الشهادة
                            </button>
                        @endif
                    </div>
                @else
                    <div class="meta-card">
                        <div class="label">الحالة</div>
                        <div class="value">لا توجد شهادة مرتبطة بهذا المتدرب حالياً</div>
                    </div>
                @endif
            </div>

            <div class="footer-note">
                هذه البطاقة مخصصة للاستخدام الإداري داخل المنصة، وتعرض بيانات المتدرب وآخر شهادة مرتبطة به لأغراض التوثيق والمتابعة.
            </div>
        </div>
    </div>
</body>
</html>