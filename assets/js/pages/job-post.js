document.addEventListener('DOMContentLoaded', async () => {
  const params = new URLSearchParams(window.location.search);
  const jobId = params.get('id');
  const isView = Boolean(jobId);

  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const form = document.getElementById('jobPostForm');
  const messageBox = document.getElementById('jobPostMessage');
  const submitBtn = document.getElementById('jobPostSubmitBtn');
  const pageTitle = document.getElementById('jobPostPageTitle');

  if (isView) {
    if (!window.AppAuth.hasPermission('workforce.jobs.view')) {
      window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
      return;
    }
  } else if (!window.AppAuth.hasPermission('workforce.jobs.create')) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  function showMessage(text, type = 'danger') {
    if (!messageBox) return;
    messageBox.className = `alert alert-${type}`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  function setLoading(isLoading) {
    if (submitBtn) {
      submitBtn.disabled = isLoading;
      submitBtn.textContent = isLoading ? 'جاري الحفظ...': (isView ? 'تحديث': SiteI18n.ta('نشر فرصة العمل'));
    }
  }

  async function loadJob() {
    const res = await window.APP_API.get(window.APP_ROUTES.jobPostingShow(jobId));
    const job = res.data;
    if (pageTitle) pageTitle.textContent = job.title;
    document.getElementById('organizationName').value = job.organization_name || '';
    document.getElementById('jobTitle').value = job.title || '';
    document.getElementById('jobCity').value = job.city || '';
    document.getElementById('employmentType').value = job.employment_type || 'full_time';
    document.getElementById('jobSector').value = job.sector || '';
    document.getElementById('jobDescription').value = job.description || '';
    document.getElementById('jobSkills').value = job.skills || '';
    if (submitBtn) submitBtn.classList.add('d-none');
    form?.querySelectorAll('input, select, textarea').forEach((el) => { el.disabled = true; });
  }

  if (isView) {
    try {
      await loadJob();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('تعذر تحميل تفاصيل الوظيفة.'));
    }
    return;
  }

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    setLoading(true);

    const payload = {
      organization_name: document.getElementById('organizationName')?.value?.trim(),
      title: document.getElementById('jobTitle')?.value?.trim(),
      city: document.getElementById('jobCity')?.value?.trim(),
      employment_type: document.getElementById('employmentType')?.value,
      sector: document.getElementById('jobSector')?.value?.trim(),
      description: document.getElementById('jobDescription')?.value?.trim(),
      skills: document.getElementById('jobSkills')?.value?.trim(),
      status: 'published',
    };

    try {
      await window.APP_API.post(window.APP_ROUTES.jobPostingStore(), payload);
      showMessage(SiteI18n.ta('تم نشر فرصة العمل بنجاح.'), 'success');
      form.reset();
      setTimeout(() => { window.location.href = 'jobs-list.php'; }, 1200);
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('تعذر نشر فرصة العمل.'));
    } finally {
      setLoading(false);
    }
  });
});
