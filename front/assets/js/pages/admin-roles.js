document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.AppAccess.guardAccessAdmin()) return;

  const tbody = document.getElementById('adminRolesTableBody');
  const loading = document.getElementById('adminRolesLoading');
  const messageBox = document.getElementById('adminRolesMessage');
  const roleFormModal = new bootstrap.Modal(document.getElementById('roleFormModal'));
  const rolePermissionsModal = new bootstrap.Modal(document.getElementById('rolePermissionsModal'));
  let permissionsGrouped = {};

  function showMessage(text, type = 'success') {
    messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  async function loadPermissionsMeta() {
    const res = await window.APP_API.get(window.APP_ROUTES.adminPermissions());
    permissionsGrouped = res?.data || {};
  }

  async function loadRoles() {
    try {
      const result = await window.APP_API.get(window.APP_ROUTES.adminRoles());
      const rows = result?.data || [];
      window.APP_UI.hideLoadingState(loading);
      tbody.innerHTML = rows.map((role, index) => `
        <tr>
          <td>${index + 1}</td>
          <td>${window.APP_HELPERS.e(window.APP_HELPERS.roleLabel(role.name))} <small class="text-muted">(${window.APP_HELPERS.e(role.name)})</small> ${role.is_protected ? '<span class="badge bg-secondary">محمي</span>': ''}</td>
          <td>${window.APP_HELPERS.safe(role.users_count, 0)}</td>
          <td>${window.APP_HELPERS.safe(role.permissions_count, 0)}</td>
          <td class="d-flex gap-1 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${role.id}" data-name="${window.APP_HELPERS.e(role.name)}" ${role.is_protected ? 'disabled' : ''}>تعديل</button>
            <button type="button" class="btn btn-sm btn-outline-info" data-action="permissions" data-id="${role.id}">الصلاحيات</button>
            <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${role.id}" ${role.is_protected ? 'disabled' : ''}>حذف</button>
          </td>
        </tr>
      `).join('') || '<tr><td colspan="5" class="text-center text-muted">لا توجد أدوار.</td></tr>';
    } catch (error) {
      window.APP_UI.hideLoadingState(loading);
      window.APP_UI.renderErrorTable(tbody, 5, error?.data?.message || SiteI18n.ta('تعذر تحميل الأدوار.'));
    }
  }

  tbody.addEventListener('click', async (event) => {
    const btn = event.target.closest('button[data-action]');
    if (!btn) return;
    const id = btn.dataset.id;
    const action = btn.dataset.action;
    try {
      if (action === 'edit') {
        document.getElementById('roleFormTitle').textContent = SiteI18n.ta('تعديل دور');
        document.getElementById('roleFormId').value = id;
        document.getElementById('roleFormName').value = btn.dataset.name;
        roleFormModal.show();
      }
      if (action === 'permissions') {
        const res = await window.APP_API.get(window.APP_ROUTES.adminRoleShow(id));
        const role = res?.data;
        const current = (role.permissions || []).map((p) => p.name || p);
        document.getElementById('rolePermissionsId').value = id;
        document.getElementById('rolePermissionsList').innerHTML = Object.entries(permissionsGrouped).map(([module, perms]) => {
          const list = Array.isArray(perms) ? perms : [];
          if (!list.length) return '';
          return `<div class="mb-3"><h6>${window.APP_HELPERS.e(module)}</h6><div class="row g-2">${list.map((perm) => {
            const name = perm.name || perm;
            return `<div class="col-md-6"><label class="form-check"><input type="checkbox" name="permissions[]" value="${window.APP_HELPERS.e(name)}" ${current.includes(name) ? 'checked' : ''}><span class="form-check-label"><code>${window.APP_HELPERS.e(name)}</code></span></label></div>`;
          }).join('')}</div></div>`;
        }).join('');
        rolePermissionsModal.show();
      }
      if (action === 'delete') {
        if (!confirm(SiteI18n.ta('حذف هذا الدور؟'))) return;
        const result = await window.APP_API.delete(window.APP_ROUTES.adminRoleDelete(id));
        showMessage(result?.message || SiteI18n.ta('تم الحذف.'));
        await loadRoles();
      }
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشلت العملية.'), 'error');
    }
  });

  document.getElementById('roleCreateBtn')?.addEventListener('click', () => {
    document.getElementById('roleFormTitle').textContent = SiteI18n.ta('إنشاء دور');
    document.getElementById('roleForm').reset();
    document.getElementById('roleFormId').value = '';
    roleFormModal.show();
  });

  document.getElementById('roleForm')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = document.getElementById('roleFormId').value;
    const name = document.getElementById('roleFormName').value.trim();
    try {
      if (id) {
        await window.APP_API.patch(window.APP_ROUTES.adminRoleUpdate(id), { name });
      } else {
        await window.APP_API.post(window.APP_ROUTES.adminRoleStore(), { name });
      }
      roleFormModal.hide();
      showMessage(SiteI18n.ta('تم حفظ الدور.'));
      await loadRoles();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل حفظ الدور.'), 'error');
    }
  });

  document.getElementById('rolePermissionsForm')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = document.getElementById('rolePermissionsId').value;
    const permissions = [...document.querySelectorAll('#rolePermissionsList input:checked')].map((el) => el.value);
    try {
      const result = await window.APP_API.post(window.APP_ROUTES.adminRoleSyncPermissions(id), { permissions });
      showMessage(result?.message || SiteI18n.ta('تم تحديث صلاحيات الدور.'));
      rolePermissionsModal.hide();
      await loadRoles();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل تحديث الصلاحيات.'), 'error');
    }
  });

  await loadPermissionsMeta();
  await loadRoles();
});
