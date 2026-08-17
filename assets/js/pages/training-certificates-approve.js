document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyPermissions: [
      window.AppPermissions.VIEW_CERTIFICATE_APPROVALS,
      window.AppPermissions.APPROVE_CENTER_CERTIFICATES,
      window.AppPermissions.APPROVE_TRAINING_CERTIFICATES,
      window.AppPermissions.APPROVE_DEPUTY_CERTIFICATES,
      window.AppPermissions.APPROVE_GENERAL_DIRECTOR_CERTIFICATES,
    ],
  });
  if (!ok) return;

  const tbody = document.getElementById('certificateApprovalsTableBody');
  const loadingBox = document.getElementById('certificateApprovalsLoadingBox');
  const messageBox = document.getElementById('certificateApprovalsMessage');

  if (!tbody || !loadingBox) {
    console.error('Certificate approval elements not found.');
    return;
  }

  const APPROVAL_STEPS = [
    {
      permission: window.AppPermissions.APPROVE_GENERAL_DIRECTOR_CERTIFICATES,
      step: 'general_director_approval',
      status: 'pending_general_director_approval',
    },
    {
      permission: window.AppPermissions.APPROVE_DEPUTY_CERTIFICATES,
      step: 'deputy_director_approval',
      status: 'pending_deputy_approval',
    },
    {
      permission: window.AppPermissions.APPROVE_TRAINING_CERTIFICATES,
      step: 'training_manager_approval',
      status: 'pending_training_approval',
    },
    {
      permission: window.AppPermissions.APPROVE_CENTER_CERTIFICATES,
      step: 'center_approval',
      status: 'pending_center_approval',
    },
  ];

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function currentUser() {
    return window.AppAuth.getUser() || {};
  }

  function showMessage(text, type = 'success') {
    if (!messageBox) return;

    messageBox.className = `certificate-approvals-message ${type}`;
    messageBox.textContent = text;
  }

  function hideMessage() {
    if (!messageBox) return;

    messageBox.className = 'certificate-approvals-message';
    messageBox.textContent = '';
  }

  const PENDING_APPROVAL_STATUSES = [
    'pending_center_approval',
    'pending_training_approval',
    'pending_deputy_approval',
    'pending_general_director_approval',
  ];

  function isViewerOnly() {
    return window.AppAuth.hasPermission(window.AppPermissions.VIEW_CERTIFICATE_APPROVALS)
      && !resolveAllowedStep();
  }

  function resolveAllowedStep() {
    const match = APPROVAL_STEPS.find((item) => window.AppAuth.hasPermission(item.permission));
    return match?.step || null;
  }

  function resolveAllowedStatus() {
    const match = APPROVAL_STEPS.find((item) => window.AppAuth.hasPermission(item.permission));
    return match?.status || null;
  }

  function resolveStepLabel(step) {
    const map = {
      center_approval: SiteI18n.ta('اعتماد المركز'),
      training_manager_approval: SiteI18n.ta('اعتماد قسم التدريب'),
      deputy_director_approval: SiteI18n.ta('اعتماد نائب المدير'),
      general_director_approval: SiteI18n.ta('اعتماد المدير العام'),
    };

    return map[step] || SiteI18n.ta('مرحلة الاعتماد');
  }

  function approvalBadge(approvals, step) {
    const item = (approvals || []).find((approval) => approval.approval_step === step);

    if (!item) {
      return '<span class="badge bg-light text-dark border">—</span>';
    }

    if (item.decision === 'approved') {
      const esig = item.electronic_signature?.verification_code;
      const esigHtml = esig
        ? `<div class="small text-muted mt-1">${safeText(esig)}</div>`
        : '';

      return `<span class="badge bg-success">مقبول</span>${esigHtml}`;
    }

    if (item.decision === 'rejected') {
      return SiteI18n.ta('<span class="badge bg-danger">مرفوض</span>');
    }

    return SiteI18n.ta('<span class="badge bg-secondary">قيد الانتظار</span>');
  }

  function canActOnItem(item, allowedStep, allowedStatus) {
    if (!allowedStep || !allowedStatus) return false;
    if (!item || item.status !== allowedStatus) return false;

    const approval = (item.approvals || []).find((a) => a.approval_step === allowedStep);

    return !!approval && approval.decision === 'pending';
  }

  function renderInfoRow(type, message, colspan = 8) {
    tbody.innerHTML = `
      <tr>
        <td colspan="${colspan}" class="text-center ${type === 'error' ? 'text-danger' : 'text-muted'}">
          ${window.APP_HELPERS.e(message)}
        </td>
      </tr>
    `;
  }

  function setButtonLoadingState(button, isLoading, loadingText) {
    if (!button) return;

    if (isLoading) {
      button.dataset.originalText = button.textContent;
      button.disabled = true;
      button.textContent = loadingText;
      return;
    }

    button.disabled = false;
    button.textContent = button.dataset.originalText || button.textContent;
  }

  async function sendDecision(certificateId, step, decision, clickedButton) {
    const user = currentUser();

    setButtonLoadingState(
      clickedButton,
      true,
      decision === 'approved' ? 'جارٍ الاعتماد...': SiteI18n.ta('جارٍ الرفض...')
    );

    try {
      const response = await window.APP_API.post(
        window.APP_ROUTES.certificateApprove(certificateId),
        {
          approval_step: step,
          decision,
          approved_by: user.id || null,
          notes: decision === 'approved'
            ? 'تمت الموافقة من الواجهة': SiteI18n.ta('تم الرفض من الواجهة'),
        }
      );

      return response;
    } finally {
      setButtonLoadingState(clickedButton, false);
    }
  }

  function bindRowActions(allowedStep) {
    tbody.querySelectorAll('.approve-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const certificateId = btn.getAttribute('data-id');
        hideMessage();

        try {
          const response = await sendDecision(certificateId, allowedStep, 'approved', btn);
          showMessage(response?.message || SiteI18n.ta('تم اعتماد الشهادة بنجاح.'), 'success');
          await loadData();
        } catch (error) {
          console.error('Approve error:', error);
          showMessage(error?.data?.message || SiteI18n.ta('فشل اعتماد الشهادة.'), 'error');
        }
      });
    });

    tbody.querySelectorAll('.reject-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const certificateId = btn.getAttribute('data-id');
        hideMessage();

        try {
          const response = await sendDecision(certificateId, allowedStep, 'rejected', btn);
          showMessage(response?.message || SiteI18n.ta('تم رفض الشهادة بنجاح.'), 'success');
          await loadData();
        } catch (error) {
          console.error('Reject error:', error);
          showMessage(error?.data?.message || SiteI18n.ta('فشل رفض الشهادة.'), 'error');
        }
      });
    });
  }

  function canPrint() {
    return window.AppAuth.hasPermission(window.AppPermissions.PRINT_CERTIFICATES);
  }

  function buildPreviewActions(item) {
    if (!item?.id) {
      return '';
    }

    const canPreview = true;
    const showPrint = canPrint();

    if (!canPreview && !showPrint) {
      return '';
    }

    const buttons = [];

    if (canPreview) {
      buttons.push(`
        <button type="button"
                class="btn btn-sm btn-outline-secondary cert-preview-btn"
                data-id="${window.APP_HELPERS.e(item.id)}">
          معاينة
        </button>
      `);
    }

    if (showPrint) {
      buttons.push(`
        <button type="button"
                class="btn btn-sm btn-outline-primary cert-print-btn"
                data-id="${window.APP_HELPERS.e(item.id)}">
          طباعة
        </button>
      `);
    }

    return `<div class="d-flex flex-wrap gap-2 mb-2">${buttons.join('')}</div>`;
  }

  function bindPreviewButtons(rows) {
    const byId = new Map((rows || []).map((item) => [String(item.id), item]));

    tbody.querySelectorAll('.cert-preview-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const item = byId.get(String(btn.getAttribute('data-id')));
        btn.disabled = true;
        try {
          await window.APP_HELPERS.openCertificatePreview(item);
        } finally {
          btn.disabled = false;
        }
      });
    });

    tbody.querySelectorAll('.cert-print-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const item = byId.get(String(btn.getAttribute('data-id')));
        btn.disabled = true;
        try {
          await window.APP_HELPERS.openCertificatePreview(item, { preferPrint: true });
        } finally {
          btn.disabled = false;
        }
      });
    });
  }

  function renderRows(rows, allowedStep, allowedStatus) {
    if (!rows.length) {
      renderInfoRow('empty', SiteI18n.ta('لا توجد شهادات ضمن مرحلة الاعتماد الحالية.'));
      return;
    }

    tbody.innerHTML = rows.map((item) => {
      const traineeName = item?.trainee?.name || '—';
      const certificateNumber = item?.certificate_number || '—';
      const canAct = canActOnItem(item, allowedStep, allowedStatus);
      const currentStepLabel = resolveStepLabel(allowedStep);

      return `
        <tr>
          <td>
            <div class="fw-bold">${safeText(certificateNumber)}</div>
            <div class="small text-muted mt-1">${safeText(item?.reference_number)}</div>
          </td>

          <td>
            <div class="fw-bold">${safeText(traineeName)}</div>
            <div class="small text-muted mt-1">${safeText(item?.trainee?.trainee_code)}</div>
          </td>

          <td>${approvalBadge(item.approvals, 'center_approval')}</td>
          <td>${approvalBadge(item.approvals, 'training_manager_approval')}</td>
          <td>${approvalBadge(item.approvals, 'deputy_director_approval')}</td>
          <td>${approvalBadge(item.approvals, 'general_director_approval')}</td>

          <td>
            <div class="mb-2">${window.APP_HELPERS.badgeHtml(item.status)}</div>
            <div class="small text-muted">${safeText(currentStepLabel)}</div>
          </td>

          <td>
            ${buildPreviewActions(item)}
            ${
              canAct
                ? `
                  <div class="d-flex flex-wrap gap-2">
                    <button type="button"
                            class="btn btn-sm btn-success approve-btn"
                            data-id="${item.id}">
                      موافقة
                    </button>

                    <button type="button"
                            class="btn btn-sm btn-danger reject-btn"
                            data-id="${item.id}">
                      رفض
                    </button>
                  </div>
                `
                : ''
            }
          </td>
        </tr>
      `;
    }).join('');

    bindRowActions(allowedStep);
    bindPreviewButtons(rows);
  }

  async function loadData() {
    const allowedStep = resolveAllowedStep();
    const allowedStatus = resolveAllowedStatus();
    const viewerOnly = isViewerOnly();

    if (!allowedStep && !viewerOnly) {
      window.APP_UI.hideLoadingState(loadingBox);
      renderInfoRow('error', SiteI18n.ta('لا توجد صلاحية لعرض اعتماد الشهادات.'));
      return;
    }

    try {
      hideMessage();

      let rows = [];

      if (viewerOnly) {
        const results = await Promise.all(
          PENDING_APPROVAL_STATUSES.map((status) =>
            window.APP_API.get(
              window.APP_ROUTES.certificates({
                with_approvals: 1,
                status,
                per_page: 100,
              })
            )
          )
        );

        const seen = new Set();
        results.forEach((result) => {
          (result?.data || []).forEach((item) => {
            if (!seen.has(item.id)) {
              seen.add(item.id);
              rows.push(item);
            }
          });
        });
      } else {
        const result = await window.APP_API.get(
          window.APP_ROUTES.certificates({
            with_approvals: 1,
            status: allowedStatus,
            per_page: 100,
          })
        );
        rows = result?.data || [];
      }

      window.APP_UI.hideLoadingState(loadingBox);
      renderRows(rows, allowedStep, allowedStatus);
    } catch (error) {
      console.error('Certificate approvals error:', error);
      window.APP_UI.hideLoadingState(loadingBox);
      renderInfoRow('error', error?.data?.message || SiteI18n.ta('تعذر تحميل بيانات اعتماد الشهادات.'));
    }
  }

  await loadData();
});
