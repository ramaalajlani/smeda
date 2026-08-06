document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.AppAccess.guardAccessAdmin()) return;

  const tbody = document.getElementById('activityLogsTableBody');
  const loading = document.getElementById('activityLogsLoading');
  const detailModal = new bootstrap.Modal(document.getElementById('logDetailModal'));
  const params = new URLSearchParams(window.location.search);
  const presetUserId = params.get('user_id');

  function getFilters() {
    return {
      search: document.getElementById('logSearch')?.value?.trim() || undefined,
      action: document.getElementById('logAction')?.value?.trim() || undefined,
      module: document.getElementById('logModule')?.value?.trim() || undefined,
      date_from: document.getElementById('logDateFrom')?.value || undefined,
      date_to: document.getElementById('logDateTo')?.value || undefined,
      user_id: presetUserId || undefined,
      per_page: 50,
    };
  }

  async function loadLogs() {
    try {
      const route = presetUserId
        ? window.APP_ROUTES.adminUserActivityLogs(presetUserId, getFilters())
        : window.APP_ROUTES.adminActivityLogs(getFilters());
      const result = await window.APP_API.get(route);
      const rows = result?.data || [];
      window.APP_UI.hideLoadingState(loading);
      tbody.innerHTML = rows.map((log, index) => `
        <tr>
          <td>${log.id || index + 1}</td>
          <td>${window.APP_HELPERS.e(log.created_at || '—')}</td>
          <td>${window.APP_HELPERS.e(log.user_name || '—')}<br><small class="text-muted">${window.APP_HELPERS.e(log.user_email || '')}</small></td>
          <td><code>${window.APP_HELPERS.e(log.action)}</code></td>
          <td>${window.APP_HELPERS.e(log.module || '—')}</td>
          <td>${window.APP_HELPERS.e(log.description || '—')}</td>
          <td>${window.APP_HELPERS.e(log.ip_address || '—')}</td>
          <td><button type="button" class="btn btn-sm btn-outline-primary" data-log-id="${log.id}">عرض</button></td>
        </tr>
      `).join('') || '<tr><td colspan="8" class="text-center text-muted">لا توجد سجلات.</td></tr>';
    } catch (error) {
      window.APP_UI.hideLoadingState(loading);
      window.APP_UI.renderErrorTable(tbody, 8, error?.data?.message || SiteI18n.ta('تعذر تحميل السجل.'));
    }
  }

  tbody.addEventListener('click', async (event) => {
    const btn = event.target.closest('button[data-log-id]');
    if (!btn) return;
    try {
      const res = await window.APP_API.get(window.APP_ROUTES.adminActivityLogShow(btn.dataset.logId));
      document.getElementById('logDetailBody').textContent = JSON.stringify(res?.data || res, null, 2);
      detailModal.show();
    } catch (error) {
      alert(error?.data?.message || SiteI18n.ta('تعذر تحميل التفاصيل.'));
    }
  });

  document.getElementById('logFilterBtn')?.addEventListener('click', loadLogs);

  document.getElementById('logExportBtn')?.addEventListener('click', async () => {
    try {
      const token = window.APPAuth.getToken();
      const url = window.APP_ROUTES.adminActivityLogsExport(getFilters());
      const res = await fetch(url, { headers: { Authorization: `Bearer ${token}`, Accept: 'text/csv' } });
      if (!res.ok) throw new Error(SiteI18n.ta('فشل التصدير'));
      const blob = await res.blob();
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `activity-logs-${new Date().toISOString().slice(0, 10)}.csv`;
      link.click();
      URL.revokeObjectURL(link.href);
    } catch (error) {
      alert(error?.message || SiteI18n.ta('فشل تصدير CSV.'));
    }
  });

  await loadLogs();
});
