document.addEventListener('DOMContentLoaded', async () => {

  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });

  if (!ok) return;

  const canView = window.AppAuth.hasPermission('view_finance') || window.AppAuth.isNationalAdmin() || window.AppAuth.hasRole('auditor');

  if (!canView) {

    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;

    return;

  }



  const canManage = window.AppAuth.isNationalAdmin() || window.AppAuth.hasPermission('manage_finance');

  const canApprove = window.AppAuth.isNationalAdmin() || window.AppAuth.hasPermission('approve_finance');

  const isReadOnly = window.AppAuth.hasRole('auditor') && !canManage;

  const tbody = document.getElementById('financeTableBody');

  const loading = document.getElementById('financeLoading');

  const statsEl = document.getElementById('financeStats');

  const messageBox = document.getElementById('financeMessage');

  const formModal = new bootstrap.Modal(document.getElementById('financeFormModal'));

  let rows = [];

  let branches = [];



  if (canManage && !isReadOnly) document.getElementById('addFinanceBtn')?.classList.remove('d-none');



  function showMessage(text, type = 'success') {

    messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;

    messageBox.textContent = text;

    messageBox.classList.remove('d-none');

  }



  function renderStats() {

    const total = rows.reduce((s, r) => s + Number(r.amount || 0), 0);

    const approved = rows.filter((r) => r.status === 'approved').reduce((s, r) => s + Number(r.amount || 0), 0);

    statsEl.innerHTML = `

      <div class="col-md-3"><div class="stat-card"><h2>${rows.length}</h2><p>عدد السجلات</p></div></div>

      <div class="col-md-3"><div class="stat-card"><h2>${total.toLocaleString('ar-SY')}</h2><p>إجمالي المبالغ</p></div></div>

      <div class="col-md-3"><div class="stat-card"><h2>${approved.toLocaleString('ar-SY')}</h2><p>المعتمد</p></div></div>

      <div class="col-md-3"><div class="stat-card"><h2>${(total - approved).toLocaleString('ar-SY')}</h2><p>غير المعتمد</p></div></div>`;

  }



  function renderTable() {

    tbody.innerHTML = rows.map((r) => `

      <tr>

        <td>${r.id}</td>

        <td>${window.APP_HELPERS.e(r.title)}</td>

        <td>${window.APP_HELPERS.e(r.record_type)}</td>

        <td>${Number(r.amount || 0).toLocaleString('ar-SY')} ${window.APP_HELPERS.e(r.currency || 'SYP')}</td>

        <td>${window.APP_HELPERS.e(r.branch?.name || '—')}</td>

        <td>${window.APP_HELPERS.e(r.status)}</td>

        <td><div class="d-flex gap-1 flex-wrap">

          <a href="finance-record-view.php?id=${r.id}" class="btn btn-sm btn-outline-secondary">عرض</a>

          ${canManage && !isReadOnly ? `<button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${r.id}">تعديل</button>` : ''}

          ${canApprove && r.status !== 'approvedSiteI18n.ta(' ? `<button type="button" class="btn btn-sm btn-outline-success" data-action="approve" data-id="${r.id}">اعتماد</button>` : ''}

        </div></td>

      </tr>`).join('') || SiteI18n.ta('<tr><td colspan="7" class="text-center text-muted py-4">لا توجد سجلات مالية.</td></tr>');

    renderStats();

  }



  async function loadRecords() {

    const [res, branchRes] = await Promise.all([

      window.APP_API.get(window.APP_ROUTES.financeRecords({ per_page: 100 })),

      window.APP_API.get(window.APP_ROUTES.branches()).catch(() => ({ data: [] })),

    ]);

    rows = res?.data || [];

    branches = branchRes?.data || [];

    const branchSel = document.getElementById('financeFormBranch');

    if (branchSel) {

      branchSel.innerHTML = '<option value=")">—</option>' + branches.map((b) =>

        `<option value="${b.id}">${window.APP_HELPERS.e(b.name)}</option>`).join('');

    }

    window.APP_UI.hideLoadingState(loading);

    renderTable();

  }



  document.getElementById('addFinanceBtn')?.addEventListener('click', () => {

    document.getElementById('financeFormTitle').textContent = SiteI18n.ta('إضافة سجل مالي');

    document.getElementById('financeForm').reset();

    document.getElementById('financeFormId').value = '';

    document.getElementById('financeFormCurrency').value = 'SYP';

    formModal.show();

  });



  tbody.addEventListener('click', async (event) => {

    const btn = event.target.closest('button[data-action]');

    if (!btn) return;

    const row = rows.find((r) => Number(r.id) === Number(btn.dataset.id));

    try {

      if (btn.dataset.action === 'edit' && row) {

        document.getElementById('financeFormTitle').textContent = SiteI18n.ta('تعديل سجل مالي');

        document.getElementById('financeFormId').value = row.id;

        document.getElementById('financeFormTitleInput').value = row.title || '';

        document.getElementById('financeFormType').value = row.record_type || 'funding';

        document.getElementById('financeFormAmount').value = row.amount || '';

        document.getElementById('financeFormCurrency').value = row.currency || 'SYP';

        document.getElementById('financeFormStatus').value = row.status || 'draft';

        document.getElementById('financeFormBranch').value = row.branch_id || '';

        document.getElementById('financeFormNotes').value = row.notes || '';

        formModal.show();

      }

      if (btn.dataset.action === 'approve') {

        await window.APP_API.post(window.APP_ROUTES.financeRecordApprove(btn.dataset.id));

        showMessage(SiteI18n.ta('تم اعتماد السجل المالي.'));

        await loadRecords();

      }

    } catch (error) {

      showMessage(error?.data?.message || SiteI18n.ta('فشلت العملية.'), 'error');

    }

  });



  document.getElementById('financeForm')?.addEventListener('submit', async (event) => {

    event.preventDefault();

    const id = document.getElementById('financeFormId').value;

    const payload = {

      title: document.getElementById('financeFormTitleInput').value.trim(),

      record_type: document.getElementById('financeFormType').value,

      amount: Number(document.getElementById('financeFormAmount').value),

      currency: document.getElementById('financeFormCurrency').value.trim() || 'SYP',

      status: document.getElementById('financeFormStatus').value,

      branch_id: document.getElementById('financeFormBranch').value || null,

      notes: document.getElementById('financeFormNotes').value.trim() || null,

    };

    try {

      if (id) {

        await window.APP_API.put(window.APP_ROUTES.financeRecordUpdate(id), payload);

        showMessage(SiteI18n.ta('تم تحديث السجل.'));

      } else {

        await window.APP_API.post(window.APP_ROUTES.financeRecordStore(), payload);

        showMessage(SiteI18n.ta('تم إنشاء السجل.'));

      }

      formModal.hide();

      await loadRecords();

    } catch (error) {

      showMessage(error?.data?.message || SiteI18n.ta('فشل الحفظ.'), 'error');

    }

  });



  try {

    await loadRecords();

  } catch (error) {

    window.APP_UI.hideLoadingState(loading);

    tbody.innerHTML = `<tr><td colspan="7">${window.APP_HELPERS.e(error?.data?.message || SiteI18n.ta('تعذر التحميل'))}</td></tr>`;

  }

});

