document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.VIEW_KITS,
  });
  if (!ok) return;

  const tbody = document.getElementById('bagsTableBody');
  const loadingBox = document.getElementById('bagsLoadingBox');
  const searchInput = document.getElementById('trainingSearchInput');
  const categoryFilter = document.getElementById('trainingCategoryFilter');
  const statusFilter = document.getElementById('trainingStatusFilter');
  const resetBtn = document.getElementById('resetTrainingFilters');
  const canManage = window.AppAuth.hasPermission(window.AppPermissions.MANAGE_KITS);

  const urlParams = new URLSearchParams(location.search);
  if (urlParams.get('workflow_status') && statusFilter) {
    statusFilter.value = urlParams.get('workflow_status');
  }

  let categories = [];

  const workflowLabels = {
    draft: 'مسودة',
    under_review: 'قيد المراجعة',
    approved: 'معتمدة',
    published: 'منشورة',
    inactive: 'غير نشطة',
    archived: 'مؤرشفة',
  };

  async function loadCategories() {
    const res = await window.APP_API.get(window.APP_ROUTES.trainingCategories({ roots_only: 1, with_children: 1 }));
    categories = res.data || [];
    if (!categoryFilter) return;
    categoryFilter.innerHTML = '<option value="">كل التصنيفات</option>' + categories.map((c) =>
      `<option value="${c.id}">${window.APP_HELPERS.e(c.name_ar)}</option>`
    ).join('');
  }

  async function downloadFile(url, name) {
    const r = await fetch(url, { headers: { Authorization: `Bearer ${window.AppAuth.getToken()}` } });
    if (!r.ok) return alert('تعذّر التحميل');
    const blob = await r.blob();
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = name || 'file.pdf';
    a.click();
    URL.revokeObjectURL(a.href);
  }

  function renderRows(rows) {
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted p-4">لا توجد حقائب مطابقة.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map((item) => {
      const cat = item.training_category?.name_ar || item.category || '—';
      const sub = item.training_subcategory?.name_ar || item.type || '—';
      const wf = workflowLabels[item.workflow_status] || item.workflow_status || '—';
      const promo = item.files?.promotional?.has_file ? '<i class="bi bi-check-circle text-success"></i>' : '—';
      const bag = item.files?.training_bag?.has_file ? '<i class="bi bi-file-pdf text-danger"></i>' : '—';
      const actions = [
        canManage ? `<a class="btn btn-sm btn-outline-primary" href="training-bag-form.php?id=${item.id}">تعديل</a>` : '',
        item.files?.promotional?.has_file ? `<button class="btn btn-sm btn-outline-secondary" data-promo="${item.id}">ترويجي</button>` : '',
        item.files?.training_bag?.has_file && canManage ? `<button class="btn btn-sm btn-outline-danger" data-bag="${item.id}">PDF</button>` : '',
      ].filter(Boolean).join(' ');

      return `<tr>
        <td><code>${window.APP_HELPERS.e(item.code)}</code></td>
        <td>${window.APP_HELPERS.e(item.name)}</td>
        <td>${window.APP_HELPERS.e(cat)}</td>
        <td>${window.APP_HELPERS.e(sub)}</td>
        <td>${window.APP_HELPERS.e(item.level || '—')}</td>
        <td>${item.hours ?? 0}</td>
        <td class="text-center">${promo}</td>
        <td class="text-center">${bag}</td>
        <td><span class="badge bg-light text-dark border">${window.APP_HELPERS.e(wf)}</span></td>
        <td class="text-nowrap">${actions}</td>
      </tr>`;
    }).join('');

    tbody.querySelectorAll('[data-promo]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const row = rows.find((r) => String(r.id) === btn.dataset.promo);
        downloadFile(window.APP_ROUTES.trainingKitPromotionalDownload(row.id), row.files?.promotional?.original_name);
      });
    });
    tbody.querySelectorAll('[data-bag]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const row = rows.find((r) => String(r.id) === btn.dataset.bag);
        downloadFile(window.APP_ROUTES.trainingKitBagFileDownload(row.id), row.files?.training_bag?.original_name);
      });
    });
  }

  async function loadRows() {
    const params = { per_page: 100, with_counts: 0 };
    const search = (searchInput?.value || '').trim();
    if (search) params.search = search;
    if (categoryFilter?.value) params.category_id = categoryFilter.value;
    if (statusFilter?.value) params.workflow_status = statusFilter.value;

    const result = await window.APP_API.get(window.APP_ROUTES.trainingKits(params));
    return result.data || [];
  }

  async function refresh() {
    try {
      loadingBox?.classList.remove('d-none');
      tbody.innerHTML = '';
      const rows = await loadRows();
      window.APP_UI.hideLoadingState(loadingBox);
      renderRows(rows);
    } catch (e) {
      window.APP_UI.hideLoadingState(loadingBox);
      tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger p-4">تعذر تحميل البيانات</td></tr>';
    }
  }

  searchInput?.addEventListener('input', () => { clearTimeout(window._bagSearchT); window._bagSearchT = setTimeout(refresh, 350); });
  categoryFilter?.addEventListener('change', refresh);
  statusFilter?.addEventListener('change', refresh);
  resetBtn?.addEventListener('click', () => {
    if (searchInput) searchInput.value = '';
    if (categoryFilter) categoryFilter.value = '';
    if (statusFilter) statusFilter.value = '';
    refresh();
  });

  await loadCategories();
  await refresh();
});
