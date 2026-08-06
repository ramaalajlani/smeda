<?php
$basePath = '../../';
$pageTitle = 'طلب تسجيل دورة';
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
    .member-card{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:16px;
      padding:18px;
      margin-bottom:14px;
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
        <span>طلب تسجيل دورة</span>
      </div>
      <h1 class="fw-bold mb-3">طلب تسجيل دورة</h1>
      <p class="section-subtitle">
        تسجيل فردي أو تسجيل من جهة أعلى/ولي أمر مع أعضاء تابعين ضمن طلب واحد.
      </p>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="courseRegistrationMessage" class="registration-message"></div>

      <div class="form-panel">
        <form id="courseRegistrationForm">
          <div class="row g-3">
            <div class="col-lg-6">
              <label class="form-label">الدورة التدريبية</label>
              <select id="trainingCourseId" class="form-select" required>
                <option value="">— اختر الدورة —</option>
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">نمط التسجيل</label>
              <select id="registrationMode" class="form-select">
                <option value="self">تسجيل فردي</option>
                <option value="guardian_with_dependents">تسجيل جهة أعلى مع تابعين</option>
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">نوع مقدم الطلب</label>
              <select id="submittedByType" class="form-select">
                <option value="self">ذاتي</option>
                <option value="guardian">ولي أمر / جهة أعلى</option>
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">اسم مقدم الطلب</label>
              <input type="text" id="applicantName" class="form-control">
            </div>

            <div class="col-lg-6">
              <label class="form-label">هاتف مقدم الطلب</label>
              <input type="text" id="applicantPhone" class="form-control">
            </div>

            <div class="col-lg-6">
              <label class="form-label">بريد مقدم الطلب</label>
              <input type="email" id="applicantEmail" class="form-control">
            </div>

            <div class="col-lg-6 guardian-fields d-none">
              <label class="form-label">اسم الجهة الأعلى / الولي</label>
              <input type="text" id="guardianName" class="form-control">
            </div>

            <div class="col-lg-6 guardian-fields d-none">
              <label class="form-label">هاتف الجهة الأعلى / الولي</label>
              <input type="text" id="guardianPhone" class="form-control">
            </div>

            <div class="col-lg-6 guardian-fields d-none">
              <label class="form-label">الرقم الوطني للجهة الأعلى / الولي</label>
              <input type="text" id="guardianNationalId" class="form-control">
            </div>

            <div class="col-12">
              <label class="form-label">ملاحظات</label>
              <textarea id="notes" class="form-control" rows="4"></textarea>
            </div>
          </div>

          <hr class="my-4">

          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">الأعضاء التابعون</h3>
            <button type="button" id="addMemberBtn" class="btn btn-outline-primary">
              إضافة تابع
            </button>
          </div>

          <div id="membersContainer"></div>

          <div class="d-flex gap-2 flex-wrap mt-4">
            <button type="submit" id="submitCourseRegistrationBtn" class="btn btn-brand">
              إرسال الطلب
            </button>
            <button type="reset" class="btn btn-outline-secondary">إعادة تعيين</button>
          </div>
        </form>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/course-registration-request.js?v=1.0"></script>
</body>
</html>