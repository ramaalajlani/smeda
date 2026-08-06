document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.REVIEW_TRAINING_KIT_NOMINATIONS,
  });
  if (!ok) return;

  const tbody = document.getElementById('reviewNominationsTableBody');
  const loadingBox = document.getElementById('reviewLoadingBox');
  const messageBox = document.getElementById('reviewMessage');

  if (!tbody || !loadingBox) return;

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function showMessage(text, type = 'success') {
    if (!messageBox) return;

    messageBox.className = `review-message ${type}`;
    messageBox.textContent = text;
  }

  function hideMessage() {
    if (!messageBox) return;

    messageBox.className = 'review-message';
    messageBox.textContent = '';
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

  async function sendReview(id, status, notes = '') {
    return await window.APP_API.post(
      window.APP_ROUTES.trainingKitNominationReview(id),
      {
        status,
        decision_notes: notes || null,
      }
    );
  }

  function bindActions() {
    document.querySelectorAll('.review-action-btn').forEach((button) => {
      button.addEventListener('click', async () => {
        hideMessage();

        const id = button.getAttribute('data-id');
        const status = button.getAttribute('data-status');

        let notes = '';
        if (status === 'rejected' || status === 'under_review') {
          notes = prompt(SiteI18n.ta('أدخل ملاحظات القرار (اختياري):')) || '';
        }

        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = SiteI18n.ta('جارٍ التنفيذ...');

        try {
          const response = await sendReview(id, status, notes);
          showMessage(response?.message || SiteI18n.ta('تم تحديث حالة الترشيح بنجاح.'), 'success');
          await loadRows();
        } catch (error) {
          console.error('Nomination review error:', error);
          showMessage(error?.data?.message || SiteI18n.ta('تعذر تحديث حالة الترشيح.'), 'error');
          button.disabled = false;
          button.textContent = originalText;
        }
      });
    });
  }

  function renderRows(rows) {
    tbody.innerHTML = '';

    if (!rows.length) {
      window.APP_UI.renderEmptyTable(tbody, 9, SiteI18n.ta('لا توجد ترشيحات حقائب حالياً.'));
      return;
    }

    rows.forEach((item, index) => {
      const row = document.createElement('tr');

      row.innerHTML = `
        <td>${index + 1}</td>
        <td>
          <div class="fw-bold">${safeText(item?.trainer?.name)}</div>
          <div class="small text-muted mt-1">${safeText(item?.trainer?.trainer_code)}</div>
        </td>
        <td>
          <div class="fw-bold">${safeText(nominationTitle(item))}</div>
          <div class="small text-muted mt-1">${safeText(item?.training_kit?.code)}</div>
        </td>
        <td>${safeText(item?.sector)}</td>
        <td>${safeText(item?.category)}</td>
        <td>${safeText(item?.hours)}</td>
        <td>${statusBadge(item?.status)}</td>
        <td>${safeText(item?.description)}</td>
        <td class="review-actions">
          <div class="d-flex flex-wrap gap-2">
            <button type="button"
                    class="btn btn-sm btn-success review-action-btn"
                    data-id="${item.id}"
                    data-status="approved">
              قبول
            </button>

            <button type="button"
                    class="btn btn-sm btn-warning review-action-btn"
                    data-id="${item.id}"
                    data-status="under_review">
              مراجعة
            </button>

            <button type="button"
                    class="btn btn-sm btn-danger review-action-btn"
                    data-id="${item.id}"
                    data-status="rejected">
              رفض
            </button>
          </div>
        </td>
      `;

      tbody.appendChild(row);
    });

    bindActions();
  }

  async function loadRows() {
    try {
      const result = await window.APP_API.get(
        window.APP_ROUTES.trainingKitNominations({
          per_page: 100,
          status: 'pending',
        })
      );

      const rows = result?.data || [];

      window.APP_UI.hideLoadingState(loadingBox);
      renderRows(rows);
    } catch (error) {
      console.error('Review nominations load error:', error);
      window.APP_UI.hideLoadingState(loadingBox);
      window.APP_UI.renderErrorTable(
        tbody,
        9,
        error?.data?.message || SiteI18n.ta('تعذر تحميل ترشيحات الحقائب.')
      );
    }
  }

  await loadRows();
});