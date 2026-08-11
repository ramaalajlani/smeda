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
            ? `تعرض هذه الشاشة طلبات فرع ${branchName} (ومحافظته). موافقة الفرع تحيل الطلب لمراجعة التمويل.`
            : 'تعرض هذه الشاشة طلبات فرعك ومحافظتك. موافقة الفرع تحيل الطلب لمراجعة التمويل.';
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
      buttons.push(`<button type="button" class="btn-act btn-view" data-action="view" data-id="${row.id}"><i class="bi bi-eye"></i> عرض</button>`);

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

    let viewingId = null;
    let viewOverlay = null;
    let viewBody = null;
    let viewActions = null;
    let viewTitle = null;
    let viewSubtitle = null;

    function ensureOverlay() {
      if (!document.getElementById('financeViewOverlayStyle')) {
        const style = document.createElement('style');
        style.id = 'financeViewOverlayStyle';
        style.textContent = `
          #viewOverlay.finance-view-overlay{
            position:fixed !important; inset:0 !important; z-index:99999 !important;
            background:rgba(15,40,36,.55) !important; display:none !important;
            align-items:flex-start !important; justify-content:center !important;
            padding:24px 12px !important; overflow:auto !important;
          }
          #viewOverlay.finance-view-overlay.show{display:flex !important;}
          #viewOverlay .view-panel{
            width:min(920px,100%); background:#fff; border-radius:20px;
            box-shadow:0 24px 60px rgba(0,0,0,.28); margin:auto;
            padding:20px 22px 24px; border:1px solid rgba(23,148,123,.13);
          }
          #viewOverlay .view-panel-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:14px;}
          #viewOverlay .view-panel-head h2{margin:0;font-size:1.1rem;font-weight:800;color:#16332E;}
          #viewOverlay .view-close{border:none;background:#EAF8F4;color:#17947B;border-radius:10px;padding:8px 12px;font-weight:800;cursor:pointer;}
          #viewOverlay .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:12px;}
          #viewOverlay .info-item{background:#EAF8F4;border-radius:12px;padding:11px 12px;}
          #viewOverlay .info-lbl{font-size:.72rem;font-weight:700;color:#6B7280;margin-bottom:3px;}
          #viewOverlay .info-val{font-size:.88rem;font-weight:800;color:#16332E;word-break:break-word;}
          #viewOverlay .text-block{background:#f8fafc;border:1px solid rgba(23,148,123,.13);border-radius:12px;padding:11px 12px;font-size:.86rem;font-weight:600;line-height:1.7;white-space:pre-wrap;margin-bottom:10px;}
          #viewOverlay .view-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;}
        `;
        document.head.appendChild(style);
      }

      viewOverlay = document.getElementById('viewOverlay');
      if (!viewOverlay) {
        viewOverlay = document.createElement('div');
        viewOverlay.id = 'viewOverlay';
        viewOverlay.className = 'view-overlay finance-view-overlay';
        viewOverlay.setAttribute('aria-hidden', 'true');
        viewOverlay.innerHTML = `
          <div class="view-panel" role="dialog" aria-modal="true">
            <div class="view-panel-head">
              <div>
                <h2 id="viewTitle">ملخص الطلب</h2>
                <div id="viewSubtitle" style="color:#6B7280;font-size:.84rem;font-weight:700;margin-top:4px"></div>
              </div>
              <button type="button" class="view-close" id="viewCloseBtn"><i class="bi bi-x-lg"></i> إغلاق</button>
            </div>
            <div id="viewBody"></div>
            <div class="view-actions" id="viewActions"></div>
          </div>`;
      }

      viewOverlay.classList.add('finance-view-overlay');
      if (viewOverlay.parentElement !== document.body) {
        document.body.appendChild(viewOverlay);
      }

      viewBody = document.getElementById('viewBody');
      viewActions = document.getElementById('viewActions');
      viewTitle = document.getElementById('viewTitle');
      viewSubtitle = document.getElementById('viewSubtitle');

      if (!viewOverlay.dataset.bound) {
        viewOverlay.dataset.bound = '1';
        document.getElementById('viewCloseBtn')?.addEventListener('click', closeView);
        viewOverlay.addEventListener('click', (e) => {
          if (e.target === viewOverlay) closeView();
        });
      }
    }

    function closeView() {
      viewingId = null;
      document.body.style.overflow = '';
      if (viewOverlay) {
        viewOverlay.classList.remove('show');
        viewOverlay.setAttribute('aria-hidden', 'true');
      }
    }

    ensureOverlay();

    function infoItem(label, value) {
      return `<div class="info-item"><div class="info-lbl">${esc(label)}</div><div class="info-val">${esc(value ?? '—')}</div></div>`;
    }

    function buildViewActions(row) {
      const buttons = [];
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

    function renderView(row) {
      const gov = row.governorate_name || row.governorate?.name_ar || '—';
      const branch = row.branch_name || row.branch?.name || '—';
      if (viewTitle) viewTitle.textContent = row.application_number || `طلب #${row.id}`;
      if (viewSubtitle) viewSubtitle.textContent = statusLabel(row.status);

      const blocks = [];
      blocks.push(`<div class="info-grid">
        ${infoItem('مقدم الطلب', row.applicant_name)}
        ${infoItem('الهاتف', row.phone)}
        ${infoItem('البريد', row.email)}
        ${infoItem('المحافظة', gov)}
        ${infoItem('الفرع', branch)}
        ${infoItem('تاريخ الإرسال', row.submitted_at || '—')}
      </div>`);
      blocks.push(`<div class="info-grid">
        ${infoItem('المشروع', row.project_name)}
        ${infoItem('القطاع', row.project_sector || row.project_type)}
        ${infoItem('المبلغ', formatAmount(row.requested_amount, row.currency))}
        ${infoItem('نوع التمويل', row.financing_type)}
        ${infoItem('نمط التمويل', row.financing_mode)}
        ${infoItem('مدة السداد', row.repayment_period_months ? `${row.repayment_period_months} شهر` : '—')}
      </div>`);
      if (row.purpose) {
        blocks.push(`<div class="info-lbl">الغرض</div><div class="text-block">${esc(row.purpose)}</div>`);
      }
      if (row.description) {
        blocks.push(`<div class="info-lbl">الوصف</div><div class="text-block">${esc(row.description)}</div>`);
      }

      if (viewBody) viewBody.innerHTML = blocks.join('');
      if (viewActions) viewActions.innerHTML = buildViewActions(row);
    }

    async function openView(id) {
      ensureOverlay();
      viewingId = id;
      viewOverlay.classList.add('show');
      viewOverlay.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';

      const cached = rows.find((r) => Number(r.id) === Number(id));
      if (cached) {
        renderView(cached);
      } else if (viewBody) {
        viewBody.innerHTML = '<div class="empty" style="padding:28px"><i class="bi bi-hourglass-split"></i>جاري تحميل الملخص...</div>';
        if (viewActions) viewActions.innerHTML = '';
        if (viewTitle) viewTitle.textContent = 'ملخص الطلب';
      }

      try {
        const showFn = window.APP_ROUTES?.fundingApplicationShow;
        if (!showFn || !window.APP_API?.get) return;
        const showUrl = showFn(id);
        const url = `${showUrl}${showUrl.includes('?') ? '&' : '?'}summary=1`;
        const res = await window.APP_API.get(url);
        const data = res.data?.data || res.data || res;
        if (Number(viewingId) === Number(id) && data) renderView(data);
      } catch (err) {
        if (!cached) {
          if (viewBody) {
            viewBody.innerHTML = `<div class="empty" style="padding:28px"><i class="bi bi-exclamation-triangle"></i>${esc(apiErrorMessage(err))}</div>`;
          }
          showMessage(apiErrorMessage(err), 'error');
        }
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
        if (viewingId && Number(viewingId) === Number(id)) {
          await openView(id);
        }
      } catch (err) {
        showMessage(apiErrorMessage(err), 'error');
      }
    }

    document.addEventListener('click', (e) => {
      const viewBtn = e.target.closest('[data-action="view"], a[href*="finance-apply.php?id="], a[href*="finance-application-view.php?id="]');
      if (viewBtn && !viewBtn.closest('#viewOverlay')) {
        e.preventDefault();
        e.stopPropagation();
        const hrefId = viewBtn.getAttribute('href')
          ? new URL(viewBtn.getAttribute('href'), window.location.href).searchParams.get('id')
          : null;
        const id = Number(viewBtn.dataset.id || hrefId || 0);
        if (id) openView(id);
        return;
      }

      const btn = e.target.closest('[data-action]');
      if (!btn) return;
      const action = btn.dataset.action;
      if (!action || action === 'view') return;
      const id = Number(btn.dataset.id || viewingId);
      if (!id) return;
      if (btn.closest('#requestsContainer') || btn.closest('#viewOverlay')) {
        runAction(action, id);
      }
    }, true);

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && viewOverlay?.classList.contains('show')) closeView();
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
