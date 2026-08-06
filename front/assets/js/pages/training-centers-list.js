document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: false });
  if (!ok) return;

  const tbody = document.getElementById('trainingCentersTableBody');
  const loadingBox = document.getElementById('centersLoadingBox');
  const emptyState = document.getElementById('centersEmptyState');

  if (!tbody || !loadingBox) {
    console.error('Training center list elements not found.');
    return;
  }

  const ta = (s) => window.SiteI18n?.ta?.(s) ?? s;

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function buildActions(center) {
    const actions = [];

    if (center?.certificate_url) {
      actions.push(`
        <a href="${center.certificate_url}" target="_blank" class="btn btn-sm btn-outline-success">
          ${ta('الشهادة')}
        </a>
      `);
    }

    if (center?.pdf_url) {
      actions.push(`
        <a href="${center.pdf_url}" target="_blank" class="btn btn-sm btn-outline-primary">
          PDF
        </a>
      `);
    }

    if (!actions.length) {
      return '—';
    }

    return `
      <div class="d-flex flex-wrap gap-2">
        ${actions.join('')}
      </div>
    `;
  }

  try {
    const result = await window.APP_API.get(
      window.APP_ROUTES.trainingCenters({ with_platforms: 1, per_page: 100 })
    );

    const rows = result?.data || [];

    window.APP_UI.hideLoadingState(loadingBox);
    tbody.innerHTML = '';

    if (!rows.length) {
      emptyState?.classList.remove('d-none');
      window.APP_UI.renderEmptyTable(tbody, 6, ta('لا توجد مراكز تدريبية حالياً.'));
      return;
    }

    emptyState?.classList.add('d-none');

    rows.forEach(center => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${safeText(center.name)}</td>
        <td>${safeText(center.city)}</td>
        <td>${safeText(center.classification)}</td>
        <td>
          <div class="mb-2">${window.APP_HELPERS.badgeHtml(center.accreditation_status)}</div>
          <div class="small text-muted">${safeText(center.eligibility_label)}</div>
        </td>
        <td>${center.supports_online_training ? ta('أونلاين') : ta('حضوري')}</td>
        <td>${buildActions(center)}</td>
      `;
      tbody.appendChild(row);
    });
  } catch (error) {
    console.error(error);
    window.APP_UI.hideLoadingState(loadingBox);
    window.APP_UI.renderErrorTable(
      tbody,
      6,
      error?.data?.message || ta('تعذر تحميل بيانات المراكز التدريبية.')
    );
  }
});
