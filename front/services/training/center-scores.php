<?php
/** إدخال الدرجات — حماية التعديلات + نطاق. */
$basePath   = '../../';
$pageTitle  = 'إدخال الدرجات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='scores'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="backScores" href="center-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">إدخال الدرجات</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="scopeBox"></div>
    <div id="box"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const P = new URLSearchParams(location.search);
const COURSE_ID = P.get('course'); const MODULE_ID = P.get('module'); const GROUP_ID = P.get('group');
const GQ = GROUP_ID ? ('&group_id='+GROUP_ID) : '';
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!COURSE_ID || !MODULE_ID){ location.href='center-app.php'; return; }
  const back = document.getElementById('backScores');
  if (back) back.href = 'center-modules.php?course='+COURSE_ID + (GROUP_ID?('&group='+GROUP_ID):'');
  TC.guardNav('#backScores');

  TC.renderScope('scopeBox', {
    groupId: GROUP_ID,
    groupHref: GROUP_ID ? (`center-group.php?course=${COURSE_ID}&group=${GROUP_ID}`) : '',
    courseHref: `center-scores.php?course=${COURSE_ID}&module=${MODULE_ID}`,
    groupsHref: `center-groups.php?course=${COURSE_ID}`,
  });

  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const HP = () => ({ ...H(), 'Content-Type':'application/json' });
  const E = TC.esc;
  const jget = async p => (await fetch(`${BASE}${p}`,{headers:H()})).json();

  async function load(){
    const box = document.getElementById('box');
    box.innerHTML = '<div class="tc-spin">جاري التحميل...</div>';
    TC.clearDirty();
    try{
      const d = (await jget(`/training-courses/${COURSE_ID}/module-scores?module_id=${MODULE_ID}${GQ}`)).data||{};
      const mod = (d.modules||[]).find(m=>String(m.id)===String(MODULE_ID));
      const moduleTitle = mod?.title || 'المحور';
      const bar = document.querySelector('.tc-bar .ttl');
      if (bar) bar.innerHTML = `إدخال الدرجات<small>${E(moduleTitle)}</small>`;
      const trainees = d.trainees||[];
      if(!trainees.length){ box.innerHTML='<div class="tc-empty">لا يوجد متدربون مسجّلون في هذا النطاق</div>'; return; }

      box.innerHTML = `<div class="tc-mlist" id="scoreList">${trainees.map((t,i)=>`<article class="tc-mcard" data-tid="${t.trainee_id}">
          <div class="tc-mcard-top">
            <div>
              <h3 class="tc-mcard-title"><i class="bi bi-person-fill"></i> ${E(t.name||'متدرب')}</h3>
              ${t.trainee_code?`<div class="tc-mcard-sub">${E(t.trainee_code)}</div>`:''}
            </div>
            <div class="tc-mcard-num">${i+1}</div>
          </div>
          <div class="tc-mcard-rows">
            <div class="tc-mcard-row">
              <span class="k"><i class="bi bi-pencil-square"></i> درجة النشاط</span>
              <span class="v"><input type="number" class="sc-cw" value="${t.coursework_score??''}" min="0" inputmode="decimal" placeholder="—"></span>
            </div>
            <div class="tc-mcard-row">
              <span class="k"><i class="bi bi-journal-check"></i> درجة الامتحان</span>
              <span class="v"><input type="number" class="sc-ex" value="${t.exam_score??''}" min="0" inputmode="decimal" placeholder="—"></span>
            </div>
            <div class="tc-mcard-row">
              <span class="k"><i class="bi bi-calculator"></i> المجموع</span>
              <span class="v sc-total">${t.score??'—'}</span>
            </div>
          </div>
        </article>`).join('')}</div>
        <button type="button" id="save" class="tc-save"><i class="bi bi-save2"></i> حفظ الدرجات</button>`;

      TC.watchDirty('#scoreList');
      box.querySelectorAll('#scoreList .tc-mcard').forEach(card=>{
        const cw=card.querySelector('.sc-cw'),ex=card.querySelector('.sc-ex'),tot=card.querySelector('.sc-total');
        const upd=()=>{ const both=cw.value===''&&ex.value===''; tot.textContent=both?'—':((parseFloat(cw.value)||0)+(parseFloat(ex.value)||0)); };
        cw.addEventListener('input',upd); ex.addEventListener('input',upd);
      });
      document.getElementById('save').addEventListener('click', save);
    }catch(e){ box.innerHTML='<div class="tc-empty">تعذّر تحميل الدرجات</div>'; }
  }

  async function save(){
    const items=[...document.querySelectorAll('#scoreList .tc-mcard')].map(card=>({
      trainee_id:+card.dataset.tid,
      coursework_score:card.querySelector('.sc-cw').value,
      exam_score:card.querySelector('.sc-ex').value
    }));
    const btn=document.getElementById('save'); btn.disabled=true; btn.textContent='جاري الحفظ...';
    const r = await fetch(`${BASE}/training-courses/${COURSE_ID}/module-scores`,{method:'POST',headers:HP(),body:JSON.stringify({program_module_id:+MODULE_ID,items})});
    btn.disabled=false; btn.innerHTML='<i class="bi bi-check2-circle"></i> حفظ الدرجات';
    if(r.ok){ TC.clearDirty(); TC.toast('تم حفظ الدرجات بنجاح'); load(); } else TC.toast('تعذّر حفظ الدرجات','err');
  }
  load();
});
</script>
</body>
</html>
