<?php
$basePath  = $basePath ?? './';
$pageTitle = $pageTitle ?? 'حسابي الشخصي';
$activePage= 'my-profile';
$profileShell = defined('PROFILE_SHELL') ? PROFILE_SHELL : 'app';
$isCenterProfileShell = ($profileShell === 'center' || $profileShell === 'trainee');
$isTraineeProfileShell = ($profileShell === 'trainee');
?>
<?php include __DIR__ . '/includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/includes/layout/head.php'; ?>
  <?php if ($isCenterProfileShell): ?>
    <?php include __DIR__ . '/services/training/_tc-styles.php'; ?>
  <?php else: ?>
    <?php include __DIR__ . '/includes/layout/app-shell-styles.php'; ?>
    <script>
    (function () {
      try {
        var u = JSON.parse(localStorage.getItem('authority_user') || '{}');
        var roles = (u.roles || []).map(function (r) { return typeof r === 'string' ? r : (r && r.name) || ''; });
        if (roles.indexOf('center_user') >= 0 || roles.indexOf('trainer_user') >= 0) {
          location.replace('services/training/center-profile.php');
        } else if (roles.indexOf('trainee_user') >= 0) {
          location.replace('services/training/trainee-profile.php');
        }
      } catch (e) {}
    })();
    </script>
  <?php endif; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#17947B;--c-accent:#06AA89;--c-soft:#EAF8F4;--c-border:rgba(23,148,123,.13);--c-text:#16332E;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(15,79,71,.07);}
    body{background:linear-gradient(160deg,#f0faf7,#e8f7f3);}
    .pw{max-width:980px;margin:auto;padding:22px 14px;}
    <?php if ($isCenterProfileShell): ?>
    .tc-phone .pw{max-width:920px;margin:0;padding:14px 14px 28px;}
    @media (min-width:900px){ .tc-phone .pw{padding:22px 28px 36px;} }
    <?php endif; ?>

    /* ── Hero card ── */
    .profile-hero{background:linear-gradient(135deg,#0f5e4f,var(--c-primary),var(--c-accent));border-radius:24px;padding:28px 32px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;}
    .profile-hero::before{content:"";position:absolute;width:220px;height:220px;border-radius:50%;top:-110px;left:-80px;background:radial-gradient(circle,rgba(255,255,255,.14),transparent 70%);}
    .avatar-wrap{width:72px;height:72px;border-radius:20px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:2.2rem;font-weight:800;flex-shrink:0;border:2px solid rgba(255,255,255,.3);}
    .role-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.18);backdrop-filter:blur(4px);border-radius:20px;padding:5px 14px;font-size:.82rem;font-weight:700;margin-top:8px;}

    /* ── Tabs ── */
    .tabs{display:flex;gap:4px;background:#fff;border:1px solid var(--c-border);border-radius:16px;padding:5px;margin-bottom:20px;box-shadow:var(--c-shadow);overflow-x:auto;}
    .tab-btn{flex-shrink:0;padding:9px 18px;border:none;border-radius:12px;background:transparent;font-weight:700;font-size:.87rem;cursor:pointer;color:var(--c-muted);transition:all .2s;white-space:nowrap;display:flex;align-items:center;gap:6px;}
    .tab-btn.active{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}

    /* ── Cards ── */
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:1rem;color:var(--c-text);margin:0 0 18px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}

    /* ── Form ── */
    .form-group{margin-bottom:16px;}
    .form-label{font-size:.83rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px;}
    .form-control{width:100%;border:1px solid var(--c-border);border-radius:12px;padding:10px 14px;font-size:.9rem;color:var(--c-text);background:#fff;transition:border-color .2s;box-sizing:border-box;}
    .form-control:focus{outline:none;border-color:var(--c-accent);box-shadow:0 0 0 3px rgba(6,170,137,.1);}
    .form-control:disabled{background:#f9fafb;color:var(--c-muted);}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    @media(max-width:600px){.form-row{grid-template-columns:1fr;}}

    /* ── Buttons ── */
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:14px;padding:11px 26px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:opacity .2s;font-size:.9rem;}
    .btn-primary:hover{opacity:.9;}
    .btn-primary:disabled{opacity:.6;cursor:not-allowed;}
    .btn-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);border-radius:14px;padding:10px 22px;font-weight:700;cursor:pointer;font-size:.88rem;transition:background .2s;}
    .btn-outline:hover{background:var(--c-soft);}
    .btn-danger{background:#fee2e2;color:#dc2626;border:none;border-radius:14px;padding:10px 22px;font-weight:700;cursor:pointer;font-size:.88rem;}

    /* ── KPI mini ── */
    .kpi-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;}
    .kpi-mini{background:var(--c-soft);border-radius:14px;padding:14px;text-align:center;}
    .kpi-mini-val{font-size:1.8rem;font-weight:800;color:var(--c-primary);}
    .kpi-mini-lbl{font-size:.76rem;font-weight:700;color:var(--c-muted);margin-top:3px;}

    /* ── Permissions list ── */
    .perm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:6px;}
    .perm-tag{background:#f3f4f6;border-radius:8px;padding:5px 10px;font-size:.77rem;font-weight:700;color:var(--c-text);display:flex;align-items:center;gap:5px;}
    .perm-tag i{color:var(--c-primary);font-size:.8rem;}

    /* ── Alert ── */
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:11px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:11px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}

    /* ── Activity ── */
    .act-item{display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--c-border);font-size:.85rem;}
    .act-item:last-child{border-bottom:none;}
    .act-text{font-weight:600;color:var(--c-text);}
    .act-time{font-size:.75rem;color:var(--c-muted);margin-top:2px;}

    /* ── Role-specific colors ── */
    .role-admin{background:linear-gradient(135deg,#1e1b4b,#4338ca);}
    .role-super_admin{background:linear-gradient(135deg,#0f5e4f,#17947B);}
    .role-branch_manager{background:linear-gradient(135deg,#1d4ed8,#3b82f6);}
    .role-training_manager{background:linear-gradient(135deg,#15803d,#22c55e);}
    .role-trainer_user{background:linear-gradient(135deg,#b45309,#f59e0b);}
    .role-trainee_user{background:linear-gradient(135deg,#6d28d9,#a78bfa);}
    .role-center_user{background:linear-gradient(135deg,#0369a1,#38bdf8);}
    .role-auditor{background:linear-gradient(135deg,#374151,#6b7280);}

    .hidden{display:none;}
  </style>
</head>
<body>
<?php if ($isTraineeProfileShell): ?>
  <?php $teActive = 'profile'; include __DIR__ . '/services/training/_te-sidebar.php'; ?>
  <div class="tc-phone">
    <div class="tc-bar">
      <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
      <a class="ic" href="<?php echo $basePath; ?>services/training/trainee-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
      <div class="ttl">حسابي الشخصي</div>
      <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
    </div>
<?php elseif ($isCenterProfileShell): ?>
  <?php $tcActive = 'profile'; include __DIR__ . '/services/training/_tc-sidebar.php'; ?>
  <div class="tc-phone">
    <div class="tc-bar">
      <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
      <a class="ic" id="profileBackHome" href="<?php echo $basePath; ?>services/training/center-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
      <div class="ttl">حسابي الشخصي</div>
      <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      try{
        if (window.AppAuth && AppAuth.isTrainerWorkspaceUser && AppAuth.isTrainerWorkspaceUser()) {
          var b = document.getElementById('profileBackHome');
          if (b) b.href = '<?php echo $basePath; ?>services/training/trainer-app.php';
        }
      }catch(e){}
    });
    </script>
<?php else: ?>
  <?php include __DIR__ . '/includes/layout/app-shell-open.php'; ?>
<?php endif; ?>

<div class="pw">

  <!-- ── Hero ── -->
  <div class="profile-hero" id="profileHero">
    <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;position:relative;z-index:1">
      <div class="avatar-wrap" id="heroAvatar">—</div>
      <div>
        <div style="font-size:1.5rem;font-weight:800;margin-bottom:2px" id="heroName">جاري التحميل...</div>
        <div style="opacity:.85;font-size:.88rem" id="heroEmail"></div>
        <div class="role-badge" id="heroBadge"><i class="bi bi-shield-check"></i> <span id="heroRole"></span></div>
      </div>
      <div style="margin-right:auto;text-align:left">
        <div style="opacity:.7;font-size:.8rem;margin-bottom:4px">آخر دخول</div>
        <div style="font-size:.88rem;font-weight:700" id="heroLastLogin">—</div>
      </div>
    </div>
  </div>

  <!-- ── Tabs ── -->
  <div class="tabs" id="tabsBar">
    <button class="tab-btn active" onclick="switchTab('info')"><i class="bi bi-person-fill"></i> معلوماتي</button>
    <button class="tab-btn" onclick="switchTab('stats')"><i class="bi bi-bar-chart-fill"></i> إحصائياتي</button>
    <button class="tab-btn" onclick="switchTab('security')"><i class="bi bi-lock-fill"></i> الأمان</button>
    <button class="tab-btn" onclick="switchTab('permissions')"><i class="bi bi-key-fill"></i> صلاحياتي</button>
    <button class="tab-btn" onclick="switchTab('activity')"><i class="bi bi-clock-history"></i> نشاطاتي</button>
    <button class="tab-btn" onclick="switchTab('children')"><i class="bi bi-diagram-3-fill"></i> مستخدميَّ</button>
  </div>

  <!-- ══════════════ TAB: INFO ══════════════ -->
  <div id="tab-info">
    <div class="card">
      <div class="card-title"><i class="bi bi-person-fill"></i> البيانات الشخصية</div>
      <div id="infoSuccess" class="alert-s"></div>
      <div id="infoError"   class="alert-e"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الاسم الكامل</label>
          <input type="text" id="f-name" class="form-control" placeholder="الاسم">
        </div>
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني</label>
          <input type="email" id="f-email" class="form-control" disabled>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">رقم الهاتف</label>
        <input type="text" id="f-phone" class="form-control" placeholder="09xxxxxxxx">
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn-primary" onclick="saveInfo()"><i class="bi bi-check-circle-fill"></i> حفظ التغييرات</button>
        <button class="btn-outline" onclick="resetInfo()"><i class="bi bi-arrow-counterclockwise"></i> إلغاء</button>
      </div>
    </div>

    <!-- Role-specific extra info -->
    <div id="roleSpecificInfo"></div>
  </div>

  <!-- ══════════════ TAB: STATS ══════════════ -->
  <div id="tab-stats" class="hidden">
    <div class="card">
      <div class="card-title"><i class="bi bi-bar-chart-fill"></i> إحصائياتي</div>
      <div class="kpi-row" id="statsKpis">
        <div style="text-align:center;padding:30px;color:var(--c-muted)"><div style="font-size:1.5rem;margin-bottom:8px">⏳</div>جاري التحميل...</div>
      </div>
    </div>
    <div id="statsExtra"></div>
  </div>

  <!-- ══════════════ TAB: SECURITY ══════════════ -->
  <div id="tab-security" class="hidden">
    <div class="card">
      <div class="card-title"><i class="bi bi-lock-fill"></i> تغيير كلمة المرور</div>
      <div id="pwSuccess" class="alert-s"></div>
      <div id="pwError"   class="alert-e"></div>
      <div class="form-group">
        <label class="form-label">كلمة المرور الحالية</label>
        <input type="password" id="pw-current" class="form-control" placeholder="••••••••">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">كلمة المرور الجديدة</label>
          <input type="password" id="pw-new" class="form-control" placeholder="8 أحرف على الأقل">
        </div>
        <div class="form-group">
          <label class="form-label">تأكيد كلمة المرور</label>
          <input type="password" id="pw-confirm" class="form-control" placeholder="أعد الكتابة">
        </div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:16px;background:#fef3c7;border-radius:12px;padding:10px 14px">
        <i class="bi bi-exclamation-triangle-fill" style="color:#d97706"></i>
        <span style="font-size:.84rem;color:#92400e;font-weight:600">تغيير كلمة المرور سيُلغي جميع جلساتك الأخرى المفتوحة.</span>
      </div>
      <button class="btn-primary" onclick="changePassword()"><i class="bi bi-lock-fill"></i> تغيير كلمة المرور</button>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-shield-exclamation"></i> معلومات الأمان</div>
      <div style="display:grid;gap:10px;font-size:.88rem">
        <div style="display:flex;justify-content:space-between;padding:10px;background:#f9fafb;border-radius:10px">
          <span style="font-weight:700;color:var(--c-muted)">حالة الحساب</span>
          <span id="sec-status" style="font-weight:700;color:#16a34a">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:10px;background:#f9fafb;border-radius:10px">
          <span style="font-weight:700;color:var(--c-muted)">نوع الكيان</span>
          <span id="sec-entity" style="font-weight:700;color:var(--c-text)">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:10px;background:#f9fafb;border-radius:10px">
          <span style="font-weight:700;color:var(--c-muted)">تاريخ إنشاء الحساب</span>
          <span id="sec-created" style="font-weight:700;color:var(--c-text)">—</span>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════════ TAB: PERMISSIONS ══════════════ -->
  <div id="tab-permissions" class="hidden">
    <div class="card">
      <div class="card-title"><i class="bi bi-shield-check"></i> أدواري</div>
      <div id="rolesList" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px"></div>
    </div>
    <div class="card">
      <div class="card-title"><i class="bi bi-key-fill"></i> صلاحياتي (<span id="permCount">0</span>)</div>
      <div class="perm-grid" id="permsList"></div>
    </div>
  </div>

  <!-- ══════════════ TAB: ACTIVITY ══════════════ -->
  <div id="tab-activity" class="hidden">
    <div class="card">
      <div class="card-title"><i class="bi bi-clock-history"></i> آخر إشعاراتي</div>
      <div id="myNotifList">
        <div style="text-align:center;padding:30px;color:var(--c-muted)">جاري التحميل...</div>
      </div>
    </div>
    <div class="card">
      <div class="card-title"><i class="bi bi-envelope-open-fill"></i> آخر رسائلي</div>
      <div id="myMsgList">
        <div style="text-align:center;padding:30px;color:var(--c-muted)">جاري التحميل...</div>
      </div>
    </div>
  </div>

  <!-- ══════════════ TAB: CHILDREN ══════════════ -->
  <div id="tab-children" class="hidden">
    <div class="card">
      <div class="card-title"><i class="bi bi-diagram-3-fill"></i> مستخدميَّ المباشرون</div>
      <div id="childrenList">
        <div style="text-align:center;padding:30px;color:var(--c-muted)">جاري التحميل...</div>
      </div>
      <div style="margin-top:14px">
        <a href="services/admin/my-children.php" class="btn-primary" style="text-decoration:none;display:inline-flex">
          <i class="bi bi-diagram-3-fill"></i> إدارة كاملة للمستخدمين
        </a>
      </div>
    </div>
    <div class="card" id="delegatableCard">
      <div class="card-title"><i class="bi bi-key-fill"></i> ما يمكنني منحه لأبنائي</div>
      <div style="margin-bottom:10px">
        <span style="font-size:.83rem;color:var(--c-muted);font-weight:600">الأدوار القابلة للتفويض:</span>
        <div id="delegatableRoles" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px"></div>
      </div>
      <div>
        <span style="font-size:.83rem;color:var(--c-muted);font-weight:600">عدد الصلاحيات القابلة للتفويض:</span>
        <span id="delegatablePermCount" style="font-weight:800;color:var(--c-primary);margin-right:6px">—</span>
      </div>
    </div>
  </div>

</div><!-- /.pw -->

<?php if ($isCenterProfileShell): ?>
  </div><!-- /.tc-phone -->
  <?php include __DIR__ . '/includes/layout/scripts.php'; ?>
<?php else: ?>
  <?php include __DIR__ . '/includes/layout/app-shell-close.php'; ?>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const front = (window.APP_CONFIG.FRONTEND_BASE_URL || '').replace(/\/$/, '');
  const token = () => window.AppAuth.getToken();
  const appHref = (path) => front + '/' + String(path || '').replace(/^\//, '');
  let me = null;

  const ROLE_LABELS = {
    admin:'مدير النظام', super_admin:'المدير العام', branch_manager:'مدير فرع',
    branch_officer:'موظف فرع', system_admin:'مدير تقني', training_manager:'مدير تدريب',
    deputy_director:'مساعد المدير', deputy_general_director:'نائب المدير العام',
    center_user:'مستخدم مركز تدريبي', trainer_user:'مدرب', trainee_user:'متدرب',
    auditor:'مراجع', consulting_office_user:'مكتب استشاري',
  };

  const ROLE_ICONS = {
    admin:'bi-shield-fill', super_admin:'bi-person-badge-fill', branch_manager:'bi-diagram-3-fill',
    training_manager:'bi-mortarboard-fill', trainer_user:'bi-person-workspace',
    trainee_user:'bi-people-fill', center_user:'bi-buildings-fill',
    auditor:'bi-eye-fill', system_admin:'bi-gear-fill',
  };

  async function apiGet(url) {
    try {
      const r = await fetch(url, { headers:{ 'Authorization':`Bearer ${token()}`, 'Accept':'application/json' }});
      return r.ok ? r.json() : null;
    } catch { return null; }
  }

  // ── Load /me ──
  const meJson = await apiGet(`${base}/me`);
  me = meJson?.user ?? meJson;
  if (!me) return;

  const mainRole = me.roles?.[0] ?? 'user';
  const roleLbl  = ROLE_LABELS[mainRole] ?? mainRole;
  const roleIcon = ROLE_ICONS[mainRole]  ?? 'bi-person-fill';

  // Hero
  const initials = (me.name||'U').split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
  document.getElementById('heroAvatar').textContent = initials;
  document.getElementById('heroName').textContent   = me.name || '—';
  document.getElementById('heroEmail').textContent  = me.email || '';
  document.getElementById('heroRole').textContent   = roleLbl;

  // Apply role color to hero
  const heroEl = document.getElementById('profileHero');
  const roleClass = 'role-' + mainRole;
  if (document.querySelector('.' + roleClass)) heroEl.classList.add(roleClass);

  // Info form
  document.getElementById('f-name').value  = me.name  || '';
  document.getElementById('f-email').value = me.email || '';
  document.getElementById('f-phone').value = me.phone || '';

  // Security tab
  document.getElementById('sec-status').textContent  = me.is_active ? '✅ نشط' : '🔴 موقوف';
  document.getElementById('sec-entity').textContent  = me.entity_type || '—';
  document.getElementById('sec-created').textContent = me.created_at ? new Date(me.created_at).toLocaleDateString('ar-SY') : '—';

  // Permissions tab
  const roles = me.roles ?? [];
  document.getElementById('rolesList').innerHTML = roles.map(r =>
    `<span style="background:var(--c-soft);color:var(--c-primary);padding:6px 14px;border-radius:20px;font-size:.84rem;font-weight:700;display:inline-flex;align-items:center;gap:6px"><i class="bi ${ROLE_ICONS[r]||'bi-person-fill'}"></i>${ROLE_LABELS[r]||r}</span>`
  ).join('');

  const perms = me.permissions ?? [];
  document.getElementById('permCount').textContent = perms.length;
  document.getElementById('permsList').innerHTML = perms.length
    ? perms.map(p => `<div class="perm-tag"><i class="bi bi-check2"></i>${p}</div>`).join('')
    : '<div style="color:var(--c-muted);font-size:.87rem">لا توجد صلاحيات مخصصة (تعمل عبر الدور)</div>';

  // ── Role-specific info panel ──
  renderRoleSpecificInfo(mainRole, me);

  // ── Dashboard stats ──
  loadStats(mainRole);

  // ── Activity tab ──
  loadActivity();

  /* ─── Role-specific info ─── */
  function renderRoleSpecificInfo(role, me) {
    const el = document.getElementById('roleSpecificInfo');
    let html = '';

    if (['admin','super_admin'].includes(role)) {
      html = `<div class="card">
        <div class="card-title"><i class="bi bi-lightning-fill"></i> وصول سريع كمدير عام</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
          ${quickLink('bi-speedometer2','لوحة المدير العام','services/admin/super-admin-dashboard.php','#0f5e4f')}
          ${quickLink('bi-people-fill','إدارة المستخدمين','services/admin/admin-users.php','#1d4ed8')}
          ${quickLink('bi-shield-check','الصلاحيات','services/admin/admin-permissions.php','#374151')}
          ${quickLink('bi-headset','الاستشارات','services/consulting/consulting-admin-dashboard.php','#6d28d9')}
          ${quickLink('bi-envelope-fill','الرسائل','inbox/inbox-list.php','')}
        </div>
      </div>`;
    } else if (role === 'branch_manager') {
      html = `<div class="card">
        <div class="card-title"><i class="bi bi-diagram-3-fill"></i> معلومات الفرع</div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">الفرع</label><input class="form-control" disabled value="${me.branch?.name||'—'}"></div>
          <div class="form-group"><label class="form-label">المحافظة</label><input class="form-control" disabled value="${me.governorate?.name_ar||'—'}"></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:6px">
          ${quickLink('bi-file-earmark-check-fill','مراجعة طلبات التسجيل','services/training/registration-requests-review.php','#15803d')}
          ${quickLink('bi-headset','طلبات الاستشارة','services/consulting/consulting-requests-list.php','#6d28d9')}
        </div>
      </div>`;
    } else if (role === 'training_manager') {
      html = `<div class="card">
        <div class="card-title"><i class="bi bi-mortarboard-fill"></i> وصول سريع</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
          ${quickLink('bi-buildings-fill','المراكز التدريبية','services/training/training-centers-list.php','#0369a1')}
          ${quickLink('bi-person-workspace','المدربون','services/training/training-trainers-list.php','#b45309')}
          ${quickLink('bi-award-fill','الشهادات','services/training/training-certificates-list.php','#be123c')}
          ${quickLink('bi-file-earmark-check-fill','طلبات التسجيل','services/training/registration-requests-review.php','#15803d')}
        </div>
      </div>`;
    } else if (role === 'center_user') {
      html = `<div class="card">
        <div class="card-title"><i class="bi bi-buildings-fill"></i> معلومات المركز التدريبي</div>
        <div class="form-group"><label class="form-label">المركز المرتبط</label><input class="form-control" disabled value="${me.training_center_id ? 'مركز #'+me.training_center_id : 'غير مرتبط'}"></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:6px">
          ${quickLink('bi-phone-fill','تطبيق المركز',appHref('services/training/center-app.php'),'#0f766e')}
          ${quickLink('bi-mortarboard-fill','الدورات',appHref('services/training/center-app.php'),'#15803d')}
          ${quickLink('bi-person-workspace','المدربون',appHref('services/training/center-trainers.php'),'#b45309')}
          ${quickLink('bi-people-fill','المتدربون',appHref('services/training/center-trainees-list.php'),'#0369a1')}
          ${quickLink('bi-briefcase-fill','الحقائب',appHref('services/training/center-kits.php'),'#6d28d9')}
        </div>
      </div>`;
    } else if (role === 'trainer_user') {
      html = `<div class="card">
        <div class="card-title"><i class="bi bi-person-workspace"></i> الملف المهني</div>
        <div class="form-group"><label class="form-label">رقم المدرب</label><input class="form-control" disabled value="${me.trainer_id ? '#'+me.trainer_id : 'غير مرتبط'}"></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:6px">
          ${quickLink('bi-phone-fill','تطبيق المركز',appHref('services/training/center-app.php'),'#0f766e')}
          ${quickLink('bi-person-badge-fill','ملفي المهني',appHref('services/training/my-trainer-profile.php'),'#b45309')}
          ${quickLink('bi-clipboard2-check-fill','ترشيحاتي',appHref('services/training/my-training-kit-nominations.php'),'#6d28d9')}
        </div>
      </div>`;
    } else if (role === 'trainee_user') {
      html = `<div class="card">
        <div class="card-title"><i class="bi bi-people-fill"></i> معلومات المتدرب</div>
        <div class="form-group"><label class="form-label">رقم المتدرب</label><input class="form-control" disabled value="${me.trainee_id ? '#'+me.trainee_id : 'غير مرتبط'}"></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:6px">
          ${quickLink('bi-award-fill','شهاداتي','services/training/training-certificates-list.php','#b45309')}
          ${quickLink('bi-mortarboard-fill','دوراتي','services/training/training-courses-list.php','#15803d')}
          ${quickLink('bi-send-fill','طلب تسجيل دورة','services/training/course-registration-request.php','#6d28d9')}
        </div>
      </div>`;
    } else if (role === 'auditor') {
      html = `<div class="card">
        <div class="card-title"><i class="bi bi-eye-fill"></i> صلاحيات المراجع</div>
        <p style="font-size:.88rem;color:var(--c-muted)">لديك صلاحية الاطلاع على جميع البيانات بدون تعديل.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
          ${quickLink('bi-buildings-fill','المراكز','services/training/training-centers-list.php','#0369a1')}
          ${quickLink('bi-award-fill','الشهادات','services/training/training-certificates-list.php','#b45309')}
          ${quickLink('bi-activity','سجل النشاط','services/admin/admin-activity-logs.php','#374151')}
        </div>
      </div>`;
    }

    el.innerHTML = html;
  }

  function quickLink(icon, label, href, color) {
    return `<a href="${href}" style="display:flex;align-items:center;gap:10px;padding:11px 14px;background:#f9fafb;border:1px solid var(--c-border);border-radius:14px;text-decoration:none;color:var(--c-text);font-weight:700;font-size:.85rem;transition:background .15s">
      <i class="bi ${icon}" style="font-size:1.2rem;color:${color||'var(--c-primary)'}"></i>${label}
    </a>`;
  }

  /* ─── Stats per role ─── */
  async function loadStats(role) {
    const dash = await apiGet(`${base}/dashboard`);
    if (!dash) {
      document.getElementById('statsKpis').innerHTML = '<div style="color:var(--c-muted);text-align:center;padding:20px">تعذّر تحميل الإحصائيات</div>';
      return;
    }

    const kpis = [];

    if (['admin','super_admin'].includes(role)) {
      kpis.push(['المستخدمون', dash.users_total ?? '—', 'bi-people-fill']);
      kpis.push(['المراكز', dash.centers ?? '—', 'bi-buildings-fill']);
      kpis.push(['الشهادات', dash.certificates_total ?? '—', 'bi-award-fill']);
      kpis.push(['الدورات', dash.courses ?? '—', 'bi-mortarboard-fill']);
      kpis.push(['طلبات بانتظار اعتماد', dash.certificates_pending ?? 0, 'bi-clock-fill']);
    } else if (role === 'branch_manager') {
      kpis.push(['الدورات', dash.courses ?? '—', 'bi-mortarboard-fill']);
      kpis.push(['المراكز', dash.centers ?? '—', 'bi-buildings-fill']);
      kpis.push(['المدربون', dash.trainers ?? '—', 'bi-person-workspace']);
      kpis.push(['المتدربون', dash.trainees ?? '—', 'bi-people-fill']);
    } else if (role === 'training_manager') {
      kpis.push(['المدربون', dash.trainers ?? '—', 'bi-person-workspace']);
      kpis.push(['الدورات', dash.courses ?? '—', 'bi-mortarboard-fill']);
      kpis.push(['المتدربون', dash.trainees ?? '—', 'bi-people-fill']);
      kpis.push(['شهادات معتمدة', dash.certificates_approved ?? '—', 'bi-award-fill']);
      kpis.push(['طلبات معلقة', (dash.trainer_registration_requests_pending??0)+(dash.center_registration_requests_pending??0), 'bi-hourglass-split']);
    } else if (role === 'trainer_user') {
      kpis.push(['دوراتي', dash.courses ?? '—', 'bi-mortarboard-fill']);
      kpis.push(['شهاداتي', dash.certificates ?? '—', 'bi-award-fill']);
      kpis.push(['ترشيحاتي', dash.training_kit_nominations_my_count ?? '—', 'bi-clipboard2-check-fill']);
    } else if (role === 'trainee_user') {
      kpis.push(['شهاداتي', dash.certificates ?? '—', 'bi-award-fill']);
      kpis.push(['اجتزت', dash.passed ?? '—', 'bi-trophy-fill']);
      kpis.push(['طلبات تسجيل', dash.course_registration_requests_my_count ?? '—', 'bi-send-fill']);
    } else if (role === 'center_user') {
      kpis.push(['الدورات', dash.courses ?? '—', 'bi-mortarboard-fill']);
      kpis.push(['المدربون', dash.trainers ?? '—', 'bi-person-workspace']);
      kpis.push(['الشهادات', dash.certificates_total ?? '—', 'bi-award-fill']);
    } else {
      kpis.push(['الدورات', dash.courses ?? '—', 'bi-mortarboard-fill']);
      kpis.push(['الشهادات', dash.certificates ?? dash.certificates_total ?? '—', 'bi-award-fill']);
    }

    document.getElementById('statsKpis').innerHTML = kpis.map(([lbl,val,icon]) => `
      <div class="kpi-mini">
        <i class="bi ${icon}" style="font-size:1.4rem;color:var(--c-primary);margin-bottom:6px;display:block"></i>
        <div class="kpi-mini-val">${typeof val === 'number' ? val.toLocaleString('ar') : val}</div>
        <div class="kpi-mini-lbl">${lbl}</div>
      </div>`).join('');
  }

  /* ─── Activity ─── */
  async function loadActivity() {
    const [notifJson, inboxJson] = await Promise.all([
      apiGet(`${base}/notifications?per_page=5`),
      apiGet(`${base}/inbox?per_page=5`),
    ]);

    const notifs = notifJson?.data ?? [];
    const COLORS = { primary:'#17947B', success:'#16a34a', warning:'#d97706', danger:'#dc2626', info:'#0ea5e9' };

    document.getElementById('myNotifList').innerHTML = notifs.length
      ? notifs.map(n => `<div class="act-item">
          <i class="bi ${n.icon||'bi-bell-fill'}" style="color:${COLORS[n.color]||'#17947B'};font-size:1rem;flex-shrink:0;margin-top:2px"></i>
          <div><div class="act-text">${window.APP_HELPERS.e(n.title)}</div><div class="act-time">${new Date(n.created_at).toLocaleString('ar-SY')}</div></div>
          ${!n.is_read?'<span style="width:8px;height:8px;border-radius:50%;background:#ef4444;flex-shrink:0;margin-top:6px;margin-right:auto"></span>':''}
        </div>`).join('')
      : '<div style="text-align:center;color:var(--c-muted);padding:20px;font-size:.88rem">لا توجد إشعارات</div>';

    const msgs = inboxJson?.data ?? [];
    document.getElementById('myMsgList').innerHTML = msgs.length
      ? msgs.map(m => `<div class="act-item" style="cursor:pointer" onclick="location.href='${appHref('inbox/inbox-view.php')}?id=${m.id}'">
          <i class="bi bi-envelope-fill" style="color:var(--c-primary);font-size:1rem;flex-shrink:0;margin-top:2px"></i>
          <div style="flex:1;min-width:0">
            <div class="act-text" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${window.APP_HELPERS.e(m.subject)}</div>
            <div class="act-time">${window.APP_HELPERS.e(m.sender?.name||'النظام')} · ${new Date(m.created_at).toLocaleString('ar-SY')}</div>
          </div>
          ${!m.is_read?'<span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;margin-top:6px"></span>':''}
        </div>`).join('')
      : '<div style="text-align:center;color:var(--c-muted);padding:20px;font-size:.88rem">لا توجد رسائل</div>';
  }

  /* ─── Tabs ─── */
  const TABS = ['info','stats','security','permissions','activity','children'];
  let childrenLoaded = false;
  window.switchTab = function(tab) {
    TABS.forEach(t => {
      document.getElementById('tab-'+t).classList.toggle('hidden', t !== tab);
    });
    document.querySelectorAll('.tab-btn').forEach((btn,i) => btn.classList.toggle('active', TABS[i]===tab));
    if (tab === 'children' && !childrenLoaded) { loadChildrenTab(); childrenLoaded = true; }
  };

  /* ─── Save info ─── */
  let origName, origPhone;
  function resetInfo() {
    document.getElementById('f-name').value  = me.name  || '';
    document.getElementById('f-phone').value = me.phone || '';
  }

  window.saveInfo = async function() {
    const name  = document.getElementById('f-name').value.trim();
    const phone = document.getElementById('f-phone').value.trim();
    if (!name) { showMsg('infoError','الاسم مطلوب'); return; }

    const r = await fetch(`${base}/me`, {
      method:'PUT',
      headers:{ 'Authorization':`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify({ name, phone: phone||null }),
    });
    const json = await r.json();
    if (!r.ok) { showMsg('infoError', json.message||'حدث خطأ'); return; }
    me = json.user ?? me;
    document.getElementById('heroName').textContent = me.name;
    showMsg('infoSuccess','✓ تم حفظ بياناتك بنجاح');
  };

  /* ─── Change password ─── */
  window.changePassword = async function() {
    const cur   = document.getElementById('pw-current').value;
    const nw    = document.getElementById('pw-new').value;
    const conf  = document.getElementById('pw-confirm').value;
    if (!cur||!nw||!conf) { showMsg('pwError','يرجى تعبئة جميع الحقول'); return; }
    if (nw !== conf) { showMsg('pwError','كلمتا المرور غير متطابقتين'); return; }
    if (nw.length < 8) { showMsg('pwError','كلمة المرور يجب أن تكون 8 أحرف على الأقل'); return; }

    const r = await fetch(`${base}/me/change-password`, {
      method:'POST',
      headers:{ 'Authorization':`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify({ current_password: cur, password: nw, password_confirmation: conf }),
    });
    const json = await r.json();
    if (!r.ok) { showMsg('pwError', Object.values(json.errors??{}).flat()[0] || json.message || 'خطأ'); return; }
    ['pw-current','pw-new','pw-confirm'].forEach(id => document.getElementById(id).value='');
    showMsg('pwSuccess','✓ تم تغيير كلمة المرور. تم إغلاق الجلسات الأخرى.');
  };

  /* ─── Children tab ─── */
  async function loadChildrenTab() {
    const ROLE_LABELS = {
      admin:'مدير', super_admin:'مدير عام', branch_manager:'مدير فرع',
      branch_officer:'موظف فرع', training_manager:'مدير تدريب',
      center_user:'مركز', trainer_user:'مدرب', trainee_user:'متدرب', auditor:'مراجع',
    };

    const [childrenRes, delegRes] = await Promise.all([
      apiGet(`${base}/admin/my-children`),
      apiGet(`${base}/admin/my-delegatable`),
    ]);

    const children = childrenRes?.data ?? [];
    const delegatable = delegRes?.data ?? { roles:[], permissions:[] };

    // render children
    document.getElementById('childrenList').innerHTML = children.length
      ? children.map(u => {
          const initials = (u.name||'U').split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
          const roles = (u.roles||[]).map(r=>`<span style="background:var(--c-soft);color:var(--c-primary);padding:3px 9px;border-radius:12px;font-size:.75rem;font-weight:700">${ROLE_LABELS[r.name]||r.name}</span>`).join(' ');
          return `<div style="display:flex;align-items:center;gap:12px;padding:10px;border:1px solid var(--c-border);border-radius:14px;margin-bottom:8px">
            <div style="width:38px;height:38px;border-radius:10px;background:var(--c-soft);display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--c-primary);flex-shrink:0">${initials}</div>
            <div style="flex:1;min-width:0">
              <div style="font-weight:700;font-size:.9rem">${window.APP_HELPERS.e(u.name)}</div>
              <div style="font-size:.77rem;color:var(--c-muted)">${window.APP_HELPERS.e(u.email||'')}</div>
              <div style="margin-top:4px">${roles}</div>
            </div>
            <span style="width:8px;height:8px;border-radius:50%;background:${u.is_active?'#16a34a':'#dc2626'};flex-shrink:0"></span>
          </div>`;
        }).join('')
      : '<div style="text-align:center;color:var(--c-muted);padding:24px">لا يوجد مستخدمون تابعون لك بعد</div>';

    // render delegatable
    document.getElementById('delegatableRoles').innerHTML = delegatable.roles.map(r =>
      `<span style="background:#f3f4f6;color:var(--c-text);padding:4px 12px;border-radius:12px;font-size:.78rem;font-weight:700">${ROLE_LABELS[r]||r}</span>`
    ).join('') || '<span style="color:var(--c-muted);font-size:.83rem">لا يمكنك تفويض أدوار</span>';
    document.getElementById('delegatablePermCount').textContent = delegatable.permissions.length;
  }

  function showMsg(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg; el.style.display='block';
    setTimeout(() => { el.style.display='none'; }, 5000);
  }
});
</script>
</body>
</html>
