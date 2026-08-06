<?php
$basePath  = '../../';
$pageTitle = 'ملف المشروع والإرشاد';
$activePage= 'incubation';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#7c3aed;--c-accent:#a78bfa;--c-soft:#f5f3ff;--c-border:rgba(124,58,237,.13);--c-text:#1e1b4b;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(124,58,237,.07);}
    body{background:linear-gradient(160deg,#f5f3ff,#ede9fe);}
    .pw{max-width:960px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#4c1d95,#7c3aed);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .btn-outline-w{background:#fff;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:12px;padding:9px 20px;font-weight:700;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px;}
    .info-item{background:var(--c-soft);border-radius:12px;padding:12px 14px;}
    .info-lbl{font-size:.76rem;font-weight:700;color:var(--c-muted);margin-bottom:4px;}
    .info-val{font-size:.92rem;font-weight:800;color:var(--c-text);}
    .tbl{width:100%;border-collapse:collapse;font-size:.86rem;}
    .tbl th{background:var(--c-soft);color:var(--c-text);font-weight:800;padding:9px 12px;text-align:right;}
    .tbl td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);}
    .tbl tr:last-child td{border-bottom:none;}
    .badge-s{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;display:inline-block;}
    .bs-completed{background:#dcfce7;color:#15803d;}
    .bs-scheduled{background:#dbeafe;color:#1d4ed8;}
    .bs-cancelled{background:#fee2e2;color:#dc2626;}
    .stars{color:#f59e0b;}
    .form-group{margin-bottom:13px;}
    .form-label{font-size:.81rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px;}
    .form-control,.form-select{border:1px solid var(--c-border);border-radius:10px;padding:8px 12px;font-size:.86rem;color:var(--c-text);width:100%;box-sizing:border-box;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:10px 22px;font-weight:700;cursor:pointer;font-size:.88rem;display:inline-flex;align-items:center;gap:7px;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .empty{text-align:center;padding:24px;color:var(--c-muted);font-size:.87rem;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-diagram-3-fill"></i> <span id="projName">المشروع</span></h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px" id="projSub"></div>
    </div>
    <a href="incubated-projects.php" class="btn-outline-w"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="card">
      <div class="card-title"><i class="bi bi-info-circle-fill"></i> بيانات المشروع</div>
      <div class="info-grid" id="infoGrid"></div>
    </div>

    <!-- جلسات الإرشاد -->
    <div class="card">
      <div class="card-title"><i class="bi bi-people-fill"></i> جلسات الإرشاد</div>
      <div id="sessionSuccess" class="alert-s"></div>
      <div id="sessionError"   class="alert-e"></div>
      <div id="sessionFormWrapper" style="display:none;background:var(--c-soft);border-radius:14px;padding:16px;margin-bottom:16px">
        <div style="font-weight:800;font-size:.88rem;color:var(--c-primary);margin-bottom:12px"><i class="bi bi-plus-circle-fill"></i> جلسة إرشاد جديدة</div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">تاريخ الجلسة *</label><input type="date" id="sDate" class="form-control"></div>
          <div class="form-group"><label class="form-label">المدة (دقيقة)</label><input type="number" id="sDur" class="form-control" value="60" min="15"></div>
        </div>
        <div class="form-group"><label class="form-label">الموضوع *</label><input type="text" id="sTopic" class="form-control"></div>
        <div class="form-group"><label class="form-label">ملاحظات</label><textarea id="sNotes" class="form-control" rows="2"></textarea></div>
        <div class="form-group"><label class="form-label">بنود العمل</label><textarea id="sActions" class="form-control" rows="2"></textarea></div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">الحالة</label>
            <select id="sStatus" class="form-select">
              <option value="scheduled">مجدولة</option>
              <option value="completed">مكتملة</option>
              <option value="cancelled">ملغاة</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">التقييم (1-5)</label><input type="number" id="sRating" class="form-control" min="1" max="5"></div>
        </div>
        <button class="btn-primary" onclick="saveSession()"><i class="bi bi-save-fill"></i> حفظ الجلسة</button>
      </div>
      <div id="sessionBtnWrapper"></div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>التاريخ</th><th>الموضوع</th><th>المدة</th><th>الحالة</th><th>التقييم</th></tr></thead>
          <tbody id="sessionsBody"></tbody>
        </table>
      </div>
    </div>

    <!-- تقارير التقدم -->
    <div class="card">
      <div class="card-title"><i class="bi bi-bar-chart-fill"></i> تقارير التقدم</div>
      <div id="reportSuccess" class="alert-s"></div>
      <div id="reportError"   class="alert-e"></div>
      <div id="reportFormWrapper" style="display:none;background:var(--c-soft);border-radius:14px;padding:16px;margin-bottom:16px">
        <div style="font-weight:800;font-size:.88rem;color:var(--c-primary);margin-bottom:12px"><i class="bi bi-plus-circle-fill"></i> تقرير تقدم جديد</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">نوع الفترة</label>
            <select id="rType" class="form-select"><option value="monthly">شهري</option><option value="quarterly">ربع سنوي</option></select>
          </div>
          <div class="form-group"><label class="form-label">الفترة *</label><input type="text" id="rLabel" class="form-control" placeholder="مثال: Q2-2026 أو يناير-2026"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">الإيرادات (ل.س)</label><input type="number" id="rRev" class="form-control" min="0"></div>
          <div class="form-group"><label class="form-label">الموظفون</label><input type="number" id="rEmp" class="form-control" min="0"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">العملاء</label><input type="number" id="rCust" class="form-control" min="0"></div>
          <div class="form-group"><label class="form-label">التقييم العام (1-10)</label><input type="number" id="rRating" class="form-control" min="1" max="10"></div>
        </div>
        <div class="form-group"><label class="form-label">الإنجازات</label><textarea id="rAch" class="form-control" rows="2"></textarea></div>
        <div class="form-group"><label class="form-label">التحديات</label><textarea id="rChal" class="form-control" rows="2"></textarea></div>
        <div class="form-group"><label class="form-label">الخطوات القادمة</label><textarea id="rNext" class="form-control" rows="2"></textarea></div>
        <button class="btn-primary" onclick="saveReport()"><i class="bi bi-save-fill"></i> حفظ التقرير</button>
      </div>
      <div id="reportBtnWrapper"></div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr><th>الفترة</th><th>الإيرادات</th><th>الموظفون</th><th>العملاء</th><th>التقييم</th><th>التاريخ</th></tr></thead>
          <tbody id="reportsBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const id = new URLSearchParams(location.search).get('id');
  if (!id) { location.href = 'incubated-projects.php'; return; }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const e     = window.APP_HELPERS.e;
  const user  = window.AppAuth.getUser();

  const STAGE_LBL  = { seed:'بذرة', early:'مبكر', growth:'نمو', exit:'تخرج' };
  const STATUS_LBL = { active:'نشط', graduated:'متخرج', withdrawn:'منسحب', terminated:'منهي' };
  const SES_BADGE  = { completed:'bs-completed', scheduled:'bs-scheduled', cancelled:'bs-cancelled' };
  const SES_LBL    = { completed:'مكتملة', scheduled:'مجدولة', cancelled:'ملغاة' };

  let project = null;

  async function load() {
    const r = await fetch(`${base}/incubation/projects/${id}`, { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل المشروع');
    const j = await r.json();
    project = j.data ?? j;

    document.getElementById('projName').textContent = project.project_name;
    document.getElementById('projSub').textContent  = project.incubator?.name ?? '';

    document.getElementById('infoGrid').innerHTML = [
      ['المشروع',       project.project_name],
      ['الحاضنة',       project.incubator?.name],
      ['صاحب المشروع',  project.owner?.name],
      ['المرشد',        project.mentor?.name],
      ['المرحلة',       STAGE_LBL[project.stage]??project.stage],
      ['الحالة',        STATUS_LBL[project.status]??project.status],
      ['تاريخ البدء',   project.start_date],
      ['الموظفون',      project.current_employees],
      ['الإيرادات',     project.current_revenue ? Number(project.current_revenue).toLocaleString('ar')+ ' ل.س' : '—'],
      ['الجلسات',       project.mentoring_sessions?.length ?? 0],
      ['التقارير',      project.progress_reports?.length ?? 0],
    ].map(([l,v])=>`<div class="info-item"><div class="info-lbl">${l}</div><div class="info-val">${e(v??'—')}</div></div>`).join('');

    // Show add session button for mentor/manager
    const isMentor = project.mentor?.id === user?.id;
    const canManage= window.AppAuth.hasPermission?.('incubation.manage') || window.AppAuth.hasRole?.('admin') || window.AppAuth.hasRole?.('super_admin');
    const isOwner  = project.owner?.id === user?.id;

    if (isMentor || canManage) {
      document.getElementById('sessionBtnWrapper').innerHTML = `<button class="btn-primary" style="margin-bottom:12px" onclick="toggleForm('session')"><i class="bi bi-plus-lg"></i> إضافة جلسة</button>`;
    }
    if (isOwner || canManage) {
      document.getElementById('reportBtnWrapper').innerHTML = `<button class="btn-primary" style="margin-bottom:12px" onclick="toggleForm('report')"><i class="bi bi-plus-lg"></i> إضافة تقرير</button>`;
    }

    // Sessions
    const sessions = project.mentoring_sessions ?? [];
    document.getElementById('sessionsBody').innerHTML = sessions.length
      ? sessions.map(s=>`<tr>
          <td>${e(s.session_date??'—')}</td>
          <td>${e(s.topic)}</td>
          <td>${s.duration_minutes??'—'} دقيقة</td>
          <td><span class="badge-s ${SES_BADGE[s.status]||'bs-scheduled'}">${SES_LBL[s.status]??s.status}</span></td>
          <td class="stars">${s.rating ? '★'.repeat(s.rating)+'☆'.repeat(5-s.rating) : '—'}</td>
        </tr>`).join('')
      : '<tr><td colspan="5" class="empty">لا توجد جلسات بعد</td></tr>';

    // Reports
    const reports = project.progress_reports ?? [];
    document.getElementById('reportsBody').innerHTML = reports.length
      ? reports.map(r=>`<tr>
          <td style="font-weight:700">${e(r.period_label)}</td>
          <td>${r.revenue ? Number(r.revenue).toLocaleString('ar')+' ل.س' : '—'}</td>
          <td>${r.employees??'—'}</td>
          <td>${r.customers??'—'}</td>
          <td>${r.overall_rating ? r.overall_rating+'/10' : '—'}</td>
          <td style="font-size:.8rem;color:var(--c-muted)">${r.created_at?.slice(0,10)??'—'}</td>
        </tr>`).join('')
      : '<tr><td colspan="6" class="empty">لا توجد تقارير بعد</td></tr>';
  }

  window.toggleForm = function(type) {
    const f = document.getElementById(type+'FormWrapper');
    f.style.display = f.style.display === 'none' ? '' : 'none';
  };

  window.saveSession = async function() {
    const payload = {
      session_date:     document.getElementById('sDate').value,
      duration_minutes: Number(document.getElementById('sDur').value||60),
      topic:            document.getElementById('sTopic').value.trim(),
      notes:            document.getElementById('sNotes').value.trim()||null,
      action_items:     document.getElementById('sActions').value.trim()||null,
      status:           document.getElementById('sStatus').value,
      rating:           document.getElementById('sRating').value ? Number(document.getElementById('sRating').value) : null,
    };
    const r = await fetch(`${base}/incubation/projects/${id}/sessions`, {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    const s = document.getElementById('sessionSuccess');
    const er= document.getElementById('sessionError');
    if (r.ok) {
      s.textContent='✓ تمت إضافة الجلسة'; s.style.display='block';
      setTimeout(()=>{ s.style.display='none'; document.getElementById('sessionFormWrapper').style.display='none'; load(); },1500);
    } else {
      er.textContent = Object.values(j.errors??{}).flat()[0]||j.message||'خطأ';
      er.style.display='block'; setTimeout(()=>er.style.display='none',4000);
    }
  };

  window.saveReport = async function() {
    const payload = {
      period_type:    document.getElementById('rType').value,
      period_label:   document.getElementById('rLabel').value.trim(),
      revenue:        document.getElementById('rRev').value ? Number(document.getElementById('rRev').value) : null,
      employees:      document.getElementById('rEmp').value ? Number(document.getElementById('rEmp').value) : null,
      customers:      document.getElementById('rCust').value ? Number(document.getElementById('rCust').value) : null,
      overall_rating: document.getElementById('rRating').value ? Number(document.getElementById('rRating').value) : null,
      achievements:   document.getElementById('rAch').value.trim()||null,
      challenges:     document.getElementById('rChal').value.trim()||null,
      next_steps:     document.getElementById('rNext').value.trim()||null,
    };
    const r = await fetch(`${base}/incubation/projects/${id}/reports`, {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    const s = document.getElementById('reportSuccess');
    const er= document.getElementById('reportError');
    if (r.ok) {
      s.textContent='✓ تمت إضافة التقرير'; s.style.display='block';
      setTimeout(()=>{ s.style.display='none'; document.getElementById('reportFormWrapper').style.display='none'; load(); },1500);
    } else {
      er.textContent = Object.values(j.errors??{}).flat()[0]||j.message||'خطأ';
      er.style.display='block'; setTimeout(()=>er.style.display='none',4000);
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
