document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  if (!window.FinancePlatform.canCreateApplication()) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const params = new URLSearchParams(window.location.search);
  const applicationId = params.get('id');
  let currentId = applicationId ? Number(applicationId) : null;

  const form = document.getElementById('fundingApplicationForm');
  const saveDraftBtn = document.getElementById('saveDraftBtn');
  const submitBtn = document.getElementById('submitApplicationBtn');
  const progressText = document.getElementById('progressText');
  const messageBox = document.getElementById('financeApplyMessage');

  function showMessage(text, type = 'success') {
    if (!messageBox) return;
    messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  function collectPayload() {
    const fd = new FormData(form);
    return {
      applicant_name: fd.get('applicant_name') || window.AppAuth.user?.name,
      national_id: fd.get('national_id') || null,
      phone: fd.get('phone') || null,
      email: fd.get('email') || window.AppAuth.user?.email,
      governorate_id: fd.get('governorate_id') ? Number(fd.get('governorate_id')) : undefined,
      branch_id: fd.get('branch_id') ? Number(fd.get('branch_id')) : undefined,
      project_name: fd.get('project_name'),
      project_sector: fd.get('project_sector') || null,
      project_size: fd.get('project_size') || 'small',
      business_stage: fd.get('business_stage') || 'startup',
      requested_amount: Number(fd.get('requested_amount') || 0),
      financing_type: fd.get('financing_type') || 'capital',
      repayment_period_months: fd.get('repayment_period_months') ? Number(fd.get('repayment_period_months')) : null,
      purpose: fd.get('purpose') || null,
      description: fd.get('description') || null,
      details: {
        owner_experience: fd.get('owner_experience') || null,
        employees_count: fd.get('employees_count') ? Number(fd.get('employees_count')) : null,
        monthly_revenue: fd.get('monthly_revenue') ? Number(fd.get('monthly_revenue')) : null,
        monthly_expenses: fd.get('monthly_expenses') ? Number(fd.get('monthly_expenses')) : null,
      },
    };
  }

  function updateProgress(payload) {
    const required = ['applicant_name', 'project_name', 'requested_amount'];
    const filled = required.filter((k) => payload[k]).length;
    const pct = Math.round((filled / required.length) * 100);
    if (progressText) progressText.textContent = `${pct}%`;
  }

  async function saveDraft() {
    const payload = collectPayload();
    updateProgress(payload);

    if (currentId) {
      await window.APP_API.put(window.APP_ROUTES.fundingApplicationUpdate(currentId), payload);
      showMessage(SiteI18n.ta('تم حفظ المسودة.'));
      return currentId;
    }

    const res = await window.APP_API.post(window.APP_ROUTES.fundingApplicationStore(), payload);
    currentId = res.data?.id || res.data?.data?.id;
    if (currentId) {
      const url = new URL(window.location.href);
      url.searchParams.set('id', currentId);
      window.history.replaceState({}, '', url);
    }
    showMessage(SiteI18n.ta('تم إنشاء مسودة الطلب.'));
    return currentId;
  }

  saveDraftBtn?.addEventListener('click', async (e) => {
    e.preventDefault();
    try {
      await saveDraft();
    } catch (err) {
      showMessage(err.message || SiteI18n.ta('تعذر حفظ المسودة.'), 'error');
    }
  });

  submitBtn?.addEventListener('click', async (e) => {
    e.preventDefault();
    try {
      const id = await saveDraft();
      if (!id) throw new Error(SiteI18n.ta('يجب حفظ الطلب أولاً.'));
      await window.APP_API.post(window.APP_ROUTES.fundingApplicationSubmit(id));
      showMessage(SiteI18n.ta('تم إرسال طلب التمويل بنجاح.'));
    } catch (err) {
      showMessage(err.message || SiteI18n.ta('تعذر إرسال الطلب.'), 'error');
    }
  });

  if (currentId) {
    try {
      const res = await window.APP_API.get(window.APP_ROUTES.fundingApplicationShow(currentId));
      const data = res.data || res;
      Object.entries(data).forEach(([key, value]) => {
        const input = form?.querySelector(`[name="${key}"]`);
        if (input && value != null) input.value = value;
      });
    } catch (_) {}
  }
});
