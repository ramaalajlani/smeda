<?php
$basePath = '../../';
$activePage = 'admin-units';
$pageTitle = 'الوحدات الإدارية';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>
</head>
<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>
<section class="services-hero"><div class="container"><h1 class="fw-bold mb-2">الوحدات الإدارية</h1></div></section>
  <section class="section pt-0">
    <div class="container">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>المحافظة</th><th>المنطقة</th><th>الوحدة</th><th>الحالة</th></tr></thead>
          <tbody id="adminUnitsBody"><tr><td colspan="4">جاري التحميل...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>
<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-platform.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/admin-units.js?v=1.0"></script>
</body>
</html>
