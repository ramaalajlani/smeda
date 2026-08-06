<?php
$basePath = '../../';
$pageTitle = 'المدربون المعتمدون';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .trainers-empty-state{
      background:#fff;
      border:1px dashed #d7dee8;
      border-radius:16px;
      padding:32px 20px;
      text-align:center;
      color:#6c757d;
    }

    .trainers-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
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
        <span>المدربون المعتمدون</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">المدربون المعتمدون</h1>
          <p class="section-subtitle">
            استعراض قائمة المدربين المعتمدين مع التخصص، التصنيف، حالة الاعتماد،
            وبيان امتلاك شهادة ToT.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">يعرض الملف</h3>
            <ul class="feature-list mb-0">
              <li>رقم المدرب</li>
              <li>الاسم والتخصص</li>
              <li>التصنيف</li>
              <li>الحالة وToT</li>
              <li>بطاقة المدرب و PDF</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="trainersLoadingBox" class="trainers-loading-box mb-4">
        جاري تحميل بيانات المدربين...
      </div>

      <div id="trainersEmptyState" class="trainers-empty-state d-none mb-4">
        لا توجد بيانات مدربين حالياً.
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>رقم المدرب</th>
                <th>الاسم</th>
                <th>التخصص</th>
                <th>التصنيف</th>
                <th>الحالة</th>
                <th>ToT</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody id="trainersTableBody">
              <tr>
                <td colspan="7" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-trainers-list.js?v=1.0"></script>

</body>
</html>