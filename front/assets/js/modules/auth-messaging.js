(() => {
  const API_BASE = (window.APP_CONFIG?.API_BASE_URL || 'http://127.0.0.1:8000/api').replace(/\/$/, '');
  const TOKEN_KEY = 'authority_token';
  const USER_KEY = 'authority_user';
  const LOGIN_REDIRECT_PATH = 'services/community/user-message-form.php';

  function getToken() {
    return localStorage.getItem(TOKEN_KEY);
  }

  function setAuth(token, user) {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user || null));
  }

  function clearAuth() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
  }

  function getStoredUser() {
    const raw = localStorage.getItem(USER_KEY);

    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw);
    } catch {
      return null;
    }
  }

  function buildHeaders(withAuth = false) {
    const headers = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    };

    if (withAuth && getToken()) {
      headers.Authorization = `Bearer ${getToken()}`;
    }

    return headers;
  }

  async function apiFetch(path, options = {}) {
    const response = await fetch(`${API_BASE}${path}`, {
      ...options,
      headers: {
        ...buildHeaders(Boolean(options.withAuth)),
        ...(options.headers || {}),
      },
    });

    let data = null;

    try {
      data = await response.json();
    } catch {
      data = null;
    }

    if (!response.ok) {
      const message = data?.message || SiteI18n.ta('حدث خطأ أثناء تنفيذ الطلب.');
      throw new Error(message);
    }

    return data;
  }

  function setFeedback(el, message, type) {
    if (!el) {
      return;
    }

    el.textContent = message;
    el.className = `small mb-3 ${type === 'error' ? 'text-danger' : 'text-success'}`;
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  async function initLoginForm() {
    const loginForm = document.getElementById('apiLoginForm');

    if (!loginForm) {
      return;
    }

    const submitBtn = document.getElementById('loginSubmitBtn');
    const feedback = document.getElementById('loginFeedback');

    loginForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      const formData = new FormData(loginForm);
      const email = (formData.get('email') || '').toString().trim();
      const password = (formData.get('password') || '').toString();

      if (!email || !password) {
        setFeedback(feedback, SiteI18n.ta('يرجى إدخال البريد الإلكتروني وكلمة المرور.'), 'error');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = SiteI18n.ta('جاري تسجيل الدخول...');
      setFeedback(feedback, '', 'success');

      try {
        const data = await apiFetch('/login', {
          method: 'POST',
          body: JSON.stringify({ email, password }),
        });

        setAuth(data.token, data.user);
        setFeedback(feedback, SiteI18n.ta('تم تسجيل الدخول بنجاح. سيتم تحويلك الآن...'), 'success');

        window.setTimeout(() => {
          window.location.href = LOGIN_REDIRECT_PATH;
        }, 500);
      } catch (error) {
        setFeedback(feedback, error.message, 'error');
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = SiteI18n.ta('تسجيل الدخول');
      }
    });
  }

  async function loadCurrentUser() {
    const token = getToken();

    if (!token) {
      return null;
    }

    try {
      const data = await apiFetch('/me', {
        method: 'GET',
        withAuth: true,
      });

      if (data?.user) {
        localStorage.setItem(USER_KEY, JSON.stringify(data.user));
      }

      return data?.user || null;
    } catch {
      clearAuth();
      return null;
    }
  }

  async function protectMessagingPages() {
    const isMessagingPage =
      window.location.pathname.includes('/services/community/user-message-form.php') ||
      window.location.pathname.includes('/services/community/my-messages.php');

    if (!isMessagingPage) {
      return;
    }

    const user = (await loadCurrentUser()) || getStoredUser();

    if (!user) {
      window.location.href = window.APP_CONFIG?.LOGIN_PAGE || '../../login.php';
      return;
    }

    document.querySelectorAll('#authUserName').forEach((el) => {
      el.textContent = user.name || SiteI18n.ta('المستخدم');
    });

    const logoutBtn = document.getElementById('logoutBtn');

    if (logoutBtn) {
      logoutBtn.addEventListener('click', async () => {
        try {
          await apiFetch('/logout', {
            method: 'POST',
            withAuth: true,
          });
        } catch {
          // Ignore logout API errors and clear local auth anyway.
        }

        clearAuth();
        window.location.href = window.APP_CONFIG?.LOGIN_PAGE || '../../login.php';
      });
    }
  }

  async function initSendMessageForm() {
    const sendForm = document.getElementById('sendMessageForm');

    if (!sendForm) {
      return;
    }

    const feedback = document.getElementById('sendMessageFeedback');
    const submitBtn = document.getElementById('sendMessageBtn');

    sendForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      setFeedback(
        feedback,
        SiteI18n.ta('خدمة المراسلة غير مفعّلة بعد — سيتم ربطها بالـ API لاحقاً.'),
        'error'
      );
      if (submitBtn) {
        submitBtn.disabled = true;
      }
    });
  }

  async function renderMessages() {
    const list = document.getElementById('messagesList');
    const feedback = document.getElementById('messagesFeedback');

    if (!list) {
      return;
    }

    list.innerHTML = SiteI18n.ta('<div class="alert alert-light mb-0">خدمة المراسلة غير مفعّلة بعد.</div>');
    setFeedback(feedback, '', 'success');
  }

  function initMessagesPage() {
    const list = document.getElementById('messagesList');

    if (!list) {
      return;
    }

    const refreshBtn = document.getElementById('refreshMessagesBtn');

    renderMessages();

    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => {
        renderMessages();
      });
    }
  }

  document.addEventListener('DOMContentLoaded', async () => {
    await initLoginForm();
    await protectMessagingPages();
    initSendMessageForm();
    initMessagesPage();
  });
})();
