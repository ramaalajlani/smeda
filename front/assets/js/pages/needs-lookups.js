document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.AppAuth.hasPermission('needs.manage_lookups')) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const container = document.getElementById('lookupsContainer');
  const loading = document.getElementById('lookupsLoading');
  const message = document.getElementById('lookupsMessage');

  const GROUP_TITLES = {
    need_category: SiteI18n.ta('تصنيفات الاحتياج'),
    facility_type: SiteI18n.ta('أنواع المنشآت'),
    facility_subtype: SiteI18n.ta('الأنواع الفرعية للحاضنات'),
    targeting_type: SiteI18n.ta('أنواع الاستهداف'),
  };

  const esc = (v) => window.APP_HELPERS.e(v ?? '');

  function notify(text, isError) {
    message.className = 'alert mb-3 ' + (isError ? 'alert-danger' : 'alert-success');
    message.textContent = text;
    message.classList.remove('d-none');
    setTimeout(() => message.classList.add('d-none'), 3000);
  }

  function rowHtml(item, kind) {
    const label = kind === 'sector' ? item.name_ar : item.label;
    const code = kind === 'sector' ? item.code : item.value;
    return `
      <tr class="${item.is_active ? '' : 'row-inactive'}" data-id="${item.id}" data-kind="${kind}">
        <td><code>${esc(code)}</code></td>
        <td>${esc(label)}</td>
        <td><input type="number" class="form-control form-control-sm sort-input" value="${item.sort_order ?? 0}" min="0"></td>
        <td>${item.is_active ? '<span class="badge-active">مفعّل</span>' : '<span class="badge-inactive">معطّل</span>'}</td>
        <td class="text-start">
          <button class="btn btn-sm ${item.is_active ? 'btn-outline-danger' : 'btn-outline-success'} btn-toggle">
            ${item.is_active ? SiteI18n.ta('تعطيل') : SiteI18n.ta('تفعيل')}
          </button>
          <button class="btn btn-sm btn-outline-primary btn-save-sort">${SiteI18n.ta('حفظ الترتيب')}</button>
        </td>
      </tr>`;
  }

  function groupHtml(title, icon, items, kind) {
    return `
      <div class="lookup-group-card">
        <div class="lookup-group-head"><i class="bi ${icon}"></i>${title}
          <span class="text-muted small ms-auto">${items.length} ${SiteI18n.ta('قيمة')}</span>
        </div>
        <table class="lookup-table">
          <thead><tr>
            <th>${SiteI18n.ta('الكود')}</th><th>${SiteI18n.ta('التسمية')}</th>
            <th>${SiteI18n.ta('الترتيب')}</th><th>${SiteI18n.ta('الحالة')}</th><th></th>
          </tr></thead>
          <tbody>${items.map((i) => rowHtml(i, kind)).join('')}</tbody>
        </table>
      </div>`;
  }

  async function load() {
    loading.classList.remove('d-none');
    try {
      const res = await window.APP_API.get(window.APP_ROUTES.needsLookupsManage());
      const data = res.data || {};
      const lookups = data.lookups || {};

      let html = '';
      Object.keys(GROUP_TITLES).forEach((type) => {
        html += groupHtml(GROUP_TITLES[type], 'bi-tags-fill', lookups[type] || [], 'lookup');
      });
      html += groupHtml(SiteI18n.ta('القطاعات'), 'bi-grid-fill', data.sectors || [], 'sector');
      container.innerHTML = html;
      bindActions();
    } catch (err) {
      container.innerHTML = '<div class="alert alert-danger">' + SiteI18n.ta('تعذر تحميل القوائم — تأكد من امتلاكك صلاحية إدارة القوائم.') + '</div>';
    } finally {
      loading.classList.add('d-none');
    }
  }

  function updateUrl(kind, id) {
    return kind === 'sector'
      ? window.APP_ROUTES.needsSectorUpdate(id)
      : window.APP_ROUTES.needsLookupUpdate(id);
  }

  function bindActions() {
    container.querySelectorAll('.btn-toggle').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const tr = btn.closest('tr');
        const isActive = !tr.classList.contains('row-inactive');
        try {
          await window.APP_API.put(updateUrl(tr.dataset.kind, tr.dataset.id), { is_active: !isActive });
          notify(SiteI18n.ta(isActive ? 'تم تعطيل القيمة.' : 'تم تفعيل القيمة.'));
          load();
        } catch (err) {
          notify(err?.data?.message || SiteI18n.ta('تعذر تحديث القيمة'), true);
        }
      });
    });

    container.querySelectorAll('.btn-save-sort').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const tr = btn.closest('tr');
        const sort = parseInt(tr.querySelector('.sort-input').value, 10) || 0;
        try {
          await window.APP_API.put(updateUrl(tr.dataset.kind, tr.dataset.id), { sort_order: sort });
          notify(SiteI18n.ta('تم حفظ الترتيب.'));
          load();
        } catch (err) {
          notify(err?.data?.message || SiteI18n.ta('تعذر حفظ الترتيب'), true);
        }
      });
    });
  }

  await load();
});
