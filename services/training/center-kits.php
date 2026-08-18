<?php
/** عرض الحقائب المكلَّف بها المركز — مع المدربين. */
$basePath   = '../../';
$pageTitle  = 'الحقائب التدريبية';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='kits'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <div class="ttl">الحقائب التدريبية</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="tcKits"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  const canManage = window.AppAuth.hasPermission('manage_kits');
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
  try {
    const r = await fetch(`${BASE}/training-kits?per_page=200&with_counts=1&with_trainers=1`, { headers:H() });
    const payload = await r.json().catch(() => ({}));
    if (!r.ok) throw Object.assign(new Error('load-failed'), { data: payload, status: r.status });
    const all = payload.data || [];
    TC.cacheKits(all);
    const box = document.getElementById('tcKits');
    let html = '';
    if (canManage) {
      html += `<a class="tc-fab-add" href="center-kit-form.php"><i class="bi bi-plus-lg"></i> إضافة حقيبة</a>`;
    }
    if (!all.length){
      html += '<div class="tc-empty">لا توجد حقائب مكلَّف بها هذا المركز</div>';
      box.innerHTML = html;
      return;
    }
    html += TC.searchBoxHtml('tcSearch','بحث عن حقيبة...') +
      `<div id="list" class="tc-mlist">${all.map((k,i)=>{
      const title = [k.code, k.name].filter(Boolean).join(' — ');
      const trainers = k.trainers || [];
      const trainersLabel = trainers.length
        ? trainers.slice(0,3).map(t=>t.name).filter(Boolean).join('، ') + (trainers.length>3?` (+${trainers.length-3})`:'')
        : 'لا يوجد مدربون مكلّفون';
      const hay = [k.code, k.name, k.sector, k.level, trainers.map(t=>t.name).join(' ')].filter(Boolean).join(' ');
      const count = k.stats?.trainers_count ?? trainers.length;
      return `<article class="tc-mcard tc-item" data-search="${E(hay)}">
        <div class="tc-mcard-top">
          <div>
            <h3 class="tc-mcard-title"><i class="bi bi-briefcase"></i> ${E(title || 'حقيبة')}</h3>
            <div class="tc-mcard-sub">${E([k.sector,k.level].filter(Boolean).join(' · ')||'—')}</div>
          </div>
          <div class="tc-mcard-num">${i+1}</div>
        </div>
        <div class="tc-mcard-rows">
          <div class="tc-mcard-row"><span class="k">المدربين المكلّفين</span><span class="v">${count}</span></div>
          <div class="tc-mcard-row"><span class="k">الأسماء</span><span class="v">${E(trainersLabel)}</span></div>
        </div>
        <div class="tc-mcard-acts">
          <a class="pdf" href="center-kit.php?id=${k.id}"><i class="bi bi-diagram-3"></i> إدارة</a>
          <a class="pdf" href="center-kit-trainers.php?kit=${k.id}"><i class="bi bi-person-workspace"></i> المدربون</a>
        </div>
      </article>`;
    }).join('')}</div>`;
    box.innerHTML = html;
    TC.bindListSearch('#tcSearch', '#list .tc-item');
  } catch(e){
    showLoadError('tcKits', 'الحقائب', e);
  }
});
</script>
</body>
</html>
