<?php
$basePath   = '../../';
$pageTitle  = 'عرض طلب التمويل';
$activePage = 'finance-applications-list';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{
      --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4;
      --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280;
      --c-shadow:0 10px 28px rgba(15,79,71,.07);
    }
    body{background:linear-gradient(160deg,#f0faf7,#e8f7f3);}
    .pw{max-width:980px;margin:auto;padding:22px 14px;}
    .page-head{
      background:linear-gradient(135deg,#0f5e4f,var(--c-primary));
      border-radius:20px;padding:22px 26px;color:#fff;margin-bottom:16px;
      display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
    }
    .page-head h1{margin:0;font-size:1.25rem;font-weight:800;}
    .page-head .sub{opacity:.88;font-size:.86rem;margin-top:4px;font-weight:600;}
    .btn-soft,.btn-brand,.btn-act{
      border:none;border-radius:12px;padding:9px 14px;font-weight:700;cursor:pointer;
      display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-size:.86rem;
    }
    .btn-soft{background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.28);}
    .btn-brand{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}
    .btn-view{background:var(--c-soft);color:var(--c-primary);}
    .btn-approve{background:#dcfce7;color:#166534;}
    .btn-forward{background:#dbeafe;color:#1d4ed8;}
    .btn-complete{background:#fef3c7;color:#92400e;}
    .btn-reject{background:#fee2e2;color:#dc2626;}
    .card{
      background:#fff;border:1px solid var(--c-border);border-radius:18px;
      padding:18px 20px;box-shadow:var(--c-shadow);margin-bottom:14px;
    }
    .card-title{font-weight:800;font-size:.95rem;color:var(--c-text);margin:0 0 14px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;}
    .info-item{background:var(--c-soft);border-radius:12px;padding:12px 14px;}
    .info-lbl{font-size:.75rem;font-weight:700;color:var(--c-muted);margin-bottom:4px;}
    .info-val{font-size:.9rem;font-weight:800;color:var(--c-text);word-break:break-word;}
    .text-block{
      background:#f8fafc;border:1px solid var(--c-border);border-radius:12px;
      padding:12px 14px;font-size:.88rem;font-weight:600;color:var(--c-text);line-height:1.7;white-space:pre-wrap;
    }
    .status-pill{display:inline-flex;align-items:center;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:800;background:#dbeafe;color:#1d4ed8;}
    .status-pill.s-approved,.status-pill.s-funded{background:#dcfce7;color:#166534;}
    .status-pill.s-rejected{background:#fee2e2;color:#dc2626;}
    .status-pill.s-needs_completion{background:#fef3c7;color:#92400e;}
    .actions{display:flex;flex-wrap:wrap;gap:8px;}
    .empty{text-align:center;padding:48px 16px;color:var(--c-muted);font-weight:600;}
    .empty i{font-size:2.4rem;display:block;margin-bottom:10px;opacity:.35;}
    .alert-box{display:none;border-radius:12px;padding:12px 14px;margin-bottom:12px;font-weight:700;}
    .alert-box.show{display:block;}
    .alert-box.ok{background:#dcfce7;color:#166534;border:1px solid #86efac;}
    .alert-box.err{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-file-earmark-text"></i> <span id="pageTitleText">عرض طلب التمويل</span></h1>
      <div class="sub" id="pageSubtitle">ملخص الطلب للمراجعة واتخاذ القرار</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="finance-applications-list.php" class="btn-soft"><i class="bi bi-arrow-right"></i> رجوع للقائمة</a>
    </div>
  </div>

  <div id="viewMessage" class="alert-box"></div>
  <div id="loadingBox" class="empty"><i class="bi bi-hourglass-split"></i>جاري تحميل ملخص الطلب...</div>

  <div id="content" style="display:none">
    <div class="card">
      <div class="card-title"><i class="bi bi-info-circle-fill"></i> بيانات أساسية</div>
      <div class="info-grid" id="basicGrid"></div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-briefcase-fill"></i> بيانات المشروع والتمويل</div>
      <div class="info-grid" id="projectGrid"></div>
    </div>

    <div class="card" id="notesCard" style="display:none">
      <div class="card-title"><i class="bi bi-chat-left-text-fill"></i> الغرض والوصف</div>
      <div id="notesArea"></div>
    </div>

    <div class="card" id="actionsCard" style="display:none">
      <div class="card-title"><i class="bi bi-gear-fill"></i> إجراءات</div>
      <div class="actions" id="actionsArea"></div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.5"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-application-view.js?v=1.0"></script>
</body>
</html>
