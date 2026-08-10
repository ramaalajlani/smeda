document.addEventListener('DOMContentLoaded', async () => {
  const loadingBox = document.getElementById('loadingBox');
  const content = document.getElementById('content');
  const messageBox = document.getElementById('viewMessage');

  function showMessage(text, type = 'success') {
    if (messageBox) {
      messageBox.className = `alert-box ${type === 'error' ? 'err' : 'ok'} show`;
      messageBox.textContent = text;
    }
    if (window.AppFeedback?.fromMessage) {
      window.AppFeedback.fromMessage(text, type);
    }
  }

  function apiErrorMessage(err) {
    return err?.data?.message
      || err?.response?.data?.message
      || err?.message
      || 'تعذر تنفيذ العملية.';
  }

  function esc(value) {
    return window.APP_HELPERS?.e?.(value ?? '') || String(value ?? '')
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  try {
    const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
    if (!ok) return;

    const FP = window.FinancePlatform || {};
    const canView = typeof FP.canViewApplications === 'function'
      ? FP.canViewApplications()
      : (
        window.AppAuth.hasPermission('finance.applications.view')
        || window.AppAuth.hasRole('branch_manager')
        || window.AppAuth.hasRole('finance_manager')
        || window.AppAuth.isNationalAdmin?.()
      );

    if (!canView) {
      window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
      return;
    }

    const id = Number(new URLSearchParams(window.location.search).get('id') || 0);
    if (!id) {
      window.location.href = 'finance-applications-list.php';
      return;
    }

    const canReview = (typeof FP.canReviewBranch === 'function'
      ? FP.canReviewBranch()
      : (window.AppAuth.hasPermission('finance.applications.review_branch') || window.AppAuth.hasRole('branch_manager')))
      && !(typeof FP.isReadOnly === 'function' && FP.isReadOnly());

    const canApprove = (typeof FP.canApproveApplication === 'function'
      ? FP.canApproveApplication()
      : (window.AppAuth.hasPermission('finance.applications.approve') || window.AppAuth.hasRole('finance_manager')))
      && !(typeof FP.isReadOnly === 'function' && FP.isReadOnly());

    const canReject = (typeof FP.canRejectApplication === 'function'
      ? FP.canRejectApplication()
      : (window.AppAuth.hasPermission('finance.applications.reject')
        || window.AppAuth.hasRole('finance_manager')
        || window.AppAuth.hasRole('branch_manager')))
      && !(typeof FP.isReadOnly === 'function' && FP.isReadOnly());

    const canCreate = typeof FP.canCreateApplication === 'function'
      ? FP.canCreateApplication()
      : (window.AppAuth.hasPermission('finance.applications.create') || window.AppAuth.hasRole('project_owner'));

    function statusLabel(status) {
      return FP.statusLabel ? FP.statusLabel(status) : (status || '—');
    }

    function formatAmount(value, currency) {
      if (FP.formatAmount) return FP.formatAmount(value, currency || 'SYP');
      return `${Number(value || 0).toLocaleString('ar-SY')} ${currency || 'SYP'}`;
    }

    function infoItem(label, value) {
      return `<div class="info-item"><div class="info-lbl">${esc(label)}</div><div class="info-val">${esc(value ?? '—')}</div></div>`;
    }

    function renderActions(row) {
      const actionsArea = document.getElementById('actionsArea');
      const actionsCard = document.getElementById('actionsCard');
      if (!actionsArea || !actionsCard) return;

      const buttons = [];

      if (canCreate && ['draft', 'needs_completion'].includes(row.status)) {
        buttons.push(`<a class="btn-act btn-view" href="finance-apply.php?id=${row.id}"><i class="bi bi-pencil-square"></i> استكمال الاستبيان</a>`);
      }

      if (['approved', 'funded'].includes(row.status)) {
        buttons.push('<a class="btn-act btn-view" href="finance-cloud.php"><i class="bi bi-cloud"></i> السحابة</a>');
      }

      if (canReview && ['submitted', 'branch_review', 'needs_completion'].includes(row.status)) {
        buttons.push(`<button type="button" class="btn-act btn-forward" data-action="branch-approve"><i class="bi bi-check2-circle"></i> إحالة للتمويل</button>`);
        buttons.push(`<button type="button" class="btn-act btn-complete" data-action="needs-completion"><i class="bi bi-pencil-square"></i> طلب استكمال</button>`);
      }

      if (canApprove && ['funder_review', 'consultant_review', 'consultant_priced'].includes(row.status)) {
        buttons.push(`<button type="button" class="btn-act btn-approve" data-action="approve"><i class="bi bi-shield-check"></i> اعتماد ونشر بالسحابة</button>`);
      }

      if (canReject && !['approved', 'funded', 'rejected', 'draft'].includes(row.status)) {
        buttons.push(`<button type="button" class="btn-act btn-reject" data-action="reject"><i class="bi bi-x-circle"></i> رفض</button>`);
      }

      if (!buttons.length) {
        actionsCard.style.display = 'none';
        return;
      }

      actionsCard.style.display = '';
      actionsArea.innerHTML = buttons.join('');
    }

    function render(row) {
      const title = document.getElementById('pageTitleText');
      const subtitle = document.getElementById('pageSubtitle');
      if (title) title.textContent = row.application_number || `طلب #${row.id}`;
      if (subtitle) {
        subtitle.innerHTML = `<span class="status-pill s-${esc(row.status)}">${esc(statusLabel(row.status))}</span>`;
      }

      const gov = row.governorate_name || row.governorate?.name_ar || '—';
      const branch = row.branch_name || row.branch?.name || '—';

      document.getElementById('basicGrid').innerHTML = [
        infoItem('رقم الطلب', row.application_number || `#${row.id}`),
        infoItem('الحالة', statusLabel(row.status)),
        infoItem('مقدم الطلب', row.applicant_name),
        infoItem('الهاتف', row.phone),
        infoItem('البريد', row.email),
        infoItem('المحافظة', gov),
        infoItem('الفرع', branch),
        infoItem('تاريخ الإرسال', row.submitted_at || '—'),
      ].join('');

      document.getElementById('projectGrid').innerHTML = [
        infoItem('اسم المشروع', row.project_name),
        infoItem('القطاع', row.project_sector || row.project_type),
        infoItem('حجم المشروع', row.project_size),
        infoItem('مرحلة النشاط', row.business_stage),
        infoItem('حالة المشروع', row.project_status),
        infoItem('المبلغ المطلوب', formatAmount(row.requested_amount, row.currency)),
        infoItem('نوع التمويل', row.financing_type),
        infoItem('نمط التمويل', row.financing_mode),
        infoItem('مدة السداد (شهر)', row.repayment_period_months),
      ].join('');

      const notesCard = document.getElementById('notesCard');
      const notesArea = document.getElementById('notesArea');
      const blocks = [];
      if (row.purpose) {
        blocks.push(`<div style="margin-bottom:10px"><div class="info-lbl">الغرض</div><div class="text-block">${esc(row.purpose)}</div></div>`);
      }
      if (row.description) {
        blocks.push(`<div><div class="info-lbl">الوصف</div><div class="text-block">${esc(row.description)}</div></div>`);
      }
      if (blocks.length) {
        notesCard.style.display = '';
        notesArea.innerHTML = blocks.join('');
      } else {
        notesCard.style.display = 'none';
      }

      renderActions(row);
      if (loadingBox) loadingBox.style.display = 'none';
      if (content) content.style.display = '';
    }

    async function runAction(action) {
      try {
        const api = window.APP_API;
        const routes = window.APP_ROUTES;
        if (!api || !routes) throw new Error('واجهة API غير جاهزة.');

        if (action === 'branch-approve') {
          await api.post(routes.fundingApplicationBranchReview(id), {
            decision: 'approve',
            notes: 'موافقة الفرع وإحالة لمراجعة التمويل',
          });
          showMessage('تمت إحالة الطلب لمراجعة التمويل.');
        } else if (action === 'needs-completion') {
          const notes = window.AppFeedback?.prompt
            ? await window.AppFeedback.prompt({
              title: 'طلب استكمال',
              text: 'اكتب ملاحظة الاستكمال إن وجدت.',
              placeholder: 'ملاحظة اختيارية',
              okLabel: 'إرسال',
            })
            : window.prompt('ملاحظة الاستكمال (اختياري):', '');
          if (notes === null) return;
          await api.post(routes.fundingApplicationBranchReview(id), {
            decision: 'needs_completion',
            notes: notes || '',
          });
          showMessage('تم طلب استكمال الطلب.');
        } else if (action === 'approve') {
          await api.post(routes.fundingApplicationApprove(id));
          showMessage('تم اعتماد الطلب ونشره في سحابة التمويل.');
        } else if (action === 'reject') {
          const notes = window.AppFeedback?.prompt
            ? await window.AppFeedback.prompt({
              title: 'رفض الطلب',
              text: 'يمكنك كتابة سبب الرفض.',
              placeholder: 'سبب الرفض (اختياري)',
              okLabel: 'تأكيد الرفض',
            })
            : window.prompt('سبب الرفض (اختياري):', '');
          if (notes === null) return;
          await api.post(routes.fundingApplicationReject(id), { notes: notes || '' });
          showMessage('تم رفض الطلب.');
        }

        await load();
      } catch (err) {
        showMessage(apiErrorMessage(err), 'error');
      }
    }

    async function load() {
      if (loadingBox) loadingBox.style.display = '';
      if (content) content.style.display = 'none';

      const url = window.APP_ROUTES.fundingApplicationShow(id)
        + (window.APP_ROUTES.fundingApplicationShow(id).includes('?') ? '&' : '?')
        + 'summary=1';

      const res = await window.APP_API.get(url);
      const data = res.data?.data || res.data || res;
      render(data);
    }

    document.getElementById('actionsArea')?.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;
      runAction(btn.dataset.action);
    });

    await load();
  } catch (err) {
    console.error(err);
    if (loadingBox) {
      loadingBox.innerHTML = `<i class="bi bi-exclamation-triangle"></i>${esc(apiErrorMessage(err))}`;
    }
    showMessage(apiErrorMessage(err), 'error');
  }
});
