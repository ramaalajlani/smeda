<?php
$basePath = '../../';
$pageTitle = 'مراجعة ترشيحات الحقائب';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .review-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .review-message{
      display:none;
      margin-bottom:20px;
      padding:14px 16px;
      border-radius:14px;
      font-weight:700;
      font-size:.95rem;
      line-height:1.8;
    }

    .review-message.success{
      display:block;
      background:rgba(25,135,84,.08);
      color:#198754;
      border:1px solid rgba(25,135,84,.16);
    }

    .review-message.error{
      display:block;
      background:rgba(220,53,69,.08);
      color:#dc3545;
      border:1px solid rgba(220,53,69,.16);
    }

    .table td,.table th{
      vertical-align:middle;
      white-space:normal;
    }

    .review-actions{
      min-width:180px;
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
        <span>مراجعة ترشيحات الحقائب</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">مراجعة ترشيحات الحقائب</h1>
          <p class="section-subtitle">
            مراجعة الترشيحات المرسلة من المدربين واتخاذ قرار القبول أو الرفض أو إبقائها قيد المراجعة.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">تتضمن الصفحة</h3>
            <ul class="feature-list mb-0">
              <li>المدرب مقدم الترشيح</li>
              <li>اسم الحقيبة أو المقترح</li>
              <li>بيانات الترشيح الكاملة</li>
              <li>إجراء قبول / رفض / مراجعة</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="reviewMessage" class="review-message"></div>

      <div id="reviewLoadingBox" class="review-loading-box">
        جاري تحميل ترشيحات الحقائب...
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>المدرب</th>
                <th>الحقيبة</th>
                <th>القطاع</th>
                <th>التصنيف</th>
                <th>الساعات</th>
                <th>الحالة</th>
                <th>الوصف</th>
                <th class="review-actions">إجراء</th>
              </tr>
            </thead>
            <tbody id="reviewNominationsTableBody">
              <tr>
                <td colspan="9" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-kit-nominations-review.js?v=1.1"></script>

</body>
</html>