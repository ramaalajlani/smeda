<?php
$basePath  = '../../';
$pageTitle = 'المكاتب الاستشارية';
$activePage= 'consulting-offices-list';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../../includes/layout/app-shell-styles.php'; ?>
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .page-hero { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border-radius:22px; padding:26px 28px; margin-bottom:22px; position:relative; overflow:hidden; }
    .page-hero::before { content:""; position:absolute; width:200px; height:200px; border-radius:50%; top:-100px; left:-70px; background:radial-gradient(circle,rgba(255,255,255,.16),transparent 70%); }
    .page-hero-title { font-size:1.5rem; font-weight:800; margin:0 0 6px; }
    .page-hero i.bg-icon { position:absolute; right:24px; top:50%; transform:translateY(-50%); font-size:4rem; opacity:.14; }
    .filter-bar { background:#fff; border:1px solid var(--c-border); border-radius:18px; padding:16px 20px; margin-bottom:18px; box-shadow:var(--c-shadow); display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
    .filter-bar select, .filter-bar input { border:1px solid var(--c-border); border-radius:12px; padding:9px 14px; font-size:.88rem; background:#fff; color:var(--c-text); font-weight:600; min-width:160px; }
    .office-card { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:20px; box-shadow:var(--c-shadow); margin-bottom:14px; transition:all .2s; }
    .office-card:hover { box-shadow:0 18px 36px rgba(15,79,71,.12); }
    .office-name { font-weight:800; font-size:1.05rem; color:var(--c-text); margin:0 0 6px; cursor:pointer; }
    .office-meta { display:flex; flex-wrap:wrap; gap:14px; color:var(--c-muted); font-size:.84rem; font-weight:600; margin-bottom:12px; }
    .office-meta i { color:var(--c-primary); }
    .rating-stars { color:#f59e0b; font-size:1rem; font-weight:700; }
    .spec-tag { background:var(--c-soft); color:var(--c-primary); padding:3px 10px; border-radius:8px; font-size:.77rem; font-weight:700; display:inline-block; margin:2px; }
    .status-pill { padding:4px 12px; border-radius:20px; font-size:.77rem; font-weight:700; }
    .status-active   { background:#dcfce7; color:#166534; }
    .status-pending  { background:#fef3c7; color:#92400e; }
    .status-suspended{ background:#fee2e2; color:#dc2626; }
    .btn-new { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:14px; padding:10px 20px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; }
    .btn-act { border:none; border-radius:10px; padding:7px 12px; font-size:.8rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; }
    .btn-edit { background:var(--c-soft); color:var(--c-primary); }
    .btn-del { background:#fee2e2; color:#dc2626; }
    .empty-box { text-align:center; padding:60px 20px; color:var(--c-muted); }
    .empty-box i { font-size:3.5rem; color:rgba(23,148,123,.2); margin-bottom:14px; display:block; }
    .stat-badge { background:var(--c-soft); color:var(--c-primary); border-radius:10px; padding:6px 12px; font-size:.82rem; font-weight:700; text-align:center; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>
<div class="container-fluid px-3 py-3" style="max-width:1100px;margin:auto">
  <div class="page-hero">
    <i class="bi bi-buildings-fill bg-icon"></i>
    <div class="page-hero-title">المكاتب الاستشارية المعتمدة</div>
    <p style="margin:0;opacity:.9;font-size:.92rem">إدارة كاملة للمكاتب: إضافة، تعديل، حذف وتفعيل</p>
  </div>

  <div class="filter-bar">
    <div>
      <label style="font-size:.8rem;font-weight:700;display:block;margin-bottom:4px;color:var(--c-muted)">الحالة</label>
      <select id="filterStatus">
        <option value="active">مكاتب نشطة</option>
        <option value="">كل المكاتب</option>
        <option value="pending">بانتظار الاعتماد</option>
        <option value="suspended">موقوفة</option>
      </select>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;display:block;margin-bottom:4px;color:var(--c-muted)">التخصص</label>
      <select id="filterCategory">
        <option value="">كل التخصصات</option>
      </select>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;display:block;margin-bottom:4px;color:var(--c-muted)">المحافظة</label>
      <select id="filterGov">
        <option value="">كل المحافظات</option>
      </select>
    </div>
    <div style="margin-top:auto" id="adminActions"></div>
  </div>

  <div id="loadingBox" style="text-align:center;padding:40px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2rem;display:block;margin-bottom:10px"></i>جاري التحميل...
  </div>
  <div id="officesContainer"></div>
  <div id="paginationBox" class="d-flex justify-content-center gap-2 mt-3"></div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: false });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const canManageOffice = window.AppAuth.hasAnyRole?.(window.AppPermissions.CONSULTING_OFFICE_MANAGE_ROLES);

  if (canManageOffice) {
    document.getElementById('adminActions').innerHTML =
      `<a href="consulting-office-create.php" class="btn-new"><i class="bi bi-plus-circle-fill"></i> تسجيل مكتب جديد</a>`;
  }

  fetch(`${base}/consulting/categories`, { headers:{ 'Authorization':`Bearer ${token()}`, Accept:'application/json' } })
    .then(r=>r.json()).then(cats=>{
      const sel = document.getElementById('filterCategory');
      (Array.isArray(cats)?cats:[]).forEach(c=>{ const o=document.createElement('option'); o.value=c.code; o.textContent=c.name_ar; sel.appendChild(o); });
    }).catch(()=>{});

  fetch(`${base}/governorates`, { headers:{ 'Authorization':`Bearer ${token()}`, Accept:'application/json' } })
    .then(r=>r.json()).then(data=>{
      const govs = data.data || data;
      const sel  = document.getElementById('filterGov');
      (Array.isArray(govs)?govs:[]).forEach(g=>{ const o=document.createElement('option'); o.value=g.id; o.textContent=g.name_ar||g.name; sel.appendChild(o); });
    }).catch(()=>{});

  window.__consDeleteOffice = async function(id) {
    if (!confirm('هل تريد حذف هذا المكتب؟')) return;
    try {
      const r = await fetch(`${base}/consulting/offices/${id}`, {
        method:'DELETE',
        headers:{ Authorization:`Bearer ${token()}`, Accept:'application/json' },
      });
      if (!r.ok) {
        const j = await r.json().catch(()=>({}));
        alert(j.message || 'فشل الحذف');
        return;
      }
      load(1);
    } catch { alert('خطأ في الاتصال'); }
  };

  async function load(page=1) {
    const status   = document.getElementById('filterStatus').value;
    const category = document.getElementById('filterCategory').value;
    const govId    = document.getElementById('filterGov').value;
    document.getElementById('loadingBox').style.display='block';
    document.getElementById('officesContainer').innerHTML='';

    const p = new URLSearchParams({ page, per_page:12 });
    if (status)   p.set('status', status);
    if (category) p.set('category_code', category);
    if (govId)    p.set('governorate_id', govId);

    try {
      const json  = await fetch(`${base}/consulting/offices?${p}`, { headers:{ 'Authorization':`Bearer ${token()}`, Accept:'application/json' } }).then(r=>r.json());
      const items = json.data?.data || (Array.isArray(json.data) ? json.data : null) || json.data || json || [];
      const list  = Array.isArray(items) ? items : [];
      const meta  = json.data?.meta || json.meta || {};
      document.getElementById('loadingBox').style.display='none';

      if (!list.length) {
        document.getElementById('officesContainer').innerHTML=`<div class="empty-box"><i class="bi bi-buildings"></i>لا توجد مكاتب مسجلة بهذه المعايير</div>`;
        return;
      }

      document.getElementById('officesContainer').innerHTML = list.map(o=>`
        <div class="office-card">
          <div style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap">
            <div style="width:52px;height:52px;background:var(--c-soft);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="bi bi-buildings-fill" style="font-size:1.6rem;color:var(--c-primary)"></i>
            </div>
            <div style="flex:1">
              <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px">
                <div class="office-name" onclick="location.href='consulting-office-view.php?id=${o.id}'">${window.APP_HELPERS.e(o.name)}</div>
                <span class="status-pill status-${o.status}">${{active:'نشط',pending:'بانتظار اعتماد',suspended:'موقوف',revoked:'ملغى'}[o.status]||o.status}</span>
                ${canManageOffice ? `
                  <button type="button" class="btn-act btn-edit" onclick="location.href='consulting-office-edit.php?id=${o.id}'"><i class="bi bi-pencil-fill"></i> تعديل</button>
                  <button type="button" class="btn-act btn-del" onclick="window.__consDeleteOffice(${o.id})"><i class="bi bi-trash"></i> حذف</button>` : ''}
              </div>
              <div class="office-meta">
                <span><i class="bi bi-geo-alt"></i>${window.APP_HELPERS.e(o.governorate?.name_ar||'—')}</span>
                <span class="rating-stars">★ ${o.overall_rating ? Number(o.overall_rating).toFixed(1) : '—'}</span>
                <span><i class="bi bi-check-circle"></i>${o.total_requests_completed||0} طلب مكتمل</span>
                ${o.license_number ? `<span><i class="bi bi-card-checklist"></i>${o.license_number}</span>` : ''}
              </div>
              <div>${(o.specializations||[]).slice(0,5).map(s=>`<span class="spec-tag">${s.category_code||s}</span>`).join('')}</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;text-align:center;min-width:80px">
              <div class="stat-badge"><div style="font-size:1.1rem;font-weight:800">${o.overall_rating?Number(o.overall_rating).toFixed(1):'—'}</div><div style="font-size:.7rem">التقييم</div></div>
              <div class="stat-badge"><div style="font-size:1rem;font-weight:800">${o.on_time_rate?Math.round(o.on_time_rate)+'%':'—'}</div><div style="font-size:.7rem">الالتزام</div></div>
            </div>
          </div>
        </div>`).join('');

      const lastPage = meta.last_page||1;
      const pg=document.getElementById('paginationBox'); pg.innerHTML='';
      if (lastPage>1) for(let p2=1;p2<=lastPage;p2++){const b=document.createElement('button');b.textContent=p2;b.className='btn btn-sm '+(p2===page?'btn-success':'btn-outline-secondary');b.style.borderRadius='8px';b.addEventListener('click',()=>load(p2));pg.appendChild(b);}
    } catch {
      document.getElementById('loadingBox').innerHTML='<div class="text-danger fw-bold">تعذّر التحميل</div>';
    }
  }

  ['filterStatus','filterCategory','filterGov'].forEach(id=>document.getElementById(id).addEventListener('change',()=>load(1)));
  load();
});
</script>
</body>
</html>
