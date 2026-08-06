<?php
$basePath  = '../../';
$pageTitle = 'تقارير الحاضنات';
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
    .col2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;}
    @media(max-width:800px){.col2{grid-template-columns:1fr;}}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:20px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:16px 20px;box-shadow:var(--c-shadow);}
    .kpi-val{font-size:1.7rem;font-weight:900;color:var(--c-primary);}
    .kpi-lbl{font-size:.77rem;color:var(--c-muted);font-weight:700;margin-top:4px;}
    .bar-row{display:grid;grid-template-columns:140px 1fr 50px;align-items:center;gap:10px;margin-bottom:10px;font-size:.84rem;}
    .bar-bg{background:#f3f4f6;border-radius:20px;height:10px;overflow:hidden;}
    .bar-fill{height:100%;border-radius:20px;background:linear-gradient(90deg,var(--c-primary),var(--c-accent));transition:width .6s;}
    .tbl{width:100%;border-collapse:collapse;font-size:.86rem;}
    .tbl th{background:var(--c-soft);color:var(--c-text);font-weight:800;padding:9px 12px;text-align:right;}
    .tbl td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:#faf8ff;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-graduated{background:#dbeafe;color:#1d4ed8;}
    .bs-seed{background:#fef9c3;color:#713f12;}
    .bs-early{background:#dcfce7;color:#15803d;}
    .bs-growth{background:#dbeafe;color:#1d4ed8;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:5px 12px;font-size:.78rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
    .empty{text-align:center;padding:24px;color:var(--c-muted);font-size:.87rem;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">
  <div class="page-head">
    <h1><i class="bi bi-bar-chart-fill"></i> تقارير وإحصائيات الحاضنات</h1>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="kpi-grid" id="kpiGrid"></div>

    <div class="col2">
      <div class="card">
        <div class="card-title"><i class="bi bi-pie-chart-fill"></i> توزيع المشاريع حسب المرحلة</div>
        <div id="stageChart"></div>
      </div>
      <div class="card">
        <div class="card-title"><i class="bi bi-bar-chart-fill"></i> توزيع الطلبات حسب الحاضنة</div>
        <div id="incChart"></div>
      </div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-trophy-fill"></i> أبرز المشاريع (حسب الإيرادات)</div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>المشروع</th><th>الحاضنة</th><th>المرحلة</th><th>الإيرادات</th><th>الموظفون</th><th>الحالة</th><th></th></tr></thead>
          <tbody id="topProj"></tbody>
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
  const STAGE_B    = { seed:'bs-seed', early:'bs-early', growth:'bs-growth', exit:'bs-graduated' };
  const STATUS_LBL = { active:'نشط', graduated:'متخرج', withdrawn:'منسحب', terminated:'منهي' };
  const STATUS_B   = { active:'bs-active', graduated:'bs-graduated' };

  async function load() {
    const [sr, pr, ir] = await Promise.all([
      fetch(`${base}/incubation/stats`,          { headers:{ Authorization:`Bearer ${token()}` }}),
      fetch(`${base}/incubation/projects?per_page=200`, { headers:{ Authorization:`Bearer ${token()}` }}),
      fetch(`${base}/incubators`,                { headers:{ Authorization:`Bearer ${token()}` }}),
    ]);
    const stats  = await sr.json();
    const pj     = await pr.json();
    const projects = pj.data ?? pj ?? [];
    const incubators = await ir.json() ?? [];

    // KPIs
    document.getElementById('kpiGrid').innerHTML = [
      ['bi-rocket-takeoff-fill','الحاضنات', stats.incubators_total ?? incubators.length],
      ['bi-check-circle-fill','نشطة', stats.incubators_active ?? 0],
      ['bi-diagram-3-fill','مشاريع نشطة', stats.projects_active ?? 0],
      ['bi-award-fill','تخرجت', stats.projects_graduated ?? 0],
      ['bi-file-earmark-text','طلبات الانضمام', stats.applications_total ?? 0],
      ['bi-people-fill','جلسات إرشاد', stats.sessions_total ?? 0],
    ].map(([ic,lbl,val])=>`<div class="kpi"><div class="kpi-val"><i class="bi ${ic}" style="font-size:1rem;opacity:.4"></i> ${val}</div><div class="kpi-lbl">${lbl}</div></div>`).join('');

    // Stage chart
    const byStage = {};
    projects.forEach(p => { byStage[p.stage] = (byStage[p.stage]||0)+1; });
    const stTotal = projects.length || 1;
    document.getElementById('stageChart').innerHTML = Object.keys(STAGE_LBL).map(s=>`
      <div class="bar-row">
        <span style="font-weight:700">${STAGE_LBL[s]}</span>
        <div class="bar-bg"><div class="bar-fill" style="width:${Math.round((byStage[s]||0)/stTotal*100)}%"></div></div>
        <span style="font-weight:800;color:var(--c-primary)">${byStage[s]||0}</span>
      </div>`).join('');

    // Incubator chart
    const incTotal = incubators.reduce((s,i)=>s+(i.applications_count||0),0) || 1;
    document.getElementById('incChart').innerHTML = incubators.slice(0,6).map(i=>`
      <div class="bar-row">
        <span style="font-weight:700;font-size:.8rem">${e(i.name)}</span>
        <div class="bar-bg"><div class="bar-fill" style="width:${Math.round((i.applications_count||0)/incTotal*100)}%"></div></div>
        <span style="font-weight:800;color:var(--c-primary)">${i.applications_count||0}</span>
      </div>`).join('') || '<div class="empty">لا توجد بيانات</div>';

    // Top projects by revenue
    const top = [...projects].filter(p=>p.current_revenue>0).sort((a,b)=>b.current_revenue-a.current_revenue).slice(0,10);
    document.getElementById('topProj').innerHTML = top.length
      ? top.map(p=>`<tr>
          <td style="font-weight:700">${e(p.project_name)}</td>
          <td>${e(p.incubator?.name??'—')}</td>
          <td><span class="badge-s ${STAGE_B[p.stage]||'bs-seed'}">${STAGE_LBL[p.stage]??p.stage}</span></td>
          <td style="font-weight:700;color:var(--c-primary)">${Number(p.current_revenue).toLocaleString('ar')} ل.س</td>
          <td>${p.current_employees??0}</td>
          <td><span class="badge-s ${STATUS_B[p.status]||'bs-active'}">${STATUS_LBL[p.status]??p.status}</span></td>
          <td><a href="project-mentoring.php?id=${p.id}" class="btn-sm"><i class="bi bi-eye-fill"></i> عرض</a></td>
        </tr>`).join('')
      : '<tr><td colspan="7" class="empty">لا توجد بيانات إيرادات</td></tr>';
  }

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
