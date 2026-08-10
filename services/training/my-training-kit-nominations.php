<?php
$basePath = '../../';
$pageTitle = 'ترشيحاتي';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .nominations-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .nominations-empty-state{
      background:#fff;
      border:1px dashed #d7dee8;
      border-radius:16px;
      padding:32px 20px;
      text-align:center;
      color:#6c757d;
      margin-bottom:24px;
    }

    .table td,.table th{
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
        <span>ترشيحاتي</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">ترشيحاتي للحقائب التدريبية</h1>
          <p class="section-subtitle">
            عرض جميع ترشيحات الحقائب التدريبية التي قمت بإرسالها، مع حالة كل ترشيح وملاحظات الإدارة إن وجدت.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">تتضمن الصفحة</h3>
            <ul class="feature-list mb-0">
              <li>اسم الحقيبة أو الحقيبة المرتبطة</li>
              <li>القطاع والتصنيف</li>
              <li>عدد الساعات</li>
              <li>حالة الترشيح وملاحظات القرار</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="myNominationsLoadingBox" class="nominations-loading-box">
        جاري تحميل الترشيحات...
      </div>

      <div id="myNominationsEmptyState" class="nominations-empty-state d-none">
        لا توجد ترشيحات مرسلة حتى الآن.
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>الحقيبة</th>
                <th>القطاع</th>
                <th>التصنيف</th>
                <th>الساعات</th>
                <th>الحالة</th>
                <th>ملاحظات القرار</th>
                <th>التاريخ</th>
              </tr>
            </thead>
            <tbody id="myNominationsTableBody">
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

<script src="<?php echo $basePath; ?>assets/js/pages/my-training-kit-nominations.js?v=1.0"></script>

</body>
</html>