document.addEventListener('DOMContentLoaded', async () => {
  const loadingBox = document.getElementById('loadingBox');
  const container = document.getElementById('requestsContainer');
  const messageBox = document.getElementById('listMessage');

  function showPageError(text) {
    if (loadingBox) loadingBox.style.display = 'none';
    if (messageBox) {
      messageBox.className = 'alert-box err show';
      messageBox.textContent = text;
    }
    if (container) {
      container.innerHTML = `<div class="empty"><i class="bi bi-exclamation-triangle"></i>${text}</div>`;
    }
  }

  try {
    if (!window.AppBootstrapAuth || !window.AppAuth || !window.APP_CONFIG) {
      showPageError('تعذر تحميل مكتبات النظام. حدّث الصفحة أو أعد تسجيل الدخول.');
      return;
    }

    const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
    if (!ok) return;

    const FP = window.FinancePlatform || {};
    const canView = typeof FP.canViewApplications === 'function'
      ? FP.canViewApplications()
      : (
        window.AppAuth.hasPermission('finance.applications.view')
        || window.AppAuth.isNationalAdmin?.()
        || window.AppAuth.hasRole('general_director')
        || window.AppAuth.hasRole('deputy_general_director')
        || window.AppAuth.hasRole('deputy_director')
        || window.AppAuth.hasRole('finance_manager')
        || window.AppAuth.hasRole('branch_manager')
        || window.AppAuth.hasRole('project_owner')
      );

    if (!canView) {
      window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
      return;
    }

    const filterStatus = document.getElementById('filterStatus');
    const filterSearch = document.getElementById('filterSearch');
    const btnRefresh = document.getElementById('btnRefresh');
    const btnNew = document.getElementById('btnNewApplication');
    const scopeNote = document.getElementById('scopeNote');
    const pageHeroSub = document.getElementById('pageHeroSub');

    const canReview = (typeof FP.canReviewBranch === 'function'
      ? FP.canReviewBranch()
      : (window.AppAuth.hasPermission('finance.applications.review_branch') || window.AppAuth.hasRole('branch_manager') || window.AppAuth.isNationalAdmin?.()))
      && !(typeof FP.isReadOnly === 'function' && FP.isReadOnly());

    const canApprove = (typeof FP.canApproveApplication === 'function'
      ? FP.canApproveApplication()
      : (window.AppAuth.hasPermission('finance.applications.approve')
        || window.AppAuth.hasRole('finance_manager')
        || window.AppAuth.hasRole('general_director')
        || window.AppAuth.hasRole('deputy_general_director')
        || window.AppAuth.isNationalAdmin?.()))
      && !(typeof FP.isReadOnly === 'function' && FP.isReadOnly());

    const canReject = (typeof FP.canRejectApplication === 'function'
      ? FP.canRejectApplication()
      : (window.AppAuth.hasPermission('finance.applications.reject')
        || window.AppAuth.hasRole('finance_manager')
        || window.AppAuth.hasRole('branch_manager')
        || window.AppAuth.hasRole('general_director')
        || window.AppAuth.isNationalAdmin?.()))
      && !(typeof FP.isReadOnly === 'function' && FP.isReadOnly());

    const canCreate = typeof FP.canCreateApplication === 'function'
      ? FP.canCreateApplication()
      : (window.AppAuth.hasPermission('finance.applications.create') || window.AppAuth.hasRole('project_owner'));

    let rows = [];

    if (btnNew) btnNew.style.display = canCreate ? '' : 'none';

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

    function statusLabel(status) {
      return FP.statusLabel ? FP.statusLabel(status) : ({
        draft: 'مسودة',
        submitted: 'مُرسل',
        branch_review: 'مراجعة فرع',
        needs_completion: 'يحتاج استكمال',
        funder_review: 'مراجعة تمويل',
        approved: 'معتمد',
        rejected: 'مرفوض',
        funded: 'ممول',
      }[status] || status || '—');
    }

    function formatAmount(value, currency) {
      if (FP.formatAmount) return FP.formatAmount(value, currency || 'SYP');
      return `${Number(value || 0).toLocaleString('ar-SY')} ${currency || 'SYP'}`;
    }

    function updateScopeNote() {
      const user = window.AppAuth.getUser?.() || {};
      const isBranch = window.AppAuth.hasRole('branch_manager');
      const isFinance = window.AppAuth.hasRole('finance_manager') || window.AppAuth.hasRole('finance_officer');
      const isGd = window.AppAuth.hasRole('general_director')
        || window.AppAuth.hasRole('deputy_general_director')
        || window.AppAuth.hasRole('deputy_director')
        || window.AppAuth.isNationalAdmin?.();

      if (isBranch) {
        const branchName = user.branch_name || user.branch?.name || '';
        if (scopeNote) {
          scopeNote.textContent = branchName
            ? `تعرض هذه الشاشة طلبات فرع ${branchName} فقط. موافقة الفرع تحيل الطلب لمراجعة التمويل.`
            : 'تعرض هذه الشاشة طلبات فرعك فقط. موافقة الفرع تحيل الطلب لمراجعة التمويل.';
        }
        if (pageHeroSub) pageHeroSub.textContent = 'متابعة طلبات التمويل ضمن نطاق فرعك';
        return;
      }

      if (isFinance || isGd) {
        if (scopeNote) {
          scopeNote.textContent = 'بعد موافقة الفرع تظهر الطلبات هنا للاعتماد. عند الاعتماد تنتقل تلقائياً إلى سحابة التمويل.';
        }
        if (pageHeroSub) pageHeroSub.textContent = 'إدارة مسار التمويل من المراجعة حتى الاعتماد والسحابة';
        return;
      }

      if (scopeNote) scopeNote.textContent = 'متابعة طلبات التمويل المتاحة حسب صلاحيتك.';
    }

    function setStatPlaceholder() {
      ['statTotal', 'statBranch', 'statFinance', 'statApproved'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.textContent = '—';
      });
    }

    function updateStats(list) {
      const set = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = String(value);
      };
      set('statTotal', list.length);
      set('statBranch', list.filter((r) => ['submitted', 'branch_review'].includes(r.status)).length);
      set('statFinance', list.filter((r) => r.status === 'funder_review').length);
      set('statApproved', list.filter((r) => ['approved', 'funded'].includes(r.status)).length);
    }

    function filteredRows() {
      // الفلترة الأساسية أصبحت على السيرفر
      return rows;
    }

    function actionButtons(row) {
      const buttons = [];
      buttons.push(`<a class="btn-act btn-view" href="finance-application-view.php?id=${row.id}"><i class="bi bi-eye"></i> عرض</a>`);

      if (['approved', 'funded'].includes(row.status)) {
        buttons.push('<a class="btn-act btn-view" href="finance-cloud.php"><i class="bi bi-cloud"></i> السحابة</a>');
      }

      if (canReview && ['submitted', 'branch_review', 'needs_completion'].includes(row.status)) {
        buttons.push(`<button type="button" class="btn-act btn-forward" data-action="branch-approve" data-id="${row.id}"><i class="bi bi-check2-circle"></i> إحالة للتمويل</button>`);
        buttons.push(`<button type="button" class="btn-act btn-complete" data-action="needs-completion" data-id="${row.id}"><i class="bi bi-pencil-square"></i> طلب استكمال</button>`);
      }

      if (canApprove && ['funder_review', 'consultant_review', 'consultant_priced'].includes(row.status)) {
        buttons.push(`<button type="button" class="btn-act btn-approve" data-action="approve" data-id="${row.id}"><i class="bi bi-shield-check"></i> اعتماد ونشر بالسحابة</button>`);
      }

      if (canReject && !['approved', 'funded', 'rejected', 'draft'].includes(row.status)) {
        buttons.push(`<button type="button" class="btn-act btn-reject" data-action="reject" data-id="${row.id}"><i class="bi bi-x-circle"></i> رفض</button>`);
      }

      return buttons.join('');
    }

    function render() {
      const list = filteredRows();
      updateStats(rows);

      if (!container) return;
      if (!list.length) {
        container.innerHTML = '<div class="empty"><i class="bi bi-inbox"></i>لا توجد طلبات مطابقة حالياً.</div>';
        return;
      }

      container.innerHTML = list.map((row) => {
        const gov = row.governorate_name || row.governorate?.name_ar || '—';
        const branch = row.branch_name || row.branch?.name || '—';
        const amount = formatAmount(row.requested_amount, row.currency || 'SYP');
        return `
          <article class="req-card" data-id="${row.id}">
            <div class="req-head">
              <span class="req-code">${esc(row.application_number || `#${row.id}`)}</span>
              <span class="req-status s-${esc(row.status)}">${esc(statusLabel(row.status))}</span>
            </div>
            <h3 class="req-title">${esc(row.project_name || '—')}</h3>
            <div class="req-meta">
              <span><i class="bi bi-person"></i> ${esc(row.applicant_name || '—')}</span>
              <span><i class="bi bi-geo-alt"></i> ${esc(gov)}</span>
              <span><i class="bi bi-building"></i> ${esc(branch)}</span>
              <span><i class="bi bi-cash-coin"></i> ${esc(amount)}</span>
            </div>
            <div class="req-actions">${actionButtons(row)}</div>
          </article>`;
      }).join('');
    }

    function extractRows(res) {
      if (Array.isArray(res?.data)) return res.data;
      if (Array.isArray(res?.data?.data)) return res.data.data;
      if (Array.isArray(res)) return res;
      return [];
    }

    async function loadRows() {
      if (loadingBox) loadingBox.style.display = '';
      setStatPlaceholder();
      try {
        const status = filterStatus?.value || undefined;
        const q = (filterSearch?.value || '').trim() || undefined;
        const params = { per_page: 50 };
        if (status) params.status = status;
        if (q) params.q = q;

        let res;
        if (window.APP_API?.get && window.APP_ROUTES?.fundingApplications) {
          res = await window.APP_API.get(window.APP_ROUTES.fundingApplications(params));
        } else {
          const qs = new URLSearchParams(params).toString();
          const url = `${window.APP_CONFIG.API_BASE_URL}/finance/applications?${qs}`;
          const r = await fetch(url, {
            headers: {
              Accept: 'application/json',
              Authorization: `Bearer ${window.AppAuth.getToken()}`,
            },
          });
          res = await r.json();
          if (!r.ok) throw { message: res?.message || 'تعذر جلب الطلبات', status: r.status, data: res };
        }
        rows = extractRows(res);
        render();
      } catch (err) {
        showMessage(apiErrorMessage(err), 'error');
        if (container) {
          container.innerHTML = `<div class="empty"><i class="bi bi-wifi-off"></i>${esc(apiErrorMessage(err))}</div>`;
        }
      } finally {
        if (loadingBox) loadingBox.style.display = 'none';
      }
    }

    async function runAction(action, id) {
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
        await loadRows();
      } catch (err) {
        showMessage(apiErrorMessage(err), 'error');
      }
    }

    container?.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;
      runAction(btn.dataset.action, Number(btn.dataset.id));
    });

    let searchTimer = null;
    filterStatus?.addEventListener('change', () => { loadRows(); });
    filterSearch?.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => loadRows(), 350);
    });
    btnRefresh?.addEventListener('click', loadRows);

    updateScopeNote();
    await loadRows();
  } catch (err) {
    console.error(err);
    showPageError(err?.message || 'حدث خطأ غير متوقع في صفحة طلبات التمويل.');
  }
});
