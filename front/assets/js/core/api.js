window.APP_API = (() => {
  function baseUrl() {
    return window.APP_CONFIG?.API_BASE_URL || 'http://127.0.0.1:8000/api';
  }

  function getToken() {
    return localStorage.getItem('authority_token')
      || localStorage.getItem('authority_api_token')
      || '';
  }

  function normalizeUrl(url = '') {
    const root = baseUrl();
    if (!url) return root;

    if (/^https?:\/\//i.test(url)) {
      return url;
    }

    if (url.startsWith('/')) {
      return `${root}${url}`;
    }

    return `${root}/${url}`;
  }

  function withQuery(url, params = {}) {
    const finalUrl = normalizeUrl(url);

    if (!params || typeof params !== 'object') {
      return finalUrl;
    }

    const searchParams = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        searchParams.append(key, value);
      }
    });

    const queryString = searchParams.toString();
    return queryString ? `${finalUrl}?${queryString}` : finalUrl;
  }

  async function request(url, options = {}) {
    const finalUrl = normalizeUrl(url);

    const hasBody =
      options.body &&
      !(options.body instanceof FormData);

    const headers = {
      Accept: 'application/json',
      ...(hasBody ? { 'Content-Type': 'application/json' } : {}),
      ...(getToken() ? { Authorization: `Bearer ${getToken()}` } : {}),
      ...(options.headers || {}),
    };

    let response;
    let data;

    try {
      response = await fetch(finalUrl, {
        ...options,
        headers,
      });
    } catch (networkError) {
      throw {
        status: 0,
        data: {
          message: SiteI18n.ta('تعذر الاتصال بالخادم. تأكد أن Laravel يعمل: php artisan serve (منفذ 8000).'),
          error: networkError?.message || 'Network Error',
        },
      };
    }

    const contentType = response.headers.get('content-type') || '';
    data = contentType.includes('application/json')
      ? await response.json()
      : await response.text();

    if (!response.ok) {
      // امسح الجلسة فقط عند انتهاء التوكن/الجلسة (401) أو انتهاء صلاحية CSRF/الجلسة (419).
      // لا تمسح عند أخطاء الخادم (5xx) أو الشبكة (status 0).
      if (response.status === 401 || response.status === 419) {
        localStorage.removeItem('authority_token');
        localStorage.removeItem('authority_user');
        localStorage.removeItem('authority_user_fetched_at');
        localStorage.removeItem('authority_api_token');
        localStorage.removeItem('authority_api_user');

        const loginPage = window.APP_CONFIG?.LOGIN_PAGE;
        const onAuthPage = /login\.php|register\.php/i.test(window.location.pathname);
        if (loginPage && !onAuthPage) {
          window.location.replace(loginPage);
        }
      }

      throw {
        status: response.status,
        data: data || {
          message: SiteI18n.ta('حدث خطأ أثناء تنفيذ الطلب.'),
        },
      };
    }

    return data;
  }

  function get(url, headers = {}) {
    return request(url, {
      method: 'GET',
      headers,
    });
  }

  function post(url, body = {}, headers = {}) {
    return request(url, {
      method: 'POST',
      body: body instanceof FormData ? body : JSON.stringify(body),
      headers,
    });
  }

  function put(url, body = {}, headers = {}) {
    return request(url, {
      method: 'PUT',
      body: body instanceof FormData ? body : JSON.stringify(body),
      headers,
    });
  }

  function patch(url, body = {}, headers = {}) {
    return request(url, {
      method: 'PATCH',
      body: body instanceof FormData ? body : JSON.stringify(body),
      headers,
    });
  }

  function del(url, body = null, headers = {}) {
    return request(url, {
      method: 'DELETE',
      ...(body !== null
        ? { body: body instanceof FormData ? body : JSON.stringify(body) }
        : {}),
      headers,
    });
  }

  async function getBlob(url, headers = {}) {
    const finalUrl = normalizeUrl(url);
    const token = getToken();

    const response = await fetch(finalUrl, {
      method: 'GET',
      headers: {
        Accept: 'image/*,*/*',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...(headers || {}),
      },
    });

    if (!response.ok) {
      // امسح الجلسة فقط عند انتهاء التوكن/الجلسة (401) أو انتهاء صلاحية CSRF/الجلسة (419).
      // لا تمسح عند أخطاء الخادم (5xx) أو الشبكة (status 0).
      if (response.status === 401 || response.status === 419) {
        localStorage.removeItem('authority_token');
        localStorage.removeItem('authority_user');
        localStorage.removeItem('authority_user_fetched_at');
        localStorage.removeItem('authority_api_token');
        localStorage.removeItem('authority_api_user');

        const loginPage = window.APP_CONFIG?.LOGIN_PAGE;
        const onAuthPage = /login\.php|register\.php/i.test(window.location.pathname);
        if (loginPage && !onAuthPage) {
          window.location.replace(loginPage);
        }
      }

      throw {
        status: response.status,
        data: { message: SiteI18n.ta('تعذر تحميل الملف.') },
      };
    }

    return response.blob();
  }

  return {
    request,
    get,
    post,
    put,
    patch,
    delete: del,
    withQuery,
    getToken,
    getBlob,
    baseUrl,
  };
})();