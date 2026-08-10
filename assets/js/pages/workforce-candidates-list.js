document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const loading = document.getElementById('workforceLoadingBox');
  const tbody = document.getElementById('workforceTableBody');
  const messageBox = document.getElementById('workforceMessageBox');
  const searchInput = document.getElementById('workforceSearchInput');
  const statusFilter = document.getElementById('workforceStatusFilter');
  const searchBtn = document.getElementById('workforceSearchBtn');
  const enrollBox = document.getElementById('workforceEnrollBox');
  const enrollForm = document.getElementById('workforceEnrollForm');
  const enrollTraineeSelect = document.getElementById('enrollTraineeId');

  const canEnroll = window.AppAuth.isPlatformAdmin() || window.AppAuth.hasRole('training_manager');

  function showMessage(text, type = 'error') {
    if (!messageBox) return;
    messageBox.className = `alert alert-${type === 'success' ? 'success' : 'danger'} mt-3`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  function statusLabel(value) {
    const map = {
      active: SiteI18n.ta('نشط'),
      inactive: SiteI18n.ta('غير نشط'),
      suspended: SiteI18n.ta('موقوف'),
    };
    return map[String(value || '').trim()] || window.APP_HELPERS.safe(value);
  }

  async function loadWorkforce() {
    const params = {
      per_page: 100,
      search: searchInput?.value?.trim() || undefined,
      status: statusFilter?.value || undefined,
    };

    const result = await window.APP_API.get(window.APP_ROUTES.workforces(params));
    const rows = result?.data || [];

    tbody.innerHTML = rows.map((item, index) => `
      <tr>
        <td>${index + 1}</td>
        <td>${window.APP_HELPERS.e(item.workforce_code)}</td>
        <td>${window.APP_HELPERS.e(item.trainee?.name)}</td>
        <td>${window.APP_HELPERS.e(item.trainee?.trainee_code)}</td>
        <td>${window.APP_HELPERS.e(item.trainee?.city || '—')}</td>
        <td>${window.APP_HELPERS.e(item.trainee?.phone || item.trainee?.email || '—')}</td>
        <td>${window.APP_HELPERS.e(statusLabel(item.status))}</td>
        <td>${window.APP_HELPERS.e(item.joined_at || '—')}</td>
      </tr>
    `).join('') || SiteI18n.ta('<tr><td colspan="8" class="text-center text-muted">لا توجد سجلات في القوى العاملة.</td></tr>');
  }

  async function loadTraineesForEnroll() {
    if (!canEnroll || !enrollTraineeSelect) return;

    const result = await window.APP_API.get(window.APP_ROUTES.trainees({ per_page: 100 }));
    const rows = result?.data || [];

    enrollTraineeSelect.innerHTML = SiteI18n.ta('<option value="SiteI18n.ta(">— اختر متدرباً —</option>') +
      rows.map((trainee) => `
        <option value=")${trainee.id}">
          ${window.APP_HELPERS.e(trainee.name)} — ${window.APP_HELPERS.e(trainee.trainee_code)}
        </option>
      `).join('');
  }

  async function initPage() {
    try {
      await loadWorkforce();
      window.APP_UI.hideLoadingState(loading);

      if (canEnroll && enrollBox) {
        enrollBox.classList.remove('d-none');
        await loadTraineesForEnroll();
      }
    } catch (error) {
      console.error(error);
      window.APP_UI.hideLoadingState(loading);
      window.APP_UI.renderErrorTable(
        tbody,
        8,
        error?.data?.message || SiteI18n.ta('تعذر تحميل بيانات القوى العاملة. تأكد من صلاحياتك.')
      );
    }
  }

  searchBtn?.addEventListener('click', async () => {
    try {
      await loadWorkforce();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل البحث.'), 'error');
    }
  });

  enrollForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!canEnroll) return;

    const traineeId = Number(enrollTraineeSelect?.value);
    const notes = document.getElementById('enrollNotes')?.value?.trim() || null;

    if (!traineeId) {
      showMessage(SiteI18n.ta('اختر متدرباً أولاً.'), 'error');
      return;
    }

    try {
      const result = await window.APP_API.post(window.APP_ROUTES.workforceEnroll(), {
        trainee_id: traineeId,
        notes,
      });
      showMessage(result?.message || SiteI18n.ta('تم إدخال المتدرب إلى القوى العاملة.'), 'success');
      enrollForm.reset();
      await loadWorkforce();
      await loadTraineesForEnroll();
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('فشل إدخال المتدرب.'), 'error');
    }
  });

  await initPage();
});
