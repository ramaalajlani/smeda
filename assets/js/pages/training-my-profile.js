document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyPermissions: [
      window.AppPermissions.VIEW_TRAINER_PROFILES,
      window.AppPermissions.EDIT_OWN_TRAINER_PROFILE,
    ],
  });
  if (!ok) return;

  const loadingBox = document.getElementById('profileLoadingBox');
  const contentWrap = document.getElementById('profileContentWrap');
  const messageBox = document.getElementById('profileMessage');

  const summaryGrid = document.getElementById('trainerSummaryGrid');

  const form = document.getElementById('myTrainerProfileForm');
  const saveBtn = document.getElementById('saveProfileBtn');

  const headlineInput = document.getElementById('headline');
  const experienceYearsInput = document.getElementById('experienceYears');
  const bioInput = document.getElementById('bio');
  const skillsInput = document.getElementById('skills');
  const specialInterestsInput = document.getElementById('specialInterests');
  const linkedinSummaryInput = document.getElementById('linkedinSummary');
  const visibilityInput = document.getElementById('visibility');

  function safe(value, fallback = '—') {
    return window.APP_HELPERS.safe(value, fallback);
  }

  function escapeHtml(value) {
    return window.APP_HELPERS.e(value);
  }

  function showMessage(text, type = 'success') {
    if (!messageBox) return;

    messageBox.className = `profile-message ${type}`;
    messageBox.textContent = text;
  }

  function hideMessage() {
    if (!messageBox) return;

    messageBox.className = 'profile-message';
    messageBox.textContent = '';
  }

  function setSaveLoadingState(isLoading) {
    if (!saveBtn) return;

    saveBtn.disabled = isLoading;
    saveBtn.textContent = isLoading ? 'جاري الحفظ...': SiteI18n.ta('حفظ الملف المهني');
  }

  function canEditProfile() {
    return window.AppAuth.hasPermission(
      window.AppPermissions.EDIT_OWN_TRAINER_PROFILE
    );
  }

  function infoItem(label, value) {
    return `
      <div class="profile-summary-item">
        <small>${escapeHtml(label)}</small>
        <strong>${escapeHtml(safe(value))}</strong>
      </div>
    `;
  }

  function renderSummary(profile) {
    if (!summaryGrid) return;

    const trainer = profile?.trainer || {};
    const trainingCenter = trainer?.training_center || {};

    const eligibilityLabel = trainer?.can_actually_train
      ? 'مؤهل للتدريب': SiteI18n.ta('غير مؤهل للتدريب حالياً');

    summaryGrid.innerHTML = [
      infoItem(SiteI18n.ta('اسم المدرب'), trainer?.name),
      infoItem(SiteI18n.ta('رقم المدرب'), trainer?.trainer_code),
      infoItem(SiteI18n.ta('البريد الإلكتروني'), trainer?.email),
      infoItem(SiteI18n.ta('رقم الهاتف'), trainer?.phone),
      infoItem(SiteI18n.ta('التخصص'), trainer?.specialization),
      infoItem(SiteI18n.ta('التصنيف'), trainer?.classification),
      infoItem(SiteI18n.ta('الحالة'), trainer?.status),
      infoItem(SiteI18n.ta('الحالة المهنية'), eligibilityLabel),
      infoItem(SiteI18n.ta('المركز التدريبي'), trainingCenter?.name),
      infoItem(SiteI18n.ta('رمز المركز'), trainingCenter?.code),
      infoItem(SiteI18n.ta('المدينة'), trainingCenter?.city),
      infoItem(SiteI18n.ta('صلاحية ToT'), trainer?.is_tot_valid ? 'صالحة': SiteI18n.ta('غير صالحة')),
    ].join('');
  }

  function fillForm(profile) {
    if (headlineInput) headlineInput.value = profile?.headline || '';
    if (experienceYearsInput) {
      experienceYearsInput.value =
        profile?.experience_years !== null && profile?.experience_years !== undefined
          ? profile.experience_years
          : '';
    }
    if (bioInput) bioInput.value = profile?.bio || '';
    if (skillsInput) skillsInput.value = profile?.skills || '';
    if (specialInterestsInput) {
      specialInterestsInput.value = profile?.special_interests || '';
    }
    if (linkedinSummaryInput) {
      linkedinSummaryInput.value = profile?.linkedin_summary || '';
    }
    if (visibilityInput) {
      visibilityInput.value = profile?.visibility || 'internal';
    }
  }

  function setFormEditableState(editable) {
    [
      headlineInput,
      experienceYearsInput,
      bioInput,
      skillsInput,
      specialInterestsInput,
      linkedinSummaryInput,
      visibilityInput,
    ].forEach((input) => {
      if (!input) return;
      input.disabled = !editable;
    });

    if (saveBtn) {
      saveBtn.classList.toggle('d-none', !editable);
    }
  }

  async function loadProfile() {
    hideMessage();

    try {
      const response = await window.APP_API.get(
        window.APP_ROUTES.myTrainerProfile()
      );

      const profile = response?.data || null;

      if (!profile) {
        throw new Error('Profile data is empty.');
      }

      renderSummary(profile);
      fillForm(profile);
      setFormEditableState(canEditProfile());

      window.APP_UI.hideLoadingState(loadingBox);
      if (contentWrap) contentWrap.classList.remove('d-none');
    } catch (error) {
      console.error('Trainer profile load error:', error);

      if (contentWrap) contentWrap.classList.add('d-none');

      if (loadingBox) {
        loadingBox.classList.remove('d-none');
        loadingBox.innerHTML = `
          <div class="text-danger fw-bold mb-2">تعذر تحميل الملف المهني</div>
          <div class="text-muted">${escapeHtml(error?.data?.message || error?.message || ')حدث خطأ أثناء تحميل البيانات.')}</div>
        `;
      }
    }
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!canEditProfile()) {
      showMessage(SiteI18n.ta('ليس لديك صلاحية تعديل الملف المهني.'), 'error');
      return;
    }

    hideMessage();
    setSaveLoadingState(true);

    try {
      const payload = {
        headline: headlineInput?.value?.trim() || null,
        experience_years:
          experienceYearsInput?.value !== ''
            ? Number(experienceYearsInput.value)
            : null,
        bio: bioInput?.value?.trim() || null,
        skills: skillsInput?.value?.trim() || null,
        special_interests: specialInterestsInput?.value?.trim() || null,
        linkedin_summary: linkedinSummaryInput?.value?.trim() || null,
        visibility: visibilityInput?.value || 'internal',
      };

      const response = await window.APP_API.post(
        window.APP_ROUTES.myTrainerProfileUpdate(),
        payload
      );

      const updatedProfile = response?.data || null;

      if (updatedProfile) {
        renderSummary(updatedProfile);
        fillForm(updatedProfile);
      }

      showMessage(
        response?.message || SiteI18n.ta('تم تحديث الملف المهني بنجاح.'),
        'success'
      );
    } catch (error) {
      console.error('Trainer profile save error:', error);
      showMessage(
        error?.data?.message || SiteI18n.ta('حدث خطأ أثناء حفظ الملف المهني.'),
        'error'
      );
    } finally {
      setSaveLoadingState(false);
    }
  });

  await loadProfile();
});