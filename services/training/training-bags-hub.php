<?php
$basePath = '../../';
$pageTitle = 'الحقائب التدريبية';
$activePage = 'training-bags-hub';
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
    <div class="breadcrumb-soft">
      <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
      <span>/</span>
      <span>الحقائب التدريبية</span>
    </div>
    <h1 class="fw-bold mb-2">الحقائب التدريبية</h1>
    <p class="section-subtitle mb-0">إدارة الحقائب التدريبية، التصنيفات، والملفات (PDF) — نقطة البداية لمنظومة المركز التدريبي.</p>
  </div>
</section>

<section class="section pt-0">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6 col-xl-4">
        <a href="training-kits-list.php" class="text-decoration-none">
          <div class="bg-white border rounded-4 p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-2"><i class="bi bi-list-ul fs-3 text-primary"></i><h2 class="h5 fw-bold mb-0">جميع الحقائب</h2></div>
            <p class="text-muted small mb-0">استعراض، بحث، فلترة، وتحميل الملفات.</p>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-xl-4">
        <a href="training-bag-form.php" class="text-decoration-none">
          <div class="bg-white border rounded-4 p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-2"><i class="bi bi-plus-circle-fill fs-3 text-success"></i><h2 class="h5 fw-bold mb-0">إضافة حقيبة</h2></div>
            <p class="text-muted small mb-0">نموذج متكامل مع رفع ملف PDF للحقيبة.</p>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-xl-4">
        <a href="training-categories-manage.php" class="text-decoration-none">
          <div class="bg-white border rounded-4 p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-2"><i class="bi bi-diagram-3-fill fs-3 text-info"></i><h2 class="h5 fw-bold mb-0">التصنيفات</h2></div>
            <p class="text-muted small mb-0">تصنيفات رئيسية وتخصصات فرعية.</p>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-xl-4">
        <a href="training-kits-list.php?workflow_status=draft" class="text-decoration-none">
          <div class="bg-white border rounded-4 p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-2"><i class="bi bi-pencil-square fs-3 text-secondary"></i><h2 class="h5 fw-bold mb-0">المسودات</h2></div>
            <p class="text-muted small mb-0">حقائب قيد الإعداد.</p>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-xl-4">
        <a href="training-kits-list.php?workflow_status=published" class="text-decoration-none">
          <div class="bg-white border rounded-4 p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-2"><i class="bi bi-check-circle-fill fs-3 text-success"></i><h2 class="h5 fw-bold mb-0">المنشورة</h2></div>
            <p class="text-muted small mb-0">حقائب جاهزة للاستخدام.</p>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-xl-4">
        <a href="training-kits-list.php?workflow_status=archived" class="text-decoration-none">
          <div class="bg-white border rounded-4 p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-2"><i class="bi bi-archive-fill fs-3 text-muted"></i><h2 class="h5 fw-bold mb-0">المؤرشفة</h2></div>
            <p class="text-muted small mb-0">حقائب مؤرشفة.</p>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

<?php include '../../includes/layout/app-shell-close.php'; ?>
</body>
</html>
