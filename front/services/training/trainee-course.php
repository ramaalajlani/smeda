<?php
/** تفاصيل دورة للمتدرب — عرض فقط. */
$basePath   = '../../';
$pageTitle  = 'تفاصيل الدورة';
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
    <a class="ic" href="trainee-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">الدورة<small id="hSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content" id="box"><div class="tc-spin">جاري التحميل...</div></div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const COURSE_ID = new URLSearchParams(location.search).get('id');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!COURSE_ID){ location.href='trainee-app.php'; return; }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  const RESULT = { passed:['ناجح','b-green'], failed:['راسب','b-red'], pending:['قيد التقييم','b-gold'], attendance_only:['حضور فقط','b-gray'] };
  try {
    const [cRes, tRes] = await Promise.all([
      fetch(`${BASE}/training-courses/${COURSE_ID}`, { headers:H() }),
      fetch(`${BASE}/training-courses/${COURSE_ID}/trainees`, { headers:H() }),
    ]);
    if (!cRes.ok) throw new Error('x');
    const c = (await cRes.json()).data || {};
    const trainees = tRes.ok ? ((await tRes.json()).data || []) : [];
    const me = window.AppAuth.getUser() || {};
    const mine = trainees.find(t => String(t.id) === String(me.trainee_id)) || trainees[0] || null;
    document.getElementById('hSub').textContent = [c.course_code, c.title].filter(Boolean).join(' — ');
    const [rl,rc] = RESULT[mine?.pivot?.result] || ['—','b-gray'];
    document.getElementById('box').innerHTML = `
      <article class="tc-mcard">
        <h3 class="tc-mcard-title">${E(c.title||'دورة')}</h3>
        <div class="tc-mcard-sub">${E(c.course_code||'')}</div>
        <div class="tc-mcard-rows" style="margin-top:12px">
          <div class="tc-mcard-row"><span class="k">المدرب</span><span class="v">${E(c.trainer?.name||'—')}</span></div>
          <div class="tc-mcard-row"><span class="k">الفترة</span><span class="v">${E([c.start_date,c.end_date].filter(Boolean).join(' ← ')||'—')}</span></div>
          <div class="tc-mcard-row"><span class="k">الحالة</span><span class="v">${E(c.status||'—')}</span></div>
          <div class="tc-mcard-row"><span class="k">حضوري</span><span class="v">${mine?.attendance_rate!=null?E(mine.attendance_rate)+'%':'—'}</span></div>
          <div class="tc-mcard-row"><span class="k">درجتي</span><span class="v">${mine?.pivot?.score??'—'}</span></div>
          <div class="tc-mcard-row"><span class="k">نتيجتي</span><span class="v"><span class="tc-badge ${rc}">${E(rl)}</span></span></div>
        </div>
        <div class="tc-mcard-acts">
          <a class="pdf" href="trainee-certificates.php?course=${COURSE_ID}"><i class="bi bi-patch-check"></i> شهاداتي لهذه الدورة</a>
        </div>
      </article>`;
  } catch(e){
    document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر تحميل الدورة</div>';
  }
});
</script>
</body>
</html>
