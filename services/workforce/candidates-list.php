<?php
$basePath = '../../';
$pageTitle = 'القوى العاملة المتاحة';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>
</head>
<body>

<?php include '../../includes/layout/app-shell-open.php'; ?>

  <section class="services-hero pb-3">
    <div class="container-fluid px-0">
      <div class="breadcrumb-soft mb-3">
        <a href="<?php echo $basePath; ?>dashboard.php">لوحة التحكم</a>
        <span>/</span>
        <span>القوى العاملة المتاحة</span>
      </div>

      <span class="section-badge">القوى العاملة</span>
      <h1 class="fw-bold mb-2">القوى العاملة المتاحة</h1>
      <p class="section-subtitle mb-0">
        استعراض المتدربين المؤهلين المسجلين ضمن القوى العاملة بعد حصولهم على شهادات معتمدة.
      </p>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container-fluid px-0">
      <div id="workforceMessageBox" class="alert d-none"></div>

      <div class="filter-bar mb-3">
        <div class="row g-3">
          <div class="col-lg-5">
            <input type="text" id="workforceSearchInput" class="form-control" placeholder="بحث بالاسم أو الرمز">
          </div>
          <div class="col-lg-4">
            <select id="workforceStatusFilter" class="form-select">
              <option value="">كل الحالات</option>
              <option value="active">نشط</option>
              <option value="inactive">غير نشط</option>
            </select>
          </div>
          <div class="col-lg-3">
            <button type="button" id="workforceSearchBtn" class="btn btn-brand w-100">بحث</button>
          </div>
        </div>
      </div>

      <div id="workforceLoadingBox" class="courses-loading-box">جاري تحميل بيانات القوى العاملة...</div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>رمز القوى العاملة</th>
                <th>اسم المتدرب</th>
                <th>رمز المتدرب</th>
                <th>المدينة</th>
                <th>التواصل</th>
                <th>الحالة</th>
                <th>تاريخ الانضمام</th>
              </tr>
            </thead>
            <tbody id="workforceTableBody">
              <tr>
                <td colspan="8" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div id="workforceEnrollBox" class="form-panel mt-4 d-none">
        <h3 class="mb-3">إدخال متدرب إلى القوى العاملة</h3>
        <p class="text-muted">يتطلب أن يكون للمتدرب شهادة معتمدة وموثقة.</p>
        <form id="workforceEnrollForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">المتدرب</label>
              <select id="enrollTraineeId" class="form-select" required></select>
            </div>
            <div class="col-md-6">
              <label class="form-label">ملاحظات</label>
              <input type="text" id="enrollNotes" class="form-control" placeholder="اختياري">
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-brand">إدخال إلى القوى العاملة</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

<?php include '../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/workforce-candidates-list.js?v=1.1"></script>
</body>
</html>
