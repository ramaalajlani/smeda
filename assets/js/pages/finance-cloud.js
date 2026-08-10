document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: false });
  if (!ok) return;

  const canManage = window.FinancePlatform.canViewApplications();

  const grid = document.getElementById('applicantGrid');
  const tableBody = document.getElementById('tableBody');
  const tableView = document.getElementById('tableView');
  const emptyNote = document.getElementById('emptyNote');
  const visibleCount = document.getElementById('visibleCount');
  const totalRequests = document.getElementById('totalRequests');
  const loading = document.getElementById('financeCloudLoading');
  const messageBox = document.getElementById('financeCloudMessage');
  const isReadOnly = window.FinancePlatform.isReadOnly();

  const searchInput = document.getElementById('searchInput');
  const sectorFilter = document.getElementById('sectorFilter');
  const financeModeFilter = document.getElementById('financeModeFilter');
  const projectStatusFilter = document.getElementById('projectStatusFilter');
  const readinessFilter = document.getElementById('readinessFilter');
  const riskFilter = document.getElementById('riskFilter');
  const applyBtn = document.getElementById('applyFiltersBtn');
  const resetBtn = document.getElementById('resetFiltersBtn');

  const modalBackdrop = document.getElementById('fileModalBackdrop');
  const modal = document.getElementById('fileModal');
  const closeModalBtn = document.getElementById('closeModalBtn');
  const modalTitle = document.getElementById('modalTitle');
  const modalSubtitle = document.getElementById('modalSubtitle');
  const panes = {
    overviewPane: document.getElementById('overviewPane'),
    applicationPane: document.getElementById('applicationPane'),
    journeyPane: document.getElementById('journeyPane'),
    documentsPane: document.getElementById('documentsPane'),
    evaluationPane: document.getElementById('evaluationPane'),
  };

  let applicants = [];

  function showMessage(text, type = 'success') {
    if (!messageBox) return;
    messageBox.className = `alert alert-${type === 'error' ? 'danger' : 'success'} container mt-3`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  function mapRow(row) {
    const status = row.status || 'submitted';
    const details = row.details || {};
    let extra = details.extra_data;
    if (!extra && details.notes) {
      try { extra = JSON.parse(details.notes); } catch (_) { extra = {}; }
    }
    extra = extra || {};

    const financingMode = row.financing_mode || extra.financing_mode || '';
    const projectStatus = row.project_status || extra.project_status
      || (row.business_stage === 'existing' || row.business_stage === 'expansion' ? 'existing' : 'new');

    const modeLabels = {
      islamic: 'إسلامي',
      conventional: 'تقليدي',
      both: 'إسلامي / تقليدي',
    };
    const sectorLabels = {
      agricultural: 'زراعي',
      industrial: 'صناعي',
      commercial: 'تجاري',
      service: 'خدمي',
    };
    const statusLabels = {
      existing: 'قائم',
      new: 'غير قائم',
    };

    const readiness = ['approved', 'funded'].includes(status)
      ? 'ready'
      : (['funder_review', 'consultant_priced'].includes(status) ? 'review' : 'pending');

    return {
      id: row.id,
      application_number: row.application_number,
      name: row.project_name,
      applicant: row.applicant_name,
      location: row.governorate_name || row.governorate?.name_ar || '—',
      branch: row.branch_name || row.branch?.name || '—',
      sector: row.project_sector || '',
      sectorAr: sectorLabels[row.project_sector] || row.project_sector || '—',
      mode: financingMode,
      modeAr: modeLabels[financingMode] || financingMode || '—',
      projectStatus,
      projectStatusAr: statusLabels[projectStatus] || projectStatus || '—',
      status,
      statusAr: window.FinancePlatform.statusLabel(status),
      readiness,
      readinessAr: readiness === 'ready' ? 'جاهز للتمويل': (readiness === 'review' ? 'قيد المراجعة': SiteI18n.ta('بحاجة استكمال')),
      risk: readiness === 'ready' ? 'low' : (readiness === 'review' ? 'medium' : 'high'),
      riskAr: readiness === 'ready' ? 'منخفض': (readiness === 'review' ? 'متوسط': SiteI18n.ta('مرتفع')),
      match: readiness === 'ready' ? 88 : (readiness === 'review' ? 72 : 55),
      amount: window.FinancePlatform.formatAmount(row.requested_amount, row.currency),
      purpose: row.purpose || row.description || '—',
      raw: row,
    };
  }

  async function loadApplicants() {
    loading?.classList.remove('d-none');
    try {
      let rows = [];
      try {
        const cloudRes = await window.APP_API.get(window.APP_ROUTES.fundingCloud({ per_page: 100 }));
        rows = cloudRes.data || [];
      } catch (_) {
        const res = await window.APP_API.get(window.APP_ROUTES.fundingApplications({ per_page: 100 }));
        rows = (res.data || []).filter((r) => ['approved', 'funded'].includes(r.status));
      }
      applicants = rows.map(mapRow);
      if (totalRequests) totalRequests.textContent = `${applicants.length} طلب`;
      filterAndRender();
    } catch (err) {
      showMessage(err.message || SiteI18n.ta('تعذر تحميل الطلبات من API.'), 'error');
    } finally {
      loading?.classList.add('d-none');
    }
  }

  function readinessClass(item) {
    if (item.readiness === 'ready') return 'ready';
    if (item.readiness === 'review') return 'review';
    return 'pending';
  }

  function riskClass(item) {
    if (item.risk === 'low') return 'ready';
    if (item.risk === 'medium') return 'pending';
    return 'danger';
  }

  function iconForSector(sector) {
    const map = {
      industrial: 'fa-industry',
      agricultural: 'fa-seedling',
      commercial: 'fa-store',
      service: 'fa-briefcase',
      digital: 'fa-laptop-code',
      tourist: 'fa-hotel',
    };
    return map[sector] || 'fa-folder';
  }

  function actionButtons(item) {
    if (isReadOnly || !canManage) return '';
    const row = item.raw;
    let html = `<a href="finance-apply.php?id=${row.id}" class="btn btn-sm btn-outline-primary">عرض</a>`;
    if (window.AppAuth.hasPermission('finance.applications.assign_partner') || window.AppAuth.isNationalAdmin()) {
      html += ` <button class="btn btn-sm btn-outline-success" data-action="assign-partner" data-id="${row.id}">إحالة تمويل</button>`;
    }
    if (window.AppAuth.hasRole('funding_partner')) {
      const pid = row.partner_assignments?.[0]?.id;
      if (pid) html += ` <button class="btn btn-sm btn-warning" data-action="partner-decision" data-assignment="${pid}">قرار</button>`;
    }
    if (window.AppAuth.hasPermission('finance.applications.review_branch') && ['submitted', 'branch_review'].includes(row.status)) {
      html += ` <button class="btn btn-sm btn-outline-dark" data-action="branch-review" data-id="${row.id}">مراجعة فرع</button>`;
    }
    return html;
  }

  function renderCards(list) {
    if (!grid) return;
    grid.innerHTML = list.map((item) => `
      <article class="applicant-card" data-id="${item.id}">
        <div class="applicant-head">
          <div>
            <h3 class="applicant-name"><i class="fa-solid ${iconForSector(item.sector)} ms-1" style="color:var(--fc-brand)"></i>${item.name}</h3>
            <div class="applicant-meta">${item.application_number} / ${item.location} / ${item.branch}</div>
          </div>
          <div class="match-circle" style="--score:${item.match}%;"><strong>${item.match}%</strong><span>مطابقة</span></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <span class="status-chip ${readinessClass(item)}">${item.readinessAr}</span>
          <span class="status-chip ${riskClass(item)}">مخاطر ${item.riskAr}</span>
          <span class="status-chip neutral">${item.statusAr}</span>
        </div>
        <div class="info-grid">
          <div class="info-box"><span>القطاع</span><strong>${item.sectorAr}</strong></div>
          <div class="info-box"><span>نوع التمويل</span><strong>${item.modeAr}</strong></div>
          <div class="info-box"><span>قيمة التمويل</span><strong>${item.amount}</strong></div>
          <div class="info-box"><span>مقدم الطلب</span><strong>${item.applicant}</strong></div>
        </div>
        <div class="risk-row">
          <div class="risk-item"><strong>الغاية:</strong> ${item.purpose}</div>
          <div class="risk-item"><strong>حالة المشروع:</strong> ${item.projectStatusAr}</div>
        </div>
        <div class="applicant-actions">${actionButtons(item)}</div>
      </article>`).join('');
  }

  function renderTable(list) {
    if (!tableBody) return;
    tableBody.innerHTML = list.map((item) => `
      <tr>
        <td><strong>${item.name}</strong><div class="text-muted small fw-bold mt-1">${item.application_number} / ${item.location}</div></td>
        <td>${item.sectorAr}</td>
        <td>${item.modeAr}</td>
        <td>${item.amount}</td>
        <td><span class="status-chip ${readinessClass(item)}">${item.readinessAr}</span></td>
        <td><strong>${item.match}%</strong></td>
        <td class="text-nowrap">${actionButtons(item)} <button class="btn btn-brand btn-sm open-file-btn" type="button" data-id="${item.id}">فتح</button></td>
      </tr>`).join('');
  }

  function getFilteredList() {
    const searchValue = (searchInput?.value || '').trim().toLowerCase();
    const sectorValue = sectorFilter?.value || '';
    const modeValue = financeModeFilter?.value || '';
    const statusValue = projectStatusFilter?.value || '';
    const readinessValue = readinessFilter?.value || '';
    const riskValue = riskFilter?.value || '';

    return applicants.filter((item) => {
      const haystack = [item.name, item.applicant, item.location, item.sectorAr, item.purpose, item.application_number].join(' ').toLowerCase();
      const matchSearch = !searchValue || haystack.includes(searchValue);
      const matchSector = !sectorValue || item.sector === sectorValue;
      const matchMode = !modeValue || item.mode === modeValue;
      const matchStatus = !statusValue || item.projectStatus === statusValue;
      const matchReadiness = !readinessValue || item.readiness === readinessValue;
      const matchRisk = !riskValue || item.risk === riskValue;
      return matchSearch && matchSector && matchMode && matchStatus && matchReadiness && matchRisk;
    });
  }

  function filterAndRender() {
    const list = getFilteredList();
    renderCards(list);
    renderTable(list);
    if (visibleCount) visibleCount.textContent = `${list.length} طلبات معروضة`;
    if (emptyNote) emptyNote.style.display = list.length === 0 ? 'block' : 'none';
    if (grid) grid.style.display = list.length === 0 ? 'none' : '';
    if (tableView) {
      tableView.style.display = tableView.classList.contains('active-view') && list.length > 0 ? 'block' : 'none';
    }
  }

  function detailRow(label, value) {
    return `<div class="detail-row"><span>${label}</span><strong>${value || '—'}</strong></div>`;
  }

  function openFile(id) {
    const item = applicants.find((x) => String(x.id) === String(id));
    if (!item || !modal) return;

    modalTitle.textContent = item.name;
    modalSubtitle.textContent = `${item.application_number} / ${item.location} / ${item.statusAr}`;

    panes.overviewPane.innerHTML = `<div class="detail-card"><h4>ملخص الملف</h4><div class="detail-list">
      ${detailRow(SiteI18n.ta('رقم الطلب'), item.application_number)}
      ${detailRow(SiteI18n.ta('المشروع'), item.name)}
      ${detailRow(SiteI18n.ta('مقدم الطلب'), item.applicant)}
      ${detailRow(SiteI18n.ta('الفرع'), item.branch)}
      ${detailRow(SiteI18n.ta('المبلغ'), item.amount)}
      ${detailRow(SiteI18n.ta('الحالة'), item.statusAr)}
      ${detailRow(SiteI18n.ta('نوع التمويل'), item.modeAr)}
      ${detailRow(SiteI18n.ta('حالة المشروع'), item.projectStatusAr)}
    </div></div>`;

    panes.applicationPane.innerHTML = `<div class="detail-card"><h4>نموذج الطلب</h4><div class="detail-list">
      ${detailRow(SiteI18n.ta('الغاية'), item.purpose)}
      ${detailRow(SiteI18n.ta('نوع التمويل'), item.modeAr)}
      ${detailRow(SiteI18n.ta('القطاع'), item.sectorAr)}
      ${detailRow(SiteI18n.ta('حالة المشروع'), item.projectStatusAr)}
    </div></div>`;

    panes.journeyPane.innerHTML = `<div class="journey">
      <div class="journey-step"><div class="num">1</div><strong>تقديم</strong><span>تعبئة الطلب من صاحب المشروع.</span></div>
      <div class="journey-step"><div class="num">2</div><strong>مراجعة</strong><span>مراجعة أولية من الفرع/الإدارة.</span></div>
      <div class="journey-step"><div class="num">3</div><strong>سحابة التمويل</strong><span>عرض الملف مع فلترة القطاع ونوع التمويل.</span></div>
      <div class="journey-step"><div class="num">4</div><strong>جهة تمويل</strong><span>دراسة وقرار تمويل.</span></div>
    </div>`;

    panes.documentsPane.innerHTML = `<div class="detail-card"><p class="text-muted">المرفقات تُعرض عبر API محمي بعد فتح الطلب من صفحة التفاصيل.</p></div>`;
    panes.evaluationPane.innerHTML = `<div class="detail-card"><div class="detail-list">${detailRow(SiteI18n.ta('درجة المطابقة'), `${item.match}%`)}${detailRow(SiteI18n.ta('مستوى المخاطر'), item.riskAr)}</div></div>`;

    modalBackdrop?.classList.add('show');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modalBackdrop?.classList.remove('show');
    modal?.classList.remove('show');
    document.body.style.overflow = '';
  }

  document.addEventListener('click', async (e) => {
    const openBtn = e.target.closest('.open-file-btn');
    if (openBtn) openFile(openBtn.dataset.id);

    const tab = e.target.closest('.file-tab');
    if (tab) {
      document.querySelectorAll('.file-tab').forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');
      Object.values(panes).forEach((p) => p?.classList.remove('active'));
      panes[tab.dataset.pane]?.classList.add('active');
    }

    const viewBtn = e.target.closest('.view-btn');
    if (viewBtn) {
      document.querySelectorAll('.view-btn').forEach((btn) => btn.classList.remove('active'));
      viewBtn.classList.add('active');
      if (viewBtn.dataset.view === 'table') {
        grid.style.display = 'none';
        tableView?.classList.add('active-view');
        tableView.style.display = getFilteredList().length ? 'block' : 'none';
      } else {
        tableView?.classList.remove('active-view');
        tableView.style.display = 'none';
        grid.style.display = getFilteredList().length ? '' : 'none';
      }
    }

    const btn = e.target.closest('[data-action]');
    if (!btn || isReadOnly) return;

    const action = btn.dataset.action;
    const appId = btn.dataset.id;
    const assignmentId = btn.dataset.assignment;

    try {
      if (action === 'assign-partner') {
        const partnerId = prompt(SiteI18n.ta('أدخل رقم جهة التمويل:'));
        if (!partnerId) return;
        await window.APP_API.post(window.APP_ROUTES.fundingApplicationAssignPartner(appId), { funding_partner_id: Number(partnerId) });
        showMessage(SiteI18n.ta('تمت الإحالة لجهة التمويل.'));
      }
      if (action === 'partner-decision' && assignmentId) {
        const decision = prompt(SiteI18n.ta('القرار (approved/rejected):'), 'approved');
        if (!decision) return;
        await window.APP_API.post(window.APP_ROUTES.fundingPartnerAssignmentDecision(assignmentId), {
          decision,
          approved_amount: decision === 'approved' ? Number(prompt(SiteI18n.ta('المبلغ المعتمد:')) || 0) : null,
        });
        showMessage(SiteI18n.ta('تم تسجيل قرار جهة التمويل.'));
      }
      if (action === 'branch-review') {
        const decision = prompt(SiteI18n.ta('قرار المراجعة (approve/needs_completion/reject):'), 'approve');
        if (!decision) return;
        await window.APP_API.post(window.APP_ROUTES.fundingApplicationBranchReview(appId), { decision });
        showMessage(SiteI18n.ta('تمت مراجعة الفرع.'));
      }
      await loadApplicants();
    } catch (err) {
      showMessage(err.message || SiteI18n.ta('تعذر تنفيذ الإجراء.'), 'error');
    }
  });

  closeModalBtn?.addEventListener('click', closeModal);
  modalBackdrop?.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  [searchInput, sectorFilter, financeModeFilter, projectStatusFilter, readinessFilter, riskFilter].forEach((el) => {
    el?.addEventListener(el?.tagName === 'INPUT' ? 'input' : 'change', filterAndRender);
  });
  applyBtn?.addEventListener('click', filterAndRender);
  resetBtn?.addEventListener('click', () => {
    if (searchInput) searchInput.value = '';
    [sectorFilter, financeModeFilter, projectStatusFilter, readinessFilter, riskFilter].forEach((el) => { if (el) el.value = ''; });
    filterAndRender();
  });

  await loadApplicants();
});
