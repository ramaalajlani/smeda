document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.AppAuth.hasPermission('needs.dashboard')) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const loading = document.getElementById('needsDashboardLoading');
  const kpis = document.getElementById('needsDashboardKpis');

  try {
    const res = await window.APP_API.get(window.APP_ROUTES.needsDashboard());
    const data = res.data || {};
    if (kpis) {
      const cards = [
        [SiteI18n.ta('إجمالي الاحتياجات'), data.total],
        [SiteI18n.ta('احتياجات مواطن'), data.citizen],
        [SiteI18n.ta('احتياجات دولة'), data.state],
        [SiteI18n.ta('على الخريطة'), data.mapped],
        [SiteI18n.ta('مراكز مطلوب إنشاؤها'), data.facility_establishment_count],
        [SiteI18n.ta('مراكز مطلوب تطويرها'), data.facility_development_count],
      ];
      kpis.innerHTML = cards.map(([label, value]) => `
        <div class="col-md-2 col-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
              <div class="text-muted small mb-1">${label}</div>
              <div class="fs-3 fw-bold">${value ?? 0}</div>
            </div>
          </div>
        </div>`).join('');
    }

    // كل توزيع يقود إلى سجل الاحتياجات مفلترًا حسب البُعد المنقور
    const LIST_URL = 'needs-list.php';

    /**
     * @param list      قائمة الصفوف من الـ API
     * @param labelKey  مفتاح التسمية المعروضة
     * @param param     اسم فلتر الـ API عند النقر
     * @param valueKey  مفتاح القيمة المُمرّرة للفلتر (افتراضيًا = labelKey)
     */
    const rows = (list, labelKey, param, valueKey) => {
      list = list || [];
      if (!list.length) return SiteI18n.ta('<div class="nd-empty">لا توجد بيانات</div>');
      const max = Math.max.apply(null, list.map((r) => Number(r.total) || 0).concat(1));
      return list.map((r, i) => {
        const label = window.APP_HELPERS.safe(r[labelKey] || '—');
        const total = Number(r.total) || 0;
        const pct = Math.max(4, Math.round((total / max) * 100));
        const val = r[valueKey || labelKey];
        const href = (param && val !== undefined && val !== null && val !== '')
          ? `${LIST_URL}?${param}=${encodeURIComponent(val)}`
          : LIST_URL;
        return `<a class="nd-row" href="${href}" title="${SiteI18n.ta('عرض الاحتياجات')}">
          <span class="nd-rank">${i + 1}</span>
          <span class="nd-label">${label}</span>
          <span class="nd-bar"><span class="nd-bar-fill" style="width:${pct}%"></span></span>
          <span class="nd-count">${total}</span>
          <i class="bi bi-chevron-left nd-chevron"></i>
        </a>`;
      }).join('');
    };

    document.getElementById('needsByGovernorate').innerHTML = rows(data.by_governorate, 'name', 'governorate_id', 'governorate_id');
    document.getElementById('needsBySector').innerHTML = rows(data.by_sector, 'sector', 'sector', 'sector');

    const facilityBox = document.getElementById('needsByFacilityType');
    if (facilityBox) facilityBox.innerHTML = rows(data.by_facility_type, 'label', 'facility_type', 'facility_type');

    const targetingBox = document.getElementById('needsByTargetingType');
    if (targetingBox) targetingBox.innerHTML = rows(data.by_targeting_type, 'label', 'targeting_type', 'targeting_type');

    const districtBox = document.getElementById('needsByDistrict');
    if (districtBox) districtBox.innerHTML = rows(data.by_district, 'district', 'district_name', 'district');

    const sectorRefBox = document.getElementById('needsBySectorRef');
    if (sectorRefBox) sectorRefBox.innerHTML = rows(data.by_sector_ref, 'label', 'sector_code', 'code');
  } finally {
    loading?.classList.add('d-none');
  }
});
