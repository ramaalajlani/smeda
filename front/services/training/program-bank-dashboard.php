<?php
$basePath  = '../../';
$pageTitle = 'بنك البرامج التدريبية';
$activePage= 'program-bank-dashboard';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <style>
    :root{--c:#1a6b5a;--ca:#10b981;--cs:#e8f8f3;--cb:rgba(26,107,90,.12);--ct:#15312a;--cm:#6b7280;--sh:0 8px 24px rgba(15,60,50,.07);}
    body{background:linear-gradient(160deg,#f0faf7 0%,#e7f6f1 100%);min-height:100vh;}
    .pw{width:100%;max-width:1340px;margin:0 auto;padding:0 0 28px;}
    @media(max-width:767px){
      .hero{padding:18px 14px;}
      .hero h1{font-size:1.2rem;}
      .hero-actions{width:100%;margin-right:0;}
      .btn-hero{flex:1;justify-content:center;min-height:44px;}
      .kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;}
      .col2{grid-template-columns:1fr !important;}
    }

    /* Hero */
    .hero{background:linear-gradient(135deg,#0d4d3f,var(--c),var(--ca));color:#fff;border-radius:22px;padding:26px 30px;margin-bottom:20px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;position:relative;overflow:hidden;}
    .hero::before{content:"";position:absolute;width:280px;height:280px;border-radius:50%;top:-140px;left:-60px;background:radial-gradient(circle,rgba(255,255,255,.15),transparent 70%);}
    .hero-icon{width:62px;height:62px;border-radius:18px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:2.1rem;flex-shrink:0;}
    .hero h1{font-size:1.6rem;font-weight:800;margin:0 0 4px;}
    .hero p{margin:0;opacity:.85;font-size:.9rem;}
    .hero-actions{margin-right:auto;display:flex;gap:8px;flex-wrap:wrap;}
    @media(max-width:600px){.hero{padding:20px 16px;}.hero h1{font-size:1.25rem;}.hero-actions{margin-right:0;width:100%;}}
    .btn-hero{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:12px;font-weight:700;font-size:.87rem;border:none;cursor:pointer;text-decoration:none;transition:.2s;}
    .btn-white{background:#fff;color:var(--c);}
    .btn-ghost{background:rgba(255,255,255,.18);color:#fff;border:1.5px solid rgba(255,255,255,.4);}

    /* KPI grid */
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:13px;margin-bottom:20px;}
    .kpi{background:#fff;border:1px solid var(--cb);border-radius:18px;padding:18px 15px 14px;box-shadow:var(--sh);position:relative;overflow:hidden;cursor:pointer;transition:transform .2s,box-shadow .2s;text-decoration:none;display:block;color:inherit;}
    .kpi:hover{transform:translateY(-3px);box-shadow:0 16px 32px rgba(15,60,50,.12);}
    .kpi::before{content:"";position:absolute;top:0;right:0;left:0;height:4px;border-radius:18px 18px 0 0;}
    .kpi.green::before{background:linear-gradient(90deg,#15803d,#4ade80);}
    .kpi.amber::before{background:linear-gradient(90deg,#b45309,#fbbf24);}
    .kpi.slate::before{background:linear-gradient(90deg,#475569,#94a3b8);}
    .kpi.red::before{background:linear-gradient(90deg,#be123c,#f87171);}
    .kpi.blue::before{background:linear-gradient(90deg,#1d4ed8,#60a5fa);}
    .kpi.purple::before{background:linear-gradient(90deg,#7c3aed,#c4b5fd);}
    .kpi.teal::before{background:linear-gradient(90deg,var(--c),var(--ca));}
    .kpi.indigo::before{background:linear-gradient(90deg,#3730a3,#818cf8);}
    .kpi.orange::before{background:linear-gradient(90deg,#c2410c,#fdba74);}
    .kpi-icon{font-size:1.5rem;margin-bottom:7px;}
    .kpi-val{font-size:2.1rem;font-weight:800;color:var(--ct);line-height:1;}
    .kpi-lbl{font-size:.76rem;font-weight:700;color:var(--cm);margin-top:4px;line-height:1.3;}

    /* Cards */
    .sec{background:#fff;border:1px solid var(--cb);border-radius:20px;padding:20px;box-shadow:var(--sh);margin-bottom:18px;}
    .sec-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;}
    .sec-title{font-weight:800;font-size:.97rem;color:var(--ct);display:flex;align-items:center;gap:8px;}
    .sec-title i{color:var(--c);}
    .sec-link{font-size:.82rem;font-weight:700;color:var(--c);text-decoration:none;}
    .col2{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
    @media(max-width:860px){.col2{grid-template-columns:1fr;}}

    /* Status badges */
    .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;}
    .badge-draft{background:#f1f5f9;color:#475569;}
    .badge-review{background:#fef3c7;color:#92400e;}
    .badge-approved{background:#dcfce7;color:#15803d;}
    .badge-suspended{background:#fee2e2;color:#991b1b;}
    .badge-archived{background:#f1f5f9;color:#334155;}

    /* Program rows */
    .prog-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--cb);}
    .prog-row:last-child{border-bottom:none;}
    .prog-icon{width:40px;height:40px;border-radius:12px;background:var(--cs);display:flex;align-items:center;justify-content:center;color:var(--c);font-size:1.2rem;flex-shrink:0;}
    .prog-info{flex:1;min-width:0;}
    .prog-name{font-size:.87rem;font-weight:700;color:var(--ct);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .prog-meta{font-size:.75rem;color:var(--cm);margin-top:2px;}
    .prog-count{font-size:1.2rem;font-weight:800;color:var(--c);min-width:32px;text-align:center;}

    /* Chart donut */
    .donut-wrap{display:flex;align-items:center;gap:20px;flex-wrap:wrap;}
    .donut-legend{display:flex;flex-direction:column;gap:6px;}
    .leg-item{display:flex;align-items:center;gap:8px;font-size:.8rem;font-weight:600;}
    .leg-dot{width:12px;height:12px;border-radius:4px;flex-shrink:0;}

    /* Quick actions */
    .qa-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;}
    .qa-btn{display:flex;flex-direction:column;align-items:center;gap:8px;padding:14px 10px;border-radius:16px;background:var(--cs);border:1.5px solid var(--cb);cursor:pointer;text-decoration:none;font-weight:700;color:var(--ct);font-size:.82rem;text-align:center;transition:.2s;}
    .qa-btn:hover{background:var(--c);color:#fff;border-color:var(--c);}
    .qa-btn i{font-size:1.5rem;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/program-bank-shell-open.php'; ?>

<div class="pw">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-icon">🎓</div>
    <div>
      <h1>بنك البرامج والحقائب التدريبية</h1>
      <p>إدارة شاملة للبرامج التدريبية واعتمادها وربطها بخدمات الهيئة</p>
    </div>
    <div class="hero-actions">
      <a href="program-bank-form.php" class="btn-hero btn-white"><i class="bi bi-plus-lg"></i> برنامج جديد</a>
      <a href="program-bank-list.php" class="btn-hero btn-ghost"><i class="bi bi-list-ul"></i> كل البرامج</a>
    </div>
  </div>

  <!-- KPI Grid -->
  <div class="kpi-grid" id="kpiGrid">
    <div class="kpi green"><div class="kpi-icon">✅</div><div class="kpi-val" id="k-approved">—</div><div class="kpi-lbl">برامج معتمدة</div></div>
    <div class="kpi amber"><div class="kpi-icon">🔄</div><div class="kpi-val" id="k-review">—</div><div class="kpi-lbl">قيد المراجعة</div></div>
    <div class="kpi slate"><div class="kpi-icon">📝</div><div class="kpi-val" id="k-draft">—</div><div class="kpi-lbl">مسودات</div></div>
    <div class="kpi red"><div class="kpi-icon">⏸️</div><div class="kpi-val" id="k-suspended">—</div><div class="kpi-lbl">موقوفة</div></div>
    <div class="kpi teal"><div class="kpi-icon">📦</div><div class="kpi-val" id="k-total">—</div><div class="kpi-lbl">إجمالي البرامج</div></div>
    <div class="kpi blue"><div class="kpi-icon">🗺️</div><div class="kpi-val" id="k-needs">—</div><div class="kpi-lbl">مرتبطة بخريطة الاحتياجات</div></div>
    <div class="kpi purple"><div class="kpi-icon">💰</div><div class="kpi-val" id="k-finance">—</div><div class="kpi-lbl">مرتبطة بالتمويل</div></div>
    <div class="kpi indigo"><div class="kpi-icon">🏢</div><div class="kpi-val" id="k-incubators">—</div><div class="kpi-lbl">مرتبطة بالحاضنات</div></div>
    <div class="kpi orange"><div class="kpi-icon">🔔</div><div class="kpi-val" id="k-update">—</div><div class="kpi-lbl">تحتاج تحديث</div></div>
  </div>

  <div class="col2">

    <!-- الأكثر تنفيذاً -->
    <div class="sec">
      <div class="sec-hd">
        <div class="sec-title"><i class="bi bi-trophy"></i> الأكثر تنفيذاً</div>
        <a href="program-bank-list.php?sort=executions" class="sec-link">عرض الكل</a>
      </div>
      <div id="mostExecuted"><div style="text-align:center;padding:20px;color:#9ca3af;">جاري التحميل...</div></div>
    </div>

    <!-- توزيع حسب النوع -->
    <div class="sec">
      <div class="sec-hd">
        <div class="sec-title"><i class="bi bi-pie-chart"></i> توزيع حسب النوع</div>
      </div>
      <div class="donut-wrap" id="typeChart">
        <svg id="donutSvg" width="120" height="120" viewBox="0 0 120 120" style="flex-shrink:0"></svg>
        <div class="donut-legend" id="donutLegend"></div>
      </div>
    </div>

  </div>

  <!-- إجراءات سريعة -->
  <div class="sec">
    <div class="sec-hd">
      <div class="sec-title"><i class="bi bi-lightning"></i> إجراءات سريعة</div>
    </div>
    <div class="qa-grid">
      <a href="program-bank-form.php" class="qa-btn"><i class="bi bi-plus-circle"></i>إنشاء برنامج</a>
      <a href="program-bank-list.php?bank_status=draft" class="qa-btn"><i class="bi bi-pencil-square"></i>المسودات</a>
      <a href="program-bank-list.php?bank_status=under_technical_review" class="qa-btn"><i class="bi bi-search"></i>انتظار مراجعة فنية</a>
      <a href="program-bank-list.php?bank_status=under_admin_review" class="qa-btn"><i class="bi bi-person-check"></i>انتظار مراجعة إدارية</a>
      <a href="program-bank-list.php?bank_status=approved" class="qa-btn"><i class="bi bi-check-circle"></i>المعتمدة</a>
      <a href="program-bank-list.php?grants_certificate=1" class="qa-btn"><i class="bi bi-award"></i>تمنح شهادة</a>
    </div>
  </div>

</div><!-- pw -->

<?php include __DIR__ . '/../../includes/layout/program-bank-shell-close.php'; ?>
<script>
const API = window.APP_CONFIG?.API_BASE_URL;

async function loadStats() {
  try {
    const r = await fetch(API + '/program-bank/stats', {
      headers: { Authorization: 'Bearer ' + AppAuth.getToken() }
    });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    const { data } = await r.json();

    document.getElementById('k-approved').textContent   = data.approved   ?? 0;
    document.getElementById('k-review').textContent     = data.under_review ?? 0;
    document.getElementById('k-draft').textContent      = data.draft      ?? 0;
    document.getElementById('k-suspended').textContent  = data.suspended  ?? 0;
    document.getElementById('k-total').textContent      = data.total      ?? 0;
    document.getElementById('k-needs').textContent      = data.linked_needs_map ?? 0;
    document.getElementById('k-finance').textContent    = data.linked_finance ?? 0;
    document.getElementById('k-incubators').textContent = data.linked_incubators ?? 0;
    document.getElementById('k-update').textContent     = data.needs_update ?? 0;

    renderMostExecuted(data.most_executed || []);
    renderTypeChart(data.by_type || []);
  } catch(e){
    console.error(e);
    document.getElementById('mostExecuted').innerHTML = '<p style="text-align:center;color:#ef4444;padding:16px">فشل تحميل الإحصائيات</p>';
  }
}

function renderMostExecuted(list) {
  const el = document.getElementById('mostExecuted');
  if (!list.length) { el.innerHTML = '<p style="text-align:center;color:#9ca3af;padding:16px">لا توجد بيانات</p>'; return; }
  el.innerHTML = list.map(item => `
    <div class="prog-row">
      <div class="prog-icon"><i class="bi bi-book"></i></div>
      <div class="prog-info">
        <div class="prog-name">${item.program?.title || item.program?.name || '—'}</div>
        <div class="prog-meta">${item.program?.code || ''}</div>
      </div>
      <div class="prog-count">${item.total}</div>
    </div>
  `).join('');
}

const TYPE_LABELS = {
  entrepreneurial:'ريادي', financial:'مالي', marketing:'تسويقي',
  technical:'فني', digital:'رقمي', administrative:'إداري',
  agricultural:'زراعي', craft:'حرفي', other:'أخرى'
};
const COLORS = ['#10b981','#3b82f6','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#ec4899','#6b7280'];

function renderTypeChart(rows) {
  const total = rows.reduce((s,r) => s + (r.total*1), 0);
  if (!total) return;
  const svg = document.getElementById('donutSvg');
  const leg = document.getElementById('donutLegend');
  const cx=60, cy=60, r=48, ri=30;
  let angle = -Math.PI/2;
  let paths = '';
  let legHtml = '';
  rows.forEach((row,i) => {
    const pct = (row.total*1) / total;
    const a2 = angle + pct * 2 * Math.PI;
    const x1=cx+r*Math.cos(angle), y1=cy+r*Math.sin(angle);
    const x2=cx+r*Math.cos(a2),   y2=cy+r*Math.sin(a2);
    const xi1=cx+ri*Math.cos(angle), yi1=cy+ri*Math.sin(angle);
    const xi2=cx+ri*Math.cos(a2),   yi2=cy+ri*Math.sin(a2);
    const large = pct > .5 ? 1 : 0;
    const col = COLORS[i % COLORS.length];
    paths += `<path d="M${x1} ${y1} A${r} ${r} 0 ${large} 1 ${x2} ${y2} L${xi2} ${yi2} A${ri} ${ri} 0 ${large} 0 ${xi1} ${yi1} Z" fill="${col}" opacity=".9"/>`;
    legHtml += `<div class="leg-item"><div class="leg-dot" style="background:${col}"></div>${TYPE_LABELS[row.type]||row.type}: <b>${row.total}</b></div>`;
    angle = a2;
  });
  svg.innerHTML = paths + `<text x="60" y="63" text-anchor="middle" font-size="11" font-weight="800" fill="#15312a">${total}</text>`;
  leg.innerHTML = legHtml;
}

document.addEventListener('DOMContentLoaded', () => {
  AppBootstrapAuth.init({ requireAuth: true });
  loadStats();
});
</script>
</body>
</html>
