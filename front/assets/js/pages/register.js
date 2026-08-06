document.addEventListener('DOMContentLoaded', async () => {
  const form = document.getElementById('registerForm');
  const messageBox = document.getElementById('registerMessage');
  const submitBtn = document.getElementById('registerSubmitBtn');
  const retryDetailBtn = document.getElementById('retryDetailBtn');
  const reviewSummary = document.getElementById('reviewSummary');
  const progressLabel = document.getElementById('wizardProgressLabel');
  const accountTypeSelect = document.getElementById('registerAccountType');

  const ACCOUNT_TYPE_LABELS = window.__REGISTER_LABELS || {
    trainer: 'مدرب',
    trainee: 'متدرب',
    center: 'مركز تدريبي',
    project_owner: 'صاحب مشروع / رائد أعمال',
    consultant: 'مكتب استشاري',
    jobseeker: 'باحث عن عمل',
  };

  const POST_REGISTER_REDIRECTS = window.__REGISTER_REDIRECTS || {
    project_owner: 'services/finance/finance-apply.php',
    consultant: 'services/consulting/consulting-office-create.php',
    jobseeker: 'services/workforce/job-request.php',
  };

  const SKIP_DETAIL_TYPES = new Set(window.__REGISTER_SKIP_DETAIL || ['project_owner', 'consultant', 'jobseeker']);

  const state = {
    currentStep: 1,
    accountCreated: false,
    redirectToForm: null,
    map: null,
    marker: null,
    leafletLoaded: false,
    mapInitialized: false,
  };

  function getValue(id) {
    return document.getElementById(id)?.value?.trim() || '';
  }

  // البريد الإلكتروني للحساب يكون دائماً على نطاق الهيئة الرسمي.
  const GOV_EMAIL_DOMAIN = 'smedc.gov.sy';
  function normalizeGovEmail(raw) {
    const v = String(raw || '').trim().toLowerCase();
    if (!v) return '';
    // نأخذ الجزء قبل @ فقط ونفرض النطاق الرسمي، مع تنقية الأحرف غير المسموحة.
    const local = v.split('@')[0].replace(/[^a-z0-9._-]/g, '');
    if (!local) return '';
    return `${local}@${GOV_EMAIL_DOMAIN}`;
  }

  function getAccountType() {
    return accountTypeSelect?.value || '';
  }

  function showMessage(text, type = 'error') {
    if (!messageBox) return;
    messageBox.className = `register-message ${type}`;
    messageBox.textContent = text;
  }

  function hideMessage() {
    if (!messageBox) return;
    messageBox.className = 'register-message';
    messageBox.textContent = '';
  }

  function clearFieldErrors(root = form) {
    root?.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    root?.querySelectorAll('.invalid-feedback').forEach((el) => {
      if (el.id !== 'cMapError') el.textContent = '';
    });
    const mapError = document.getElementById('cMapError');
    if (mapError) mapError.textContent = '';
  }

  function setFieldError(id, message) {
    const input = document.getElementById(id);
    if (!input) return false;
    input.classList.add('is-invalid');
    const feedback = input.parentElement?.querySelector('.invalid-feedback');
    if (feedback) feedback.textContent = message;
    return true;
  }

  function setMapError(message) {
    const mapError = document.getElementById('cMapError');
    if (mapError) mapError.textContent = message;
  }

  function focusFirstInvalidField(root = document) {
    const firstInvalid = root.querySelector('.is-invalid');
    if (!firstInvalid) return;
    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    firstInvalid.focus?.();
  }

  function setLoading(isLoading, label) {
    if (submitBtn) {
      submitBtn.disabled = isLoading;
      if (!state.accountCreated) {
        submitBtn.textContent = isLoading ? 'جارٍ إنشاء الحساب...': SiteI18n.ta('إنشاء الحساب');
      }
    }
    if (retryDetailBtn) retryDetailBtn.disabled = isLoading;
    if (isLoading && label) showMessage(label, 'success');
  }

  function getActiveStep2Panel() {
    const type = getAccountType();
    return document.querySelector(`.wizard-panel[data-step="2"][data-type-panel="${type}"]`);
  }

  function showStep(step) {
    state.currentStep = step;

    document.querySelectorAll('.wizard-panel').forEach((panel) => {
      const panelStep = Number(panel.dataset.step);
      const typePanel = panel.dataset.typePanel;
      let visible = false;

      if (panelStep === step) {
        if (step === 2) {
          visible = typePanel === getAccountType();
        } else {
          visible = !typePanel;
        }
      }

      panel.classList.toggle('active', visible);
    });

    if (progressLabel) progressLabel.textContent = `الخطوة ${step} من 3`;

    document.querySelectorAll('[data-step-dot]').forEach((dot) => {
      const n = Number(dot.dataset.stepDot);
      dot.classList.toggle('active', n === step);
      dot.classList.toggle('done', n < step);
    });

    document.querySelectorAll('[data-step-line]').forEach((line) => {
      const n = Number(line.dataset.stepLine);
      line.classList.toggle('done', n < step);
    });

    document.querySelectorAll('[data-step-name]').forEach((nameEl) => {
      const n = Number(nameEl.dataset.stepName);
      nameEl.classList.toggle('active', n === step);
    });

    if (step === 2 && getAccountType() === 'center') {
      initCenterMapWhenVisible();
    }

    if (step === 3) {
      renderReviewSummary();
    }

    hideMessage();
  }

  function prefillStep2FromStep1() {
    const name = getValue('registerName');
    const email = getValue('registerEmail');
    const type = getAccountType();

    if (type === 'trainee') {
      const fullName = document.getElementById('tFullName');
      const tEmail = document.getElementById('tEmail');
      if (fullName && !fullName.value.trim()) fullName.value = name;
      if (tEmail && !tEmail.value.trim()) tEmail.value = email;
    }

    if (type === 'trainer') {
      const fullName = document.getElementById('trFullName');
      const trEmail = document.getElementById('trEmail');
      if (fullName && !fullName.value.trim()) fullName.value = name;
      if (trEmail && !trEmail.value.trim()) trEmail.value = email;
    }

    if (type === 'center') {
      const cEmail = document.getElementById('cCenterEmail');
      if (cEmail && !cEmail.value.trim()) cEmail.value = email;
    }
  }

  function validateStep1() {
    clearFieldErrors(form.querySelector('[data-step="1"]'));
    let valid = true;

    const name = getValue('registerName');
    // افرض نطاق الهيئة الرسمي على البريد وحدّث الحقل ليستخدمه كل ما يلي (الحمولة/التعبئة).
    const emailField = document.getElementById('registerEmail');
    const email = normalizeGovEmail(getValue('registerEmail'));
    if (emailField && email) emailField.value = email;
    const password = document.getElementById('registerPassword')?.value || '';
    const passwordConfirmation = document.getElementById('registerPasswordConfirmation')?.value || '';
    const accountType = getAccountType();

    if (!name) {
      setFieldError('registerName', SiteI18n.ta('يرجى إدخال الاسم الكامل.'));
      valid = false;
    }

    if (!email) {
      setFieldError('registerEmail', SiteI18n.ta('يرجى إدخال البريد الإلكتروني.'));
      valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setFieldError('registerEmail', SiteI18n.ta('يرجى إدخال بريد إلكتروني صالح.'));
      valid = false;
    }

    if (!password) {
      setFieldError('registerPassword', SiteI18n.ta('يرجى إدخال كلمة المرور.'));
      valid = false;
    } else if (password.length < 8) {
      setFieldError('registerPassword', SiteI18n.ta('يجب أن تكون كلمة المرور 8 أحرف على الأقل.'));
      valid = false;
    }

    if (!passwordConfirmation) {
      setFieldError('registerPasswordConfirmation', SiteI18n.ta('يرجى تأكيد كلمة المرور.'));
      valid = false;
    } else if (password !== passwordConfirmation) {
      setFieldError('registerPasswordConfirmation', SiteI18n.ta('كلمتا المرور غير متطابقتين.'));
      valid = false;
    }

    if (!accountType) {
      setFieldError('registerAccountType', SiteI18n.ta('يرجى اختيار نوع الحساب.'));
      valid = false;
    }

    return valid;
  }

  function validateStep2() {
    const panel = getActiveStep2Panel();
    clearFieldErrors(panel);
    const type = getAccountType();
    let valid = true;
    const missingLabels = [];

    if (type === 'trainee') {
      if (!getValue('tFullName')) {
        setFieldError('tFullName', SiteI18n.ta('يرجى إدخال الاسم الكامل.'));
        valid = false;
      }
    }

    if (type === 'trainer') {
      if (!getValue('trFullName')) {
        setFieldError('trFullName', SiteI18n.ta('يرجى إدخال الاسم الكامل.'));
        valid = false;
      }
    }

    if (type === 'center') {
      const required = [
        ['cCenterName', SiteI18n.ta('اسم المركز')],
        ['cCenterCity', SiteI18n.ta('المدينة')],
        ['cCenterAddress', SiteI18n.ta('العنوان')],
        ['cCenterPhone', SiteI18n.ta('الهاتف')],
        ['cLicenseNumber', SiteI18n.ta('رقم الترخيص')],
      ];

      required.forEach(([id, label]) => {
        if (!getValue(id)) {
          setFieldError(id, `${SiteI18n.ta('هذا الحقل مطلوب')}.`);
          missingLabels.push(label);
          valid = false;
        }
      });

      if (!getValue('cCenterLatitude') || !getValue('cCenterLongitude')) {
        // Fallback to current map center if map is loaded but user did not click.
        if (state.map?.getCenter) {
          const center = state.map.getCenter();
          if (center?.lat && center?.lng) {
            updateCenterCoords(center.lat, center.lng);
          }
        } else {
          // Safety net if map was not ready yet.
          updateCenterCoords(35.9306, 36.6339);
        }
      }

      if (!getValue('cCenterLatitude') || !getValue('cCenterLongitude')) {
        setMapError(SiteI18n.ta('يرجى تحديد موقع المركز على الخريطة.'));
        missingLabels.push(SiteI18n.ta('موقع المركز على الخريطة'));
        valid = false;
      }
    }

    if (!valid) {
      if (missingLabels.length) {
        showMessage(
          `${SiteI18n.ta('الحقول المطلوبة غير مكتملة')}: ${missingLabels.join(' - ')}`,
          'error'
        );
      } else {
        showMessage(SiteI18n.ta('يرجى تعبئة الحقول المطلوبة قبل الانتقال للخطوة التالية.'), 'error');
      }
      focusFirstInvalidField(panel || form);
    }

    return valid;
  }

  function buildAccountPayload() {
    const payload = {
      name: getValue('registerName'),
      email: getValue('registerEmail'),
      password: document.getElementById('registerPassword')?.value || '',
      password_confirmation: document.getElementById('registerPasswordConfirmation')?.value || '',
      account_type: getAccountType(),
      device_name: 'front-web',
    };

    return payload;
  }

  function buildTraineePayload() {
    return {
      full_name: getValue('tFullName'),
      national_id: getValue('tNationalId') || null,
      phone: getValue('tPhone') || null,
      email: getValue('tEmail') || null,
      city: getValue('tCity') || null,
      education_level: getValue('tEducationLevel') || null,
      registration_mode: document.getElementById('tRegistrationMode')?.value || 'self',
    };
  }

  function buildTrainerPayload() {
    const centerId = document.getElementById('trTrainingCenterId')?.value;
    return {
      training_center_id: centerId ? Number(centerId) : null,
      full_name: getValue('trFullName'),
      national_id: getValue('trNationalId') || null,
      phone: getValue('trPhone') || null,
      email: getValue('trEmail') || null,
      specialization: getValue('trSpecialization') || null,
      classification_requested: getValue('trClassificationRequested') || null,
      has_tot: document.getElementById('trHasTot')?.value === '1',
      tot_certificate_number: getValue('trTotCertificateNumber') || null,
      tot_certificate_source: getValue('trTotCertificateSource') || null,
    };
  }

  function buildCenterFormData() {
    const formData = new FormData();
    formData.append('center_name', getValue('cCenterName'));
    formData.append('city', getValue('cCenterCity'));
    formData.append('address', getValue('cCenterAddress'));
    formData.append('phone', getValue('cCenterPhone'));
    formData.append('email', getValue('cCenterEmail'));
    formData.append('classification_requested', getValue('cCenterClassification'));
    formData.append('supports_offline_training', document.getElementById('cSupportsOfflineTraining')?.value || '0');
    formData.append('supports_online_training', document.getElementById('cSupportsOnlineTraining')?.value || '0');
    formData.append('latitude', getValue('cCenterLatitude'));
    formData.append('longitude', getValue('cCenterLongitude'));
    formData.append('license_number', getValue('cLicenseNumber'));
    formData.append('license_issue_date', document.getElementById('cLicenseIssueDate')?.value || '');
    formData.append('license_issued_by', getValue('cLicenseIssuedBy'));

    const licenseFile = document.getElementById('cLicenseImage')?.files?.[0];
    if (licenseFile) formData.append('license_image', licenseFile);

    return formData;
  }

  async function submitDetailRequest() {
    const type = getAccountType();

    if (SKIP_DETAIL_TYPES.has(type)) {
      return { skipped: true };
    }

    if (type === 'trainee') {
      return window.APP_API.post(
        window.APP_ROUTES.traineeRegistrationRequestStore(),
        buildTraineePayload()
      );
    }

    if (type === 'trainer') {
      return window.APP_API.post(
        window.APP_ROUTES.trainerRegistrationRequestStore(),
        buildTrainerPayload()
      );
    }

    if (type === 'center') {
      return window.APP_API.post(
        window.APP_ROUTES.centerRegistrationRequestStore(),
        buildCenterFormData()
      );
    }

    throw new Error(SiteI18n.ta('نوع الحساب غير مدعوم.'));
  }

  function redirectAfterSuccess(detailResponse = null) {
    const type = getAccountType();
    const requestNumber = detailResponse?.data?.request_number;
    if (type === 'trainer') {
      const pendingMsg = requestNumber
        ? `تم إنشاء الحساب وإرسال طلب المدرب رقم ${requestNumber}. سيظهر الحساب ضمن قائمة المدربين بعد الاعتماد الإداري فقط.`
        : 'تم إنشاء الحساب وإرسال طلب المدرب. سيظهر الحساب ضمن قائمة المدربين بعد الاعتماد الإداري فقط.';
      showMessage(pendingMsg, 'success');
    } else {
      showMessage('تم إنشاء الحساب وحفظ البيانات بنجاح، جارٍ تحويلك...', 'success');
    }

    const base = (window.APP_CONFIG?.FRONTEND_BASE_URL || '').replace(/\/$/, '');
    const rel = POST_REGISTER_REDIRECTS[type];
    const target = rel ? `${base}/${rel}` : window.AppAuth.resolveHomePage();
    window.setTimeout(() => {
      window.location.href = target;
    }, 800);
  }

  function showPartialFailure(error) {
    state.accountCreated = true;
    if (submitBtn) submitBtn.style.display = 'none';
    if (retryDetailBtn) retryDetailBtn.classList.add('show');
    showMessage(
      error?.data?.message ||
        SiteI18n.ta('تم إنشاء الحساب، لكن لم تكتمل البيانات التفصيلية. يرجى المحاولة مرة أخرى.'),
      'warning'
    );
  }

  async function loadTrainerCentersIfPossible() {
    if (!window.AppAuth.isLoggedIn()) return;

    const select = document.getElementById('trTrainingCenterId');
    if (!select) return;

    try {
      const result = await window.APP_API.get(window.APP_ROUTES.trainingCenters({ per_page: 100 }));
      const rows = result?.data || [];
      const current = select.value;
      select.innerHTML =
        '<option value="">— بدون مركز —</option>' +
        rows.map((row) => `<option value="${row.id}">${row.name}</option>`).join('');
      if (current) select.value = current;
    } catch (error) {
      console.warn('Could not load training centers:', error);
    }
  }

  async function createAccountIfNeeded(payload) {
    if (state.accountCreated) return null;

    const response = await window.APP_API.post(window.APP_ROUTES.register(), payload);

    if (response?.token) {
      window.AppAuth.setSession(response);
      await window.AppAuth.fetchMe();
      state.accountCreated = true;
      state.redirectToForm = response.redirect_to_form || getAccountType();
      await loadTrainerCentersIfPossible();
    }

    return response;
  }

  async function handleFinalSubmit() {
    hideMessage();

    if (!validateStep1() || !validateStep2()) {
      showMessage(SiteI18n.ta('يرجى مراجعة الحقول الناقصة في الخطوات السابقة.'), 'error');
      return;
    }

    setLoading(true);

    try {
      await createAccountIfNeeded(buildAccountPayload());
      const detailResponse = await submitDetailRequest();
      redirectAfterSuccess(detailResponse);
    } catch (error) {
      console.error('Registration wizard error:', error);

      if (error?.isValidation) {
        showMessage(error.data.message, 'error');
        return;
      }

      if (state.accountCreated) {
        showPartialFailure(error);
      } else {
        showMessage(error?.data?.message || SiteI18n.ta('تعذر إنشاء الحساب حالياً.'), 'error');
      }
    } finally {
      setLoading(false);
    }
  }

  async function retryDetailSave() {
    hideMessage();
    setLoading(true, SiteI18n.ta('جارٍ إعادة حفظ البيانات التفصيلية...'));

    try {
      if (!window.AppAuth.isLoggedIn()) {
        throw { data: { message: SiteI18n.ta('انتهت الجلسة. يرجى تسجيل الدخول ثم إكمال الطلب من صفحة التسجيل.') } };
      }
      const detailResponse = await submitDetailRequest();
      redirectAfterSuccess(detailResponse);
    } catch (error) {
      console.error('Retry detail save error:', error);
      showPartialFailure(error);
    } finally {
      setLoading(false);
    }
  }

  function reviewRow(label, value) {
    const display = value || '—';
    return `<div class="review-row"><span class="label">${label}</span><span class="value">${display}</span></div>`;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderReviewSummary() {
    if (!reviewSummary) return;

    const type = getAccountType();
    let detailHtml = '';

    if (type === 'trainee') {
      const traineeCityText = document.getElementById('tCity')?.selectedOptions?.[0]?.textContent?.trim() || '—';
      const traineeEducationText = document.getElementById('tEducationLevel')?.selectedOptions?.[0]?.textContent?.trim() || '—';
      detailHtml = `
        ${reviewRow(SiteI18n.ta('الاسم الكامل'), escapeHtml(getValue('tFullName')))}
        ${reviewRow(SiteI18n.ta('الرقم الوطني'), escapeHtml(getValue('tNationalId')))}
        ${reviewRow(SiteI18n.ta('الهاتف'), escapeHtml(getValue('tPhone')))}
        ${reviewRow(SiteI18n.ta('البريد'), escapeHtml(getValue('tEmail')))}
        ${reviewRow(SiteI18n.ta('المدينة'), escapeHtml(traineeCityText === '— اختر المدينة —' ? '—' : traineeCityText))}
        ${reviewRow(SiteI18n.ta('المستوى التعليمي'), escapeHtml(traineeEducationText === '— اختر المستوى التعليمي —' ? '—' : traineeEducationText))}
        ${reviewRow(SiteI18n.ta('نمط التسجيل'), document.getElementById('tRegistrationMode')?.selectedOptions?.[0]?.textContent || '—')}
      `;
    }

    if (type === 'trainer') {
      detailHtml = `
        ${reviewRow(SiteI18n.ta('المركز التدريبي'), document.getElementById('trTrainingCenterId')?.selectedOptions?.[0]?.textContent?.trim() || SiteI18n.ta('— بدون مركز —'))}
        ${reviewRow(SiteI18n.ta('الاسم الكامل'), escapeHtml(getValue('trFullName')))}
        ${reviewRow(SiteI18n.ta('الرقم الوطني'), escapeHtml(getValue('trNationalId')))}
        ${reviewRow(SiteI18n.ta('الهاتف'), escapeHtml(getValue('trPhone')))}
        ${reviewRow(SiteI18n.ta('البريد'), escapeHtml(getValue('trEmail')))}
        ${reviewRow(SiteI18n.ta('التخصص'), escapeHtml(getValue('trSpecialization')))}
        ${reviewRow(SiteI18n.ta('التصنيف المطلوب'), escapeHtml(getValue('trClassificationRequested')))}
        ${reviewRow(SiteI18n.ta('يحمل ToT'), document.getElementById('trHasTot')?.value === '1' ? 'نعم': SiteI18n.ta('لا'))}
      `;
    }

    if (type === 'center') {
      const licenseFile = document.getElementById('cLicenseImage')?.files?.[0];
      detailHtml = `
        ${reviewRow(SiteI18n.ta('اسم المركز'), escapeHtml(getValue('cCenterName')))}
        ${reviewRow(SiteI18n.ta('المدينة'), escapeHtml(getValue('cCenterCity')))}
        ${reviewRow(SiteI18n.ta('العنوان'), escapeHtml(getValue('cCenterAddress')))}
        ${reviewRow(SiteI18n.ta('الهاتف'), escapeHtml(getValue('cCenterPhone')))}
        ${reviewRow(SiteI18n.ta('البريد'), escapeHtml(getValue('cCenterEmail')))}
        ${reviewRow(SiteI18n.ta('خط العرض'), escapeHtml(getValue('cCenterLatitude')))}
        ${reviewRow(SiteI18n.ta('خط الطول'), escapeHtml(getValue('cCenterLongitude')))}
        ${reviewRow(SiteI18n.ta('رقم الترخيص'), escapeHtml(getValue('cLicenseNumber')))}
        ${reviewRow(SiteI18n.ta('تاريخ الترخيص'), escapeHtml(document.getElementById('cLicenseIssueDate')?.value || ''))}
        ${reviewRow(SiteI18n.ta('صورة الترخيص'), licenseFile ? escapeHtml(licenseFile.name) : '—')}
      `;
    }

    if (SKIP_DETAIL_TYPES.has(type)) {
      detailHtml = `<p class="text-muted mb-0" style="line-height:1.8;">سيتم توجيهك بعد التسجيل لإكمال ملفك حسب نوع حسابك.</p>`;
    }

    reviewSummary.innerHTML = `
      <div class="review-block">
        <h4>بيانات الحساب</h4>
        ${reviewRow('الاسم', escapeHtml(getValue('registerName')))}
        ${reviewRow(SiteI18n.ta('البريد الإلكتروني'), escapeHtml(getValue('registerEmail')))}
        ${reviewRow(SiteI18n.ta('نوع الحساب'), ACCOUNT_TYPE_LABELS[type] || '—')}
      </div>
      <div class="review-block">
        <h4>البيانات التفصيلية</h4>
        ${detailHtml}
      </div>
    `;
  }

  function loadScript(src) {
    return new Promise((resolve, reject) => {
      const existing = document.querySelector(`script[src="${src}"]`);
      if (existing) {
        existing.addEventListener('load', () => resolve());
        if (existing.dataset.loaded === '1') resolve();
        return;
      }
      const script = document.createElement('script');
      script.src = src;
      script.onload = () => {
        script.dataset.loaded = '1';
        resolve();
      };
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  function loadStylesheet(href) {
    return new Promise((resolve, reject) => {
      if (document.querySelector(`link[href="${href}"]`)) {
        resolve();
        return;
      }
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = href;
      link.onload = () => resolve();
      link.onerror = reject;
      document.head.appendChild(link);
    });
  }

  async function ensureLeafletLoaded() {
    if (state.leafletLoaded && window.L) return;
    await loadStylesheet('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    await loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
    state.leafletLoaded = true;
  }

  function updateCenterCoords(lat, lng) {
    const latFixed = Number(lat).toFixed(6);
    const lngFixed = Number(lng).toFixed(6);
    const latInput = document.getElementById('cCenterLatitude');
    const lngInput = document.getElementById('cCenterLongitude');
    const latPreview = document.getElementById('cLatitudePreview');
    const lngPreview = document.getElementById('cLongitudePreview');

    if (latInput) latInput.value = latFixed;
    if (lngInput) lngInput.value = lngFixed;
    if (latPreview) latPreview.textContent = latFixed;
    if (lngPreview) lngPreview.textContent = lngFixed;
    setMapError('');
  }

  async function initCenterMapWhenVisible() {
    try {
      await ensureLeafletLoaded();
    } catch (error) {
      console.warn('Leaflet load failed:', error);
      setMapError(SiteI18n.ta('تعذر تحميل الخريطة. حاول تحديث الصفحة.'));
      return;
    }

    if (state.mapInitialized || !window.L) return;

    const mapElement = document.getElementById('wizardCenterMap');
    if (!mapElement) return;

    const defaultLat = 35.9306;
    const defaultLng = 36.6339;

    state.map = window.L.map('wizardCenterMap').setView([defaultLat, defaultLng], 11);
    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap',
    }).addTo(state.map);

    state.map.on('click', (e) => {
      const { lat, lng } = e.latlng;
      if (!state.marker) {
        state.marker = window.L.marker([lat, lng], { draggable: true }).addTo(state.map);
        state.marker.on('dragend', (event) => {
          const position = event.target.getLatLng();
          updateCenterCoords(position.lat, position.lng);
        });
      } else {
        state.marker.setLatLng([lat, lng]);
      }
      updateCenterCoords(lat, lng);
    });

    // Provide a default valid location to avoid blocking step progression
    // when user fills all fields but forgets to click on the map.
    state.marker = window.L.marker([defaultLat, defaultLng], { draggable: true }).addTo(state.map);
    state.marker.on('dragend', (event) => {
      const position = event.target.getLatLng();
      updateCenterCoords(position.lat, position.lng);
    });
    updateCenterCoords(defaultLat, defaultLng);

    state.mapInitialized = true;

    window.setTimeout(() => {
      state.map?.invalidateSize();
    }, 300);
  }

  function handleLicensePreview() {
    const input = document.getElementById('cLicenseImage');
    const preview = document.getElementById('cLicenseFilePreview');
    const file = input?.files?.[0];

    if (!preview) return;

    if (!file) {
      preview.classList.remove('show');
      preview.textContent = '';
      return;
    }

    preview.classList.add('show');
    preview.textContent = `الملف: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
  }

  document.getElementById('wizardNextBtn1')?.addEventListener('click', () => {
    if (!validateStep1()) return;
    prefillStep2FromStep1();
    showStep(2);
  });

  form?.querySelectorAll('[data-wizard-next="3"]').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (!validateStep2()) return;
      showStep(3);
    });
  });

  form?.querySelectorAll('[data-wizard-prev]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const target = Number(btn.dataset.wizardPrev);
      showStep(target);
    });
  });

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    if (state.currentStep !== 3) return;
    handleFinalSubmit();
  });

  retryDetailBtn?.addEventListener('click', retryDetailSave);
  document.getElementById('cLicenseImage')?.addEventListener('change', handleLicensePreview);

  // إكمال نطاق البريد الرسمي @smedc.gov.sy تلقائياً عند مغادرة الحقل.
  const registerEmailField = document.getElementById('registerEmail');
  if (registerEmailField) {
    const applyGovEmail = () => {
      const normalized = normalizeGovEmail(registerEmailField.value);
      if (normalized) registerEmailField.value = normalized;
    };
    registerEmailField.addEventListener('blur', applyGovEmail);
    registerEmailField.addEventListener('change', applyGovEmail);
  }

  if (window.__REGISTER_PRESELECT && accountTypeSelect) {
    accountTypeSelect.value = window.__REGISTER_PRESELECT;
  }

  showStep(1);
});
