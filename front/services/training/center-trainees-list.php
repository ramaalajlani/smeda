<?php
/** المتدربون — الاسم الثلاثي واسم الأم والجنس وتاريخ الميلاد. */
$basePath   = '../../';
$pageTitle  = 'المتدربون';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='trainees-list'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <div class="ttl">المتدربون</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="tcTrainees"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  const canManage = window.AppAuth.hasPermission('manage_trainees');
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  const genderLabel = g => g==='male'?'ذكر':(g==='female'?'أنثى':'—');
  const box = document.getElementById('tcTrainees');

  const PER = 50;
  let page = 0, done = false, count = 0;

  const cardHtml = (t) => {
    count++;
    const hay = [t.trainee_code, t.name, t.mother_name, t.gender, t.birth_date].filter(Boolean).join(' ');
    return `<article class="tc-mcard tc-item" data-search="${E(hay)}">
      <div class="tc-mcard-top">
        <div>
          <h3 class="tc-mcard-title"><i class="bi bi-person-fill"></i> ${E(t.name||'—')}</h3>
          <div class="tc-mcard-sub">${E(t.trainee_code||'بدون رمز')}</div>
        </div>
        <div class="tc-mcard-num">${count}</div>
      </div>
      <div class="tc-mcard-rows">
        <div class="tc-mcard-row"><span class="k">الاسم الثلاثي</span><span class="v">${E(t.name||'—')}</span></div>
        <div class="tc-mcard-row"><span class="k">اسم الأم</span><span class="v">${E(t.mother_name||'—')}</span></div>
        <div class="tc-mcard-row"><span class="k">الجنس</span><span class="v">${genderLabel(t.gender)}</span></div>
        <div class="tc-mcard-row"><span class="k">تاريخ الميلاد</span><span class="v">${E(t.birth_date||'—')}</span></div>
      </div>
      <div class="tc-mcard-acts">
        <a class="pdf" href="center-trainee.php?trainee=${t.id}"><i class="bi bi-person-vcard"></i> البطاقة</a>
        ${canManage ? `<a class="pdf" href="center-trainee-form.php?id=${t.id}"><i class="bi bi-pencil-square"></i> تعديل</a>` : ''}
      </div>
    </article>`;
  };

  async function loadMore() {
    const btn = document.getElementById('moreBtn');
    if (btn) { btn.disabled = true; btn.innerHTML = 'جاري التحميل...'; }
    page++;
    let arr = [];
    try {
      const r = await fetch(`${BASE}/trainees?per_page=${PER}&page=${page}`, { headers:H() });
      if (r.ok) arr = (await r.json()).data || [];
    } catch(e) {}
    if (arr.length < PER) done = true;
    document.getElementById('list').insertAdjacentHTML('beforeend', arr.map(cardHtml).join(''));
    const wrap = document.getElementById('moreWrap');
    if (done) wrap.innerHTML = count ? '' : '';
    else wrap.innerHTML = `<button type="button" id="moreBtn" class="tc-save" style="width:auto"><i class="bi bi-arrow-down-circle"></i> عرض المزيد</button>`;
    const nb = document.getElementById('moreBtn'); if (nb) nb.addEventListener('click', loadMore);
  }

  try {
    // الدفعة الأولى لتحديد إن كان هناك متدربون
    const r = await fetch(`${BASE}/trainees?per_page=${PER}&page=1`, { headers:H() });
    if (!r.ok) throw new Error('x');
    const first = (await r.json()).data || [];
    page = 1; if (first.length < PER) done = true;
    let html = '';
    if (canManage) html += `<a class="tc-fab-add" href="center-trainee-form.php"><i class="bi bi-plus-lg"></i> إضافة متدرب</a>`;
    if (!first.length) { box.innerHTML = html + '<div class="tc-empty">لا يوجد متدربون</div>'; return; }
    html += TC.searchBoxHtml('tcSearch','بحث بالاسم أو اسم الأم أو الرمز...') +
      `<div id="list" class="tc-mlist">${first.map(cardHtml).join('')}</div>` +
      `<div id="moreWrap" style="text-align:center;margin-top:14px">${done ? '' : `<button type="button" id="moreBtn" class="tc-save" style="width:auto"><i class="bi bi-arrow-down-circle"></i> عرض المزيد</button>`}</div>`;
    box.innerHTML = html;
    TC.bindListSearch('#tcSearch', '#list .tc-item');
    const nb = document.getElementById('moreBtn'); if (nb) nb.addEventListener('click', loadMore);
  } catch(e) {
    box.innerHTML = '<div class="tc-empty">تعذّر تحميل المتدربين</div>';
  }
});
</script>
</body>
</html>
