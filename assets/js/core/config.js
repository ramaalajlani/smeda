(function initAppConfig() {
  const protocol = window.location.protocol;
  const host = window.location.hostname;
  const port = window.location.port;
  const origin = port
    ? `${protocol}//${host}:${port}`
    : `${protocol}//${host}`;

  function readMeta(name) {
    const meta = document.querySelector(`meta[name="${name}"]`);
    const value = meta && meta.getAttribute('content');
    return value ? value.replace(/\/$/, '') : '';
  }

  function resolveFrontendBase() {
    const fromMeta = readMeta('frontend-base-url');
    if (fromMeta) {
      return fromMeta;
    }

    const isLocalHost = ['localhost', '127.0.0.1', '::1'].includes(host);
    if (isLocalHost && (!port || port === '80')) {
      return 'http://127.0.0.1:8080';
    }

    return origin.replace(/\/$/, '');
  }

  const frontendBase = resolveFrontendBase();
  const isLocal = ['localhost', '127.0.0.1', '::1'].includes(host);

  function resolveApiBase() {
    const fromMeta = readMeta('api-base-url');
    if (fromMeta) {
      return fromMeta;
    }

    if (isLocal) {
      return 'http://127.0.0.1:8000/api';
    }

    return `${frontendBase}/api/api`;
  }

  function resolveBackendBase() {
    const fromMeta = readMeta('backend-base-url');
    if (fromMeta) {
      return fromMeta;
    }

    if (isLocal) {
      return 'http://127.0.0.1:8000';
    }

    return `${frontendBase}/api`;
  }

  window.APP_CONFIG = {
    API_BASE_URL: resolveApiBase(),
    BACKEND_BASE_URL: resolveBackendBase(),
    FRONTEND_BASE_URL: frontendBase,
    LOGIN_PAGE: `${frontendBase}/login.php`,
    REGISTER_PAGE: `${frontendBase}/register.php`,
    HOME_PAGE: `${frontendBase}/dashboard.php`,
    FORBIDDEN_PAGE: `${frontendBase}/403.php`,
  };
})();
