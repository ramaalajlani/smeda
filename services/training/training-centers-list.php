<?php
$basePath = '../../';
$pageTitle = 'المراكز التدريبية';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .centers-empty-state{
      background:#fff;
      border:1px dashed #d7dee8;
      border-radius:16px;
      padding:32px 20px;
      text-align:center;
      color:#6c757d;
    }

    .centers-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
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
        <span>المراكز التدريبية</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">المراكز التدريبية المعتمدة</h1>
          <p class="section-subtitle">
            قائمة بالمراكز التدريبية المعتمدة مع مواقعها وتصنيفها وحالة الاعتماد.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">يعرض المركز</h3>
            <ul class="feature-list mb-0">
              <li>الاسم والموقع</li>
              <li>التصنيف</li>
              <li>الحالة</li>
              <li>نوع التنفيذ</li>
              <li>الشهادة و PDF</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="centersLoadingBox" class="centers-loading-box mb-4">
        جاري تحميل بيانات المراكز التدريبية...
      </div>

      <div id="centersEmptyState" class="centers-empty-state d-none mb-4">
        لا توجد مراكز تدريبية متاحة حالياً.
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>الاسم</th>
                <th>الموقع</th>
                <th>التصنيف</th>
                <th>الحالة</th>
                <th>نوع التنفيذ</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody id="trainingCentersTableBody">
              <tr>
                <td colspan="6" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-centers-list.js?v=1.1"></script>

</body>
</html>