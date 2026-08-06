<?php
/** مواد الحقيبة (وحدات/محاور داخل الحقيبة) — إضافة/حذف. */
$basePath   = '../../';
$pageTitle  = 'مواد الحقيبة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='kit-materials'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">مواد الحقيبة<small id="hSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <form id="addForm" class="tc-form-card">
      <div class="fld"><label>عنوان المادة</label><input id="mTitle" required placeholder="مثال: الوحدة الأولى — المقدمة"></div>
      <div class="tc-form-grid two">
        <div class="fld"><label>الساعات (اختياري)</label><input id="mHours" type="number" min="0" placeholder="—"></div>
        <div class="fld"><label>طريقة التقييم (اختياري)</label><input id="mEval" placeholder="اختبار عملي / نظري"></div>
      </div>
      <div class="fld"><label>الأهداف (اختياري)</label><textarea id="mObj" rows="2"></textarea></div>
      <div style="text-align:center"><button type="submit" class="tc-save" id="addBtn"><i class="bi bi-plus-circle"></i> إضافة مادة</button></div>
    </form>
    <div id="list"><div class="tc-spin">جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const KIT_ID = new URLSearchParams(location.search).get('kit') || new URLSearchParams(location.search).get('id');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!KIT_ID){ location.href='center-kits.php'; return; }
  document.getElementById('back').href = 'center-kit.php?id='+KIT_ID;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const HP = () => ({ ...H(), 'Content-Type':'application/json' });
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const jget = async p => (await fetch(`${BASE}${p}`,{headers:H()})).json();

  const canManage = !window.AppAuth.hasPermission || window.AppAuth.hasPermission('manage_kits');
  if (!canManage) document.getElementById('addForm').style.display='none';

  async function load(){
    const box = document.getElementById('list'); box.innerHTML='<div class="tc-spin">جاري التحميل...</div>';
    try{
      const d = await jget(`/training-kits/${KIT_ID}/materials`);
      document.getElementById('hSub').textContent = d.meta?.kit_name || '';
      const rows = d.data||[];
      if(!rows.length){ box.innerHTML='<div class="tc-empty">لا توجد مواد بعد — أضف مادة بالأعلى</div>'; return; }
      box.innerHTML = `<table class="tc-table">
        <thead><tr><th>#</th><th>المادة</th><th>الساعات</th><th>التقييم</th>${canManage?'<th></th>':''}</tr></thead>
        <tbody>${rows.map((m,i)=>`<tr>
          <td>${i+1}</td><td class="tc-t-name">${E(m.title)}</td><td>${m.hours!=null?E(m.hours):'—'}</td><td>${E(m.evaluation_method||'—')}</td>
          ${canManage?`<td><button type="button" class="tc-item-btn" style="background:#fdecec;color:#b91c1c" onclick="delMat(${m.id},'${E(m.title)}')"><i class="bi bi-trash"></i></button></td>`:''}
        </tr>`).join('')}</tbody></table>`;
    }catch(e){ box.innerHTML='<div class="tc-empty">تعذّر تحميل المواد</div>'; }
  }

  window.delMat = async (mid, title) => {
    if (!(await TC.confirm(`حذف المادة «${title}»؟`))) return;
    const r = await fetch(`${BASE}/training-kits/${KIT_ID}/materials/${mid}`, { method:'DELETE', headers:H() });
    TC.toast(r.ok?'تم حذف المادة':'تعذّر الحذف', r.ok?'ok':'err'); if(r.ok) load();
  };

  document.getElementById('addForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const title = document.getElementById('mTitle').value.trim(); if(!title){ TC.toast('أدخل عنوان المادة','err'); return; }
    const body = {
      title,
      hours: document.getElementById('mHours').value ? Number(document.getElementById('mHours').value) : null,
      evaluation_method: document.getElementById('mEval').value.trim() || null,
      objectives: document.getElementById('mObj').value.trim() || null,
    };
    const btn = document.getElementById('addBtn'); btn.disabled=true;
    const r = await fetch(`${BASE}/training-kits/${KIT_ID}/materials`, { method:'POST', headers:HP(), body:JSON.stringify(body) });
    btn.disabled=false;
    if(r.ok){ document.getElementById('addForm').reset(); TC.toast('تمت إضافة المادة','ok'); load(); }
    else TC.toast('تعذّرت الإضافة','err');
  });

  load();
});
</script>
</body>
</html>
