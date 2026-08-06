document.addEventListener('DOMContentLoaded', async () => {
  const form = document.getElementById('kitNominationForm');
  const messageBox = document.getElementById('nominationMessage');
  const submitBtn = document.getElementById('submitNominationBtn');

  function showMessage(text, type = 'error') {
    if (!messageBox) return;
    messageBox.className = `nomination-message ${type}`;
    messageBox.classList.remove('d-none');
    messageBox.textContent = text;
  }

  function setLoading(isLoading) {
    if (!submitBtn) return;
    submitBtn.disabled = isLoading;
    submitBtn.textContent = isLoading ? 'جاري الإرسال...': SiteI18n.ta('إرسال الطلب');
  }

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
      applicant_name: document.getElementById('fullNameInput')?.value?.trim() || '',
      applicant_email: document.getElementById('emailInput')?.value?.trim() || '',
      proposed_name: document.getElementById('kitNameInput')?.value?.trim() || '',
      city: document.getElementById('cityInput')?.value?.trim() || '',
      notes: document.getElementById('notesInput')?.value?.trim() || '',
    };

    if (!payload.applicant_name || !payload.applicant_email || !payload.proposed_name) {
      showMessage(SiteI18n.ta('يرجى تعبئة الاسم والبريد واسم الحقيبة على الأقل.'), 'error');
      return;
    }

    setLoading(true);

    try {
      await window.APP_API.post(window.APP_ROUTES.trainingKitPublicRequestStore(), payload);
      showMessage(SiteI18n.ta('تم إرسال طلب ترشيح الحقيبة بنجاح. سيتم التواصل معك عبر البريد.'), 'success');
      form.reset();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('تعذر إرسال الطلب.'), 'error');
    } finally {
      setLoading(false);
    }
  });
});
