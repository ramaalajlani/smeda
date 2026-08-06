<?php
$basePath = '../../';
$pageTitle = 'اعتماد الشهادات';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .certificate-approvals-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .certificate-approvals-message{
      display:none;
      margin-bottom:20px;
      padding:14px 16px;
      border-radius:14px;
      font-weight:700;
      font-size:.95rem;
      line-height:1.8;
    }

    .certificate-approvals-message.success{
      display:block;
      background:rgba(25,135,84,.08);
      color:#198754;
      border:1px solid rgba(25,135,84,.16);
    }

    .certificate-approvals-message.error{
      display:block;
      background:rgba(220,53,69,.08);
      color:#dc3545;
      border:1px solid rgba(220,53,69,.16);
    }

    .certificate-approvals-note{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:18px 20px;
      color:#64748b;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
      line-height:1.9;
      font-weight:600;
    }

    .table td, .table th{
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
        <span>اعتماد الشهادات</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">اعتماد الشهادات</h1>
          <p class="section-subtitle">
            تمر الشهادات بمراحل اعتماد متسلسلة: المركز، قسم التدريب، نائب المدير العام (توقيع إلكتروني)،
            ثم المدير العام (توقيع إلكتروني نهائي). تعرض هذه الصفحة المرحلة المرتبطة بصلاحيات حسابك الحالي.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">تتضمن الصفحة</h3>
            <ul class="feature-list mb-0">
              <li>عرض الشهادات حسب مرحلة الاعتماد</li>
              <li>إتاحة الموافقة أو الرفض حسب الصلاحية</li>
              <li>إظهار حالة كل مرحلة بشكل واضح</li>
              <li>تحديث مباشر بعد تنفيذ الإجراء</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div class="certificate-approvals-note">
        تعرض هذه الصفحة فقط الشهادات التي تخص مرحلة الاعتماد المرتبطة بحسابك الحالي.
        لن تظهر لك إلا السجلات التي تملك صلاحية اتخاذ إجراء عليها.
      </div>

      <div id="certificateApprovalsMessage" class="certificate-approvals-message"></div>

      <div id="certificateApprovalsLoadingBox" class="certificate-approvals-loading-box">
        جاري تحميل بيانات اعتماد الشهادات...
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>رقم الشهادة</th>
                <th>المتدرب</th>
                <th>اعتماد المركز</th>
                <th>اعتماد التدريب</th>
                <th>اعتماد نائب المدير</th>
                <th>اعتماد المدير العام</th>
                <th>الحالة</th>
                <th>الإجراء</th>
              </tr>
            </thead>
            <tbody id="certificateApprovalsTableBody">
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

<script src="<?php echo $basePath; ?>assets/js/pages/training-certificates-approve.js?v=1.1"></script>

</body>
</html>