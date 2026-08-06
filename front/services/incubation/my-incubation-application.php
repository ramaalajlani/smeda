<?php
$basePath  = '../../';
$pageTitle = 'طلبي للانضمام للحاضنة';
$activePage= 'incubation';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#7c3aed;--c-accent:#a78bfa;--c-soft:#f5f3ff;--c-border:rgba(124,58,237,.13);--c-text:#1e1b4b;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(124,58,237,.07);}
    body{background:linear-gradient(160deg,#f5f3ff,#ede9fe);}
    .pw{max-width:800px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#4c1d95,#7c3aed);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:14px;}
    .info-item{background:var(--c-soft);border-radius:12px;padding:12px 14px;}
    .info-lbl{font-size:.76rem;font-weight:700;color:var(--c-muted);margin-bottom:4px;}
    .info-val{font-size:.92rem;font-weight:800;color:var(--c-text);}
    .badge-s{padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:700;display:inline-block;}
    .bs-pending{background:#fef3c7;color:#92400e;}
    .bs-review{background:#ede9fe;color:#7c3aed;}
    .bs-accepted{background:#dcfce7;color:#15803d;}
    .bs-rejected{background:#fee2e2;color:#dc2626;}
    .timeline{padding:0;list-style:none;position:relative;}
    .timeline::before{content:'';position:absolute;right:18px;top:0;bottom:0;width:2px;background:var(--c-border);}
    .tl-item{display:flex;gap:14px;padding:0 0 20px;}
    .tl-dot{width:36px;height:36px;border-radius:50%;background:var(--c-soft);border:3px solid var(--c-border);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem;color:var(--c-muted);}
    .tl-dot.done{background:var(--c-primary);border-color:var(--c-primary);color:#fff;}
    .tl-dot.accept{background:#15803d;border-color:#15803d;color:#fff;}
    .tl-dot.reject{background:#dc2626;border-color:#dc2626;color:#fff;}
    .tl-body{flex:1;}
    .tl-title{font-weight:800;font-size:.9rem;color:var(--c-text);}
    .tl-meta{font-size:.78rem;color:var(--c-muted);margin-top:2px;}
    .project-box{background:linear-gradient(135deg,var(--c-soft),#ede9fe);border:1px solid var(--c-border);border-radius:16px;padding:18px;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:10px 22px;font-weight:700;cursor:pointer;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:7px;}
    .no-app{text-align:center;padding:50px;color:var(--c-muted);}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">
  <div class="page-head">
    <h1><i class="bi bi-file-earmark-person-fill"></i> طلبي للانضمام للحاضنة</h1>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>
  <div id="content"></div>
</div>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const e     = window.APP_HELPERS.e;

  const STAGE_LBL = { idea:'فكرة', pre_seed:'ما قبل الإطلاق', seed:'بذرة', early:'مبكر', growth:'نمو' };
  const STATUS_MAP = {
    pending:      { lbl:'معلق — بانتظار المراجعة', cls:'bs-pending', icon:'bi-clock-fill' },
    under_review: { lbl:'قيد المراجعة', cls:'bs-review', icon:'bi-search' },
    accepted:     { lbl:'مقبول ✓', cls:'bs-accepted', icon:'bi-check-circle-fill' },
    rejected:     { lbl:'مرفوض', cls:'bs-rejected', icon:'bi-x-circle-fill' },
    withdrawn:    { lbl:'منسحب', cls:'bs-pending', icon:'bi-dash-circle-fill' },
  };

  async function load() {
    const r = await fetch(`${base}/incubation/my-applications`, { headers:{ Authorization:`Bearer ${token()}` }});
    const j = await r.json();
    const apps = j ?? [];
    const cont = document.getElementById('content');

    if (!apps.length) {
      cont.innerHTML = `<div class="no-app">
        <i class="bi bi-file-earmark-plus" style="font-size:3rem;display:block;margin-bottom:14px;opacity:.3;color:var(--c-primary)"></i>
        <div style="font-weight:800;font-size:1rem;margin-bottom:8px">لم تتقدم لأي حاضنة بعد</div>
        <a href="incubation-apply.php" class="btn-primary"><i class="bi bi-send-fill"></i> تقديم طلب الآن</a>
      </div>`;
      return;
    }

    cont.innerHTML = apps.map(a => {
      const sm = STATUS_MAP[a.status] ?? { lbl:a.status, cls:'bs-pending', icon:'bi-circle' };
      const isAccepted = a.status === 'accepted';
      return `
      <div class="card">
        <div class="card-title"><i class="bi ${sm.icon}" style="color:var(--c-primary)"></i> ${e(a.project_name)}</div>
        <div style="margin-bottom:14px"><span class="badge-s ${sm.cls}">${sm.lbl}</span></div>
        <div class="info-grid">
          <div class="info-item"><div class="info-lbl">الحاضنة</div><div class="info-val">${e(a.incubator?.name??'—')}</div></div>
          <div class="info-item"><div class="info-lbl">البرنامج</div><div class="info-val">${e(a.program?.name??'—')}</div></div>
          <div class="info-item"><div class="info-lbl">المرحلة</div><div class="info-val">${STAGE_LBL[a.business_stage]??a.business_stage??'—'}</div></div>
          <div class="info-item"><div class="info-lbl">تاريخ التقديم</div><div class="info-val">${a.created_at?.slice(0,10)??'—'}</div></div>
          <div class="info-item"><div class="info-lbl">حجم الفريق</div><div class="info-val">${a.team_size??'—'} أفراد</div></div>
          <div class="info-item"><div class="info-lbl">نموذج أولي</div><div class="info-val">${a.has_prototype?'نعم':'لا'}</div></div>
        </div>
        ${a.reviewer_notes ? `<div style="background:#fef9c3;border-radius:12px;padding:12px 14px;font-size:.86rem;margin-bottom:12px"><strong>ملاحظات المراجع:</strong> ${e(a.reviewer_notes)}</div>` : ''}
        ${isAccepted && a.project ? `
          <div class="project-box">
            <div style="font-weight:800;font-size:.92rem;color:var(--c-primary);margin-bottom:10px"><i class="bi bi-diagram-3-fill"></i> مشروعك المحتضن</div>
            <div class="info-grid">
              <div class="info-item"><div class="info-lbl">المرحلة</div><div class="info-val">${{seed:'بذرة',early:'مبكر',growth:'نمو',exit:'تخرج'}[a.project.stage]??a.project.stage}</div></div>
              <div class="info-item"><div class="info-lbl">تاريخ البدء</div><div class="info-val">${a.project.start_date??'—'}</div></div>
              <div class="info-item"><div class="info-lbl">المرشد</div><div class="info-val">${e(a.project.mentor?.name??'—')}</div></div>
              <div class="info-item"><div class="info-lbl">الحالة</div><div class="info-val">${{active:'نشط',graduated:'متخرج',withdrawn:'منسحب',terminated:'منهي'}[a.project.status]??a.project.status}</div></div>
            </div>
            <a href="my-project-dashboard.php" class="btn-primary" style="margin-top:10px"><i class="bi bi-speedometer2"></i> لوحة مشروعي</a>
          </div>` : ''}

        <!-- Timeline -->
        <ul class="timeline" style="margin-top:16px">
          <li class="tl-item">
            <div class="tl-dot done"><i class="bi bi-send-fill"></i></div>
            <div class="tl-body">
              <div class="tl-title">تم إرسال الطلب</div>
              <div class="tl-meta">${a.created_at?.slice(0,10)??'—'}</div>
            </div>
          </li>
          <li class="tl-item">
            <div class="tl-dot ${['under_review','accepted','rejected'].includes(a.status)?'done':''}"><i class="bi bi-search"></i></div>
            <div class="tl-body">
              <div class="tl-title">المراجعة</div>
              <div class="tl-meta">${a.reviewed_at ? a.reviewed_at.slice(0,10) : 'بانتظار المراجعة'}</div>
            </div>
          </li>
          <li class="tl-item">
            <div class="tl-dot ${a.status==='accepted'?'accept':a.status==='rejected'?'reject':''}"><i class="bi bi-${a.status==='accepted'?'check-circle-fill':a.status==='rejected'?'x-circle-fill':'circle'}"></i></div>
            <div class="tl-body">
              <div class="tl-title">${a.status==='accepted'?'تم القبول':a.status==='rejected'?'تم الرفض':'القرار النهائي'}</div>
              <div class="tl-meta">${a.reviewed_at ? a.reviewed_at.slice(0,10) : 'لم يُحدد بعد'}</div>
            </div>
          </li>
        </ul>
      </div>`;
    }).join('');
  }

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
