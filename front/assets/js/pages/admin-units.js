document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.AppAuth.hasPermission('needs.manage_admin_units')) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }
  const res = await window.APP_API.get(window.APP_ROUTES.needsAdminUnits({ per_page: 200 }));
  const rows = res.data || [];
  document.getElementById('adminUnitsBody').innerHTML = rows.map((r) => `
    <tr>
      <td>${window.APP_HELPERS.safe(r.governorate?.name_ar || '—')}</td>
      <td>${window.APP_HELPERS.safe(r.district_name || '—')}</td>
      <td>${window.APP_HELPERS.safe(r.unit_name || '—')}</td>
      <td>${r.is_active ? 'نشط': SiteI18n.ta('موقوف')}</td>
    </tr>`).join('') || SiteI18n.ta('<tr><td colspan="4" class="text-center text-muted">لا توجد بيانات</td></tr>');
});
