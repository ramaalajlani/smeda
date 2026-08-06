<?php
/** إضافة دورة تدريبية (مستقل) — اختيار الحقيبة ← المدرب ← التفاصيل. */
$basePath   = '../../';
$pageTitle  = 'دورة جديدة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='course-create'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" href="center-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">دورة جديدة</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <form id="createForm" class="tc-form-card">
      <div class="fld"><label>الحقيبة التدريبية</label><select id="kitId" required><option value="">— اختر الحقيبة —</option></select></div>
      <div class="fld"><label>المدرب</label><select id="trainerId" required><option value="">— اختر الحقيبة أولاً —</option></select></div>
      <div class="fld"><label>البرنامج (اختياري)</label><select id="programId"><option value="">— بدون —</option></select></div>
      <div class="fld"><label>عنوان الدورة</label><input id="title" required placeholder="عنوان الدورة"></div>
      <div class="tc-form-grid two">
        <div class="fld"><label>نوع التنفيذ</label><select id="deliveryMode"><option value="offline">حضوري</option><option value="online">أونلاين</option></select></div>
        <div class="fld"><label>الحالة</label><select id="status"><option value="scheduled">مجدولة</option><option value="draft">مسودة</option><option value="ongoing">جارية</option></select></div>
        <div class="fld"><label>تاريخ البدء</label><input type="date" id="startDate"></div>
        <div class="fld"><label>تاريخ الانتهاء</label><input type="date" id="endDate"></div>
        <div class="fld"><label>الساعات المخططة</label><input type="number" min="1" id="plannedHours" value="20" required></div>
        <div class="fld"><label>السعة</label><input type="number" min="1" id="capacity" value="20" required></div>
      </div>
      <div class="fld"><label>ملاحظات</label><textarea id="notes" rows="3"></textarea></div>
      <div style="text-align:center"><button type="submit" class="tc-save" id="saveBtn"><i class="bi bi-save2-fill"></i> حفظ الدورة</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json', 'Content-Type':'application/json' });
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const msg = (t, ok=false) => { const el=document.getElementById('formMsg'); el.className='tc-form-msg '+(ok?'ok':'err'); el.textContent=t||''; };
  const fill = (sel, rows, labelFn, ph) => { sel.innerHTML = `<option value="">${ph||'— اختر —'}</option>` + rows.map(r=>`<option value="${r.id}">${E(labelFn(r))}</option>`).join(''); };

  if (window.AppAuth.hasPermission && !window.AppAuth.hasPermission('manage_courses')) {
    msg('لا تملك صلاحية إنشاء الدورات'); document.getElementById('createForm').style.display='none'; return;
  }

  // المركز (تلقائي: مركز المستخدم أو الأول المتاح)
  let centerId = null;
  const user = window.AppAuth.getUser() || {};
  try {
    const cs = ((await (await fetch(`${BASE}/training-centers?per_page=50`, { headers:H() })).json()).data) || [];
    centerId = user.training_center_id || (cs[0] && cs[0].id) || null;
  } catch(e){}

  // الحقائب
  let kits = [];
  try {
    kits = ((await (await fetch(`${BASE}/training-kits?per_page=100`, { headers:H() })).json()).data) || [];
    fill(document.getElementById('kitId'), kits, k => `${k.name||''} (${k.code||k.id})`, '— اختر الحقيبة —');
  } catch(e){ msg('تعذّر تحميل الحقائب'); return; }

  let currentKit = null;
  document.getElementById('kitId').addEventListener('change', async (e) => {
    const kid = e.target.value;
    const trainerSel = document.getElementById('trainerId');
    const progSel = document.getElementById('programId');
    if (!kid){ fill(trainerSel, [], ()=>'', '— اختر الحقيبة أولاً —'); progSel.innerHTML='<option value="">— بدون —</option>'; return; }
    try {
      currentKit = (await (await fetch(`${BASE}/training-kits/${kid}`, { headers:H() })).json()).data || {};
      document.getElementById('title').value = currentKit.name || '';
      document.getElementById('plannedHours').value = currentKit.hours || 20;
      // البرامج
      fill(progSel, currentKit.programs || [], p => `${p.name||''} (${p.code||p.id})`, '— بدون برنامج —');
      // المدربون المعتمدون على الحقيبة ضمن المركز
      let trainers = (currentKit.trainers || []).filter(t => (t.authorization?.is_authorized !== false) && (!t.training_center_id || Number(t.training_center_id)===Number(centerId)) && (t.status==='active' || !t.status));
      if (!trainers.length && centerId) {
        const r = await fetch(`${BASE}/trainers?per_page=100&training_center_id=${centerId}&status=active&can_train=1`, { headers:H() });
        trainers = (await r.json()).data || [];
      }
      fill(trainerSel, trainers, t => `${t.name||''} (${t.trainer_code||t.id})`, trainers.length?'— اختر المدرب —':'لا يوجد مدرب متاح');
    } catch(err){ msg('تعذّر تحميل بيانات الحقيبة'); }
  });

  document.getElementById('createForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('saveBtn'); btn.disabled = true; msg('');
    const body = {
      training_center_id: Number(centerId),
      trainer_id: Number(document.getElementById('trainerId').value),
      training_kit_id: Number(document.getElementById('kitId').value),
      training_program_id: document.getElementById('programId').value ? Number(document.getElementById('programId').value) : null,
      title: document.getElementById('title').value.trim(),
      delivery_mode: document.getElementById('deliveryMode').value,
      status: document.getElementById('status').value,
      start_date: document.getElementById('startDate').value || null,
      end_date: document.getElementById('endDate').value || null,
      planned_hours: Number(document.getElementById('plannedHours').value),
      capacity: Number(document.getElementById('capacity').value),
      notes: document.getElementById('notes').value.trim() || null,
    };
    if (!body.training_center_id) { msg('تعذّر تحديد المركز'); btn.disabled=false; return; }
    try {
      const r = await fetch(`${BASE}/training-courses`, { method:'POST', headers:H(), body: JSON.stringify(body) });
      const j = await r.json().catch(()=>({}));
      if (!r.ok) throw new Error(j.message || (j.errors && Object.values(j.errors).flat()[0]) || 'فشل الحفظ');
      if (window.TC) TC.toast('تم إنشاء الدورة بنجاح', 'ok');
      msg('تم الحفظ — جاري فتح إدارة الدورة...', true);
      setTimeout(() => { location.href = 'center-course.php?id=' + (j.data?.id || ''); }, 700);
    } catch (err) { msg(err.message || 'تعذّر حفظ الدورة'); btn.disabled = false; }
  });
});
</script>
</body>
</html>
