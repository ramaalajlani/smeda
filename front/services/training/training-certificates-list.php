<?php
$basePath = '../../';
$pageTitle = 'الشهادات التدريبية';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .certificates-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .table td,
    .table th{
      vertical-align:middle;
      white-space:normal;
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
        <span>الشهادات التدريبية</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">الشهادات التدريبية</h1>
          <p class="section-subtitle">
            عرض جميع الشهادات التدريبية الصادرة، بنوعيها شهادة حضور وشهادة اجتياز،
            مع حالة الاعتماد، النتيجة، الساعات، وروابط الطباعة والتحقق عند توفرها.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">تتضمن الصفحة</h3>
            <ul class="feature-list mb-0">
              <li>رقم الشهادة والرقم المرجعي</li>
              <li>اسم المتدرب</li>
              <li>نوع الشهادة والنتيجة</li>
              <li>الحالة وروابط الطباعة والتحقق</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="certificatesLoadingBox" class="certificates-loading-box">
        جاري تحميل بيانات الشهادات التدريبية...
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>رقم الشهادة</th>
                <th>اسم المتدرب</th>
                <th>نوع الشهادة</th>
                <th>النتيجة</th>
                <th>العلامة / الساعات</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody id="certificatesTableBody">
              <tr>
                <td colspan="8" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-certificates-list.js?v=1.1"></script>

</body>
</html>