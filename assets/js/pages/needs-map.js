document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: false });
  if (!ok) return;

  const API_BASE = window.APP_CONFIG?.API_BASE_URL || '';
  const PUBLIC_NEEDS_LOOKUPS_URL = `${API_BASE}/public/needs/lookups`;
  const PUBLIC_NEEDS_MAP_URL = `${API_BASE}/public/needs/map`;

  /* ══ ألوان القطاعات SyrSIC A–S ══ */
  const SECTOR_COLORS = {
    A:'#16a34a', B:'#92400e', C:'#1d4ed8', D:'#ca8a04',
    E:'#0891b2', F:'#ea580c', G:'#7c3aed', H:'#0f172a',
    I:'#db2777', J:'#0d9488', K:'#b45309', L:'#dc2626',
    M:'#4f46e5', N:'#64748b', O:'#334155', P:'#15803d',
    Q:'#be123c', R:'#9333ea', S:'#475569',
  };
  const SECTOR_NAMES = {
    A:SiteI18n.ta('الزراعة'), B:SiteI18n.ta('التعدين'), C:SiteI18n.ta('الصناعة التحويلية'), D:SiteI18n.ta('الكهرباء والغاز'),
    E:SiteI18n.ta('إمدادات المياه'), F:SiteI18n.ta('البناء والتشييد'), G:SiteI18n.ta('التجارة'), H:SiteI18n.ta('النقل والتخزين'),
    I:SiteI18n.ta('الفنادق والمطاعم'), J:SiteI18n.ta('المعلومات والاتصالات'), K:SiteI18n.ta('الأنشطة المالية'),
    L:SiteI18n.ta('العقارات'), M:SiteI18n.ta('الأنشطة المهنية'), N:SiteI18n.ta('الخدمات الإدارية'),
    O:SiteI18n.ta('الإدارة العامة'), P:SiteI18n.ta('التعليم'), Q:SiteI18n.ta('الصحة'), R:SiteI18n.ta('الفنون والترفيه'), S:SiteI18n.ta('خدمات أخرى'),
  };

  /* ══ حجم + حلقة الأولوية ══ */
  const PRI_SIZE = { عاجلة:22, عالية:17, متوسطة:13, منخفضة:9 };
  const PRI_RING = { عاجلة:'#dc2626', عالية:'#f97316', متوسطة:'#3b82f6', منخفضة:'#94a3b8' };
  const PRI_CLS  = { عاجلة:'np-pri-عاجلة', عالية:'np-pri-عالية', متوسطة:'np-pri-متوسطة', منخفضة:'np-pri-منخفضة' };

  /* ══ أيقونة + لون مميز لكل نوع منشأة ══ */
  const FACILITY_STYLE = {
    family_development_center:         { icon: 'bi-house-heart-fill',   color: '#db2777' },
    small_projects_development_unit:   { icon: 'bi-briefcase-fill',     color: '#b45309' },
    project_environment:               { icon: 'bi-houses-fill',        color: '#0d9488' },
    business_incubator:                { icon: 'bi-egg-fill',           color: '#7c3aed' },
    business_hub:                      { icon: 'bi-diagram-3-fill',     color: '#1d4ed8' },
    entrepreneurship_center:           { icon: 'bi-rocket-takeoff-fill',color: '#ea580c' },
    free_workspace:                    { icon: 'bi-laptop-fill',        color: '#0891b2' },
    studies_center:                    { icon: 'bi-journal-text',       color: '#4f46e5' },
    financing_services_center:         { icon: 'bi-cash-coin',          color: '#16a34a' },
    micro_project_registration_center: { icon: 'bi-patch-check-fill',   color: '#be123c' },
  };

  function facilityIcon(facilityType, priority) {
    var st = FACILITY_STYLE[facilityType];
    if (!st) return null;
    var ring = PRI_RING[priority] || '#6b7280';
    var size = 30;
    return L.divIcon({
      className: '',
      html: '<div style="width:' + size + 'px;height:' + size + 'px;border-radius:8px;background:' + st.color +
            ';border:2.5px solid ' + ring + ';display:flex;align-items:center;justify-content:center;' +
            'box-shadow:0 2px 8px rgba(0,0,0,.4)">' +
            '<i class="bi ' + st.icon + '" style="color:#fff;font-size:15px"></i></div>',
      iconSize: [size, size],
      iconAnchor: [size / 2, size / 2],
      popupAnchor: [0, -(size / 2 + 6)],
    });
  }

  function sectorColor(sec, name) {
    if (sec && SECTOR_COLORS[sec]) return SECTOR_COLORS[sec];
    if (!name) return '#6b7280';
    var h = 0;
    for (var i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) & 0xffffff;
    return Object.values(SECTOR_COLORS)[Math.abs(h) % 19];
  }

  function makeIcon(priority, sectionCode, sectorName) {
    var s    = PRI_SIZE[priority] || 13;
    var ring = PRI_RING[priority] || '#6b7280';
    var fill = sectorColor(sectionCode, sectorName);
    var outer = s + 5;
    var pulse = priority === 'عاجلة'
      ? '<div class="pulse-ring"></div>'
      : '';
    return L.divIcon({
      className: '',
      html: '<div style="position:relative;width:' + outer + 'px;height:' + outer + 'px">' +
            pulse +
            '<div style="position:absolute;inset:0;border-radius:50%;background:' + ring +
            ';display:flex;align-items:center;justify-content:center;' +
            'box-shadow:0 2px 8px rgba(0,0,0,.4)">' +
            '<div style="width:' + s + 'px;height:' + s + 'px;border-radius:50%;background:' +
            fill + ';border:1.5px solid rgba(255,255,255,.85)"></div></div></div>',
      iconSize: [outer, outer],
      iconAnchor: [outer / 2, outer / 2],
      popupAnchor: [0, -(outer / 2 + 6)],
    });
  }

  /* ══ الخريطة ══ */
  const GEO_BASE = '../../assets/geo/';
  const BOUNDS  = L.latLngBounds([32.31, 35.47], [37.32, 42.38]);
  const CENTER  = [35.0, 38.5];
  const DIST_MIN_ZOOM = 7;

  const map = L.map('needsMap', {
    center: CENTER, zoom: 7, minZoom: 7, maxZoom: 18,
    maxBounds: BOUNDS, maxBoundsViscosity: 1.0,
    zoomControl: false,
  });

  L.control.zoom({ position: 'bottomleft' }).addTo(map);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
  }).addTo(map);

  var districtLayer = null;
  var governorateLayer = null;
  var govBoundsByName = {};
  var govIdByName = {};
  var selectedGovName = '';
  var govModalPage = 1;
  var govModalGovId = null;
  var govModalGovName = '';
  var govModalLastPage = 1;
  var suppressGovChangeModal = false;

  function govFillColor(name) {
    if (!name) return '#3b82f6';
    var h = 0;
    for (var i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) & 0xffffff;
    return 'hsl(' + (h % 360) + ', 55%, 45%)';
  }

  function defaultGovStyle(feature) {
    var name = feature.properties.name || '';
    var selected = selectedGovName && selectedGovName === name;
    return {
      color: selected ? '#0f172a' : '#1e3a5f',
      weight: selected ? 3 : 2,
      fillColor: govFillColor(name),
      fillOpacity: selected ? 0.18 : 0.07,
    };
  }

  async function loadGeoLayers() {
    try {
      var responses = await Promise.all([
        fetch(GEO_BASE + 'syria-adm1.geojson'),
        fetch(GEO_BASE + 'syria-adm2.geojson'),
      ]);

      if (!responses[0].ok || !responses[1].ok) {
        throw new Error('geojson fetch failed');
      }

      var adm1 = await responses[0].json();
      var adm2 = await responses[1].json();

      districtLayer = L.geoJSON(adm2, {
        style: function () {
          return { color: '#64748b', weight: 0.8, fillColor: '#94a3b8', fillOpacity: 0.04, dashArray: '4 4' };
        },
        onEachFeature: function (f, layer) {
          var label = f.properties.name || f.properties.name_en || '';
          var gov = f.properties.governorate || '';
          if (label) {
            layer.bindTooltip(gov ? (label + ' — ' + gov) : label, {
              permanent: false,
              className: 'district-tooltip',
            });
          }
        },
      });

      governorateLayer = L.geoJSON(adm1, {
        style: defaultGovStyle,
        onEachFeature: function (f, layer) {
          var name = f.properties.name || '';
          if (name) {
            govBoundsByName[name] = layer.getBounds();
            attachGovernorateInteractions(layer, name);
          }

          var center = null;
          if (f.properties.center_lat != null && f.properties.center_lng != null) {
            center = [Number(f.properties.center_lat), Number(f.properties.center_lng)];
          } else if (window.SYRIA_GOVERNORATE_CENTERS && window.SYRIA_GOVERNORATE_CENTERS[name]) {
            center = window.SYRIA_GOVERNORATE_CENTERS[name];
          } else {
            center = layer.getBounds().getCenter();
          }

          if (name && center) {
            L.marker(center, {
              icon: L.divIcon({
                className: 'gov-label',
                html: name,
                iconSize: [100, 20],
                iconAnchor: [50, 10],
              }),
              interactive: false,
            }).addTo(map);
          }
        },
      }).addTo(map);
    } catch (err) {
      console.warn('[needs-map] GeoJSON layers unavailable; map will run without boundaries.', err);
      var fallbackGov = window.SYRIA_GOVERNORATES_GEOJSON || window.SYRIA_GOVERNORATES;
      if (fallbackGov) {
        governorateLayer = L.geoJSON(fallbackGov, {
          style: defaultGovStyle,
          onEachFeature: function (f, layer) {
            var name = f.properties.name || '';
            if (name) {
              govBoundsByName[name] = layer.getBounds();
              attachGovernorateInteractions(layer, name);
            }
          },
        }).addTo(map);
      }
      var fallbackDist = window.SYRIA_DISTRICTS_GEOJSON;
      if (fallbackDist) {
        districtLayer = L.geoJSON(fallbackDist, {
          style: { color: '#64748b', weight: 0.8, fillOpacity: 0.04, dashArray: '4 4' },
        });
      }
    }
  }


  function findGovOptionByName(name) {
    if (!name || !govFilter) return null;
    var n = String(name).trim();
    for (var i = 0; i < govFilter.options.length; i++) {
      var t = (govFilter.options[i].text || '').trim();
      if (t === n || t.indexOf(n) === 0 || n.indexOf(t) === 0) return govFilter.options[i];
    }
    if (govIdByName[n]) {
      for (var j = 0; j < govFilter.options.length; j++) {
        if (String(govFilter.options[j].value) === String(govIdByName[n])) return govFilter.options[j];
      }
    }
    return null;
  }

  function attachGovernorateInteractions(layer, name) {
    if (!layer || !name) return;
    layer.on('mouseover', function () {
      layer.setStyle({ weight: 2.4, fillOpacity: 0.28 });
      if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) layer.bringToFront();
    });
    layer.on('mouseout', function () { refreshGovernorateStyles(); });
    layer.on('click', function (e) {
      if (e && e.originalEvent) L.DomEvent.stopPropagation(e.originalEvent);
      onGovernorateClick(name);
    });
  }

  function onGovernorateClick(name) {
    if (currentMode !== 'needs') switchMode('needs');
    var opt = findGovOptionByName(name);
    selectedGovName = name;
    if (opt) {
      suppressGovChangeModal = true;
      govFilter.value = opt.value;
      selectedGovName = opt.text || name;
      refreshGovernorateStyles();
      loadDistrictOptions();
      if (govBoundsByName[selectedGovName]) {
        map.fitBounds(govBoundsByName[selectedGovName], { padding: [36, 36], maxZoom: 10 });
      } else if (govBoundsByName[name]) {
        map.fitBounds(govBoundsByName[name], { padding: [36, 36], maxZoom: 10 });
      }
      loadMode();
      openGovNeedsModal(opt.value, selectedGovName);
      setTimeout(function () { suppressGovChangeModal = false; }, 0);
    } else {
      refreshGovernorateStyles();
      openGovNeedsModal(govIdByName[name] || null, name);
    }
  }

  function refreshGovernorateStyles() {
    if (!governorateLayer) return;
    governorateLayer.setStyle(defaultGovStyle);
  }

  function syncDistrictLayer() {
    if (!districtLayer) return;
    if (map.getZoom() >= DIST_MIN_ZOOM) districtLayer.addTo(map);
    else if (map.hasLayer(districtLayer)) map.removeLayer(districtLayer);
  }

  map.on('zoomend', syncDistrictLayer);

  /* ══ أوضاع الخريطة ══ */
  var currentMode    = 'needs';
  var markersLayer   = L.layerGroup().addTo(map);
  var highlightLayer = L.layerGroup().addTo(map);
  var activeSectors  = {};

  // تمييز احتياج مُحدَّد قادم من صفحة الحفظ (?highlight=<id>) — يلمع ويتوسّط الخريطة
  var HIGHLIGHT_ID   = new URLSearchParams(window.location.search).get('highlight');
  var highlightDone  = false;

  var MODE_CONFIG = {
    needs:         { label: SiteI18n.ta('الاحتياجات'),    icon: 'bi-clipboard-data',  color: '#1d4ed8' },
    entrepreneurs: { label: SiteI18n.ta('رواد الأعمال'),  icon: 'bi-person-workspace', color: '#16a34a' },
    centers:       { label: SiteI18n.ta('المراكز التدريبية'), icon: 'bi-building',         color: '#ea580c' },
    trainees:      { label: SiteI18n.ta('المتدربون'),      icon: 'bi-mortarboard',      color: '#9333ea' },
  };

  function switchMode(mode) {
    currentMode = mode;
    document.querySelectorAll('.map-mode-btn').forEach(function (b) {
      b.classList.toggle('active', b.dataset.mode === mode);
    });
    var needsFilters = document.getElementById('needsFiltersBar');
    if (needsFilters) needsFilters.classList.toggle('d-none', mode !== 'needs');
    markersLayer.clearLayers();
    activeSectors = {};
    activeFacilities = {};
    sectorLegend.style.display = 'none';
    if (facilityLegend) facilityLegend.style.display = 'none';
    loadMode();
  }

  /* ══ عناصر الصفحة ══ */
  var govFilter       = document.getElementById('mapGovFilter');
  var sectorFilter    = document.getElementById('mapSectorFilter');
  var priorityFilter  = document.getElementById('mapPriorityFilter');
  var statusFilter    = document.getElementById('mapStatusFilter');
  var categoryFilter  = document.getElementById('mapCategoryFilter');
  var facilityFilter  = document.getElementById('mapFacilityFilter');
  var subtypeFilter   = document.getElementById('mapSubtypeFilter');
  var targetingFilter = document.getElementById('mapTargetingFilter');
  var districtFilter  = document.getElementById('mapDistrictFilter');
  var searchFilter    = document.getElementById('mapSearchFilter');
  var ownerFilter     = document.getElementById('mapOwnerFilter');
  var scopeFilter     = document.getElementById('mapScopeFilter');
  var complexityFilter= document.getElementById('mapComplexityFilter');
  var needTypeFilter  = document.getElementById('mapNeedTypeFilter');
  var sourceFilter    = document.getElementById('mapSourceFilter');
  var interventionFilter = document.getElementById('mapInterventionFilter');
  var meta            = document.getElementById('needsMapMeta');
  var sectorLegend    = document.getElementById('sectorLegend');
  var facilityLegend  = document.getElementById('facilityLegend');
  var activeFacilities = {};
  var searchTimer = null;

  function collectFilterParams() {
    var params = {};
    if (govFilter && govFilter.value) params.governorate_id = govFilter.value;
    if (priorityFilter && priorityFilter.value) params.priority = priorityFilter.value;
    if (statusFilter && statusFilter.value) params.status = statusFilter.value;
    if (sectorFilter && sectorFilter.value) {
      if (sectorFilter.value.indexOf('code:') === 0) params.sector_code = sectorFilter.value.slice(5);
      else params.sector = sectorFilter.value;
    }
    if (categoryFilter && categoryFilter.value) params.need_category = categoryFilter.value;
    if (facilityFilter && facilityFilter.value) params.facility_type = facilityFilter.value;
    if (subtypeFilter && subtypeFilter.value) params.facility_subtype = subtypeFilter.value;
    if (targetingFilter && targetingFilter.value) params.targeting_type = targetingFilter.value;
    if (districtFilter && districtFilter.value) params.district_name = districtFilter.value;
    if (ownerFilter && ownerFilter.value) params.need_owner_type = ownerFilter.value;
    if (scopeFilter && scopeFilter.value) params.need_scope = scopeFilter.value;
    if (complexityFilter && complexityFilter.value) params.need_complexity = complexityFilter.value;
    if (needTypeFilter && needTypeFilter.value) params.need_type = needTypeFilter.value;
    if (sourceFilter && sourceFilter.value) params.source_platform = sourceFilter.value;
    if (interventionFilter && interventionFilter.value) params.proposed_intervention = interventionFilter.value;
    if (searchFilter && searchFilter.value.trim()) params.q = searchFilter.value.trim();
    return params;
  }

  function clearAllFilters() {
    [sectorFilter, priorityFilter, statusFilter, categoryFilter, facilityFilter, subtypeFilter,
     targetingFilter, districtFilter, ownerFilter, scopeFilter, complexityFilter, needTypeFilter,
     sourceFilter, interventionFilter].forEach(function (el) { if (el) el.value = ''; });
    if (searchFilter) searchFilter.value = '';
    if (govFilter) govFilter.value = '';
    selectedGovName = '';
    refreshGovernorateStyles();
    loadDistrictOptions();
    map.flyTo(CENTER, 7, { duration: 0.5 });
    if (currentMode === 'needs') loadPoints();
  }


  function setLoading(on) {
    meta.innerHTML = on
      ? '<span class="spinner-border spinner-border-sm"></span> جاري التحميل...' : '';
  }

  /* ══ تحميل المحافظات من API ══ */
  async function loadGovernorates() {
    try {
      var res  = await window.APP_API.get(
        window.APP_ROUTES.governorates
          ? window.APP_ROUTES.governorates()
          : (window.APP_CONFIG.API_BASE_URL + '/governorates')
      );
      var list = res.data || res || [];
      list.forEach(function (g) {
        var label = g.name_ar || g.name || '';
        govFilter.appendChild(new Option(label, g.id));
        if (label) govIdByName[label] = g.id;
      });
    } catch (_) {
      (window.SYRIA_GOVERNORATES_LIST || []).forEach(function (g) {
        govFilter.appendChild(new Option(g.label, g.value));
      });
    }
  }

  govFilter.addEventListener('change', function () {
    var txt = govFilter.options[govFilter.selectedIndex]
      ? govFilter.options[govFilter.selectedIndex].text : '';
    selectedGovName = govFilter.value ? txt : '';
    refreshGovernorateStyles();
    loadDistrictOptions();

    if (!govFilter.value) {
      map.flyTo(CENTER, 7, { duration: 0.7 });
    } else if (govBoundsByName[txt]) {
      map.fitBounds(govBoundsByName[txt], { padding: [36, 36], maxZoom: 10, duration: 0.7 });
    } else {
      var c = (window.SYRIA_GOVERNORATE_CENTERS || {})[txt];
      if (c) map.flyTo(c, 9, { duration: 0.7 });
      else map.flyTo(CENTER, 7, { duration: 0.7 });
    }
    loadMode();
  });

  /* ══ قائمة المناطق حسب المحافظة ══ */
  async function loadDistrictOptions() {
    if (!districtFilter) return;
    districtFilter.innerHTML = '<option value="">' + SiteI18n.ta('كل المناطق') + '</option>';
    districtFilter.disabled = true;
    if (!govFilter.value || !window.APP_ROUTES.needsAdminUnits) return;
    try {
      var res = await window.APP_API.get(
        window.APP_ROUTES.needsAdminUnits({ governorate_id: govFilter.value, per_page: 200 })
      );
      var districts = [];
      (res.data || []).forEach(function (u) {
        if (u.district_name && districts.indexOf(u.district_name) === -1) districts.push(u.district_name);
      });
      districts.forEach(function (d) { districtFilter.appendChild(new Option(d, d)); });
      districtFilter.disabled = !districts.length;
    } catch (_) {}
  }

  [sectorFilter, priorityFilter, statusFilter, categoryFilter, facilityFilter, subtypeFilter, targetingFilter, districtFilter,
   ownerFilter, scopeFilter, complexityFilter, needTypeFilter, sourceFilter, interventionFilter]
    .filter(Boolean)
    .forEach(function (el) {
      el.addEventListener('change', function () { if (currentMode === 'needs') loadPoints(); });
    });
  if (searchFilter) {
    searchFilter.addEventListener('input', function () {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () { if (currentMode === 'needs') loadPoints(); }, 400);
    });
  }
  var clearBtn = document.getElementById('mapClearFiltersBtn');
  if (clearBtn) clearBtn.addEventListener('click', clearAllFilters);

  /* إظهار فلتر نوع الحاضنة فقط عند اختيار حاضنة أعمال */
  facilityFilter && facilityFilter.addEventListener('change', function () {
    var wrap = subtypeFilter ? subtypeFilter.closest('.col-6') : null;
    var isIncubator = facilityFilter.value === 'business_incubator' || facilityFilter.value === '';
    if (wrap) wrap.style.display = isIncubator ? '' : 'none';
    if (!isIncubator && subtypeFilter) subtypeFilter.value = '';
  });

  /* ══ تحميل القوائم ══ */
  async function fetchNeedsLookups() {
    try {
      return await window.APP_API.get(window.APP_ROUTES.needsLookups());
    } catch (error) {
      if (error?.status === 403) {
        return window.APP_API.get(PUBLIC_NEEDS_LOOKUPS_URL);
      }
      throw error;
    }
  }

  async function loadLookups() {
    try {
      var res  = await fetchNeedsLookups();
      var data = res.data || {};

      // القطاعات: القائمة المرجعية الجديدة إن وجدت، وإلا القيم النصية القديمة
      if (data.sector_options && data.sector_options.length) {
        data.sector_options.forEach(function (s) {
          sectorFilter.appendChild(new Option(s.label, 'code:' + s.value));
        });
      } else {
        (data.sectors || []).forEach(function (s) { sectorFilter.appendChild(new Option(s, s)); });
      }

      Object.entries(data.status_codes || {}).forEach(function ([label, code]) {
        statusFilter.appendChild(new Option(label, code));
      });

      var fillTaxonomy = function (select, items) {
        if (!select) return;
        (items || []).forEach(function (item) {
          select.appendChild(new Option(item.label, item.value));
        });
      };
      fillTaxonomy(categoryFilter, data.need_categories);
      fillTaxonomy(facilityFilter, data.facility_types);
      fillTaxonomy(subtypeFilter, data.facility_subtypes);
      fillTaxonomy(targetingFilter, data.targeting_types);
    } catch (_) {}
  }

  /* ══ تحميل النقاط ══ */
  async function fetchNeedsMap(params) {
    try {
      return await window.APP_API.get(window.APP_ROUTES.needsMap(params));
    } catch (error) {
      if (error?.status === 403) {
        return window.APP_API.get(window.APP_API.withQuery(PUBLIC_NEEDS_MAP_URL, params));
      }
      throw error;
    }
  }

  async function loadPoints() {
    var params = collectFilterParams();
    params.limit = 500;

    setLoading(true);
    try {
      var res    = await fetchNeedsMap(params);
      var points = res.data || [];
      markersLayer.clearLayers();
      activeSectors = {};
      activeFacilities = {};
      var shown = 0;
      var hlLatLng = null;
      var hlMarker = null;

      points.forEach(function (p) {
        if (!p.latitude || !p.longitude) return;
        var lat = parseFloat(p.latitude);
        var lng = parseFloat(p.longitude);
        if (!BOUNDS.contains([lat, lng])) return;

        var sec  = p.syrsic_section || '';
        var col  = sectorColor(sec, p.sector);
        var pri  = p.priority || 'متوسطة';

        // احتياجات المنشآت: أيقونة مميزة حسب نوع المنشأة — والباقي بنمط القطاع/الأولوية القديم
        var icon = facilityIcon(p.facility_type, pri);
        if (icon) {
          if (!activeFacilities[p.facility_type]) {
            activeFacilities[p.facility_type] = {
              label: p.facility_type_label || p.facility_type,
              style: FACILITY_STYLE[p.facility_type],
            };
          }
        } else {
          icon = makeIcon(pri, sec, p.sector);
          var key = sec || p.sector || '';
          if (key && !activeSectors[key]) {
            activeSectors[key] = { name: SECTOR_NAMES[sec] || p.sector || sec, color: col };
          }
        }

        var marker = L.marker([lat, lng], { icon: icon });
        marker.bindPopup(buildNeedPopup(p, pri), { maxWidth: 290, minWidth: 220 });

        markersLayer.addLayer(marker);
        shown++;

        if (HIGHLIGHT_ID && String(p.id) === String(HIGHLIGHT_ID)) {
          hlLatLng = [lat, lng];
          hlMarker = marker;
        }
      });

      meta.textContent = SiteI18n.ta('يُعرض ') + shown + SiteI18n.ta(' احتياج') + (shown !== points.length ? SiteI18n.ta(' (من ') + points.length + ')' : '');
      buildSectorLegend();
      buildFacilityLegend();

      // تمييز الاحتياج القادم من صفحة الحفظ: توسيط + لمعان + فتح النافذة (مرة واحدة)
      if (HIGHLIGHT_ID && !highlightDone && hlLatLng) {
        highlightDone = true;
        highlightLayer.clearLayers();
        L.marker(hlLatLng, {
          icon: L.divIcon({ className: 'need-hl-icon', html: '<div class="need-hl-pulse"></div>', iconSize: [30, 30], iconAnchor: [15, 15] }),
          interactive: false,
          zIndexOffset: -1,
        }).addTo(highlightLayer);
        map.flyTo(hlLatLng, 13, { duration: 1.0 });
        setTimeout(function () { if (hlMarker) hlMarker.openPopup(); }, 1150);
      }
    } catch (_) {
      meta.textContent = SiteI18n.ta('تعذّر تحميل البيانات');
    }
  }

  function buildNeedPopup(p, pri) {
    var priCls = PRI_CLS[pri] || 'np-pri-متوسطة';
    var statusLabel = p.status_label
      || (window.NeedsPlatform ? window.NeedsPlatform.statusLabel(p.status) : (p.status || ''));

    var sectorsLabels = (p.sectors || []).map(function (s) { return s.label; }).join('، ');
    if (!sectorsLabels && p.sector) sectorsLabels = p.sector;

    var locationText = govDisplay(p.governorate);
    if (p.district_name) locationText += ' — ' + p.district_name;

    var facilityRow = '';
    if (p.facility_type_label) {
      var fs = FACILITY_STYLE[p.facility_type] || {};
      facilityRow = '<div class="np-row"><i class="bi ' + (fs.icon || 'bi-building') + '" style="color:' + (fs.color || '#6b7280') + '"></i>' +
        _e(p.facility_type_label) +
        (p.facility_subtype_label ? ' <span style="color:#9ca3af">(' + _e(p.facility_subtype_label) + ')</span>' : '') +
        '</div>';
    }

    return '<div class="need-popup">' +
      '<p class="np-title">' + _e(p.title) + '</p>' +
      '<div class="np-row">' +
        '<span class="np-badge ' + priCls + '">' + _e(pri) + '</span>' +
        (p.need_category_label ? '<span class="np-sector-tag">' + _e(p.need_category_label) + '</span>' : '') +
      '</div>' +
      '<hr class="np-divider">' +
      facilityRow +
      (sectorsLabels ? '<div class="np-row"><i class="bi bi-grid-fill" style="color:#6b7280"></i>' + _e(sectorsLabels) + '</div>' : '') +
      (p.targeting_type_label ? '<div class="np-row"><i class="bi bi-bullseye" style="color:#6b7280"></i>' + _e(p.targeting_type_label) + '</div>' : '') +
      '<div class="np-row"><i class="bi bi-geo-alt-fill" style="color:#dc2626"></i>' + _e(locationText) + '</div>' +
      '<div class="np-row"><i class="bi bi-clipboard-check" style="color:#6b7280"></i>' + statusLabel + '</div>' +
      (p.beneficiaries_count ? '<div class="np-row"><i class="bi bi-people" style="color:#6b7280"></i>' + p.beneficiaries_count + SiteI18n.ta(' مستفيد') + '</div>': '') +
      (p.need_code ? '<div class="np-row" style="font-family:monospace;font-size:10px;color:#9ca3af">#' + _e(p.need_code) + '</div>' : '') +
      '<a href="../gis/need-view.php?id=' + p.id + '" class="btn btn-sm btn-brand w-100 mt-2">' + SiteI18n.ta('عرض التفاصيل') + '</a>' +
      '</div>';
  }

  function buildSectorLegend() {
    var keys = Object.keys(activeSectors);
    if (!keys.length) { sectorLegend.style.display = 'none'; return; }
    sectorLegend.style.display = '';
    var html = '<span class="lg-label">' + SiteI18n.ta('القطاع') + '</span>';
    keys.forEach(function (k) {
      var s = activeSectors[k];
      html += '<span class="legend-item">' +
              '<span class="legend-dot" style="width:10px;height:10px;background:' + s.color + '"></span>' +
              _e(s.name || k) + '</span>';
    });
    sectorLegend.innerHTML = html;
  }

  function buildFacilityLegend() {
    if (!facilityLegend) return;
    var keys = Object.keys(activeFacilities);
    if (!keys.length) { facilityLegend.style.display = 'none'; return; }
    facilityLegend.style.display = '';
    var html = '<span class="lg-label">' + SiteI18n.ta('نوع المنشأة') + '</span>';
    keys.forEach(function (k) {
      var f = activeFacilities[k];
      var st = f.style || {};
      html += '<span class="legend-item">' +
              '<span class="legend-dot" style="width:12px;height:12px;border-radius:3px;background:' + (st.color || '#6b7280') + ';display:inline-flex;align-items:center;justify-content:center">' +
              '<i class="bi ' + (st.icon || 'bi-building') + '" style="color:#fff;font-size:7px"></i></span>' +
              _e(f.label || k) + '</span>';
    });
    facilityLegend.innerHTML = html;
  }

  function govDisplay(gov) {
    if (!gov) return SiteI18n.ta('غير محدد');
    if (typeof gov === 'string') return gov.trim() || SiteI18n.ta('غير محدد');
    if (typeof gov === 'object') {
      return gov.name_ar || gov.governorate_name || gov.name || gov.label || SiteI18n.ta('غير محدد');
    }
    return SiteI18n.ta('غير محدد');
  }

  function _e(v) {
    if (!v) return '';
    return String(v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }


  /* ══ مودال احتياجات المحافظة ══ */
  function openGovNeedsModal(govId, govName) {
    var modal = document.getElementById('govNeedsModal');
    if (!modal) return;
    govModalGovId = govId || null;
    govModalGovName = govName || '';
    govModalPage = 1;
    document.getElementById('govNeedsTitle').textContent = 'احتياجات — ' + (govName || 'محافظة');
    document.getElementById('govNeedsSub').textContent = 'قائمة الاحتياجات ضمن هذه المحافظة';
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    var listLink = document.getElementById('govNeedsListLink');
    if (listLink) {
      if (govId) {
        listLink.style.display = '';
        listLink.href = 'needs-list.php?governorate_id=' + encodeURIComponent(govId);
      } else {
        listLink.style.display = 'none';
      }
    }
    loadGovNeedsPage(1);
  }

  function closeGovNeedsModal() {
    var modal = document.getElementById('govNeedsModal');
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
  }

  async function loadGovNeedsPage(page) {
    govModalPage = page || 1;
    var body = document.getElementById('govNeedsBody');
    var countEl = document.getElementById('govNeedsCount');
    var prevBtn = document.getElementById('govNeedsPrev');
    var nextBtn = document.getElementById('govNeedsNext');
    if (body) body.innerHTML = '<div class="gov-needs-loading"><span class="spinner-border spinner-border-sm"></span> جاري التحميل...</div>';

    var params = collectFilterParams();
    if (govModalGovId) params.governorate_id = govModalGovId;
    params.page = govModalPage;
    params.per_page = 20;

    var items = [];
    var total = 0;
    var lastPage = 1;

    try {
      if (window.AppAuth && window.AppAuth.isLoggedIn && window.AppAuth.isLoggedIn() && window.APP_ROUTES.needs) {
        var res = await window.APP_API.get(window.APP_ROUTES.needs(params));
        items = res.data || [];
        if (!Array.isArray(items) && res.data && Array.isArray(res.data.data)) items = res.data.data;
        total = res.total || res.meta?.total || items.length;
        lastPage = res.last_page || res.meta?.last_page || 1;
      } else {
        var mapParams = Object.assign({}, params, { limit: 200 });
        delete mapParams.page;
        delete mapParams.per_page;
        var mapRes = await fetchNeedsMap(mapParams);
        var all = mapRes.data || [];
        total = all.length;
        lastPage = Math.max(1, Math.ceil(total / 20));
        var start = (govModalPage - 1) * 20;
        items = all.slice(start, start + 20);
      }
    } catch (err) {
      try {
        var mapParams2 = Object.assign({}, collectFilterParams(), { limit: 200 });
        if (govModalGovId) mapParams2.governorate_id = govModalGovId;
        var mapRes2 = await fetchNeedsMap(mapParams2);
        var all2 = mapRes2.data || [];
        total = all2.length;
        lastPage = Math.max(1, Math.ceil(total / 20));
        var start2 = (govModalPage - 1) * 20;
        items = all2.slice(start2, start2 + 20);
      } catch (_) {
        if (body) body.innerHTML = '<div class="gov-needs-empty">تعذّر تحميل الاحتياجات</div>';
        if (countEl) countEl.textContent = '—';
        return;
      }
    }

    govModalLastPage = lastPage;
    if (countEl) countEl.textContent = total + ' احتياج — صفحة ' + govModalPage + ' / ' + lastPage;
    if (prevBtn) prevBtn.disabled = govModalPage <= 1;
    if (nextBtn) nextBtn.disabled = govModalPage >= lastPage;

    if (!items.length) {
      if (body) body.innerHTML = '<div class="gov-needs-empty"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.4"></i>لا توجد احتياجات بهذه الفلاتر في ' + _e(govModalGovName || 'هذه المحافظة') + '</div>';
      return;
    }

    body.innerHTML = items.map(function (n) {
      var pri = n.priority || 'متوسطة';
      var priCls = PRI_CLS[pri] || 'np-pri-متوسطة';
      var statusLabel = n.status_label || (window.NeedsPlatform ? window.NeedsPlatform.statusLabel(n.status) : (n.status || '—'));
      var sectorsLabels = (n.sectors || []).map(function (s) { return s.label || s.name_ar || s.code; }).filter(Boolean).join('، ');
      if (!sectorsLabels && n.sector) sectorsLabels = n.sector;
      var loc = govDisplay(n.governorate);
      if (n.district_name) loc += ' — ' + n.district_name;
      var hasGeo = n.latitude && n.longitude;
      return '<div class="gov-need-card">' +
        '<div class="gov-need-title">' + _e(n.title || n.need_code || ('#' + n.id)) + '</div>' +
        '<div class="gov-need-meta">' +
          '<span class="np-badge ' + priCls + '">' + _e(pri) + '</span>' +
          (n.need_category_label ? '<span class="np-sector-tag">' + _e(n.need_category_label) + '</span>' : '') +
          '<span><i class="bi bi-clipboard-check"></i> ' + _e(statusLabel) + '</span>' +
          (sectorsLabels ? '<span><i class="bi bi-grid"></i> ' + _e(sectorsLabels) + '</span>' : '') +
          '<span><i class="bi bi-geo-alt"></i> ' + _e(loc) + '</span>' +
          (n.need_code ? '<span style="font-family:monospace">#' + _e(n.need_code) + '</span>' : '') +
        '</div>' +
        '<div class="gov-need-actions">' +
          '<a class="gov-btn-view" href="need-view.php?id=' + n.id + '"><i class="bi bi-eye"></i> التفاصيل</a>' +
          (hasGeo ? '<button type="button" class="gov-btn-map" data-lat="' + n.latitude + '" data-lng="' + n.longitude + '" data-id="' + n.id + '"><i class="bi bi-geo-alt-fill"></i> على الخريطة</button>' : '') +
        '</div>' +
      '</div>';
    }).join('');

    body.querySelectorAll('.gov-btn-map').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var lat = parseFloat(btn.getAttribute('data-lat'));
        var lng = parseFloat(btn.getAttribute('data-lng'));
        closeGovNeedsModal();
        if (!isNaN(lat) && !isNaN(lng)) {
          map.flyTo([lat, lng], 13, { duration: 0.8 });
        }
      });
    });
  }

  (function wireGovNeedsModal() {
    var modal = document.getElementById('govNeedsModal');
    if (!modal) return;
    var closeBtn = document.getElementById('govNeedsClose');
    if (closeBtn) closeBtn.addEventListener('click', closeGovNeedsModal);
    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeGovNeedsModal();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('open')) closeGovNeedsModal();
    });
    var prevBtn = document.getElementById('govNeedsPrev');
    var nextBtn = document.getElementById('govNeedsNext');
    if (prevBtn) prevBtn.addEventListener('click', function () {
      if (govModalPage > 1) loadGovNeedsPage(govModalPage - 1);
    });
    if (nextBtn) nextBtn.addEventListener('click', function () {
      if (govModalPage < govModalLastPage) loadGovNeedsPage(govModalPage + 1);
    });
  })();

  /* ══ دالة توزيع الأوضاع ══ */
  async function loadMode() {
    if (currentMode === 'needs')         return loadPoints();
    if (currentMode === 'entrepreneurs') return loadEntrepreneurs();
    if (currentMode === 'centers')       return loadCenters();
    if (currentMode === 'trainees')      return loadTrainees();
  }

  /* ══ رواد الأعمال: احتياجات خاصة / مواطن معتمدة ══ */
  async function loadEntrepreneurs() {
    setLoading(true);
    var params = { need_complexity: 'specific', need_owner_type: 'citizen', status: 'approved', limit: 500 };
    if (govFilter.value) params.governorate_id = govFilter.value;
    try {
      var res = await fetchNeedsMap(params);
      var points = res.data || [];
      markersLayer.clearLayers();
      var shown = 0;
      points.forEach(function (p) {
        if (!p.latitude || !p.longitude) return;
        var lat = parseFloat(p.latitude), lng = parseFloat(p.longitude);
        if (!BOUNDS.contains([lat, lng])) return;
        var icon = L.divIcon({
          className: '', iconSize: [14, 14], iconAnchor: [7, 7],
          html: '<div style="width:14px;height:14px;border-radius:50%;background:#16a34a;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>',
        });
        L.marker([lat, lng], { icon: icon })
          .bindPopup('<div class="need-popup"><p class="np-title">' + _e(p.title) + '</p>'
            + '<div class="np-row"><i class="bi bi-person-fill" style="color:#16a34a"></i>' + _e(p.applicant_name || p.sector || '—') + '</div>'
            + '<div class="np-row"><i class="bi bi-geo-alt-fill" style="color:#dc2626"></i>' + _e(govDisplay(p.governorate)) + '</div>'
            + '<a href="../gis/need-view.php?id=' + p.id + '" class="btn btn-sm btn-outline-success w-100 mt-2">عرض</a></div>', { maxWidth: 240 })
          .addTo(markersLayer);
        shown++;
      });
      meta.textContent = shown + SiteI18n.ta(' رائد أعمال');
    } catch (_) { meta.textContent = SiteI18n.ta('تعذر التحميل'); }
  }

  /* ══ مراكز التدريب (نقاط خريطة مخصّصة) ══ */
  async function loadCenters() {
    setLoading(true);
    try {
      var res = await window.APP_API.get(
        window.APP_ROUTES.mapTrainingCenters
          ? window.APP_ROUTES.mapTrainingCenters({ limit: 500 })
          : (window.APP_CONFIG.API_BASE_URL + '/map/training-centers?limit=500')
      );
      var centers = res.data || [];
      markersLayer.clearLayers();
      var shown = 0;
      centers.forEach(function (c) {
        var lat = c.latitude != null ? parseFloat(c.latitude) : NaN;
        var lng = c.longitude != null ? parseFloat(c.longitude) : NaN;
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        if (!BOUNDS.contains([lat, lng])) return;
        var icon = L.divIcon({
          className: '', iconSize: [16, 16], iconAnchor: [8, 8],
          html: '<div style="width:16px;height:16px;border-radius:3px;background:#ea580c;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>',
        });
        L.marker([lat, lng], { icon: icon })
          .bindPopup('<div class="need-popup"><p class="np-title">' + _e(c.name || '—') + '</p>'
            + '<div class="np-row"><i class="bi bi-building" style="color:#ea580c"></i>' + _e(govDisplay(c.governorate || c.city)) + '</div>'
            + (c.address ? '<div class="np-row"><i class="bi bi-geo-alt"></i>' + _e(c.address) + '</div>' : '')
            + '</div>', { maxWidth: 240 })
          .addTo(markersLayer);
        shown++;
      });
      meta.textContent = shown + SiteI18n.ta(' مركز تدريبي');
    } catch (_) { meta.textContent = SiteI18n.ta('تعذر التحميل'); }
  }

  /* ══ المتدربون (على مواقع المراكز / المحافظة) ══ */
  async function loadTrainees() {
    setLoading(true);
    try {
      var res = await window.APP_API.get(
        window.APP_ROUTES.mapTrainees
          ? window.APP_ROUTES.mapTrainees({ limit: 500 })
          : (window.APP_CONFIG.API_BASE_URL + '/map/trainees?limit=500')
      );
      var list = res.data || [];
      markersLayer.clearLayers();
      var shown = 0;
      list.forEach(function (t) {
        var lat = t.latitude != null ? parseFloat(t.latitude) : NaN;
        var lng = t.longitude != null ? parseFloat(t.longitude) : NaN;
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        if (!BOUNDS.contains([lat, lng])) return;
        var icon = L.divIcon({
          className: '', iconSize: [12, 12], iconAnchor: [6, 6],
          html: '<div style="width:12px;height:12px;border-radius:50%;background:#9333ea;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>',
        });
        L.marker([lat, lng], { icon: icon })
          .bindPopup('<div class="need-popup"><p class="np-title">' + _e(t.name || '—') + '</p>'
            + (t.trainee_code ? '<div class="np-row"><i class="bi bi-hash"></i>' + _e(t.trainee_code) + '</div>' : '')
            + '<div class="np-row"><i class="bi bi-geo-alt-fill" style="color:#dc2626"></i>' + _e(govDisplay(t.governorate || t.city)) + '</div>'
            + (t.address ? '<div class="np-row"><i class="bi bi-building"></i>' + _e(t.address) + '</div>' : '')
            + '</div>', { maxWidth: 220 })
          .addTo(markersLayer);
        shown++;
      });
      meta.textContent = shown + SiteI18n.ta(' متدرب');
    } catch (_) { meta.textContent = SiteI18n.ta('تعذر التحميل'); }
  }

  /* ══ تشغيل ══ */
  var allowedModes = window.NeedsPlatform ? window.NeedsPlatform.allowedMapModes() : ['needs'];
  document.querySelectorAll('.map-mode-btn').forEach(function (btn) {
    if (!allowedModes.includes(btn.dataset.mode)) {
      btn.parentElement.classList.add('d-none');
    } else {
      btn.addEventListener('click', function () { switchMode(btn.dataset.mode); });
    }
  });

  document.getElementById('mapReloadBtn').addEventListener('click', loadMode);
  await loadGeoLayers();
  syncDistrictLayer();
  await loadGovernorates();
  await loadLookups();
  await loadMode();

  setTimeout(function () { map.invalidateSize(); }, 250);

  function refreshMapSize() {
    try {
      map.invalidateSize({ animate: false });
    } catch (e) { /* ignore */ }
  }

  window.__needsMapInstance = map;
  window.addEventListener('resize', function () {
    clearTimeout(window.__needsMapResizeT);
    window.__needsMapResizeT = setTimeout(refreshMapSize, 120);
  });

  ['dsSidebar', 'appSidebar'].forEach(function (id) {
    var el = document.getElementById(id);
    if (!el || !window.MutationObserver) return;
    new MutationObserver(function () {
      setTimeout(refreshMapSize, 50);
      setTimeout(refreshMapSize, 280);
    }).observe(el, { attributes: true, attributeFilter: ['class', 'style'] });
  });

  var guestBar = document.getElementById('guestCtaBar');
  if (guestBar) {
    var syncGuestClass = function () {
      var visible = guestBar.style.display !== 'none' && window.getComputedStyle(guestBar).display !== 'none';
      document.body.classList.toggle('has-guest-cta', visible);
      setTimeout(refreshMapSize, 60);
    };
    syncGuestClass();
    if (window.MutationObserver) {
      new MutationObserver(syncGuestClass).observe(guestBar, { attributes: true, attributeFilter: ['style', 'class'] });
    }
  }

  // بعد انتهاء رسم القوقعة (ds/app) أعد قياس الخريطة
  setTimeout(refreshMapSize, 600);
});
