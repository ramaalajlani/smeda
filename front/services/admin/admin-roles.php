<?php

$basePath = '../../';

$pageTitle = 'إدارة الأدوار';

$activePage = 'services';

?>

<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>

  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

</head>

<body>

<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="services-hero">

    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">

      <h1 class="fw-bold mb-0">إدارة الأدوار</h1>

      <button type="button" id="roleCreateBtn" class="btn btn-brand">+ دور جديد</button>

    </div>

  </section>

  <section class="section pt-0">

    <div class="container">

      <div id="adminRolesMessage" class="alert d-none"></div>

      <div id="adminRolesLoading" class="courses-loading-box">جاري التحميل...</div>

      <div class="data-table-wrap">

        <div class="table-responsive">

          <table class="table align-middle">

            <thead>

              <tr><th>#</th><th>الدور</th><th>المستخدمون</th><th>الصلاحيات</th><th>إجراءات</th></tr>

            </thead>

            <tbody id="adminRolesTableBody"></tbody>

          </table>

        </div>

      </div>

    </div>

  </section>

</main>



<div class="modal fade" id="roleFormModal" tabindex="-1">

  <div class="modal-dialog"><div class="modal-content">

    <form id="roleForm">

      <div class="modal-header"><h5 class="modal-title" id="roleFormTitle">دور</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body">

        <input type="hidden" id="roleFormId">

        <label class="form-label">اسم الدور</label>

        <input type="text" id="roleFormName" class="form-control" required pattern="[a-z0-9_]+">

      </div>

      <div class="modal-footer"><button type="submit" class="btn btn-brand">حفظ</button></div>

    </form>

  </div></div>

</div>



<div class="modal fade" id="rolePermissionsModal" tabindex="-1">

  <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">

    <form id="rolePermissionsForm">

      <div class="modal-header"><h5 class="modal-title">صلاحيات الدور</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body"><input type="hidden" id="rolePermissionsId"><div id="rolePermissionsList"></div></div>

      <div class="modal-footer"><button type="submit" class="btn btn-brand">حفظ الصلاحيات</button></div>

    </form>

  </div></div>

</div>



<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/admin-roles.js?v=2.0"></script>

</body>

</html>

