<?php
$basePath = '../../';
$pageTitle = 'البرامج التدريبية';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .programs-loading-box{
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
        <span>البرامج التدريبية</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">البرامج التدريبية المعتمدة</h1>
          <p class="section-subtitle">
            استعراض البرامج التدريبية المعتمدة وربط كل برنامج بالحقائب التابعة له
            وعدد الدورات والشهادات المرتبطة.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">خصائص البرنامج</h3>
            <ul class="feature-list mb-0">
              <li>الاسم والكود</li>
              <li>الوصف المختصر</li>
              <li>عدد الحقائب والدورات</li>
              <li>الحالة العامة</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="programsLoadingBox" class="programs-loading-box">
        جاري تحميل بيانات البرامج التدريبية...
      </div>

      <div class="row g-4" id="trainingProgramsContainer"></div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>


<script src="<?php echo $basePath; ?>assets/js/pages/training-programs-list.js"></script>

</body>
</html>