<?php
/** شهادات المركز — اختر الدورة ثم أصدر أو اطبع شهادات المتدربين. */
$basePath   = '../../';
$pageTitle  = 'شهادات المتدربين';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='certificates-hub'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <div class="ttl">شهادات المتدربين</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <p style="margin:0 0 14px;color:#64748b;font-size:.9rem;line-height:1.7">
      الشهادات مرتبطة بالدورة — اختر الدورة ثم أصدر أو اطبع شهادات الناجحين.
    </p>
    <div id="tcCerts"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.5"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  if (window.AppAuth.isTrainerWorkspaceUser && AppAuth.isTrainerWorkspaceUser()) {
    location.replace('trainer-app.php');
    return;
  }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  const box = document.getElementById('tcCerts');
  const canIssue = window.AppAuth.hasPermission('issue_certificates');
  const canView = window.AppAuth.hasPermission('view_certificates');

  if (!canView) {
    box.innerHTML = '<div class="tc-empty">لا تملك صلاحية عرض الشهادات — تواصل مع إدارة المركز.</div>';
    return;
  }

  try {
    const r = await fetch(`${BASE}/training-courses?per_page=200`, { headers: H() });
    const payload = await r.json().catch(() => ({}));
    if (!r.ok) throw Object.assign(new Error('load-failed'), { status: r.status, data: payload });
    const courses = payload.data || [];
    TC.cacheCourses(courses);
    if (!courses.length) {
      box.innerHTML = '<div class="tc-empty">لا توجد دورات بعد — أنشئ دورة من «الدورات» ثم سجّل المتدربين لإصدار الشهادات.</div>';
      return;
    }
    box.innerHTML = TC.searchBoxHtml('tcSearch', 'بحث عن دورة...') +
      `<div id="list" class="tc-mlist">${courses.map((c, i) => {
        const title = [c.course_code, c.title].filter(Boolean).join(' — ');
        const certs = c.certificates_count ?? (c.certificates || []).length ?? 0;
        const hay = [c.course_code, c.title, c.training_kit?.name].filter(Boolean).join(' ');
        return `<article class="tc-mcard tc-item" data-search="${E(hay)}">
          <div class="tc-mcard-top">
            <div>
              <h3 class="tc-mcard-title"><i class="bi bi-mortarboard"></i> ${E(title || 'دورة')}</h3>
              <div class="tc-mcard-sub">${E(c.training_kit?.name || '—')}</div>
            </div>
            <div class="tc-mcard-num">${i + 1}</div>
          </div>
          <div class="tc-mcard-rows">
            <div class="tc-mcard-row"><span class="k">المتدربون</span><span class="v">${c.trainees_count ?? '—'}</span></div>
            <div class="tc-mcard-row"><span class="k">الشهادات</span><span class="v">${certs}</span></div>
          </div>
          <div class="tc-mcard-acts">
            <a class="pdf" href="center-certificates.php?course=${c.id}"><i class="bi bi-patch-check"></i> ${canIssue ? 'إصدار / طباعة' : 'عرض الشهادات'}</a>
            <a class="pdf" href="center-course.php?id=${c.id}"><i class="bi bi-diagram-3"></i> إدارة الدورة</a>
          </div>
        </article>`;
      }).join('')}</div>`;
    TC.bindListSearch('#tcSearch', '#list .tc-item');
  } catch (e) {
    let msg = 'تعذّر تحميل الدورات';
    if (e?.status === 403) msg = 'لا تملك صلاحية عرض الدورات — تأكد أن حسابك مربوط بمركز تدريبي.';
    else if (e?.status === 401) msg = 'انتهت الجلسة — سجّل الدخول مجدداً.';
    box.innerHTML = `<div class="tc-empty">${E(msg)}</div>`;
  }
});
</script>
</body>
</html>
