document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.CREATE_TRAINEE_REGISTRATION_REQUESTS,
  });
  if (!ok) return;

  const form = document.getElementById('traineeRegistrationForm');
  const messageBox = document.getElementById('traineeRegistrationMessage');
  const submitBtn = document.getElementById('submitTraineeRegistrationBtn');

  function showMessage(text, type = 'success') {
    messageBox.className = `registration-message ${type}`;
    messageBox.textContent = text;
  }

  function setLoading(isLoading) {
    submitBtn.disabled = isLoading;
    submitBtn.textContent = isLoading ? 'جارٍ الإرسال...': SiteI18n.ta('إرسال الطلب');
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    setLoading(true);

    const payload = {
      full_name: document.getElementById('fullName')?.value?.trim() || null,
      national_id: document.getElementById('nationalId')?.value?.trim() || null,
      phone: document.getElementById('phone')?.value?.trim() || null,
      email: document.getElementById('email')?.value?.trim() || null,
      city: document.getElementById('city')?.value?.trim() || null,
      education_level: document.getElementById('educationLevel')?.value?.trim() || null,
      registration_mode: document.getElementById('registrationMode')?.value || 'self',
    };

    try {
      const response = await window.APP_API.post(
        window.APP_ROUTES.traineeRegistrationRequestStore(),
        payload
      );
      showMessage(response?.message || SiteI18n.ta('تم إرسال طلب تسجيل المتدرب بنجاح.'), 'success');
      form.reset();
    } catch (error) {
      console.error(error);
      showMessage(error?.data?.message || SiteI18n.ta('تعذر إرسال طلب تسجيل المتدرب.'), 'error');
    } finally {
      setLoading(false);
    }
  });
});