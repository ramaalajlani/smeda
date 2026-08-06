<?php
$basePath  = '../../';
$pageTitle = 'القروض الممولة';
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
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:20px;box-shadow:var(--c-shadow);}
    .tbl{width:100%;border-collapse:collapse;font-size:.88rem;}
    .tbl th{background:var(--c-soft);color:var(--c-text);font-weight:800;padding:10px 14px;text-align:right;}
    .tbl td{padding:10px 14px;border-bottom:1px solid var(--c-border);color:var(--c-text);vertical-align:middle;}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:#f9fafb;}
    .badge-status{padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;display:inline-block;}
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-funded{background:#dbeafe;color:#1d4ed8;}
    .bs-closed{background:#f3f4f6;color:#6b7280;}
    .bs-defaulted{background:#fee2e2;color:#dc2626;}
    .btn-sm{padding:5px 12px;border-radius:10px;font-size:.8rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;}
    .btn-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);}
    .filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
    .form-control{border:1px solid var(--c-border);border-radius:10px;padding:8px 12px;font-size:.87rem;color:var(--c-text);}
    .empty{text-align:center;padding:40px;color:var(--c-muted);}
    .empty i{font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-cash-stack"></i> القروض الممولة</h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px">القروض النشطة والمسددة</div>
    </div>
    <a href="finance.php" style="color:#fff;font-size:.88rem;font-weight:700;text-decoration:none;opacity:.85"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div class="filter-bar">
    <input type="text" id="fSearch" class="form-control" placeholder="🔍 بحث بالمشروع أو رقم القرض..." style="max-width:260px">
    <select id="fStatus" class="form-control" style="max-width:160px">
      <option value="">كل الحالات</option>
      <option value="active">نشط</option>
      <option value="paid">مسدد</option>
      <option value="closed">مغلق</option>
    </select>
  </div>

  <div class="card">
    <div id="loadingRow" class="empty"><i class="bi bi-hourglass-split"></i>جاري التحميل...</div>
    <div id="tableWrap" style="display:none;overflow-x:auto">
      <table class="tbl">
        <thead><tr>
          <th>رقم القرض</th><th>المشروع</th><th>جهة التمويل</th>
          <th>المبلغ</th><th>الأقساط</th><th>الحالة</th><th></th>
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

  const STATUS_BADGES = {
    active:'bs-active',funded:'bs-funded',paid:'bs-closed',closed:'bs-closed',defaulted:'bs-defaulted'
  };

  async function load() {
    const r = await fetch(`${base}/finance/funded?per_page=200`, {
      headers:{ Authorization:`Bearer ${token()}` }
    });
    const json = await r.json();
    allRows = json.data ?? [];
    render();
    document.getElementById('loadingRow').style.display = 'none';
    document.getElementById('tableWrap').style.display = '';
  }

  function render() {
    const search = document.getElementById('fSearch').value.toLowerCase();
    const status = document.getElementById('fStatus').value;
    const rows = allRows.filter(l =>
      (!search || (l.loan_number||'').toLowerCase().includes(search) || (l.application?.project_name||'').toLowerCase().includes(search)) &&
      (!status || l.status === status)
    );
    document.getElementById('tbody').innerHTML = rows.length
      ? rows.map(l => `<tr>
          <td style="font-weight:700">${window.APP_HELPERS.e(l.loan_number)}</td>
          <td>${window.APP_HELPERS.e(l.application?.project_name||'—')}</td>
          <td>${window.APP_HELPERS.e(l.partner?.name||'—')}</td>
          <td style="font-weight:700">${window.FinancePlatform.formatAmount(l.approved_amount, l.currency)}</td>
          <td>${l.installment_count??'—'} قسط</td>
          <td><span class="badge-status ${STATUS_BADGES[l.status]||'bs-closed'}">${window.FinancePlatform.statusLabel(l.status)}</span></td>
          <td><a href="finance-loan-view.php?id=${l.id}" class="btn-sm btn-outline"><i class="bi bi-eye-fill"></i> تفاصيل</a></td>
        </tr>`).join('')
      : `<tr><td colspan="7" class="empty"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>لا توجد قروض</td></tr>`;
  }

  document.getElementById('fSearch').addEventListener('input', render);
  document.getElementById('fStatus').addEventListener('change', render);
  load();
});
</script>
</body>
</html>
