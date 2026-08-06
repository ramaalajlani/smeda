<?php
/** المتدربون — بطاقات متجاوبة. */
$basePath   = '../../';
$pageTitle  = 'المتدربون';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='trainees'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">المتدربون</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div class="tc-scope is-course" style="margin-bottom:12px">
      <div class="tc-scope-txt"><i class="bi bi-people-fill"></i> متدربو <strong>هذه الدورة</strong> · للتصنيف في صفوف استخدم <a href="#" id="toGroups">الصفوف</a></div>
    </div>
    <div id="box"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const COURSE_ID = new URLSearchParams(location.search).get('course');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!COURSE_ID){ location.href='center-app.php'; return; }
  document.getElementById('back').href = 'center-course.php?id='+COURSE_ID;
  const toG = document.getElementById('toGroups'); if (toG) toG.href = 'center-groups.php?course='+COURSE_ID;
  const canManage = window.AppAuth.hasPermission('manage_trainees');
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  const RESULT = {
    passed:['ناجح','b-green'], failed:['راسب','b-red'],
    pending:['قيد التقييم','b-gold'], attendance_only:['حضور فقط','b-gray']
  };
  try{
    const arr = (await (await fetch(`${BASE}/training-courses/${COURSE_ID}/trainees`,{headers:H()})).json()).data||[];
    const box = document.getElementById('box');
    let html = '';
    if (canManage) {
      html += `<a class="tc-fab-add" href="center-trainee-form.php?course=${COURSE_ID}"><i class="bi bi-plus-lg"></i> إضافة متدرب للدورة</a>`;
    }
    if(!arr.length){ box.innerHTML=html+'<div class="tc-empty">لا يوجد متدربون</div>'; return; }
    html += `<div class="tc-mlist">${arr.map((t,i)=>{
      const [rl,rc]=RESULT[t.pivot?.result]||[t.pivot?.result||'—','b-gray'];
      return `<article class="tc-mcard">
        <div class="tc-mcard-top">
          <div>
            <h3 class="tc-mcard-title"><i class="bi bi-person-fill"></i> ${E(t.name||'—')}</h3>
            <div class="tc-mcard-sub">${E(t.trainee_code||'بدون رمز')}</div>
          </div>
          <div class="tc-mcard-num">${i+1}</div>
        </div>
        <div class="tc-mcard-rows">
          <div class="tc-mcard-row"><span class="k"><i class="bi bi-calendar-check"></i> الحضور</span><span class="v">${t.attendance_rate!=null?E(t.attendance_rate)+'%':'—'}</span></div>
          <div class="tc-mcard-row"><span class="k"><i class="bi bi-calculator"></i> الدرجة</span><span class="v">${t.pivot?.score??'—'}</span></div>
          <div class="tc-mcard-row"><span class="k"><i class="bi bi-trophy"></i> النتيجة</span><span class="v"><span class="tc-badge ${rc}">${E(rl)}</span></span></div>
        </div>
      </article>`;
    }).join('')}</div>`;
    box.innerHTML = html;
  }catch(e){ document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر التحميل</div>'; }
});
</script>
</body>
</html>
