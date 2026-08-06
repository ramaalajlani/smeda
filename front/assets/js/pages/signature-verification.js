document.addEventListener('DOMContentLoaded', async () => {
  await window.AppBootstrapAuth.init({ requireAuth: false });

  const form = document.getElementById('signatureVerificationForm');
  const verifyBtn = document.getElementById('verifySignatureBtn');
  const messageBox = document.getElementById('signatureVerificationMessage');
  const statusBox = document.getElementById('signatureStatusBox');
  const statusTitle = document.getElementById('signatureStatusTitle');
  const statusText = document.getElementById('signatureStatusText');
  const resultGrid = document.getElementById('signatureResultGrid');

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function showMessage(text, type = 'error') {
    if (!messageBox) return;
    messageBox.classList.remove('d-none', 'success', 'error');
    messageBox.classList.add(type);
    messageBox.textContent = text;
  }

  function hideMessage() {
    if (!messageBox) return;
    messageBox.classList.add('d-none');
    messageBox.textContent = '';
  }

  function setLoading(isLoading) {
    if (!verifyBtn) return;
    verifyBtn.disabled = isLoading;
    verifyBtn.textContent = isLoading ? 'جاري التحقق...': SiteI18n.ta('تحقق من التوقيع');
  }

  function renderInvalid(message) {
    if (statusBox) {
      statusBox.classList.remove('d-none', 'valid');
      statusBox.classList.add('invalid');
    }
    if (statusTitle) statusTitle.textContent = SiteI18n.ta('توقيع غير صالح');
    if (statusText) statusText.textContent = message;

    if (resultGrid) {
      resultGrid.innerHTML = `
        <div class="col-12">
          <div class="mini-card h-100 text-danger">
            <h3>لم يتم العثور على توقيع صالح</h3>
            <p class="mb-0">${safeText(message)}</p>
          </div>
        </div>
      `;
    }
  }

  function renderValid(data) {
    if (statusBox) {
      statusBox.classList.remove('d-none', 'invalid');
      statusBox.classList.add('valid');
    }
    if (statusTitle) statusTitle.textContent = SiteI18n.ta('توقيع إلكتروني صالح');
    if (statusText) {
      statusText.textContent = SiteI18n.ta('تم التحقق من سلامة التوقيع ولم يُكتشف أي تلاعب.');
    }

    const fields = [
      [SiteI18n.ta('رمز التحقق'), data.verification_code],
      [SiteI18n.ta('الموقّع'), data.signer_name],
      [SiteI18n.ta('المنصب'), data.signer_title || data.role_label],
      [SiteI18n.ta('تاريخ التوقيع'), data.signed_at],
      [SiteI18n.ta('رمز الشهادة'), data.certificate_code],
      [SiteI18n.ta('رقم الشهادة'), data.certificate_number],
    ];

    if (resultGrid) {
      resultGrid.innerHTML = fields.map(([label, value]) => `
        <div class="col-md-6">
          <div class="mini-card h-100">
            <h3>${safeText(label)}</h3>
            <p class="value-text">${safeText(value)}</p>
          </div>
        </div>
      `).join('');
    }
  }

  async function verifyCode(rawCode) {
    const code = String(rawCode || '').trim().toUpperCase();
    if (!code) {
      showMessage(SiteI18n.ta('يرجى إدخال رمز التحقق ESIG.'), 'error');
      return;
    }

    hideMessage();
    setLoading(true);

    try {
      const url = window.APP_ROUTES.signatureVerify(code);
      const response = await fetch(url, {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok || !data.valid) {
        renderInvalid(data.message || SiteI18n.ta('رمز التوقيع غير صالح أو غير موجود.'));
        return;
      }

      renderValid(data);
    } catch (error) {
      console.error('Signature verify error:', error);
      renderInvalid(SiteI18n.ta('تعذر الاتصال بخدمة التحقق. حاول مرة أخرى.'));
    } finally {
      setLoading(false);
    }
  }

  if (form) {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const input = document.getElementById('signatureCode');
      await verifyCode(input?.value);
    });
  }

  const params = new URLSearchParams(window.location.search);
  const preset = params.get('code');
  if (preset) {
    const input = document.getElementById('signatureCode');
    if (input) input.value = preset;
    await verifyCode(preset);
  }
});
