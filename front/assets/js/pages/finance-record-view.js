document.addEventListener('DOMContentLoaded', async () => {

  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });

  if (!ok) return;



  const id = new URLSearchParams(window.location.search).get('id');

  if (!id) { window.location.href = 'finance.php'; return; }



  const loading = document.getElementById('financeRecordViewLoading');

  const content = document.getElementById('financeRecordViewContent');

  const grid = document.getElementById('financeRecordDetailsGrid');

  const actions = document.getElementById('financeRecordActions');

  const canManage = window.AppAuth.isNationalAdmin() || window.AppAuth.hasPermission('manage_finance');

  const canApprove = window.AppAuth.isNationalAdmin() || window.AppAuth.hasPermission('approve_finance');

  const isAuditor = window.AppAuth.hasRole('auditor') && !canManage;



  function item(label, value) {

    return `<div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">${label}</small><strong>${window.APP_HELPERS.e(value ?? '—')}</strong></div></div>`;

  }



  const typeLabels = { funding: SiteI18n.ta('تمويل'), payment: SiteI18n.ta('دفع'), commitment: SiteI18n.ta('التزام'), revenue: SiteI18n.ta('إيراد') };



  try {

    const res = await window.APP_API.get(window.APP_ROUTES.financeRecordShow(id));

    const row = res?.data;

    window.APP_UI.hideLoadingState(loading);

    content.classList.remove('d-none');

    document.getElementById('financeRecordTitle').textContent = row.title || SiteI18n.ta('تفاصيل السجل المالي');

    grid.innerHTML = [

      item(SiteI18n.ta('نوع السجل'), typeLabels[row.record_type] || row.record_type),

      item(SiteI18n.ta('المبلغ'), `${Number(row.amount || 0).toLocaleString('ar-SY')} ${row.currency || 'SYP'}`),

      item(SiteI18n.ta('الحالة'), row.status),

      item(SiteI18n.ta('المحافظة'), row.governorate?.name_ar),

      item(SiteI18n.ta('الفرع'), row.branch?.name),

      item(SiteI18n.ta('أنشأه'), row.creator?.name),

      item(SiteI18n.ta('اعتمده'), row.approver?.name),

      item(SiteI18n.ta('تاريخ الإنشاء'), row.created_at),

      item(SiteI18n.ta('آخر تعديل'), row.updated_at),

      `<div class="col-12">${item(SiteI18n.ta('الملاحظات'), row.notes)}</div>`,

    ].join('');

    if (canManage && !isAuditor) actions.innerHTML += `<a href="finance.php" class="btn btn-outline-primary">تعديل من القائمة</a>`;

    if (canApprove && row.status !== 'approved') {

      actions.innerHTML += `<button type="button" class="btn btn-brand" id="approveFinanceBtn">اعتماد</button>`;

      document.getElementById('approveFinanceBtn')?.addEventListener('click', async () => {

        await window.APP_API.post(window.APP_ROUTES.financeRecordApprove(id));

        window.location.reload();

      });

    }

  } catch (error) {

    window.APP_UI.hideLoadingState(loading);

    grid.innerHTML = `<div class="col-12 text-danger">${window.APP_HELPERS.e(error?.data?.message || SiteI18n.ta('غير مصرح أو غير موجود'))}</div>`;

  }

});

