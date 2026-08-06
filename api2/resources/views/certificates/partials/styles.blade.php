<style>
    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    html, body {
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, sans-serif;
        background: #f4f6f9;
        color: #111827;
        direction: rtl;
        text-align: right;
        unicode-bidi: embed;
        font-size: 15px;
        line-height: 1.8;
        /* اطبع الخلفيات والألوان كما في المعاينة (Chrome/Edge/Firefox) */
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    * { box-sizing: border-box; }

    .print-toolbar {
        padding: 16px;
        text-align: center;
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
    }

    .print-toolbar button,
    .print-toolbar a.btn-print {
        display: inline-block;
        background: #0f766e;
        color: #fff;
        border: 0;
        padding: 12px 22px;
        border-radius: 10px;
        font-size: 15px;
        cursor: pointer;
        font-family: DejaVu Sans, sans-serif;
        font-weight: 700;
        text-decoration: none;
        margin: 0 6px;
    }

    .certificate-wrap { padding: 24px; }

    .certificate {
        background: #ffffff;
        border: 6px solid #0f766e;
        border-radius: 20px;
        min-height: 250mm;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
        padding: 28px 30px;
        position: relative;
        overflow: hidden;
    }

    .header { text-align: center; margin-bottom: 24px; }
    .gov { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    .title { font-size: 34px; font-weight: 700; color: #0f766e; margin-bottom: 8px; line-height: 1.3; }
    .certificate-body { text-align: center; margin-bottom: 22px; }
    .certificate-body p { font-size: 20px; line-height: 1.9; margin: 8px 0; }
    .name { font-size: 32px; font-weight: 700; color: #111827; margin: 14px 0; }
    .highlight { color: #0f766e; font-weight: 700; }

    .meta-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 10px 10px;
        table-layout: fixed;
        margin-top: 18px;
    }

    .meta-card {
        border: 1px solid #dbe4ea;
        border-radius: 14px;
        padding: 12px 10px;
        background: #f8fafc;
        text-align: center;
        vertical-align: top;
    }

    .meta-card .label {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-bottom: 6px;
        font-weight: 700;
    }

    .meta-card .value {
        display: block;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        word-wrap: break-word;
    }

    .footer-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 14px 0;
        margin-top: 26px;
        table-layout: fixed;
    }

    .sign-box { text-align: center; vertical-align: bottom; padding-top: 28px; }
    .sign-line { border-top: 2px solid #94a3b8; margin-bottom: 10px; height: 1px; }
    .sign-title { font-size: 14px; color: #334155; font-weight: 700; }
    .sign-name { font-size: 12px; color: #0f172a; font-weight: 700; margin-top: 4px; }
    .sign-title-small { font-size: 10px; color: #475569; margin-top: 2px; }
    .sign-date { font-size: 11px; color: #64748b; margin-top: 2px; }
    .sign-image {
        display: block;
        max-width: 120px;
        max-height: 48px;
        margin: 0 auto 6px;
        object-fit: contain;
    }
    .esign-code {
        font-size: 9px;
        color: #0f766e;
        font-weight: 700;
        margin-top: 6px;
        letter-spacing: 0.3px;
        word-break: break-all;
    }
    .esign-badge {
        display: inline-block;
        background: #ecfdf5;
        color: #065f46;
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 8px;
        margin-top: 4px;
    }

    .qr-box {
        text-align: center;
        background: #ffffff;
        border: 1px solid #dbe4ea;
        border-radius: 14px;
        padding: 10px;
    }

    .qr-box img { width: 130px; height: 130px; object-fit: contain; display: inline-block; }
    .qr-title { font-size: 13px; color: #475569; margin-top: 8px; font-weight: 700; }
    .status-note { margin-top: 22px; text-align: center; font-size: 14px; color: #065f46; font-weight: 700; }

    .verify-banner {
        max-width: 900px;
        margin: 0 auto 20px;
        background: #fff;
        border: 1px solid #dbe4ea;
        border-radius: 16px;
        padding: 18px 20px;
    }

    .verify-banner.pending { border-color: #f59e0b; background: #fffbeb; }
    .verify-banner h1 { margin: 0 0 10px; font-size: 22px; color: #0f172a; }
    .verify-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .verify-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
    }

    .verify-item small { display: block; color: #64748b; font-weight: 700; margin-bottom: 4px; }
    .verify-item strong { color: #0f172a; word-break: break-word; }

    @media print {
        html, body {
            background: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-toolbar, .verify-banner { display: none !important; }
        .certificate-wrap { padding: 0; }
        .certificate { box-shadow: none; }
        /* تجنّب قص الشهادة أو ظهور صفحة بيضاء إضافية */
        .certificate { page-break-inside: avoid; break-inside: avoid; }
    }
</style>
