<?php
/** إدارة الحقيبة — مطابق لشاشة «شعب». */
$basePath   = '../../';
$pageTitle  = 'إدارة الحقيبة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='kit'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" href="center-kits.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">الحقيبة<small id="barSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-hub">
    <h2 id="hubTitle">إدارة الحقيبة</h2>
    <div class="tc-hub-actions" id="hubActions"></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const KIT_ID = new URLSearchParams(location.search).get('id') || new URLSearchParams(location.search).get('kit');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  if (!KIT_ID) { location.href = 'center-kits.php'; return; }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const canManageCourses = !!(window.AppAuth.hasPermission && window.AppAuth.hasPermission('manage_courses'));
  const canManageKits = !!(window.AppAuth.hasPermission && window.AppAuth.hasPermission('manage_kits'));

  try {
    const r = await fetch(`${BASE}/training-kits/${KIT_ID}`, { headers:H() });
    const k = (await r.json()).data || {};
    if (window.TC) TC.cacheKit(k);
    document.getElementById('barSub').textContent = [k.code, k.name].filter(Boolean).join(' — ');
    document.getElementById('hubTitle').textContent = k.name || 'إدارة الحقيبة';
    const trainers = k.trainers || [];
    const trainersCount = k.stats?.trainers_count ?? trainers.length;
    if (trainersCount) {
      const names = trainers.slice(0, 4).map(t => t.name).filter(Boolean).join('، ');
      const extra = trainers.length > 4 ? ` (+${trainers.length - 4})` : '';
      document.getElementById('hubTitle').insertAdjacentHTML('afterend',
        `<p style="margin:8px 0 0;color:#64748b;font-size:.9rem">المدربين المكلّفين (${trainersCount}): ${E(names || '—')}${E(extra)}</p>`);
    }
  } catch(e) {}

  let actions = `
    <a href="center-kit-materials.php?kit=${KIT_ID}"><i class="bi bi-collection"></i> مواد الحقيبة</a>
    <a href="center-kit-trainers.php?kit=${KIT_ID}"><i class="bi bi-person-workspace"></i> مدربو الحقيبة</a>
    <a href="center-kit-programs.php?kit=${KIT_ID}"><i class="bi bi-journal-text"></i> عرض البرامج</a>
    <a href="center-kit-courses.php?kit=${KIT_ID}"><i class="bi bi-person"></i> عرض الدورات</a>
  `;
  if (canManageKits) actions += `<a href="center-kit-form.php?id=${KIT_ID}"><i class="bi bi-pencil-square"></i> تعديل الحقيبة</a>`;
  if (canManageCourses) actions += `<a href="center-kit-create-course.php?kit=${KIT_ID}"><i class="bi bi-plus-circle"></i> إنشاء دورة</a>`;
  document.getElementById('hubActions').innerHTML = actions;
});
</script>
</body>
</html>
