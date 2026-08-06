<?php
$basePath  = '../../';
$pageTitle = 'لوحة تحكم مدير ريادة الأعمال';
$activePage= 'entrepreneur_manager';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{
      --c-primary:#d97706;--c-accent:#f59e0b;--c-dark:#92400e;
      --c-soft:#fffbeb;--c-border:rgba(217,119,6,.15);
      --c-text:#1c1917;--c-muted:#78716c;
      --c-shadow:0 8px 24px rgba(217,119,6,.1);
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{background:linear-gradient(160deg,#fffbeb,#fef3c7 60%,#fde68a 100%);font-family:'Segoe UI',Tahoma,sans-serif;color:var(--c-text);min-height:100vh;}
    a{text-decoration:none;}
    .pw{max-width:1280px;margin:auto;padding:20px 14px 60px;}

    /* ── Hero ── */
    .hero{background:linear-gradient(135deg,#451a03,#92400e,#d97706);color:#fff;border-radius:22px;padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;overflow:hidden;}
    .hero::before{content:"🚀";position:absolute;font-size:16rem;opacity:.04;top:-50px;left:-20px;line-height:1;pointer-events:none;}
    .hero-text h1{font-size:1.5rem;font-weight:900;margin-bottom:4px;}
    .hero-text p{opacity:.8;font-size:.9rem;}
    .hero-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:5px 14px;font-size:.8rem;font-weight:700;margin-bottom:10px;}
    .hero-actions{display:flex;gap:10px;flex-wrap:wrap;}
    .btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:12px;border:none;font-size:.88rem;font-weight:700;cursor:pointer;transition:all .18s;}
    .btn-white{background:#fff;color:var(--c-dark);}
    .btn-white:hover{background:#fef3c7;}
    .btn-outline{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.3);}
    .btn-outline:hover{background:rgba(255,255,255,.2);}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}
    .btn-primary:hover{opacity:.9;}
    .btn-success{background:#dcfce7;color:#15803d;}
    .btn-danger{background:#fee2e2;color:#dc2626;}
    .btn-info{background:#e0f2fe;color:#0369a1;}
    .btn-warn{background:#fef3c7;color:#b45309;}
    .btn-sm{padding:6px 12px;font-size:.8rem;}

    /* ── KPI strip ── */
    .kpi-strip{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:24px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:18px 16px;text-align:center;box-shadow:var(--c-shadow);transition:transform .15s;}
    .kpi:hover{transform:translateY(-2px);}
    .kpi-icon{font-size:1.6rem;margin-bottom:6px;}
    .kpi-val{font-size:1.8rem;font-weight:900;color:var(--c-primary);line-height:1;}
    .kpi-lbl{font-size:.76rem;color:var(--c-muted);font-weight:700;margin-top:4px;}
    .kpi.green .kpi-val{color:#15803d;}
    .kpi.blue .kpi-val{color:#0369a1;}
    .kpi.red .kpi-val{color:#dc2626;}
    .kpi.purple .kpi-val{color:#7c3aed;}

    /* ── Layout ── */
    .main-grid{display:grid;grid-template-columns:1fr 320px;gap:18px;}
    @media(max-width:900px){.main-grid{grid-template-columns:1fr;}}

    /* ── Card ── */
    .card{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:20px;box-shadow:var(--c-shadow);margin-bottom:18px;}
    .card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;}
    .card-title{font-size:1rem;font-weight:800;color:var(--c-text);display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}

    /* ── Filters ── */
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;}
    .fi,.fs{padding:9px 13px;border:1.5px solid var(--c-border);border-radius:11px;font-size:.85rem;color:var(--c-text);background:#fff;transition:border-color .15s;}
    .fi:focus,.fs:focus{outline:none;border-color:var(--c-primary);}
    .fi{flex:1;min-width:160px;}

    /* ── Table ── */
    .tbl{width:100%;border-collapse:collapse;font-size:.86rem;}
    .tbl th{background:var(--c-soft);color:var(--c-primary);font-weight:800;padding:10px 12px;text-align:right;border-bottom:2px solid var(--c-border);}
    .tbl td{padding:10px 12px;border-bottom:1px solid #f5f5f4;vertical-align:middle;}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:#fffbeb;}
    .tbl-wrap{overflow-x:auto;border-radius:12px;border:1px solid var(--c-border);}

    /* ── Badges ── */
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;}
    .badge-draft{background:#f5f5f4;color:#78716c;}
    .badge-submitted{background:#fef3c7;color:#b45309;}
    .badge-under_review{background:#e0f2fe;color:#0369a1;}
    .badge-approved{background:#dcfce7;color:#15803d;}
    .badge-rejected{background:#fee2e2;color:#dc2626;}

    /* ── Pagination ── */
    .pagination{display:flex;gap:6px;justify-content:center;margin-top:14px;flex-wrap:wrap;}
    .page-btn{min-width:34px;height:34px;border-radius:9px;border:1.5px solid var(--c-border);background:#fff;font-size:.83rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--c-muted);transition:all .15s;}
    .page-btn.active,.page-btn:hover{background:var(--c-primary);border-color:var(--c-primary);color:#fff;}

    /* ── Sidebar ── */
    .stat-box{background:var(--c-soft);border:1px solid var(--c-border);border-radius:14px;padding:14px;margin-bottom:14px;}
    .stat-row{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px dashed var(--c-border);font-size:.84rem;}
    .stat-row:last-child{border:none;}
    .stat-row-label{color:var(--c-muted);font-weight:600;}
    .stat-row-val{font-weight:800;color:var(--c-text);}

    /* ── Field chart bars ── */
    .field-bar{display:flex;align-items:center;gap:8px;margin:6px 0;font-size:.82rem;}
    .field-bar-label{min-width:120px;color:var(--c-muted);font-weight:600;text-align:right;}
    .field-bar-track{flex:1;background:#f5f5f4;border-radius:20px;height:8px;overflow:hidden;}
    .field-bar-fill{height:100%;border-radius:20px;background:linear-gradient(90deg,var(--c-primary),var(--c-accent));transition:width .5s;}
    .field-bar-cnt{font-weight:800;color:var(--c-primary);min-width:28px;text-align:left;}

    /* ── Donut (fake) ── */
    .status-chart{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px;}
    .sc-item{border-radius:12px;padding:10px 12px;text-align:center;}
    .sc-item .sc-val{font-size:1.3rem;font-weight:900;}
    .sc-item .sc-lbl{font-size:.73rem;font-weight:700;margin-top:2px;}

    /* ── Modal ── */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;display:none;align-items:center;justify-content:center;padding:16px;}
    .modal-overlay.open{display:flex;}
    .modal-box{background:#fff;border-radius:22px;width:100%;max-width:780px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);}
    .modal-head{position:sticky;top:0;background:#fff;padding:18px 22px;border-bottom:1px solid #f5f5f4;display:flex;align-items:center;justify-content:space-between;z-index:1;border-radius:22px 22px 0 0;}
    .modal-head h3{font-size:1.05rem;font-weight:900;color:var(--c-text);}
    .modal-close{background:none;border:none;font-size:1.3rem;color:var(--c-muted);cursor:pointer;padding:4px 8px;border-radius:8px;}
    .modal-close:hover{background:#f5f5f4;}
    .modal-body{padding:22px;}

    /* ── Profile view ── */
    .profile-hero{background:linear-gradient(135deg,var(--c-soft),#fde68a);border-radius:16px;padding:20px;margin-bottom:18px;display:flex;align-items:center;gap:18px;}
    .profile-avatar{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--c-primary),var(--c-accent));display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;flex-shrink:0;}
    .profile-name{font-size:1.2rem;font-weight:900;}
    .profile-meta{font-size:.83rem;color:var(--c-muted);margin-top:4px;}
    .profile-section{margin-bottom:16px;}
    .profile-section-title{font-size:.82rem;font-weight:800;color:var(--c-primary);background:var(--c-soft);padding:6px 12px;border-radius:8px;margin-bottom:10px;display:flex;align-items:center;gap:6px;}
    .profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    @media(max-width:500px){.profile-grid{grid-template-columns:1fr;}}
    .pf-item{background:#fafaf9;border-radius:10px;padding:10px 12px;}
    .pf-item-label{font-size:.72rem;color:var(--c-muted);font-weight:700;margin-bottom:3px;}
    .pf-item-val{font-size:.88rem;font-weight:700;color:var(--c-text);}
    .tag{display:inline-flex;align-items:center;gap:4px;background:var(--c-soft);color:var(--c-primary);border-radius:20px;padding:3px 10px;font-size:.75rem;font-weight:700;margin:2px;}

    /* ── Review form ── */
    .review-form{background:var(--c-soft);border-radius:14px;padding:16px;margin-top:14px;}
    .review-form label{font-size:.82rem;font-weight:700;display:block;margin-bottom:6px;}
    .review-form select,.review-form textarea{width:100%;padding:9px 12px;border:1.5px solid var(--c-border);border-radius:10px;font-size:.85rem;margin-bottom:10px;background:#fff;}
    .review-form select:focus,.review-form textarea:focus{outline:none;border-color:var(--c-primary);}

    .spin{display:inline-block;width:16px;height:16px;border:2.5px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg);}}
    .loading{text-align:center;padding:30px;color:var(--c-muted);}
    .empty{text-align:center;padding:40px 20px;color:var(--c-muted);}
    .empty i{font-size:2.5rem;opacity:.3;display:block;margin-bottom:10px;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-text">
      <div class="hero-badge"><i class="bi bi-rocket-takeoff-fill"></i> لوحة ريادة الأعمال</div>
      <h1 id="heroGreet">مرحباً...</h1>
      <p>إدارة ومتابعة استبيانات رواد الأعمال والمشاريع التقنية الابتكارية</p>
    </div>
    <div class="hero-actions">
      <a href="<?php echo $basePath;?>services/incubation/entrepreneur-profile.php" class="btn btn-white">
        <i class="bi bi-plus-circle-fill"></i> استبيان جديد
      </a>
      <a href="<?php echo $basePath;?>services/incubation/success-stories.php" class="btn btn-outline">
        <i class="bi bi-star-fill"></i> قصص النجاح
      </a>
    </div>
  </div>

  <!-- KPIs -->
  <div class="kpi-strip" id="kpiStrip">
    <div class="kpi"><div class="kpi-icon">📋</div><div class="kpi-val" id="kTotal">—</div><div class="kpi-lbl">إجمالي الاستبيانات</div></div>
    <div class="kpi"><div class="kpi-icon">📤</div><div class="kpi-val" id="kSubmitted">—</div><div class="kpi-lbl">مُقدَّمة</div></div>
    <div class="kpi blue"><div class="kpi-icon">🔍</div><div class="kpi-val" id="kReview">—</div><div class="kpi-lbl">قيد المراجعة</div></div>
    <div class="kpi green"><div class="kpi-icon">✅</div><div class="kpi-val" id="kApproved">—</div><div class="kpi-lbl">مقبولة</div></div>
    <div class="kpi red"><div class="kpi-icon">❌</div><div class="kpi-val" id="kRejected">—</div><div class="kpi-lbl">مرفوضة</div></div>
    <div class="kpi purple"><div class="kpi-icon">💰</div><div class="kpi-val" id="kInvestment">—</div><div class="kpi-lbl">يبحثون عن استثمار</div></div>
  </div>

  <div class="main-grid">
    <!-- LEFT: profiles list -->
    <div>
      <div class="card">
        <div class="card-head">
          <div class="card-title"><i class="bi bi-people-fill"></i> استبيانات رواد الأعمال</div>
          <button class="btn btn-primary btn-sm" onclick="exportList()"><i class="bi bi-download"></i> تصدير</button>
        </div>

        <!-- Filters -->
        <div class="filters">
          <input type="text" id="fSearch" class="fi" placeholder="🔍  بحث بالاسم أو المشروع..." oninput="debouncedLoad()">
          <select id="fStatus" class="fs" onchange="loadProfiles(1)">
            <option value="">كل الحالات</option>
            <option value="draft">مسودة</option>
            <option value="submitted">مُقدَّمة</option>
            <option value="under_review">قيد المراجعة</option>
            <option value="approved">مقبولة</option>
            <option value="rejected">مرفوضة</option>
          </select>
          <select id="fField" class="fs" onchange="loadProfiles(1)">
            <option value="">كل المجالات</option>
            <option value="ai">الذكاء الاصطناعي</option>
            <option value="mobile_apps">التطبيقات الذكية</option>
            <option value="software">البرمجيات</option>
            <option value="games">الألعاب</option>
            <option value="ecommerce">التجارة الإلكترونية</option>
            <option value="cybersecurity">الأمن السيبراني</option>
            <option value="fintech">FinTech</option>
            <option value="elearning">التعليم الإلكتروني</option>
            <option value="digital_health">الصحة الرقمية</option>
            <option value="iot">IoT</option>
            <option value="blockchain">Blockchain</option>
            <option value="other">أخرى</option>
          </select>
          <select id="fGov" class="fs" onchange="loadProfiles(1)">
            <option value="">كل المحافظات</option>
            <?php foreach(['دمشق','ريف دمشق','حلب','حمص','حماة','اللاذقية','طرطوس','درعا','السويداء','القنيطرة','دير الزور','الرقة','الحسكة','إدلب'] as $g):?>
            <option value="<?php echo $g;?>"><?php echo $g;?></option>
            <?php endforeach;?>
          </select>
        </div>

        <div class="tbl-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>#</th>
                <th>رائد الأعمال</th>
                <th>المشروع</th>
                <th>المجال</th>
                <th>المحافظة</th>
                <th>الجاهزية</th>
                <th>الاستثمار</th>
                <th>الحالة</th>
                <th>إجراءات</th>
              </tr>
            </thead>
            <tbody id="profilesTbody">
              <tr><td colspan="9" class="loading"><div class="spin" style="border-top-color:var(--c-primary);border-color:rgba(217,119,6,.3);width:28px;height:28px;margin:auto"></div></td></tr>
            </tbody>
          </table>
        </div>
        <div class="pagination" id="paginationEl"></div>
      </div>
    </div>

    <!-- RIGHT: sidebar -->
    <div>
      <!-- Status chart -->
      <div class="card">
        <div class="card-title" style="margin-bottom:12px"><i class="bi bi-pie-chart-fill"></i> توزيع الحالات</div>
        <div class="status-chart" id="statusChart">
          <div class="sc-item" style="background:#fef3c7"><div class="sc-val" style="color:#b45309" id="sc_submitted">—</div><div class="sc-lbl" style="color:#92400e">مُقدَّمة</div></div>
          <div class="sc-item" style="background:#e0f2fe"><div class="sc-val" style="color:#0369a1" id="sc_review">—</div><div class="sc-lbl" style="color:#075985">قيد المراجعة</div></div>
          <div class="sc-item" style="background:#dcfce7"><div class="sc-val" style="color:#15803d" id="sc_approved">—</div><div class="sc-lbl" style="color:#14532d">مقبولة</div></div>
          <div class="sc-item" style="background:#fee2e2"><div class="sc-val" style="color:#dc2626" id="sc_rejected">—</div><div class="sc-lbl" style="color:#991b1b">مرفوضة</div></div>
        </div>
      </div>

      <!-- By field -->
      <div class="card">
        <div class="card-title" style="margin-bottom:12px"><i class="bi bi-bar-chart-fill"></i> توزيع حسب المجال</div>
        <div id="fieldBars"><div class="loading">جاري التحميل...</div></div>
      </div>

      <!-- Quick actions -->
      <div class="card">
        <div class="card-title" style="margin-bottom:12px"><i class="bi bi-lightning-fill"></i> إجراءات سريعة</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <button class="btn btn-warn" style="justify-content:flex-start" onclick="filterAndLoad('submitted')">
            <i class="bi bi-inbox-fill"></i> عرض المُقدَّمة حديثاً
          </button>
          <button class="btn btn-info" style="justify-content:flex-start" onclick="filterAndLoad('under_review')">
            <i class="bi bi-eye-fill"></i> عرض قيد المراجعة
          </button>
          <button class="btn btn-success" style="justify-content:flex-start" onclick="filterAndLoad('approved')">
            <i class="bi bi-check-circle-fill"></i> عرض المقبولة
          </button>
          <a href="<?php echo $basePath;?>services/incubation/entrepreneur-profile.php" class="btn btn-primary" style="justify-content:flex-start">
            <i class="bi bi-plus-circle-fill"></i> استبيان جديد
          </a>
        </div>
      </div>

      <!-- Seeking investment -->
      <div class="card" style="background:linear-gradient(135deg,#1c1917,#44403c);color:#fff;border:none">
        <div style="font-size:.85rem;font-weight:800;margin-bottom:8px;opacity:.8"><i class="bi bi-currency-dollar"></i> يبحثون عن استثمار</div>
        <div style="font-size:2.2rem;font-weight:900" id="investCount">—</div>
        <div style="font-size:.78rem;opacity:.6;margin-top:4px">مشروع من إجمالي الاستبيانات</div>
        <div style="margin-top:12px;background:rgba(255,255,255,.1);border-radius:10px;padding:10px 12px;font-size:.82rem;opacity:.8">
          <i class="bi bi-info-circle"></i> يمكن ربطهم بشبكة المستثمرين في SMEDA
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════
     PROFILE VIEW MODAL
══════════════════════════════════ -->
<div class="modal-overlay" id="viewModal">
  <div class="modal-box">
    <div class="modal-head">
      <h3 id="viewTitle">تفاصيل الاستبيان</h3>
      <button class="modal-close" onclick="closeModal('viewModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" id="viewBody">
      <div class="loading">جاري التحميل...</div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════
     REVIEW MODAL
══════════════════════════════════ -->
<div class="modal-overlay" id="reviewModal">
  <div class="modal-box" style="max-width:500px">
    <div class="modal-head">
      <h3>مراجعة الاستبيان</h3>
      <button class="modal-close" onclick="closeModal('reviewModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <div id="reviewProjName" style="font-weight:800;font-size:1rem;margin-bottom:16px;color:var(--c-primary)"></div>
      <div class="review-form">
        <label>الإجراء <span style="color:red">*</span></label>
        <select id="reviewStatus">
          <option value="under_review">قيد المراجعة</option>
          <option value="approved">قبول</option>
          <option value="rejected">رفض</option>
        </select>
        <label>ملاحظات المراجع</label>
        <textarea id="reviewNotes" rows="4" placeholder="سبب القبول أو الرفض، التوصيات..."></textarea>
        <button class="btn btn-primary" style="width:100%" onclick="submitReview()">
          <i class="bi bi-send-fill"></i> حفظ المراجعة
        </button>
      </div>
    </div>
  </div>
</div>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const user = window.AppAuth.getUser();
  const hasRole = window.AppAuth.hasRole.bind(window.AppAuth);

  /* Role guard */
  const allowed = [
    'entrepreneur_manager',
    'general_director',
    'deputy_general_director',
    'deputy_director',
    'admin',
    'super_admin',
    'system_admin',
    'branch_manager',
    'incubator_manager',
  ];
  if (!allowed.some(r => hasRole(r))) {
    document.body.innerHTML = '<div style="text-align:center;padding:80px;color:#dc2626;font-size:1.2rem;font-weight:800">⛔ غير مصرح لك بالوصول لهذه الصفحة</div>';
    return;
  }

  document.getElementById('heroGreet').textContent = `مرحباً، ${user?.name || 'مدير ريادة الأعمال'} 👋`;

  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H    = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, 'Content-Type':'application/json', Accept:'application/json' });

  const E = window.APP_HELPERS?.e || (s => String(s??'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])));

  let currentPage = 1;
  let reviewId    = null;

  const FIELD_LABELS = {
    ai:'الذكاء الاصطناعي',mobile_apps:'التطبيقات الذكية',software:'البرمجيات',
    games:'الألعاب',ecommerce:'التجارة الإلكترونية',cybersecurity:'الأمن السيبراني',
    fintech:'FinTech',elearning:'التعليم الإلكتروني',digital_health:'الصحة الرقمية',
    iot:'IoT',blockchain:'Blockchain',other:'أخرى'
  };
  const READINESS_LABELS = {
    idea:'فكرة فقط',mvp:'نموذج أولي',usable:'منتج قابل للاستخدام',
    has_users:'لديه مستخدمون',revenue:'يحقق إيرادات',scalable:'قابل للتوسع'
  };
  const STATUS_BADGE = {
    draft:'<span class="badge badge-draft">مسودة</span>',
    submitted:'<span class="badge badge-submitted">مُقدَّمة</span>',
    under_review:'<span class="badge badge-under_review">قيد المراجعة</span>',
    approved:'<span class="badge badge-approved">✅ مقبولة</span>',
    rejected:'<span class="badge badge-rejected">❌ مرفوضة</span>',
  };

  /* ── Load stats ── */
  async function loadStats() {
    const res = await fetch(`${BASE}/entrepreneur/profiles/stats`, { headers:H() });
    if (!res.ok) return;
    const s = await res.json();
    document.getElementById('kTotal').textContent      = s.total ?? 0;
    document.getElementById('kSubmitted').textContent  = s.submitted ?? 0;
    document.getElementById('kReview').textContent     = s.under_review ?? 0;
    document.getElementById('kApproved').textContent   = s.approved ?? 0;
    document.getElementById('kRejected').textContent   = s.rejected ?? 0;
    document.getElementById('kInvestment').textContent = s.seeking_investment ?? 0;
    document.getElementById('investCount').textContent = s.seeking_investment ?? 0;
    document.getElementById('sc_submitted').textContent = s.submitted ?? 0;
    document.getElementById('sc_review').textContent    = s.under_review ?? 0;
    document.getElementById('sc_approved').textContent  = s.approved ?? 0;
    document.getElementById('sc_rejected').textContent  = s.rejected ?? 0;

    /* Field bars */
    const byField = s.by_field || {};
    const maxVal  = Math.max(1, ...Object.values(byField));
    const barsEl  = document.getElementById('fieldBars');
    const entries = Object.entries(byField).sort((a,b)=>b[1]-a[1]);
    if (!entries.length) { barsEl.innerHTML='<div class="empty"><i class="bi bi-bar-chart"></i>لا توجد بيانات</div>'; return; }
    barsEl.innerHTML = entries.map(([field, cnt]) => `
      <div class="field-bar">
        <div class="field-bar-label">${FIELD_LABELS[field]||field}</div>
        <div class="field-bar-track"><div class="field-bar-fill" style="width:${Math.round(cnt/maxVal*100)}%"></div></div>
        <div class="field-bar-cnt">${cnt}</div>
      </div>`).join('');
  }

  /* ── Load profiles ── */
  window.loadProfiles = async function(page=1) {
    currentPage = page;
    const search = document.getElementById('fSearch').value.trim();
    const status = document.getElementById('fStatus').value;
    const field  = document.getElementById('fField').value;
    const gov    = document.getElementById('fGov').value;
    const params = new URLSearchParams({ page, per_page:12 });
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (field)  params.append('project_field', field);
    if (gov)    params.append('governorate', gov);

    document.getElementById('profilesTbody').innerHTML =
      '<tr><td colspan="9" class="loading"><div class="spin" style="border-top-color:var(--c-primary);border-color:rgba(217,119,6,.3);width:28px;height:28px;margin:auto"></div></td></tr>';

    const res = await fetch(`${BASE}/entrepreneur/profiles?${params}`, { headers:H() });
    if (!res.ok) return;
    const data = await res.json();
    renderProfiles(data.data || []);
    renderPagination(data);
  };

  function renderProfiles(items) {
    if (!items.length) {
      document.getElementById('profilesTbody').innerHTML =
        '<tr><td colspan="9" class="empty"><i class="bi bi-inbox"></i><br>لا توجد نتائج</td></tr>';
      return;
    }
    document.getElementById('profilesTbody').innerHTML = items.map(p => `
      <tr>
        <td style="color:var(--c-muted);font-size:.8rem">#${p.id}</td>
        <td>
          <div style="font-weight:700">${E(p.full_name)}</div>
          <div style="font-size:.75rem;color:var(--c-muted)">${E(p.user?.name||'')}</div>
        </td>
        <td style="font-weight:700;max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${E(p.project_name)}</td>
        <td><span class="tag">${FIELD_LABELS[p.project_field]||p.project_field||'—'}</span></td>
        <td style="font-size:.82rem">${E(p.governorate||'—')}</td>
        <td style="font-size:.8rem;color:var(--c-muted)">${READINESS_LABELS[p.readiness_stage]||'—'}</td>
        <td style="text-align:center">${p.seeking_investment ? '<i class="bi bi-check-circle-fill" style="color:#15803d"></i>' : '<i class="bi bi-dash-circle" style="color:#d1d5db"></i>'}</td>
        <td>${STATUS_BADGE[p.status]||p.status}</td>
        <td>
          <div style="display:flex;gap:5px;flex-wrap:nowrap">
            <button class="btn btn-info btn-sm" onclick="viewProfile(${p.id})"><i class="bi bi-eye-fill"></i></button>
            <button class="btn btn-warn btn-sm" onclick="openReview(${p.id},'${E(p.project_name)}')"><i class="bi bi-clipboard2-check-fill"></i></button>
          </div>
        </td>
      </tr>`).join('');
  }

  function renderPagination(data) {
    const el = document.getElementById('paginationEl');
    if (!data.last_page || data.last_page <= 1) { el.innerHTML=''; return; }
    let html = '';
    if (data.current_page > 1)
      html += `<button class="page-btn" onclick="loadProfiles(${data.current_page-1})"><i class="bi bi-chevron-right"></i></button>`;
    for (let i=Math.max(1,data.current_page-2); i<=Math.min(data.last_page,data.current_page+2); i++)
      html += `<button class="page-btn ${i===data.current_page?'active':''}" onclick="loadProfiles(${i})">${i}</button>`;
    if (data.current_page < data.last_page)
      html += `<button class="page-btn" onclick="loadProfiles(${data.current_page+1})"><i class="bi bi-chevron-left"></i></button>`;
    el.innerHTML = html;
  }

  /* ── View profile ── */
  window.viewProfile = async function(id) {
    document.getElementById('viewTitle').textContent = 'جاري التحميل...';
    document.getElementById('viewBody').innerHTML    = '<div class="loading">جاري التحميل...</div>';
    openModal('viewModal');

    const res = await fetch(`${BASE}/entrepreneur/profiles/${id}`, { headers:H() });
    if (!res.ok) { document.getElementById('viewBody').innerHTML='<div class="empty">فشل التحميل</div>'; return; }
    const p = await res.json();
    document.getElementById('viewTitle').textContent = p.project_name;

    const tags = (arr) => (arr||[]).map(v=>`<span class="tag">${E(v)}</span>`).join('') || '—';
    const yn   = (v) => v ? '<span class="badge badge-approved">نعم</span>' : '<span class="badge badge-rejected">لا</span>';
    const val  = (v) => E(v||'—');

    document.getElementById('viewBody').innerHTML = `
      <div class="profile-hero">
        <div class="profile-avatar">🚀</div>
        <div>
          <div class="profile-name">${E(p.full_name)}</div>
          <div class="profile-meta">${E(p.governorate||'')} | ${E(p.phone||'')} | ${E(p.email||'')}</div>
          <div style="margin-top:8px">${STATUS_BADGE[p.status]||''}</div>
        </div>
      </div>

      <!-- المشروع -->
      <div class="profile-section">
        <div class="profile-section-title"><i class="bi bi-rocket-fill"></i> معلومات المشروع</div>
        <div class="profile-grid">
          <div class="pf-item"><div class="pf-item-label">اسم المشروع</div><div class="pf-item-val">${val(p.project_name)}</div></div>
          <div class="pf-item"><div class="pf-item-label">المجال</div><div class="pf-item-val">${FIELD_LABELS[p.project_field]||val(p.project_field)}</div></div>
          <div class="pf-item"><div class="pf-item-label">سنة التأسيس</div><div class="pf-item-val">${val(p.founding_year)}</div></div>
          <div class="pf-item"><div class="pf-item-label">مرحلة الجاهزية</div><div class="pf-item-val">${READINESS_LABELS[p.readiness_stage]||val(p.readiness_stage)}</div></div>
          <div class="pf-item"><div class="pf-item-label">نموذج أولي</div><div class="pf-item-val">${yn(p.has_prototype)}</div></div>
          <div class="pf-item"><div class="pf-item-label">اختبار مع مستخدمين</div><div class="pf-item-val">${yn(p.tested_with_users)}</div></div>
        </div>
      </div>

      <!-- الملخص -->
      ${p.executive_summary ? `
      <div class="profile-section">
        <div class="profile-section-title"><i class="bi bi-file-text-fill"></i> الملخص التنفيذي</div>
        <div style="background:#fafaf9;border-radius:12px;padding:14px;font-size:.88rem;line-height:1.7;color:var(--c-text)">${E(p.executive_summary)}</div>
      </div>` : ''}

      <!-- المشكلة والحل -->
      ${p.problem_description ? `
      <div class="profile-section">
        <div class="profile-section-title"><i class="bi bi-patch-question-fill"></i> المشكلة والحل</div>
        <div class="profile-grid">
          <div class="pf-item" style="grid-column:1/-1"><div class="pf-item-label">المشكلة</div><div class="pf-item-val">${E(p.problem_description)}</div></div>
          ${p.target_customers?`<div class="pf-item"><div class="pf-item-label">الفئة المستهدفة</div><div class="pf-item-val">${E(p.target_customers)}</div></div>`:''}
          ${p.differentiation?`<div class="pf-item"><div class="pf-item-label">الميزة التنافسية</div><div class="pf-item-val">${E(p.differentiation)}</div></div>`:''}
        </div>
        ${(p.competitive_advantages||[]).length?`<div style="margin-top:8px">${tags(p.competitive_advantages)}</div>`:''}
      </div>` : ''}

      <!-- الفريق والتقنيات -->
      <div class="profile-section">
        <div class="profile-section-title"><i class="bi bi-people-fill"></i> الفريق والتقنيات</div>
        <div class="profile-grid">
          <div class="pf-item"><div class="pf-item-label">حجم الفريق</div><div class="pf-item-val">${val(p.team_size_range)}</div></div>
          <div class="pf-item"><div class="pf-item-label">أدوار الفريق</div><div class="pf-item-val">${tags(p.team_roles)}</div></div>
          <div class="pf-item" style="grid-column:1/-1"><div class="pf-item-label">التقنيات المستخدمة</div><div class="pf-item-val">${tags(p.technologies)}</div></div>
        </div>
      </div>

      <!-- السوق -->
      <div class="profile-section">
        <div class="profile-section-title"><i class="bi bi-globe2"></i> السوق والعملاء</div>
        <div class="profile-grid">
          <div class="pf-item"><div class="pf-item-label">السوق المستهدف</div><div class="pf-item-val">${val(p.target_market)}</div></div>
          <div class="pf-item"><div class="pf-item-label">المستخدمون الحاليون</div><div class="pf-item-val">${val(p.current_users_range)}</div></div>
          <div class="pf-item"><div class="pf-item-label">العملاء الحاليون</div><div class="pf-item-val">${val(p.current_customers_range)}</div></div>
          <div class="pf-item"><div class="pf-item-label">يحقق إيرادات</div><div class="pf-item-val">${yn(p.has_revenue)}</div></div>
        </div>
        ${(p.revenue_sources||[]).length?`<div style="margin-top:8px;font-size:.78rem;color:var(--c-muted);font-weight:700">مصادر الإيرادات:</div><div>${tags(p.revenue_sources)}</div>`:''}
      </div>

      <!-- التمويل -->
      <div class="profile-section">
        <div class="profile-section-title"><i class="bi bi-bank2"></i> التمويل والاستثمار</div>
        <div class="profile-grid">
          <div class="pf-item"><div class="pf-item-label">يبحث عن استثمار</div><div class="pf-item-val">${yn(p.seeking_investment)}</div></div>
          ${p.investment_needed_range?`<div class="pf-item"><div class="pf-item-label">حجم الاستثمار</div><div class="pf-item-val">${val(p.investment_needed_range)}</div></div>`:''}
        </div>
        ${(p.funding_sources||[]).length?`<div style="margin-top:8px;font-size:.78rem;color:var(--c-muted);font-weight:700">مصادر التمويل الحالية:</div><div>${tags(p.funding_sources)}</div>`:''}
      </div>

      <!-- الأثر والتحديات -->
      <div class="profile-section">
        <div class="profile-section-title"><i class="bi bi-graph-up-arrow"></i> الأثر والتحديات</div>
        <div class="profile-grid">
          <div class="pf-item"><div class="pf-item-label">فرص عمل (3 سنوات)</div><div class="pf-item-val">${val(p.jobs_3years_range)}</div></div>
          <div class="pf-item"><div class="pf-item-label">قابلية التوسع</div><div class="pf-item-val">${val(p.scalability_outside_syria)}</div></div>
        </div>
        ${(p.challenges||[]).length?`<div style="margin-top:8px;font-size:.78rem;color:var(--c-muted);font-weight:700">التحديات:</div><div>${tags(p.challenges)}</div>`:''}
        ${(p.support_needed||[]).length?`<div style="margin-top:8px;font-size:.78rem;color:var(--c-muted);font-weight:700">الدعم المطلوب:</div><div>${tags(p.support_needed)}</div>`:''}
      </div>

      <!-- ملاحظات المراجع -->
      ${p.reviewer_notes ? `
      <div class="profile-section">
        <div class="profile-section-title"><i class="bi bi-chat-square-text-fill"></i> ملاحظات المراجع</div>
        <div style="background:#fef3c7;border-radius:12px;padding:14px;font-size:.88rem;line-height:1.7">${E(p.reviewer_notes)}</div>
        ${p.reviewer?`<div style="font-size:.78rem;color:var(--c-muted);margin-top:6px">بواسطة: ${E(p.reviewer.name)}</div>`:''}
      </div>` : ''}

      <!-- Action buttons -->
      <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap">
        <button class="btn btn-warn" onclick="closeModal('viewModal');openReview(${p.id},'${E(p.project_name)}')">
          <i class="bi bi-clipboard2-check-fill"></i> مراجعة
        </button>
        <button class="btn btn-primary" onclick="closeModal('viewModal')">
          <i class="bi bi-x-circle"></i> إغلاق
        </button>
      </div>
    `;
  };

  /* ── Review modal ── */
  window.openReview = function(id, projName) {
    reviewId = id;
    document.getElementById('reviewProjName').textContent = `📋 ${projName}`;
    document.getElementById('reviewStatus').value = 'under_review';
    document.getElementById('reviewNotes').value  = '';
    openModal('reviewModal');
  };

  window.submitReview = async function() {
    const btn = document.querySelector('#reviewModal .btn-primary');
    btn.disabled=true; btn.innerHTML='<div class="spin"></div> جاري الحفظ...';

    const res = await fetch(`${BASE}/entrepreneur/profiles/${reviewId}/review`, {
      method:'POST', headers:H(),
      body: JSON.stringify({
        status:          document.getElementById('reviewStatus').value,
        reviewer_notes:  document.getElementById('reviewNotes').value.trim()||null,
      })
    });

    btn.disabled=false; btn.innerHTML='<i class="bi bi-send-fill"></i> حفظ المراجعة';

    if (res.ok) {
      closeModal('reviewModal');
      loadProfiles(currentPage);
      loadStats();
    } else {
      alert('حدث خطأ في الحفظ، يرجى المحاولة مرة أخرى');
    }
  };

  /* ── Filter shortcut ── */
  window.filterAndLoad = function(status) {
    document.getElementById('fStatus').value = status;
    loadProfiles(1);
  };

  /* ── Export (simple CSV) ── */
  window.exportList = async function() {
    const res = await fetch(`${BASE}/entrepreneur/profiles?per_page=500`, { headers:H() });
    if (!res.ok) return;
    const data = await res.json();
    const items = data.data || [];
    const header = ['#','الاسم','المشروع','المجال','المحافظة','الجاهزية','يبحث عن استثمار','الحالة'];
    const rows = items.map(p => [p.id, p.full_name, p.project_name, FIELD_LABELS[p.project_field]||p.project_field, p.governorate, READINESS_LABELS[p.readiness_stage]||p.readiness_stage, p.seeking_investment?'نعم':'لا', p.status]);
    const csv = '﻿' + [header,...rows].map(r=>r.map(c=>`"${String(c??'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const a=document.createElement('a'); a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'})); a.download='entrepreneur_profiles.csv'; a.click();
  };

  /* ── Modal helpers ── */
  window.openModal  = id => document.getElementById(id).classList.add('open');
  window.closeModal = id => document.getElementById(id).classList.remove('open');
  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if(e.target===m) m.classList.remove('open'); });
  });

  /* ── Debounce ── */
  let debTimer;
  window.debouncedLoad = () => { clearTimeout(debTimer); debTimer=setTimeout(()=>loadProfiles(1),400); };

  /* ── Init ── */
  loadStats();
  loadProfiles(1);
});
</script>
</body>
</html>
