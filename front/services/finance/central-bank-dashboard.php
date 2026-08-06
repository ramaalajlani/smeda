<?php
$basePath  = '../../';
$pageTitle = 'لوحة مصرف سورية المركزي';
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
    .page-head{background:linear-gradient(135deg,#0f5e4f,var(--c-primary));border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .page-head p{margin:6px 0 0;opacity:.8;font-size:.87rem;}
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:20px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:18px 20px;box-shadow:var(--c-shadow);}
    .kpi-val{font-size:1.7rem;font-weight:900;color:var(--c-primary);line-height:1;}
    .kpi-lbl{font-size:.78rem;color:var(--c-muted);font-weight:700;margin-top:6px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
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
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-pending{background:#fef3c7;color:#92400e;}
    .bs-funded{background:#dbeafe;color:#1d4ed8;}
    .bs-rejected{background:#fee2e2;color:#dc2626;}
    .bs-defaulted{background:#fce7f3;color:#9d174d;}
    .bs-closed{background:#f3f4f6;color:#4b5563;}
    .empty{text-align:center;padding:30px;color:var(--c-muted);font-size:.88rem;}
    .btn-sm-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);border-radius:8px;padding:4px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
    .btn-sm-outline:hover{background:var(--c-soft);}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <h1><i class="bi bi-bank"></i> لوحة مصرف سورية المركزي</h1>
    <p>الإشراف على البنوك وجهات التمويل ومتابعة محفظة القروض</p>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="kpi-grid" id="kpiGrid"></div>

    <div class="card">
      <div class="card-title"><i class="bi bi-table"></i> قائمة القروض الممنوحة</div>
      <div class="filters">
        <input type="text" id="srch" class="form-control" placeholder="بحث بالمشروع أو رقم القرض..." style="max-width:240px" oninput="filterLoans()">
        <select id="statusFilter" class="form-select" style="max-width:170px" onchange="filterLoans()">
          <option value="">كل الحالات</option>
          <option value="active">نشط</option>
          <option value="funded">ممول</option>
          <option value="defaulted">متعثر</option>
          <option value="closed">مغلق</option>
          <option value="paid">مسدد</option>
        </select>
        <select id="partnerFilter" class="form-select" style="max-width:200px" onchange="filterLoans()">
          <option value="">كل الجهات</option>
        </select>
      </div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr>
            <th>رقم القرض</th><th>المشروع</th><th>جهة التمويل</th>
            <th>المبلغ</th><th>الأقساط</th><th>الحالة</th><th></th>
          </tr></thead>
          <tbody id="loansBody"></tbody>
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

  if (!window.AppAuth.hasPermission('finance.central_bank.dashboard')) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const FP    = window.FinancePlatform;

  const STATUS_BADGE = {
    active:'bs-active', pending:'bs-pending', funded:'bs-funded',
    rejected:'bs-rejected', defaulted:'bs-defaulted', closed:'bs-closed', paid:'bs-active'
  };

  let allLoans = [], allPartners = [];

  async function load() {
    const [lr, pr] = await Promise.all([
      fetch(`${base}/finance/loans?per_page=500`, { headers:{ Authorization:`Bearer ${token()}` }}),
      fetch(window.APP_ROUTES.fundingPartners(),   { headers:{ Authorization:`Bearer ${token()}` }})
    ]);
    const lj = await lr.json();
    const pj = await pr.json();
    allLoans   = lj.data ?? lj ?? [];
    allPartners= pj.data ?? pj ?? [];

    // KPIs
    const total      = allLoans.length;
    const active     = allLoans.filter(l=>l.status==='active').length;
    const defaulted  = allLoans.filter(l=>l.status==='defaulted').length;
    const totalAmt   = allLoans.reduce((s,l)=>s+Number(l.approved_amount||0),0);

    document.getElementById('kpiGrid').innerHTML = [
      ['bi-cash-stack','إجمالي القروض', total],
      ['bi-check-circle-fill','قروض نشطة', active],
      ['bi-exclamation-triangle-fill','متعثرة', defaulted],
      ['bi-bank','جهات التمويل', allPartners.length],
      ['bi-currency-dollar','إجمالي المبالغ', FP.formatAmount(totalAmt)],
    ].map(([ic,lbl,val])=>`
      <div class="kpi">
        <div class="kpi-val"><i class="bi ${ic}" style="font-size:1rem;opacity:.5"></i> ${val}</div>
        <div class="kpi-lbl">${lbl}</div>
      </div>`).join('');

    // Partner filter
    const pSel = document.getElementById('partnerFilter');
    allPartners.forEach(p => {
      const o = document.createElement('option');
      o.value = p.id; o.textContent = p.name;
      pSel.appendChild(o);
    });

    filterLoans();
  }

  window.filterLoans = function() {
    const q  = document.getElementById('srch').value.toLowerCase();
    const st = document.getElementById('statusFilter').value;
    const pt = document.getElementById('partnerFilter').value;

    const rows = allLoans.filter(l => {
      const txt = ((l.loan_number??'')+(l.application?.project_name??'')).toLowerCase();
      return (!q || txt.includes(q))
          && (!st || l.status === st)
          && (!pt || String(l.funding_partner_id) === pt);
    });

    const tbody = document.getElementById('loansBody');
    tbody.innerHTML = rows.length ? rows.map(l => `<tr>
      <td style="font-weight:700">${window.APP_HELPERS.e(l.loan_number??'—')}</td>
      <td>${window.APP_HELPERS.e(l.application?.project_name??'—')}</td>
      <td>${window.APP_HELPERS.e(l.partner?.name??'—')}</td>
      <td style="font-weight:700">${FP.formatAmount(l.approved_amount, l.currency)}</td>
      <td>${l.installment_count??'—'} قسط</td>
      <td><span class="badge-s ${STATUS_BADGE[l.status]||'bs-pending'}">${FP.statusLabel(l.status)}</span></td>
      <td><a href="finance-loan-view.php?id=${l.id}" class="btn-sm-outline"><i class="bi bi-eye-fill"></i> عرض</a></td>
    </tr>`).join('') : '<tr><td colspan="7" class="empty">لا توجد نتائج</td></tr>';
  };

  try {
    await load();
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = '';
  } catch(e) {
    document.getElementById('loading').innerHTML = `<div style="color:#dc2626;font-weight:700">${e.message}</div>`;
  }
});
</script>
</body>
</html>
