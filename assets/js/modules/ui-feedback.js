/**
 * Shared success/error popup + toast feedback.
 * Usage:
 *   AppFeedback.success('تم الحفظ بنجاح', 'تم إرسال الطلب للمراجعة.');
 *   AppFeedback.error('تعذر الإرسال', 'أكمل الحقول المطلوبة.');
 *   AppFeedback.toast('تم التحديث', 'success');
 *   AppFeedback.prompt({ title, text, placeholder, required }) -> Promise<string|null>
 */
(function () {
  const STYLE_ID = 'app-feedback-styles';
  const ROOT_ID = 'appFeedbackRoot';

  function ensureStyles() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = `
      #${ROOT_ID} { position: relative; z-index: 10050; }
      #appFeedbackToast {
        position: fixed; bottom: 24px; left: 50%;
        transform: translateX(-50%) translateY(18px);
        max-width: min(920px, calc(100vw - 24px));
        padding: 14px 20px; border-radius: 14px; font-weight: 700; font-size: .95rem;
        z-index: 10051; display: none; align-items: flex-start; gap: 10px;
        box-shadow: 0 14px 36px rgba(6,40,36,.18); line-height: 1.55;
        opacity: 0; transition: opacity .2s ease, transform .2s ease;
      }
      #appFeedbackToast.is-visible {
        display: flex; opacity: 1; transform: translateX(-50%) translateY(0);
      }
      #appFeedbackToast.toast-ok {
        background: #dcfce7; color: #15803d; border: 1.5px solid #4ade80;
      }
      #appFeedbackToast.toast-err {
        background: #fee2e2; color: #991b1b; border: 1.5px solid #f87171;
      }
      #appFeedbackToast i { font-size: 1.15rem; margin-top: .1rem; flex: 0 0 auto; }
      #appFeedbackModal {
        position: fixed; inset: 0; z-index: 10052;
        display: none; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .55); padding: 16px;
      }
      #appFeedbackModal.is-visible { display: flex; }
      #appFeedbackModal .afb-box {
        background: #fff; border-radius: 18px; padding: 28px 22px 20px;
        max-width: 440px; width: 100%; text-align: center;
        box-shadow: 0 22px 55px rgba(0,0,0,.28);
        animation: afbPop .22s ease;
      }
      @keyframes afbPop {
        from { opacity: 0; transform: scale(.94) translateY(8px); }
        to { opacity: 1; transform: none; }
      }
      #appFeedbackModal .afb-icon {
        width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 14px;
        display: flex; align-items: center; justify-content: center; font-size: 1.9rem;
      }
      #appFeedbackModal.afb-ok .afb-icon { background: #dcfce7; color: #16a34a; }
      #appFeedbackModal.afb-err .afb-icon { background: #fee2e2; color: #dc2626; }
      #appFeedbackModal.afb-info .afb-icon { background: #e0f2fe; color: #0284c7; }
      #appFeedbackModal .afb-title {
        font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;
      }
      #appFeedbackModal .afb-text {
        font-size: .92rem; color: #64748b; margin-bottom: 18px; line-height: 1.75;
        white-space: pre-wrap; word-break: break-word;
      }
      #appFeedbackModal .afb-input {
        width: 100%; border: 1px solid rgba(23,148,123,.2); border-radius: 12px;
        padding: 11px 12px; margin-bottom: 16px; font-size: .92rem; font-weight: 600;
        color: #16332E; background: #f8fcfb;
      }
      #appFeedbackModal .afb-input:focus {
        outline: none; border-color: #17947B; box-shadow: 0 0 0 3px rgba(23,148,123,.15);
      }
      #appFeedbackModal .afb-actions {
        display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;
      }
      #appFeedbackModal .afb-btn {
        border: none; border-radius: 12px; padding: 10px 18px; font-weight: 800;
        cursor: pointer; min-width: 110px;
      }
      #appFeedbackModal .afb-btn-primary {
        background: linear-gradient(135deg, #17947B, #06AA89); color: #fff;
      }
      #appFeedbackModal .afb-btn-ghost {
        background: #eef7f4; color: #0f4f47;
      }
      @media (max-width: 575.98px) {
        #appFeedbackToast {
          bottom: 16px; font-size: .88rem; padding: 12px 14px;
          max-width: calc(100vw - 16px);
        }
        #appFeedbackModal .afb-box { padding: 22px 16px 16px; border-radius: 16px; }
        #appFeedbackModal .afb-btn { width: 100%; }
      }
    `;
    document.head.appendChild(style);
  }

  function ensureDom() {
    ensureStyles();
    let root = document.getElementById(ROOT_ID);
    if (!root) {
      root = document.createElement('div');
      root.id = ROOT_ID;
      root.innerHTML = `
        <div id="appFeedbackToast" role="status" aria-live="polite"></div>
        <div id="appFeedbackModal" role="dialog" aria-modal="true" aria-labelledby="appFeedbackTitle">
          <div class="afb-box">
            <div class="afb-icon"><i class="bi bi-check-circle-fill" id="appFeedbackIcon"></i></div>
            <div class="afb-title" id="appFeedbackTitle">تنبيه</div>
            <div class="afb-text" id="appFeedbackText"></div>
            <input type="text" class="afb-input d-none" id="appFeedbackInput" autocomplete="off">
            <div class="afb-actions">
              <button type="button" class="afb-btn afb-btn-ghost d-none" id="appFeedbackCancel">إلغاء</button>
              <button type="button" class="afb-btn afb-btn-primary" id="appFeedbackOk">حسناً</button>
            </div>
          </div>
        </div>`;
      document.body.appendChild(root);
    }
    return {
      toast: document.getElementById('appFeedbackToast'),
      modal: document.getElementById('appFeedbackModal'),
      icon: document.getElementById('appFeedbackIcon'),
      title: document.getElementById('appFeedbackTitle'),
      text: document.getElementById('appFeedbackText'),
      input: document.getElementById('appFeedbackInput'),
      ok: document.getElementById('appFeedbackOk'),
      cancel: document.getElementById('appFeedbackCancel'),
    };
  }

  let toastTimer = null;
  let resolvePrompt = null;

  function closeModal() {
    const ui = ensureDom();
    ui.modal.classList.remove('is-visible', 'afb-ok', 'afb-err', 'afb-info');
    ui.input.classList.add('d-none');
    ui.cancel.classList.add('d-none');
    ui.input.value = '';
    if (resolvePrompt) {
      const done = resolvePrompt;
      resolvePrompt = null;
      done(null);
    }
  }

  function toast(message, type = 'success') {
    const ui = ensureDom();
    const ok = type !== 'error' && type !== 'danger';
    ui.toast.className = `${ok ? 'toast-ok' : 'toast-err'} is-visible`;
    ui.toast.innerHTML = ok
      ? `<i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>${message}</span>`
      : `<i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i><span>${message}</span>`;
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      ui.toast.classList.remove('is-visible');
    }, ok ? 4200 : 5600);
  }

  function popup(opts = {}) {
    const {
      title = 'تنبيه',
      text = '',
      type = 'success',
      okLabel = 'حسناً',
      onClose = null,
    } = opts;
    const ui = ensureDom();
    const kind = type === 'error' || type === 'danger' ? 'err' : (type === 'info' ? 'info' : 'ok');
    ui.modal.className = `afb-${kind} is-visible`;
    ui.title.textContent = title;
    ui.text.textContent = text || '';
    ui.ok.textContent = okLabel;
    ui.icon.className = kind === 'err'
      ? 'bi bi-exclamation-circle-fill'
      : (kind === 'info' ? 'bi bi-info-circle-fill' : 'bi bi-check-circle-fill');
    ui.input.classList.add('d-none');
    ui.cancel.classList.add('d-none');

    const finish = () => {
      ui.modal.classList.remove('is-visible', 'afb-ok', 'afb-err', 'afb-info');
      if (typeof onClose === 'function') onClose();
    };
    ui.ok.onclick = finish;
    ui.modal.onclick = (e) => {
      if (e.target === ui.modal) finish();
    };

    toast(title, kind === 'err' ? 'error' : 'success');
  }

  function success(title, text = '') {
    popup({ title, text, type: 'success' });
  }

  function error(title, text = '') {
    popup({ title, text, type: 'error' });
  }

  function prompt(opts = {}) {
    const {
      title = 'أدخل ملاحظة',
      text = '',
      placeholder = '',
      okLabel = 'تأكيد',
      cancelLabel = 'إلغاء',
      required = false,
      defaultValue = '',
    } = opts;

    return new Promise((resolve) => {
      const ui = ensureDom();
      resolvePrompt = resolve;
      ui.modal.className = 'afb-info is-visible';
      ui.title.textContent = title;
      ui.text.textContent = text || '';
      ui.icon.className = 'bi bi-chat-left-text-fill';
      ui.input.classList.remove('d-none');
      ui.input.placeholder = placeholder;
      ui.input.value = defaultValue || '';
      ui.cancel.classList.remove('d-none');
      ui.cancel.textContent = cancelLabel;
      ui.ok.textContent = okLabel;

      const finish = (value) => {
        ui.modal.classList.remove('is-visible', 'afb-ok', 'afb-err', 'afb-info');
        ui.input.classList.add('d-none');
        ui.cancel.classList.add('d-none');
        const done = resolvePrompt;
        resolvePrompt = null;
        if (done) done(value);
      };

      ui.ok.onclick = () => {
        const value = String(ui.input.value || '').trim();
        if (required && !value) {
          ui.input.focus();
          return;
        }
        finish(value);
      };
      ui.cancel.onclick = () => finish(null);
      ui.modal.onclick = (e) => {
        if (e.target === ui.modal) finish(null);
      };
      setTimeout(() => ui.input.focus(), 50);
    });
  }

  function fromMessage(text, type = 'success') {
    const ok = type !== 'error' && type !== 'danger';
    popup({
      title: ok ? 'تمت العملية بنجاح' : 'حدث خطأ',
      text: text || '',
      type: ok ? 'success' : 'error',
    });
  }

  window.AppFeedback = {
    toast,
    popup,
    success,
    error,
    prompt,
    fromMessage,
    close: closeModal,
  };
})();
