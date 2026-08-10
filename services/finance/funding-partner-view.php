<?php
$basePath  = '../../';
$pageTitle = 'تفاصيل جهة التمويل';
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
    .bs-active{background:#dcfce7;color:#15803d;}
    .bs-pending{background:#fef3c7;color:#92400e;}
    .bs-funded{background:#dbeafe;color:#1d4ed8;}
    .bs-defaulted{background:#fce7f3;color:#9d174d;}
    .bs-closed{background:#f3f4f6;color:#4b5563;}
    .empty{text-align:center;padding:24px;color:var(--c-muted);font-size:.87rem;}
    .btn-sm{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:9px;padding:5px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
  </style>
</head>
<body>
<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-bank"></i> <span id="partnerName">جهة التمويل</span></h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px" id="partnerSub"></div>
    </div>
    <a href="funding-partners.php" class="btn-outline"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="card">
      <div class="card-title"><i class="bi bi-info-circle-fill"></i> معلومات جهة التمويل</div>
      <div class="info-grid" id="infoGrid"></div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-clipboard2-check-fill"></i> الإحالات الممنوحة</div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>المشروع</th><th>تاريخ الإحالة</th><th>الحالة</th><th>المبلغ المعتمد</th><th></th></tr></thead>
          <tbody id="assignBody"></tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-cash-stack"></i> القروض الممنوحة</div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>رقم القرض</th><th>المشروع</th><th>المبلغ</th><th>عدد الأقساط</th><th>الحالة</th><th></th></tr></thead>
          <tbody id="loansBody"></tbody>
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
  if (!id) { location.href = 'funding-partners.php'; return; }

  if (!window.AppAuth.hasPermission('finance.partners.view')) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const FP    = window.FinancePlatform;
  const e     = window.APP_HELPERS.e;

  const TYPE_LBL = { bank:'بنك', investment_fund:'صندوق استثماري', microfinance:'تمويل أصغر', development_fund:'صندوق تنموي' };
  const ABADGE   = { pending:'bs-pending', assigned:'bs-funded', completed:'bs-active', rejected:'bs-defaulted' };
  const LBADGE   = { active:'bs-active', funded:'bs-funded', defaulted:'bs-defaulted', closed:'bs-closed', paid:'bs-active' };

  async function load() {
    const r = await fetch(`${window.APP_ROUTES.fundingPartners()}/${id}`, { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل بيانات جهة التمويل');
    const j = await r.json();
    const p = j.data ?? j;

    document.getElementById('partnerName').textContent = p.name ?? 'جهة التمويل';
    document.getElementById('partnerSub').textContent  = TYPE_LBL[p.type] ?? p.type ?? '';

    document.getElementById('infoGrid').innerHTML = [
      ['الاسم',         p.name],
      ['النوع',         TYPE_LBL[p.type]??p.type],
      ['الهاتف',        p.phone],
      ['البريد',        p.email],
      ['الموقع',        p.website],
      ['الحالة',        FP.statusLabel(p.status)],
      ['عدد الإحالات', p.assignments?.length ?? p.assignments_count ?? 0],
      ['عدد القروض',   p.loans?.length ?? p.loans_count ?? 0],
    ].map(([l,v]) => `<div class="info-item"><div class="info-lbl">${l}</div><div class="info-val">${e(v??'—')}</div></div>`).join('');

    // Assignments
    const assigns = p.assignments ?? [];
    document.getElementById('assignBody').innerHTML = assigns.length
      ? assigns.map(a=>`<tr>
          <td>${e(a.application?.project_name??'—')}</td>
          <td>${e(a.assigned_at??a.created_at?.slice(0,10)??'—')}</td>
          <td><span class="badge-s ${ABADGE[a.status]||'bs-pending'}">${FP.statusLabel(a.status)}</span></td>
          <td style="font-weight:700">${a.approved_amount ? FP.formatAmount(a.approved_amount, a.currency) : '—'}</td>
          <td><a href="finance-apply.php?id=${a.application_id}" class="btn-sm"><i class="bi bi-eye-fill"></i> الطلب</a></td>
        </tr>`).join('')
      : '<tr><td colspan="5" class="empty">لا توجد إحالات</td></tr>';

    // Loans
    const loans = p.loans ?? [];
    document.getElementById('loansBody').innerHTML = loans.length
      ? loans.map(l=>`<tr>
          <td style="font-weight:700">${e(l.loan_number??'—')}</td>
          <td>${e(l.application?.project_name??'—')}</td>
          <td style="font-weight:700">${FP.formatAmount(l.approved_amount, l.currency)}</td>
          <td>${l.installment_count??'—'} قسط</td>
          <td><span class="badge-s ${LBADGE[l.status]||'bs-pending'}">${FP.statusLabel(l.status)}</span></td>
          <td><a href="finance-loan-view.php?id=${l.id}" class="btn-sm"><i class="bi bi-eye-fill"></i> عرض</a></td>
        </tr>`).join('')
      : '<tr><td colspan="6" class="empty">لا توجد قروض</td></tr>';
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
