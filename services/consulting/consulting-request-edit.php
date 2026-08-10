<?php
$basePath   = '../../';
$pageTitle  = 'تعديل طلب استشارة';
$activePage = 'consulting-request-edit';
$requestId  = isset($_GET['id']) ? (int) $_GET['id'] : 0;
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
    <a href="consulting-requests-list.php" style="color:var(--c-primary);font-weight:700;text-decoration:none;font-size:.9rem">
      <i class="bi bi-arrow-right"></i> العودة للطلبات
    </a>
    <h1 style="font-size:1.45rem;font-weight:800;margin:12px 0 6px">تعديل طلب استشارة</h1>
    <p style="color:var(--c-muted);margin:0 0 18px" id="subTitle">جاري التحميل...</p>

    <div id="errorBox" class="alert-box alert-danger"></div>
    <div id="okBox" class="alert-box alert-ok"></div>

    <form id="editForm" class="form-card" style="display:none">
      <div class="section-title"><i class="bi bi-pencil-square"></i> بيانات الطلب</div>
      <div class="form-row">
        <div>
          <label class="form-label">العنوان *</label>
          <input class="form-control" name="title" required>
        </div>
        <div>
          <label class="form-label">نوع الاستشارة</label>
          <select class="form-control" name="category_code" id="categorySelect"></select>
        </div>
      </div>
      <div class="form-row" style="margin-top:12px">
        <div>
          <label class="form-label">نوع الطلب</label>
          <select class="form-control" name="request_type">
            <option value="new_project">مشروع جديد</option>
            <option value="existing">قائم</option>
            <option value="financing">تمويلي</option>
            <option value="classification">تصنيف</option>
          </select>
        </div>
        <div>
          <label class="form-label">الحالة</label>
          <select class="form-control" name="status" id="statusSelect">
            <option value="draft">مسودة</option>
            <option value="submitted">مُرسَل</option>
            <option value="needs_info">بحاجة معلومات</option>
            <option value="awaiting_offers">بانتظار عروض</option>
            <option value="offer_submitted">عرض مقدم</option>
            <option value="in_progress">قيد التنفيذ</option>
            <option value="report_submitted">تقرير مرفوع</option>
            <option value="completed">مكتمل</option>
            <option value="rejected">مرفوض</option>
            <option value="cancelled">ملغى</option>
          </select>
        </div>
      </div>
      <div class="form-row" style="margin-top:12px">
        <div>
          <label class="form-label">اسم المشروع</label>
          <input class="form-control" name="project_name">
        </div>
        <div>
          <label class="form-label">النشاط الاقتصادي</label>
          <input class="form-control" name="economic_activity">
        </div>
      </div>
      <div class="form-row" style="margin-top:12px">
        <div>
          <label class="form-label">الميزانية من</label>
          <input class="form-control" type="number" name="budget_min" min="0">
        </div>
        <div>
          <label class="form-label">الميزانية إلى</label>
          <input class="form-control" type="number" name="budget_max" min="0">
        </div>
      </div>
      <div style="margin-top:12px">
        <label class="form-label">الوصف *</label>
        <textarea class="form-control" name="description" rows="4" required></textarea>
      </div>
      <div style="margin-top:12px">
        <label class="form-label">ملاحظات إدارية</label>
        <textarea class="form-control" name="branch_notes" rows="2"></textarea>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px">
        <button type="submit" class="btn-save" id="saveBtn"><i class="bi bi-check2-circle"></i> حفظ التعديلات</button>
        <button type="button" class="btn-del" id="deleteBtn"><i class="bi bi-trash"></i> حذف الطلب</button>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const id = <?php echo (int) $requestId; ?>;
  if (!id) { location.href = 'consulting-requests-list.php'; return; }

  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyRoles: window.AppPermissions.CONSULTING_ADMIN_ROLES,
  });
  if (!ok) return;

  const base = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const form = document.getElementById('editForm');
  const showErr = (m) => { const el = document.getElementById('errorBox'); el.style.display='block'; el.textContent=m; };
  const showOk = (m) => { const el = document.getElementById('okBox'); el.style.display='block'; el.textContent=m; };

  try {
    const [cats, reqJson] = await Promise.all([
      fetch(`${base}/consulting/categories`, { headers:{ Authorization:`Bearer ${token()}`, Accept:'application/json' } }).then(r=>r.json()),
      fetch(`${base}/consulting/requests/${id}`, { headers:{ Authorization:`Bearer ${token()}`, Accept:'application/json' } }).then(r=>r.json()),
    ]);
    const req = reqJson.data || reqJson;
    document.getElementById('subTitle').textContent = (req.request_code || '#'+id) + ' — ' + (req.title || '');
    const catSel = document.getElementById('categorySelect');
    (Array.isArray(cats)?cats:[]).forEach(c => {
      const o = document.createElement('option'); o.value=c.code; o.textContent=c.name_ar; catSel.appendChild(o);
    });
    ['title','category_code','request_type','status','project_name','economic_activity','budget_min','budget_max','description','branch_notes']
      .forEach(k => { if (form.elements[k] && req[k] != null) form.elements[k].value = req[k]; });
    form.style.display = 'block';
  } catch {
    showErr('تعذّر تحميل الطلب.');
    return;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    const body = Object.fromEntries(new FormData(form).entries());
    ['budget_min','budget_max'].forEach(k => { if (body[k]==='') delete body[k]; else body[k]=Number(body[k]); });
    try {
      const r = await fetch(`${base}/consulting/requests/${id}`, {
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
      setTimeout(() => location.href = 'consulting-requests-list.php', 700);
    } catch {
      showErr('خطأ في الاتصال.');
      btn.disabled = false;
    }
  });

  document.getElementById('deleteBtn').addEventListener('click', async () => {
    if (!confirm('هل تريد حذف هذا الطلب نهائياً؟')) return;
    try {
      const r = await fetch(`${base}/consulting/requests/${id}`, {
        method:'DELETE',
        headers:{ Authorization:`Bearer ${token()}`, Accept:'application/json' },
      });
      const json = await r.json().catch(()=>({}));
      if (!r.ok) { showErr(json.message || 'فشل الحذف'); return; }
      location.href = 'consulting-requests-list.php';
    } catch { showErr('خطأ في الاتصال.'); }
  });
});
</script>
</body>
</html>
