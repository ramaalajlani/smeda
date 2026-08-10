<?php
$basePath  = '../../';
$pageTitle = 'طلبات الاستشارة';
$activePage= 'consulting-requests-list';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../../includes/layout/app-shell-styles.php'; ?>
  <style>
    :root {
      --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4;
      --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280;
      --c-shadow:0 10px 28px rgba(15,79,71,.07);
    }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .page-hero { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border-radius:22px; padding:26px 28px; margin-bottom:22px; position:relative; overflow:hidden; }
    .page-hero::before { content:""; position:absolute; width:200px; height:200px; border-radius:50%; top:-100px; left:-70px; background:radial-gradient(circle,rgba(255,255,255,.16),transparent 70%); }
    .page-hero-title { font-size:1.5rem; font-weight:800; margin:0 0 6px; }
    .page-hero-sub { margin:0; opacity:.92; font-size:.92rem; }
    .page-hero i.bg-icon { position:absolute; right:24px; top:50%; transform:translateY(-50%); font-size:4rem; opacity:.14; }

    .filter-bar { background:#fff; border:1px solid var(--c-border); border-radius:18px; padding:16px 20px; margin-bottom:18px; box-shadow:var(--c-shadow); display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
    .filter-bar select, .filter-bar input { border:1px solid var(--c-border); border-radius:12px; padding:9px 14px; font-size:.88rem; background:#fff; color:var(--c-text); font-weight:600; }
    .btn-primary-custom { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:14px; padding:10px 20px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:opacity .2s; }
    .btn-primary-custom:hover { opacity:.92; color:#fff; }
    .btn-act { border:none; border-radius:10px; padding:7px 12px; font-size:.8rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; }
    .btn-edit { background:var(--c-soft); color:var(--c-primary); }
    .btn-del { background:#fee2e2; color:#dc2626; }

    .req-card { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:20px; box-shadow:var(--c-shadow); margin-bottom:14px; transition:box-shadow .2s; }
    .req-card:hover { box-shadow:0 18px 36px rgba(15,79,71,.11); }
    .req-card-head { display:flex; align-items:center; gap:12px; margin-bottom:12px; flex-wrap:wrap; }
    .req-code { font-size:.8rem; font-weight:700; background:var(--c-soft); color:var(--c-primary); padding:4px 10px; border-radius:8px; }
    .req-status { font-size:.78rem; font-weight:700; padding:4px 12px; border-radius:20px; }
    .status-draft    { background:#f3f4f6; color:#6b7280; }
    .status-submitted{ background:#dbeafe; color:#1d4ed8; }
    .status-needs_info{ background:#fef3c7; color:#92400e; }
    .status-awaiting_offers{ background:#e0e7ff; color:#4338ca; }
    .status-offer_submitted{ background:#f0fdf4; color:#166534; }
    .status-in_progress{ background:#ecfdf5; color:#065f46; }
    .status-report_submitted{ background:#f0f9ff; color:#0369a1; }
    .status-completed{ background:#dcfce7; color:#166534; }
    .status-rejected { background:#fee2e2; color:#dc2626; }
    .status-transferred_financing,.status-transferred_training,.status-transferred_incubation,.status-transferred_gis { background:#f3e8ff; color:#7c3aed; }

    .req-title { font-weight:800; font-size:1rem; color:var(--c-text); margin:0 0 4px; cursor:pointer; }
    .req-meta { display:flex; flex-wrap:wrap; gap:14px; color:var(--c-muted); font-size:.83rem; font-weight:600; }
    .req-meta i { color:var(--c-primary); }
    .empty-box { text-align:center; padding:60px 20px; color:var(--c-muted); }
    .empty-box i { font-size:3.5rem; color:rgba(23,148,123,.2); margin-bottom:14px; display:block; }
    @media (max-width:575px) { .filter-bar { flex-direction:column; } }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>
<div class="container-fluid px-3 py-3" style="max-width:1100px;margin:auto">

  <div class="page-hero">
    <i class="bi bi-headset bg-icon"></i>
    <div class="page-hero-title">طلبات الاستشارة</div>
    <p class="page-hero-sub">إدارة كاملة: إضافة، تعديل، حذف ومتابعة الحالة</p>
  </div>

  <div class="filter-bar">
    <div>
      <label style="font-size:.8rem;font-weight:700;display:block;margin-bottom:4px;color:var(--c-muted)">الحالة</label>
      <select id="filterStatus">
        <option value="">كل الحالات</option>
        <option value="draft">مسودة</option>
        <option value="submitted">مُرسَل</option>
        <option value="needs_info">بحاجة معلومات</option>
        <option value="awaiting_offers">بانتظار عروض</option>
        <option value="offer_submitted">عرض مقدم</option>
        <option value="in_progress">قيد التنفيذ</option>
        <option value="report_submitted">تقرير مرفوع</option>
        <option value="awaiting_authority_approval">بانتظار اعتماد الهيئة</option>
        <option value="completed">مكتمل</option>
        <option value="rejected">مرفوض</option>
      </select>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;display:block;margin-bottom:4px;color:var(--c-muted)">نوع الاستشارة</label>
      <select id="filterCategory">
        <option value="">كل الأنواع</option>
      </select>
    </div>
    <div style="margin-top:auto;display:flex;gap:8px;flex-wrap:wrap">
      <a href="consulting-request-create.php" class="btn-primary-custom" id="btnNewRequest">
        <i class="bi bi-plus-circle-fill"></i> طلب استشارة جديدة
      </a>
    </div>
  </div>

  <div id="loadingBox" style="text-align:center;padding:40px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2rem;display:block;margin-bottom:10px"></i>
    جاري التحميل...
  </div>

  <div id="requestsContainer"></div>
  <div id="paginationBox" class="d-flex justify-content-center gap-2 mt-3"></div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyRoles: window.AppPermissions.CONSULTING_REQUEST_ACCESS_ROLES,
  });
  if (!ok) return;

  const base   = window.APP_CONFIG.API_BASE_URL;
  const token  = () => window.AppAuth.getToken();
  const canManage = window.AppAuth.hasAnyRole?.(window.AppPermissions.CONSULTING_ADMIN_ROLES);
  const canCreate = window.AppAuth.hasAnyRole?.(window.AppPermissions.CONSULTING_REQUEST_CREATE_ROLES);
  if (!canCreate) document.getElementById('btnNewRequest')?.remove();

  const STATUS_LABELS = {
    draft:'مسودة', submitted:'مُرسَل', needs_info:'بحاجة معلومات',
    sorting:'قيد الفرز', awaiting_offers:'بانتظار عروض',
    offer_submitted:'عرض مقدم', awaiting_client_approval:'بانتظار موافقة العميل',
    in_progress:'قيد التنفيذ', report_submitted:'تقرير مرفوع',
    awaiting_authority_approval:'بانتظار اعتماد الهيئة',
    completed:'مكتمل', transferred_financing:'محوّل للتمويل',
    transferred_training:'محوّل للتدريب', transferred_incubation:'محوّل للحاضنات',
    transferred_gis:'محوّل إلى GIS', rejected:'مرفوض', cancelled:'ملغى',
  };

  try {
    const cats = await fetch(`${base}/consulting/categories`, {
      headers: { 'Authorization': `Bearer ${token()}`, 'Accept': 'application/json' }
    }).then(r => r.json());
    const sel = document.getElementById('filterCategory');
    (Array.isArray(cats)?cats:[]).forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.code; opt.textContent = c.name_ar;
      sel.appendChild(opt);
    });
  } catch {}

  async function deleteRequest(id) {
    if (!confirm('هل تريد حذف هذا الطلب؟')) return;
    try {
      const r = await fetch(`${base}/consulting/requests/${id}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token()}`, Accept: 'application/json' },
      });
      if (!r.ok) {
        const j = await r.json().catch(()=>({}));
        alert(j.message || 'فشل الحذف');
        return;
      }
      loadRequests();
    } catch { alert('خطأ في الاتصال'); }
  }
  window.__consDeleteRequest = deleteRequest;

  async function loadRequests(page = 1) {
    const status   = document.getElementById('filterStatus').value;
    const category = document.getElementById('filterCategory').value;

    document.getElementById('loadingBox').style.display = 'block';
    document.getElementById('requestsContainer').innerHTML = '';

    const params = new URLSearchParams({ page, per_page: 15 });
    if (status)   params.set('status', status);
    if (category) params.set('category_code', category);

    try {
      const res  = await fetch(`${base}/consulting/requests?${params}`, {
        headers: { 'Authorization': `Bearer ${token()}`, 'Accept': 'application/json' }
      });
      const json = await res.json();
      const data = json.data || json;
      const items = Array.isArray(data) ? data : (data.data || []);
      const meta  = json.meta || data.meta || json;

      document.getElementById('loadingBox').style.display = 'none';

      if (!items.length) {
        document.getElementById('requestsContainer').innerHTML = `
          <div class="empty-box">
            <i class="bi bi-inbox"></i>
            لا توجد طلبات بعد. <a href="consulting-request-create.php" style="color:var(--c-primary);font-weight:700">أنشئ طلبك الأول</a>
          </div>`;
        return;
      }

      document.getElementById('requestsContainer').innerHTML = items.map(req => `
        <div class="req-card">
          <div class="req-card-head">
            <span class="req-code">${req.request_code || '#' + req.id}</span>
            <span class="req-status status-${req.status}">${STATUS_LABELS[req.status] || req.status}</span>
            <span style="font-size:.8rem;color:var(--c-muted);margin-right:auto">${req.category_code || ''}</span>
            ${canManage ? `
              <button type="button" class="btn-act btn-edit" onclick="location.href='consulting-request-edit.php?id=${req.id}'">
                <i class="bi bi-pencil-fill"></i> تعديل
              </button>
              <button type="button" class="btn-act btn-del" onclick="window.__consDeleteRequest(${req.id})">
                <i class="bi bi-trash"></i> حذف
              </button>` : ''}
          </div>
          <div class="req-title" onclick="location.href='consulting-request-view.php?id=${req.id}'">${window.APP_HELPERS.e(req.title)}</div>
          <div class="req-meta">
            <span><i class="bi bi-geo-alt"></i> ${window.APP_HELPERS.e(req.governorate?.name_ar || '—')}</span>
            <span><i class="bi bi-calendar3"></i> ${req.submitted_at ? new Date(req.submitted_at).toLocaleDateString('ar-SY') : 'مسودة'}</span>
            ${req.budget_max ? `<span><i class="bi bi-cash-coin"></i> حتى ${Number(req.budget_max).toLocaleString('ar')} ل.س</span>` : ''}
          </div>
        </div>
      `).join('');

      const lastPage = meta.last_page || 1;
      const pg = document.getElementById('paginationBox');
      pg.innerHTML = '';
      if (lastPage > 1) {
        for (let p = 1; p <= lastPage; p++) {
          const btn = document.createElement('button');
          btn.textContent = p;
          btn.className = 'btn btn-sm ' + (p === page ? 'btn-success' : 'btn-outline-secondary');
          btn.style.borderRadius = '8px';
          btn.addEventListener('click', () => loadRequests(p));
          pg.appendChild(btn);
        }
      }
    } catch (e) {
      document.getElementById('loadingBox').innerHTML = '<div class="text-danger fw-bold">تعذّر تحميل الطلبات.</div>';
    }
  }

  document.getElementById('filterStatus').addEventListener('change',   () => loadRequests(1));
  document.getElementById('filterCategory').addEventListener('change', () => loadRequests(1));
  loadRequests();
});
</script>
</body>
</html>
