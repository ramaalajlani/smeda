document.addEventListener('DOMContentLoaded', async () => {

  const ok = await window.AppBootstrapAuth.init({

    requireAuth: true,

    requiredAnyPermissions: [

      window.AppPermissions.APPROVE_CENTER_CERTIFICATES,

      window.AppPermissions.APPROVE_TRAINING_CERTIFICATES,

      window.AppPermissions.APPROVE_DEPUTY_CERTIFICATES,

      window.AppPermissions.APPROVE_GENERAL_DIRECTOR_CERTIFICATES,

    ],

  });

  if (!ok) return;



  const messageBox = document.getElementById('signatureMessage');

  const preview = document.getElementById('signaturePreview');

  const form = document.getElementById('signatureUploadForm');

  const fileInput = document.getElementById('signatureFile');

  const deactivateBtn = document.getElementById('deactivateSignatureBtn');



  let previewObjectUrl = null;



  function clearPreviewUrl() {

    if (previewObjectUrl) {

      URL.revokeObjectURL(previewObjectUrl);

      previewObjectUrl = null;

    }

  }



  function showMessage(text, type = 'success') {

    if (!messageBox) return;

    messageBox.className = `signature-message ${type}`;

    messageBox.textContent = text;

  }



  function showNoSignature() {

    clearPreviewUrl();

    if (preview) {

      preview.innerHTML = SiteI18n.ta('<span class="text-muted">لا يوجد توقيع مفعّل</span>');

    }

  }



  async function loadSignaturePreview() {

    if (!preview) return;



    clearPreviewUrl();



    try {

      const res = await window.APP_API.get(window.APP_ROUTES.myElectronicSignature());

      const data = res?.data;



      if (!data) {

        showNoSignature();

        return;

      }



      const blob = await window.APP_API.getBlob(window.APP_ROUTES.myElectronicSignatureImage());

      previewObjectUrl = URL.createObjectURL(blob);

      preview.innerHTML = `<img src=")${previewObjectUrl}" alt=SiteI18n.ta("التوقيع الحالي")>`;

    } catch (e) {

      if (e?.status === 404) {

        showNoSignature();

        return;

      }



      if (e?.status === 401) {

        return;

      }



      preview.innerHTML = SiteI18n.ta('<span class="text-danger">تعذر تحميل التوقيع</span>');

    }

  }



  form?.addEventListener('submit', async (event) => {

    event.preventDefault();

    const file = fileInput?.files?.[0];

    if (!file) {

      showMessage(SiteI18n.ta('يرجى اختيار ملف التوقيع.'), 'error');

      return;

    }

    const fd = new FormData();

    fd.append('signature', file);

    try {

      const res = await window.APP_API.post(window.APP_ROUTES.myElectronicSignature(), fd);

      showMessage(res?.message || SiteI18n.ta('تم رفع التوقيع بنجاح.'), 'success');

      if (fileInput) fileInput.value = '';

      await loadSignaturePreview();

    } catch (e) {

      showMessage(e?.data?.message || SiteI18n.ta('فشل رفع التوقيع.'), 'error');

    }

  });



  deactivateBtn?.addEventListener('click', async () => {

    try {

      const res = await window.APP_API.delete(window.APP_ROUTES.myElectronicSignature());

      showMessage(res?.message || SiteI18n.ta('تم تعطيل التوقيع.'), 'success');

      showNoSignature();

    } catch (e) {

      showMessage(e?.data?.message || SiteI18n.ta('تعذر تعطيل التوقيع.'), 'error');

    }

  });



  window.addEventListener('beforeunload', clearPreviewUrl);



  await loadSignaturePreview();

});

