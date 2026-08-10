<?php /* قوقعة لوحة التحكم الموحّدة — أنماط CSS. مصدرها الوحيد dashboard shell. */ ?>
  <style>
    :root {
      --c-primary: #17947B;
      --c-primary-dark: #0F5F4F;
      --c-accent: #06AA89;
      --c-soft: #EAF8F4;
      --c-border: rgba(23,148,123,.13);
      --c-text: #16332E;
      --c-muted: #6B7280;
      --c-white: #ffffff;
      --c-shadow: 0 16px 40px rgba(15,79,71,.09);
      --c-shadow-sm: 0 8px 20px rgba(15,79,71,.06);
      --sidebar-w: 300px;
      --topbar-h: 64px;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      background: linear-gradient(160deg, #f4fbf8 0%, #eef9f5 100%);
      font-family: 'Segoe UI', Tahoma, sans-serif;
      margin: 0;
    }

    /* ─── TOPBAR ─── */
    .app-topbar {
      position: fixed;
      top: 0; right: 0; left: 0;
      height: var(--topbar-h);
      background: var(--c-white);
      border-bottom: 1px solid var(--c-border);
      box-shadow: var(--c-shadow-sm);
      display: flex;
      align-items: center;
      padding: 0 20px;
      gap: 14px;
      z-index: 1040;
    }

    .app-topbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 800;
      color: var(--c-text);
      font-size: 1rem;
      flex: 1;
    }

    .app-topbar-brand .brand-badge {
      width: 40px; height: 40px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: .85rem; font-weight: 800;
      box-shadow: 0 8px 18px rgba(23,148,123,.22);
      flex-shrink: 0;
    }

    .app-topbar-user {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .topbar-avatar {
      width: 38px; height: 38px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; font-size: .9rem;
      cursor: pointer;
    }

    .topbar-user-name {
      font-weight: 700;
      font-size: .9rem;
      color: var(--c-text);
      max-width: 140px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .topbar-user-role {
      font-size: .78rem;
      color: var(--c-muted);
      font-weight: 600;
    }

    .btn-sidebar-toggle {
      display: none;
      background: none;
      border: 1px solid var(--c-border);
      border-radius: 10px;
      padding: 6px 10px;
      color: var(--c-text);
      cursor: pointer;
      font-size: 1.15rem;
      align-items: center;
    }

    .btn-logout-top {
      background: none;
      border: 1px solid rgba(220,53,69,.25);
      border-radius: 10px;
      color: #dc3545;
      padding: 6px 12px;
      font-size: .85rem;
      font-weight: 700;
      cursor: pointer;
      display: flex; align-items: center; gap: 6px;
      transition: all .2s;
    }

    .btn-logout-top:hover {
      background: rgba(220,53,69,.07);
    }

    /* ─── LAYOUT SHELL ─── */
    .app-shell {
      display: flex;
      padding-top: var(--topbar-h);
      min-height: 100vh;
    }

    /* ─── SIDEBAR ─── */
    .app-sidebar {
      width: var(--sidebar-w);
      flex-shrink: 0;
      position: fixed;
      top: var(--topbar-h);
      right: 0;
      bottom: 0;
      overflow-y: auto;
      overflow-x: hidden;
      background: var(--c-white);
      border-left: 1px solid var(--c-border);
      padding: 16px 12px 24px;
      transition: transform .28s ease;
      z-index: 1035;
    }

    .app-sidebar::-webkit-scrollbar { width: 4px; }
    .app-sidebar::-webkit-scrollbar-thumb { background: rgba(23,148,123,.18); border-radius: 4px; }

    /* user card inside sidebar */
    .sb-user-card {
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      border-radius: 20px;
      padding: 16px;
      color: #fff;
      margin-bottom: 16px;
      position: relative;
      overflow: hidden;
    }

    .sb-user-card::before {
      content: "";
      position: absolute;
      top: -40px; left: -30px;
      width: 120px; height: 120px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
    }

    .sb-user-top { display: flex; align-items: center; gap: 12px; position: relative; }
    .sb-user-avatar {
      width: 52px; height: 52px;
      border-radius: 16px;
      background: rgba(255,255,255,.9);
      color: var(--c-primary-dark);
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; font-size: 1.2rem;
      flex-shrink: 0;
    }

    .sb-user-name { font-weight: 800; font-size: .97rem; margin: 0 0 4px; }
    .sb-user-role { font-size: .82rem; opacity: .9; margin: 0; }

    .sb-user-meta {
      display: flex;
      gap: 8px;
      margin-top: 12px;
      position: relative;
    }

    .sb-user-meta-item {
      flex: 1;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 12px;
      padding: 8px 10px;
    }

    .sb-user-meta-label { font-size: .72rem; opacity: .8; margin-bottom: 2px; }
    .sb-user-meta-value { font-size: .82rem; font-weight: 700; word-break: break-word; }

    /* nav section */
    .sb-section { margin-bottom: 6px; }

    .sb-section-btn {
      width: 100%;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 11px 12px;
      border: none;
      background: none;
      border-radius: 14px;
      font-weight: 800;
      font-size: .9rem;
      color: var(--c-text);
      cursor: pointer;
      text-align: right;
      transition: background .18s, color .18s;
    }

    .sb-section-btn:hover {
      background: var(--c-soft);
      color: var(--c-primary-dark);
    }

    .sb-section-btn .sb-section-icon {
      width: 34px; height: 34px;
      border-radius: 10px;
      background: linear-gradient(135deg, rgba(23,148,123,.12), rgba(6,170,137,.08));
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem;
      color: var(--c-primary);
      flex-shrink: 0;
    }

    .sb-section-btn .sb-section-label { flex: 1; }

    .sb-section-btn .sb-chevron {
      font-size: .8rem;
      color: var(--c-muted);
      transition: transform .22s;
    }

    .sb-section-btn[aria-expanded="true"] .sb-chevron {
      transform: rotate(90deg);
    }

    .sb-links {
      padding: 4px 0 4px 8px;
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .sb-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 12px;
      border-radius: 12px;
      text-decoration: none;
      color: var(--c-text);
      font-size: .88rem;
      font-weight: 700;
      transition: background .18s, color .18s;
      border: 1px solid transparent;
    }

    .sb-link i {
      font-size: 1rem;
      color: var(--c-primary);
      flex-shrink: 0;
      width: 20px;
      text-align: center;
    }

    .sb-link:hover, .sb-link.active {
      background: var(--c-soft);
      color: var(--c-primary-dark);
      border-color: rgba(23,148,123,.12);
    }

    .sb-link .sb-badge {
      margin-right: auto;
      font-size: .72rem;
      color: var(--c-muted);
      font-weight: 600;
    }

    .sb-divider {
      height: 1px;
      background: var(--c-border);
      margin: 10px 4px;
    }

    /* ─── MAIN CONTENT ─── */
    .app-main {
      flex: 1;
      margin-right: var(--sidebar-w);
      padding: 24px 22px;
      min-width: 0;
    }

    /* ─── ضغط الهيدر التسويقي داخل قوقعة الداشبورد ─── */
    /* صفحات الخدمات تستخدم .services-hero بحشوة كبيرة (72px) تدفع المحتوى للأسفل.
       داخل الداشبورد نجعلها هيدر مضغوط حتى يظهر الجدول/المحتوى مباشرة بلا تمرير. */
    .app-main .services-hero {
      padding-top: 16px !important;
      padding-bottom: 14px !important;
      min-height: 0 !important;
      background: transparent !important;
    }
    .app-main .services-hero .page-intro-card { display: none !important; }
    .app-main .services-hero .section-subtitle { margin-bottom: 0 !important; }
    .app-main .services-hero h1 { font-size: 1.5rem !important; margin-bottom: 6px !important; }
    .app-main section.section { padding-top: 0 !important; padding-bottom: 24px !important; }
    .app-main .breadcrumb-soft { margin-bottom: 10px !important; }

    /* ─── HERO BANNER ─── */
    .dash-banner {
      border-radius: 24px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff;
      padding: 26px 28px;
      margin-bottom: 22px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 18px 42px rgba(15,79,71,.16);
    }

    .dash-banner::before {
      content: "";
      position: absolute;
      width: 220px; height: 220px; border-radius: 50%;
      top: -110px; left: -80px;
      background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
    }

    .dash-banner::after {
      content: "";
      position: absolute;
      width: 160px; height: 160px; border-radius: 50%;
      bottom: -80px; right: -50px;
      background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%);
    }

    .dash-banner > * { position: relative; z-index: 1; }
    .dash-banner-title { font-size: 1.55rem; font-weight: 800; margin: 0 0 6px; }
    .dash-banner-sub { margin: 0; opacity: .92; font-size: .95rem; line-height: 1.8; }

    .dash-banner-icon {
      font-size: 3.5rem;
      opacity: .18;
      position: absolute;
      left: 24px;
      top: 50%;
      transform: translateY(-50%);
    }

    /* ─── STAT CARDS ─── */
    .dash-loading {
      background: var(--c-white);
      border: 1px solid var(--c-border);
      border-radius: 20px;
      padding: 28px;
      text-align: center;
      color: var(--c-muted);
      font-weight: 700;
      margin-bottom: 22px;
      box-shadow: var(--c-shadow-sm);
    }

    #dashboardCards .stat-card {
      background: var(--c-white);
      border: 1px solid var(--c-border);
      border-radius: 22px;
      padding: 22px 18px 20px;
      box-shadow: var(--c-shadow-sm);
      text-decoration: none;
      color: var(--c-text);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      height: 100%;
      transition: transform .22s, box-shadow .22s, border-color .22s;
      position: relative;
      overflow: hidden;
      cursor: pointer;
    }

    #dashboardCards .stat-card::before {
      content: "";
      position: absolute;
      inset: 0 0 auto 0;
      height: 4px;
      background: linear-gradient(90deg, var(--c-primary), var(--c-accent));
    }

    #dashboardCards .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 22px 44px rgba(15,79,71,.12);
      border-color: rgba(23,148,123,.22);
      color: var(--c-text);
    }

    #dashboardCards .stat-card-icon-wrap {
      width: 56px; height: 56px;
      border-radius: 18px;
      background: linear-gradient(135deg, rgba(23,148,123,.12), rgba(6,170,137,.08));
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 14px;
      font-size: 1.5rem;
      color: var(--c-primary);
    }

    #dashboardCards .stat-card-value {
      font-size: 2.1rem;
      font-weight: 800;
      color: var(--c-primary-dark);
      line-height: 1;
      margin-bottom: 8px;
      letter-spacing: -.5px;
    }

    #dashboardCards .stat-card-label {
      font-size: .9rem;
      font-weight: 700;
      color: var(--c-muted);
      line-height: 1.6;
    }

    /* ─── BOTTOM BLOCKS ─── */
    .dash-block {
      background: var(--c-white);
      border: 1px solid var(--c-border);
      border-radius: 22px;
      padding: 22px;
      box-shadow: var(--c-shadow-sm);
      height: 100%;
    }

    .dash-block-title {
      font-size: 1.05rem;
      font-weight: 800;
      color: var(--c-text);
      margin: 0 0 6px;
      display: flex; align-items: center; gap: 8px;
    }

    .dash-block-title i { color: var(--c-primary); font-size: 1.1rem; }
    .dash-block-sub { color: var(--c-muted); font-size: .87rem; margin: 0 0 16px; line-height: 1.7; }

    /* ─── BI / Analytics charts ─── */
    .bi-chart-wrap {
      position: relative;
      width: 100%;
      height: 260px;
      min-height: 220px;
    }
    .bi-chart-wrap.bi-chart-lg { height: 340px; min-height: 280px; }
    .bi-chart-wrap canvas {
      width: 100% !important;
      height: 100% !important;
      max-width: 100%;
    }
    #nationalAnalytics .dash-block { overflow: hidden; }

    .activity-list { display: flex; flex-direction: column; gap: 10px; }

    .activity-item {
      display: flex; align-items: flex-start; gap: 12px;
      padding: 13px 14px;
      border-radius: 16px;
      background: linear-gradient(180deg, #fff 0%, #f7fcfa 100%);
      border: 1px solid rgba(23,148,123,.07);
    }

    .activity-item-icon {
      width: 36px; height: 36px;
      border-radius: 11px;
      background: linear-gradient(135deg, rgba(23,148,123,.13), rgba(6,170,137,.08));
      display: flex; align-items: center; justify-content: center;
      font-size: .95rem;
      color: var(--c-primary);
      flex-shrink: 0;
    }

    .activity-item h4 { font-size: .92rem; font-weight: 800; margin: 0 0 3px; color: var(--c-text); }
    .activity-item p { margin: 0; color: var(--c-muted); font-size: .83rem; line-height: 1.7; }

    /* Quick links grid */
    .quick-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .quick-link {
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 16px 14px;
      border-radius: 16px;
      background: linear-gradient(180deg, #fff 0%, #f7fcfa 100%);
      border: 1px solid rgba(23,148,123,.08);
      text-decoration: none;
      color: var(--c-text);
      transition: all .2s;
    }

    .quick-link:hover {
      transform: translateY(-3px);
      border-color: rgba(23,148,123,.2);
      box-shadow: 0 14px 26px rgba(15,79,71,.09);
      color: var(--c-text);
    }

    .quick-link i {
      font-size: 1.25rem;
      color: var(--c-primary);
    }

    .quick-link h4 { font-size: .9rem; font-weight: 800; margin: 0; }
    .quick-link p { font-size: .79rem; color: var(--c-muted); margin: 0; line-height: 1.6; }

    /* ─── SIDEBAR OVERLAY (mobile) ─── */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.45);
      z-index: 1034;
      backdrop-filter: blur(2px);
      -webkit-tap-highlight-color: transparent;
      cursor: pointer;
      touch-action: manipulation;
    }
    .sidebar-overlay.open {
      display: block;
      pointer-events: auto;
    }

    /* ─── RESPONSIVE ─── */

    /* تابلت وأصغر (≤991): السايدبار يصبح منزلقاً من الجانب عبر زر القائمة */
    @media (max-width: 991.98px) {
      .btn-sidebar-toggle { display: flex; }
      .topbar-user-name, .topbar-user-role { display: none; }

      .app-sidebar {
        transform: translateX(100%);
        box-shadow: -4px 0 24px rgba(0,0,0,.12);
        position: fixed;
        top: var(--topbar-h);
        right: 0;
        bottom: 0;
        height: auto;
        max-height: calc(100dvh - var(--topbar-h));
        -webkit-overflow-scrolling: touch;
      }
      .app-sidebar.open { transform: translateX(0); }
      .sidebar-overlay.open { display: block; }

      .app-main { margin-right: 0; padding: 18px 16px; }
      .dash-banner { padding: 22px 20px; }
      .dash-banner-icon { display: none; }
    }

    /* موبايل (≤767): تبسيط وتوضيح — لا شيء متداخل أو مبتور */
    @media (max-width: 767.98px) {
      .app-topbar { padding: 0 12px; gap: 10px; }
      /* إخفاء العنوان الفرعي للعلامة حتى لا يتزاحم مع الأفاتار والخروج */
      .app-topbar-brand > div:nth-child(2) > div:nth-child(2) { display: none; }
      .app-topbar-brand { font-size: .95rem; }

      .app-main { padding: 14px 12px; }
      .dash-banner-title { font-size: 1.3rem; }
      .dash-banner-sub { font-size: .86rem; }

      .bi-chart-wrap { height: 240px; min-height: 200px; }
      .bi-chart-wrap.bi-chart-lg { height: 280px; min-height: 240px; }
      #nationalAnalytics .dash-block { padding: 16px 14px; }

      /* الجداول العريضة: تمرير أفقي سلس بدل كسر الصفحة */
      .table-responsive { -webkit-overflow-scrolling: touch; }
      .table-responsive > .table { min-width: 640px; }

      /* لمس أوضح لأزرار الإجراءات داخل الجداول والبطاقات */
      .btn-sm { padding: .4rem .7rem; }
      .d-flex.flex-wrap.gap-2 > .btn { flex: 1 1 auto; }
    }

    /* موبايل صغير (≤575): تكبير قابلية القراءة واللمس */
    @media (max-width: 575.98px) {
      :root { --sidebar-w: 86vw; }
      .quick-grid { grid-template-columns: 1fr; }

      #dashboardCards .stat-card { padding: 18px 12px 16px; }
      #dashboardCards .stat-card-value { font-size: 1.7rem; }
      #dashboardCards .stat-card-icon-wrap { width: 46px; height: 46px; font-size: 1.2rem; margin-bottom: 10px; }
      #dashboardCards .stat-card-label { font-size: .82rem; }
    }
  </style>
