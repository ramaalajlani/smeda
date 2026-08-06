<?php
$basePath  = '../../';
$pageTitle = 'لوحة مدير الإعلام';
$activePage= 'media_dashboard';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{
      --c-primary:#0369a1;--c-accent:#38bdf8;--c-soft:#e0f2fe;
      --c-border:rgba(3,105,161,.13);--c-text:#0c2340;
      --c-muted:#6B7280;--c-shadow:0 10px 28px rgba(3,105,161,.08);
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{background:linear-gradient(160deg,#f0f9ff,#e0f2fe);font-family:'Segoe UI',Tahoma,sans-serif;color:var(--c-text);}
    .pw{max-width:1200px;margin:auto;padding:22px 14px;}

    /* Hero */
    .hero{background:linear-gradient(135deg,#082f49,#0369a1,#0ea5e9,#38bdf8);color:#fff;border-radius:24px;padding:28px 32px;margin-bottom:20px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;position:relative;overflow:hidden;}
    .hero::before{content:"";position:absolute;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.1),transparent 70%);top:-100px;left:-80px;}
    .hero-avatar{width:68px;height:68px;border-radius:20px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;border:2px solid rgba(255,255,255,.3);}
    .hero-info{flex:1;}
    .hero-title{font-size:1.5rem;font-weight:900;margin-bottom:4px;}
    .hero-sub{opacity:.85;font-size:.88rem;}
    .hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-right:auto;}
    .hero-btn{background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.4);border-radius:12px;color:#fff;padding:8px 18px;font-size:.85rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:background .2s;}
    .hero-btn:hover{background:rgba(255,255,255,.28);}
    .hero-btn.primary{background:rgba(255,255,255,.9);color:#0369a1;}

    /* KPIs */
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:20px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:18px 16px;box-shadow:var(--c-shadow);position:relative;overflow:hidden;}
    .kpi::before{content:"";position:absolute;top:0;right:0;left:0;height:4px;border-radius:18px 18px 0 0;}
    .kpi.blue::before{background:linear-gradient(90deg,#0369a1,#38bdf8);}
    .kpi.green::before{background:linear-gradient(90deg,#15803d,#4ade80);}
    .kpi.amber::before{background:linear-gradient(90deg,#b45309,#fbbf24);}
    .kpi.purple::before{background:linear-gradient(90deg,#7c3aed,#a78bfa);}
    .kpi.rose::before{background:linear-gradient(90deg,#be123c,#fb7185);}
    .kpi-icon{font-size:1.4rem;margin-bottom:8px;}
    .kpi-val{font-size:1.9rem;font-weight:900;color:var(--c-text);line-height:1;}
    .kpi-lbl{font-size:.75rem;font-weight:700;color:var(--c-muted);margin-top:4px;}

    /* Layout */
    .col2{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:18px;}
    @media(max-width:900px){.col2{grid-template-columns:1fr;}}

    /* Section */
    .sec{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);}
    .sec-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;}
    .sec-title{font-weight:800;font-size:.97rem;color:var(--c-text);display:flex;align-items:center;gap:8px;}
    .sec-title i{color:var(--c-primary);}

    /* Table */
    .tbl{width:100%;border-collapse:collapse;font-size:.85rem;}
    .tbl th{padding:9px 12px;font-weight:700;color:var(--c-muted);text-align:right;border-bottom:2px solid var(--c-border);font-size:.78rem;white-space:nowrap;}
    .tbl td{padding:10px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);font-weight:600;vertical-align:middle;}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:var(--c-soft);}

    /* Badges */
    .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;}
    .b-published{background:#dcfce7;color:#15803d;}
    .b-draft{background:#f3f4f6;color:#374151;}
    .b-archived{background:#fef3c7;color:#92400e;}
    .b-pinned{background:#dbeafe;color:#1d4ed8;}

    /* Buttons */
    .btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:10px;border:none;font-size:.82rem;font-weight:700;cursor:pointer;text-decoration:none;transition:all .15s;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}
    .btn-primary:hover{opacity:.9;color:#fff;}
    .btn-soft{background:var(--c-soft);color:var(--c-primary);}
    .btn-soft:hover{background:#bae6fd;}
    .btn-green{background:#dcfce7;color:#15803d;}
    .btn-red{background:#fee2e2;color:#dc2626;}
    .btn-sm{padding:5px 10px;font-size:.76rem;}
    .btn-amber{background:#fef3c7;color:#92400e;}

    /* News preview card */
    .prev-card{border:1px solid var(--c-border);border-radius:14px;overflow:hidden;margin-bottom:10px;display:flex;gap:0;}
    .prev-img{width:80px;min-height:80px;background:linear-gradient(135deg,#0369a1,#38bdf8);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;flex-shrink:0;}
    .prev-img img{width:80px;height:80px;object-fit:cover;}
    .prev-body{padding:10px 12px;flex:1;min-width:0;}
    .prev-title{font-weight:800;font-size:.87rem;color:var(--c-text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:4px;}
    .prev-meta{font-size:.75rem;color:var(--c-muted);display:flex;gap:8px;flex-wrap:wrap;}
    .prev-actions{display:flex;gap:6px;align-items:center;padding:0 10px;flex-shrink:0;}

    /* Category pill */
    .cat-pill{display:inline-flex;align-items:center;gap:4px;border-radius:20px;padding:3px 9px;font-size:.72rem;font-weight:700;}

    /* Stats mini */
    .stat-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--c-border);font-size:.85rem;}
    .stat-row:last-child{border-bottom:none;}
    .stat-lbl{font-weight:700;color:var(--c-muted);}
    .stat-val{font-weight:900;color:var(--c-text);}

    /* Modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:16px;}
    .modal-box{background:#fff;border-radius:24px;padding:28px;max-width:680px;width:100%;max-height:92vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.2);}
    .modal-title{font-weight:900;font-size:1.1rem;color:var(--c-text);margin-bottom:18px;display:flex;align-items:center;gap:10px;}
    .modal-title i{color:var(--c-primary);}
    .form-group{margin-bottom:14px;}
    .form-label{display:block;font-size:.82rem;font-weight:700;color:var(--c-text);margin-bottom:5px;}
    .form-control,.form-select{width:100%;padding:9px 12px;border:1.5px solid var(--c-border);border-radius:11px;font-size:.87rem;color:var(--c-text);background:#fff;}
    .form-control:focus,.form-select:focus{outline:none;border-color:var(--c-primary);box-shadow:0 0 0 3px rgba(3,105,161,.1);}
    textarea.form-control{resize:vertical;min-height:80px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    @media(max-width:520px){.form-row{grid-template-columns:1fr;}}
    .modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;}

    /* Image preview */
    .img-preview{width:100%;height:140px;border-radius:12px;object-fit:cover;border:2px solid var(--c-border);margin-top:6px;display:none;}

    .spin{display:inline-block;width:20px;height:20px;border:3px solid rgba(3,105,161,.2);border-top-color:var(--c-primary);border-radius:50%;animation:spin .7s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg);}}
    .empty{text-align:center;padding:28px;color:var(--c-muted);font-size:.88rem;}
    .empty i{font-size:2rem;display:block;margin-bottom:8px;opacity:.3;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.85rem;margin-bottom:12px;display:none;}
  </style>
</head>
<body>
<div class="pw">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-avatar"><i class="bi bi-megaphone-fill"></i></div>
    <div class="hero-info">
      <div class="hero-title" id="heroName">لوحة مدير الإعلام</div>
      <div class="hero-sub">إدارة الأخبار والإعلانات على الصفحة الرئيسية للمنصة</div>
    </div>
    <div class="hero-actions">
      <button class="hero-btn primary" onclick="openAddModal()">
        <i class="bi bi-plus-lg"></i> خبر جديد
      </button>
      <a href="<?php echo $basePath;?>" target="_blank" class="hero-btn">
        <i class="bi bi-globe2"></i> الصفحة الرئيسية
      </a>
    </div>
  </div>

  <!-- Alert -->
  <div class="alert-s" id="globalAlert"></div>

  <!-- KPIs -->
  <div class="kpi-grid">
    <div class="kpi blue"><div class="kpi-icon"><i class="bi bi-newspaper" style="color:var(--c-primary)"></i></div><div class="kpi-val" id="k-total">—</div><div class="kpi-lbl">إجمالي الأخبار</div></div>
    <div class="kpi green"><div class="kpi-icon"><i class="bi bi-eye-fill" style="color:#15803d"></i></div><div class="kpi-val" id="k-pub">—</div><div class="kpi-lbl">منشورة على الموقع</div></div>
    <div class="kpi amber"><div class="kpi-icon"><i class="bi bi-pencil-square" style="color:#b45309"></i></div><div class="kpi-val" id="k-draft">—</div><div class="kpi-lbl">مسودات</div></div>
    <div class="kpi blue"><div class="kpi-icon"><i class="bi bi-pin-fill" style="color:var(--c-primary)"></i></div><div class="kpi-val" id="k-pinned">—</div><div class="kpi-lbl">مثبتة في الأعلى</div></div>
    <div class="kpi purple"><div class="kpi-icon"><i class="bi bi-graph-up-arrow" style="color:#7c3aed"></i></div><div class="kpi-val" id="k-views">—</div><div class="kpi-lbl">إجمالي المشاهدات</div></div>
  </div>

  <div class="col2">

    <!-- ── قائمة الأخبار ── -->
    <div class="sec">
      <div class="sec-hd">
        <div class="sec-title"><i class="bi bi-list-ul"></i> إدارة الأخبار</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <select id="statusFilter" class="form-select" style="max-width:140px;padding:6px 10px;font-size:.82rem" onchange="loadNews()">
            <option value="">الكل</option>
            <option value="published">منشورة</option>
            <option value="draft">مسودات</option>
            <option value="archived">مؤرشفة</option>
          </select>
          <select id="catFilter" class="form-select" style="max-width:160px;padding:6px 10px;font-size:.82rem" onchange="loadNews()">
            <option value="">كل التصنيفات</option>
            <option value="general">عام</option>
            <option value="event">فعالية</option>
            <option value="announcement">إعلان</option>
            <option value="training">تدريب</option>
            <option value="incubation">حاضنات</option>
            <option value="finance">تمويل</option>
          </select>
          <input type="text" id="searchInp" class="form-control" placeholder="بحث..." style="max-width:160px;padding:6px 10px;font-size:.82rem" oninput="loadNews()">
        </div>
      </div>
      <div id="newsList"><div class="empty"><div class="spin"></div></div></div>
      <div id="pagination" style="display:flex;gap:6px;justify-content:center;margin-top:14px;flex-wrap:wrap"></div>
    </div>

    <!-- ── الجانب ── -->
    <div style="display:flex;flex-direction:column;gap:16px">

      <!-- إحصائيات سريعة -->
      <div class="sec">
        <div class="sec-hd"><div class="sec-title"><i class="bi bi-bar-chart-fill"></i> الإحصائيات</div></div>
        <div id="statsBox"><div class="empty"><div class="spin"></div></div></div>
      </div>

      <!-- معاينة الموقع -->
      <div class="sec">
        <div class="sec-hd"><div class="sec-title"><i class="bi bi-window-fullscreen"></i> معاينة قسم الأخبار</div></div>
        <div style="font-size:.83rem;color:var(--c-muted);margin-bottom:10px">آخر 3 أخبار منشورة ستظهر في الصفحة الرئيسية</div>
        <div id="previewBox"><div class="empty"><div class="spin"></div></div></div>
        <a href="<?php echo $basePath;?>#news" target="_blank" class="btn btn-soft" style="width:100%;justify-content:center;margin-top:10px">
          <i class="bi bi-box-arrow-up-left"></i> معاينة على الموقع
        </a>
      </div>

    </div>
  </div>

</div><!-- /pw -->

<!-- ══ MODAL: إضافة / تعديل خبر ══ -->
<div class="modal-overlay" id="newsModal" style="display:none" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <div class="modal-title"><i class="bi bi-newspaper"></i> <span id="modalTitleTxt">إضافة خبر جديد</span></div>
    <input type="hidden" id="editId">

    <div class="form-group">
      <label class="form-label">عنوان الخبر <span style="color:#dc2626">*</span></label>
      <input type="text" id="fTitle" class="form-control" placeholder="عنوان واضح وجذاب...">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">التصنيف</label>
        <select id="fCat" class="form-select">
          <option value="">-- اختر --</option>
          <option value="general">عام</option>
          <option value="event">فعالية</option>
          <option value="announcement">إعلان رسمي</option>
          <option value="training">تدريب</option>
          <option value="incubation">حاضنات</option>
          <option value="finance">تمويل</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">الحالة</label>
        <select id="fStatus" class="form-select">
          <option value="draft">مسودة (غير منشورة)</option>
          <option value="published">نشر فوري على الموقع</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">ملخص الخبر <span style="color:#dc2626">*</span></label>
      <textarea id="fSummary" class="form-control" rows="2" placeholder="وصف موجز يظهر في البطاقة على الصفحة الرئيسية (جملة أو جملتين)..."></textarea>
    </div>
    <div class="form-group">
      <label class="form-label">تفاصيل الخبر كاملة <span style="color:#dc2626">*</span></label>
      <textarea id="fBody" class="form-control" rows="6" placeholder="اكتب الخبر كاملاً بالتفاصيل..."></textarea>
    </div>
    <div class="form-group">
      <label class="form-label"><i class="bi bi-image-fill" style="color:var(--c-primary)"></i> رابط صورة الخبر</label>
      <input type="url" id="fImage" class="form-control" placeholder="https://..." oninput="previewImg()">
      <img id="imgPreviewEl" class="img-preview" alt="معاينة الصورة">
    </div>
    <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;background:var(--c-soft);border-radius:12px;margin-bottom:4px">
      <input type="checkbox" id="fPinned" style="width:17px;height:17px;accent-color:var(--c-primary)">
      <label for="fPinned" style="font-weight:700;cursor:pointer;color:var(--c-primary)">
        <i class="bi bi-pin-fill"></i> تثبيت هذا الخبر في أعلى قائمة الأخبار
      </label>
    </div>

    <div id="modalErr" style="color:#dc2626;font-size:.83rem;font-weight:700;display:none;margin-top:10px;padding:8px 12px;background:#fee2e2;border-radius:10px"></div>
    <div class="modal-footer">
      <button class="btn btn-soft" onclick="closeModal()">إلغاء</button>
      <button class="btn btn-primary" id="saveBtn" onclick="saveNews()">
        <i class="bi bi-check-lg"></i> <span id="saveBtnTxt">حفظ الخبر</span>
      </button>
    </div>
  </div>
</div>

<!-- ══ MODAL: عرض خبر ══ -->
<div class="modal-overlay" id="viewModal" style="display:none" onclick="if(event.target===this)closeView()">
  <div class="modal-box" style="max-width:740px">
    <div id="viewContent"></div>
    <div class="modal-footer" style="justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div id="viewActionBtns" style="display:flex;gap:8px;flex-wrap:wrap"></div>
      <button class="btn btn-soft" onclick="closeView()"><i class="bi bi-x-lg"></i> إغلاق</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {

  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const allowedRoles = ['media_manager','general_director','admin','super_admin','system_admin'];
  if (!allowedRoles.some(r => window.AppAuth.hasRole?.(r))) {
    document.querySelector('.pw').innerHTML = `
      <div style="text-align:center;padding:100px;color:#dc2626">
        <i class="bi bi-shield-lock-fill" style="font-size:4rem;display:block;margin-bottom:16px"></i>
        <strong style="font-size:1.2rem">صلاحية مرفوضة — هذه اللوحة لمدير الإعلام فقط</strong><br>
        <a href="../../dashboard.php" style="color:var(--c-primary);font-weight:700;margin-top:12px;display:block">رجوع للوحة الرئيسية</a>
      </div>`;
    return;
  }

  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H    = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, 'Content-Type':'application/json', Accept:'application/json' });
  const e    = window.APP_HELPERS.e;
  const user = window.AppAuth.getUser();

  document.getElementById('heroName').textContent = 'مرحباً، ' + (user?.name || 'مدير الإعلام');

  const CAT_LBL   = { general:'عام', event:'فعالية', announcement:'إعلان', training:'تدريب', incubation:'حاضنات', finance:'تمويل' };
  const CAT_COLOR  = { general:'#6b7280', event:'#7c3aed', announcement:'#0369a1', training:'#15803d', incubation:'#b45309', finance:'#be123c' };

  function set(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = typeof val === 'number' ? val.toLocaleString('ar') : val;
  }

  async function api(url, opts={}) {
    const r = await fetch(url, { headers: H(), ...opts });
    return r.ok ? r.json() : null;
  }

  function showAlert(msg, ok=true) {
    const el = document.getElementById('globalAlert');
    el.textContent = msg;
    el.style.background = ok ? '#dcfce7' : '#fee2e2';
    el.style.color = ok ? '#14532d' : '#991b1b';
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 3500);
  }

  /* ── Stats ── */
  async function loadStats() {
    const s = await api(`${BASE}/news/stats`);
    if (!s) return;
    set('k-total',  s.total);
    set('k-pub',    s.published);
    set('k-draft',  s.draft);
    set('k-pinned', s.pinned);
    set('k-views',  s.views);
    document.getElementById('statsBox').innerHTML = `
      <div class="stat-row"><span class="stat-lbl">منشورة على الموقع</span><span class="stat-val" style="color:#15803d">${s.published}</span></div>
      <div class="stat-row"><span class="stat-lbl">مسودات لم تُنشر</span><span class="stat-val" style="color:#b45309">${s.draft}</span></div>
      <div class="stat-row"><span class="stat-lbl">مؤرشفة</span><span class="stat-val" style="color:#6b7280">${(s.total - s.published - s.draft)}</span></div>
      <div class="stat-row"><span class="stat-lbl">مثبتة في الأعلى</span><span class="stat-val" style="color:var(--c-primary)">${s.pinned}</span></div>
      <div class="stat-row"><span class="stat-lbl">إجمالي المشاهدات</span><span class="stat-val">${Number(s.views).toLocaleString('ar')}</span></div>`;
  }

  /* ── Preview (latest 3 published) ── */
  async function loadPreview() {
    const data = await api(`${BASE}/news?status=published&per_page=3`);
    const items = data?.data ?? [];
    document.getElementById('previewBox').innerHTML = items.length
      ? items.map(n => `
        <div class="prev-card">
          <div class="prev-img">${n.image_url ? `<img src="${e(n.image_url)}" alt="">` : '<i class="bi bi-newspaper"></i>'}</div>
          <div class="prev-body">
            <div class="prev-title">${e(n.title)}</div>
            <div class="prev-meta">
              ${n.category ? `<span style="color:${CAT_COLOR[n.category]||'#6b7280'};font-weight:700">${CAT_LBL[n.category]||n.category}</span>` : ''}
              ${n.published_at ? `<span>${new Date(n.published_at).toLocaleDateString('ar')}</span>` : ''}
              ${n.is_pinned ? '<span style="color:var(--c-primary)"><i class="bi bi-pin-fill"></i> مثبت</span>' : ''}
            </div>
          </div>
        </div>`).join('')
      : '<div class="empty"><i class="bi bi-newspaper"></i>لا توجد أخبار منشورة بعد</div>';
  }

  /* ── News list ── */
  let currentPage = 1;
  window.loadNews = async function(page=1) {
    currentPage = page;
    document.getElementById('newsList').innerHTML = '<div class="empty"><div class="spin"></div></div>';

    const status = document.getElementById('statusFilter').value;
    const cat    = document.getElementById('catFilter').value;
    const search = document.getElementById('searchInp').value;
    const params = new URLSearchParams({ per_page:8, page });
    if (status) params.set('status', status);
    if (cat)    params.set('category', cat);
    if (search) params.set('search', search);

    const data  = await api(`${BASE}/news?${params}`);
    const items = data?.data ?? [];
    const meta  = data?.meta ?? {};

    if (!items.length) {
      document.getElementById('newsList').innerHTML = '<div class="empty"><i class="bi bi-newspaper"></i>لا توجد أخبار</div>';
      document.getElementById('pagination').innerHTML = '';
      return;
    }

    document.getElementById('newsList').innerHTML = items.map(n => `
      <div class="prev-card" style="margin-bottom:10px">
        <div class="prev-img" style="cursor:pointer" onclick="viewNews(${n.id})">
          ${n.image_url ? `<img src="${e(n.image_url)}" alt="">` : '<i class="bi bi-newspaper"></i>'}
        </div>
        <div class="prev-body" style="cursor:pointer" onclick="viewNews(${n.id})">
          <div class="prev-title">${e(n.title)}</div>
          <div class="prev-meta">
            <span class="badge ${n.status==='published'?'b-published':n.status==='draft'?'b-draft':'b-archived'}">
              ${n.status==='published'?'منشور':n.status==='draft'?'مسودة':'مؤرشف'}
            </span>
            ${n.category ? `<span style="color:${CAT_COLOR[n.category]||'#6b7280'};font-weight:700;font-size:.73rem">${CAT_LBL[n.category]||n.category}</span>` : ''}
            ${n.is_pinned ? '<span class="badge b-pinned"><i class="bi bi-pin-fill"></i> مثبت</span>' : ''}
            <span><i class="bi bi-eye-fill" style="opacity:.5"></i> ${n.views_count||0}</span>
            ${n.published_at ? `<span>${new Date(n.published_at).toLocaleDateString('ar')}</span>` : ''}
          </div>
        </div>
        <div class="prev-actions">
          ${n.status !== 'published'
            ? `<button class="btn btn-green btn-sm" onclick="quickPublish(${n.id},event)" title="نشر"><i class="bi bi-check-circle-fill"></i></button>`
            : `<button class="btn btn-amber btn-sm" onclick="quickArchive(${n.id},event)" title="أرشفة"><i class="bi bi-archive-fill"></i></button>`}
          <button class="btn btn-soft btn-sm" onclick="editNews(${n.id},event)" title="تعديل"><i class="bi bi-pencil-fill"></i></button>
          <button class="btn btn-red btn-sm" onclick="deleteNews(${n.id},event)" title="حذف"><i class="bi bi-trash-fill"></i></button>
        </div>
      </div>`).join('');

    // Pagination
    const lastPage = meta.last_page ?? 1;
    const pagEl    = document.getElementById('pagination');
    if (lastPage > 1) {
      let pages = '';
      for (let p=1; p<=lastPage; p++) {
        pages += `<button class="btn ${p===currentPage?'btn-primary':'btn-soft'} btn-sm" onclick="loadNews(${p})">${p}</button>`;
      }
      pagEl.innerHTML = pages;
    } else {
      pagEl.innerHTML = '';
    }
  };

  /* ── Quick actions ── */
  window.quickPublish = async (id, ev) => {
    ev.stopPropagation();
    await fetch(`${BASE}/news/${id}`, { method:'PUT', headers:H(), body:JSON.stringify({status:'published'}) });
    showAlert('تم نشر الخبر على الموقع ✅');
    loadNews(currentPage); loadPreview(); loadStats();
  };
  window.quickArchive = async (id, ev) => {
    ev.stopPropagation();
    await fetch(`${BASE}/news/${id}`, { method:'PUT', headers:H(), body:JSON.stringify({status:'archived'}) });
    showAlert('تم أرشفة الخبر');
    loadNews(currentPage); loadPreview(); loadStats();
  };
  window.deleteNews = async (id, ev) => {
    ev.stopPropagation();
    if (!confirm('هل تريد حذف هذا الخبر نهائياً؟')) return;
    await fetch(`${BASE}/news/${id}`, { method:'DELETE', headers:H() });
    showAlert('تم حذف الخبر');
    loadNews(currentPage); loadStats();
  };

  /* ── View ── */
  window.viewNews = async (id) => {
    document.getElementById('viewContent').innerHTML = '<div style="text-align:center;padding:40px"><div class="spin"></div></div>';
    document.getElementById('viewModal').style.display = 'flex';
    const n = await api(`${BASE}/news/${id}`);
    if (!n) return;
    document.getElementById('viewContent').innerHTML = `
      ${n.image_url ? `<img src="${e(n.image_url)}" style="width:100%;height:200px;object-fit:cover;border-radius:14px;margin-bottom:16px" alt="">` : ''}
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;flex-wrap:wrap">
        <span class="badge ${n.status==='published'?'b-published':n.status==='draft'?'b-draft':'b-archived'}">${n.status==='published'?'منشور':n.status==='draft'?'مسودة':'مؤرشف'}</span>
        ${n.category ? `<span class="cat-pill" style="background:${CAT_COLOR[n.category]||'#6b7280'}22;color:${CAT_COLOR[n.category]||'#6b7280'}">${CAT_LBL[n.category]||n.category}</span>` : ''}
        ${n.is_pinned ? '<span class="badge b-pinned"><i class="bi bi-pin-fill"></i> مثبت</span>' : ''}
        <span style="font-size:.78rem;color:var(--c-muted);margin-right:auto"><i class="bi bi-eye-fill"></i> ${n.views_count||0} مشاهدة</span>
      </div>
      <div style="font-size:1.15rem;font-weight:900;color:var(--c-text);margin-bottom:8px;line-height:1.4">${e(n.title)}</div>
      <div style="font-size:.85rem;color:var(--c-muted);margin-bottom:14px;font-weight:600;line-height:1.6">${e(n.summary)}</div>
      <hr style="border:none;border-top:1px solid #e5e7eb;margin:14px 0">
      <div style="font-size:.9rem;line-height:1.85;color:var(--c-text);white-space:pre-wrap">${e(n.body)}</div>
      <div style="font-size:.76rem;color:var(--c-muted);margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
        ${n.creator ? `<span><i class="bi bi-person-fill"></i> ${e(n.creator.name)}</span>` : ''}
        ${n.published_at ? `<span><i class="bi bi-calendar-fill"></i> ${new Date(n.published_at).toLocaleDateString('ar')}</span>` : ''}
      </div>`;

    document.getElementById('viewActionBtns').innerHTML = `
      <button class="btn btn-soft btn-sm" onclick="closeView();editNews(${n.id})"><i class="bi bi-pencil-fill"></i> تعديل</button>
      ${n.status !== 'published' ? `<button class="btn btn-green btn-sm" onclick="closeView();quickPublish(${n.id},{stopPropagation:()=>{}})"><i class="bi bi-check-circle-fill"></i> نشر</button>` : ''}
      <button class="btn btn-red btn-sm" onclick="closeView();deleteNews(${n.id},{stopPropagation:()=>{}})"><i class="bi bi-trash-fill"></i> حذف</button>`;
  };
  window.closeView = () => document.getElementById('viewModal').style.display = 'none';

  /* ── Add modal ── */
  window.openAddModal = function() {
    document.getElementById('editId').value = '';
    document.getElementById('modalTitleTxt').textContent = 'إضافة خبر جديد';
    document.getElementById('saveBtnTxt').textContent = 'نشر الخبر';
    ['fTitle','fSummary','fBody','fImage'].forEach(id => document.getElementById(id).value='');
    document.getElementById('fCat').value    = '';
    document.getElementById('fStatus').value = 'draft';
    document.getElementById('fPinned').checked = false;
    document.getElementById('imgPreviewEl').style.display = 'none';
    document.getElementById('modalErr').style.display = 'none';
    document.getElementById('newsModal').style.display = 'flex';
  };

  /* ── Edit ── */
  window.editNews = async (id, ev) => {
    if (ev) ev.stopPropagation();
    const n = await api(`${BASE}/news/${id}`);
    if (!n) return;
    document.getElementById('editId').value  = id;
    document.getElementById('modalTitleTxt').textContent = 'تعديل الخبر';
    document.getElementById('saveBtnTxt').textContent    = 'حفظ التعديلات';
    document.getElementById('fTitle').value   = n.title||'';
    document.getElementById('fSummary').value = n.summary||'';
    document.getElementById('fBody').value    = n.body||'';
    document.getElementById('fImage').value   = n.image_url||'';
    document.getElementById('fCat').value     = n.category||'';
    document.getElementById('fStatus').value  = n.status||'draft';
    document.getElementById('fPinned').checked= !!n.is_pinned;
    document.getElementById('modalErr').style.display='none';
    previewImg();
    document.getElementById('newsModal').style.display='flex';
  };

  window.closeModal = () => document.getElementById('newsModal').style.display='none';

  /* ── Image preview ── */
  window.previewImg = function() {
    const url = document.getElementById('fImage').value.trim();
    const img = document.getElementById('imgPreviewEl');
    if (url) { img.src=url; img.style.display='block'; }
    else img.style.display='none';
  };

  /* ── Save ── */
  window.saveNews = async function() {
    const title   = document.getElementById('fTitle').value.trim();
    const summary = document.getElementById('fSummary').value.trim();
    const body    = document.getElementById('fBody').value.trim();
    const errEl   = document.getElementById('modalErr');

    if (!title || !summary || !body) {
      errEl.textContent = 'العنوان والملخص والتفاصيل مطلوبة';
      errEl.style.display=''; return;
    }
    errEl.style.display='none';

    const btn = document.getElementById('saveBtn');
    btn.disabled=true;
    btn.innerHTML='<div class="spin" style="width:16px;height:16px;border-width:2px"></div> جاري الحفظ...';

    const payload = {
      title, summary, body,
      image_url : document.getElementById('fImage').value.trim()||null,
      category  : document.getElementById('fCat').value||null,
      status    : document.getElementById('fStatus').value,
      is_pinned : document.getElementById('fPinned').checked,
    };

    const editId = document.getElementById('editId').value;
    const url    = editId ? `${BASE}/news/${editId}` : `${BASE}/news`;
    const method = editId ? 'PUT' : 'POST';

    const res = await fetch(url, { method, headers:H(), body:JSON.stringify(payload) });
    btn.disabled=false;
    btn.innerHTML='<i class="bi bi-check-lg"></i> <span id="saveBtnTxt">حفظ الخبر</span>';

    if (res.ok) {
      closeModal();
      const isPublish = payload.status === 'published';
      showAlert(editId ? 'تم تحديث الخبر ✅' : (isPublish ? 'تم نشر الخبر على الموقع ✅' : 'تم حفظ المسودة'));
      loadNews(currentPage); loadPreview(); loadStats();
    } else {
      const j = await res.json();
      errEl.textContent = Object.values(j.errors??{}).flat()[0] || j.message || 'حدث خطأ';
      errEl.style.display='';
    }
  };

  // تحميل أولي
  loadStats();
  loadNews();
  loadPreview();
});
</script>
</body>
</html>
