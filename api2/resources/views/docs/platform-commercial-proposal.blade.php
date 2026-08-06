<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>عرض تجاري — منصة الهيئة</title>
    <style>
        @page { size: A4 portrait; margin: 14mm 12mm; }
        body {
            margin: 0; padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px; line-height: 1.65;
            color: #1e293b; direction: rtl; text-align: right;
        }
        .cover {
            text-align: center; padding-top: 35mm;
            page-break-after: always;
        }
        .cover-badge {
            display: inline-block; background: #ecfdf5; color: #065f46;
            border: 1px solid #6ee7b7; padding: 6px 16px; border-radius: 20px;
            font-size: 10px; font-weight: bold; margin-bottom: 18px;
        }
        .cover h1 { font-size: 26px; color: #0f766e; margin: 0 0 10px; line-height: 1.4; }
        .cover h2 { font-size: 14px; color: #475569; font-weight: normal; margin: 0 0 28px; }
        .cover-meta { font-size: 11px; color: #64748b; margin-top: 40mm; }
        h2.section {
            font-size: 15px; color: #0f766e; border-bottom: 2px solid #0f766e;
            padding-bottom: 4px; margin: 18px 0 10px;
        }
        h3 { font-size: 12px; color: #334155; margin: 12px 0 6px; }
        p { margin: 0 0 8px; }
        ul { margin: 0 0 10px; padding-right: 18px; }
        li { margin-bottom: 3px; }
        table {
            width: 100%; border-collapse: collapse; margin-bottom: 12px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1; padding: 6px 8px;
            vertical-align: top;
        }
        th { background: #f0fdfa; color: #0f766e; font-weight: bold; }
        tr:nth-child(even) td { background: #f8fafc; }
        .pkg-table th { text-align: center; }
        .pkg-table td { text-align: center; }
        .pkg-table td:first-child { text-align: right; font-weight: bold; }
        .highlight { background: #fef3c7; font-weight: bold; }
        .price { color: #0f766e; font-weight: bold; font-size: 12px; }
        .note {
            background: #f1f5f9; border-right: 3px solid #64748b;
            padding: 8px 10px; margin: 10px 0; font-size: 10px;
        }
        .footer {
            margin-top: 20px; padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px; color: #94a3b8; text-align: center;
        }
        .page-break { page-break-before: always; }
        .badge { display: inline-block; background: #dbeafe; color: #1d4ed8; padding: 2px 8px; border-radius: 10px; font-size: 9px; }
    </style>
</head>
<body>

<div class="cover">
    <div class="cover-badge">عرض تجاري سري — Confidential</div>
    <h1>منصة الهيئة الرقمية<br>للمشاريع الصغيرة والمتوسطة</h1>
    <h2>حل حكومي متكامل — Laravel + API + لوحات تحكم حسب الدور</h2>
    <p style="font-size:12px;color:#334155;max-width:420px;margin:0 auto;">
        منصة موحّدة تشمل: التدريب، التمويل، GIS/الاحتياجات، الحضانات، ريادة الأعمال،
        الإعلام، سوق العمل، والإدارة الهرمية لـ 14 محافظة.
    </p>
    <div class="cover-meta">
        <div>تاريخ العرض: {{ $generatedAt }}</div>
        <div>الإصدار: {{ $version }}</div>
        <div>السوق المستهدف: الشرق الأوسط — سوريا / الخليج / الأردن</div>
    </div>
</div>

<h2 class="section">1. الملخص التنفيذي</h2>
<p>
    المنصة عبارة عن نظام حكومي/ semi-government جاهز للإنتاج بنسبة <strong>75–85%</strong>،
    مبني على Laravel 11 مع Sanctum و Spatie Permissions، ويضم أكثر من <strong>48</strong> متحكم API
    و<strong>250+</strong> اختبار Feature، و<strong>25+</strong> دور وظيفي مع عزل بيانات وطني/فرعي.
</p>
<p>
    تقدير تكلفة إعادة البناء من الصفر: <span class="price">120,000 – 350,000 USD</span> (سوق خليجي)
    أو <span class="price">25,000 – 80,000 USD</span> (سوق سوريا/لبنان).
</p>

<h2 class="section">2. الوحدات والميزات</h2>
<table>
    <thead>
        <tr><th style="width:22%">الوحدة</th><th>الميزات الرئيسية</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>البنية الأساسية</td>
            <td>مصادقة Sanctum، RBAC، 14 محافظة + فروع، لوحات KPI حسب الدور، CAPTCHA، Rate limits، Policies</td>
        </tr>
        <tr>
            <td>التدريب</td>
            <td>مراكز، مدربون، متدربون، دورات، حقائب، طلبات تسجيل، شهادات + QR، خريطة مراكز</td>
        </tr>
        <tr>
            <td>التمويل</td>
            <td>طلبات تمويل، workflow كامل، استشاريون، بنك مركزي، شركاء، قروض، مقاييس وطنية/فرعية</td>
        </tr>
        <tr>
            <td>GIS / الاحتياجات</td>
            <td>احتياجات مواطن/دولة، خريطة، workflow مراجعة، export، dashboard</td>
        </tr>
        <tr>
            <td>الاستشارات</td>
            <td>طلبات، عروض، عقود، تقارير استشارية</td>
        </tr>
        <tr>
            <td>الحضانات</td>
            <td>حاضنات، برامج، طلبات، مشاريع محتضنة، إرشاد، تقارير تقدم، policies</td>
        </tr>
        <tr>
            <td>ريادة الأعمال</td>
            <td>ملف رائد أعمال شامل، مراجعة إدارية، إحصائيات</td>
        </tr>
        <tr>
            <td>الإعلام</td>
            <td>أخبار، قصص نجاح، فلترة published، inbox داخلي، إشعارات</td>
        </tr>
        <tr>
            <td>سوق العمل</td>
            <td>وظائف، طلبات توظيف، طلبات تدريب للموظفين</td>
        </tr>
        <tr>
            <td>الإدارة</td>
            <td>مستخدمون، أدوار، صلاحيات، سجل نشاط، agreements، فروع</td>
        </tr>
    </tbody>
</table>

<h2 class="section">3. المواصفات التقنية</h2>
<ul>
    <li><strong>Backend:</strong> Laravel 11, PHP 8.2+, MySQL/SQLite, REST API</li>
    <li><strong>Frontend:</strong> PHP + JavaScript, RTL عربي، Bootstrap</li>
    <li><strong>أمان:</strong> Policies, Spatie Permissions, throttling, mass-assignment protection</li>
    <li><strong>اختبارات:</strong> 250+ Feature Test (PHPUnit)</li>
    <li><strong>PDF:</strong> DomPDF (شهادات، بطاقات)</li>
</ul>

<div class="page-break"></div>

<h2 class="section">4. باقات التسعير المقترحة</h2>

<h3>4.1 سوريا / لبنان / العراق</h3>
<table class="pkg-table">
    <thead>
        <tr>
            <th>الباقة</th>
            <th>يشمل</th>
            <th>السعر (USD)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>أساسي — ترخيص</td>
            <td>استخدام سنوي بدون IP</td>
            <td class="price">8,000 – 15,000 / سنة</td>
        </tr>
        <tr>
            <td>قياسي — بيع كود</td>
            <td>كود كامل + IP + نشر أول</td>
            <td class="price">25,000 – 45,000</td>
        </tr>
        <tr class="highlight">
            <td>موصى به</td>
            <td>كود + IP + تخصيص + 6 أشهر دعم</td>
            <td class="price">40,000 – 70,000</td>
        </tr>
        <tr>
            <td>SaaS</td>
            <td>استضافة + صيانة لجهة واحدة</td>
            <td class="price">1,500 – 3,500 / شهر</td>
        </tr>
    </tbody>
</table>

<h3>4.2 الأردن / مصر</h3>
<table class="pkg-table">
    <thead>
        <tr><th>الباقة</th><th>السعر (USD)</th></tr>
    </thead>
    <tbody>
        <tr><td>White-label كامل</td><td class="price">60,000 – 120,000</td></tr>
        <tr><td>ترخيص + دعم سنوي</td><td class="price">15,000 – 30,000 / سنة</td></tr>
        <tr><td>SaaS</td><td class="price">3,000 – 8,000 / شهر</td></tr>
    </tbody>
</table>

<h3>4.3 الخليج (الإمارات / السعودية / قطر / الكويت / البحرين / عُمان)</h3>
<table class="pkg-table">
    <thead>
        <tr>
            <th>الباقة</th>
            <th>يشمل</th>
            <th>السعر (USD)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Starter</td>
            <td>منصة + تخصيص أساسي + نشر</td>
            <td class="price">100,000 – 150,000</td>
        </tr>
        <tr class="highlight">
            <td>Standard</td>
            <td>تخصيص + تدريب + 6 أشهر SLA</td>
            <td class="price">180,000 – 280,000</td>
        </tr>
        <tr>
            <td>Enterprise</td>
            <td>تكاملات + SSO + pen-test + SLA 99.9%</td>
            <td class="price">280,000 – 500,000+</td>
        </tr>
        <tr>
            <td>SaaS</td>
            <td>Multi-tenant / جهة واحدة</td>
            <td class="price">8,000 – 25,000 / شهر</td>
        </tr>
    </tbody>
</table>

<h2 class="section">5. السعر المرجعي الموصى به</h2>
<table>
    <thead>
        <tr><th>السوق</th><th>بيع كامل (كود + IP + نشر + 3 أشهر دعم)</th></tr>
    </thead>
    <tbody>
        <tr><td>سوريا / إقليمي</td><td class="price">35,000 – 55,000 USD</td></tr>
        <tr><td>أردن / العراق</td><td class="price">70,000 – 110,000 USD</td></tr>
        <tr><td>خليجي Enterprise</td><td class="price">180,000 – 280,000 USD</td></tr>
    </tbody>
</table>

<h2 class="section">6. يُباع منفصلاً (Add-ons)</h2>
<ul>
    <li>تكامل Sham Cash / بوابات دفع — <span class="badge">+15–25%</span></li>
    <li>تكامل السجل التجاري / API حكومي — <span class="badge">+20–30%</span></li>
    <li>تطبيق Mobile (Flutter) — <span class="badge">40,000 – 80,000 USD</span></li>
    <li>SSO / توقيع إلكتروني — <span class="badge">+15–20%</span></li>
    <li>صيانة سنوية — <span class="badge">15–20% من قيمة العقد</span></li>
    <li>Pen-test رسمي + شهادة أمن — <span class="badge">5,000 – 15,000 USD</span></li>
</ul>

<h2 class="section">7. خارطة الطريق (لم تُدمج بعد)</h2>
<ul>
    <li>Sham Cash + السجل التجاري</li>
    <li>httpOnly cookies بدل localStorage</li>
    <li>تقسيم routes/api.php</li>
    <li>SSO حكومي / توقيع إلكتروني</li>
</ul>

<div class="note">
    <strong>تنويه:</strong> الأسعار تقديرية استشارية وتعتمد على: نطاق IP، مدة الدعم، التخصيص،
    الاستضافة، ومتطلبات المشتري (procurement). صالحة لمدة 90 يوماً من تاريخ العرض.
</div>

<div class="footer">
    منصة الهيئة الرقمية — عرض تجاري — {{ $generatedAt }} — جميع الحقوق محفوظة
</div>

</body>
</html>
