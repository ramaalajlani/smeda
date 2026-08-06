<?php
/** متدربو الصف — بطاقات متجاوبة + إضافة/إخراج. */
$basePath   = '../../';
$pageTitle  = 'متدربو الصف';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='group'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">متدربو الصف<small id="hSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="scopeBox"></div>
    <div class="tc-hub-actions" id="grpActions" style="margin-bottom:14px"></div>
    <div id="box"><div class="tc-spin">جاري التحميل...</div></div>
    <h3 style="margin:20px 2px 10px;font-size:.95rem;color:var(--ref-ink)"><i class="bi bi-person-plus"></i> إضافة متدربين للصف</h3>
    <div id="pool"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const P = new URLSearchParams(location.search);
const COURSE_ID = P.get('course'); const GROUP_ID = P.get('group');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!COURSE_ID || !GROUP_ID){ location.href='center-app.php'; return; }
  document.getElementById('back').href = 'center-groups.php?course='+COURSE_ID;
  document.getElementById('grpActions').innerHTML = `
    <a href="center-attendance.php?course=${COURSE_ID}&group=${GROUP_ID}"><i class="bi bi-calendar-check"></i> حضور الصف</a>
    <a href="center-modules.php?course=${COURSE_ID}&group=${GROUP_ID}"><i class="bi bi-journal-text"></i> درجات الصف</a>`;
  TC.renderScope('scopeBox', {
    groupId: GROUP_ID,
    groupHref: `center-group.php?course=${COURSE_ID}&group=${GROUP_ID}`,
    courseHref: `center-trainees.php?course=${COURSE_ID}`,
    groupsHref: `center-groups.php?course=${COURSE_ID}`,
  });
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const HP = () => ({ ...H(), 'Content-Type':'application/json' });
  const E = TC.esc;
  const jget = async p => (await fetch(`${BASE}${p}`,{headers:H()})).json();
  const RESULT = { passed:['ناجح','b-green'], failed:['راسب','b-red'], pending:['قيد التقييم','b-gold'], attendance_only:['حضور فقط','b-gray'] };

  async function loadGroup(){
    const box = document.getElementById('box'); box.innerHTML='<div class="tc-spin">جاري التحميل...</div>';
    try{
      const d = await jget(`/training-courses/${COURSE_ID}/groups/${GROUP_ID}/trainees`);
      const rows = d.data||[]; const g = d.meta?.group||{};
      document.getElementById('hSub').textContent = g.name || '';
      if (g.id) TC.cacheGroup({ id:g.id, name:g.name, code:g.code, course_id:COURSE_ID });
      TC.renderScope('scopeBox', {
        groupId: GROUP_ID, groupName: g.name || '',
        groupHref: `center-group.php?course=${COURSE_ID}&group=${GROUP_ID}`,
        courseHref: `center-trainees.php?course=${COURSE_ID}`,
        groupsHref: `center-groups.php?course=${COURSE_ID}`,
      });
      if(!rows.length){ box.innerHTML='<div class="tc-empty">لا يوجد متدربون في هذا الصف بعد</div>'; return; }
      const genderLabel = g => g==='male'?'ذكر':(g==='female'?'أنثى':'—');
      box.innerHTML = `<div class="tc-mlist">${rows.map((t,i)=>{
        const [rl,rc]=RESULT[t.result]||['—','b-gray'];
        return `<article class="tc-mcard">
          <div class="tc-mcard-top">
            <div>
              <h3 class="tc-mcard-title"><i class="bi bi-person-fill"></i> ${E(t.name||'—')}</h3>
              <div class="tc-mcard-sub">${E(t.trainee_code||'بدون رمز')}</div>
            </div>
            <div class="tc-mcard-num">${i+1}</div>
          </div>
          <div class="tc-mcard-rows">
            <div class="tc-mcard-row"><span class="k">الاسم الثلاثي</span><span class="v">${E(t.name||'—')}</span></div>
            <div class="tc-mcard-row"><span class="k">اسم الأم</span><span class="v">${E(t.mother_name||'—')}</span></div>
            <div class="tc-mcard-row"><span class="k">الجنس</span><span class="v">${genderLabel(t.gender)}</span></div>
            <div class="tc-mcard-row"><span class="k">تاريخ الميلاد</span><span class="v">${E(t.birth_date||'—')}</span></div>
            <div class="tc-mcard-row"><span class="k">الدرجة</span><span class="v">${t.score??'—'}</span></div>
            <div class="tc-mcard-row"><span class="k">النتيجة</span><span class="v"><span class="tc-badge ${rc}">${rl}</span></span></div>
          </div>
          <div class="tc-mcard-acts">
            <button type="button" class="pdf" style="background:#fdecec;color:#b91c1c;border-color:#f5c2c2" onclick="rem(${t.id})"><i class="bi bi-box-arrow-right"></i> إخراج من الصف</button>
          </div>
        </article>`;
      }).join('')}</div>`;
    }catch(e){ box.innerHTML='<div class="tc-empty">تعذّر تحميل متدربي الصف</div>'; }
  }

  async function loadPool(){
    const pool = document.getElementById('pool'); pool.innerHTML='<div class="tc-spin">جاري التحميل...</div>';
    try{
      const rows = (await jget(`/training-courses/${COURSE_ID}/ungrouped-trainees`)).data||[];
      if(!rows.length){ pool.innerHTML='<div class="tc-empty">كل المتدربين مُصنّفون في صفوف</div>'; return; }
      pool.innerHTML = `<div class="tc-mlist" id="poolList">${rows.map((t,i)=>`<article class="tc-mcard">
          <div class="tc-mcard-top">
            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;flex:1">
              <input type="checkbox" class="pick" value="${t.id}" style="margin-top:4px;width:18px;height:18px">
              <span>
                <h3 class="tc-mcard-title">${E(t.name)}</h3>
                <div class="tc-mcard-sub">${E(t.trainee_code||'بدون رمز')}</div>
              </span>
            </label>
            <div class="tc-mcard-num">${i+1}</div>
          </div>
        </article>`).join('')}</div>
        <div class="tc-bar-row" style="padding:8px 0 0">
          <label style="font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" id="chkAll"> تحديد الكل</label>
        </div>
        <button type="button" class="tc-save" id="addSel"><i class="bi bi-plus-circle"></i> إضافة المحدّدين للصف</button>`;
      document.getElementById('chkAll').addEventListener('change', e=>{ pool.querySelectorAll('.pick').forEach(c=>c.checked=e.target.checked); });
      document.getElementById('addSel').addEventListener('click', assignSel);
    }catch(e){ pool.innerHTML='<div class="tc-empty">تعذّر تحميل المتدربين</div>'; }
  }

  async function assignSel(){
    const ids = [...document.querySelectorAll('#pool .pick:checked')].map(c=>+c.value);
    if(!ids.length){ TC.toast('اختر متدرباً واحداً على الأقل','err'); return; }
    const r = await fetch(`${BASE}/training-courses/${COURSE_ID}/groups/${GROUP_ID}/assign`, { method:'POST', headers:HP(), body:JSON.stringify({ trainee_ids: ids }) });
    if(r.ok){ TC.toast('تمت الإضافة للصف','ok'); loadGroup(); loadPool(); } else TC.toast('تعذّرت الإضافة','err');
  }

  window.rem = async (tid) => {
    const r = await fetch(`${BASE}/training-courses/${COURSE_ID}/groups/${GROUP_ID}/remove`, { method:'POST', headers:HP(), body:JSON.stringify({ trainee_ids:[tid] }) });
    if(r.ok){ TC.toast('تم إخراج المتدرب','ok'); loadGroup(); loadPool(); } else TC.toast('تعذّر الإخراج','err');
  };

  loadGroup(); loadPool();
});
</script>
</body>
</html>
