<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شهادة مركز تدريبي</title>
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

        .certificate-wrap {
            padding: 24px;
        }

        .certificate {
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

        .certificate::before,
        .certificate::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(15, 118, 110, .06);
        }

        .certificate::before {
            top: -40px;
            right: -40px;
        }

        .certificate::after {
            bottom: -50px;
            left: -50px;
        }

        .header,
        .body,
        .status-box,
        .meta-grid,
        .stats-grid,
        .platforms-box,
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

        .center-name {
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
        }

        .center-code {
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
        }

        .status-box.eligible {
            background: rgba(25, 135, 84, .08);
            border: 1px solid rgba(25, 135, 84, .18);
            color: #198754;
        }

        .status-box.not-eligible {
            background: rgba(220, 53, 69, .08);
            border: 1px solid rgba(220, 53, 69, .18);
            color: #dc3545;
        }

        .meta-grid,
        .stats-grid {
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

        .platforms-box {
            margin-top: 26px;
            border: 1px solid #dbe4ea;
            border-radius: 18px;
            padding: 18px;
            background: #f8fafc;
        }

        .platforms-title {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 14px;
            color: #0f172a;
            text-align: center;
        }

        .platform-item {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #fff;
            padding: 12px 14px;
            margin-bottom: 10px;
        }

        .platform-item:last-child {
            margin-bottom: 0;
        }

        .platform-name {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .platform-meta {
            color: #475569;
            font-size: 14px;
            line-height: 1.8;
        }

        .footer-note {
            margin-top: 28px;
            text-align: center;
            font-size: 14px;
            color: #475569;
            line-height: 1.9;
        }

        @media print {
            body {
                background: #fff;
            }

            .print-toolbar {
                display: none;
            }

            .certificate-wrap {
                padding: 0;
            }

            .certificate {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button onclick="window.print()">طباعة شهادة المركز</button>
    </div>

    <div class="certificate-wrap">
        <div class="certificate">
            <div class="header">
                <div class="gov">الهيئة / الجهة الإدارية المختصة بالتدريب</div>
                <div class="title">شهادة مركز تدريبي</div>
                <div class="subtitle">وثيقة اعتماد إداري للمركز التدريبي</div>
            </div>

            <div class="body">
                <div class="center-name">{{ $center->name ?? '—' }}</div>
                <div class="center-code">رمز المركز: {{ $center->code ?? '—' }}</div>
            </div>

            <div class="status-box {{ $isEligible ? 'eligible' : 'not-eligible' }}">
                @if($isEligible)
                    هذا المركز معتمد وصالح لاستقبال وتنفيذ التدريب وفق بيانات الاعتماد الحالية
                @else
                    هذا المركز غير صالح حالياً للاعتماد الكامل أو يحتاج مراجعة حالة الاعتماد
                @endif
            </div>

            <div class="meta-grid">
                <div class="meta-card">
                    <div class="label">المدينة</div>
                    <div class="value">{{ $center->city ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">العنوان</div>
                    <div class="value">{{ $center->address ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">الهاتف</div>
                    <div class="value">{{ $center->phone ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">البريد الإلكتروني</div>
                    <div class="value">{{ $center->email ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">التصنيف</div>
                    <div class="value">{{ $center->classification ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">حالة الاعتماد</div>
                    <div class="value">{{ $center->accreditation_status ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">بداية الاعتماد</div>
                    <div class="value">{{ optional($center->accreditation_start_date)->format('Y-m-d') ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">نهاية الاعتماد</div>
                    <div class="value">{{ optional($center->accreditation_end_date)->format('Y-m-d') ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">يدعم التدريب الحضوري</div>
                    <div class="value">{{ $center->supports_offline_training ? 'نعم' : 'لا' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">يدعم التدريب الأونلاين</div>
                    <div class="value">{{ $center->supports_online_training ? 'نعم' : 'لا' }}</div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="meta-card">
                    <div class="label">عدد المدربين</div>
                    <div class="value">{{ $center->trainers_count ?? 0 }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">عدد الدورات</div>
                    <div class="value">{{ $center->courses_count ?? 0 }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">عدد الشهادات</div>
                    <div class="value">{{ $center->certificates_count ?? 0 }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">نشط</div>
                    <div class="value">{{ $center->is_active ? 'نعم' : 'لا' }}</div>
                </div>
            </div>

            @if($center->supports_online_training && $center->platforms && $center->platforms->count())
                <div class="platforms-box">
                    <div class="platforms-title">المنصات التدريبية المعتمدة</div>

                    @foreach($center->platforms as $platform)
                        <div class="platform-item">
                            <div class="platform-name">{{ $platform->platform_name ?? '—' }}</div>
                            <div class="platform-meta">
                                الرابط: {{ $platform->platform_url ?? '—' }}<br>
                                الحالة: {{ $platform->status ?? '—' }}<br>
                                تاريخ الموافقة: {{ optional($platform->approved_at)->format('Y-m-d') ?? '—' }}<br>
                                تاريخ الانتهاء: {{ optional($platform->expires_at)->format('Y-m-d') ?? '—' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="footer-note">
                هذه الشهادة مخصصة للاستخدام الإداري داخل المنصة، وتوضح وضع المركز التدريبي من حيث الاعتماد والأهلية والبنية التشغيلية المعتمدة.
            </div>
        </div>
    </div>
</body>
</html>