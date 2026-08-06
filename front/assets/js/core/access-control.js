/*
|--------------------------------------------------------------------------
| AppAccess — نظام الصلاحيات الوحيد للواجهة
|--------------------------------------------------------------------------
| محرك واحد فقط لإظهار/إخفاء العناصر حسب الصلاحية (غير مُتلِف: يستخدم d-none).
| كل الصفحات — بما فيها لوحة التحكم — تعتمد على AppAccess.toggleByPermission،
| و APP_UI.applyPermissionVisibility يفوّض إليه (اسم واحد للتوافق، تنفيذ واحد).
*/
window.AppAccess = (() => {

  function hasPermission(permission) {
    return window.AppAuth.hasPermission(permission);
  }

  function hasAnyPermission(permissions = []) {
    return permissions.some((permission) => hasPermission(permission));
  }

  function hasAllPermissions(permissions = []) {
    return permissions.length > 0 && permissions.every((permission) => hasPermission(permission));
  }

  function hasRole(role) {
    return window.AppAuth.hasRole(role);
  }

  function hasAnyRole(roles = []) {
    return roles.some((role) => hasRole(role));
  }

  /**
   * تحويلات الصلاحيات تستخدم replace حتى لا تُفسد زر الرجوع
   * (صفحة تُعيد التوجيه عند فتحها يجب ألا تبقى في سجل التصفح).
   */
  function redirect(url) {
    window.location.replace(url);
  }

  function guardPermission(permission) {
    if (!hasPermission(permission)) {
      redirect(window.APP_CONFIG.FORBIDDEN_PAGE);
      return false;
    }
    return true;
  }

  function guardAnyPermission(permissions = []) {
    if (!hasAnyPermission(permissions)) {
      redirect(window.APP_CONFIG.FORBIDDEN_PAGE);
      return false;
    }
    return true;
  }

  function guardRole(role) {
    if (!hasRole(role)) {
      redirect(window.APP_CONFIG.FORBIDDEN_PAGE);
      return false;
    }
    return true;
  }

  function guardAnyRole(roles = []) {
    if (!hasAnyRole(roles)) {
      redirect(window.APP_CONFIG.FORBIDDEN_PAGE);
      return false;
    }
    return true;
  }

  function guardPlatformAdmin() {
    if (!window.AppAuth.isPlatformAdmin()) {
      redirect(window.APP_CONFIG.FORBIDDEN_PAGE);
      return false;
    }
    return true;
  }

  function guardAccessAdmin() {
    if (!window.AppAuth.isAccessAdministrator()) {
      redirect(window.APP_CONFIG.FORBIDDEN_PAGE);
      return false;
    }
    return true;
  }

  /* ── محرك الإظهار/الإخفاء (غير مُتلِف) ── */

  function csv(value) {
    return String(value || '')
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean);
  }

  function setVisible(el, visible) {
    if (visible) {
      el.classList.remove('d-none');
      el.style.removeProperty('display');
    } else {
      el.classList.add('d-none');
    }
  }

  /**
   * يطبّق رؤية العناصر حسب الصلاحية/الدور على شجرة DOM.
   * غير مُتلِف: يُبدّل d-none فقط (آمن مع منطق إخفاء الأقسام الفارغة وقابل للتكرار).
   */
  function toggleByPermission(root = document) {
    if (!root || typeof root.querySelectorAll !== 'function') return;

    const auth = window.AppAuth;

    root.querySelectorAll('[data-permission]').forEach((el) =>
      setVisible(el, hasPermission(el.getAttribute('data-permission'))));

    root.querySelectorAll('[data-any-permission]').forEach((el) =>
      setVisible(el, hasAnyPermission(csv(el.getAttribute('data-any-permission')))));

    root.querySelectorAll('[data-all-permissions]').forEach((el) =>
      setVisible(el, hasAllPermissions(csv(el.getAttribute('data-all-permissions')))));

    root.querySelectorAll('[data-role]').forEach((el) =>
      setVisible(el, hasRole(el.getAttribute('data-role'))));

    root.querySelectorAll('[data-any-role]').forEach((el) =>
      setVisible(el, hasAnyRole(csv(el.getAttribute('data-any-role')))));

    root.querySelectorAll('[data-national-admin]').forEach((el) =>
      setVisible(el, auth.isNationalAdmin()));

    root.querySelectorAll('[data-platform-admin]').forEach((el) =>
      setVisible(el, auth.isPlatformAdmin()));

    root.querySelectorAll('[data-access-admin]').forEach((el) =>
      setVisible(el, auth.isAccessAdministrator()));

    root.querySelectorAll('[data-branch-manager-only]').forEach((el) =>
      setVisible(el, auth.isBranchManager()));

    root.querySelectorAll('[data-hide-from-branch-manager]').forEach((el) =>
      setVisible(el, !auth.isBranchManager()));

    root.querySelectorAll('[data-hide-from-institutional-partner]').forEach((el) => {
      const isInstitutionalPartner = auth.hasRole('consultant_union_admin')
        || auth.hasRole('central_bank_admin')
        || auth.hasRole('consultant_office')
        || auth.hasRole('funding_partner');
      setVisible(el, !isInstitutionalPartner);
    });

    root.querySelectorAll('[data-auth-only]').forEach((el) =>
      setVisible(el, auth.isLoggedIn()));

    root.querySelectorAll('[data-guest-only]').forEach((el) =>
      setVisible(el, !auth.isLoggedIn()));

    root.querySelectorAll('.workforce-pending-link').forEach((el) =>
      setVisible(el, true));

    root.querySelectorAll('.workforce-api-link').forEach((el) =>
      setVisible(el, auth.isLoggedIn()));

    // يُطبَّق آخراً ليتفوّق على صلاحيات العرض العامة
    root.querySelectorAll('[data-hide-role]').forEach((el) => {
      if (hasAnyRole(csv(el.getAttribute('data-hide-role')))) {
        setVisible(el, false);
      }
    });
  }

  return {
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    hasRole,
    hasAnyRole,
    guardPermission,
    guardAnyPermission,
    guardAnyRole,
    guardRole,
    guardPlatformAdmin,
    guardAccessAdmin,
    toggleByPermission,
  };

})();
