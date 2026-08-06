(() => {
  const SKIP_KEYS = new Set([
    'dashboard_role', 'dashboard_title', 'operational_links', 'recent_items',
    'recent_activity', 'recent_users', 'recent_certificates', 'message',
    'scope', 'by_sector', 'by_priority', 'by_governorate', 'by_status',
    'governorate_stats', 'comparison', 'branch_activities', 'governorate_name',
    'branch_name', 'branch_id', 'needs_scope',
  ]);

  const KPI_LABELS = {
    users_total: SiteI18n.ta('إجمالي المستخدمين'),
    users_active: SiteI18n.ta('المستخدمون النشطون'),
    users_inactive: SiteI18n.ta('المستخدمون المعطلون'),
    funding_applications_total: SiteI18n.ta('طلبات التمويل'),
    consulting_requests_total: SiteI18n.ta('طلبات الاستشارات'),
    needs_total: SiteI18n.ta('احتياجات GIS'),
    jobs_published: SiteI18n.ta('فرص العمل المنشورة'),
    jobs_pending_review: SiteI18n.ta('وظائف قيد المراجعة'),
    jobs_closed: SiteI18n.ta('وظائف مغلقة'),
    applications_total: SiteI18n.ta('الطلبات المقدمة'),
    candidates_total: SiteI18n.ta('المرشحون'),
    my_drafts: SiteI18n.ta('المسودات'),
    my_approved: SiteI18n.ta('المعتمدة'),
    my_returned: SiteI18n.ta('المعادة'),
    branch_applications_visible: SiteI18n.ta('طلبات الفرع المرئية'),
    certificates_total: SiteI18n.ta('الشهادات'),
    certificates_approved: SiteI18n.ta('الشهادات المعتمدة'),
    certificates_pending: SiteI18n.ta('بانتظار الاعتماد'),
    certificates_pending_deputy: SiteI18n.ta('بانتظار نائب المدير'),
    certificates_pending_general_director: SiteI18n.ta('بانتظار اعتماد المدير العام'),
    assignments_total: SiteI18n.ta('الإحالات'),
    under_review: SiteI18n.ta('قيد الدراسة'),
    approved: SiteI18n.ta('موافق عليها'),
    rejected: SiteI18n.ta('مرفوضة'),
    my_total: SiteI18n.ta('إجمالي إدخالاتي'),
    my_drafts: SiteI18n.ta('المسودات'),
    my_pending: SiteI18n.ta('بانتظار المراجعة'),
    pending_review: SiteI18n.ta('بانتظار المراجعة'),
    partners_total: SiteI18n.ta('شركاء التمويل'),
    offices_total: SiteI18n.ta('المكاتب الاستشارية'),
    trainers: SiteI18n.ta('المدربون'),
    trainees: SiteI18n.ta('المتدربون'),
    courses: SiteI18n.ta('الدورات'),
    centers: SiteI18n.ta('المراكز'),
    audit_logs_total: SiteI18n.ta('سجلات التدقيق'),
    funding_pending: SiteI18n.ta('قيد المراجعة'),
    funding_approved: SiteI18n.ta('موافق عليها'),
    funding_rejected: SiteI18n.ta('مرفوضة'),
    my_applications_total: SiteI18n.ta('طلباتي'),
    my_tasks_total: SiteI18n.ta('مهامي'),
    my_tasks_pending: SiteI18n.ta('مهام قيد المعالجة'),
    needs_pending_review: SiteI18n.ta('احتياجات بانتظار المراجعة'),
    needs_approved: SiteI18n.ta('احتياجات معتمدة'),
    high_priority: SiteI18n.ta('أولوية عالية'),
    total_funded_amount: SiteI18n.ta('إجمالي التمويل'),
    pending_applications: SiteI18n.ta('طلبات معلقة'),
    registration_requests_total: SiteI18n.ta('طلبات التسجيل'),
    governorates_count: SiteI18n.ta('المحافظات'),
    branches_count: SiteI18n.ta('الفروع'),
    agreements_count: SiteI18n.ta('الاتفاقيات'),
  };

  function labelFor(key) {
    return KPI_LABELS[key] || key.replace(/_/g, ' ');
  }

  function isRenderableValue(value) {
    return typeof value === 'number' || (typeof value === 'string' && value !== '' && !value.includes('T'));
  }

  function buildKpis(data) {
    const cards = [];
    Object.entries(data || {}).forEach(([key, value]) => {
      if (SKIP_KEYS.has(key) || Array.isArray(value) || (value && typeof value === 'object')) return;
      if (!isRenderableValue(value)) return;
      cards.push({ label: labelFor(key), value });
    });
    return cards.slice(0, 8);
  }

  function renderCards(container, cards) {
    if (!cards.length) {
      container.innerHTML = SiteI18n.ta('<div class="col-12"><div class="alert alert-light border">لا توجد مؤشرات متاحة حالياً.</div></div>');
      return;
    }
    container.innerHTML = cards.map((c) => `
      <div class="col-md-6 col-lg-3">
        <div class="card p-3 h-100 shadow-sm">
          <div class="text-muted small">${c.label}</div>
          <div class="fs-3 fw-bold mt-1">${c.value ?? 0}</div>
        </div>
      </div>`).join('');
  }

  function renderLinks(container, links, basePath) {
    if (!links?.length) {
      container.innerHTML = '';
      return;
    }
    const base = (basePath || '').replace(/\/?$/, '/');
    container.innerHTML = links.map((l) =>
      `<a href="${base}${l.path}" class="btn btn-brand btn-sm">${l.label}</a>`
    ).join('');
  }

  function renderRecent(container, items) {
    if (!items?.length) {
      container.innerHTML = '';
      return;
    }
    const rows = items.map((item) => {
      const label = item.label || item.title || item.project_name || item.application_number || item.need_code || item.action || '—';
      const status = item.status || item.module || '';
      const at = item.at || item.created_at || '';
      return `<tr><td>${label}</td><td>${status}</td><td>${at ? String(at).slice(0, 16) : '—'}</td></tr>`;
    }).join('');
    container.innerHTML = `
      <div class="card shadow-sm mt-4">
        <div class="card-header fw-bold">آخر النشاطات</div>
        <div class="table-responsive">
          <table class="table table-sm mb-0"><thead><tr><th>العنصر</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody>${rows}</tbody></table>
        </div>
      </div>`;
  }

  async function init(options = {}) {
    const {
      apiUrl = window.APP_ROUTES?.dashboard?.(),
      requiredRoles = [],
      requiredPermissions = [],
      redirectOnFail = window.APP_CONFIG?.FORBIDDEN_PAGE,
      loadingId = 'roleDashboardLoading',
      cardsId = 'roleDashboardCards',
      linksId = 'roleDashboardLinks',
      recentId = 'roleDashboardRecent',
      titleId = 'roleDashboardTitle',
      basePath = '',
    } = options;

    const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
    if (!ok) return null;

    if (requiredRoles.length && !window.AppAuth.hasAnyRole(requiredRoles)) {
      window.location.href = redirectOnFail;
      return null;
    }
    if (requiredPermissions.length && !requiredPermissions.some((p) => window.AppAuth.hasPermission(p))) {
      window.location.href = redirectOnFail;
      return null;
    }

    const loading = document.getElementById(loadingId);
    const cards = document.getElementById(cardsId);
    const links = document.getElementById(linksId);
    const recent = document.getElementById(recentId);
    const title = document.getElementById(titleId);

    try {
      const res = await window.APP_API.get(apiUrl);
      const data = res.data || res;
      if (title && data.dashboard_title) title.textContent = data.dashboard_title;
      renderCards(cards, buildKpis(data));
      renderLinks(links, data.operational_links, basePath);
      renderRecent(recent, data.recent_items || data.recent_activity);
      return data;
    } catch (e) {
      if (cards) cards.innerHTML = `<div class="col-12"><div class="alert alert-danger">${e.message || SiteI18n.ta('تعذر تحميل اللوحة.')}</div></div>`;
      return null;
    } finally {
      loading?.classList.add('d-none');
    }
  }

  window.AppRoleDashboard = { init, buildKpis, renderCards, renderLinks, renderRecent };
})();
