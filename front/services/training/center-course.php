<?php
/** إدارة الدورة — مسار موجّه بدل قائمة مكررة. */
$basePath   = '../../';
$pageTitle  = 'إدارة الدورة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='course'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="backBtn" href="center-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl" id="barTitle">الدورة<small id="barSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-hub">
    <h2 id="hubTitle">إدارة الدورة</h2>
    <p id="hubHint" class="tc-muted" style="margin:0 0 14px;font-size:.85rem;font-weight:650">اتبع الخطوات بالترتيب — أو استخدم القائمة الجانبية في أي وقت</p>
    <div id="sumBox" class="tc-sum" hidden></div>
    <div class="tc-hub-guide" id="hubActions"></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const COURSE_ID = new URLSearchParams(location.search).get('id');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  if (!COURSE_ID) { location.href = (AppAuth.isTrainerWorkspaceUser && AppAuth.isTrainerWorkspaceUser()) ? 'trainer-app.php' : 'center-app.php'; return; }
  const isTrainer = !!(AppAuth.isTrainerWorkspaceUser && AppAuth.isTrainerWorkspaceUser());
  if (isTrainer) document.getElementById('backBtn').href = 'trainer-app.php';
  const canManageCourse = window.AppAuth.hasPermission('manage_courses');
  const canIssue = window.AppAuth.hasPermission('issue_certificates');
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;

  const setHead = (title, code) => {
    document.getElementById('barTitle').innerHTML = `الدورة<small>${E(code || title || '')}</small>`;
    document.getElementById('hubTitle').textContent = title || 'إدارة الدورة';
  };
  const cached = TC.getCourse(COURSE_ID);
  if (cached) setHead(cached.title, cached.code);

  let traineesCount = 0, groupsCount = 0, ungrouped = 0, certsCount = 0;
  try {
    const r = await fetch(`${BASE}/training-courses/${COURSE_ID}`, { headers:H() });
    const c = (await r.json()).data || {};
    setHead(c.title, c.course_code);
    TC.cacheCourse({id:COURSE_ID,title:c.title,course_code:c.course_code,trainer:c.trainer});
    traineesCount = c.trainees_count ?? (c.trainees || []).length;
    certsCount = c.certificates_count ?? (c.certificates || []).length;
  } catch(e) {}

  try {
    const gd = await (await fetch(`${BASE}/training-courses/${COURSE_ID}/groups`, { headers:H() })).json();
    groupsCount = (gd.data || []).length;
    ungrouped = gd.meta?.ungrouped_count ?? 0;
  } catch(e) {}

  const sum = document.getElementById('sumBox');
  sum.hidden = false;
  sum.innerHTML = `
    <div class="cell"><span class="n">${traineesCount}</span><span class="l">متدرب</span></div>
    <div class="cell"><span class="n">${groupsCount}</span><span class="l">صف</span></div>
    <div class="cell"><span class="n">${certsCount}</span><span class="l">شهادة</span></div>`;

  if (ungrouped > 0) {
    document.getElementById('hubHint').innerHTML =
      `<strong style="color:#9a6500">${ungrouped} متدرب غير مُصنّف في صف</strong> — ابدأ من الصفوف`;
  }

  if (isTrainer) {
    document.getElementById('hubHint').textContent = 'مساحة المدرب — سجّل الحضور والدرجات لمتدربيك';
  }

  let actions = `
    <a href="center-groups.php?course=${COURSE_ID}"><i class="bi bi-collection"></i><span class="lab">1 · الصفوف</span><small>تقسيم المتدربين ثم حضور/درجات لكل صف</small></a>
    <a href="center-trainees.php?course=${COURSE_ID}"><i class="bi bi-people"></i><span class="lab">2 · متدربو الدورة</span><small>عرض النتائج والحضور على مستوى الدورة</small></a>
    <a href="center-attendance.php?course=${COURSE_ID}"><i class="bi bi-calendar-check"></i><span class="lab">3 · الحضور</span><small>تسجيل حضور الجلسات</small></a>
    <a href="center-modules.php?course=${COURSE_ID}"><i class="bi bi-journal-text"></i><span class="lab">4 · المواد والدرجات</span><small>إدخال درجات المحاور</small></a>
    <a href="center-certificates.php?course=${COURSE_ID}"><i class="bi bi-patch-check"></i><span class="lab">5 · الشهادات</span><small>${canIssue ? 'إصدار للناجحين + فتح/PDF' : 'عرض وفتح/PDF'}</small></a>
    <a href="center-course-report.php?id=${COURSE_ID}"><i class="bi bi-file-earmark"></i><span class="lab">6 · التقرير</span><small>ملخص الدورة للطباعة</small></a>`;
  if (canManageCourse) {
    actions += `<a href="center-course-edit.php?id=${COURSE_ID}" data-center-only><i class="bi bi-pencil-square"></i><span class="lab">تعديل بيانات الدورة</span><small>العنوان والتواريخ والمدرب</small></a>`;
  }
  document.getElementById('hubActions').innerHTML = actions;
  if (typeof tcApplyWorkspaceNav === 'function') tcApplyWorkspaceNav();
});
</script>
</body>
</html>
