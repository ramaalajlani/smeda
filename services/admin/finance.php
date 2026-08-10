<?php

$basePath = '../../';

$pageTitle = 'الملفات المالية والتمويل';

$activePage = 'services';

?>

<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?></head>

<body>

<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="services-hero">

    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">

      <div>

        <h1 class="fw-bold mb-2">الملفات المالية والتمويل</h1>

        <p class="section-subtitle mb-0">متابعة التمويل والمدفوعات والالتزامات</p>

      </div>

      <button type="button" class="btn btn-brand d-none" id="addFinanceBtn">+ إضافة سجل</button>

    </div>

  </section>

  <section class="section pt-0">

    <div class="container">

      <div id="financeMessage" class="alert d-none"></div>

      <div id="financeLoading" class="courses-loading-box">جاري التحميل...</div>

      <div class="row g-3 mb-4" id="financeStats"></div>

      <div class="data-table-wrap">

        <div class="table-responsive">

          <table class="table align-middle">

            <thead><tr><th>#</th><th>العنوان</th><th>النوع</th><th>المبلغ</th><th>الفرع</th><th>الحالة</th><th>إجراءات</th></tr></thead>

            <tbody id="financeTableBody"></tbody>

          </table>

        </div>

      </div>

    </div>

  </section>

</main>



<div class="modal fade" id="financeFormModal" tabindex="-1">

  <div class="modal-dialog modal-lg"><div class="modal-content">

    <form id="financeForm">

      <div class="modal-header"><h5 class="modal-title" id="financeFormTitle">إضافة سجل مالي</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body">

        <input type="hidden" id="financeFormId">

        <div class="row g-3">

          <div class="col-md-6"><label class="form-label">العنوان</label><input type="text" id="financeFormTitleInput" class="form-control" required></div>

          <div class="col-md-6"><label class="form-label">النوع</label><select id="financeFormType" class="form-select"><option value="funding">تمويل</option><option value="payment">دفع</option><option value="commitment">التزام</option><option value="revenue">إيراد</option></select></div>

          <div class="col-md-4"><label class="form-label">المبلغ</label><input type="number" step="0.01" id="financeFormAmount" class="form-control" required></div>

          <div class="col-md-4"><label class="form-label">العملة</label><input type="text" id="financeFormCurrency" class="form-control" value="SYP"></div>

          <div class="col-md-4"><label class="form-label">الحالة</label><select id="financeFormStatus" class="form-select"><option value="draft">مسودة</option><option value="approved">معتمد</option></select></div>

          <div class="col-md-6"><label class="form-label">الفرع (اختياري)</label><select id="financeFormBranch" class="form-select"><option value="">—</option></select></div>

          <div class="col-12"><label class="form-label">ملاحظات</label><textarea id="financeFormNotes" class="form-control" rows="2"></textarea></div>

        </div>

      </div>

      <div class="modal-footer"><button type="submit" class="btn btn-brand">حفظ</button></div>

    </form>

  </div></div>

</div>



<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?= $basePath ?>assets/js/pages/finance.js?v=1.0"></script>

</body>

</html>

