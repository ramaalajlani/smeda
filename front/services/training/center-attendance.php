<?php
/** الحضور — نطاق صف/دورة + حماية التعديلات. */
$basePath   = '../../';
$pageTitle  = 'الحضور';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='attendance'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">الحضور<small id="hSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="scopeBox"></div>
    <div class="tc-bar-row">
      <label style="font-weight:800;white-space:nowrap"><i class="bi bi-calendar-plus"></i> جلسة جديدة</label>
      <input type="date" id="atDate" class="tc-inp">
      <button type="button" id="atCreate" class="tc-btn-teal"><i class="bi bi-plus-circle"></i> إنشاء</button>
    </div>
    <div id="atSessions" class="tc-chips" style="margin:4px 0 14px"><div class="tc-spin">جاري التحميل...</div></div>
    <div id="atDetail"></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const COURSE_ID = new URLSearchParams(location.search).get('course');
const GROUP_ID = new URLSearchParams(location.search).get('group');
const GQ = GROUP_ID ? ('?group_id='+GROUP_ID) : '';
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!COURSE_ID){ location.href='center-app.php'; return; }
  document.getElementById('back').href = GROUP_ID
    ? ('center-group.php?course='+COURSE_ID+'&group='+GROUP_ID)
    : ('center-course.php?id='+COURSE_ID);
  TC.guardNav('#back');

  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const HP = () => ({ ...H(), 'Content-Type':'application/json' });
  const E = TC.esc;
  const jget = async p => (await fetch(`${BASE}${p}`,{headers:H()})).json();

  TC.renderScope('scopeBox', {
    groupId: GROUP_ID,
    groupHref: GROUP_ID ? (`center-group.php?course=${COURSE_ID}&group=${GROUP_ID}`) : '',
    courseHref: `center-attendance.php?course=${COURSE_ID}`,
    groupsHref: `center-groups.php?course=${COURSE_ID}`,
  });

  const cached = TC.getCourse(COURSE_ID);
  if (cached) document.getElementById('hSub').textContent = [cached.code, cached.title].filter(Boolean).join(' — ');
  else {
    try{
      const c = (await jget(`/training-courses/${COURSE_ID}`)).data||{};
      document.getElementById('hSub').textContent = [c.course_code, c.title].filter(Boolean).join(' — ');
      TC.cacheCourse({id:COURSE_ID,title:c.title,course_code:c.course_code,trainer:c.trainer});
    }catch(e){}
  }

  if (GROUP_ID) {
    try {
      const gd = await jget(`/training-courses/${COURSE_ID}/groups/${GROUP_ID}/trainees`);
      const g = gd.meta?.group || {};
      if (g.id) TC.cacheGroup({ id:g.id, name:g.name, code:g.code, course_id:COURSE_ID });
      TC.renderScope('scopeBox', {
        groupId: GROUP_ID, groupName: g.name || '',
        groupHref: `center-group.php?course=${COURSE_ID}&group=${GROUP_ID}`,
        courseHref: `center-attendance.php?course=${COURSE_ID}`,
        groupsHref: `center-groups.php?course=${COURSE_ID}`,
      });
    } catch(e){}
  }

  document.getElementById('atCreate').addEventListener('click', createSession);
  await refreshSessions();

  async function refreshSessions(){
    try{
      const arr = (await jget(`/training-courses/${COURSE_ID}/sessions${GQ}`)).data||[];
      const el = document.getElementById('atSessions');
      el.innerHTML = arr.length
        ? arr.map(x=>`<button type="button" class="tc-chip" data-sid="${x.id}"><i class="bi bi-calendar-event"></i> ${E((x.session_date||'').slice(0,10))} · #${x.session_no}</button>`).join('')
        : '<div class="tc-muted">لا توجد جلسات بعد — أنشئ جلسة لتسجيل الحضور</div>';
      el.querySelectorAll('.tc-chip').forEach(b=>b.addEventListener('click',()=>openAttendance(b.dataset.sid,b)));
    }catch(e){ document.getElementById('atSessions').innerHTML='<div class="tc-muted">تعذّر تحميل الجلسات</div>'; }
  }

  async function createSession(){
    if (TC.isDirty() && !(await TC.confirm('لديك تعديلات غير محفوظة. إنشاء جلسة جديدة؟'))) return;
    const date=document.getElementById('atDate').value; if(!date){TC.toast('اختر تاريخ الجلسة','err');return;}
    const r=await fetch(`${BASE}/training-courses/${COURSE_ID}/sessions`,{method:'POST',headers:HP(),body:JSON.stringify({session_date:date, course_group_id: GROUP_ID?Number(GROUP_ID):null})});
    if(r.ok){ document.getElementById('atDate').value=''; TC.clearDirty(); refreshSessions(); TC.toast('تم إنشاء الجلسة'); } else TC.toast('تعذّر إنشاء الجلسة','err');
  }

  async function openAttendance(sid,btn){
    if (TC.isDirty() && !(await TC.confirm('لديك تعديلات غير محفوظة. تبديل الجلسة؟'))) return;
    TC.clearDirty();
    document.querySelectorAll('#atSessions .tc-chip').forEach(c=>c.classList.remove('active')); btn&&btn.classList.add('active');
    const detail=document.getElementById('atDetail'); detail.innerHTML='<div class="tc-spin">جاري التحميل...</div>';
    try{
      const rows=(await jget(`/training-courses/${COURSE_ID}/sessions/${sid}/attendance`)).data||[];
      if(!rows.length){ detail.innerHTML='<div class="tc-empty">لا يوجد متدربون</div>'; return; }
      const STAT=[['present','حاضر'],['absent','غائب'],['late','متأخر'],['excused','معذور']];
      detail.innerHTML = `<div class="tc-mlist" id="atList">${rows.map((r,i)=>`<article class="tc-mcard" data-tid="${r.trainee_id}">
          <div class="tc-mcard-top">
            <div>
              <h3 class="tc-mcard-title"><i class="bi bi-person-fill"></i> ${E(r.name||'متدرب')}</h3>
              ${r.trainee_code?`<div class="tc-mcard-sub">${E(r.trainee_code)}</div>`:''}
            </div>
            <div class="tc-mcard-num">${i+1}</div>
          </div>
          <div class="tc-mcard-rows">
            <div class="tc-mcard-row">
              <span class="k"><i class="bi bi-clipboard-check"></i> الحالة</span>
              <span class="v"><select class="tc-status">${STAT.map(([v,l])=>`<option value="${v}" ${r.status===v?'selected':''}>${l}</option>`).join('')}</select></span>
            </div>
          </div>
        </article>`).join('')}</div>
        <button type="button" id="atSave" class="tc-save"><i class="bi bi-check2-circle"></i> حفظ الحضور</button>`;
      TC.watchDirty('#atList');
      document.getElementById('atSave').addEventListener('click',()=>saveAttendance(sid));
    }catch(e){ detail.innerHTML='<div class="tc-empty">تعذّر تحميل الحضور</div>'; }
  }

  async function saveAttendance(sid){
    const items=[...document.querySelectorAll('#atList .tc-mcard')].map(card=>({
      trainee_id:+card.dataset.tid,
      status:card.querySelector('.tc-status').value
    }));
    const btn=document.getElementById('atSave'); btn.disabled=true; btn.textContent='جاري الحفظ...';
    const r=await fetch(`${BASE}/training-courses/${COURSE_ID}/sessions/${sid}/attendance`,{method:'POST',headers:HP(),body:JSON.stringify({items})});
    btn.disabled=false; btn.innerHTML='<i class="bi bi-check2-circle"></i> حفظ الحضور';
    if(r.ok){ TC.clearDirty(); TC.toast('تم حفظ الحضور بنجاح'); } else TC.toast('تعذّر حفظ الحضور','err');
  }
});
</script>
</body>
</html>
