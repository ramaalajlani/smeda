document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.NeedsPlatform.canView()) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const tbody = document.getElementById('needsListBody');
  const loading = document.getElementById('needsListLoading');
  const search = document.getElementById('needsSearch');
  const statusFilter = document.getElementById('needsStatusFilter');
  const sectorFilter = document.getElementById('needsSectorFilter');

  // فلاتر واردة من الرابط (من لوحة المؤشرات عند النقر على أي توزيع)
  const urlParams = new URLSearchParams(window.location.search);
  // فلاتر تُمرَّر للـ API مباشرةً بلا عنصر واجهة مخصّص
  const FORWARD_KEYS = [
    'governorate_id', 'sector_code', 'priority', 'need_type', 'need_category',
    'facility_type', 'facility_subtype', 'targeting_type', 'district_name',
    'need_owner_type', 'need_scope', 'source_platform', 'proposed_intervention',
  ];
  // تسميات عربية مختصرة لعرض شارة الفلتر النشط
  const FILTER_LABELS = {
    governorate_id: 'المحافظة', sector: 'القطاع', sector_code: 'القطاع المرجعي',
    facility_type: 'نوع المنشأة', targeting_type: 'نوع الاستهداف', district_name: 'المنطقة',
    status: 'الحالة', priority: 'الأولوية',
  };

  // تعبئة صندوق البحث من الرابط
  if (search && urlParams.get('q')) search.value = urlParams.get('q');

  // شارة الفلتر النشط + زر المسح
  function renderActiveFilterChip() {
    const active = [];
    urlParams.forEach((v, k) => { if (v && FILTER_LABELS[k]) active.push(FILTER_LABELS[k]); });
    let chip = document.getElementById('needsActiveFilterChip');
    if (!active.length) { chip?.remove(); return; }
    if (!chip) {
      chip = document.createElement('div');
      chip.id = 'needsActiveFilterChip';
      chip.className = 'd-flex align-items-center gap-2 mb-3';
      loading?.parentNode?.insertBefore(chip, loading);
    }
    chip.innerHTML =
      `<span class="badge border" style="font-weight:700;background:#EAF8F4;color:#17947B;border-color:rgba(23,148,123,.25)!important">` +
      `<i class="bi bi-funnel-fill me-1"></i>مُصفّى حسب: ${active.join('، ')}</span>` +
      `<a href="needs-list.php" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-x-lg me-1"></i>مسح الفلاتر</a>`;
  }

  async function loadLookups() {
    try {
      const cacheKey = 'needs_lookups_v2';
      let data = null;
      const cached = sessionStorage.getItem(cacheKey);
      if (cached) {
        data = JSON.parse(cached);
      } else {
        const res = await window.APP_API.get(window.APP_ROUTES.needsLookups());
        data = res.data || {};
        try { sessionStorage.setItem(cacheKey, JSON.stringify(data)); } catch (_) {}
      }
      (data.statuses || []).forEach((s) => {
        const opt = document.createElement('option');
        opt.value = data.status_codes?.[s] || s;
        opt.textContent = s;
        statusFilter.appendChild(opt);
      });
      (data.sectors || []).forEach((s) => {
        const opt = document.createElement('option');
        opt.value = s;
        opt.textContent = s;
        sectorFilter.appendChild(opt);
      });
      if (statusFilter && urlParams.get('status')) statusFilter.value = urlParams.get('status');
      if (sectorFilter && urlParams.get('sector')) sectorFilter.value = urlParams.get('sector');
    } catch (_) {}
  }

  async function loadRows() {
    loading?.classList.remove('d-none');
    try {
      const params = { per_page: 30, lite: 1 };
      FORWARD_KEYS.forEach((k) => { const v = urlParams.get(k); if (v) params[k] = v; });
      if (search?.value.trim()) params.q = search.value.trim();
      if (statusFilter?.value) params.status = statusFilter.value;
      if (sectorFilter?.value) params.sector = sectorFilter.value;
      const res = await window.APP_API.get(window.APP_ROUTES.needs(params));
      const rows = res.data || [];
      if (tbody) {
        tbody.innerHTML = rows.map((row) => `
          <tr>
            <td>${window.APP_HELPERS.safe(row.need_code)}</td>
            <td>${window.APP_HELPERS.safe(row.title)}</td>
            <td>${window.NeedsPlatform.ownerLabel(row.need_owner_type)}</td>
            <td>${window.APP_HELPERS.safe(row.sector || '—')}</td>
            <td>${window.APP_HELPERS.safe(row.governorate?.name_ar || '—')}</td>
            <td>${window.NeedsPlatform.statusLabel(row.status)}</td>
            <td><a class="btn btn-sm btn-outline-primary" href="need-view.php?id=${row.id}">عرض</a></td>
          </tr>`).join('') || '<tr><td colspan="7" class="text-center text-muted">لا توجد بيانات</td></tr>';
      }
    } finally {
      loading?.classList.add('d-none');
    }
  }

  await Promise.all([loadLookups(), loadRows()]);
  renderActiveFilterChip();
  // عند تغيير المستخدم لأي فلتر من الواجهة، نُلغي فلتر القطاع/الحالة الوارد من الرابط لتفادي التعارض
  [search, statusFilter, sectorFilter].forEach((el) => el?.addEventListener('change', () => {
    urlParams.delete('status'); urlParams.delete('sector');
    loadRows();
  }));
  search?.addEventListener('keyup', (e) => { if (e.key === 'Enter') loadRows(); });
});
