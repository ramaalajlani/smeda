document.addEventListener('DOMContentLoaded', async () => {

  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });

  if (!ok) return;

  if (!window.AppAuth.hasPermission('manage_agreements') && !window.AppAuth.isNationalAdmin() && !window.AppAuth.isBranchManager()) {

    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;

    return;

  }



  const canManage = window.AppAuth.isNationalAdmin() || window.AppAuth.hasPermission('manage_agreements');

  const canApprove = window.AppAuth.isNationalAdmin() || window.AppAuth.hasPermission('approve_agreements');

  const tbody = document.getElementById('agreementsTableBody');

  const loading = document.getElementById('agreementsLoading');

  const statsEl = document.getElementById('agreementsStats');

  const messageBox = document.getElementById('agreementsMessage');

  const formModal = new bootstrap.Modal(document.getElementById('agreementFormModal'));

  let rows = [];

  let governorates = [];

  let branches = [];



  if (canManage) document.getElementById('addAgreementBtn')?.classList.remove('d-none');



  function showMessage(text, type = 'success') {

    messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;

    messageBox.textContent = text;

    messageBox.classList.remove('d-none');

  }



  function scopeLabel(row) {

    return row.scope_type === 'branch' ? 'فرع': SiteI18n.ta('مركزية');

  }



  function toggleAgreementBranchScope() {

    const show = document.getElementById('agreementFormScope')?.value === 'branch';

    document.getElementById('agreementBranchScopeWrap')?.classList.toggle('d-none', !show);

    if (show) filterAgreementBranches();

  }



  function filterAgreementBranches() {

    const govId = Number(document.getElementById('agreementFormGovernorate')?.value || 0);

    const branchSel = document.getElementById('agreementFormBranch');

    if (!branchSel) return;

    const filtered = branches.filter((b) => !govId || Number(b.governorate_id) === govId);

    branchSel.innerHTML = filtered.map((b) => `<option value="${b.id}">${window.APP_HELPERS.e(b.name)}</option>`).join('');

  }



  function renderStats() {

    const total = rows.length;

    const active = rows.filter((r) => r.status === 'active').length;

    const draft = rows.filter((r) => r.status === 'draft').length;

    const expired = rows.filter((r) => r.status === 'expired').length;

    statsEl.innerHTML = `

      <div class="col-md-3"><div class="stat-card"><h2>${total}</h2><p>إجمالي الاتفاقيات</p></div></div>

      <div class="col-md-3"><div class="stat-card"><h2>${active}</h2><p>النشطة</p></div></div>

      <div class="col-md-3"><div class="stat-card"><h2>${draft}</h2><p>قيد المراجعة</p></div></div>

      <div class="col-md-3"><div class="stat-card"><h2>${expired}</h2><p>المنتهية</p></div></div>`;

  }



  function renderTable() {

    tbody.innerHTML = rows.map((r) => `

      <tr>

        <td>${r.id}</td>

        <td><a href=")agreement-view.php?id=${r.id}">${window.APP_HELPERS.e(r.title)}</a></td>

        <td>${window.APP_HELPERS.e(r.partner_name)}</td>

        <td>${scopeLabel(r)}</td>

        <td>${window.APP_HELPERS.e(r.status)}</td>

        <td>${window.APP_HELPERS.e(r.start_date || '—')}</td>

        <td><div class="d-flex gap-1 flex-wrap">

          <a href="agreement-view.php?id=${r.id}" class="btn btn-sm btn-outline-secondary">عرض</a>

          ${canManage ? `<button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${r.id}">تعديل</button>` : ''}

          ${canApprove && r.status !== 'activeSiteI18n.ta(' ? `<button type="button" class="btn btn-sm btn-outline-success" data-action="approve" data-id="${r.id}">اعتماد</button>` : ''}

        </div></td>

      </tr>`).join('') || SiteI18n.ta('<tr><td colspan="7" class="text-center text-muted py-4">لا توجد اتفاقيات.</td></tr>');

    renderStats();

  }



  async function loadAgreements() {

    const [res, govRes, branchRes] = await Promise.all([

      window.APP_API.get(window.APP_ROUTES.agreements({ per_page: 100 })),

      window.APP_API.get(window.APP_ROUTES.governorates()).catch(() => ({ data: [] })),

      window.APP_API.get(window.APP_ROUTES.branches()).catch(() => ({ data: [] })),

    ]);

    rows = res?.data || [];

    governorates = govRes?.data || [];

    branches = branchRes?.data || [];

    const govSel = document.getElementById('agreementFormGovernorate');

    if (govSel) govSel.innerHTML = governorates.map((g) => `<option value=")${g.id}">${window.APP_HELPERS.e(g.name_ar)}</option>`).join('');

    filterAgreementBranches();

    window.APP_UI.hideLoadingState(loading);

    renderTable();

  }



  document.getElementById('agreementFormScope')?.addEventListener('change', toggleAgreementBranchScope);

  document.getElementById('agreementFormGovernorate')?.addEventListener('change', filterAgreementBranches);



  document.getElementById('addAgreementBtn')?.addEventListener('click', () => {

    document.getElementById('agreementFormTitle').textContent = SiteI18n.ta('إضافة اتفاقية');

    document.getElementById('agreementForm').reset();

    document.getElementById('agreementFormId').value = '';

    document.getElementById('agreementFormScope').value = 'national';

    toggleAgreementBranchScope();

    formModal.show();

  });



  tbody.addEventListener('click', async (event) => {

    const btn = event.target.closest('button[data-action]');

    if (!btn) return;

    const row = rows.find((r) => Number(r.id) === Number(btn.dataset.id));

    try {

      if (btn.dataset.action === 'edit' && row) {

        document.getElementById('agreementFormTitle').textContent = SiteI18n.ta('تعديل اتفاقية');

        document.getElementById('agreementFormId').value = row.id;

        document.getElementById('agreementFormTitleInput').value = row.title || '';

        document.getElementById('agreementFormPartner').value = row.partner_name || '';

        document.getElementById('agreementFormType').value = row.agreement_type || 'general';

        document.getElementById('agreementFormScope').value = row.scope_type || 'national';

        document.getElementById('agreementFormStatus').value = row.status || 'draft';

        document.getElementById('agreementFormAmount').value = row.amount || '';

        document.getElementById('agreementFormStart').value = row.start_date?.slice?.(0, 10) || row.start_date || '';

        document.getElementById('agreementFormEnd').value = row.end_date?.slice?.(0, 10) || row.end_date || '';

        document.getElementById('agreementFormNotes').value = row.notes || '';

        if (row.governorate_id) document.getElementById('agreementFormGovernorate').value = row.governorate_id;

        toggleAgreementBranchScope();

        if (row.branch_id) document.getElementById('agreementFormBranch').value = row.branch_id;

        formModal.show();

      }

      if (btn.dataset.action === 'approve') {

        await window.APP_API.post(window.APP_ROUTES.agreementApprove(btn.dataset.id));

        showMessage(SiteI18n.ta('تم اعتماد الاتفاقية.'));

        await loadAgreements();

      }

    } catch (error) {

      showMessage(error?.data?.message || SiteI18n.ta('فشلت العملية.'), 'error');

    }

  });



  document.getElementById('agreementForm')?.addEventListener('submit', async (event) => {

    event.preventDefault();

    const id = document.getElementById('agreementFormId').value;

    const scopeType = document.getElementById('agreementFormScope').value;

    const payload = {

      title: document.getElementById('agreementFormTitleInput').value.trim(),

      partner_name: document.getElementById('agreementFormPartner').value.trim(),

      agreement_type: document.getElementById('agreementFormType').value.trim(),

      scope_type: scopeType,

      status: document.getElementById('agreementFormStatus').value,

      amount: document.getElementById('agreementFormAmount').value || null,

      start_date: document.getElementById('agreementFormStart').value || null,

      end_date: document.getElementById('agreementFormEnd').value || null,

      notes: document.getElementById('agreementFormNotes').value.trim() || null,

    };

    if (scopeType === 'branch') {

      payload.governorate_id = Number(document.getElementById('agreementFormGovernorate').value);

      payload.branch_id = Number(document.getElementById('agreementFormBranch').value);

    }

    try {

      if (id) {

        await window.APP_API.put(window.APP_ROUTES.agreementUpdate(id), payload);

        showMessage(SiteI18n.ta('تم تحديث الاتفاقية.'));

      } else {

        await window.APP_API.post(window.APP_ROUTES.agreementStore(), payload);

        showMessage(SiteI18n.ta('تم إنشاء الاتفاقية.'));

      }

      formModal.hide();

      await loadAgreements();

    } catch (error) {

      showMessage(error?.data?.message || SiteI18n.ta('فشل الحفظ.'), 'error');

    }

  });



  try {

    await loadAgreements();

  } catch (error) {

    window.APP_UI.hideLoadingState(loading);

    tbody.innerHTML = `<tr><td colspan="7">${window.APP_HELPERS.e(error?.data?.message || SiteI18n.ta('تعذر التحميل'))}</td></tr>`;

  }

});

