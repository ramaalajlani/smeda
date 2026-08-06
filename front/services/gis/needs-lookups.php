<?php
$basePath = '../../';
$activePage = 'needs-lookups';
$pageTitle = 'إدارة قوائم الاحتياجات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>
  <style>
    .lookup-group-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
    .lookup-group-head {
      background: #f8fafc; border-bottom: 1px solid #e5e7eb; padding: 12px 18px;
      font-weight: 700; font-size: 14px; color: #1e40af;
      display: flex; align-items: center; gap: 8px;
    }
    .lookup-table { width: 100%; font-size: 13px; }
    .lookup-table th { font-size: 11px; color: #6b7280; font-weight: 600; padding: 8px 14px; border-bottom: 1px solid #f1f5f9; }
    .lookup-table td { padding: 8px 14px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
    .lookup-table tr:last-child td { border-bottom: none; }
    .lookup-table code { font-size: 11px; color: #7c3aed; }
    .sort-input { width: 70px; }
    .row-inactive td { opacity: .5; }
    .badge-inactive { background: #fee2e2; color: #b91c1c; font-size: 10px; padding: 2px 8px; border-radius: 10px; }
    .badge-active { background: #dcfce7; color: #15803d; font-size: 10px; padding: 2px 8px; border-radius: 10px; }
  </style>
</head>
<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>
<section class="services-hero">
    <div class="container">
      <h1 class="fw-bold mb-2">إدارة القوائم المرجعية للاحتياجات</h1>
      <p class="section-subtitle mb-0">تفعيل وتعطيل وترتيب تصنيفات الاحتياجات وأنواع المنشآت والقطاعات — دون حذف القيم المستخدمة في احتياجات قديمة.</p>
    </div>
  </section>
  <section class="section pt-0">
    <div class="container" style="max-width: 980px">
      <div id="lookupsMessage" class="alert d-none mb-3"></div>
      <div id="lookupsLoading" class="alert alert-light border">جاري التحميل...</div>
      <div id="lookupsContainer"></div>
    </div>
  </section>
<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-platform.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-lookups.js?v=2.0"></script>
</body>
</html>
