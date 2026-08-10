<?php
/** الشهادات — ملخص قبل الإصدار + حالة فارغة موجّهة. */
$basePath   = '../../';
$pageTitle  = 'الشهادات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
</head>
<body>
<?php $tcActive='certificates'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-phone">
  <div class="tc-bar">
    <button type="button" class="ic tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
    <a class="ic" id="back" href="center-app.php" aria-label="رجوع"><i class="bi bi-arrow-right"></i></a>
    <div class="ttl">الشهادات<small id="hSub"></small></div>
    <button type="button" class="ic" onclick="location.reload()" aria-label="تحديث"><i class="bi bi-arrow-clockwise"></i></button>
  </div>
  <div class="tc-content">
    <div class="tc-scope is-course" style="margin-bottom:12px">
      <div class="tc-scope-txt"><i class="bi bi-patch-check"></i> الشهادات على مستوى <strong>كل الدورة</strong> للناجحين فقط</div>
    </div>
    <div id="sumBox" class="tc-sum" hidden></div>
    <div class="tc-bar-row" style="justify-content:stretch">
      <button type="button" id="issue" class="tc-btn-teal" style="width:100%" disabled><i class="bi bi-patch-check-fill"></i> إصدار الشهادات للناجحين</button>
    </div>
    <div id="hint" class="tc-muted" style="margin:0 4px 12px;font-size:.82rem"></div>
    <div id="box"><div class="tc-spin"><i class="bi bi-hourglass-split"></i> جاري التحميل...</div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>services/training/tc-common.js?v=1.4"></script>
<script>
const COURSE_ID = new URLSearchParams(location.search).get('course');
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true }); if (!ok) return;
  if (!COURSE_ID){ location.href='center-app.php'; return; }
  document.getElementById('back').href = 'center-course.php?id='+COURSE_ID;

  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const HP = () => ({ ...H(), 'Content-Type':'application/json' });
  const E = TC.esc;
  const jget = async p => (await fetch(`${BASE}${p}`,{headers:H()})).json();

  const CERT_ST = {
    approved:['معتمدة','b-green'],
    issued:['صادرة','b-green'],
    pending:['قيد الاعتماد','b-gold'],
    pending_center_approval:['بانتظار اعتماد المركز','b-gold'],
    pending_training_approval:['بانتظار اعتماد التدريب','b-gold'],
    pending_deputy_approval:['بانتظار اعتماد النائب','b-gold'],
    pending_general_director_approval:['بانتظار اعتماد المدير العام','b-gold'],
    rejected:['مرفوضة','b-red'],
    draft:['مسودة','b-gray'],
  };

  const cached = TC.getCourse(COURSE_ID);
  if (cached) document.getElementById('hSub').textContent = [cached.code, cached.title].filter(Boolean).join(' — ');

  document.getElementById('issue').addEventListener('click', issue);
  if (!window.AppAuth.hasPermission('issue_certificates')) {
    document.getElementById('issue').closest('.tc-bar-row')?.setAttribute('hidden', '');
  }
  load();

  async function load(){
    const box = document.getElementById('box');
    const issueBtn = document.getElementById('issue');
    box.innerHTML = '<div class="tc-spin"><i class="bi bi-hourglass-split"></i> جاري التحميل...</div>';
    try{
      const course = (await jget(`/training-courses/${COURSE_ID}?include=trainees,certificates`)).data || {};
      document.getElementById('hSub').textContent = [course.course_code, course.title].filter(Boolean).join(' — ');
      TC.cacheCourse({ id:COURSE_ID, title:course.title, course_code:course.course_code, trainer:course.trainer });

      const trainees = course.trainees || [];
      const arr = course.certificates || [];
      const certByTid = {};
      arr.forEach(c => { if (c.trainee_id) certByTid[c.trainee_id] = c; });

      const passed = trainees.filter(t => (t.pivot?.result || t.result) === 'passed');
      const failed = trainees.filter(t => (t.pivot?.result || t.result) === 'failed');
      const pendingResults = trainees.filter(t => {
        const r = t.pivot?.result || t.result;
        return !r || r === 'pending';
      });
      const needIssue = passed.filter(t => !certByTid[t.id]);
      const issuedCount = arr.filter(c => c.status !== 'rejected').length;

      const sum = document.getElementById('sumBox');
      sum.hidden = false;
      sum.innerHTML = `
        <div class="cell"><span class="n">${needIssue.length}</span><span class="l">جاهز للإصدار</span></div>
        <div class="cell"><span class="n">${issuedCount}</span><span class="l">شهادات صادرة</span></div>
        <div class="cell"><span class="n">${pendingResults.length}</span><span class="l">بدون نتيجة بعد</span></div>`;

      const hint = document.getElementById('hint');
      if (needIssue.length) {
        issueBtn.disabled = false;
        hint.textContent = `سيُصدر لـ ${needIssue.length} ناجح${needIssue.length>1?'ين':''} بدون شهادة. الراسبون (${failed.length}) لن يحصلوا على شهادة.`;
      } else if (!passed.length) {
        issueBtn.disabled = true;
        hint.innerHTML = pendingResults.length
          ? `لا يوجد ناجحون بعد — أدخل الدرجات أولاً من <a href="center-modules.php?course=${COURSE_ID}">المواد والدرجات</a>.`
          : 'لا يوجد متدربون ناجحون لإصدار شهادات.';
      } else {
        issueBtn.disabled = true;
        hint.textContent = 'كل الناجحين لديهم شهادات بالفعل.';
      }

      if (!arr.length){
        box.innerHTML = `<div class="tc-empty">
          <i class="bi bi-patch-check" style="font-size:2rem;display:block;margin-bottom:8px"></i>
          لم تُصدر شهادات بعد
          <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center">
            <a class="tc-item-btn" href="center-modules.php?course=${COURSE_ID}"><i class="bi bi-journal-text"></i> إدخال الدرجات</a>
            <a class="tc-item-btn" href="center-trainees.php?course=${COURSE_ID}"><i class="bi bi-people"></i> متدربو الدورة</a>
          </div>
        </div>`;
        return;
      }

      box.innerHTML = `<div class="tc-mlist">${arr.map((c,i)=>{
        const [sl,sc] = CERT_ST[c.status] || [c.status || '—', 'b-gray'];
        const name = c.trainee_name || ('متدرب #'+(c.trainee_id||'—'));
        const num = c.certificate_number || c.reference_number || ('#'+c.id);
        const acts = [];
        if (c.printable_url) acts.push(`<a class="open" href="${E(c.printable_url)}" target="_blank" rel="noopener"><i class="bi bi-eye-fill"></i> فتح</a>`);
        if (c.pdf_url) acts.push(`<a class="pdf" href="${E(c.pdf_url)}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</a>`);
        return `<article class="tc-mcard">
          <div class="tc-mcard-top">
            <div>
              <h3 class="tc-mcard-title"><i class="bi bi-person-fill"></i> ${E(name)}</h3>
              ${c.trainee_code?`<div class="tc-mcard-sub">${E(c.trainee_code)}</div>`:''}
            </div>
            <div class="tc-mcard-num">${i+1}</div>
          </div>
          <div class="tc-mcard-rows">
            <div class="tc-mcard-row"><span class="k">رقم الشهادة</span><span class="v">${E(num)}</span></div>
            <div class="tc-mcard-row"><span class="k">النتيجة</span><span class="v">${E(c.result||'—')}</span></div>
            <div class="tc-mcard-row"><span class="k">التاريخ</span><span class="v">${E(c.issue_date||'—')}</span></div>
            <div class="tc-mcard-row"><span class="k">الحالة</span><span class="v"><span class="tc-badge ${sc}">${E(sl)}</span></span></div>
          </div>
          ${acts.length?`<div class="tc-mcard-acts">${acts.join('')}</div>`:''}
        </article>`;
      }).join('')}</div>`;
    }catch(e){
      box.innerHTML = '<div class="tc-empty">تعذّر تحميل الشهادات</div>';
      document.getElementById('issue').disabled = true;
    }
  }

  async function issue(){
    const needTxt = document.getElementById('hint').textContent || '';
    if (!(await TC.confirm((needTxt ? needTxt + '\n\n' : '') + 'متابعة إصدار الشهادات؟'))) return;
    const btn = document.getElementById('issue');
    btn.disabled = true; btn.textContent = 'جاري الإصدار...';
    try{
      const r = await fetch(`${BASE}/training-courses/${COURSE_ID}/issue-certificates`, { method:'POST', headers:HP(), body:'{}' });
      const d = await r.json().catch(()=>({}));
      TC.toast(d.message || (r.ok ? 'تم الإصدار' : 'تعذّر الإصدار'), r.ok ? 'ok' : 'err');
      await load();
    }catch(e){
      TC.toast('تعذّر إصدار الشهادات','err');
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-patch-check-fill"></i> إصدار الشهادات للناجحين';
    }
  }
});
</script>
</body>
</html>
