document.addEventListener('DOMContentLoaded', async () => {
  const form = document.getElementById('loginForm');
  const messageBox = document.getElementById('loginMessage');
  const loginBtn = document.getElementById('loginBtn');

  if (!form) {
    console.error('Login form not found.');
    return;
  }

  function showMessage(text, type = 'error') {
    if (!messageBox) return;

    messageBox.className = `login-message ${type}`;
    messageBox.classList.remove('d-none');
    messageBox.textContent = text;
  }

  function clearMessage() {
    if (!messageBox) return;

    messageBox.className = 'login-message d-none';
    messageBox.textContent = '';
  }

  function setLoadingState(isLoading) {
    if (!loginBtn) return;
    loginBtn.disabled = isLoading;
    loginBtn.textContent = isLoading
      ? (window.SiteI18n?.t('auth_logging_in') || '...')
      : (window.SiteI18n?.t('auth_submit_login') || 'Sign in');
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    clearMessage();
    setLoadingState(true);

    const payload = {
      email: document.getElementById('emailInput')?.value?.trim() || '',
      password: document.getElementById('passwordInput')?.value || '',
      device_name: 'front-web',
    };

    if (!payload.email || !payload.password) {
      showMessage(window.SiteI18n?.t('auth_fill_email_password') || 'Please enter email and password.', 'error');
      setLoadingState(false);
      return;
    }

    try {
      await window.AppAuth.login(payload);
      showMessage(window.SiteI18n?.t('auth_login_success') || 'Signed in successfully.', 'success');
    } catch (error) {
      console.error('Login error:', error);
      showMessage(
        error?.data?.message || window.SiteI18n?.t('auth_login_failed') || 'Sign in failed.',
        'error'
      );
    } finally {
      setLoadingState(false);
    }
  });
});