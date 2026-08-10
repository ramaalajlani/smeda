<?php
/** دورات الحقيبة — بطاقات مطابقة للمرجع. */
$basePath   = '../../';
$pageTitle  = 'دورات الحقيبة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='kit-courses'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-kits.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">دورات الحقيبة<small id="barSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content" id="box"><div class="tc-spin">جاري التحميل...</div></div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const KIT_ID = new URLSearchParams(location.search).get('kit');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!KIT_ID){ location.href='center-kits.php'; return; }
  document.getElementById('back').href = 'center-kit.php?id='+KIT_ID;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  try{
    const [kr, cr] = await Promise.all([
      fetch(`${BASE}/training-kits/${KIT_ID}`, { headers:H() }),
      fetch(`${BASE}/training-courses?per_page=100&training_kit_id=${KIT_ID}`, { headers:H() }),
    ]);
    const k = (await kr.json()).data || {};
    document.getElementById('barSub').textContent = k.name || '';
    const courses = (await cr.json()).data || [];
    const box = document.getElementById('box');
    if(!courses.length){ box.innerHTML='<div class="tc-empty">لا توجد دورات</div>'; return; }
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
