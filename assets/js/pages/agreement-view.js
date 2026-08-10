document.addEventListener('DOMContentLoaded', async () => {

  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });

  if (!ok) return;



  const id = new URLSearchParams(window.location.search).get('id');

  if (!id) { window.location.href = 'agreements.php'; return; }



  const loading = document.getElementById('agreementViewLoading');

  const content = document.getElementById('agreementViewContent');

  const grid = document.getElementById('agreementDetailsGrid');

  const actions = document.getElementById('agreementActions');

  const canManage = window.AppAuth.isNationalAdmin() || window.AppAuth.hasPermission('manage_agreements');

  const canApprove = window.AppAuth.isNationalAdmin() || window.AppAuth.hasPermission('approve_agreements');



  function item(label, value) {

    return `<div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">${label}</small><strong>${window.APP_HELPERS.e(value ?? '—')}</strong></div></div>`;

  }



  try {

    const res = await window.APP_API.get(window.APP_ROUTES.agreementShow(id));

    const row = res?.data;

    window.APP_UI.hideLoadingState(loading);

    content.classList.remove('d-none');

    document.getElementById('agreementTitle').textContent = row.title || SiteI18n.ta('تفاصيل الاتفاقية');

    const scopeLabel = row.scope_type === 'branch' ? 'خاصة بفرع': SiteI18n.ta('مركزية');

    grid.innerHTML = [

      item(SiteI18n.ta('الجهة الشريكة'), row.partner_name),

      item(SiteI18n.ta('نوع الاتفاقية'), row.agreement_type),

      item(SiteI18n.ta('النطاق'), scopeLabel),

      item(SiteI18n.ta('المحافظة'), row.governorate?.name_ar),

      item(SiteI18n.ta('الفرع'), row.branch?.name),

      item(SiteI18n.ta('الحالة'), row.status),

      item(SiteI18n.ta('تاريخ البداية'), row.start_date),

      item(SiteI18n.ta('تاريخ النهاية'), row.end_date),

      item(SiteI18n.ta('المبلغ'), row.amount),

      item(SiteI18n.ta('أنشأها'), row.creator?.name),

      item(SiteI18n.ta('اعتمدها'), row.approver?.name),

      item(SiteI18n.ta('تاريخ الإنشاء'), row.created_at),

      item(SiteI18n.ta('آخر تعديل'), row.updated_at),

      `<div class="col-12">${item(SiteI18n.ta('الملاحظات'), row.notes)}</div>`,

    ].join('');

    if (canManage) actions.innerHTML += `<a href="agreements.php" class="btn btn-outline-primary">تعديل من القائمة</a>`;

    if (canApprove && row.status !== 'active') {

      actions.innerHTML += `<button type="button" class="btn btn-brand" id="approveAgreementBtn">اعتماد</button>`;

      document.getElementById('approveAgreementBtn')?.addEventListener('click', async () => {

        await window.APP_API.post(window.APP_ROUTES.agreementApprove(id));

        window.location.reload();

      });

    }

  } catch (error) {

    window.APP_UI.hideLoadingState(loading);

    grid.innerHTML = `<div class="col-12 text-danger">${window.APP_HELPERS.e(error?.data?.message || SiteI18n.ta('غير مصرح أو غير موجود'))}</div>`;

  }

});

