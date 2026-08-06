window.AppAuth = (() => {
  // TODO(security-migration): Move bearer token from localStorage to Sanctum httpOnly session cookies.
  // Steps: set SANCTUM_STATEFUL_DOMAINS to FRONTEND host(s), enable credentials on fetch,
  // use /sanctum/csrf-cookie before login, remove TOKEN_KEY from localStorage after migration.
  // Until then, only the bearer token is stored — no extra secrets in the browser.
  const TOKEN_KEY = 'authority_token';
  const USER_KEY = 'authority_user';
  const USER_FETCHED_AT_KEY = 'authority_user_fetched_at';
  const LEGACY_TOKEN_KEY = 'authority_api_token';
  const LEGACY_USER_KEY = 'authority_api_user';
  /** مدة كاش بيانات المستخدم (ثوانٍ) — يقلل طلبات /me المتكررة بين الصفحات */
  const ME_CACHE_TTL_MS = 60 * 1000;

  let meInFlight = null;

  /** مدير المنصة / المدير العام — صلاحيات وطنية كاملة */
  const NATIONAL_ADMIN_ROLES = ['general_director', 'admin', 'super_admin'];
  const BRANCH_MANAGER_ROLES = ['branch_manager'];
  const GOVERNOR_ROLES = ['governor'];
  const NATIONAL_EXECUTIVE_ROLES = ['deputy_general_director', 'deputy_director'];
  /** مديرو الوصول — إدارة المستخدمين والأدوار والصلاحيات */
  const ACCESS_ADMIN_ROLES = ['admin', 'super_admin', 'system_admin'];

  /** أدوار تستخدم قوقعة الداشبورد (ds-sidebar) بدل app-shell العام */
  const DASHBOARD_SHELL_ROLES = [
    'project_services_manager',
    'training_manager',
    'branch_manager',
    'incubator_manager',
    'media_manager',
    'entrepreneur_manager',
    'general_director',
  ];
  const SHELL_ROLE_COOKIE = 'authority_shell_role';

  function pickDashboardShellRole(user = null) {
    const roles = roleNames(user);
    return DASHBOARD_SHELL_ROLES.find((role) => roles.includes(role)) || '';
  }

  function syncShellRoleCookie(user = null) {
    try {
      const role = pickDashboardShellRole(user);
      if (role) {
        document.cookie = `${SHELL_ROLE_COOKIE}=${encodeURIComponent(role)};path=/;max-age=2592000;SameSite=Lax`;
      } else {
        document.cookie = `${SHELL_ROLE_COOKIE}=;path=/;max-age=0;SameSite=Lax`;
      }
    } catch (e) { /* ignore */ }
  }

  function setSession(data) {
    if (!data || !data.token) return;

    localStorage.setItem(TOKEN_KEY, data.token);
    localStorage.setItem(USER_KEY, JSON.stringify(data.user || {}));
    localStorage.setItem(USER_FETCHED_AT_KEY, String(Date.now()));
    localStorage.removeItem(LEGACY_TOKEN_KEY);
    localStorage.removeItem(LEGACY_USER_KEY);
    syncShellRoleCookie(data.user || {});
  }

  function clearSession() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    localStorage.removeItem(USER_FETCHED_AT_KEY);
    localStorage.removeItem(LEGACY_TOKEN_KEY);
    localStorage.removeItem(LEGACY_USER_KEY);
    meInFlight = null;
    syncShellRoleCookie({});
  }

  function hasCachedUser() {
    const user = getUser();
    return !!(user && (user.id || user.email || (user.roles && user.roles.length)));
  }

  function isMeCacheFresh() {
    const raw = localStorage.getItem(USER_FETCHED_AT_KEY);
    const fetchedAt = Number(raw || 0);
    if (!fetchedAt) return false;
    return (Date.now() - fetchedAt) < ME_CACHE_TTL_MS;
  }

  function getToken() {
    return localStorage.getItem(TOKEN_KEY)
      || localStorage.getItem(LEGACY_TOKEN_KEY)
      || '';
  }

  function getUser() {
    try {
      const raw = localStorage.getItem(USER_KEY) || localStorage.getItem(LEGACY_USER_KEY) || '{}';
      return JSON.parse(raw);
    } catch (e) {
      return {};
    }
  }

  function roleNames(user = null) {
    const raw = (user || getUser()).roles || [];
    return raw.map((role) => (typeof role === 'string' ? role : (role?.name || ''))).filter(Boolean);
  }

  function isLoggedIn() {
    return !!getToken();
  }

  function hasRole(role) {
    return roleNames().includes(role);
  }

  function hasAnyRole(roles = []) {
    const userRoles = roleNames();
    return roles.some((role) => userRoles.includes(role));
  }

  function isCenterWorkspaceUser() {
    return hasRole('center_user');
  }

  function isTrainerWorkspaceUser() {
    return hasRole('trainer_user') && !hasRole('center_user');
  }

  function isTraineeWorkspaceUser() {
    return hasRole('trainee_user') && !hasRole('center_user') && !hasRole('trainer_user');
  }

  function isPlatformAdmin() {
    return hasAnyRole(NATIONAL_ADMIN_ROLES);
  }

  function isNationalAdmin() {
    return isPlatformAdmin();
  }

  function isBranchManager() {
    return hasAnyRole(BRANCH_MANAGER_ROLES);
  }

  function isGovernor() {
    return hasAnyRole(GOVERNOR_ROLES);
  }

  function isNationalExecutive() {
    return hasAnyRole(NATIONAL_EXECUTIVE_ROLES);
  }

  function isAccessAdministrator() {
    return hasAnyRole(ACCESS_ADMIN_ROLES);
  }

  function hasPermission(permission) {
    if (isPlatformAdmin()) {
      return true;
    }

    const user = getUser();
    return (user.permissions || []).includes(permission);
  }

  function hasAnyPermission(permissions = []) {
    if (isPlatformAdmin()) {
      return true;
    }

    return permissions.some((permission) => hasPermission(permission));
  }

  async function fetchMe(options = {}) {
    const force = !!options.force;

    // كاش طازج: نعيد بيانات المستخدم المخزّنة بدون انتظار الشبكة
    if (!force && hasCachedUser() && isMeCacheFresh()) {
      return { user: getUser(), cached: true };
    }

    // طلب قيد التنفيذ: نشارك نفس الـ Promise بدل طلبات متوازية
    if (meInFlight) {
      return meInFlight;
    }

    // لا تمسح الجلسة هنا. عند الفشل يُرمى الخطأ ليقرّر المُستدعي:
    // 401/419 → انتهاء جلسة (يُمسح في bootstrap-auth)، غير ذلك → خطأ خادم/شبكة.
    meInFlight = (async () => {
      try {
        const response = await window.APP_API.get(window.APP_ROUTES.me());

        if (response?.user) {
          localStorage.setItem(USER_KEY, JSON.stringify(response.user));
          localStorage.setItem(USER_FETCHED_AT_KEY, String(Date.now()));
          syncShellRoleCookie(response.user);
        }

        return response;
      } finally {
        meInFlight = null;
      }
    })();

    return meInFlight;
  }

  /** تحديث خلفي بدون حجب الصفحة — يُستخدم بعد الاعتماد على الكاش */
  function refreshMeInBackground() {
    if (!isLoggedIn() || meInFlight) return;
    fetchMe({ force: true }).catch((e) => {
      console.warn('background fetchMe failed', e);
    });
  }

  async function register(payload) {
    const response = await window.APP_API.post(
      window.APP_ROUTES.register(),
      payload
    );

    if (response?.token) {
      setSession(response);
      try { await fetchMe({ force: true }); } catch (e) { console.warn('fetchMe after register failed', e); }

      const redirectMap = {
        trainer: `${window.APP_CONFIG.FRONTEND_BASE_URL}/services/training/trainer-registration-request.php`,
        trainee: `${window.APP_CONFIG.FRONTEND_BASE_URL}/services/training/trainee-registration-request.php`,
        center: `${window.APP_CONFIG.FRONTEND_BASE_URL}/services/training/center-registration-request.php`,
      };

      const target = redirectMap[response?.redirect_to_form] || resolveHomePage();
      window.location.replace(target);
    }

    return response;
  }

  async function login(credentials) {
    const response = await window.APP_API.post(
      window.APP_ROUTES.login(),
      credentials
    );

    if (response?.token) {
      setSession(response);
      try { await fetchMe({ force: true }); } catch (e) { console.warn('fetchMe after login failed', e); }
      window.location.replace(resolveHomePage());
    }

    return response;
  }

  function resolveHomePage() {
    const base = window.APP_CONFIG.FRONTEND_BASE_URL;
    const roles = roleNames();
    const has = (role) => roles.includes(role);
    const any = (list) => list.some(has);

    // مركز / مدرب / متدرب: مساحات منفصلة — قبل أي لوحة منصة عامة.
    if (has('center_user')) {
      return `${base}/services/training/center-app.php`;
    }
    if (has('trainer_user')) {
      return `${base}/services/training/trainer-app.php`;
    }
    if (has('trainee_user')) {
      return `${base}/services/training/trainee-app.php`;
    }

    if (has('super_admin')) {
      return `${base}/services/admin/super-admin-dashboard.php`;
    }

    if (has('general_director') || has('deputy_general_director') || has('admin')) {
      return `${base}/services/admin/general-director-dashboard.php`;
    }

    if (has('system_admin')) {
      return `${base}/services/admin/admin-users.php`;
    }

    if (has('auditor')) {
      return `${base}/services/admin/auditor-dashboard.php`;
    }

    if (has('branch_officer')) {
      return `${base}/services/admin/branch-officer-dashboard.php`;
    }

    if (has('central_bank_admin')) {
      return `${base}/services/finance/central-bank-dashboard.php`;
    }

    if (has('consultant_union_admin')) {
      return `${base}/services/finance/consultant-union-dashboard.php`;
    }

    if (has('consultant_office')) {
      return `${base}/services/finance/consultant-office-dashboard.php`;
    }

    if (has('funding_partner')) {
      return `${base}/services/finance/funding-partner-dashboard.php`;
    }

    if (has('finance_manager') || has('finance_officer')) {
      return `${base}/services/finance/finance-manager-dashboard.php`;
    }

    if (has('data_entry')) {
      return `${base}/services/gis/data-entry-dashboard.php`;
    }

    if (has('data_reviewer')) {
      return `${base}/services/gis/data-reviewer-dashboard.php`;
    }

    if (has('project_services_manager')) {
      return `${base}/services/admin/project-services-manager-dashboard.php`;
    }

    if (any(['development_manager', 'local_development_manager'])) {
      return `${base}/services/gis/development-manager-dashboard.php`;
    }

    if (has('governor')) {
      return `${base}/services/gis/needs-dashboard.php`;
    }

    if (any(NATIONAL_EXECUTIVE_ROLES)) {
      return `${base}/services/admin/deputy-dashboard.php`;
    }

    if (isBranchManager()) {
      return `${base}/services/admin/branch-manager-dashboard.php`;
    }

    if (has('training_manager')) {
      return `${base}/dashboard.php`;
    }

    if (has('workforce_manager')) {
      return `${base}/services/workforce/workforce-manager-dashboard.php`;
    }

    if (has('incubator_manager')) {
      return `${base}/services/admin/incubator-manager-dashboard.php`;
    }

    if (has('media_manager')) {
      return `${base}/services/admin/media-manager-dashboard.php`;
    }

    if (has('entrepreneur_manager')) {
      return `${base}/services/admin/entrepreneur-manager-dashboard.php`;
    }

    if (has('project_owner')) {
      return `${base}/services/finance/project-owner-dashboard.php`;
    }

    return `${base}/my-profile.php`;
  }

  async function logout() {
    try {
      if (getToken()) {
        await window.APP_API.post(window.APP_ROUTES.logout(), {});
      }
    } catch (e) {
      console.warn('logout api failed');
    } finally {
      clearSession();
      window.location.replace(window.APP_CONFIG.LOGIN_PAGE);
    }
  }

  /** صفحات خدمات يمكن للزائر تصفّحها دون تسجيل دخول (معلومات + استكشاف). */
  function isPublicServicePage(path) {
    const publicSegments = [
      'services/index.php',
      'landing.php',
      'incubators.php',
      'entrepreneurship-hub.php',
      'success-stories.php',
      'training-verification.php',
      'training-kit-nomination-request.php',
      'signature-verification.php',
      'training-programs-list.php',
      'training-centers-list.php',
      'consulting-offices-list.php',
      'finance.php',
      'finance-cloud.php',
      'finance-metrics.php',
      'needs-map.php',
      'user-guide.php',
    ];
    return publicSegments.some((segment) => path.includes(segment));
  }

  function bootstrap() {
    const currentPage = window.location.pathname;

    if (isLoggedIn()) {
      syncShellRoleCookie(getUser());
    }

    const isLoginPage = currentPage.includes('login.php');
    const isRegisterPage = currentPage.includes('register.php');
    const isPublicPage =
      isLoginPage ||
      isRegisterPage ||
      currentPage.endsWith('/index.php') ||
      currentPage.endsWith('/') ||
      isPublicServicePage(currentPage);

    const isProtectedPage =
      (currentPage.includes('dashboard.php') ||
      currentPage.includes('/services/')) &&
      !isPublicPage;

    if (!isLoggedIn() && isProtectedPage) {
      window.location.replace(window.APP_CONFIG.LOGIN_PAGE);
      return;
    }

    if (isLoggedIn() && (isLoginPage || isRegisterPage)) {
      window.location.replace(resolveHomePage());
      return;
    }

    // مركز: صفحات center-* فقط.
    if (isLoggedIn() && isCenterWorkspaceUser()) {
      const path = String(currentPage || '').toLowerCase();
      const onCenterApp = /\/services\/training\/center-/.test(path);
      const onSafe =
        path.includes('login.php') ||
        path.includes('register.php') ||
        path.includes('forbidden.php') ||
        path.includes('403.php') ||
        path.endsWith('/') ||
        path.endsWith('/index.php') ||
        isPublicServicePage(path);

      if (!onCenterApp && !onSafe) {
        window.location.replace(resolveHomePage());
      }
    }

    // مدرب: تطبيق المدرب + صفحات تشغيل الدورة المشتركة.
    if (isLoggedIn() && isTrainerWorkspaceUser()) {
      const path = String(currentPage || '').toLowerCase();
      const onTrainerHome = /\/services\/training\/trainer-/.test(path);
      const allowedShared = /\/services\/training\/center-(course|course-edit|course-report|groups|group|modules|scores|attendance|certificates|profile)\.php/.test(path)
        || /\/services\/training\/center-trainees\.php/.test(path);
      const onSafe =
        path.includes('login.php') ||
        path.includes('register.php') ||
        path.includes('forbidden.php') ||
        path.includes('403.php') ||
        path.endsWith('/') ||
        path.endsWith('/index.php') ||
        isPublicServicePage(path);

      if (!onTrainerHome && !allowedShared && !onSafe) {
        window.location.replace(resolveHomePage());
      }
    }

    // متدرب: صفحات trainee-* فقط.
    if (isLoggedIn() && isTraineeWorkspaceUser()) {
      const path = String(currentPage || '').toLowerCase();
      const onTraineeApp = /\/services\/training\/trainee-/.test(path);
      const onSafe =
        path.includes('login.php') ||
        path.includes('register.php') ||
        path.includes('forbidden.php') ||
        path.includes('403.php') ||
        path.endsWith('/') ||
        path.endsWith('/index.php') ||
        isPublicServicePage(path);

      if (!onTraineeApp && !onSafe) {
        window.location.replace(resolveHomePage());
      }
    }
  }

  document.addEventListener('DOMContentLoaded', bootstrap);

  return {
    setSession,
    clearSession,
    getToken,
    getUser,
    isLoggedIn,
    hasRole,
    hasAnyRole,
    isPlatformAdmin,
    isNationalAdmin,
    isBranchManager,
    isGovernor,
    isNationalExecutive,
    isAccessAdministrator,
    hasPermission,
    hasCachedUser,
    isMeCacheFresh,
    fetchMe,
    refreshMeInBackground,
    register,
    login,
    logout,
    resolveHomePage,
    isCenterWorkspaceUser,
    isTrainerWorkspaceUser,
    isTraineeWorkspaceUser,
    bootstrap
  };
})();