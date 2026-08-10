<?php
$basePath  = '../../';
$pageTitle = 'الطلبات المحالة إليّ';
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
    .pw{max-width:1000px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#0f5e4f,var(--c-primary));border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .page-head p{margin:6px 0 0;opacity:.8;font-size:.87rem;}
    .kpi-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
    @media(max-width:600px){.kpi-strip{grid-template-columns:1fr 1fr;}}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:16px 20px;box-shadow:var(--c-shadow);}
    .kpi-val{font-size:1.6rem;font-weight:900;color:var(--c-primary);}
    .kpi-lbl{font-size:.78rem;color:var(--c-muted);font-weight:700;margin-top:4px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
    .form-control,.form-select{border:1px solid var(--c-border);border-radius:10px;padding:7px 11px;font-size:.85rem;color:var(--c-text);}
    .tbl{width:100%;border-collapse:collapse;font-size:.86rem;}
    .tbl th{background:var(--c-soft);color:var(--c-text);font-weight:800;padding:9px 12px;text-align:right;}
    .tbl td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:#f9fffe;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-pending{background:#fef3c7;color:#92400e;}
    .bs-assigned{background:#dbeafe;color:#1d4ed8;}
    .bs-completed{background:#dcfce7;color:#15803d;}
    .bs-no-report{background:#f3f4f6;color:#4b5563;}
    .bs-has-report{background:#dcfce7;color:#15803d;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:5px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
    .btn-sm:hover{background:#d0f3ec;}
    .btn-sm-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:9px;padding:5px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
    .empty{text-align:center;padding:30px;color:var(--c-muted);font-size:.88rem;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <h1><i class="bi bi-clipboard2-check-fill"></i> الطلبات المحالة إليّ</h1>
    <p>قائمة طلبات التمويل المحالة لمكتبي للدراسة وإعداد التقارير</p>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="kpi-strip" id="kpiStrip"></div>

    <div class="card">
      <div class="card-title"><i class="bi bi-table"></i> الإحالات</div>
      <div class="filters">
        <input type="text" id="srch" class="form-control" placeholder="بحث بالمشروع..." style="max-width:220px" oninput="filterAssigns()">
        <select id="statusF" class="form-select" style="max-width:160px" onchange="filterAssigns()">
          <option value="">كل الحالات</option>
          <option value="pending">معلق</option>
          <option value="assigned">محال</option>
          <option value="completed">مكتمل</option>
        </select>
        <select id="reportF" class="form-select" style="max-width:170px" onchange="filterAssigns()">
          <option value="">كل التقارير</option>
          <option value="yes">تم رفع تقرير</option>
          <option value="no">بدون تقرير</option>
        </select>
      </div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr>
            <th>المشروع</th><th>تاريخ الإحالة</th><th>الحالة</th><th>التقرير</th><th>التوصية</th><th>إجراء</th>
          </tr></thead>
          <tbody id="assignsBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  if (!window.AppAuth.hasPermission('finance.consultant_assignments.view_own')) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const FP    = window.FinancePlatform;
  const e     = window.APP_HELPERS.e;

  const ABADGE = { pending:'bs-pending', assigned:'bs-assigned', completed:'bs-completed' };
  const REC_LBL= { approve:'موافقة', reject:'رفض', needs_adjustment:'يحتاج تعديل' };
  let allAssigns = [];

  async function load() {
    const r = await fetch(`${base}/finance/my-consultant-assignments`, { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل الإحالات');
    const j = await r.json();
    allAssigns = j.data ?? j ?? [];

    const total     = allAssigns.length;
    const pending   = allAssigns.filter(a=>a.status==='pending'||a.status==='assigned').length;
    const completed = allAssigns.filter(a=>a.status==='completed').length;

    document.getElementById('kpiStrip').innerHTML = [
      ['bi-clipboard2-check','إجمالي الإحالات', total],
      ['bi-hourglass-split','قيد الدراسة', pending],
      ['bi-check-circle-fill','مكتملة', completed],
    ].map(([ic,lbl,val])=>`<div class="kpi"><div class="kpi-val"><i class="bi ${ic}" style="font-size:1rem;opacity:.5"></i> ${val}</div><div class="kpi-lbl">${lbl}</div></div>`).join('');

    filterAssigns();
  }

  window.filterAssigns = function() {
    const q  = document.getElementById('srch').value.toLowerCase();
    const st = document.getElementById('statusF').value;
    const rp = document.getElementById('reportF').value;
    const list = allAssigns.filter(a => {
      const txt = (a.application?.project_name??'').toLowerCase();
      const hasRep = !!a.report;
      return (!q  || txt.includes(q))
          && (!st || a.status === st)
          && (!rp || (rp==='yes'? hasRep : !hasRep));
    });
    document.getElementById('assignsBody').innerHTML = list.length
      ? list.map(a=>`<tr>
          <td style="font-weight:700">${e(a.application?.project_name??'—')}</td>
          <td>${e(a.assigned_at??a.created_at?.slice(0,10)??'—')}</td>
          <td><span class="badge-s ${ABADGE[a.status]||'bs-pending'}">${FP.statusLabel(a.status)}</span></td>
          <td><span class="badge-s ${a.report?'bs-has-report':'bs-no-report'}">${a.report?'مرفوع':'لم يرفع'}</span></td>
          <td>${a.report ? (REC_LBL[a.report.recommendation]??a.report.recommendation) : '—'}</td>
          <td style="display:flex;gap:6px">
            <a href="finance-apply.php?id=${a.application_id}" class="btn-sm"><i class="bi bi-eye-fill"></i> الطلب</a>
            <a href="consultant-report-form.php?assignment_id=${a.id}" class="btn-sm-primary"><i class="bi bi-pencil-fill"></i> ${a.report?'تعديل':'رفع'} تقرير</a>
          </td>
        </tr>`).join('')
      : '<tr><td colspan="6" class="empty">لا توجد إحالات</td></tr>';
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
