window.AppBootstrapAuth = (() => {
  async function init(options = {}) {
    const {
      requireAuth = true,
      requiredPermission = null,
      requiredAnyPermissions = [],
      requiredAnyRoles = [],
      forceRefreshMe = false,
    } = options;

    if (requireAuth && !window.AppAuth.isLoggedIn()) {
      window.location.replace(window.APP_CONFIG.LOGIN_PAGE);
      return false;
    }

    if (window.AppAuth.isLoggedIn()) {
      const hasCache = window.AppAuth.hasCachedUser?.() === true;
      const cacheFresh = window.AppAuth.isMeCacheFresh?.() === true;

      // إذا عندنا مستخدم مخزّن: نكمّل فورًا ونحدّث /me بالخلفية بدل انتظار الشبكة
      if (hasCache && !forceRefreshMe) {
        if (!cacheFresh) {
          window.AppAuth.refreshMeInBackground?.();
        }
      } else {
        try {
          await window.AppAuth.fetchMe({ force: forceRefreshMe });
        } catch (error) {
          const status = error?.status;

          // انتهاء الجلسة/التوكن فقط → امسح وأعد التوجيه للدخول
          if (status === 401 || status === 419) {
            window.AppAuth.clearSession();
            window.location.replace(window.APP_CONFIG.LOGIN_PAGE);
            return false;
          }

          // تعطّل خادم/شبكة (0 أو 5xx) → لا تمسح الجلسة إذا عندنا كاش
          console.error('fetchMe failed (server/network):', error);
          if (!hasCache) {
            window.APP_UI.showConnectionError(() => window.location.reload());
            return false;
          }
        }
      }
    }

    if (requiredPermission) {
      const ok = window.AppAccess.guardPermission(requiredPermission);
      if (!ok) return false;
    }

    if (requiredAnyPermissions.length) {
      const ok = window.AppAccess.guardAnyPermission(requiredAnyPermissions);
      if (!ok) return false;
    }

    if (requiredAnyRoles.length) {
      const ok = window.AppAccess.guardAnyRole(requiredAnyRoles);
      if (!ok) return false;
    }

    window.AppAccess.toggleByPermission(document);
    return true;
  }

  return { init };
})();
