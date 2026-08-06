document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.VIEW_TRAINEES,
  });
  if (!ok) return;

  const tbody = document.getElementById('traineesTableBody');
  const loadingBox = document.getElementById('traineesLoadingBox');
  const emptyState = document.getElementById('traineesEmptyState');
  const searchInput = document.getElementById('traineeSearchInput');
  const statusFilter = document.getElementById('traineeStatusFilter');
  const searchBtn = document.getElementById('traineeSearchBtn');
  const resetBtn = document.getElementById('traineeResetBtn');

  const totalCounter = document.getElementById('totalTraineesCounter');
  const activeCounter = document.getElementById('activeTraineesCounter');
  const withCoursesCounter = document.getElementById('withCoursesCounter');
  const withCertificatesCounter = document.getElementById('withCertificatesCounter');

  if (!tbody || !loadingBox) {
    console.error('Trainee list elements not found.');
    return;
  }

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function genderLabel(value) {
    if (value === 'male') return SiteI18n.ta('ذكر');
    if (value === 'female') return SiteI18n.ta('أنثى');
    return '—';
  }

  function statusLabel(value) {
    const map = {
      active: SiteI18n.ta('نشط'),
      inactive: SiteI18n.ta('غير نشط'),
      suspended: SiteI18n.ta('موقوف'),
      graduated: SiteI18n.ta('متخرج'),
      blocked: SiteI18n.ta('محظور'),
    };
    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function buildActions(trainee) {
    const actions = [];

    if (trainee?.card_url) {
      actions.push(`
        <a href="${window.APP_HELPERS.e(trainee.card_url)}"
           target="_blank"
           class="btn btn-sm btn-outline-success">
          البطاقة
        </a>
      `);
    }

    if (trainee?.pdf_url) {
      actions.push(`
        <a href="${window.APP_HELPERS.e(trainee.pdf_url)}"
           target="_blank"
           class="btn btn-sm btn-outline-primary">
          PDF
        </a>
      `);
    }

    if (!actions.length) return '—';

    return `<div class="trainee-actions">${actions.join('')}</div>`;
  }

  function updateCounters(rows) {
    if (totalCounter) totalCounter.textContent = String(rows.length);
    if (activeCounter) {
      activeCounter.textContent = String(rows.filter((r) => r.status === 'active').length);
    }
    if (withCoursesCounter) {
      withCoursesCounter.textContent = String(
        rows.filter((r) => Number(r?.stats?.courses_count || 0) > 0).length
      );
    }
    if (withCertificatesCounter) {
      withCertificatesCounter.textContent = String(
        rows.filter((r) => Number(r?.stats?.certificates_count || 0) > 0).length
      );
    }
  }

  function renderRows(rows) {
    tbody.innerHTML = '';

    if (!rows.length) {
      emptyState?.classList.remove('d-none');
      window.APP_UI.renderEmptyTable(tbody, 11, SiteI18n.ta('لا يوجد متدربون حالياً.'));
      return;
    }

    emptyState?.classList.add('d-none');

    rows.forEach((trainee, index) => {
      const city = trainee?.location?.city || trainee?.city || '—';
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${index + 1}</td>
        <td><code>${safeText(trainee.trainee_code)}</code></td>
        <td>
          <div class="fw-bold">${safeText(trainee.name)}</div>
          <div class="small text-muted mt-1">${safeText(trainee.education_level, '')}</div>
        </td>
        <td>${safeText(trainee.mother_name)}</td>
        <td>${safeText(genderLabel(trainee.gender))}</td>
        <td>${safeText(trainee.birth_date)}</td>
        <td>${safeText(city)}</td>
        <td>
          ${window.APP_HELPERS.badgeHtml(trainee.status)}
          <div class="small text-muted mt-1">${safeText(statusLabel(trainee.status))}</div>
        </td>
        <td>${safeText(trainee?.stats?.courses_count, 0)}</td>
        <td>${safeText(trainee?.stats?.certificates_count, 0)}</td>
        <td>${buildActions(trainee)}</td>
      `;
      tbody.appendChild(row);
    });
  }

  async function loadTrainees() {
    loadingBox.classList.remove('d-none');
    emptyState?.classList.add('d-none');

    const params = { per_page: 100 };
    const search = (searchInput?.value || '').trim();
    const status = statusFilter?.value || '';
    if (search) params.search = search;
    if (status) params.status = status;

    try {
      const result = await window.APP_API.get(window.APP_ROUTES.trainees(params));
      const rows = result?.data || [];

      window.APP_UI.hideLoadingState(loadingBox);
      updateCounters(rows);
      renderRows(rows);
    } catch (error) {
      console.error('Trainees list error:', error);
      window.APP_UI.hideLoadingState(loadingBox);
      updateCounters([]);
      window.APP_UI.renderErrorTable(
        tbody,
        8,
        error?.data?.message || SiteI18n.ta('تعذر تحميل سجل المتدربين.')
      );
    }
  }

  searchBtn?.addEventListener('click', loadTrainees);
  resetBtn?.addEventListener('click', () => {
    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    loadTrainees();
  });
  searchInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      loadTrainees();
    }
  });

  await loadTrainees();
});
