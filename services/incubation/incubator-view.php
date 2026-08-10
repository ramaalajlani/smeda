<?php
$basePath  = '../../';
$pageTitle = 'تفاصيل الحاضنة';
$activePage= 'incubation';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#7c3aed;--c-accent:#a78bfa;--c-soft:#f5f3ff;--c-border:rgba(124,58,237,.13);--c-text:#1e1b4b;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(124,58,237,.07);}
    body{background:linear-gradient(160deg,#f5f3ff,#ede9fe);}
    .pw{max-width:1000px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#4c1d95,#7c3aed);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .btn-outline-w{background:#fff;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:12px;padding:9px 20px;font-weight:700;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:9px 20px;font-weight:700;cursor:pointer;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;}
    .info-item{background:var(--c-soft);border-radius:12px;padding:12px 14px;}
    .info-lbl{font-size:.76rem;font-weight:700;color:var(--c-muted);margin-bottom:4px;}
    .info-val{font-size:.92rem;font-weight:800;color:var(--c-text);}
    .programs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:12px;}
    .prog-card{background:var(--c-soft);border-radius:14px;padding:16px;}
    .prog-name{font-weight:800;font-size:.9rem;color:var(--c-text);margin-bottom:6px;}
    .prog-meta{font-size:.78rem;color:var(--c-muted);}
    .tbl{width:100%;border-collapse:collapse;font-size:.86rem;}
    .tbl th{background:var(--c-soft);color:var(--c-text);font-weight:800;padding:9px 12px;text-align:right;}
    .tbl td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);}
    .tbl tr:last-child td{border-bottom:none;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-pending{background:#fef3c7;color:#92400e;}
    .bs-accepted{background:#ede9fe;color:#7c3aed;}
    .bs-rejected{background:#fee2e2;color:#dc2626;}
    .bs-graduated{background:#dbeafe;color:#1d4ed8;}
    .bs-seed{background:#fef9c3;color:#713f12;}
    .bs-early{background:#dcfce7;color:#15803d;}
    .bs-growth{background:#dbeafe;color:#1d4ed8;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:5px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
    .empty{text-align:center;padding:24px;color:var(--c-muted);font-size:.87rem;}
    .tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;border-bottom:2px solid var(--c-border);padding-bottom:0;}
    .tab{padding:8px 18px;border-radius:12px 12px 0 0;font-weight:700;font-size:.85rem;cursor:pointer;color:var(--c-muted);border:none;background:transparent;}
    .tab.active{background:var(--c-soft);color:var(--c-primary);}
    .tab-pane{display:none;} .tab-pane.active{display:block;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .form-group{margin-bottom:12px;}
    .form-label{font-size:.81rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px;}
    .form-control,.form-select{border:1px solid var(--c-border);border-radius:10px;padding:8px 12px;font-size:.86rem;color:var(--c-text);width:100%;box-sizing:border-box;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-rocket-takeoff-fill"></i> <span id="incName">الحاضنة</span></h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px" id="incSub"></div>
    </div>
    <a href="incubators-list.php" class="btn-outline-w"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <!-- معلومات -->
    <div class="card">
      <div class="card-title"><i class="bi bi-info-circle-fill"></i> معلومات الحاضنة</div>
      <div class="info-grid" id="infoGrid"></div>
    </div>

    <!-- تبويبات -->
    <div class="card">
      <div class="tabs">
        <button class="tab active" onclick="switchTab('programs')"><i class="bi bi-collection-fill"></i> البرامج</button>
        <button class="tab" onclick="switchTab('applications')"><i class="bi bi-file-earmark-text-fill"></i> الطلبات</button>
        <button class="tab" onclick="switchTab('projects')"><i class="bi bi-diagram-3-fill"></i> المشاريع</button>
      </div>

      <!-- البرامج -->
      <div class="tab-pane active" id="pane-programs">
        <div id="pgSuccess" class="alert-s"></div>
        <div id="pgError"   class="alert-e"></div>
        <div id="pgManage" style="display:none;margin-bottom:14px">
          <button class="btn-primary" onclick="toggleAddProgram()"><i class="bi bi-plus-lg"></i> إضافة برنامج</button>
        </div>
        <div id="addProgramForm" style="display:none;background:var(--c-soft);border-radius:14px;padding:16px;margin-bottom:14px">
          <div class="form-row">
            <div class="form-group"><label class="form-label">اسم البرنامج *</label><input type="text" id="pgName" class="form-control"></div>
            <div class="form-group"><label class="form-label">المدة (أشهر)</label><input type="number" id="pgDur" class="form-control" value="12" min="1"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">تاريخ البدء</label><input type="date" id="pgStart" class="form-control"></div>
            <div class="form-group"><label class="form-label">تاريخ الانتهاء</label><input type="date" id="pgEnd" class="form-control"></div>
          </div>
          <div class="form-group"><label class="form-label">المقاعد</label><input type="number" id="pgSeats" class="form-control" value="10" min="1"></div>
          <div class="form-group"><label class="form-label">المتطلبات</label><textarea id="pgReq" class="form-control" rows="2"></textarea></div>
          <button class="btn-primary" onclick="saveProgram()"><i class="bi bi-save-fill"></i> حفظ البرنامج</button>
        </div>
        <div class="programs-grid" id="programsGrid"></div>
      </div>

      <!-- الطلبات -->
      <div class="tab-pane" id="pane-applications">
        <div style="overflow-x:auto">
          <table class="tbl">
            <thead><tr><th>مقدم الطلب</th><th>اسم المشروع</th><th>المرحلة</th><th>الحالة</th><th>تاريخ التقديم</th><th>إجراء</th></tr></thead>
            <tbody id="appsBody"></tbody>
          </table>
        </div>
      </div>

      <!-- المشاريع -->
      <div class="tab-pane" id="pane-projects">
        <div style="overflow-x:auto">
          <table class="tbl">
            <thead><tr><th>المشروع</th><th>صاحب المشروع</th><th>المرشد</th><th>المرحلة</th><th>الحالة</th><th></th></tr></thead>
            <tbody id="projBody"></tbody>
          </table>
        </div>
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

  const incRoles = ['general_director','deputy_general_director','branch_manager','branch_officer','incubator_manager','incubator_mentor','admin','super_admin','system_admin','auditor'];
  const hasIncAccess = incRoles.some(r => window.AppAuth.hasRole?.(r))
    || window.AppAuth.hasPermission?.('incubation.view')
    || window.AppAuth.hasPermission?.('incubation.manage');
  if (!hasIncAccess) { location.href = '../../dashboard.php'; return; }

  const id = new URLSearchParams(location.search).get('id');
  if (!id) { location.href = 'incubators-list.php'; return; }

  const base     = window.APP_CONFIG.API_BASE_URL;
  const token    = () => window.AppAuth.getToken();
  const e        = window.APP_HELPERS.e;
  const canManage= window.AppAuth.hasPermission('incubation.manage') || window.AppAuth.hasRole?.('admin') || window.AppAuth.hasRole?.('super_admin') || window.AppAuth.hasRole?.('general_director') || window.AppAuth.hasRole?.('incubator_manager');

  const SECTOR_LBL= { tech:'تقنية', industrial:'صناعي', agricultural:'زراعي', services:'خدمات', creative:'إبداعي' };
  const STAGE_LBL = { idea:'فكرة', pre_seed:'ما قبل الإطلاق', seed:'بذرة', early:'مبكر', growth:'نمو' };
  const STAGE_BADGE={ seed:'bs-seed', early:'bs-early', growth:'bs-growth', exit:'bs-graduated' };
  const APP_BADGE = { pending:'bs-pending', under_review:'bs-accepted', accepted:'bs-active', rejected:'bs-rejected' };
  const APP_LBL   = { pending:'معلق', under_review:'قيد المراجعة', accepted:'مقبول', rejected:'مرفوض', withdrawn:'منسحب' };
  const PRJ_BADGE = { active:'bs-active', graduated:'bs-graduated', withdrawn:'bs-pending', terminated:'bs-rejected' };
  const PRJ_LBL   = { active:'نشط', graduated:'متخرج', withdrawn:'منسحب', terminated:'منهي' };

  if (canManage) document.getElementById('pgManage').style.display = '';

  let incubator = null;

  async function load() {
    const r = await fetch(`${base}/incubators/${id}`, { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل الحاضنة');
    const j = await r.json();
    incubator = j.data ?? j;

    document.getElementById('incName').textContent = incubator.name;
    document.getElementById('incSub').textContent  = SECTOR_LBL[incubator.sector] ?? '';

    document.getElementById('infoGrid').innerHTML = [
      ['الاسم',        incubator.name],
      ['الرمز',        incubator.code],
      ['القطاع',       SECTOR_LBL[incubator.sector]??incubator.sector],
      ['الموقع',       incubator.location],
      ['الهاتف',       incubator.phone],
      ['البريد',       incubator.email],
      ['الطاقة',       (incubator.capacity??'—') + ' مشروع'],
      ['المدير',       incubator.manager?.name],
      ['الفرع',        incubator.branch?.name],
      ['الحالة',       {active:'نشط',inactive:'غير نشط',suspended:'موقوف'}[incubator.status]??incubator.status],
      ['عدد الطلبات',  incubator.applications_count ?? 0],
      ['عدد المشاريع', incubator.projects_count ?? 0],
    ].map(([l,v])=>`<div class="info-item"><div class="info-lbl">${l}</div><div class="info-val">${e(v??'—')}</div></div>`).join('');

    // Programs
    const progs = incubator.programs ?? [];
    document.getElementById('programsGrid').innerHTML = progs.length
      ? progs.map(p=>`<div class="prog-card">
          <div class="prog-name">${e(p.name)}</div>
          <div class="prog-meta">
            <i class="bi bi-calendar3"></i> ${p.duration_months ?? '—'} أشهر &nbsp;
            <i class="bi bi-people-fill"></i> ${p.seats ?? '—'} مقعد<br>
            ${p.start_date ? `<i class="bi bi-play-circle"></i> ${p.start_date}` : ''}
            ${p.status === 'open' ? '<span style="color:#15803d;font-weight:700"> — مفتوح</span>' : ''}
          </div>
        </div>`).join('')
      : '<div class="empty">لا توجد برامج مضافة بعد</div>';

    // Applications
    const apps = incubator.applications ?? [];
    if (!apps.length) {
      const ar = await fetch(`${base}/incubators/${id}/applications`, { headers:{ Authorization:`Bearer ${token()}` }});
      const aj = await ar.json();
      renderApps(aj.data ?? aj ?? []);
    } else { renderApps(apps); }

    // Projects
    const projs = incubator.projects ?? [];
    document.getElementById('projBody').innerHTML = projs.length
      ? projs.map(p=>`<tr>
          <td style="font-weight:700">${e(p.project_name)}</td>
          <td>${e(p.owner?.name??'—')}</td>
          <td>${e(p.mentor?.name??'—')}</td>
          <td><span class="badge-s ${STAGE_BADGE[p.stage]||'bs-seed'}">${{seed:'بذرة',early:'مبكر',growth:'نمو',exit:'تخرج'}[p.stage]??p.stage}</span></td>
          <td><span class="badge-s ${PRJ_BADGE[p.status]||'bs-active'}">${PRJ_LBL[p.status]??p.status}</span></td>
          <td><a href="incubated-projects.php?id=${p.id}" class="btn-sm"><i class="bi bi-eye-fill"></i> عرض</a></td>
        </tr>`).join('')
      : '<tr><td colspan="6" class="empty">لا توجد مشاريع محتضنة</td></tr>';
  }

  function renderApps(apps) {
    document.getElementById('appsBody').innerHTML = apps.length
      ? apps.map(a=>`<tr>
          <td>${e(a.applicant?.name??'—')}</td>
          <td style="font-weight:700">${e(a.project_name)}</td>
          <td>${STAGE_LBL[a.business_stage]??a.business_stage??'—'}</td>
          <td><span class="badge-s ${APP_BADGE[a.status]||'bs-pending'}">${APP_LBL[a.status]??a.status}</span></td>
          <td style="font-size:.8rem;color:var(--c-muted)">${a.created_at?.slice(0,10)??'—'}</td>
          <td>${canManage && a.status==='pending' ? `
            <button class="btn-sm" style="background:#dcfce7;color:#15803d" onclick="reviewApp(${a.id},'accepted')"><i class="bi bi-check-lg"></i> قبول</button>
            <button class="btn-sm" style="background:#fee2e2;color:#dc2626;margin-top:3px" onclick="reviewApp(${a.id},'rejected')"><i class="bi bi-x-lg"></i> رفض</button>
          ` : ''}</td>
        </tr>`).join('')
      : '<tr><td colspan="6" class="empty">لا توجد طلبات</td></tr>';
  }

  window.reviewApp = async function(appId, status) {
    const r = await fetch(`${base}/incubation/applications/${appId}/review`, {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify({ status })
    });
    if (r.ok) load();
  };

  window.switchTab = function(tab) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('pane-'+tab).classList.add('active');
  };

  window.toggleAddProgram = () => {
    const f = document.getElementById('addProgramForm');
    f.style.display = f.style.display === 'none' ? '' : 'none';
  };

  window.saveProgram = async function() {
    const payload = {
      name:            document.getElementById('pgName').value.trim(),
      duration_months: Number(document.getElementById('pgDur').value||12),
      seats:           Number(document.getElementById('pgSeats').value||10),
      start_date:      document.getElementById('pgStart').value||null,
      end_date:        document.getElementById('pgEnd').value||null,
      requirements:    document.getElementById('pgReq').value.trim()||null,
    };
    const r = await fetch(`${base}/incubators/${id}/programs`, {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    const s = document.getElementById('pgSuccess');
    const er= document.getElementById('pgError');
    if (r.ok) {
      s.textContent='✓ تمت إضافة البرنامج'; s.style.display='block';
      setTimeout(()=>{ s.style.display='none'; load(); },1500);
      document.getElementById('addProgramForm').style.display='none';
    } else {
      er.textContent = Object.values(j.errors??{}).flat()[0]||j.message||'خطأ';
      er.style.display='block'; setTimeout(()=>er.style.display='none',4000);
    }
  };

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
