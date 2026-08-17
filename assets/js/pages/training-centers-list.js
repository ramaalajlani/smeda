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
    if (!center?.id) {
      return '—';
    }

    return `
      <div class="d-flex flex-wrap gap-2">
        <button type="button"
                class="btn btn-sm btn-outline-success center-cert-btn"
                data-id="${window.APP_HELPERS.e(center.id)}">
          ${ta('الشهادة')}
        </button>
        <button type="button"
                class="btn btn-sm btn-outline-primary center-pdf-btn"
                data-id="${window.APP_HELPERS.e(center.id)}">
          PDF
        </button>
      </div>
    `;
  }

  function bindCenterActions(rows) {
    const byId = new Map((rows || []).map((item) => [String(item.id), item]));

    tbody.querySelectorAll('.center-cert-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const item = byId.get(String(btn.getAttribute('data-id')));
        btn.disabled = true;
        try {
          await window.APP_HELPERS.openSignedPrintAsset({
            id: item?.id,
            fetchUrl: window.APP_ROUTES.trainingCenterShow,
            htmlField: 'certificate_url',
            pdfField: 'pdf_url',
            fallbackItem: item,
            errorMessage: ta('تعذّر فتح شهادة المركز.'),
          });
        } finally {
          btn.disabled = false;
        }
      });
    });

    tbody.querySelectorAll('.center-pdf-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const item = byId.get(String(btn.getAttribute('data-id')));
        btn.disabled = true;
        try {
          await window.APP_HELPERS.openSignedPrintAsset({
            id: item?.id,
            fetchUrl: window.APP_ROUTES.trainingCenterShow,
            htmlField: 'certificate_url',
            pdfField: 'pdf_url',
            preferPdf: true,
            fallbackItem: item,
            errorMessage: ta('تعذّر فتح ملف PDF.'),
          });
        } finally {
          btn.disabled = false;
        }
      });
    });
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

    rows.forEach((center) => {
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

    bindCenterActions(rows);
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
