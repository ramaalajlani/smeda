document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.CREATE_CENTER_REGISTRATION_REQUESTS,
  });
  if (!ok) return;

  const form = document.getElementById('centerRegistrationRequestForm');
  const submitBtn = document.getElementById('submitCenterRegistrationBtn');
  const resetBtn = document.getElementById('resetCenterRegistrationBtn');
  const messageBox = document.getElementById('centerRegistrationMessage');

  const nameInput = document.getElementById('centerName');
  const cityInput = document.getElementById('centerCity');
  const addressInput = document.getElementById('centerAddress');
  const phoneInput = document.getElementById('centerPhone');
  const emailInput = document.getElementById('centerEmail');
  const classificationInput = document.getElementById('centerClassification');
  const supportsOfflineInput = document.getElementById('supportsOfflineTraining');
  const supportsOnlineInput = document.getElementById('supportsOnlineTraining');

  const latitudeInput = document.getElementById('centerLatitude');
  const longitudeInput = document.getElementById('centerLongitude');
  const latitudePreview = document.getElementById('latitudePreview');
  const longitudePreview = document.getElementById('longitudePreview');

  const licenseNumberInput = document.getElementById('licenseNumber');
  const licenseIssueDateInput = document.getElementById('licenseIssueDate');
  const licenseIssuedByInput = document.getElementById('licenseIssuedBy');
  const licenseImageInput = document.getElementById('licenseImage');
  const licenseFilePreview = document.getElementById('licenseFilePreview');

  let map = null;
  let marker = null;

  function safe(value, fallback = '—') {
    return window.APP_HELPERS.safe(value, fallback);
  }

  function escapeHtml(value) {
    return window.APP_HELPERS.e(value);
  }

  function showMessage(text, type = 'success') {
    if (!messageBox) return;
    messageBox.className = `registration-message ${type}`;
    messageBox.textContent = text;
  }

  function hideMessage() {
    if (!messageBox) return;
    messageBox.className = 'registration-message';
    messageBox.textContent = '';
  }

  function setLoadingState(isLoading) {
    if (!submitBtn) return;
    submitBtn.disabled = isLoading;
    submitBtn.textContent = isLoading ? 'جارٍ الإرسال...': SiteI18n.ta('إرسال الطلب');
  }

  function updateCoords(lat, lng) {
    const latFixed = Number(lat).toFixed(6);
    const lngFixed = Number(lng).toFixed(6);

    if (latitudeInput) latitudeInput.value = latFixed;
    if (longitudeInput) longitudeInput.value = lngFixed;

    if (latitudePreview) latitudePreview.textContent = latFixed;
    if (longitudePreview) longitudePreview.textContent = lngFixed;
  }

  function initMap() {
    const mapElement = document.getElementById('centerLocationMap');
    if (!mapElement || typeof L === 'undefined') return;

    const defaultLat = 35.9306;
    const defaultLng = 36.6339;

    map = L.map('centerLocationMap').setView([defaultLat, defaultLng], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    map.on('click', function (e) {
      const { lat, lng } = e.latlng;

      if (!marker) {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', function (event) {
          const position = event.target.getLatLng();
          updateCoords(position.lat, position.lng);
        });
      } else {
        marker.setLatLng([lat, lng]);
      }

      updateCoords(lat, lng);
    });

    setTimeout(() => {
      map.invalidateSize();
    }, 300);
  }

  function resetMapSelection() {
    if (marker && map) {
      map.removeLayer(marker);
      marker = null;
    }

    if (latitudeInput) latitudeInput.value = '';
    if (longitudeInput) longitudeInput.value = '';
    if (latitudePreview) latitudePreview.textContent = '—';
    if (longitudePreview) longitudePreview.textContent = '—';
  }

  function handleFilePreview() {
    const file = licenseImageInput?.files?.[0];

    if (!licenseFilePreview) return;

    if (!file) {
      licenseFilePreview.classList.remove('show');
      licenseFilePreview.textContent = '';
      return;
    }

    licenseFilePreview.classList.add('show');
    licenseFilePreview.innerHTML = `
      <div>اسم الملف: ${escapeHtml(file.name)}</div>
      <div>الحجم: ${escapeHtml((file.size / 1024).toFixed(1))} KB</div>
      <div>النوع: ${escapeHtml(file.type || ')غير معروف')}</div>
    `;
  }

  function resetForm() {
    form?.reset();
    resetMapSelection();
    handleFilePreview();
  }

  function validatePayload() {
    if (!nameInput?.value?.trim()) return SiteI18n.ta('يرجى إدخال اسم المركز.');
    if (!cityInput?.value?.trim()) return SiteI18n.ta('يرجى إدخال المدينة.');
    if (!addressInput?.value?.trim()) return SiteI18n.ta('يرجى إدخال العنوان.');
    if (!phoneInput?.value?.trim()) return SiteI18n.ta('يرجى إدخال رقم الهاتف.');
    if (!licenseNumberInput?.value?.trim()) return SiteI18n.ta('يرجى إدخال رقم الترخيص.');
    if (!licenseIssueDateInput?.value?.trim()) return SiteI18n.ta('يرجى إدخال تاريخ الترخيص.');
    if (!licenseIssuedByInput?.value?.trim()) return SiteI18n.ta('يرجى إدخال الجهة الصادرة عنها.');
    if (!licenseImageInput?.files?.length) return SiteI18n.ta('يرجى رفع صورة الترخيص.');
    if (!latitudeInput?.value || !longitudeInput?.value) return SiteI18n.ta('يرجى تحديد موقع المركز على الخريطة.');

    return null;
  }

  function buildFormData() {
    const formData = new FormData();

    formData.append('center_name', nameInput?.value?.trim() || '');
    formData.append('city', cityInput?.value?.trim() || '');
    formData.append('address', addressInput?.value?.trim() || '');
    formData.append('phone', phoneInput?.value?.trim() || '');
    formData.append('email', emailInput?.value?.trim() || '');
    formData.append('classification_requested', classificationInput?.value?.trim() || '');
    formData.append('supports_offline_training', supportsOfflineInput?.value || '0');
    formData.append('supports_online_training', supportsOnlineInput?.value || '0');

    formData.append('latitude', latitudeInput?.value || '');
    formData.append('longitude', longitudeInput?.value || '');

    formData.append('license_number', licenseNumberInput?.value?.trim() || '');
    formData.append('license_issue_date', licenseIssueDateInput?.value || '');
    formData.append('license_issued_by', licenseIssuedByInput?.value?.trim() || '');

    if (licenseImageInput?.files?.[0]) {
      formData.append('license_image', licenseImageInput.files[0]);
    }

    return formData;
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideMessage();

    const validationMessage = validatePayload();
    if (validationMessage) {
      showMessage(validationMessage, 'error');
      return;
    }

    setLoadingState(true);

    try {
      const payload = buildFormData();

      const response = await window.APP_API.post(
        window.APP_ROUTES.centerRegistrationRequestStore(),
        payload
      );

      showMessage(
        response?.message || SiteI18n.ta('تم إرسال طلب تسجيل المركز بنجاح.'),
        'success'
      );

      resetForm();
    } catch (error) {
      console.error('Center registration request error:', error);

      showMessage(
        error?.data?.message || SiteI18n.ta('تعذر إرسال طلب تسجيل المركز.'),
        'error'
      );
    } finally {
      setLoadingState(false);
    }
  });

  resetBtn?.addEventListener('click', () => {
    hideMessage();
    resetForm();
  });

  licenseImageInput?.addEventListener('change', handleFilePreview);

  initMap();
});