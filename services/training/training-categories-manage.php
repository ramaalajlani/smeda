<?php
$basePath = '../../';
$pageTitle = 'إدارة تصنيفات الحقائب';
$activePage = 'training-categories-manage';
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
    <div class="breadcrumb-soft mb-2">
      <a href="training-bags-hub.php">الحقائب التدريبية</a>
      <span>/</span>
      <span>التصنيفات</span>
    </div>
    <h1 class="fw-bold">إدارة تصنيفات الحقائب</h1>
  </div>
</section>

<section class="section pt-0">
  <div class="container">
    <div class="services-grid-card mb-4">
      <h2 class="h6 fw-bold mb-3">إضافة / تعديل تصنيف</h2>
      <form id="catForm" class="row g-3 align-items-end">
        <input type="hidden" id="catId">
        <div class="col-md-3"><label class="form-label">نوع</label>
          <select class="form-select" id="catParent"><option value="">رئيسي</option></select>
        </div>
        <div class="col-md-3"><label class="form-label">الاسم (عربي) *</label><input class="form-control" id="catNameAr" required></div>
        <div class="col-md-2"><label class="form-label">الاسم (EN)</label><input class="form-control" id="catNameEn"></div>
        <div class="col-md-2"><label class="form-label">الترتيب</label><input type="number" class="form-control" id="catSort" value="0" min="0"></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">حفظ</button></div>
      </form>
      <div id="catMsg" class="small mt-2"></div>
    </div>

    <div class="table-responsive bg-white border rounded-4">
      <table class="table table-hover mb-0">
        <thead><tr><th>الاسم</th><th>النوع</th><th>الترتيب</th><th>الحالة</th><th></th></tr></thead>
        <tbody id="catTableBody"><tr><td colspan="5" class="text-center text-muted p-4">جاري التحميل...</td></tr></tbody>
      </table>
    </div>
  </div>
</section>

<?php include '../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/training-categories-manage.js?v=1.0"></script>
</body>
</html>
