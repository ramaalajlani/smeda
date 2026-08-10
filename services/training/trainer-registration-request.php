<?php
$basePath = '../../';
$pageTitle = 'طلب تسجيل مدرب';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>
  <style>
    .registration-message{
      display:none;
      margin-bottom:20px;
      padding:14px 16px;
      border-radius:14px;
      font-weight:700;
      line-height:1.8;
    }
    .registration-message.success{
      display:block;
      background:rgba(25,135,84,.08);
      color:#198754;
      border:1px solid rgba(25,135,84,.16);
    }
    .registration-message.error{
      display:block;
      background:rgba(220,53,69,.08);
      color:#dc3545;
      border:1px solid rgba(220,53,69,.16);
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
        <span>طلب تسجيل مدرب</span>
      </div>
      <h1 class="fw-bold mb-3">طلب تسجيل مدرب</h1>
      <p class="section-subtitle">إرسال طلب تسجيل مدرب جديد وربطه بالمركز عند الحاجة.</p>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="trainerRegistrationMessage" class="registration-message"></div>

      <div class="form-panel">
        <form id="trainerRegistrationForm">
          <div class="row g-3">
            <div class="col-lg-6">
              <label class="form-label">المركز التدريبي</label>
              <select id="trainingCenterId" class="form-select">
                <option value="">— بدون مركز —</option>
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">الاسم الكامل</label>
              <input type="text" id="fullName" class="form-control" required>
            </div>

            <div class="col-lg-6">
              <label class="form-label">الرقم الوطني</label>
              <input type="text" id="nationalId" class="form-control">
            </div>

            <div class="col-lg-6">
              <label class="form-label">الهاتف</label>
              <input type="text" id="phone" class="form-control">
            </div>

            <div class="col-lg-6">
              <label class="form-label">البريد الإلكتروني</label>
              <input type="email" id="email" class="form-control">
            </div>

            <div class="col-lg-6">
              <label class="form-label">التخصص</label>
              <input type="text" id="specialization" class="form-control">
            </div>

            <div class="col-lg-6">
              <label class="form-label">التصنيف المطلوب</label>
              <input type="text" id="classificationRequested" class="form-control">
            </div>

            <div class="col-lg-6">
              <label class="form-label">هل يحمل ToT؟</label>
              <select id="hasTot" class="form-select">
                <option value="1">نعم</option>
                <option value="0" selected>لا</option>
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">رقم شهادة ToT</label>
              <input type="text" id="totCertificateNumber" class="form-control">
            </div>

            <div class="col-lg-6">
              <label class="form-label">جهة إصدار ToT</label>
              <input type="text" id="totCertificateSource" class="form-control">
            </div>

            <div class="col-12 d-flex gap-2 flex-wrap">
              <button type="submit" id="submitTrainerRegistrationBtn" class="btn btn-brand">إرسال الطلب</button>
              <button type="reset" class="btn btn-outline-secondary">إعادة تعيين</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/trainer-registration-request.js?v=1.0"></script>
</body>
</html>