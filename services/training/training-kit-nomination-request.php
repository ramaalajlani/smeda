<?php
$basePath = '../../';
$pageTitle = 'طلب ترشيح حقيبة تدريبية';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>

  <style>
    .nomination-message{
      margin-top:16px;
      font-size:.95rem;
    }

    .nomination-message.success{
      color:#198754;
    }

    .nomination-message.error{
      color:#dc3545;
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
        <span>طلب ترشيح حقيبة تدريبية</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">طلب ترشيح حقيبة تدريبية</h1>
          <p class="section-subtitle">
            إذا لم تكن الحقيبة متاحة حالياً في مركز محدد، يمكن للمستخدم إرسال طلب
            ترشيح ليصل إلى المراكز المعنية ويتيح فرصة لتقديمها.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="form-panel">
        <form id="kitNominationForm">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label">الاسم الكامل</label>
              <input
                type="text"
                id="fullNameInput"
                class="form-control"
                placeholder="أدخل الاسم"
              />
            </div>

            <div class="col-md-6">
              <label class="form-label">البريد الإلكتروني</label>
              <input
                type="email"
                id="emailInput"
                class="form-control"
                placeholder="name@example.com"
              />
            </div>

            <div class="col-md-6">
              <label class="form-label">اسم الحقيبة</label>
              <input
                type="text"
                id="kitNameInput"
                class="form-control"
                placeholder="مثال: إدارة المشاريع"
              />
            </div>

            <div class="col-md-6">
              <label class="form-label">المدينة / المركز المطلوب</label>
              <input
                type="text"
                id="cityInput"
                class="form-control"
                placeholder="مثال: دمشق"
              />
            </div>

            <div class="col-12">
              <label class="form-label">ملاحظات إضافية</label>
              <textarea
                id="notesInput"
                class="form-control form-textarea"
                rows="5"
                placeholder="أدخل أي ملاحظات إضافية"
              ></textarea>
            </div>

            <div class="col-12">
              <button type="submit" id="submitNominationBtn" class="btn btn-brand">
                إرسال الطلب
              </button>
            </div>
          </div>

          <div id="nominationMessage" class="nomination-message d-none"></div>
        </form>
      </div>
    </div>
  </section>
</main>

<?php include '../../includes/layout/footer.php'; ?>
<?php include '../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/training-kit-nomination-request.js?v=2.0"></script>

</body>
</html>