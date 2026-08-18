<?php
/** عرض المدربين — بحث فوري. */
$basePath   = '../../';
$pageTitle  = 'عرض المدربين';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='trainers'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <div class="ttl">عرض المدربين</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="tcTrainers"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  const canManage = window.AppAuth.hasPermission('manage_trainers');
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  const showLoadError = (boxId, label, err) => {
    let msg = `تعذّر تحميل ${label}`;
    const status = err?.status;
    const apiMsg = err?.data?.message || err?.message;
    if (status) msg += ` (${status})`;
    if (apiMsg && status !== 401) msg += `: ${apiMsg}`;
    document.getElementById(boxId).innerHTML = `<div class="tc-empty">${E(msg)}</div>`;
  };
  try{
    const user = window.AppAuth.getUser()||{};
    let url = `${BASE}/trainers?per_page=200`;
    if (user.training_center_id) url += `&training_center_id=${user.training_center_id}`;
    const r = await fetch(url, { headers:H() });
    const payload = await r.json().catch(() => ({}));
    if (!r.ok) throw Object.assign(new Error('load-failed'), { data: payload, status: r.status });
    const all = payload.data || [];
    const box = document.getElementById('tcTrainers');
    let html = '';
    if (canManage) {
      html += `<a class="tc-fab-add" href="center-trainer-form.php"><i class="bi bi-plus-lg"></i> إضافة مدرب</a>`;
    }
    if (!all.length) {
      html += '<div class="tc-empty">لا يوجد مدربون</div>';
      box.innerHTML = html;
      return;
    }
    html += TC.searchBoxHtml('tcSearch','بحث عن مدرب...') +
      `<div id="list">${all.map((t,i)=>{
      const title = [t.trainer_code, t.name].filter(Boolean).join(' — ');
      const hay = [t.trainer_code, t.name, t.phone].filter(Boolean).join(' ');
      return `<div class="tc-item" data-search="${E(hay)}">
        <div class="tc-item-num">${i+1}</div>
        <div class="tc-item-card">
          <div class="tc-item-title">${E(title || 'مدرب')}</div>
          <a class="tc-item-btn" href="center-trainer.php?id=${t.id}"><i class="bi bi-diagram-3"></i> إدارة المدرب</a>
        </div>
      </div>`;
    }).join('')}</div>`;
    box.innerHTML = html;
    TC.bindListSearch('#tcSearch', '#list .tc-item');
  }catch(e){
    showLoadError('tcTrainers', 'المدربين', e);
  }
});
</script>
</body>
</html>
