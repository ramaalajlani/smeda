<?php
$basePath = '../../';
$pageTitle = 'طلب فرصة عمل';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
</head>
<body>

<?php include '../../includes/layout/header.php'; ?>

<main>
  <section class="services-hero">
    <div class="container">
      <h1 class="fw-bold mb-3">طلب فرصة عمل</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div id="jobRequestMessage" class="alert d-none"></div>
      <div class="form-panel">
        <form id="jobRequestForm">
          <input type="hidden" id="jobPostingId" value="">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label">الاسم الكامل</label>
              <input type="text" id="applicantName" class="form-control" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">رقم الهاتف</label>
              <input type="text" id="applicantPhone" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">البريد الإلكتروني</label>
              <input type="email" id="applicantEmail" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">الاختصاص المطلوب</label>
              <input type="text" id="applicantSpecialty" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">المدينة</label>
              <input type="text" id="applicantCity" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">سنوات الخبرة</label>
              <input type="text" id="experienceYears" class="form-control" />
            </div>
            <div class="col-12">
              <label class="form-label">نبذة مختصرة</label>
              <textarea id="applicantSummary" class="form-control form-textarea" rows="5"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">السيرة الذاتية (PDF/DOC)</label>
              <input type="file" id="cvFile" class="form-control" accept=".pdf,.doc,.docx" />
            </div>
            <div class="col-12">
              <button type="submit" id="jobRequestSubmitBtn" class="btn btn-brand">إرسال الطلب</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>

<?php include '../../includes/layout/footer.php'; ?>
<?php include '../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/job-request.js?v=1.0"></script>
</body>
</html>
