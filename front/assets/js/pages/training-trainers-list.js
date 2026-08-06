document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.VIEW_TRAINERS,
  });
  if (!ok) return;

  const tbody = document.getElementById('trainersTableBody');
  const loadingBox = document.getElementById('trainersLoadingBox');
  const emptyState = document.getElementById('trainersEmptyState');

  if (!tbody || !loadingBox) {
    console.error('Trainer list elements not found.');
    return;
  }

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function buildActions(trainer) {
    const actions = [];

    if (trainer?.card_url) {
      actions.push(`
        <a href="${trainer.card_url}" target="_blank" class="btn btn-sm btn-outline-success">
          البطاقة
        </a>
      `);
    }

    if (trainer?.pdf_url) {
      actions.push(`
        <a href="${trainer.pdf_url}" target="_blank" class="btn btn-sm btn-outline-primary">
          PDF
        </a>
      `);
    }

    if (!actions.length) {
      return ')—';
    }

    return `
      <div class="d-flex flex-wrap gap-2">
        ${actions.join('')}
      </div>
    `;
  }

  try {
    const result = await window.APP_API.get(
      window.APP_ROUTES.trainers({ per_page: 100 })
    );

    const rows = result?.data || [];

    window.APP_UI.hideLoadingState(loadingBox);
    tbody.innerHTML = '';

    if (!rows.length) {
      emptyState?.classList.remove('d-none');
      window.APP_UI.renderEmptyTable(tbody, 7, SiteI18n.ta('لا توجد بيانات مدربين حالياً.'));
      return;
    }

    emptyState?.classList.add('d-none');

    rows.forEach(trainer => {
      const row = document.createElement('tr');

      row.innerHTML = `
        <td>${safeText(trainer.trainer_code)}</td>
        <td>${safeText(trainer.name)}</td>
        <td>${safeText(trainer.specialization)}</td>
        <td>${safeText(trainer.classification)}</td>
        <td>
          <div class="mb-2">${window.APP_HELPERS.badgeHtml(trainer.status)}</div>
          <div class="small text-muted">${safeText(trainer.eligibility_label)}</div>
        </td>
        <td>${trainer.has_tot ? 'نعم': SiteI18n.ta('لا')}</td>
        <td>${buildActions(trainer)}</td>
      `;

      tbody.appendChild(row);
    });
  } catch (error) {
    console.error(error);
    window.APP_UI.hideLoadingState(loadingBox);
    window.APP_UI.renderErrorTable(
      tbody,
      7,
      error?.data?.message || SiteI18n.ta('تعذر تحميل بيانات المدربين.')
    );
  }
});