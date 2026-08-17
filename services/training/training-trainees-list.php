<?php
$basePath = '../../';
$pageTitle = 'المتدربون';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .trainees-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }
    .light-alt-section{ margin-top:32px; }
    .trainee-actions{ display:flex; flex-wrap:wrap; gap:8px; }
    .table td,.table th{ vertical-align:middle; white-space:normal; }
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
        <span>المتدربون</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">سجل المتدربين</h1>
          <p class="section-subtitle">
            قائمة كاملة بالمتدربين المسجّلين في النظام مع عدد الدورات والشهادات وحالتهم.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">محتوى الصفحة</h3>
            <ul class="feature-list mb-0">
              <li>الاسم الثلاثي واسم الأم</li>
              <li>الجنس وتاريخ الميلاد</li>
              <li>المدينة والحالة</li>
              <li>عدد الدورات والشهادات</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section stats-section">
    <div class="container">
      <div class="section-heading text-center">
        <h2>مؤشرات المتدربين</h2>
        <p>ملخص سريع عن المتدربين في نطاق صلاحياتك.</p>
      </div>

      <div class="row g-4 justify-content-center metrics-row">
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <h2 id="totalTraineesCounter">0</h2>
            <p>إجمالي المتدربين</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <h2 id="activeTraineesCounter">0</h2>
            <p>نشطون</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <h2 id="withCoursesCounter">0</h2>
            <p>لديهم دورات</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <h2 id="withCertificatesCounter">0</h2>
            <p>لديهم شهادات</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div class="filter-bar">
        <div class="row g-3">
          <div class="col-lg-5">
            <input type="text" id="traineeSearchInput" class="form-control" placeholder="ابحث بالاسم أو الرقم أو الهاتف أو المدينة">
          </div>
          <div class="col-lg-3">
            <select id="traineeStatusFilter" class="form-select">
              <option value="">كل الحالات</option>
              <option value="active">نشط</option>
              <option value="inactive">غير نشط</option>
              <option value="suspended">موقوف</option>
            </select>
          </div>
          <div class="col-lg-2">
            <button type="button" id="traineeSearchBtn" class="btn btn-brand w-100">بحث</button>
          </div>
          <div class="col-lg-2">
            <button type="button" id="traineeResetBtn" class="btn btn-outline-secondary w-100">إعادة</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="traineesLoadingBox" class="trainees-loading-box">
        جاري تحميل سجل المتدربين...
      </div>

      <div id="traineesEmptyState" class="alert alert-light border d-none text-center">
        لا يوجد متدربون ضمن نطاق صلاحياتك حالياً.
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>رقم المتدرب</th>
                <th>الاسم الثلاثي</th>
                <th>اسم الأم</th>
                <th>الجنس</th>
                <th>تاريخ الميلاد</th>
                <th>المدينة</th>
                <th>الحالة</th>
                <th>الدورات</th>
                <th>الشهادات</th>
                <th>إجراء</th>
              </tr>
            </thead>
            <tbody id="traineesTableBody">
              <tr>
                <td colspan="11" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <section class="section light-alt-section">
    <div class="container">
      <div class="section-heading text-center">
        <h2>روابط مرتبطة</h2>
        <p>انتقل مباشرة إلى بقية صفحات التدريب المرتبطة.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-6 col-xl-3">
          <div class="mini-card text-center h-100">
            <h3>المدربون</h3>
            <p>عرض المدربين المعتمدين مع أرقام الاعتماد والتصنيف.</p>
            <a href="training-trainers-list.php" class="soft-btn mt-3">الدخول</a>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="mini-card text-center h-100">
            <h3>المراكز التدريبية</h3>
            <p>استعراض المراكز وتصنيفها واعتمادها.</p>
            <a href="training-centers-list.php" class="soft-btn mt-3">الدخول</a>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="mini-card text-center h-100">
            <h3>الدورات</h3>
            <p>عرض الدورات التدريبية ومتدربيها.</p>
            <a href="training-courses-list.php" class="soft-btn mt-3">الدخول</a>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="mini-card text-center h-100">
            <h3>الشهادات</h3>
            <p>عرض الشهادات الصادرة والمعتمدة.</p>
            <a href="training-certificates-list.php" class="soft-btn mt-3">الدخول</a>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-trainees-list.js?v=2.2"></script>

</body>
</html>
