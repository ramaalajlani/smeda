<?php

$basePath = '../../';

$pageTitle = 'إدارة المستخدمين';

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

      <div>

        <h1 class="fw-bold mb-2">إدارة المستخدمين</h1>

        <p class="section-subtitle mb-0">إنشاء وتعديل وإدارة المستخدمين والأدوار والصلاحيات.</p>

      </div>

      <button type="button" id="adminUserCreateBtn" class="btn btn-brand">+ إنشاء مستخدم</button>

    </div>

  </section>

  <section class="section pt-0">

    <div class="container">

      <div id="adminUsersMessage" class="alert d-none"></div>

      <div class="filter-bar mb-3">

        <div class="row g-3">

          <div class="col-lg-3"><input type="text" id="filterSearch" class="form-control" placeholder="بحث بالاسم أو البريد"></div>

          <div class="col-lg-2">

            <select id="filterRole" class="form-select"><option value="">كل الأدوار</option></select>

          </div>

          <div class="col-lg-2">

            <select id="filterStatus" class="form-select">

              <option value="">كل الحالات</option>

              <option value="1">فعال</option>

              <option value="0">معطل</option>

            </select>

          </div>

          <div class="col-lg-2"><input type="date" id="filterCreatedFrom" class="form-control"></div>

          <div class="col-lg-2"><input type="date" id="filterCreatedTo" class="form-control"></div>

          <div class="col-lg-1"><button type="button" id="filterApplyBtn" class="btn btn-brand w-100">بحث</button></div>

        </div>

      </div>

      <div id="adminUsersLoading" class="courses-loading-box">جاري التحميل...</div>

      <div class="data-table-wrap">

        <div class="table-responsive">

          <table class="table align-middle">

            <thead>

              <tr>

                <th>ID</th>

                <th>الاسم</th>

                <th>البريد</th>

                <th>الأدوار</th>

                <th>المحافظة</th>

                <th>الفرع</th>

                <th>الصلاحيات</th>

                <th>الحالة</th>

                <th>آخر دخول</th>

                <th>إجراءات</th>

              </tr>

            </thead>

            <tbody id="adminUsersTableBody"></tbody>

          </table>

        </div>

      </div>

    </div>

  </section>

</main>



<!-- Create/Edit Modal -->

<div class="modal fade" id="userFormModal" tabindex="-1">

  <div class="modal-dialog modal-lg modal-dialog-scrollable">

    <div class="modal-content">

      <form id="userForm">

        <div class="modal-header"><h5 class="modal-title" id="userFormTitle">مستخدم</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

        <div class="modal-body row g-3">

          <input type="hidden" id="userFormId">

          <div class="col-md-6"><label class="form-label">الاسم</label><input type="text" id="userFormName" class="form-control" required></div>

          <div class="col-md-6"><label class="form-label">البريد</label><input type="email" id="userFormEmail" class="form-control" required></div>

          <div class="col-md-6"><label class="form-label">الهاتف</label><input type="text" id="userFormPhone" class="form-control"></div>

          <div class="col-md-6" id="userFormPasswordWrap"><label class="form-label">كلمة المرور</label><input type="password" id="userFormPassword" class="form-control" minlength="8"></div>

          <div class="col-md-6" id="userFormPasswordConfirmWrap"><label class="form-label">تأكيد كلمة المرور</label><input type="password" id="userFormPasswordConfirm" class="form-control" minlength="8"></div>

          <div class="col-md-6"><label class="form-label">الدور</label><select id="userFormRole" class="form-select" required></select></div>

          <div class="col-md-6"><label class="form-label">الحالة</label><select id="userFormActive" class="form-select"><option value="1">فعال</option><option value="0">معطل</option></select></div>

          <div class="col-md-6"><label class="form-label">المركز</label><select id="userFormCenter" class="form-select"><option value="">—</option></select></div>

          <div class="col-md-6 d-none" id="userFormGovernorateWrap"><label class="form-label">المحافظة</label><select id="userFormGovernorate" class="form-select"><option value="">— اختر المحافظة —</option></select></div>

          <div class="col-md-6 d-none" id="userFormBranchWrap"><label class="form-label">الفرع</label><select id="userFormBranch" class="form-select"><option value="">— اختر الفرع —</option></select></div>

        </div>

        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-brand">حفظ</button></div>

      </form>

    </div>

  </div>

</div>



<!-- Password Modal -->

<div class="modal fade" id="passwordModal" tabindex="-1">

  <div class="modal-dialog"><div class="modal-content">

    <form id="passwordForm">

      <div class="modal-header"><h5 class="modal-title">تغيير كلمة المرور</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body">

        <input type="hidden" id="passwordUserId">

        <div class="mb-3"><label class="form-label">كلمة المرور الجديدة</label><input type="password" id="passwordNew" class="form-control" required minlength="8"></div>

        <div class="mb-3"><label class="form-label">تأكيد كلمة المرور</label><input type="password" id="passwordConfirm" class="form-control" required minlength="8"></div>

      </div>

      <div class="modal-footer"><button type="submit" class="btn btn-brand">حفظ</button></div>

    </form>

  </div></div>

</div>



<!-- Roles Modal -->

<div class="modal fade" id="rolesModal" tabindex="-1">

  <div class="modal-dialog modal-dialog-scrollable"><div class="modal-content">

    <form id="rolesForm">

      <div class="modal-header"><h5 class="modal-title">إدارة الأدوار</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body">
        <input type="hidden" id="rolesUserId">
        <div id="rolesCheckboxList" class="d-grid gap-2 mb-3"></div>
        <div id="rolesBranchScopeWrap" class="d-none border-top pt-3">
          <p class="text-muted small mb-2">مطلوب عند تعيين دور <strong>branch_manager</strong></p>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">المحافظة</label>
              <select id="rolesFormGovernorate" class="form-select"><option value="">— اختر المحافظة —</option></select>
            </div>
            <div class="col-md-6">
              <label class="form-label">الفرع</label>
              <select id="rolesFormBranch" class="form-select"><option value="">— اختر الفرع —</option></select>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer"><button type="submit" class="btn btn-brand">حفظ الأدوار</button></div>

    </form>

  </div></div>

</div>



<!-- Permissions Modal -->

<div class="modal fade" id="permissionsModal" tabindex="-1">

  <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">

    <form id="permissionsForm">

      <div class="modal-header"><h5 class="modal-title">الصلاحيات المباشرة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body"><input type="hidden" id="permissionsUserId"><div id="permissionsCheckboxList"></div></div>

      <div class="modal-footer"><button type="submit" class="btn btn-brand">حفظ الصلاحيات</button></div>

    </form>

  </div></div>

</div>



<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/admin-users.js?v=4.4"></script>

</body>

</html>

