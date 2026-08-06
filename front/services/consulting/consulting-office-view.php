<?php
$basePath  = '../../';
$pageTitle = 'ملف المكتب الاستشاري';
$activePage= 'consulting';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .page-wrap { max-width:960px; margin:auto; padding:24px 14px; }
    .profile-hero { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border-radius:22px; padding:28px; margin-bottom:18px; }
    .profile-name { font-size:1.5rem; font-weight:800; margin:0 0 6px; }
    .profile-meta { display:flex; flex-wrap:wrap; gap:16px; opacity:.92; font-size:.9rem; }
    .info-card { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:20px; box-shadow:var(--c-shadow); margin-bottom:16px; }
    .info-card-title { font-weight:800; font-size:1rem; color:var(--c-text); margin:0 0 14px; display:flex; align-items:center; gap:8px; }
    .info-card-title i { color:var(--c-primary); }
    .stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:12px; }
    .stat-item { background:var(--c-soft); border-radius:14px; padding:14px; text-align:center; }
    .stat-val { font-size:1.6rem; font-weight:800; color:var(--c-primary); }
    .stat-lbl { font-size:.78rem; color:var(--c-muted); font-weight:700; }
    .spec-tag { background:var(--c-soft); color:var(--c-primary); padding:5px 12px; border-radius:10px; font-size:.83rem; font-weight:700; display:inline-block; margin:3px; }
    .review-card { border:1px solid var(--c-border); border-radius:14px; padding:14px; margin-bottom:10px; background:#fff; }
    .stars { color:#f59e0b; font-size:1.1rem; }
    .btn-action { display:inline-flex; align-items:center; gap:7px; padding:10px 18px; border-radius:14px; font-weight:700; font-size:.88rem; cursor:pointer; border:none; }
    .btn-suspend { background:#fee2e2; color:#dc2626; }
    .btn-activate { background:#dcfce7; color:#166534; }
    .btn-violation { background:#fef3c7; color:#92400e; }
    .violation-card { border-right:4px solid #f59e0b; padding:10px 14px; background:#fffbeb; border-radius:0 10px 10px 0; margin-bottom:8px; font-size:.87rem; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="page-wrap">
  <a href="consulting-offices-list.php" style="color:var(--c-primary);font-weight:700;text-decoration:none;font-size:.9rem;display:inline-block;margin-bottom:14px">
    <i class="bi bi-arrow-right"></i> العودة للمكاتب
  </a>
  <div id="mainContent" style="text-align:center;padding:60px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>جاري التحميل...
  </div>
</div>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyRoles: window.AppPermissions.CONSULTING_REQUEST_ACCESS_ROLES,
  });
  if (!ok) return;

  const base    = window.APP_CONFIG.API_BASE_URL;
  const token   = () => window.AppAuth.getToken();
  const officeId= new URLSearchParams(location.search).get('id');
  if (!officeId) { alert('معرّف المكتب مفقود'); return; }

  const canManageOffice = window.AppAuth.hasAnyRole?.(window.AppPermissions.CONSULTING_OFFICE_MANAGE_ROLES);

  async function apiGet(url) {
    return fetch(url, { headers:{ 'Authorization':`Bearer ${token()}`, 'Accept':'application/json' }}).then(r=>r.json());
  }
  async function apiPost(url, body) {
    return fetch(url, { method:'POST', headers:{ 'Authorization':`Bearer ${token()}`, 'Content-Type':'application/json', 'Accept':'application/json' }, body:JSON.stringify(body)}).then(r=>r.json());
  }

  async function load() {
    const json = await apiGet(`${base}/consulting/offices/${officeId}`);
    const o = json.data || json;
    render(o);
  }

  function starRow(n) { return [1,2,3,4,5].map(i=>`<span style="color:${i<=n?'#f59e0b':'#d1d5db'};font-size:.9rem">★</span>`).join(''); }

  function render(o) {
    const statusMap = { active:'نشط', pending:'بانتظار اعتماد', suspended:'موقوف', revoked:'ملغى' };
    let html = `
      <div class="profile-hero">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
          <div style="width:60px;height:60px;background:rgba(255,255,255,.15);border-radius:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="bi bi-buildings-fill" style="font-size:2rem"></i>
          </div>
          <div style="flex:1">
            <div class="profile-name">${window.APP_HELPERS.e(o.name)}</div>
            <div class="profile-meta">
              <span><i class="bi bi-geo-alt"></i> ${o.governorate?.name_ar||'—'}</span>
              <span><i class="bi bi-telephone"></i> ${o.phone||'—'}</span>
              <span><i class="bi bi-envelope"></i> ${o.email||'—'}</span>
              <span style="background:rgba(255,255,255,.2);padding:3px 10px;border-radius:10px">${statusMap[o.status]||o.status}</span>
            </div>
          </div>
          ${canManageOffice ? `<div style="display:flex;flex-direction:column;gap:8px">
            ${o.status==='active'?`<button class="btn-action btn-suspend" onclick="changeStatus('suspend')"><i class="bi bi-pause-circle-fill"></i> إيقاف</button>`:''}
            ${o.status!=='active'?`<button class="btn-action btn-activate" onclick="changeStatus('activate')"><i class="bi bi-play-circle-fill"></i> تفعيل</button>`:''}
            <button class="btn-action btn-violation" onclick="addViolation()"><i class="bi bi-exclamation-triangle-fill"></i> مخالفة</button>
          </div>` : ''}
        </div>
      </div>

      <div class="stat-grid" style="margin-bottom:16px">
        <div class="stat-item"><div class="stat-val">${o.overall_rating ? Number(o.overall_rating).toFixed(1) : '—'}</div><div class="stat-lbl">التقييم العام</div></div>
        <div class="stat-item"><div class="stat-val">${o.total_requests_completed||0}</div><div class="stat-lbl">طلبات مكتملة</div></div>
        <div class="stat-item"><div class="stat-val">${o.on_time_rate ? Math.round(o.on_time_rate)+'%' : '—'}</div><div class="stat-lbl">الالتزام بالمواعيد</div></div>
        <div class="stat-item"><div class="stat-val">${o.license_number||'—'}</div><div class="stat-lbl">رقم الترخيص</div></div>
      </div>

      <div class="info-card">
        <div class="info-card-title"><i class="bi bi-patch-check-fill"></i> التخصصات</div>
        <div>${(o.specializations||[]).length ? o.specializations.map(s=>`<span class="spec-tag"><i class="bi bi-check2"></i> ${s.category_code||s}</span>`).join('') : '<span style="color:var(--c-muted);font-size:.88rem">لا توجد تخصصات مسجلة</span>'}</div>
      </div>`;

    if (o.bio || o.address) {
      html += `<div class="info-card">
        <div class="info-card-title"><i class="bi bi-info-circle-fill"></i> نبذة عن المكتب</div>
        ${o.bio?`<p style="font-size:.9rem;color:var(--c-text);line-height:1.9;margin:0 0 10px">${window.APP_HELPERS.e(o.bio)}</p>`:''}
        ${o.address?`<div style="font-size:.88rem;color:var(--c-muted)"><i class="bi bi-geo-alt"></i> ${window.APP_HELPERS.e(o.address)}</div>`:''}
      </div>`;
    }

    // Reviews
    if (o.reviews?.length) {
      html += `<div class="info-card">
        <div class="info-card-title"><i class="bi bi-star-fill"></i> تقييمات العملاء (${o.reviews.length})</div>
        ${o.reviews.map(r=>`
          <div class="review-card">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap">
              <strong style="font-size:.88rem">${r.reviewer?.name||'عميل'}</strong>
              <span class="stars">${starRow(r.overall_rating)}</span>
              <span style="margin-right:auto;font-size:.78rem;color:var(--c-muted)">${new Date(r.created_at).toLocaleDateString('ar-SY')}</span>
            </div>
            ${r.comment?`<p style="font-size:.85rem;color:var(--c-muted);margin:0;line-height:1.7">${window.APP_HELPERS.e(r.comment)}</p>`:''}
            <div style="display:flex;gap:10px;margin-top:6px;font-size:.78rem;color:var(--c-muted);flex-wrap:wrap">
              ${r.quality_rating?`<span>الجودة: ${starRow(r.quality_rating)}</span>`:''}
              ${r.time_rating?`<span>المواعيد: ${starRow(r.time_rating)}</span>`:''}
              ${r.communication_rating?`<span>التواصل: ${starRow(r.communication_rating)}</span>`:''}
            </div>
          </div>`).join('')}
      </div>`;
    }

    // Violations (admin only)
    if (canManageOffice && o.violations?.length) {
      html += `<div class="info-card">
        <div class="info-card-title"><i class="bi bi-exclamation-triangle-fill" style="color:#d97706"></i> سجل المخالفات</div>
        ${o.violations.map(v=>`
          <div class="violation-card">
            <div style="display:flex;gap:10px;flex-wrap:wrap">
              <strong>${window.APP_HELPERS.e(v.violation_type)}</strong>
              <span style="margin-right:auto;color:var(--c-muted);font-size:.78rem">${new Date(v.created_at).toLocaleDateString('ar-SY')}</span>
            </div>
            ${v.description?`<p style="margin:4px 0 0;font-size:.84rem;color:var(--c-muted)">${window.APP_HELPERS.e(v.description)}</p>`:''}
          </div>`).join('')}
      </div>`;
    }

    document.getElementById('mainContent').innerHTML = html;
  }

  window.changeStatus = async function(action) {
    if (!confirm(`هل تريد ${action==='suspend'?'إيقاف':'تفعيل'} هذا المكتب؟`)) return;
    const r = await apiPost(`${base}/consulting/offices/${officeId}/${action}`, {});
    alert(r.message||'تم');
    load();
  };

  window.addViolation = async function() {
    const type = prompt('نوع المخالفة:');
    if (!type) return;
    const desc = prompt('وصف المخالفة (اختياري):');
    const r = await apiPost(`${base}/consulting/offices/${officeId}/violations`, { violation_type: type, description: desc||undefined });
    alert(r.message||'تم تسجيل المخالفة');
    load();
  };

  load();
});
</script>
</body>
</html>
