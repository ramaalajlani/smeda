document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const cardsContainer = document.getElementById('dashboardCards');
  const loadingBox     = document.getElementById('dashboardLoadingBox');
  if (!cardsContainer || !loadingBox) return;

  const safe = (v, fallback = 0) => (v === undefined || v === null || v === '') ? fallback : v;
  const hp   = p => !!(window.AppAuth?.hasPermission?.(p));
  const chartInstances = [];

  /* ── Card icon + link mapping ── */
  const CARD_META = {
    'المحافظات':               { icon: 'bi-map-fill',             href: 'services/admin/admin-branches.php' },
    'الفروع':                  { icon: 'bi-diagram-2-fill',        href: 'services/admin/admin-branches.php' },
    'الفروع النشطة':           { icon: 'bi-check-circle-fill',     href: 'services/admin/admin-branches.php' },
    'إجمالي الدورات':          { icon: 'bi-book-half',             href: 'services/training/training-courses-list.php' },
    'طلبات قيد المراجعة':     { icon: 'bi-clipboard2-check-fill', href: 'services/training/registration-requests-review.php' },
    'فرعي':                    { icon: 'bi-building-fill',         href: 'services/admin/admin-branches.php' },
    'إجمالي المستخدمين':      { icon: 'bi-people-fill',           href: 'services/admin/admin-users.php' },
    'المستخدمون الفعالون':    { icon: 'bi-person-check-fill',     href: 'services/admin/admin-users.php' },
    'المستخدمون المعطلون':    { icon: 'bi-person-x-fill',         href: 'services/admin/admin-users.php' },
    'المراكز التدريبية':       { icon: 'bi-buildings-fill',        href: 'services/training/training-centers-list.php' },
    'المدربون':                { icon: 'bi-person-workspace',      href: 'services/training/training-trainers-list.php' },
    'المتدربون':               { icon: 'bi-people-fill',           href: 'services/training/training-trainees-list.php' },
    'الدورات':                 { icon: 'bi-book-half',             href: 'services/training/training-courses-list.php' },
    'الدورات النشطة':          { icon: 'bi-play-circle-fill',      href: 'services/training/training-courses-list.php' },
    'الدورات المكتملة':        { icon: 'bi-check2-all',            href: 'services/training/training-courses-list.php' },
    'إجمالي الشهادات':        { icon: 'bi-award-fill',            href: 'services/training/training-certificates-list.php' },
    'شهادات بانتظار الاعتماد':{ icon: 'bi-hourglass-split',       href: 'services/training/training-certificates-approve.php' },
    'بانتظار اعتماد المركز':  { icon: 'bi-building-check',        href: 'services/training/training-certificates-approve.php' },
    'بانتظار قسم التدريب':    { icon: 'bi-clock-history',         href: 'services/training/training-certificates-approve.php' },
    'بانتظار نائب المدير':    { icon: 'bi-person-fill-up',        href: 'services/training/training-certificates-approve.php' },
    'بانتظار اعتماد المدير العام': { icon: 'bi-person-fill-check',     href: 'services/training/training-certificates-approve.php' },
    'الشهادات المعتمدة':      { icon: 'bi-patch-check-fill',      href: 'services/training/training-certificates-list.php' },
    'الشهادات المرفوضة':      { icon: 'bi-x-circle-fill',         href: 'services/training/training-certificates-list.php' },
    'ملفي المهني':             { icon: 'bi-person-badge-fill',     href: 'services/training/my-trainer-profile.php' },
    'ترشيحات الحقائب':        { icon: 'bi-briefcase-fill',        href: 'services/training/my-training-kit-nominations.php' },
    'بانتظار مراجعة الترشيحات':{ icon: 'bi-star-half',           href: 'services/training/training-kit-nominations-review.php' },
    'طلبات المراكز':           { icon: 'bi-building-add',          href: 'services/training/registration-requests-review.php' },
    'طلبات المدربين':          { icon: 'bi-person-plus-fill',      href: 'services/training/registration-requests-review.php' },
    'طلبات المتدربين':         { icon: 'bi-person-badge-fill',     href: 'services/training/registration-requests-review.php' },
    'طلبات الدورات الخاصة بي':{ icon: 'bi-journal-plus',          href: 'services/training/course-registration-request.php' },
    'طلبات تحتاج تأكيد':      { icon: 'bi-check2-square',         href: 'services/training/registration-requests-review.php' },
    'طلبات تحتاج إكمال':      { icon: 'bi-pencil-square',         href: 'services/training/registration-requests-review.php' },
    'إجمالي طلبات التسجيل':   { icon: 'bi-clipboard-data-fill',   href: 'services/training/registration-requests-review.php' },
    'طلبات الفرع قيد المراجعة':{ icon: 'bi-inbox-fill',           href: 'services/training/registration-requests-review.php' },
    'البيانات':                { icon: 'bi-database-fill',         href: null },
    'طلبات التمويل':          { icon: 'bi-bank2',                 href: 'services/finance/finance-cloud.php' },
    'طلبات الاستشارات':       { icon: 'bi-headset',               href: 'services/consulting/consulting-requests-list.php' },
    'احتياجات GIS':           { icon: 'bi-geo-alt-fill',          href: 'services/gis/needs-list.php' },
    'فرص العمل':              { icon: 'bi-briefcase-fill',        href: 'services/workforce/jobs-list.php' },
  };

  const STATUS_AR = {
    approved: 'معتمدة',
    rejected: 'مرفوضة',
    pending: 'قيد المراجعة',
    submitted: 'مقدّمة',
    under_review: 'قيد الدراسة',
    draft: 'مسودة',
    closed: 'مغلقة',
    cancelled: 'ملغاة',
    new: 'جديدة',
    open: 'مفتوحة',
    in_progress: 'قيد التنفيذ',
    completed: 'مكتملة',
    published: 'منشورة',
    resolved: 'محلولة',
    archived: 'مؤرشفة',
    classified: 'مصنّفة',
    pending_governorate_review: 'بانتظار مراجعة المحافظة',
    returned_for_edit: 'معادة للتعديل',
    pending_branch_approval: 'بانتظار موافقة الفرع',
    pending_center_approval: 'بانتظار المركز',
    pending_training_approval: 'بانتظار التدريب',
    pending_deputy_approval: 'بانتظار نائب المدير',
    pending_general_director_approval: 'بانتظار المدير العام',
  };

  const CHART_COLORS = [
    '#17947b', '#0f5e4f', '#06aa89', '#f59e0b', '#3b82f6',
    '#8b5cf6', '#ef4444', '#14b8a6', '#64748b', '#ec4899',
  ];

  function getCardMeta(title) {
    return CARD_META[title] || { icon: 'bi-bar-chart-fill', href: null };
  }

  function pushKpiCard(cards, title, permission, dataKey, data) {
    if (!hp(permission)) return;
    if (!Object.prototype.hasOwnProperty.call(data, dataKey)) return;
    cards.push({ title, value: safe(data[dataKey]) });
  }

  function isAnalyticsDashboard(data) {
    return window.AppAuth?.isNationalAdmin?.()
      || ['national', 'national_executive'].includes(data?.scope)
      || ['general_director', 'admin', 'super_admin'].includes(data?.dashboard_role);
  }

  /* ── Build card list by permissions ── */
  function getCards(data) {
    const cards = [];

    if (window.AppAuth.isNationalAdmin()) {
      cards.push({ title: SiteI18n.ta('المحافظات'),           value: safe(data.governorates_count ?? 14) });
      cards.push({ title: SiteI18n.ta('الفروع'),              value: safe(data.branches_count) });
      cards.push({ title: SiteI18n.ta('الفروع النشطة'),       value: safe(data.branches_active) });
      cards.push({ title: SiteI18n.ta('إجمالي الدورات'),      value: safe(data.courses_total ?? data.courses) });
      cards.push({ title: SiteI18n.ta('طلبات قيد المراجعة'), value: safe(data.registration_requests_pending) });
    }

    if (window.AppAuth.isBranchManager()) {
      cards.push({ title: SiteI18n.ta('فرعي'),                      value: data.branch_name || '—' });
      cards.push({ title: SiteI18n.ta('طلبات الفرع قيد المراجعة'), value: safe(data.registration_requests_pending) });
    }

    if (hp(window.AppPermissions.VIEW_USERS)) {
      cards.push({ title: SiteI18n.ta('إجمالي المستخدمين'),   value: safe(data.users_total) });
      cards.push({ title: SiteI18n.ta('المستخدمون الفعالون'), value: safe(data.users_active) });
      cards.push({ title: SiteI18n.ta('المستخدمون المعطلون'), value: safe(data.users_inactive) });
    }

    if (hp(window.AppPermissions.VIEW_CENTERS))   cards.push({ title: SiteI18n.ta('المراكز التدريبية'), value: safe(data.centers) });
    if (hp(window.AppPermissions.VIEW_TRAINERS))  cards.push({ title: SiteI18n.ta('المدربون'),          value: safe(data.trainers) });
    if (hp(window.AppPermissions.VIEW_TRAINEES))  cards.push({ title: SiteI18n.ta('المتدربون'),         value: safe(data.trainees) });

    if (hp(window.AppPermissions.VIEW_COURSES)) {
      cards.push({ title: SiteI18n.ta('الدورات'),          value: safe(data.courses) });
      cards.push({ title: SiteI18n.ta('الدورات النشطة'),   value: safe(data.courses_active) });
      cards.push({ title: SiteI18n.ta('الدورات المكتملة'), value: safe(data.courses_completed) });
    }

    if (hp(window.AppPermissions.VIEW_CERTIFICATES)) {
      cards.push({ title: SiteI18n.ta('إجمالي الشهادات'),   value: safe(data.certificates_total ?? data.certificates) });
      cards.push({ title: SiteI18n.ta('الشهادات المعتمدة'), value: safe(data.certificates_approved) });
      cards.push({ title: SiteI18n.ta('الشهادات المرفوضة'), value: safe(data.certificates_rejected) });
    }

    if (hp(window.AppPermissions.VIEW_CERTIFICATE_APPROVALS)
      && Object.prototype.hasOwnProperty.call(data, 'certificates_pending')) {
      cards.push({ title: SiteI18n.ta('شهادات بانتظار الاعتماد'), value: safe(data.certificates_pending) });
    }
    pushKpiCard(cards, SiteI18n.ta('بانتظار اعتماد المركز'), window.AppPermissions.APPROVE_CENTER_CERTIFICATES, 'certificates_pending_center', data);
    pushKpiCard(cards, SiteI18n.ta('بانتظار قسم التدريب'), window.AppPermissions.APPROVE_TRAINING_CERTIFICATES, 'certificates_pending_training', data);
    pushKpiCard(cards, SiteI18n.ta('بانتظار نائب المدير'), window.AppPermissions.APPROVE_DEPUTY_CERTIFICATES, 'certificates_pending_deputy', data);
    pushKpiCard(cards, SiteI18n.ta('بانتظار اعتماد المدير العام'), window.AppPermissions.APPROVE_GENERAL_DIRECTOR_CERTIFICATES, 'certificates_pending_general_director', data);

    if (hp(window.AppPermissions.EDIT_OWN_TRAINER_PROFILE) || hp(window.AppPermissions.VIEW_TRAINER_PROFILES))
      cards.push({ title: SiteI18n.ta('ملفي المهني'), value: SiteI18n.ta('جاهز') });

    if (hp(window.AppPermissions.NOMINATE_TRAINING_KITS))
      cards.push({ title: SiteI18n.ta('ترشيحات الحقائب'), value: safe(data.training_kit_nominations_my_count) });
    if (hp(window.AppPermissions.REVIEW_TRAINING_KIT_NOMINATIONS))
      cards.push({ title: SiteI18n.ta('بانتظار مراجعة الترشيحات'), value: safe(data.training_kit_nominations_pending) });
    if (hp(window.AppPermissions.REVIEW_CENTER_REGISTRATION_REQUESTS))
      cards.push({ title: SiteI18n.ta('طلبات المراكز'), value: safe(data.center_registration_requests_pending) });
    if (hp(window.AppPermissions.REVIEW_TRAINER_REGISTRATION_REQUESTS))
      cards.push({ title: SiteI18n.ta('طلبات المدربين'), value: safe(data.trainer_registration_requests_pending) });
    if (hp(window.AppPermissions.REVIEW_TRAINEE_REGISTRATION_REQUESTS))
      cards.push({ title: SiteI18n.ta('طلبات المتدربين'), value: safe(data.trainee_registration_requests_pending) });
    if (hp(window.AppPermissions.CREATE_COURSE_REGISTRATION_REQUESTS))
      cards.push({ title: SiteI18n.ta('طلبات الدورات الخاصة بي'), value: safe(data.course_registration_requests_my_count) });
    if (hp(window.AppPermissions.CONFIRM_COURSE_REGISTRATION_REQUESTS))
      cards.push({ title: SiteI18n.ta('طلبات تحتاج تأكيد'), value: safe(data.course_registration_requests_need_confirmation) });
    if (hp(window.AppPermissions.COMPLETE_COURSE_REGISTRATION_REQUESTS))
      cards.push({ title: SiteI18n.ta('طلبات تحتاج إكمال'), value: safe(data.course_registration_requests_need_completion) });
    if (hp(window.AppPermissions.VIEW_REGISTRATION_REQUESTS))
      cards.push({ title: SiteI18n.ta('إجمالي طلبات التسجيل'), value: safe(data.registration_requests_total) });

    if (hp('finance.applications.view'))
      cards.push({ title: SiteI18n.ta('طلبات التمويل'), value: safe(data.funding_applications_total) });
    if (data.consulting_requests_total !== undefined)
      cards.push({ title: SiteI18n.ta('طلبات الاستشارات'), value: safe(data.consulting_requests_total) });
    if (data.needs_total !== undefined && (hp('needs.view') || hp('needs.dashboard')))
      cards.push({ title: SiteI18n.ta('احتياجات GIS'), value: safe(data.needs_total) });
    if (data.job_postings_published !== undefined)
      cards.push({ title: SiteI18n.ta('فرص العمل'), value: safe(data.job_postings_published) });

    return cards;
  }

  /* ── Render cards ── */
  function renderCards(cards) {
    const base = window.APP_CONFIG?.FRONTEND_BASE_URL || '';

    const clickable = cards
      .map(card => ({ card, meta: getCardMeta(card.title) }))
      .filter(({ meta }) => !!meta.href);

    if (!clickable.length) {
      cardsContainer.innerHTML = `
        <div class="col-12">
          <div class="text-center text-muted bg-white rounded-4 border p-4">لا توجد بيانات لعرضها.</div>
        </div>`;
      return;
    }

    cardsContainer.innerHTML = clickable.map(({ card, meta }) => {
      const { icon, href } = meta;
      const url = `${base}/${href}`;

      return `
        <div class="col-6 col-md-4 col-xl-3">
          <a href="${url}" class="stat-card h-100">
            <div class="stat-card-icon-wrap"><i class="bi ${icon}"></i></div>
            <div class="stat-card-value">${window.APP_HELPERS.e(window.APP_HELPERS.safe(card.value, 0))}</div>
            <div class="stat-card-label">${window.APP_HELPERS.e(window.APP_HELPERS.safe(card.title))}</div>
          </a>
        </div>`;
    }).join('');
  }

  function statusLabel(status) {
    const key = String(status || '');
    return STATUS_AR[key] || key.replace(/_/g, ' ') || 'أخرى';
  }

  function destroyCharts() {
    while (chartInstances.length) {
      const c = chartInstances.pop();
      try { c.destroy(); } catch (_) { /* ignore */ }
    }
  }

  function makeChart(canvasId, config) {
    const el = document.getElementById(canvasId);
    if (!el || typeof Chart === 'undefined') return null;
    const chart = new Chart(el, config);
    chartInstances.push(chart);
    return chart;
  }

  function doughnutDefaults() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: window.matchMedia('(max-width: 767.98px)').matches ? 'bottom' : 'right',
          rtl: true,
          labels: { boxWidth: 12, padding: 12, font: { family: 'inherit', size: 11 } },
        },
        tooltip: { rtl: true, titleFont: { family: 'inherit' }, bodyFont: { family: 'inherit' } },
      },
    };
  }

  function renderNationalAnalytics(data) {
    const panel = document.getElementById('nationalAnalytics');
    const activityCol = document.getElementById('dashActivityCol');
    const shortcutsCol = document.getElementById('dashShortcutsCol');
    if (!panel) return;

    if (!isAnalyticsDashboard(data) || !data.analytics) {
      panel.classList.add('d-none');
      return;
    }

    panel.classList.remove('d-none');
    if (activityCol) activityCol.classList.add('d-none');
    if (shortcutsCol) {
      shortcutsCol.classList.remove('col-xl-7');
      shortcutsCol.classList.add('col-12');
    }

    const bannerTitle = document.getElementById('dashBannerTitle');
    const bannerSub = document.getElementById('dashBannerSub');
    if (bannerTitle && data.dashboard_title) bannerTitle.textContent = data.dashboard_title;
    if (bannerSub) {
      bannerSub.textContent = 'لوحة مؤشرات تفاعلية لأداء المنصة على المستوى الوطني — متجاوبة مع الموبايل.';
    }

    destroyCharts();
    const analytics = data.analytics;
    const isMobile = window.matchMedia('(max-width: 767.98px)').matches;

    const modules = Array.isArray(analytics.modules) ? analytics.modules : [];
    makeChart('chartModules', {
      type: 'doughnut',
      data: {
        labels: modules.map(m => m.label),
        datasets: [{
          data: modules.map(m => Number(m.value) || 0),
          backgroundColor: CHART_COLORS.slice(0, modules.length),
          borderWidth: 0,
          hoverOffset: 6,
        }],
      },
      options: doughnutDefaults(),
    });

    const certPipe = Array.isArray(analytics.certificates_pipeline) ? analytics.certificates_pipeline : [];
    makeChart('chartCertificates', {
      type: 'doughnut',
      data: {
        labels: certPipe.map(m => m.label),
        datasets: [{
          data: certPipe.map(m => Number(m.value) || 0),
          backgroundColor: ['#17947b', '#f59e0b', '#ef4444'],
          borderWidth: 0,
          hoverOffset: 6,
        }],
      },
      options: doughnutDefaults(),
    });

    const funding = Array.isArray(analytics.funding_by_status) ? analytics.funding_by_status : [];
    makeChart('chartFunding', {
      type: 'doughnut',
      data: {
        labels: funding.map(m => statusLabel(m.status)),
        datasets: [{
          data: funding.map(m => Number(m.total) || 0),
          backgroundColor: CHART_COLORS.slice(0, Math.max(funding.length, 1)),
          borderWidth: 0,
          hoverOffset: 6,
        }],
      },
      options: doughnutDefaults(),
    });

    const needs = Array.isArray(analytics.needs_by_status) ? analytics.needs_by_status : [];
    makeChart('chartNeeds', {
      type: 'doughnut',
      data: {
        labels: needs.map(m => statusLabel(m.status)),
        datasets: [{
          data: needs.map(m => Number(m.total) || 0),
          backgroundColor: CHART_COLORS.slice(0, Math.max(needs.length, 1)),
          borderWidth: 0,
          hoverOffset: 6,
        }],
      },
      options: doughnutDefaults(),
    });

    const byGov = Array.isArray(analytics.by_governorate) ? analytics.by_governorate : [];
    makeChart('chartGovernorates', {
      type: 'bar',
      data: {
        labels: byGov.map(g => g.name),
        datasets: [
          { label: 'الدورات', data: byGov.map(g => Number(g.courses) || 0), backgroundColor: '#17947b', borderRadius: 6 },
          { label: 'المتدربون', data: byGov.map(g => Number(g.trainees) || 0), backgroundColor: '#3b82f6', borderRadius: 6 },
          { label: 'الاحتياجات', data: byGov.map(g => Number(g.needs) || 0), backgroundColor: '#f59e0b', borderRadius: 6 },
          { label: 'التمويل', data: byGov.map(g => Number(g.funding) || 0), backgroundColor: '#8b5cf6', borderRadius: 6 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            position: 'top',
            rtl: true,
            labels: { boxWidth: 12, padding: isMobile ? 8 : 14, font: { family: 'inherit', size: isMobile ? 10 : 12 } },
          },
          tooltip: { rtl: true, titleFont: { family: 'inherit' }, bodyFont: { family: 'inherit' } },
        },
        scales: {
          x: {
            ticks: {
              maxRotation: isMobile ? 60 : 40,
              minRotation: isMobile ? 45 : 0,
              autoSkip: true,
              maxTicksLimit: isMobile ? 8 : 14,
              font: { family: 'inherit', size: isMobile ? 9 : 11 },
            },
            grid: { display: false },
          },
          y: {
            beginAtZero: true,
            ticks: { precision: 0, font: { family: 'inherit', size: 11 } },
            grid: { color: 'rgba(15, 94, 79, 0.08)' },
          },
        },
      },
    });
  }

  /* ── Permission visibility ── */
  function applyVisibility() {
    if (window.APP_UI?.applyPermissionVisibility) {
      window.APP_UI.applyPermissionVisibility(document);
    }
  }

  /* ── Main ── */
  try {
    applyVisibility();
    const data = await window.APP_API.get(window.APP_ROUTES.dashboard());
    const cards = getCards(data);

    if (window.APP_UI?.hideLoadingState) {
      window.APP_UI.hideLoadingState(loadingBox);
    } else {
      loadingBox.style.display = 'none';
    }

    renderCards(cards);
    renderNationalAnalytics(data);

    const linksBox = document.getElementById('dashboardQuickLinks');
    if (linksBox && Array.isArray(data.operational_links) && data.operational_links.length) {
      const base = window.APP_CONFIG?.FRONTEND_BASE_URL || '';
      linksBox.innerHTML = data.operational_links.map((l) =>
        `<a href="${base}/${l.path}" class="btn btn-brand btn-sm">${window.APP_HELPERS.e(l.label)}</a>`
      ).join('');
      linksBox.classList.remove('d-none');
    }

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        if (isAnalyticsDashboard(data) && data.analytics) {
          renderNationalAnalytics(data);
        }
      }, 250);
    });
  } catch (error) {
    console.error('Dashboard error:', error);
    loadingBox.innerHTML = `<div class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>${window.APP_HELPERS.e(error?.data?.message || SiteI18n.ta('تعذر تحميل البيانات.'))}</div>`;
  }
});
