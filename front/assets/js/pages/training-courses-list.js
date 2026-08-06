document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.VIEW_COURSES,
  });
  if (!ok) return;

  const tbody = document.getElementById('trainingCoursesTableBody');
  const loadingBox = document.getElementById('coursesLoadingBox');
  const emptyState = document.getElementById('coursesEmptyState');
  const createBtn = document.getElementById('createCourseBtn');
  const createModal = document.getElementById('createCourseModal');
  const createForm = document.getElementById('createCourseForm');
  const createMessage = document.getElementById('createCourseMessage');

  const centerSelect = document.getElementById('createCenterId');
  const trainerSelect = document.getElementById('createTrainerId');
  const kitSelect = document.getElementById('createKitId');
  const programSelect = document.getElementById('createProgramId');

  const canManage = window.AppAuth.hasPermission(window.AppPermissions.MANAGE_COURSES);
  if (!canManage && createBtn) {
    createBtn.remove();
  }

  const createState = {
    centers: [],
    trainersByCenter: new Map(),
    kitsByTrainer: new Map(),
    programsByKit: new Map(),
  };

  function formatDate(dateValue) {
    if (!dateValue) return '—';
    return window.APP_HELPERS.e(dateValue);
  }

  function formatPeriod(startDate, endDate) {
    const start = startDate ? formatDate(startDate) : '—';
    const end = endDate ? formatDate(endDate) : '—';
    return `${start} <br><small class="text-muted">إلى</small><br> ${end}`;
  }

  function deliveryModeLabel(value) {
    const map = {
      online: SiteI18n.ta('أونلاين'),
      offline: SiteI18n.ta('حضوري'),
      hybrid: SiteI18n.ta('هجين'),
    };
    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function resetCreateMessage() {
    if (!createMessage) return;
    createMessage.textContent = '';
    createMessage.className = 'small mb-2';
  }

  function setCreateMessage(text, type = 'danger') {
    if (!createMessage) return;
    createMessage.className = `small mb-2 text-${type}`;
    createMessage.textContent = text;
  }

  function fillSelect(select, rows, labelFn, options = {}) {
    if (!select) return;
    const {
      placeholder = SiteI18n.ta('— اختر —'),
      disabled = false,
      keepValue = null,
    } = options;

    select.innerHTML = '';
    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = placeholder;
    select.appendChild(placeholderOption);

    rows.forEach((row) => {
      const option = document.createElement('option');
      option.value = String(row.id);
      option.textContent = labelFn(row);
      select.appendChild(option);
    });

    if (keepValue && rows.some((row) => String(row.id) === String(keepValue))) {
      select.value = String(keepValue);
    } else {
      select.value = '';
    }

    select.disabled = disabled || rows.length === 0;
  }

  function renderRows(rows) {
    tbody.innerHTML = '';

    if (!rows.length) {
      emptyState?.classList.remove('d-none');
      window.APP_UI.renderEmptyTable(tbody, 11, SiteI18n.ta('لا توجد دورات تدريبية حالياً.'));
      return;
    }

    emptyState?.classList.add('d-none');

    rows.forEach((course, index) => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${index + 1}</td>
        <td>${window.APP_HELPERS.e(window.APP_HELPERS.safe(course.course_code))}</td>
        <td>${window.APP_HELPERS.e(window.APP_HELPERS.safe(course.title))}</td>
        <td>${window.APP_HELPERS.e(window.APP_HELPERS.safe(course.training_center?.name || course.training_center_name))}</td>
        <td>${window.APP_HELPERS.e(window.APP_HELPERS.safe(course.trainer?.name || course.trainer_name))}</td>
        <td>${window.APP_HELPERS.e(window.APP_HELPERS.safe(course.training_kit?.name || course.training_kit_name))}</td>
        <td>${window.APP_HELPERS.e(deliveryModeLabel(course.delivery_mode))}</td>
        <td>${formatPeriod(course.start_date, course.end_date)}</td>
        <td>${window.APP_HELPERS.safe(course.actual_hours || course.planned_hours, '—')}</td>
        <td>${window.APP_HELPERS.badgeHtml(course.status)}</td>
        <td>
          <a href="training-course-show.php?id=${course.id}" class="btn btn-sm btn-outline-primary">
            التفاصيل
          </a>
        </td>
      `;
      tbody.appendChild(row);
    });
  }

  async function loadCourses() {
    const result = await window.APP_API.get(window.APP_ROUTES.trainingCourses({ per_page: 100 }));
    const rows = result?.data || [];
    window.APP_UI.hideLoadingState(loadingBox);
    renderRows(rows);
  }

  async function loadCenters() {
    const centersRes = await window.APP_API.get(window.APP_ROUTES.trainingCenters({ per_page: 100 }));
    const centers = (centersRes?.data || []).filter((center) => center?.is_active !== false);
    createState.centers = centers;
    fillSelect(centerSelect, centers, (item) => `${item.name || ''} (${item.code || item.id})`);
  }

  async function loadTrainersForCenter(centerId) {
    if (!centerId) {
      fillSelect(trainerSelect, [], () => '', {
        placeholder: SiteI18n.ta('اختر المركز أولاً'),
        disabled: true,
      });
      return [];
    }

    const cacheKey = String(centerId);
    if (!createState.trainersByCenter.has(cacheKey)) {
      const trainersRes = await window.APP_API.get(
        window.APP_ROUTES.trainers({
          per_page: 100,
          training_center_id: Number(centerId),
          status: 'active',
          can_train: true,
        })
      );

      const trainers = (trainersRes?.data || []).filter((trainer) => {
        if (!trainer) return false;
        const trainerCenterId = Number(
          trainer.training_center_id || trainer.training_center?.id || 0
        );
        return trainerCenterId === Number(centerId)
          && trainer.status === 'active'
          && Boolean(trainer.can_train);
      });

      createState.trainersByCenter.set(cacheKey, trainers);
    }

    const trainers = createState.trainersByCenter.get(cacheKey) || [];
    fillSelect(
      trainerSelect,
      trainers,
      (item) => `${item.name || ''} (${item.trainer_code || item.id})`,
      { placeholder: SiteI18n.ta('— اختر المدرب —') }
    );

    return trainers;
  }

  async function loadAuthorizedKitsForTrainer(trainerId) {
    if (!trainerId) {
      fillSelect(kitSelect, [], () => '', {
        placeholder: SiteI18n.ta('اختر المدرب أولاً'),
        disabled: true,
      });
      return [];
    }

    const cacheKey = String(trainerId);
    if (!createState.kitsByTrainer.has(cacheKey)) {
      const trainerDetails = await window.APP_API.get(window.APP_ROUTES.trainerShow(Number(trainerId)));
      const trainer = trainerDetails?.data || {};
      const kits = Array.isArray(trainer?.kits) ? trainer.kits : [];

      const activeKits = kits.filter((kit) => {
        if (!kit) return false;
        const isActive = kit.is_active !== false;
        return isActive && kit.status === 'active';
      });

      createState.kitsByTrainer.set(cacheKey, activeKits);
    }

    const kits = createState.kitsByTrainer.get(cacheKey) || [];
    fillSelect(
      kitSelect,
      kits,
      (item) => `${item.name || ''} (${item.code || item.id})`,
      { placeholder: SiteI18n.ta('— اختر الحقيبة —') }
    );

    return kits;
  }

  async function loadProgramsForKit(kitId) {
    if (!kitId) {
      fillSelect(programSelect, [], () => '', {
        placeholder: SiteI18n.ta('اختياري - اختر الحقيبة أولاً'),
        disabled: true,
      });
      return [];
    }

    const cacheKey = String(kitId);
    if (!createState.programsByKit.has(cacheKey)) {
      const kitDetails = await window.APP_API.get(window.APP_ROUTES.trainingKitShow(Number(kitId)));
      const kit = kitDetails?.data || {};
      const programs = Array.isArray(kit?.programs) ? kit.programs : [];
      const activePrograms = programs.filter((program) => program && program.status === 'active');
      createState.programsByKit.set(cacheKey, activePrograms);
    }

    const programs = createState.programsByKit.get(cacheKey) || [];
    fillSelect(
      programSelect,
      programs,
      (item) => `${item.name || ''} (${item.code || item.id})`,
      { placeholder: SiteI18n.ta('— بدون برنامج —') }
    );

    return programs;
  }

  async function syncCreateFormByCenter() {
    const centerId = centerSelect?.value || '';
    fillSelect(kitSelect, [], () => '', {
      placeholder: SiteI18n.ta('اختر المدرب أولاً'),
      disabled: true,
    });
    fillSelect(programSelect, [], () => '', {
      placeholder: SiteI18n.ta('اختياري - اختر الحقيبة أولاً'),
      disabled: true,
    });
    await loadTrainersForCenter(centerId);
  }

  async function syncCreateFormByTrainer() {
    const trainerId = trainerSelect?.value || '';
    fillSelect(programSelect, [], () => '', {
      placeholder: SiteI18n.ta('اختياري - اختر الحقيبة أولاً'),
      disabled: true,
    });
    await loadAuthorizedKitsForTrainer(trainerId);
  }

  async function syncCreateFormByKit() {
    const kitId = kitSelect?.value || '';
    await loadProgramsForKit(kitId);
  }

  function resolveTrainerCenterId(trainer) {
    return Number(
      trainer?.training_center_id
      || trainer?.training_center?.id
      || 0
    );
  }

  async function ensureTrainerMatchesCenterSelection() {
    const trainerId = trainerSelect?.value ? Number(trainerSelect.value) : null;
    if (!trainerId) return;

    const trainerDetails = await window.APP_API.get(window.APP_ROUTES.trainerShow(trainerId));
    const trainer = trainerDetails?.data || null;
    const trainerCenterId = resolveTrainerCenterId(trainer);

    if (!trainerCenterId) return;

    if (String(centerSelect?.value || '') !== String(trainerCenterId)) {
      const centerExists = Array.from(centerSelect?.options || []).some(
        (option) => String(option.value) === String(trainerCenterId)
      );
      if (!centerExists && trainer?.training_center?.name) {
        const option = document.createElement('option');
        option.value = String(trainerCenterId);
        option.textContent = `${trainer.training_center.name} (${trainer.training_center.code || trainerCenterId})`;
        centerSelect?.appendChild(option);
      }

      centerSelect.value = String(trainerCenterId);
      await syncCreateFormByCenter();
      trainerSelect.value = String(trainerId);
      await syncCreateFormByTrainer();

      setCreateMessage(
        SiteI18n.ta('تم ضبط المركز تلقائيًا حسب المركز المعتمد للمدرب المختار.'),
        'warning'
      );
    }
  }

  function validateCreatePayload(payload) {
    if (!payload.training_center_id) {
      return SiteI18n.ta('يرجى اختيار المركز.');
    }
    if (!payload.trainer_id) {
      return SiteI18n.ta('يرجى اختيار المدرب.');
    }
    if (!payload.training_kit_id) {
      return SiteI18n.ta('يرجى اختيار الحقيبة.');
    }

    const centerId = Number(payload.training_center_id);
    const trainerId = Number(payload.trainer_id);
    const kitId = Number(payload.training_kit_id);
    const selectedProgramId = payload.training_program_id ? Number(payload.training_program_id) : null;

    const trainers = createState.trainersByCenter.get(String(centerId)) || [];
    if (!trainers.some((trainer) => Number(trainer.id) === trainerId)) {
      return SiteI18n.ta('المدرب المختار لا يتبع المركز المحدد.');
    }

    const kits = createState.kitsByTrainer.get(String(trainerId)) || [];
    if (!kits.some((kit) => Number(kit.id) === kitId)) {
      return SiteI18n.ta('هذه الحقيبة غير معتمدة لهذا المدرب.');
    }

    if (selectedProgramId) {
      const programs = createState.programsByKit.get(String(kitId)) || [];
      if (!programs.some((program) => Number(program.id) === selectedProgramId)) {
        return SiteI18n.ta('البرنامج المختار لا ينتمي إلى الحقيبة المحددة.');
      }
    }

    return null;
  }

  createBtn?.addEventListener('click', async () => {
    if (!window.AppAccess.guardPermission(window.AppPermissions.MANAGE_COURSES)) return;

    try {
      resetCreateMessage();
      await loadCenters();
      await syncCreateFormByCenter();
      createModal?.classList.remove('d-none');
    } catch (error) {
      console.error(error);
      setCreateMessage(error?.data?.message || SiteI18n.ta('تعذر تحميل بيانات النموذج.'), 'danger');
      createModal?.classList.remove('d-none');
    }
  });

  centerSelect?.addEventListener('change', async () => {
    resetCreateMessage();
    try {
      await syncCreateFormByCenter();
    } catch (error) {
      console.error(error);
      setCreateMessage(error?.data?.message || SiteI18n.ta('تعذر تحميل المدربين للمركز المحدد.'), 'danger');
    }
  });

  trainerSelect?.addEventListener('change', async () => {
    resetCreateMessage();
    try {
      await ensureTrainerMatchesCenterSelection();
      await syncCreateFormByTrainer();
    } catch (error) {
      console.error(error);
      setCreateMessage(error?.data?.message || SiteI18n.ta('تعذر تحميل الحقائب المعتمدة للمدرب.'), 'danger');
    }
  });

  kitSelect?.addEventListener('change', async () => {
    resetCreateMessage();
    try {
      await syncCreateFormByKit();
    } catch (error) {
      console.error(error);
      setCreateMessage(error?.data?.message || SiteI18n.ta('تعذر تحميل البرامج المرتبطة بالحقيبة.'), 'danger');
    }
  });

  createModal?.querySelectorAll('[data-close-modal]').forEach((el) => {
    el.addEventListener('click', () => createModal.classList.add('d-none'));
  });

  createForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    resetCreateMessage();

    const submitLock = window.APP_UI.beginSubmit(createForm, SiteI18n.ta('جاري الحفظ...'));
    if (!submitLock) return; // طلب سابق قيد التنفيذ — منع الإرسال المزدوج

    try {
      try {
        await ensureTrainerMatchesCenterSelection();
      } catch (error) {
        console.error(error);
        setCreateMessage(error?.data?.message || SiteI18n.ta('تعذر التحقق من مطابقة المدرب مع المركز.'), 'danger');
        return;
      }

      const payload = {
      training_center_id: centerSelect?.value ? Number(centerSelect.value) : null,
      trainer_id: trainerSelect?.value ? Number(trainerSelect.value) : null,
      training_kit_id: kitSelect?.value ? Number(kitSelect.value) : null,
      training_program_id: programSelect?.value ? Number(programSelect.value) : null,
      title: document.getElementById('createTitle')?.value?.trim() || '',
      delivery_mode: document.getElementById('createDeliveryMode')?.value || 'offline',
      approved_platform: document.getElementById('createPlatform')?.value?.trim() || null,
      start_date: document.getElementById('createStartDate')?.value || null,
      end_date: document.getElementById('createEndDate')?.value || null,
      planned_hours: Number(document.getElementById('createPlannedHours')?.value || 0),
      capacity: Number(document.getElementById('createCapacity')?.value || 0),
      status: document.getElementById('createStatus')?.value || 'scheduled',
      notes: document.getElementById('createNotes')?.value?.trim() || null,
    };

    const validationError = validateCreatePayload(payload);
    if (validationError) {
      setCreateMessage(validationError, 'danger');
      return;
    }

    try {
      const response = await window.APP_API.post(window.APP_ROUTES.trainingCourseStore(), payload);
      setCreateMessage(response?.message || SiteI18n.ta('تم إنشاء الدورة بنجاح.'), 'success');
      createModal.classList.add('d-none');
      createForm.reset();
      await loadCourses();

      const courseId = response?.data?.id;
      if (courseId) {
        window.location.href = `training-course-show.php?id=${courseId}`;
      }
      } catch (error) {
        console.error(error);
        setCreateMessage(error?.data?.message || SiteI18n.ta('فشل إنشاء الدورة.'), 'danger');
      }
    } finally {
      submitLock.done();
    }
  });

  try {
    await loadCourses();
  } catch (error) {
    console.error(error);
    window.APP_UI.hideLoadingState(loadingBox);
    window.APP_UI.renderErrorTable(tbody, 11, error?.data?.message || SiteI18n.ta('تعذر تحميل الدورات.'));
  }
});
