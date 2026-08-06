<?php
$basePath = '../../';
$pageTitle = 'فرص العمل المتاحة';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
</head>
<body>

<?php include '../../includes/layout/header.php'; ?>

<main>
  <section class="services-hero">
    <div class="container">
      <div class="breadcrumb-soft">
        <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
        <span>/</span>
        <a href="<?php echo $basePath; ?>services/index.php">خدمات المشروعات</a>
        <span>/</span>
        <span>فرص العمل المتاحة</span>
      </div>
      <h1 class="fw-bold mb-3">فرص العمل المتاحة</h1>
      <p class="section-subtitle mb-0">استعراض فرص العمل المنشورة وربطها بطلبات التوظيف.</p>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="jobsMessage" class="alert d-none"></div>
      <div class="filter-bar mb-4">
        <div class="row g-3">
          <div class="col-lg-4">
            <input type="text" id="jobsSearchInput" class="form-control" placeholder="ابحث باسم الوظيفة أو الجهة" />
          </div>
          <div class="col-lg-3">
            <select id="jobsSectorFilter" class="form-select">
              <option value="">كل القطاعات</option>
              <option value="إداري">إداري</option>
              <option value="تقني">تقني</option>
              <option value="مالي">مالي</option>
              <option value="تسويقي">تسويقي</option>
            </select>
          </div>
          <div class="col-lg-3">
            <input type="text" id="jobsCityFilter" class="form-control" placeholder="المدينة" />
          </div>
          <div class="col-lg-2">
            <button type="button" id="jobsSearchBtn" class="btn btn-brand w-100">بحث</button>
          </div>
        </div>
      </div>

      <div id="jobsLoading" class="text-center py-4">جاري التحميل...</div>
      <div id="jobsGrid" class="row g-4"></div>
    </div>
  </section>
</main>

<?php include '../../includes/layout/footer.php'; ?>
<?php include '../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/jobs-list.js?v=1.0"></script>
</body>
</html>
