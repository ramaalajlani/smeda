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
    if (!trainer?.id) {
      return '—';
    }

    return `
      <div class="d-flex flex-wrap gap-2">
        <button type="button"
                class="btn btn-sm btn-outline-success trainer-card-btn"
                data-id="${window.APP_HELPERS.e(trainer.id)}">
          البطاقة
        </button>
        <button type="button"
                class="btn btn-sm btn-outline-primary trainer-pdf-btn"
                data-id="${window.APP_HELPERS.e(trainer.id)}">
          PDF
        </button>
      </div>
    `;
  }

  function bindTrainerActions(rows) {
    const byId = new Map((rows || []).map((item) => [String(item.id), item]));

    tbody.querySelectorAll('.trainer-card-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const item = byId.get(String(btn.getAttribute('data-id')));
        btn.disabled = true;
        try {
          await window.APP_HELPERS.openSignedPrintAsset({
            id: item?.id,
            fetchUrl: window.APP_ROUTES.trainerShow,
            htmlField: 'card_url',
            pdfField: 'pdf_url',
            fallbackItem: item,
            errorMessage: SiteI18n.ta('تعذّر فتح بطاقة المدرب.'),
          });
        } finally {
          btn.disabled = false;
        }
      });
    });

    tbody.querySelectorAll('.trainer-pdf-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const item = byId.get(String(btn.getAttribute('data-id')));
        btn.disabled = true;
        try {
          await window.APP_HELPERS.openSignedPrintAsset({
            id: item?.id,
            fetchUrl: window.APP_ROUTES.trainerShow,
            htmlField: 'card_url',
            pdfField: 'pdf_url',
            preferPdf: true,
            fallbackItem: item,
            errorMessage: SiteI18n.ta('تعذّر فتح ملف PDF.'),
          });
        } finally {
          btn.disabled = false;
        }
      });
    });
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

    rows.forEach((trainer) => {
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
        <td>${trainer.has_tot ? 'نعم' : SiteI18n.ta('لا')}</td>
        <td>${buildActions(trainer)}</td>
      `;

      tbody.appendChild(row);
    });

    bindTrainerActions(rows);
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
