<?php
$basePath  = '../../';
$pageTitle = 'القروض المتعثرة';
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
    .page-head{background:linear-gradient(135deg,#7f1d1d,#dc2626);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:20px;box-shadow:var(--c-shadow);}
    .tbl{width:100%;border-collapse:collapse;font-size:.88rem;}
    .tbl th{background:#fff5f5;color:var(--c-text);font-weight:800;padding:10px 14px;text-align:right;}
    .tbl td{padding:10px 14px;border-bottom:1px solid var(--c-border);color:var(--c-text);vertical-align:middle;}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:#fef2f2;}
    .badge-danger{background:#fee2e2;color:#dc2626;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;display:inline-block;}
    .btn-sm{padding:5px 12px;border-radius:10px;font-size:.8rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;}
    .btn-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);}
    .filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
    .form-control{border:1px solid var(--c-border);border-radius:10px;padding:8px 12px;font-size:.87rem;color:var(--c-text);}
    .empty{text-align:center;padding:40px;color:var(--c-muted);}
    .empty i{font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4;}
    .kpi-strip{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-bottom:18px;}
    .kpi{background:#fff;border:1px solid #fecaca;border-radius:14px;padding:14px;text-align:center;}
    .kpi-val{font-size:1.8rem;font-weight:800;color:#dc2626;}
    .kpi-lbl{font-size:.75rem;font-weight:700;color:var(--c-muted);margin-top:3px;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-exclamation-triangle-fill"></i> القروض المتعثرة</h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px">قروض تجاوزت مدة السداد</div>
    </div>
    <a href="finance.php" style="color:#fff;font-size:.88rem;font-weight:700;text-decoration:none;opacity:.85"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div class="kpi-strip" id="kpiStrip" style="display:none">
    <div class="kpi"><div class="kpi-val" id="kpiTotal">—</div><div class="kpi-lbl">إجمالي القروض المتعثرة</div></div>
    <div class="kpi"><div class="kpi-val" id="kpiAmount">—</div><div class="kpi-lbl">إجمالي المبالغ المتعثرة</div></div>
  </div>

  <div class="filter-bar">
    <input type="text" id="fSearch" class="form-control" placeholder="🔍 بحث بالمشروع أو رقم القرض..." style="max-width:260px">
  </div>

  <div class="card">
    <div id="loadingRow" class="empty"><i class="bi bi-hourglass-split"></i>جاري التحميل...</div>
    <div id="tableWrap" style="display:none;overflow-x:auto">
      <table class="tbl">
        <thead><tr>
          <th>رقم القرض</th><th>المشروع</th><th>جهة التمويل</th>
          <th>المبلغ</th><th>الحالة</th><th></th>
        </tr></thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  if (!window.AppAuth.hasPermission('finance.loans.view') && !window.FinancePlatform.canViewApplications()) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  let allRows = [];

  async function load() {
    const r = await fetch(`${base}/finance/defaulted?per_page=200`, {
      headers:{ Authorization:`Bearer ${token()}` }
    });
    const json = await r.json();
    allRows = json.data ?? [];

    document.getElementById('kpiTotal').textContent = allRows.length;
    const total = allRows.reduce((s, l) => s + Number(l.approved_amount||0), 0);
    document.getElementById('kpiAmount').textContent = total.toLocaleString('ar-SY') + ' ل.س';
    document.getElementById('kpiStrip').style.display = '';

    render();
    document.getElementById('loadingRow').style.display = 'none';
    document.getElementById('tableWrap').style.display = '';
  }

  function render() {
    const search = document.getElementById('fSearch').value.toLowerCase();
    const rows = allRows.filter(l =>
      !search || (l.loan_number||'').toLowerCase().includes(search) || (l.application?.project_name||'').toLowerCase().includes(search)
    );
    document.getElementById('tbody').innerHTML = rows.length
      ? rows.map(l => `<tr>
          <td style="font-weight:700">${window.APP_HELPERS.e(l.loan_number)}</td>
          <td>${window.APP_HELPERS.e(l.application?.project_name||'—')}</td>
          <td>${window.APP_HELPERS.e(l.partner?.name||'—')}</td>
          <td style="font-weight:700;color:#dc2626">${window.FinancePlatform.formatAmount(l.approved_amount, l.currency)}</td>
          <td><span class="badge-danger">${window.FinancePlatform.statusLabel(l.status)}</span></td>
          <td><a href="finance-loan-view.php?id=${l.id}" class="btn-sm btn-outline"><i class="bi bi-eye-fill"></i> تفاصيل</a></td>
        </tr>`).join('')
      : `<tr><td colspan="6" class="empty"><i class="bi bi-check-circle" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#16a34a;opacity:1"></i>لا توجد قروض متعثرة</td></tr>`;
  }

  document.getElementById('fSearch').addEventListener('input', render);
  load();
});
</script>
</body>
</html>
