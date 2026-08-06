<?php
$basePath       = '../../';
$pageTitle      = 'لوحة مدير الفرع';
$activePage     = 'branch-manager-dashboard';
$dashboardRole  = 'branch_manager';
$sidebarTitle   = 'SMEDA — الفروع';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<style>
  body{background:#f0fdf9;margin:0;}
  /* ══ خريطة الاحتياجات وسير المعالجة ══ */
  .needs-flow{display:flex;align-items:stretch;gap:0;overflow-x:auto;padding:4px 2px 10px;scrollbar-width:thin}
  .needs-flow-step{display:flex;align-items:center;gap:0;flex:0 0 auto}
  .needs-flow-chip{display:flex;flex-direction:column;align-items:center;gap:4px;min-width:112px;padding:11px 10px;border-radius:14px;background:#f9fafb;border:1.5px solid var(--ds-border,#e5e7eb);text-align:center;position:relative;transition:.15s}
  .needs-flow-chip .nfc-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem}
  .needs-flow-chip .nfc-count{font-size:1.25rem;font-weight:900;line-height:1}
  .needs-flow-chip .nfc-label{font-size:.7rem;font-weight:700;color:#475569;line-height:1.25}
  .needs-flow-chip.is-action{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
  .needs-flow-chip .nfc-badge{position:absolute;top:-7px;inset-inline-end:-6px;background:#3b82f6;color:#fff;font-size:.6rem;font-weight:800;padding:2px 6px;border-radius:20px;white-space:nowrap}
  .needs-flow-arrow{display:flex;align-items:center;color:#cbd5e1;font-size:1.05rem;padding:0 3px}
  .needs-side-flow{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
  .needs-side-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:20px;background:#f9fafb;border:1.5px solid var(--ds-border,#e5e7eb);font-size:.74rem;font-weight:800;color:#475569}
  .needs-side-chip b{font-weight:900}
  .needs-map-frame{width:100%;height:520px;border:0;border-radius:14px;margin-top:14px;background:#eef2f5;display:block}
</style>
</head>
<body>
<div class="ds-layout">

<?php include __DIR__ . '/../../includes/partials/dashboard-sidebar.php'; ?>

<div class="ds-main">
  <div class="ds-topbar">
    <button class="ds-hamburger" id="dsHamburger"><i class="bi bi-list"></i></button>
    <div class="ds-topbar-title">لوحة تحكم مدير الفرع</div>
    <div class="ds-topbar-right">
      <div class="ds-topbar-user">
        <span id="dsTopbarName"></span>
        <div class="ds-topbar-avatar" id="dsTopbarAvatar">م</div>
      </div>
    </div>
  </div>

  <div class="ds-content">

    <!-- Hero -->
    <div style="background:linear-gradient(135deg,#062824,#0d6b58,#17947B);color:#fff;border-radius:20px;padding:22px 26px;margin-bottom:20px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div style="position:absolute;font-size:12rem;opacity:.04;left:-10px;top:-30px;line-height:1;pointer-events:none">🌿</div>
      <div style="position:relative;z-index:1">
        <div style="font-size:.75rem;font-weight:800;opacity:.65;letter-spacing:.5px;margin-bottom:4px">SMEDA — إدارة الفروع</div>
        <h1 style="font-size:1.3rem;font-weight:900;margin:0 0 3px" id="heroGreet">مرحباً...</h1>
        <p style="opacity:.72;font-size:.82rem;margin:0" id="heroBranch">جاري تحميل بيانات الفرع...</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;position:relative;z-index:1">
        <a href="<?php echo $basePath;?>services/incubation/incubators.php" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:11px;font-size:.82rem;font-weight:700;text-decoration:none"><i class="bi bi-building"></i>الحاضنات</a>
        <a href="<?php echo $basePath;?>services/incubation/incubation-apply.php" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:#fff;color:#17947B;border-radius:11px;font-size:.82rem;font-weight:800;text-decoration:none"><i class="bi bi-plus-circle-fill"></i>طلب جديد</a>
      </div>
    </div>

    <!-- KPIs -->
    <div class="ds-kpi-grid">
      <?php foreach([
        ['kIncTotal','🏢','حاضنات الفرع',null],
        ['kPending','⏳','طلبات معلقة','#b45309'],
        ['kActive','✅','مشاريع نشطة','#15803d'],
        ['kSessions','📅','جلسات الشهر',null],
        ['kReports','📊','تقارير',null],
        ['kPrograms','🎓','برامج نشطة',null],
        ['kRevenue','💰','إيرادات (ألف)','#15803d'],
        ['kGraduated','🏆','تخرّجت','#7c3aed'],
      ] as [$id,$ic,$lbl,$col]):?>
      <div class="ds-kpi">
        <div class="ds-kpi-icon"><?php echo $ic;?></div>
        <div class="ds-kpi-val" id="<?php echo $id;?>" <?php echo $col?"style='color:$col'":'';?>>—</div>
        <div class="ds-kpi-lbl"><?php echo $lbl;?></div>
      </div>
      <?php endforeach;?>
    </div>

    <!-- خريطة الاحتياجات وسير المعالجة -->
    <div class="ds-card" style="margin-bottom:20px">
      <div class="ds-card-head">
        <div class="ds-card-title"><i class="bi bi-diagram-3-fill"></i> خريطة الاحتياجات وسير المعالجة</div>
        <a href="<?php echo $basePath;?>services/gis/needs-map.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">فتح الخريطة الكاملة <i class="bi bi-box-arrow-up-left"></i></a>
      </div>
      <p style="color:#64748b;font-size:.8rem;margin:0 0 12px">متابعة احتياجات الفرع وحالتها عبر مراحل المعالجة — من تدقيق بيانات المحافظة حتى الحل، مع توزيعها الجغرافي.</p>

      <!-- خط سير الحالات (البروسس) -->
      <div id="needsFlow" class="needs-flow">
        <div style="color:#9ca3af;font-size:.84rem;padding:10px">جاري تحميل مراحل المعالجة...</div>
      </div>
      <div id="needsSideFlow" class="needs-side-flow"></div>

      <!-- الخريطة الكاملة مضمّنة (نفس خريطة الاحتياجات) -->
      <iframe
        class="needs-map-frame"
        src="<?php echo $basePath;?>services/gis/needs-map.php?embed=1"
        title="خريطة الاحتياجات"
        loading="lazy"
        referrerpolicy="same-origin"></iframe>
    </div>

    <!-- Main grid -->
    <div class="ds-two-col">
      <div>
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-inbox-fill"></i> الطلبات المعلقة</div>
            <a href="<?php echo $basePath;?>services/incubation/incubated-projects.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div class="ds-tbl-wrap">
            <table class="ds-tbl">
              <thead><tr><th>المتقدم</th><th>الحاضنة</th><th>المشروع</th><th>الحالة</th><th>إجراء</th></tr></thead>
              <tbody id="pendingTbody"><tr><td colspan="5" style="text-align:center;padding:22px;color:#9ca3af">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-kanban-fill"></i> المشاريع النشطة</div>
            <a href="<?php echo $basePath;?>services/incubation/incubated-projects.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div class="ds-tbl-wrap">
            <table class="ds-tbl">
              <thead><tr><th>المشروع</th><th>الحاضنة</th><th>المرحلة</th><th>الإيرادات</th><th>وظائف</th></tr></thead>
              <tbody id="projectsTbody"><tr><td colspan="5" style="text-align:center;padding:22px;color:#9ca3af">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>

      <div>
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-building"></i> حاضنات الفرع</div>
          <div id="incListEl" style="display:flex;flex-direction:column;gap:8px"><div style="color:#9ca3af;font-size:.84rem;text-align:center;padding:12px">جاري التحميل...</div></div>
        </div>
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-grid-fill"></i> روابط سريعة</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <?php foreach([
              [$basePath.'services/incubation/incubators.php','bi-building','الحاضنات'],
              [$basePath.'services/incubation/incubated-projects.php','bi-kanban-fill','المشاريع'],
              [$basePath.'services/incubation/project-mentoring.php','bi-people-fill','الجلسات'],
              [$basePath.'services/incubation/incubation-reports.php','bi-bar-chart-fill','التقارير'],
              [$basePath.'services/incubation/success-stories.php','bi-trophy-fill','قصص النجاح'],
              [$basePath.'services/admin/entrepreneur-manager-dashboard.php','bi-rocket-fill','ريادة الأعمال'],
            ] as [$href,$ic,$lbl]):?>
            <a href="<?php echo $href;?>" style="display:flex;flex-direction:column;align-items:center;gap:5px;padding:12px 6px;background:var(--ds-soft);border-radius:12px;text-decoration:none;color:var(--ds-primary);font-size:.75rem;font-weight:800;text-align:center;border:1.5px solid var(--ds-border)">
              <i class="bi <?php echo $ic;?>" style="font-size:1.1rem"></i><?php echo $lbl;?>
            </a>
            <?php endforeach;?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</div>

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath;?>assets/js/modules/dashboard-sidebar.js?v=2.3"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  const allowed = ['branch_manager','admin','super_admin','general_director','system_admin'];
  if (!allowed.some(r => window.AppAuth.hasRole(r))) { location.href='<?php echo $basePath;?>'; return; }
  const user = window.AppAuth.getUser();
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H    = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E    = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const STAGE = {idea:'فكرة',validation:'تحقق',development:'تطوير',testing:'اختبار',launch:'إطلاق',growth:'نمو',graduated:'تخرّج'};
  document.getElementById('heroGreet').textContent = `مرحباً، ${user?.name||'مدير الفرع'} 👋`;

  const [rInc, rApps, rProj, rStats] = await Promise.allSettled([
    fetch(`${BASE}/incubators?per_page=100`, { headers:H() }),
    fetch(`${BASE}/incubation/applications?status=pending&per_page=8`, { headers:H() }),
    fetch(`${BASE}/incubation/projects?status=active&per_page=6`, { headers:H() }),
    fetch(`${BASE}/incubation/stats`, { headers:H() }),
  ]);

  if (rInc.status==='fulfilled' && rInc.value.ok) {
    const d = await rInc.value.json(); const incs = d.data||d||[];
    document.getElementById('heroBranch').textContent = `${incs.length} حاضنة مسجلة في الفرع`;
    document.getElementById('kIncTotal').textContent = incs.length;
    document.getElementById('incListEl').innerHTML = incs.slice(0,6).map(i=>`
      <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 12px;background:#f9fafb;border-radius:11px">
        <div><div style="font-weight:800;font-size:.85rem">${E(i.name)}</div><div style="color:#9ca3af;font-size:.7rem">${E(i.governorate||'')} — ${E(i.sector||'')}</div></div>
        <span style="background:#dcfce7;color:#15803d;border-radius:20px;padding:2px 8px;font-size:.7rem;font-weight:800">${i.active_projects_count||0} مشروع</span>
      </div>`).join('') || '<div style="color:#9ca3af;text-align:center;padding:12px;font-size:.84rem">لا توجد حاضنات</div>';
  }
  if (rApps.status==='fulfilled' && rApps.value.ok) {
    const d = await rApps.value.json(); const apps = d.data||[];
    document.getElementById('kPending').textContent = d.total||apps.length;
    document.getElementById('pendingTbody').innerHTML = apps.length
      ? apps.map(a=>`<tr><td style="font-weight:700">${E(a.applicant?.name||'—')}</td><td style="font-size:.8rem">${E(a.incubator?.name||'—')}</td><td style="font-size:.8rem">${E(a.project_name||'—')}</td><td><span style="background:#fef3c7;color:#b45309;border-radius:20px;padding:2px 8px;font-size:.7rem;font-weight:700">معلق</span></td><td><a href="<?php echo $basePath;?>services/incubation/incubated-projects.php" style="font-size:.78rem;font-weight:700;color:var(--ds-primary)">مراجعة</a></td></tr>`).join('')
      : '<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:18px">لا توجد طلبات معلقة</td></tr>';
  }
  if (rProj.status==='fulfilled' && rProj.value.ok) {
    const d = await rProj.value.json(); const projs = d.data||[];
    document.getElementById('kActive').textContent = d.total||projs.length;
    document.getElementById('projectsTbody').innerHTML = projs.length
      ? projs.map(p=>`<tr><td style="font-weight:700">${E(p.name||p.project_name||'—')}</td><td style="font-size:.8rem">${E(p.incubator?.name||'—')}</td><td><span style="background:var(--ds-soft);color:var(--ds-primary);border-radius:20px;padding:2px 8px;font-size:.7rem;font-weight:700">${STAGE[p.stage]||p.stage||'—'}</span></td><td style="font-size:.8rem">${p.revenue?Number(p.revenue).toLocaleString('ar'):'—'}</td><td style="text-align:center;font-size:.82rem">${p.employees_count||'—'}</td></tr>`).join('')
      : '<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:18px">لا توجد مشاريع</td></tr>';
  }
  if (rStats.status==='fulfilled' && rStats.value.ok) {
    const s = await rStats.value.json();
    document.getElementById('kSessions').textContent  = s.sessions_this_month||s.sessions||0;
    document.getElementById('kReports').textContent   = s.reports||0;
    document.getElementById('kPrograms').textContent  = s.active_programs||0;
    document.getElementById('kRevenue').textContent   = s.total_revenue ? Math.round(s.total_revenue/1000) : 0;
    document.getElementById('kGraduated').textContent = s.graduated||0;
  }
});
</script>

<!-- ══ سير معالجة الاحتياجات — البروسس (إضافة مستقلة) ══ -->
<script>
(async function () {
  const ready = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ready) return;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H    = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });

  // مراحل البروسس بالترتيب (خط السير الرئيسي)
  const FLOW = [
    { key:'new',                        label:'مسودة',                   color:'#94a3b8', icon:'bi-pencil-square' },
    { key:'pending_governorate_review', label:'تدقيق بيانات المحافظة',   color:'#f59e0b', icon:'bi-hourglass-split' },
    { key:'returned_for_edit',          label:'معاد للتعديل',            color:'#fb923c', icon:'bi-arrow-counterclockwise' },
    { key:'pending_branch_approval',    label:'موافقة مدير الفرع',       color:'#3b82f6', icon:'bi-person-check-fill', action:true },
    { key:'approved',                   label:'موافق عليه',              color:'#22c55e', icon:'bi-check-circle-fill' },
    { key:'classified',                 label:'مصنّف',                   color:'#14b8a6', icon:'bi-tags-fill' },
    { key:'in_progress',                label:'قيد المعالجة',            color:'#6366f1', icon:'bi-gear-fill' },
    { key:'resolved',                   label:'تم الحل',                 color:'#10b981', icon:'bi-flag-fill' },
  ];
  // حالات جانبية (خارج المسار الطبيعي)
  const SIDE = [
    { key:'rejected', label:'مرفوض', color:'#ef4444', icon:'bi-x-circle-fill' },
    { key:'archived', label:'مؤرشف', color:'#64748b', icon:'bi-archive-fill' },
  ];

  /* خط سير الحالات من /needs/dashboard (مُقيَّد تلقائيًا بنطاق الفرع) */
  try {
    const r = await fetch(`${BASE}/needs/dashboard`, { headers:H() });
    if (r.ok) {
      const d = await r.json();
      const counts = {};
      (d.data?.by_status || []).forEach(row => { counts[row.status] = (counts[row.status]||0) + Number(row.total||0); });

      const flowEl = document.getElementById('needsFlow');
      flowEl.innerHTML = FLOW.map((s, i) => {
        const n = counts[s.key] || 0;
        const arrow = i < FLOW.length - 1
          ? '<div class="needs-flow-arrow"><i class="bi bi-chevron-double-left"></i></div>' : '';
        const badge = (s.action && n > 0) ? '<span class="nfc-badge">بانتظار إجراءك</span>' : '';
        return `<div class="needs-flow-step">
          <div class="needs-flow-chip ${s.action && n>0 ? 'is-action':''}">
            ${badge}
            <div class="nfc-icon" style="background:${s.color}"><i class="bi ${s.icon}"></i></div>
            <div class="nfc-count" style="color:${s.color}">${n}</div>
            <div class="nfc-label">${s.label}</div>
          </div>${arrow}</div>`;
      }).join('');

      const sideEl = document.getElementById('needsSideFlow');
      sideEl.innerHTML = SIDE.map(s => {
        const n = counts[s.key] || 0;
        return `<span class="needs-side-chip"><i class="bi ${s.icon}" style="color:${s.color}"></i>${s.label}: <b style="color:${s.color}">${n}</b></span>`;
      }).join('');
    }
  } catch (e) { console.warn('needs dashboard load failed', e); }
})();
</script>
</body>
</html>
