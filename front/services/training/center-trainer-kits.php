<?php
/** حقائب المدرب — جدول. */
$basePath   = '../../';
$pageTitle  = 'حقائب المدرب';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='trainer-kits'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-trainers.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">حقائب المدرب</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-table-wrap" id="box"><div class="tc-spin">جاري التحميل...</div></div>
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
    const t = (await (await fetch(`${BASE}/trainers/${ID}`,{headers:H()})).json()).data||{};
    const kits = t.kits || [];
    const box = document.getElementById('box');
    if (!kits.length) { box.innerHTML='<div class="tc-empty">لا توجد حقائب</div>'; return; }
    box.innerHTML = `<table class="tc-table">
      <thead><tr><th>الحقيبة</th><th>الكود</th><th>القطاع</th><th>إدارة</th></tr></thead>
      <tbody>${kits.map(k=>`<tr>
        <td>${E(k.name||'—')}</td><td>${E(k.code||'—')}</td><td>${E(k.sector||'—')}</td>
        <td><a class="tc-gold-btn" href="center-kit.php?id=${k.id}">+</a></td>
      </tr>`).join('')}</tbody></table>`;
  }catch(e){ document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر التحميل</div>'; }
});
</script>
</body>
</html>
