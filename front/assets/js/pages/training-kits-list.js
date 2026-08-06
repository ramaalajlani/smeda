document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.VIEW_KITS,
  });
  if (!ok) return;

  const container = document.getElementById('trainingBagsContainer');
  const loadingBox = document.getElementById('bagsLoadingBox');
  const searchInput = document.getElementById('trainingSearchInput');
  const sectorFilter = document.getElementById('trainingSectorFilter');
  const resetBtn = document.getElementById('resetTrainingFilters');

  let allRows = [];

  function fillSectorFilter(rows) {
    if (!sectorFilter) return;
    const sectors = [...new Set(rows.map(item => item.sector).filter(Boolean))];
    sectorFilter.innerHTML = `<option value="">كل القطاعات</option>` +
      sectors.map(sector => `<option value="${window.APP_HELPERS.e(sector)}">${window.APP_HELPERS.e(sector)}</option>`).join('');
  }

  function render(rows) {
    if (!rows.length) {
      container.innerHTML = `
        <div class="col-12">
          <div class="bg-white border rounded-4 p-4 text-center text-muted">
            لا توجد حقائب مطابقة.
          </div>
        </div>
      `;
      return;
    }

    container.innerHTML = rows.map(item => {
      const trainers = item.trainers || [];
      const centers = item.centers || [];
      const trainersCount = item.stats?.trainers_count ?? trainers.length;
      const centersCount = item.stats?.centers_count ?? centers.length;
      const trainersNames = trainers.length
        ? trainers.slice(0, 3).map(t => window.APP_HELPERS.safe(t.name)).join('، ') + (trainers.length > 3 ? ` (+${trainers.length - 3})` : '')
        : 'لا يوجد';
      const centersNames = centers.length
        ? centers.slice(0, 2).map(c => window.APP_HELPERS.safe(c.name)).join('، ') + (centers.length > 2 ? ` (+${centers.length - 2})` : '')
        : 'لا يوجد';
      return `
      <div class="col-md-6 col-xl-4">
        <div class="bg-white border rounded-4 p-4 h-100 shadow-sm d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <h3 class="h5 fw-bold mb-0">${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.name))}</h3>
            ${window.APP_HELPERS.badgeHtml(item.status)}
          </div>
          <div class="small text-muted mb-2">الكود: ${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.code))}</div>
          <div class="small mb-2">القطاع: ${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.sector))}</div>
          <div class="small mb-2">الصنف: ${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.category))}</div>
          <div class="small mb-2">النوع: ${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.type))}</div>
          <div class="small mb-2">المستوى: ${window.APP_HELPERS.e(window.APP_HELPERS.safe(item.level))}</div>
          <div class="small mb-2">الساعات: ${window.APP_HELPERS.safe(item.hours)}</div>
          <div class="small mb-1">المراكز المكلَّفة: <strong>${centersCount}</strong></div>
          <div class="small mb-2 text-muted">${window.APP_HELPERS.e(centersNames)}</div>
          <div class="small mb-1">المدربين المكلّفين: <strong>${trainersCount}</strong></div>
          <div class="small mb-3 text-muted">${window.APP_HELPERS.e(trainersNames)}</div>
          <div class="mt-auto">
            <a class="btn btn-sm btn-outline-primary" href="training-kit-manage.php?id=${item.id}">إدارة التكليف</a>
          </div>
        </div>
      </div>
    `;
    }).join('');
  }

  function applyFilters() {
    const search = (searchInput?.value || '').trim().toLowerCase();
    const sector = sectorFilter?.value || '';

    const filtered = allRows.filter(item => {
      const haystack = [
        item.name,
        item.code,
        item.sector,
        item.category,
        item.type,
        item.level,
        item.objective,
        item.description
      ].join(' ').toLowerCase();

      const matchesSearch = !search || haystack.includes(search);
      const matchesSector = !sector || item.sector === sector;

      return matchesSearch && matchesSector;
    });

    render(filtered);
  }

  try {
    const result = await window.APP_API.get(window.APP_ROUTES.trainingKits({
      with_trainers: 1,
      with_centers: 1,
      with_counts: 1,
      per_page: 100,
    }));
    allRows = result.data || [];

    window.APP_UI.hideLoadingState(loadingBox);
    fillSectorFilter(allRows);
    render(allRows);

    searchInput?.addEventListener('input', applyFilters);
    sectorFilter?.addEventListener('change', applyFilters);
    resetBtn?.addEventListener('click', () => {
      if (searchInput) searchInput.value = '';
      if (sectorFilter) sectorFilter.value = '';
      render(allRows);
    });
  } catch (error) {
    console.error(error);
    window.APP_UI.hideLoadingState(loadingBox);
    container.innerHTML = `
      <div class="col-12">
        <div class="bg-white border rounded-4 p-4 text-center text-danger">
          تعذر تحميل بيانات الحقائب التدريبية.
        </div>
      </div>
    `;
  }
});