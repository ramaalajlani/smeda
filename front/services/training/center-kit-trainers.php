<?php
/** مدربو الحقيبة — عرض وتكليف. */
$basePath   = '../../';
$pageTitle  = 'مدربو الحقيبة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='kit-trainers'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-kits.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">مدربو الحقيبة<small id="barSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div id="box"><div class="tc-spin">جاري التحميل...</div></div>
    <div id="assignBox" style="margin-top:18px"></div>
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
  const E = TC.esc;
  const canManage = !!(window.AppAuth.hasPermission && window.AppAuth.hasPermission('manage_kits'));
  const STATUS = { active:'نشط', pending:'قيد الاعتماد', inactive:'غير نشط', suspended:'موقوف' };

  async function load(){
    const box = document.getElementById('box');
    box.innerHTML = '<div class="tc-spin">جاري التحميل...</div>';
    try{
      const k = (await (await fetch(`${BASE}/training-kits/${KIT_ID}`,{headers:H()})).json()).data || {};
      if (window.TC) TC.cacheKit(k);
      document.getElementById('barSub').textContent = [k.code, k.name].filter(Boolean).join(' — ');
      const trainers = k.trainers || [];
      if (!trainers.length) {
        box.innerHTML = '<div class="tc-empty">لا يوجد مدربون مكلّفون بهذه الحقيبة</div>';
      } else {
        box.innerHTML = `<div class="tc-mlist">${trainers.map((t,i)=>{
          const auth = t.authorization || {};
          const okAuth = auth.is_authorized !== false;
          return `<article class="tc-mcard">
            <div class="tc-mcard-top">
              <div>
                <h3 class="tc-mcard-title"><i class="bi bi-person-badge"></i> ${E(t.name||'—')}</h3>
                <div class="tc-mcard-sub">${E(t.trainer_code||'بدون رمز')} · ${E(STATUS[t.status]||t.status||'—')}</div>
              </div>
              <div class="tc-mcard-num">${i+1}</div>
            </div>
            <div class="tc-mcard-rows">
              <div class="tc-mcard-row"><span class="k">التخصص</span><span class="v">${E(t.specialization||'—')}</span></div>
              <div class="tc-mcard-row"><span class="k">التفويض</span><span class="v"><span class="tc-badge ${okAuth?'b-green':'b-red'}">${okAuth?'مفوَّض':'غير مفوَّض'}</span></span></div>
              <div class="tc-mcard-row"><span class="k">من</span><span class="v">${E(auth.authorized_from||'—')}</span></div>
              <div class="tc-mcard-row"><span class="k">إلى</span><span class="v">${E(auth.authorized_to||'—')}</span></div>
            </div>
            <div class="tc-mcard-acts">
              <a class="pdf" href="center-trainer.php?id=${t.id}"><i class="bi bi-eye"></i> ملف المدرب</a>
            </div>
          </article>`;
        }).join('')}</div>`;
      }
      if (canManage) await renderAssign(trainers.map(t=>t.id));
    }catch(e){
      box.innerHTML = '<div class="tc-empty">تعذّر تحميل مدربي الحقيبة</div>';
    }
  }

  async function renderAssign(selectedIds){
    const wrap = document.getElementById('assignBox');
    wrap.innerHTML = '<div class="tc-spin">جاري تحميل المدربين...</div>';
    try{
      const rows = (await (await fetch(`${BASE}/trainers?per_page=200`,{headers:H()})).json()).data || [];
      const selected = new Set((selectedIds||[]).map(Number));
      wrap.innerHTML = `<h3 style="margin:0 2px 10px;font-size:.95rem;color:var(--ref-ink)"><i class="bi bi-person-plus"></i> تكليف مدربين للحقيبة</h3>
        <div class="tc-mlist" id="poolList">${rows.map((t,i)=>`<article class="tc-mcard">
          <div class="tc-mcard-top">
            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;flex:1">
              <input type="checkbox" class="pick" value="${t.id}" ${selected.has(Number(t.id))?'checked':''} style="margin-top:4px;width:18px;height:18px">
              <span>
                <h3 class="tc-mcard-title">${E(t.name||'—')}</h3>
                <div class="tc-mcard-sub">${E(t.trainer_code||'بدون رمز')} · ${E(t.specialization||'—')}</div>
              </span>
            </label>
            <div class="tc-mcard-num">${i+1}</div>
          </div>
        </article>`).join('')||'<div class="tc-empty">لا يوجد مدربون</div>'}</div>
        <button type="button" class="tc-save" id="saveTrainers" style="margin-top:12px"><i class="bi bi-save2"></i> حفظ التكليف</button>`;
      document.getElementById('saveTrainers')?.addEventListener('click', async ()=>{
        const ids = [...document.querySelectorAll('#poolList .pick:checked')].map(c=>+c.value);
        const r = await fetch(`${BASE}/training-kits/${KIT_ID}`, {
          method:'PUT', headers:HP(), body:JSON.stringify({ trainer_ids: ids })
        });
        if (r.ok){ TC.toast('تم تحديث مدربي الحقيبة','ok'); load(); }
        else TC.toast('تعذّر الحفظ','err');
      });
    }catch(e){
      wrap.innerHTML = '<div class="tc-empty">تعذّر تحميل قائمة المدربين</div>';
    }
  }

  load();
});
</script>
</body>
</html>
