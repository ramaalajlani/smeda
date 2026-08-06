document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.CREATE_COURSE_REGISTRATION_REQUESTS,
  });
  if (!ok) return;

  const form = document.getElementById('courseRegistrationForm');
  const messageBox = document.getElementById('courseRegistrationMessage');
  const submitBtn = document.getElementById('submitCourseRegistrationBtn');
  const addMemberBtn = document.getElementById('addMemberBtn');
  const membersContainer = document.getElementById('membersContainer');
  const registrationMode = document.getElementById('registrationMode');
  const trainingCourseId = document.getElementById('trainingCourseId');

  function showMessage(text, type = 'success') {
    messageBox.className = `registration-message ${type}`;
    messageBox.textContent = text;
  }

  function setLoading(isLoading) {
    submitBtn.disabled = isLoading;
    submitBtn.textContent = isLoading ? 'جارٍ الإرسال...': SiteI18n.ta('إرسال الطلب');
  }

  function toggleGuardianFields() {
    const isGuardian = registrationMode?.value === 'guardian_with_dependents';
    document.querySelectorAll('.guardian-fields').forEach(el => {
      el.classList.toggle('d-none', !isGuardian);
    });
  }

  function memberTemplate(index) {
    return `
      <div class="member-card" data-member-index="${index}">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0">تابع #${index + 1}</h6>
          <button type="button" class="btn btn-sm btn-outline-danger remove-member-btn">حذف</button>
        </div>

        <div class="row g-3">
          <div class="col-lg-6">
            <label class="form-label">الاسم الكامل</label>
            <input type="text" class="form-control member-full-name">
          </div>
          <div class="col-lg-6">
            <label class="form-label">الرقم الوطني</label>
            <input type="text" class="form-control member-national-id">
          </div>
          <div class="col-lg-6">
            <label class="form-label">الهاتف</label>
            <input type="text" class="form-control member-phone">
          </div>
          <div class="col-lg-6">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control member-email">
          </div>
          <div class="col-lg-4">
            <label class="form-label">الجنس</label>
            <select class="form-select member-gender">
              <option value="">— اختر —</option>
              <option value="male">ذكر</option>
              <option value="female">أنثى</option>
            </select>
          </div>
          <div class="col-lg-4">
            <label class="form-label">المستوى التعليمي</label>
            <input type="text" class="form-control member-education-level">
          </div>
          <div class="col-lg-4">
            <label class="form-label">صلة العلاقة</label>
            <input type="text" class="form-control member-relation-type" placeholder="ابن / ابنة / تابع">
          </div>
        </div>
      </div>
    `;
  }

  function bindRemoveButtons() {
    membersContainer.querySelectorAll('.remove-member-btn').forEach(btn => {
      btn.onclick = () => {
        btn.closest('.member-card')?.remove();
      };
    });
  }

  function addMember() {
    const index = membersContainer.querySelectorAll('.member-card').length;
    membersContainer.insertAdjacentHTML('beforeend', memberTemplate(index));
    bindRemoveButtons();
  }

  function collectMembers() {
    return [...membersContainer.querySelectorAll('.member-card')].map(card => ({
      full_name: card.querySelector('.member-full-name')?.value?.trim() || null,
      national_id: card.querySelector('.member-national-id')?.value?.trim() || null,
      phone: card.querySelector('.member-phone')?.value?.trim() || null,
      email: card.querySelector('.member-email')?.value?.trim() || null,
      gender: card.querySelector('.member-gender')?.value || null,
      education_level: card.querySelector('.member-education-level')?.value?.trim() || null,
      relation_type: card.querySelector('.member-relation-type')?.value?.trim() || null,
    })).filter(item => item.full_name);
  }

  async function loadCourses() {
    try {
      const result = await window.APP_API.get(window.APP_ROUTES.trainingCourses({ per_page: 100 }));
      const rows = result?.data || [];
      trainingCourseId.innerHTML =
        `<option value="">— اختر الدورة —</option>` +
        rows.map(row => `<option value="${row.id}">${window.APP_HELPERS.e(row.title)} - ${window.APP_HELPERS.e(row.course_code)}</option>`).join('');
    } catch (error) {
      console.error(error);
    }
  }

  addMemberBtn?.addEventListener('click', addMember);
  registrationMode?.addEventListener('change', toggleGuardianFields);

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    setLoading(true);

    const payload = {
      training_course_id: trainingCourseId?.value ? Number(trainingCourseId.value) : null,
      registration_mode: registrationMode?.value || 'self',
      submitted_by_type: document.getElementById('submittedByType')?.value || 'self',
      applicant_name: document.getElementById('applicantName')?.value?.trim() || null,
      applicant_phone: document.getElementById('applicantPhone')?.value?.trim() || null,
      applicant_email: document.getElementById('applicantEmail')?.value?.trim() || null,
      guardian_name: document.getElementById('guardianName')?.value?.trim() || null,
      guardian_phone: document.getElementById('guardianPhone')?.value?.trim() || null,
      guardian_national_id: document.getElementById('guardianNationalId')?.value?.trim() || null,
      notes: document.getElementById('notes')?.value?.trim() || null,
      members: collectMembers(),
    };

    try {
      const response = await window.APP_API.post(
        window.APP_ROUTES.courseRegistrationRequestStore(),
        payload
      );

      showMessage(response?.message || SiteI18n.ta('تم إنشاء طلب التسجيل في الدورة بنجاح.'), 'success');
      form.reset();
      membersContainer.innerHTML = '';
      toggleGuardianFields();
    } catch (error) {
      console.error(error);
      showMessage(error?.data?.message || SiteI18n.ta('تعذر إرسال طلب تسجيل الدورة.'), 'error');
    } finally {
      setLoading(false);
    }
  });

  await loadCourses();
  toggleGuardianFields();
});