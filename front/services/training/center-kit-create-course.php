<?php
/** إنشاء دورة من حقيبة — نموذج إدخال بأسلوب تطبيق المركز. */
$basePath   = '../../';
$pageTitle  = 'إنشاء دورة من حقيبة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='kit-create'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="backKit" href="center-kits.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">إنشاء دورة</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <form id="createForm" class="tc-form-card">
      <div class="fld"><label>الحقيبة</label><input id="kitName" type="text" readonly></div>
      <div class="fld"><label>المركز</label><select id="centerId" required></select></div>
      <div class="fld"><label>المدرب المعتمد للحقيبة</label><select id="trainerId" required></select></div>
      <div class="fld"><label>البرنامج (اختياري)</label><select id="programId"><option value="">— بدون —</option></select></div>
      <div class="fld"><label>عنوان الدورة</label><input id="title" required placeholder="عنوان الدورة"></div>
      <div class="tc-form-grid two">
        <div class="fld"><label>نوع التنفيذ</label><select id="deliveryMode"><option value="offline">حضوري</option><option value="online">أونلاين</option></select></div>
        <div class="fld"><label>الحالة</label><select id="status"><option value="scheduled">مجدولة</option><option value="draft">مسودة</option><option value="ongoing">جارية</option></select></div>
        <div class="fld"><label>تاريخ البدء</label><input type="date" id="startDate"></div>
        <div class="fld"><label>تاريخ الانتهاء</label><input type="date" id="endDate"></div>
        <div class="fld"><label>الساعات المخططة</label><input type="number" min="1" id="plannedHours" required></div>
        <div class="fld"><label>السعة</label><input type="number" min="1" id="capacity" value="20" required></div>
      </div>
      <div class="fld"><label>ملاحظات</label><textarea id="notes" rows="3"></textarea></div>
      <div style="text-align:center"><button type="submit" class="tc-save" id="saveBtn"><i class="bi bi-save2-fill"></i> حفظ</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const KIT_ID = new URLSearchParams(location.search).get('kit');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!KIT_ID){ location.href='center-kits.php'; return; }
  if (!window.AppAuth.hasPermission('manage_courses')) {
    document.getElementById('formMsg').className='tc-form-msg err';
    document.getElementById('formMsg').textContent='لا تملك صلاحية إنشاء الدورات';
    document.getElementById('createForm').style.display='none';
    return;
  }
  document.getElementById('backKit').href = 'center-kit.php?id='+KIT_ID;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json', 'Content-Type':'application/json' });
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const msg = (t, ok=false) => { const el=document.getElementById('formMsg'); el.className='tc-form-msg '+(ok?'ok':'err'); el.textContent=t||''; };
  const fill = (sel, rows, labelFn, placeholder) => {
    sel.innerHTML = `<option value="">${placeholder||'— اختر —'}</option>` +
      rows.map(r => `<option value="${r.id}">${E(labelFn(r))}</option>`).join('');
  };

  let kit = null;
  try {
    const [kitRes, centersRes] = await Promise.all([
      fetch(`${BASE}/training-kits/${KIT_ID}`, { headers:H() }),
      fetch(`${BASE}/training-centers?per_page=50`, { headers:H() }),
    ]);
    if (!kitRes.ok) throw new Error('kit');
    kit = (await kitRes.json()).data || {};
    if (window.TC) TC.cacheKit(kit);
    document.querySelector('.tc-bar .ttl').innerHTML = `إنشاء دورة<small>${E(kit.name||'')}</small>`;
    document.getElementById('kitName').value = `${kit.name||''} (${kit.code||''})`;
    document.getElementById('title').value = kit.name || '';
    document.getElementById('plannedHours').value = kit.hours || 20;
    const programs = kit.programs || [];
    fill(document.getElementById('programId'), programs, p => `${p.name||''} (${p.code||p.id})`, '— بدون برنامج —');

    const centers = ((await centersRes.json()).data || []).filter(c => c.is_active !== false);
    const user = window.AppAuth.getUser() || {};
    fill(document.getElementById('centerId'), centers, c => `${c.name||''} (${c.code||c.id})`);
    if (user.training_center_id) {
      document.getElementById('centerId').value = String(user.training_center_id);
    } else if (centers.length === 1) {
      document.getElementById('centerId').value = String(centers[0].id);
    }
  } catch (e) {
    msg('تعذّر تحميل بيانات الحقيبة/المركز');
    return;
  }

  async function loadTrainers() {
    const centerId = document.getElementById('centerId').value;
    const trainerSel = document.getElementById('trainerId');
    if (!centerId) { fill(trainerSel, [], ()=>'', 'اختر المركز أولاً'); return; }
    try {
      // المدربون المعتمدون على الحقيبة (من تفاصيل الحقيبة) ضمن نفس المركز
      let trainers = (kit.trainers || []).filter(t => {
        const authOk = t.authorization?.is_authorized !== false;
        const centerOk = !t.training_center_id || Number(t.training_center_id) === Number(centerId);
        return authOk && centerOk && (t.status === 'active' || !t.status);
      });
      if (!trainers.length) {
        const r = await fetch(`${BASE}/trainers?per_page=100&training_center_id=${centerId}&status=active&can_train=1`, { headers:H() });
        trainers = (await r.json()).data || [];
      }
      fill(trainerSel, trainers, t => `${t.name||''} (${t.trainer_code||t.id})`, trainers.length?'— اختر المدرب —':'لا يوجد مدرب متاح');
    } catch (e) {
      fill(trainerSel, [], ()=>'', 'تعذّر تحميل المدربين');
    }
  }

  document.getElementById('centerId').addEventListener('change', loadTrainers);
  await loadTrainers();

  document.getElementById('createForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    msg('');
    const body = {
      training_center_id: Number(document.getElementById('centerId').value),
      trainer_id: Number(document.getElementById('trainerId').value),
      training_kit_id: Number(KIT_ID),
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
    try {
      const r = await fetch(`${BASE}/training-courses`, { method:'POST', headers:H(), body: JSON.stringify(body) });
      const j = await r.json().catch(()=>({}));
      if (!r.ok) throw new Error(j.message || (j.errors && Object.values(j.errors).flat()[0]) || 'فشل الحفظ');
      const course = j.data || {};
      if (window.TC) TC.toast('تم حفظ الدورة بنجاح', 'ok');
      msg('تم الحفظ — جاري فتح إدارة الدورة...', true);
      setTimeout(() => { location.href = 'center-course.php?id=' + course.id; }, 700);
    } catch (err) {
      msg(err.message || 'تعذّر حفظ الدورة');
      btn.disabled = false;
    }
  });
});
</script>
</body>
</html>
