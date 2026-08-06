<?php
$basePath = '../../';
$pageTitle = 'عرض / نشر فرصة عمل';
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
      <h1 class="fw-bold mb-3" id="jobPostPageTitle">عرض / نشر فرصة عمل</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div id="jobPostMessage" class="alert d-none"></div>
      <div class="form-panel">
        <form id="jobPostForm">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label">اسم الجهة / المشروع</label>
              <input type="text" id="organizationName" class="form-control" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">المسمى الوظيفي</label>
              <input type="text" id="jobTitle" class="form-control" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">المدينة</label>
              <input type="text" id="jobCity" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">نوع الدوام</label>
              <select id="employmentType" class="form-select">
                <option value="full_time">دوام كامل</option>
                <option value="part_time">دوام جزئي</option>
                <option value="contract">عقد مؤقت</option>
                <option value="freelance">عمل حر</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">القطاع</label>
              <input type="text" id="jobSector" class="form-control" />
            </div>
            <div class="col-12">
              <label class="form-label">وصف الوظيفة</label>
              <textarea id="jobDescription" class="form-control form-textarea" rows="5"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">المهارات المطلوبة</label>
              <textarea id="jobSkills" class="form-control form-textarea" rows="4"></textarea>
            </div>
            <div class="col-12">
              <button type="submit" id="jobPostSubmitBtn" class="btn btn-brand">نشر فرصة العمل</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>

<?php include '../../includes/layout/footer.php'; ?>
<?php include '../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/job-post.js?v=1.0"></script>
</body>
</html>
