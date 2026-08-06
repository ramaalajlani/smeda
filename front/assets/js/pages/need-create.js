document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.NeedsPlatform.canCreate() || window.NeedsPlatform.isReadOnly()) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  // ══════════════════════════════════════════
  // مسار الإنشاء: احتياج خاص مباشرة للاستبيان
  // ══════════════════════════════════════════
  let currentComplexity = 'specific';
  let currentOwnerType  = 'citizen';

  const stepsWrapper = document.getElementById('formStepsWrapper');
  const hComplexity  = document.getElementById('hComplexity');
  const hOwnerType   = document.getElementById('hOwnerType');

  function setComplexity() {
    currentComplexity = 'specific';
    currentOwnerType = 'citizen';
    if (hComplexity) hComplexity.value = 'specific';
    if (hOwnerType) hOwnerType.value = 'citizen';
    document.querySelectorAll('.step-specific').forEach((s) => s.classList.remove('d-none'));
    document.getElementById('generalFields')?.classList.add('d-none');
    document.getElementById('stateFields')?.classList.add('d-none');
    document.getElementById('citizenFields')?.classList.remove('d-none');
  }

  function setOwnerType(val) {
    currentOwnerType = val === 'state' ? 'state' : 'citizen';
    if (hOwnerType) hOwnerType.value = currentOwnerType;
  }

  document.getElementById('complexityPicker')?.classList.add('d-none');
  stepsWrapper?.classList.remove('d-none');
  setComplexity();

  // ══════════════════════════════════════════
  // الخطوات (Steps)
  // ══════════════════════════════════════════
  let currentStep = 1;
  const totalSteps = 5;

  function showStep(n) {
    document.querySelectorAll('.form-panel').forEach((p) => p.classList.remove('active'));
    document.querySelectorAll('.form-step').forEach((s) => {
      s.classList.remove('active');
      if (parseInt(s.dataset.step) < n) s.classList.add('done');
      else s.classList.remove('done');
    });
    document.querySelector(`.form-panel[data-panel="${n}"]`)?.classList.add('active');
    document.querySelector(`.form-step[data-step="${n}"]`)?.classList.add('active');
    currentStep = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // تحميل كسول لتصنيف SyrSIC (~1.3MB) عند الحاجة فقط
    if ((n === 2 || n === 3) && window.SYRSIC?.ensureLoaded) {
      window.SYRSIC.ensureLoaded().catch(() => {});
    }
    if (n === 4) setTimeout(initMap, 100); // تهيئة الخريطة عند الوصول لخطوة الموقع
    if (n === 5) buildSummary();
  }

  document.querySelectorAll('.btn-next').forEach((btn) => {
    btn.addEventListener('click', () => {
      let next = parseInt(btn.dataset.next);
      if (currentComplexity === 'general') {
        // الاحتياج العام: معلومات أساسية ← موقع ← حفظ (يتجاوز التصنيف والتفاصيل)
        if (next === 2) next = 4;
        if (next === 5) { submitForm(); return; }
      }
      // التحقق من خطوة تصنيف الاحتياج قبل المتابعة
      if (currentStep === 2 && next === 3 && !validateTaxonomyStep()) return;
      showStep(next);
    });
  });
  document.querySelectorAll('.btn-prev').forEach((btn) => {
    btn.addEventListener('click', () => showStep(parseInt(btn.dataset.prev)));
  });
  document.querySelectorAll('.form-step').forEach((s) => {
    s.addEventListener('click', () => {
      const n = parseInt(s.dataset.step);
      if (n <= currentStep) showStep(n);
    });
  });

  // ══════════════════════════════════════════
  // تصنيف الاحتياج (الخطوة 2)
  // ══════════════════════════════════════════
  const categorySelect     = document.getElementById('needCategorySelect');
  const facilityTypeWrap   = document.getElementById('facilityTypeWrap');
  const facilityTypeSelect = document.getElementById('facilityTypeSelect');
  const subtypeWrap        = document.getElementById('facilitySubtypeWrap');
  const subtypeSelect      = document.getElementById('facilitySubtypeSelect');
  const targetingSelect    = document.getElementById('targetingTypeSelect');
  const sectorChipsBox     = document.getElementById('sectorChips');
  const existingProjWrap   = document.getElementById('existingProjectWrap');
  const existingProjInput  = document.getElementById('existingProjectInput');
  const categoryOtherWrap  = document.getElementById('categoryOtherWrap');
  const categoryOtherInput = document.getElementById('needCategoryOtherInput');
  const targetingOtherWrap = document.getElementById('targetingOtherWrap');
  const targetingOtherInput= document.getElementById('targetingTypeOtherInput');
  const taxonomyError      = document.getElementById('taxonomyError');

  const FACILITY_CATEGORIES = ['facility_establishment', 'facility_development'];
  let taxonomyLists = null;

  function fillSelect(select, items) {
    if (!select) return;
    (items || []).forEach((item) => select.appendChild(new Option(item.label, item.value)));
  }

  function ensureOtherOption(select) {
    if (!select) return;
    const hasOther = [...select.options].some((o) => o.value === 'other');
    if (!hasOther) select.appendChild(new Option('أخرى', 'other'));
  }

  async function loadTaxonomy() {
    try {
      const cacheKey = 'needs_lookups_v2';
      const cached = sessionStorage.getItem(cacheKey);
      if (cached) {
        taxonomyLists = JSON.parse(cached);
      } else {
        const res = await window.APP_API.get(window.APP_ROUTES.needsLookups());
        taxonomyLists = res.data || {};
        try { sessionStorage.setItem(cacheKey, JSON.stringify(taxonomyLists)); } catch (_) {}
      }
    } catch (_) {
      taxonomyLists = {};
    }
    fillSelect(categorySelect, taxonomyLists.need_categories);
    fillSelect(facilityTypeSelect, taxonomyLists.facility_types);
    fillSelect(subtypeSelect, taxonomyLists.facility_subtypes);
    fillSelect(targetingSelect, taxonomyLists.targeting_types);
    ensureOtherOption(categorySelect);
    ensureOtherOption(targetingSelect);
    buildSectorChips(taxonomyLists.sector_options || []);
  }

  function buildSectorChips(options) {
    if (!sectorChipsBox) return;
    sectorChipsBox.innerHTML = '';
    options.forEach((opt) => {
      const chip = document.createElement('label');
      chip.className = 'sector-chip' + (opt.value === 'all' ? ' chip-all' : '');
      chip.dataset.value = opt.value;
      chip.innerHTML = `<input type="checkbox" value="${opt.value}"><i class="bi bi-check-circle-fill d-none"></i>${opt.label}`;
      chip.addEventListener('click', (e) => {
        e.preventDefault();
        toggleSectorChip(chip);
      });
      sectorChipsBox.appendChild(chip);
    });
  }

  function toggleSectorChip(chip) {
    const isAll = chip.dataset.value === 'all';
    const selected = chip.classList.contains('selected');

    if (!selected && isAll) {
      // "جميع القطاعات" يلغي بقية الاختيارات
      sectorChipsBox.querySelectorAll('.sector-chip').forEach((c) => c.classList.remove('selected'));
      chip.classList.add('selected');
      return;
    }

    if (!selected && !isAll) {
      sectorChipsBox.querySelector('.chip-all')?.classList.remove('selected');
    }

    chip.classList.toggle('selected');
  }

  function selectedSectors() {
    return [...sectorChipsBox.querySelectorAll('.sector-chip.selected')].map((c) => c.dataset.value);
  }

  function syncFacilityVisibility() {
    const isOther = categorySelect.value === 'other';
    categoryOtherWrap?.classList.toggle('d-none', !isOther);
    if (!isOther && categoryOtherInput) categoryOtherInput.value = '';

    const showFacility = FACILITY_CATEGORIES.includes(categorySelect.value);
    facilityTypeWrap.classList.toggle('d-none', !showFacility);
    if (!showFacility) facilityTypeSelect.value = '';

    const showSubtype = showFacility && facilityTypeSelect.value === 'business_incubator';
    subtypeWrap.classList.toggle('d-none', !showSubtype);
    if (!showSubtype) subtypeSelect.value = '';
  }

  function syncTargetingVisibility() {
    const isOther = targetingSelect.value === 'other';
    targetingOtherWrap?.classList.toggle('d-none', !isOther);
    if (!isOther && targetingOtherInput) targetingOtherInput.value = '';

    const isExisting = targetingSelect.value === 'existing_project';
    existingProjWrap.classList.toggle('d-none', !isExisting);
    if (!isExisting) existingProjInput.value = '';

    const districtMark = document.getElementById('districtRequiredMark');
    if (districtMark) districtMark.classList.toggle('d-none', targetingSelect.value !== 'geographic_area');
  }

  categorySelect?.addEventListener('change', syncFacilityVisibility);
  facilityTypeSelect?.addEventListener('change', syncFacilityVisibility);
  targetingSelect?.addEventListener('change', syncTargetingVisibility);

  function showTaxonomyError(msg) {
    taxonomyError.textContent = msg;
    taxonomyError.classList.add('show');
  }

  function validateTaxonomyStep() {
    taxonomyError.classList.remove('show');

    if (!categorySelect.value) {
      showTaxonomyError(SiteI18n.ta('يرجى اختيار تصنيف الاحتياج.'));
      return false;
    }
    if (categorySelect.value === 'other' && !(categoryOtherInput?.value || '').trim()) {
      showTaxonomyError(SiteI18n.ta('يرجى كتابة تصنيف الاحتياج.'));
      return false;
    }
    if (FACILITY_CATEGORIES.includes(categorySelect.value) && !facilityTypeSelect.value) {
      showTaxonomyError(SiteI18n.ta('يرجى اختيار نوع المنشأة عند إنشاء أو تطوير منشأة.'));
      return false;
    }
    if (facilityTypeSelect.value === 'business_incubator' && !subtypeSelect.value) {
      showTaxonomyError(SiteI18n.ta('يرجى اختيار نوع الحاضنة (تقنية / حرفية / زراعية / متعددة القطاعات).'));
      return false;
    }
    if (!selectedSectors().length) {
      showTaxonomyError(SiteI18n.ta('يرجى اختيار قطاع واحد على الأقل أو "جميع القطاعات".'));
      return false;
    }
    if (targetingSelect.value === 'other' && !(targetingOtherInput?.value || '').trim()) {
      showTaxonomyError(SiteI18n.ta('يرجى كتابة نوع الاستهداف.'));
      return false;
    }
    if (targetingSelect.value === 'existing_project' && !existingProjInput.value.trim()) {
      showTaxonomyError(SiteI18n.ta('يرجى إدخال اسم المشروع القائم.'));
      return false;
    }

    return true;
  }

  loadTaxonomy();

  // ══════════════════════════════════════════
  // SyrSIC — بحث ذكي فوري
  // ══════════════════════════════════════════
  const S = window.SYRSIC;

  const searchInput = document.getElementById('syrsicSearchInput');
  const resultsBox  = document.getElementById('syrsicResults');
  const codeBox     = document.getElementById('activityCodeBox');
  const codeDisplay = document.getElementById('activityCodeDisplay');
  const nameDisplay = document.getElementById('activityNameDisplay');
  const sectionDisplay   = document.getElementById('activitySectionDisplay');
  const pathDisplay      = document.getElementById('activityPathDisplay');
  const clearActivityBtn = document.getElementById('clearActivityBtn');

  // الحقول المخفية — SyrSIC
  const hSection  = document.getElementById('hSyrsicSection');
  const hDivision = document.getElementById('hSyrsicDivision');
  const hGroup    = document.getElementById('hSyrsicGroup');
  const hClass    = document.getElementById('hSyrsicClass');
  const hActivity = document.getElementById('hSyrsicActivity');
  // الحقول المخفية — القطاع (للخريطة والتصفية)
  const hSector         = document.getElementById('hSector');
  const hEconomicSector = document.getElementById('hEconomicSector');

  if (!S) {
    searchInput.placeholder = SiteI18n.ta('جاري تحميل بيانات التصنيف...');
    searchInput.disabled = true;
    const waitSyrsic = setInterval(() => {
      if (window.SYRSIC) {
        clearInterval(waitSyrsic);
        searchInput.disabled = false;
        searchInput.placeholder = SiteI18n.ta('ابحث بالاسم أو الكود...');
        window.SYRSIC.ensureLoaded?.().catch(() => {});
      }
    }, 200);
  } else {
    searchInput.addEventListener('focus', () => {
      if (!S.isReady()) {
        searchInput.placeholder = SiteI18n.ta('جاري تحميل بيانات التصنيف...');
        S.ensureLoaded().then(() => {
          searchInput.placeholder = SiteI18n.ta('ابحث بالاسم أو الكود...');
        }).catch(() => {
          searchInput.placeholder = SiteI18n.ta('تعذّر تحميل التصنيف');
        });
      }
    });
  }

  const LEVEL_LABELS = { activity: SiteI18n.ta('نشاط'), class: SiteI18n.ta('صنف'), group: SiteI18n.ta('مجموعة'), division: SiteI18n.ta('شعبة'), section: SiteI18n.ta('باب') };
  const LEVEL_BADGE  = { activity: 'badge-activity', class: 'badge-class', group: 'badge-group', division: 'badge-division', section: 'badge-section' };

  let focusedIdx = -1;
  let lastResults = [];

  function renderResults(results) {
    lastResults = results;
    focusedIdx = -1;
    if (!results.length) {
      resultsBox.innerHTML = '<div class="syrsic-no-result">' + SiteI18n.ta('لا توجد نتائج — جرّب كلمة مختلفة أو رقم الكود') + '</div>';
      resultsBox.classList.add('open');
      return;
    }
    resultsBox.innerHTML = results.map((item, i) => `
      <div class="syrsic-result-item" data-idx="${i}" role="option">
        <div class="rc">${item.code}</div>
        <div style="flex:1;min-width:0">
          <div class="rn">${item.name}</div>
          <div class="rp">${item.path}</div>
        </div>
        <span class="badge-level ${LEVEL_BADGE[item.level] || ''}">${LEVEL_LABELS[item.level] || ''}</span>
      </div>
    `).join('');
    resultsBox.classList.add('open');

    resultsBox.querySelectorAll('.syrsic-result-item').forEach((el) => {
      el.addEventListener('mousedown', (e) => {
        e.preventDefault();
        selectItem(parseInt(el.dataset.idx));
      });
    });
  }

  function selectItem(idx) {
    const item = lastResults[idx];
    if (!item) return;

    // ملء الحقول المخفية — SyrSIC
    hSection.value  = item.sectionCode  || '';
    hDivision.value = item.divCode      || '';
    hGroup.value    = item.grpCode      || '';
    hClass.value    = item.classCode    || '';
    hActivity.value = item.activityCode || '';
    // القطاع (يُستخدم في الخريطة والفلترة)
    if (hSector)         hSector.value         = item.sectionName || '';
    if (hEconomicSector) hEconomicSector.value  = item.sectionName || '';

    // عرض الكود المختار
    codeDisplay.textContent    = item.code;
    nameDisplay.textContent    = item.name;
    sectionDisplay.textContent = item.sectionName || '';
    pathDisplay.textContent    = item.path;
    codeBox.classList.add('show');
    requestAnimationFrame(() => codeBox.classList.add('visible'));

    // إخفاء نتائج البحث وتحديث حقل الإدخال
    searchInput.value = `${item.code} — ${item.name}`;
    resultsBox.classList.remove('open');
    searchInput.blur();

    // تحديث تقدير العمال
    updateJobsHint(item.code);
  }

  // إزالة الاختيار (زر "تغيير النشاط")
  clearActivityBtn?.addEventListener('click', () => {
    searchInput.value = '';
    codeBox.classList.remove('show', 'visible');
    [hSection, hDivision, hGroup, hClass, hActivity, hSector, hEconomicSector].forEach((h) => { if(h) h.value = ''; });
    searchInput.focus();
  });

  // بحث فوري عند الكتابة
  let debounceTimer;
  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const q = searchInput.value.trim();
    if (q.length < 2) { resultsBox.classList.remove('open'); return; }
    debounceTimer = setTimeout(() => {
      const syrsic = window.SYRSIC;
      if (!syrsic || !syrsic.isReady()) {
        resultsBox.innerHTML = '<div class="syrsic-no-result">' + SiteI18n.ta('البيانات لم تُحمَّل بعد، انتظر لحظة...') + '</div>';
        resultsBox.classList.add('open');
        return;
      }
      renderResults(syrsic.search(q, 12));
    }, 120);
  });

  // التنقل بلوحة المفاتيح
  searchInput.addEventListener('keydown', (e) => {
    const items = resultsBox.querySelectorAll('.syrsic-result-item');
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      focusedIdx = Math.min(focusedIdx + 1, items.length - 1);
      items.forEach((el, i) => el.classList.toggle('focused', i === focusedIdx));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      focusedIdx = Math.max(focusedIdx - 1, 0);
      items.forEach((el, i) => el.classList.toggle('focused', i === focusedIdx));
    } else if (e.key === 'Enter' && focusedIdx >= 0) {
      e.preventDefault();
      selectItem(focusedIdx);
    } else if (e.key === 'Escape') {
      resultsBox.classList.remove('open');
    }
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.syrsic-search-wrap')) resultsBox.classList.remove('open');
  });

  // دالة مرجعية للكود المختار (تُستخدم في buildSummary)
  function getSelectedActivityCode() { return hActivity.value || hClass.value || hGroup.value || hDivision.value || hSection.value; }

  // ══════════════════════════════════════════
  // الموقع الجغرافي — قوائم متسلسلة
  // ══════════════════════════════════════════
  const govSelect = document.getElementById('govSelect');

  /* ══ مكوّن قائمة بحث منسدلة منسّقة (searchable select) ══ */
  function makeSearchableSelect(mountId, { name, placeholder, onChange, allowCustom = false }) {
    const wrap = document.getElementById(mountId);
    wrap.classList.add('ss-wrap', 'disabled');

    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = name;

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'form-select ss-toggle is-placeholder';
    toggle.textContent = placeholder;
    toggle.disabled = true;

    const menu = document.createElement('div');
    menu.className = 'ss-menu';
    const searchWrap = document.createElement('div');
    searchWrap.className = 'ss-search';
    const search = document.createElement('input');
    search.type = 'text';
    search.className = 'form-control form-control-sm';
    search.placeholder = SiteI18n.ta('اكتب للبحث...');
    searchWrap.appendChild(search);
    const list = document.createElement('ul');
    list.className = 'ss-list';
    menu.append(searchWrap, list);
    wrap.append(hidden, toggle, menu);

    const esc = (str) => String(str).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let options = [];
    let value = '';
    let disabled = true;

    function render(filter) {
      const f = (filter || '').trim();
      const shown = f ? options.filter((o) => o.includes(f)) : options;
      const exact = f && options.some((o) => o === f);
      let html = shown.map((o) => `<li class="ss-opt${o === value ? ' selected' : ''}" data-v="${esc(o)}">${esc(o)}</li>`).join('');
      // السماح بإدخال قيمة غير موجودة في القائمة (كتابة حرة)
      if (allowCustom && f && !exact) {
        html = `<li class="ss-opt ss-custom" data-v="${esc(f)}"><i class="bi bi-plus-circle me-1"></i>${SiteI18n.ta('استخدام')}: "${esc(f)}"</li>` + html;
      }
      if (!html) html = `<li class="ss-empty">${SiteI18n.ta(allowCustom ? 'اكتب لإضافة قيمة' : 'لا نتائج مطابقة')}</li>`;
      list.innerHTML = html;
    }
    function open() {
      if (disabled) return;
      wrap.classList.add('open');
      search.value = '';
      render('');
      setTimeout(() => search.focus(), 30);
    }
    function close() { wrap.classList.remove('open'); }
    function setValue(v, fire) {
      value = v || '';
      toggle.textContent = value || placeholder;
      toggle.classList.toggle('is-placeholder', !value);
      hidden.value = value;
      if (fire && onChange) onChange(value);
    }

    toggle.addEventListener('click', (e) => { e.stopPropagation(); wrap.classList.contains('open') ? close() : open(); });
    search.addEventListener('click', (e) => e.stopPropagation());
    search.addEventListener('input', () => render(search.value));
    search.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const f = search.value.trim();
        const exact = options.find((o) => o === f);
        if (exact) { setValue(exact, true); close(); }
        else if (allowCustom && f) { setValue(f, true); close(); }
        else { const first = list.querySelector('.ss-opt'); if (first) { setValue(first.dataset.v, true); close(); } }
      } else if (e.key === 'Escape') { close(); }
    });
    list.addEventListener('click', (e) => {
      const li = e.target.closest('.ss-opt');
      if (!li) return;
      setValue(li.dataset.v, true);
      close();
    });
    document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) close(); });

    return {
      get value() { return value; },
      setOptions(arr) { options = (arr || []).slice(); render(search.value); },
      setValue: (v) => setValue(v, false),
      clear() { setValue('', false); options = []; },
      setDisabled(b) {
        disabled = !!b;
        toggle.disabled = disabled;
        wrap.classList.toggle('disabled', disabled);
        if (disabled) close();
      },
    };
  }

  const districtSelect = makeSearchableSelect('districtSelect', {
    name: 'district_name',
    placeholder: SiteI18n.ta('اختر أو ابحث عن المنطقة...'),
    onChange: (v) => loadCountrysides(v),
    allowCustom: true,
  });
  const countrysideSelect = makeSearchableSelect('countrysideSelect', {
    name: 'countryside_name',
    placeholder: SiteI18n.ta('اختر أو ابحث عن الناحية...'),
    allowCustom: true,
  });

  // ملء المحافظات من الـ API (وضع خفيف) — أو خيار واحد إذا المحافظة مقفولة للحساب
  async function loadGovernorates() {
    const u = window.AppAuth?.getUser?.() || {};
    if (u.governorate_id) {
      const label = u.governorate?.name_ar || u.governorate_name || ('محافظة #' + u.governorate_id);
      govSelect.appendChild(new Option(label, u.governorate_id));
      return;
    }
    try {
      const url = window.APP_ROUTES.governorates
        ? window.APP_ROUTES.governorates({ lite: 1 })
        : `${window.APP_CONFIG.API_BASE_URL}/governorates?lite=1`;
      const res = await window.APP_API.get(url);
      const list = res.data || res || [];
      list.forEach((g) => {
        govSelect.appendChild(new Option(g.name_ar || g.name, g.id));
      });
    } catch (_) {
      (window.SYRIA_GOVERNORATES_LIST || []).forEach((g) => {
        govSelect.appendChild(new Option(g.label, g.value));
      });
    }
  }
  await loadGovernorates();

  // ══════════════════════════════════════════
  // حصر المحافظة حسب نطاق الحساب (محافظ / مدير فرع / موظف فرع)
  // المحافظة والفرع يُملآن تلقائياً في الخادم ولا يمكن تغييرهما.
  // ══════════════════════════════════════════
  (function lockGovernorateToUserScope() {
    const u = window.AppAuth?.getUser?.() || {};
    if (!u.governorate_id) return;

    govSelect.value = String(u.governorate_id);
    if (govSelect.value === String(u.governorate_id)) {
      govSelect.dispatchEvent(new Event('change'));
      govSelect.disabled = true;
      const hint = document.getElementById('govHint');
      if (hint) hint.textContent = SiteI18n.ta('المحافظة محددة تلقائياً حسب نطاق حسابك ولا يمكن تغييرها.');
    }
  })();

  /* ══ تحميل قوائم الربط بالوحدات الأخرى ══ */
  async function loadLinkOptions() {
    const fundingSel  = document.getElementById('linkFundingSelect');
    const trainingSel = document.getElementById('linkTrainingSelect');

    if (fundingSel && window.AppAuth.hasAnyPermission(['needs.create_state', 'needs.create'])) {
      try {
        const res = await window.APP_API.get(
          window.APP_ROUTES.fundingApplications
            ? window.APP_ROUTES.fundingApplications({ per_page: 100, status: 'approved' })
            : (window.APP_CONFIG.API_BASE_URL + '/finance/applications?per_page=100&status=approved')
        );
        (res.data || []).forEach((fa) => {
          fundingSel.appendChild(new Option(
            (fa.application_number || ('#' + fa.id)) + (fa.status ? ' — ' + fa.status : ''),
            fa.id
          ));
        });
      } catch (_) {}
    }

    if (trainingSel) {
      try {
        const res = await window.APP_API.get(
          window.APP_ROUTES.trainingCourses
            ? window.APP_ROUTES.trainingCourses({ per_page: 100 })
            : (window.APP_CONFIG.API_BASE_URL + '/training-courses?per_page=100')
        );
        (res.data || []).forEach((tc) => {
          trainingSel.appendChild(new Option(tc.title || ('#' + tc.id), tc.id));
        });
      } catch (_) {}
    }
  }
  loadLinkOptions();

  // ══════════════════════════════════════════
  // الموقع الإداري: قوائم بحث متتالية (منطقة ← ناحية) من admin-units
  // ══════════════════════════════════════════
  let adminUnitsCache = []; // لم يعد يُستخدم كحمولة كاملة — مُبقى للتوافق

  function flyToGovernorate() {
    const govName = govSelect.options[govSelect.selectedIndex]?.text;
    const center = (window.SYRIA_GOVERNORATE_CENTERS || {})[govName];
    if (center && window._pickerMap) window._pickerMap.flyTo(center, 9, { duration: 0.8 });
  }

  // الناحية تُجلب حسب المنطقة المختارة (طلب خفيف level=subdistrict)
  async function loadCountrysides(districtName) {
    countrysideSelect.clear();
    countrysideSelect.setDisabled(true);
    if (!districtName || !govSelect.value) return;
    try {
      const res = await window.APP_API.get(
        window.APP_ROUTES.needsAdminUnits({
          governorate_id: govSelect.value,
          district_name: districtName,
          level: 'subdistrict',
        })
      );
      const list = [...new Set(
        (res.data || []).map((u) => u.countryside_name).filter(Boolean)
      )].sort((a, b) => String(a).localeCompare(b, 'ar'));
      countrysideSelect.setOptions(list);
      countrysideSelect.setDisabled(false);
    } catch (_) {
      countrysideSelect.setDisabled(false);
    }
  }

  async function loadDistricts(govId) {
    districtSelect.clear();
    districtSelect.setDisabled(true);
    countrysideSelect.clear();
    countrysideSelect.setDisabled(true);
    if (!govId) return;
    try {
      const res = await window.APP_API.get(
        window.APP_ROUTES.needsAdminUnits({ governorate_id: govId, level: 'district' })
      );
      const districts = [...new Set((res.data || []).map((u) => u.district_name).filter(Boolean))]
        .sort((a, b) => String(a).localeCompare(b, 'ar'));
      if (districts.length) {
        districtSelect.setOptions(districts);
        districtSelect.setDisabled(false);
      }
    } catch (_) {}
  }

  govSelect.addEventListener('change', () => {
    flyToGovernorate();
    loadDistricts(govSelect.value);
  });

  // تحميل مناطق المحافظة الحالية فورًا — ضروري لمدير الفرع الذي حُدّدت محافظته وقُفلت مسبقاً
  if (govSelect.value) {
    flyToGovernorate();
    loadDistricts(govSelect.value);
  }

  // ══════════════════════════════════════════
  // الخريطة التفاعلية (تُهيّأ عند خطوة الموقع)
  // ══════════════════════════════════════════
  let mapInited = false;
  async function initMap() {
    if (mapInited) return;
    mapInited = true;

    const BOUNDS = L.latLngBounds([32.31, 35.73], [37.32, 42.38]);
    const map = L.map('locationPickerMap', {
      center: [35.0, 38.5],
      zoom: 6,
      minZoom: 6,
      maxZoom: 16,
      maxBounds: BOUNDS,
      maxBoundsViscosity: 0.9,
    });
    window._pickerMap = map;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    try {
      const [adm1Res, adm2Res] = await Promise.all([
        fetch('../../assets/geo/syria-adm1.geojson'),
        fetch('../../assets/geo/syria-adm2.geojson'),
      ]);
      const adm1 = await adm1Res.json();
      const adm2 = await adm2Res.json();

      L.geoJSON(adm2, {
        style: { color: '#64748b', weight: 0.8, fillColor: '#94a3b8', fillOpacity: 0.04, dashArray: '4 4' },
        onEachFeature(feature, layer) {
          const label = feature.properties.name || feature.properties.name_en || '';
          const gov = feature.properties.governorate || '';
          if (label) {
            layer.bindTooltip(gov ? `${label} — ${gov}` : label, { permanent: false, direction: 'center', className: 'district-tooltip' });
          }
        },
      }).addTo(map);

      L.geoJSON(adm1, {
        style: () => ({
          color: '#1e3a5f',
          weight: 2,
          fillColor: '#3b82f6',
          fillOpacity: 0.06,
        }),
        onEachFeature(feature, layer) {
          const name = feature.properties.name || '';
          let center = null;
          if (feature.properties.center_lat != null && feature.properties.center_lng != null) {
            center = [Number(feature.properties.center_lat), Number(feature.properties.center_lng)];
          } else if (window.SYRIA_GOVERNORATE_CENTERS?.[name]) {
            center = window.SYRIA_GOVERNORATE_CENTERS[name];
          } else {
            center = layer.getBounds().getCenter();
          }
          if (name && center) {
            L.marker(center, {
              icon: L.divIcon({ className: 'gov-label', html: name, iconSize: [100, 20], iconAnchor: [50, 10] }),
              interactive: false,
            }).addTo(map);
          }
        },
      }).addTo(map);
    } catch (_) {
      if (window.SYRIA_DISTRICTS_GEOJSON) {
        L.geoJSON(window.SYRIA_DISTRICTS_GEOJSON, {
          style: { color: '#64748b', weight: 0.8, fillColor: '#94a3b8', fillOpacity: 0.04, dashArray: '2 4' },
        }).addTo(map);
      }
      if (window.SYRIA_GOVERNORATES_GEOJSON) {
        L.geoJSON(window.SYRIA_GOVERNORATES_GEOJSON, {
          style: { color: '#1e3a5f', weight: 2, fillOpacity: 0.06 },
        }).addTo(map);
      }
    }

    const latInput = document.getElementById('latInput');
    const lngInput = document.getElementById('lngInput');
    const tip = document.getElementById('mapTip');
    const mapEl = document.getElementById('locationPickerMap');
    let pin = null;

    function setPin(lat, lng) {
      latInput.value = lat.toFixed(6);
      lngInput.value = lng.toFixed(6);
      if (pin) pin.setLatLng([lat, lng]);
      else {
        pin = L.marker([lat, lng], { draggable: true }).addTo(map);
        pin.on('dragend', (e) => setPin(e.target.getLatLng().lat, e.target.getLatLng().lng));
      }
      if (tip) {
        tip.textContent = SiteI18n.ta('✅ تم تحديد الموقع — اسحب المؤشر لضبطه');
        tip.classList.add('ok');
      }
      mapEl.classList.add('has-pin');
    }

    map.on('click', (e) => setPin(e.latlng.lat, e.latlng.lng));
    setTimeout(() => map.invalidateSize(), 200);
  }

  // ══════════════════════════════════════════
  // تقدير المستفيدين حسب كود النشاط
  // ══════════════════════════════════════════
  function updateJobsHint(actCode) {
    const est = null; // job estimates removed — fill manually
    if (!est) return;

    const jobsHint = document.getElementById('jobsHint');
    const beneHint = document.getElementById('beneficiariesHint');
    const jobsInput = document.getElementById('jobsInput');
    const bInput = document.getElementById('beneficiariesInput');

    if (jobsHint) {
      jobsHint.textContent = `📊 تقدير وفق كود النشاط: ${est.min}–${est.max} عامل (${est.label})`;
      jobsHint.classList.add('show');
    }
    if (beneHint) {
      beneHint.textContent = `💡 يُقترح تضمين صاحب المشروع + العمال المتوقعين`;
      beneHint.classList.add('show');
    }
    // ملء تلقائي إذا كانت الحقول فارغة
    if (jobsInput && !jobsInput.value) jobsInput.value = est.min;
    if (bInput && !bInput.value) bInput.value = est.min + 1;
  }

  // ══════════════════════════════════════════
  // ملخص قبل الإرسال
  // ══════════════════════════════════════════
  function buildSummary() {
    const get = (name) => {
      const el = document.querySelector(`[name="${name}"]`);
      if (!el) return '—';
      if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '—';
      return el.value || '—';
    };

    const actCode = getSelectedActivityCode();
    const govName = govSelect.options[govSelect.selectedIndex]?.text || '—';

    const selText = (sel) => (sel && sel.value) ? (sel.options[sel.selectedIndex]?.text || '—') : '—';
    const sectorsText = selectedSectors()
      .map((v) => sectorChipsBox.querySelector(`.sector-chip[data-value="${v}"]`)?.textContent?.trim() || v)
      .join('، ') || '—';

    document.getElementById('summaryContent').innerHTML = `
      <div class="row g-2">
        <div class="col-md-6"><strong>اسم الاحتياج:</strong> ${get('title')}</div>
        <div class="col-md-6"><strong>تصنيف الاحتياج:</strong> ${selText(categorySelect)}${categorySelect?.value === 'other' && categoryOtherInput?.value ? ' — ' + categoryOtherInput.value : ''}</div>
        <div class="col-md-6"><strong>نوع المنشأة:</strong> ${selText(facilityTypeSelect)}</div>
        <div class="col-md-6"><strong>نوع الحاضنة:</strong> ${selText(subtypeSelect)}</div>
        <div class="col-md-6"><strong>القطاعات المستهدفة:</strong> ${sectorsText}</div>
        <div class="col-md-6"><strong>نوع الاستهداف:</strong> ${selText(targetingSelect)}${targetingSelect?.value === 'other' && targetingOtherInput?.value ? ' — ' + targetingOtherInput.value : ''}</div>
        <div class="col-md-6"><strong>الجهة الطالبة:</strong> ${get('responsible_entity')}</div>
        <div class="col-md-6"><strong>نوع الملكية:</strong> ${get('need_owner_type')}</div>
        <div class="col-md-6"><strong>الأولوية:</strong> ${get('priority') || '—'}</div>
        <div class="col-md-6"><strong>كود النشاط (SyrSIC):</strong> <span class="text-primary fw-bold">${actCode || '—'}</span></div>
        <div class="col-md-6"><strong>اسم النشاط:</strong> ${document.getElementById('activityNameDisplay')?.textContent || '—'}</div>
        <div class="col-md-6"><strong>المحافظة:</strong> ${govName}</div>
        <div class="col-md-6"><strong>المنطقة:</strong> ${get('district_name')}</div>
        <div class="col-md-6"><strong>الناحية:</strong> ${get('countryside_name')}</div>
        <div class="col-md-6"><strong>القرية/الحي:</strong> ${get('village_or_neighborhood')}</div>
        <div class="col-md-4"><strong>إجمالي المستفيدين:</strong> ${get('beneficiaries_count')}</div>
        <div class="col-md-4"><strong>فرص العمل:</strong> ${get('expected_jobs_count')}</div>
        <div class="col-md-4"><strong>المشاريع:</strong> ${get('expected_projects_count')}</div>
      </div>
    `;
  }

  // ══════════════════════════════════════════
  // إرسال الفورم
  // ══════════════════════════════════════════
  const form = document.getElementById('needCreateForm');
  const message = document.getElementById('needCreateMessage');
  const submitBtn = document.getElementById('submitBtn');
  const saveToast = document.getElementById('needSaveToast');
  const saveModal = document.getElementById('needSaveModal');
  const saveModalTitle = document.getElementById('needSaveModalTitle');
  const saveModalText = document.getElementById('needSaveModalText');
  const saveModalIcon = document.getElementById('needSaveModalIcon');
  const saveModalOk = document.getElementById('needSaveModalOk');
  let pendingRedirectUrl = null;

  function showSaveToast(msg, type = 'ok') {
    if (!saveToast) return;
    saveToast.className = (type === 'ok' ? 'toast-ok' : 'toast-err') + ' is-visible';
    saveToast.innerHTML = type === 'ok'
      ? `<i class="bi bi-check-circle-fill"></i><span>${msg}</span>`
      : `<i class="bi bi-exclamation-circle-fill"></i><span>${msg}</span>`;
  }

  function showSaveModal(opts) {
    const { title, text, type = 'ok', redirectUrl = null, okLabel = 'حسناً' } = opts;
    pendingRedirectUrl = redirectUrl;
    if (saveModal) {
      saveModal.className = type === 'ok' ? 'nsm-ok is-visible' : 'nsm-err is-visible';
      if (saveModalTitle) saveModalTitle.textContent = title;
      if (saveModalText) saveModalText.textContent = text;
      if (saveModalIcon) {
        saveModalIcon.className = type === 'ok'
          ? 'bi bi-check-circle-fill'
          : 'bi bi-exclamation-circle-fill';
      }
      if (saveModalOk) saveModalOk.textContent = SiteI18n.ta(okLabel);
    }
    showSaveToast(title, type);
    message.className = type === 'ok' ? 'alert alert-success' : 'alert alert-danger';
    message.textContent = text || title;
    message.classList.remove('d-none');
  }

  if (saveModalOk) {
    saveModalOk.addEventListener('click', () => {
      if (pendingRedirectUrl) {
        window.location.href = pendingRedirectUrl;
        return;
      }
      saveModal?.classList.remove('is-visible');
    });
  }

  async function submitForm() {
    form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
  }

  function failSubmit(msg, step) {
    showSaveModal({
      title: SiteI18n.ta('تعذر الحفظ'),
      text: msg,
      type: 'err',
      okLabel: 'حسناً',
    });
    submitBtn.disabled = false;
    submitBtn.innerHTML = SiteI18n.ta('<i class="bi bi-check-circle me-1"></i>حفظ الاحتياج');
    if (step) showStep(step);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.textContent = SiteI18n.ta('جاري الحفظ...');
    message.classList.add('d-none');
    saveToast?.classList.remove('is-visible');

    // اسم الاحتياج إلزامي
    const activeTitleInput = document.querySelector('#citizenFields [name="title"]');
    if (activeTitleInput && !activeTitleInput.value.trim()) {
      failSubmit(SiteI18n.ta('يرجى إدخال اسم الاحتياج.'), 1);
      return;
    }

    const requestingEntity = document.querySelector('#citizenFields [name="responsible_entity"]');
    if (requestingEntity && !requestingEntity.value) {
      failSubmit(SiteI18n.ta('يرجى اختيار الجهة الطالبة.'), 1);
      return;
    }

    // المحافظة إلزامية قبل الحفظ
    if (!govSelect?.value) {
      failSubmit(SiteI18n.ta('يرجى اختيار المحافظة قبل الحفظ.'), 4);
      return;
    }

    // إحداثيات الخريطة إلزامية قبل الحفظ
    const latVal = document.getElementById('latInput')?.value;
    const lngVal = document.getElementById('lngInput')?.value;
    if (!latVal || !lngVal) {
      failSubmit(SiteI18n.ta('يرجى تحديد موقع الاحتياج على الخريطة قبل الحفظ.'), 4);
      return;
    }

    // المنطقة إلزامية عند نوع الاستهداف "دعم منطقة"
    if (currentComplexity !== 'general' && targetingSelect?.value === 'geographic_area' && !districtSelect.value) {
      failSubmit(SiteI18n.ta('المنطقة إلزامية عند اختيار "دعم منطقة" كنوع استهداف.'), 4);
      return;
    }

    // الأولوية إلزامية (احتياج خاص)
    if (currentComplexity !== 'general') {
      const priorityInput = document.querySelector('[name="priority"]');
      if (priorityInput && !priorityInput.value) {
        failSubmit(SiteI18n.ta('يرجى اختيار الأولوية.'), 5);
        return;
      }
    }

    // التحقق النهائي من خطوة التصنيف
    if (currentComplexity !== 'general' && !validateTaxonomyStep()) {
      failSubmit(SiteI18n.ta('يرجى استكمال خطوة تصنيف الاحتياج.'), 2);
      return;
    }

    // تعطيل الحقول المخفية لمنع تكرار أسماء الحقول في FormData
    const hiddenInputs = form.querySelectorAll('.d-none input, .d-none select, .d-none textarea');
    hiddenInputs.forEach(el => { el.disabled = true; });
    const fd = new FormData(form);
    hiddenInputs.forEach(el => { el.disabled = false; });
    const payload = {};
    for (const [k, v] of fd.entries()) {
      if (v !== '' && v !== null) payload[k] = v;
    }

    // القطاعات المستهدفة (اختيار متعدد)
    if (currentComplexity !== 'general') {
      const sectors = selectedSectors();
      if (sectors.length) payload.sectors = sectors;
    }

    // نصوص خيار «أخرى»
    if (payload.need_category === 'other' && categoryOtherInput?.value.trim()) {
      payload.need_reason = categoryOtherInput.value.trim();
    }
    if (payload.targeting_type === 'other' && targetingOtherInput?.value.trim()) {
      payload.notes = targetingOtherInput.value.trim();
    }

    // تحويل أنواع البيانات
    if (payload.latitude) payload.latitude = parseFloat(payload.latitude);
    if (payload.longitude) payload.longitude = parseFloat(payload.longitude);
    if (payload.beneficiaries_count) payload.beneficiaries_count = parseInt(payload.beneficiaries_count);
    if (payload.expected_jobs_count) payload.expected_jobs_count = parseInt(payload.expected_jobs_count);
    if (payload.expected_projects_count) payload.expected_projects_count = parseInt(payload.expected_projects_count);
    if (payload.governorate_id && !isNaN(payload.governorate_id)) {
      payload.governorate_id = parseInt(payload.governorate_id);
    }
    payload.location_source = 'map_click';

    try {
      const res = await window.APP_API.post(window.APP_ROUTES.needStore(), payload);
      const okMsg = res.message || SiteI18n.ta('تم الحفظ بنجاح');
      const id = res.data?.id;
      const mapUrl = id ? `needs-map.php?highlight=${id}` : 'needs-map.php';
      submitBtn.innerHTML = SiteI18n.ta('<i class="bi bi-check-circle me-1"></i>تم الحفظ');
      showSaveModal({
        title: SiteI18n.ta('تم الحفظ بنجاح'),
        text: okMsg,
        type: 'ok',
        redirectUrl: mapUrl,
        okLabel: 'عرض على الخريطة',
      });
    } catch (err) {
      const errors = err?.data?.errors;
      let errMsg;
      if (errors) {
        errMsg = Object.values(errors).flat().join(' — ')
          || SiteI18n.ta('تعذر حفظ الاحتياج');
        message.innerHTML = Object.values(errors).flat().map((e) => `<div>• ${e}</div>`).join('');
      } else {
        errMsg = err?.data?.message || err?.message || SiteI18n.ta('تعذر حفظ الاحتياج');
      }
      showSaveModal({
        title: SiteI18n.ta('تعذر الحفظ'),
        text: errMsg,
        type: 'err',
        okLabel: 'حسناً',
      });
      window.scrollTo({ top: 0, behavior: 'smooth' });
      submitBtn.disabled = false;
      submitBtn.innerHTML = SiteI18n.ta('<i class="bi bi-check-circle me-1"></i>حفظ الاحتياج');
    }
  });
});
