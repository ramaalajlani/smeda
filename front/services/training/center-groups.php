<?php
/** صفوف الدورة — إنشاء مطوي + تنبيه غير المصنّفين. */
$basePath   = '../../';
$pageTitle  = 'صفوف الدورة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='groups'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">صفوف الدورة<small id="hSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <button type="button" class="tc-collapse-btn" id="toggleAdd"><i class="bi bi-plus-circle"></i> إضافة صف جديد</button>
    <form id="addForm" class="tc-form-card tc-collapse-panel" hidden>
      <div class="tc-form-grid two">
        <div class="fld"><label>اسم الصف</label><input id="gName" required placeholder="مثال: الصف الأول"></div>
        <div class="fld"><label>السعة (اختياري)</label><input id="gCap" type="number" min="1" placeholder="—"></div>
      </div>
      <div style="text-align:center"><button type="submit" class="tc-save" id="addBtn"><i class="bi bi-plus-circle"></i> حفظ الصف</button></div>
    </form>
    <div id="groups"><div class="tc-spin">جاري التحميل...</div></div>
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
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const HP = () => ({ ...H(), 'Content-Type':'application/json' });
  const E = TC.esc;
  const jget = async p => (await fetch(`${BASE}${p}`,{headers:H()})).json();

  const cc = TC.getCourse(COURSE_ID);
  if (cc) document.getElementById('hSub').textContent = cc.title || '';
  else jget(`/training-courses/${COURSE_ID}`).then(d=>{document.getElementById('hSub').textContent=(d.data||{}).title||'';}).catch(()=>{});

  document.getElementById('toggleAdd').addEventListener('click', ()=>{
    const f = document.getElementById('addForm');
    f.hidden = !f.hidden;
    if (!f.hidden) document.getElementById('gName').focus();
  });

  async function load(){
    const box = document.getElementById('groups'); box.innerHTML='<div class="tc-spin">جاري التحميل...</div>';
    try{
      const d = await jget(`/training-courses/${COURSE_ID}/groups`);
      const groups = d.data||[]; const ung = d.meta?.ungrouped_count ?? 0;
      groups.forEach(g => TC.cacheGroup({ id:g.id, name:g.name, code:g.code, course_id:COURSE_ID }));
      let html = '';
      if (ung>0) html += `<div class="tc-scope is-group" style="margin-bottom:12px"><div class="tc-scope-txt"><i class="bi bi-exclamation-triangle"></i> <strong>${ung}</strong> متدرب غير مُصنّف — افتح صفاً وأضفهم من الأسفل.</div></div>`;
      if (!groups.length){ box.innerHTML = html + '<div class="tc-empty">لا توجد صفوف — اضغط «إضافة صف جديد»</div>'; return; }
      html += groups.map((g,i)=>`<div class="tc-item" data-search="${E(g.name)} ${E(g.code||'')}">
        <div class="tc-item-num">${i+1}</div>
        <div class="tc-item-card">
          <div class="tc-item-title">${E(g.name)} ${g.code?`<small style="color:#7a8891">(${E(g.code)})</small>`:''}</div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <span class="tc-badge b-blue"><i class="bi bi-people"></i> ${g.trainees_count} متدرب${g.capacity?` / ${g.capacity}`:''}</span>
            <a class="tc-item-btn" href="center-group.php?course=${COURSE_ID}&group=${g.id}"><i class="bi bi-box-arrow-in-left"></i> متدربو الصف</a>
            <a class="tc-item-btn" href="center-attendance.php?course=${COURSE_ID}&group=${g.id}"><i class="bi bi-calendar-check"></i> حضور</a>
            <a class="tc-item-btn" href="center-modules.php?course=${COURSE_ID}&group=${g.id}"><i class="bi bi-journal-text"></i> درجات</a>
            <button type="button" class="tc-item-btn" style="background:#fdecec;color:#b91c1c" onclick="delGroup(${g.id},'${E(g.name)}')"><i class="bi bi-trash"></i></button>
          </div>
        </div>
      </div>`).join('');
      box.innerHTML = html;
    }catch(e){ box.innerHTML='<div class="tc-empty">تعذّر تحميل الصفوف</div>'; }
  }

  window.delGroup = async (gid, name) => {
    if (!(await TC.confirm(`حذف الصف «${name}»؟ سيُفكّ ارتباط متدربيه (لن يُحذفوا).`))) return;
    const r = await fetch(`${BASE}/training-courses/${COURSE_ID}/groups/${gid}`, { method:'DELETE', headers:H() });
    TC.toast(r.ok?'تم حذف الصف':'تعذّر الحذف', r.ok?'ok':'err'); if(r.ok) load();
  };

  document.getElementById('addForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const name = document.getElementById('gName').value.trim(); if(!name){TC.toast('أدخل اسم الصف','err'); return;}
    const cap = document.getElementById('gCap').value;
    const btn = document.getElementById('addBtn'); btn.disabled=true;
    const r = await fetch(`${BASE}/training-courses/${COURSE_ID}/groups`, { method:'POST', headers:HP(), body:JSON.stringify({ name, capacity: cap?Number(cap):null }) });
    btn.disabled=false;
    if(r.ok){
      document.getElementById('gName').value=''; document.getElementById('gCap').value='';
      document.getElementById('addForm').hidden = true;
      TC.toast('تم إنشاء الصف','ok'); load();
    } else TC.toast('تعذّر إنشاء الصف','err');
  });

  load();
});
</script>
</body>
</html>
