window.APP_HELPERS = {
  ROLE_LABELS: {
    general_director: 'المدير العام',
    deputy_general_director: 'نائب المدير العام',
    branch_manager: 'مدير فرع',
    center_user: 'مستخدم مركز',
    trainer_user: 'مدرب',
    trainee_user: 'متدرب',
    auditor: 'مدقق',
    admin: 'مدير نظام',
    super_admin: 'مدير نظام كامل',
    system_admin: 'مدير نظام (صلاحيات)',
    training_manager: 'مدير التدريب',
    training_supervisor: 'مشرف تدريب',
    deputy_director: 'نائب مدير',
    project_owner: 'صاحب مشروع',
    consultant_office: 'مكتب استشاري',
    funding_partner: 'شريك تمويل',
    finance_manager: 'مدير التمويل',
    finance_officer: 'موظف تمويل',
    workforce_manager: 'مدير القوى العاملة',
    branch_officer: 'موظف فرع',
    governor: 'محافظ',
    data_entry: 'مدخل بيانات',
    data_reviewer: 'مدقق بيانات',
    development_manager: 'مدير التنمية',
    local_development_manager: 'مدير التنمية المحلية',
    project_services_manager: 'مدير خدمات المشروعات',
    incubator_manager: 'مدير حاضنة',
    incubator_mentor: 'مرشد حاضنة',
    entrepreneur_manager: 'مدير ريادة',
    media_manager: 'مدير إعلام',
    consultant_union_admin: 'إدارة نقابة الاستشاريين',
    central_bank_admin: 'إدارة البنك المركزي',
  },

  /** أدوار أعلى من مدير المنصة — لا تُعرَض للتفويض إلا للمدير العام / مدير النظام الكامل */
  EXECUTIVE_ROLES: ['general_director', 'deputy_general_director'],

    PERMISSION_GROUP_LABELS: {
    approve: 'اعتماد',
    view: 'عرض',
    manage: 'إدارة',
    create: 'إنشاء',
    edit: 'تعديل',
    delete: 'حذف',
    assign: 'إسناد',
    revoke: 'سحب',
    issue: 'إصدار',
    print: 'طباعة',
    verify: 'تحقق',
    review: 'مراجعة',
    nominate: 'ترشيح',
    confirm: 'تأكيد',
    complete: 'إكمال',
    finance: 'التمويل',
    needs: 'الاحتياجات',
    workforce: 'القوى العاملة',
    incubation: 'الحاضنات',
    entrepreneur: 'ريادة الأعمال',
    story: 'قصص النجاح',
    news: 'الأخبار',
    program: 'برامج التدريب',
    program_bank: 'بنك البرامج',
  },

  PERMISSION_LABELS: {
    view_trainers: 'عرض المدربين',
    manage_trainers: 'إدارة المدربين',
    view_trainer_profiles: 'عرض ملفات المدربين',
    edit_own_trainer_profile: 'تعديل ملف المدرب الخاص',
    view_centers: 'عرض المراكز',
    manage_centers: 'إدارة المراكز',
    view_kits: 'عرض الحقائب التدريبية',
    manage_kits: 'إدارة الحقائب التدريبية',
    nominate_training_kits: 'ترشيح حقائب تدريبية',
    review_training_kit_nominations: 'مراجعة ترشيحات الحقائب',
    view_programs: 'عرض البرامج',
    manage_programs: 'إدارة البرامج',
    'program_bank.view': 'عرض بنك البرامج',
    'program_bank.create': 'إنشاء برنامج في البنك',
    'program_bank.update': 'تعديل برنامج في البنك',
    'program_bank.delete': 'حذف برنامج من البنك',
    'program_bank.approve': 'اعتماد برنامج في البنك',
    'program_bank.reports': 'تقارير بنك البرامج',
    view_courses: 'عرض الدورات',
    manage_courses: 'إدارة الدورات',
    view_course_details: 'عرض تفاصيل الدورة',
    view_trainees: 'عرض المتدربين',
    manage_trainees: 'إدارة المتدربين',
    view_certificates: 'عرض الشهادات',
    issue_certificates: 'إصدار الشهادات',
    view_certificate_approvals: 'عرض اعتمادات الشهادات',
    approve_center_certificates: 'اعتماد شهادات المركز',
    approve_training_certificates: 'اعتماد شهادات التدريب',
    approve_deputy_certificates: 'اعتماد شهادات نائب المدير',
    approve_general_director_certificates: 'اعتماد شهادات المدير العام',
    print_certificates: 'طباعة الشهادات',
    verify_certificates: 'التحقق من الشهادات',
    view_reports: 'عرض التقارير',
    view_audit: 'عرض سجل التدقيق',
    view_registration_requests: 'عرض طلبات التسجيل',
    create_center_registration_requests: 'إنشاء طلب تسجيل مركز',
    review_center_registration_requests: 'مراجعة طلب تسجيل مركز',
    create_trainer_registration_requests: 'إنشاء طلب تسجيل مدرب',
    review_trainer_registration_requests: 'مراجعة طلب تسجيل مدرب',
    create_trainee_registration_requests: 'إنشاء طلب تسجيل متدرب',
    review_trainee_registration_requests: 'مراجعة طلب تسجيل متدرب',
    create_course_registration_requests: 'إنشاء طلب تسجيل دورة',
    confirm_course_registration_requests: 'تأكيد تسجيل الدورة',
    complete_course_registration_requests: 'إكمال تسجيل الدورة',
    manage_roles: 'إدارة الأدوار',
    view_roles: 'عرض الأدوار',
    create_roles: 'إنشاء أدوار',
    update_roles: 'تعديل الأدوار',
    delete_roles: 'حذف الأدوار',
    manage_permissions: 'إدارة الصلاحيات',
    view_permissions: 'عرض الصلاحيات',
    create_permissions: 'إنشاء صلاحيات',
    update_permissions: 'تعديل الصلاحيات',
    delete_permissions: 'حذف الصلاحيات',
    assign_roles: 'إسناد أدوار',
    revoke_roles: 'سحب أدوار',
    assign_permissions: 'إسناد صلاحيات',
    revoke_permissions: 'سحب صلاحيات',
    view_users: 'عرض المستخدمين',
    manage_user_access: 'إدارة وصول المستخدمين',
    view_governorates: 'عرض المحافظات',
    view_branches: 'عرض الفروع',
    manage_branches: 'إدارة الفروع',
    manage_branch_managers: 'إدارة مدراء الفروع',
    view_national_reports: 'عرض التقارير الوطنية',
    view_branch_reports: 'عرض تقارير الفرع',
    manage_agreements: 'إدارة الاتفاقيات',
    approve_agreements: 'اعتماد الاتفاقيات',
    view_finance: 'عرض التمويل',
    manage_finance: 'إدارة التمويل',
    approve_finance: 'اعتماد التمويل',
    'finance.applications.view': 'عرض طلبات التمويل',
    'finance.applications.create': 'إنشاء طلب تمويل',
    'finance.applications.update': 'تعديل طلب تمويل',
    'finance.applications.submit': 'إرسال طلب تمويل',
    'finance.applications.review_branch': 'مراجعة طلب التمويل في الفرع',
    'finance.applications.request_completion': 'طلب استكمال طلب التمويل',
    'finance.applications.assign_consultant': 'إسناد استشاري للطلب',
    'finance.applications.assign_partner': 'إسناد شريك تمويل',
    'finance.applications.approve': 'اعتماد طلب التمويل',
    'finance.applications.reject': 'رفض طلب التمويل',
    'finance.consultants.view': 'عرض الاستشاريين',
    'finance.consultants.manage': 'إدارة الاستشاريين',
    'finance.consultants.assign': 'إسناد استشاري',
    'finance.consultants.submit_price': 'تقديم عرض سعر استشاري',
    'finance.consultants.approve_price': 'اعتماد عرض سعر استشاري',
    'finance.consultants.submit_report': 'تقديم تقرير استشاري',
    'finance.consultants.view_all': 'عرض كل الاستشاريين',
    'finance.consultants.create': 'إنشاء استشاري',
    'finance.consultants.update': 'تعديل استشاري',
    'finance.consultants.approve': 'اعتماد استشاري',
    'finance.consultants.activate': 'تفعيل استشاري',
    'finance.consultants.suspend': 'إيقاف استشاري',
    'finance.consultants.monitor': 'متابعة الاستشاريين',
    'finance.consultants.reports.view': 'عرض تقارير الاستشاريين',
    'finance.consultants.price_offers.view': 'عرض عروض أسعار الاستشاريين',
    'finance.consultant_union.dashboard': 'لوحة نقابة الاستشاريين',
    'finance.consultant_office.dashboard': 'لوحة المكتب الاستشاري',
    'finance.consultant_assignments.view_own': 'عرض إسنادات الاستشاري الخاصة',
    'finance.consultant_assignments.accept': 'قبول إسناد استشاري',
    'finance.consultant_assignments.reject': 'رفض إسناد استشاري',
    'finance.consultant_assignments.submit_price': 'تقديم سعر لإسناد استشاري',
    'finance.consultant_reports.create': 'إنشاء تقرير استشاري',
    'finance.consultant_reports.update_own': 'تعديل تقريري الاستشاري',
    'finance.consultant_reports.view_own': 'عرض تقاريري الاستشارية',
    'finance.partners.view': 'عرض شركاء التمويل',
    'finance.partners.manage': 'إدارة شركاء التمويل',
    'finance.partners.review': 'مراجعة شريك تمويل',
    'finance.partners.decide': 'اتخاذ قرار بشأن شريك تمويل',
    'finance.partners.view_all': 'عرض كل شركاء التمويل',
    'finance.partners.create': 'إنشاء شريك تمويل',
    'finance.partners.update': 'تعديل شريك تمويل',
    'finance.partners.approve': 'اعتماد شريك تمويل',
    'finance.partners.activate': 'تفعيل شريك تمويل',
    'finance.partners.suspend': 'إيقاف شريك تمويل',
    'finance.partners.monitor': 'متابعة شركاء التمويل',
    'finance.partner_decisions.view_all': 'عرض كل قرارات الشركاء',
    'finance.partner_decisions.view_final': 'عرض القرارات النهائية للشركاء',
    'finance.partner_assignments.view_own': 'عرض إسنادات الشريك الخاصة',
    'finance.partner_assignments.review': 'مراجعة إسناد شريك تمويل',
    'finance.partner_assignments.decide': 'اتخاذ قرار بإسناد شريك',
    'finance.partner_assignments.approve_amount': 'اعتماد مبلغ التمويل',
    'finance.funding_partner.dashboard': 'لوحة شريك التمويل',
    'finance.central_bank.dashboard': 'لوحة البنك المركزي',
    'finance.bank_metrics.view': 'عرض مؤشرات البنك',
    'finance.loans.view': 'عرض القروض',
    'finance.loans.manage': 'إدارة القروض',
    'finance.loans.payments': 'مدفوعات القروض',
    'finance.loans.defaulted': 'القروض المتعثرة',
    'finance.loans.close': 'إغلاق قرض',
    'finance.loans.view_own': 'عرض قروضي',
    'finance.loans.update_own_status': 'تحديث حالة قروضي',
    'finance.metrics.view': 'عرض مؤشرات التمويل',
    'finance.metrics.national': 'مؤشرات التمويل الوطنية',
    'finance.metrics.branch': 'مؤشرات التمويل للفرع',
    'needs.view': 'عرض الاحتياجات',
    'needs.view_all': 'عرض كل الاحتياجات',
    'needs.view_branch': 'عرض احتياجات الفرع',
    'needs.create': 'إنشاء احتياج',
    'needs.create_citizen': 'إنشاء احتياج مواطن',
    'needs.create_state': 'إنشاء احتياج حكومي',
    'needs.update': 'تعديل احتياج',
    'needs.review': 'مراجعة احتياج',
    'needs.approve': 'اعتماد احتياج',
    'needs.reject': 'رفض احتياج',
    'needs.return': 'إعادة احتياج',
    'needs.classify': 'تصنيف احتياج',
    'needs.resolve': 'حل احتياج',
    'needs.export': 'تصدير الاحتياجات',
    'needs.dashboard': 'لوحة مؤشرات الاحتياجات',
    'needs.map': 'خريطة الاحتياجات',
    'needs.manage_lookups': 'إدارة قوائم الاحتياجات',
    'needs.manage_admin_units': 'إدارة الوحدات الإدارية',
    'needs.view_state_only': 'عرض الاحتياجات الحكومية فقط',
    'workforce.jobs.view': 'عرض فرص العمل',
    'workforce.jobs.create': 'إنشاء فرصة عمل',
    'workforce.jobs.manage': 'إدارة فرص العمل',
    'workforce.applications.create': 'التقدم لفرصة عمل',
    'workforce.applications.view': 'عرض طلبات التوظيف',
    'workforce.training_requests.create': 'إنشاء طلب تدريب وظيفي',
    'workforce.training_requests.view': 'عرض طلبات التدريب الوظيفي',
    'incubation.view': 'عرض الحاضنات',
    'incubation.manage': 'إدارة الحاضنات',
    'incubation.mentor': 'إرشاد الحاضنة',
    'entrepreneur.manage': 'إدارة ريادة الأعمال',
    'story.manage': 'إدارة قصص النجاح',
    'news.manage': 'إدارة الأخبار',
  },

  roleLabel(role) {
    const key = String(role || '').trim();
    if (!key) return '—';
    const ar = this.ROLE_LABELS[key] || key;
    return window.SiteI18n?.ta?.(ar) ?? ar;
  },

  roleLabels(roles) {
    return (roles || []).map((role) => this.roleLabel(role));
  },

  permissionLabel(name) {
    const key = String(name || '').trim();
    if (!key) return '—';
    const mapped = this.PERMISSION_LABELS[key];
    if (mapped) return window.SiteI18n?.ta?.(mapped) ?? mapped;

    // ترجمة تلقائية لأي صلاحية غير مضافة يدوياً
    const actionMap = {
      view: 'عرض', view_all: 'عرض الكل', view_own: 'عرض الخاص', view_branch: 'عرض الفرع',
      create: 'إنشاء', update: 'تعديل', update_own: 'تعديل الخاص', delete: 'حذف',
      manage: 'إدارة', approve: 'اعتماد', reject: 'رفض', review: 'مراجعة',
      decide: 'اتخاذ قرار', activate: 'تفعيل', suspend: 'إيقاف', monitor: 'متابعة',
      assign: 'إسناد', accept: 'قبول', submit: 'إرسال', close: 'إغلاق',
      payments: 'المدفوعات', defaulted: 'المتعثر', dashboard: 'لوحة التحكم',
      national: 'وطني', branch: 'فرع', reports: 'تقارير', map: 'خريطة',
      export: 'تصدير', classify: 'تصنيف', resolve: 'حل', return: 'إعادة',
      approve_amount: 'اعتماد المبلغ', submit_price: 'تقديم سعر',
      approve_price: 'اعتماد السعر', submit_report: 'تقديم تقرير',
      request_completion: 'طلب استكمال', review_branch: 'مراجعة الفرع',
      assign_consultant: 'إسناد استشاري', assign_partner: 'إسناد شريك',
      update_own_status: 'تحديث الحالة', price_offers: 'عروض الأسعار',
    };
    const entityMap = {
      finance: 'التمويل', applications: 'الطلبات', consultants: 'الاستشاريين',
      partners: 'شركاء التمويل', loans: 'القروض', metrics: 'المؤشرات',
      needs: 'الاحتياجات', workforce: 'القوى العاملة', jobs: 'فرص العمل',
      incubation: 'الحاضنات', entrepreneur: 'ريادة الأعمال', story: 'قصص النجاح',
      news: 'الأخبار', consultant_union: 'نقابة الاستشاريين',
      consultant_office: 'المكتب الاستشاري', consultant_assignments: 'إسنادات الاستشاري',
      consultant_reports: 'تقارير الاستشاري', central_bank: 'البنك المركزي',
      funding_partner: 'شريك التمويل', partner_assignments: 'إسنادات الشريك',
      partner_decisions: 'قرارات الشركاء', bank_metrics: 'مؤشرات البنك',
      program_bank: 'بنك البرامج', applications_finance: 'طلبات التمويل',
    };

    const parts = key.split(/[._]/);
    const translated = parts.map((part) => actionMap[part] || entityMap[part] || part);
    const label = translated.join(' ').replace(/\s+/g, ' ').trim();
    return window.SiteI18n?.ta?.(label) ?? label;
  },
  permissionGroupLabel(prefix) {
    const key = String(prefix || '').trim();
    const ar = this.PERMISSION_GROUP_LABELS[key] || key;
    return window.SiteI18n?.ta?.(ar) ?? ar;
  },

  isExecutiveRole(role) {
    return this.EXECUTIVE_ROLES.includes(String(role || '').trim());
  },

  safe(value, fallback = '—') {
    return value === undefined || value === null || value === '' ? fallback : value;
  },

  e(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
  },

  badgeHtml(value) {
    const map = {
      active: ['success', 'نشط'],
      inactive: ['secondary', 'غير نشط'],
      approved: ['success', 'معتمد'],
      rejected: ['danger', 'مرفوض'],
      pending: ['secondary', 'قيد الانتظار'],
      pending_center_approval: ['secondary', 'بانتظار المركز'],
      pending_training_approval: ['warning', 'بانتظار قسم التدريب'],
      pending_deputy_approval: ['info', 'بانتظار نائب المدير'],
      pending_general_director_approval: ['primary', 'بانتظار اعتماد المدير العام'],
      under_review: ['warning', 'قيد المراجعة'],
      suspended: ['danger', 'موقوف'],
      expired: ['dark', 'منتهي'],
      attendance: ['primary', 'حضور'],
      pass: ['success', 'اجتياز'],
      passed: ['success', 'ناجح'],
      failed: ['danger', 'راسب'],
      review: ['warning', 'مراجعة'],
      attended: ['success', 'حضر'],
      absent: ['danger', 'غائب'],
      completed: ['success', 'مكتملة'],
      ongoing: ['info', 'قيد التنفيذ'],
      scheduled: ['primary', 'مجدولة'],
      cancelled: ['danger', 'ملغاة'],
      draft: ['secondary', 'مسودة'],
      online: ['info', 'أونلاين'],
      offline: ['dark', 'حضوري'],
      hybrid: ['warning', 'هجين'],
    };

    const key = String(value || '').trim();
    const found = map[key];
    const lbl = (s) => window.SiteI18n?.ta?.(s) ?? s;

    if (!found) {
      return `<span class="badge bg-secondary">${this.e(this.safe(key, '—'))}</span>`;
    }

    return `<span class="badge bg-${found[0]}">${lbl(found[1])}</span>`;
  },

  /**
   * Resolve certificate view/print URLs using frontend BACKEND_BASE_URL.
   * Pending certificates open the signed print preview (full document).
   */
  resolveCertificateLinks(certificate) {
    const routes = window.APP_ROUTES || {};
    const code = certificate?.certificate_code;
    const id = certificate?.id;
    const isApproved = certificate?.status === 'approved' && !!certificate?.is_verified;

    const signedPrintUrl = certificate?.printable_url || certificate?.pdf_url || null;

    const verifyUrl = (code && routes.certificateViewByCode)
      ? routes.certificateViewByCode(code)
      : (certificate?.view_url || null);

    const printUrl = signedPrintUrl
      || (isApproved && code && routes.certificatePrintByCode
        ? routes.certificatePrintByCode(code)
        : null);

    const viewUrl = (!isApproved && signedPrintUrl)
      ? signedPrintUrl
      : (verifyUrl || printUrl);

    return { viewUrl, printUrl: printUrl || signedPrintUrl, verifyUrl, pdfUrl: certificate?.pdf_url || null };
  },

  async openCertificatePreview(certificate, { preferPrint = false } = {}) {
    let item = certificate || null;

    if (item?.id && window.APP_API && window.APP_ROUTES?.certificateShow) {
      try {
        const response = await window.APP_API.get(window.APP_ROUTES.certificateShow(item.id));
        if (response?.data) {
          item = response.data;
        }
      } catch (error) {
        console.warn('Certificate preview refresh failed:', error);
      }
    }

    const links = this.resolveCertificateLinks(item);
    const target = preferPrint
      ? (links.printUrl || links.viewUrl)
      : (links.viewUrl || links.printUrl);

    if (!target) {
      if (window.APP_UI?.showMessage) {
        window.APP_UI.showMessage('تعذّر فتح معاينة الشهادة.', 'error');
      } else {
        window.alert('تعذّر فتح معاينة الشهادة.');
      }
      return false;
    }

    window.open(target, '_blank', 'noopener,noreferrer');
    return true;
  },
};
