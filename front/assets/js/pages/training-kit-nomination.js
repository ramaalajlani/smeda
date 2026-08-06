document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.NOMINATE_TRAINING_KITS,
  });
  if (!ok) return;

  const loadingBox = document.getElementById('kitNominationLoadingBox');
  const contentWrap = document.getElementById('kitNominationContentWrap');
  const messageBox = document.getElementById('kitNominationMessage');

  const form = document.getElementById('trainingKitNominationForm');
  const submitBtn = document.getElementById('submitNominationBtn');
  const resetBtn = document.getElementById('resetNominationBtn');

  const trainingKitIdInput = document.getElementById('trainingKitId');
  const proposedNameInput = document.getElementById('proposedName');
  const descriptionInput = document.getElementById('description');
  const sectorInput = document.getElementById('sector');
  const categoryInput = document.getElementById('category');
  const hoursInput = document.getElementById('hours');

  function safe(value, fallback = '—') {
    return window.APP_HELPERS.safe(value, fallback);
  }

  function escapeHtml(value) {
    return window.APP_HELPERS.e(value);
  }

  function showMessage(text, type = 'success') {
    if (!messageBox) return;

    messageBox.className = `kit-nomination-message ${type}`;
    messageBox.textContent = text;
  }

  function hideMessage() {
    if (!messageBox) return;

    messageBox.className = 'kit-nomination-message';
    messageBox.textContent = '';
  }

  function setLoadingState(isLoading) {
    if (!submitBtn) return;

    submitBtn.disabled = isLoading;
    submitBtn.textContent = isLoading ? 'جارٍ الإرسال...': SiteI18n.ta('إرسال الترشيح');
  }

  function fillTrainingKitsOptions(rows) {
    if (!trainingKitIdInput) return;

    const currentValue = trainingKitIdInput.value || 'SiteI18n.ta(';

    let html = `<option value="SiteI18n.ta(">— لا يوجد / حقيبة جديدة —</option>`;

    rows.forEach((kit) => {
      html += `
        <option value=")${kit.id}">
          ${escapeHtml(safe(kit.name))} — ${escapeHtml(safe(kit.code))}
        </option>
      `;
    });

    trainingKitIdInput.innerHTML = html;
    trainingKitIdInput.value = currentValue;
  }

  async function loadTrainingKits() {
    try {
      const response = await window.APP_API.get(
        window.APP_ROUTES.trainingKits({ per_page: 100 })
      );

      const rows = response?.data || [];
      fillTrainingKitsOptions(rows);
    } catch (error) {
      console.error('Training kits load error:', error);
      fillTrainingKitsOptions([]);
    }
  }

  function resetForm() {
    if (form) form.reset();
    if (trainingKitIdInput) trainingKitIdInput.value = '';
  }

  function validatePayload(payload) {
    if (!payload.training_kit_id && !payload.proposed_name) {
      return SiteI18n.ta('يجب اختيار حقيبة موجودة أو كتابة اسم حقيبة مقترحة.');
    }

    if (!payload.description) {
      return SiteI18n.ta('يرجى إدخال وصف الحقيبة التدريبية.');
    }

    if (!payload.sector) {
      return SiteI18n.ta('يرجى إدخال القطاع.');
    }

    if (!payload.category) {
      return SiteI18n.ta('يرجى إدخال التصنيف.');
    }

    if (!payload.hours || Number(payload.hours) < 1) {
      return SiteI18n.ta('يرجى إدخال عدد ساعات صحيح.');
    }

    return null;
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideMessage();

    const payload = {
      training_kit_id: trainingKitIdInput?.value ? Number(trainingKitIdInput.value) : null,
      proposed_name: proposedNameInput?.value?.trim() || null,
      description: descriptionInput?.value?.trim() || null,
      sector: sectorInput?.value?.trim() || null,
      category: categoryInput?.value?.trim() || null,
      hours: hoursInput?.value ? Number(hoursInput.value) : null,
    };

    const validationMessage = validatePayload(payload);
    if (validationMessage) {
      showMessage(validationMessage, 'error');
      return;
    }

    setLoadingState(true);

    try {
      const response = await window.APP_API.post(
        window.APP_ROUTES.trainingKitNominationStore(),
        payload
      );

      showMessage(response?.message || SiteI18n.ta('تم إرسال ترشيح الحقيبة بنجاح.'), 'success');
      resetForm();
    } catch (error) {
      console.error('Training kit nomination error:', error);
      showMessage(
        error?.data?.message || SiteI18n.ta('تعذر إرسال ترشيح الحقيبة.'),
        'error'
      );
    } finally {
      setLoadingState(false);
    }
  });

  resetBtn?.addEventListener('click', () => {
    hideMessage();
    resetForm();
  });

  try {
    await loadTrainingKits();

    if (loadingBox) {
      window.APP_UI.hideLoadingState(loadingBox);
    }

    if (contentWrap) {
      contentWrap.classList.remove('d-none');
    }
  } catch (error) {
    console.error('Nomination page init error:', error);

    if (contentWrap) {
      contentWrap.classList.add('d-none');
    }

    if (loadingBox) {
      loadingBox.classList.remove('d-none');
      loadingBox.innerHTML = `
        <div class="text-danger fw-bold mb-2">تعذر تجهيز نموذج الترشيح</div>
        <div class="text-muted">${escapeHtml(error?.data?.message || ')حدث خطأ أثناء تحميل الصفحة.')}</div>
      `;
    }
  }
});