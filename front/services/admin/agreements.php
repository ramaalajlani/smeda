<?php

$basePath = '../../';

$pageTitle = 'الاتفاقيات';

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

        <h1 class="fw-bold mb-2">الاتفاقيات</h1>

        <p class="section-subtitle mb-0">إدارة ومتابعة الاتفاقيات الوطنية</p>

      </div>

      <button type="button" class="btn btn-brand d-none" id="addAgreementBtn">+ إضافة اتفاقية</button>

    </div>

  </section>

  <section class="section pt-0">

    <div class="container">

      <div id="agreementsMessage" class="alert d-none"></div>

      <div id="agreementsLoading" class="courses-loading-box">جاري التحميل...</div>

      <div class="row g-3 mb-4" id="agreementsStats"></div>

      <div class="data-table-wrap">

        <div class="table-responsive">

          <table class="table align-middle">

            <thead><tr><th>#</th><th>العنوان</th><th>الجهة</th><th>النطاق</th><th>الحالة</th><th>تاريخ البدء</th><th>إجراءات</th></tr></thead>

            <tbody id="agreementsTableBody"></tbody>

          </table>

        </div>

      </div>

    </div>

  </section>

</main>



<div class="modal fade" id="agreementFormModal" tabindex="-1">

  <div class="modal-dialog modal-lg"><div class="modal-content">

    <form id="agreementForm">

      <div class="modal-header"><h5 class="modal-title" id="agreementFormTitle">إضافة اتفاقية</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body">

        <input type="hidden" id="agreementFormId">

        <div class="row g-3">

          <div class="col-md-6"><label class="form-label">العنوان</label><input type="text" id="agreementFormTitleInput" class="form-control" required></div>

          <div class="col-md-6"><label class="form-label">الجهة الشريكة</label><input type="text" id="agreementFormPartner" class="form-control" required></div>

          <div class="col-md-4"><label class="form-label">النوع</label><input type="text" id="agreementFormType" class="form-control" value="general"></div>
          <div class="col-md-4"><label class="form-label">نطاق الاتفاقية</label><select id="agreementFormScope" class="form-select"><option value="national">مركزية</option><option value="branch">خاصة بفرع</option></select></div>
          <div class="col-md-4"><label class="form-label">الحالة</label><select id="agreementFormStatus" class="form-select"><option value="draft">مسودة</option><option value="active">نشطة</option><option value="expired">منتهية</option></select></div>
          <div class="col-md-4"><label class="form-label">المبلغ</label><input type="number" step="0.01" id="agreementFormAmount" class="form-control"></div>
          <div id="agreementBranchScopeWrap" class="col-12 d-none">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">المحافظة</label><select id="agreementFormGovernorate" class="form-select"></select></div>
              <div class="col-md-6"><label class="form-label">الفرع</label><select id="agreementFormBranch" class="form-select"></select></div>
            </div>
          </div>

          <div class="col-md-6"><label class="form-label">تاريخ البدء</label><input type="date" id="agreementFormStart" class="form-control"></div>

          <div class="col-md-6"><label class="form-label">تاريخ الانتهاء</label><input type="date" id="agreementFormEnd" class="form-control"></div>

          <div class="col-12"><label class="form-label">ملاحظات</label><textarea id="agreementFormNotes" class="form-control" rows="2"></textarea></div>

        </div>

      </div>

      <div class="modal-footer"><button type="submit" class="btn btn-brand">حفظ</button></div>

    </form>

  </div></div>

</div>



<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?= $basePath ?>assets/js/pages/agreements.js?v=1.0"></script>

</body>

</html>

