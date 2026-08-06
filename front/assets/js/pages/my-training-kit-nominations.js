document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.NOMINATE_TRAINING_KITS,
  });
  if (!ok) return;

  const tbody = document.getElementById('myNominationsTableBody');
  const loadingBox = document.getElementById('myNominationsLoadingBox');
  const emptyState = document.getElementById('myNominationsEmptyState');

  if (!tbody || !loadingBox) return;

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function statusBadge(status) {
    const map = {
      pending: ['secondary', SiteI18n.ta('قيد الانتظار')],
      under_review: ['warning', SiteI18n.ta('قيد المراجعة')],
      approved: ['success', SiteI18n.ta('مقبول')],
      rejected: ['danger', SiteI18n.ta('مرفوض')],
    };

    const item = map[String(status || '').trim()];
    if (!item) {
      return `<span class="badge bg-secondary">${safeText(status)}</span>`;
    }

    return `<span class="badge bg-${item[0]}">${item[1]}</span>`;
  }

  function nominationTitle(item) {
    return item?.training_kit?.name || item?.proposed_name || '—';
  }

  try {
    const result = await window.APP_API.get(
      window.APP_ROUTES.trainingKitNominations({ per_page: 100 })
    );

    const rows = result?.data || [];

    window.APP_UI.hideLoadingState(loadingBox);
    tbody.innerHTML = '';

    if (!rows.length) {
      emptyState?.classList.remove('d-none');
      window.APP_UI.renderEmptyTable(tbody, 8, SiteI18n.ta('لا توجد ترشيحات مرسلة حتى الآن.'));
      return;
    }

    emptyState?.classList.add('d-none');

    rows.forEach((item, index) => {
      const row = document.createElement('tr');

      row.innerHTML = `
        <td>${index + 1}</td>
        <td>
          <div class="fw-bold">${safeText(nominationTitle(item))}</div>
          <div class="small text-muted mt-1">${safeText(item?.training_kit?.code)}</div>
        </td>
        <td>${safeText(item?.sector)}</td>
        <td>${safeText(item?.category)}</td>
        <td>${safeText(item?.hours)}</td>
        <td>${statusBadge(item?.status)}</td>
        <td>${safeText(item?.decision_notes)}</td>
        <td>${safeText(item?.created_at)}</td>
      `;

      tbody.appendChild(row);
    });
  } catch (error) {
    console.error('My nominations error:', error);
    window.APP_UI.hideLoadingState(loadingBox);
    window.APP_UI.renderErrorTable(
      tbody,
      8,
      error?.data?.message || SiteI18n.ta('تعذر تحميل ترشيحات الحقائب.')
    );
  }
});