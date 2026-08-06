document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: false });
  if (!ok) return;

  const container = document.getElementById('trainingProgramsContainer');
  const loadingBox = document.getElementById('programsLoadingBox');

  try {
    const result = await window.APP_API.get(window.APP_ROUTES.trainingPrograms());
    const rows = result.data || [];

    window.APP_UI.hideLoadingState(loadingBox);

    if (!rows.length) {
      container.innerHTML = `
        <div class="col-12">
          <div class="bg-white border rounded-4 p-4 text-center text-muted">
            ${window.SiteI18n?.ta?.('لا توجد برامج تدريبية حالياً.') ?? 'لا توجد برامج تدريبية حالياً.'}
          </div>
        </div>
      `;
      return;
    }

    const codeLabel = window.SiteI18n?.ta?.('الكود') ?? 'الكود';

    container.innerHTML = rows.map(item => `
      <div class="col-md-6 col-xl-4">
        <div class="bg-white border rounded-4 p-4 h-100 shadow-sm">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <h3 class="h5 fw-bold mb-0">${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.name))}</h3>
            ${window.APP_HELPERS.badgeHtml(item.status)}
          </div>
          <div class="small text-muted mb-2">${codeLabel}: ${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.code))}</div>
          <div class="small mb-2">${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.description))}</div>
        </div>
      </div>
    `).join('');
  } catch (error) {
    console.error(error);
    window.APP_UI.hideLoadingState(loadingBox);
    container.innerHTML = `
      <div class="col-12">
        <div class="bg-white border rounded-4 p-4 text-center text-danger">
          ${window.SiteI18n?.ta?.('تعذر تحميل بيانات البرامج التدريبية.') ?? 'تعذر تحميل بيانات البرامج التدريبية.'}
        </div>
      </div>
    `;
  }
});
