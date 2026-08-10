<?php
$basePath  = '../../';
$pageTitle = 'المشاريع المحتضنة';
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
    .page-head{background:linear-gradient(135deg,#4c1d95,#7c3aed);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .page-head p{margin:6px 0 0;opacity:.8;font-size:.87rem;}
    .kpi-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
    @media(max-width:700px){.kpi-strip{grid-template-columns:1fr 1fr;}}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:16px 20px;box-shadow:var(--c-shadow);}
    .kpi-val{font-size:1.6rem;font-weight:900;color:var(--c-primary);}
    .kpi-lbl{font-size:.77rem;color:var(--c-muted);font-weight:700;margin-top:4px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
    .form-control,.form-select{border:1px solid var(--c-border);border-radius:10px;padding:7px 11px;font-size:.85rem;color:var(--c-text);}
    .tbl{width:100%;border-collapse:collapse;font-size:.86rem;}
    .tbl th{background:var(--c-soft);color:var(--c-text);font-weight:800;padding:9px 12px;text-align:right;}
    .tbl td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:#faf8ff;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-graduated{background:#dbeafe;color:#1d4ed8;}
    .bs-withdrawn{background:#fef3c7;color:#92400e;}
    .bs-terminated{background:#fee2e2;color:#dc2626;}
    .bs-seed{background:#fef9c3;color:#713f12;}
    .bs-early{background:#dcfce7;color:#15803d;}
    .bs-growth{background:#dbeafe;color:#1d4ed8;}
    .bs-exit{background:#ede9fe;color:#7c3aed;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:5px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
    .empty{text-align:center;padding:30px;color:var(--c-muted);}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">
  <div class="page-head">
    <h1><i class="bi bi-diagram-3-fill"></i> المشاريع المحتضنة</h1>
    <p>متابعة المشاريع المقبولة في الحاضنات وتتبع تقدمها</p>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="kpi-strip" id="kpiStrip"></div>
    <div class="card">
      <div class="card-title"><i class="bi bi-table"></i> قائمة المشاريع</div>
      <div class="filters">
        <input type="text" id="srch" class="form-control" placeholder="بحث بالمشروع أو صاحبه..." style="max-width:230px" oninput="filterProj()">
        <select id="statusF" class="form-select" style="max-width:160px" onchange="filterProj()">
          <option value="">كل الحالات</option>
          <option value="active">نشط</option>
          <option value="graduated">متخرج</option>
          <option value="withdrawn">منسحب</option>
          <option value="terminated">منهي</option>
        </select>
        <select id="stageF" class="form-select" style="max-width:160px" onchange="filterProj()">
          <option value="">كل المراحل</option>
          <option value="seed">بذرة</option>
          <option value="early">مبكر</option>
          <option value="growth">نمو</option>
          <option value="exit">تخرج</option>
        </select>
        <select id="incF" class="form-select" style="max-width:200px" onchange="filterProj()">
          <option value="">كل الحاضنات</option>
        </select>
      </div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr>
            <th>المشروع</th><th>الحاضنة</th><th>صاحب المشروع</th><th>المرشد</th>
            <th>المرحلة</th><th>الحالة</th><th>الموظفون</th><th></th>
          </tr></thead>
          <tbody id="projBody"></tbody>
        </table>
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

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const e     = window.APP_HELPERS.e;

  const STAGE_LBL  = { seed:'بذرة', early:'مبكر', growth:'نمو', exit:'تخرج' };
  const STAGE_B    = { seed:'bs-seed', early:'bs-early', growth:'bs-growth', exit:'bs-exit' };
  const STATUS_B   = { active:'bs-active', graduated:'bs-graduated', withdrawn:'bs-withdrawn', terminated:'bs-terminated' };
  const STATUS_LBL = { active:'نشط', graduated:'متخرج', withdrawn:'منسحب', terminated:'منهي' };

  let allProj = [], incMap = {};

  async function load() {
    const [pr, ir] = await Promise.all([
      fetch(`${base}/incubation/projects?per_page=200`, { headers:{ Authorization:`Bearer ${token()}` }}),
      fetch(`${base}/incubators`,                       { headers:{ Authorization:`Bearer ${token()}` }})
    ]);
    const pj = await pr.json();
    allProj = pj.data ?? pj ?? [];
    const incs = (await ir.json()) ?? [];

    incs.forEach(i => { incMap[i.id] = i.name; });
    const sel = document.getElementById('incF');
    incs.forEach(i => { const o=document.createElement('option'); o.value=i.id; o.textContent=i.name; sel.appendChild(o); });

    const total    = allProj.length;
    const active   = allProj.filter(p=>p.status==='active').length;
    const graduated= allProj.filter(p=>p.status==='graduated').length;
    const sessions = allProj.reduce((s,p)=>s+(p.mentoring_sessions_count||0),0);

    document.getElementById('kpiStrip').innerHTML = [
      ['bi-diagram-3-fill','إجمالي المشاريع', total],
      ['bi-check-circle-fill','نشطة', active],
      ['bi-award-fill','متخرجة', graduated],
      ['bi-people-fill','جلسات إرشاد', sessions],
    ].map(([ic,lbl,val])=>`<div class="kpi"><div class="kpi-val"><i class="bi ${ic}" style="font-size:1rem;opacity:.4"></i> ${val}</div><div class="kpi-lbl">${lbl}</div></div>`).join('');

    filterProj();
  }

  window.filterProj = function() {
    const q  = document.getElementById('srch').value.toLowerCase();
    const st = document.getElementById('statusF').value;
    const sg = document.getElementById('stageF').value;
    const ic = document.getElementById('incF').value;
    const list = allProj.filter(p =>
      (!q  || (p.project_name??'').toLowerCase().includes(q) || (p.owner?.name??'').toLowerCase().includes(q))
      && (!st || p.status === st)
      && (!sg || p.stage === sg)
      && (!ic || String(p.incubator_id) === ic)
    );
    document.getElementById('projBody').innerHTML = list.length
      ? list.map(p=>`<tr>
          <td style="font-weight:700">${e(p.project_name)}</td>
          <td>${e(p.incubator?.name ?? incMap[p.incubator_id] ?? '—')}</td>
          <td>${e(p.owner?.name??'—')}</td>
          <td>${e(p.mentor?.name??'—')}</td>
          <td><span class="badge-s ${STAGE_B[p.stage]||'bs-seed'}">${STAGE_LBL[p.stage]??p.stage}</span></td>
          <td><span class="badge-s ${STATUS_B[p.status]||'bs-active'}">${STATUS_LBL[p.status]??p.status}</span></td>
          <td>${p.current_employees ?? '—'}</td>
          <td><a href="project-mentoring.php?id=${p.id}" class="btn-sm"><i class="bi bi-eye-fill"></i> عرض</a></td>
        </tr>`).join('')
      : '<tr><td colspan="8" class="empty">لا توجد مشاريع</td></tr>';
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
