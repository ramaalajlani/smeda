<?php
$basePath = '../../';
$activePage = 'needs-map';
$pageTitle = 'خريطة الاحتياجات';
$embed = isset($_GET['embed']); // وضع التضمين داخل داشبورد (يخفي الهيدر وشريط الزوار)
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <style>
    /* ══ تخطيط الصفحة — ملء الشاشة (قوقعة المنصة أو الداشبورد) ══ */
    html, body { height: 100%; overflow: hidden; }
    :root { --map-topbar-h: 58px; }

    .app-main,
    .ds-layout .ds-content.app-main,
    .ds-layout .ds-content {
      display: flex !important;
      flex-direction: column !important;
      height: calc(100dvh - var(--topbar-h, var(--map-topbar-h))) !important;
      max-height: calc(100dvh - var(--topbar-h, var(--map-topbar-h))) !important;
      padding: 0 !important;
      margin: 0 !important;
      max-width: none !important;
      width: 100% !important;
      overflow: hidden !important;
      min-height: 0 !important;
      box-sizing: border-box;
    }

    @media (min-width: 992px) {
      body:not(:has(.ds-layout)) .app-main {
        margin-right: var(--sidebar-w, 300px) !important;
        width: auto !important;
      }
    }

    .ds-layout {
      height: 100dvh;
      max-height: 100dvh;
      overflow: hidden;
      --topbar-h: var(--map-topbar-h);
      --sidebar-w: 260px;
    }
    .ds-layout .ds-main {
      min-height: 0;
      height: 100dvh;
      max-height: 100dvh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .ds-layout .ds-topbar {
      flex-shrink: 0;
      height: var(--map-topbar-h);
    }

    .map-section { flex: 1 1 auto; min-height: 0; padding: 0 !important; position: relative; width: 100%; }
    .map-wrapper  { position: absolute; inset: 0; width: 100%; height: 100%; }
    #needsMap     { position: absolute; inset: 0; width: 100%; height: 100%; }

    .map-topbar {
      position: absolute; top: 12px; left: 50%; transform: translateX(-50%);
      z-index: 1000;
      display: flex; align-items: center; gap: 4px;
      background: rgba(255,255,255,.97);
      backdrop-filter: blur(8px);
      border: 1px solid #e5e7eb;
      border-radius: 40px;
      padding: 5px 8px;
      box-shadow: 0 4px 16px rgba(0,0,0,.13);
      white-space: nowrap;
      max-width: min(720px, calc(100% - 24px));
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none;
    }
    .map-topbar::-webkit-scrollbar { display: none; width: 0; height: 0; }

    .map-mode-btn {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 8px 13px; border-radius: 30px; font-size: 12px; font-weight: 600;
      border: none; background: transparent; color: #6b7280;
      cursor: pointer; transition: all .18s; white-space: nowrap; flex-shrink: 0;
      min-height: 36px; touch-action: manipulation;
    }
    .map-mode-btn:hover { background: #f1f5f9; color: #1e293b; }
    .map-mode-btn.active { background: var(--brand-color,#3b82f6); color: #fff; }
    .map-topbar-sep { width: 1px; height: 20px; background: #e5e7eb; flex-shrink: 0; }

    .map-filter-toggle {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 8px 13px; border-radius: 30px; font-size: 12px; font-weight: 600;
      border: none; background: transparent; color: #6b7280;
      cursor: pointer; transition: all .18s; flex-shrink: 0;
      min-height: 36px; touch-action: manipulation;
    }
    .map-filter-toggle:hover { background: #f1f5f9; color: #1e293b; }
    .map-filter-toggle.has-filters { color: var(--brand-color,#3b82f6); }
    .filter-count {
      display: none; align-items: center; justify-content: center;
      width: 16px; height: 16px; border-radius: 50%;
      background: var(--brand-color,#3b82f6); color: #fff;
      font-size: 9px; font-weight: 700;
    }

    .map-filter-panel {
      position: absolute; top: 60px; left: 50%; transform: translateX(-50%);
      z-index: 999;
      width: min(480px, calc(100% - 24px));
      max-height: min(70dvh, 420px);
      overflow: auto;
      background: rgba(255,255,255,.98);
      backdrop-filter: blur(10px);
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0,0,0,.14);
      padding: 14px 16px;
      opacity: 0; transform: translateX(-50%) translateY(-8px);
      transition: opacity .2s, transform .2s;
      pointer-events: none;
    }
    .map-filter-panel.open {
      opacity: 1; transform: translateX(-50%) translateY(0);
      pointer-events: auto;
    }
    .filter-label {
      font-size: 10px; color: #9ca3af; margin-bottom: 3px;
      display: block; font-weight: 700; text-transform: uppercase; letter-spacing: .4px;
    }
    .map-filter-panel .form-select {
      font-size: 16px; border-radius: 8px; border-color: #e5e7eb; min-height: 40px;
    }
    .map-filter-panel .form-select:focus {
      border-color: var(--brand-color,#3b82f6);
      box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }

    .map-meta-badge {
      position: absolute; bottom: 36px; left: 10px;
      z-index: 1000;
      font-size: 11px; color: #374151;
      background: rgba(255,255,255,.88);
      backdrop-filter: blur(4px);
      border: 1px solid #e5e7eb; border-radius: 20px;
      padding: 4px 12px; display: flex; align-items: center; gap: 5px;
      box-shadow: 0 1px 5px rgba(0,0,0,.08);
      pointer-events: none;
      max-width: calc(100% - 120px);
    }
    .map-meta-badge .spinner-border { width: 10px; height: 10px; border-width: 2px; }

    .map-add-need-btn {
      position: absolute;
      z-index: 1000;
      bottom: 84px;
      left: 12px;
      right: auto;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 10px 14px;
      min-height: 44px;
      border-radius: 999px;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
      font-weight: 700;
      font-size: 12px;
      touch-action: manipulation;
    }

    .map-legend-wrap {
      position: absolute; bottom: 36px; right: 10px;
      z-index: 1000;
    }
    .legend-toggle {
      display: flex; align-items: center; gap: 5px;
      background: rgba(255,255,255,.88); backdrop-filter: blur(4px);
      border: 1px solid #e5e7eb; border-radius: 20px;
      padding: 8px 12px; font-size: 11px; font-weight: 600; color: #374151;
      cursor: pointer; box-shadow: 0 1px 5px rgba(0,0,0,.08);
      min-height: 36px; touch-action: manipulation;
    }
    .map-legend {
      position: absolute; bottom: calc(100% + 6px); right: 0;
      background: rgba(255,255,255,.97); backdrop-filter: blur(6px);
      border: 1px solid #e5e7eb; border-radius: 12px;
      padding: 10px 14px; display: flex; flex-wrap: wrap; gap: 4px 12px;
      font-size: 11px; box-shadow: 0 4px 14px rgba(0,0,0,.1);
      min-width: 160px; max-width: min(280px, calc(100vw - 24px));
      opacity: 0; transform: translateY(4px);
      transition: opacity .18s, transform .18s;
      pointer-events: none;
    }
    .map-legend.open { opacity: 1; transform: translateY(0); pointer-events: auto; }
    .lg-label { width: 100%; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
    .legend-item { display: flex; align-items: center; gap: 5px; white-space: nowrap; color: #374151; }
    .legend-dot { border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(255,255,255,.8), 0 1px 3px rgba(0,0,0,.2); }
    .ld-urgent { width: 16px; height: 16px; background: #dc2626; }
    .ld-high   { width: 13px; height: 13px; background: #f97316; }
    .ld-medium { width: 10px; height: 10px; background: #3b82f6; }
    .ld-low    { width:  8px; height:  8px; background: #94a3b8; }

    .leaflet-control-zoom { margin-bottom: 8px !important; margin-left: 10px !important; }
    .leaflet-bottom.leaflet-left { z-index: 900; }
    .leaflet-container { font: inherit; width: 100% !important; height: 100% !important; }

    .gov-label {
      background: transparent; border: none;
      font-size: 11px; font-weight: 700; color: #1e3a5f;
      text-shadow: 0 1px 2px #fff, 0 -1px 2px #fff, 1px 0 2px #fff, -1px 0 2px #fff;
      white-space: nowrap; pointer-events: none; text-align: center;
    }
    .district-tooltip {
      font-size: 11px; color: #374151;
      background: rgba(255,255,255,.95); border: 1px solid #d1d5db;
      border-radius: 5px; padding: 2px 8px; box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }

    .need-popup { min-width: 210px; max-width: min(280px, calc(100vw - 48px)); font-family: inherit; direction: rtl; }
    .need-popup .np-title { font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 8px; line-height: 1.5; }
    .need-popup .np-row { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #4b5563; margin-bottom: 5px; }
    .np-badge { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .np-pri-عاجلة  { background: #fee2e2; color: #b91c1c; }
    .np-pri-عالية  { background: #ffedd5; color: #c2410c; }
    .np-pri-متوسطة { background: #dbeafe; color: #1d4ed8; }
    .np-pri-منخفضة { background: #f1f5f9; color: #475569; }
    .np-sector-tag { background: #f0f9ff; color: #0369a1; border-radius: 20px; padding: 2px 8px; font-size: 10px; font-weight: 600; }
    .np-divider { border: none; border-top: 1px solid #f3f4f6; margin: 6px 0; }

    @keyframes pulse-urgent {
      0%   { transform: scale(1); opacity: .6; }
      70%  { transform: scale(2.2); opacity: 0; }
      100% { transform: scale(1); opacity: 0; }
    }
    .pulse-ring {
      position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
      width: 22px; height: 22px; border-radius: 50%;
      background: rgba(220,38,38,.35);
      animation: pulse-urgent 1.8s ease-out infinite; pointer-events: none;
    }

    @keyframes need-hl-pulse-kf {
      0%   { transform: translate(-50%,-50%) scale(.6); opacity: 1; box-shadow: 0 0 0 0 rgba(23,148,123,.55); }
      70%  { transform: translate(-50%,-50%) scale(1);  opacity: .35; box-shadow: 0 0 0 22px rgba(23,148,123,0); }
      100% { transform: translate(-50%,-50%) scale(.6); opacity: 1; box-shadow: 0 0 0 0 rgba(23,148,123,0); }
    }
    .need-hl-icon { pointer-events: none; }
    .need-hl-pulse {
      position: absolute; top: 50%; left: 50%;
      width: 26px; height: 26px; border-radius: 50%;
      background: rgba(23,148,123,.35); border: 3px solid #17947B;
      animation: need-hl-pulse-kf 1.4s ease-out infinite; pointer-events: none;
    }

    .guest-cta-bar {
      position: fixed; bottom: 0; inset-inline: 0; z-index: 2000;
      background: linear-gradient(135deg, #062824 0%, #0f4f47 100%);
      border-top: 1px solid rgba(255,255,255,.12);
      padding: 14px 20px;
      padding-bottom: max(14px, env(safe-area-inset-bottom));
      display: flex; align-items: center; justify-content: space-between; gap: 16px;
      box-shadow: 0 -8px 32px rgba(6,40,36,.35);
      backdrop-filter: blur(8px);
    }
    .guest-cta-bar .cta-text { color: rgba(255,255,255,.85); font-size: .88rem; font-weight: 600; }
    .guest-cta-bar .cta-text strong { color: #fff; font-weight: 800; display: block; font-size: .95rem; }
    .guest-cta-bar .cta-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
    .cta-btn-register {
      display: inline-flex; align-items: center; gap: 6px;
      background: linear-gradient(135deg,#17947B,#06AA89);
      color: #fff; font-weight: 800; font-size: .875rem;
      padding: 9px 20px; border-radius: 10px; text-decoration: none;
      box-shadow: 0 4px 16px rgba(23,148,123,.35);
      transition: opacity .2s, transform .2s;
      white-space: nowrap;
    }
    .cta-btn-register:hover { opacity: .88; transform: translateY(-1px); color: #fff; }
    .cta-btn-login {
      display: inline-flex; align-items: center; gap: 6px;
      color: rgba(255,255,255,.8); font-weight: 700; font-size: .85rem;
      padding: 9px 16px; border-radius: 10px; text-decoration: none;
      border: 1.5px solid rgba(255,255,255,.2);
      transition: background .2s, color .2s;
      white-space: nowrap;
    }
    .cta-btn-login:hover { background: rgba(255,255,255,.1); color: #fff; }
    .cta-dismiss {
      background: none; border: none; color: rgba(255,255,255,.4);
      font-size: 1.1rem; cursor: pointer; padding: 4px; line-height: 1;
      transition: color .2s; flex-shrink: 0;
    }
    .cta-dismiss:hover { color: rgba(255,255,255,.8); }

    @media (max-width: 1023px) {
      .ds-layout .ds-main { margin-right: 0 !important; }
    }

    @media (max-width: 767px) {
      .ds-layout .ds-topbar { height: 54px; }
      .ds-layout { --map-topbar-h: 54px; --topbar-h: 54px; }
      .ds-topbar-title { font-size: .85rem; }
      .ds-topbar-user span { display: none; }

      .map-topbar {
        top: 8px;
        max-width: calc(100% - 16px);
        border-radius: 28px;
        padding: 4px 6px;
        gap: 2px;
      }
      .map-mode-btn { padding: 8px 10px; font-size: 11px; }
      .map-mode-btn span.mode-label,
      .map-filter-toggle span.mode-label { display: none; }
      .map-filter-panel { top: 52px; width: calc(100% - 16px); padding: 12px; }

      .map-meta-badge {
        bottom: auto;
        top: 52px;
        left: 8px;
        right: auto;
        font-size: 10px;
        max-width: calc(100% - 24px);
      }

      .map-add-need-btn {
        bottom: max(96px, calc(env(safe-area-inset-bottom) + 88px));
        left: 12px;
        width: 52px;
        height: 52px;
        padding: 0;
        border-radius: 50%;
        font-size: 1.25rem;
      }
      .map-add-need-btn span { display: none; }

      .map-legend-wrap {
        bottom: max(20px, env(safe-area-inset-bottom));
        right: 8px;
      }

      .leaflet-control-zoom {
        margin-bottom: 72px !important;
        margin-left: 12px !important;
      }

      .guest-cta-bar {
        flex-wrap: wrap;
        padding: 12px 14px;
        padding-bottom: max(12px, env(safe-area-inset-bottom));
        gap: 10px;
      }
      .guest-cta-bar .cta-text { font-size: .8rem; flex: 1 1 100%; }
      .guest-cta-bar .cta-actions { width: 100%; }
      .cta-btn-register, .cta-btn-login { flex: 1; justify-content: center; min-height: 44px; }
      body.has-guest-cta .map-add-need-btn { bottom: max(150px, calc(env(safe-area-inset-bottom) + 140px)); }
      body.has-guest-cta .map-legend-wrap { bottom: max(78px, calc(env(safe-area-inset-bottom) + 70px)); }
      body.has-guest-cta .leaflet-control-zoom { margin-bottom: 130px !important; }
    }

    @media (max-width: 399px) {
      .map-topbar { gap: 0; }
      .map-mode-btn { padding: 8px 9px; }
      .need-popup { min-width: 0; width: calc(100vw - 40px); }
    }

    .map-filter-hint {
      margin-top: 10px;
      font-size: 11px;
      color: #6b7280;
      font-weight: 600;
      text-align: center;
    }
    .map-filter-panel .form-control {
      font-size: 16px; border-radius: 8px; border-color: #e5e7eb; min-height: 40px;
    }
    @media (min-width: 768px) {
      .map-filter-panel { width: min(560px, calc(100% - 24px)); }
    }

    .gov-needs-modal {
      position: fixed; inset: 0; z-index: 3000;
      display: none; align-items: flex-end; justify-content: center;
      background: rgba(15, 23, 42, .45);
      backdrop-filter: blur(3px);
      padding: 12px;
      padding-bottom: max(12px, env(safe-area-inset-bottom));
    }
    .gov-needs-modal.open { display: flex; }
    .gov-needs-sheet {
      width: min(720px, 100%);
      max-height: min(82dvh, 720px);
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 20px 50px rgba(0,0,0,.25);
      display: flex; flex-direction: column;
      overflow: hidden;
      animation: govSheetIn .22s ease-out;
    }
    @keyframes govSheetIn {
      from { transform: translateY(24px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .gov-needs-hd {
      display: flex; align-items: flex-start; gap: 10px;
      padding: 14px 16px; border-bottom: 1px solid #e5e7eb;
      background: linear-gradient(135deg, #062824, #0f4f47);
      color: #fff;
    }
    .gov-needs-hd h3 { margin: 0; font-size: 1.05rem; font-weight: 800; flex: 1; line-height: 1.35; }
    .gov-needs-hd p { margin: 4px 0 0; font-size: .8rem; opacity: .85; }
    .gov-needs-close {
      width: 40px; height: 40px; border: none; border-radius: 10px;
      background: rgba(255,255,255,.15); color: #fff; cursor: pointer;
      display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }
    .gov-needs-body { flex: 1; overflow: auto; padding: 12px 14px; -webkit-overflow-scrolling: touch; }
    .gov-need-card {
      border: 1px solid #e5e7eb; border-radius: 14px; padding: 12px 14px;
      margin-bottom: 10px; background: #fafafa;
    }
    .gov-need-card:hover { background: #fff; border-color: #17947B; }
    .gov-need-title { font-weight: 800; font-size: .92rem; color: #16332E; margin: 0 0 6px; }
    .gov-need-meta { display: flex; flex-wrap: wrap; gap: 6px 10px; font-size: .78rem; color: #6b7280; font-weight: 600; }
    .gov-need-actions { margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap; }
    .gov-need-actions a, .gov-need-actions button {
      min-height: 36px; border-radius: 10px; font-size: .8rem; font-weight: 700;
      padding: 6px 12px; text-decoration: none; border: none; cursor: pointer;
      display: inline-flex; align-items: center; gap: 5px;
    }
    .gov-btn-view { background: #17947B; color: #fff; }
    .gov-btn-map { background: #e0f2fe; color: #0369a1; }
    .gov-needs-empty, .gov-needs-loading {
      text-align: center; padding: 36px 16px; color: #6b7280; font-weight: 600;
    }
    .gov-needs-ft {
      padding: 10px 14px; border-top: 1px solid #e5e7eb;
      display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;
      background: #f8fafc;
    }
    .gov-needs-ft .btn { min-height: 40px; font-weight: 700; font-size: .84rem; border-radius: 10px; }
    @media (min-width: 768px) {
      .gov-needs-modal { align-items: center; }
    }
  </style>
  <?php if ($embed): ?>
  <style>
    /* وضع التضمين: لا يوجد هيدر، الخريطة تملأ الإطار بالكامل */
    .app-main, .ds-layout .ds-content.app-main, main {
      height: 100dvh !important;
      max-height: 100dvh !important;
    }
  </style>
  <?php endif; ?>
</head>
<body class="needs-map-page">
<?php if (!$embed): ?>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>
<?php endif; ?>

  <section class="map-section">
    <div class="map-wrapper">

      <!-- الخريطة -->
      <div id="needsMap"></div>

      <!-- شريط التحكم العلوي المركزي -->
      <div class="map-topbar" id="mapModesBar">
        <button class="map-mode-btn active" data-mode="needs">
          <i class="bi bi-clipboard-data"></i><span class="mode-label"> الاحتياجات</span>
        </button>
        <button class="map-mode-btn" data-mode="entrepreneurs">
          <i class="bi bi-person-workspace"></i><span class="mode-label"> رواد الأعمال</span>
        </button>
        <button class="map-mode-btn" data-mode="centers">
          <i class="bi bi-building"></i><span class="mode-label"> المراكز التدريبية</span>
        </button>
        <button class="map-mode-btn" data-mode="trainees">
          <i class="bi bi-mortarboard"></i><span class="mode-label"> المتدربون</span>
        </button>
        <span class="map-topbar-sep"></span>
        <button class="map-filter-toggle" id="filterToggleBtn" type="button">
          <i class="bi bi-sliders"></i><span class="mode-label"> فلترة</span>
          <span class="filter-count" id="filterCountBadge">0</span>
        </button>
      </div>

      <!-- Panel الفلاتر (مطوي) -->
      <div class="map-filter-panel" id="needsFiltersBar">
        <div class="row g-2">
          <div class="col-12">
            <label class="filter-label">بحث</label>
            <input type="search" id="mapSearchFilter" class="form-control form-control-sm" placeholder="رمز، عنوان، قطاع..." autocomplete="off">
          </div>
          <div class="col-6">
            <label class="filter-label">تصنيف الاحتياج</label>
            <select id="mapCategoryFilter" class="form-select form-select-sm">
              <option value="">كل التصنيفات</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">نوع المنشأة</label>
            <select id="mapFacilityFilter" class="form-select form-select-sm">
              <option value="">كل الأنواع</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">نوع الحاضنة</label>
            <select id="mapSubtypeFilter" class="form-select form-select-sm">
              <option value="">كل أنواع الحاضنات</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">نوع الاستهداف</label>
            <select id="mapTargetingFilter" class="form-select form-select-sm">
              <option value="">كل أنواع الاستهداف</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">المحافظة</label>
            <select id="mapGovFilter" class="form-select form-select-sm">
              <option value="">كل المحافظات</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">المنطقة</label>
            <select id="mapDistrictFilter" class="form-select form-select-sm" disabled>
              <option value="">كل المناطق</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">القطاع</label>
            <select id="mapSectorFilter" class="form-select form-select-sm">
              <option value="">كل القطاعات</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">الأولوية</label>
            <select id="mapPriorityFilter" class="form-select form-select-sm">
              <option value="">كل الأولويات</option>
              <option value="عاجلة">عاجلة</option>
              <option value="عالية">عالية</option>
              <option value="متوسطة">متوسطة</option>
              <option value="منخفضة">منخفضة</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">الحالة</label>
            <select id="mapStatusFilter" class="form-select form-select-sm">
              <option value="">كل الحالات</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">نوع صاحب الاحتياج</label>
            <select id="mapOwnerFilter" class="form-select form-select-sm">
              <option value="">الكل</option>
              <option value="citizen">مواطن / رائد</option>
              <option value="state">دولة / جهة</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">نطاق الاحتياج</label>
            <select id="mapScopeFilter" class="form-select form-select-sm">
              <option value="">الكل</option>
              <option value="individual">فردي</option>
              <option value="project">مشروع</option>
              <option value="local">محلي</option>
              <option value="governorate">محافظة</option>
              <option value="national">وطني</option>
              <option value="sectoral">قطاعي</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">درجة التعقيد</label>
            <select id="mapComplexityFilter" class="form-select form-select-sm">
              <option value="">الكل</option>
              <option value="general">عام</option>
              <option value="specific">محدد</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">نوع الاحتياج</label>
            <select id="mapNeedTypeFilter" class="form-select form-select-sm">
              <option value="">الكل</option>
              <option value="دورة">دورة</option>
              <option value="تدريب">تدريب</option>
              <option value="تمويل">تمويل</option>
              <option value="دراسة">دراسة</option>
              <option value="استشارة">استشارة</option>
              <option value="مشروع">مشروع</option>
              <option value="حاضنة">حاضنة</option>
              <option value="بيت حرفي / إنتاجي">بيت حرفي / إنتاجي</option>
              <option value="تسويق">تسويق</option>
              <option value="دعم فني">دعم فني</option>
              <option value="تجهيزات">تجهيزات</option>
              <option value="بنية تحتية">بنية تحتية</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">مصدر التسجيل</label>
            <select id="mapSourceFilter" class="form-select form-select-sm">
              <option value="">الكل</option>
              <option value="gis">خريطة الاحتياجات</option>
              <option value="finance">التمويل</option>
              <option value="training">التدريب</option>
              <option value="manual">إدخال يدوي</option>
            </select>
          </div>
          <div class="col-6">
            <label class="filter-label">التدخل المقترح</label>
            <select id="mapInterventionFilter" class="form-select form-select-sm">
              <option value="">الكل</option>
              <option value="دراسة">دراسة</option>
              <option value="تدريب">تدريب</option>
              <option value="استشارات">استشارات</option>
              <option value="تمويل">تمويل</option>
              <option value="حاضنة">حاضنة</option>
              <option value="بنية تحتية">بنية تحتية</option>
            </select>
          </div>
          <div class="col-6 d-flex align-items-end">
            <button type="button" id="mapReloadBtn" class="btn btn-brand btn-sm w-100">
              <i class="bi bi-arrow-clockwise me-1"></i>تطبيق الفلاتر
            </button>
          </div>
          <div class="col-6 d-flex align-items-end">
            <button type="button" id="mapClearFiltersBtn" class="btn btn-outline-secondary btn-sm w-100">
              <i class="bi bi-x-circle me-1"></i>مسح الفلاتر
            </button>
          </div>
        </div>
        <div class="map-filter-hint">اضغط على محافظة في الخريطة لعرض احتياجاتها</div>
      </div>

      <!-- Meta أسفل يسار -->
      <div id="needsMapMeta" class="map-meta-badge">جاري التحميل...</div>

      <a
        href="<?php echo $basePath; ?>services/gis/need-create.php"
        id="mapAddNeedBtn"
        class="btn btn-brand map-add-need-btn d-none"
        data-any-permission="needs.create,needs.create_citizen,needs.create_state"
      >
        <i class="bi bi-plus-circle-fill"></i>
        <span>إضافة احتياج</span>
      </a>

      <!-- Legend أسفل يمين -->
      <div class="map-legend-wrap">
        <div class="legend-toggle" id="legendToggleBtn">
          <i class="bi bi-circle-fill" style="font-size:9px;color:#3b82f6"></i> المفتاح
        </div>
        <div class="map-legend" id="mapLegendPanel">
          <span class="lg-label">الأولوية</span>
          <span class="legend-item"><span class="legend-dot ld-urgent"></span>عاجلة</span>
          <span class="legend-item"><span class="legend-dot ld-high"></span>عالية</span>
          <span class="legend-item"><span class="legend-dot ld-medium"></span>متوسطة</span>
          <span class="legend-item"><span class="legend-dot ld-low"></span>منخفضة</span>
        </div>
        <div id="facilityLegend" class="map-legend" style="display:none">
          <span class="lg-label">نوع المنشأة</span>
        </div>
        <div id="sectorLegend" class="map-legend" style="display:none">
          <span class="lg-label">القطاع</span>
        </div>
      </div>

    </div>
  </section>

<!-- مودال احتياجات المحافظة -->
<div class="gov-needs-modal" id="govNeedsModal" aria-hidden="true">
  <div class="gov-needs-sheet" role="dialog" aria-modal="true" aria-labelledby="govNeedsTitle">
    <div class="gov-needs-hd">
      <div style="flex:1;min-width:0">
        <h3 id="govNeedsTitle">احتياجات المحافظة</h3>
        <p id="govNeedsSub">جاري التحميل...</p>
      </div>
      <button type="button" class="gov-needs-close" id="govNeedsClose" aria-label="إغلاق">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="gov-needs-body" id="govNeedsBody">
      <div class="gov-needs-loading"><span class="spinner-border spinner-border-sm"></span> جاري التحميل...</div>
    </div>
    <div class="gov-needs-ft">
      <div id="govNeedsCount" style="font-size:.82rem;font-weight:700;color:#6b7280">—</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="govNeedsPrev" disabled>السابق</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="govNeedsNext" disabled>التالي</button>
        <a href="#" class="btn btn-brand btn-sm" id="govNeedsListLink" style="display:none">كل القائمة</a>
      </div>
    </div>
  </div>
</div>

<!-- CTA للزوار غير المسجلين -->
<?php if (!$embed): ?>
<div class="guest-cta-bar" id="guestCtaBar" style="display:none">
  <div class="cta-text">
    <strong>هل تريد إضافة احتياج مشروعك؟</strong>
    سجّل دخولك للتفاعل مع الخريطة، إضافة الاحتياجات، والتواصل مع الموردين
  </div>
  <div class="cta-actions">
    <a href="<?= $basePath ?>register.php" class="cta-btn-register">
      <i class="bi bi-person-plus-fill"></i> إنشاء حساب مجاناً
    </a>
    <a href="<?= $basePath ?>login.php" class="cta-btn-login">
      <i class="bi bi-box-arrow-in-right"></i> دخول
    </a>
  </div>
  <button class="cta-dismiss" id="guestCtaDismiss" title="إغلاق">
    <i class="bi bi-x-lg"></i>
  </button>
</div>
<?php endif; ?>

<?php if (!$embed): ?>
<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<?php else: ?>
<?php include $basePath . 'includes/layout/scripts.php'; ?>
<?php endif; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/syria-geo-config.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-platform.js?v=2.1"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-map.js?v=6.4" defer></script>
<script>
(function () {
  // فتح/إغلاق panel الفلاتر
  var filterBtn   = document.getElementById('filterToggleBtn');
  var filterPanel = document.getElementById('needsFiltersBar');
  var filterCount = document.getElementById('filterCountBadge');

  filterBtn && filterBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    filterPanel.classList.toggle('open');
    filterBtn.classList.toggle('has-filters', filterPanel.classList.contains('open'));
  });

  // إغلاق عند الضغط خارجه
  document.addEventListener('click', function (e) {
    if (filterPanel && !filterPanel.contains(e.target) && e.target !== filterBtn) {
      filterPanel.classList.remove('open');
    }
  });

  // عدّاد الفلاتر المفعّلة
  function updateFilterCount() {
    var selects = filterPanel ? filterPanel.querySelectorAll('select') : [];
    var active = Array.from(selects).filter(function (s) { return s.value !== ''; }).length;
    var search = document.getElementById('mapSearchFilter');
    if (search && search.value.trim()) active++;
    if (filterCount) {
      filterCount.style.display = active ? 'inline-flex' : 'none';
      filterCount.textContent = active;
    }
    if (filterBtn) filterBtn.classList.toggle('has-filters', active > 0);
  }
  filterPanel && filterPanel.querySelectorAll('select').forEach(function (s) {
    s.addEventListener('change', updateFilterCount);
  });
  var searchEl = document.getElementById('mapSearchFilter');
  if (searchEl) searchEl.addEventListener('input', updateFilterCount);
  var clearFiltersBtn = document.getElementById('mapClearFiltersBtn');
  if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', function () {
    setTimeout(updateFilterCount, 0);
  });

  // فتح/إغلاق Legend
  var legendBtn   = document.getElementById('legendToggleBtn');
  var legendPanel = document.getElementById('mapLegendPanel');
  legendBtn && legendBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    legendPanel && legendPanel.classList.toggle('open');
  });
  document.addEventListener('click', function (e) {
    if (legendPanel && !legendPanel.contains(e.target) && e.target !== legendBtn) {
      legendPanel.classList.remove('open');
    }
  });
})();

// Guest CTA bar
(function () {
  var bar = document.getElementById('guestCtaBar');
  var dismissBtn = document.getElementById('guestCtaDismiss');
  if (!bar) return;

  var dismissed = sessionStorage.getItem('guestCtaDismissed');
  var token = localStorage.getItem('authority_token');
  var loggedIn = !!token;

  if (!loggedIn && !dismissed) {
    setTimeout(function () { bar.style.display = 'flex'; }, 1500);
  }

  dismissBtn && dismissBtn.addEventListener('click', function () {
    bar.style.display = 'none';
    sessionStorage.setItem('guestCtaDismissed', '1');
  });
})();
</script>
</body>
</html>
