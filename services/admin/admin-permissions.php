<?php
$basePath = '../../';
$pageTitle = 'إدارة الصلاحيات';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>
</head>
<body>
<?php include '../../includes/layout/app-shell-open.php'; ?>
<section class="services-hero">
    <div class="container">
      <h1 class="fw-bold mb-3">إدارة الصلاحيات</h1>
    </div>
  </section>
  <section class="section pt-0">
    <div class="container">
      <div id="adminPermissionsMessage" class="alert d-none"></div>
      <div id="adminPermissionsLoading" class="courses-loading-box">جاري التحميل...</div>
      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr><th>#</th><th>الصلاحية</th><th>الوحدة</th><th>عدد الأدوار</th></tr>
            </thead>
            <tbody id="adminPermissionsTableBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/admin-permissions.js?v=2.0"></script>
</body>
</html>
