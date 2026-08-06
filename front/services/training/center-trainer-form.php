<?php
/** إضافة / تعديل مدرب — نموذج بأسلوب الشاشات المرجعية. */
$basePath   = '../../';
$pageTitle  = 'بيانات المدرب';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php
  $tcActive = (!empty($_GET['id']) ? 'trainer-form' : 'trainer-create');
  include __DIR__ . '/_tc-sidebar.php';
?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-trainers.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl" id="barTitle">بيانات المدرب</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <form id="form" class="tc-form-card">
      <div class="fld"><label>الاسم الكامل *</label><input id="name" required></div>
      <div class="tc-form-grid two">
        <div class="fld"><label>رمز المدرب</label><input id="trainer_code" placeholder="يُولَّد تلقائياً إن تُرك فارغاً"></div>
        <div class="fld"><label>الحالة</label>
          <select id="status">
            <option value="active">نشط</option>
            <option value="pending">قيد الاعتماد</option>
            <option value="inactive">غير نشط</option>
            <option value="suspended">موقوف</option>
          </select>
        </div>
        <div class="fld"><label>الهاتف</label><input id="phone"></div>
        <div class="fld"><label>البريد</label><input id="email" type="email"></div>
        <div class="fld"><label>التخصص</label><input id="specialization"></div>
        <div class="fld"><label>التصنيف</label><input id="classification" placeholder="مثلاً: معتمد / مبتدئ"></div>
      </div>
      <div class="tc-form-grid two">
        <div class="fld"><label>شهادة TOT</label>
          <select id="has_tot"><option value="0">لا</option><option value="1">نعم</option></select>
        </div>
        <div class="fld"><label>رقم شهادة TOT</label><input id="tot_certificate_number"></div>
        <div class="fld"><label>مصدر الشهادة</label><input id="tot_certificate_source"></div>
        <div class="fld"><label>يمكنه التدريب</label>
          <select id="can_train"><option value="1">نعم</option><option value="0">لا</option></select>
        </div>
        <div class="fld"><label>تاريخ اعتماد من</label><input type="date" id="accreditation_start_date"></div>
        <div class="fld"><label>تاريخ اعتماد إلى</label><input type="date" id="accreditation_end_date"></div>
      </div>
      <div class="fld"><label>الحقائب المعتمدة للمدرب</label>
        <div id="kitsBox" class="tc-chips" style="max-height:180px;overflow:auto;border:1px solid var(--tc-line);border-radius:12px;padding:10px;background:#fafcfc"></div>
      </div>
      <div class="fld"><label>ملاحظات</label><textarea id="notes" rows="3"></textarea></div>
      <div style="text-align:center"><button class="tc-save" id="saveBtn" type="submit"><i class="bi bi-save2-fill"></i> حفظ</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const ID = new URLSearchParams(location.search).get('id');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!window.AppAuth.hasPermission('manage_trainers')) {
    document.getElementById('formMsg').className='tc-form-msg err';
    document.getElementById('formMsg').textContent='لا تملك صلاحية إدارة المدربين';
    document.getElementById('form').style.display='none';
    return;
  }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json', 'Content-Type':'application/json' });
  const msg = (t, ok=false)=>{ const el=document.getElementById('formMsg'); el.className='tc-form-msg '+(ok?'ok':'err'); el.textContent=t||''; };
  const val = id => document.getElementById(id).value;
  const set = (id,v)=>{ if(document.getElementById(id)) document.getElementById(id).value = v??''; };
  let allKits = [];
  let selected = new Set();

  function renderKits(){
    document.getElementById('kitsBox').innerHTML = allKits.map(k=>{
      const on = selected.has(Number(k.id));
      return `<button type="button" class="tc-chip ${on?'active':''}" data-id="${k.id}">${k.name||k.code||k.id}</button>`;
    }).join('') || '<span class="tc-muted">لا توجد حقائب متاحة</span>';
    document.querySelectorAll('#kitsBox .tc-chip').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = Number(btn.dataset.id);
        if (selected.has(id)) selected.delete(id); else selected.add(id);
        renderKits();
      });
    });
  }

  try{
    const kitsRes = await fetch(`${BASE}/training-kits?per_page=100&is_active=1`, { headers:H() });
    allKits = (await kitsRes.json()).data || [];
    renderKits();
  }catch(e){}

  if (ID) {
    document.getElementById('barTitle').textContent = 'تعديل المدرب';
    document.getElementById('back').href = 'center-trainer.php?id='+ID;
    try{
      const r = await fetch(`${BASE}/trainers/${ID}`, { headers:H() });
      if (!r.ok) throw new Error('x');
      const t = (await r.json()).data || {};
      set('name', t.name); set('trainer_code', t.trainer_code); set('phone', t.phone); set('email', t.email);
      set('specialization', t.specialization); set('classification', t.classification);
      set('status', t.status||'active'); set('has_tot', t.has_tot?1:0);
      set('tot_certificate_number', t.tot_certificate_number); set('tot_certificate_source', t.tot_certificate_source);
      set('can_train', t.can_train===false?0:1);
      set('accreditation_start_date', t.accreditation_start_date); set('accreditation_end_date', t.accreditation_end_date);
      set('notes', t.notes);
      (t.kits||[]).forEach(k => selected.add(Number(k.id)));
      renderKits();
    }catch(e){ msg('تعذّر تحميل بيانات المدرب'); }
  }

  document.getElementById('form').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const btn = document.getElementById('saveBtn'); btn.disabled=true; msg('');
    const user = window.AppAuth.getUser()||{};
    const body = {
      name: val('name').trim(),
      trainer_code: val('trainer_code').trim() || null,
      phone: val('phone').trim() || null,
      email: val('email').trim() || null,
      specialization: val('specialization').trim() || null,
      classification: val('classification').trim() || null,
      status: val('status'),
      has_tot: val('has_tot')==='1',
      tot_certificate_number: val('tot_certificate_number').trim() || null,
      tot_certificate_source: val('tot_certificate_source').trim() || null,
      can_train: val('can_train')==='1',
      accreditation_start_date: val('accreditation_start_date') || null,
      accreditation_end_date: val('accreditation_end_date') || null,
      notes: val('notes').trim() || null,
      kit_ids: [...selected],
    };
    if (user.training_center_id) body.training_center_id = Number(user.training_center_id);
    else {
      try{
        const cr = await fetch(`${BASE}/training-centers?per_page=5`, { headers:H() });
        const centers = (await cr.json()).data || [];
        if (centers[0]) body.training_center_id = centers[0].id;
      }catch(_){}
    }
    try{
      const url = ID ? `${BASE}/trainers/${ID}` : `${BASE}/trainers`;
      const method = ID ? 'PUT' : 'POST';
      const r = await fetch(url, { method, headers:H(), body: JSON.stringify(body) });
      const j = await r.json().catch(()=>({}));
      if (!r.ok) throw new Error(j.message || (j.errors && Object.values(j.errors).flat()[0]) || 'فشل الحفظ');
      const t = j.data || {};
      if (window.TC) TC.toast(ID?'تم التحديث':'تم إضافة المدرب', 'ok');
      msg('تم الحفظ بنجاح', true);
      setTimeout(()=> location.href = 'center-trainer.php?id='+(t.id||ID), 600);
    }catch(err){
      msg(err.message||'تعذّر الحفظ');
      btn.disabled=false;
    }
  });
});
</script>
</body>
</html>
