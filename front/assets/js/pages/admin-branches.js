document.addEventListener('DOMContentLoaded', async () => {

  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });

  if (!ok) return;

  if (!window.AppAuth.isNationalAdmin() && !window.AppAuth.isNationalExecutive() && !window.AppAuth.isBranchManager()) {

    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;

    return;

  }



  const canManage = window.AppAuth.isNationalAdmin() && window.AppAuth.hasPermission('manage_branches');

  const grid = document.getElementById('governoratesGrid');

  const tbody = document.getElementById('branchesTableBody');

  const loading = document.getElementById('branchesLoading');

  const messageBox = document.getElementById('branchesMessage');

  const formModal = new bootstrap.Modal(document.getElementById('branchFormModal'));

  let governorates = [];

  let branches = [];

  let branchManagers = [];



  if (canManage) document.getElementById('branchCreateBtn')?.classList.remove('d-none');



  const DISABLE_CONFIRM = SiteI18n.ta('سيتم تعطيل الفرع بدلاً من حذفه إذا كان مرتبطاً ببيانات مثل مستخدمين أو دورات أو شهادات. هل تريد المتابعة؟');



  function showMessage(text, type = 'success') {

    if (!messageBox) return;

    messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'} mt-3`;

    messageBox.textContent = text;

    messageBox.classList.remove('d-none');

  }



  function statusBadge(active) {
    return active
      ? '<span class="badge text-bg-success">فعال</span>'
      : '<span class="badge text-bg-secondary">معطل</span>';
  }

  function renderManagerOptions(selectedId = '') {
    const sel = document.getElementById('branchFormManager');
    if (!sel) return;
    const govId = Number(document.getElementById('branchFormGovernorate')?.value || 0);
    const filtered = branchManagers.filter((u) => !govId || !u.governorate_id || Number(u.governorate_id) === govId);
    sel.innerHTML = '<option value="">— بدون مدير —</option>' + filtered.map((u) =>
      `<option value="${u.id}">${window.APP_HELPERS.e(u.name)} (${window.APP_HELPERS.e(u.email)})</option>`).join('');
    if (selectedId) sel.value = String(selectedId);
  }

  function renderGovernorates(stats) {
    grid.innerHTML = governorates.map((gov) => {
      const stat = stats.find((s) => s.id === gov.id) || {};
      return `<div class="col-md-4 col-lg-3"><div class="stat-card h-100"><h2>${window.APP_HELPERS.safe(stat.courses_count, 0)}</h2><p>${window.APP_HELPERS.e(gov.name_ar)} — دورات</p></div></div>`;
    }).join('');
  }

  function renderBranches() {
    tbody.innerHTML = branches.map((b) => `
      <tr>
        <td>${window.APP_HELPERS.e(b.name)}</td>
        <td>${window.APP_HELPERS.e(b.governorate?.name_ar || '—')}</td>
        <td><code>${window.APP_HELPERS.e(b.code || '—')}</code></td>
        <td>${window.APP_HELPERS.e(b.manager?.name || '—')}</td>
        <td>${statusBadge(b.is_active)}</td>
        <td>${canManage ? `
          <div class="d-flex gap-1 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${b.id}">تعديل</button>
            ${b.is_active
              ? `<button type="button" class="btn btn-sm btn-outline-warning" data-action="disable" data-id="${b.id}">تعطيل</button>`
              : `<button type="button" class="btn btn-sm btn-outline-success" data-action="enable" data-id="${b.id}">إعادة تفعيل</button>`}
          </div>` : '—'}</td>
      </tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted">لا توجد فروع.</td></tr>';
  }



  async function loadData() {

    const requests = [

      window.APP_API.get(window.APP_ROUTES.governorates()),

      window.APP_API.get(window.APP_ROUTES.branches()),

      window.APP_API.get(window.APP_ROUTES.dashboard()).catch(() => ({ governorate_stats: [] })),

    ];

    if (canManage) {

      requests.push(window.APP_API.get(window.APP_ROUTES.adminUsers({ role: 'branch_manager', is_active: 1, per_page: 100 })).catch(() => ({ data: [] })));

    }

    const [govRes, branchRes, dashRes, managersRes] = await Promise.all(requests);

    governorates = govRes?.data || [];

    branches = branchRes?.data || [];

    branchManagers = managersRes?.data || [];

    window.APP_UI.hideLoadingState(loading);

    renderGovernorates(dashRes?.governorate_stats || []);

    renderBranches();

    const govSel = document.getElementById('branchFormGovernorate');

    if (govSel) {

      govSel.innerHTML = governorates.map((g) => `<option value="${g.id}">${window.APP_HELPERS.e(g.name_ar)}</option>`).join('');

    }

    renderManagerOptions();

  }



  function openCreateBranch() {

    document.getElementById('branchFormTitle').textContent = SiteI18n.ta('إضافة فرع');

    document.getElementById('branchForm').reset();

    document.getElementById('branchFormId').value = '';

    document.getElementById('branchFormActive').value = '1';

    renderManagerOptions();

    formModal.show();

  }



  function openEditBranch(id) {

    const branch = branches.find((b) => Number(b.id) === Number(id));

    if (!branch) return;

    document.getElementById('branchFormTitle').textContent = SiteI18n.ta('تعديل فرع');

    document.getElementById('branchFormId').value = branch.id;

    document.getElementById('branchFormName').value = branch.name || '';

    document.getElementById('branchFormCode').value = branch.code || '';

    document.getElementById('branchFormGovernorate').value = branch.governorate_id || '';

    document.getElementById('branchFormActive').value = branch.is_active ? '1' : '0';

    document.getElementById('branchFormNotes').value = branch.notes || '';

    renderManagerOptions(branch.manager_user_id || '');

    formModal.show();

  }



  document.getElementById('branchCreateBtn')?.addEventListener('click', openCreateBranch);

  document.getElementById('branchFormGovernorate')?.addEventListener('change', () => renderManagerOptions());



  tbody.addEventListener('click', async (event) => {

    const btn = event.target.closest('button[data-action]');

    if (!btn || !canManage) return;

    const id = btn.dataset.id;

    const branch = branches.find((b) => Number(b.id) === Number(id));

    try {

      if (btn.dataset.action === 'edit') openEditBranch(id);

      if (btn.dataset.action === 'disable') {

        if (!confirm(DISABLE_CONFIRM)) return;

        const res = await window.APP_API.delete(window.APP_ROUTES.branchDelete(id));

        showMessage(res?.message || SiteI18n.ta('تم تعطيل الفرع.'));

        await loadData();

      }

      if (btn.dataset.action === 'enable') {

        await window.APP_API.put(window.APP_ROUTES.branchUpdate(id), {

          name: branch.name,

          code: branch.code,

          governorate_id: branch.governorate_id,

          is_active: true,

        });

        showMessage(SiteI18n.ta('تم إعادة تفعيل الفرع.'));

        await loadData();

      }

    } catch (error) {

      showMessage(error?.data?.message || SiteI18n.ta('فشلت العملية.'), 'error');

    }

  });



  document.getElementById('branchForm')?.addEventListener('submit', async (event) => {

    event.preventDefault();

    const id = document.getElementById('branchFormId').value;

    const managerVal = document.getElementById('branchFormManager')?.value;

    const payload = {

      name: document.getElementById('branchFormName').value.trim(),

      code: document.getElementById('branchFormCode').value.trim(),

      governorate_id: Number(document.getElementById('branchFormGovernorate').value),

      manager_user_id: managerVal ? Number(managerVal) : null,

      is_active: document.getElementById('branchFormActive').value === '1',

      notes: document.getElementById('branchFormNotes').value.trim() || null,

    };

    try {

      if (id) {

        await window.APP_API.put(window.APP_ROUTES.branchUpdate(id), payload);

        showMessage(SiteI18n.ta('تم تحديث الفرع.'));

      } else {

        await window.APP_API.post(window.APP_ROUTES.branchStore(), payload);

        showMessage(SiteI18n.ta('تم إنشاء الفرع.'));

      }

      formModal.hide();

      await loadData();

    } catch (error) {

      showMessage(error?.data?.message || Object.values(error?.data?.errors || {})[0]?.[0] || SiteI18n.ta('فشل الحفظ.'), 'error');

    }

  });



  try {

    await loadData();

  } catch (error) {

    window.APP_UI.hideLoadingState(loading);

    tbody.innerHTML = `<tr><td colspan="6">${window.APP_HELPERS.e(error?.data?.message || SiteI18n.ta('تعذر التحميل'))}</td></tr>`;

  }

});

