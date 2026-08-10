document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.AppAccess.guardAccessAdmin()) return;

  const state = { roles: [], permissionsGrouped: {}, centers: [], governorates: [], branches: [], currentUserId: Number(window.AppAuth.getUser()?.id || 0) };
  const messageBox = document.getElementById('adminUsersMessage');
  const loading = document.getElementById('adminUsersLoading');
  const tbody = document.getElementById('adminUsersTableBody');
  const canManage = window.AppAuth.hasPermission('manage_user_access') || window.AppAuth.isPlatformAdmin();

  const userFormModal = new bootstrap.Modal(document.getElementById('userFormModal'));
  const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
  const rolesModal = new bootstrap.Modal(document.getElementById('rolesModal'));
  const permissionsModal = new bootstrap.Modal(document.getElementById('permissionsModal'));

  function showMessage(text, type = 'success') {
    if (!messageBox) return;
    messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'} mt-3`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  function getFilters() {
    return {
      search: document.getElementById('filterSearch')?.value?.trim() || undefined,
      role: document.getElementById('filterRole')?.value || undefined,
      is_active: document.getElementById('filterStatus')?.value ?? undefined,
      created_from: document.getElementById('filterCreatedFrom')?.value || undefined,
      created_to: document.getElementById('filterCreatedTo')?.value || undefined,
      per_page: 100,
    };
  }

  async function loadMeta() {
    const [rolesRes, permsRes, centersRes, govRes, branchesRes] = await Promise.all([
      window.APP_API.get(window.APP_ROUTES.adminRoles()),
      window.APP_API.get(window.APP_ROUTES.adminPermissions()),
      window.APP_API.get(window.APP_ROUTES.trainingCenters({ per_page: 100 })),
      window.APP_API.get(window.APP_ROUTES.governorates()).catch(() => ({ data: [] })),
      window.APP_API.get(window.APP_ROUTES.branches()).catch(() => ({ data: [] })),
    ]);
    state.roles = rolesRes?.data || [];
    const perms = permsRes?.data || {};
    state.permissionsGrouped = typeof perms === 'object' && !Array.isArray(perms) ? perms : { all: Array.isArray(perms) ? perms : [] };
    state.centers = centersRes?.data || [];
    state.governorates = govRes?.data || [];
    state.branches = branchesRes?.data || [];

    const roleSelects = [document.getElementById('filterRole'), document.getElementById('userFormRole')];
    roleSelects.forEach((sel) => {
      if (!sel) return;
      const keep = sel.id === 'filterRole' ? '<option value="">كل الأدوار</option>' : '';
      const rolesForSelect = sel.id === 'userFormRole'
        ? state.roles.filter((r) => {
            if (!window.APP_HELPERS.isExecutiveRole(r.name)) return true;
            return window.AppAuth.hasRole('general_director') || window.AppAuth.hasRole('super_admin');
          })
        : state.roles;
      sel.innerHTML = keep + rolesForSelect.map((r) => `<option value="${window.APP_HELPERS.e(r.name)}">${window.APP_HELPERS.e(window.APP_HELPERS.roleLabel(r.name))}</option>`).join('');
    });

    const centerSel = document.getElementById('userFormCenter');
    if (centerSel) {
      centerSel.innerHTML = '<option value="">—</option>' + state.centers.map((c) =>
        `<option value="${c.id}">${window.APP_HELPERS.e(c.name)}</option>`).join('');
    }

    const govSel = document.getElementById('userFormGovernorate');
    if (govSel) {
      govSel.innerHTML = SiteI18n.ta('<option value="">— اختر المحافظة —</option>') + state.governorates.map((g) =>
        `<option value="${g.id}">${window.APP_HELPERS.e(g.name_ar || g.name)}</option>`).join('');
    }

    document.getElementById('userFormRole')?.addEventListener('change', toggleBranchFields);
    document.getElementById('userFormGovernorate')?.addEventListener('change', filterBranchesByGovernorate);
    document.getElementById('rolesFormGovernorate')?.addEventListener('change', filterRolesBranchesByGovernorate);
  }

  function toggleBranchFields() {
    const role = document.getElementById('userFormRole')?.value || '';
    const show = ['branch_manager', 'branch_officer', 'center_user', 'trainer_user', 'trainee_user', 'training_supervisor', 'governor', 'data_entry', 'data_reviewer'].includes(role);
    document.getElementById('userFormGovernorateWrap')?.classList.toggle('d-none', !show);
    document.getElementById('userFormBranchWrap')?.classList.toggle('d-none', !show);
    if (show) filterBranchesByGovernorate();
  }

  function filterBranchesByGovernorate() {
    const govId = Number(document.getElementById('userFormGovernorate')?.value || 0);
    const branchSel = document.getElementById('userFormBranch');
    if (!branchSel) return;
    const filtered = state.branches.filter((b) => !govId || Number(b.governorate_id) === govId);
    branchSel.innerHTML = SiteI18n.ta('<option value="">— اختر الفرع —</option>') + filtered.map((b) =>
      `<option value="${b.id}">${window.APP_HELPERS.e(b.name)}</option>`).join('');
  }

  function buildActions(user) {
    if (!canManage || Number(user.id) === state.currentUserId) {
      return `<a href="admin-activity-logs.php?user_id=${user.id}" class="btn btn-sm btn-outline-secondary">السجل</a>`;
    }
    return `
      <div class="d-flex flex-wrap gap-1">
        <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${user.id}">تعديل</button>
        <button type="button" class="btn btn-sm btn-outline-warning" data-action="password" data-id="${user.id}">كلمة المرور</button>
        <button type="button" class="btn btn-sm btn-outline-info" data-action="roles" data-id="${user.id}">الأدوار</button>
        <button type="button" class="btn btn-sm btn-outline-dark" data-action="permissions" data-id="${user.id}">الصلاحيات</button>
        <button type="button" class="btn btn-sm btn-outline-${user.is_active ? 'danger' : 'success'}" data-action="toggle" data-id="${user.id}" data-active="${user.is_active ? '0' : '1'}">${user.is_active ? 'تعطيل': SiteI18n.ta('تفعيل')}</button>
        <a href="admin-activity-logs.php?user_id=${user.id}" class="btn btn-sm btn-outline-secondary">السجل</a>
      </div>`;
  }

  async function loadUsers() {
    try {
      const result = await window.APP_API.get(window.APP_ROUTES.adminUsers(getFilters()));
      const rows = result?.data || [];
      window.APP_UI.hideLoadingState(loading);
      tbody.innerHTML = rows.map((user) => `
        <tr>
          <td>${user.id}</td>
          <td>${window.APP_HELPERS.e(user.name)}</td>
          <td>${window.APP_HELPERS.e(user.email)}</td>
          <td>${window.APP_HELPERS.e(window.APP_HELPERS.roleLabels(user.role_names || []).join(SiteI18n.ta('، ')) || '—')}</td>
          <td>${window.APP_HELPERS.e(user.governorate_name || user.governorate?.name_ar || '—')}</td>
          <td>${window.APP_HELPERS.e(user.branch_name || user.branch?.name || '—')}</td>
          <td>${window.APP_HELPERS.safe(user.permissions_count, (user.effective_permissions || []).length)}</td>
          <td>${user.is_active ? '<span class="text-success">فعال</span>': SiteI18n.ta('<span class="text-danger">معطل</span>')}</td>
          <td>${window.APP_HELPERS.e(user.last_login_at || '—')}</td>
          <td>${buildActions(user)}</td>
        </tr>
      `).join('') || SiteI18n.ta('<tr><td colspan="10" class="text-center text-muted">لا يوجد مستخدمون.</td></tr>');
    } catch (error) {
      window.APP_UI.hideLoadingState(loading);
      window.APP_UI.renderErrorTable(tbody, 10, error?.data?.message || SiteI18n.ta('تعذر تحميل المستخدمين.'));
    }
  }

  async function openEditUser(id) {
    const res = await window.APP_API.get(window.APP_ROUTES.adminUserAccess(id));
    const user = res?.data;
    document.getElementById('userFormTitle').textContent = SiteI18n.ta('تعديل مستخدم');
    document.getElementById('userFormId').value = user.id;
    document.getElementById('userFormName').value = user.name || '';
    document.getElementById('userFormEmail').value = user.email || '';
    document.getElementById('userFormPhone').value = user.phone || '';
    document.getElementById('userFormActive').value = user.is_active ? '1' : '0';
    document.getElementById('userFormCenter').value = user.training_center_id || '';
    document.getElementById('userFormGovernorate').value = user.governorate_id || '';
    filterBranchesByGovernorate();
    document.getElementById('userFormBranch').value = user.branch_id || '';
    toggleBranchFields();
    document.getElementById('userFormPasswordWrap').classList.add('d-none');
    document.getElementById('userFormPasswordConfirmWrap').classList.add('d-none');
    document.getElementById('userFormRole').closest('.col-md-6').classList.add('d-none');
    userFormModal.show();
  }

  function openCreateUser() {
    document.getElementById('userFormTitle').textContent = SiteI18n.ta('إنشاء مستخدم جديد');
    document.getElementById('userForm').reset();
    document.getElementById('userFormId').value = '';
    document.getElementById('userFormPasswordWrap').classList.remove('d-none');
    document.getElementById('userFormPasswordConfirmWrap').classList.remove('d-none');
    document.getElementById('userFormRole').closest('.col-md-6').classList.remove('d-none');
    toggleBranchFields();
    userFormModal.show();
  }

  const NATIONAL_ROLES = ['general_director', 'deputy_general_director', 'admin', 'super_admin', 'system_admin', 'training_manager', 'auditor', 'deputy_director'];
  const BRANCH_ROLES = ['branch_manager', 'center_user', 'trainer_user', 'trainee_user'];

  function toggleRolesBranchFields() {
    const checked = [...document.querySelectorAll('#rolesCheckboxList input:checked')].map((el) => el.value);
    const needsBranch = checked.some((r) => BRANCH_ROLES.includes(r));
    const onlyNational = checked.length > 0 && checked.every((r) => NATIONAL_ROLES.includes(r));
    const show = needsBranch && !onlyNational;
    document.getElementById('rolesBranchScopeWrap')?.classList.toggle('d-none', !show);
    if (show) filterRolesBranchesByGovernorate();
  }

  function filterRolesBranchesByGovernorate() {
    const govId = Number(document.getElementById('rolesFormGovernorate')?.value || 0);
    const branchSel = document.getElementById('rolesFormBranch');
    if (!branchSel) return;
    const filtered = state.branches.filter((b) => !govId || Number(b.governorate_id) === govId);
    branchSel.innerHTML = SiteI18n.ta('<option value="">— اختر الفرع —</option>') + filtered.map((b) =>
      `<option value="${b.id}">${window.APP_HELPERS.e(b.name)}</option>`).join('');
  }

  async function openRolesModal(id) {
    const res = await window.APP_API.get(window.APP_ROUTES.adminUserAccess(id));
    const user = res?.data;
    document.getElementById('rolesUserId').value = id;
    const current = user.role_names || [];
    document.getElementById('rolesCheckboxList').innerHTML = state.roles
      .filter((role) => {
        if (!window.APP_HELPERS.isExecutiveRole(role.name)) return true;
        return window.AppAuth.hasRole('general_director') || window.AppAuth.hasRole('super_admin');
      })
      .map((role) => `
      <label class="form-check">
        <input class="form-check-input roles-role-checkbox" type="checkbox" name="roles[]" value="${window.APP_HELPERS.e(role.name)}" ${current.includes(role.name) ? 'checked' : ''}>
        <span class="form-check-label">${window.APP_HELPERS.e(window.APP_HELPERS.roleLabel(role.name))}</span>
      </label>
    `).join('');

    const rolesGovSel = document.getElementById('rolesFormGovernorate');
    if (rolesGovSel) {
      rolesGovSel.innerHTML = '<option value="">— اختر المحافظة —</option>' + state.governorates.map((g) =>
        `<option value="${g.id}">${window.APP_HELPERS.e(g.name_ar || g.name)}</option>`).join('');
      rolesGovSel.value = user.governorate_id || '';
    }
    filterRolesBranchesByGovernorate();
    const rolesBranchSel = document.getElementById('rolesFormBranch');
    if (rolesBranchSel) rolesBranchSel.value = user.branch_id || '';
    document.querySelectorAll('.roles-role-checkbox').forEach((el) => el.addEventListener('change', toggleRolesBranchFields));
    toggleRolesBranchFields();
    rolesModal.show();
  }

  async function openPermissionsModal(id) {
    const res = await window.APP_API.get(window.APP_ROUTES.adminUserAccess(id));
    const user = res?.data;
    document.getElementById('permissionsUserId').value = id;
    const current = user.direct_permissions || [];
    const grouped = user.permissions_by_module || state.permissionsGrouped;
    document.getElementById('permissionsCheckboxList').innerHTML = Object.entries(grouped).map(([module, perms]) => {
      if (!perms?.length) return '';
      return `
        <div class="mb-3">
          <h6 class="fw-bold">${window.APP_HELPERS.e(module)}</h6>
          <div class="row g-2">
            ${perms.map((perm) => {
              const name = typeof perm === 'string' ? perm : perm.name;
              return `<div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="permissions[]" value="${window.APP_HELPERS.e(name)}" ${current.includes(name) ? 'checked' : ''}><span class="form-check-label">${window.APP_HELPERS.e(window.APP_HELPERS.permissionLabel(name))}</span></label></div>`;
            }).join('')}
          </div>
        </div>`;
    }).join('');
    permissionsModal.show();
  }

  tbody.addEventListener('click', async (event) => {
    const btn = event.target.closest('button[data-action]');
    if (!btn) return;
    const id = Number(btn.dataset.id);
    const action = btn.dataset.action;
    try {
      if (action === 'edit') await openEditUser(id);
      if (action === 'password') { document.getElementById('passwordUserId').value = id; passwordModal.show(); }
      if (action === 'roles') await openRolesModal(id);
      if (action === 'permissions') await openPermissionsModal(id);
      if (action === 'toggle') {
        if (!confirm(SiteI18n.ta('تأكيد تغيير حالة المستخدم؟'))) return;
        const result = await window.APP_API.patch(window.APP_ROUTES.adminUserUpdateStatus(id), { is_active: btn.dataset.active === '1' });
        showMessage(result?.message || SiteI18n.ta('تم تحديث الحالة.'));
        await loadUsers();
      }
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشلت العملية.'), 'error');
    }
  });

  document.getElementById('userForm')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = document.getElementById('userFormId').value;
    try {
      if (id) {
        const payload = {
          name: document.getElementById('userFormName').value.trim(),
          email: document.getElementById('userFormEmail').value.trim(),
          phone: document.getElementById('userFormPhone').value.trim() || null,
          is_active: document.getElementById('userFormActive').value === '1',
          training_center_id: document.getElementById('userFormCenter').value || null,
          governorate_id: document.getElementById('userFormGovernorate').value || null,
          branch_id: document.getElementById('userFormBranch').value || null,
        };
        const result = await window.APP_API.put(window.APP_ROUTES.adminUserUpdate(id), payload);
        showMessage(result?.message || SiteI18n.ta('تم التحديث.'));
      } else {
        const password = document.getElementById('userFormPassword').value;
        const confirm = document.getElementById('userFormPasswordConfirm').value;
        if (password !== confirm) { showMessage(SiteI18n.ta('كلمتا المرور غير متطابقتين.'), 'error'); return; }
        const payload = {
          name: document.getElementById('userFormName').value.trim(),
          email: document.getElementById('userFormEmail').value.trim(),
          phone: document.getElementById('userFormPhone').value.trim() || null,
          password,
          password_confirmation: confirm,
          role: document.getElementById('userFormRole').value,
          is_active: document.getElementById('userFormActive').value === '1',
          training_center_id: document.getElementById('userFormCenter').value || null,
          governorate_id: document.getElementById('userFormGovernorate').value || null,
          branch_id: document.getElementById('userFormBranch').value || null,
        };
        const result = await window.APP_API.post(window.APP_ROUTES.adminUserStore(), payload);
        showMessage(result?.message || SiteI18n.ta('تم إنشاء المستخدم.'));
      }
      userFormModal.hide();
      await loadUsers();
    } catch (error) {
      showMessage(error?.data?.message || Object.values(error?.data?.errors || {})[0]?.[0] || SiteI18n.ta('فشل الحفظ.'), 'error');
    }
  });

  document.getElementById('passwordForm')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = document.getElementById('passwordUserId').value;
    const password = document.getElementById('passwordNew').value;
    const confirm = document.getElementById('passwordConfirm').value;
    if (password !== confirm) { showMessage(SiteI18n.ta('كلمتا المرور غير متطابقتين.'), 'error'); return; }
    try {
      const result = await window.APP_API.post(window.APP_ROUTES.adminUserChangePassword(id), { password, password_confirmation: confirm });
      showMessage(result?.message || SiteI18n.ta('تم تغيير كلمة المرور.'));
      passwordModal.hide();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل تغيير كلمة المرور.'), 'error');
    }
  });

  document.getElementById('rolesForm')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = document.getElementById('rolesUserId').value;
    const roles = [...document.querySelectorAll('#rolesCheckboxList input:checked')].map((el) => el.value);
    if (!roles.length) { showMessage(SiteI18n.ta('اختر دوراً واحداً على الأقل.'), 'error'); return; }
    const payload = { roles };
    const needsBranchManager = roles.includes('branch_manager');
    const needsAnyBranch = roles.some((r) => BRANCH_ROLES.includes(r));
    if (needsAnyBranch && !roles.every((r) => NATIONAL_ROLES.includes(r))) {
      payload.governorate_id = document.getElementById('rolesFormGovernorate')?.value || null;
      payload.branch_id = document.getElementById('rolesFormBranch')?.value || null;
      if (needsBranchManager && (!payload.governorate_id || !payload.branch_id)) {
        showMessage(SiteI18n.ta('يجب اختيار المحافظة والفرع عند تعيين دور مدير فرع.'), 'error');
        return;
      }
    }
    try {
      const result = await window.APP_API.post(window.APP_ROUTES.adminUserSyncRoles(id), payload);
      showMessage(result?.message || SiteI18n.ta('تم تحديث الأدوار.'));
      rolesModal.hide();
      await loadUsers();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل تحديث الأدوار.'), 'error');
    }
  });

  document.getElementById('permissionsForm')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = document.getElementById('permissionsUserId').value;
    const permissions = [...document.querySelectorAll('#permissionsCheckboxList input:checked')].map((el) => el.value);
    try {
      const result = await window.APP_API.post(window.APP_ROUTES.adminUserSyncPermissions(id), { permissions });
      showMessage(result?.message || SiteI18n.ta('تم تحديث الصلاحيات.'));
      permissionsModal.hide();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل تحديث الصلاحيات.'), 'error');
    }
  });

  document.getElementById('adminUserCreateBtn')?.addEventListener('click', openCreateUser);
  document.getElementById('filterApplyBtn')?.addEventListener('click', loadUsers);

  await loadMeta();
  await loadUsers();
});
