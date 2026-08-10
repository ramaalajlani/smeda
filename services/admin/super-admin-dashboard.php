<?php
$basePath  = '../../';
$pageTitle = 'لوحة المدير العام';
$activePage= 'super_dashboard';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#17947B;--c-accent:#06AA89;--c-soft:#EAF8F4;--c-border:rgba(23,148,123,.13);--c-text:#16332E;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(15,79,71,.07);}
    body{background:linear-gradient(160deg,#f0faf7,#e8f7f3);}
    .pw{max-width:1300px;margin:auto;padding:20px 14px;}

    /* ── Hero ── */
    .hero{background:linear-gradient(135deg,#0f5e4f,var(--c-primary),var(--c-accent));color:#fff;border-radius:24px;padding:28px 32px;margin-bottom:22px;position:relative;overflow:hidden;display:flex;align-items:center;gap:20px;flex-wrap:wrap;}
    .hero::before,.hero::after{content:"";position:absolute;border-radius:50%;opacity:.12;}
    .hero::before{width:260px;height:260px;top:-130px;left:-80px;background:radial-gradient(circle,#fff,transparent 70%);}
    .hero::after{width:180px;height:180px;bottom:-90px;right:60px;background:radial-gradient(circle,#fff,transparent 70%);}
    .hero-avatar{width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;}
    .hero-title{font-size:1.6rem;font-weight:800;margin:0 0 4px;}
    .hero-sub{opacity:.88;font-size:.93rem;margin:0;}
    .hero-time{margin-right:auto;font-size:.82rem;opacity:.75;text-align:left;}

    /* ── KPI grid ── */
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;margin-bottom:22px;}
    .kpi{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:18px 16px;box-shadow:var(--c-shadow);position:relative;overflow:hidden;cursor:pointer;transition:transform .2s,box-shadow .2s;}
    .kpi:hover{transform:translateY(-3px);box-shadow:0 18px 36px rgba(15,79,71,.13);}
    .kpi::before{content:"";position:absolute;top:0;right:0;left:0;height:4px;border-radius:18px 18px 0 0;}
    .kpi.teal::before{background:linear-gradient(90deg,var(--c-primary),var(--c-accent));}
    .kpi.blue::before{background:linear-gradient(90deg,#1d4ed8,#60a5fa);}
    .kpi.green::before{background:linear-gradient(90deg,#15803d,#4ade80);}
    .kpi.amber::before{background:linear-gradient(90deg,#b45309,#fbbf24);}
    .kpi.purple::before{background:linear-gradient(90deg,#6d28d9,#c4b5fd);}
    .kpi.rose::before{background:linear-gradient(90deg,#be123c,#fb7185);}
    .kpi.sky::before{background:linear-gradient(90deg,#0369a1,#38bdf8);}
    .kpi.indigo::before{background:linear-gradient(90deg,#3730a3,#818cf8);}
    .kpi-icon{font-size:1.5rem;margin-bottom:8px;}
    .kpi-val{font-size:2.1rem;font-weight:800;color:var(--c-text);line-height:1;}
    .kpi-lbl{font-size:.78rem;font-weight:700;color:var(--c-muted);margin-top:5px;}
    .kpi-delta{font-size:.72rem;font-weight:700;margin-top:4px;}
    .delta-up{color:#16a34a;} .delta-dn{color:#dc2626;}

    /* ── Section cards ── */
    .sec{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:20px;box-shadow:var(--c-shadow);margin-bottom:18px;}
    .sec-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;}
    .sec-title{font-weight:800;font-size:1rem;color:var(--c-text);display:flex;align-items:center;gap:8px;}
    .sec-title i{color:var(--c-primary);}
    .sec-link{font-size:.82rem;font-weight:700;color:var(--c-primary);text-decoration:none;}

    /* ── Two/three columns ── */
    .col2{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
    .col3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;}
    @media(max-width:900px){.col2,.col3{grid-template-columns:1fr;}}

    /* ── Module cards ── */
    .mod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;}
    .mod-card{border-radius:16px;padding:16px;display:flex;align-items:center;gap:12px;text-decoration:none;font-weight:700;color:#fff;transition:opacity .2s;}
    .mod-card:hover{opacity:.9;color:#fff;}
    .mod-card i{font-size:1.8rem;opacity:.9;}
    .mod-card .mod-info{flex:1;}
    .mod-card .mod-name{font-size:.9rem;font-weight:800;margin-bottom:2px;}
    .mod-card .mod-count{font-size:1.4rem;font-weight:800;line-height:1;}

    /* ── Activity log ── */
    .act-item{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--c-border);}
    .act-item:last-child{border-bottom:none;}
    .act-dot{width:10px;height:10px;border-radius:50%;margin-top:5px;flex-shrink:0;}
    .act-text{font-size:.86rem;color:var(--c-text);font-weight:600;margin-bottom:2px;}
    .act-meta{font-size:.76rem;color:var(--c-muted);}

    /* ── Alerts ── */
    .alert-list{display:flex;flex-direction:column;gap:8px;}
    .alert-item{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:14px;font-size:.87rem;font-weight:700;}
    .alert-warn{background:#fef3c7;color:#92400e;border-right:4px solid #f59e0b;}
    .alert-danger{background:#fee2e2;color:#991b1b;border-right:4px solid #ef4444;}
    .alert-info{background:#e0f2fe;color:#0c4a6e;border-right:4px solid #38bdf8;}
    .alert-success{background:#dcfce7;color:#14532d;border-right:4px solid #4ade80;}

    /* ── Users table ── */
    .usr-table{width:100%;border-collapse:collapse;font-size:.85rem;}
    .usr-table th{padding:9px 12px;font-weight:700;color:var(--c-muted);text-align:right;border-bottom:2px solid var(--c-border);font-size:.78rem;}
    .usr-table td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);font-weight:600;}
    .usr-table tr:hover td{background:var(--c-soft);}

    /* ── Bar chart ── */
    .bar-row{display:grid;grid-template-columns:130px 1fr 50px;align-items:center;gap:10px;margin-bottom:10px;font-size:.84rem;}
    .bar-bg{background:#f3f4f6;border-radius:20px;height:10px;overflow:hidden;}
    .bar-fill{height:100%;border-radius:20px;transition:width .6s cubic-bezier(.4,0,.2,1);}

    /* ── Spinner ── */
    .spin{display:inline-block;width:20px;height:20px;border:3px solid rgba(23,148,123,.2);border-top-color:var(--c-primary);border-radius:50%;animation:spin .7s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg);}}

    /* ── Status pills ── */
    .pill{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;}
  </style>
</head>
<body>
<div class="pw">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-avatar"><i class="bi bi-person-badge-fill"></i></div>
    <div>
      <div class="hero-title" id="heroName">لوحة المدير العام</div>
      <p class="hero-sub">نظرة شاملة على أداء الهيئة — التدريب، الاستشارات، التمويل، القوى العاملة</p>
    </div>
    <div class="hero-time" id="heroTime"></div>
  </div>

  <!-- KPIs -->
  <div class="kpi-grid" id="kpiGrid">
    <div class="kpi teal"><div class="kpi-icon"><i class="bi bi-people-fill" style="color:var(--c-primary)"></i></div><div class="kpi-val" id="k-users">—</div><div class="kpi-lbl">المستخدمون النشطون</div></div>
    <div class="kpi blue"><div class="kpi-icon"><i class="bi bi-buildings-fill" style="color:#1d4ed8"></i></div><div class="kpi-val" id="k-centers">—</div><div class="kpi-lbl">المراكز التدريبية</div></div>
    <div class="kpi green"><div class="kpi-icon"><i class="bi bi-mortarboard-fill" style="color:#15803d"></i></div><div class="kpi-val" id="k-courses">—</div><div class="kpi-lbl">الدورات التدريبية</div></div>
    <div class="kpi amber"><div class="kpi-icon"><i class="bi bi-award-fill" style="color:#b45309"></i></div><div class="kpi-val" id="k-certs">—</div><div class="kpi-lbl">الشهادات المُصدَرة</div></div>
    <div class="kpi purple"><div class="kpi-icon"><i class="bi bi-headset" style="color:#6d28d9"></i></div><div class="kpi-val" id="k-consulting">—</div><div class="kpi-lbl">طلبات الاستشارة</div></div>
    <div class="kpi rose"><div class="kpi-icon"><i class="bi bi-bank2" style="color:#be123c"></i></div><div class="kpi-val" id="k-finance">—</div><div class="kpi-lbl">طلبات التمويل</div></div>
    <div class="kpi sky"><div class="kpi-icon"><i class="bi bi-briefcase-fill" style="color:#0369a1"></i></div><div class="kpi-val" id="k-workforce">—</div><div class="kpi-lbl">المرشحون للتوظيف</div></div>
    <div class="kpi indigo"><div class="kpi-icon"><i class="bi bi-bell-fill" style="color:#3730a3"></i></div><div class="kpi-val" id="k-notif">—</div><div class="kpi-lbl">إشعارات غير مقروءة</div></div>
    <div class="kpi" style="--c-kpi:#7c3aed"><div class="kpi-icon"><i class="bi bi-rocket-takeoff-fill" style="color:#7c3aed"></i></div><div class="kpi-val" id="k-incubation">—</div><div class="kpi-lbl">مشاريع محتضنة</div></div>
  </div>

  <!-- Alerts box -->
  <div class="sec" id="alertsBox" style="display:none">
    <div class="sec-hd"><div class="sec-title"><i class="bi bi-exclamation-triangle-fill"></i> تنبيهات تحتاج انتباهك</div></div>
    <div class="alert-list" id="alertsList"></div>
  </div>

  <!-- Module quick access -->
  <div class="sec">
    <div class="sec-hd"><div class="sec-title"><i class="bi bi-grid-fill"></i> الوصول السريع للأقسام</div></div>
    <div class="mod-grid">
      <a href="<?php echo $basePath;?>services/training/training-centers-list.php" class="mod-card" style="background:linear-gradient(135deg,#0f5e4f,var(--c-primary))">
        <i class="bi bi-buildings-fill"></i><div class="mod-info"><div class="mod-name">المراكز التدريبية</div><div class="mod-count" id="m-centers">—</div></div>
      </a>
      <a href="<?php echo $basePath;?>services/training/training-trainers-list.php" class="mod-card" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6)">
        <i class="bi bi-person-workspace"></i><div class="mod-info"><div class="mod-name">المدربون</div><div class="mod-count" id="m-trainers">—</div></div>
      </a>
      <a href="<?php echo $basePath;?>services/training/training-trainees-list.php" class="mod-card" style="background:linear-gradient(135deg,#15803d,#22c55e)">
        <i class="bi bi-people-fill"></i><div class="mod-info"><div class="mod-name">المتدربون</div><div class="mod-count" id="m-trainees">—</div></div>
      </a>
      <a href="<?php echo $basePath;?>services/consulting/consulting-requests-list.php" class="mod-card" style="background:linear-gradient(135deg,#6d28d9,#a78bfa)">
        <i class="bi bi-headset"></i><div class="mod-info"><div class="mod-name">الاستشارات</div><div class="mod-count" id="m-consulting">—</div></div>
      </a>
      <a href="<?php echo $basePath;?>services/finance/finance.php" class="mod-card" style="background:linear-gradient(135deg,#be123c,#f43f5e)">
        <i class="bi bi-bank2"></i><div class="mod-info"><div class="mod-name">التمويل</div><div class="mod-count" id="m-finance">—</div></div>
      </a>
      <a href="<?php echo $basePath;?>services/workforce/candidates-list.php" class="mod-card" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
        <i class="bi bi-briefcase-fill"></i><div class="mod-info"><div class="mod-name">القوى العاملة</div><div class="mod-count" id="m-wf">—</div></div>
      </a>
      <a href="<?php echo $basePath;?>services/gis/needs-map.php" class="mod-card" style="background:linear-gradient(135deg,#b45309,#f59e0b)">
        <i class="bi bi-geo-alt-fill"></i><div class="mod-info"><div class="mod-name">GIS / الاحتياجات</div><div class="mod-count" id="m-gis">—</div></div>
      </a>
      <a href="<?php echo $basePath;?>services/admin/admin-users.php" class="mod-card" style="background:linear-gradient(135deg,#374151,#6b7280)">
        <i class="bi bi-shield-lock-fill"></i><div class="mod-info"><div class="mod-name">إدارة المستخدمين</div><div class="mod-count" id="m-users">—</div></div>
      </a>
      <a href="<?php echo $basePath;?>services/incubation/incubators-list.php" class="mod-card" style="background:linear-gradient(135deg,#4c1d95,#7c3aed)">
        <i class="bi bi-rocket-takeoff-fill"></i><div class="mod-info"><div class="mod-name">الحاضنات</div><div class="mod-count" id="m-incubation">—</div></div>
      </a>
    </div>
  </div>

  <div class="col2">
    <!-- Training bar chart -->
    <div class="sec">
      <div class="sec-hd">
        <div class="sec-title"><i class="bi bi-bar-chart-fill"></i> التدريب — توزيع الشهادات</div>
        <a href="<?php echo $basePath;?>services/training/training-certificates-list.php" class="sec-link">عرض الكل <i class="bi bi-arrow-left"></i></a>
      </div>
      <div id="certChart"><div class="spin"></div></div>
    </div>

    <!-- Consulting status -->
    <div class="sec">
      <div class="sec-hd">
        <div class="sec-title"><i class="bi bi-pie-chart-fill"></i> الاستشارات — توزيع الحالات</div>
        <a href="<?php echo $basePath;?>services/consulting/consulting-admin-dashboard.php" class="sec-link">التفاصيل <i class="bi bi-arrow-left"></i></a>
      </div>
      <div id="consultingChart"><div class="spin"></div></div>
    </div>
  </div>

  <div class="col2">
    <!-- Recent requests needing action -->
    <div class="sec">
      <div class="sec-hd">
        <div class="sec-title"><i class="bi bi-clock-history"></i> طلبات بانتظار الإجراء</div>
      </div>
      <div id="pendingActions"><div class="spin"></div></div>
    </div>

    <!-- Recent users -->
    <div class="sec">
      <div class="sec-hd">
        <div class="sec-title"><i class="bi bi-person-plus-fill"></i> آخر المستخدمين المسجلين</div>
        <a href="<?php echo $basePath;?>services/admin/admin-users.php" class="sec-link">إدارة المستخدمين <i class="bi bi-arrow-left"></i></a>
      </div>
      <div style="overflow-x:auto">
        <table class="usr-table">
          <thead><tr><th>الاسم</th><th>البريد</th><th>تاريخ التسجيل</th></tr></thead>
          <tbody id="recentUsersTable"><tr><td colspan="3" style="text-align:center;color:var(--c-muted);padding:20px"><div class="spin"></div></td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent activity log -->
  <div class="sec">
    <div class="sec-hd">
      <div class="sec-title"><i class="bi bi-activity"></i> آخر النشاطات في النظام</div>
      <a href="<?php echo $basePath;?>services/admin/admin-activity-logs.php" class="sec-link">كل النشاطات <i class="bi bi-arrow-left"></i></a>
    </div>
    <div id="activityLog"><div class="spin"></div></div>
  </div>

  <!-- All sections admin links -->
  <div class="sec">
    <div class="sec-hd"><div class="sec-title"><i class="bi bi-lightning-fill"></i> روابط الإدارة السريعة</div></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:10px" id="adminLinks">
      <?php
      $links = [
        ['bi-person-check-fill','إدارة المستخدمين والأدوار','services/admin/admin-users.php','#374151'],
        ['bi-shield-check','الصلاحيات','services/admin/admin-permissions.php','#374151'],
        ['bi-diagram-3-fill','الفروع والمحافظات','services/admin/admin-branches.php','#0369a1'],
        ['bi-file-earmark-check-fill','مراجعة طلبات التسجيل','services/training/registration-requests-review.php','#15803d'],
        ['bi-award-fill','اعتماد الشهادات','services/training/training-certificates-approve.php','#b45309'],
        ['bi-headset','لوحة الاستشارات','services/consulting/consulting-admin-dashboard.php','#6d28d9'],
        ['bi-buildings-fill','المكاتب الاستشارية','services/consulting/consulting-offices-list.php','#6d28d9'],
        ['bi-geo-alt-fill','خريطة GIS','services/gis/needs-map.php','#b45309'],
        ['bi-rocket-takeoff-fill','الحاضنات','services/incubation/incubators-list.php','#7c3aed'],
        ['bi-diagram-3-fill','المشاريع المحتضنة','services/incubation/incubated-projects.php','#7c3aed'],
        ['bi-bar-chart-fill','تقارير الحاضنات','services/incubation/incubation-reports.php','#7c3aed'],
        ['bi-envelope-fill','صندوق الرسائل','inbox/inbox-list.php','var(--c-primary)'],
        ['bi-bell-fill','الإشعارات','notifications/notifications-list.php','#3730a3'],
      ];
      foreach($links as $l): ?>
      <a href="<?php echo $basePath.$l[2];?>" style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f9fafb;border:1px solid var(--c-border);border-radius:14px;text-decoration:none;color:var(--c-text);font-weight:700;font-size:.87rem;transition:background .15s">
        <i class="bi <?php echo $l[0];?>" style="font-size:1.2rem;color:<?php echo $l[3];?>"></i><?php echo $l[1];?>
      </a>
      <?php endforeach;?>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const isAdmin = window.AppAuth.hasRole?.('admin') || window.AppAuth.hasRole?.('super_admin');
  if (!isAdmin) {
    document.querySelector('.pw').innerHTML = `<div style="text-align:center;padding:100px;color:#dc2626">
      <i class="bi bi-shield-lock-fill" style="font-size:4rem;display:block;margin-bottom:16px"></i>
      <strong style="font-size:1.2rem">صلاحية مرفوضة</strong><br>
      <a href="../../dashboard.php" style="color:var(--c-primary);font-weight:700">العودة للوحة الرئيسية</a>
    </div>`;
    return;
  }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const user  = window.AppAuth.getUser();

  // Hero
  document.getElementById('heroName').textContent = 'مرحباً، ' + (user?.name || 'المدير العام');
  document.getElementById('heroTime').textContent = new Date().toLocaleString('ar-SY', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  async function apiGet(url) {
    try {
      const r = await fetch(url, { headers: { 'Authorization': `Bearer ${token()}`, 'Accept': 'application/json' } });
      return r.ok ? r.json() : null;
    } catch { return null; }
  }

  const STATUS_COLORS = {
    draft:'#9ca3af', submitted:'#3b82f6', needs_info:'#f59e0b', sorting:'#8b5cf6',
    awaiting_offers:'#6366f1', offer_submitted:'#22c55e', in_progress:'#10b981',
    report_submitted:'#0ea5e9', completed:'#16a34a', rejected:'#ef4444',
  };
  const STATUS_LABELS = {
    draft:'مسودة', submitted:'مُرسَل', needs_info:'بحاجة معلومات', sorting:'قيد الفرز',
    awaiting_offers:'بانتظار عروض', offer_submitted:'عرض مقدم', in_progress:'قيد التنفيذ',
    report_submitted:'تقرير مرفوع', completed:'مكتمل', rejected:'مرفوض',
  };
  const MODULE_COLORS = { تدريب:'#15803d', استشارة:'#6d28d9', تمويل:'#be123c' };
  const ACT_COLORS = ['#17947B','#1d4ed8','#15803d','#b45309','#6d28d9','#be123c'];

  // ── Load dashboard API ──
  const dash = await apiGet(`${base}/dashboard`);

  if (dash) {
    set('k-users',   dash.users_active ?? dash.users_total ?? '—');
    set('k-centers', dash.centers ?? '—');
    set('k-courses', dash.courses ?? '—');
    set('k-certs',   dash.certificates_approved ?? dash.certificates_total ?? '—');
    set('m-centers', dash.centers ?? '—');
    set('m-trainers', dash.trainers ?? '—');
    set('m-trainees', dash.trainees ?? '—');
    set('m-users',   dash.users_total ?? '—');

    // Alerts
    const alerts = [];
    if ((dash.certificates_pending ?? 0) > 0) alerts.push({ cls:'alert-warn', icon:'bi-award-fill', text:`${dash.certificates_pending} شهادة بانتظار الاعتماد`, url:'../../services/training/training-certificates-approve.php' });
    if ((dash.center_registration_requests_pending ?? 0) > 0) alerts.push({ cls:'alert-info', icon:'bi-building-fill', text:`${dash.center_registration_requests_pending} طلب تسجيل مركز بانتظار المراجعة`, url:'../../services/training/registration-requests-review.php' });
    if ((dash.trainer_registration_requests_pending ?? 0) > 0) alerts.push({ cls:'alert-info', icon:'bi-person-fill', text:`${dash.trainer_registration_requests_pending} طلب تسجيل مدرب جديد`, url:'../../services/training/registration-requests-review.php' });

    if (alerts.length) {
      document.getElementById('alertsBox').style.display = 'block';
      document.getElementById('alertsList').innerHTML = alerts.map(a =>
        `<a href="${a.url}" class="alert-item ${a.cls}" style="text-decoration:none"><i class="bi ${a.icon}"></i>${a.text} <i class="bi bi-arrow-left" style="margin-right:auto"></i></a>`
      ).join('');
    }

    // Recent users
    if (dash.recent_users?.length) {
      document.getElementById('recentUsersTable').innerHTML = dash.recent_users.map(u =>
        `<tr><td style="font-weight:700">${window.APP_HELPERS.e(u.name)}</td><td style="color:var(--c-muted)">${window.APP_HELPERS.e(u.email)}</td><td style="font-size:.8rem;color:var(--c-muted)">${new Date(u.created_at).toLocaleDateString('ar-SY')}</td></tr>`
      ).join('');
    } else { document.getElementById('recentUsersTable').innerHTML = '<tr><td colspan="3" style="text-align:center;color:var(--c-muted);padding:16px">لا توجد بيانات</td></tr>'; }

    // Cert chart
    const certData = [
      { label:'معتمدة', val: dash.certificates_approved ?? 0, color:'#16a34a' },
      { label:'بانتظار اعتماد', val: dash.certificates_pending ?? 0, color:'#f59e0b' },
      { label:'مرفوضة', val: dash.certificates_rejected ?? 0, color:'#ef4444' },
    ];
    const certTotal = certData.reduce((s, d) => s + d.val, 0) || 1;
    document.getElementById('certChart').innerHTML = certData.map(d => `
      <div class="bar-row">
        <span style="font-weight:700;color:var(--c-text)">${d.label}</span>
        <div class="bar-bg"><div class="bar-fill" style="width:${Math.round(d.val/certTotal*100)}%;background:${d.color}"></div></div>
        <span style="font-weight:800;color:${d.color}">${d.val}</span>
      </div>`).join('');

    // Activity log
    if (dash.recent_activity?.length) {
      document.getElementById('activityLog').innerHTML = dash.recent_activity.map((a, i) => `
        <div class="act-item">
          <div class="act-dot" style="background:${ACT_COLORS[i % ACT_COLORS.length]}"></div>
          <div>
            <div class="act-text">${window.APP_HELPERS.e(a.description || a.action)}</div>
            <div class="act-meta"><strong>${window.APP_HELPERS.e(a.user?.name || 'النظام')}</strong> · ${a.module || ''} · ${new Date(a.created_at).toLocaleString('ar-SY')}</div>
          </div>
        </div>`).join('');
    } else { document.getElementById('activityLog').innerHTML = '<div style="color:var(--c-muted);font-size:.88rem;text-align:center;padding:20px">لا توجد نشاطات مسجلة</div>'; }
  }

  // ── Consulting stats ──
  apiGet(`${base}/consulting/requests?per_page=500`).then(json => {
    const items = json?.data?.data || json?.data || json || [];
    set('k-consulting', items.length);
    set('m-consulting', items.length);

    const byStatus = {};
    items.forEach(r => { byStatus[r.status] = (byStatus[r.status] || 0) + 1; });
    const total = items.length || 1;
    const sorted = Object.entries(byStatus).sort((a,b) => b[1]-a[1]);

    document.getElementById('consultingChart').innerHTML = sorted.length ? sorted.map(([s,n]) => `
      <div class="bar-row">
        <span style="font-weight:700;color:var(--c-text);font-size:.82rem">${STATUS_LABELS[s] || s}</span>
        <div class="bar-bg"><div class="bar-fill" style="width:${Math.round(n/total*100)}%;background:${STATUS_COLORS[s] || '#9ca3af'}"></div></div>
        <span style="font-weight:800;color:${STATUS_COLORS[s] || '#9ca3af'}">${n}</span>
      </div>`).join('') : '<div style="color:var(--c-muted);font-size:.87rem;text-align:center;padding:20px">لا توجد طلبات بعد</div>';

    // Pending actions
    const pending = items.filter(r => ['submitted','report_submitted','needs_info'].includes(r.status)).slice(0, 6);
    document.getElementById('pendingActions').innerHTML = pending.length ? pending.map(r => `
      <div style="display:flex;gap:10px;align-items:center;padding:9px 0;border-bottom:1px solid var(--c-border)">
        <span style="width:8px;height:8px;border-radius:50%;background:${STATUS_COLORS[r.status]||'#9ca3af'};flex-shrink:0"></span>
        <div style="flex:1;min-width:0">
          <div style="font-weight:700;font-size:.86rem;color:var(--c-text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${window.APP_HELPERS.e(r.title)}</div>
          <div style="font-size:.76rem;color:var(--c-muted)">${r.request_code} · ${STATUS_LABELS[r.status]||r.status}</div>
        </div>
        <a href="../../services/consulting/consulting-request-view.php?id=${r.id}" style="color:var(--c-primary);font-size:.82rem;font-weight:700;text-decoration:none;flex-shrink:0">عرض</a>
      </div>`).join('') : '<div style="color:var(--c-muted);font-size:.87rem;text-align:center;padding:20px">لا توجد طلبات معلقة</div>';
  }).catch(() => {
    set('k-consulting', '—'); set('m-consulting', '—');
    document.getElementById('consultingChart').innerHTML = '<div style="color:var(--c-muted);font-size:.85rem">تعذّر التحميل</div>';
    document.getElementById('pendingActions').innerHTML = '<div style="color:var(--c-muted);font-size:.85rem">تعذّر التحميل</div>';
  });

  // ── Finance stats ──
  apiGet(`${base}/finance/applications?per_page=1`).then(json => {
    const total = json?.meta?.total ?? json?.total ?? (json?.data?.length ?? '—');
    set('k-finance', total); set('m-finance', total);
  }).catch(() => { set('k-finance','—'); set('m-finance','—'); });

  // ── Workforce stats ──
  apiGet(`${base}/workforces?per_page=1`).then(json => {
    const total = json?.meta?.total ?? json?.total ?? (json?.data?.length ?? '—');
    set('k-workforce', total); set('m-wf', total);
  }).catch(() => { set('k-workforce','—'); set('m-wf','—'); });

  // ── GIS needs ──
  apiGet(`${base}/needs?per_page=1`).then(json => {
    const total = json?.meta?.total ?? json?.total ?? '—';
    set('m-gis', total);
  }).catch(() => { set('m-gis','—'); });

  // ── Incubation stats ──
  apiGet(`${base}/incubation/stats`).then(json => {
    set('k-incubation', json?.projects_active ?? 0);
    set('m-incubation', json?.incubators_total ?? 0);
  }).catch(() => { set('k-incubation','—'); set('m-incubation','—'); });

  // ── Notifications summary ──
  apiGet(`${base}/notifications/summary`).then(json => {
    set('k-notif', json?.unread_count ?? 0);
  }).catch(() => { set('k-notif', '—'); });

  function set(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = typeof val === 'number' ? val.toLocaleString('ar') : val;
  }
});
</script>
</body>
</html>
