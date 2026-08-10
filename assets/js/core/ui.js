window.APP_UI = {
  hideLoadingState(element) {
    if (!element) return;
    element.classList.add('d-none');
  },

  showLoadingState(element, text = SiteI18n.ta('جاري التحميل...')) {
    if (!element) return;
    element.classList.remove('d-none');
    element.textContent = text;
  },

  renderEmptyTable(tbody, colspan, message) {
    if (!tbody) return;
    tbody.innerHTML = `
      <tr>
        <td colspan="${colspan}" class="text-center text-muted">${message}</td>
      </tr>
    `;
  },

  renderErrorTable(tbody, colspan, message) {
    if (!tbody) return;
    tbody.innerHTML = `
      <tr>
        <td colspan="${colspan}" class="text-center text-danger">${message}</td>
      </tr>
    `;
  },

  showMessage(element, text, type = 'success') {
    if (!element) return;
    element.classList.remove('d-none', 'success', 'error');
    element.classList.add(type);
    element.textContent = text;
  },

  hideMessage(element) {
    if (!element) return;
    element.classList.add('d-none');
    element.textContent = '';
    element.classList.remove('success', 'error');
  },

  /**
   * Guard against double-submit / double-click.
   * Disables the trigger button synchronously and returns a handle whose
   * done() re-enables it and restores its label — call done() in a finally
   * block so the button is restored on both success and failure.
   *
   * @param {HTMLElement} target A submit button, action button, or a <form>
   *   (its [type="submit"] control is used).
   * @param {string} [loadingText] Optional label shown while in-flight.
   * @returns {{done: Function}|null} null when the button is already
   *   in-flight (caller should abort to avoid a duplicate request).
   */
  beginSubmit(target, loadingText) {
    const btn = target && target.tagName === 'FORM'
      ? target.querySelector('[type="submit"]')
      : target;

    if (!btn) {
      return { done() {} };
    }

    if (btn.disabled) {
      return null;
    }

    btn.dataset.submitOriginalHtml = btn.innerHTML;
    btn.disabled = true;

    if (loadingText != null && loadingText !== '') {
      btn.textContent = loadingText;
    }

    return {
      done() {
        btn.disabled = false;
        if (typeof btn.dataset.submitOriginalHtml === 'string') {
          btn.innerHTML = btn.dataset.submitOriginalHtml;
          delete btn.dataset.submitOriginalHtml;
        }
      },
    };
  },

  hasPermission(permission) {
    if (!window.AppAuth || typeof window.AppAuth.hasPermission !== 'function') {
      return false;
    }

    return window.AppAuth.hasPermission(permission);
  },

  hasAnyPermission(csv) {
    const values = String(csv || '')
      .split(',')
      .map(item => item.trim())
      .filter(Boolean);

    if (!values.length) return false;

    return values.some(permission => this.hasPermission(permission));
  },

  hasAllPermissions(csv) {
    const values = String(csv || '')
      .split(',')
      .map(item => item.trim())
      .filter(Boolean);

    if (!values.length) return false;

    return values.every(permission => this.hasPermission(permission));
  },

  hasAnyRole(csv) {
    if (!window.AppAuth || typeof window.AppAuth.hasRole !== 'function') {
      return false;
    }

    const values = String(csv || '')
      .split(',')
      .map(item => item.trim())
      .filter(Boolean);

    if (!values.length) return false;

    return values.some(role => window.AppAuth.hasRole(role));
  },

  // يفوّض إلى نظام الصلاحيات الوحيد (AppAccess) — تنفيذ واحد لا نظامان.
  applyPermissionVisibility(root = document) {
    if (window.AppAccess && typeof window.AppAccess.toggleByPermission === 'function') {
      window.AppAccess.toggleByPermission(root);
    }
  },

  /**
   * يعرض طبقة "تعذّر الاتصال بالخادم" مع زر إعادة المحاولة — دون مسح الجلسة.
   * تُستخدم عند فشل الخادم/الشبكة (وليس 401/419) حتى لا يُطرد المستخدم.
   * @param {Function} [onRetry] يُستدعى عند الضغط على إعادة المحاولة (الافتراضي: إعادة تحميل الصفحة).
   * @param {string} [message]
   */
  showConnectionError(onRetry, message) {
    const id = 'appConnectionError';
    let overlay = document.getElementById(id);

    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = id;
      overlay.setAttribute('dir', 'rtl');
      overlay.style.cssText =
        'position:fixed;inset:0;z-index:3000;display:flex;align-items:center;justify-content:center;'
        + 'background:rgba(15,23,42,.55);padding:24px;font-family:Tajawal,\'Segoe UI\',Tahoma,sans-serif;';
      overlay.innerHTML = `
        <div style="max-width:440px;width:100%;background:#fff;border-radius:20px;padding:28px 24px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.2)">
          <div style="font-size:2.6rem;line-height:1;margin-bottom:10px">⚠️</div>
          <h3 style="margin:0 0 8px;font-size:1.15rem;font-weight:800;color:#0f172a">تعذّر الاتصال بالخادم</h3>
          <p id="${id}Msg" style="margin:0 0 18px;color:#64748b;font-size:.92rem;line-height:1.9"></p>
          <button type="button" id="${id}Retry"
            style="background:#0f766e;color:#fff;border:0;border-radius:12px;padding:11px 22px;font-weight:700;cursor:pointer">
            إعادة المحاولة
          </button>
        </div>`;
      document.body.appendChild(overlay);
    }

    const msgEl = document.getElementById(id + 'Msg');
    if (msgEl) {
      msgEl.textContent = message
        || 'لم نتمكن من الوصول إلى الخادم. جلستك محفوظة — تأكد من الاتصال ثم أعد المحاولة.';
    }

    overlay.style.display = 'flex';

    const retryBtn = document.getElementById(id + 'Retry');
    if (retryBtn) {
      retryBtn.onclick = () => {
        overlay.style.display = 'none';
        if (typeof onRetry === 'function') {
          onRetry();
        } else {
          window.location.reload();
        }
      };
    }
  },

  hideConnectionError() {
    const overlay = document.getElementById('appConnectionError');
    if (overlay) overlay.style.display = 'none';
  }
};