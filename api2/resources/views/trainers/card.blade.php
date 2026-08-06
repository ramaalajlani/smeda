<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بطاقة مدرب</title>
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

        .trainer-card {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 8px solid #0f766e;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
            position: relative;
            overflow: hidden;
        }

        .trainer-card::before,
        .trainer-card::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(15, 118, 110, .06);
        }

        .trainer-card::before {
            top: -40px;
            right: -40px;
        }

        .trainer-card::after {
            bottom: -50px;
            left: -50px;
        }

        .header,
        .body,
        .status-box,
        .meta-grid,
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

        .trainer-name {
            font-size: 34px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
        }

        .trainer-code {
            font-size: 18px;
            color: #475569;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .trainer-specialization {
            font-size: 18px;
            color: #0f766e;
            font-weight: 800;
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

            .card-wrap {
                padding: 0;
            }

            .trainer-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button onclick="window.print()">طباعة بطاقة المدرب</button>
    </div>

    <div class="card-wrap">
        <div class="trainer-card">
            <div class="header">
                <div class="gov">الهيئة / الجهة الإدارية المختصة بالتدريب</div>
                <div class="title">بطاقة مدرب</div>
                <div class="subtitle">بطاقة تعريف واعتماد إداري للمدرب</div>
            </div>

            <div class="body">
                <div class="trainer-name">{{ $trainer->name ?? '—' }}</div>
                <div class="trainer-code">رقم المدرب: {{ $trainer->trainer_code ?? '—' }}</div>
                <div class="trainer-specialization">{{ $trainer->specialization ?? '—' }}</div>
            </div>

            <div class="status-box {{ $isEligible ? 'eligible' : 'not-eligible' }}">
                @if($isEligible)
                    هذا المدرب معتمد ومؤهل للتدريب حالياً
                @else
                    هذا المدرب مسجل لكنه غير مؤهل للتدريب حالياً
                @endif
            </div>

            <div class="meta-grid">
                <div class="meta-card">
                    <div class="label">التصنيف</div>
                    <div class="value">{{ $trainer->classification ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">الحالة</div>
                    <div class="value">{{ $trainer->status ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">لديه ToT</div>
                    <div class="value">{{ $trainer->has_tot ? 'نعم' : 'لا' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">يستطيع التدريب</div>
                    <div class="value">{{ $trainer->can_train ? 'نعم' : 'لا' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">رقم شهادة ToT</div>
                    <div class="value">{{ $trainer->tot_certificate_number ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">مصدر شهادة ToT</div>
                    <div class="value">{{ $trainer->tot_certificate_source ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">بداية الاعتماد</div>
                    <div class="value">{{ optional($trainer->accreditation_start_date)->format('Y-m-d') ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">نهاية الاعتماد</div>
                    <div class="value">{{ optional($trainer->accreditation_end_date)->format('Y-m-d') ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">المركز التدريبي</div>
                    <div class="value">{{ $trainer->trainingCenter?->name ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">المدينة</div>
                    <div class="value">{{ $trainer->trainingCenter?->city ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">الهاتف</div>
                    <div class="value">{{ $trainer->phone ?? '—' }}</div>
                </div>

                <div class="meta-card">
                    <div class="label">البريد الإلكتروني</div>
                    <div class="value">{{ $trainer->email ?? '—' }}</div>
                </div>
            </div>

            <div class="footer-note">
                هذه البطاقة مخصصة للاستخدام الإداري داخل المنصة، وتوضح حالة المدرب وأهليته التدريبية وفق بيانات الاعتماد الحالية.
            </div>
        </div>
    </div>
</body>
</html>