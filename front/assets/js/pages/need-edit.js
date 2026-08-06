document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.NeedsPlatform.canView()) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const params = new URLSearchParams(window.location.search);
  const id = params.get('id');
  if (!id) {
    window.location.href = 'needs-list.php';
    return;
  }

  const form = document.getElementById('needEditForm');
  const message = document.getElementById('needEditMessage');

  const res = await window.APP_API.get(window.APP_ROUTES.needShow(id));
  const need = res.data || {};
  form.classList.remove('d-none');
  ['title', 'sector', 'need_type', 'priority', 'description'].forEach((field) => {
    if (form.elements[field]) form.elements[field].value = need[field] || '';
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = Object.fromEntries(new FormData(form).entries());
    try {
      const updateRes = await window.APP_API.put(window.APP_ROUTES.needUpdate(id), payload);
      message.className = 'alert alert-success';
      message.textContent = updateRes.message || SiteI18n.ta('تم التحديث');
      message.classList.remove('d-none');
    } catch (err) {
      message.className = 'alert alert-danger';
      message.textContent = err?.message || SiteI18n.ta('تعذر التحديث');
      message.classList.remove('d-none');
    }
  });
});
