<?php
/** إضافة / تعديل متدرب. */
$basePath   = '../../';
$pageTitle  = 'بيانات المتدرب';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php
  $tcActive = (!empty($_GET['id']) ? 'trainee-form' : 'trainee-create');
  include __DIR__ . '/_tc-sidebar.php';
?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-trainees-list.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl" id="barTitle">بيانات المتدرب</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <form id="form" class="tc-form-card">
      <div class="fld"><label>الاسم الثلاثي *</label><input id="name" required placeholder="الاسم الأول والأب والجد/العائلة"></div>
      <div class="fld"><label>اسم الأم</label><input id="mother_name" placeholder="اسم الأم"></div>
      <div class="tc-form-grid two">
        <div class="fld"><label>رمز المتدرب</label><input id="trainee_code" placeholder="يُولَّد تلقائياً إن تُرك فارغاً"></div>
        <div class="fld"><label>الحالة</label>
          <select id="status">
            <option value="active">نشط</option>
            <option value="inactive">غير نشط</option>
            <option value="blocked">محظور</option>
          </select>
        </div>
        <div class="fld"><label>الرقم الوطني</label><input id="national_id"></div>
        <div class="fld"><label>الهاتف</label><input id="phone"></div>
        <div class="fld"><label>البريد</label><input id="email" type="email"></div>
        <div class="fld"><label>المدينة</label><input id="city"></div>
        <div class="fld"><label>الجنس</label>
          <select id="gender"><option value="">—</option><option value="male">ذكر</option><option value="female">أنثى</option></select>
        </div>
        <div class="fld"><label>تاريخ الميلاد</label><input type="date" id="birth_date"></div>
        <div class="fld"><label>المستوى التعليمي</label><input id="education_level"></div>
        <div class="fld"><label>تسجيل في دورة (اختياري)</label>
          <select id="training_course_id"><option value="">بدون دورة الآن</option></select>
        </div>
      </div>
      <div class="fld"><label>العنوان</label><input id="address"></div>
      <div class="fld"><label>ملاحظات</label><textarea id="notes" rows="3"></textarea></div>
      <div style="text-align:center"><button class="tc-save" id="saveBtn" type="submit"><i class="bi bi-save2-fill"></i> حفظ</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const ID = new URLSearchParams(location.search).get('id');
const COURSE_PRE = new URLSearchParams(location.search).get('course');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!window.AppAuth.hasPermission('manage_trainees')) {
    document.getElementById('formMsg').className='tc-form-msg err';
    document.getElementById('formMsg').textContent='لا تملك صلاحية إدارة المتدربين';
    document.getElementById('form').style.display='none';
    return;
  }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json', 'Content-Type':'application/json' });
  const msg = (t, ok=false)=>{ const el=document.getElementById('formMsg'); el.className='tc-form-msg '+(ok?'ok':'err'); el.textContent=t||''; };
  const val = id => document.getElementById(id).value;
  const set = (id,v)=>{ if(document.getElementById(id)) document.getElementById(id).value = v??''; };
  const stripMarker = (n) => String(n||'').replace(/^\[center:\d+\]\s*/, '');

  try{
    const cr = await fetch(`${BASE}/training-courses?per_page=100`, { headers:H() });
    const courses = (await cr.json()).data || [];
    const sel = document.getElementById('training_course_id');
    courses.forEach(c=>{
      const o=document.createElement('option');
      o.value=c.id; o.textContent=[c.course_code,c.title].filter(Boolean).join(' — ')||('دورة #'+c.id);
      sel.appendChild(o);
    });
    if (COURSE_PRE) sel.value = COURSE_PRE;
  }catch(e){}

  if (ID) {
    document.getElementById('barTitle').textContent = 'تعديل المتدرب';
    try{
      const r = await fetch(`${BASE}/trainees/${ID}`, { headers:H() });
      if (!r.ok) throw new Error('x');
      const t = (await r.json()).data || {};
      set('name', t.name); set('mother_name', t.mother_name); set('trainee_code', t.trainee_code); set('national_id', t.national_id);
      set('phone', t.phone); set('email', t.email); set('city', t.city); set('address', t.address);
      set('birth_date', t.birth_date); set('gender', t.gender||''); set('education_level', t.education_level);
      set('status', t.status||'active'); set('notes', stripMarker(t.notes));
    }catch(e){ msg('تعذّر تحميل بيانات المتدرب'); }
  }

  document.getElementById('form').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const btn = document.getElementById('saveBtn'); btn.disabled=true; msg('');
    const body = {
      name: val('name').trim(),
      mother_name: val('mother_name').trim() || null,
      trainee_code: val('trainee_code').trim() || null,
      national_id: val('national_id').trim() || null,
      phone: val('phone').trim() || null,
      email: val('email').trim() || null,
      city: val('city').trim() || null,
      address: val('address').trim() || null,
      birth_date: val('birth_date') || null,
      gender: val('gender') || null,
      education_level: val('education_level').trim() || null,
      status: val('status'),
      notes: val('notes').trim() || null,
    };
    const courseId = val('training_course_id');
    if (courseId) body.training_course_id = Number(courseId);
    try{
      const url = ID ? `${BASE}/trainees/${ID}` : `${BASE}/trainees`;
      const method = ID ? 'PUT' : 'POST';
      const r = await fetch(url, { method, headers:H(), body: JSON.stringify(body) });
      const j = await r.json().catch(()=>({}));
      if (!r.ok) throw new Error(j.message || (j.errors && Object.values(j.errors).flat()[0]) || 'فشل الحفظ');
      if (window.TC) TC.toast(ID?'تم التحديث':'تم إضافة المتدرب', 'ok');
      msg('تم الحفظ بنجاح', true);
      setTimeout(()=> location.href = 'center-trainees-list.php', 600);
    }catch(err){
      msg(err.message||'تعذّر الحفظ');
      btn.disabled=false;
    }
  });
});
</script>
</body>
</html>
