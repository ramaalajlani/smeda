<?php
$basePath  = '../../';
$pageTitle = 'البنوك وجهات التمويل';
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
    .kpi-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
    @media(max-width:600px){.kpi-strip{grid-template-columns:1fr 1fr;}}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:16px 20px;box-shadow:var(--c-shadow);}
    .kpi-val{font-size:1.6rem;font-weight:900;color:var(--c-primary);}
    .kpi-lbl{font-size:.78rem;color:var(--c-muted);font-weight:700;margin-top:4px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;}
    .form-control,.form-select{border:1px solid var(--c-border);border-radius:10px;padding:7px 11px;font-size:.85rem;color:var(--c-text);}
    .partners-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
    .partner-card{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:20px;box-shadow:var(--c-shadow);transition:box-shadow .2s;}
    .partner-card:hover{box-shadow:0 14px 36px rgba(15,79,71,.13);}
    .partner-icon{width:44px;height:44px;background:var(--c-soft);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:var(--c-primary);margin-bottom:12px;}
    .partner-name{font-weight:800;font-size:1rem;color:var(--c-text);margin-bottom:4px;}
    .partner-type{font-size:.78rem;color:var(--c-muted);margin-bottom:12px;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-inactive{background:#f3f4f6;color:#4b5563;}
    .bs-suspended{background:#fef3c7;color:#92400e;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:6px 14px;font-size:.8rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;}
    .btn-sm:hover{background:#d0f3ec;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:10px 22px;font-weight:700;cursor:pointer;font-size:.88rem;display:inline-flex;align-items:center;gap:7px;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .empty{text-align:center;padding:40px;color:var(--c-muted);font-size:.9rem;}
    .modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center;}
    .modal-bg.show{display:flex;}
    .modal-box{background:#fff;border-radius:20px;padding:28px;width:100%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.2);}
    .modal-title{font-weight:800;font-size:1.05rem;margin:0 0 18px;color:var(--c-text);}
    .form-group{margin-bottom:13px;}
    .form-label{font-size:.81rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;}
    .btn-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);border-radius:12px;padding:9px 20px;font-weight:700;cursor:pointer;font-size:.88rem;}
    .TYPE_ICON={bank:'bi-bank',investment_fund:'bi-graph-up-arrow',microfinance:'bi-coin',development_fund:'bi-building-check'};
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <h1><i class="bi bi-bank"></i> البنوك وجهات التمويل</h1>
    <button class="btn-white" id="addBtn" style="display:none" onclick="openModal()"><i class="bi bi-plus-lg"></i> إضافة جهة</button>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="kpi-strip" id="kpiStrip"></div>

    <div class="card">
      <div class="card-title"><i class="bi bi-grid-fill"></i> جهات التمويل</div>
      <div id="formSuccess" class="alert-s"></div>
      <div id="formError"   class="alert-e"></div>
      <div class="filters">
        <input type="text" id="srch" class="form-control" placeholder="بحث بالاسم..." style="max-width:220px" oninput="filterPartners()">
        <select id="typeF" class="form-select" style="max-width:180px" onchange="filterPartners()">
          <option value="">كل الأنواع</option>
          <option value="bank">بنك</option>
          <option value="fund">صندوق</option>
          <option value="guarantee_company">شركة ضمان</option>
          <option value="donor">جهة مانحة</option>
          <option value="other">أخرى</option>
        </select>
        <select id="statusF" class="form-select" style="max-width:150px" onchange="filterPartners()">
          <option value="">كل الحالات</option>
          <option value="active">نشط</option>
          <option value="inactive">غير نشط</option>
          <option value="suspended">موقوف</option>
        </select>
      </div>
      <div class="partners-grid" id="partnersGrid"></div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-bg" id="modal">
  <div class="modal-box">
    <div class="modal-title"><i class="bi bi-bank2" style="color:var(--c-primary)"></i> إضافة جهة تمويل</div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">الاسم *</label><input type="text" id="fName" class="form-control"></div>
      <div class="form-group">
        <label class="form-label">النوع *</label>
        <select id="fType" class="form-select">
          <option value="bank">بنك</option>
          <option value="fund">صندوق</option>
          <option value="guarantee_company">شركة ضمان</option>
          <option value="donor">جهة مانحة</option>
          <option value="other">أخرى</option>
        </select>
      </div>
    </div>
    <div class="form-group"><label class="form-label">الوصف</label><textarea id="fDesc" class="form-control" rows="2"></textarea></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">الهاتف</label><input type="text" id="fPhone" class="form-control"></div>
      <div class="form-group"><label class="form-label">البريد</label><input type="email" id="fEmail" class="form-control"></div>
    </div>
    <div class="form-group"><label class="form-label">الموقع الإلكتروني</label><input type="text" id="fWebsite" class="form-control"></div>
    <div class="modal-footer">
      <button class="btn-outline" onclick="closeModal()">إلغاء</button>
      <button class="btn-primary" onclick="savePartner()"><i class="bi bi-save-fill"></i> حفظ</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  if (!window.AppAuth.hasPermission('finance.partners.view')) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base   = window.APP_CONFIG.API_BASE_URL;
  const token  = () => window.AppAuth.getToken();
  const FP     = window.FinancePlatform;
  const canAdd = window.AppAuth.hasPermission('finance.partners.create');
  if (canAdd) document.getElementById('addBtn').style.display = '';

  const TYPE_LBL  = { bank:'بنك', fund:'صندوق', guarantee_company:'شركة ضمان', donor:'جهة مانحة', other:'أخرى' };
  const TYPE_ICON = { bank:'bi-bank', fund:'bi-graph-up-arrow', guarantee_company:'bi-shield-check', donor:'bi-heart-fill', other:'bi-building' };
  const partnerType = p => p.partner_type ?? p.type;
  const SBADGE    = { active:'bs-active', inactive:'bs-inactive', suspended:'bs-suspended' };
  let allPartners = [];

  async function loadPartners() {
    const r = await fetch(window.APP_ROUTES.fundingPartners(), { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل جهات التمويل');
    const j = await r.json();
    allPartners = j.data ?? j ?? [];

    const active = allPartners.filter(p=>p.status==='active').length;
    const types  = [...new Set(allPartners.map(partnerType))].length;
    document.getElementById('kpiStrip').innerHTML = [
      ['bi-bank','جهات التمويل', allPartners.length],
      ['bi-check-circle-fill','جهات نشطة', active],
      ['bi-diagram-3-fill','أنواع مختلفة', types],
    ].map(([ic,lbl,val])=>`<div class="kpi"><div class="kpi-val"><i class="bi ${ic}" style="font-size:1rem;opacity:.5"></i> ${val}</div><div class="kpi-lbl">${lbl}</div></div>`).join('');

    filterPartners();
  }

  window.filterPartners = function() {
    const q  = document.getElementById('srch').value.toLowerCase();
    const tp = document.getElementById('typeF').value;
    const st = document.getElementById('statusF').value;
    const list = allPartners.filter(p =>
      (!q  || (p.name??'').toLowerCase().includes(q))
      && (!tp || partnerType(p) === tp)
      && (!st || p.status === st)
    );
    const grid = document.getElementById('partnersGrid');
    grid.innerHTML = list.length ? list.map(p=>`
      <div class="partner-card">
        <div class="partner-icon"><i class="bi ${TYPE_ICON[partnerType(p)]||'bi-bank'}"></i></div>
        <div class="partner-name">${window.APP_HELPERS.e(p.name)}</div>
        <div class="partner-type">${TYPE_LBL[partnerType(p)]??partnerType(p)??'—'}</div>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span class="badge-s ${SBADGE[p.status]||'bs-inactive'}">${FP.statusLabel(p.status)}</span>
          <a href="funding-partner-view.php?id=${p.id}" class="btn-sm"><i class="bi bi-eye-fill"></i> عرض</a>
        </div>
      </div>`).join('')
      : '<div class="empty"><i class="bi bi-bank" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3"></i>لا توجد جهات تمويل</div>';
  };

  window.openModal  = () => document.getElementById('modal').classList.add('show');
  window.closeModal = () => document.getElementById('modal').classList.remove('show');

  window.savePartner = async function() {
    const payload = {
      name:    document.getElementById('fName').value.trim(),
      partner_type: document.getElementById('fType').value,
      phone:   document.getElementById('fPhone').value.trim()||null,
      email:   document.getElementById('fEmail').value.trim()||null,
    };
    const r = await fetch(window.APP_ROUTES.fundingPartners(), {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    const s = document.getElementById('formSuccess');
    const e = document.getElementById('formError');
    if (r.ok) {
      closeModal();
      s.textContent='✓ تم إضافة جهة التمويل'; s.style.display='block';
      setTimeout(()=>{ s.style.display='none'; loadPartners(); },1500);
    } else {
      e.textContent = Object.values(j.errors??{}).flat()[0]||j.message||'خطأ';
      e.style.display='block'; setTimeout(()=>e.style.display='none',4000);
    }
  };

  document.getElementById('modal').addEventListener('click', ev => { if(ev.target===ev.currentTarget) closeModal(); });

  try {
    await loadPartners();
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = '';
  } catch(err) {
    document.getElementById('loading').innerHTML = `<div style="color:#dc2626;font-weight:700">${err.message}</div>`;
  }
});
</script>
</body>
</html>
