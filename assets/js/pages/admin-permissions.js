document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.AppAccess.guardAccessAdmin()) return;

  const tbody = document.getElementById('adminPermissionsTableBody');
  const loading = document.getElementById('adminPermissionsLoading');
  const messageBox = document.getElementById('adminPermissionsMessage');

  function showMessage(text, type = 'success') {
    if (!messageBox) return;
    messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  try {
    const result = await window.APP_API.get(window.APP_ROUTES.adminPermissions());
    const grouped = result?.data || {};
    const rows = [];
    Object.entries(grouped).forEach(([module, perms]) => {
      (Array.isArray(perms) ? perms : []).forEach((perm) => {
        rows.push({ module, name: perm.name || perm, roles_count: perm.roles_count });
      });
    });
    window.APP_UI.hideLoadingState(loading);
    tbody.innerHTML = rows.map((perm, index) => `
      <tr>
        <td>${index + 1}</td>
        <td><code>${window.APP_HELPERS.e(perm.name)}</code></td>
        <td>${window.APP_HELPERS.e(perm.module)}</td>
        <td>${window.APP_HELPERS.safe(perm.roles_count, '—')}</td>
      </tr>
    `).join('') || SiteI18n.ta('<tr><td colspan="4" class="text-center text-muted">لا توجد صلاحيات.</td></tr>');
  } catch (error) {
    window.APP_UI.hideLoadingState(loading);
    window.APP_UI.renderErrorTable(tbody, 4, error?.data?.message || SiteI18n.ta('تعذر تحميل الصلاحيات.'));
    showMessage(error?.data?.message || SiteI18n.ta('تعذر تحميل الصلاحيات.'), 'error');
  }
});
