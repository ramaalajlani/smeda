<?php
$basePath  = '../../';
$pageTitle = 'تسجيل مكتب استشاري';
$activePage= 'consulting';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .form-card { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:24px; box-shadow:var(--c-shadow); margin-bottom:18px; max-width:800px; margin-inline:auto; }
    .form-card + .form-card { margin-top:0; }
    .section-title { font-weight:800; font-size:1rem; color:var(--c-text); margin:0 0 18px; display:flex; align-items:center; gap:8px; }
    .section-title i { color:var(--c-primary); }
    .form-group { margin-bottom:16px; }
    .form-label { font-size:.85rem; font-weight:700; color:var(--c-muted); margin-bottom:5px; display:block; }
    .form-control { width:100%; border:1px solid var(--c-border); border-radius:12px; padding:10px 14px; font-size:.9rem; color:var(--c-text); background:#fff; transition:border-color .2s; }
    .form-control:focus { outline:none; border-color:var(--c-accent); box-shadow:0 0 0 3px rgba(6,170,137,.1); }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .btn-submit { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:16px; padding:14px 32px; font-weight:800; font-size:1rem; cursor:pointer; display:inline-flex; align-items:center; gap:10px; transition:opacity .2s; }
    .btn-submit:hover { opacity:.92; }
    .spec-checkbox { display:flex; align-items:center; gap:8px; padding:8px 12px; border:1px solid var(--c-border); border-radius:10px; cursor:pointer; margin-bottom:6px; font-size:.87rem; font-weight:600; color:var(--c-text); transition:background .15s; }
    .spec-checkbox:hover { background:var(--c-soft); }
    .spec-checkbox input { accent-color:var(--c-primary); width:16px; height:16px; }
    .alert-danger { background:#fee2e2; color:#dc2626; border-radius:12px; padding:12px 16px; font-size:.88rem; font-weight:700; margin-bottom:14px; display:none; }
    @media (max-width:640px) { .form-row { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div style="max-width:820px;margin:auto;padding:24px 14px">
  <a href="consulting-offices-list.php" style="color:var(--c-primary);font-weight:700;text-decoration:none;font-size:.9rem;display:inline-block;margin-bottom:16px">
    <i class="bi bi-arrow-right"></i> العودة للمكاتب
  </a>

  <div style="margin-bottom:22px">
    <h1 style="font-size:1.5rem;font-weight:800;color:var(--c-text);margin:0 0 4px">تسجيل مكتب استشاري جديد</h1>
    <p style="color:var(--c-muted);font-size:.9rem;margin:0">أدخل بيانات المكتب الاستشاري لإضافته لقاعدة المكاتب المعتمدة</p>
  </div>

  <div id="errorBox" class="alert-danger"></div>

  <form id="officeForm">
    <!-- البيانات الأساسية -->
    <div class="form-card">
      <div class="section-title"><i class="bi bi-buildings-fill"></i> البيانات الأساسية</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">اسم المكتب *</label>
          <input type="text" name="name" class="form-control" required placeholder="مثال: مكتب النهضة للاستشارات">
        </div>
        <div class="form-group">
          <label class="form-label">المحافظة *</label>
          <select name="governorate_id" class="form-control" required>
            <option value="">-- اختر --</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">رقم الترخيص</label>
          <input type="text" name="license_number" class="form-control" placeholder="رقم الترخيص الرسمي">
        </div>
        <div class="form-group">
          <label class="form-label">تاريخ الترخيص</label>
          <input type="date" name="license_date" class="form-control">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">صلاحية الترخيص</label>
          <input type="date" name="license_expiry" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">تاريخ الاعتماد لدى الهيئة</label>
          <input type="date" name="accreditation_date" class="form-control">
        </div>
      </div>
    </div>

    <!-- بيانات التواصل -->
    <div class="form-card" style="margin-top:14px">
      <div class="section-title"><i class="bi bi-telephone-fill"></i> بيانات التواصل</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">رقم الهاتف</label>
          <input type="text" name="phone" class="form-control" placeholder="09xxxxxxxx">
        </div>
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني</label>
          <input type="email" name="email" class="form-control" placeholder="office@example.com">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الموقع الإلكتروني</label>
          <input type="url" name="website" class="form-control" placeholder="https://...">
        </div>
        <div class="form-group">
          <label class="form-label">العنوان</label>
          <input type="text" name="address" class="form-control" placeholder="حلب - شارع...">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">نبذة عن المكتب</label>
        <textarea name="bio" class="form-control" rows="3" placeholder="وصف مختصر لنشاط المكتب وخبراته..."></textarea>
      </div>
    </div>

    <!-- التخصصات -->
    <div class="form-card" style="margin-top:14px">
      <div class="section-title"><i class="bi bi-patch-check-fill"></i> التخصصات</div>
      <p style="font-size:.85rem;color:var(--c-muted);margin:0 0 14px">اختر تخصصات المكتب (يمكن اختيار أكثر من تخصص)</p>
      <div id="specList" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:6px">
        <div style="color:var(--c-muted);font-size:.88rem">جاري التحميل...</div>
      </div>
    </div>

    <!-- ملاحظات -->
    <div class="form-card" style="margin-top:14px">
      <div class="section-title"><i class="bi bi-chat-text-fill"></i> ملاحظات إدارية</div>
      <div class="form-group">
        <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات داخلية (لا تظهر للمكتب)"></textarea>
      </div>
    </div>

    <div style="text-align:center;margin-top:22px">
      <button type="submit" class="btn-submit" id="submitBtn">
        <i class="bi bi-check-circle-fill"></i> تسجيل المكتب
      </button>
    </div>
  </form>
</div>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyRoles: window.AppPermissions.CONSULTING_OFFICE_MANAGE_ROLES,
  });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();

  // Load governorates
  fetch(`${base}/governorates`, { headers:{ 'Authorization':`Bearer ${token()}` } })
    .then(r=>r.json()).then(data=>{
      const govs = data.data || data;
      const sel  = document.querySelector('select[name="governorate_id"]');
      (Array.isArray(govs)?govs:[]).forEach(g=>{
        const o=document.createElement('option'); o.value=g.id; o.textContent=g.name_ar||g.name; sel.appendChild(o);
      });
    }).catch(()=>{});

  // Load categories for specializations
  fetch(`${base}/consulting/categories`, { headers:{ 'Authorization':`Bearer ${token()}` } })
    .then(r=>r.json()).then(cats=>{
      document.getElementById('specList').innerHTML = cats.map(c=>`
        <label class="spec-checkbox">
          <input type="checkbox" name="specs" value="${c.code}">
          <span>${c.name_ar}</span>
        </label>`).join('');
    }).catch(()=>{ document.getElementById('specList').innerHTML='<span style="color:var(--c-muted);font-size:.85rem">تعذّر تحميل التخصصات</span>'; });

  document.getElementById('officeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري الحفظ...';

    const fd   = new FormData(e.target);
    const data = Object.fromEntries(fd.entries());

    // جمع التخصصات
    const specs = [...document.querySelectorAll('input[name="specs"]:checked')].map(el=>el.value);
    delete data.specs;

    const body = { ...data, specializations: specs };

    // إزالة الحقول الفارغة
    Object.keys(body).forEach(k=>{ if(body[k]==='') delete body[k]; });

    try {
      const r = await fetch(`${base}/consulting/offices`, {
        method:'POST',
        headers:{ 'Authorization':`Bearer ${token()}`, 'Content-Type':'application/json', 'Accept':'application/json' },
        body: JSON.stringify(body),
      });
      const json = await r.json();

      if (!r.ok) {
        const errs = json.errors ? Object.values(json.errors).flat().join(' | ') : (json.message||'حدث خطأ');
        const box = document.getElementById('errorBox');
        box.style.display='block'; box.textContent=errs;
        btn.disabled=false; btn.innerHTML='<i class="bi bi-check-circle-fill"></i> تسجيل المكتب';
        return;
      }

      alert('تم تسجيل المكتب بنجاح!');
      location.href = 'consulting-offices-list.php';
    } catch {
      document.getElementById('errorBox').style.display='block';
      document.getElementById('errorBox').textContent='حدث خطأ في الاتصال بالخادم.';
      btn.disabled=false; btn.innerHTML='<i class="bi bi-check-circle-fill"></i> تسجيل المكتب';
    }
  });
});
</script>
</body>
</html>
