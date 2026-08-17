<?php
$basePath = '../../';
$pageTitle = 'الحقائب التدريبية';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .bags-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }
  </style>
</head>
<body>

<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="services-hero">
    <div class="container">
      <div class="breadcrumb-soft">
        <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
        <span>/</span>
        <a href="<?php echo $basePath; ?>services/index.php">خدمات المشروعات</a>
        <span>/</span>
        <span>الحقائب التدريبية</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">الحقائب التدريبية المعتمدة</h1>
          <p class="section-subtitle">
            استعراض الحقائب التدريبية وفق التصنيف المؤسسي: القطاع، الصنف، النوع،
            المستوى، وعدد البرامج والدورات المرتبطة.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">يتضمن العرض</h3>
            <ul class="feature-list mb-0">
              <li>التصنيف الأكاديمي والمهني</li>
              <li>الكود الفريد للحقيبة</li>
              <li>عدد البرامج والدورات</li>
              <li>عدد المدربين المرتبطين</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="h5 fw-bold mb-0">جدول الحقائب</h2>
        <?php if (true): ?>
        <a href="training-bag-form.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> إضافة حقيبة</a>
        <?php endif; ?>
      </div>

      <div class="services-grid-card mb-4">
        <div class="row g-3 align-items-end">
          <div class="col-lg-4">
            <label for="trainingSearchInput" class="form-label fw-bold">البحث</label>
            <input type="text" id="trainingSearchInput" class="form-control" placeholder="اسم، كود، وصف...">
          </div>
          <div class="col-lg-3">
            <label for="trainingCategoryFilter" class="form-label fw-bold">التصنيف</label>
            <select id="trainingCategoryFilter" class="form-select"><option value="">الكل</option></select>
          </div>
          <div class="col-lg-3">
            <label for="trainingStatusFilter" class="form-label fw-bold">الحالة</label>
            <select id="trainingStatusFilter" class="form-select">
              <option value="">الكل</option>
              <option value="draft">مسودة</option>
              <option value="under_review">قيد المراجعة</option>
              <option value="approved">معتمدة</option>
              <option value="published">منشورة</option>
              <option value="inactive">غير نشطة</option>
              <option value="archived">مؤرشفة</option>
            </select>
          </div>
          <div class="col-lg-2">
            <button type="button" id="resetTrainingFilters" class="soft-btn w-100 border-0">إعادة</button>
          </div>
        </div>
      </div>

      <div id="bagsLoadingBox" class="bags-loading-box">جاري تحميل بيانات الحقائب...</div>

      <div class="table-responsive bg-white border rounded-4">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>الكود</th><th>الاسم</th><th>التصنيف</th><th>التخصص</th><th>المستوى</th><th>ساعات</th>
              <th>ترويجي</th><th>PDF</th><th>الحالة</th><th>إجراءات</th>
            </tr>
          </thead>
          <tbody id="bagsTableBody"></tbody>
        </table>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-kits-list.js?v=3.0"></script>

</body>
</html>