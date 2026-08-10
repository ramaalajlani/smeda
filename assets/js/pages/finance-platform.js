window.FinancePlatform = {
  canViewApplications() {
    return window.AppAuth?.hasPermission('finance.applications.view')
      || window.AppAuth?.hasPermission('finance.applications.review_branch')
      || window.AppAuth?.isNationalAdmin()
      || window.AppAuth?.hasRole('project_owner')
      || window.AppAuth?.hasRole('consultant_office')
      || window.AppAuth?.hasRole('funding_partner')
      || window.AppAuth?.hasRole('finance_manager')
      || window.AppAuth?.hasRole('finance_officer')
      || window.AppAuth?.hasRole('branch_manager')
      || window.AppAuth?.hasRole('general_director')
      || window.AppAuth?.hasRole('deputy_general_director')
      || window.AppAuth?.hasRole('deputy_director');
  },

  canCreateApplication() {
    return window.AppAuth?.hasPermission('finance.applications.create')
      || window.AppAuth?.hasRole('project_owner');
  },

  canReviewBranch() {
    return window.AppAuth?.hasPermission('finance.applications.review_branch')
      || window.AppAuth?.hasRole('branch_manager')
      || window.AppAuth?.isNationalAdmin();
  },

  canApproveApplication() {
    return window.AppAuth?.hasPermission('finance.applications.approve')
      || window.AppAuth?.hasRole('finance_manager')
      || window.AppAuth?.hasRole('general_director')
      || window.AppAuth?.hasRole('deputy_general_director')
      || window.AppAuth?.hasRole('deputy_director')
      || window.AppAuth?.isNationalAdmin();
  },

  canRejectApplication() {
    return window.AppAuth?.hasPermission('finance.applications.reject')
      || window.AppAuth?.hasRole('finance_manager')
      || window.AppAuth?.hasRole('branch_manager')
      || window.AppAuth?.hasRole('general_director')
      || window.AppAuth?.isNationalAdmin();
  },

  isReadOnly() {
    return window.AppAuth?.hasRole('auditor')
      && !window.AppAuth?.isNationalAdmin();
  },

  statusLabel(status) {
    const map = {
      draft: SiteI18n.ta('مسودة'),
      submitted: SiteI18n.ta('مُرسل'),
      branch_review: SiteI18n.ta('مراجعة فرع'),
      needs_completion: SiteI18n.ta('يحتاج استكمال'),
      consultant_review: SiteI18n.ta('مراجعة استشارية'),
      consultant_priced: SiteI18n.ta('عرض سعر'),
      funder_review: SiteI18n.ta('مراجعة تمويل'),
      approved: SiteI18n.ta('معتمد'),
      rejected: SiteI18n.ta('مرفوض'),
      funded: SiteI18n.ta('ممول'),
      defaulted: SiteI18n.ta('متعثر'),
      active: SiteI18n.ta('نشط'),
      paid: SiteI18n.ta('مسدد'),
      restructured: SiteI18n.ta('إعادة هيكلة'),
      closed: SiteI18n.ta('مغلق'),
    };
    return map[status] || status;
  },

  readinessLabel(status) {
    if (['approved', 'funded'].includes(status)) return SiteI18n.ta('جاهز للسحابة');
    if (['submitted', 'branch_review', 'funder_review', 'consultant_review', 'consultant_priced'].includes(status)) {
      return SiteI18n.ta('قيد المراجعة');
    }
    if (status === 'needs_completion') return SiteI18n.ta('بحاجة استكمال');
    if (status === 'rejected') return SiteI18n.ta('مرفوض');
    return SiteI18n.ta('قيد الاستكمال');
  },

  formatAmount(value, currency = 'SYP') {
    const n = Number(value || 0);
    return `${n.toLocaleString('ar-SY')} ${currency}`;
  },

  applyRoleNav(containerId = 'financeRoleNav') {
    const el = document.getElementById(containerId);
    if (!el || !window.AppAuth) return;

    el.querySelectorAll('[data-finance-role], [data-finance-permission]').forEach((node) => {
      const roles = (node.dataset.financeRole || '').split(',').map((r) => r.trim()).filter(Boolean);
      const perms = (node.dataset.financePermission || '').split(',').map((p) => p.trim()).filter(Boolean);
      let show = false;

      if (window.AppAuth.isNationalAdmin()) show = true;
      if (roles.length && roles.some((r) => window.AppAuth.hasRole(r))) show = true;
      if (perms.length && perms.some((p) => window.AppAuth.hasPermission(p))) show = true;

      node.classList.toggle('d-none', !show);
    });
  },

  isUnionAdmin() {
    return window.AppAuth?.hasRole('consultant_union_admin');
  },

  isCentralBankAdmin() {
    return window.AppAuth?.hasRole('central_bank_admin');
  },

  isConsultantOffice() {
    return window.AppAuth?.hasRole('consultant_office');
  },

  isFundingPartner() {
    return window.AppAuth?.hasRole('funding_partner');
  },
};
