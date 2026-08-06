document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  if (!window.AppAuth.hasPermission('workforce.training_requests.create')) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const form = document.getElementById('staffTrainingForm');
  const messageBox = document.getElementById('staffTrainingMessage');
  const submitBtn = document.getElementById('staffTrainingSubmitBtn');

  function showMessage(text, type = 'danger') {
    if (!messageBox) return;
    messageBox.className = `alert alert-${type}`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  function setLoading(isLoading) {
    if (!submitBtn) return;
    submitBtn.disabled = isLoading;
    submitBtn.textContent = isLoading ? 'جاري الإرسال...': SiteI18n.ta('إرسال الطلب');
  }

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    setLoading(true);

    const payload = {
      organization_name: document.getElementById('organizationName')?.value?.trim(),
      employees_count: Number(document.getElementById('employeesCount')?.value || 1),
      training_field: document.getElementById('trainingField')?.value?.trim(),
      city: document.getElementById('trainingCity')?.value?.trim(),
      details: document.getElementById('trainingDetails')?.value?.trim(),
    };

    try {
      await window.APP_API.post(window.APP_ROUTES.staffTrainingRequestStore(), payload);
      showMessage(SiteI18n.ta('تم إرسال طلب تدريب الكوادر بنجاح.'), 'success');
      form.reset();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('تعذر إرسال الطلب.'));
    } finally {
      setLoading(false);
    }
  });
});
