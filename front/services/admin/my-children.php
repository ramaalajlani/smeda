<?php
$basePath  = '../../';
$pageTitle = 'إدارة مستخدميَّ';
$activePage= 'my-children';
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

    /* ─ header ─ */
    .page-head{background:linear-gradient(135deg,#0f5e4f,var(--c-primary));border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.4rem;font-weight:800;}

    /* ─ cards ─ */
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:20px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.95rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}

    /* ─ tree ─ */
    .tree-wrap{overflow-x:auto;}
    .tree-node{border:1px solid var(--c-border);border-radius:16px;padding:14px 16px;background:#fff;margin-bottom:10px;display:flex;align-items:center;gap:12px;transition:box-shadow .15s;}
    .tree-node:hover{box-shadow:0 4px 14px rgba(15,79,71,.1);}
    .tree-node.level-1{border-right:4px solid var(--c-accent);}
    .tree-node.level-2{margin-right:28px;border-right:4px solid #3b82f6;}
    .tree-node.level-3{margin-right:56px;border-right:4px solid #a78bfa;}
    .tree-avatar{width:40px;height:40px;border-radius:12px;background:var(--c-soft);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;color:var(--c-primary);flex-shrink:0;}
    .tree-info{flex:1;min-width:0;}
    .tree-name{font-weight:700;font-size:.92rem;color:var(--c-text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .tree-meta{font-size:.78rem;color:var(--c-muted);margin-top:2px;}
    .badge-role{display:inline-flex;align-items:center;gap:4px;background:var(--c-soft);color:var(--c-primary);font-size:.74rem;font-weight:700;padding:3px 10px;border-radius:20px;margin:2px 2px 0 0;}
    .badge-perm{background:#eff6ff;color:#1d4ed8;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:12px;margin:2px 2px 0 0;display:inline-block;}
    .status-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
    .status-dot.active{background:#16a34a;}
    .status-dot.inactive{background:#dc2626;}

    /* ─ btn ─ */
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:9px 20px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:7px;font-size:.87rem;transition:opacity .2s;}
    .btn-primary:hover{opacity:.9;}
    .btn-sm{padding:6px 12px;font-size:.8rem;border-radius:10px;}
    .btn-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);border-radius:10px;padding:6px 12px;font-size:.8rem;font-weight:700;cursor:pointer;transition:background .15s;}
    .btn-outline:hover{background:var(--c-soft);}
    .btn-danger{background:#fee2e2;color:#dc2626;border:none;border-radius:10px;padding:6px 12px;font-size:.8rem;font-weight:700;cursor:pointer;}

    /* ─ form ─ */
    .form-group{margin-bottom:14px;}
    .form-label{font-size:.81rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px;}
    .form-control{width:100%;border:1px solid var(--c-border);border-radius:11px;padding:9px 13px;font-size:.88rem;color:var(--c-text);box-sizing:border-box;}
    .form-control:focus{outline:none;border-color:var(--c-accent);}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    @media(max-width:600px){.form-row{grid-template-columns:1fr;}}

    /* ─ modal ─ */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;display:none;align-items:center;justify-content:center;padding:16px;}
    .modal-overlay.open{display:flex;}
    .modal-box{background:#fff;border-radius:20px;padding:28px;max-width:560px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);}
    .modal-title{font-size:1.1rem;font-weight:800;color:var(--c-text);margin:0 0 20px;display:flex;align-items:center;gap:8px;}
    .modal-title i{color:var(--c-primary);}

    /* ─ perms grid ─ */
    .perm-section{margin-bottom:14px;}
    .perm-section-title{font-size:.8rem;font-weight:800;color:var(--c-muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;}
    .perm-checkboxes{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:6px;}
    .perm-check{display:flex;align-items:flex-start;gap:8px;padding:7px 10px;border:1px solid var(--c-border);border-radius:10px;cursor:pointer;transition:background .15s;}
    .perm-check:hover{background:var(--c-soft);}
    .perm-check input[type=checkbox]{width:16px;height:16px;accent-color:var(--c-primary);flex-shrink:0;margin-top:2px;}
    .perm-check span{font-size:.8rem;font-weight:600;color:var(--c-text);cursor:pointer;line-height:1.45;word-break:break-word;}

    /* ─ alert ─ */
    .alert-s{background:#dcfce7;color:#14532d;border-radius:11px;padding:10px 14px;font-weight:700;font-size:.85rem;display:none;margin-bottom:12px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:11px;padding:10px 14px;font-weight:700;font-size:.85rem;display:none;margin-bottom:12px;}
    .hidden{display:none;}

    /* ─ empty ─ */
    .empty-box{text-align:center;padding:48px 20px;color:var(--c-muted);}
    .empty-box i{font-size:3rem;margin-bottom:12px;display:block;opacity:.4;}

    /* ─ filter bar ─ */
    .filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;}
    .filter-bar .form-control{max-width:200px;}

    /* ─ kpi strip ─ */
    .kpi-strip{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-bottom:18px;}
    .kpi-mini{background:#fff;border:1px solid var(--c-border);border-radius:14px;padding:14px;text-align:center;}
    .kpi-mini-val{font-size:1.8rem;font-weight:800;color:var(--c-primary);}
    .kpi-mini-lbl{font-size:.75rem;font-weight:700;color:var(--c-muted);margin-top:3px;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">

  <!-- Head -->
  <div class="page-head">
    <div>
      <h1><i class="bi bi-diagram-3-fill"></i> إدارة مستخدميَّ</h1>
      <div style="opacity:.8;font-size:.88rem;margin-top:4px">أنشئ محافظين ومدخلي بيانات ومدققين وفق الهيكلية، وامنحهم صلاحيات من ضمن ما تملكه</div>
    </div>
    <button class="btn-primary" onclick="openCreateModal()">
      <i class="bi bi-person-plus-fill"></i> إضافة مستخدم جديد
    </button>
  </div>

  <!-- KPIs -->
  <div class="kpi-strip" id="kpiStrip">
    <div class="kpi-mini"><div class="kpi-mini-val" id="kpiTotal">—</div><div class="kpi-mini-lbl">إجمالي الأبناء</div></div>
    <div class="kpi-mini"><div class="kpi-mini-val" id="kpiActive" style="color:#16a34a">—</div><div class="kpi-mini-lbl">نشطون</div></div>
    <div class="kpi-mini"><div class="kpi-mini-val" id="kpiInactive" style="color:#dc2626">—</div><div class="kpi-mini-lbl">موقوفون</div></div>
    <div class="kpi-mini"><div class="kpi-mini-val" id="kpiPerms">—</div><div class="kpi-mini-lbl">صلاحياتي المتاحة</div></div>
  </div>

  <!-- Filter -->
  <div class="filter-bar">
    <input type="text"   id="fSearch"   class="form-control" placeholder="🔍 بحث بالاسم أو البريد...">
    <select id="fStatus" class="form-control">
      <option value="">كل الحالات</option>
      <option value="1">نشط</option>
      <option value="0">موقوف</option>
    </select>
    <button class="btn-outline" onclick="loadChildren()"><i class="bi bi-funnel-fill"></i> تصفية</button>
  </div>

  <!-- Tree -->
  <div class="card">
    <div class="card-title"><i class="bi bi-diagram-2-fill"></i> شجرة المستخدمين</div>
    <div id="treeWrap">
      <div class="empty-box"><i class="bi bi-hourglass-split"></i>جاري التحميل...</div>
    </div>
  </div>

</div><!-- /.pw -->

<!-- ═══════════════════ Modal: إنشاء مستخدم ═══════════════════ -->
<div class="modal-overlay" id="createModal" onclick="if(event.target===this)closeCreate()">
  <div class="modal-box">
    <div class="modal-title"><i class="bi bi-person-plus-fill"></i> إضافة مستخدم جديد</div>
    <div id="createSuccess" class="alert-s"></div>
    <div id="createError"   class="alert-e"></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">الاسم الكامل *</label><input type="text" id="cn-name" class="form-control" placeholder="الاسم"></div>
      <div class="form-group"><label class="form-label">البريد الإلكتروني *</label><input type="email" id="cn-email" class="form-control" placeholder="email@example.com"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">كلمة المرور *</label><input type="password" id="cn-pass" class="form-control" placeholder="8 أحرف+"></div>
      <div class="form-group"><label class="form-label">الهاتف</label><input type="text" id="cn-phone" class="form-control" placeholder="09xxxxxxxx"></div>
    </div>
    <div class="form-group">
      <label class="form-label">الدور *</label>
      <select id="cn-role" class="form-control"><option value="">-- اختر الدور --</option></select>
      <div id="cn-role-hint" style="font-size:.78rem;color:var(--c-muted);margin-top:6px"></div>
    </div>
    <div class="form-row" id="cn-scope-row" style="display:none">
      <div class="form-group" id="cn-gov-wrap">
        <label class="form-label" id="cn-gov-label">المحافظة *</label>
        <select id="cn-governorate" class="form-control"><option value="">-- اختر المحافظة --</option></select>
      </div>
      <div class="form-group" id="cn-branch-wrap">
        <label class="form-label">الفرع *</label>
        <select id="cn-branch" class="form-control"><option value="">-- اختر الفرع --</option></select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">الصلاحيات المباشرة (اختياري)</label>
      <div id="cn-perms-wrap"></div>
    </div>
    <div style="display:flex;gap:10px;margin-top:8px">
      <button class="btn-primary" onclick="doCreate()"><i class="bi bi-check-circle-fill"></i> إنشاء</button>
      <button class="btn-outline" onclick="closeCreate()">إلغاء</button>
    </div>
  </div>
</div>

<!-- ═══════════════════ Modal: إدارة ابن ═══════════════════ -->
<div class="modal-overlay" id="manageModal" onclick="if(event.target===this)closeManage()">
  <div class="modal-box">
    <div class="modal-title"><i class="bi bi-person-gear"></i> إدارة: <span id="manageUserName"></span></div>
    <div id="manageSuccess" class="alert-s"></div>
    <div id="manageError"   class="alert-e"></div>

    <!-- Tabs -->
    <div style="display:flex;gap:6px;margin-bottom:16px;border-bottom:1px solid var(--c-border);padding-bottom:10px">
      <button class="btn-outline active" id="mtab-roles"  onclick="switchManageTab('roles')"  style="border-color:var(--c-primary);color:var(--c-primary)"><i class="bi bi-shield-check"></i> الأدوار</button>
      <button class="btn-outline" id="mtab-perms" onclick="switchManageTab('perms')"><i class="bi bi-key-fill"></i> الصلاحيات</button>
      <button class="btn-outline" id="mtab-info"  onclick="switchManageTab('info')"><i class="bi bi-pencil-fill"></i> البيانات</button>
    </div>

    <!-- Roles tab -->
    <div id="mt-roles">
      <p style="font-size:.83rem;color:var(--c-muted);margin-bottom:12px">الأدوار الحالية. يمكنك إضافة أو إزالة الأدوار المسموح بها لك.</p>
      <div id="mn-current-roles" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px"></div>
      <div class="form-group">
        <label class="form-label">إضافة دور</label>
        <div style="display:flex;gap:8px">
          <select id="mn-add-role" class="form-control"><option value="">-- اختر --</option></select>
          <button class="btn-primary btn-sm" onclick="doAddRole()"><i class="bi bi-plus-circle-fill"></i></button>
        </div>
      </div>
    </div>

    <!-- Permissions tab -->
    <div id="mt-perms" class="hidden">
      <p style="font-size:.83rem;color:var(--c-muted);margin-bottom:12px">الصلاحيات المباشرة. لا يمكنك منح صلاحيات لا تملكها أنت.</p>
      <div id="mn-perms-wrap"></div>
      <button class="btn-primary btn-sm" style="margin-top:12px" onclick="doSyncPerms()"><i class="bi bi-check2-all"></i> حفظ الصلاحيات</button>
    </div>

    <!-- Info tab -->
    <div id="mt-info" class="hidden">
      <div class="form-row">
        <div class="form-group"><label class="form-label">الاسم</label><input type="text" id="mn-name" class="form-control"></div>
        <div class="form-group"><label class="form-label">الهاتف</label><input type="text" id="mn-phone" class="form-control"></div>
      </div>
      <div class="form-group">
        <label class="form-label">كلمة مرور جديدة (اتركه فارغاً لعدم التغيير)</label>
        <input type="password" id="mn-pass" class="form-control" placeholder="اختياري">
      </div>
      <div class="form-group" style="display:flex;align-items:center;gap:10px">
        <label class="form-label" style="margin:0">الحالة</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:.88rem;font-weight:700">
          <input type="checkbox" id="mn-active" style="width:18px;height:18px;accent-color:var(--c-primary)"> نشط
        </label>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px">
        <button class="btn-primary btn-sm" onclick="doSaveInfo()"><i class="bi bi-save-fill"></i> حفظ</button>
        <button class="btn-primary btn-sm" onclick="doChangePass()" id="passBtn" style="background:linear-gradient(135deg,#b45309,#f59e0b);display:none"><i class="bi bi-lock-fill"></i> تغيير كلمة المرور</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  let delegatable = { roles: [], permissions: [] };
  let currentChildId = null;
  let childrenData = [];
  let governorates = [];
  let branches = [];

  const GOV_ONLY_ROLES = ['governor'];
  const BRANCH_REQUIRED_ROLES = [
    'branch_manager', 'branch_officer', 'center_user', 'trainer_user',
    'trainee_user', 'training_supervisor', 'data_entry', 'data_reviewer',
  ];
  const GIS_PRIORITY_ROLES = ['governor', 'data_entry', 'data_reviewer'];

  function roleLabel(r) {
    return window.APP_HELPERS?.roleLabel?.(r) || r;
  }

  function permLabel(p) {
    return window.APP_HELPERS?.permissionLabel?.(p) || p;
  }

  function permGroupLabel(prefix) {
    return window.APP_HELPERS?.permissionGroupLabel?.(prefix) || prefix;
  }

  function filterAssignableRoles(roles) {
    const canAssignExecutive = window.AppAuth.hasRole('general_director')
      || window.AppAuth.hasRole('super_admin');
    if (canAssignExecutive) return roles;
    return roles.filter((r) => !window.APP_HELPERS.isExecutiveRole(r));
  }

  function sortRolesForActor(roles) {
    const list = [...roles];
    if (!window.AppAuth.hasRole('project_services_manager')) return list;
    return list.sort((a, b) => {
      const ai = GIS_PRIORITY_ROLES.indexOf(a);
      const bi = GIS_PRIORITY_ROLES.indexOf(b);
      if (ai === -1 && bi === -1) return String(roleLabel(a)).localeCompare(String(roleLabel(b)), 'ar');
      if (ai === -1) return 1;
      if (bi === -1) return -1;
      return ai - bi;
    });
  }

  function roleHint(role) {
    if (role === 'governor') {
      return 'المحافظ مرتبط بمحافظة فقط — بدون فرع.';
    }
    if (role === 'data_entry') {
      return 'مدخل البيانات يحتاج محافظة + فرع ضمن نفس المحافظة.';
    }
    if (role === 'data_reviewer') {
      return 'مدقق البيانات يحتاج محافظة + فرع ضمن نفس المحافظة.';
    }
    if (BRANCH_REQUIRED_ROLES.includes(role)) {
      return 'هذا الدور يحتاج تحديد المحافظة والفرع.';
    }
    return '';
  }

  async function api(method, url, body) {
    const r = await fetch(url, {
      method, headers:{ 'Authorization':`Bearer ${token()}`, 'Accept':'application/json', 'Content-Type':'application/json' },
      body: body ? JSON.stringify(body) : undefined,
    });
    return { ok: r.ok, json: await r.json(), status: r.status };
  }

  async function loadGeo() {
    const [govRes, branchRes] = await Promise.all([
      api('GET', `${base}/governorates`),
      api('GET', `${base}/branches?lite=1`),
    ]);
    governorates = govRes.ok ? (govRes.json.data || []) : [];
    branches = branchRes.ok ? (branchRes.json.data || []) : [];
    const govSel = document.getElementById('cn-governorate');
    if (govSel) {
      govSel.innerHTML = '<option value="">-- اختر المحافظة --</option>' +
        governorates.map((g) => `<option value="${g.id}">${window.APP_HELPERS.e(g.name_ar || g.name)}</option>`).join('');
    }
  }

  function filterBranches() {
    const govId = Number(document.getElementById('cn-governorate')?.value || 0);
    const branchSel = document.getElementById('cn-branch');
    if (!branchSel) return;
    const filtered = branches.filter((b) => !govId || Number(b.governorate_id) === govId);
    branchSel.innerHTML = '<option value="">-- اختر الفرع --</option>' +
      filtered.map((b) => `<option value="${b.id}">${window.APP_HELPERS.e(b.name)}</option>`).join('');
  }

  function toggleBranchScope() {
    const role = document.getElementById('cn-role')?.value || '';
    const scopeRow = document.getElementById('cn-scope-row');
    const branchWrap = document.getElementById('cn-branch-wrap');
    const hint = document.getElementById('cn-role-hint');
    const needsGovOnly = GOV_ONLY_ROLES.includes(role);
    const needsBranch = BRANCH_REQUIRED_ROLES.includes(role);
    const showScope = needsGovOnly || needsBranch;

    if (scopeRow) scopeRow.style.display = showScope ? 'grid' : 'none';
    if (branchWrap) branchWrap.style.display = needsBranch ? '' : 'none';
    if (hint) hint.textContent = roleHint(role);
    if (showScope) filterBranches();
  }

  // ─── load delegatable (what I can grant) ───
  async function loadDelegatable() {
    const { ok, json } = await api('GET', `${base}/admin/my-delegatable`);
    if (ok) delegatable = json.data ?? { roles:[], permissions:[] };

    document.getElementById('kpiPerms').textContent = delegatable.permissions.length;

    const roles = sortRolesForActor(filterAssignableRoles(delegatable.roles || []));
    const roleOpts = roles.map((r) =>
      `<option value="${window.APP_HELPERS.e(r)}">${window.APP_HELPERS.e(roleLabel(r))}</option>`).join('');
    document.getElementById('cn-role').innerHTML   = '<option value="">-- اختر الدور --</option>' + roleOpts;
    document.getElementById('mn-add-role').innerHTML = '<option value="">-- اختر --</option>' + roleOpts;

    renderPermCheckboxes('cn-perms-wrap', delegatable.permissions, []);
  }

  // ─── load children ───
  window.loadChildren = async function() {
    const search   = document.getElementById('fSearch').value.trim();
    const isActive = document.getElementById('fStatus').value;
    let url = `${base}/admin/my-children`;
    const params = [];
    if (search)   params.push(`search=${encodeURIComponent(search)}`);
    if (isActive !== '') params.push(`is_active=${isActive}`);
    if (params.length) url += '?' + params.join('&');

    const { ok, json } = await api('GET', url);
    childrenData = ok ? (json.data ?? []) : [];

    document.getElementById('kpiTotal').textContent    = childrenData.length;
    document.getElementById('kpiActive').textContent   = childrenData.filter(u => u.is_active).length;
    document.getElementById('kpiInactive').textContent = childrenData.filter(u => !u.is_active).length;

    renderTree(childrenData);
  };

  function renderTree(users, parentId = null, level = 1) {
    if (level === 1) {
      const el = document.getElementById('treeWrap');
      const top = users.filter(u => !u.parent_user_id || !users.find(p => p.id === u.parent_user_id));
      if (!top.length) {
        el.innerHTML = `<div class="empty-box"><i class="bi bi-people"></i>لا يوجد مستخدمون بعد.<br><small>أضف أول مستخدم بالزر أعلاه</small></div>`;
        return;
      }
      el.innerHTML = buildNodes(users, null, level);
    }
  }

  function buildNodes(all, parentId, level) {
    const nodes = parentId === null
      ? all
      : all.filter(u => u.parent_user_id === parentId);

    return nodes.map(u => {
      const initials = (u.name||'U').split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
      const roles    = (u.roles||[]).map(r => `<span class="badge-role"><i class="bi bi-shield-check"></i>${window.APP_HELPERS.e(roleLabel(r.name))}</span>`).join('');
      const perms    = (u.permissions||[]).map(p => `<span class="badge-perm">${window.APP_HELPERS.e(permLabel(p.name))}</span>`).join('');
      const childCount = (u.children||[]).length;
      const branchHint = u.branch?.name || u.branch_name
        ? `<div style="margin-top:4px;font-size:.76rem;color:var(--c-muted)"><i class="bi bi-building"></i> ${window.APP_HELPERS.e(u.branch?.name || u.branch_name)}</div>`
        : '';
      const govHint = u.governorate?.name_ar || u.governorate_name
        ? `<div style="margin-top:4px;font-size:.76rem;color:var(--c-muted)"><i class="bi bi-geo-alt-fill"></i> ${window.APP_HELPERS.e(u.governorate?.name_ar || u.governorate_name)}</div>`
        : '';

      return `<div class="tree-node level-${Math.min(level,3)}">
        <div class="tree-avatar">${initials}</div>
        <div class="tree-info">
          <div class="tree-name">${window.APP_HELPERS.e(u.name)}</div>
          <div class="tree-meta">${window.APP_HELPERS.e(u.email||'')} ${u.phone?'· '+window.APP_HELPERS.e(u.phone):''}</div>
          <div style="margin-top:5px">${roles}</div>
          ${govHint}${branchHint}
          ${perms ? `<div style="margin-top:4px">${perms}</div>` : ''}
          ${childCount ? `<div style="margin-top:4px;font-size:.76rem;color:var(--c-muted)"><i class="bi bi-diagram-2"></i> لديه ${childCount} ابن/أبناء</div>` : ''}
        </div>
        <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0">
          <div class="status-dot ${u.is_active?'active':'inactive'}" title="${u.is_active?'نشط':'موقوف'}"></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:5px;flex-shrink:0">
          <button class="btn-outline" onclick="openManage(${u.id})" title="إدارة"><i class="bi bi-gear-fill"></i></button>
          <button class="btn-danger"  onclick="toggleStatus(${u.id},${!u.is_active})" title="${u.is_active?'إيقاف':'تفعيل'}">
            <i class="bi bi-${u.is_active?'pause-fill':'play-fill'}"></i>
          </button>
        </div>
      </div>`;
    }).join('');
  }

  let geoLoaded = false;
  let delegatableLoaded = false;

  async function ensureGeo() {
    if (geoLoaded) return;
    await loadGeo();
    geoLoaded = true;
  }

  async function ensureDelegatable() {
    if (delegatableLoaded) return;
    await loadDelegatable();
    delegatableLoaded = true;
  }

  // ─── Create modal ───
  window.openCreateModal = async function() {
    ['createSuccess','createError'].forEach(id => { document.getElementById(id).style.display='none'; });
    ['cn-name','cn-email','cn-pass','cn-phone'].forEach(id => { document.getElementById(id).value=''; });
    document.getElementById('cn-role').value = '';
    document.getElementById('createModal').classList.add('open');
    await Promise.all([ensureGeo(), ensureDelegatable()]);
    document.getElementById('cn-governorate').value = '';
    filterBranches();
    toggleBranchScope();
    renderPermCheckboxes('cn-perms-wrap', delegatable.permissions, []);
  };
  window.closeCreate = () => document.getElementById('createModal').classList.remove('open');

  window.doCreate = async function() {
    const name  = document.getElementById('cn-name').value.trim();
    const email = document.getElementById('cn-email').value.trim();
    const pass  = document.getElementById('cn-pass').value;
    const phone = document.getElementById('cn-phone').value.trim();
    const role  = document.getElementById('cn-role').value;
    if (!name||!email||!pass||!role) { showMsg('createError','يرجى تعبئة الحقول المطلوبة'); return; }

    if (window.APP_HELPERS.isExecutiveRole(role)) {
      showMsg('createError','لا يمكنك منح دور المدير العام أو نائب المدير العام — صلاحياتهم أعلى من مدير المنصة.');
      return;
    }

    const needsGovOnly = GOV_ONLY_ROLES.includes(role);
    const needsBranch = BRANCH_REQUIRED_ROLES.includes(role);
    const governorateId = document.getElementById('cn-governorate').value;
    const branchId = document.getElementById('cn-branch').value;

    if (needsGovOnly && !governorateId) {
      showMsg('createError','يرجى اختيار المحافظة للمحافظ.');
      return;
    }
    if (needsBranch && (!governorateId || !branchId)) {
      showMsg('createError','يرجى اختيار المحافظة والفرع لهذا الدور.');
      return;
    }

    const perms = getChecked('cn-perms-wrap');
    const payload = {
      name,
      email,
      password: pass,
      password_confirmation: pass,
      phone: phone || null,
      role,
      permissions: perms,
    };
    if (needsGovOnly || needsBranch) {
      payload.governorate_id = Number(governorateId);
    }
    if (needsBranch) {
      payload.branch_id = Number(branchId);
    }

    const { ok, json } = await api('POST', `${base}/admin/users`, payload);
    if (!ok) { showMsg('createError', extractError(json)); return; }
    showMsg('createSuccess','✓ تم إنشاء المستخدم بنجاح');
    setTimeout(() => { closeCreate(); loadChildren(); }, 1200);
  };

  // ─── Manage modal ───
  let manageUser = null;

  window.openManage = async function(id) {
    currentChildId = id;
    const u = childrenData.find(x => x.id === id);
    if (!u) return;
    manageUser = u;

    document.getElementById('manageUserName').textContent = u.name;
    ['manageSuccess','manageError'].forEach(id => { document.getElementById(id).style.display='none'; });
    document.getElementById('manageModal').classList.add('open');

    await ensureDelegatable();

    renderCurrentRoles(u.roles||[]);

    const currentPerms = (u.permissions||[]).map(p => p.name || p);
    renderPermCheckboxes('mn-perms-wrap', delegatable.permissions, currentPerms);

    document.getElementById('mn-name').value  = u.name;
    document.getElementById('mn-phone').value = u.phone||'';
    document.getElementById('mn-active').checked = !!u.is_active;
    document.getElementById('mn-pass').value = '';
    document.getElementById('passBtn').style.display = 'none';
    document.getElementById('mn-pass').oninput = () => {
      document.getElementById('passBtn').style.display = document.getElementById('mn-pass').value ? 'inline-flex' : 'none';
    };

    switchManageTab('roles');
  };

  window.closeManage = () => document.getElementById('manageModal').classList.remove('open');

  function renderCurrentRoles(roles) {
    document.getElementById('mn-current-roles').innerHTML = roles.map(r =>
      `<span class="badge-role" style="background:#fef3c7;color:#92400e">
        ${window.APP_HELPERS.e(roleLabel(r.name))}
        <i class="bi bi-x-circle-fill" style="cursor:pointer;margin-right:4px" onclick="doRemoveRole('${r.name}')"></i>
      </span>`
    ).join('') || '<span style="color:var(--c-muted);font-size:.83rem">لا يوجد أدوار</span>';
  }

  window.doAddRole = async function() {
    const role = document.getElementById('mn-add-role').value;
    if (!role) return;
    if (window.APP_HELPERS.isExecutiveRole(role)) {
      showMsg('manageError','لا يمكنك منح دور المدير العام أو نائب المدير العام.');
      return;
    }
    const { ok, json } = await api('POST', `${base}/admin/users/${currentChildId}/roles`, { role });
    if (!ok) { showMsg('manageError', extractError(json)); return; }
    manageUser = json.data;
    showMsg('manageSuccess','✓ تم إضافة الدور');
    renderCurrentRoles(json.data?.roles||[]);
    refreshChild(json.data);
  };

  window.doRemoveRole = async function(role) {
    const { ok, json } = await api('DELETE', `${base}/admin/users/${currentChildId}/roles/${encodeURIComponent(role)}`);
    if (!ok) { showMsg('manageError', extractError(json)); return; }
    showMsg('manageSuccess','✓ تم إزالة الدور');
    renderCurrentRoles(json.data?.roles||[]);
    refreshChild(json.data);
  };

  window.doSyncPerms = async function() {
    const perms = getChecked('mn-perms-wrap');
    const { ok, json } = await api('POST', `${base}/admin/users/${currentChildId}/permissions/sync`, { permissions: perms });
    if (!ok) { showMsg('manageError', extractError(json)); return; }
    showMsg('manageSuccess','✓ تم حفظ الصلاحيات');
    refreshChild(json.data);
  };

  window.doSaveInfo = async function() {
    const name    = document.getElementById('mn-name').value.trim();
    const phone   = document.getElementById('mn-phone').value.trim();
    const isActive = document.getElementById('mn-active').checked;

    const { ok, json } = await api('PUT', `${base}/admin/users/${currentChildId}`, { name, phone: phone||null });
    if (!ok) { showMsg('manageError', extractError(json)); return; }

    const { ok:ok2, json:json2 } = await api('PATCH', `${base}/admin/users/${currentChildId}/status`, { is_active: isActive });
    if (!ok2) { showMsg('manageError', extractError(json2)); return; }

    showMsg('manageSuccess','✓ تم حفظ البيانات');
    refreshChild(json2.data);
  };

  window.doChangePass = async function() {
    const pass = document.getElementById('mn-pass').value;
    if (!pass) return;
    const { ok, json } = await api('POST', `${base}/admin/users/${currentChildId}/change-password`, { password: pass, password_confirmation: pass });
    if (!ok) { showMsg('manageError', extractError(json)); return; }
    showMsg('manageSuccess','✓ تم تغيير كلمة المرور');
    document.getElementById('mn-pass').value = '';
    document.getElementById('passBtn').style.display = 'none';
  };

  window.toggleStatus = async function(id, newStatus) {
    const { ok, json } = await api('PATCH', `${base}/admin/users/${id}/status`, { is_active: newStatus });
    if (!ok) { alert(extractError(json)); return; }
    loadChildren();
  };

  // ─── Helpers ───
  function renderPermCheckboxes(containerId, allPerms, checked) {
    const el = document.getElementById(containerId);
    if (!allPerms.length) { el.innerHTML = '<div style="color:var(--c-muted);font-size:.83rem">لا توجد صلاحيات يمكنك منحها</div>'; return; }

    const groups = {};
    allPerms.forEach(p => {
      const prefix = p.includes('.') ? p.split('.')[0] : p.split('_')[0];
      if (!groups[prefix]) groups[prefix] = [];
      groups[prefix].push(p);
    });

    el.innerHTML = Object.entries(groups).map(([prefix, perms]) => `
      <div class="perm-section">
        <div class="perm-section-title">${window.APP_HELPERS.e(permGroupLabel(prefix))}</div>
        <div class="perm-checkboxes">
          ${perms.map(p => `<label class="perm-check">
            <input type="checkbox" name="perm" value="${window.APP_HELPERS.e(p)}" ${checked.includes(p)?'checked':''}>
            <span>${window.APP_HELPERS.e(permLabel(p))}</span>
          </label>`).join('')}
        </div>
      </div>`).join('');
  }

  function getChecked(containerId) {
    return [...document.querySelectorAll(`#${containerId} input[type=checkbox]:checked`)]
      .map(el => el.value);
  }

  function refreshChild(data) {
    if (!data) return;
    const idx = childrenData.findIndex(u => u.id === data.id);
    if (idx !== -1) childrenData[idx] = data;
    else childrenData.push(data);
    renderTree(childrenData);
  }

  window.switchManageTab = function(tab) {
    ['roles','perms','info'].forEach(t => {
      document.getElementById('mt-'+t).classList.toggle('hidden', t!==tab);
      const btn = document.getElementById('mtab-'+t);
      btn.style.borderColor  = t===tab ? 'var(--c-primary)' : 'var(--c-border)';
      btn.style.color        = t===tab ? 'var(--c-primary)' : 'var(--c-text)';
      btn.style.background   = t===tab ? 'var(--c-soft)'    : '#fff';
    });
  };

  function showMsg(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg; el.style.display = 'block';
    setTimeout(() => { el.style.display='none'; }, 4000);
  }

  function extractError(json) {
    if (json?.errors) return Object.values(json.errors).flat()[0];
    return json?.message || 'حدث خطأ';
  }

  // ─── Init: الشجرة أولاً — باقي البيانات عند فتح المودال ───
  document.getElementById('cn-role')?.addEventListener('change', toggleBranchScope);
  document.getElementById('cn-governorate')?.addEventListener('change', filterBranches);

  await loadChildren();

  document.getElementById('fSearch').addEventListener('keyup', e => { if (e.key==='Enter') loadChildren(); });
});
</script>
</body>
</html>
