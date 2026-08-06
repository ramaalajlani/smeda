<?php
/** المواد والدرجات — مع شارة النطاق. */
$basePath   = '../../';
$pageTitle  = 'المواد والدرجات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='modules'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">المواد والدرجات</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="scopeBox"></div>
    <div id="box"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const COURSE_ID = new URLSearchParams(location.search).get('course');
const GROUP_ID = new URLSearchParams(location.search).get('group');
const GQ = GROUP_ID ? ('&group='+GROUP_ID) : '';
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!COURSE_ID){ location.href='center-app.php'; return; }
  document.getElementById('back').href = GROUP_ID ? ('center-group.php?course='+COURSE_ID+'&group='+GROUP_ID) : ('center-course.php?id='+COURSE_ID);

  TC.renderScope('scopeBox', {
    groupId: GROUP_ID,
    groupHref: GROUP_ID ? (`center-group.php?course=${COURSE_ID}&group=${GROUP_ID}`) : '',
    courseHref: `center-modules.php?course=${COURSE_ID}`,
    groupsHref: `center-groups.php?course=${COURSE_ID}`,
  });

  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  try{
    if (GROUP_ID) {
      try {
        const gd = await (await fetch(`${BASE}/training-courses/${COURSE_ID}/groups/${GROUP_ID}/trainees`,{headers:H()})).json();
        const g = gd.meta?.group || {};
        if (g.id) TC.cacheGroup({ id:g.id, name:g.name, code:g.code, course_id:COURSE_ID });
        TC.renderScope('scopeBox', {
          groupId: GROUP_ID, groupName: g.name || '',
          groupHref: `center-group.php?course=${COURSE_ID}&group=${GROUP_ID}`,
          courseHref: `center-modules.php?course=${COURSE_ID}`,
          groupsHref: `center-groups.php?course=${COURSE_ID}`,
        });
      } catch(e){}
    }
    const arr = (await (await fetch(`${BASE}/training-courses/${COURSE_ID}/modules`,{headers:H()})).json()).data||[];
    const box = document.getElementById('box');
    if(!arr.length){
      box.innerHTML='<div class="tc-empty">لا توجد محاور<br><span class="tc-muted">تأكد أن الحقيبة/البرنامج مرتبط بالدورة</span></div>';
      return;
    }
    box.innerHTML = `<div class="tc-mlist">${arr.map((m,i)=>`<article class="tc-mcard">
        <div class="tc-mcard-top">
          <div>
            <h3 class="tc-mcard-title"><i class="bi bi-journal-text"></i> ${E(m.title||'محور')}</h3>
            <div class="tc-mcard-sub">${E(m.evaluation_method||'بدون طريقة تقييم')}</div>
          </div>
          <div class="tc-mcard-num">${i+1}</div>
        </div>
        <div class="tc-mcard-rows">
          <div class="tc-mcard-row"><span class="k"><i class="bi bi-clock"></i> الساعات</span><span class="v">${m.hours!=null?E(m.hours):'—'}</span></div>
        </div>
        <div class="tc-mcard-acts">
          <a class="pdf" href="center-scores.php?course=${COURSE_ID}&module=${m.id}${GQ}"><i class="bi bi-pencil-square"></i> إدخال الدرجات</a>
        </div>
      </article>`).join('')}</div>`;
  }catch(e){ document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر التحميل</div>'; }
});
</script>
</body>
</html>
