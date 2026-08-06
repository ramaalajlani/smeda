<?php
$basePath = '../../';
$activePage = 'needs-list';
$pageTitle = 'سجل الاحتياجات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>
</head>
<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>
<section class="services-hero">
    <div class="container">
      <div class="breadcrumb-soft">
        <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
        <span>/</span>
        <span>سجل الاحتياجات</span>
      </div>
      <h1 class="fw-bold mb-2">سجل الاحتياجات</h1>
      <p class="section-subtitle mb-0">استعراض وبحث احتياجات المواطن والدولة ضمن نطاق صلاحياتك.</p>
    </div>
  </section>
  <section class="section pt-0">
    <div class="container">
      <div class="row g-2 mb-3">
        <div class="col-md-4"><input type="search" id="needsSearch" class="form-control" placeholder="بحث..."></div>
        <div class="col-md-3"><select id="needsStatusFilter" class="form-select"><option value="">كل الحالات</option></select></div>
        <div class="col-md-3"><select id="needsSectorFilter" class="form-select"><option value="">كل القطاعات</option></select></div>
        <div class="col-md-2 text-md-end">
          <a href="need-create.php" class="btn btn-brand w-100" data-permission="needs.create">تسجيل احتياج</a>
        </div>
      </div>
      <div id="needsListLoading" class="alert alert-light border">جاري التحميل...</div>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>الكود</th>
              <th>العنوان</th>
              <th>النوع</th>
              <th>القطاع</th>
              <th>المحافظة</th>
              <th>الحالة</th>
              <th>إجراء</th>
            </tr>
          </thead>
          <tbody id="needsListBody"></tbody>
        </table>
      </div>
    </div>
  </section>
<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-platform.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-list.js?v=1.3"></script>
</body>
</html>
