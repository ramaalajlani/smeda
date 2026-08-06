<?php
$basePath  = '../../';
$pageTitle = 'قصص النجاح';
$activePage= 'incubation';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#7c3aed;--c-accent:#a78bfa;--c-soft:#f5f3ff;--c-border:rgba(124,58,237,.13);--c-text:#1e1b4b;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(124,58,237,.07);}
    *{box-sizing:border-box;}
    body{background:linear-gradient(160deg,#f5f3ff,#ede9fe);font-family:'Segoe UI',Tahoma,sans-serif;}
    .pw{max-width:1200px;margin:auto;padding:22px 14px;}

    /* Hero */
    .hero{background:linear-gradient(135deg,#1e1b4b,#4c1d95,#7c3aed);color:#fff;border-radius:26px;padding:40px 36px;margin-bottom:26px;text-align:center;position:relative;overflow:hidden;}
    .hero::before{content:"✦";position:absolute;font-size:20rem;opacity:.03;top:-80px;right:-60px;line-height:1;}
    .hero-badge{display:inline-block;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:5px 16px;font-size:.82rem;font-weight:700;margin-bottom:14px;}
    .hero h1{font-size:2.2rem;font-weight:900;margin:0 0 10px;letter-spacing:-.5px;}
    .hero p{opacity:.8;font-size:1rem;margin:0 0 20px;}
    .hero-stats{display:flex;justify-content:center;gap:32px;flex-wrap:wrap;}
    .hero-stat{text-align:center;}
    .hero-stat-val{font-size:1.8rem;font-weight:900;display:block;}
    .hero-stat-lbl{font-size:.78rem;opacity:.75;}

    /* KPI strip */
    .kpi-strip{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:24px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:16px;box-shadow:var(--c-shadow);display:flex;align-items:center;gap:12px;}
    .kpi-icon{width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;}
    .kpi-body .val{font-size:1.6rem;font-weight:900;color:var(--c-text);line-height:1;}
    .kpi-body .lbl{font-size:.74rem;color:var(--c-muted);font-weight:700;margin-top:3px;}

    /* Filters */
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;align-items:center;}
    .filters input,.filters select{border:1.5px solid var(--c-border);border-radius:12px;padding:9px 13px;font-size:.86rem;color:var(--c-text);background:#fff;}
    .filters input:focus,.filters select:focus{outline:none;border-color:var(--c-primary);}
    .btn-add{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:13px;padding:10px 20px;font-weight:700;font-size:.88rem;cursor:pointer;display:flex;align-items:center;gap:7px;margin-right:auto;}

    /* Featured slider */
    .featured-section{margin-bottom:28px;}
    .section-title{font-size:1.1rem;font-weight:900;color:var(--c-text);margin-bottom:14px;display:flex;align-items:center;gap:8px;}
    .section-title i{color:var(--c-primary);}
    .featured-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:18px;}

    /* Featured card */
    .f-card{background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 8px 32px rgba(124,58,237,.12);border:1px solid var(--c-border);transition:transform .2s,box-shadow .2s;cursor:pointer;text-decoration:none;display:block;color:inherit;}
    .f-card:hover{transform:translateY(-4px);box-shadow:0 16px 48px rgba(124,58,237,.18);}
    .f-card-cover{height:200px;background:linear-gradient(135deg,#2e1065,#7c3aed);position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;}
    .f-card-cover img{width:100%;height:100%;object-fit:cover;}
    .f-card-cover-placeholder{font-size:4rem;opacity:.5;color:#fff;}
    .f-card-badge{position:absolute;top:12px;right:12px;background:rgba(255,215,0,.9);color:#1e1b4b;border-radius:20px;padding:3px 11px;font-size:.73rem;font-weight:800;display:flex;align-items:center;gap:4px;}
    .f-card-sector{position:absolute;top:12px;left:12px;background:rgba(255,255,255,.2);color:#fff;border-radius:20px;padding:3px 10px;font-size:.73rem;font-weight:700;}
    .f-card-body{padding:20px;}
    .f-card-title{font-size:1.05rem;font-weight:900;color:var(--c-text);margin-bottom:8px;line-height:1.35;}
    .f-card-summary{font-size:.84rem;color:var(--c-muted);line-height:1.65;margin-bottom:14px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
    .f-card-hero{display:flex;align-items:center;gap:10px;border-top:1px solid var(--c-border);padding-top:13px;}
    .f-card-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--c-primary),var(--c-accent));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.95rem;flex-shrink:0;overflow:hidden;}
    .f-card-avatar img{width:100%;height:100%;object-fit:cover;}
    .f-card-hero-name{font-weight:800;font-size:.88rem;color:var(--c-text);}
    .f-card-hero-title{font-size:.76rem;color:var(--c-muted);}
    .f-card-stats{display:flex;gap:12px;margin-top:10px;flex-wrap:wrap;}
    .f-card-stat{display:flex;align-items:center;gap:4px;font-size:.76rem;font-weight:700;color:var(--c-muted);}
    .f-card-stat i{color:var(--c-primary);}

    /* Regular grid */
    .stories-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
    .s-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:var(--c-shadow);border:1px solid var(--c-border);transition:transform .18s;cursor:pointer;text-decoration:none;display:block;color:inherit;}
    .s-card:hover{transform:translateY(-3px);}
    .s-card-cover{height:140px;background:linear-gradient(135deg,#4c1d95,#7c3aed);position:relative;display:flex;align-items:center;justify-content:center;}
    .s-card-cover img{width:100%;height:100%;object-fit:cover;}
    .s-card-cover-placeholder{font-size:2.8rem;opacity:.5;color:#fff;}
    .s-card-sector{position:absolute;bottom:8px;right:10px;background:rgba(0,0,0,.4);color:#fff;border-radius:10px;padding:2px 9px;font-size:.72rem;font-weight:700;}
    .s-card-body{padding:16px;}
    .s-card-title{font-size:.92rem;font-weight:800;color:var(--c-text);margin-bottom:6px;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
    .s-card-hero{font-size:.8rem;color:var(--c-muted);font-weight:700;display:flex;align-items:center;gap:6px;margin-bottom:8px;}
    .s-card-nums{display:flex;gap:8px;flex-wrap:wrap;}
    .s-num{background:var(--c-soft);color:var(--c-primary);border-radius:8px;padding:3px 9px;font-size:.73rem;font-weight:700;}

    /* Sector filter tabs */
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;}
    .tab{padding:7px 16px;border-radius:20px;font-size:.82rem;font-weight:700;border:1.5px solid var(--c-border);background:#fff;cursor:pointer;color:var(--c-muted);transition:all .15s;}
    .tab.active,.tab:hover{background:var(--c-primary);color:#fff;border-color:var(--c-primary);}

    /* Modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(5px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:16px;}
    .modal-box{background:#fff;border-radius:24px;padding:28px;max-width:680px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 30px 70px rgba(0,0,0,.25);}
    .modal-title{font-weight:900;font-size:1.1rem;color:var(--c-text);margin-bottom:20px;display:flex;align-items:center;gap:10px;}
    .modal-title i{color:var(--c-primary);}
    .form-group{margin-bottom:14px;}
    .form-label{display:block;font-size:.82rem;font-weight:700;color:var(--c-text);margin-bottom:5px;}
    .form-control,.form-select{width:100%;padding:9px 12px;border:1.5px solid var(--c-border);border-radius:11px;font-size:.87rem;color:var(--c-text);background:#fff;}
    .form-control:focus,.form-select:focus{outline:none;border-color:var(--c-primary);}
    textarea.form-control{resize:vertical;min-height:80px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    @media(max-width:560px){.form-row{grid-template-columns:1fr;}}
    .modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:12px;border:none;font-size:.87rem;font-weight:700;cursor:pointer;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}
    .btn-soft{background:var(--c-soft);color:var(--c-primary);}

    .spin{display:inline-block;width:22px;height:22px;border:3px solid rgba(124,58,237,.2);border-top-color:var(--c-primary);border-radius:50%;animation:spin .7s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg);}}
    .empty{text-align:center;padding:50px;color:var(--c-muted);}
    .empty i{font-size:3rem;display:block;margin-bottom:10px;opacity:.3;}

    /* Admin controls */
    .admin-bar{background:#fff;border:1px solid var(--c-border);border-radius:14px;padding:12px 16px;display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;}
    .admin-bar-title{font-size:.85rem;font-weight:700;color:var(--c-muted);margin-left:auto;}

    @media(max-width:700px){.hero h1{font-size:1.5rem;}.featured-grid{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-badge"><i class="bi bi-stars"></i> قصص النجاح</div>
    <h1>رحلات نجاح من حاضنات SMEDA</h1>
    <p>قصص حقيقية لرواد أعمال غيّروا الواقع وأطلقوا مشاريعهم من حاضناتنا</p>
    <div class="hero-stats">
      <div class="hero-stat"><span class="hero-stat-val" id="hs-total">—</span><span class="hero-stat-lbl">قصة نجاح</span></div>
      <div class="hero-stat"><span class="hero-stat-val" id="hs-jobs">—</span><span class="hero-stat-lbl">وظيفة أُوجدت</span></div>
      <div class="hero-stat"><span class="hero-stat-val" id="hs-featured">—</span><span class="hero-stat-lbl">قصة مميزة</span></div>
    </div>
  </div>

  <!-- KPI strip -->
  <div class="kpi-strip">
    <div class="kpi"><div class="kpi-icon" style="background:#ede9fe"><i class="bi bi-trophy-fill" style="color:#7c3aed"></i></div><div class="kpi-body"><div class="val" id="k-total">—</div><div class="lbl">إجمالي القصص</div></div></div>
    <div class="kpi"><div class="kpi-icon" style="background:#dcfce7"><i class="bi bi-eye-fill" style="color:#15803d"></i></div><div class="kpi-body"><div class="val" id="k-pub">—</div><div class="lbl">منشورة</div></div></div>
    <div class="kpi"><div class="kpi-icon" style="background:#fef9c3"><i class="bi bi-star-fill" style="color:#b45309"></i></div><div class="kpi-body"><div class="val" id="k-feat">—</div><div class="lbl">مميزة</div></div></div>
    <div class="kpi"><div class="kpi-icon" style="background:#fee2e2"><i class="bi bi-briefcase-fill" style="color:#be123c"></i></div><div class="kpi-body"><div class="val" id="k-jobs">—</div><div class="lbl">وظيفة أُوجدت</div></div></div>
    <div class="kpi"><div class="kpi-icon" style="background:#e0f2fe"><i class="bi bi-file-earmark-text" style="color:#0369a1"></i></div><div class="kpi-body"><div class="val" id="k-draft">—</div><div class="lbl">مسودات</div></div></div>
  </div>

  <!-- Admin bar -->
  <div class="admin-bar" id="adminBar" style="display:none">
    <i class="bi bi-shield-fill-check" style="color:var(--c-primary)"></i>
    <span style="font-size:.87rem;font-weight:700;color:var(--c-text)">وضع الإدارة</span>
    <button class="btn btn-primary" style="padding:7px 16px;font-size:.82rem" onclick="openAddModal()">
      <i class="bi bi-plus-lg"></i> إضافة قصة نجاح
    </button>
    <select id="statusFilterAdmin" class="form-select" style="max-width:150px;padding:6px 10px;font-size:.82rem" onchange="load()">
      <option value="published">منشورة</option>
      <option value="draft">مسودات</option>
      <option value="archived">مؤرشفة</option>
      <option value="">الكل</option>
    </select>
    <span class="admin-bar-title">يمكنك إدارة ونشر قصص النجاح من هنا</span>
  </div>

  <!-- Sector tabs -->
  <div class="tabs">
    <div class="tab active" onclick="setSector('')" data-sector="">الكل</div>
    <div class="tab" onclick="setSector('tech')" data-sector="tech"><i class="bi bi-cpu-fill"></i> تقنية</div>
    <div class="tab" onclick="setSector('industrial')" data-sector="industrial"><i class="bi bi-gear-fill"></i> صناعية</div>
    <div class="tab" onclick="setSector('agricultural')" data-sector="agricultural"><i class="bi bi-tree-fill"></i> زراعية</div>
    <div class="tab" onclick="setSector('services')" data-sector="services"><i class="bi bi-headset"></i> خدمات</div>
    <div class="tab" onclick="setSector('creative')" data-sector="creative"><i class="bi bi-palette-fill"></i> إبداعية</div>
  </div>

  <!-- Filters -->
  <div class="filters">
    <input type="text" id="searchInp" placeholder="بحث في القصص..." style="max-width:250px" oninput="load()">
    <select id="incubatorF" style="max-width:200px" onchange="load()">
      <option value="">كل الحاضنات</option>
    </select>
  </div>

  <!-- Featured -->
  <div class="featured-section" id="featuredSection" style="display:none">
    <div class="section-title"><i class="bi bi-star-fill" style="color:#f59e0b"></i> قصص مميزة</div>
    <div class="featured-grid" id="featuredGrid"></div>
  </div>

  <!-- All stories -->
  <div class="section-title"><i class="bi bi-collection-fill"></i> كل قصص النجاح</div>
  <div id="storiesArea"><div class="empty"><div class="spin"></div></div></div>

</div>

<!-- ══ MODAL: إضافة / تعديل قصة ══ -->
<div class="modal-overlay" id="storyModal" style="display:none" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <div class="modal-title"><i class="bi bi-trophy-fill"></i> <span id="modalTitleText">إضافة قصة نجاح</span></div>
    <input type="hidden" id="editId">

    <div class="form-group">
      <label class="form-label">عنوان القصة <span style="color:#dc2626">*</span></label>
      <input type="text" id="fTitle" class="form-control" placeholder="مثال: من فكرة إلى مليون — قصة أحمد ومشروع TechSY">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">اسم صاحب النجاح <span style="color:#dc2626">*</span></label>
        <input type="text" id="fHeroName" class="form-control" placeholder="الاسم الكامل">
      </div>
      <div class="form-group">
        <label class="form-label">المسمى / الدور</label>
        <input type="text" id="fHeroTitle" class="form-control" placeholder="مؤسس ومدير تنفيذي">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">اسم المشروع</label>
        <input type="text" id="fProjName" class="form-control">
      </div>
      <div class="form-group">
        <label class="form-label">القطاع</label>
        <select id="fSector" class="form-select">
          <option value="">-- اختر --</option>
          <option value="tech">تقنية</option>
          <option value="industrial">صناعي</option>
          <option value="agricultural">زراعي</option>
          <option value="services">خدمات</option>
          <option value="creative">إبداعي</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">ملخص القصة <span style="color:#dc2626">*</span></label>
      <textarea id="fSummary" class="form-control" rows="2" placeholder="وصف موجز يظهر في البطاقة (2-3 جمل)..."></textarea>
    </div>
    <div class="form-group">
      <label class="form-label">تفاصيل القصة كاملة <span style="color:#dc2626">*</span></label>
      <textarea id="fBody" class="form-control" rows="5" placeholder="اكتب القصة كاملة — التحديات، الانطلاقة، المحطات، النجاح..."></textarea>
    </div>
    <div class="form-group">
      <label class="form-label"><i class="bi bi-chat-quote-fill" style="color:var(--c-primary)"></i> الاقتباس البارز</label>
      <textarea id="fQuote" class="form-control" rows="2" placeholder="اقتباس مؤثر من صاحب القصة..."></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label"><i class="bi bi-cash-coin"></i> الإيرادات المحققة (ل.س)</label>
        <input type="number" id="fRevenue" class="form-control" placeholder="0" min="0">
      </div>
      <div class="form-group">
        <label class="form-label"><i class="bi bi-people-fill"></i> الوظائف التي أوجدها</label>
        <input type="number" id="fJobs" class="form-control" placeholder="0" min="0">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">سنوات في الحاضنة</label>
        <input type="number" id="fYears" class="form-control" placeholder="1" min="0" max="20">
      </div>
      <div class="form-group">
        <label class="form-label">مرحلة المشروع الآن</label>
        <input type="text" id="fCurrentStage" class="form-control" placeholder="مثال: يصدّر لـ 5 دول، Series A...">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">رابط الصورة الغلاف</label>
        <input type="url" id="fCover" class="form-control" placeholder="https://...">
      </div>
      <div class="form-group">
        <label class="form-label">رابط صورة صاحب النجاح</label>
        <input type="url" id="fHeroPhoto" class="form-control" placeholder="https://...">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">رابط فيديو (يوتيوب أو Vimeo)</label>
      <input type="url" id="fVideo" class="form-control" placeholder="https://youtube.com/...">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">الحاضنة</label>
        <select id="fIncubator" class="form-select"><option value="">-- اختر --</option></select>
      </div>
      <div class="form-group">
        <label class="form-label">الحالة</label>
        <select id="fStatus" class="form-select">
          <option value="draft">مسودة</option>
          <option value="published">منشورة</option>
          <option value="archived">مؤرشفة</option>
        </select>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--c-soft);border-radius:12px;margin-bottom:4px">
      <input type="checkbox" id="fFeatured" style="width:18px;height:18px;accent-color:var(--c-primary)">
      <label for="fFeatured" style="font-weight:700;cursor:pointer;color:#4c1d95"><i class="bi bi-star-fill" style="color:#f59e0b"></i> تمييز هذه القصة (تظهر في الأعلى)</label>
    </div>

    <div id="modalErr" style="color:#dc2626;font-size:.83rem;font-weight:700;display:none;margin-top:10px"></div>
    <div class="modal-footer">
      <button class="btn btn-soft" onclick="closeModal()">إلغاء</button>
      <button class="btn btn-primary" id="saveBtn" onclick="saveStory()"><i class="bi bi-check-lg"></i> حفظ القصة</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: عرض قصة ══ -->
<div class="modal-overlay" id="viewModal" style="display:none" onclick="if(event.target===this)closeView()">
  <div class="modal-box" style="max-width:740px">
    <div id="viewContent"></div>
    <div class="modal-footer" style="justify-content:space-between">
      <div id="viewAdminBtns" style="display:none;gap:8px;display:flex"></div>
      <button class="btn btn-soft" onclick="closeView()"><i class="bi bi-x-lg"></i> إغلاق</button>
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

  const BASE    = window.APP_CONFIG.API_BASE_URL;
  const H       = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, 'Content-Type':'application/json', Accept:'application/json' });
  const e       = window.APP_HELPERS.e;
  const canMgr  = ['general_director','admin','super_admin','system_admin','incubator_manager','branch_manager']
                    .some(r => window.AppAuth.hasRole?.(r));

  const SECTOR_LBL  = { tech:'تقنية', industrial:'صناعي', agricultural:'زراعي', services:'خدمات', creative:'إبداعي' };
  const SECTOR_ICON = { tech:'bi-cpu-fill', industrial:'bi-gear-fill', agricultural:'bi-tree-fill', services:'bi-headset', creative:'bi-palette-fill' };
  const SECTOR_COLOR= { tech:'#7c3aed', industrial:'#1d4ed8', agricultural:'#15803d', services:'#b45309', creative:'#be123c' };

  let activeSector = '';
  let allIncubators = [];
  let currentViewId = null;

  if (canMgr) {
    document.getElementById('adminBar').style.display = 'flex';
  }

  async function api(url, opts={}) {
    const r = await fetch(url, { headers: H(), ...opts });
    return r.ok ? r.json() : null;
  }
  function set(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = typeof val === 'number' ? val.toLocaleString('ar') : val;
  }

  // تحميل الحاضنات للفلتر
  api(`${BASE}/incubators`).then(data => {
    allIncubators = (Array.isArray(data) ? data : data?.data) ?? [];
    const incSel  = document.getElementById('incubatorF');
    const fIncSel = document.getElementById('fIncubator');
    allIncubators.forEach(i => {
      [incSel, fIncSel].forEach(s => {
        const o = document.createElement('option');
        o.value = i.id; o.textContent = i.name; s.appendChild(o);
      });
    });
  });

  // Stats
  api(`${BASE}/success-stories/stats`).then(s => {
    if (!s) return;
    set('k-total',  s.total);     set('hs-total',   s.published);
    set('k-pub',    s.published);  set('hs-jobs',    s.total_jobs);
    set('k-feat',   s.featured);   set('hs-featured', s.featured);
    set('k-jobs',   s.total_jobs);
    set('k-draft',  s.draft);
  });

  /* ── Load stories ── */
  window.load = async function() {
    document.getElementById('storiesArea').innerHTML = '<div class="empty"><div class="spin"></div></div>';

    const status  = canMgr ? (document.getElementById('statusFilterAdmin')?.value ?? 'published') : 'published';
    const search  = document.getElementById('searchInp').value;
    const incId   = document.getElementById('incubatorF').value;
    const params  = new URLSearchParams({ per_page:50 });
    if (status)       params.set('status', status);
    if (activeSector) params.set('sector', activeSector);
    if (search)       params.set('search', search);
    if (incId)        params.set('incubator_id', incId);

    const data    = await api(`${BASE}/success-stories?${params}`);
    const stories = data?.data ?? data ?? [];

    const featured = stories.filter(s => s.is_featured);
    const regular  = stories.filter(s => !s.is_featured);

    // Featured
    const featSec = document.getElementById('featuredSection');
    if (featured.length && !activeSector && !search) {
      featSec.style.display = '';
      document.getElementById('featuredGrid').innerHTML = featured.map(renderFeaturedCard).join('');
    } else {
      featSec.style.display = 'none';
    }

    // Regular
    document.getElementById('storiesArea').innerHTML = regular.length
      ? `<div class="stories-grid">${regular.map(renderCard).join('')}</div>`
      : `<div class="empty"><i class="bi bi-trophy-fill"></i>لا توجد قصص نجاح${activeSector?' في هذا القطاع':''} حتى الآن</div>`;
  };

  window.setSector = function(sector) {
    activeSector = sector;
    document.querySelectorAll('.tab').forEach(t => {
      t.classList.toggle('active', t.dataset.sector === sector);
    });
    load();
  };

  /* ── Cards ── */
  function renderFeaturedCard(s) {
    const initials = (s.hero_name||'؟').split(' ').map(w=>w[0]).join('').slice(0,2);
    return `<a class="f-card" onclick="viewStory(${s.id});return false" href="#">
      <div class="f-card-cover">
        ${s.cover_image_url ? `<img src="${e(s.cover_image_url)}" alt="">` : `<i class="bi bi-trophy-fill f-card-cover-placeholder"></i>`}
        <div class="f-card-badge"><i class="bi bi-star-fill"></i> مميزة</div>
        ${s.sector ? `<div class="f-card-sector"><i class="bi ${SECTOR_ICON[s.sector]||'bi-briefcase-fill'}"></i> ${SECTOR_LBL[s.sector]||s.sector}</div>` : ''}
      </div>
      <div class="f-card-body">
        <div class="f-card-title">${e(s.title)}</div>
        <div class="f-card-summary">${e(s.summary)}</div>
        ${s.featured_quote ? `<div style="border-right:3px solid var(--c-primary);padding-right:10px;font-size:.82rem;color:var(--c-muted);font-style:italic;margin-bottom:10px">"${e(s.featured_quote)}"</div>` : ''}
        <div class="f-card-hero">
          <div class="f-card-avatar">${s.hero_photo_url ? `<img src="${e(s.hero_photo_url)}" alt="">` : initials}</div>
          <div>
            <div class="f-card-hero-name">${e(s.hero_name)}</div>
            ${s.hero_title ? `<div class="f-card-hero-title">${e(s.hero_title)}</div>` : ''}
          </div>
        </div>
        <div class="f-card-stats">
          ${s.revenue_achieved > 0 ? `<div class="f-card-stat"><i class="bi bi-cash-coin"></i> ${Number(s.revenue_achieved).toLocaleString('ar')} ل.س</div>` : ''}
          ${s.jobs_created > 0 ? `<div class="f-card-stat"><i class="bi bi-people-fill"></i> ${s.jobs_created} وظيفة</div>` : ''}
          ${s.years_in_incubator ? `<div class="f-card-stat"><i class="bi bi-calendar-check-fill"></i> ${s.years_in_incubator} سنة في الحاضنة</div>` : ''}
          <div class="f-card-stat"><i class="bi bi-eye-fill"></i> ${s.views_count||0}</div>
        </div>
      </div>
    </a>`;
  }

  function renderCard(s) {
    return `<a class="s-card" onclick="viewStory(${s.id});return false" href="#">
      <div class="s-card-cover" style="background:linear-gradient(135deg,${SECTOR_COLOR[s.sector]||'#4c1d95'}cc,${SECTOR_COLOR[s.sector]||'#7c3aed'})">
        ${s.cover_image_url ? `<img src="${e(s.cover_image_url)}" alt="">` : `<i class="bi bi-trophy-fill s-card-cover-placeholder"></i>`}
        ${s.sector ? `<div class="s-card-sector"><i class="bi ${SECTOR_ICON[s.sector]||'bi-briefcase-fill'}"></i> ${SECTOR_LBL[s.sector]||s.sector}</div>` : ''}
      </div>
      <div class="s-card-body">
        <div class="s-card-title">${e(s.title)}</div>
        <div class="s-card-hero"><i class="bi bi-person-fill"></i> ${e(s.hero_name)}${s.project_name ? ' · ' + e(s.project_name) : ''}</div>
        <div class="s-card-nums">
          ${s.revenue_achieved > 0 ? `<span class="s-num"><i class="bi bi-cash-coin"></i> ${Number(s.revenue_achieved).toLocaleString('ar')} ل.س</span>` : ''}
          ${s.jobs_created > 0 ? `<span class="s-num"><i class="bi bi-people-fill"></i> ${s.jobs_created} وظيفة</span>` : ''}
          <span class="s-num"><i class="bi bi-eye-fill"></i> ${s.views_count||0}</span>
        </div>
      </div>
    </a>`;
  }

  /* ── View story ── */
  window.viewStory = async function(id) {
    currentViewId = id;
    document.getElementById('viewContent').innerHTML = '<div style="text-align:center;padding:40px"><div class="spin"></div></div>';
    document.getElementById('viewModal').style.display = 'flex';

    const s = await api(`${BASE}/success-stories/${id}`);
    if (!s) return;

    const initials = (s.hero_name||'؟').split(' ').map(w=>w[0]).join('').slice(0,2);
    document.getElementById('viewContent').innerHTML = `
      ${s.cover_image_url ? `<img src="${e(s.cover_image_url)}" style="width:100%;height:220px;object-fit:cover;border-radius:16px;margin-bottom:16px" alt="">` : ''}
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap">
        <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--c-primary),var(--c-accent));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;font-weight:800;flex-shrink:0;overflow:hidden">
          ${s.hero_photo_url ? `<img src="${e(s.hero_photo_url)}" style="width:100%;height:100%;object-fit:cover" alt="">` : initials}
        </div>
        <div>
          <div style="font-weight:900;font-size:1.05rem;color:var(--c-text)">${e(s.hero_name)}</div>
          ${s.hero_title ? `<div style="font-size:.83rem;color:var(--c-muted);font-weight:700">${e(s.hero_title)}</div>` : ''}
          ${s.project_name ? `<div style="font-size:.82rem;color:var(--c-primary);font-weight:700"><i class="bi bi-rocket-fill"></i> ${e(s.project_name)}</div>` : ''}
        </div>
        ${s.sector ? `<span style="background:${SECTOR_COLOR[s.sector]||'#7c3aed'}22;color:${SECTOR_COLOR[s.sector]||'#7c3aed'};border-radius:20px;padding:4px 12px;font-size:.78rem;font-weight:700;margin-right:auto"><i class="bi ${SECTOR_ICON[s.sector]||'bi-briefcase-fill'}"></i> ${SECTOR_LBL[s.sector]||s.sector}</span>` : ''}
      </div>

      <div style="font-size:1.15rem;font-weight:900;color:var(--c-text);margin-bottom:10px;line-height:1.4">${e(s.title)}</div>

      ${s.featured_quote ? `
        <div style="background:var(--c-soft);border-right:4px solid var(--c-primary);border-radius:0 14px 14px 0;padding:14px 16px;margin-bottom:14px;font-size:.92rem;font-style:italic;color:var(--c-text);line-height:1.7">
          <i class="bi bi-quote" style="font-size:1.5rem;color:var(--c-accent);opacity:.6"></i>
          ${e(s.featured_quote)}
        </div>` : ''}

      <!-- أرقام النجاح -->
      ${(s.revenue_achieved > 0 || s.jobs_created > 0 || s.years_in_incubator || s.current_stage) ? `
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:16px">
          ${s.revenue_achieved > 0 ? `<div style="background:#dcfce7;border-radius:14px;padding:12px;text-align:center"><div style="font-size:1.1rem;font-weight:900;color:#15803d">${Number(s.revenue_achieved).toLocaleString('ar')}</div><div style="font-size:.72rem;color:#15803d;font-weight:700">ل.س إيرادات</div></div>` : ''}
          ${s.jobs_created > 0 ? `<div style="background:#dbeafe;border-radius:14px;padding:12px;text-align:center"><div style="font-size:1.1rem;font-weight:900;color:#1d4ed8">${s.jobs_created}</div><div style="font-size:.72rem;color:#1d4ed8;font-weight:700">وظيفة أوجدها</div></div>` : ''}
          ${s.years_in_incubator ? `<div style="background:#ede9fe;border-radius:14px;padding:12px;text-align:center"><div style="font-size:1.1rem;font-weight:900;color:#7c3aed">${s.years_in_incubator}</div><div style="font-size:.72rem;color:#7c3aed;font-weight:700">سنة في الحاضنة</div></div>` : ''}
          ${s.current_stage ? `<div style="background:#fef3c7;border-radius:14px;padding:12px;text-align:center"><div style="font-size:.88rem;font-weight:900;color:#b45309">${e(s.current_stage)}</div><div style="font-size:.72rem;color:#b45309;font-weight:700">مرحلة المشروع الآن</div></div>` : ''}
        </div>` : ''}

      <!-- نص القصة -->
      <div style="font-size:.9rem;line-height:1.85;color:var(--c-text);white-space:pre-wrap">${e(s.body)}</div>

      ${s.video_url ? `
        <div style="margin-top:16px">
          <a href="${e(s.video_url)}" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#fee2e2;color:#be123c;border-radius:12px;padding:10px 18px;font-weight:700;font-size:.88rem;text-decoration:none">
            <i class="bi bi-play-btn-fill" style="font-size:1.2rem"></i> مشاهدة الفيديو التوثيقي
          </a>
        </div>` : ''}

      <div style="font-size:.76rem;color:var(--c-muted);margin-top:14px;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        ${s.incubator ? `<span><i class="bi bi-rocket-takeoff-fill"></i> ${e(s.incubator.name)}</span>` : ''}
        ${s.published_at ? `<span><i class="bi bi-calendar-fill"></i> ${new Date(s.published_at).toLocaleDateString('ar')}</span>` : ''}
        <span><i class="bi bi-eye-fill"></i> ${s.views_count||0} مشاهدة</span>
      </div>`;

    // أزرار الإدارة
    const adminBtns = document.getElementById('viewAdminBtns');
    if (canMgr) {
      adminBtns.style.display = 'flex';
      adminBtns.innerHTML = `
        <button class="btn btn-soft" onclick="editStory(${id})" style="padding:7px 14px;font-size:.82rem"><i class="bi bi-pencil-fill"></i> تعديل</button>
        ${s.status !== 'published' ? `<button class="btn" style="background:#dcfce7;color:#15803d;padding:7px 14px;font-size:.82rem" onclick="publishStory(${id})"><i class="bi bi-check-circle-fill"></i> نشر</button>` : ''}
        <button class="btn" style="background:#fee2e2;color:#dc2626;padding:7px 14px;font-size:.82rem" onclick="deleteStory(${id})"><i class="bi bi-trash-fill"></i> حذف</button>`;
    }
  };

  window.closeView = () => { document.getElementById('viewModal').style.display='none'; };

  /* ── Publish shortcut ── */
  window.publishStory = async function(id) {
    await fetch(`${BASE}/success-stories/${id}`, { method:'PUT', headers:H(), body:JSON.stringify({ status:'published' }) });
    closeView(); load();
  };

  /* ── Delete ── */
  window.deleteStory = async function(id) {
    if (!confirm('هل أنت متأكد من حذف هذه القصة؟')) return;
    await fetch(`${BASE}/success-stories/${id}`, { method:'DELETE', headers:H() });
    closeView(); load();
  };

  /* ── Add modal ── */
  window.openAddModal = function() {
    document.getElementById('editId').value = '';
    document.getElementById('modalTitleText').textContent = 'إضافة قصة نجاح';
    ['fTitle','fHeroName','fHeroTitle','fProjName','fSummary','fBody','fQuote','fCover','fHeroPhoto','fVideo','fCurrentStage'].forEach(id => { document.getElementById(id).value=''; });
    ['fRevenue','fJobs','fYears'].forEach(id => { document.getElementById(id).value=''; });
    document.getElementById('fSector').value = '';
    document.getElementById('fIncubator').value = '';
    document.getElementById('fStatus').value = 'draft';
    document.getElementById('fFeatured').checked = false;
    document.getElementById('modalErr').style.display='none';
    document.getElementById('storyModal').style.display='flex';
  };

  window.editStory = async function(id) {
    closeView();
    const s = await api(`${BASE}/success-stories/${id}`);
    if (!s) return;
    document.getElementById('editId').value = id;
    document.getElementById('modalTitleText').textContent = 'تعديل القصة';
    document.getElementById('fTitle').value       = s.title||'';
    document.getElementById('fHeroName').value    = s.hero_name||'';
    document.getElementById('fHeroTitle').value   = s.hero_title||'';
    document.getElementById('fProjName').value    = s.project_name||'';
    document.getElementById('fSummary').value     = s.summary||'';
    document.getElementById('fBody').value        = s.body||'';
    document.getElementById('fQuote').value       = s.featured_quote||'';
    document.getElementById('fRevenue').value     = s.revenue_achieved||'';
    document.getElementById('fJobs').value        = s.jobs_created||'';
    document.getElementById('fYears').value       = s.years_in_incubator||'';
    document.getElementById('fCurrentStage').value= s.current_stage||'';
    document.getElementById('fCover').value       = s.cover_image_url||'';
    document.getElementById('fHeroPhoto').value   = s.hero_photo_url||'';
    document.getElementById('fVideo').value       = s.video_url||'';
    document.getElementById('fSector').value      = s.sector||'';
    document.getElementById('fIncubator').value   = s.incubator_id||'';
    document.getElementById('fStatus').value      = s.status||'draft';
    document.getElementById('fFeatured').checked  = !!s.is_featured;
    document.getElementById('modalErr').style.display='none';
    document.getElementById('storyModal').style.display='flex';
  };

  window.closeModal = () => { document.getElementById('storyModal').style.display='none'; };

  /* ── Save ── */
  window.saveStory = async function() {
    const title    = document.getElementById('fTitle').value.trim();
    const heroName = document.getElementById('fHeroName').value.trim();
    const summary  = document.getElementById('fSummary').value.trim();
    const body     = document.getElementById('fBody').value.trim();
    const errEl    = document.getElementById('modalErr');

    if (!title || !heroName || !summary || !body) {
      errEl.textContent = 'العنوان، اسم صاحب النجاح، الملخص، والتفاصيل مطلوبة';
      errEl.style.display=''; return;
    }
    errEl.style.display='none';

    const btn = document.getElementById('saveBtn');
    btn.disabled=true; btn.innerHTML='<div class="spin" style="width:16px;height:16px;border-width:2px"></div> جاري الحفظ...';

    const payload = {
      title, hero_name:heroName, summary, body,
      hero_title     : document.getElementById('fHeroTitle').value.trim()||null,
      project_name   : document.getElementById('fProjName').value.trim()||null,
      sector         : document.getElementById('fSector').value||null,
      featured_quote : document.getElementById('fQuote').value.trim()||null,
      revenue_achieved: parseFloat(document.getElementById('fRevenue').value)||null,
      jobs_created   : parseInt(document.getElementById('fJobs').value)||null,
      years_in_incubator: parseInt(document.getElementById('fYears').value)||null,
      current_stage  : document.getElementById('fCurrentStage').value.trim()||null,
      cover_image_url: document.getElementById('fCover').value.trim()||null,
      hero_photo_url : document.getElementById('fHeroPhoto').value.trim()||null,
      video_url      : document.getElementById('fVideo').value.trim()||null,
      incubator_id   : document.getElementById('fIncubator').value||null,
      status         : document.getElementById('fStatus').value,
      is_featured    : document.getElementById('fFeatured').checked,
    };

    const editId = document.getElementById('editId').value;
    const url    = editId ? `${BASE}/success-stories/${editId}` : `${BASE}/success-stories`;
    const method = editId ? 'PUT' : 'POST';

    const res = await fetch(url, { method, headers:H(), body:JSON.stringify(payload) });
    btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg"></i> حفظ القصة';

    if (res.ok) {
      closeModal();
      load();
    } else {
      const j = await res.json();
      errEl.textContent = Object.values(j.errors??{}).flat()[0] || j.message || 'حدث خطأ';
      errEl.style.display='';
    }
  };

  // تحميل أولي
  load();
});
</script>
</body>
</html>
