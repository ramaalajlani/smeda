<?php
$basePath = '../../';
$pageTitle = 'التحقق من التوقيع الإلكتروني';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>

  <style>
    .verification-message{
      margin-top:16px;
      font-size:.95rem;
      font-weight:700;
    }

    .verification-message.success{ color:#198754; }
    .verification-message.error{ color:#dc3545; }

    .verification-status-box{
      border-radius:18px;
      padding:16px 18px;
      margin-bottom:18px;
      border:1px solid #e5e7eb;
      background:#fff;
    }

    .verification-status-box.valid{
      border-color:rgba(25,135,84,.22);
      background:rgba(25,135,84,.05);
    }

    .verification-status-box.invalid{
      border-color:rgba(220,53,69,.22);
      background:rgba(220,53,69,.05);
    }

    .verification-status-title{
      font-size:1rem;
      font-weight:800;
      margin-bottom:6px;
    }

    .verification-status-text{
      margin:0;
      color:#6b7280;
      line-height:1.8;
    }

    .mini-card h3{
      font-size:1rem;
      font-weight:800;
      margin-bottom:10px;
    }

    .mini-card p,
    .mini-card .value-text{
      margin-bottom:0;
      color:#374151;
      line-height:1.9;
      word-break:break-word;
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
        <span>التحقق من التوقيع الإلكتروني</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">التحقق من التوقيع الإلكتروني</h1>
          <p class="section-subtitle">
            تحقق من صحة توقيع المدير العام أو نائب المدير العام على الشهادات المعتمدة
            عبر رمز التحقق المطبوع على الشهادة بصيغة ESIG-XXXX-XXXX.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">ما الذي يتحقق منه النظام؟</h3>
            <ul class="feature-list mb-0">
              <li>اسم الموقّع ومنصبه</li>
              <li>تاريخ التوقيع</li>
              <li>رمز الشهادة المرتبطة</li>
              <li>سلامة التوقيع ضد التلاعب</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="form-panel">
        <form id="signatureVerificationForm">
          <div class="row g-3">
            <div class="col-lg-8">
              <label class="form-label">رمز التحقق من التوقيع (ESIG)</label>
              <input
                type="text"
                id="signatureCode"
                class="form-control"
                placeholder="مثال: ESIG-ABCD-1234"
                autocomplete="off"
              />
            </div>

            <div class="col-lg-4 d-flex align-items-end">
              <button type="submit" id="verifySignatureBtn" class="btn btn-brand w-100">تحقق من التوقيع</button>
            </div>
          </div>

          <div id="signatureVerificationMessage" class="verification-message d-none"></div>
        </form>
      </div>

      <div class="services-grid-card mt-4">
        <h3 class="mb-3">نتيجة التحقق</h3>

        <div id="signatureStatusBox" class="verification-status-box d-none">
          <div id="signatureStatusTitle" class="verification-status-title">—</div>
          <p id="signatureStatusText" class="verification-status-text">—</p>
        </div>

        <div class="row g-3" id="signatureResultGrid">
          <div class="col-12">
            <div class="mini-card h-100 text-muted">
              <h3>لا توجد نتيجة بعد</h3>
              <p class="mb-0">أدخل رمز ESIG المطبوع على الشهادة ثم اضغط تحقق.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include '../../includes/layout/footer.php'; ?>
<?php include '../../includes/layout/scripts.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/signature-verification.js?v=1.0"></script>

</body>
</html>
