<?php
$basePath  = '../../';
$pageTitle = 'الحاضنات';
$activePage= 'incubation';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#7c3aed;--c-accent:#a78bfa;--c-soft:#f5f3ff;--c-border:rgba(124,58,237,.13);--c-text:#1e1b4b;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(124,58,237,.07);}
    body{background:linear-gradient(160deg,#f5f3ff,#ede9fe);}
    .pw{max-width:1100px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#4c1d95,#7c3aed);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .page-head p{margin:6px 0 0;opacity:.8;font-size:.87rem;}
    .btn-white{background:#fff;color:var(--c-primary);border:none;border-radius:12px;padding:9px 20px;font-weight:800;cursor:pointer;font-size:.88rem;display:inline-flex;align-items:center;gap:6px;}
    .kpi-strip{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;margin-bottom:20px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:16px 20px;box-shadow:var(--c-shadow);}
    .kpi-val{font-size:1.7rem;font-weight:900;color:var(--c-primary);}
    .kpi-lbl{font-size:.78rem;color:var(--c-muted);font-weight:700;margin-top:4px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .inc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;}
    .inc-card{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:22px;box-shadow:var(--c-shadow);transition:box-shadow .2s;position:relative;}
    .inc-card:hover{box-shadow:0 14px 40px rgba(124,58,237,.15);}
    .inc-badge{position:absolute;top:14px;left:14px;}
    .inc-icon{width:48px;height:48px;background:var(--c-soft);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--c-primary);margin-bottom:12px;}
    .inc-name{font-weight:800;font-size:1rem;color:var(--c-text);margin-bottom:4px;}
    .inc-meta{font-size:.8rem;color:var(--c-muted);margin-bottom:14px;line-height:1.7;}
    .inc-stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:14px;}
    .inc-stat{background:var(--c-soft);border-radius:10px;padding:8px;text-align:center;}
    .inc-stat-val{font-size:1.1rem;font-weight:800;color:var(--c-primary);}
    .inc-stat-lbl{font-size:.7rem;color:var(--c-muted);font-weight:700;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;display:inline-block;}
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-inactive{background:#f3f4f6;color:#4b5563;}
    .bs-suspended{background:#fef3c7;color:#92400e;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:6px 14px;font-size:.8rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;}
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;}
    .form-control,.form-select{border:1px solid var(--c-border);border-radius:10px;padding:7px 11px;font-size:.85rem;color:var(--c-text);}
    .empty{text-align:center;padding:50px;color:var(--c-muted);}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:10px 22px;font-weight:700;cursor:pointer;font-size:.88rem;display:inline-flex;align-items:center;gap:7px;}
    .modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center;}
    .modal-bg.show{display:flex;}
    .modal-box{background:#fff;border-radius:20px;padding:28px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);}
    .modal-title{font-weight:800;font-size:1.05rem;margin:0 0 18px;color:var(--c-text);}
    .form-group{margin-bottom:13px;}
    .form-label{font-size:.81rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;}
    .btn-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);border-radius:12px;padding:9px 20px;font-weight:700;cursor:pointer;font-size:.88rem;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .SECTOR_ICON={tech:'bi-cpu-fill',industrial:'bi-gear-fill',agricultural:'bi-tree-fill',services:'bi-lightning-fill',creative:'bi-palette-fill'};
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-rocket-takeoff-fill"></i> الحاضنات</h1>
      <p>إدارة حاضنات الأعمال ومتابعة المشاريع الناشئة</p>
    </div>
    <button class="btn-white" id="addBtn" style="display:none" onclick="openModal()"><i class="bi bi-plus-lg"></i> إضافة حاضنة</button>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="kpi-strip" id="kpiStrip"></div>

    <div class="card">
      <div class="card-title"><i class="bi bi-grid-3x3-gap-fill"></i> قائمة الحاضنات</div>
      <div id="formSuccess" class="alert-s"></div>
      <div id="formError"   class="alert-e"></div>
      <div class="filters">
        <input type="text" id="srch" class="form-control" placeholder="بحث بالاسم..." style="max-width:220px" oninput="filterInc()">
        <select id="sectorF" class="form-select" style="max-width:170px" onchange="filterInc()">
          <option value="">كل القطاعات</option>
          <option value="tech">تقنية</option>
          <option value="industrial">صناعي</option>
          <option value="agricultural">زراعي</option>
          <option value="services">خدمات</option>
          <option value="creative">إبداعي</option>
        </select>
        <select id="statusF" class="form-select" style="max-width:150px" onchange="filterInc()">
          <option value="">كل الحالات</option>
          <option value="active">نشط</option>
          <option value="inactive">غير نشط</option>
          <option value="suspended">موقوف</option>
        </select>
      </div>
      <div class="inc-grid" id="incGrid"></div>
    </div>
  </div>
</div>

<!-- Modal إضافة حاضنة -->
<div class="modal-bg" id="modal">
  <div class="modal-box">
    <div class="modal-title"><i class="bi bi-rocket-takeoff-fill" style="color:var(--c-primary)"></i> إضافة حاضنة جديدة</div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">اسم الحاضنة *</label><input type="text" id="fName" class="form-control"></div>
      <div class="form-group"><label class="form-label">الرمز (Code) *</label><input type="text" id="fCode" class="form-control" placeholder="INC-001"></div>
    </div>
    <div class="form-group"><label class="form-label">الوصف</label><textarea id="fDesc" class="form-control" rows="2"></textarea></div>
    <div class="form-row">
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
      <div class="form-group"><label class="form-label">الطاقة الاستيعابية</label><input type="number" id="fCapacity" class="form-control" value="20" min="1"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">الموقع</label><input type="text" id="fLocation" class="form-control"></div>
      <div class="form-group"><label class="form-label">الهاتف</label><input type="text" id="fPhone" class="form-control"></div>
    </div>
    <div class="form-group"><label class="form-label">البريد الإلكتروني</label><input type="email" id="fEmail" class="form-control"></div>
    <div class="modal-footer">
      <button class="btn-outline" onclick="closeModal()">إلغاء</button>
      <button class="btn-primary" onclick="saveInc()"><i class="bi bi-save-fill"></i> حفظ</button>
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

  const incRoles = ['general_director','deputy_general_director','branch_manager','branch_officer','incubator_manager','incubator_mentor','admin','super_admin','system_admin','auditor'];
  const hasIncAccess = incRoles.some(r => window.AppAuth.hasRole?.(r))
    || window.AppAuth.hasPermission?.('incubation.view')
    || window.AppAuth.hasPermission?.('incubation.manage');
  if (!hasIncAccess) { location.href = '../../dashboard.php'; return; }

  const base     = window.APP_CONFIG.API_BASE_URL;
  const token    = () => window.AppAuth.getToken();
  const canManage= window.AppAuth.hasPermission('incubation.manage') || window.AppAuth.hasRole?.('admin') || window.AppAuth.hasRole?.('super_admin') || window.AppAuth.hasRole?.('general_director');
  if (canManage) document.getElementById('addBtn').style.display = '';

  const SECTOR_LBL  = { tech:'تقنية', industrial:'صناعي', agricultural:'زراعي', services:'خدمات', creative:'إبداعي' };
  const SECTOR_ICON = { tech:'bi-cpu-fill', industrial:'bi-gear-fill', agricultural:'bi-tree-fill', services:'bi-lightning-fill', creative:'bi-palette-fill' };
  const SBADGE = { active:'bs-active', inactive:'bs-inactive', suspended:'bs-suspended' };
  let allInc = [], stats = {};

  async function load() {
    const [ir, sr] = await Promise.all([
      fetch(`${base}/incubators`,       { headers:{ Authorization:`Bearer ${token()}` }}),
      fetch(`${base}/incubation/stats`, { headers:{ Authorization:`Bearer ${token()}` }})
    ]);
    allInc = (await ir.json()) ?? [];
    stats  = (await sr.json()) ?? {};

    document.getElementById('kpiStrip').innerHTML = [
      ['bi-rocket-takeoff-fill','الحاضنات', stats.incubators_total ?? allInc.length],
      ['bi-check-circle-fill','نشطة', stats.incubators_active ?? 0],
      ['bi-file-earmark-text-fill','طلبات منضمام', stats.applications_total ?? 0],
      ['bi-hourglass-split','طلبات معلقة', stats.applications_pending ?? 0],
      ['bi-diagram-3-fill','مشاريع محتضنة', stats.projects_active ?? 0],
      ['bi-award-fill','مشاريع تخرجت', stats.projects_graduated ?? 0],
    ].map(([ic,lbl,val])=>`<div class="kpi"><div class="kpi-val"><i class="bi ${ic}" style="font-size:1rem;opacity:.4"></i> ${val}</div><div class="kpi-lbl">${lbl}</div></div>`).join('');

    filterInc();
  }

  window.filterInc = function() {
    const q  = document.getElementById('srch').value.toLowerCase();
    const sc = document.getElementById('sectorF').value;
    const st = document.getElementById('statusF').value;
    const list = allInc.filter(i =>
      (!q  || (i.name??'').toLowerCase().includes(q))
      && (!sc || i.sector === sc)
      && (!st || i.status === st)
    );
    const grid = document.getElementById('incGrid');
    grid.innerHTML = list.length ? list.map(i=>`
      <div class="inc-card">
        <div class="inc-badge"><span class="badge-s ${SBADGE[i.status]||'bs-inactive'}">${statusLbl(i.status)}</span></div>
        <div class="inc-icon"><i class="bi ${SECTOR_ICON[i.sector]||'bi-rocket-takeoff-fill'}"></i></div>
        <div class="inc-name">${window.APP_HELPERS.e(i.name)}</div>
        <div class="inc-meta">
          ${i.sector ? `<i class="bi bi-tag-fill"></i> ${SECTOR_LBL[i.sector]??i.sector}<br>` : ''}
          ${i.location ? `<i class="bi bi-geo-alt-fill"></i> ${window.APP_HELPERS.e(i.location)}<br>` : ''}
          <i class="bi bi-people-fill"></i> الطاقة: ${i.capacity??'—'} مشروع
        </div>
        <div class="inc-stats">
          <div class="inc-stat"><div class="inc-stat-val">${i.applications_count??0}</div><div class="inc-stat-lbl">طلب</div></div>
          <div class="inc-stat"><div class="inc-stat-val">${i.projects_count??0}</div><div class="inc-stat-lbl">مشروع</div></div>
          <div class="inc-stat"><div class="inc-stat-val">${i.programs?.length??0}</div><div class="inc-stat-lbl">برنامج</div></div>
        </div>
        <a href="incubator-view.php?id=${i.id}" class="btn-sm" style="width:100%;justify-content:center"><i class="bi bi-eye-fill"></i> عرض التفاصيل</a>
      </div>`).join('')
    : '<div class="empty"><i class="bi bi-rocket-takeoff" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3"></i>لا توجد حاضنات</div>';
  };

  function statusLbl(s) { return {active:'نشط',inactive:'غير نشط',suspended:'موقوف'}[s]??s; }

  window.openModal  = () => document.getElementById('modal').classList.add('show');
  window.closeModal = () => document.getElementById('modal').classList.remove('show');

  window.saveInc = async function() {
    const payload = {
      name:     document.getElementById('fName').value.trim(),
      code:     document.getElementById('fCode').value.trim(),
      description: document.getElementById('fDesc').value.trim()||null,
      sector:   document.getElementById('fSector').value||null,
      capacity: Number(document.getElementById('fCapacity').value||20),
      location: document.getElementById('fLocation').value.trim()||null,
      phone:    document.getElementById('fPhone').value.trim()||null,
      email:    document.getElementById('fEmail').value.trim()||null,
    };
    const r = await fetch(`${base}/incubators`, {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    const s = document.getElementById('formSuccess');
    const e = document.getElementById('formError');
    if (r.ok) {
      closeModal();
      s.textContent='✓ تمت إضافة الحاضنة'; s.style.display='block';
      setTimeout(()=>{ s.style.display='none'; load(); },1500);
    } else {
      e.textContent = Object.values(j.errors??{}).flat()[0]||j.message||'خطأ';
      e.style.display='block'; setTimeout(()=>e.style.display='none',4000);
    }
  };

  document.getElementById('modal').addEventListener('click', ev=>{ if(ev.target===ev.currentTarget) closeModal(); });

  try {
    await load();
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = '';
  } catch(err) {
    document.getElementById('loading').innerHTML = `<div style="color:#dc2626;font-weight:700">${err.message}</div>`;
  }
});
</script>
</body>
</html>
