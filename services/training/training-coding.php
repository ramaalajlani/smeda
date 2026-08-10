<?php
$basePath = '../../';
$pageTitle = 'التكويد والترميز التدريبي';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>

  <style>
    .coding-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15, 23, 42, .04);
      margin-bottom:24px;
    }
  </style>
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
        <span>التكويد والترميز التدريبي</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">التكويد والترميز التدريبي</h1>
          <p class="section-subtitle">
            صفحة مرجعية لعرض بنية التكويد المعتمدة للقطاع، الصنف، النوع،
            المادة التدريبية، المستوى، والشهادات.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">

      <div id="codingLoadingBox" class="coding-loading-box">
        جاري تحميل بيانات التكويد والترميز التدريبي...
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>القطاع</th>
                <th>الصنف</th>
                <th>النوع</th>
                <th>المادة</th>
                <th>المستوى</th>
                <th>الكود</th>
              </tr>
            </thead>
            <tbody id="trainingCodingTableBody">
              <tr>
                <td colspan="6" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</main>

<?php include '../../includes/layout/footer.php'; ?>
<?php include '../../includes/layout/scripts.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/core/config.js"></script>
<script src="<?php echo $basePath; ?>assets/js/core/routes.js"></script>
<script src="<?php echo $basePath; ?>assets/js/core/helpers.js"></script>
<script src="<?php echo $basePath; ?>assets/js/core/api.js"></script>
<script src="<?php echo $basePath; ?>assets/js/core/ui.js"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/training-coding.js"></script>

</body>
</html>