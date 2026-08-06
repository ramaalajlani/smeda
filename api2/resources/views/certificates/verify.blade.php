<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التحقق من الشهادة</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            padding: 40px 20px;
            background: #f8fafc;
            font-family: Tahoma, Arial, sans-serif;
            color: #0f172a;
            direction: rtl;
        }

        .verify-card {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 14px 35px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .verify-header {
            padding: 28px 24px;
            background: linear-gradient(135deg, #0f766e, #0d9488);
            color: #fff;
            text-align: center;
        }

        .verify-header h1 {
            margin: 0 0 8px;
            font-size: 30px;
            font-weight: 800;
        }

        .verify-header p {
            margin: 0;
            font-size: 15px;
            color: rgba(255,255,255,.92);
        }

        .verify-body {
            padding: 24px;
        }

        .status-box {
            padding: 18px;
            border-radius: 16px;
            margin-bottom: 24px;
            font-weight: 700;
            text-align: center;
            line-height: 1.8;
        }

        .status-valid {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status-invalid {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .status-warning {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fdba74;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 16px;
        }

        .label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .value {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
            line-height: 1.8;
        }

        .footer-note {
            margin-top: 22px;
            font-size: 14px;
            color: #64748b;
            line-height: 1.9;
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #ffedd5;
            color: #9a3412;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .verify-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-header">
            <h1>التحقق من الشهادة</h1>
            <p>منصة التحقق من الشهادات التدريبية المعتمدة</p>
        </div>

        <div class="verify-body">
            @if(empty($publicData))
                <div class="status-box status-invalid">
                    لم يتم العثور على الشهادة المطلوبة.
                </div>
            @elseif(!($isApproved ?? false))
                <div class="status-box status-warning">
                    الشهادة موجودة، لكنها غير معتمدة أو غير متاحة للتحقق العام.
                </div>

                <div class="grid">
                    <div class="item">
                        <div class="label">رقم الشهادة</div>
                        <div class="value">{{ $publicData['certificate_number'] ?? '—' }}</div>
                    </div>
                    <div class="item">
                        <div class="label">الحالة</div>
                        <div class="value">
                            <span class="badge badge-warning">{{ $publicData['status'] ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            @elseif(($isApproved ?? false) && ($isValidHash ?? false))
                <div class="status-box status-valid">
                    الشهادة أصلية وصحيحة ومعتمدة، ولم يتم العبث برابط التحقق أو ببيانات الأمان.
                </div>

                <div class="grid">
                    <div class="item">
                        <div class="label">اسم المتدرب</div>
                        <div class="value">{{ $publicData['trainee_name'] ?? '—' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">رقم الشهادة</div>
                        <div class="value">{{ $publicData['certificate_number'] ?? '—' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">نوع الشهادة</div>
                        <div class="value">
                            {{ ($publicData['certificate_type'] ?? '') === 'attendance' ? 'شهادة حضور' : 'شهادة اجتياز' }}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">عدد الساعات</div>
                        <div class="value">{{ $publicData['hours_awarded'] ?? '—' }}</div>
                    </div>

                    @if(($publicData['certificate_type'] ?? '') === 'pass')
                        <div class="item">
                            <div class="label">الدرجة</div>
                            <div class="value">{{ $publicData['score'] ?? '—' }}</div>
                        </div>
                    @endif

                    <div class="item">
                        <div class="label">الدورة</div>
                        <div class="value">{{ $publicData['course_title'] ?? '—' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">الحقيبة التدريبية</div>
                        <div class="value">{{ $publicData['kit_name'] ?? '—' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">المركز التدريبي</div>
                        <div class="value">{{ $publicData['center_name'] ?? '—' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">المدرب</div>
                        <div class="value">{{ $publicData['trainer_name'] ?? '—' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">الحالة</div>
                        <div class="value">
                            <span class="badge badge-success">معتمدة</span>
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">تاريخ الإصدار</div>
                        <div class="value">{{ $publicData['issue_date'] ?? '—' }}</div>
                    </div>

                    @if(!empty($publicData['approved_at']))
                        <div class="item">
                            <div class="label">تاريخ الاعتماد</div>
                            <div class="value">{{ $publicData['approved_at'] }}</div>
                        </div>
                    @endif
                </div>

                <div class="footer-note">
                    يتم التحقق من هذه الشهادة عبر رمز تحقق فريد وبصمة أمان رقمية مرتبطة ببيانات الشهادة المعتمدة داخل النظام.
                </div>
            @else
                <div class="status-box status-invalid">
                    تم العثور على الشهادة، لكن فشل التحقق من بصمة الأمان الرقمية.
                    هذا يعني أن الرابط أو البيانات قد تكون تعرضت للتعديل أو العبث.
                </div>

                <div class="grid">
                    <div class="item">
                        <div class="label">رقم الشهادة</div>
                        <div class="value">{{ $publicData['certificate_number'] ?? '—' }}</div>
                    </div>
                    <div class="item">
                        <div class="label">الحالة</div>
                        <div class="value">
                            <span class="badge badge-danger">فشل التحقق</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
