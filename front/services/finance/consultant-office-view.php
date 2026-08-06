<?php
$basePath  = '../../';
$pageTitle = 'تفاصيل المكتب الاستشاري';
$activePage= 'finance';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#17947B;--c-accent:#06AA89;--c-soft:#EAF8F4;--c-border:rgba(23,148,123,.13);--c-text:#16332E;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(15,79,71,.07);}
    body{background:linear-gradient(160deg,#f0faf7,#e8f7f3);}
    .pw{max-width:960px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#0f5e4f,var(--c-primary));border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .btn-outline{background:#fff;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:12px;padding:9px 20px;font-weight:700;cursor:pointer;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;}
    .info-item{background:var(--c-soft);border-radius:12px;padding:12px 14px;}
    .info-lbl{font-size:.76rem;font-weight:700;color:var(--c-muted);margin-bottom:4px;}
    .info-val{font-size:.92rem;font-weight:800;color:var(--c-text);}
    .tbl{width:100%;border-collapse:collapse;font-size:.86rem;}
    .tbl th{background:var(--c-soft);color:var(--c-text);font-weight:800;padding:9px 12px;text-align:right;}
    .tbl td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);}
    .tbl tr:last-child td{border-bottom:none;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-pending{background:#fef3c7;color:#92400e;}
    .bs-assigned{background:#dbeafe;color:#1d4ed8;}
    .bs-completed{background:#dcfce7;color:#15803d;}
    .bs-rejected{background:#fee2e2;color:#dc2626;}
    .empty{text-align:center;padding:24px;color:var(--c-muted);font-size:.87rem;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:5px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
  </style>
</head>
<body>
<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-building"></i> <span id="officeName">المكتب الاستشاري</span></h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px" id="officeSub"></div>
    </div>
    <a href="consultant-offices.php" class="btn-outline"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="card">
      <div class="card-title"><i class="bi bi-info-circle-fill"></i> معلومات المكتب</div>
      <div class="info-grid" id="infoGrid"></div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-clipboard2-check-fill"></i> الإحالات</div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>المشروع</th><th>تاريخ الإحالة</th><th>الحالة</th><th>التوصية</th><th></th></tr></thead>
          <tbody id="assignBody"></tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-file-earmark-text-fill"></i> التقارير المرفوعة</div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>المشروع</th><th>التوصية</th><th>نسبة الجدوى</th><th>تاريخ التقرير</th></tr></thead>
          <tbody id="reportBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const id = new URLSearchParams(location.search).get('id');
  if (!id) { location.href = 'consultant-offices.php'; return; }

  if (!window.AppAuth.hasPermission('finance.consultants.view') && !window.AppAuth.hasPermission('finance.consultant_union.dashboard')) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const FP    = window.FinancePlatform;
  const e     = window.APP_HELPERS.e;

  const ABADGE = { pending:'bs-pending', assigned:'bs-assigned', completed:'bs-completed', rejected:'bs-rejected' };
  const REC_LBL= { approve:'موافقة', reject:'رفض', needs_adjustment:'يحتاج تعديل' };

  async function load() {
    const r = await fetch(`${base}/finance/consultant-offices/${id}`, { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل بيانات المكتب');
    const j = await r.json();
    const o = j.data ?? j;

    document.getElementById('officeName').textContent = o.name ?? 'المكتب';
    document.getElementById('officeSub').textContent  = o.license_number ? `ترخيص: ${o.license_number}` : '';

    document.getElementById('infoGrid').innerHTML = [
      ['الاسم',          o.name],
      ['رقم الترخيص',    o.license_number],
      ['الهاتف',         o.phone],
      ['البريد',         o.email],
      ['العنوان',        o.address],
      ['الحالة',         FP.statusLabel(o.status)],
      ['عدد الإحالات',  o.assignments_count ?? (o.assignments?.length ?? 0)],
    ].map(([l,v]) => `<div class="info-item"><div class="info-lbl">${l}</div><div class="info-val">${e(v??'—')}</div></div>`).join('');

    // Assignments
    const assigns = o.assignments ?? [];
    document.getElementById('assignBody').innerHTML = assigns.length
      ? assigns.map(a => `<tr>
          <td>${e(a.application?.project_name??'—')}</td>
          <td>${e(a.assigned_at??a.created_at??'—')}</td>
          <td><span class="badge-s ${ABADGE[a.status]||'bs-pending'}">${FP.statusLabel(a.status)}</span></td>
          <td>${e(REC_LBL[a.report?.recommendation]??'—')}</td>
          <td><a href="consultant-report-form.php?assignment_id=${a.id}" class="btn-sm"><i class="bi bi-pencil-fill"></i> تقرير</a></td>
        </tr>`).join('')
      : '<tr><td colspan="5" class="empty">لا توجد إحالات</td></tr>';

    // Reports
    const reports = o.reports ?? assigns.filter(a=>a.report).map(a=>({...a.report, project_name: a.application?.project_name}));
    document.getElementById('reportBody').innerHTML = reports.length
      ? reports.map(rp => `<tr>
          <td>${e(rp.project_name??rp.application?.project_name??'—')}</td>
          <td>${e(REC_LBL[rp.recommendation]??rp.recommendation??'—')}</td>
          <td>${rp.feasibility_score != null ? rp.feasibility_score+'%' : '—'}</td>
          <td>${e(rp.created_at?.slice(0,10)??'—')}</td>
        </tr>`).join('')
      : '<tr><td colspan="4" class="empty">لا توجد تقارير</td></tr>';
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
