<?php
$basePath   = '../../';
$pageTitle  = 'تعديل مكتب استشاري';
$activePage = 'consulting-office-edit';
$officeId   = isset($_GET['id']) ? (int) $_GET['id'] : 0;
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../../includes/layout/app-shell-styles.php'; ?>
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    .form-card { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:24px; box-shadow:var(--c-shadow); max-width:860px; margin:0 auto 18px; }
    .section-title { font-weight:800; font-size:1rem; margin:0 0 16px; display:flex; align-items:center; gap:8px; }
    .section-title i { color:var(--c-primary); }
    .form-label { font-size:.85rem; font-weight:700; color:var(--c-muted); margin-bottom:5px; display:block; }
    .form-control { width:100%; border:1px solid var(--c-border); border-radius:12px; padding:10px 14px; font-size:.9rem; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .spec-checkbox { display:flex; align-items:center; gap:8px; padding:8px 12px; border:1px solid var(--c-border); border-radius:10px; cursor:pointer; font-size:.87rem; font-weight:600; }
    .btn-save { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:14px; padding:12px 28px; font-weight:800; cursor:pointer; }
    .btn-del { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; border-radius:14px; padding:12px 22px; font-weight:800; cursor:pointer; }
    .alert-box { display:none; border-radius:12px; padding:12px 16px; font-weight:700; margin-bottom:14px; }
    .alert-danger { background:#fee2e2; color:#dc2626; }
    .alert-ok { background:#dcfce7; color:#166534; }
    @media (max-width:640px){ .form-row{ grid-template-columns:1fr; } }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>
<div class="container-fluid px-3 py-3">
  <div style="max-width:900px;margin:auto">
    <a href="consulting-offices-list.php" style="color:var(--c-primary);font-weight:700;text-decoration:none;font-size:.9rem">
      <i class="bi bi-arrow-right"></i> العودة للمكاتب
    </a>
    <h1 style="font-size:1.45rem;font-weight:800;margin:12px 0 6px">تعديل مكتب استشاري</h1>
    <p style="color:var(--c-muted);margin:0 0 18px" id="subTitle">جاري التحميل...</p>
    <div id="errorBox" class="alert-box alert-danger"></div>
    <div id="okBox" class="alert-box alert-ok"></div>

    <form id="editForm" class="form-card" style="display:none">
      <div class="section-title"><i class="bi bi-buildings-fill"></i> بيانات المكتب</div>
      <div class="form-row">
        <div>
          <label class="form-label">اسم المكتب *</label>
          <input class="form-control" name="name" required>
        </div>
        <div>
          <label class="form-label">المحافظة</label>
          <select class="form-control" name="governorate_id" id="govSelect"><option value="">—</option></select>
        </div>
      </div>
      <div class="form-row" style="margin-top:12px">
        <div>
          <label class="form-label">الحالة</label>
          <select class="form-control" name="status">
            <option value="pending">بانتظار الاعتماد</option>
            <option value="active">نشط</option>
            <option value="suspended">موقوف</option>
            <option value="revoked">ملغى</option>
          </select>
        </div>
        <div>
          <label class="form-label">رقم الترخيص</label>
          <input class="form-control" name="license_number">
        </div>
      </div>
      <div class="form-row" style="margin-top:12px">
        <div>
          <label class="form-label">الهاتف</label>
          <input class="form-control" name="phone">
        </div>
        <div>
          <label class="form-label">البريد</label>
          <input class="form-control" type="email" name="email">
        </div>
      </div>
      <div class="form-row" style="margin-top:12px">
        <div>
          <label class="form-label">الموقع</label>
          <input class="form-control" type="url" name="website" placeholder="https://">
        </div>
        <div>
          <label class="form-label">العنوان</label>
          <input class="form-control" name="address">
        </div>
      </div>
      <div style="margin-top:12px">
        <label class="form-label">نبذة</label>
        <textarea class="form-control" name="bio" rows="3"></textarea>
      </div>
      <div style="margin-top:12px">
        <label class="form-label">ملاحظات</label>
        <textarea class="form-control" name="notes" rows="2"></textarea>
      </div>
      <div style="margin-top:16px">
        <div class="section-title" style="margin-bottom:10px"><i class="bi bi-patch-check-fill"></i> التخصصات</div>
        <div id="specList" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:6px"></div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px">
        <button type="submit" class="btn-save" id="saveBtn"><i class="bi bi-check2-circle"></i> حفظ التعديلات</button>
        <button type="button" class="btn-del" id="deleteBtn"><i class="bi bi-trash"></i> حذف المكتب</button>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const id = <?php echo (int) $officeId; ?>;
  if (!id) { location.href = 'consulting-offices-list.php'; return; }

  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyRoles: window.AppPermissions.CONSULTING_OFFICE_MANAGE_ROLES,
  });
  if (!ok) return;

  const base = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const form = document.getElementById('editForm');
  const showErr = (m) => { const el=document.getElementById('errorBox'); el.style.display='block'; el.textContent=m; };
  const showOk = (m) => { const el=document.getElementById('okBox'); el.style.display='block'; el.textContent=m; };

  try {
    const [govJson, cats, officeJson] = await Promise.all([
      fetch(`${base}/governorates`, { headers:{ Authorization:`Bearer ${token()}`, Accept:'application/json' } }).then(r=>r.json()),
      fetch(`${base}/consulting/categories`, { headers:{ Authorization:`Bearer ${token()}`, Accept:'application/json' } }).then(r=>r.json()),
      fetch(`${base}/consulting/offices/${id}`, { headers:{ Authorization:`Bearer ${token()}`, Accept:'application/json' } }).then(r=>r.json()),
    ]);
    const office = officeJson.data || officeJson;
    document.getElementById('subTitle').textContent = (office.code || '') + ' — ' + (office.name || '');

    const govs = govJson.data || govJson;
    const govSel = document.getElementById('govSelect');
    (Array.isArray(govs)?govs:[]).forEach(g => {
      const o=document.createElement('option'); o.value=g.id; o.textContent=g.name_ar||g.name; govSel.appendChild(o);
    });

    const selectedSpecs = new Set((office.specializations||[]).map(s => s.category_code || s));
    document.getElementById('specList').innerHTML = (Array.isArray(cats)?cats:[]).map(c => `
      <label class="spec-checkbox">
        <input type="checkbox" name="specs" value="${c.code}" ${selectedSpecs.has(c.code)?'checked':''}>
        <span>${c.name_ar}</span>
      </label>`).join('');

    ['name','governorate_id','status','license_number','phone','email','website','address','bio','notes']
      .forEach(k => { if (form.elements[k] && office[k] != null) form.elements[k].value = office[k]; });
    form.style.display = 'block';
  } catch {
    showErr('تعذّر تحميل المكتب.');
    return;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    const fd = new FormData(form);
    const body = Object.fromEntries(fd.entries());
    delete body.specs;
    body.specializations = [...document.querySelectorAll('input[name="specs"]:checked')].map(el => el.value);
    Object.keys(body).forEach(k => { if (body[k]==='') delete body[k]; });
    try {
      const r = await fetch(`${base}/consulting/offices/${id}`, {
        method:'PUT',
        headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json', Accept:'application/json' },
        body: JSON.stringify(body),
      });
      const json = await r.json();
      if (!r.ok) {
        showErr(json.message || 'فشل الحفظ');
        btn.disabled = false;
        return;
      }
      showOk('تم حفظ التعديلات.');
      setTimeout(() => location.href = 'consulting-offices-list.php', 700);
    } catch {
      showErr('خطأ في الاتصال.');
      btn.disabled = false;
    }
  });

  document.getElementById('deleteBtn').addEventListener('click', async () => {
    if (!confirm('هل تريد حذف هذا المكتب؟')) return;
    try {
      const r = await fetch(`${base}/consulting/offices/${id}`, {
        method:'DELETE',
        headers:{ Authorization:`Bearer ${token()}`, Accept:'application/json' },
      });
      const json = await r.json().catch(()=>({}));
      if (!r.ok) { showErr(json.message || 'فشل الحذف'); return; }
      location.href = 'consulting-offices-list.php';
    } catch { showErr('خطأ في الاتصال.'); }
  });
});
</script>
</body>
</html>
