document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.MANAGE_KITS,
  });
  if (!ok) return;

  const params = new URLSearchParams(location.search);
  const kitId = params.get('id');
  const form = document.getElementById('bagForm');
  const msg = document.getElementById('formMsg');
  const saveBtn = document.getElementById('saveBtn');
  let categoriesTree = [];

  document.querySelectorAll('.bag-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.bag-tab').forEach((t) => t.classList.remove('active'));
      document.querySelectorAll('.bag-panel').forEach((p) => p.classList.remove('active'));
      tab.classList.add('active');
      document.querySelector(`.bag-panel[data-panel="${tab.dataset.tab}"]`)?.classList.add('active');
    });
  });

  function showMsg(text, success = false) {
    msg.textContent = text;
    msg.className = 'alert ' + (success ? 'alert-success' : 'alert-danger');
    msg.classList.remove('d-none');
  }

  function val(id) {
    return document.getElementById(id)?.value ?? '';
  }

  function set(id, v) {
    const el = document.getElementById(id);
    if (el) el.value = v ?? '';
  }

  function renderFileStatus(containerId, fileInfo, downloadUrl) {
    const el = document.getElementById(containerId);
    if (!el) return;
    if (!fileInfo?.has_file) {
      el.innerHTML = '<span class="text-muted small">لا يوجد ملف مرفوع</span>';
      return;
    }
    el.innerHTML = `<span class="file-badge"><i class="bi bi-file-earmark-pdf"></i> ${window.APP_HELPERS.e(fileInfo.original_name || 'ملف')}</span>
      <a class="btn btn-sm btn-outline-primary ms-2" href="#" data-download="${downloadUrl}">تحميل</a>`;
    el.querySelector('[data-download]')?.addEventListener('click', async (e) => {
      e.preventDefault();
      await downloadProtected(downloadUrl, fileInfo.original_name);
    });
  }

  async function downloadProtected(url, filename) {
    const r = await fetch(url, { headers: { Authorization: `Bearer ${window.AppAuth.getToken()}`, Accept: 'application/json' } });
    if (!r.ok) {
      showMsg('تعذّر تحميل الملف.');
      return;
    }
    const blob = await r.blob();
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename || 'file.pdf';
    a.click();
    URL.revokeObjectURL(a.href);
  }

  async function loadCategories() {
    const res = await window.APP_API.get(window.APP_ROUTES.trainingCategories({ roots_only: 1, with_children: 1, active_only: 1 }));
    categoriesTree = res.data || [];
    const catSel = document.getElementById('category_id');
    catSel.innerHTML = '<option value="">—</option>' + categoriesTree.map((c) =>
      `<option value="${c.id}">${window.APP_HELPERS.e(c.name_ar)}</option>`
    ).join('');
  }

  function fillSubcategories(parentId, selected) {
    const subSel = document.getElementById('subcategory_id');
    const parent = categoriesTree.find((c) => String(c.id) === String(parentId));
    const children = parent?.active_children || parent?.children || [];
    subSel.innerHTML = '<option value="">—</option>' + children.map((c) =>
      `<option value="${c.id}">${window.APP_HELPERS.e(c.name_ar)}</option>`
    ).join('');
    if (selected) subSel.value = String(selected);
  }

  document.getElementById('category_id')?.addEventListener('change', (e) => fillSubcategories(e.target.value));

  async function loadKit() {
    if (!kitId) return;
    document.getElementById('pageTitle').textContent = 'تعديل حقيبة تدريبية';
    document.getElementById('pageCrumb').textContent = 'تعديل';
    const res = await window.APP_API.get(window.APP_ROUTES.trainingKitShow(kitId));
    const k = res.data || {};
    set('name', k.name);
    set('name_en', k.name_en);
    set('code', k.code);
    set('level', k.level);
    set('short_description', k.short_description);
    set('description', k.description);
    set('sector', k.sector);
    set('hours', k.hours ?? 0);
    set('suggested_days', k.suggested_days);
    set('target_audience', k.target_audience);
    set('prerequisites', k.prerequisites);
    set('objective', k.objective);
    set('expected_outcomes', k.expected_outcomes);
    set('workflow_status', k.workflow_status || 'draft');
    if (k.category_id) {
      set('category_id', k.category_id);
      fillSubcategories(k.category_id, k.subcategory_id);
    }
    renderFileStatus('promoStatus', k.files?.promotional, window.APP_ROUTES.trainingKitPromotionalDownload(kitId));
    renderFileStatus('bagFileStatus', k.files?.training_bag, window.APP_ROUTES.trainingKitBagFileDownload(kitId));
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    saveBtn.disabled = true;
    msg.classList.add('d-none');

    const fd = new FormData();
    fd.append('name', val('name').trim());
    if (val('name_en').trim()) fd.append('name_en', val('name_en').trim());
    if (val('code').trim()) fd.append('code', val('code').trim());
    if (val('level')) fd.append('level', val('level'));
    if (val('short_description').trim()) fd.append('short_description', val('short_description').trim());
    if (val('description').trim()) fd.append('description', val('description').trim());
    if (val('sector').trim()) fd.append('sector', val('sector').trim());
    fd.append('hours', String(Number(val('hours') || 0)));
    if (val('suggested_days')) fd.append('suggested_days', val('suggested_days'));
    if (val('target_audience').trim()) fd.append('target_audience', val('target_audience').trim());
    if (val('prerequisites').trim()) fd.append('prerequisites', val('prerequisites').trim());
    if (val('objective').trim()) fd.append('objective', val('objective').trim());
    if (val('expected_outcomes').trim()) fd.append('expected_outcomes', val('expected_outcomes').trim());
    fd.append('workflow_status', val('workflow_status') || 'draft');
    if (val('category_id')) fd.append('category_id', val('category_id'));
    if (val('subcategory_id')) fd.append('subcategory_id', val('subcategory_id'));

    const promo = document.getElementById('promotional_file')?.files?.[0];
    const bagPdf = document.getElementById('training_bag_file')?.files?.[0];
    if (promo) fd.append('promotional_file', promo);
    if (bagPdf) {
      if (!/\.pdf$/i.test(bagPdf.name) && bagPdf.type !== 'application/pdf') {
        showMsg('ملف الحقيبة يجب أن يكون PDF.');
        saveBtn.disabled = false;
        return;
      }
      fd.append('training_bag_file', bagPdf);
    }

    try {
      const url = kitId ? window.APP_ROUTES.trainingKitShow(kitId) : window.APP_ROUTES.trainingKits();
      const method = kitId ? 'POST' : 'POST';
      if (kitId) fd.append('_method', 'PUT');

      const r = await fetch(url, {
        method,
        headers: { Authorization: `Bearer ${window.AppAuth.getToken()}`, Accept: 'application/json' },
        body: fd,
      });
      const j = await r.json().catch(() => ({}));
      if (!r.ok) throw new Error(j.message || (j.errors && Object.values(j.errors).flat()[0]) || 'فشل الحفظ');

      showMsg(j.message || 'تم الحفظ بنجاح', true);
      setTimeout(() => {
        location.href = 'training-kits-list.php';
      }, 700);
    } catch (err) {
      showMsg(err.message || 'تعذّر الحفظ');
      saveBtn.disabled = false;
    }
  });

  try {
    await loadCategories();
    await loadKit();
  } catch (err) {
    showMsg(err.message || 'تعذّر تحميل البيانات');
  }
});
