<?php
/** مرفقات الحقيبة — رفع وتحميل ملفات متعددة لكل حقيبة. */
$basePath   = '../../';
$pageTitle  = 'مرفقات الحقيبة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
<style>
.tc-att-list { margin-top: 16px; }
.tc-att-item {
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
  padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 12px;
  margin-bottom: 8px; background: #fff;
}
.tc-att-item .name { flex: 1; min-width: 140px; font-weight: 600; color: #0f172a; }
.tc-att-item .meta { font-size: .82rem; color: #64748b; }
.tc-att-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.tc-att-actions button, .tc-att-actions a {
  border: none; border-radius: 8px; padding: 6px 10px; font-size: .85rem;
  cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
}
.tc-att-dl { background: #eff6ff; color: #1d4ed8; }
.tc-att-del { background: #fdecec; color: #b91c1c; }
.tc-att-hint { font-size: .85rem; color: #64748b; margin-top: 6px; line-height: 1.5; }
</style>
</head>
<body>
<?php $tcActive='kit-attachments'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">مرفقات الحقيبة<small id="hSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="formMsg" class="tc-form-msg"></div>
    <form id="uploadForm" class="tc-form-card">
      <div class="fld">
        <label>رفع مرفقات (يمكن اختيار عدة ملفات)</label>
        <input type="file" id="files" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webp">
        <p class="tc-att-hint">PDF، Word، PowerPoint، Excel، أو صور — حتى 25 MB لكل ملف — بحد أقصى 30 مرفقاً للحقيبة.</p>
      </div>
      <div class="fld"><label>عنوان اختياري (للملف الأول)</label><input id="title" placeholder="مثال: دليل المدرب"></div>
      <div style="text-align:center"><button type="submit" class="tc-save" id="uploadBtn"><i class="bi bi-cloud-upload"></i> رفع المرفقات</button></div>
    </form>
    <div id="list" class="tc-att-list"><div class="tc-spin">جاري التحميل...</div></div>
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
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const fmtSize = n => {
    const b = Number(n||0);
    if (b >= 1048576) return (b/1048576).toFixed(1)+' MB';
    if (b >= 1024) return Math.round(b/1024)+' KB';
    return b+' B';
  };

  const canManage = !window.AppAuth.hasPermission || window.AppAuth.hasPermission('manage_kits');
  if (!canManage) document.getElementById('uploadForm').style.display='none';

  async function load(){
    const box = document.getElementById('list'); box.innerHTML='<div class="tc-spin">جاري التحميل...</div>';
    try{
      const r = await fetch(`${BASE}/training-kits/${KIT_ID}/attachments`, { headers:H() });
      const d = await r.json();
      if (!r.ok) throw new Error(d.message||'x');
      document.getElementById('hSub').textContent = d.meta?.kit_name ? ' — '+d.meta.kit_name : '';
      const rows = d.data||[];
      const max = d.meta?.max_allowed ?? 30;
      const count = d.meta?.count ?? rows.length;
      if(!rows.length){
        box.innerHTML='<div class="tc-empty">لا توجد مرفقات بعد — ارفع ملفات بالأعلى</div>';
        return;
      }
      box.innerHTML = `<p class="tc-att-hint" style="margin-bottom:10px">${count} / ${max} مرفق</p>` +
        rows.map(a => `<div class="tc-att-item">
          <div class="name">${E(a.display_name||a.original_name)}</div>
          <div class="meta">${E(a.mime||'—')} · ${fmtSize(a.size)}</div>
          <div class="tc-att-actions">
            <a class="tc-att-dl" href="#" data-id="${a.id}" onclick="return dlAtt(${a.id})"><i class="bi bi-download"></i> تحميل</a>
            ${canManage?`<button type="button" class="tc-att-del" onclick="delAtt(${a.id},'${E(a.display_name||a.original_name)}')"><i class="bi bi-trash"></i> حذف</button>`:''}
          </div>
        </div>`).join('');
    }catch(e){ box.innerHTML='<div class="tc-empty">تعذّر تحميل المرفقات</div>'; }
  }

  window.dlAtt = async (id) => {
    try {
      const r = await fetch(`${BASE}/training-kits/${KIT_ID}/attachments/${id}/download`, { headers:H() });
      if (!r.ok) { TC.toast('تعذّر تحميل الملف','err'); return false; }
      const blob = await r.blob();
      const cd = r.headers.get('Content-Disposition')||'';
      const m = cd.match(/filename="?([^";]+)"?/i);
      const name = m ? decodeURIComponent(m[1]) : 'attachment';
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a'); a.href=url; a.download=name; a.click();
      URL.revokeObjectURL(url);
    } catch(e) { TC.toast('تعذّر تحميل الملف','err'); }
    return false;
  };

  window.delAtt = async (id, title) => {
    if (!(await TC.confirm(`حذف المرفق «${title}»؟`))) return;
    const r = await fetch(`${BASE}/training-kits/${KIT_ID}/attachments/${id}`, { method:'DELETE', headers:H() });
    TC.toast(r.ok?'تم حذف المرفق':'تعذّر الحذف', r.ok?'ok':'err'); if(r.ok) load();
  };

  document.getElementById('uploadForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const input = document.getElementById('files');
    const files = input.files;
    if (!files || !files.length) { TC.toast('اختر ملفاً واحداً على الأقل','err'); return; }
    const btn = document.getElementById('uploadBtn'); btn.disabled=true;
    const fd = new FormData();
    for (let i=0; i<files.length; i++) fd.append('files[]', files[i]);
    const t = document.getElementById('title').value.trim();
    if (t) fd.append('title', t);
    try {
      const r = await fetch(`${BASE}/training-kits/${KIT_ID}/attachments`, {
        method:'POST',
        headers:{ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' },
        body: fd
      });
      const d = await r.json().catch(()=>({}));
      if (!r.ok) { TC.toast(d.message||'تعذّر الرفع','err'); return; }
      TC.toast(d.message||'تم الرفع','ok');
      input.value=''; document.getElementById('title').value='';
      load();
    } catch(err) { TC.toast('تعذّر الرفع','err'); }
    finally { btn.disabled=false; }
  });

  load();
});
</script>
</body>
</html>
