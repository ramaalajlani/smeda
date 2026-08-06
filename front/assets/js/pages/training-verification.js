document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: false,
  });
  if (!ok) return;

  const form = document.getElementById('certificateVerificationForm');
  const verifyBtn = document.getElementById('verifyBtn');
  const messageBox = document.getElementById('verificationMessage');
  const resultGrid = document.getElementById('verificationResultGrid');
  const qrBox = document.getElementById('qrBox');
  const qrNote = document.getElementById('qrNote');

  const statusBox = document.getElementById('verificationStatusBox');
  const statusTitle = document.getElementById('verificationStatusTitle');
  const statusText = document.getElementById('verificationStatusText');
  const verificationValueInput = document.getElementById('verificationValue');
  const scanQrBtn = document.getElementById('scanQrBtn');
  const uploadQrBtn = document.getElementById('uploadQrBtn');
  const uploadQrInput = document.getElementById('uploadQrInput');
  const scanPanel = document.getElementById('scanPanel');
  const scanVideo = document.getElementById('scanVideo');
  const stopScanBtn = document.getElementById('stopScanBtn');
  const scanHint = document.getElementById('scanHint');

  let scanStream = null;
  let scanActive = false;
  let scanRafId = null;
  let qrDetector = null;
  let scanDecodeMode = null;
  let scanCanvas = null;
  let scanCtx = null;

  function safeText(value, fallback = '—') {
    return window.APP_HELPERS.e(window.APP_HELPERS.safe(value, fallback));
  }

  function resetResult() {
    if (resultGrid) {
      resultGrid.innerHTML = `
        <div class="col-12">
          <div class="mini-card h-100 verification-placeholder">
            <h3>لا توجد نتيجة بعد</h3>
            <p class="mb-0">أدخل بيانات الشهادة ثم اضغط على ")تحقق الآن".</p>
          </div>
        </div>
      `;
    }

    if (qrBox) {
      qrBox.innerHTML = 'QR';
    }

    if (qrNote) {
      qrNote.textContent = SiteI18n.ta('مكان مخصص لرمز التحقق.');
    }

    if (statusBox) {
      statusBox.classList.add('d-none');
      statusBox.classList.remove('valid', 'invalid');
    }

    if (statusTitle) statusTitle.textContent = '—';
    if (statusText) statusText.textContent = '—';
  }

  function setLoadingState(isLoading) {
    if (!verifyBtn) return;
    verifyBtn.disabled = isLoading;
    verifyBtn.textContent = isLoading ? 'جاري التحقق...': SiteI18n.ta('تحقق الآن');
  }

  function humanCertificateType(value) {
    const map = {
      attendance: SiteI18n.ta('شهادة حضور'),
      pass: SiteI18n.ta('شهادة اجتياز تدريب'),
      completion: SiteI18n.ta('شهادة اجتياز تدريب'),
    };
    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function humanResult(value) {
    const map = {
      pending: SiteI18n.ta('قيد الانتظار'),
      passed: SiteI18n.ta('ناجح'),
      failed: SiteI18n.ta('راسب'),
      review: SiteI18n.ta('مراجعة'),
    };
    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function humanDeliveryMode(value) {
    const map = {
      online: SiteI18n.ta('أونلاين'),
      offline: SiteI18n.ta('حضوري'),
      hybrid: SiteI18n.ta('هجين'),
    };
    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function humanCertificateStatus(value) {
    const map = {
      draft: SiteI18n.ta('مسودة'),
      pending_center_approval: SiteI18n.ta('بانتظار اعتماد المركز'),
      pending_training_approval: SiteI18n.ta('بانتظار قسم التدريب'),
      pending_deputy_approval: SiteI18n.ta('بانتظار نائب المدير'),
      pending_general_director_approval: SiteI18n.ta('بانتظار اعتماد المدير العام'),
      approved: SiteI18n.ta('شهادة معتمدة'),
      rejected: SiteI18n.ta('شهادة مرفوضة'),
      cancelled: SiteI18n.ta('شهادة ملغاة'),
    };
    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  function isApprovedCertificate(cert) {
    return cert?.status === 'approved' && !!cert?.is_verified;
  }

  function setStatus(cert) {
    if (!statusBox || !statusTitle || !statusText) return;

    const approved = isApprovedCertificate(cert);

    statusBox.classList.remove('d-none', 'valid', 'invalid');
    statusBox.classList.add(approved ? 'valid' : 'invalid');

    if (approved) {
      statusTitle.textContent = SiteI18n.ta('الشهادة صحيحة ومعتمدة');
      statusText.textContent = SiteI18n.ta('تم العثور على الشهادة وهي معتمدة وجاهزة للتحقق والطباعة.');
    } else {
      statusTitle.textContent = SiteI18n.ta('تم العثور على الشهادة لكن حالتها غير نهائية');
      statusText.textContent = SiteI18n.ta('تم العثور على السجل، لكن الشهادة ليست معتمدة نهائياً أو ما زالت قيد المعالجة.');
    }
  }

  function renderQr(cert) {
    if (!qrBox) return;

    if (cert?.qr_code_url && isApprovedCertificate(cert)) {
      const qrSrc = safeAttrUrl(cert.qr_code_url);
      if (qrSrc) {
        qrBox.innerHTML = `<img id="verificationQrImage" src="${qrSrc}" alt="QR Code" style="max-width:100%;max-height:220px;object-fit:contain;">`;
      } else {
        qrBox.innerHTML = 'QR';
      }

      const img = document.getElementById('verificationQrImage');
      if (img) {
        img.onerror = function () {
          this.onerror = null;
          qrBox.innerHTML = 'QR';
          if (qrNote) {
            qrNote.textContent = SiteI18n.ta('تعذر تحميل صورة QR حالياً.');
          }
        };
      }

      if (qrNote) {
        qrNote.textContent = SiteI18n.ta('رمز QR المرتبط بالشهادة.');
      }

      return;
    }

    qrBox.innerHTML = 'QR';

    if (qrNote) {
      qrNote.textContent = isApprovedCertificate(cert)
        ? 'لا يوجد رمز QR متاح لهذه الشهادة.': SiteI18n.ta('رمز QR يظهر فقط بعد الاعتماد النهائي للشهادة.');
    }
  }

  function safeAttrUrl(url) {
    if (!url || typeof url !== 'string') return '';
    try {
      const parsed = new URL(url, window.location.origin);
      if (!['http:', 'https:'].includes(parsed.protocol)) return '';
      return window.APP_HELPERS.e(parsed.href);
    } catch (error) {
      return '';
    }
  }

  function parseScanPayload(rawValue) {
    const scanned = String(rawValue || '').trim();
    if (!scanned) return null;

    let value = scanned;
    let type = 'certificate_code';

    try {
      const parsedUrl = new URL(scanned);
      const p = parsedUrl.searchParams;
      if (p.get('certificate_code')) return { value: p.get('certificate_code').trim(), type: 'certificate_code' };
      if (p.get('certificate_number')) return { value: p.get('certificate_number').trim(), type: 'certificate_number' };
      if (p.get('reference_number')) return { value: p.get('reference_number').trim(), type: 'reference_number' };
      if (p.get('verification_code')) return { value: p.get('verification_code').trim(), type: 'verification_code' };
      if (p.get('value')) {
        value = p.get('value').trim();
        type = (p.get('type') || 'certificate_code').trim();
        return { value, type };
      }

      const parts = parsedUrl.pathname.split('/').filter(Boolean);
      const lastSegment = parts[parts.length - 1] || '';
      if (parts.includes('verify-certificate') && lastSegment) {
        return { value: decodeURIComponent(lastSegment), type: 'certificate_code' };
      }
    } catch (error) {
      // ليس URL صالح، نتعامل معه كقيمة مباشرة.
    }

    if (/^CERT[-_]/i.test(scanned)) type = 'certificate_number';
    else if (/^REF[-_]/i.test(scanned)) type = 'reference_number';
    else if (/^VER[-_]/i.test(scanned)) type = 'verification_code';

    return { value, type };
  }

  async function runVerification(value, type, useFallback = false) {
    window.APP_UI.hideMessage(messageBox);
    resetResult();

    if (!value) {
      window.APP_UI.showMessage(messageBox, SiteI18n.ta('أدخل قيمة التحقق أولاً.'), 'error');
      return;
    }

    setLoadingState(true);

    const firstType = String(type || 'certificate_code').trim() || 'certificate_code';
    const fallbackTypes = ['certificate_code', 'certificate_number', 'reference_number', 'verification_code'];
    const attempts = [firstType];

    if (useFallback) {
      fallbackTypes.forEach((t) => {
        if (!attempts.includes(t)) attempts.push(t);
      });
    }

    let lastError = null;

    try {
      for (const currentType of attempts) {
        try {
          const result = await window.APP_API.post(window.APP_ROUTES.certificateVerify(), {
            value,
            type: currentType,
          });

          const cert = result?.data;
          renderResult(cert);
          renderQr(cert);
          setStatus(cert);

          window.APP_UI.showMessage(
            messageBox,
            result?.message || SiteI18n.ta('تم التحقق من الشهادة بنجاح.'),
            'success'
          );
          return;
        } catch (error) {
          lastError = error;
        }
      }

      throw lastError || new Error('Verification failed');
    } catch (error) {
      console.error('Verification error:', error);

      if (resultGrid) {
        resultGrid.innerHTML = `
          <div class="col-12">
            <div class="mini-card h-100">
              <h3>لا توجد نتيجة</h3>
              <p class="mb-0">تعذر العثور على الشهادة أو الوصول إلى بياناتها.</p>
            </div>
          </div>
        `;
      }

      if (qrBox) {
        qrBox.innerHTML = 'QR';
      }

      if (qrNote) {
        qrNote.textContent = SiteI18n.ta('لا يوجد رمز تحقق متاح.');
      }

      if (statusBox) {
        statusBox.classList.remove('d-none', 'valid');
        statusBox.classList.add('invalid');
      }

      if (statusTitle) statusTitle.textContent = SiteI18n.ta('فشل التحقق');
      if (statusText) statusText.textContent = error?.data?.message || SiteI18n.ta('لم يتم العثور على الشهادة المطلوبة.');

      window.APP_UI.showMessage(
        messageBox,
        error?.data?.message || SiteI18n.ta('فشل التحقق من الشهادة.'),
        'error'
      );
    } finally {
      setLoadingState(false);
    }
  }

  function setScanHint(text, isError = false) {
    if (!scanHint) return;
    scanHint.textContent = text;
    scanHint.style.color = isError ? '#dc3545' : '#475569';
  }

  function ensureScanCanvas() {
    if (!scanCanvas) {
      scanCanvas = document.createElement('canvas');
      scanCtx = scanCanvas.getContext('2d', { willReadFrequently: true });
    }
    return { canvas: scanCanvas, ctx: scanCtx };
  }

  function stopScanner() {
    scanActive = false;
    if (scanRafId) {
      cancelAnimationFrame(scanRafId);
      scanRafId = null;
    }

    if (scanStream) {
      scanStream.getTracks().forEach((track) => track.stop());
      scanStream = null;
    }

    if (scanVideo) {
      scanVideo.srcObject = null;
    }

    if (scanPanel) {
      scanPanel.classList.add('d-none');
    }

    scanDecodeMode = null;
  }

  async function handleScannedValue(rawValue) {
    const payload = parseScanPayload(rawValue);
    if (!payload?.value) {
      setScanHint(SiteI18n.ta('تمت القراءة لكن لم يمكن استخراج بيانات صالحة.'), true);
      return;
    }

    if (verificationValueInput) verificationValueInput.value = payload.value;
    stopScanner();
    await runVerification(payload.value, payload.type, true);
  }

  async function scanFrameLoop() {
    if (!scanActive || !scanVideo) return;

    try {
      if (scanDecodeMode === 'barcode-detector' && qrDetector) {
        const codes = await qrDetector.detect(scanVideo);
        const qrCode = Array.isArray(codes) ? codes.find((c) => c?.rawValue) : null;
        if (qrCode?.rawValue) {
          await handleScannedValue(qrCode.rawValue);
          return;
        }
      } else if (scanDecodeMode === 'jsqr' && typeof window.jsQR === 'function') {
        if (scanVideo.readyState >= 2 && scanVideo.videoWidth > 0 && scanVideo.videoHeight > 0) {
          const { canvas, ctx } = ensureScanCanvas();
          if (ctx) {
            canvas.width = scanVideo.videoWidth;
            canvas.height = scanVideo.videoHeight;
            ctx.drawImage(scanVideo, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = window.jsQR(imageData.data, imageData.width, imageData.height, {
              inversionAttempts: 'attemptBoth',
            });
            if (code?.data) {
              await handleScannedValue(code.data);
              return;
            }
          }
        }
      }
    } catch (error) {
      // نتجاهل أخطاء الإطار المؤقتة أثناء المسح.
    }

    scanRafId = requestAnimationFrame(scanFrameLoop);
  }

  async function startScanner() {
    if (!scanQrBtn) return;

    const supportsBarcodeDetector = 'BarcodeDetector' in window;
    const supportsJsQr = typeof window.jsQR === 'function';

    if (!supportsBarcodeDetector && !supportsJsQr) {
      window.APP_UI.showMessage(
        messageBox,
        SiteI18n.ta('المتصفح الحالي لا يدعم QR Scan بالكاميرا.'),
        'error'
      );
      return;
    }

    if (!navigator.mediaDevices?.getUserMedia) {
      window.APP_UI.showMessage(
        messageBox,
        SiteI18n.ta('لا يمكن الوصول للكاميرا على هذا الجهاز.'),
        'error'
      );
      return;
    }

    try {
      qrDetector = null;
      if (supportsBarcodeDetector) {
        try {
          qrDetector = new window.BarcodeDetector({ formats: ['qr_code'] });
          scanDecodeMode = 'barcode-detector';
        } catch (error) {
          qrDetector = null;
        }
      }

      if (!scanDecodeMode && supportsJsQr) {
        scanDecodeMode = 'jsqr';
      }

      if (!scanDecodeMode) {
        throw new Error('No QR decoder available');
      }

      scanStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: 'environment' } },
        audio: false,
      });

      if (scanVideo) {
        scanVideo.srcObject = scanStream;
        await scanVideo.play();
      }

      scanActive = true;
      if (scanPanel) scanPanel.classList.remove('d-none');
      setScanHint(
        scanDecodeMode === 'jsqr'
          ? SiteI18n.ta('وضع توافق مفعل. وجّه الكاميرا نحو QR الخاص بالشهادة.')
          : SiteI18n.ta('وجّه الكاميرا نحو QR الخاص بالشهادة.')
      );
      scanFrameLoop();
    } catch (error) {
      stopScanner();
      window.APP_UI.showMessage(
        messageBox,
        SiteI18n.ta('تعذر تشغيل الكاميرا. امنح صلاحية الكاميرا ثم أعد المحاولة.'),
        'error'
      );
    }
  }

  function loadImageFromFile(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error('Invalid image'));
        img.src = String(reader.result || '');
      };
      reader.onerror = () => reject(new Error('Read failed'));
      reader.readAsDataURL(file);
    });
  }

  async function decodeQrFromImageFile(file) {
    if (!file || !file.type.startsWith('image/')) {
      throw new Error('INVALID_FILE');
    }

    const img = await loadImageFromFile(file);
    const { canvas, ctx } = ensureScanCanvas();
    if (!ctx) throw new Error('CANVAS_UNAVAILABLE');

    canvas.width = img.naturalWidth || img.width || 0;
    canvas.height = img.naturalHeight || img.height || 0;
    if (!canvas.width || !canvas.height) throw new Error('INVALID_IMAGE_SIZE');

    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

    if (typeof window.jsQR === 'function') {
      const code = window.jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: 'attemptBoth',
      });
      if (code?.data) return code.data;
    }

    if ('BarcodeDetector' in window) {
      try {
        const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
        const codes = await detector.detect(canvas);
        const qrCode = Array.isArray(codes) ? codes.find((c) => c?.rawValue) : null;
        if (qrCode?.rawValue) return qrCode.rawValue;
      } catch (error) {
        // fallback انتهى
      }
    }

    throw new Error('QR_NOT_FOUND');
  }

  function buildActionButtons(cert) {
    const actions = [];

    const viewUrl = safeAttrUrl(cert?.view_url);
    if (viewUrl) {
      actions.push(`
        <a href="${viewUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
          عرض الشهادة
        </a>
      `);
    }

    const printUrl = safeAttrUrl(cert?.printable_url);
    if (printUrl) {
      actions.push(`
        <a href="${printUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success">
          طباعة
        </a>
      `);
    }

    const verifyUrl = safeAttrUrl(cert?.verify_url);
    if (verifyUrl) {
      actions.push(`
        <a href="${verifyUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark">
          رابط التحقق
        </a>
      `);
    }

    if (!actions.length) {
      return '—';
    }

    return `<div class="verification-actions">${actions.join('')}</div>`;
  }

  function renderResult(cert) {
    if (!resultGrid) return;

    resultGrid.innerHTML = `
      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>اسم المتدرب</h3>
          <div class="value-text">${safeText(cert?.trainee?.name)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>رمز الشهادة</h3>
          <div class="value-text">${safeText(cert?.certificate_code)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>رقم الشهادة</h3>
          <div class="value-text">${safeText(cert?.certificate_number)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>كود الدورة</h3>
          <div class="value-text">${safeText(cert?.course_code || cert?.training_course?.course_code)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>ساعات التدريب</h3>
          <div class="value-text">${safeText(cert?.training_hours ?? cert?.hours_awarded, '0')} ساعة</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>رمز المركز</h3>
          <div class="value-text">${safeText(cert?.center_code || cert?.training_center?.code)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>رمز المدرب</h3>
          <div class="value-text">${safeText(cert?.trainer_code || cert?.trainer?.trainer_code)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>رمز الحقيبة</h3>
          <div class="value-text">${safeText(cert?.kit_code || cert?.training_kit?.code)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>رمز المتدرب</h3>
          <div class="value-text">${safeText(cert?.trainee_code || cert?.trainee?.trainee_code)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>الرقم المرجعي</h3>
          <div class="value-text">${safeText(cert?.reference_number)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>كود التحقق</h3>
          <div class="value-text">${safeText(cert?.verification_code)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>نوع الشهادة</h3>
          <div class="value-text mb-2">${window.APP_HELPERS.badgeHtml(cert?.certificate_type)}</div>
          <div class="small text-muted">${safeText(humanCertificateType(cert?.certificate_type))}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>الحالة</h3>
          <div class="value-text mb-2">${window.APP_HELPERS.badgeHtml(cert?.status)}</div>
          <div class="small text-muted">${safeText(humanCertificateStatus(cert?.status))}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>النتيجة</h3>
          <div class="value-text mb-2">${window.APP_HELPERS.badgeHtml(cert?.result)}</div>
          <div class="small text-muted">${safeText(humanResult(cert?.result))}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>العلامة / الساعات</h3>
          <div class="value-text">
            ${safeText(cert?.score, '—')} / ${safeText(cert?.hours_awarded, '0')} ساعة
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>الدورة التدريبية</h3>
          <div class="value-text">${safeText(cert?.training_course?.title || cert?.training_program?.name || cert?.training_kit?.name)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>نوع التنفيذ</h3>
          <div class="value-text">${safeText(humanDeliveryMode(cert?.training_course?.delivery_mode))}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>المركز التدريبي</h3>
          <div class="value-text">${safeText(cert?.training_center?.name)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>المدرب</h3>
          <div class="value-text">${safeText(cert?.trainer?.name)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>تاريخ الإصدار</h3>
          <div class="value-text">${safeText(cert?.issue_date)}</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mini-card h-100">
          <h3>الإجراءات</h3>
          ${buildActionButtons(cert)}
        </div>
      </div>
    `;
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const value = verificationValueInput?.value?.trim();
    await runVerification(value, 'certificate_code', true);
  });

  scanQrBtn?.addEventListener('click', async () => {
    await startScanner();
  });

  uploadQrBtn?.addEventListener('click', () => {
    uploadQrInput?.click();
  });

  uploadQrInput?.addEventListener('change', async () => {
    const file = uploadQrInput.files?.[0];
    if (!file) return;

    try {
      stopScanner();
      setScanHint(SiteI18n.ta('جاري قراءة صورة QR...'));
      const rawValue = await decodeQrFromImageFile(file);
      await handleScannedValue(rawValue);
    } catch (error) {
      const msg = error?.message === 'QR_NOT_FOUND'
        ? SiteI18n.ta('لم يتم العثور على QR داخل الصورة.')
        : SiteI18n.ta('تعذر قراءة الصورة. جرّب صورة أوضح.');
      window.APP_UI.showMessage(messageBox, msg, 'error');
    } finally {
      uploadQrInput.value = '';
    }
  });

  stopScanBtn?.addEventListener('click', () => {
    stopScanner();
  });

  window.addEventListener('beforeunload', () => {
    stopScanner();
  });
});