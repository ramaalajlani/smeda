window.NeedsPlatform = {

  /* ══ صلاحيات العرض ══ */
  canView() {
    return window.AppAuth?.hasPermission('needs.view')
      || window.AppAuth?.hasPermission('needs.view_all')
      || window.AppAuth?.hasPermission('needs.view_branch')
      || window.AppAuth?.hasPermission('needs.view_state_only')
      || window.AppAuth?.isNationalAdmin();
  },

  /* ══ صلاحيات الإنشاء ══ */
  canCreate() {
    return window.AppAuth?.hasPermission('needs.create')
      || window.AppAuth?.hasPermission('needs.create_citizen')
      || window.AppAuth?.hasPermission('needs.create_state');
  },

  canCreateGeneral() {
    return this.canCreate();
  },

  canCreateSpecific() {
    return this.canCreate();
  },

  canCreateStatNeeds() {
    return window.AppAuth?.hasPermission('needs.create_state')
      || window.AppAuth?.isNationalAdmin()
      || window.AppAuth?.isBranchManager()
      || window.AppAuth?.isGovernor();
  },

  canCreateCitizenNeeds() {
    return window.AppAuth?.hasPermission('needs.create_citizen')
      || window.AppAuth?.hasPermission('needs.create')
      || window.AppAuth?.isNationalAdmin()
      || window.AppAuth?.isBranchManager();
  },

  /* ══ صلاحيات سير العمل ══ */
  canReview() {
    return window.AppAuth?.hasPermission('needs.review');
  },

  canApprove() {
    return window.AppAuth?.hasPermission('needs.approve');
  },

  canClassify() {
    return window.AppAuth?.hasPermission('needs.classify');
  },

  canExport() {
    return window.AppAuth?.hasPermission('needs.export');
  },

  /* ══ خصائص الدور ══ */
  isReadOnly() {
    return window.AppAuth?.hasRole('auditor') && !window.AppAuth?.isNationalAdmin();
  },

  isGovernor() {
    return window.AppAuth?.isGovernor?.() || false;
  },

  isBranchManager() {
    return window.AppAuth?.isBranchManager?.() || false;
  },

  /* محافظ: يرى احتياجات محافظته فقط */
  seesGovernorateNeedsOnly() {
    return this.isGovernor() && !window.AppAuth?.isNationalAdmin();
  },

  /* مدير الفرع: يرى كل احتياجات فرعه ويوافق عليها */
  canApproveAll() {
    return this.isBranchManager() || window.AppAuth?.isNationalAdmin();
  },

  /* ══ وضع الخريطة المسموح به لهذا الدور ══ */
  allowedMapModes() {
    if (window.AppAuth?.isNationalAdmin()) {
      return ['needs', 'entrepreneurs', 'centers', 'trainees'];
    }
    if (window.AppAuth?.isNationalExecutive?.()) {
      return ['needs', 'entrepreneurs', 'centers', 'trainees'];
    }
    if (this.isGovernor()) {
      return ['needs', 'entrepreneurs', 'centers', 'trainees'];
    }
    if (this.isBranchManager()) {
      return ['needs', 'entrepreneurs', 'centers', 'trainees'];
    }

    const modes = [];
    if (this.canView()) modes.push('needs');

    // مدير خدمات المشروعات / مدير التدريب: أوضاع التدريب على الخريطة
    const isPsm = window.AppAuth?.hasRole?.('project_services_manager');
    const isTrainingManager = window.AppAuth?.hasRole?.('training_manager');
    const canCenters = isPsm || isTrainingManager
      || window.AppAuth?.hasPermission?.('view_centers');
    const canTrainees = isPsm || isTrainingManager
      || window.AppAuth?.hasPermission?.('view_trainees');

    if (canCenters && !modes.includes('centers')) modes.push('centers');
    if (canTrainees && !modes.includes('trainees')) modes.push('trainees');
    if ((isPsm || window.AppAuth?.hasPermission?.('needs.view_all')) && !modes.includes('entrepreneurs')) {
      modes.push('entrepreneurs');
    }

    return modes.length ? modes : ['needs'];
  },

  /* ══ تسميات ══ */
  statusLabel(status) {
    const map = {
      new: SiteI18n.ta('مسودة'),
      pending_governorate_review: SiteI18n.ta('بانتظار تدقيق بيانات المحافظة'),
      returned_for_edit: SiteI18n.ta('معاد للتعديل'),
      pending_branch_approval: SiteI18n.ta('بانتظار موافقة مدير الفرع'),
      approved: SiteI18n.ta('موافق عليه'),
      rejected: SiteI18n.ta('مرفوض'),
      classified: SiteI18n.ta('مصنف'),
      in_progress: SiteI18n.ta('قيد المعالجة'),
      resolved: SiteI18n.ta('تم الحل'),
      archived: SiteI18n.ta('مؤرشف'),
    };
    return map[status] || status;
  },

  complexityLabel(c) {
    return c === 'general' ? 'احتياج عام': SiteI18n.ta('احتياج خاص');
  },

  ownerLabel(type) {
    return type === 'state' ? 'جهة حكومية': SiteI18n.ta('مواطن / قطاع خاص');
  },

  scopeLabel(scope) {
    const map = {
      individual: SiteI18n.ta('فردي'),
      project: SiteI18n.ta('مشروع'),
      local: SiteI18n.ta('محلي'),
      governorate: SiteI18n.ta('محافظة'),
      national: SiteI18n.ta('وطني'),
      sectoral: SiteI18n.ta('قطاعي'),
    };
    return map[scope] || scope;
  },

  interventionLabel(v) {
    const map = {
      'تدريب': SiteI18n.ta('تدريب وتأهيل'),
      'تمويل': SiteI18n.ta('تمويل'),
      'استشارات': SiteI18n.ta('استشارات'),
      'دراسة': SiteI18n.ta('دراسة جدوى'),
      'حاضنة': SiteI18n.ta('حاضنة أعمال'),
      'بنية تحتية': SiteI18n.ta('بنية تحتية'),
    };
    return map[v] || v;
  },

  /* ══ تطبيق قائمة التنقل بالصلاحيات ══ */
  applyRoleNav(containerId = 'needsRoleNav') {
    const el = document.getElementById(containerId);
    if (!el || !window.AppAuth) return;

    el.querySelectorAll('[data-needs-permission]').forEach((node) => {
      const perms = (node.dataset.needsPermission || '').split(',').map((p) => p.trim()).filter(Boolean);
      let show = window.AppAuth.isNationalAdmin();
      if (perms.length && perms.some((p) => window.AppAuth.hasPermission(p))) show = true;
      node.classList.toggle('d-none', !show);
    });
  },
};
