<?php
/** عرض الدورات — بحث فوري. */
$basePath   = '../../';
$pageTitle  = 'عرض الدورات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='courses'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <div class="ttl">عرض الدورات</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div style="margin-bottom:14px;text-align:left" data-center-only>
      <a class="tc-save" style="display:inline-flex;width:auto" href="center-course-create.php"><i class="bi bi-plus-circle"></i> دورة جديدة</a>
    </div>
    <div id="tcCourses"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  if (window.AppAuth.isTrainerWorkspaceUser && AppAuth.isTrainerWorkspaceUser()) {
    location.replace('trainer-app.php'); return;
  }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  try {
    const r = await fetch(`${BASE}/training-courses?per_page=200`, { headers:H() });
    if (!r.ok) throw new Error('x');
    const courses = (await r.json()).data || [];
    TC.cacheCourses(courses);
    const box = document.getElementById('tcCourses');
    if (!courses.length){
      box.innerHTML='<div class="tc-empty">لا توجد دورات<br><a class="tc-item-btn" style="margin-top:14px" href="center-kits.php"><i class="bi bi-diagram-3"></i> ابدأ من الحقائب التدريبية</a></div>';
      return;
    }
    box.innerHTML = TC.searchBoxHtml('tcSearch','بحث عن دورة...') +
      `<div id="list">${courses.map((c,i)=>{
      const kit = c.training_kit?.name || '';
      const title = [c.course_code, kit || c.title].filter(Boolean).join(' — ');
      const hay = [c.course_code, c.title, kit].filter(Boolean).join(' ');
      return `<div class="tc-item" data-search="${E(hay)}">
        <div class="tc-item-num">${i+1}</div>
        <div class="tc-item-card">
          <div class="tc-item-title">${E(title || c.title || 'دورة')}</div>
          <a class="tc-item-btn" href="center-course.php?id=${c.id}"><i class="bi bi-diagram-3"></i> عرض الإدارة</a>
        </div>
      </div>`;
    }).join('')}</div>`;
    TC.bindListSearch('#tcSearch', '#list .tc-item');
  } catch(e){
    document.getElementById('tcCourses').innerHTML='<div class="tc-empty">تعذّر تحميل الدورات</div>';
  }
});
</script>
</body>
</html>
