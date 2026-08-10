<?php
$basePath = '../../';
$pageTitle = 'طلب تدريب موظفين / كوادر';
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
      <h1 class="fw-bold mb-3">طلب تدريب موظفين / كوادر</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div id="staffTrainingMessage" class="alert d-none"></div>
      <div class="form-panel">
        <form id="staffTrainingForm">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label">اسم الجهة</label>
              <input type="text" id="organizationName" class="form-control" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">عدد الموظفين</label>
              <input type="number" id="employeesCount" class="form-control" min="1" value="1" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">المجال التدريبي</label>
              <input type="text" id="trainingField" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">المدينة</label>
              <input type="text" id="trainingCity" class="form-control" />
            </div>
            <div class="col-12">
              <label class="form-label">تفاصيل الاحتياج التدريبي</label>
              <textarea id="trainingDetails" class="form-control form-textarea" rows="5"></textarea>
            </div>
            <div class="col-12">
              <button type="submit" id="staffTrainingSubmitBtn" class="btn btn-brand">إرسال الطلب</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>

<?php include '../../includes/layout/footer.php'; ?>
<?php include '../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/staff-training-request.js?v=1.0"></script>
</body>
</html>
