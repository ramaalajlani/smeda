document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredPermission: window.AppPermissions.VIEW_KITS,
  });
  if (!ok) return;

  const kitId = new URLSearchParams(location.search).get('id');
  const loading = document.getElementById('loading');
  const formWrap = document.getElementById('formWrap');
  const msg = document.getElementById('msg');
  const canManage = window.AppAuth.hasPermission(window.AppPermissions.MANAGE_KITS);

  if (!kitId) {
    loading.textContent = 'معرّف الحقيبة مفقود.';
    return;
  }

  function showMsg(text, type = 'danger') {
    msg.className = `alert alert-${type}`;
    msg.textContent = text;
    msg.classList.remove('d-none');
  }

  function chip(id, label, sub, checked, disabled) {
    return `<label class="kit-chip">
      <input type="checkbox" value="${id}" ${checked ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
      <span>
        <div class="fw-semibold">${window.APP_HELPERS.e(label || '—')}</div>
        <div class="small text-muted">${window.APP_HELPERS.e(sub || '')}</div>
      </span>
    </label>`;
  }

  try {
    const [kitRes, centersRes, trainersRes] = await Promise.all([
      window.APP_API.get(window.APP_ROUTES.trainingKitShow(kitId)),
      window.APP_API.get(window.APP_ROUTES.trainingCenters({ per_page: 200 })),
      window.APP_API.get(window.APP_ROUTES.trainers({ per_page: 200 })),
    ]);

    const kit = kitRes.data || {};
    document.getElementById('kitTitle').textContent = kit.name || 'إدارة الحقيبة';
    document.getElementById('kitSub').textContent = [kit.code, kit.sector, kit.level].filter(Boolean).join(' · ')
      || 'تكليف المراكز والمدربين بهذه الحقيبة.';

    const selectedCenters = new Set((kit.centers || []).map((c) => Number(c.id)));
    const selectedTrainers = new Set((kit.trainers || []).map((t) => Number(t.id)));

    const centers = centersRes.data || [];
    const trainers = trainersRes.data || [];

    document.getElementById('centersBox').innerHTML = centers.length
      ? centers.map((c) => chip(
          c.id,
          c.name,
          [c.code, c.city].filter(Boolean).join(' · '),
          selectedCenters.has(Number(c.id)),
          !canManage
        )).join('')
      : '<div class="text-muted">لا توجد مراكز</div>';

    document.getElementById('trainersBox').innerHTML = trainers.length
      ? trainers.map((t) => chip(
          t.id,
          t.name,
          [t.trainer_code, t.specialization].filter(Boolean).join(' · '),
          selectedTrainers.has(Number(t.id)),
          !canManage
        )).join('')
      : '<div class="text-muted">لا يوجد مدربون</div>';

    loading.classList.add('d-none');
    formWrap.classList.remove('d-none');

    const saveBtn = document.getElementById('saveBtn');
    if (!canManage) {
      saveBtn.disabled = true;
      saveBtn.textContent = 'عرض فقط — لا تملك صلاحية التعديل';
      return;
    }

    saveBtn.addEventListener('click', async () => {
      saveBtn.disabled = true;
      msg.classList.add('d-none');
      const center_ids = [...document.querySelectorAll('#centersBox input:checked')].map((el) => Number(el.value));
      const trainer_ids = [...document.querySelectorAll('#trainersBox input:checked')].map((el) => Number(el.value));
      try {
        await window.APP_API.put(window.APP_ROUTES.trainingKitShow(kitId), { center_ids, trainer_ids });
        showMsg('تم حفظ تكليف المراكز والمدربين بنجاح.', 'success');
      } catch (err) {
        showMsg(err?.message || 'تعذّر الحفظ', 'danger');
      } finally {
        saveBtn.disabled = false;
      }
    });
  } catch (error) {
    console.error(error);
    loading.textContent = 'تعذّر تحميل بيانات الحقيبة.';
  }
});
