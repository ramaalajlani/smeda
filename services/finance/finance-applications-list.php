<?php
$basePath  = '../../';
$pageTitle = 'طلبات التمويل';
$activePage= 'finance-applications-list';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../../includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{
      --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4;
      --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280;
      --c-shadow:0 10px 28px rgba(15,79,71,.07);
    }
    body{background:linear-gradient(160deg,#f0faf7,#e8f7f3);}
    .pw{max-width:1100px;margin:auto;padding:22px 14px;}
    .page-head{
      background:linear-gradient(135deg,#0f5e4f,var(--c-primary));
      border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:18px;
      display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;
    }
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .page-head .sub{opacity:.88;font-size:.88rem;margin-top:4px;font-weight:600;}
    .scope-note{
      background:#fff;border:1px dashed var(--c-border);border-radius:14px;
      padding:12px 16px;margin-bottom:14px;color:var(--c-muted);font-size:.88rem;font-weight:600;
    }
    .stats-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px;}
    .stat-card{background:#fff;border:1px solid var(--c-border);border-radius:16px;padding:14px 16px;box-shadow:var(--c-shadow);}
    .stat-card .label{color:var(--c-muted);font-size:.78rem;font-weight:700;margin-bottom:4px;}
    .stat-card .value{color:var(--c-text);font-size:1.3rem;font-weight:800;}
    .filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;align-items:end;}
    .form-control,.form-select{
      border:1px solid var(--c-border);border-radius:10px;padding:8px 12px;
      font-size:.87rem;color:var(--c-text);background:#fff;min-width:160px;
    }
    .btn-brand,.btn-soft{
      border:none;border-radius:12px;padding:9px 14px;font-weight:700;cursor:pointer;
      display:inline-flex;align-items:center;gap:6px;text-decoration:none;
    }
    .btn-brand{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}
    .btn-soft{background:var(--c-soft);color:var(--c-primary);}
    .req-card{
      background:#fff;border:1px solid var(--c-border);border-radius:18px;
      padding:18px;box-shadow:var(--c-shadow);margin-bottom:12px;
    }
    .req-head{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:8px;}
    .req-code{font-size:.78rem;font-weight:700;background:var(--c-soft);color:var(--c-primary);padding:4px 10px;border-radius:8px;}
    .req-status{font-size:.76rem;font-weight:700;padding:4px 12px;border-radius:20px;background:#eef2ff;color:#3730a3;}
    .req-status.s-approved,.req-status.s-funded{background:#dcfce7;color:#166534;}
    .req-status.s-rejected{background:#fee2e2;color:#dc2626;}
    .req-status.s-needs_completion{background:#fef3c7;color:#92400e;}
    .req-status.s-submitted,.req-status.s-branch_review,.req-status.s-funder_review{background:#dbeafe;color:#1d4ed8;}
    .req-title{font-weight:800;font-size:1rem;color:var(--c-text);margin:0 0 8px;}
    .req-meta{display:flex;flex-wrap:wrap;gap:12px;color:var(--c-muted);font-size:.83rem;font-weight:600;}
    .req-meta i{color:var(--c-primary);}
    .req-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;}
    .btn-act{border:none;border-radius:10px;padding:7px 12px;font-size:.8rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;text-decoration:none;}
    .btn-view{background:var(--c-soft);color:var(--c-primary);}
    .btn-approve{background:#dcfce7;color:#166534;}
    .btn-forward{background:#dbeafe;color:#1d4ed8;}
    .btn-complete{background:#fef3c7;color:#92400e;}
    .btn-reject{background:#fee2e2;color:#dc2626;}
    .empty{text-align:center;padding:48px 16px;color:var(--c-muted);font-weight:600;}
    .empty i{font-size:2.5rem;display:block;margin-bottom:10px;opacity:.35;}
    .alert-box{display:none;border-radius:12px;padding:12px 14px;margin-bottom:12px;font-weight:700;}
    .alert-box.show{display:block;}
    .alert-box.ok{background:#dcfce7;color:#166534;border:1px solid #86efac;}
    .alert-box.err{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
    .view-overlay{
      position:fixed;inset:0;background:rgba(15,40,36,.45);z-index:1200;
      display:none;align-items:flex-start;justify-content:center;padding:24px 12px;overflow:auto;
    }
    .view-overlay.show{display:flex;}
    .view-panel{
      width:min(920px,100%);background:#fff;border-radius:20px;box-shadow:0 24px 60px rgba(0,0,0,.2);
      margin:auto;padding:20px 22px 24px;border:1px solid var(--c-border);
    }
    .view-panel-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:14px;}
    .view-panel-head h2{margin:0;font-size:1.1rem;font-weight:800;color:var(--c-text);}
    .view-close{border:none;background:var(--c-soft);color:var(--c-primary);border-radius:10px;padding:8px 12px;font-weight:800;cursor:pointer;}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:12px;}
    .info-item{background:var(--c-soft);border-radius:12px;padding:11px 12px;}
    .info-lbl{font-size:.72rem;font-weight:700;color:var(--c-muted);margin-bottom:3px;}
    .info-val{font-size:.88rem;font-weight:800;color:var(--c-text);word-break:break-word;}
    .text-block{background:#f8fafc;border:1px solid var(--c-border);border-radius:12px;padding:11px 12px;font-size:.86rem;font-weight:600;line-height:1.7;white-space:pre-wrap;margin-bottom:10px;}
    .view-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;}
    @media (max-width:900px){.stats-row{grid-template-columns:repeat(2,minmax(0,1fr));}}
    @media (max-width:575px){
      .stats-row{grid-template-columns:1fr;}
      .filter-bar{flex-direction:column;align-items:stretch;}
      .form-control,.form-select,.btn-brand,.btn-soft{width:100%;}
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>

<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-inboxes-fill"></i> طلبات التمويل</h1>
      <div class="sub" id="pageHeroSub">متابعة الطلبات حسب الصلاحية والمسار المعتمد</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="finance-cloud.php" class="btn-soft"><i class="bi bi-cloud-fill"></i> السحابة</a>
      <a href="finance.php" class="btn-soft"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
  </div>

  <div class="scope-note" id="scopeNote">جاري تحديد نطاق المتابعة...</div>

  <div class="stats-row">
    <div class="stat-card"><div class="label">الإجمالي</div><div class="value" id="statTotal">—</div></div>
    <div class="stat-card"><div class="label">بانتظار الفرع</div><div class="value" id="statBranch">—</div></div>
    <div class="stat-card"><div class="label">مراجعة التمويل</div><div class="value" id="statFinance">—</div></div>
    <div class="stat-card"><div class="label">معتمد / سحابة</div><div class="value" id="statApproved">—</div></div>
  </div>

  <div class="filter-bar">
    <div>
      <label style="display:block;font-size:.78rem;font-weight:700;color:var(--c-muted);margin-bottom:4px">الحالة</label>
      <select id="filterStatus" class="form-select">
        <option value="">كل الحالات</option>
        <option value="submitted">مُرسل</option>
        <option value="branch_review">مراجعة فرع</option>
        <option value="needs_completion">يحتاج استكمال</option>
        <option value="funder_review">مراجعة تمويل</option>
        <option value="approved">معتمد</option>
        <option value="funded">ممول</option>
        <option value="rejected">مرفوض</option>
        <option value="draft">مسودة</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:.78rem;font-weight:700;color:var(--c-muted);margin-bottom:4px">بحث</label>
      <input type="search" id="filterSearch" class="form-control" placeholder="رقم الطلب أو اسم المشروع" style="min-width:220px">
    </div>
    <button type="button" class="btn-brand" id="btnRefresh"><i class="bi bi-arrow-clockwise"></i> تحديث</button>
    <a href="finance-apply.php" class="btn-soft" id="btnNewApplication"><i class="bi bi-plus-circle-fill"></i> طلب جديد</a>
  </div>

  <div id="listMessage" class="alert-box"></div>
  <div id="loadingBox" class="empty"><i class="bi bi-hourglass-split"></i>جاري التحميل...</div>
  <div id="requestsContainer"></div>
</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>

<div class="view-overlay finance-view-overlay" id="viewOverlay" aria-hidden="true">
  <div class="view-panel" role="dialog" aria-modal="true" aria-labelledby="viewTitle">
    <div class="view-panel-head">
      <div>
        <h2 id="viewTitle">ملخص الطلب</h2>
        <div id="viewSubtitle" style="color:var(--c-muted);font-size:.84rem;font-weight:700;margin-top:4px"></div>
      </div>
      <button type="button" class="view-close" id="viewCloseBtn"><i class="bi bi-x-lg"></i> إغلاق</button>
    </div>
    <div id="viewBody"><div class="empty" style="padding:28px"><i class="bi bi-hourglass-split"></i>جاري التحميل...</div></div>
    <div class="view-actions" id="viewActions"></div>
  </div>
</div>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.7"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-applications-list.js?v=1.7"></script>
</body>
</html>
