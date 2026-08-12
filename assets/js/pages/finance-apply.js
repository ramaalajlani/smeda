document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const params = new URLSearchParams(window.location.search);
  const applicationId = params.get('id');
  let currentId = applicationId ? Number(applicationId) : null;
  const canCreate = window.FinancePlatform?.canCreateApplication?.()
    || window.AppAuth?.hasPermission?.('finance.applications.create')
    || window.AppAuth?.hasRole?.('project_owner');
  const canView = window.FinancePlatform?.canViewApplications?.()
    || window.FinancePlatform?.canReviewBranch?.()
    || window.AppAuth?.hasPermission?.('finance.applications.view')
    || window.AppAuth?.hasPermission?.('finance.applications.review_branch')
    || window.AppAuth?.hasRole?.('branch_manager')
    || window.AppAuth?.hasRole?.('finance_manager')
    || window.AppAuth?.isNationalAdmin?.();

  // إنشاء طلب جديد يتطلب صلاحية الإنشاء؛ عرض طلب قائم يكفي بصلاحية العرض/المراجعة
  if (!canCreate && !(canView && currentId)) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const viewOnly = !canCreate;

  const form = document.getElementById('fundingApplicationForm');
  const saveDraftBtn = document.getElementById('saveDraftBtn');
  const submitBtn = document.getElementById('submitApplicationBtn');
  const progressText = document.getElementById('progressText');
  const progressBar = document.getElementById('progressBar');
  const statusLabel = document.getElementById('applicationStatusLabel');
  const readinessLabel = document.getElementById('readinessStatusLabel');
  const currentStepLabel = document.getElementById('currentStepLabel');
  const messageBox = document.getElementById('financeApplyMessage');
  const govSelect = document.getElementById('governorateId');
  const branchSelect = document.getElementById('branchId');

  let branchesCache = [];
  let currentStatus = 'draft';

  function showMessage(text, type = 'success') {
    if (messageBox) {
      messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
      messageBox.textContent = text;
      messageBox.classList.remove('d-none');
    }
    if (window.AppFeedback) {
      window.AppFeedback.fromMessage(text, type);
      return;
    }
  }

  function updateSummaryStatus(status, stageLabel = null) {
    currentStatus = status || 'draft';
    if (statusLabel) {
      statusLabel.textContent = window.FinancePlatform.statusLabel(currentStatus);
    }
    if (readinessLabel) {
      readinessLabel.textContent = window.FinancePlatform.readinessLabel(currentStatus);
    }
    if (currentStepLabel && stageLabel) {
      currentStepLabel.textContent = stageLabel;
    }
  }

  function lockSubmittedForm() {
    if (!form) return;
    form.querySelectorAll('input, select, textarea, button').forEach((el) => {
      if (el.id === 'submitApplicationBtn' || el.id === 'saveDraftBtn') {
        el.disabled = true;
        return;
      }
      if (el.closest('.wizard-nav')) return;
      el.disabled = true;
    });
  }

  function apiErrorMessage(err) {
    if (err?.data?.errors) {
      const first = Object.values(err.data.errors).flat()[0];
      if (first) return first;
    }
    return err?.data?.message || err?.message || SiteI18n.ta('تعذر إكمال العملية.');
  }

  function numOrNull(v) {
    if (v === null || v === undefined || v === '') return null;
    const n = Number(v);
    return Number.isFinite(n) ? n : null;
  }

  function strOrNull(v) {
    const s = String(v ?? '').trim();
    return s || null;
  }

  function getAll(name) {
    return Array.from(form.querySelectorAll(`[name="${name}"]`)).map((el) => el.value);
  }

  function collectExtraData(fd) {
    return {
      financing_mode: strOrNull(fd.get('financing_mode')),
      project_status: strOrNull(fd.get('project_status')),
      syrsic_activity_code: strOrNull(fd.get('syrsic_activity_code')),
      legal_status: strOrNull(fd.get('legal_status')),
      profession: strOrNull(fd.get('profession')),
      syrian_nationality: strOrNull(fd.get('syrian_nationality')),
      city_or_location: strOrNull(fd.get('city_or_location')),
      proposed_margin: strOrNull(fd.get('proposed_margin')),
      first_payment_date: strOrNull(fd.get('first_payment_date')),
      first_payment_value: numOrNull(fd.get('first_payment_value')),
      proposed_guarantee_margin: strOrNull(fd.get('proposed_guarantee_margin')),
      provided_guarantees: strOrNull(fd.get('provided_guarantees')),
      default_case_action: strOrNull(fd.get('default_case_action')),
      company_name: strOrNull(fd.get('company_name')),
      commercial_register: strOrNull(fd.get('commercial_register')),
      business_phone: strOrNull(fd.get('business_phone')),
      invoice_type: strOrNull(fd.get('invoice_type')),
      guarantor_details: strOrNull(fd.get('guarantor_details')),
      total_workforce: numOrNull(fd.get('total_workforce')),
      admin_employees: numOrNull(fd.get('admin_employees')),
      technical_employees: numOrNull(fd.get('technical_employees')),
      industrial_workers: numOrNull(fd.get('industrial_workers')),
      postgraduate_count: numOrNull(fd.get('postgraduate_count')),
      university_count: numOrNull(fd.get('university_count')),
      institute_count: numOrNull(fd.get('institute_count')),
      secondary_count: numOrNull(fd.get('secondary_count')),
      below_secondary_count: numOrNull(fd.get('below_secondary_count')),
      has_training_support: strOrNull(fd.get('has_training_support')),
      training_history: strOrNull(fd.get('training_history')),
      admin_training_need: strOrNull(fd.get('admin_training_need')),
      technical_training_need: strOrNull(fd.get('technical_training_need')),
      industrial_training_need: strOrNull(fd.get('industrial_training_need')),
      acknowledge_info: !!form.querySelector('#acknowledgeInfo')?.checked,
      balance_sheets: {
        y2023: getAll('balance_2023[]'),
        y2024: getAll('balance_2024[]'),
        y2025: getAll('balance_2025[]'),
        audited: getAll('balance_audited[]'),
        notes: getAll('balance_notes[]'),
      },
      income_statements: {
        y2023: getAll('income_2023[]'),
        y2024: getAll('income_2024[]'),
        y2025: getAll('income_2025[]'),
        audited: getAll('income_audited[]'),
        notes: getAll('income_notes[]'),
      },
    };
  }

  function collectPayload() {
    const fd = new FormData(form);
    const user = window.AppAuth.getUser?.() || window.AppAuth.user || {};
    const employees = numOrNull(fd.get('total_workforce'));

    return {
      applicant_name: strOrNull(fd.get('applicant_name')) || user.name || null,
      national_id: strOrNull(fd.get('national_id')),
      phone: strOrNull(fd.get('phone')),
      email: strOrNull(fd.get('email')) || user.email || null,
      governorate_id: numOrNull(fd.get('governorate_id')) || undefined,
      branch_id: numOrNull(fd.get('branch_id')) || undefined,
      project_name: strOrNull(fd.get('project_name')) || strOrNull(fd.get('company_name')),
      project_type: strOrNull(fd.get('project_sector')),
      project_sector: strOrNull(fd.get('project_sector')),
      project_size: strOrNull(fd.get('project_size')) || 'small',
      business_stage: strOrNull(fd.get('business_stage')) || (
        fd.get('project_status') === 'existing' ? 'existing' : 'startup'
      ),
      project_status: strOrNull(fd.get('project_status')),
      requested_amount: Number(fd.get('requested_amount') || 0),
      currency: strOrNull(fd.get('currency')) || 'SYP',
      financing_type: strOrNull(fd.get('financing_type')) || 'capital',
      financing_mode: strOrNull(fd.get('financing_mode')),
      repayment_period_months: numOrNull(fd.get('repayment_period_months')),
      purpose: strOrNull(fd.get('purpose')),
      description: strOrNull(fd.get('description')),
      details: {
        owner_experience: strOrNull(fd.get('owner_experience')),
        employees_count: employees,
        monthly_revenue: null,
        monthly_expenses: null,
        existing_debts: null,
        assets_description: strOrNull(fd.get('provided_guarantees')),
        market_description: strOrNull(fd.get('market_description')),
        challenges: strOrNull(fd.get('challenges')),
        requested_support: strOrNull(fd.get('requested_support')),
        notes: JSON.stringify(collectExtraData(fd)),
        extra_data: collectExtraData(fd),
      },
    };
  }

  function validateRequired(payload, forSubmit = false) {
    const missing = [];
    if (!payload.applicant_name) missing.push(SiteI18n.ta('اسم مقدم الطلب'));
    if (!payload.project_name) missing.push(SiteI18n.ta('اسم المشروع'));
    if (!(Number(payload.requested_amount) > 0)) missing.push(SiteI18n.ta('مبلغ التمويل'));
    if (forSubmit && !payload.governorate_id) missing.push(SiteI18n.ta('المحافظة'));
    return missing;
  }

  function updateProgress(payload, forcedPct = null) {
    const required = ['applicant_name', 'project_name', 'requested_amount', 'governorate_id'];
    const filled = required.filter((k) => {
      if (k === 'requested_amount') return Number(payload[k]) > 0;
      return !!payload[k];
    }).length;
    const pct = forcedPct != null
      ? forcedPct
      : (currentStatus !== 'draft' && currentStatus !== 'needs_completion'
        ? 100
        : Math.round((filled / required.length) * 100));
    if (progressText) progressText.textContent = `${pct}%`;
    if (progressBar) progressBar.style.width = `${pct}%`;
  }

  function fillBranches(govId, selectedBranchId = null) {
    if (!branchSelect) return;
    const list = branchesCache.filter((b) => !govId || Number(b.governorate_id) === Number(govId));
    branchSelect.innerHTML = `<option value="">${SiteI18n.ta('يُحدَّد تلقائياً حسب المحافظة')}</option>`
      + list.map((b) => `<option value="${b.id}">${window.APP_HELPERS?.e?.(b.name) || b.name}</option>`).join('');
    if (selectedBranchId) branchSelect.value = String(selectedBranchId);
  }

  async function fetchGovernorates() {
    try {
      const res = await window.APP_API.get(window.APP_ROUTES.governorates({ lite: 1 }));
      const list = res?.data || res || [];
      if (Array.isArray(list) && list.length) return list;
    } catch (_) { /* fallback below */ }

    try {
      const url = window.APP_ROUTES.publicGovernorates?.()
        || `${window.APP_CONFIG.API_BASE_URL}/public/governorates`;
      const res = await window.APP_API.get(url);
      const list = res?.data || res || [];
      if (Array.isArray(list) && list.length) return list;
    } catch (_) { /* fallback below */ }

    return (window.SYRIA_GOVERNORATES_LIST || []).map((g) => ({
      id: g.value,
      name_ar: g.label,
    }));
  }

  async function loadLookups() {
    const [governorates, branchRes] = await Promise.all([
      fetchGovernorates(),
      window.APP_API.get(window.APP_ROUTES.branches({ lite: 1 })).catch(() => ({ data: [] })),
    ]);

    branchesCache = branchRes?.data || branchRes || [];

    if (govSelect) {
      const user = window.AppAuth.getUser?.() || {};
      govSelect.innerHTML = `<option value="">${SiteI18n.ta('اختر المحافظة')}</option>`
        + (Array.isArray(governorates) ? governorates : []).map((g) =>
          `<option value="${g.id}">${window.APP_HELPERS?.e?.(g.name_ar || g.name) || (g.name_ar || g.name)}</option>`
        ).join('');
      if (user.governorate_id) {
        govSelect.value = String(user.governorate_id);
      }
    }

    fillBranches(govSelect?.value || null, window.AppAuth.getUser?.()?.branch_id || null);
  }

  govSelect?.addEventListener('change', () => fillBranches(govSelect.value));

  function setFieldValue(name, value) {
    if (value == null) return;
    const el = form.querySelector(`[name="${name}"]`);
    if (!el) return;
    if (el.type === 'radio' || el.type === 'checkbox') {
      const match = form.querySelector(`[name="${name}"][value="${value}"]`);
      if (match) match.checked = true;
      return;
    }
    el.value = value;
  }

  function hydrateForm(data) {
    if (!data || !form) return;
    [
      'applicant_name', 'national_id', 'phone', 'email', 'project_name', 'project_sector',
      'project_size', 'business_stage', 'project_status', 'requested_amount', 'currency',
      'financing_type', 'financing_mode', 'repayment_period_months', 'purpose', 'description',
      'governorate_id', 'branch_id',
    ].forEach((key) => setFieldValue(key, data[key]));

    updateSummaryStatus(data.status || 'draft', window.FinancePlatform.statusLabel(data.status || 'draft'));
    if (viewOnly || (data.status && data.status !== 'draft' && data.status !== 'needs_completion')) {
      lockSubmittedForm();
    }

    const details = data.details || {};
    [
      'owner_experience', 'market_description', 'challenges', 'requested_support',
    ].forEach((key) => setFieldValue(key, details[key]));

    let extra = details.extra_data;
    if (!extra && details.notes) {
      try { extra = JSON.parse(details.notes); } catch (_) { extra = null; }
    }
    if (extra && typeof extra === 'object') {
      Object.entries(extra).forEach(([key, value]) => {
        if (['balance_sheets', 'income_statements', 'acknowledge_info'].includes(key)) return;
        setFieldValue(key, value);
      });
      if (extra.total_workforce != null) setFieldValue('total_workforce', extra.total_workforce);
    }

    if (details.employees_count != null) setFieldValue('total_workforce', details.employees_count);
    fillBranches(data.governorate_id, data.branch_id);
  }

  async function saveDraft() {
    const payload = collectPayload();
    updateProgress(payload);

    const missing = validateRequired(payload, false);
    if (missing.length) {
      throw new Error(`${SiteI18n.ta('أكمل الحقول التالية:')} ${missing.join('، ')}`);
    }

    if (currentId) {
      await window.APP_API.put(window.APP_ROUTES.fundingApplicationUpdate(currentId), payload);
      await uploadFormFiles(currentId);
      showMessage(SiteI18n.ta('تم حفظ المسودة.'));
      return currentId;
    }

    const res = await window.APP_API.post(window.APP_ROUTES.fundingApplicationStore(), payload);
    currentId = res.data?.id || res.data?.data?.id || res?.id;
    if (currentId) {
      const url = new URL(window.location.href);
      url.searchParams.set('id', currentId);
      window.history.replaceState({}, '', url);
      await uploadFormFiles(currentId);
    }
    showMessage(SiteI18n.ta('تم إنشاء مسودة الطلب.'));
    return currentId;
  }

  async function uploadFormFiles(applicationId) {
    if (!form || !applicationId || !window.APP_ROUTES?.fundingApplicationDocuments) return;
    const groups = [
      ['activity_invoices', 'activity_invoices'],
      ['work_license_or_request', 'work_license_or_request'],
      ['real_estate_record', 'real_estate_record'],
      ['bank_statement', 'bank_statement'],
    ];
    for (const [inputName, documentType] of groups) {
      const input = form.querySelector(`[name="${inputName}"]`);
      const files = Array.from(input?.files || []);
      for (const file of files) {
        const body = new FormData();
        body.append('document_type', documentType);
        body.append('file', file);
        await window.APP_API.post(window.APP_ROUTES.fundingApplicationDocuments(applicationId), body);
      }
      if (input) input.value = '';
    }
  }

  saveDraftBtn?.addEventListener('click', async (e) => {
    e.preventDefault();
    try {
      saveDraftBtn.disabled = true;
      await saveDraft();
    } catch (err) {
      showMessage(apiErrorMessage(err), 'error');
    } finally {
      saveDraftBtn.disabled = false;
    }
  });

  submitBtn?.addEventListener('click', async (e) => {
    e.preventDefault();
    try {
      if (window.FinanceApplySteps && !window.FinanceApplySteps.validateAll()) {
        return;
      }
      if (!form.querySelector('#acknowledgeInfo')?.checked) {
        throw new Error(SiteI18n.ta('يجب الموافقة على الإقرار قبل الإرسال.'));
      }
      const payload = collectPayload();
      const missing = validateRequired(payload, true);
      if (missing.length) {
        throw new Error(`${SiteI18n.ta('أكمل الحقول التالية قبل الإرسال:')} ${missing.join('، ')}`);
      }
      submitBtn.disabled = true;
      const id = await saveDraft();
      if (!id) throw new Error(SiteI18n.ta('يجب حفظ الطلب أولاً.'));
      const submitRes = await window.APP_API.post(window.APP_ROUTES.fundingApplicationSubmit(id));
      const submitted = submitRes?.data?.data || submitRes?.data || {};
      updateSummaryStatus(submitted.status || 'submitted', SiteI18n.ta('بانتظار مراجعة الفرع'));
      updateProgress(collectPayload(), 100);
      lockSubmittedForm();
      showMessage(SiteI18n.ta('تم إرسال طلب التمويل بنجاح. سيظهر لمدير الفرع المختص، ويُسجَّل تلقائياً على خارطة الاحتياجات (فلتر: تمويل).'));
    } catch (err) {
      showMessage(apiErrorMessage(err), 'error');
      submitBtn.disabled = false;
    }
  });

  try {
    await loadLookups();
    const user = window.AppAuth.getUser?.() || {};
    if (!form.querySelector('[name="applicant_name"]')?.value && user.name) {
      setFieldValue('applicant_name', user.name);
    }
    if (!form.querySelector('[name="email"]')?.value && user.email) {
      setFieldValue('email', user.email);
    }
    if (!form.querySelector('[name="phone"]')?.value && user.phone) {
      setFieldValue('phone', user.phone);
    }

    if (viewOnly) {
      if (saveDraftBtn) saveDraftBtn.classList.add('d-none');
      if (submitBtn) submitBtn.classList.add('d-none');
      lockSubmittedForm();
    }

    if (currentId) {
      const res = await window.APP_API.get(window.APP_ROUTES.fundingApplicationShow(currentId));
      const data = res.data?.data || res.data || res;
      hydrateForm(data);
      updateProgress(collectPayload());
    }
  } catch (err) {
    showMessage(apiErrorMessage(err), 'error');
  }
});
