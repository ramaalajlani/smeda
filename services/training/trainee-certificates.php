<?php
/** شهادات المتدرب. */
$basePath   = '../../';
$pageTitle  = 'شهاداتي';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $teActive='certificates'; include __DIR__ . '/_te-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <div class="ttl">شهاداتي</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content" id="box"><div class="tc-spin">جاري التحميل...</div></div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const COURSE_FILTER = new URLSearchParams(location.search).get('course');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  const me = window.AppAuth.getUser() || {};
  const CERT_ST = {
    approved:['معتمدة','b-green'], issued:['صادرة','b-green'],
    pending:['قيد الاعتماد','b-gold'], rejected:['مرفوضة','b-red'], draft:['مسودة','b-gray'],
  };
  try {
    let url = `${BASE}/certificates?per_page=100`;
    if (me.trainee_id) url += `&trainee_id=${me.trainee_id}`;
    if (COURSE_FILTER) url += `&training_course_id=${COURSE_FILTER}`;
    const r = await fetch(url, { headers:H() });
    if (!r.ok) throw new Error('x');
    let arr = (await r.json()).data || [];
    if (COURSE_FILTER) arr = arr.filter(c => String(c.training_course_id) === String(COURSE_FILTER));
    const box = document.getElementById('box');
    if (!arr.length){
      box.innerHTML='<div class="tc-empty">لا توجد شهادات بعد<br><a class="tc-item-btn" style="margin-top:12px" href="trainee-app.php">العودة لدوراتي</a></div>';
      return;
    }
    box.innerHTML = `<div class="tc-mlist">${arr.map((c,i)=>{
      const [sl,sc] = CERT_ST[c.status] || [c.status||'—','b-gray'];
      const num = c.certificate_number || c.reference_number || ('#'+c.id);
      const acts = [];
      if (c.printable_url) acts.push(`<a class="open" href="${E(c.printable_url)}" target="_blank" rel="noopener"><i class="bi bi-eye-fill"></i> فتح</a>`);
      if (c.pdf_url) acts.push(`<a class="pdf" href="${E(c.pdf_url)}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</a>`);
      return `<article class="tc-mcard">
        <div class="tc-mcard-top">
          <div>
            <h3 class="tc-mcard-title"><i class="bi bi-patch-check"></i> ${E(num)}</h3>
            <div class="tc-mcard-sub">${E(c.training_course?.title || c.course_title || '')}</div>
          </div>
          <div class="tc-mcard-num">${i+1}</div>
        </div>
        <div class="tc-mcard-rows">
          <div class="tc-mcard-row"><span class="k">النتيجة</span><span class="v">${E(c.result||'—')}</span></div>
          <div class="tc-mcard-row"><span class="k">التاريخ</span><span class="v">${E(c.issue_date||'—')}</span></div>
          <div class="tc-mcard-row"><span class="k">الحالة</span><span class="v"><span class="tc-badge ${sc}">${E(sl)}</span></span></div>
        </div>
        ${acts.length?`<div class="tc-mcard-acts">${acts.join('')}</div>`:''}
      </article>`;
    }).join('')}</div>`;
  } catch(e){
    document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر تحميل الشهادات</div>';
  }
});
</script>
</body>
</html>
