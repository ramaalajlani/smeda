<?php
/** تطبيق المتدرب — دوراتي. */
$basePath   = '../../';
$pageTitle  = 'دوراتي';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $teActive='courses'; include __DIR__ . '/_te-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <div class="ttl">دوراتي</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div class="tc-scope is-course" style="margin-bottom:12px">
      <div class="tc-scope-txt"><i class="bi bi-mortarboard"></i> الدورات المسجَّل فيها · للشهادات استخدم قائمة «شهاداتي»</div>
    </div>
    <div id="box"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  try {
    const r = await fetch(`${BASE}/training-courses?per_page=100`, { headers:H() });
    if (!r.ok) throw new Error('x');
    const courses = (await r.json()).data || [];
    TC.cacheCourses(courses);
    const box = document.getElementById('box');
    if (!courses.length){
      box.innerHTML='<div class="tc-empty">لست مسجّلاً في أي دورة بعد</div>';
      return;
    }
    box.innerHTML = TC.searchBoxHtml('tcSearch','بحث...') +
      `<div id="list">${courses.map((c,i)=>{
      const title = [c.course_code, c.title].filter(Boolean).join(' — ');
      return `<div class="tc-item" data-search="${E(title)}">
        <div class="tc-item-num">${i+1}</div>
        <div class="tc-item-card">
          <div class="tc-item-title">${E(title || 'دورة')}</div>
          <div class="tc-muted" style="font-size:.8rem;margin:4px 0 8px">${E(c.trainer?.name || '')}</div>
          <a class="tc-item-btn" href="trainee-course.php?id=${c.id}"><i class="bi bi-eye"></i> عرض الدورة</a>
        </div>
      </div>`;
    }).join('')}</div>`;
    TC.bindListSearch('#tcSearch', '#list .tc-item');
  } catch(e){
    document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر تحميل الدورات</div>';
  }
});
</script>
</body>
</html>
