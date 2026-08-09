<?php
/**
 * بوابة المستشار الذكي — واجهة مطابقة لـ ai.smedc-sy.tech
 * المحادثة/ISIC/السجل عبر Laravel، والصوت عبر WebSocket كما اتُفق.
 */
$basePath = '../../';
$pageTitle = 'المستشار الذكي';
$advisorPortal = true;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>خدمات ISIC — المستشار الذكي</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    :root {
      color-scheme: dark;
      --bg: #0b0d12;
      --panel: #14171f;
      --panel-2: #1b1f29;
      --border: #262b36;
      --text: #eef0f4;
      --muted: #8b93a3;
      --accent: #5b8dff;
      --accent-dim: #5b8dff22;
      --good: #35c47a;
      --warn: #e0a83d;
      --bad: #e0605d;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: -apple-system, "Segoe UI", Tahoma, Arial, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
    }
    header {
      padding: 22px 28px 0;
      border-bottom: 1px solid var(--border);
    }
    .hdr-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }
    h1 { font-size: 17px; margin: 0 0 4px; font-weight: 600; }
    p.sub { color: var(--muted); margin: 0 0 18px; font-size: 13px; line-height: 1.6; }
    .back-link {
      color: var(--muted);
      text-decoration: none;
      font-size: 13px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: 2px;
    }
    .back-link:hover { color: var(--accent); }
    nav {
      display: flex;
      gap: 2px;
      margin: 0 -4px;
    }
    nav button {
      background: none;
      border: none;
      border-bottom: 2px solid transparent;
      color: var(--muted);
      padding: 10px 16px;
      font-size: 13.5px;
      cursor: pointer;
      font-family: inherit;
    }
    nav button:hover { color: var(--text); }
    nav button.active {
      color: var(--text);
      border-bottom-color: var(--accent);
      font-weight: 600;
    }
    main { padding: 26px 28px 100px; max-width: 860px; margin: 0 auto; }
    .tab { display: none; }
    .tab.active { display: block; }
    .nd-hero { max-width: 560px; margin: 48px auto 24px; text-align: center; }
    .nd-hero-icon {
      width: 64px; height: 64px; margin: 0 auto 16px;
      border-radius: 16px; display: grid; place-items: center;
      background: linear-gradient(145deg, #2a3344, #1a2030);
      border: 1px solid var(--border);
      font-size: 28px;
      box-shadow: 0 6px 20px var(--accent-dim);
    }
    .nd-hero h2 { margin: 0 0 12px; font-size: 21px; }
    .nd-hero p { color: var(--muted); font-size: 13.5px; line-height: 1.85; margin: 0 0 10px; }
    .arrow-hint {
      margin-top: 36px;
      color: var(--accent);
      font-size: 13px;
      cursor: pointer;
      background: none;
      border: none;
      font-family: inherit;
      animation: nd-bounce 1.6s ease-in-out infinite;
    }
    .arrow-hint:hover { opacity: .9; }
    @keyframes nd-bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(6px); }
    }
    .nd-kb-section { max-width: 720px; margin: 0 auto 40px; }
    .nd-kb-heading { font-size: 15px; margin: 0 0 14px; }
    .card {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 16px 18px;
      margin-top: 16px;
    }
    .card h3 {
      margin: 0 0 10px;
      font-size: 13.5px;
      color: var(--muted);
      font-weight: 600;
    }
    label.field-label {
      display: block;
      color: var(--muted);
      font-size: 12.5px;
      margin-bottom: 6px;
    }
    textarea, input[type="text"], input[type="file"] {
      width: 100%;
      background: var(--panel);
      border: 1px solid var(--border);
      color: var(--text);
      border-radius: 10px;
      padding: 11px 13px;
      font-size: 14px;
      font-family: inherit;
    }
    textarea { resize: vertical; min-height: 96px; }
    textarea:focus, input:focus {
      outline: none;
      border-color: var(--accent);
    }
    .field { margin-bottom: 12px; }
    button.primary {
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 9px;
      padding: 10px 20px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
    }
    button.primary:hover:not(:disabled) { opacity: .9; }
    button.primary:disabled { opacity: .55; cursor: wait; }
    .dept-pills { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; }
    .dept-pill {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 100px;
      padding: 6px 14px;
      font-size: 13px;
      cursor: pointer;
      color: var(--muted);
      font-family: inherit;
    }
    .dept-pill.active {
      border-color: var(--accent);
      color: var(--text);
      background: var(--accent-dim);
    }
    .dept-pill .n { color: var(--accent); font-weight: 600; margin-inline-start: 4px; }
    .nd-kb-type {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 12px;
      margin-bottom: 10px;
      overflow: hidden;
    }
    .nd-kb-type-row {
      padding: 13px 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      cursor: pointer;
    }
    .nd-kb-type-row:hover { background: var(--panel-2); }
    .nd-kb-type-name {
      font-size: 13.5px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 7px;
    }
    .nd-kb-type-meta { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .badge {
      display: inline-block;
      font-size: 11px;
      padding: 2px 8px;
      border-radius: 100px;
      background: var(--panel-2);
      color: var(--muted);
      border: 1px solid var(--border);
    }
    .nd-kb-type-files { display: none; padding: 0 12px 12px; }
    .nd-kb-type.open .nd-kb-type-files { display: block; }
    .nd-kb-file {
      background: var(--panel-2);
      border: 1px solid var(--border);
      border-radius: 10px;
      margin-top: 8px;
      overflow: hidden;
    }
    .nd-kb-file-row {
      padding: 11px 13px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      cursor: pointer;
    }
    .nd-kb-file-row:hover { background: var(--accent-dim); }
    .nd-kb-file-name {
      font-size: 12.5px;
      word-break: break-all;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .nd-kb-file-details {
      display: none;
      padding: 4px 13px 13px;
      border-top: 1px solid var(--border);
      color: var(--muted);
      font-size: 12.5px;
      line-height: 1.7;
      white-space: pre-wrap;
      max-height: 220px;
      overflow: auto;
    }
    .nd-kb-file.open .nd-kb-file-details { display: block; }
    .spinner, .placeholder {
      color: var(--muted);
      font-size: 13px;
      padding: 20px 0;
      text-align: center;
    }
    .code {
      font-family: ui-monospace, "SF Mono", monospace;
      color: var(--accent);
      background: var(--accent-dim);
      padding: 1px 7px;
      border-radius: 6px;
      font-size: 13px;
    }
    .ingest-result { margin-top: 12px; }
  </style>
</head>
<body class="aic-portal" data-aic-portal="1">
  <header>
    <div class="hdr-row">
      <div>
        <h1>خدمات ISIC — المستشار الذكي</h1>
        <p class="sub">لوحة المستشار الذكي داخل منصة الهيئة — محادثة، تصنيف ISIC4، صوت، وقاعدة معرفة.</p>
      </div>
      <a class="back-link" href="<?php echo $basePath; ?>dashboard.php">
        <i class="bi bi-arrow-right"></i> العودة للمنصة
      </a>
    </div>
    <nav aria-label="أقسام الصفحة">
      <button type="button" class="active" data-portal-tab="home">الرئيسية</button>
      <button type="button" data-portal-tab="kb">قاعدة المعرفة</button>
    </nav>
  </header>

  <main>
    <section class="tab active" data-portal-view="home">
      <div class="nd-hero">
        <div class="nd-hero-icon" aria-hidden="true">🧭</div>
        <h2>خدمات ISIC — المستشار الذكي</h2>
        <p>منصة هيئة المشروعات لمساعدة المنشآت الصغيرة والمتوسطة: تصنيف النشاط وفق نظام ISIC4 / SYRSIC السوري، وإرشاد تفاعلي عبر المستشار الذكي نصًا وصوتًا.</p>
        <p>يستند المستشار إلى قاعدة معرفة معتمدة لكل قسم، ويساعد في تهيئة المستندات والإجراءات دون الاعتماد على معلومات عامة غير موثّقة.</p>
        <p>ابدأ من أيقونة المحادثة العائمة أسفل الشاشة، أو انتقل إلى تبويب قاعدة المعرفة لاستعراض المواد المدرَّبة لكل قسم.</p>
        <button type="button" class="arrow-hint" data-aic-open-chat>↙ اضغط على الأيقونة العائمة أسفل الشاشة</button>
      </div>
    </section>

    <section class="tab" data-portal-view="kb">
      <div class="nd-kb-section">
        <div class="card" id="kbIngestCard" hidden>
          <h3>إضافة ملفات جديدة إلى قسم</h3>
          <form id="kbIngestForm">
            <div class="field">
              <label class="field-label" for="kbDept">القسم (مثال: isic4, finance, legal, training)</label>
              <input type="text" id="kbDept" name="department_id" placeholder="اسم القسم" required maxlength="64" />
            </div>
            <div class="field">
              <label class="field-label" for="kbFiles">ملفات (فيديو، صورة، PDF، Word، Excel، نص...)</label>
              <input type="file" id="kbFiles" name="files[]" multiple />
            </div>
            <div class="field">
              <label class="field-label" for="kbText">أو ألصق نصًا مباشرة (اختياري)</label>
              <textarea id="kbText" name="text" placeholder="نص يُضاف إلى قاعدة معرفة هذا القسم"></textarea>
            </div>
            <button type="submit" class="primary" id="kbSubmit">رفع وتدريب</button>
          </form>
          <div class="ingest-result" id="kbIngestResult"></div>
        </div>

        <div class="card" id="kbBrowseCard" style="margin-top:22px">
          <h3>📚 ما تم تدريبه في قاعدة المعرفة</h3>
          <div class="field-label" style="margin-bottom:8px">اختر القسم</div>
          <div class="dept-pills" id="kbPills"><div class="spinner">جارٍ التحميل...</div></div>
          <div id="kbFilesList"></div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
  <script src="<?php echo $basePath; ?>assets/js/modules/ai-advisor-portal.js?v=1.1"></script>
</body>
</html>
