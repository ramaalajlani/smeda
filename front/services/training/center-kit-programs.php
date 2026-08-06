<?php
/** برامج الحقيبة — جدول مطابق. */
$basePath   = '../../';
$pageTitle  = 'برامج الحقيبة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='kit-programs'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-kits.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">برامج الحقيبة</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-table-wrap" id="box"><div class="tc-spin">جاري التحميل...</div></div>
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
    const k = (await (await fetch(`${BASE}/training-kits/${KIT_ID}`,{headers:H()})).json()).data||{};
    const arr = k.programs || [];
    const box = document.getElementById('box');
    if(!arr.length){ box.innerHTML='<div class="tc-empty">لا توجد برامج</div>'; return; }
    box.innerHTML = `<table class="tc-table">
      <thead><tr><th>البرنامج</th><th>الكود</th><th>الحالة</th><th>إلزامي</th></tr></thead>
      <tbody>${arr.map(p=>`<tr>
        <td>${E(p.name||'—')}</td><td>${E(p.code||'—')}</td><td>${E(p.status||'—')}</td>
        <td>${p.linking?.is_required?'نعم':'لا'}</td>
      </tr>`).join('')}</tbody></table>`;
  }catch(e){ document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر التحميل</div>'; }
});
</script>
</body>
</html>
