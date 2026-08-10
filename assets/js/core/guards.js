document.addEventListener('DOMContentLoaded', () => {

  const currentPage = window.location.pathname;

  const isLoginPage = currentPage.includes('login.php');
  const isDashboard = currentPage.includes('dashboard.php');

  // ❌ إذا مو مسجل → ممنوع يدخل dashboard
  if (!window.AppAuth.isLoggedIn() && isDashboard) {
    window.location.href = window.APP_CONFIG.LOGIN_PAGE;
    return;
  }

  // ❌ إذا مسجل → ممنوع يرجع login
  if (window.AppAuth.isLoggedIn() && isLoginPage) {
    window.location.href = window.APP_CONFIG.HOME_PAGE;
    return;
  }

});