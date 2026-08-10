<?php
/**
 * تقرير دورة تدريبية — صفحة قابلة للطباعة / الحفظ كـ PDF (عبر متصفح المستخدم).
 * تسحب البيانات من Laravel API بمصادقة Bearer token.
 */
$basePath   = '../../';
$pageTitle  = 'تقرير دورة تدريبية';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<?php include __DIR__ . '/_tc-styles.php'; ?>
<style>
  :root{ --tc-teal:#1c7d6a; --tc-teal-d:#0c4d40; --tc-line:#d8dee2; --tc-ink:#16302b; --tc-muted:#6b7880; }
  body.tc-app{background:#e6e9ec;color:var(--tc-ink)}
  .rp-toolbar{
    position:sticky;top:0;z-index:40;background:#fff;border-bottom:1px solid var(--tc-line);
    padding:10px 16px;display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap;
  }
  .rp-toolbar .btn{
    border:0;border-radius:10px;padding:9px 15px;font:inherit;font-weight:800;font-size:.85rem;
    cursor:pointer;display:inline-flex;align-items:center;gap:7px;text-decoration:none;
  }
  .rp-toolbar .btn.tc-burger{padding:9px 12px}
  .btn-teal{background:var(--tc-teal);color:#fff} .btn-light{background:#eef2f4;color:var(--tc-teal-d)}
  .rp-page{max-width:820px;margin:18px auto;background:#fff;box-shadow:0 6px 30px rgba(0,0,0,.12);padding:0}
  .rp-head{background:linear-gradient(135deg,var(--tc-teal-d),var(--tc-teal));color:#fff;padding:24px 30px;display:flex;justify-content:space-between;align-items:flex-start;gap:16px}
  .rp-head .brand{font-size:.8rem;font-weight:800;opacity:.8;letter-spacing:.5px}
  .rp-head h1{margin:6px 0 2px;font-size:1.5rem;font-weight:900}
  .rp-head .code{opacity:.9;font-size:.85rem}
  .rp-head .logo{width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,.15);display:grid;place-items:center;font-size:30px;flex:0 0 auto}
  .rp-body{padding:24px 30px}
  .rp-meta{display:grid;grid-template-columns:1fr 1fr;gap:8px 24px;margin-bottom:22px}
  .rp-meta .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed var(--tc-line);font-size:.88rem}
  .rp-meta .row span:first-child{color:var(--tc-muted);font-weight:600}
  .rp-meta .row span:last-child{font-weight:800}
  .rp-tiles{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:22px}
  .rp-tile{border:1px solid var(--tc-line);border-radius:12px;padding:12px 8px;text-align:center}
  .rp-tile .v{font-size:1.4rem;font-weight:900;color:var(--tc-teal-d)}
  .rp-tile .l{font-size:.68rem;color:var(--tc-muted);font-weight:700;margin-top:2px}
  h2.rp-sec{font-size:1rem;margin:22px 0 10px;color:var(--tc-teal-d);border-right:4px solid var(--tc-teal);padding-right:9px}
  table.rp-tbl{width:100%;border-collapse:collapse;font-size:.82rem}
  table.rp-tbl th{background:var(--tc-teal-d);color:#fff;padding:9px 8px;text-align:center;font-weight:700}
  table.rp-tbl td{padding:8px;border-bottom:1px solid var(--tc-line);text-align:center}
  table.rp-tbl td.name{text-align:right;font-weight:700}
  table.rp-tbl tr:nth-child(even) td{background:#fafbfc}
  .rp-badge{padding:2px 9px;border-radius:20px;font-size:.72rem;font-weight:800}
  .b-green{background:#e8f7ed;color:#15803d} .b-red{background:#fdecec;color:#b91c1c} .b-gold{background:#fdf3dd;color:#9a6500} .b-gray{background:#eef2f4;color:#546}
  .rp-foot{margin-top:26px;padding-top:14px;border-top:1px solid var(--tc-line);display:flex;justify-content:space-between;font-size:.75rem;color:var(--tc-muted)}
  .rp-loading{padding:60px;text-align:center;color:var(--tc-muted)}
  @media(max-width:700px){
    .rp-meta{grid-template-columns:1fr}
    .rp-tiles{grid-template-columns:repeat(3,1fr)}
    .rp-head,.rp-body{padding:16px}
    .rp-head h1{font-size:1.2rem}
  }
  @media print{
    body{background:#fff}
    .rp-toolbar,.tc-sidebar,.tc-overlay{display:none!important}
    .tc-main,.tc-phone{margin:0!important}
    .rp-page{box-shadow:none;margin:0;max-width:100%}
    @page{size:A4;margin:12mm}
    table.rp-tbl th,.rp-head{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  }
</style>
</head>
<body>
<?php $tcActive='report'; include __DIR__ . '/_tc-sidebar.php'; ?>
<div class="tc-main">
  <div class="rp-toolbar">
    <div class="tc-bar-start">
      <button type="button" class="btn btn-light tc-burger" onclick="tcToggleSidebar()" aria-label="القائمة"><i class="bi bi-list"></i></button>
      <a class="btn btn-light" id="rpBack"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    <button type="button" class="btn btn-teal" onclick="window.print()"><i class="bi bi-printer-fill"></i> طباعة / حفظ PDF</button>
  </div>

  <div class="rp-page" id="rpPage">
    <div class="rp-loading"><i class="bi bi-hourglass-split"></i> جاري تجهيز التقرير...</div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
const COURSE_ID = new URLSearchParams(location.search).get('id');

document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  if (!COURSE_ID) { location.href = 'center-app.php'; return; }
  document.getElementById('rpBack').href = 'center-course.php?id=' + COURSE_ID;

  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const jget = async (p) => { const r = await fetch(`${BASE}${p}`, { headers:H() }); if(!r.ok) throw new Error(r.status); return r.json(); };

  const RESULT = { passed:['ناجح','b-green'], failed:['راسب','b-red'], pending:['قيد التقييم','b-gold'], attendance_only:['حضور فقط','b-gray'] };
  const MODE = { onsite:'حضوري', offline:'حضوري', online:'أونلاين', hybrid:'مدمج' };

  try {
    const [c, t] = await Promise.all([
      jget(`/training-courses/${COURSE_ID}?include=certificates`),
      jget(`/training-courses/${COURSE_ID}/trainees`),
    ]);
    const course = c.data || {};
    const trainees = t.data || [];
    const certs = course.certificates || [];

    const passed = trainees.filter(x=>x.pivot?.result==='passed').length;
    const failed = trainees.filter(x=>x.pivot?.result==='failed').length;
    const rates = trainees.map(x=>x.attendance_rate).filter(v=>v!=null);
    const avgAtt = rates.length ? Math.round(rates.reduce((a,b)=>a+b,0)/rates.length) : null;
    const sessionsTotal = trainees[0]?.sessions_total ?? 0;
    const period = [course.start_date, course.end_date].filter(Boolean).join(' ← ') || '—';

    document.getElementById('rpPage').innerHTML = `
      <div class="rp-head">
        <div>
          <div class="brand">SMEDA — هيئة تنمية المشاريع الصغيرة والمتوسطة</div>
          <h1>تقرير دورة تدريبية</h1>
          <div class="code">${E(course.title||'')} — ${E(course.course_code||'')}</div>
        </div>
        <div class="logo"><i class="bi bi-mortarboard-fill"></i></div>
      </div>
      <div class="rp-body">
        <div class="rp-meta">
          <div class="row"><span>المركز التدريبي</span><span>${E(course.training_center?.name||'—')}</span></div>
          <div class="row"><span>المدرب</span><span>${E(course.trainer?.name||'—')}</span></div>
          <div class="row"><span>الفترة</span><span>${E(period)}</span></div>
          <div class="row"><span>نمط التنفيذ</span><span>${E(MODE[course.delivery_mode]||course.delivery_mode||'—')}</span></div>
          <div class="row"><span>الساعات المخطّطة</span><span>${E(course.planned_hours??'—')}</span></div>
          <div class="row"><span>الحالة</span><span>${course.status==='completed'?'مكتملة':E(course.status||'—')}</span></div>
        </div>

        <div class="rp-tiles">
          <div class="rp-tile"><div class="v">${trainees.length}</div><div class="l">متدرب</div></div>
          <div class="rp-tile"><div class="v" style="color:#15803d">${passed}</div><div class="l">ناجح</div></div>
          <div class="rp-tile"><div class="v" style="color:#b91c1c">${failed}</div><div class="l">راسب</div></div>
          <div class="rp-tile"><div class="v">${avgAtt!=null?avgAtt+'%':'—'}</div><div class="l">متوسط الحضور</div></div>
          <div class="rp-tile"><div class="v">${sessionsTotal}</div><div class="l">جلسات</div></div>
          <div class="rp-tile"><div class="v">${certs.length}</div><div class="l">شهادات</div></div>
        </div>

        <h2 class="rp-sec">كشف المتدربين</h2>
        <table class="rp-tbl">
          <thead><tr><th>#</th><th>الاسم</th><th>الرمز</th><th>الحضور</th><th>الدرجة النهائية</th><th>النتيجة</th><th>شهادة</th></tr></thead>
          <tbody>${trainees.length ? trainees.map((x,i)=>{
            const [rl,rc]=RESULT[x.pivot?.result]||['—','b-gray'];
            return `<tr>
              <td>${i+1}</td><td class="name">${E(x.name||'—')}</td><td>${E(x.trainee_code||'—')}</td>
              <td>${x.attendance_rate!=null?x.attendance_rate+'%':'—'}</td>
              <td>${x.pivot?.score??'—'}</td>
              <td><span class="rp-badge ${rc}">${rl}</span></td>
              <td>${x.has_certificate?'✔':'—'}</td>
            </tr>`;
          }).join('') : '<tr><td colspan="7" style="padding:20px;color:#9ca3af">لا يوجد متدربون</td></tr>'}</tbody>
        </table>

        <div class="rp-foot">
          <span>تم إنشاء التقرير آلياً من منصة SMEDA</span>
          <span>تاريخ الطباعة: ${new Date().toLocaleDateString('ar-EG')}</span>
        </div>
      </div>`;
    document.title = `تقرير — ${course.course_code || course.title || 'دورة'}`;
  } catch(e){
    document.getElementById('rpPage').innerHTML = '<div class="rp-loading">تعذّر تحميل بيانات التقرير</div>';
  }
});
</script>
</body>
</html>
