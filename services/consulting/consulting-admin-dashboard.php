<?php
$basePath  = '../../';
$pageTitle = 'لوحة تحكم الاستشارات';
$activePage= 'consulting';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../../includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .page-wrap { max-width:1200px; margin:auto; padding:24px 14px; }
    .page-hero { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border-radius:22px; padding:26px 28px; margin-bottom:22px; position:relative; overflow:hidden; }
    .page-hero::before { content:""; position:absolute; width:220px; height:220px; border-radius:50%; top:-110px; left:-80px; background:radial-gradient(circle,rgba(255,255,255,.16),transparent 70%); }
    .page-hero-title { font-size:1.6rem; font-weight:800; margin:0 0 6px; }
    .page-hero i.bg-icon { position:absolute; right:24px; top:50%; transform:translateY(-50%); font-size:5rem; opacity:.12; }

    /* KPI cards */
    .kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:14px; margin-bottom:22px; }
    .kpi-card { background:#fff; border:1px solid var(--c-border); border-radius:18px; padding:18px; box-shadow:var(--c-shadow); text-align:center; position:relative; overflow:hidden; }
    .kpi-card::before { content:""; position:absolute; top:0; right:0; left:0; height:4px; border-radius:18px 18px 0 0; }
    .kpi-card.green::before  { background:linear-gradient(90deg,#16a34a,#22c55e); }
    .kpi-card.blue::before   { background:linear-gradient(90deg,#1d4ed8,#3b82f6); }
    .kpi-card.amber::before  { background:linear-gradient(90deg,#d97706,#f59e0b); }
    .kpi-card.purple::before { background:linear-gradient(90deg,#7c3aed,#a78bfa); }
    .kpi-card.teal::before   { background:linear-gradient(90deg,var(--c-primary),var(--c-accent)); }
    .kpi-card.red::before    { background:linear-gradient(90deg,#dc2626,#ef4444); }
    .kpi-val  { font-size:2rem; font-weight:800; color:var(--c-text); margin:8px 0 4px; }
    .kpi-lbl  { font-size:.8rem; font-weight:700; color:var(--c-muted); }
    .kpi-icon { font-size:1.6rem; }

    .section-card { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:20px; box-shadow:var(--c-shadow); margin-bottom:18px; }
    .section-title { font-weight:800; font-size:1.05rem; color:var(--c-text); margin:0 0 16px; display:flex; align-items:center; gap:8px; }
    .section-title i { color:var(--c-primary); }

    /* Status breakdown */
    .status-bar-wrap { display:flex; flex-direction:column; gap:10px; }
    .status-bar-row { display:grid; grid-template-columns:160px 1fr 60px; align-items:center; gap:10px; font-size:.86rem; }
    .status-bar-bg { background:#f3f4f6; border-radius:20px; height:10px; overflow:hidden; }
    .status-bar-fill { height:100%; border-radius:20px; transition:width .5s; }
    .status-label { font-weight:700; color:var(--c-text); }
    .status-count  { font-weight:800; color:var(--c-primary); text-align:left; }

    /* Office performance table */
    .perf-table { width:100%; border-collapse:collapse; font-size:.86rem; }
    .perf-table th { padding:10px 14px; font-weight:700; color:var(--c-muted); text-align:right; border-bottom:2px solid var(--c-border); font-size:.8rem; }
    .perf-table td { padding:10px 14px; border-bottom:1px solid var(--c-border); color:var(--c-text); font-weight:600; }
    .perf-table tr:hover td { background:var(--c-soft); }
    .stars-sm { color:#f59e0b; font-size:.85rem; }

    /* Recent activity */
    .activity-list { display:flex; flex-direction:column; gap:10px; }
    .activity-item { display:flex; align-items:center; gap:12px; padding:10px; border-radius:12px; background:#f9fafb; }
    .activity-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
    .activity-text { font-size:.87rem; font-weight:700; color:var(--c-text); }
    .activity-time  { font-size:.77rem; color:var(--c-muted); margin-top:2px; }

    /* Quick links */
    .quick-links { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; }
    .quick-link { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border-radius:16px; padding:16px; text-decoration:none; display:flex; align-items:center; gap:12px; font-weight:700; transition:opacity .2s; }
    .quick-link:hover { opacity:.9; color:#fff; }
    .quick-link i { font-size:1.5rem; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>
<div class="page-wrap">

  <div class="page-hero">
    <i class="bi bi-graph-up-arrow bg-icon"></i>
    <div class="page-hero-title">لوحة تحكم الاستشارات</div>
    <p style="margin:0;opacity:.9;font-size:.93rem">نظرة شاملة على أداء قسم الاستشارات — الطلبات، المكاتب، والعقود</p>
  </div>

  <!-- KPI Cards -->
  <div class="kpi-grid" id="kpiGrid">
    <div class="kpi-card teal"><div class="kpi-icon"><i class="bi bi-collection-fill" style="color:var(--c-primary)"></i></div><div class="kpi-val" id="kpi-total">—</div><div class="kpi-lbl">إجمالي الطلبات</div></div>
    <div class="kpi-card green"><div class="kpi-icon"><i class="bi bi-check-circle-fill" style="color:#16a34a"></i></div><div class="kpi-val" id="kpi-completed">—</div><div class="kpi-lbl">مكتملة</div></div>
    <div class="kpi-card blue"><div class="kpi-icon"><i class="bi bi-hourglass-split" style="color:#1d4ed8"></i></div><div class="kpi-val" id="kpi-in-progress">—</div><div class="kpi-lbl">قيد التنفيذ</div></div>
    <div class="kpi-card amber"><div class="kpi-icon"><i class="bi bi-clock-history" style="color:#d97706"></i></div><div class="kpi-val" id="kpi-pending">—</div><div class="kpi-lbl">بانتظار المعالجة</div></div>
    <div class="kpi-card purple"><div class="kpi-icon"><i class="bi bi-buildings-fill" style="color:#7c3aed"></i></div><div class="kpi-val" id="kpi-offices">—</div><div class="kpi-lbl">مكاتب نشطة</div></div>
    <div class="kpi-card red"><div class="kpi-icon"><i class="bi bi-x-circle-fill" style="color:#dc2626"></i></div><div class="kpi-val" id="kpi-rejected">—</div><div class="kpi-lbl">مرفوضة</div></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px" class="two-col">
    <!-- Status breakdown -->
    <div class="section-card">
      <div class="section-title"><i class="bi bi-bar-chart-fill"></i> توزيع الطلبات حسب الحالة</div>
      <div class="status-bar-wrap" id="statusBreakdown">
        <div style="color:var(--c-muted);font-size:.88rem">جاري التحميل...</div>
      </div>
    </div>

    <!-- Category breakdown -->
    <div class="section-card">
      <div class="section-title"><i class="bi bi-pie-chart-fill"></i> توزيع حسب نوع الاستشارة</div>
      <div id="categoryBreakdown" style="display:flex;flex-direction:column;gap:8px">
        <div style="color:var(--c-muted);font-size:.88rem">جاري التحميل...</div>
      </div>
    </div>
  </div>

  <!-- Top offices -->
  <div class="section-card">
    <div class="section-title"><i class="bi bi-trophy-fill"></i> أعلى المكاتب أداءً</div>
    <div style="overflow-x:auto">
      <table class="perf-table">
        <thead>
          <tr>
            <th>#</th>
            <th>المكتب</th>
            <th>المحافظة</th>
            <th>التقييم</th>
            <th>الطلبات المكتملة</th>
            <th>الالتزام بالمواعيد</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody id="topOfficesTable">
          <tr><td colspan="7" style="text-align:center;color:var(--c-muted);padding:20px">جاري التحميل...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent requests -->
  <div class="section-card">
    <div class="section-title"><i class="bi bi-clock-history"></i> آخر الطلبات</div>
    <div id="recentRequests">
      <div style="color:var(--c-muted);font-size:.88rem">جاري التحميل...</div>
    </div>
  </div>

  <!-- Quick links -->
  <div class="section-card">
    <div class="section-title"><i class="bi bi-lightning-fill"></i> روابط سريعة</div>
    <div class="quick-links">
      <a href="consulting-requests-list.php" class="quick-link"><i class="bi bi-list-check"></i><span>كل الطلبات</span></a>
      <a href="consulting-offices-list.php" class="quick-link"><i class="bi bi-buildings-fill"></i><span>المكاتب</span></a>
      <a href="consulting-request-create.php" class="quick-link"><i class="bi bi-plus-circle-fill"></i><span>طلب استشارة</span></a>
      <a href="consulting-requests-list.php?status=submitted" class="quick-link" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6)"><i class="bi bi-inbox-fill"></i><span>طلبات جديدة</span></a>
      <a href="consulting-requests-list.php?status=report_submitted" class="quick-link" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i class="bi bi-file-earmark-pdf-fill"></i><span>تقارير بانتظار اعتماد</span></a>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyRoles: window.AppPermissions.CONSULTING_ADMIN_ROLES,
  });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();

  async function apiGet(url) {
    return fetch(url, { headers:{ 'Authorization':`Bearer ${token()}`, 'Accept':'application/json' }}).then(r=>r.json());
  }

  const STATUS_LABELS = {
    draft:'مسودة', submitted:'مُرسَل', needs_info:'بحاجة معلومات',
    sorting:'قيد الفرز', awaiting_offers:'بانتظار عروض',
    offer_submitted:'عرض مقدم', in_progress:'قيد التنفيذ',
    report_submitted:'تقرير مرفوع', completed:'مكتمل', rejected:'مرفوض',
    transferred_financing:'محوّل تمويل', transferred_training:'محوّل تدريب',
  };

  const STATUS_COLORS = {
    draft:'#9ca3af', submitted:'#3b82f6', needs_info:'#f59e0b',
    sorting:'#8b5cf6', awaiting_offers:'#6366f1', offer_submitted:'#22c55e',
    in_progress:'#10b981', report_submitted:'#0ea5e9', completed:'#16a34a', rejected:'#ef4444',
  };

  // Load requests for stats
  async function loadStats() {
    try {
      const json = await apiGet(`${base}/consulting/requests?per_page=500`);
      const items = json.data?.data || json.data || json || [];

      const byStatus = {};
      items.forEach(r => { byStatus[r.status] = (byStatus[r.status]||0) + 1; });

      document.getElementById('kpi-total').textContent      = items.length;
      document.getElementById('kpi-completed').textContent  = byStatus['completed']||0;
      document.getElementById('kpi-in-progress').textContent= byStatus['in_progress']||0;
      document.getElementById('kpi-pending').textContent    = (byStatus['submitted']||0)+(byStatus['needs_info']||0);
      document.getElementById('kpi-rejected').textContent   = byStatus['rejected']||0;

      // Status breakdown bars
      const total = items.length || 1;
      const sorted = Object.entries(byStatus).sort((a,b)=>b[1]-a[1]);
      document.getElementById('statusBreakdown').innerHTML = sorted.map(([s,n])=>`
        <div class="status-bar-row">
          <span class="status-label">${STATUS_LABELS[s]||s}</span>
          <div class="status-bar-bg"><div class="status-bar-fill" style="width:${Math.round(n/total*100)}%;background:${STATUS_COLORS[s]||'#9ca3af'}"></div></div>
          <span class="status-count">${n}</span>
        </div>`).join('');

      // Category breakdown
      const byCat = {};
      items.forEach(r => { const c=r.category_code||'أخرى'; byCat[c]=(byCat[c]||0)+1; });
      const catSorted = Object.entries(byCat).sort((a,b)=>b[1]-a[1]);
      document.getElementById('categoryBreakdown').innerHTML = catSorted.map(([c,n])=>`
        <div style="display:flex;align-items:center;gap:10px;font-size:.86rem">
          <span style="width:100px;font-weight:700;color:var(--c-text)">${c}</span>
          <div style="flex:1;background:#f3f4f6;border-radius:20px;height:8px;overflow:hidden">
            <div style="width:${Math.round(n/total*100)}%;height:100%;background:linear-gradient(90deg,var(--c-primary),var(--c-accent));border-radius:20px"></div>
          </div>
          <span style="font-weight:800;color:var(--c-primary);min-width:28px;text-align:left">${n}</span>
        </div>`).join('');

      // Recent 5
      const recent = [...items].sort((a,b)=>new Date(b.created_at)-new Date(a.created_at)).slice(0,5);
      document.getElementById('recentRequests').innerHTML = `
        <div style="overflow-x:auto">
          <table class="perf-table">
            <thead><tr><th>الكود</th><th>العنوان</th><th>النوع</th><th>الحالة</th><th>التاريخ</th><th></th></tr></thead>
            <tbody>${recent.map(r=>`
              <tr>
                <td style="font-weight:700;color:var(--c-primary);font-size:.82rem">${r.request_code||'#'+r.id}</td>
                <td>${window.APP_HELPERS.e(r.title).substring(0,45)}</td>
                <td style="font-size:.8rem">${r.category_code||'—'}</td>
                <td><span style="padding:3px 10px;border-radius:20px;font-size:.77rem;font-weight:700;background:${STATUS_COLORS[r.status]||'#e5e7eb'}22;color:${STATUS_COLORS[r.status]||'#6b7280'}">${STATUS_LABELS[r.status]||r.status}</span></td>
                <td style="font-size:.8rem;color:var(--c-muted)">${new Date(r.created_at).toLocaleDateString('ar-SY')}</td>
                <td><a href="consulting-request-view.php?id=${r.id}" style="color:var(--c-primary);font-weight:700;text-decoration:none;font-size:.82rem"><i class="bi bi-eye"></i></a></td>
              </tr>`).join('')}
            </tbody>
          </table>
        </div>`;
    } catch(e) { console.error(e); }
  }

  async function loadOffices() {
    try {
      const json  = await apiGet(`${base}/consulting/offices?status=active&per_page=100`);
      const items = json.data?.data || json.data || json || [];
      document.getElementById('kpi-offices').textContent = items.length;

      const sorted = [...items].sort((a,b)=>Number(b.overall_rating||0)-Number(a.overall_rating||0)).slice(0,10);
      document.getElementById('topOfficesTable').innerHTML = sorted.length ? sorted.map((o,i)=>`
        <tr>
          <td style="font-weight:800;color:var(--c-primary)">${i+1}</td>
          <td style="font-weight:700">${window.APP_HELPERS.e(o.name)}</td>
          <td style="font-size:.84rem;color:var(--c-muted)">${o.governorate?.name_ar||'—'}</td>
          <td><span class="stars-sm">${'★'.repeat(Math.round(o.overall_rating||0))}${'☆'.repeat(5-Math.round(o.overall_rating||0))}</span> ${Number(o.overall_rating||0).toFixed(1)}</td>
          <td style="text-align:center;font-weight:700">${o.total_requests_completed||0}</td>
          <td style="text-align:center">${o.on_time_rate ? Math.round(o.on_time_rate)+'%' : '—'}</td>
          <td><a href="consulting-office-view.php?id=${o.id}" style="color:var(--c-primary);font-size:.83rem;font-weight:700;text-decoration:none"><i class="bi bi-eye"></i> عرض</a></td>
        </tr>`).join('') :
        '<tr><td colspan="7" style="text-align:center;color:var(--c-muted);padding:20px">لا توجد مكاتب</td></tr>';
    } catch(e) { console.error(e); }
  }

  // Responsive two-col
  const mq = window.matchMedia('(max-width:640px)');
  if (mq.matches) document.querySelector('.two-col').style.gridTemplateColumns='1fr';

  loadStats();
  loadOffices();
});
</script>
</body>
</html>
