<?php
$basePath  = '../../';
$pageTitle = 'مؤشرات التمويل';
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
    .page-head{background:linear-gradient(135deg,#1e3a5f,#2563eb);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:20px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:20px;box-shadow:var(--c-shadow);text-align:center;}
    .kpi-icon{font-size:1.6rem;margin-bottom:8px;display:block;}
    .kpi-val{font-size:2rem;font-weight:800;color:var(--c-primary);}
    .kpi-lbl{font-size:.8rem;font-weight:700;color:var(--c-muted);margin-top:4px;}
    .kpi.danger .kpi-val{color:#dc2626;}
    .kpi.warning .kpi-val{color:#d97706;}
    .kpi.info .kpi-val{color:#2563eb;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:20px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.95rem;color:var(--c-text);margin:0 0 14px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .bar-row{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
    .bar-label{font-size:.82rem;font-weight:700;color:var(--c-muted);width:140px;flex-shrink:0;text-align:right;}
    .bar-track{flex:1;background:#f3f4f6;border-radius:8px;height:10px;overflow:hidden;}
    .bar-fill{height:100%;border-radius:8px;background:linear-gradient(90deg,var(--c-primary),var(--c-accent));transition:width .4s;}
    .bar-num{font-size:.82rem;font-weight:800;color:var(--c-text);width:60px;text-align:left;}
    .empty{text-align:center;padding:40px;color:var(--c-muted);}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-graph-up-arrow"></i> مؤشرات التمويل</h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px">إحصائيات شاملة لمنظومة التمويل</div>
    </div>
    <a href="finance.php" style="color:#fff;font-size:.88rem;font-weight:700;text-decoration:none;opacity:.85"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div id="loading" class="empty"><i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري تحميل المؤشرات...</div>

  <div id="content" style="display:none">
    <div class="kpi-grid" id="kpiGrid"></div>

    <div class="card">
      <div class="card-title"><i class="bi bi-bar-chart-fill"></i> توزيع الطلبات حسب الحالة</div>
      <div id="statusBars"></div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  if (!window.AppAuth.hasPermission('finance.metrics.view') && !window.AppAuth.isNationalAdmin()) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();

  try {
    const r = await fetch(`${base}/finance/metrics`, { headers:{ Authorization:`Bearer ${token()}` }});
    const json = await r.json();
    const d = json.data ?? json;

    const KPIS = [
      { icon:'bi-file-earmark-text-fill', label:'إجمالي الطلبات', val: d.total_applications??0, cls:'' },
      { icon:'bi-cash-coin',              label:'طلبات ممولة',     val: d.funded_applications??0, cls:'' },
      { icon:'bi-hourglass-split',        label:'قيد المعالجة',   val: d.pending_applications??0, cls:'warning' },
      { icon:'bi-exclamation-diamond-fill',label:'قروض متعثرة',  val: d.defaulted_loans??0,      cls:'danger' },
      { icon:'bi-check-circle-fill',      label:'قروض نشطة',      val: d.active_loans??0,         cls:'' },
      { icon:'bi-percent',                label:'نسبة السداد',    val: (d.repayment_rate??0)+'%', cls:'info' },
      { icon:'bi-bank',                   label:'إجمالي المبالغ', val: Number(d.total_funded_amount||0).toLocaleString('ar-SY')+' ل.س', cls:'' },
    ];

    document.getElementById('kpiGrid').innerHTML = KPIS.map(k => `
      <div class="kpi ${k.cls}">
        <i class="bi ${k.icon} kpi-icon" style="color:var(--c-primary)"></i>
        <div class="kpi-val">${k.val}</div>
        <div class="kpi-lbl">${k.label}</div>
      </div>`).join('');

    if (d.status_breakdown && Object.keys(d.status_breakdown).length) {
      const max = Math.max(...Object.values(d.status_breakdown));
      document.getElementById('statusBars').innerHTML = Object.entries(d.status_breakdown).map(([status, count]) => `
        <div class="bar-row">
          <span class="bar-label">${window.FinancePlatform.statusLabel(status)}</span>
          <div class="bar-track"><div class="bar-fill" style="width:${max?Math.round(count/max*100):0}%"></div></div>
          <span class="bar-num">${count}</span>
        </div>`).join('');
    } else {
      document.getElementById('statusBars').innerHTML = '<div style="color:var(--c-muted);text-align:center;padding:20px">لا توجد بيانات</div>';
    }

    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = '';
  } catch(e) {
    document.getElementById('loading').innerHTML = `<div style="color:#dc2626;font-weight:700">تعذر تحميل المؤشرات: ${e.message}</div>`;
  }
});
</script>
</body>
</html>
