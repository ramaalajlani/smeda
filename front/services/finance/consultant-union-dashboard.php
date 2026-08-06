<?php
$basePath  = '../../';
$pageTitle = 'لوحة نقابة الاقتصاديين الأحرار';
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
    .page-head{background:linear-gradient(135deg,#3b0764,#7c3aed);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .page-head p{margin:6px 0 0;opacity:.8;font-size:.87rem;}
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:20px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:18px 20px;box-shadow:var(--c-shadow);}
    .kpi-val{font-size:1.7rem;font-weight:900;color:#7c3aed;line-height:1;}
    .kpi-lbl{font-size:.78rem;color:var(--c-muted);font-weight:700;margin-top:6px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:#7c3aed;}
    .tbl{width:100%;border-collapse:collapse;font-size:.86rem;}
    .tbl th{background:#f5f3ff;color:var(--c-text);font-weight:800;padding:9px 12px;text-align:right;}
    .tbl td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:#faf5ff;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-pending{background:#fef3c7;color:#92400e;}
    .bs-assigned{background:#ede9fe;color:#7c3aed;}
    .bs-completed{background:#dcfce7;color:#15803d;}
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-revoked{background:#fee2e2;color:#dc2626;}
    .btn-sm{background:#f5f3ff;color:#7c3aed;border:none;border-radius:9px;padding:5px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
    .btn-sm:hover{background:#ede9fe;}
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
    .form-control,.form-select{border:1px solid var(--c-border);border-radius:10px;padding:7px 11px;font-size:.85rem;color:var(--c-text);}
    .empty{text-align:center;padding:28px;color:var(--c-muted);font-size:.88rem;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <h1><i class="bi bi-people-fill"></i> لوحة نقابة الاقتصاديين الأحرار</h1>
    <p>الإشراف على المكاتب الاستشارية ومتابعة إحالات الدراسات</p>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="kpi-grid" id="kpiGrid"></div>

    <div class="card">
      <div class="card-title"><i class="bi bi-building"></i> المكاتب المسجلة</div>
      <div class="filters">
        <input type="text" id="offSrch" class="form-control" placeholder="بحث بالاسم..." style="max-width:220px" oninput="filterOffices()">
        <select id="offStatus" class="form-select" style="max-width:160px" onchange="filterOffices()">
          <option value="">كل الحالات</option>
          <option value="active">نشط</option>
          <option value="suspended">موقوف</option>
          <option value="revoked">ملغى</option>
        </select>
      </div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>اسم المكتب</th><th>الترخيص</th><th>الهاتف</th><th>الحالة</th><th>الإحالات</th><th></th></tr></thead>
          <tbody id="officesBody"></tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-clipboard2-check-fill"></i> آخر الإحالات</div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>المشروع</th><th>المكتب</th><th>تاريخ الإحالة</th><th>الحالة</th><th></th></tr></thead>
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

  if (!window.AppAuth.hasPermission('finance.consultant_union.dashboard')) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const FP    = window.FinancePlatform;
  const e     = window.APP_HELPERS.e;

  const ABADGE = { pending:'bs-pending', assigned:'bs-assigned', completed:'bs-completed' };
  const OBADGE = { active:'bs-active', suspended:'bs-pending', revoked:'bs-revoked' };
  let allOffices = [], allAssigns = [];

  async function load() {
    const [or, ar] = await Promise.all([
      fetch(`${base}/finance/consultant-offices`,    { headers:{ Authorization:`Bearer ${token()}` }}),
      fetch(`${base}/finance/consultant-assignments`,{ headers:{ Authorization:`Bearer ${token()}` }})
    ]);
    const oj = await or.json();
    const aj = await ar.json();
    allOffices = oj.data ?? oj ?? [];
    allAssigns = (aj.data ?? aj ?? []).slice(0,30);

    // KPIs
    const active    = allOffices.filter(o=>o.status==='active').length;
    const pending   = allAssigns.filter(a=>a.status==='pending').length;
    const completed = allAssigns.filter(a=>a.status==='completed').length;

    document.getElementById('kpiGrid').innerHTML = [
      ['bi-building','المكاتب المسجلة', allOffices.length],
      ['bi-check-circle-fill','مكاتب نشطة', active],
      ['bi-clipboard2-check','إحالات مكتملة', completed],
      ['bi-hourglass-split','إحالات معلقة', pending],
    ].map(([ic,lbl,val])=>`
      <div class="kpi">
        <div class="kpi-val"><i class="bi ${ic}" style="font-size:1rem;opacity:.5"></i> ${val}</div>
        <div class="kpi-lbl">${lbl}</div>
      </div>`).join('');

    filterOffices();
    renderAssigns();
  }

  window.filterOffices = function() {
    const q  = document.getElementById('offSrch').value.toLowerCase();
    const st = document.getElementById('offStatus').value;
    const list = allOffices.filter(o =>
      (!q || (o.name??'').toLowerCase().includes(q))
      && (!st || o.status === st)
    );
    document.getElementById('officesBody').innerHTML = list.length
      ? list.map(o=>`<tr>
          <td style="font-weight:700">${e(o.name)}</td>
          <td>${e(o.license_number??'—')}</td>
          <td>${e(o.phone??'—')}</td>
          <td><span class="badge-s ${OBADGE[o.status]||'bs-active'}">${FP.statusLabel(o.status)}</span></td>
          <td>${o.assignments_count??'—'}</td>
          <td><a href="consultant-office-view.php?id=${o.id}" class="btn-sm"><i class="bi bi-eye-fill"></i> عرض</a></td>
        </tr>`).join('')
      : '<tr><td colspan="6" class="empty">لا توجد مكاتب</td></tr>';
  };

  function renderAssigns() {
    document.getElementById('assignsBody').innerHTML = allAssigns.length
      ? allAssigns.map(a=>`<tr>
          <td>${e(a.application?.project_name??'—')}</td>
          <td>${e(a.office?.name??'—')}</td>
          <td>${e(a.assigned_at??a.created_at?.slice(0,10)??'—')}</td>
          <td><span class="badge-s ${ABADGE[a.status]||'bs-pending'}">${FP.statusLabel(a.status)}</span></td>
          <td><a href="consultant-office-view.php?id=${a.office_id}" class="btn-sm"><i class="bi bi-building"></i> المكتب</a></td>
        </tr>`).join('')
      : '<tr><td colspan="5" class="empty">لا توجد إحالات</td></tr>';
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
