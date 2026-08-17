document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.MANAGE_TRAINING_CATEGORIES,
  });
  if (!ok) return;

  const tbody = document.getElementById('catTableBody');
  const catMsg = document.getElementById('catMsg');
  const catForm = document.getElementById('catForm');
  const catParent = document.getElementById('catParent');
  let roots = [];

  function msg(t, ok = false) {
    catMsg.textContent = t;
    catMsg.className = 'small mt-2 ' + (ok ? 'text-success' : 'text-danger');
  }

  async function loadRoots() {
    const res = await window.APP_API.get(window.APP_ROUTES.trainingCategories({ roots_only: 1, active_only: 0 }));
    roots = res.data || [];
    catParent.innerHTML = '<option value="">رئيسي</option>' + roots.map((r) =>
      `<option value="${r.id}">${window.APP_HELPERS.e(r.name_ar)}</option>`
    ).join('');
  }

  async function loadTable() {
    const res = await window.APP_API.get(window.APP_ROUTES.trainingCategories({ roots_only: 1, with_children: 1, active_only: 0 }));
    const rows = [];
    (res.data || []).forEach((root) => {
      rows.push({ ...root, kind: 'رئيسي' });
      (root.children || root.active_children || []).forEach((ch) => rows.push({ ...ch, kind: 'فرعي', parentName: root.name_ar }));
    });
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-4">لا توجد تصنيفات</td></tr>';
      return;
    }
    tbody.innerHTML = rows.map((r) => `
      <tr>
        <td>${window.APP_HELPERS.e(r.name_ar)}${r.parentName ? `<div class="small text-muted">${window.APP_HELPERS.e(r.parentName)}</div>` : ''}</td>
        <td>${r.kind}</td>
        <td>${r.sort_order ?? 0}</td>
        <td>${r.is_active ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">معطّل</span>'}</td>
        <td class="text-nowrap">
          <button class="btn btn-sm btn-outline-primary" data-edit="${r.id}">تعديل</button>
          <button class="btn btn-sm btn-outline-danger" data-del="${r.id}">حذف</button>
        </td>
      </tr>
    `).join('');

    tbody.querySelectorAll('[data-edit]').forEach((btn) => btn.addEventListener('click', () => editCat(btn.dataset.edit, rows)));
    tbody.querySelectorAll('[data-del]').forEach((btn) => btn.addEventListener('click', () => deleteCat(btn.dataset.del)));
  }

  function editCat(id, rows) {
    const r = rows.find((x) => String(x.id) === String(id));
    if (!r) return;
    document.getElementById('catId').value = r.id;
    document.getElementById('catNameAr').value = r.name_ar || '';
    document.getElementById('catNameEn').value = r.name_en || '';
    document.getElementById('catSort').value = r.sort_order ?? 0;
    catParent.value = r.parent_id || '';
  }

  async function deleteCat(id) {
    if (!confirm('حذف هذا التصنيف؟')) return;
    await window.APP_API.delete(window.APP_ROUTES.trainingCategoryDelete(id));
    msg('تم الحذف', true);
    catForm.reset();
    document.getElementById('catId').value = '';
    await loadTable();
    await loadRoots();
  }

  catForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('catId').value;
    const payload = {
      name_ar: document.getElementById('catNameAr').value.trim(),
      name_en: document.getElementById('catNameEn').value.trim() || null,
      sort_order: Number(document.getElementById('catSort').value || 0),
      is_active: true,
    };
    const parent = catParent.value;
    if (parent) payload.parent_id = Number(parent);

    try {
      if (id) {
        await window.APP_API.put(window.APP_ROUTES.trainingCategoryUpdate(id), payload);
      } else {
        await window.APP_API.post(window.APP_ROUTES.trainingCategoryStore(), payload);
      }
      msg('تم الحفظ', true);
      catForm.reset();
      document.getElementById('catId').value = '';
      await loadTable();
      await loadRoots();
    } catch (err) {
      msg(err.message || 'فشل الحفظ');
    }
  });

  await loadRoots();
  await loadTable();
});
