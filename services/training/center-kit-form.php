<?php
/** إضافة / تعديل حقيبة تدريبية. */
$basePath   = '../../';
$pageTitle  = 'بيانات الحقيبة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php
  $tcActive = (!empty($_GET['id']) ? 'kit-form' : 'kit-create-form');
  include __DIR__ . '/_tc-sidebar.php';
?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-kits.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl" id="barTitle">بيانات الحقيبة</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <form id="form" class="tc-form-card">
      <div class="fld"><label>اسم الحقيبة *</label><input id="name" required></div>
      <div class="tc-form-grid two">
        <div class="fld"><label>الرمز</label><input id="code" placeholder="يُولَّد تلقائياً إن تُرك فارغاً"></div>
        <div class="fld"><label>الحالة</label>
          <select id="status">
            <option value="active">نشطة</option>
            <option value="inactive">غير نشطة</option>
            <option value="archived">مؤرشفة</option>
          </select>
        </div>
        <div class="fld"><label>القطاع</label><input id="sector"></div>
        <div class="fld"><label>التصنيف *</label>
          <select id="category_id"><option value="">— اختر التصنيف —</option></select>
        </div>
        <div class="fld"><label>التخصص / التصنيف الفرعي</label>
          <select id="subcategory_id"><option value="">—</option></select>
        </div>
        <div class="fld"><label>المستوى</label><input id="level"></div>
        <div class="fld"><label>رمز المادة</label><input id="material_code"></div>
        <div class="fld"><label>الساعات</label><input id="hours" type="number" min="0" value="0"></div>
        <div class="fld"><label>مفعّلة</label>
          <select id="is_active"><option value="1">نعم</option><option value="0">لا</option></select>
        </div>
      </div>
      <div class="fld"><label>الهدف</label><textarea id="objective" rows="2"></textarea></div>
      <div class="fld"><label>الوصف</label><textarea id="description" rows="3"></textarea></div>
      <div id="attachmentsBlock" class="fld" style="display:none">
        <label>المرفقات</label>
        <p style="margin:0;color:#64748b;font-size:.9rem">يمكن رفع عدة ملفات (PDF، Word، PowerPoint، Excel، صور) من صفحة المرفقات.</p>
        <a id="attachmentsLink" href="#" class="tc-save" style="display:inline-flex;margin-top:10px;text-decoration:none;font-size:.9rem;padding:8px 14px"><i class="bi bi-paperclip"></i> إدارة مرفقات الحقيبة</a>
      </div>
      <div id="legacyFilesBlock">
        <div class="fld"><label>ملف الحقيبة (PDF)</label><input type="file" id="training_bag_file" accept=".pdf,application/pdf"><small class="text-muted">PDF فقط — محمي (ملف واحد — للمرفقات المتعددة استخدم صفحة المرفقات بعد الحفظ)</small></div>
        <div class="fld"><label>ملف ترويجي (اختياري)</label><input type="file" id="promotional_file" accept=".pdf,.doc,.docx,.ppt,.pptx"></div>
      </div>
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
  if (!window.AppAuth.hasPermission('manage_kits')) {
    document.getElementById('formMsg').className='tc-form-msg err';
    document.getElementById('formMsg').textContent='لا تملك صلاحية إدارة الحقائب';
    document.getElementById('form').style.display='none';
    return;
  }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json', 'Content-Type':'application/json' });
  const msg = (t, ok=false)=>{ const el=document.getElementById('formMsg'); el.className='tc-form-msg '+(ok?'ok':'err'); el.textContent=t||''; };
  const val = id => document.getElementById(id).value;
  const set = (id,v)=>{ if(document.getElementById(id)) document.getElementById(id).value = v??''; };
  let categoriesTree = [];

  async function loadCategories() {
    try {
      const res = await window.APP_API.get(window.APP_ROUTES.trainingCategories({ roots_only: 1, with_children: 1, active_only: 1 }));
      categoriesTree = res.data || [];
      const catSel = document.getElementById('category_id');
      if (!catSel) return;
      catSel.innerHTML = '<option value="">— اختر التصنيف —</option>' + categoriesTree.map(c =>
        `<option value="${c.id}">${String(c.name_ar||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]))}</option>`
      ).join('');
    } catch (e) {}
  }

  function fillSubcategories(parentId, selected) {
    const subSel = document.getElementById('subcategory_id');
    if (!subSel) return;
    const parent = categoriesTree.find(c => String(c.id) === String(parentId));
    const children = parent?.active_children || parent?.children || [];
    subSel.innerHTML = '<option value="">—</option>' + children.map(c =>
      `<option value="${c.id}">${String(c.name_ar||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]))}</option>`
    ).join('');
    if (selected) subSel.value = String(selected);
  }

  document.getElementById('category_id')?.addEventListener('change', e => fillSubcategories(e.target.value));
  await loadCategories();

  if (ID) {
    document.getElementById('barTitle').textContent = 'تعديل الحقيبة';
    document.getElementById('back').href = 'center-kit.php?id='+ID;
    document.getElementById('attachmentsBlock').style.display='block';
    document.getElementById('attachmentsLink').href = 'center-kit-attachments.php?kit='+ID;
    document.getElementById('legacyFilesBlock').style.display='none';
    try{
      const r = await fetch(`${BASE}/training-kits/${ID}`, { headers:H() });
      if (!r.ok) throw new Error('x');
      const k = (await r.json()).data || {};
      set('name', k.name); set('code', k.code); set('sector', k.sector);
      set('level', k.level); set('material_code', k.material_code);
      if (k.category_id) {
        set('category_id', k.category_id);
        fillSubcategories(k.category_id, k.subcategory_id);
      } else if (k.category) {
        const match = categoriesTree.find(c => c.name_ar === k.category);
        if (match) {
          set('category_id', match.id);
          fillSubcategories(match.id, k.subcategory_id);
        }
      }
      set('hours', k.hours ?? 0); set('status', k.status||'active');
      set('is_active', k.is_active===false?0:1);
      set('objective', k.objective); set('description', k.description);
    }catch(e){ msg('تعذّر تحميل بيانات الحقيبة'); }
  }

  document.getElementById('form').addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!val('category_id')) { msg('يرجى اختيار تصنيف الحقيبة (تعليمي، مهني، …)'); return; }
    const btn = document.getElementById('saveBtn'); btn.disabled=true; msg('');
    const bagFile = document.getElementById('training_bag_file')?.files?.[0];
    const promoFile = document.getElementById('promotional_file')?.files?.[0];
    const useMultipart = !!(bagFile || promoFile);

    const payload = {
      name: val('name').trim(),
      code: val('code').trim() || null,
      sector: val('sector').trim() || null,
      category_id: val('category_id') ? Number(val('category_id')) : null,
      subcategory_id: val('subcategory_id') ? Number(val('subcategory_id')) : null,
      level: val('level').trim() || null,
      material_code: val('material_code').trim() || null,
      hours: Number(val('hours')||0),
      status: val('status'),
      is_active: val('is_active')==='1',
      objective: val('objective').trim() || null,
      description: val('description').trim() || null,
    };

    try{
      const url = ID ? `${BASE}/training-kits/${ID}` : `${BASE}/training-kits`;
      let r;
      if (useMultipart) {
        const fd = new FormData();
        Object.entries(payload).forEach(([k,v]) => { if (v !== null && v !== undefined) fd.append(k, typeof v === 'boolean' ? (v ? '1' : '0') : String(v)); });
        if (bagFile) fd.append('training_bag_file', bagFile);
        if (promoFile) fd.append('promotional_file', promoFile);
        if (ID) fd.append('_method', 'PUT');
        r = await fetch(url, { method: 'POST', headers: { Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' }, body: fd });
      } else {
        r = await fetch(url, { method: ID ? 'PUT' : 'POST', headers: H(), body: JSON.stringify(payload) });
      }
      const j = await r.json().catch(()=>({}));
      if (!r.ok) throw new Error(j.message || (j.errors && Object.values(j.errors).flat()[0]) || 'فشل الحفظ');
      const k = j.data || {};
      if (window.TC) TC.toast(ID?'تم التحديث':'تم إضافة الحقيبة', 'ok');
      msg('تم الحفظ بنجاح', true);
      setTimeout(()=> location.href = 'center-kit.php?id='+(k.id||ID), 600);
    }catch(err){
      msg(err.message||'تعذّر الحفظ');
      btn.disabled=false;
    }
  });
});
</script>
</body>
</html>
