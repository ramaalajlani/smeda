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
          #viewOverlay .view-section-title{font-weight:800;font-size:.92rem;color:#0f5e4f;margin:18px 0 10px;}
          #viewOverlay .info-item.is-empty .info-val{color:#9ca3af;font-weight:700;font-style:italic;}
          #viewOverlay .text-block.is-empty{color:#9ca3af;font-style:italic;}
          #viewOverlay .file-row{display:flex;align-items:center;justify-content:space-between;gap:10px;background:#f8fafc;border:1px solid rgba(23,148,123,.13);border-radius:12px;padding:10px 12px;margin-bottom:8px;font-weight:700;}
          #viewOverlay .file-row.is-empty{color:#9ca3af;font-style:italic;}
          #viewOverlay .fin-wrap{overflow:auto;border:1px solid rgba(23,148,123,.13);border-radius:12px;margin-bottom:12px;}
          #viewOverlay .fin-table{width:100%;border-collapse:collapse;font-size:.78rem;min-width:640px;}
          #viewOverlay .fin-table th,#viewOverlay .fin-table td{border-bottom:1px solid rgba(23,148,123,.1);padding:7px 8px;text-align:right;}
          #viewOverlay .fin-table th{background:#EAF8F4;color:#16332E;}
          #viewOverlay .fin-table td.is-empty{color:#9ca3af;font-style:italic;}
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

    const EMPTY = 'لم يتم تعبئته';
    const EMPTY_FILE = 'لم يتم إرفاقه';
    const BALANCE_ITEMS = [
      'النقد في الصندوق', 'مدينون', 'بضاعة جاهزة وتحت التشغيل ومواد خام', 'أخرى',
      'مجموع الموجودات المتداولة', 'أراضي / مباني (بالصافي)', 'آلات ومعدات وأثاث وسيارات (بالصافي)',
      'مجموع الموجودات الثابتة', 'مجموع الموجودات', 'دائنون', 'قروض / مطلوبات طويلة الأجل',
      'مجموع المطلوبات', 'رأس المال المدفوع', 'جاري شريك', 'أرباح / خسائر مجمعة', 'أرباح الفترة',
      'صافي حقوق الملكية', 'إجمالي المطلوبات وحقوق الملكية',
    ];
    const INCOME_ITEMS = [
      'المبيعات', 'كلفة المبيعات', 'مجمل الربح', 'مصاريف البيع والتوزيع', 'المصاريف الإدارية والعمومية',
      'إطفاءات واستهلاكات ومخصصات', 'مصاريف أخرى', 'صافي ربح العمليات',
      'صافي الربح / الخسارة قبل الضريبة', 'ضريبة الدخل', 'صافي الربح / الخسارة بعد الضريبة',
    ];
    const DOC_SLOTS = [
      ['activity_invoices', 'فواتير البيع / الشراء أو إثبات النشاط'],
      ['work_license_or_request', 'ترخيص العمل أو طلب الترخيص'],
      ['real_estate_record', 'بيان قيد عقاري للعقار موضوع الضمان'],
      ['bank_statement', 'كشف حساب مصرفي يفيد النشاط'],
    ];

    function isBlank(value) {
      if (value === null || value === undefined) return true;
      if (typeof value === 'string' && value.trim() === '') return true;
      if (Array.isArray(value) && value.every((item) => isBlank(item))) return true;
      return false;
    }

    function displayValue(value, mapped) {
      if (isBlank(value)) return EMPTY;
      return mapped != null ? mapped : String(value);
    }

    function mapLabel(value, map) {
      if (isBlank(value)) return EMPTY;
      return map[value] || String(value);
    }

    function extractExtra(row) {
      const details = row.details || {};
      let extra = details.extra_data;
      if (typeof extra === 'string') {
        try { extra = JSON.parse(extra); } catch (_) { extra = null; }
      }
      if (!extra && details.notes) {
        try { extra = JSON.parse(details.notes); } catch (_) { extra = null; }
      }
      return extra && typeof extra === 'object' ? extra : {};
    }

    function infoItem(label, value, mapped) {
      const text = displayValue(value, mapped);
      const empty = text === EMPTY;
      return `<div class="info-item${empty ? ' is-empty' : ''}"><div class="info-lbl">${esc(label)}</div><div class="info-val">${esc(text)}</div></div>`;
    }

    function textBlock(label, value) {
      const text = displayValue(value);
      const empty = text === EMPTY;
      return `<div class="info-lbl">${esc(label)}</div><div class="text-block${empty ? ' is-empty' : ''}">${esc(text)}</div>`;
    }

    function sectionTitle(title) {
      return `<div class="view-section-title">${esc(title)}</div>`;
    }

    function renderFinancialTable(title, items, sheet) {
      const data = sheet || {};
      const y2023 = Array.isArray(data.y2023) ? data.y2023 : [];
      const y2024 = Array.isArray(data.y2024) ? data.y2024 : [];
      const y2025 = Array.isArray(data.y2025) ? data.y2025 : [];
      const audited = Array.isArray(data.audited) ? data.audited : [];
      const notes = Array.isArray(data.notes) ? data.notes : [];
      const auditMap = { audited: 'مدققة', unaudited: 'غير مدققة' };
      const cell = (v) => {
        const text = displayValue(v);
        return `<td class="${text === EMPTY ? 'is-empty' : ''}">${esc(text)}</td>`;
      };
      const rowsHtml = items.map((item, i) => `<tr>
        <th>${esc(item)}</th>
        ${cell(y2023[i])}
        ${cell(y2024[i])}
        ${cell(y2025[i])}
        ${cell(auditMap[audited[i]] || audited[i])}
        ${cell(notes[i])}
      </tr>`).join('');
      return `${sectionTitle(title)}<div class="fin-wrap"><table class="fin-table">
        <thead><tr><th>البند</th><th>2023</th><th>2024</th><th>2025</th><th>حالة التدقيق</th><th>ملاحظات</th></tr></thead>
        <tbody>${rowsHtml}</tbody>
      </table></div>`;
    }

    function renderDocuments(row) {
      const docs = Array.isArray(row.documents) ? row.documents : [];
      const byType = {};
      docs.forEach((doc) => {
        const type = doc.document_type || 'other';
        if (!byType[type]) byType[type] = [];
        byType[type].push(doc);
      });
      const blocks = [sectionTitle('المرفقات')];
      const used = new Set();
      DOC_SLOTS.forEach(([type, label]) => {
        used.add(type);
        const files = byType[type] || [];
        if (!files.length) {
          blocks.push(`<div class="file-row is-empty"><span>${esc(label)}</span><span>${EMPTY_FILE}</span></div>`);
          return;
        }
        files.forEach((doc) => {
          blocks.push(`<div class="file-row">
            <span><i class="bi bi-paperclip"></i> ${esc(label)} — ${esc(doc.original_name || 'ملف')}</span>
            <button type="button" class="btn-act btn-view" data-action="download-doc" data-app-id="${row.id}" data-doc-id="${doc.id}" data-name="${esc(doc.original_name || 'file')}"><i class="bi bi-download"></i> تنزيل</button>
          </div>`);
        });
      });
      docs.filter((doc) => !used.has(doc.document_type)).forEach((doc) => {
        blocks.push(`<div class="file-row">
          <span><i class="bi bi-paperclip"></i> ${esc(doc.document_type || 'مرفق')} — ${esc(doc.original_name || 'ملف')}</span>
          <button type="button" class="btn-act btn-view" data-action="download-doc" data-app-id="${row.id}" data-doc-id="${doc.id}" data-name="${esc(doc.original_name || 'file')}"><i class="bi bi-download"></i> تنزيل</button>
        </div>`);
      });
      return blocks.join('');
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
      const extra = extractExtra(row);
      const details = row.details || {};
      const gov = row.governorate_name || row.governorate?.name_ar;
      const branch = row.branch_name || row.branch?.name;
      if (viewTitle) viewTitle.textContent = row.application_number || `طلب #${row.id}`;
      if (viewSubtitle) viewSubtitle.textContent = statusLabel(row.status);

      const blocks = [];
      blocks.push(sectionTitle('بيانات الطلب'));
      blocks.push(`<div class="info-grid">
        ${infoItem('رقم الطلب', row.application_number)}
        ${infoItem('الحالة', row.status, statusLabel(row.status))}
        ${infoItem('تاريخ الإرسال', row.submitted_at)}
      </div>`);

      blocks.push(sectionTitle('المشروع'));
      blocks.push(`<div class="info-grid">
        ${infoItem('نمط التمويل', extra.financing_mode || row.financing_mode, mapLabel(extra.financing_mode || row.financing_mode, { islamic: 'إسلامي', conventional: 'تقليدي', both: 'كلاهما' }))}
        ${infoItem('حالة المشروع', extra.project_status || row.project_status, mapLabel(extra.project_status || row.project_status, { existing: 'قائم', new: 'جديد' }))}
        ${infoItem('القطاع', row.project_sector || row.project_type)}
        ${infoItem('رمز النشاط SYRSIC', extra.syrsic_activity_code)}
        ${infoItem('اسم المشروع', row.project_name)}
        ${infoItem('حجم المشروع', row.project_size, mapLabel(row.project_size, { micro: 'متناهي الصغر', small: 'صغير', medium: 'متوسط' }))}
      </div>`);
      blocks.push(textBlock('وصف النشاط', row.description));

      blocks.push(sectionTitle('مقدم الطلب'));
      blocks.push(`<div class="info-grid">
        ${infoItem('الاسم', row.applicant_name)}
        ${infoItem('الهاتف', row.phone)}
        ${infoItem('البريد', row.email)}
        ${infoItem('الرقم الوطني', row.national_id)}
        ${infoItem('المحافظة', gov)}
        ${infoItem('الفرع', branch)}
        ${infoItem('الصفة القانونية', extra.legal_status)}
        ${infoItem('المهنة', extra.profession)}
        ${infoItem('الجنسية السورية', extra.syrian_nationality, mapLabel(extra.syrian_nationality, { yes: 'نعم', no: 'لا', 1: 'نعم', 0: 'لا' }))}
        ${infoItem('المدينة / الموقع', extra.city_or_location)}
      </div>`);

      blocks.push(sectionTitle('طلب التمويل'));
      blocks.push(`<div class="info-grid">
        ${infoItem('الغرض', row.purpose)}
        ${infoItem('المبلغ المطلوب', row.requested_amount, row.requested_amount != null ? formatAmount(row.requested_amount, row.currency) : EMPTY)}
        ${infoItem('العملة', row.currency)}
        ${infoItem('نوع التمويل', row.financing_type, mapLabel(row.financing_type, { capital: 'رأسمالي', working_capital: 'رأس مال عامل', mixed: 'مختلط' }))}
        ${infoItem('الهامش المقترح', extra.proposed_margin)}
        ${infoItem('مدة السداد (شهر)', row.repayment_period_months)}
        ${infoItem('تاريخ أول دفعة', extra.first_payment_date)}
        ${infoItem('قيمة أول دفعة', extra.first_payment_value)}
        ${infoItem('هامش الضمان المقترح', extra.proposed_guarantee_margin)}
      </div>`);
      blocks.push(textBlock('الضمانات المقدمة', extra.provided_guarantees || details.assets_description));
      blocks.push(textBlock('الإجراء عند التعثر', extra.default_case_action));

      blocks.push(sectionTitle('بيانات النشاط'));
      blocks.push(`<div class="info-grid">
        ${infoItem('مرحلة النشاط', row.business_stage, mapLabel(row.business_stage, { idea: 'فكرة', startup: 'ناشئ', existing: 'قائم', expansion: 'توسعة' }))}
        ${infoItem('خبرة صاحب المشروع', extra.owner_experience || details.owner_experience)}
      </div>`);
      blocks.push(textBlock('وصف السوق', extra.market_description || details.market_description));
      blocks.push(textBlock('التحديات', extra.challenges || details.challenges));
      blocks.push(textBlock('الدعم المطلوب', extra.requested_support || details.requested_support));

      blocks.push(sectionTitle('المنشأة والضمانات والمستندات'));
      blocks.push(`<div class="info-grid">
        ${infoItem('اسم الشركة / المنشأة', extra.company_name)}
        ${infoItem('السجل التجاري', extra.commercial_register)}
        ${infoItem('هاتف العمل', extra.business_phone)}
        ${infoItem('نوع الفاتورة / المستند', extra.invoice_type, mapLabel(extra.invoice_type, { sale: 'فاتورة بيع', purchase: 'فاتورة شراء', 'activity-proof': 'إثبات نشاط' }))}
      </div>`);
      blocks.push(textBlock('تفاصيل الكفيل الشخصي', extra.guarantor_details));
      blocks.push(renderDocuments(row));

      blocks.push(renderFinancialTable('الميزانية', BALANCE_ITEMS, extra.balance_sheets));
      blocks.push(renderFinancialTable('قائمة الدخل', INCOME_ITEMS, extra.income_statements));

      blocks.push(sectionTitle('العمالة والتأهيل'));
      blocks.push(`<div class="info-grid">
        ${infoItem('إجمالي العمالة', extra.total_workforce || details.employees_count)}
        ${infoItem('إداريون', extra.admin_employees)}
        ${infoItem('فنيون', extra.technical_employees)}
        ${infoItem('عمال صناعيون', extra.industrial_workers)}
        ${infoItem('دراسات عليا', extra.postgraduate_count)}
        ${infoItem('جامعيون', extra.university_count)}
        ${infoItem('معهد', extra.institute_count)}
        ${infoItem('ثانوية', extra.secondary_count)}
        ${infoItem('دون الثانوية', extra.below_secondary_count)}
        ${infoItem('سبق الحصول على تدريب', extra.has_training_support, mapLabel(extra.has_training_support, { yes: 'نعم', no: 'لا', 1: 'نعم', 0: 'لا' }))}
        ${infoItem('تاريخ التدريب', extra.training_history)}
      </div>`);
      blocks.push(textBlock('احتياج تدريب إداري', extra.admin_training_need));
      blocks.push(textBlock('احتياج تدريب فني', extra.technical_training_need));
      blocks.push(textBlock('احتياج تدريب صناعي', extra.industrial_training_need));
      blocks.push(infoItem('الإقرار بصحة المعلومات', extra.acknowledge_info, extra.acknowledge_info ? 'تم الإقرار' : EMPTY));

      if (viewBody) viewBody.innerHTML = blocks.join('');
      if (viewActions) viewActions.innerHTML = buildViewActions(row);
    }

    async function downloadDocument(appId, docId, name) {
      try {
        const url = window.APP_ROUTES.fundingApplicationDocumentDownload(appId, docId);
        const blob = await window.APP_API.getBlob(url);
        const href = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = href;
        a.download = name || 'file';
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(href), 1500);
      } catch (err) {
        showMessage(apiErrorMessage(err), 'error');
      }
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
        viewBody.innerHTML = '<div class="empty" style="padding:28px"><i class="bi bi-hourglass-split"></i>جاري تحميل بيانات الطلب...</div>';
        if (viewActions) viewActions.innerHTML = '';
        if (viewTitle) viewTitle.textContent = 'عرض الطلب';
      }

      try {
        const showFn = window.APP_ROUTES?.fundingApplicationShow;
        if (!showFn || !window.APP_API?.get) return;
        const res = await window.APP_API.get(showFn(id));
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
      const downloadBtn = e.target.closest('[data-action="download-doc"]');
      if (downloadBtn) {
        e.preventDefault();
        e.stopPropagation();
        downloadDocument(
          Number(downloadBtn.dataset.appId || viewingId),
          Number(downloadBtn.dataset.docId),
          downloadBtn.dataset.name || 'file'
        );
        return;
      }

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
      if (!action || action === 'view' || action === 'download-doc') return;
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
