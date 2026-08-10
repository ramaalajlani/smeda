<?php
$basePath  = '../../';
$pageTitle = 'رفع تقرير استشاري';
$activePage= 'finance';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../../includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#17947B;--c-accent:#06AA89;--c-soft:#EAF8F4;--c-border:rgba(23,148,123,.13);--c-text:#16332E;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(15,79,71,.07);}
    body{background:linear-gradient(160deg,#f0faf7,#e8f7f3);}
    .pw{max-width:760px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#0f5e4f,var(--c-primary));border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .btn-outline-w{background:#fff;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:12px;padding:9px 20px;font-weight:700;cursor:pointer;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:26px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 18px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:20px;}
    .info-item{background:var(--c-soft);border-radius:12px;padding:12px 14px;}
    .info-lbl{font-size:.76rem;font-weight:700;color:var(--c-muted);margin-bottom:4px;}
    .info-val{font-size:.92rem;font-weight:800;color:var(--c-text);}
    .form-group{margin-bottom:16px;}
    .form-label{font-size:.82rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:6px;}
    .form-control,.form-select{width:100%;border:1px solid var(--c-border);border-radius:11px;padding:10px 13px;font-size:.88rem;color:var(--c-text);box-sizing:border-box;}
    .form-control:focus,.form-select:focus{outline:none;border-color:var(--c-primary);box-shadow:0 0 0 3px rgba(23,148,123,.12);}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    @media(max-width:560px){.form-row{grid-template-columns:1fr;}}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:12px 28px;font-weight:700;cursor:pointer;font-size:.92rem;display:inline-flex;align-items:center;gap:8px;}
    .btn-primary:disabled{opacity:.6;cursor:default;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:12px 16px;font-weight:700;font-size:.88rem;display:none;margin-bottom:14px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:12px 16px;font-weight:700;font-size:.88rem;display:none;margin-bottom:14px;}
    .existing-box{background:var(--c-soft);border:1px solid var(--c-border);border-radius:14px;padding:16px;margin-bottom:18px;}
    .existing-box-title{font-weight:800;font-size:.88rem;color:var(--c-primary);margin-bottom:10px;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-file-earmark-text-fill"></i> رفع تقرير استشاري</h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px" id="assignSub"></div>
    </div>
    <a href="my-consultant-assignments.php" class="btn-outline-w"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="card">
      <div class="card-title"><i class="bi bi-info-circle-fill"></i> بيانات الإحالة</div>
      <div class="info-grid" id="infoGrid"></div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-pencil-square"></i> بيانات التقرير</div>

      <div id="existingBox" class="existing-box" style="display:none">
        <div class="existing-box-title"><i class="bi bi-check-circle-fill"></i> تقرير مرفوع مسبقاً</div>
        <div id="existingContent"></div>
      </div>

      <div id="formSuccess" class="alert-s"></div>
      <div id="formError"   class="alert-e"></div>

      <div class="form-group">
        <label class="form-label">ملخص التقرير *</label>
        <textarea id="fSummary" class="form-control" rows="4" placeholder="اكتب ملخصاً شاملاً للدراسة والتحليل..."></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">التوصية *</label>
          <select id="fRec" class="form-select">
            <option value="">-- اختر --</option>
            <option value="approve">موافقة</option>
            <option value="reject">رفض</option>
            <option value="needs_adjustment">يحتاج تعديل</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">نسبة الجدوى (%) *</label>
          <input type="number" id="fViability" class="form-control" min="0" max="100" step="0.1" placeholder="مثال: 75">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">ملاحظات إضافية</label>
        <textarea id="fNotes" class="form-control" rows="3" placeholder="أي ملاحظات أو تحفظات..."></textarea>
      </div>
      <button class="btn-primary" id="submitBtn" onclick="submitReport()">
        <i class="bi bi-send-fill"></i> رفع التقرير
      </button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const assignmentId = new URLSearchParams(location.search).get('assignment_id');
  if (!assignmentId) { location.href = 'my-consultant-assignments.php'; return; }

  if (!window.AppAuth.hasAnyPermission(['finance.consultant_reports.create', 'finance.consultants.submit_report'])) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const FP    = window.FinancePlatform;
  let assignment = null;

  async function load() {
    const r = await fetch(`${base}/finance/my-consultant-assignments`, { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل بيانات الإحالة');
    const j = await r.json();
    const rows = j.data ?? j ?? [];
    assignment = rows.find(item => String(item.id) === String(assignmentId));
    if (!assignment) throw new Error('الإحالة غير موجودة أو غير مصرح لك بالوصول إليها');

    const a = assignment;
    document.getElementById('assignSub').textContent = a.application?.project_name ?? '';

    document.getElementById('infoGrid').innerHTML = [
      ['المشروع',      a.application?.project_name],
      ['صاحب الطلب',  a.application?.user?.name],
      ['تاريخ الإحالة',a.assigned_at ?? a.created_at?.slice(0,10)],
      ['الحالة',       FP.statusLabel(a.status)],
    ].map(([l,v]) => `<div class="info-item"><div class="info-lbl">${l}</div><div class="info-val">${window.APP_HELPERS.e(v??'—')}</div></div>`).join('');

    if (a.status === 'completed') {
      document.getElementById('existingBox').style.display = '';
      document.getElementById('existingContent').innerHTML = `
        <div style="font-size:.87rem;color:var(--c-text)">
          تم رفع تقرير لهذه الإحالة مسبقاً. يمكنك مراجعة التفاصيل من قائمة الإحالات.
        </div>`;
      document.getElementById('submitBtn').disabled = true;
    }
  }

  window.submitReport = async function() {
    if (!assignment) return;
    const btn     = document.getElementById('submitBtn');
    const summary = document.getElementById('fSummary').value.trim();
    const rec     = document.getElementById('fRec').value;
    const score   = document.getElementById('fViability').value;
    const s = document.getElementById('formSuccess');
    const e = document.getElementById('formError');
    const officeId = assignment.consultant_office_id;

    if (!summary || !rec || score === '') {
      e.textContent='يرجى تعبئة جميع الحقول المطلوبة'; e.style.display='block';
      setTimeout(()=>e.style.display='none',4000); return;
    }
    if (!officeId) {
      e.textContent='حسابك غير مرتبط بمكتب استشاري'; e.style.display='block';
      setTimeout(()=>e.style.display='none',4000); return;
    }

    btn.disabled = true;
    const payload = {
      funding_application_id: assignment.funding_application_id ?? assignment.application_id,
      consultant_office_id: officeId,
      report_summary: summary,
      recommendation: rec,
      feasibility_score: Number(score),
      conditions: document.getElementById('fNotes').value.trim() || null,
    };
    const r = await fetch(window.APP_ROUTES.fundingConsultantReports(), {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    btn.disabled = false;
    if (r.ok) {
      s.textContent='✓ تم رفع التقرير بنجاح'; s.style.display='block';
      setTimeout(()=>{ s.style.display='none'; location.href = 'my-consultant-assignments.php'; },1500);
    } else {
      e.textContent = Object.values(j.errors??{}).flat()[0]||j.message||'خطأ في الحفظ';
      e.style.display='block'; setTimeout(()=>e.style.display='none',5000);
    }
  };

  try {
    await load();
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = '';
  } catch(err) {
    document.getElementById('loading').innerHTML = `<div style="color:#dc2626;font-weight:700">${err.message}</div>`;
  }
});
</script>
</body>
</html>
