document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  if (!window.AppAuth.hasPermission('workforce.applications.create')) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const form = document.getElementById('jobRequestForm');
  const messageBox = document.getElementById('jobRequestMessage');
  const submitBtn = document.getElementById('jobRequestSubmitBtn');
  const jobIdInput = document.getElementById('jobPostingId');
  const params = new URLSearchParams(window.location.search);
  const jobId = params.get('job_id');

  if (jobId && jobIdInput) {
    jobIdInput.value = jobId;
  }

  const user = window.AppAuth.getUser();
  const nameInput = document.getElementById('applicantName');
  const emailInput = document.getElementById('applicantEmail');
  if (nameInput && !nameInput.value) nameInput.value = user.name || '';
  if (emailInput && !emailInput.value) emailInput.value = user.email || '';

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

    const formData = new FormData();
    const postingId = jobIdInput?.value || '';
    if (postingId) formData.append('job_posting_id', postingId);
    formData.append('applicant_name', document.getElementById('applicantName')?.value?.trim() || '');
    formData.append('phone', document.getElementById('applicantPhone')?.value?.trim() || '');
    formData.append('email', document.getElementById('applicantEmail')?.value?.trim() || '');
    formData.append('specialty', document.getElementById('applicantSpecialty')?.value?.trim() || '');
    formData.append('city', document.getElementById('applicantCity')?.value?.trim() || '');
    formData.append('experience_years', document.getElementById('experienceYears')?.value?.trim() || '');
    formData.append('summary', document.getElementById('applicantSummary')?.value?.trim() || '');

    const cvFile = document.getElementById('cvFile')?.files?.[0];
    if (cvFile) formData.append('cv', cvFile);

    try {
      await window.APP_API.post(window.APP_ROUTES.jobApplicationStore(), formData);
      showMessage(SiteI18n.ta('تم إرسال طلب التوظيف بنجاح.'), 'success');
      form.reset();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('تعذر إرسال الطلب.'));
    } finally {
      setLoading(false);
    }
  });
});
