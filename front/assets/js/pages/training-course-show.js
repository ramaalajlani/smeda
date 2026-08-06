document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.VIEW_COURSES,
  });
  if (!ok) return;

  const loadingBox = document.getElementById('courseLoadingBox');
  const detailsWrap = document.getElementById('courseDetailsWrap');
  const infoGrid = document.getElementById('courseInfoGrid');
  const tbody = document.getElementById('courseTraineesTableBody');
  const statsBox = document.getElementById('courseStatsBox');
  const emptyNote = document.getElementById('courseTraineesEmptyNote');
  const messageBox = document.getElementById('courseActionMessage');
  const manageToolbar = document.getElementById('courseManageToolbar');
  const editCourseModal = document.getElementById('editCourseModal');
  const editCourseForm = document.getElementById('editCourseForm');
  const editTraineeResultModal = document.getElementById('editTraineeResultModal');
  const editTraineeResultForm = document.getElementById('editTraineeResultForm');

  const canManageCourses = window.AppAuth.hasPermission(window.AppPermissions.MANAGE_COURSES);
  let currentCourse = null;

  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('id');

  if (!courseId) {
    if (loadingBox) {
      loadingBox.textContent = SiteI18n.ta('رقم الدورة غير موجود في الرابط.');
    }
    return;
  }

  function showMessage(text, type = 'success') {
    if (!messageBox) return;
    messageBox.className = `course-action-message ${type}`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  function hideMessage() {
    if (!messageBox) return;
    messageBox.className = 'course-action-message d-none';
    messageBox.textContent = '';
  }

  function safe(value, fallback = '—') {
    return window.APP_HELPERS.safe(value, fallback);
  }

  function escapeHtml(value) {
    return window.APP_HELPERS.e(value);
  }

  function badge(value) {
    return window.APP_HELPERS.badgeHtml(value);
  }

  function deliveryModeLabel(value) {
    const map = {
      online: SiteI18n.ta('أونلاين'),
      offline: SiteI18n.ta('حضوري'),
      hybrid: SiteI18n.ta('هجين'),
    };

    const key = String(value || '').trim();
    return map[key] || safe(key);
  }

  function infoItem(label, value) {
    return `
      <div class="course-info-item">
        <small>${escapeHtml(label)}</small>
        <strong>${escapeHtml(safe(value))}</strong>
      </div>
    `;
  }

  function formatDate(dateValue) {
    return safe(dateValue);
  }

  function isApprovedCertificate(cert) {
    return cert?.status === 'approved' && !!cert?.is_verified;
  }

  function buildStatsChips(course) {
    const trainees = Array.isArray(course.trainees) ? course.trainees : [];

    const attendedCount = trainees.filter(
      item => item?.pivot?.attendance_status === 'attended'
    ).length;

    const absentCount = trainees.filter(
      item => item?.pivot?.attendance_status === 'absent'
    ).length;

    const passedCount = trainees.filter(
      item => item?.pivot?.result === 'passed'
    ).length;

    const failedCount = trainees.filter(
      item => item?.pivot?.result === 'failed'
    ).length;

    return `
      <div class="course-stat-chip">إجمالي المتدربين: ${safe(course.trainees_count, 0)}</div>
      <div class="course-stat-chip">الحضور: ${attendedCount}</div>
      <div class="course-stat-chip">الغياب: ${absentCount}</div>
      <div class="course-stat-chip">الناجحون: ${passedCount}</div>
      <div class="course-stat-chip">الراسبون: ${failedCount}</div>
      <div class="course-stat-chip">الشهادات: ${safe(course.certificates_count, 0)}</div>
    `;
  }

  function getExistingCertificate(course, traineeId, certificateType = null) {
    const certificates = Array.isArray(course.certificates) ? course.certificates : [];
    return certificates.find(item => {
      if (Number(item.trainee_id) !== Number(traineeId)) {
        return false;
      }

      if (!certificateType) {
        return true;
      }

      const normalized = certificateType === 'pass' ? 'completion' : certificateType;
      const itemType = item.certificate_type === 'pass' ? 'completion' : item.certificate_type;
      return itemType === normalized;
    }) || null;
  }

  function canIssueAttendance(trainee) {
    return trainee?.pivot?.attendance_status === 'attended';
  }

  function canIssuePass(trainee) {
    return (
      trainee?.pivot?.attendance_status === 'attended' &&
      trainee?.pivot?.result === 'passed'
    );
  }

  function certificateStatusBadge(cert) {
    if (!cert) return '';

    const map = {
      pending_center_approval: ['secondary', SiteI18n.ta('تم إصدارها — بانتظار اعتماد المركز')],
      pending_training_approval: ['warning', SiteI18n.ta('بانتظار قسم التدريب')],
      pending_deputy_approval: ['info', SiteI18n.ta('بانتظار نائب المدير')],
      pending_general_director_approval: ['primary', SiteI18n.ta('بانتظار اعتماد المدير العام')],
      approved: ['success', SiteI18n.ta('شهادة معتمدة')],
      rejected: ['danger', SiteI18n.ta('شهادة مرفوضة')],
      cancelled: ['dark', SiteI18n.ta('شهادة ملغاة')],
      draft: ['secondary', SiteI18n.ta('مسودة')],
    };

    const key = String(cert.status || '').trim();
    const found = map[key];

    if (!found) {
      return `<span class="badge bg-secondary">${escapeHtml(key || '—')}</span>`;
    }

    return `<span class="badge bg-${found[0]}">${found[1]}</span>`;
  }

  function ensureQrModal() {
    let modal = document.getElementById('courseQrModal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'courseQrModal';
    modal.className = 'course-qr-modal d-none';
    modal.innerHTML = `
      <div class="course-qr-backdrop" data-close="1"></div>
      <div class="course-qr-dialog">
        <button type="button" class="course-qr-close" data-close="1">×</button>
        <h3 class="course-qr-title">رمز QR للشهادة</h3>
        <div class="course-qr-image-wrap">
          <img id="courseQrImage" src="" alt="QR Code" />
        </div>
        <div class="course-qr-meta" id="courseQrMeta">—</div>
      </div>
    `;

    const style = document.createElement('style');
    style.innerHTML = `
      .course-qr-modal{
        position:fixed;
        inset:0;
        z-index:2000;
      }
      .course-qr-backdrop{
        position:absolute;
        inset:0;
        background:rgba(15,23,42,.55);
      }
      .course-qr-dialog{
        position:relative;
        z-index:2;
        width:min(92%,420px);
        margin:6vh auto 0;
        background:#fff;
        border-radius:24px;
        padding:24px;
        box-shadow:0 20px 50px rgba(0,0,0,.2);
        text-align:center;
      }
      .course-qr-close{
        position:absolute;
        top:10px;
        left:12px;
        width:38px;
        height:38px;
        border:none;
        border-radius:50%;
        background:#f1f5f9;
        font-size:24px;
        cursor:pointer;
      }
      .course-qr-title{
        margin:0 0 18px;
        font-size:1.2rem;
        font-weight:800;
        color:#0f172a;
      }
      .course-qr-image-wrap{
        display:flex;
        align-items:center;
        justify-content:center;
        min-height:220px;
        border:1px dashed #cbd5e1;
        border-radius:18px;
        background:#f8fafc;
        padding:16px;
      }
      .course-qr-image-wrap img{
        max-width:220px;
        max-height:220px;
        object-fit:contain;
      }
      .course-qr-meta{
        margin-top:14px;
        color:#475569;
        line-height:1.9;
        font-size:.95rem;
      }
      .course-qr-actions{
        display:grid;
        gap:8px;
      }
    `;

    document.head.appendChild(style);
    document.body.appendChild(modal);

    modal.querySelectorAll('[data-close="1"]').forEach(el => {
      el.addEventListener('click', () => {
        modal.classList.add('d-none');
      });
    });

    return modal;
  }

  function openQrModal(certificate) {
    if (!certificate?.qr_code_url || !isApprovedCertificate(certificate)) {
      showMessage(SiteI18n.ta('رمز QR يظهر فقط بعد الاعتماد النهائي للشهادة.'), 'error');
      return;
    }

    const modal = ensureQrModal();
    const image = document.getElementById('courseQrImage');
    const meta = document.getElementById('courseQrMeta');

    if (image) {
      image.onerror = function () {
        this.onerror = null;
        this.removeAttribute('src');

        if (meta) {
          meta.innerHTML = `
            <div class="text-danger fw-bold mb-2">تعذر تحميل صورة QR</div>
            <div><strong>رقم الشهادة:</strong> ${escapeHtml(safe(certificate.certificate_number))}</div>
            <div><strong>كود التحقق:</strong> ${escapeHtml(safe(certificate.verification_code))}</div>
            <div><strong>الرابط:</strong> <a href="${escapeHtml(safe(certificate.verify_url || ''))}" target="_blank">فتح صفحة التحقق</a></div>
          `;
        }
      };

      image.src = certificate.qr_code_url;
    }

    if (meta) {
      meta.innerHTML = `
        <div><strong>رمز الشهادة:</strong> ${escapeHtml(safe(certificate.certificate_code))}</div>
        <div><strong>رقم الشهادة:</strong> ${escapeHtml(safe(certificate.certificate_number))}</div>
        <div><strong>كود التحقق:</strong> ${escapeHtml(safe(certificate.verification_code))}</div>
        <div><strong>الحالة:</strong> ${escapeHtml(safe(certificate.status))}</div>
        ${certificate.view_url ? `<div><strong>الرابط:</strong> <a href="${escapeHtml(safe(certificate.view_url))}" target="_blank">فتح صفحة الشهادة</a></div>` : ''}
      `;
    }

    modal.classList.remove('d-none');
  }

  function buildCertificateLinks(existingCertificate) {
    const printUrl = existingCertificate?.public_print_url
      || existingCertificate?.printable_url
      || (existingCertificate?.certificate_code
        ? window.APP_ROUTES.certificatePrintByCode(existingCertificate.certificate_code)
        : null);
    const viewUrl = existingCertificate?.view_url
      || (existingCertificate?.certificate_code
        ? window.APP_ROUTES.certificateViewByCode(existingCertificate.certificate_code)
        : null);
    const qrUrl = existingCertificate?.qr_code_url || null;

    let html = `<div class="course-qr-actions">`;

    if (viewUrl) {
      html += `
        <a href="${escapeHtml(viewUrl)}" target="_blank" class="btn btn-sm btn-outline-secondary">
          عرض الشهادة
        </a>
      `;
    }

    if (printUrl) {
      html += `
        <a href="${escapeHtml(printUrl)}" target="_blank" class="btn btn-sm btn-outline-success">
          طباعة
        </a>
      `;
    }

    if (qrUrl && isApprovedCertificate(existingCertificate)) {
      html += `
        <div class="d-flex align-items-center gap-2">
          <button
            type="button"
            class="btn btn-sm btn-outline-dark show-qr-btn"
            data-certificate-id="${existingCertificate.id}"
          >
            عرض QR
          </button>
          <img
            src="${escapeHtml(qrUrl)}"
            alt="QR"
            style="width:72px;height:72px;object-fit:contain;border:1px solid #e5e7eb;border-radius:10px;padding:4px;background:#fff"
          />
        </div>
      `;
    }

    html += `</div>`;
    return html;
  }

  function bindModalCloseHandlers() {
    document.querySelectorAll('.course-modal [data-close-modal]').forEach((el) => {
      el.addEventListener('click', () => {
        el.closest('.course-modal')?.classList.add('d-none');
      });
    });
  }

  function renderManageToolbar(course) {
    if (!manageToolbar || !canManageCourses) {
      manageToolbar?.replaceChildren();
      return;
    }

    let html = `
      <button type="button" class="btn btn-sm btn-outline-primary" id="editCourseBtn">تعديل الدورة</button>
    `;

    if (course.status !== 'completed') {
      html += `<button type="button" class="btn btn-sm btn-brand" id="completeCourseBtn">إكمال الدورة</button>`;
    }

    manageToolbar.innerHTML = html;

    document.getElementById('editCourseBtn')?.addEventListener('click', () => openEditCourseModal(course));
    document.getElementById('completeCourseBtn')?.addEventListener('click', (event) => completeCourse(course, event.currentTarget));
  }

  function openEditCourseModal(course) {
    document.getElementById('editTitle').value = course.title || '';
    document.getElementById('editPlannedHours').value = course.planned_hours || '';
    document.getElementById('editActualHours').value = course.actual_hours || '';
    document.getElementById('editCapacity').value = course.capacity || '';
    document.getElementById('editStatus').value = course.status || 'scheduled';
    document.getElementById('editNotes').value = course.notes || '';
    editCourseModal?.classList.remove('d-none');
  }

  async function saveCourseEdits(event) {
    event.preventDefault();
    if (!currentCourse) return;

    const submitLock = window.APP_UI.beginSubmit(event.target, SiteI18n.ta('جاري الحفظ...'));
    if (!submitLock) return; // طلب سابق قيد التنفيذ — منع الإرسال المزدوج

    const payload = {
      title: document.getElementById('editTitle')?.value?.trim(),
      planned_hours: Number(document.getElementById('editPlannedHours')?.value || 0),
      actual_hours: document.getElementById('editActualHours')?.value !== ''
        ? Number(document.getElementById('editActualHours').value)
        : null,
      capacity: Number(document.getElementById('editCapacity')?.value || 0),
      status: document.getElementById('editStatus')?.value,
      notes: document.getElementById('editNotes')?.value?.trim() || null,
    };

    try {
      const response = await window.APP_API.patch(
        window.APP_ROUTES.trainingCourseUpdate(currentCourse.id),
        payload
      );
      editCourseModal?.classList.add('d-none');
      showMessage(response?.message || SiteI18n.ta('تم تحديث الدورة.'), 'success');
      await loadCourse();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل تحديث الدورة.'), 'error');
    } finally {
      submitLock.done();
    }
  }

  async function completeCourse(course, triggerButton) {
    if (!confirm(SiteI18n.ta('هل أنت متأكد من إكمال هذه الدورة؟'))) return;

    const submitLock = window.APP_UI.beginSubmit(triggerButton, SiteI18n.ta('جاري الإكمال...'));
    if (!submitLock) return; // طلب سابق قيد التنفيذ — منع النقر المزدوج

    try {
      const response = await window.APP_API.post(
        window.APP_ROUTES.trainingCourseComplete(course.id),
        {}
      );
      showMessage(response?.message || SiteI18n.ta('تم إكمال الدورة.'), 'success');
      await loadCourse();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('تعذر إكمال الدورة.'), 'error');
    } finally {
      submitLock.done();
    }
  }

  function openEditTraineeModal(trainee) {
    document.getElementById('editTraineeId').value = trainee.id;
    document.getElementById('editAttendanceStatus').value = trainee?.pivot?.attendance_status || 'registered';
    document.getElementById('editResult').value = trainee?.pivot?.result || 'pending';
    document.getElementById('editScore').value = trainee?.pivot?.score ?? '';
    document.getElementById('editAttendedHours').value = trainee?.pivot?.attended_hours ?? '';
    document.getElementById('editTraineeNotes').value = trainee?.pivot?.notes || '';
    editTraineeResultModal?.classList.remove('d-none');
  }

  async function saveTraineeResult(event) {
    event.preventDefault();
    if (!currentCourse) return;

    const submitLock = window.APP_UI.beginSubmit(event.target, SiteI18n.ta('جاري الحفظ...'));
    if (!submitLock) return; // طلب سابق قيد التنفيذ — منع الإرسال المزدوج

    const traineeId = Number(document.getElementById('editTraineeId')?.value);
    const payload = {
      attendance_status: document.getElementById('editAttendanceStatus')?.value,
      result: document.getElementById('editResult')?.value,
      attended_hours: Number(document.getElementById('editAttendedHours')?.value || 0),
      notes: document.getElementById('editTraineeNotes')?.value?.trim() || null,
    };

    const scoreRaw = document.getElementById('editScore')?.value;
    if (scoreRaw !== '') {
      payload.score = Number(scoreRaw);
    }

    try {
      const response = await window.APP_API.patch(
        window.APP_ROUTES.trainingCourseUpdateTrainee(currentCourse.id, traineeId),
        payload
      );
      editTraineeResultModal?.classList.add('d-none');
      showMessage(response?.message || SiteI18n.ta('تم حفظ نتيجة المتدرب.'), 'success');
      await loadCourse();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل حفظ النتيجة.'), 'error');
    } finally {
      submitLock.done();
    }
  }

  function buildTraineeActions(course, trainee) {
    let html = '';

    if (canManageCourses) {
      html += `
        <button type="button" class="btn btn-sm btn-outline-secondary mb-2 edit-trainee-result-btn" data-trainee-id="${trainee.id}">
          تعديل النتيجة
        </button>
      `;
    }

    html += buildActionButton(course, trainee);
    return html;
  }

  function bindTraineeResultButtons(course) {
    document.querySelectorAll('.edit-trainee-result-btn').forEach((button) => {
      button.addEventListener('click', () => {
        const traineeId = Number(button.dataset.traineeId);
        const trainee = (course.trainees || []).find(item => Number(item.id) === traineeId);
        if (trainee) openEditTraineeModal(trainee);
      });
    });
  }

  function buildActionButton(course, trainee) {
    const existingPass = getExistingCertificate(course, trainee.id, 'completion');
    const existingAttendance = getExistingCertificate(course, trainee.id, 'attendance');

    if (existingPass || existingAttendance) {
      const blocks = [];

      if (existingAttendance) {
        blocks.push(`
          <div class="small fw-bold text-muted mb-1">شهادة حضور</div>
          ${certificateStatusBadge(existingAttendance)}
          ${buildCertificateLinks(existingAttendance)}
        `);
      }

      if (existingPass) {
        blocks.push(`
          <div class="small fw-bold text-muted mb-1">شهادة اجتياز تدريب</div>
          ${certificateStatusBadge(existingPass)}
          ${buildCertificateLinks(existingPass)}
        `);
      }

      return `<div class="d-grid gap-2">${blocks.join('')}</div>`;
    }

    if (canIssuePass(trainee)) {
      return `
        <button
          type="button"
          class="btn btn-sm btn-brand issue-certificate-btn"
          data-trainee-id="${trainee.id}"
          data-certificate-type="completion"
          data-score="${safe(trainee?.pivot?.score, '')}"
          data-hours="${safe(course.actual_hours || course.planned_hours || trainee?.pivot?.attended_hours, 0)}"
        >
          إصدار شهادة اجتياز تدريب
        </button>
      `;
    }

    if (canIssueAttendance(trainee)) {
      return `
        <button
          type="button"
          class="btn btn-sm btn-outline-primary issue-certificate-btn"
          data-trainee-id="${trainee.id}"
          data-certificate-type="attendance"
          data-score=""
          data-hours="${safe(trainee?.pivot?.attended_hours, 0)}"
        >
          إصدار شهادة حضور
        </button>
      `;
    }

    return `<span class="text-muted">غير مؤهل</span>`;
  }

  function buildCourseInfo(course) {
    infoGrid.innerHTML = [
      infoItem(SiteI18n.ta('رقم الدورة'), course.course_code),
      infoItem(SiteI18n.ta('عنوان الدورة'), course.title),
      infoItem(SiteI18n.ta('المركز التدريبي'), course.training_center?.name),
      infoItem(SiteI18n.ta('المدينة'), course.training_center?.city),
      infoItem(SiteI18n.ta('المدرب'), course.trainer?.name),
      infoItem(SiteI18n.ta('رقم المدرب'), course.trainer?.trainer_code),
      infoItem(SiteI18n.ta('الحقيبة التدريبية'), course.training_kit?.name),
      infoItem(SiteI18n.ta('كود الحقيبة'), course.training_kit?.code),
      infoItem(SiteI18n.ta('البرنامج التدريبي'), course.training_program?.name),
      infoItem(SiteI18n.ta('نوع التنفيذ'), deliveryModeLabel(course.delivery_mode)),
      infoItem(SiteI18n.ta('المنصة المعتمدة'), course.approved_platform),
      infoItem(SiteI18n.ta('تاريخ البدء'), formatDate(course.start_date)),
      infoItem(SiteI18n.ta('تاريخ الانتهاء'), formatDate(course.end_date)),
      infoItem(SiteI18n.ta('الساعات المخططة'), safe(course.planned_hours, 0)),
      infoItem(SiteI18n.ta('الساعات الفعلية'), safe(course.actual_hours || course.planned_hours, 0)),
      infoItem(SiteI18n.ta('السعة'), safe(course.capacity, 0)),
      infoItem(SiteI18n.ta('عدد المتدربين'), safe(course.trainees_count, 0)),
      infoItem(SiteI18n.ta('عدد الشهادات'), safe(course.certificates_count, 0)),
      infoItem(SiteI18n.ta('الحالة'), course.status),
      infoItem(SiteI18n.ta('الملاحظات'), course.notes),
    ].join('');
  }

  function renderTrainees(course) {
    const trainees = Array.isArray(course.trainees) ? course.trainees : [];
    tbody.innerHTML = '';

    if (!trainees.length) {
      if (emptyNote) emptyNote.classList.remove('d-none');
      window.APP_UI.renderEmptyTable(tbody, 9, SiteI18n.ta('لا يوجد متدربون ضمن هذه الدورة.'));
      return;
    }

    if (emptyNote) emptyNote.classList.add('d-none');

    trainees.forEach((trainee, index) => {
      const row = document.createElement('tr');

      row.innerHTML = `
        <td>${index + 1}</td>
        <td>${escapeHtml(safe(trainee.trainee_code))}</td>
        <td>${escapeHtml(safe(trainee.name))}</td>
        <td>${badge(trainee?.pivot?.attendance_status || 'pending')}</td>
        <td>${badge(trainee?.pivot?.result || 'pending')}</td>
        <td>${escapeHtml(safe(trainee?.pivot?.score, '—'))}</td>
        <td>${escapeHtml(safe(trainee?.pivot?.attended_hours, '—'))}</td>
        <td>${badge(trainee?.status || 'active')}</td>
        <td class="issue-action-cell">
          ${buildTraineeActions(course, trainee)}
        </td>
      `;

      tbody.appendChild(row);
    });
  }

  async function issueCertificate(course, trainee, button) {
    const certificateType = button.dataset.certificateType;
    const attendedHours = Number(button.dataset.hours || 0);
    const scoreRaw = button.dataset.score;

    const payload = {
      training_course_id: Number(course.id),
      trainee_id: Number(trainee.id),
      certificate_type: certificateType,
      hours_awarded: attendedHours,
      notes: SiteI18n.ta('تم إصدار الشهادة من صفحة تفاصيل الدورة'),
    };

    if (certificateType === 'completion' || certificateType === 'pass') {
      payload.certificate_type = 'completion';
      payload.result = 'passed';
      if (scoreRaw !== '') {
        payload.score = Number(scoreRaw);
      }
    } else {
      payload.certificate_type = 'attendance';
      payload.result = 'passed';
    }

    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = SiteI18n.ta('جاري الإصدار...');

    try {
      const result = await window.APP_API.post(
        window.APP_ROUTES.certificateIssue(),
        payload
      );

      const issuedCode = result?.data?.certificate_code;
      showMessage(
        issuedCode
          ? `${result?.message || SiteI18n.ta('تم إصدار الشهادة بنجاح.')} رمز الشهادة: ${issuedCode}`
          : (result?.message || SiteI18n.ta('تم إصدار الشهادة بنجاح.')),
        'success'
      );
      await loadCourse();
    } catch (error) {
      console.error(error);
      showMessage(error?.data?.message || SiteI18n.ta('فشل إصدار الشهادة.'), 'error');
      button.disabled = false;
      button.textContent = originalText;
    }
  }

  function bindIssueButtons(course) {
    document.querySelectorAll('.issue-certificate-btn').forEach(button => {
      button.addEventListener('click', async () => {
        hideMessage();

        const traineeId = Number(button.dataset.traineeId);
        const trainee = (course.trainees || []).find(
          item => Number(item.id) === traineeId
        );

        if (!trainee) {
          showMessage(SiteI18n.ta('تعذر العثور على المتدرب.'), 'error');
          return;
        }

        await issueCertificate(course, trainee, button);
      });
    });
  }

  function bindQrButtons(course) {
    document.querySelectorAll('.show-qr-btn').forEach(button => {
      button.addEventListener('click', () => {
        hideMessage();

        const certificateId = Number(button.dataset.certificateId);
        const certificate = (course.certificates || []).find(
          item => Number(item.id) === certificateId
        );

        if (!certificate) {
          showMessage(SiteI18n.ta('تعذر العثور على بيانات الشهادة.'), 'error');
          return;
        }

        openQrModal(certificate);
      });
    });
  }

  function renderCourse(course) {
    currentCourse = course;
    buildCourseInfo(course);
    renderManageToolbar(course);

    if (statsBox) {
      statsBox.innerHTML = buildStatsChips(course);
    }

    renderTrainees(course);
    bindIssueButtons(course);
    bindQrButtons(course);
    bindTraineeResultButtons(course);
  }

  bindModalCloseHandlers();
  editCourseForm?.addEventListener('submit', saveCourseEdits);
  editTraineeResultForm?.addEventListener('submit', saveTraineeResult);

  async function loadCourse() {
    try {
      const response = await window.APP_API.get(
        window.APP_API.withQuery(
          window.APP_ROUTES.trainingCourseShow(courseId),
          { with_trainees: 1, with_certificates: 1 }
        )
      );

      let course = response?.data || {};

      // احتياط: إذا العدد موجود والقائمة فارغة، نجلب المتدربين من مسارهم المستقل
      const count = Number(course.trainees_count || 0);
      const list = Array.isArray(course.trainees) ? course.trainees : [];
      if (count > 0 && !list.length && window.APP_ROUTES.trainingCourseTrainees) {
        try {
          const traineesRes = await window.APP_API.get(
            window.APP_ROUTES.trainingCourseTrainees(courseId)
          );
          const trainees = traineesRes?.data || traineesRes?.trainees || [];
          if (Array.isArray(trainees) && trainees.length) {
            course = Object.assign({}, course, {
              trainees,
              trainees_count: trainees.length,
            });
          }
        } catch (_) {}
      }

      window.APP_UI.hideLoadingState(loadingBox);
      if (detailsWrap) detailsWrap.classList.remove('d-none');

      renderCourse(course);
    } catch (error) {
      console.error(error);

      if (detailsWrap) detailsWrap.classList.add('d-none');

      if (loadingBox) {
        loadingBox.classList.remove('d-none');
        loadingBox.innerHTML = `
          <div class="text-danger fw-bold mb-2">تعذر تحميل تفاصيل الدورة التدريبية</div>
          <div class="text-muted">${window.APP_HELPERS.e(error?.data?.message || SiteI18n.ta('حدث خطأ أثناء تحميل البيانات.'))}</div>
        `;
      }
    }
  }

  await loadCourse();
});