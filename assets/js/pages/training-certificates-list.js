document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.VIEW_CERTIFICATES,
  });
  if (!ok) return;

  const tbody = document.getElementById('certificatesTableBody');
  const loadingBox = document.getElementById('certificatesLoadingBox');

  if (!tbody || !loadingBox) {
    console.error('Certificate list elements not found.');
    return;
  }

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function canPrint() {
    return window.AppAuth.hasPermission(window.AppPermissions.PRINT_CERTIFICATES);
  }

  function canVerify() {
    return window.AppAuth.hasPermission(window.AppPermissions.VERIFY_CERTIFICATES);
  }

  function isApprovedCertificate(item) {
    return item?.status === 'approved' && !!item?.is_verified;
  }

  function typeLabel(value) {
    const map = {
      attendance: SiteI18n.ta('شهادة حضور'),
      pass: SiteI18n.ta('شهادة اجتياز تدريب'),
      completion: SiteI18n.ta('شهادة اجتياز تدريب'),
    };

    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function resultLabel(value) {
    const map = {
      pending: SiteI18n.ta('قيد الانتظار'),
      passed: SiteI18n.ta('ناجح'),
      failed: SiteI18n.ta('راسب'),
      review: SiteI18n.ta('مراجعة'),
    };

    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function statusLabel(value) {
    const map = {
      pending_center_approval: SiteI18n.ta('تم إصدار الشهادة — بانتظار اعتماد المركز'),
      pending_training_approval: SiteI18n.ta('بانتظار قسم التدريب'),
      pending_deputy_approval: SiteI18n.ta('بانتظار نائب المدير'),
      pending_general_director_approval: SiteI18n.ta('بانتظار اعتماد المدير العام'),
      approved: SiteI18n.ta('شهادة معتمدة'),
      rejected: SiteI18n.ta('شهادة مرفوضة'),
      cancelled: SiteI18n.ta('شهادة ملغاة'),
      draft: SiteI18n.ta('مسودة'),
    };

    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function buildActions(item) {
    const actions = [];
    const qrSrc = item?.qr_code_url ? window.APP_HELPERS.e(item.qr_code_url) : '';
    const links = window.APP_HELPERS.resolveCertificateLinks(item);

    if (links.viewUrl || item?.id) {
      actions.push(`
        <button type="button"
                class="btn btn-sm btn-outline-secondary cert-preview-btn"
                data-id="${window.APP_HELPERS.e(item.id)}">
          عرض
        </button>
      `);
    }

    if ((links.printUrl || item?.id) && canPrint()) {
      actions.push(`
        <button type="button"
                class="btn btn-sm btn-outline-success cert-print-btn"
                data-id="${window.APP_HELPERS.e(item.id)}">
          طباعة
        </button>
      `);
    }

    if (links.verifyUrl && canVerify()) {
      actions.push(`
        <a href="${window.APP_HELPERS.e(links.verifyUrl)}"
           target="_blank"
           rel="noopener noreferrer"
           class="btn btn-sm btn-outline-dark">
          تحقق
        </a>
      `);
    }

    if (!actions.length) {
      if (qrSrc && isApprovedCertificate(item)) {
        return `
          <div class="d-flex flex-column gap-2">
            <img
              src="${qrSrc}"
              alt="QR"
              style="width:96px;height:96px;object-fit:contain;border:1px solid #e5e7eb;border-radius:10px;padding:4px;background:#fff"
            />
          </div>
        `;
      }
      return '—';
    }

    const qrBlock = (qrSrc && isApprovedCertificate(item))
      ? `
        <div class="mt-2">
          <img
            src="${qrSrc}"
            alt="QR"
            style="width:96px;height:96px;object-fit:contain;border:1px solid #e5e7eb;border-radius:10px;padding:4px;background:#fff"
          />
        </div>
      `
      : '';

    return `
      <div class="d-flex flex-column gap-2">
        <div class="d-flex flex-wrap gap-2">
          ${actions.join('')}
        </div>
        ${qrBlock}
      </div>
    `;
  }

  function renderRows(rows) {
    tbody.innerHTML = '';

    if (!rows.length) {
      window.APP_UI.renderEmptyTable(tbody, 8, SiteI18n.ta('لا توجد شهادات تدريبية.'));
      return;
    }

    rows.forEach((item, index) => {
      const row = document.createElement('tr');

      row.innerHTML = `
        <td>${index + 1}</td>

        <td>
          <div class="fw-bold">
            ${safeText(item?.certificate_code || item?.certificate_number)}
          </div>
          <div class="small text-muted mt-1">
            ${item?.certificate_code ? safeText(item?.certificate_number) : safeText(item?.reference_number)}
          </div>
        </td>

        <td>
          <div class="fw-bold">
            ${safeText(item?.trainee?.name)}
          </div>
          <div class="small text-muted mt-1">
            ${safeText(item?.trainee?.trainee_code)}
          </div>
        </td>

        <td>
          <div class="mb-2">
            ${window.APP_HELPERS.badgeHtml(item?.certificate_type)}
          </div>
          <div class="small text-muted">
            ${safeText(typeLabel(item?.certificate_type))}
          </div>
        </td>

        <td>
          <div class="mb-2">
            ${window.APP_HELPERS.badgeHtml(item?.result)}
          </div>
          <div class="small text-muted">
            ${safeText(resultLabel(item?.result))}
          </div>
        </td>

        <td>
          <div class="fw-bold">
            ${safeText(item?.score, '—')}
          </div>
          <div class="small text-muted mt-1">
            ${safeText(item?.hours_awarded ?? item?.training_hours, '0')} ساعة
          </div>
        </td>

        <td>
          <div class="mb-2">
            ${window.APP_HELPERS.badgeHtml(item?.status)}
          </div>
          <div class="small text-muted">
            ${safeText(statusLabel(item?.status))}
          </div>
          <div class="small text-muted mt-2">
            تاريخ الإصدار: ${safeText(item?.issue_date)}
          </div>
        </td>

        <td>
          ${buildActions(item)}
        </td>
      `;

      tbody.appendChild(row);
    });

    bindCertificateActionButtons(rows);
  }

  function bindCertificateActionButtons(rows) {
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

  try {
    const result = await window.APP_API.get(
      window.APP_ROUTES.certificates({ per_page: 100 })
    );

    const rows = result?.data || [];

    window.APP_UI.hideLoadingState(loadingBox);
    renderRows(rows);
  } catch (error) {
    console.error('Certificates list error:', error);
    window.APP_UI.hideLoadingState(loadingBox);
    window.APP_UI.renderErrorTable(
      tbody,
      8,
      error?.data?.message || SiteI18n.ta('تعذر تحميل بيانات الشهادات التدريبية.')
    );
  }
});