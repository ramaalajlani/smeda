document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.CREATE_TRAINER_REGISTRATION_REQUESTS,
  });
  if (!ok) return;

  const form = document.getElementById('trainerRegistrationForm');
  const messageBox = document.getElementById('trainerRegistrationMessage');
  const submitBtn = document.getElementById('submitTrainerRegistrationBtn');
  const trainingCenterId = document.getElementById('trainingCenterId');

  function showMessage(text, type = 'success') {
    messageBox.className = `registration-message ${type}`;
    messageBox.textContent = text;
  }

  function setLoading(isLoading) {
    submitBtn.disabled = isLoading;
    submitBtn.textContent = isLoading ? 'جارٍ الإرسال...': SiteI18n.ta('إرسال الطلب');
  }

  async function loadCenters() {
    try {
      const result = await window.APP_API.get(window.APP_ROUTES.trainingCenters({ per_page: 100 }));
      const rows = result?.data || [];
      trainingCenterId.innerHTML =
        '<option value="">— بدون مركز —</option>' +
        rows.map(row => `<option value="${row.id}">${window.APP_HELPERS.e(row.name)}</option>`).join('');
    } catch (error) {
      console.error(error);
    }
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    setLoading(true);

    const payload = {
      training_center_id: trainingCenterId?.value ? Number(trainingCenterId.value) : null,
      full_name: document.getElementById('fullName')?.value?.trim() || null,
      national_id: document.getElementById('nationalId')?.value?.trim() || null,
      phone: document.getElementById('phone')?.value?.trim() || null,
      email: document.getElementById('email')?.value?.trim() || null,
      specialization: document.getElementById('specialization')?.value?.trim() || null,
      classification_requested: document.getElementById('classificationRequested')?.value?.trim() || null,
      has_tot: document.getElementById('hasTot')?.value === '1',
      tot_certificate_number: document.getElementById('totCertificateNumber')?.value?.trim() || null,
      tot_certificate_source: document.getElementById('totCertificateSource')?.value?.trim() || null,
    };

    try {
      const response = await window.APP_API.post(
        window.APP_ROUTES.trainerRegistrationRequestStore(),
        payload
      );
      showMessage(response?.message || SiteI18n.ta('تم إرسال طلب تسجيل المدرب بنجاح.'), 'success');
      form.reset();
    } catch (error) {
      console.error(error);
      showMessage(error?.data?.message || SiteI18n.ta('تعذر إرسال طلب تسجيل المدرب.'), 'error');
    } finally {
      setLoading(false);
    }
  });

  await loadCenters();
});