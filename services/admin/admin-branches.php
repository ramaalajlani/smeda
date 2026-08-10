<?php

$basePath = '../../';

$pageTitle = 'المحافظات والفروع';

$activePage = 'services';

?>

<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?></head>

<body>

<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="services-hero">

    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">

      <div><h1 class="fw-bold mb-0">المحافظات والفروع</h1></div>

      <button type="button" class="btn btn-brand d-none" id="branchCreateBtn">+ إضافة فرع</button>

    </div>

  </section>

  <section class="section pt-0"><div class="container">

    <div id="branchesMessage" class="alert d-none"></div>

    <div id="branchesLoading" class="courses-loading-box">جاري التحميل...</div>

    <div class="row g-3 mb-4" id="governoratesGrid"></div>

    <div class="data-table-wrap mt-4"><div class="table-responsive">

      <table class="table align-middle"><thead><tr><th>الفرع</th><th>المحافظة</th><th>الرمز</th><th>مدير الفرع</th><th>الحالة</th><th>إجراءات</th></tr></thead>

      <tbody id="branchesTableBody"></tbody></table>

    </div></div>

  </div></section>

</main>



<div class="modal fade" id="branchFormModal" tabindex="-1">

  <div class="modal-dialog"><div class="modal-content">

    <form id="branchForm">

      <div class="modal-header"><h5 class="modal-title" id="branchFormTitle">إضافة فرع</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body">

        <input type="hidden" id="branchFormId">

        <div class="mb-3"><label class="form-label">اسم الفرع</label><input type="text" id="branchFormName" class="form-control" required></div>

        <div class="mb-3"><label class="form-label">الرمز</label><input type="text" id="branchFormCode" class="form-control" required></div>

        <div class="mb-3"><label class="form-label">المحافظة</label><select id="branchFormGovernorate" class="form-select" required></select></div>

        <div class="mb-3"><label class="form-label">مدير الفرع</label><select id="branchFormManager" class="form-select"><option value="">— بدون مدير —</option></select></div>

        <div class="mb-3"><label class="form-label">الحالة</label><select id="branchFormActive" class="form-select"><option value="1">فعال</option><option value="0">معطل</option></select></div>

        <div class="mb-3"><label class="form-label">ملاحظات</label><textarea id="branchFormNotes" class="form-control" rows="2"></textarea></div>

      </div>

      <div class="modal-footer"><button type="submit" class="btn btn-brand">حفظ</button></div>

    </form>

  </div></div>

</div>



<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?= $basePath ?>assets/js/pages/admin-branches.js?v=3.0"></script>

</body>

</html>

