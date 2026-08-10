<?php
$basePath  = '../../';
$pageTitle = 'المكاتب الاستشارية';
$activePage= 'finance';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../../includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#17947B;--c-accent:#06AA89;--c-soft:#EAF8F4;--c-border:rgba(23,148,123,.13);--c-text:#16332E;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(15,79,71,.07);}
    body{background:linear-gradient(160deg,#f0faf7,#e8f7f3);}
    .pw{max-width:1100px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#0f5e4f,var(--c-primary));border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .btn-white{background:#fff;color:var(--c-primary);border:none;border-radius:12px;padding:9px 20px;font-weight:800;cursor:pointer;font-size:.88rem;display:inline-flex;align-items:center;gap:6px;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:10px 22px;font-weight:700;cursor:pointer;font-size:.88rem;display:inline-flex;align-items:center;gap:7px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .offices-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
    .office-card{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:20px;box-shadow:var(--c-shadow);transition:box-shadow .2s;}
    .office-card:hover{box-shadow:0 14px 36px rgba(15,79,71,.13);}
    .office-name{font-weight:800;font-size:1rem;color:var(--c-text);margin-bottom:6px;}
    .office-meta{font-size:.8rem;color:var(--c-muted);margin-bottom:12px;}
    .office-meta span{display:inline-flex;align-items:center;gap:4px;margin-left:10px;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-suspended{background:#fef3c7;color:#92400e;}
    .bs-revoked{background:#fee2e2;color:#dc2626;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:6px 14px;font-size:.8rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;}
    .btn-sm:hover{background:#d0f3ec;}
    .form-control,.form-select{border:1px solid var(--c-border);border-radius:10px;padding:7px 11px;font-size:.85rem;color:var(--c-text);}
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;}
    .empty{text-align:center;padding:40px;color:var(--c-muted);font-size:.9rem;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    /* Modal */
    .modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center;}
    .modal-bg.show{display:flex;}
    .modal-box{background:#fff;border-radius:20px;padding:28px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2);}
    .modal-title{font-weight:800;font-size:1.05rem;margin:0 0 18px;color:var(--c-text);}
    .form-group{margin-bottom:13px;}
    .form-label{font-size:.81rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px;}
    .modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;}
    .btn-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);border-radius:12px;padding:9px 20px;font-weight:700;cursor:pointer;font-size:.88rem;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <h1><i class="bi bi-building"></i> المكاتب الاستشارية</h1>
    <button class="btn-white" id="addBtn" style="display:none" onclick="openModal()"><i class="bi bi-plus-lg"></i> إضافة مكتب</button>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="card">
      <div class="card-title"><i class="bi bi-grid-fill"></i> قائمة المكاتب</div>
      <div id="formSuccess" class="alert-s"></div>
      <div id="formError"   class="alert-e"></div>
      <div class="filters">
        <input type="text" id="srch" class="form-control" placeholder="بحث بالاسم أو الترخيص..." style="max-width:240px" oninput="filterOffices()">
        <select id="statusF" class="form-select" style="max-width:160px" onchange="filterOffices()">
          <option value="">كل الحالات</option>
          <option value="active">نشط</option>
          <option value="suspended">موقوف</option>
          <option value="revoked">ملغى</option>
        </select>
      </div>
      <div class="offices-grid" id="officesGrid"></div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-bg" id="modal">
  <div class="modal-box">
    <div class="modal-title"><i class="bi bi-building-add" style="color:var(--c-primary)"></i> إضافة مكتب استشاري</div>
    <div class="form-group"><label class="form-label">اسم المكتب *</label><input type="text" id="fName" class="form-control"></div>
    <div class="form-group"><label class="form-label">رقم الترخيص *</label><input type="text" id="fLicense" class="form-control"></div>
    <div class="form-group"><label class="form-label">العنوان</label><input type="text" id="fAddress" class="form-control"></div>
    <div class="form-group"><label class="form-label">الهاتف</label><input type="text" id="fPhone" class="form-control"></div>
    <div class="form-group"><label class="form-label">البريد الإلكتروني</label><input type="email" id="fEmail" class="form-control"></div>
    <div class="modal-footer">
      <button class="btn-outline" onclick="closeModal()">إلغاء</button>
      <button class="btn-primary" onclick="saveOffice()"><i class="bi bi-save-fill"></i> حفظ</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  if (!window.AppAuth.hasPermission('finance.consultants.view') && !window.AppAuth.hasPermission('finance.consultant_union.dashboard')) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base    = window.APP_CONFIG.API_BASE_URL;
  const token   = () => window.AppAuth.getToken();
  const canAdd  = window.AppAuth.hasPermission('finance.consultants.create');
  if (canAdd) document.getElementById('addBtn').style.display = '';

  const BADGE = { active:'bs-active', suspended:'bs-suspended', revoked:'bs-revoked' };
  let allOffices = [];

  async function loadOffices() {
    const r = await fetch(`${base}/finance/consultant-offices`, { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل المكاتب');
    const j = await r.json();
    allOffices = j.data ?? j ?? [];
    filterOffices();
  }

  window.filterOffices = function() {
    const q  = document.getElementById('srch').value.toLowerCase();
    const st = document.getElementById('statusF').value;
    const list = allOffices.filter(o =>
      (!q || (o.name??'').toLowerCase().includes(q) || (o.license_number??'').toLowerCase().includes(q))
      && (!st || o.status === st)
    );
    const grid = document.getElementById('officesGrid');
    grid.innerHTML = list.length ? list.map(o => `
      <div class="office-card">
        <div class="office-name">${window.APP_HELPERS.e(o.name)}</div>
        <div class="office-meta">
          <span><i class="bi bi-card-text"></i> ${window.APP_HELPERS.e(o.license_number??'—')}</span>
          <span><i class="bi bi-telephone"></i> ${window.APP_HELPERS.e(o.phone??'—')}</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span class="badge-s ${BADGE[o.status]||'bs-active'}">${window.FinancePlatform.statusLabel(o.status)}</span>
          <a href="consultant-office-view.php?id=${o.id}" class="btn-sm"><i class="bi bi-eye-fill"></i> عرض</a>
        </div>
      </div>`).join('')
      : '<div class="empty"><i class="bi bi-building-slash" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3"></i>لا توجد مكاتب</div>';
  };

  window.openModal  = () => document.getElementById('modal').classList.add('show');
  window.closeModal = () => document.getElementById('modal').classList.remove('show');

  window.saveOffice = async function() {
    const payload = {
      name:           document.getElementById('fName').value.trim(),
      license_number: document.getElementById('fLicense').value.trim(),
      address:        document.getElementById('fAddress').value.trim()||null,
      phone:          document.getElementById('fPhone').value.trim()||null,
      email:          document.getElementById('fEmail').value.trim()||null,
    };
    const r = await fetch(`${base}/finance/consultant-offices`, {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    const s = document.getElementById('formSuccess');
    const e = document.getElementById('formError');
    if (r.ok) {
      closeModal();
      s.textContent='✓ تم إضافة المكتب'; s.style.display='block';
      setTimeout(()=>{ s.style.display='none'; loadOffices(); },1500);
    } else {
      e.textContent = Object.values(j.errors??{}).flat()[0]||j.message||'خطأ';
      e.style.display='block'; setTimeout(()=>e.style.display='none',4000);
    }
  };

  document.getElementById('modal').addEventListener('click', e => { if (e.target===e.currentTarget) closeModal(); });

  try {
    await loadOffices();
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = '';
  } catch(e) {
    document.getElementById('loading').innerHTML = `<div style="color:#dc2626;font-weight:700">${e.message}</div>`;
  }
});
</script>
</body>
</html>
