<?php
/** بطاقة متدرب (عرض) — معلوماته + دوراته + شهاداته. */
$basePath   = '../../';
$pageTitle  = 'بطاقة المتدرب';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='trainee'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" href="center-trainees-list.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">بطاقة المتدرب</div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content" id="box"><div class="tc-spin">جاري التحميل...</div></div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const TID = new URLSearchParams(location.search).get('trainee') || new URLSearchParams(location.search).get('id');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!TID){ location.href='center-trainees-list.php'; return; }
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = TC.esc;
  const canManage = window.AppAuth.hasPermission && window.AppAuth.hasPermission('manage_trainees');
  const gLbl = g => g==='male'?'ذكر':(g==='female'?'أنثى':'—');
  const RESULT = { passed:['ناجح','b-green'], failed:['راسب','b-red'], pending:['قيد التقييم','b-gold'], attendance_only:['حضور فقط','b-gray'] };
  const CST = { approved:['معتمدة','b-green'], issued:['صادرة','b-green'], pending:['قيد الاعتماد','b-gold'], rejected:['مرفوضة','b-red'], draft:['مسودة','b-gray'] };

  try {
    const t = (await (await fetch(`${BASE}/trainees/${TID}`, { headers:H() })).json()).data || {};
    const info = [
      ['الاسم الثلاثي', t.name], ['اسم الأم', t.mother_name], ['الرمز', t.trainee_code],
      ['الرقم الوطني', t.national_id], ['الهاتف', t.phone], ['البريد', t.email],
      ['الجنس', gLbl(t.gender)], ['تاريخ الميلاد', t.birth_date], ['الموقع', t.location],
      ['المؤهل', t.education_level], ['الحالة', t.status==='active'?'نشط':(t.status||'—')],
    ];
    const courses = t.courses || [];
    const certs = t.certificates || [];

    document.getElementById('box').innerHTML = `
      <div class="tc-form-card" style="text-align:center;margin-bottom:14px">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--ref-bar-2,#1c7d6a);color:#fff;display:grid;place-items:center;font-size:30px;font-weight:800;margin:0 auto 10px">${E((t.name||'م').trim()[0])}</div>
        <div style="font-weight:900;font-size:1.05rem">${E(t.name||'—')}</div>
        <div style="color:#7a8891;font-size:.82rem;margin-top:2px">${E(t.trainee_code||'')}</div>
        <div style="display:flex;gap:10px;justify-content:center;margin-top:12px;flex-wrap:wrap">
          <span class="tc-badge b-blue"><i class="bi bi-mortarboard"></i> ${courses.length} دورة</span>
          <span class="tc-badge b-green"><i class="bi bi-patch-check"></i> ${certs.length} شهادة</span>
        </div>
        ${canManage?`<div style="margin-top:12px"><a class="tc-item-btn" href="center-trainee-form.php?id=${TID}"><i class="bi bi-pencil-square"></i> تعديل البيانات</a></div>`:''}
      </div>

      <div class="tc-form-card" style="margin-bottom:14px">
        <div class="tc-mcard-rows">
          ${info.map(([k,v])=>`<div class="tc-mcard-row"><span class="k">${k}</span><span class="v">${E(v||'—')}</span></div>`).join('')}
        </div>
      </div>

      <h3 style="margin:6px 2px 10px;font-size:.95rem;color:var(--ref-bar-2,#0c4d40)"><i class="bi bi-mortarboard-fill"></i> الدورات</h3>
      <div id="box2">${courses.length?`<table class="tc-table">
        <thead><tr><th>#</th><th>الدورة</th><th>الرمز</th><th>الحالة</th></tr></thead>
        <tbody>${courses.map((c,i)=>`<tr><td>${i+1}</td><td class="tc-t-name">${E(c.title||'—')}</td><td>${E(c.course_code||'—')}</td><td>${E(c.status||'—')}</td></tr>`).join('')}</tbody>
      </table>`:'<div class="tc-empty">لا دورات</div>'}</div>

      <h3 style="margin:18px 2px 10px;font-size:.95rem;color:var(--ref-bar-2,#0c4d40)"><i class="bi bi-patch-check-fill"></i> الشهادات</h3>
      <div>${certs.length?`<table class="tc-table">
        <thead><tr><th>#</th><th>رقم الشهادة</th><th>النتيجة</th><th>الحالة</th></tr></thead>
        <tbody>${certs.map((c,i)=>{const [sl,sc]=CST[c.status]||[c.status||'—','b-gray'];return `<tr><td>${i+1}</td><td class="tc-t-name">${E(c.certificate_number||c.reference_number||('#'+c.id))}</td><td>${E(c.result||'—')}</td><td><span class="tc-badge ${sc}">${sl}</span></td></tr>`;}).join('')}</tbody>
      </table>`:'<div class="tc-empty">لا شهادات</div>'}</div>`;
  } catch(e){ document.getElementById('box').innerHTML='<div class="tc-empty">تعذّر تحميل بطاقة المتدرب</div>'; }
});
</script>
</body>
</html>
