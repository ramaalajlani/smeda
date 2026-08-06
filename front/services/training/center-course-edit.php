<?php
/** تعديل بيانات الدورة. */
$basePath   = '../../';
$pageTitle  = 'تعديل الدورة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='course'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">تعديل الدورة</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <form id="editForm" class="tc-form-card">
      <div class="fld"><label>عنوان الدورة</label><input id="title" required></div>
      <div class="tc-form-grid two">
        <div class="fld"><label>نوع التنفيذ</label><select id="deliveryMode"><option value="offline">حضوري</option><option value="online">أونلاين</option></select></div>
        <div class="fld"><label>الحالة</label><select id="status"><option value="scheduled">مجدولة</option><option value="draft">مسودة</option><option value="ongoing">جارية</option><option value="completed">مكتملة</option><option value="cancelled">ملغاة</option></select></div>
        <div class="fld"><label>تاريخ البدء</label><input type="date" id="startDate"></div>
        <div class="fld"><label>تاريخ الانتهاء</label><input type="date" id="endDate"></div>
        <div class="fld"><label>الساعات المخططة</label><input type="number" min="1" id="plannedHours"></div>
        <div class="fld"><label>السعة</label><input type="number" min="1" id="capacity"></div>
      </div>
      <div class="fld"><label>ملاحظات</label><textarea id="notes" rows="3"></textarea></div>
      <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
        <button type="submit" class="tc-save" style="width:auto" id="saveBtn"><i class="bi bi-save2-fill"></i> حفظ التعديلات</button>
        <button type="button" class="tc-save" style="width:auto;background:#b91c1c" id="delBtn"><i class="bi bi-trash"></i> حذف الدورة</button>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const COURSE_ID = new URLSearchParams(location.search).get('id');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!COURSE_ID){ location.href='center-app.php'; return; }
  document.getElementById('back').href = 'center-course.php?id='+COURSE_ID;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json', 'Content-Type':'application/json' });
  const msg = (t, ok=false) => { const el=document.getElementById('formMsg'); el.className='tc-form-msg '+(ok?'ok':'err'); el.textContent=t||''; };
  const V = id => document.getElementById(id).value;
  const setV = (id,v) => { const e=document.getElementById(id); if(e) e.value = v ?? ''; };

  try {
    const c = (await (await fetch(`${BASE}/training-courses/${COURSE_ID}`, { headers:H() })).json()).data || {};
    setV('title', c.title); setV('deliveryMode', c.delivery_mode||'offline'); setV('status', c.status||'scheduled');
    setV('startDate', c.start_date); setV('endDate', c.end_date);
    setV('plannedHours', c.planned_hours); setV('capacity', c.capacity); setV('notes', c.notes);
  } catch(e){ msg('تعذّر تحميل بيانات الدورة'); }

  document.getElementById('editForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn=document.getElementById('saveBtn'); btn.disabled=true; msg('');
    const body = {
      title: V('title').trim(), delivery_mode: V('deliveryMode'), status: V('status'),
      start_date: V('startDate')||null, end_date: V('endDate')||null,
      planned_hours: Number(V('plannedHours'))||null, capacity: Number(V('capacity'))||null,
      notes: V('notes').trim()||null,
    };
    try {
      const r = await fetch(`${BASE}/training-courses/${COURSE_ID}`, { method:'PATCH', headers:H(), body: JSON.stringify(body) });
      const j = await r.json().catch(()=>({}));
      if (!r.ok) throw new Error(j.message || (j.errors && Object.values(j.errors).flat()[0]) || 'فشل الحفظ');
      if (window.TC) { TC.toast('تم حفظ التعديلات','ok'); TC.cacheCourse({id:COURSE_ID,title:body.title,course_code:(j.data?.course_code)}); }
      setTimeout(()=>{ location.href='center-course.php?id='+COURSE_ID; }, 600);
    } catch(err){ msg(err.message||'تعذّر الحفظ'); btn.disabled=false; }
  });

  document.getElementById('delBtn').addEventListener('click', async () => {
    if (!(await TC.confirm('حذف هذه الدورة؟ (يمكن استرجاعها لاحقاً من الإدارة)'))) return;
    const r = await fetch(`${BASE}/training-courses/${COURSE_ID}`, { method:'DELETE', headers:H() });
    if (r.ok){ TC.toast('تم حذف الدورة','ok'); setTimeout(()=>{ location.href='center-app.php'; }, 600); }
    else TC.toast('تعذّر حذف الدورة','err');
  });
});
</script>
</body>
</html>
