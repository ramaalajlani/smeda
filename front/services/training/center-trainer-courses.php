<?php
/** دورات المدرب — بطاقات مطابقة. */
$basePath   = '../../';
$pageTitle  = 'دورات المدرب';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='trainer-courses'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-trainers.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">دورات المدرب</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content" id="box"><div class="tc-spin">جاري التحميل...</div></div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const ID = new URLSearchParams(location.search).get('trainer') || new URLSearchParams(location.search).get('id');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!ID){ location.href='center-trainers.php'; return; }
  document.getElementById('back').href = 'center-trainer.php?id='+ID;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  try{
    const courses = (await (await fetch(`${BASE}/training-courses?per_page=100&trainer_id=${ID}`,{headers:H()})).json()).data||[];
    const box = document.getElementById('box');
    if (!courses.length) { box.innerHTML='<div class="tc-empty">لا توجد دورات</div>'; return; }
    box.innerHTML = courses.map((c,i)=>`<div class="tc-item">
      <div class="tc-item-num">${i+1}</div>
      <div class="tc-item-card">
        <div class="tc-item-title">${E([c.course_code,c.title].filter(Boolean).join(' — '))}</div>
        <a class="tc-item-btn" href="center-course.php?id=${c.id}"><i class="bi bi-diagram-3"></i> عرض الإدارة</a>
      </div>
    </div>`).join('');
  }catch(e){ document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر التحميل</div>'; }
});
</script>
</body>
</html>
