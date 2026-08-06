document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
  });
  if (!ok) return;

  const messageBox = document.getElementById('registrationReviewMessage');
  const loadingBox = document.getElementById('registrationReviewLoadingBox');
  const typeInput = document.getElementById('registrationRequestType');
  const statusInput = document.getElementById('registrationRequestStatus');
  const loadBtn = document.getElementById('loadRegistrationRequestsBtn');
  const tableHead = document.getElementById('registrationRequestsTableHead');
  const tbody = document.getElementById('registrationRequestsTableBody');
  const detailsContent = document.getElementById('requestDetailsContent');

  if (!loadingBox || !typeInput || !statusInput || !loadBtn || !tableHead || !tbody || !detailsContent) {
    console.error('Registration review page elements not found.');
    return;
  }

  const canViewAllRegistrationRequests = window.AppAuth.hasPermission(window.AppPermissions.VIEW_REGISTRATION_REQUESTS);
  const canAccessCourseRequestsOnly =
    window.AppAuth.hasPermission(window.AppPermissions.CREATE_COURSE_REGISTRATION_REQUESTS) ||
    window.AppAuth.hasPermission(window.AppPermissions.CONFIRM_COURSE_REGISTRATION_REQUESTS) ||
    window.AppAuth.hasPermission(window.AppPermissions.COMPLETE_COURSE_REGISTRATION_REQUESTS);

  if (!canViewAllRegistrationRequests && !canAccessCourseRequestsOnly) {
    window.APP_UI.renderErrorTable(
      tbody,
      7,
      SiteI18n.ta('ليس لديك صلاحية للوصول إلى طلبات التسجيل.')
    );
    return;
  }

  if (!canViewAllRegistrationRequests && canAccessCourseRequestsOnly) {
    typeInput.innerHTML = '<option value="course">طلبات الدورات</option>';
    statusInput.innerHTML = `
      <option value="">الكل</option>
      <option value="submitted">مرسل</option>
      <option value="guardian_confirmed">مؤكد من الجهة الأعلى</option>
      <option value="completed">مكتمل</option>
      <option value="cancelled">ملغي</option>
    `;
  }

  function safe(value, fallback = '—') {
    return window.APP_HELPERS.safe(value, fallback);
  }

  function escapeHtml(value) {
    return window.APP_HELPERS.e(value);
  }

  function showMessage(text, type = 'success') {
    if (!messageBox) return;
    messageBox.className = `review-message ${type}`;
    messageBox.textContent = text;
  }

  function hideMessage() {
    if (!messageBox) return;
    messageBox.className = 'review-message';
    messageBox.textContent = '';
  }

  function requestActionNotes({
    title = SiteI18n.ta('ملاحظات الإجراء'),
    label = SiteI18n.ta('الملاحظات (اختياري)'),
    placeholder = SiteI18n.ta('اكتب الملاحظات هنا...'),
    confirmText = SiteI18n.ta('تأكيد'),
    initialValue = '',
  } = {}) {
    return new Promise((resolve) => {
      const overlay = document.getElementById('reviewNotesDialogOverlay');
      const titleEl = document.getElementById('reviewNotesDialogTitle');
      const labelEl = document.getElementById('reviewNotesDialogLabel');
      const inputEl = document.getElementById('reviewNotesDialogInput');
      const closeBtn = document.getElementById('reviewNotesDialogCloseBtn');
      const cancelBtn = document.getElementById('reviewNotesDialogCancelBtn');
      const confirmBtn = document.getElementById('reviewNotesDialogConfirmBtn');

      if (!overlay || !titleEl || !labelEl || !inputEl || !closeBtn || !cancelBtn || !confirmBtn) {
        resolve({ confirmed: false, notes: '' });
        return;
      }

      titleEl.textContent = title;
      labelEl.textContent = label;
      inputEl.placeholder = placeholder;
      inputEl.value = initialValue;
      confirmBtn.textContent = confirmText;

      const teardown = () => {
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        closeBtn.removeEventListener('click', onCancel);
        cancelBtn.removeEventListener('click', onCancel);
        confirmBtn.removeEventListener('click', onConfirm);
        overlay.removeEventListener('click', onOverlayClick);
        document.removeEventListener('keydown', onKeyDown);
      };

      const onCancel = () => {
        teardown();
        resolve({ confirmed: false, notes: '' });
      };

      const onConfirm = () => {
        const notes = String(inputEl.value || '').trim();
        teardown();
        resolve({ confirmed: true, notes });
      };

      const onOverlayClick = (event) => {
        if (event.target === overlay) {
          onCancel();
        }
      };

      const onKeyDown = (event) => {
        if (event.key === 'Escape') {
          onCancel();
        } else if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
          onConfirm();
        }
      };

      closeBtn.addEventListener('click', onCancel);
      cancelBtn.addEventListener('click', onCancel);
      confirmBtn.addEventListener('click', onConfirm);
      overlay.addEventListener('click', onOverlayClick);
      document.addEventListener('keydown', onKeyDown);

      overlay.classList.add('active');
      overlay.setAttribute('aria-hidden', 'false');
      setTimeout(() => inputEl.focus(), 10);
    });
  }

  function statusBadge(status) {
    return window.APP_HELPERS.badgeHtml(status);
  }

  function typeLabel(type) {
    const map = {
      center: SiteI18n.ta('طلبات المراكز'),
      trainer: SiteI18n.ta('طلبات المدربين'),
      trainee: SiteI18n.ta('طلبات المتدربين'),
      course: SiteI18n.ta('طلبات الدورات'),
    };
    return map[String(type || '').trim()] || SiteI18n.ta('الطلبات');
  }

  function endpointByType(type, params = {}) {
    if (type === 'center') return window.APP_ROUTES.centerRegistrationRequests(params);
    if (type === 'trainer') return window.APP_ROUTES.trainerRegistrationRequests(params);
    if (type === 'trainee') return window.APP_ROUTES.traineeRegistrationRequests(params);
    if (type === 'course') return window.APP_ROUTES.courseRegistrationRequests(params);

    return window.APP_ROUTES.centerRegistrationRequests(params);
  }

  function showDetailsGrid(items) {
    if (!items.length) {
      detailsContent.innerHTML = `
        <div class="request-details-empty">لا توجد تفاصيل متاحة لهذا الطلب.</div>
      `;
      return;
    }

    detailsContent.innerHTML = `
      <div class="request-details-grid">
        ${items.map(item => `
          <div class="request-details-item">
            <small>${escapeHtml(item.label)}</small>
            <strong style="white-space:pre-line;">${escapeHtml(safe(item.value))}</strong>
          </div>
        `).join('')}
      </div>
    `;
  }

  function getCenterDetails(item) {
    return [
      { label: SiteI18n.ta('رقم الطلب'), value: item.request_number },
      { label: SiteI18n.ta('اسم المركز'), value: item.center_name },
      { label: SiteI18n.ta('المدينة'), value: item.city },
      { label: SiteI18n.ta('العنوان'), value: item.address },
      { label: SiteI18n.ta('الهاتف'), value: item.phone },
      { label: SiteI18n.ta('البريد الإلكتروني'), value: item.email },
      { label: SiteI18n.ta('التصنيف المطلوب'), value: item.classification_requested },
      { label: SiteI18n.ta('يدعم التدريب الحضوري'), value: item.supports_offline_training ? 'نعم': SiteI18n.ta('لا') },
      { label: SiteI18n.ta('يدعم التدريب الأونلاين'), value: item.supports_online_training ? 'نعم': SiteI18n.ta('لا') },
      { label: SiteI18n.ta('خط العرض'), value: item.latitude },
      { label: SiteI18n.ta('خط الطول'), value: item.longitude },
      { label: SiteI18n.ta('رقم الترخيص'), value: item.license_number },
      { label: SiteI18n.ta('تاريخ الترخيص'), value: item.license_issue_date },
      { label: SiteI18n.ta('الجهة المصدرة'), value: item.license_issued_by },
      { label: SiteI18n.ta('صورة الترخيص'), value: item.license_image_url || item.license_image_path || SiteI18n.ta('غير متوفر') },
      { label: SiteI18n.ta('حالة الطلب'), value: item.status },
      { label: SiteI18n.ta('ملاحظات الطلب'), value: item.notes },
      { label: SiteI18n.ta('ملاحظات المراجعة'), value: item.decision_notes || item.review_notes },
      { label: SiteI18n.ta('مقدم الطلب'), value: item.submitted_by?.name },
      { label: SiteI18n.ta('تمت المراجعة بواسطة'), value: item.reviewed_by?.name },
      { label: SiteI18n.ta('المركز الناتج بعد القبول'), value: item.approved_training_center?.name },
      { label: SiteI18n.ta('تاريخ الإنشاء'), value: item.created_at },
      { label: SiteI18n.ta('تاريخ المراجعة'), value: item.reviewed_at },
      { label: SiteI18n.ta('تاريخ الموافقة'), value: item.approved_at },
      { label: SiteI18n.ta('تاريخ الرفض'), value: item.rejected_at },
    ];
  }

  function getTrainerDetails(item) {
    return [
      { label: SiteI18n.ta('رقم الطلب'), value: item.request_number },
      { label: SiteI18n.ta('اسم المدرب'), value: item.full_name },
      { label: SiteI18n.ta('رقم الهوية'), value: item.national_id },
      { label: SiteI18n.ta('الهاتف'), value: item.phone },
      { label: SiteI18n.ta('البريد الإلكتروني'), value: item.email },
      { label: SiteI18n.ta('التخصص'), value: item.specialization },
      { label: SiteI18n.ta('التصنيف المطلوب'), value: item.classification_requested },
      { label: SiteI18n.ta('لديه ToT'), value: item.has_tot ? 'نعم': SiteI18n.ta('لا') },
      { label: SiteI18n.ta('رقم شهادة ToT'), value: item.tot_certificate_number },
      { label: SiteI18n.ta('مصدر شهادة ToT'), value: item.tot_certificate_source },
      { label: SiteI18n.ta('المركز التدريبي المرتبط'), value: item.training_center?.name || item.training_center_id },
      { label: SiteI18n.ta('حالة الطلب'), value: item.status },
      { label: SiteI18n.ta('ملاحظات المراجعة'), value: item.decision_notes || item.review_notes },
      { label: SiteI18n.ta('مقدم الطلب'), value: item.submitted_by?.name },
      { label: SiteI18n.ta('تمت المراجعة بواسطة'), value: item.reviewed_by?.name },
      { label: SiteI18n.ta('المدرب الناتج بعد القبول'), value: item.approved_trainer?.name },
      { label: SiteI18n.ta('تاريخ الإنشاء'), value: item.created_at },
      { label: SiteI18n.ta('تاريخ الموافقة'), value: item.approved_at },
      { label: SiteI18n.ta('تاريخ الرفض'), value: item.rejected_at },
    ];
  }

  function getTraineeDetails(item) {
    return [
      { label: SiteI18n.ta('رقم الطلب'), value: item.request_number },
      { label: SiteI18n.ta('اسم المتدرب'), value: item.full_name },
      { label: SiteI18n.ta('رقم الهوية'), value: item.national_id },
      { label: SiteI18n.ta('الهاتف'), value: item.phone },
      { label: SiteI18n.ta('البريد الإلكتروني'), value: item.email },
      { label: SiteI18n.ta('المدينة'), value: item.city },
      { label: SiteI18n.ta('المستوى التعليمي'), value: item.education_level },
      { label: SiteI18n.ta('نمط التسجيل'), value: item.registration_mode },
      { label: SiteI18n.ta('حالة الطلب'), value: item.status },
      { label: SiteI18n.ta('ملاحظات المراجعة'), value: item.decision_notes || item.review_notes },
      { label: SiteI18n.ta('مقدم الطلب'), value: item.submitted_by?.name },
      { label: SiteI18n.ta('تمت المراجعة بواسطة'), value: item.reviewed_by?.name },
      { label: SiteI18n.ta('المتدرب الناتج بعد القبول'), value: item.approved_trainee?.name },
      { label: SiteI18n.ta('تاريخ الإنشاء'), value: item.created_at },
      { label: SiteI18n.ta('تاريخ الموافقة'), value: item.approved_at },
      { label: SiteI18n.ta('تاريخ الرفض'), value: item.rejected_at },
    ];
  }

  function getCourseDetails(item) {
    const members = Array.isArray(item.members) ? item.members : [];
    const membersText = members.length
      ? members.map((member, index) => {
          return `${index + 1}- ${safe(member.full_name)} | ${safe(member.relation_type)} | ${safe(member.status)}`;
        }).join('\n')
      : SiteI18n.ta('لا يوجد أعضاء');

    return [
      { label: SiteI18n.ta('رقم الطلب'), value: item.request_number },
      { label: SiteI18n.ta('الدورة'), value: item.training_course?.title },
      { label: SiteI18n.ta('كود الدورة'), value: item.training_course?.course_code },
      { label: SiteI18n.ta('نمط التسجيل'), value: item.registration_mode },
      { label: SiteI18n.ta('نوع مقدم الطلب'), value: item.submitted_by_type },
      { label: SiteI18n.ta('اسم مقدم الطلب'), value: item.applicant_name },
      { label: SiteI18n.ta('هاتف مقدم الطلب'), value: item.applicant_phone },
      { label: SiteI18n.ta('بريد مقدم الطلب'), value: item.applicant_email },
      { label: SiteI18n.ta('اسم الجهة الأعلى'), value: item.guardian_name },
      { label: SiteI18n.ta('هاتف الجهة الأعلى'), value: item.guardian_phone },
      { label: SiteI18n.ta('هوية الجهة الأعلى'), value: item.guardian_national_id },
      { label: SiteI18n.ta('الحالة'), value: item.status },
      { label: SiteI18n.ta('الملاحظات'), value: item.notes },
      { label: SiteI18n.ta('الأعضاء المرتبطون'), value: membersText },
      { label: SiteI18n.ta('مقدم الطلب'), value: item.submitted_by?.name },
      { label: SiteI18n.ta('تاريخ تأكيد الجهة الأعلى'), value: item.guardian_confirmed_at },
      { label: SiteI18n.ta('تاريخ الإكمال'), value: item.completed_at },
      { label: SiteI18n.ta('تاريخ الإنشاء'), value: item.created_at },
    ];
  }

  function buildDetailsByType(type, item) {
    if (type === 'center') return getCenterDetails(item);
    if (type === 'trainer') return getTrainerDetails(item);
    if (type === 'trainee') return getTraineeDetails(item);
    if (type === 'course') return getCourseDetails(item);
    return [];
  }

  function renderTableHead(type) {
    if (type === 'center') {
      tableHead.innerHTML = `
        <tr>
          <th>#</th>
          <th>رقم الطلب</th>
          <th>اسم المركز</th>
          <th>المدينة</th>
          <th>الحالة</th>
          <th>الملاحظات</th>
          <th class="review-action-cell">إجراء</th>
        </tr>
      `;
      return;
    }

    if (type === 'trainer') {
      tableHead.innerHTML = `
        <tr>
          <th>#</th>
          <th>رقم الطلب</th>
          <th>اسم المدرب</th>
          <th>التخصص</th>
          <th>الحالة</th>
          <th>الملاحظات</th>
          <th class="review-action-cell">إجراء</th>
        </tr>
      `;
      return;
    }

    if (type === 'trainee') {
      tableHead.innerHTML = `
        <tr>
          <th>#</th>
          <th>رقم الطلب</th>
          <th>اسم المتدرب</th>
          <th>المدينة</th>
          <th>الحالة</th>
          <th>الملاحظات</th>
          <th class="review-action-cell">إجراء</th>
        </tr>
      `;
      return;
    }

    if (type === 'course') {
      tableHead.innerHTML = `
        <tr>
          <th>#</th>
          <th>رقم الطلب</th>
          <th>الدورة</th>
          <th>مقدم الطلب</th>
          <th>نمط التسجيل</th>
          <th>الحالة</th>
          <th class="review-action-cell">إجراء</th>
        </tr>
      `;
    }
  }

  function getSummaryColumns(type, item, index) {
    if (type === 'center') {
      return `
        <td>${index + 1}</td>
        <td>${escapeHtml(safe(item.request_number))}</td>
        <td>${escapeHtml(safe(item.center_name))}</td>
        <td>${escapeHtml(safe(item.city))}</td>
        <td>${statusBadge(item.status)}</td>
        <td>${escapeHtml(safe(item.decision_notes || item.review_notes))}</td>
      `;
    }

    if (type === 'trainer') {
      return `
        <td>${index + 1}</td>
        <td>${escapeHtml(safe(item.request_number))}</td>
        <td>${escapeHtml(safe(item.full_name))}</td>
        <td>${escapeHtml(safe(item.specialization))}</td>
        <td>${statusBadge(item.status)}</td>
        <td>${escapeHtml(safe(item.decision_notes || item.review_notes))}</td>
      `;
    }

    if (type === 'trainee') {
      return `
        <td>${index + 1}</td>
        <td>${escapeHtml(safe(item.request_number))}</td>
        <td>${escapeHtml(safe(item.full_name))}</td>
        <td>${escapeHtml(safe(item.city))}</td>
        <td>${statusBadge(item.status)}</td>
        <td>${escapeHtml(safe(item.decision_notes || item.review_notes))}</td>
      `;
    }

    return `
      <td>${index + 1}</td>
      <td>${escapeHtml(safe(item.request_number))}</td>
      <td>${escapeHtml(safe(item.training_course?.title))}</td>
      <td>${escapeHtml(safe(item.applicant_name))}</td>
      <td>${escapeHtml(safe(item.registration_mode))}</td>
      <td>${statusBadge(item.status)}</td>
    `;
  }

  function canReviewCurrentType(type) {
    if (type === 'center') {
      return window.AppAuth.hasPermission(window.AppPermissions.REVIEW_CENTER_REGISTRATION_REQUESTS);
    }

    if (type === 'trainer') {
      return window.AppAuth.hasPermission(window.AppPermissions.REVIEW_TRAINER_REGISTRATION_REQUESTS);
    }

    if (type === 'trainee') {
      return window.AppAuth.hasPermission(window.AppPermissions.REVIEW_TRAINEE_REGISTRATION_REQUESTS);
    }

    if (type === 'course') {
      return window.AppAuth.hasPermission(window.AppPermissions.COMPLETE_COURSE_REGISTRATION_REQUESTS);
    }

    return false;
  }

  function buildActionButtons(type, item) {
    const buttons = [];

    buttons.push(`
      <button type="button"
              class="btn btn-sm btn-outline-primary show-details-btn"
              data-type="${escapeHtml(type)}"
              data-id="${item.id}">
        عرض التفاصيل
      </button>
    `);

    if (type !== 'course' && canReviewCurrentType(type) && String(item.status) === 'pending') {
      buttons.push(`
        <button type="button"
                class="btn btn-sm btn-success review-btn"
                data-type="${escapeHtml(type)}"
                data-id="${item.id}"
                data-status="approved">
          قبول
        </button>
      `);

      buttons.push(`
        <button type="button"
                class="btn btn-sm btn-danger review-btn"
                data-type="${escapeHtml(type)}"
                data-id="${item.id}"
                data-status="rejected">
          رفض
        </button>
      `);
    }

    if (type === 'course' &&
        window.AppAuth.hasPermission(window.AppPermissions.CONFIRM_COURSE_REGISTRATION_REQUESTS) &&
        String(item.status) === 'submitted') {
      buttons.push(`
        <button type="button"
                class="btn btn-sm btn-success course-confirm-btn"
                data-id="${item.id}">
          تأكيد الجهة الأعلى
        </button>
      `);
    }

    if (type === 'course' &&
        window.AppAuth.hasPermission(window.AppPermissions.COMPLETE_COURSE_REGISTRATION_REQUESTS) &&
        String(item.status) === 'guardian_confirmed') {
      buttons.push(`
        <button type="button"
                class="btn btn-sm btn-success course-complete-btn"
                data-id="${item.id}">
          إكمال الطلب
        </button>
      `);
    }

    if (type === 'course' &&
        String(item.status) !== 'completed' &&
        String(item.status) !== 'cancelled') {
      buttons.push(`
        <button type="button"
                class="btn btn-sm btn-outline-danger course-cancel-btn"
                data-id="${item.id}">
          إلغاء
        </button>
      `);
    }

    return `<div class="review-row-actions">${buttons.join('')}</div>`;
  }

  function renderRows(type, rows) {
    renderTableHead(type);
    tbody.innerHTML = '';

    if (!rows.length) {
      window.APP_UI.renderEmptyTable(
        tbody,
        7,
        `لا توجد بيانات ضمن ${typeLabel(type)} حالياً.`
      );
      detailsContent.innerHTML = `
        <div class="request-details-empty">لم يتم العثور على طلبات مطابقة.</div>
      `;
      return;
    }

    rows.forEach((item, index) => {
      const row = document.createElement('tr');

      row.innerHTML = `
        ${getSummaryColumns(type, item, index)}
        <td class="review-action-cell">
          ${buildActionButtons(type, item)}
        </td>
      `;

      tbody.appendChild(row);
    });

    bindDetailsButtons(type, rows);
    bindReviewButtons(type);
    bindCourseActionButtons();
  }

  function bindDetailsButtons(type, rows) {
    document.querySelectorAll('.show-details-btn').forEach((button) => {
      button.addEventListener('click', () => {
        const id = Number(button.getAttribute('data-id'));
        const item = rows.find(row => Number(row.id) === id);

        if (!item) {
          showMessage(SiteI18n.ta('تعذر العثور على بيانات الطلب.'), 'error');
          return;
        }

        const details = buildDetailsByType(type, item);
        showDetailsGrid(details);
      });
    });
  }

  async function reviewRequest(type, id, status) {
    let notes = '';

    if (status === 'rejected') {
      const decision = await requestActionNotes({
        title: SiteI18n.ta('رفض الطلب'),
        label: SiteI18n.ta('أدخل ملاحظات الرفض (اختياري)'),
        placeholder: SiteI18n.ta('سبب الرفض أو أي ملاحظات توضيحية...'),
        confirmText: SiteI18n.ta('تأكيد الرفض'),
      });

      if (!decision.confirmed) {
        throw { cancelled: true };
      }

      notes = decision.notes || '';
    }

    if (type === 'center') {
      return window.APP_API.post(
        window.APP_ROUTES.centerRegistrationRequestReview(id),
        { status, decision_notes: notes || null }
      );
    }

    if (type === 'trainer') {
      return window.APP_API.post(
        window.APP_ROUTES.trainerRegistrationRequestReview(id),
        { status, decision_notes: notes || null }
      );
    }

    if (type === 'trainee') {
      return window.APP_API.post(
        window.APP_ROUTES.traineeRegistrationRequestReview(id),
        { status, decision_notes: notes || null }
      );
    }

    throw new Error('Unsupported request type');
  }

  function bindReviewButtons(type) {
    document.querySelectorAll('.review-btn').forEach((button) => {
      button.addEventListener('click', async () => {
        hideMessage();

        const id = button.getAttribute('data-id');
        const status = button.getAttribute('data-status');
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = SiteI18n.ta('جارٍ التنفيذ...');

        try {
          const response = await reviewRequest(type, id, status);
          showMessage(response?.message || SiteI18n.ta('تم تحديث الطلب بنجاح.'), 'success');
          await loadRows();
        } catch (error) {
          if (error?.cancelled) {
            button.disabled = false;
            button.textContent = originalText;
            return;
          }
          console.error('Review request error:', error);
          showMessage(error?.data?.message || SiteI18n.ta('تعذر تنفيذ الإجراء.'), 'error');
          button.disabled = false;
          button.textContent = originalText;
        }
      });
    });
  }

  async function confirmCourseRequest(id) {
    return window.APP_API.post(
      window.APP_ROUTES.courseRegistrationRequestConfirmByGuardian(id),
      {}
    );
  }

  async function completeCourseRequest(id) {
    return window.APP_API.post(
      window.APP_ROUTES.courseRegistrationRequestComplete(id),
      {}
    );
  }

  async function cancelCourseRequest(id) {
    const decision = await requestActionNotes({
      title: SiteI18n.ta('إلغاء الطلب'),
      label: SiteI18n.ta('أدخل سبب الإلغاء (اختياري)'),
      placeholder: SiteI18n.ta('سبب الإلغاء أو أي ملاحظات...'),
      confirmText: SiteI18n.ta('تأكيد الإلغاء'),
    });

    if (!decision.confirmed) {
      throw { cancelled: true };
    }

    return window.APP_API.post(
      window.APP_ROUTES.courseRegistrationRequestCancel(id),
      { notes: decision.notes || null }
    );
  }

  function bindCourseActionButtons() {
    document.querySelectorAll('.course-confirm-btn').forEach((button) => {
      button.addEventListener('click', async () => {
        hideMessage();

        const id = button.getAttribute('data-id');
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = SiteI18n.ta('جارٍ التأكيد...');

        try {
          const response = await confirmCourseRequest(id);
          showMessage(response?.message || SiteI18n.ta('تم تأكيد الطلب بنجاح.'), 'success');
          await loadRows();
        } catch (error) {
          console.error('Confirm course request error:', error);
          showMessage(error?.data?.message || SiteI18n.ta('تعذر تأكيد الطلب.'), 'error');
          button.disabled = false;
          button.textContent = originalText;
        }
      });
    });

    document.querySelectorAll('.course-complete-btn').forEach((button) => {
      button.addEventListener('click', async () => {
        hideMessage();

        const id = button.getAttribute('data-id');
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = SiteI18n.ta('جارٍ الإكمال...');

        try {
          const response = await completeCourseRequest(id);
          showMessage(response?.message || SiteI18n.ta('تم إكمال الطلب بنجاح.'), 'success');
          await loadRows();
        } catch (error) {
          console.error('Complete course request error:', error);
          showMessage(error?.data?.message || SiteI18n.ta('تعذر إكمال الطلب.'), 'error');
          button.disabled = false;
          button.textContent = originalText;
        }
      });
    });

    document.querySelectorAll('.course-cancel-btn').forEach((button) => {
      button.addEventListener('click', async () => {
        hideMessage();

        const id = button.getAttribute('data-id');
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = SiteI18n.ta('جارٍ الإلغاء...');

        try {
          const response = await cancelCourseRequest(id);
          showMessage(response?.message || SiteI18n.ta('تم إلغاء الطلب بنجاح.'), 'success');
          await loadRows();
        } catch (error) {
          if (error?.cancelled) {
            button.disabled = false;
            button.textContent = originalText;
            return;
          }
          console.error('Cancel course request error:', error);
          showMessage(error?.data?.message || SiteI18n.ta('تعذر إلغاء الطلب.'), 'error');
          button.disabled = false;
          button.textContent = originalText;
        }
      });
    });
  }

  async function loadRows() {
    const type = typeInput.value || 'center';
    const status = statusInput.value || '';

    hideMessage();
    window.APP_UI.showLoadingState(loadingBox, SiteI18n.ta('جاري تحميل الطلبات...'));
    detailsContent.innerHTML = `
      <div class="request-details-empty">لم يتم تحديد أي طلب بعد.</div>
    `;

    try {
      const response = await window.APP_API.get(
        endpointByType(type, {
          status,
          per_page: 100,
        })
      );

      const rows = response?.data || [];

      window.APP_UI.hideLoadingState(loadingBox);
      renderRows(type, rows);
    } catch (error) {
      console.error('Load registration requests error:', error);
      window.APP_UI.hideLoadingState(loadingBox);
      window.APP_UI.renderErrorTable(
        tbody,
        7,
        error?.data?.message || SiteI18n.ta('تعذر تحميل الطلبات.')
      );
      detailsContent.innerHTML = `
        <div class="request-details-empty">تعذر تحميل تفاصيل الطلبات.</div>
      `;
    }
  }

  loadBtn.addEventListener('click', loadRows);
  typeInput.addEventListener('change', loadRows);
  statusInput.addEventListener('change', loadRows);

  await loadRows();
});