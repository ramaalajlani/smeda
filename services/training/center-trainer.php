<?php
/** إدارة المدرب — مطابق لشاشة «شعب». */
$basePath   = '../../';
$pageTitle  = 'إدارة المدرب';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='trainer'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" href="center-trainers.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">المدرب<small id="barSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-hub">
    <h2 id="hubTitle">إدارة المدرب</h2>
    <div class="tc-hub-actions" id="hubActions"></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const ID = new URLSearchParams(location.search).get('id') || new URLSearchParams(location.search).get('trainer');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!ID){ location.href='center-trainers.php'; return; }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const canManage = window.AppAuth.hasPermission('manage_trainers');
  try{
    const r = await fetch(`${BASE}/trainers/${ID}`, { headers:H() });
    const t = (await r.json()).data || {};
    document.getElementById('barSub').textContent = [t.trainer_code, t.name].filter(Boolean).join(' — ');
    document.getElementById('hubTitle').textContent = t.name || 'إدارة المدرب';
  }catch(e){}
  let html = `
    <a href="center-trainer-kits.php?trainer=${ID}"><i class="bi bi-briefcase"></i> عرض الحقائب</a>
    <a href="center-trainer-courses.php?trainer=${ID}"><i class="bi bi-collection"></i> عرض الدورات</a>
  `;
  if (canManage) html = `<a href="center-trainer-form.php?id=${ID}"><i class="bi bi-pencil-square"></i> تعديل البيانات</a>` + html;
  document.getElementById('hubActions').innerHTML = html;
});
</script>
</body>
</html>
