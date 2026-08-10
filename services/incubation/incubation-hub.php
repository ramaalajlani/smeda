<?php
$basePath  = '../../';
$pageTitle = 'مساحة الاحتضان';
$activePage= 'incubation';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <style>
    :root {
      --hub-primary: #7c3aed;
      --hub-accent:  #a78bfa;
      --hub-dark:    #1e1b4b;
      --hub-soft:    #f5f3ff;
      --hub-border:  rgba(124,58,237,.13);
      --hub-shadow:  0 10px 32px rgba(124,58,237,.10);
      --hub-green:   #15803d;
      --hub-muted:   #6b7280;
    }

    body { background: linear-gradient(160deg,#f5f3ff 0%,#ede9fe 100%); min-height: 100vh; }

    /* ── Hub Hero ── */
    .hub-hero {
      background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 50%, #7c3aed 100%);
      padding: 36px 0 28px;
      color: #fff;
      position: relative;
      overflow: hidden;
    }
    .hub-hero::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(ellipse at 80% 50%, rgba(167,139,250,.18) 0%, transparent 60%);
    }
    .hub-hero-inner {
      position: relative; z-index: 1;
      max-width: 900px; margin: 0 auto; padding: 0 20px;
      display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
    }
    .hub-brand { display: flex; align-items: center; gap: 14px; }
    .hub-brand-icon {
      width: 56px; height: 56px; border-radius: 18px;
      background: rgba(255,255,255,.15); backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.2);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.6rem;
    }
    .hub-brand-text h1 { font-size: 1.3rem; font-weight: 900; margin: 0; }
    .hub-brand-text p  { font-size: .82rem; opacity: .75; margin: 3px 0 0; }
    .hub-status-pill {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
      border-radius: 30px; padding: 8px 18px; font-size: .83rem; font-weight: 700;
    }
    .hub-status-pill .dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: #4ade80;
      box-shadow: 0 0 0 3px rgba(74,222,128,.3);
      animation: pulse-dot 2s ease infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { box-shadow: 0 0 0 3px rgba(74,222,128,.3); }
      50%       { box-shadow: 0 0 0 6px rgba(74,222,128,.1); }
    }

    /* ── Layout ── */
    .hub-body { max-width: 900px; margin: 0 auto; padding: 24px 20px 60px; }

    /* ── Stage track (progress header) ── */
    .stage-track {
      background: #fff; border: 1px solid var(--hub-border);
      border-radius: 20px; padding: 20px 24px; margin-bottom: 20px;
      box-shadow: var(--hub-shadow);
    }
    .stage-track-title { font-size: .78rem; font-weight: 700; color: var(--hub-muted); margin-bottom: 14px; text-transform: uppercase; letter-spacing: .5px; }
    .stages {
      display: flex; align-items: center; gap: 0; position: relative;
    }
    .stage-item {
      flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;
      position: relative;
    }
    .stage-item::after {
      content: ''; position: absolute;
      top: 17px; left: calc(-50% + 18px); right: calc(50% + 18px);
      height: 2px; background: #e5e7eb;
    }
    .stage-item:first-child::after { display: none; }
    .stage-item.done::after, .stage-item.active::after { background: var(--hub-primary); }
    .stage-dot {
      width: 36px; height: 36px; border-radius: 50%;
      background: #f3f4f6; border: 2px solid #e5e7eb;
      display: flex; align-items: center; justify-content: center;
      font-size: .85rem; color: var(--hub-muted); z-index: 1;
      transition: all .3s;
    }
    .stage-item.done .stage-dot { background: var(--hub-primary); border-color: var(--hub-primary); color: #fff; }
    .stage-item.active .stage-dot {
      background: #fff; border-color: var(--hub-primary); color: var(--hub-primary);
      box-shadow: 0 0 0 4px rgba(124,58,237,.15);
    }
    .stage-lbl { font-size: .72rem; font-weight: 700; color: var(--hub-muted); text-align: center; }
    .stage-item.done .stage-lbl, .stage-item.active .stage-lbl { color: var(--hub-primary); }

    /* ── Cards ── */
    .hub-card {
      background: #fff; border: 1px solid var(--hub-border);
      border-radius: 20px; padding: 24px;
      box-shadow: var(--hub-shadow); margin-bottom: 16px;
    }
    .card-hd {
      font-weight: 800; font-size: .97rem; color: var(--hub-dark);
      margin: 0 0 20px; display: flex; align-items: center; gap: 9px;
    }
    .card-hd i { color: var(--hub-primary); }

    /* ── Empty state (no application) ── */
    .empty-hub {
      text-align: center; padding: 48px 20px;
    }
    .empty-hub-icon {
      width: 90px; height: 90px; border-radius: 28px; margin: 0 auto 20px;
      background: linear-gradient(135deg, #ede9fe, #ddd6fe);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.5rem; color: var(--hub-primary);
    }
    .empty-hub h2 { font-size: 1.25rem; font-weight: 900; color: var(--hub-dark); margin-bottom: 8px; }
    .empty-hub p  { color: var(--hub-muted); font-size: .9rem; margin-bottom: 24px; line-height: 1.7; max-width: 400px; margin-inline: auto; }

    /* ── Benefit chips ── */
    .benefit-chips { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 28px; }
    .benefit-chip {
      display: inline-flex; align-items: center; gap: 7px;
      background: var(--hub-soft); border: 1px solid var(--hub-border);
      border-radius: 30px; padding: 7px 16px;
      font-size: .82rem; font-weight: 700; color: var(--hub-dark);
    }
    .benefit-chip i { color: var(--hub-primary); }

    /* ── Buttons ── */
    .btn-hub {
      display: inline-flex; align-items: center; gap: 8px;
      background: linear-gradient(135deg, var(--hub-primary), var(--hub-accent));
      color: #fff; border: none; border-radius: 14px;
      padding: 13px 32px; font-weight: 800; font-size: .95rem;
      cursor: pointer; text-decoration: none;
      box-shadow: 0 6px 20px rgba(124,58,237,.3);
      transition: opacity .2s, transform .2s;
    }
    .btn-hub:hover { opacity: .88; transform: translateY(-2px); color: #fff; }
    .btn-hub-ghost {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--hub-soft); color: var(--hub-primary);
      border: 1.5px solid var(--hub-border); border-radius: 14px;
      padding: 12px 24px; font-weight: 700; font-size: .88rem;
      cursor: pointer; text-decoration: none;
      transition: background .2s;
    }
    .btn-hub-ghost:hover { background: #ede9fe; color: var(--hub-primary); }

    /* ── Form ── */
    .form-section { display: none; }
    .form-section.active { display: block; }
    .form-group { margin-bottom: 16px; }
    .form-label { font-size: .82rem; font-weight: 700; color: var(--hub-muted); display: block; margin-bottom: 6px; }
    .form-control, .form-select {
      width: 100%; border: 1.5px solid var(--hub-border);
      border-radius: 12px; padding: 11px 14px;
      font-size: .9rem; color: var(--hub-dark); box-sizing: border-box;
      background: #fff; transition: border-color .2s, box-shadow .2s;
      font-family: inherit;
    }
    .form-control:focus, .form-select:focus {
      outline: none; border-color: var(--hub-primary);
      box-shadow: 0 0 0 3px rgba(124,58,237,.12);
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 560px) { .form-row { grid-template-columns: 1fr; } }
    .check-row {
      display: flex; align-items: center; gap: 10px;
      padding: 12px 14px; background: var(--hub-soft);
      border-radius: 12px; margin-bottom: 10px; cursor: pointer;
    }
    .check-row input[type=checkbox] { width: 18px; height: 18px; accent-color: var(--hub-primary); cursor: pointer; }
    .check-row label { font-weight: 700; font-size: .88rem; cursor: pointer; color: var(--hub-dark); }

    /* step indicator */
    .step-bar { display: flex; gap: 0; margin-bottom: 24px; }
    .step-seg {
      flex: 1; padding-bottom: 9px; text-align: center;
      font-size: .78rem; font-weight: 700; color: var(--hub-muted);
      border-bottom: 3px solid #e5e7eb; transition: color .2s, border-color .2s;
    }
    .step-seg.active { color: var(--hub-primary); border-color: var(--hub-primary); }
    .step-seg.done   { color: var(--hub-green); border-color: var(--hub-green); }

    .form-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }

    /* ── Alert ── */
    .hub-alert {
      border-radius: 12px; padding: 12px 16px;
      font-weight: 700; font-size: .88rem; margin-bottom: 14px; display: none;
    }
    .hub-alert.success { background: #dcfce7; color: #14532d; }
    .hub-alert.error   { background: #fee2e2; color: #991b1b; }

    /* ── Timeline ── */
    .tl { list-style: none; padding: 0; margin: 0; position: relative; }
    .tl::before {
      content: ''; position: absolute;
      right: 17px; top: 0; bottom: 0; width: 2px;
      background: var(--hub-border);
    }
    .tl-item { display: flex; gap: 14px; padding-bottom: 22px; }
    .tl-dot {
      width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
      background: var(--hub-soft); border: 2px solid var(--hub-border);
      display: flex; align-items: center; justify-content: center;
      font-size: .85rem; color: var(--hub-muted); z-index: 1;
    }
    .tl-dot.done    { background: var(--hub-primary); border-color: var(--hub-primary); color: #fff; }
    .tl-dot.current { background: #fff; border-color: var(--hub-primary); color: var(--hub-primary); box-shadow: 0 0 0 4px rgba(124,58,237,.12); }
    .tl-dot.ok      { background: var(--hub-green); border-color: var(--hub-green); color: #fff; }
    .tl-dot.fail    { background: #dc2626; border-color: #dc2626; color: #fff; }
    .tl-body { flex: 1; padding-top: 4px; }
    .tl-title { font-weight: 800; font-size: .92rem; color: var(--hub-dark); }
    .tl-sub   { font-size: .8rem; color: var(--hub-muted); margin-top: 3px; }

    /* ── Badge ── */
    .badge-hub { padding: 5px 14px; border-radius: 20px; font-size: .8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
    .b-pending  { background: #fef3c7; color: #92400e; }
    .b-review   { background: #ede9fe; color: #7c3aed; }
    .b-accepted { background: #dcfce7; color: #15803d; }
    .b-rejected { background: #fee2e2; color: #dc2626; }

    /* ── Cloud Workspace ── */
    .cloud-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 640px) { .cloud-grid { grid-template-columns: 1fr; } }

    .cloud-tile {
      background: var(--hub-soft); border: 1px solid var(--hub-border);
      border-radius: 16px; padding: 18px;
    }
    .cloud-tile-lbl { font-size: .75rem; font-weight: 700; color: var(--hub-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .4px; }
    .cloud-tile-val { font-size: 1rem; font-weight: 800; color: var(--hub-dark); }

    .progress-bar-wrap { background: #e5e7eb; border-radius: 999px; height: 8px; margin: 10px 0 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--hub-primary), var(--hub-accent)); transition: width .6s ease; }

    .mentor-card {
      display: flex; align-items: center; gap: 14px;
      background: linear-gradient(135deg, var(--hub-soft), #ede9fe);
      border: 1px solid var(--hub-border); border-radius: 16px; padding: 16px;
    }
    .mentor-avatar {
      width: 52px; height: 52px; border-radius: 50%;
      background: linear-gradient(135deg, var(--hub-primary), var(--hub-accent));
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 1.3rem; flex-shrink: 0;
    }
    .mentor-name { font-weight: 800; font-size: .95rem; color: var(--hub-dark); }
    .mentor-role { font-size: .8rem; color: var(--hub-muted); margin-top: 2px; }

    .session-item {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 0; border-bottom: 1px solid var(--hub-border);
    }
    .session-item:last-child { border-bottom: none; }
    .session-date-box {
      width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
      background: var(--hub-primary); color: #fff;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .session-date-box .day { font-size: 1.1rem; font-weight: 900; line-height: 1; }
    .session-date-box .mon { font-size: .6rem; font-weight: 700; opacity: .8; }

    .doc-item {
      display: flex; align-items: center; justify-content: space-between;
      padding: 11px 14px; background: var(--hub-soft);
      border-radius: 12px; margin-bottom: 8px;
    }
    .doc-item:last-child { margin-bottom: 0; }
    .doc-lbl { font-size: .87rem; font-weight: 700; color: var(--hub-dark); }
    .doc-status { font-size: .75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
    .doc-ok      { background: #dcfce7; color: #15803d; }
    .doc-pending { background: #fef3c7; color: #92400e; }
    .doc-missing { background: #fee2e2; color: #dc2626; }

    .milestone-list { display: flex; flex-direction: column; gap: 8px; }
    .milestone-item {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 14px; background: var(--hub-soft); border-radius: 12px;
    }
    .milestone-check {
      width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center; font-size: .85rem;
    }
    .milestone-check.done { background: #dcfce7; color: #15803d; }
    .milestone-check.todo { background: #f3f4f6; color: var(--hub-muted); }
    .milestone-txt { font-size: .88rem; font-weight: 700; color: var(--hub-dark); }

    /* Rejected state */
    .reject-box {
      text-align: center; padding: 32px 20px;
    }
    .reject-icon { font-size: 3rem; color: #dc2626; margin-bottom: 14px; display: block; }
    .reviewer-note {
      background: #fff7ed; border: 1px solid #fed7aa;
      border-radius: 14px; padding: 16px 18px; text-align: right;
      font-size: .88rem; line-height: 1.7; color: #7c2d12; margin: 16px 0;
    }

    /* Loading */
    .hub-loading { text-align: center; padding: 60px 20px; color: var(--hub-muted); }
    .hub-loading i { font-size: 2.5rem; display: block; margin-bottom: 12px; opacity: .4; animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>

<!-- Hub Hero -->
<div class="hub-hero">
  <div class="hub-hero-inner">
    <div class="hub-brand">
      <div class="hub-brand-icon"><i class="bi bi-rocket-takeoff-fill"></i></div>
      <div class="hub-brand-text">
        <h1>مساحة الاحتضان</h1>
        <p>هيئة تنمية المشروعات الصغيرة والمتوسطة — SMEDC Hub</p>
      </div>
    </div>
    <div class="hub-status-pill" id="heroStatusPill" style="display:none">
      <span class="dot"></span>
      <span id="heroStatusText">جاري التحميل...</span>
    </div>
  </div>
</div>

<main>
<div class="hub-body">

  <!-- Loading -->
  <div class="hub-loading" id="hubLoading">
    <i class="bi bi-arrow-repeat"></i>
    جاري تحميل مساحتك...
  </div>

  <!-- Content (hidden until loaded) -->
  <div id="hubContent" style="display:none">

    <!-- Stage Track (shown when application exists) -->
    <div class="stage-track" id="stageTrack" style="display:none">
      <div class="stage-track-title">رحلتك مع الهيئة</div>
      <div class="stages">
        <div class="stage-item" id="stg0">
          <div class="stage-dot"><i class="bi bi-send-fill"></i></div>
          <div class="stage-lbl">الطلب</div>
        </div>
        <div class="stage-item" id="stg1">
          <div class="stage-dot"><i class="bi bi-search"></i></div>
          <div class="stage-lbl">المراجعة</div>
        </div>
        <div class="stage-item" id="stg2">
          <div class="stage-dot"><i class="bi bi-camera-video-fill"></i></div>
          <div class="stage-lbl">المقابلة</div>
        </div>
        <div class="stage-item" id="stg3">
          <div class="stage-dot"><i class="bi bi-patch-check-fill"></i></div>
          <div class="stage-lbl">القبول</div>
        </div>
        <div class="stage-item" id="stg4">
          <div class="stage-dot"><i class="bi bi-lightning-charge-fill"></i></div>
          <div class="stage-lbl">الاحتضان</div>
        </div>
      </div>
    </div>

    <!-- STATE 0: No application -->
    <div id="stateNew" style="display:none">
      <div class="hub-card">
        <div class="empty-hub">
          <div class="empty-hub-icon"><i class="bi bi-cloud-upload-fill"></i></div>
          <h2>ارفع مشروعك إلى الهيئة</h2>
          <p>أرسل طلب احتضان وسنتكفل بكل شيء — من المراجعة إلى تعيين الحاضنة والمرشد ومتابعة رحلتك كاملةً.</p>

          <div class="benefit-chips">
            <span class="benefit-chip"><i class="bi bi-building-fill"></i> حاضنة مناسبة لمشروعك</span>
            <span class="benefit-chip"><i class="bi bi-person-badge-fill"></i> مرشد متخصص</span>
            <span class="benefit-chip"><i class="bi bi-cash-coin"></i> دعم تمويلي</span>
            <span class="benefit-chip"><i class="bi bi-graph-up-arrow"></i> تتبع مراحل النمو</span>
            <span class="benefit-chip"><i class="bi bi-file-earmark-check-fill"></i> توجيه قانوني وإداري</span>
          </div>

          <button class="btn-hub" onclick="showForm()">
            <i class="bi bi-cloud-upload-fill"></i> ارفع طلب احتضان
          </button>
        </div>
      </div>

      <!-- Apply Form -->
      <div class="hub-card" id="applyForm" style="display:none">
        <div class="card-hd"><i class="bi bi-cloud-upload-fill"></i> طلب الاحتضان</div>

        <div class="step-bar">
          <div class="step-seg active" id="seg0">1 — المشروع</div>
          <div class="step-seg" id="seg1">2 — التفاصيل</div>
          <div class="step-seg" id="seg2">3 — الإرسال</div>
        </div>

        <div id="hubAlert" class="hub-alert"></div>

        <!-- Step 1: Project basics -->
        <div class="form-section active" id="fs0">
          <div class="form-group">
            <label class="form-label">اسم المشروع <span style="color:#dc2626">*</span></label>
            <input type="text" id="fName" class="form-control" placeholder="ما اسم مشروعك؟">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">القطاع <span style="color:#dc2626">*</span></label>
              <select id="fSector" class="form-select">
                <option value="">— اختر —</option>
                <option value="tech">تقنية ورقمية</option>
                <option value="industrial">صناعي وتحويلي</option>
                <option value="agricultural">زراعي وغذائي</option>
                <option value="services">خدمات</option>
                <option value="creative">إبداعي وثقافي</option>
                <option value="health">صحة وطب</option>
                <option value="education">تعليم وتدريب</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">مرحلة المشروع حالياً <span style="color:#dc2626">*</span></label>
              <select id="fStage" class="form-select">
                <option value="idea">فكرة — لم يبدأ بعد</option>
                <option value="pre_seed">نموذج أولي</option>
                <option value="seed">انطلق ولم يحقق إيرادات</option>
                <option value="early">انطلق ويحقق إيرادات</option>
                <option value="growth">نمو — يبحث عن توسع</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">فكرة المشروع باختصار <span style="color:#dc2626">*</span></label>
            <textarea id="fDesc" class="form-control" rows="4" placeholder="اشرح فكرة مشروعك في 3-5 جمل — ماذا تقدم ولمن؟"></textarea>
          </div>
          <div class="form-actions">
            <span></span>
            <button class="btn-hub" onclick="goStep(1)">التالي <i class="bi bi-arrow-left-circle-fill"></i></button>
          </div>
        </div>

        <!-- Step 2: Details -->
        <div class="form-section" id="fs1">
          <div class="form-group">
            <label class="form-label">المشكلة التي يحلها المشروع</label>
            <textarea id="fProblem" class="form-control" rows="3" placeholder="ما التحدي أو المشكلة التي يعالجها مشروعك؟"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">السوق المستهدف</label>
            <textarea id="fMarket" class="form-control" rows="2" placeholder="من هم عملاؤك؟ وما حجم السوق المستهدف؟"></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">حجم الفريق</label>
              <input type="number" id="fTeam" class="form-control" value="1" min="1">
            </div>
            <div class="form-group">
              <label class="form-label">التمويل المطلوب (مليون ل.س)</label>
              <input type="number" id="fFunding" class="form-control" placeholder="0" min="0">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">الميزة التنافسية</label>
            <textarea id="fAdvantage" class="form-control" rows="2" placeholder="ما الذي يميز مشروعك عن البدائل الموجودة؟"></textarea>
          </div>
          <label class="check-row">
            <input type="checkbox" id="fProto">
            <span><label>لدي نموذج أولي أو MVP</label></span>
          </label>
          <label class="check-row">
            <input type="checkbox" id="fRevenue">
            <span><label>المشروع يحقق إيرادات حالياً</label></span>
          </label>
          <div class="form-actions">
            <button class="btn-hub-ghost" onclick="goStep(0)"><i class="bi bi-arrow-right-circle-fill"></i> السابق</button>
            <button class="btn-hub" onclick="goStep(2)">التالي <i class="bi bi-arrow-left-circle-fill"></i></button>
          </div>
        </div>

        <!-- Step 3: Confirm & Submit -->
        <div class="form-section" id="fs2">
          <div style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);border:1px solid var(--hub-border);border-radius:16px;padding:20px 22px;margin-bottom:20px">
            <div style="font-weight:800;font-size:.92rem;color:var(--hub-dark);margin-bottom:14px"><i class="bi bi-check2-circle" style="color:var(--hub-primary)"></i> مراجعة الطلب</div>
            <div style="display:grid;gap:8px">
              <div style="display:flex;justify-content:space-between;font-size:.87rem"><span style="color:var(--hub-muted);font-weight:700">اسم المشروع</span><span id="rv-name" style="font-weight:800;color:var(--hub-dark)">—</span></div>
              <div style="display:flex;justify-content:space-between;font-size:.87rem"><span style="color:var(--hub-muted);font-weight:700">القطاع</span><span id="rv-sector" style="font-weight:800;color:var(--hub-dark)">—</span></div>
              <div style="display:flex;justify-content:space-between;font-size:.87rem"><span style="color:var(--hub-muted);font-weight:700">مرحلة المشروع</span><span id="rv-stage" style="font-weight:800;color:var(--hub-dark)">—</span></div>
              <div style="display:flex;justify-content:space-between;font-size:.87rem"><span style="color:var(--hub-muted);font-weight:700">حجم الفريق</span><span id="rv-team" style="font-weight:800;color:var(--hub-dark)">—</span></div>
            </div>
          </div>

          <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:14px 18px;font-size:.85rem;color:#14532d;line-height:1.7;margin-bottom:20px">
            <i class="bi bi-info-circle-fill" style="margin-left:6px"></i>
            بعد إرسال الطلب، ستتولى الهيئة مراجعته وستُبلَّغ بكل خطوة مباشرةً عبر مساحتك هنا.
          </div>

          <div class="form-actions">
            <button class="btn-hub-ghost" onclick="goStep(1)"><i class="bi bi-arrow-right-circle-fill"></i> السابق</button>
            <button class="btn-hub" id="submitBtn" onclick="submitApplication()">
              <i class="bi bi-cloud-upload-fill"></i> إرسال الطلب للهيئة
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- STATE 1: Pending / Under Review -->
    <div id="statePending" style="display:none">
      <div class="hub-card">
        <div class="card-hd"><i class="bi bi-hourglass-split"></i> حالة طلبك</div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
          <span id="pendingBadge" class="badge-hub b-pending">—</span>
          <span id="pendingProjectName" style="font-weight:800;color:var(--hub-dark);font-size:.95rem">—</span>
        </div>
        <ul class="tl" id="pendingTimeline"></ul>
        <div id="reviewerNoteBox" style="display:none;background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;padding:14px 18px;font-size:.87rem;color:#7c2d12;line-height:1.7;margin-top:14px">
          <strong><i class="bi bi-chat-left-text-fill"></i> ملاحظات المراجع:</strong>
          <div id="reviewerNoteText" style="margin-top:6px"></div>
        </div>
      </div>
    </div>

    <!-- STATE 2: Accepted — Cloud Workspace -->
    <div id="stateCloud" style="display:none">

      <!-- Project header -->
      <div class="hub-card" style="background:linear-gradient(135deg,#1e1b4b,#4c1d95);color:#fff;border:none">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap">
          <div>
            <div style="font-size:.78rem;opacity:.65;font-weight:700;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">مشروعك المحتضن</div>
            <div style="font-size:1.2rem;font-weight:900;margin-bottom:4px" id="cloudProjName">—</div>
            <div id="cloudProjStage" style="background:rgba(255,255,255,.12);display:inline-block;padding:4px 14px;border-radius:20px;font-size:.8rem;font-weight:700">—</div>
          </div>
          <div style="text-align:center">
            <div style="font-size:2rem;font-weight:900" id="cloudDays">—</div>
            <div style="font-size:.75rem;opacity:.65;font-weight:700">يوماً في الاحتضان</div>
          </div>
        </div>
        <div class="progress-bar-wrap" style="margin-top:20px;background:rgba(255,255,255,.15)">
          <div class="progress-bar-fill" id="cloudProgress" style="width:0%"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.75rem;opacity:.65;margin-top:6px">
          <span>بذرة</span><span>مبكر</span><span>نمو</span><span>تخرج</span>
        </div>
      </div>

      <!-- Mentor -->
      <div class="hub-card" id="mentorCard" style="display:none">
        <div class="card-hd"><i class="bi bi-person-badge-fill"></i> مرشدك</div>
        <div class="mentor-card">
          <div class="mentor-avatar"><i class="bi bi-person-fill"></i></div>
          <div>
            <div class="mentor-name" id="mentorName">—</div>
            <div class="mentor-role" id="mentorRole">—</div>
          </div>
        </div>
      </div>

      <!-- Cloud Grid -->
      <div class="cloud-grid" style="margin-bottom:16px">
        <div class="hub-card" style="margin:0">
          <div class="card-hd"><i class="bi bi-calendar-check-fill"></i> الجلسات القادمة</div>
          <div id="sessionsList">
            <div style="text-align:center;padding:20px;color:var(--hub-muted);font-size:.87rem"><i class="bi bi-calendar3" style="display:block;font-size:1.8rem;margin-bottom:8px;opacity:.3"></i>لا توجد جلسات مجدولة</div>
          </div>
        </div>

        <div class="hub-card" style="margin:0">
          <div class="card-hd"><i class="bi bi-flag-fill"></i> الإنجازات</div>
          <div class="milestone-list" id="milestonesList">
            <div style="text-align:center;padding:20px;color:var(--hub-muted);font-size:.87rem"><i class="bi bi-trophy" style="display:block;font-size:1.8rem;margin-bottom:8px;opacity:.3"></i>لا توجد إنجازات بعد</div>
          </div>
        </div>
      </div>

      <!-- Documents -->
      <div class="hub-card">
        <div class="card-hd"><i class="bi bi-file-earmark-check-fill"></i> الوثائق المطلوبة</div>
        <div id="docsList">
          <div style="color:var(--hub-muted);font-size:.87rem;text-align:center;padding:14px">لا توجد وثائق مطلوبة حالياً</div>
        </div>
      </div>

      <!-- Quick links -->
      <div class="hub-card">
        <div class="card-hd"><i class="bi bi-grid-fill"></i> الخدمات المتاحة</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px">
          <a href="project-mentoring.php" class="btn-hub-ghost" style="justify-content:center;text-align:center;flex-direction:column;gap:6px;padding:16px 12px">
            <i class="bi bi-person-lines-fill" style="font-size:1.4rem"></i>
            <span style="font-size:.82rem">جلسات الإرشاد</span>
          </a>
          <a href="incubation-reports.php" class="btn-hub-ghost" style="justify-content:center;text-align:center;flex-direction:column;gap:6px;padding:16px 12px">
            <i class="bi bi-bar-chart-fill" style="font-size:1.4rem"></i>
            <span style="font-size:.82rem">التقارير</span>
          </a>
          <a href="success-stories.php" class="btn-hub-ghost" style="justify-content:center;text-align:center;flex-direction:column;gap:6px;padding:16px 12px">
            <i class="bi bi-star-fill" style="font-size:1.4rem"></i>
            <span style="font-size:.82rem">قصص النجاح</span>
          </a>
        </div>
      </div>
    </div>

    <!-- STATE 3: Rejected -->
    <div id="stateRejected" style="display:none">
      <div class="hub-card">
        <div class="reject-box">
          <i class="reject-icon bi bi-x-circle-fill"></i>
          <h2 style="font-size:1.15rem;font-weight:900;color:#1e1b4b;margin-bottom:6px">لم يتم قبول طلبك في هذه الدورة</h2>
          <p style="color:var(--hub-muted);font-size:.9rem">يمكنك مراجعة الملاحظات وتطوير مشروعك والتقديم مرة أخرى.</p>
        </div>
        <div class="reviewer-note" id="rejectedNote" style="display:none">
          <strong><i class="bi bi-chat-left-text-fill"></i> ملاحظات فريق التقييم:</strong>
          <div id="rejectedNoteText" style="margin-top:8px"></div>
        </div>
        <div style="text-align:center;margin-top:20px">
          <button class="btn-hub" onclick="resetAndApply()">
            <i class="bi bi-arrow-repeat"></i> التقديم مرة أخرى
          </button>
        </div>
      </div>
    </div>

  </div><!-- /hubContent -->

</div>
</main>

<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {

  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const e     = window.APP_HELPERS.e;

  const SECTOR_LBL = { tech:'تقنية ورقمية', industrial:'صناعي', agricultural:'زراعي', services:'خدمات', creative:'إبداعي', health:'صحة', education:'تعليم' };
  const STAGE_LBL  = { idea:'فكرة', pre_seed:'نموذج أولي', seed:'إطلاق — بلا إيرادات', early:'إطلاق — إيرادات', growth:'نمو' };
  const PROJECT_STAGE = { seed:'بذرة', early:'مبكر', growth:'نمو', exit:'تخرج' };
  const STAGE_PROGRESS = { seed:20, early:50, growth:75, exit:100 };

  let currentStep = 0;

  // ── Load ──────────────────────────────────────────
  async function load() {
    try {
      const r = await fetch(`${base}/incubation/my-applications`, {
        headers: { Authorization: `Bearer ${token()}` }
      });
      const apps = await r.json() ?? [];

      document.getElementById('hubLoading').style.display  = 'none';
      document.getElementById('hubContent').style.display  = 'block';

      if (!apps.length) {
        showState('new');
        return;
      }

      const app = apps[0];
      showStageTrack(app.status);
      document.getElementById('heroStatusPill').style.display = 'flex';

      if (app.status === 'accepted') {
        showCloud(app);
        document.getElementById('heroStatusText').textContent = 'مشروعك قيد الاحتضان';
      } else if (app.status === 'rejected') {
        showRejected(app);
        document.getElementById('heroStatusText').textContent = 'طلب مرفوض';
      } else {
        showPending(app);
        document.getElementById('heroStatusText').textContent = 'طلبك قيد المراجعة';
      }
    } catch {
      document.getElementById('hubLoading').innerHTML =
        '<i class="bi bi-wifi-off" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.4"></i>تعذّر تحميل البيانات';
    }
  }

  function showState(name) {
    ['New','Pending','Cloud','Rejected'].forEach(s => {
      document.getElementById('state' + s).style.display = s.toLowerCase() === name ? 'block' : 'none';
    });
  }

  // ── Stage Track ───────────────────────────────────
  function showStageTrack(status) {
    document.getElementById('stageTrack').style.display = 'block';
    const map = { pending:0, under_review:1, interview:2, accepted:3, rejected:1 };
    const active = status === 'accepted' ? 4 : (map[status] ?? 0);
    for (let i = 0; i <= 4; i++) {
      const el = document.getElementById('stg' + i);
      el.classList.remove('done','active');
      if (i < active)      el.classList.add('done');
      else if (i === active) el.classList.add('active');
    }
  }

  // ── Pending ───────────────────────────────────────
  function showPending(app) {
    showState('pending');
    const SM = {
      pending:      { lbl:'بانتظار المراجعة', cls:'b-pending' },
      under_review: { lbl:'قيد المراجعة',     cls:'b-review'  },
    };
    const sm = SM[app.status] ?? { lbl: app.status, cls:'b-pending' };

    document.getElementById('pendingBadge').className = `badge-hub ${sm.cls}`;
    document.getElementById('pendingBadge').textContent = sm.lbl;
    document.getElementById('pendingProjectName').textContent = app.project_name ?? '—';

    const tl = document.getElementById('pendingTimeline');
    const steps = [
      { lbl:'تم إرسال الطلب للهيئة', sub: app.created_at?.slice(0,10) ?? '—', done: true, icon:'bi-send-fill' },
      { lbl:'قيد المراجعة من فريق التقييم', sub:'الهيئة تراجع طلبك', done: app.status !== 'pending', icon:'bi-search', current: app.status === 'pending' },
      { lbl:'مقابلة تقييمية', sub:'ستُبلَّغ بموعدها', done: false, icon:'bi-camera-video-fill' },
      { lbl:'القرار النهائي', sub:'القبول أو الرفض مع الملاحظات', done: false, icon:'bi-patch-check-fill' },
    ];
    tl.innerHTML = steps.map(s => `
      <li class="tl-item">
        <div class="tl-dot ${s.done ? 'done' : s.current ? 'current' : ''}">
          <i class="bi ${s.icon}"></i>
        </div>
        <div class="tl-body">
          <div class="tl-title">${e(s.lbl)}</div>
          <div class="tl-sub">${e(s.sub)}</div>
        </div>
      </li>`).join('');

    if (app.reviewer_notes) {
      document.getElementById('reviewerNoteBox').style.display = 'block';
      document.getElementById('reviewerNoteText').textContent = app.reviewer_notes;
    }
  }

  // ── Cloud Workspace ───────────────────────────────
  function showCloud(app) {
    showState('cloud');
    const proj = app.project ?? {};

    document.getElementById('cloudProjName').textContent  = e(app.project_name ?? '—');
    document.getElementById('cloudProjStage').textContent = PROJECT_STAGE[proj.stage] ?? proj.stage ?? '—';

    if (proj.start_date) {
      const days = Math.floor((Date.now() - new Date(proj.start_date)) / 86400000);
      document.getElementById('cloudDays').textContent = days > 0 ? days : 0;
    }

    const pct = STAGE_PROGRESS[proj.stage] ?? 0;
    document.getElementById('cloudProgress').style.width = pct + '%';

    if (proj.mentor) {
      document.getElementById('mentorCard').style.display = 'block';
      document.getElementById('mentorName').textContent = e(proj.mentor.name ?? '—');
      document.getElementById('mentorRole').textContent = e(proj.mentor.specialization ?? 'مرشد معتمد');
    }

    if (proj.sessions?.length) {
      document.getElementById('sessionsList').innerHTML = proj.sessions.map(s => {
        const d = new Date(s.date ?? s.scheduled_at);
        return `<div class="session-item">
          <div class="session-date-box">
            <div class="day">${d.getDate()}</div>
            <div class="mon">${d.toLocaleString('ar-SY',{month:'short'})}</div>
          </div>
          <div><div style="font-weight:800;font-size:.88rem;color:var(--hub-dark)">${e(s.topic ?? s.title ?? 'جلسة إرشاد')}</div>
          <div style="font-size:.78rem;color:var(--hub-muted);margin-top:2px">${e(s.type ?? 'عن بُعد')}</div></div>
        </div>`;
      }).join('');
    }

    if (proj.milestones?.length) {
      document.getElementById('milestonesList').innerHTML = `<div class="milestone-list">${proj.milestones.map(m => `
        <div class="milestone-item">
          <div class="milestone-check ${m.completed ? 'done' : 'todo'}">
            <i class="bi bi-${m.completed ? 'check-lg' : 'circle'}"></i>
          </div>
          <div class="milestone-txt">${e(m.title ?? m.name)}</div>
        </div>`).join('')}</div>`;
    }

    if (proj.documents?.length) {
      document.getElementById('docsList').innerHTML = proj.documents.map(d => {
        const cls = d.status === 'approved' ? 'doc-ok' : d.status === 'pending' ? 'doc-pending' : 'doc-missing';
        const lbl = d.status === 'approved' ? 'مقبول' : d.status === 'pending' ? 'بالمراجعة' : 'مطلوب';
        return `<div class="doc-item">
          <div class="doc-lbl"><i class="bi bi-file-earmark-text-fill" style="color:var(--hub-primary);margin-left:6px"></i>${e(d.name ?? d.title)}</div>
          <span class="doc-status ${cls}">${lbl}</span>
        </div>`;
      }).join('');
    }
  }

  // ── Rejected ──────────────────────────────────────
  function showRejected(app) {
    showState('rejected');
    if (app.reviewer_notes) {
      document.getElementById('rejectedNote').style.display = 'block';
      document.getElementById('rejectedNoteText').textContent = app.reviewer_notes;
    }
  }

  // ── Form logic ────────────────────────────────────
  window.showForm = function () {
    document.getElementById('applyForm').style.display = 'block';
    document.getElementById('applyForm').scrollIntoView({ behavior:'smooth', block:'start' });
  };

  window.goStep = function (n) {
    // Validate current step
    if (n > currentStep) {
      if (currentStep === 0) {
        if (!document.getElementById('fName').value.trim()) return alert('أدخل اسم المشروع');
        if (!document.getElementById('fSector').value)      return alert('اختر قطاع المشروع');
        if (!document.getElementById('fDesc').value.trim()) return alert('أدخل وصف المشروع');
      }
      if (currentStep === 1 && n === 2) fillReview();
    }

    document.getElementById('fs' + currentStep).classList.remove('active');
    document.getElementById('seg' + currentStep).classList.remove('active');
    document.getElementById('seg' + currentStep).classList.add('done');
    currentStep = n;
    document.getElementById('fs' + n).classList.add('active');
    document.getElementById('seg' + n).classList.add('active');
  };

  function fillReview() {
    const SECTOR_MAP = { tech:'تقنية', industrial:'صناعي', agricultural:'زراعي', services:'خدمات', creative:'إبداعي', health:'صحة', education:'تعليم' };
    const STAGE_MAP  = { idea:'فكرة', pre_seed:'نموذج أولي', seed:'إطلاق', early:'إطلاق+إيرادات', growth:'نمو' };
    document.getElementById('rv-name').textContent   = document.getElementById('fName').value || '—';
    document.getElementById('rv-sector').textContent = SECTOR_MAP[document.getElementById('fSector').value] || '—';
    document.getElementById('rv-stage').textContent  = STAGE_MAP[document.getElementById('fStage').value]  || '—';
    document.getElementById('rv-team').textContent   = (document.getElementById('fTeam').value || '1') + ' أفراد';
  }

  window.submitApplication = async function () {
    const btn = document.getElementById('submitBtn');
    const alert = document.getElementById('hubAlert');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite"></i> جارٍ الإرسال...';
    alert.style.display = 'none';

    try {
      const body = {
        project_name:   document.getElementById('fName').value.trim(),
        sector:         document.getElementById('fSector').value,
        business_stage: document.getElementById('fStage').value,
        description:    document.getElementById('fDesc').value.trim(),
        problem:        document.getElementById('fProblem').value.trim(),
        target_market:  document.getElementById('fMarket').value.trim(),
        team_size:      parseInt(document.getElementById('fTeam').value) || 1,
        funding_needed: parseFloat(document.getElementById('fFunding').value) || 0,
        competitive_advantage: document.getElementById('fAdvantage').value.trim(),
        has_prototype:  document.getElementById('fProto').checked,
        has_revenue:    document.getElementById('fRevenue').checked,
      };

      const r = await fetch(`${base}/incubation/applications`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', Authorization:`Bearer ${token()}` },
        body: JSON.stringify(body)
      });

      if (r.ok) {
        alert.className = 'hub-alert success';
        alert.textContent = '✓ تم إرسال طلبك بنجاح! ستتولى الهيئة المراجعة وستُبلَّغ بكل تحديث.';
        alert.style.display = 'block';
        setTimeout(() => load(), 2000);
      } else {
        const j = await r.json().catch(() => ({}));
        throw new Error(j.message ?? 'حدث خطأ');
      }
    } catch (err) {
      alert.className = 'hub-alert error';
      alert.textContent = err.message || 'تعذّر إرسال الطلب، حاول مجدداً.';
      alert.style.display = 'block';
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-cloud-upload-fill"></i> إرسال الطلب للهيئة';
    }
  };

  window.resetAndApply = function () {
    showState('new');
    document.getElementById('stageTrack').style.display = 'none';
    document.getElementById('heroStatusPill').style.display = 'none';
  };

  load();
});
</script>
</body>
</html>
