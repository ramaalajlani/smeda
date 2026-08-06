<?php
require_once __DIR__ . '/includes/i18n/bootstrap.php';
$basePath = '';
$pageTitle = __('page_title');
$activePage = 'home';
global $siteIsRtl;
$arrowIcon = $siteIsRtl ? 'bi-arrow-left' : 'bi-arrow-right';
?>
<?php include __DIR__ . '/includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/includes/layout/head.php'; ?>
  <link rel="preload" as="image"
        href="<?= $basePath ?>assets/images/hero1.webp"
        media="(min-width: 768px)"
        fetchpriority="high">
  <link rel="preload" as="image"
        href="<?= $basePath ?>assets/images/hero1-mobile.webp"
        media="(max-width: 767px)"
        fetchpriority="high">
  <link rel="preload" as="image"
        href="<?= $basePath ?>assets/images/Logo.png"
        fetchpriority="high">
  <style>
    /* ─── TOKENS ─── */
    :root {
      --c-dark:    #062824;
      --c-deep:    #0F4F47;
      --c-primary: #17947B;
      --c-accent:  #06AA89;
      --c-soft:    #f2fbf8;
      --c-soft2:   #f7fcfa;
      --c-line:    rgba(23,148,123,.14);
      --c-text:    #1a3530;
      --c-body:    #4b5563;
      --c-muted:   #6b7280;
      --shadow-sm: 0 8px 24px rgba(6,40,36,.07);
      --shadow-md: 0 16px 40px rgba(6,40,36,.10);
      --shadow-lg: 0 24px 60px rgba(6,40,36,.13);
    }

    *, *::before, *::after { box-sizing: border-box; }
    body { background: #fff; color: var(--c-text); overflow-x: hidden; }
    .section { padding: 90px 0; position: relative; }

    /* ══════════════════════════════════
       SPLASH SCREEN
    ══════════════════════════════════ */
    #lp-splash {
      position: fixed;
      inset: 0;
      background: var(--c-dark);
      z-index: 9999;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 24px;
      animation: splashFadeOut .55s 1.9s ease-in forwards;
      pointer-events: none;
    }
    #lp-splash.hidden { display: none; }

    .splash-logo-wrap {
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 36px;
      animation: splashLogoIn .8s .15s cubic-bezier(.22,.68,0,1.2) both;
      box-shadow:
        0 0 0 8px rgba(255,255,255,.12),
        0 0 0 16px rgba(255,255,255,.06),
        0 24px 80px rgba(0,0,0,.45);
    }
    .splash-logo {
      width: 100%;
      height: auto;
    }
    .splash-name {
      color: #fff;
      font-size: 1.15rem;
      font-weight: 800;
      letter-spacing: .5px;
      text-align: center;
      animation: splashLogoIn .8s .38s ease-out both;
      opacity: 0;
    }
    .splash-name span {
      display: block;
      font-size: .8rem;
      font-weight: 500;
      color: rgba(255,255,255,.55);
      margin-top: 4px;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .splash-bar {
      width: 60px;
      height: 3px;
      border-radius: 2px;
      background: linear-gradient(90deg, var(--c-primary), var(--c-accent));
      animation: splashBarIn .7s .6s ease-out both;
      transform-origin: right;
    }

    @keyframes splashLogoIn {
      from { opacity: 0; transform: scale(.78) translateY(12px); }
      to   { opacity: 1; transform: scale(1)  translateY(0); }
    }
    @keyframes splashBarIn {
      from { opacity: 0; transform: scaleX(0); }
      to   { opacity: 1; transform: scaleX(1); }
    }
    @keyframes splashFadeOut {
      to { opacity: 0; visibility: hidden; }
    }

    /* ─── ANIMATION REVEAL ─── */
    [data-reveal] {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity .65s ease, transform .65s ease;
    }
    [data-reveal].visible { opacity: 1; transform: translateY(0); }
    [data-reveal-delay="1"] { transition-delay: .10s; }
    [data-reveal-delay="2"] { transition-delay: .20s; }
    [data-reveal-delay="3"] { transition-delay: .32s; }
    [data-reveal-delay="4"] { transition-delay: .44s; }
    [data-reveal-delay="5"] { transition-delay: .56s; }

    /* ─── SECTION HEADING ─── */
    .sec-badge {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 7px 16px; border-radius: 999px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; font-size: .88rem; font-weight: 800;
      margin-bottom: 16px;
      box-shadow: 0 10px 24px rgba(6,170,137,.22);
    }
    .sec-title {
      font-size: clamp(1.9rem, 3vw, 2.8rem);
      font-weight: 900; color: var(--c-dark);
      letter-spacing: -.3px; line-height: 1.15; margin-bottom: 14px;
    }
    .sec-sub {
      color: var(--c-body); font-size: 1.05rem;
      line-height: 2; max-width: 800px;
    }

    /* ══════════════════════════════════
       HERO — SSF STYLE
    ══════════════════════════════════ */
    .lp-hero {
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      background: var(--c-dark) url("<?= $basePath ?>assets/images/hero1-mobile.webp") center center / cover no-repeat;
      overflow: hidden;
    }
    @media (min-width: 768px) {
      .lp-hero { background-image: url("<?= $basePath ?>assets/images/hero1.webp"); }
    }

    /* Overlay — dark + directional gradient from text side */
    .lp-hero::before {
      content: "";
      position: absolute; inset: 0; z-index: 1;
      background:
        linear-gradient(to left,
          rgba(6,40,36,.92) 0%,
          rgba(6,40,36,.75) 45%,
          rgba(6,40,36,.30) 100%);
    }
    html[dir="ltr"] .lp-hero::before {
      background:
        linear-gradient(to right,
          rgba(6,40,36,.92) 0%,
          rgba(6,40,36,.75) 45%,
          rgba(6,40,36,.30) 100%);
    }
    /* Subtle radial light */
    .lp-hero::after {
      content: "";
      position: absolute; inset: 0; z-index: 2; pointer-events: none;
      background:
        radial-gradient(circle at 80% 50%, rgba(6,170,137,.08), transparent 40%),
        radial-gradient(circle at 20% 80%, rgba(23,148,123,.06), transparent 30%);
    }

    .lp-hero-inner {
      position: relative; z-index: 3; width: 100%;
      padding: 5rem 0;
    }

    /* ─── Logo column (left in RTL = visually left on screen) ─── */
    .hero-seal-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 0;
    }
    .hero-seal {
      width: min(300px, 85vw);
      height: auto;
      opacity: 0;
      animation: sealIn .9s .55s cubic-bezier(.22,.68,0,1.1) forwards;
      filter:
        drop-shadow(0 0 40px rgba(6,170,137,.25))
        drop-shadow(0 0 80px rgba(6,40,36,.6));
    }
    @keyframes sealIn {
      from { opacity: 0; transform: scale(.82) rotate(-4deg); }
      to   { opacity: 1; transform: scale(1)   rotate(0deg); }
    }

    /* ─── Text column (right in RTL = visually right on screen) ─── */
    .hero-text-col {
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: right;
    }
    .lp-hero-tag {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 7px 16px; border-radius: 10px;
      background: rgba(6,170,137,.18);
      color: var(--c-accent);
      border: 1px solid rgba(6,170,137,.30);
      font-weight: 800; font-size: .86rem;
      margin-bottom: 20px; align-self: flex-start;
      opacity: 0; animation: heroUp .6s .15s ease-out forwards;
    }
    .lp-hero h1 {
      font-size: clamp(2.2rem, 4vw, 3.8rem);
      font-weight: 900; color: #fff;
      line-height: 1.12; letter-spacing: -.4px;
      margin-bottom: 18px;
      opacity: 0; animation: heroUp .65s .28s ease-out forwards;
    }
    .lp-hero h1 .accent-word { color: var(--c-accent); }
    .lp-hero-divider {
      width: 56px; height: 4px;
      border-radius: 2px;
      background: linear-gradient(90deg, var(--c-accent), var(--c-primary));
      margin-bottom: 22px;
      opacity: 0; animation: heroUp .6s .38s ease-out forwards;
    }
    .lp-hero-sub {
      font-size: 1.07rem; line-height: 2;
      color: rgba(255,255,255,.88);
      font-weight: 500; max-width: 540px;
      margin-bottom: 34px;
      opacity: 0; animation: heroUp .65s .46s ease-out forwards;
    }
    .lp-hero-actions {
      display: flex; flex-wrap: wrap; gap: 14px;
      opacity: 0; animation: heroUp .65s .58s ease-out forwards;
    }

    .hero-btn-primary {
      height: 54px; padding: 0 30px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; font-weight: 800; font-size: .97rem;
      text-decoration: none;
      display: inline-flex; align-items: center; gap: 9px;
      border: none;
      transition: transform .22s, opacity .22s;
      box-shadow: 0 12px 32px rgba(6,170,137,.30);
    }
    .hero-btn-primary:hover { transform: translateY(-3px); opacity: .88; color: #fff; }

    .hero-btn-outline {
      height: 54px; padding: 0 28px;
      border-radius: 12px;
      background: transparent; color: #fff;
      font-weight: 700; font-size: .97rem;
      text-decoration: none;
      display: inline-flex; align-items: center; gap: 9px;
      border: 1.5px solid rgba(255,255,255,.35);
      transition: background .22s, border-color .22s, transform .22s;
    }
    .hero-btn-outline:hover {
      background: rgba(255,255,255,.12);
      border-color: rgba(255,255,255,.6);
      transform: translateY(-3px); color: #fff;
    }

    /* ─── Stats strip inside hero (below text) ─── */
    .hero-stats-strip {
      display: flex; gap: 0;
      margin-top: 44px;
      border-top: 1px solid rgba(255,255,255,.12);
      padding-top: 28px;
      opacity: 0; animation: heroUp .65s .72s ease-out forwards;
    }
    .hero-stat {
      flex: 1; text-align: center;
      padding: 0 12px;
      border-left: 1px solid rgba(255,255,255,.10);
    }
    .hero-stat:last-child { border-left: none; }
    .hero-stat-num {
      display: block;
      font-size: clamp(1.6rem, 3vw, 2.2rem);
      font-weight: 900; color: var(--c-accent);
      line-height: 1;
    }
    .hero-stat-lbl {
      display: block;
      font-size: .78rem; font-weight: 700;
      color: rgba(255,255,255,.65);
      margin-top: 5px;
    }

    /* Scroll hint */
    .hero-scroll-hint {
      position: absolute; bottom: 28px; left: 50%;
      transform: translateX(-50%); z-index: 4;
      display: flex; flex-direction: column;
      align-items: center; gap: 6px;
      color: rgba(255,255,255,.45);
      font-size: .76rem; font-weight: 600;
      animation: heroUp .65s .9s ease-out forwards, bounce 2s 1.8s ease-in-out 3;
      opacity: 0;
    }
    .hero-scroll-hint i { font-size: 1.2rem; }

    @keyframes heroUp {
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes bounce {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50%       { transform: translateX(-50%) translateY(7px); }
    }

    /* ══════════════════════════════════
       ABOUT DARK
    ══════════════════════════════════ */
    .about-dark {
      background:
        radial-gradient(circle at top right, rgba(6,170,137,.18), transparent 26%),
        radial-gradient(circle at bottom left, rgba(23,148,123,.14), transparent 26%),
        linear-gradient(140deg, var(--c-dark), var(--c-deep));
      color: #fff; overflow: hidden;
    }
    .about-dark .sec-title { color: #fff; }
    .about-dark-text {
      font-size: 1.07rem; line-height: 2.05;
      color: rgba(255,255,255,.9);
      max-width: 860px; margin: 0 auto;
    }
    .about-pillars {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px; margin-top: 44px;
    }
    .about-pillar {
      background: rgba(255,255,255,.07);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 22px; padding: 28px 22px;
      text-align: center;
      transition: background .25s, transform .25s;
    }
    .about-pillar:hover { background: rgba(255,255,255,.12); transform: translateY(-6px); }
    .about-pillar-icon {
      width: 56px; height: 56px; border-radius: 16px;
      background: linear-gradient(135deg, rgba(6,170,137,.30), rgba(23,148,123,.20));
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; color: var(--c-accent); margin: 0 auto 14px;
    }
    .about-pillar h4 { font-weight: 800; font-size: 1rem; margin-bottom: 8px; color: #fff; }
    .about-pillar p  { font-size: .87rem; line-height: 1.85; color: rgba(255,255,255,.75); margin: 0; }

    /* ══════════════════════════════════
       SERVICES GRID — Premium Editorial
    ══════════════════════════════════ */
    .services-section {
      background: linear-gradient(180deg, #f4faf8 0%, #fff 100%);
    }

    .service-card {
      background: #fff;
      border-radius: 22px;
      height: 100%;
      text-decoration: none;
      color: var(--c-text);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border: 1px solid rgba(23,148,123,.10);
      transition: transform .3s cubic-bezier(.22,.68,0,1.15), box-shadow .3s ease;
      box-shadow: 0 2px 12px rgba(6,40,36,.06);
    }
    .service-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 24px 60px rgba(6,40,36,.14);
      color: var(--c-text);
    }

    /* Image area — clear & tall */
    .service-card-img-wrap {
      position: relative;
      overflow: hidden;
      height: 250px;
      flex-shrink: 0;
      background: linear-gradient(135deg, var(--c-deep), var(--c-primary));
    }
    .service-card-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover; object-position: center center;
      display: block;
      transition: transform .55s cubic-bezier(.22,.68,0,1.1);
    }
    .service-card:hover .service-card-img-wrap img { transform: scale(1.07); }

    /* Gradient overlay — darker at bottom for label readability */
    .service-card-img-wrap::before {
      content: "";
      position: absolute; inset: 0;
      background: linear-gradient(
        to top,
        rgba(6,40,36,.82) 0%,
        rgba(6,40,36,.18) 50%,
        transparent 100%
      );
      z-index: 1; pointer-events: none;
    }

    /* Category pill — top right corner */
    .service-cat-pill {
      position: absolute; top: 14px; right: 14px; z-index: 2;
      background: rgba(255,255,255,.18);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.30);
      color: #fff; font-size: .75rem; font-weight: 800;
      padding: 4px 12px; border-radius: 999px;
      letter-spacing: .4px;
    }

    /* Title + icon overlay on image */
    .service-card-label {
      position: absolute; bottom: 0; right: 0; left: 0;
      z-index: 2; padding: 18px 20px 16px;
      display: flex; align-items: center; gap: 12px;
    }
    .service-card-label .svc-icon {
      width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; color: #fff;
      box-shadow: 0 4px 14px rgba(6,170,137,.35);
    }
    .service-card-label h3 {
      font-size: 1.05rem; font-weight: 900;
      color: #fff; margin: 0; line-height: 1.3;
      text-shadow: 0 1px 4px rgba(0,0,0,.3);
    }

    /* Body below image */
    .service-card-body {
      padding: 20px 22px 22px;
      display: flex; flex-direction: column; gap: 14px; flex: 1;
    }
    .service-card-body p {
      font-size: 1rem; color: var(--c-body);
      line-height: 1.9; margin: 0; flex: 1;
    }
    /* Service CTA link — pill with animated arrow */
    .service-link {
      display: inline-flex; align-items: center; gap: 8px;
      font-size: .9rem; font-weight: 800; color: var(--c-primary);
      padding: 9px 18px; border-radius: 50px;
      background: rgba(23,148,123,.08);
      border: 1.5px solid rgba(23,148,123,.18);
      text-decoration: none; align-self: flex-start;
      transition: background .22s, border-color .22s, gap .22s, padding-right .22s;
    }
    .service-link .arrow-icon {
      width: 24px; height: 24px; border-radius: 50%;
      background: var(--c-primary);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: .75rem; flex-shrink: 0;
      transition: transform .25s cubic-bezier(.22,.68,0,1.3);
    }
    .service-card:hover .service-link {
      background: rgba(23,148,123,.13);
      border-color: rgba(23,148,123,.35);
      padding-right: 22px;
    }
    .service-card:hover .service-link .arrow-icon {
      transform: translateX(-4px);
    }

    /* ══════════════════════════════════
       WHO CAN REGISTER — Premium Tiles
    ══════════════════════════════════ */
    .register-section {
      background: linear-gradient(180deg, #fff 0%, #f2fbf8 100%);
    }

    .reg-card {
      background: #fff;
      border-radius: 22px;
      height: 100%;
      border: 1px solid rgba(23,148,123,.10);
      text-align: center;
      overflow: visible; /* ← مهم: يخلي الأيقونة تطلع من الصورة */
      display: flex; flex-direction: column;
      transition: transform .3s cubic-bezier(.22,.68,0,1.15), box-shadow .3s ease;
      box-shadow: 0 2px 12px rgba(6,40,36,.06);
    }
    .reg-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 24px 60px rgba(6,40,36,.14);
    }

    /* Image wrap — overflow hidden للتأثير فقط */
    .reg-card-img-wrap {
      position: relative; overflow: hidden;
      height: 240px; flex-shrink: 0;
      border-radius: 22px 22px 0 0;
      background: linear-gradient(135deg, var(--c-deep), var(--c-primary));
    }
    .reg-card-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover; object-position: center center;
      display: block;
      transition: transform .55s cubic-bezier(.22,.68,0,1.1);
    }
    .reg-card:hover .reg-card-img-wrap img { transform: scale(1.07); }

    .reg-card-img-wrap::before {
      content: "";
      position: absolute; inset: 0;
      background: linear-gradient(
        to top,
        rgba(6,40,36,.60) 0%,
        rgba(6,40,36,.08) 50%,
        transparent 100%
      );
      z-index: 1; pointer-events: none;
    }

    /* Role pill */
    .reg-role-pill {
      position: absolute; top: 14px; left: 14px; z-index: 2;
      background: rgba(255,255,255,.20);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,.35);
      color: #fff; font-size: .73rem; font-weight: 800;
      padding: 5px 13px; border-radius: 999px;
      letter-spacing: .3px;
    }

    /* Floating icon — خارج img-wrap عشان ما يتقطع */
    .reg-icon-wrap {
      display: flex; justify-content: center;
      position: relative; z-index: 4;
      margin-top: -28px; /* يشيله فوق حد الصورة */
      flex-shrink: 0;
    }
    .reg-icon-float {
      width: 58px; height: 58px; border-radius: 18px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      display: flex; align-items: center; justify-content: center;
      font-size: 1.55rem; color: #fff;
      box-shadow: 0 8px 26px rgba(23,148,123,.42);
      border: 3px solid #fff;
      transition: transform .35s cubic-bezier(.22,.68,0,1.3);
    }
    .reg-card:hover .reg-icon-float {
      transform: translateY(-5px) scale(1.08);
    }

    .reg-card-body {
      padding: 18px 24px 26px;
      display: flex; flex-direction: column; align-items: center; flex: 1;
    }
    .reg-card-body h3 {
      font-size: 1.12rem; font-weight: 900;
      color: var(--c-text); margin-bottom: 10px; margin-top: 6px;
    }
    .reg-card-body p {
      font-size: 1rem; color: var(--c-body);
      line-height: 1.9; margin-bottom: 20px; flex: 1;
    }

    /* CTA Button — animated with ripple */
    .reg-btn {
      position: relative; overflow: hidden;
      display: inline-flex; align-items: center; gap: 8px;
      padding: 11px 26px; border-radius: 50px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; font-weight: 800; font-size: .9rem;
      text-decoration: none;
      transition: transform .25s, box-shadow .25s;
      box-shadow: 0 6px 20px rgba(23,148,123,.30);
    }
    .reg-btn::after {
      content: ""; position: absolute; inset: 0;
      background: rgba(255,255,255,0);
      transition: background .2s;
    }
    .reg-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 30px rgba(23,148,123,.40);
      color: #fff;
    }
    .reg-btn:hover::after { background: rgba(255,255,255,.08); }
    .reg-btn:active { transform: translateY(0) scale(.97); }
    .reg-btn .btn-arrow {
      width: 22px; height: 22px; border-radius: 50%;
      background: rgba(255,255,255,.25);
      display: flex; align-items: center; justify-content: center;
      font-size: .72rem; flex-shrink: 0;
      transition: transform .25s cubic-bezier(.22,.68,0,1.3), background .2s;
    }
    .reg-btn:hover .btn-arrow {
      transform: translateX(-4px);
      background: rgba(255,255,255,.35);
    }

    /* Responsive — cards */
    @media (max-width: 991.98px) {
      .service-card-img-wrap { height: 220px; }
      .reg-card-img-wrap { height: 210px; }
    }
    @media (max-width: 767.98px) {
      .service-card-img-wrap { height: 210px; }
      .reg-card-img-wrap { height: 200px; }
    }
    @media (max-width: 575.98px) {
      .service-card-img-wrap { height: 200px; }
      .service-card-label h3 { font-size: .95rem; }
      .reg-card-img-wrap { height: 190px; }
    }

    /* ══════════════════════════════════
       SECTORS SECTION
    ══════════════════════════════════ */
    .sectors-section { background: #fff; }

    .sector-card {
      background: #fff;
      border: 1px solid rgba(23,148,123,.12);
      border-radius: 22px;
      padding: 28px 22px 24px;
      height: 100%;
      transition: transform .28s, box-shadow .28s;
      box-shadow: 0 2px 12px rgba(6,40,36,.05);
    }
    .sector-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 48px rgba(6,40,36,.11);
    }
    .sector-icon {
      width: 56px; height: 56px; border-radius: 16px;
      background: color-mix(in srgb, var(--sc) 14%, #fff);
      border: 1.5px solid color-mix(in srgb, var(--sc) 25%, transparent);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; color: var(--sc);
      margin-bottom: 16px;
    }
    .sector-card h4 { font-size: 1.1rem; font-weight: 900; margin-bottom: 8px; }
    .sector-card > p { font-size: 1rem; color: var(--c-body); line-height: 1.9; margin-bottom: 18px; }
    .sector-criteria { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 7px; }
    .sector-criteria li {
      display: flex; justify-content: space-between; align-items: center;
      font-size: .875rem; padding: 6px 10px; border-radius: 9px;
      background: var(--c-soft2);
    }
    .crit-label { font-weight: 700; color: var(--c-text); }
    .crit-val { font-weight: 800; color: var(--c-primary); font-size: .85rem; }

    /* Classification table */
    .classif-table-wrap {
      background: #fff;
      border: 1px solid rgba(23,148,123,.12);
      border-radius: 22px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(6,40,36,.07);
    }
    .classif-table-head {
      background: linear-gradient(135deg, var(--c-dark), var(--c-deep));
      color: #fff; padding: 18px 24px;
      font-weight: 800; font-size: .95rem;
      display: flex; align-items: center; gap: 10px;
    }
    .classif-table {
      width: 100%; border-collapse: collapse; font-size: 1rem;
    }
    .classif-table thead tr { background: #f8fffe; }
    .classif-table th {
      padding: 14px 18px; font-weight: 800; color: var(--c-text);
      text-align: center; border-bottom: 2px solid var(--c-line);
      white-space: nowrap; font-size: .95rem;
    }
    .classif-table th:first-child { text-align: right; }
    .classif-table td {
      padding: 13px 18px; border-bottom: 1px solid rgba(0,0,0,.05);
      text-align: center; color: #374151; font-weight: 600; font-size: .95rem;
    }
    .classif-table td:first-child { text-align: right; }

    /* فاصل قوي بين كل قطاع والآخر */
    .sector-divider td {
      border-top: 2px solid rgba(0,0,0,.09) !important;
    }
    .classif-table tbody tr:last-child td { border-bottom: none; }

    /* خلية اسم القطاع */
    .sector-name {
      font-weight: 900; font-size: 1rem;
      white-space: nowrap;
      vertical-align: middle;
      padding: 0 20px !important;
    }
    /* ألوان القطاعات — خلفية + بوردر */
    .sector-agri td { background: #f0fdf4; }
    .sector-ind  td { background: #fffbeb; }
    .sector-com  td { background: #faf5ff; }
    .sector-svc  td { background: #f0f9ff; }

    .sector-agri .sector-name {
      color: #16a34a;
      border-right: 5px solid #22c55e;
      background: #dcfce7 !important;
    }
    .sector-ind .sector-name {
      color: #b45309;
      border-right: 5px solid #f59e0b;
      background: #fef3c7 !important;
    }
    .sector-com .sector-name {
      color: #7c3aed;
      border-right: 5px solid #a78bfa;
      background: #ede9fe !important;
    }
    .sector-svc .sector-name {
      color: #0369a1;
      border-right: 5px solid #38bdf8;
      background: #e0f2fe !important;
    }

    /* أرقام القيم ملونة حسب الفئة */
    .val-nano   { color: #16a34a; font-weight: 800; font-size: 1rem; }
    .val-small  { color: #2563eb; font-weight: 800; font-size: 1rem; }
    .val-medium { color: #b45309; font-weight: 800; font-size: 1rem; }

    /* badges الهيدر */
    .badge-nano   { background: #dcfce7; color: #16a34a; padding: 6px 16px; border-radius: 999px; font-size: .875rem; font-weight: 800; display: inline-block; }
    .badge-small  { background: #dbeafe; color: #2563eb; padding: 6px 16px; border-radius: 999px; font-size: .875rem; font-weight: 800; display: inline-block; }
    .badge-medium { background: #fef9c3; color: #b45309; padding: 6px 16px; border-radius: 999px; font-size: .875rem; font-weight: 800; display: inline-block; }

    /* عمود المعيار */
    .crit-lbl { color: #6b7280; font-weight: 600; text-align: right !important; }

    /* ══════════════════════════════════
       DOCS SECTION
    ══════════════════════════════════ */
    .docs-section { background: linear-gradient(180deg, #f4faf8, #fff); }

    .doc-card {
      background: #fff;
      border: 1px solid rgba(23,148,123,.12);
      border-radius: 24px;
      padding: 0;
      height: 100%;
      overflow: hidden;
      transition: transform .3s, box-shadow .3s;
      box-shadow: 0 4px 18px rgba(6,40,36,.07);
      display: flex; flex-direction: column;
    }
    .doc-card:hover { transform: translateY(-6px); box-shadow: 0 22px 55px rgba(6,40,36,.12); }

    .doc-card-icon {
      height: 100px;
      background: linear-gradient(135deg, var(--c-dark) 0%, var(--c-deep) 100%);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.8rem; color: rgba(255,255,255,.85);
      flex-shrink: 0;
    }
    .doc-card--accent .doc-card-icon {
      background: linear-gradient(135deg, #064e3b, var(--c-primary));
    }

    .doc-card-body { padding: 26px; display: flex; flex-direction: column; flex: 1; }
    .doc-badge {
      display: inline-block; font-size: .8125rem; font-weight: 800;
      color: var(--c-primary); background: var(--c-soft);
      border: 1px solid rgba(23,148,123,.18);
      padding: 4px 12px; border-radius: 999px;
      margin-bottom: 14px;
    }
    .doc-card h3 { font-size: 1.1rem; font-weight: 900; margin-bottom: 12px; line-height: 1.5; }
    .doc-card > .doc-card-body > p { font-size: 1rem; color: var(--c-body); line-height: 1.9; margin-bottom: 18px; }
    .doc-highlights {
      list-style: none; padding: 0; margin: 0 0 22px;
      display: flex; flex-direction: column; gap: 8px; flex: 1;
    }
    .doc-highlights li {
      font-size: .9375rem; font-weight: 700; color: var(--c-text);
      display: flex; align-items: center; gap: 9px;
    }
    .doc-highlights li i { color: var(--c-primary); font-size: .9rem; flex-shrink: 0; }
    .doc-read-btn {
      display: inline-flex; align-items: center; gap: 9px;
      padding: 12px 24px; border-radius: 13px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; font-weight: 800; font-size: .9rem;
      text-decoration: none; transition: transform .22s, opacity .22s;
      box-shadow: 0 6px 20px rgba(23,148,123,.28);
      align-self: flex-start;
    }
    .doc-read-btn:hover { transform: translateY(-2px); opacity: .88; color: #fff; }

    /* ══════════════════════════════════
       PARTNERS SLIDER
    ══════════════════════════════════ */
    .partners-section { background: #fff; content-visibility: auto; contain-intrinsic-size: 0 500px; }
    .partners-shell {
      margin-top: 40px; border-radius: 28px;
      background: linear-gradient(180deg, #fff, var(--c-soft2));
      border: 1px solid var(--c-line); box-shadow: var(--shadow-md);
      padding: 28px 0; position: relative; overflow: hidden;
    }
    .partners-shell::before,
    .partners-shell::after {
      content: ""; position: absolute;
      top: 0; height: 100%; width: 100px; z-index: 3; pointer-events: none;
    }
    .partners-shell::before { right: 0; background: linear-gradient(to left, #fff 0%, transparent 100%); }
    .partners-shell::after  { left:  0; background: linear-gradient(to right, #fff 0%, transparent 100%); }
    .logo-slider { width: 100%; overflow: hidden; direction: ltr; }
    .logo-track {
      display: flex; align-items: center; gap: 22px;
      width: max-content;
      animation: logoScroll 38s linear infinite;
      padding: 8px 0; will-change: transform;
    }
    .logo-slider:hover .logo-track { animation-play-state: paused; }
    .logo-item {
      flex: 0 0 auto; width: 190px; height: 110px;
      border-radius: 18px; background: #fff;
      border: 1px solid var(--c-line);
      display: flex; align-items: center; justify-content: center;
      padding: 18px; transition: transform .28s, opacity .28s;
    }
    .logo-item:hover { transform: translateY(-5px); opacity: .82; }
    .logo-item img { max-width: 100%; max-height: 68px; object-fit: contain; }
    @keyframes logoScroll {
      0%   { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }

    /* ══════════════════════════════════
       NEWS
    ══════════════════════════════════ */
    .news-section { background: var(--c-soft); }
    .news-card {
      background: #fff; border-radius: 22px;
      border: 1px solid var(--c-line); box-shadow: var(--shadow-sm);
      overflow: hidden; height: 100%;
      transition: transform .25s;
      text-decoration: none; color: var(--c-text);
      display: flex; flex-direction: column;
    }
    .news-card:hover { transform: translateY(-6px); color: var(--c-text); }
    .news-card-img {
      width: 100%; height: 195px; object-fit: cover;
      background: linear-gradient(135deg, var(--c-deep), var(--c-primary));
      display: flex; align-items: center; justify-content: center;
      color: rgba(255,255,255,.4); font-size: 3rem;
    }
    .news-card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .news-tag {
      display: inline-flex; align-items: center;
      padding: 4px 10px; border-radius: 8px;
      background: var(--c-soft); color: var(--c-primary);
      font-size: .78rem; font-weight: 800; align-self: flex-start;
    }
    .news-card h4 { font-size: .97rem; font-weight: 800; margin: 0; line-height: 1.6; flex: 1; }
    .news-meta { font-size: .8rem; color: var(--c-muted); font-weight: 600; display: flex; align-items: center; gap: 5px; }

    /* ══════════════════════════════════
       GUEST CTA BAND
    ══════════════════════════════════ */
    .guest-cta-band {
      background: linear-gradient(135deg, #062824 0%, #17947B 100%);
      padding: 60px 0;
    }
    .gcta-inner {
      display: flex; align-items: center; gap: 32px;
      flex-wrap: wrap;
    }
    .gcta-icon {
      width: 72px; height: 72px; border-radius: 20px;
      background: rgba(255,255,255,.12);
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; color: #fff; flex-shrink: 0;
    }
    .gcta-text { flex: 1; min-width: 220px; }
    .gcta-text h3 { color: #fff; font-size: 1.35rem; font-weight: 900; margin-bottom: 8px; }
    .gcta-text p  { color: rgba(255,255,255,.78); font-size: .93rem; margin: 0; line-height: 1.7; }
    .gcta-actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; flex-wrap: wrap; }
    .gcta-btn-primary {
      display: inline-flex; align-items: center; gap: 8px;
      background: #fff; color: #062824;
      font-weight: 800; font-size: .9rem;
      padding: 12px 24px; border-radius: 12px; text-decoration: none;
      box-shadow: 0 6px 20px rgba(0,0,0,.18);
      transition: opacity .2s, transform .2s;
      white-space: nowrap;
    }
    .gcta-btn-primary:hover { opacity: .9; transform: translateY(-2px); color: #062824; }
    .gcta-btn-outline {
      display: inline-flex; align-items: center; gap: 8px;
      color: rgba(255,255,255,.85); font-weight: 700; font-size: .9rem;
      padding: 12px 22px; border-radius: 12px; text-decoration: none;
      border: 1.5px solid rgba(255,255,255,.3);
      transition: background .2s, color .2s;
      white-space: nowrap;
    }
    .gcta-btn-outline:hover { background: rgba(255,255,255,.12); color: #fff; }
    @media (max-width: 767px) {
      .gcta-inner { flex-direction: column; text-align: center; }
      .gcta-icon { margin: 0 auto; }
      .gcta-actions { justify-content: center; }
    }

    /* ══════════════════════════════════
       CONTACT
    ══════════════════════════════════ */
    .contact-section {
      background: linear-gradient(135deg, var(--c-dark), var(--c-deep));
      color: #fff;
    }
    .contact-section .sec-title { color: #fff; }
    .contact-info-item {
      display: flex; align-items: flex-start; gap: 14px;
      padding: 20px 22px;
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.10);
      border-radius: 18px; transition: transform .22s;
    }
    .contact-info-item:hover { transform: translateX(-4px); }
    .contact-info-icon {
      width: 44px; height: 44px; border-radius: 13px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; color: #fff; flex-shrink: 0;
      box-shadow: 0 8px 18px rgba(6,170,137,.30);
    }
    .contact-info-label { font-size: .78rem; color: rgba(255,255,255,.6); font-weight: 600; margin-bottom: 3px; }
    .contact-info-val   { font-size: .97rem; font-weight: 800; color: #fff; }
    .contact-form-card {
      background: rgba(255,255,255,.09);
      border: 1px solid rgba(255,255,255,.14);
      border-radius: 24px; padding: 32px;
    }
    .contact-form-card .form-control,
    .contact-form-card .form-select {
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 12px; color: #fff;
      padding: 12px 16px; font-size: .93rem;
    }
    .contact-form-card .form-control::placeholder { color: rgba(255,255,255,.45); }
    .contact-form-card .form-control:focus {
      background: rgba(255,255,255,.15);
      border-color: var(--c-accent);
      box-shadow: 0 0 0 3px rgba(6,170,137,.20); color: #fff;
    }
    .contact-form-card label { color: rgba(255,255,255,.85); font-weight: 700; margin-bottom: 6px; }
    .btn-contact-submit {
      width: 100%; height: 52px; border: none; border-radius: 14px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; font-weight: 800; font-size: 1rem; cursor: pointer;
      transition: transform .22s, opacity .22s;
    }
    .btn-contact-submit:hover { transform: translateY(-2px); opacity: .88; }

    /* ══════════════════════════════════
       RESPONSIVE
    ══════════════════════════════════ */
    @media (max-width: 991.98px) {
      .lp-hero { min-height: auto; }
      .lp-hero-inner { padding: 4rem 0 3.5rem; }
      .about-pillars { grid-template-columns: 1fr 1fr; }
      .section { padding: 70px 0; }
      .hero-seal { width: min(240px, 75vw); }
      .hero-stats-strip { gap: 0; }
      .hero-stat { padding: 0 8px; }
    }
    @media (max-width: 767.98px) {
      .lp-hero-inner { padding: 3rem 0 2.5rem; }
      .hero-seal-wrap { padding: 1rem 0 2rem; }
      .hero-seal { width: min(180px, 60vw); }
      .hero-text-col { text-align: center; }
      .lp-hero-tag { align-self: center; }
      .lp-hero-divider { margin: 0 auto 22px; }
      .lp-hero-sub { font-size: .97rem; margin: 0 auto 28px; }
      .lp-hero-actions { justify-content: center; }
      .about-pillars { grid-template-columns: 1fr; }
      .section { padding: 52px 0; }
      .hero-stats-strip { flex-wrap: wrap; margin-top: 32px; padding-top: 22px; }
      .hero-stat { flex: 0 0 50%; border-left: none; border-bottom: 1px solid rgba(255,255,255,.08); padding: 14px 8px; }
      .hero-stat:nth-child(odd) { border-left: 1px solid rgba(255,255,255,.08); }
      .hero-stat:nth-last-child(-n+2) { border-bottom: none; }
      .contact-form-card { padding: 24px 18px; }
      .partners-shell::before, .partners-shell::after { width: 50px; }
    }
    @media (max-width: 575.98px) {
      .splash-logo { width: 100px; }
      .lp-hero h1 { font-size: 1.9rem; }
      .lp-hero-inner { padding: 2.5rem 0 2rem; }
      .hero-btn-primary, .hero-btn-outline { width: 100%; justify-content: center; height: 50px; font-size: .92rem; }
      .hero-stats-strip { margin-top: 24px; }
      .contact-form-card { padding: 20px 14px; }
      .sec-title { letter-spacing: -.2px; }
    }

    @media (prefers-reduced-motion: reduce) {
      #lp-splash { display: none; }
      [data-reveal] { opacity: 1; transform: none; transition: none; }
      .logo-track { animation: none; }
      .hero-seal, .lp-hero-tag, .lp-hero h1, .lp-hero-divider,
      .lp-hero-sub, .lp-hero-actions, .hero-stats-strip, .hero-scroll-hint {
        opacity: 1; animation: none;
      }
    }
  </style>
</head>
<body>

  <!-- ══════════════════════════════════════
       SPLASH SCREEN
  ══════════════════════════════════════ -->
  <div id="lp-splash" role="status" aria-label="جارٍ التحميل">
    <div class="splash-logo-wrap">
      <img src="<?= $basePath ?>assets/images/Logo.png" alt="شعار الهيئة" class="splash-logo">
    </div>
    <div class="splash-name">
      <?php echo htmlspecialchars(__('splash_name')); ?>
      <span><?php echo htmlspecialchars(__('splash_sub')); ?></span>
    </div>
    <div class="splash-bar"></div>
  </div>

  <?php include __DIR__ . '/includes/layout/header.php'; ?>

  <main>

    <!-- ══════════════════════════════════════
         HERO
    ══════════════════════════════════════ -->
    <section class="lp-hero" id="home">
      <div class="lp-hero-inner">
        <div class="container">
          <div class="row align-items-center g-4 g-lg-5">

            <!-- Logo / Seal — right column (RTL = right side on screen) -->
            <div class="col-lg-5 order-lg-2">
              <div class="hero-seal-wrap">
                <img src="<?= $basePath ?>assets/images/Logo.png"
                     alt="شعار هيئة تنمية المشروعات الصغيرة والمتوسطة"
                     class="hero-seal">
              </div>
            </div>

            <!-- Text — left column -->
            <div class="col-lg-7 order-lg-1">
              <div class="hero-text-col">

                <div class="lp-hero-tag">
                  <i class="bi bi-patch-check-fill"></i>
                  <?php echo htmlspecialchars(__('hero_tag')); ?>
                </div>

                <h1>
                  <?php echo __('hero_h1'); ?>
                </h1>

                <div class="lp-hero-divider"></div>

                <p class="lp-hero-sub">
                  <?php echo htmlspecialchars(__('hero_sub')); ?>
                </p>

                <div class="lp-hero-actions">
                  <a href="<?= $basePath ?>register.php" class="hero-btn-primary">
                    <i class="bi bi-person-plus-fill"></i>
                    <?php echo htmlspecialchars(__('hero_cta_register')); ?>
                  </a>
                  <a href="<?= $basePath ?>user-guide.php" class="hero-btn-outline">
                    <i class="bi bi-book-fill"></i>
                    <?php echo htmlspecialchars(__('hero_cta_guide')); ?>
                  </a>
                  <a href="<?= $basePath ?>services/index.php" class="hero-btn-outline">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <?php echo htmlspecialchars(__('hero_cta_services')); ?>
                  </a>
                </div>

                <div class="hero-stats-strip">
                  <div class="hero-stat">
                    <span class="hero-stat-num counter" data-target="1250">0</span>
                    <span class="hero-stat-lbl"><?php echo htmlspecialchars(__('hero_stat_projects')); ?></span>
                  </div>
                  <div class="hero-stat">
                    <span class="hero-stat-num counter" data-target="3420">0</span>
                    <span class="hero-stat-lbl"><?php echo htmlspecialchars(__('hero_stat_beneficiaries')); ?></span>
                  </div>
                  <div class="hero-stat">
                    <span class="hero-stat-num counter" data-target="95">0</span>
                    <span class="hero-stat-lbl"><?php echo htmlspecialchars(__('hero_stat_centers')); ?></span>
                  </div>
                  <div class="hero-stat">
                    <span class="hero-stat-num counter" data-target="14">0</span>
                    <span class="hero-stat-lbl"><?php echo htmlspecialchars(__('hero_stat_branches')); ?></span>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="hero-scroll-hint">
        <i class="bi bi-chevron-double-down"></i>
        <?php echo htmlspecialchars(__('hero_scroll')); ?>
      </div>
    </section>

    <!-- ══════════════════════════════════════
         ABOUT
    ══════════════════════════════════════ -->
    <section class="section about-dark" id="about">
      <div class="container text-center">

        <div data-reveal>
          <span class="sec-badge"><i class="bi bi-info-circle-fill"></i> <?php echo htmlspecialchars(__('about_badge')); ?></span>
          <h2 class="sec-title mt-2"><?php echo htmlspecialchars(__('about_title')); ?></h2>
          <p class="about-dark-text mx-auto mt-3">
            <?php echo htmlspecialchars(__('about_text')); ?>
          </p>
        </div>

        <div class="about-pillars" data-reveal data-reveal-delay="1">
          <div class="about-pillar">
            <div class="about-pillar-icon"><i class="bi bi-shield-check"></i></div>
            <h4><?php echo htmlspecialchars(__('about_p1_title')); ?></h4>
            <p><?php echo htmlspecialchars(__('about_p1_text')); ?></p>
          </div>
          <div class="about-pillar">
            <div class="about-pillar-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <h4><?php echo htmlspecialchars(__('about_p2_title')); ?></h4>
            <p><?php echo htmlspecialchars(__('about_p2_text')); ?></p>
          </div>
          <div class="about-pillar">
            <div class="about-pillar-icon"><i class="bi bi-link-45deg"></i></div>
            <h4><?php echo htmlspecialchars(__('about_p3_title')); ?></h4>
            <p><?php echo htmlspecialchars(__('about_p3_text')); ?></p>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════
         SERVICES
    ══════════════════════════════════════ -->
    <section class="section services-section" id="services">
      <div class="container">

        <div class="text-center mb-5" data-reveal>
          <span class="sec-badge"><i class="bi bi-grid-3x3-gap-fill"></i> <?php echo htmlspecialchars(__('services_badge')); ?></span>
          <h2 class="sec-title mt-2"><?php echo htmlspecialchars(__('services_title')); ?></h2>
          <p class="sec-sub mx-auto mt-2">
            <?php echo htmlspecialchars(__('services_sub')); ?>
          </p>
        </div>

        <div class="row g-4">

          <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="1">
            <a href="<?= $basePath ?>services/landing.php?slug=training" class="service-card">
              <div class="service-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('svc_training')); ?>" loading="lazy">
                <span class="service-cat-pill"><?php echo htmlspecialchars(__('svc_pill_training')); ?></span>
                <div class="service-card-label">
                  <div class="svc-icon"><i class="bi bi-mortarboard-fill"></i></div>
                  <h3><?php echo htmlspecialchars(__('svc_training')); ?></h3>
                </div>
              </div>
              <div class="service-card-body">
                <p><?php echo htmlspecialchars(__('svc_training_desc')); ?></p>
                <span class="service-link"><?php echo htmlspecialchars(__('discover_more')); ?> <span class="arrow-icon"><i class="bi <?= $arrowIcon ?>"></i></span></span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="2">
            <a href="<?= $basePath ?>services/landing.php?slug=finance" class="service-card">
              <div class="service-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('svc_finance')); ?>" loading="lazy">
                <span class="service-cat-pill"><?php echo htmlspecialchars(__('svc_pill_finance')); ?></span>
                <div class="service-card-label">
                  <div class="svc-icon"><i class="bi bi-bank2"></i></div>
                  <h3><?php echo htmlspecialchars(__('svc_finance')); ?></h3>
                </div>
              </div>
              <div class="service-card-body">
                <p><?php echo htmlspecialchars(__('svc_finance_desc')); ?></p>
                <span class="service-link"><?php echo htmlspecialchars(__('discover_more')); ?> <span class="arrow-icon"><i class="bi <?= $arrowIcon ?>"></i></span></span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="3">
            <a href="<?= $basePath ?>services/landing.php?slug=incubation" class="service-card">
              <div class="service-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('svc_incubation')); ?>" loading="lazy">
                <span class="service-cat-pill"><?php echo htmlspecialchars(__('svc_pill_incubation')); ?></span>
                <div class="service-card-label">
                  <div class="svc-icon"><i class="bi bi-buildings-fill"></i></div>
                  <h3><?php echo htmlspecialchars(__('svc_incubation')); ?></h3>
                </div>
              </div>
              <div class="service-card-body">
                <p><?php echo htmlspecialchars(__('svc_incubation_desc')); ?></p>
                <span class="service-link"><?php echo htmlspecialchars(__('discover_more')); ?> <span class="arrow-icon"><i class="bi <?= $arrowIcon ?>"></i></span></span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="1">
            <a href="<?= $basePath ?>services/landing.php?slug=consulting" class="service-card">
              <div class="service-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('svc_consulting')); ?>" loading="lazy">
                <span class="service-cat-pill"><?php echo htmlspecialchars(__('svc_pill_consulting')); ?></span>
                <div class="service-card-label">
                  <div class="svc-icon"><i class="bi bi-headset"></i></div>
                  <h3><?php echo htmlspecialchars(__('svc_consulting')); ?></h3>
                </div>
              </div>
              <div class="service-card-body">
                <p><?php echo htmlspecialchars(__('svc_consulting_desc')); ?></p>
                <span class="service-link"><?php echo htmlspecialchars(__('discover_more')); ?> <span class="arrow-icon"><i class="bi <?= $arrowIcon ?>"></i></span></span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="2">
            <a href="<?= $basePath ?>services/landing.php?slug=certificates" class="service-card">
              <div class="service-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('svc_certificates')); ?>" loading="lazy">
                <span class="service-cat-pill"><?php echo htmlspecialchars(__('svc_pill_certificates')); ?></span>
                <div class="service-card-label">
                  <div class="svc-icon"><i class="bi bi-patch-check-fill"></i></div>
                  <h3><?php echo htmlspecialchars(__('svc_certificates')); ?></h3>
                </div>
              </div>
              <div class="service-card-body">
                <p><?php echo htmlspecialchars(__('svc_certificates_desc')); ?></p>
                <span class="service-link"><?php echo htmlspecialchars(__('discover_more')); ?> <span class="arrow-icon"><i class="bi <?= $arrowIcon ?>"></i></span></span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="3">
            <a href="<?= $basePath ?>services/landing.php?slug=needs-map" class="service-card">
              <div class="service-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('svc_gis')); ?>" loading="lazy">
                <span class="service-cat-pill">GIS</span>
                <div class="service-card-label">
                  <div class="svc-icon"><i class="bi bi-geo-alt-fill"></i></div>
                  <h3><?php echo htmlspecialchars(__('svc_gis')); ?></h3>
                </div>
              </div>
              <div class="service-card-body">
                <p><?php echo htmlspecialchars(__('svc_gis_desc')); ?></p>
                <span class="service-link"><?php echo htmlspecialchars(__('discover_more')); ?> <span class="arrow-icon"><i class="bi <?= $arrowIcon ?>"></i></span></span>
              </div>
            </a>
          </div>

        </div>
      </div>
    </section>

    <?php include __DIR__ . '/includes/sections/landing-journeys.php'; ?>

    <?php include __DIR__ . '/includes/sections/user-guide-preview.php'; ?>

    <!-- ══════════════════════════════════════
         WHO CAN REGISTER
    ══════════════════════════════════════ -->
    <section class="section register-section" id="register">
      <div class="container">

        <div class="text-center mb-5" data-reveal>
          <span class="sec-badge"><i class="bi bi-person-plus-fill"></i> <?php echo htmlspecialchars(__('register_badge')); ?></span>
          <h2 class="sec-title mt-2"><?php echo htmlspecialchars(__('register_title')); ?></h2>
          <p class="sec-sub mx-auto mt-2">
            <?php echo htmlspecialchars(__('register_sub')); ?>
            <a href="<?= $basePath ?>user-guide.php#choose-role" style="color:var(--c-primary);font-weight:800">
              <?php echo htmlspecialchars(__('register_guide_link')); ?>
            </a>
          </p>
        </div>

        <div class="row g-4 justify-content-center">

          <div class="col-sm-6 col-lg-4" data-reveal data-reveal-delay="1">
            <div class="reg-card">
              <div class="reg-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('reg_trainer_title')); ?>" loading="lazy">
                <span class="reg-role-pill"><?php echo htmlspecialchars(__('reg_trainer_pill')); ?></span>
              </div>
              <div class="reg-icon-wrap">
                <div class="reg-icon-float"><i class="bi bi-person-workspace"></i></div>
              </div>
              <div class="reg-card-body">
                <h3><?php echo htmlspecialchars(__('reg_trainer_title')); ?></h3>
                <p><?php echo htmlspecialchars(__('reg_trainer_desc')); ?></p>
                <a href="<?= $basePath ?>register.php?type=trainer" class="reg-btn">
                  <?php echo htmlspecialchars(__('reg_trainer_btn')); ?> <span class="btn-arrow"><i class="bi <?= $arrowIcon ?>"></i></span>
                </a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-lg-4" data-reveal data-reveal-delay="2">
            <div class="reg-card">
              <div class="reg-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('reg_trainee_title')); ?>" loading="lazy">
                <span class="reg-role-pill"><?php echo htmlspecialchars(__('reg_trainee_pill')); ?></span>
              </div>
              <div class="reg-icon-wrap">
                <div class="reg-icon-float"><i class="bi bi-person-badge-fill"></i></div>
              </div>
              <div class="reg-card-body">
                <h3><?php echo htmlspecialchars(__('reg_trainee_title')); ?></h3>
                <p><?php echo htmlspecialchars(__('reg_trainee_desc')); ?></p>
                <a href="<?= $basePath ?>register.php?type=trainee" class="reg-btn">
                  <?php echo htmlspecialchars(__('reg_trainee_btn')); ?> <span class="btn-arrow"><i class="bi <?= $arrowIcon ?>"></i></span>
                </a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-lg-4" data-reveal data-reveal-delay="3">
            <div class="reg-card">
              <div class="reg-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('reg_center_title')); ?>" loading="lazy">
                <span class="reg-role-pill"><?php echo htmlspecialchars(__('reg_center_pill')); ?></span>
              </div>
              <div class="reg-icon-wrap">
                <div class="reg-icon-float"><i class="bi bi-building-add"></i></div>
              </div>
              <div class="reg-card-body">
                <h3><?php echo htmlspecialchars(__('reg_center_title')); ?></h3>
                <p><?php echo htmlspecialchars(__('reg_center_desc')); ?></p>
                <a href="<?= $basePath ?>register.php?type=center" class="reg-btn">
                  <?php echo htmlspecialchars(__('reg_center_btn')); ?> <span class="btn-arrow"><i class="bi <?= $arrowIcon ?>"></i></span>
                </a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-lg-4" data-reveal data-reveal-delay="1">
            <div class="reg-card">
              <div class="reg-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('reg_entrepreneur_title')); ?>" loading="lazy">
                <span class="reg-role-pill"><?php echo htmlspecialchars(__('reg_entrepreneur_pill')); ?></span>
              </div>
              <div class="reg-icon-wrap">
                <div class="reg-icon-float"><i class="bi bi-rocket-takeoff-fill"></i></div>
              </div>
              <div class="reg-card-body">
                <h3><?php echo htmlspecialchars(__('reg_entrepreneur_title')); ?></h3>
                <p><?php echo htmlspecialchars(__('reg_entrepreneur_desc')); ?></p>
                <a href="<?= $basePath ?>register.php?type=project_owner" class="reg-btn">
                  <?php echo htmlspecialchars(__('reg_entrepreneur_btn')); ?> <span class="btn-arrow"><i class="bi <?= $arrowIcon ?>"></i></span>
                </a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-lg-4" data-reveal data-reveal-delay="2">
            <div class="reg-card">
              <div class="reg-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1542744094-24638eff58bb?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('reg_consultant_title')); ?>" loading="lazy">
                <span class="reg-role-pill"><?php echo htmlspecialchars(__('reg_consultant_pill')); ?></span>
              </div>
              <div class="reg-icon-wrap">
                <div class="reg-icon-float"><i class="bi bi-briefcase-fill"></i></div>
              </div>
              <div class="reg-card-body">
                <h3><?php echo htmlspecialchars(__('reg_consultant_title')); ?></h3>
                <p><?php echo htmlspecialchars(__('reg_consultant_desc')); ?></p>
                <a href="<?= $basePath ?>register.php?type=consultant" class="reg-btn">
                  <?php echo htmlspecialchars(__('reg_consultant_btn')); ?> <span class="btn-arrow"><i class="bi <?= $arrowIcon ?>"></i></span>
                </a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-lg-4" data-reveal data-reveal-delay="3">
            <div class="reg-card">
              <div class="reg-card-img-wrap">
                <img src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?w=700&q=85&fit=crop" alt="<?php echo htmlspecialchars(__('reg_jobseeker_title')); ?>" loading="lazy">
                <span class="reg-role-pill"><?php echo htmlspecialchars(__('reg_jobseeker_pill')); ?></span>
              </div>
              <div class="reg-icon-wrap">
                <div class="reg-icon-float"><i class="bi bi-person-lines-fill"></i></div>
              </div>
              <div class="reg-card-body">
                <h3><?php echo htmlspecialchars(__('reg_jobseeker_title')); ?></h3>
                <p><?php echo htmlspecialchars(__('reg_jobseeker_desc')); ?></p>
                <a href="<?= $basePath ?>register.php?type=jobseeker" class="reg-btn">
                  <?php echo htmlspecialchars(__('reg_jobseeker_btn')); ?> <span class="btn-arrow"><i class="bi <?= $arrowIcon ?>"></i></span>
                </a>
              </div>
            </div>
          </div>

        </div>

        <div class="text-center mt-5" data-reveal>
          <p class="text-muted mb-3">هل لديك حساب مسبق؟</p>
          <a href="<?= $basePath ?>login.php" class="hero-btn-primary" style="display:inline-flex">
            <i class="bi bi-box-arrow-in-right"></i>
            تسجيل الدخول
          </a>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════
         SECTORS CLASSIFICATION
    ══════════════════════════════════════ -->
    <section class="section sectors-section" id="sectors">
      <div class="container">

        <div class="text-center mb-5" data-reveal>
          <span class="sec-badge"><i class="bi bi-diagram-3-fill"></i> <?php echo htmlspecialchars(__('sectors_badge')); ?></span>
          <h2 class="sec-title mt-2"><?php echo htmlspecialchars(__('sectors_title')); ?></h2>
          <p class="sec-sub mx-auto mt-2">
            وفق دليل تعريف المشروعات الرسمي، تُصنَّف المشروعات حسب 4 قطاعات اقتصادية أساسية.
          </p>
        </div>

        <div class="row g-4 mb-5" data-reveal>

          <!-- زراعي -->
          <div class="col-sm-6 col-lg-3">
            <div class="sector-card">
              <div class="sector-icon" style="--sc:#22863a">
                <i class="bi bi-tree-fill"></i>
              </div>
              <h4>الزراعي</h4>
              <p>الإنتاج النباتي والحيواني وصيد الأسماك والحراجة</p>
              <ul class="sector-criteria">
                <li><span class="crit-label">متناهي الصغر</span><span class="crit-val">≤ 5 عمال</span></li>
                <li><span class="crit-label">صغير</span><span class="crit-val">≤ 20 عامل</span></li>
                <li><span class="crit-label">متوسط</span><span class="crit-val">≤ 70 عامل</span></li>
              </ul>
            </div>
          </div>

          <!-- صناعي -->
          <div class="col-sm-6 col-lg-3">
            <div class="sector-card">
              <div class="sector-icon" style="--sc:#d97706">
                <i class="bi bi-gear-wide-connected"></i>
              </div>
              <h4>الصناعي</h4>
              <p>التحويل والتصنيع واستخراج المواد الخام وتجهيزها</p>
              <ul class="sector-criteria">
                <li><span class="crit-label">متناهي الصغر</span><span class="crit-val">≤ 5 عمال</span></li>
                <li><span class="crit-label">صغير</span><span class="crit-val">≤ 35 عامل</span></li>
                <li><span class="crit-label">متوسط</span><span class="crit-val">≤ 100 عامل</span></li>
              </ul>
            </div>
          </div>

          <!-- تجاري -->
          <div class="col-sm-6 col-lg-3">
            <div class="sector-card">
              <div class="sector-icon" style="--sc:#7c3aed">
                <i class="bi bi-shop-window"></i>
              </div>
              <h4>التجاري</h4>
              <p>شراء وبيع وتوزيع السلع بهدف تحقيق الربح وإعادة الاستثمار</p>
              <ul class="sector-criteria">
                <li><span class="crit-label">متناهي الصغر</span><span class="crit-val">≤ 3 عمال</span></li>
                <li><span class="crit-label">صغير</span><span class="crit-val">≤ 10 عمال</span></li>
                <li><span class="crit-label">متوسط</span><span class="crit-val">≤ 30 عامل</span></li>
              </ul>
            </div>
          </div>

          <!-- خدمي -->
          <div class="col-sm-6 col-lg-3">
            <div class="sector-card">
              <div class="sector-icon" style="--sc:#0891b2">
                <i class="bi bi-headset"></i>
              </div>
              <h4>الخدمي</h4>
              <p>التعليم والصحة والنقل والاتصالات والسياحة وسائر الخدمات</p>
              <ul class="sector-criteria">
                <li><span class="crit-label">متناهي الصغر</span><span class="crit-val">≤ 5 عمال</span></li>
                <li><span class="crit-label">صغير</span><span class="crit-val">≤ 40 عامل</span></li>
                <li><span class="crit-label">متوسط</span><span class="crit-val">≤ 100 عامل</span></li>
              </ul>
            </div>
          </div>

        </div>

        <!-- Classification table -->
        <div class="classif-table-wrap" data-reveal>
          <div class="classif-table-head">
            <i class="bi bi-table"></i>
            جدول معايير التصنيف الرسمية — دليل تعريف المشروعات
          </div>
          <div class="table-responsive">
            <table class="classif-table">
              <thead>
                <tr>
                  <th>القطاع</th>
                  <th>المعيار</th>
                  <th><span class="badge-nano">متناهي الصغر</span></th>
                  <th><span class="badge-small">صغير</span></th>
                  <th><span class="badge-medium">متوسط</span></th>
                </tr>
              </thead>
              <tbody>
                <!-- زراعي -->
                <tr class="sector-agri sector-divider">
                  <td rowspan="3" class="sector-name"><i class="bi bi-tree-fill"></i>&nbsp; زراعي</td>
                  <td class="crit-lbl">عدد العمال</td>
                  <td class="val-nano">≤ 5</td><td class="val-small">≤ 20</td><td class="val-medium">≤ 70</td>
                </tr>
                <tr class="sector-agri"><td class="crit-lbl">قيمة المبيعات (م.ل.س)</td><td class="val-nano">≤ 75</td><td class="val-small">≤ 900</td><td class="val-medium">≤ 3,000</td></tr>
                <tr class="sector-agri"><td class="crit-lbl">رأس المال المستثمر (م.ل.س)</td><td class="val-nano">≤ 50</td><td class="val-small">≤ 750</td><td class="val-medium">≤ 2,250</td></tr>

                <!-- صناعي -->
                <tr class="sector-ind sector-divider">
                  <td rowspan="3" class="sector-name"><i class="bi bi-gear-wide-connected"></i>&nbsp; صناعي</td>
                  <td class="crit-lbl">عدد العمال</td>
                  <td class="val-nano">≤ 5</td><td class="val-small">≤ 35</td><td class="val-medium">≤ 100</td>
                </tr>
                <tr class="sector-ind"><td class="crit-lbl">قيمة المبيعات (م.ل.س)</td><td class="val-nano">≤ 110</td><td class="val-small">≤ 2,250</td><td class="val-medium">≤ 7,500</td></tr>
                <tr class="sector-ind"><td class="crit-lbl">رأس المال المستثمر (م.ل.س)</td><td class="val-nano">≤ 75</td><td class="val-small">≤ 1,500</td><td class="val-medium">≤ 6,750</td></tr>

                <!-- تجاري -->
                <tr class="sector-com sector-divider">
                  <td rowspan="3" class="sector-name"><i class="bi bi-shop-window"></i>&nbsp; تجاري</td>
                  <td class="crit-lbl">عدد العمال</td>
                  <td class="val-nano">≤ 3</td><td class="val-small">≤ 10</td><td class="val-medium">≤ 30</td>
                </tr>
                <tr class="sector-com"><td class="crit-lbl">قيمة المبيعات (م.ل.س)</td><td class="val-nano">≤ 150</td><td class="val-small">≤ 1,500</td><td class="val-medium">≤ 3,000</td></tr>
                <tr class="sector-com"><td class="crit-lbl">رأس المال العامل (م.ل.س)</td><td class="val-nano">≤ 60</td><td class="val-small">≤ 600</td><td class="val-medium">≤ 2,250</td></tr>

                <!-- خدمي -->
                <tr class="sector-svc sector-divider">
                  <td rowspan="3" class="sector-name"><i class="bi bi-headset"></i>&nbsp; خدمي</td>
                  <td class="crit-lbl">عدد العمال</td>
                  <td class="val-nano">≤ 5</td><td class="val-small">≤ 40</td><td class="val-medium">≤ 100</td>
                </tr>
                <tr class="sector-svc"><td class="crit-lbl">قيمة المبيعات (م.ل.س)</td><td class="val-nano">≤ 110</td><td class="val-small">≤ 1,500</td><td class="val-medium">≤ 4,500</td></tr>
                <tr class="sector-svc"><td class="crit-lbl">رأس المال المستثمر (م.ل.س)</td><td class="val-nano">≤ 150</td><td class="val-small">≤ 2,250</td><td class="val-medium">≤ 7,500</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════
         OFFICIAL DOCUMENTS
    ══════════════════════════════════════ -->
    <section class="section docs-section" id="docs">
      <div class="container">

        <div class="text-center mb-5" data-reveal>
          <span class="sec-badge"><i class="bi bi-file-earmark-text-fill"></i> وثائق رسمية</span>
          <h2 class="sec-title mt-2">الإطار التنظيمي والتشريعي</h2>
          <p class="sec-sub mx-auto mt-2">
            الوثائق والقرارات الرسمية الصادرة عن وزارة الاقتصاد والتجارة الخارجية التي تحكم عمل الهيئة.
          </p>
        </div>

        <div class="row g-4" data-reveal>

          <div class="col-md-6">
            <div class="doc-card">
              <div class="doc-card-icon">
                <i class="bi bi-journal-bookmark-fill"></i>
              </div>
              <div class="doc-card-body">
                <span class="doc-badge">التصنيف الوطني SYRSIC — الإصدار الأول</span>
                <h3>التصنيف الوطني للأنشطة الاقتصادية SYRSIC</h3>
                <p>
                  إطار شامل يُصنّف جميع الأنشطة الاقتصادية في سورية عبر 18 باباً وأقساماً فرعية دقيقة،
                  مستنداً إلى التصنيف الصناعي الدولي الموحّد ISIC4.
                </p>
                <ul class="doc-highlights">
                  <li><i class="bi bi-check-circle-fill"></i> +18 باباً للأنشطة الاقتصادية</li>
                  <li><i class="bi bi-check-circle-fill"></i> يغطي الزراعة والصناعة والتجارة والخدمات</li>
                  <li><i class="bi bi-check-circle-fill"></i> يُستخدم لإصدار التراخيص وجمع الإحصاءات</li>
                </ul>
                <a href="<?= $basePath ?>docs/syrsic.php" class="doc-read-btn">
                  <i class="bi bi-book-open-fill"></i>
                  قراءة الوثيقة كاملة
                  <i class="bi bi-arrow-left ms-1"></i>
                </a>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="doc-card doc-card--accent">
              <div class="doc-card-icon">
                <i class="bi bi-clipboard2-check-fill"></i>
              </div>
              <div class="doc-card-body">
                <span class="doc-badge">دليل تعريف المشروعات الرسمي</span>
                <h3>دليل تعريف المشروعات متناهية الصغر والصغيرة والمتوسطة</h3>
                <p>
                  دليل رسمي موحّد يحدّد معايير تصنيف المشروعات حسب عدد العمال وقيمة المبيعات ورأس المال،
                  ملزم للتطبيق من جميع الجهات المعنية.
                </p>
                <ul class="doc-highlights">
                  <li><i class="bi bi-check-circle-fill"></i> 4 قطاعات: زراعي، صناعي، تجاري، خدمي</li>
                  <li><i class="bi bi-check-circle-fill"></i> مؤشر ERI لتصنيف المشروعات آلياً</li>
                  <li><i class="bi bi-check-circle-fill"></i> ملزم لجميع الجهات الحكومية وغير الحكومية</li>
                </ul>
                <a href="<?= $basePath ?>docs/sme-guide.php" class="doc-read-btn">
                  <i class="bi bi-book-open-fill"></i>
                  قراءة الدليل كاملاً
                  <i class="bi bi-arrow-left ms-1"></i>
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════
         STRATEGIC PARTNERS
    ══════════════════════════════════════ -->
    <section class="section partners-section" id="partners">
      <div class="container">

        <div class="text-center mb-2" data-reveal>
          <span class="sec-badge"><i class="bi bi-handshake-fill"></i> <?php echo htmlspecialchars(__('partners_badge')); ?></span>
          <h2 class="sec-title mt-2"><?php echo htmlspecialchars(__('partners_title')); ?></h2>
          <p class="sec-sub mx-auto mt-2">
            نعتز بالتعاون مع شركائنا في دعم الاستدامة المالية وتمكين المشروعات عبر مبادرات نوعية وشراكات مؤسسية فاعلة.
          </p>
        </div>

        <div class="partners-shell" data-reveal data-reveal-delay="1">
          <div class="logo-slider">
            <div class="logo-track">
              <?php
              $logos = [
                'adelandtamkem.png','Albaraka.png','Artboard.png','etc.png','idleb.png',
                'itc.png','jci.PNG','loan-guarantee.png','moe.png','mohe.png',
                'moneyoutsyria.png','Orange.png','sdetc.png','shafak.png',
                'teachas.png','Tedree.png','undp.png','violet.png'
              ];
              foreach ($logos as $logo): ?>
              <div class="logo-item">
                <img src="<?= $basePath ?>assets/images/istrategy/<?= htmlspecialchars($logo) ?>"
                     alt="شريك استراتيجي" loading="eager" decoding="async">
              </div>
              <?php endforeach;
              foreach ($logos as $logo): ?>
              <div class="logo-item" aria-hidden="true">
                <img src="<?= $basePath ?>assets/images/istrategy/<?= htmlspecialchars($logo) ?>"
                     alt="" loading="lazy" decoding="async">
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════
         NEWS
    ══════════════════════════════════════ -->
    <section class="section news-section" id="news">
      <div class="container">

        <div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3" data-reveal>
          <div>
            <span class="sec-badge"><i class="bi bi-newspaper"></i> الأخبار</span>
            <h2 class="sec-title mt-2 mb-0"><?php echo htmlspecialchars(__('news_title')); ?></h2>
          </div>
          <a href="<?= $basePath ?>index.php#news" class="hero-btn-primary" style="flex-shrink:0">
            <i class="bi bi-arrow-left"></i>
            جميع الأخبار
          </a>
        </div>

        <div class="row g-4">
          <?php
          $newsItems = [
            ['title' => 'إطلاق برنامج تدريبي جديد لتطوير المشاريع الصغيرة في المحافظات', 'tag' => 'تدريب',  'date' => '', 'icon' => 'bi-mortarboard-fill'],
            ['title' => 'الهيئة تُبرم اتفاقية شراكة مع عدد من البنوك لتمويل المشاريع الناشئة',  'tag' => 'تمويل',  'date' => '', 'icon' => 'bi-bank2'],
            ['title' => 'افتتاح حاضنة أعمال جديدة في محافظة حلب لدعم رواد الأعمال الشباب',      'tag' => 'حاضنات', 'date' => '', 'icon' => 'bi-buildings-fill'],
          ];
          foreach ($newsItems as $i => $item): ?>
          <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="<?= $i+1 ?>">
            <a href="#" class="news-card">
              <div class="news-card-img"><i class="bi <?= $item['icon'] ?>"></i></div>
              <div class="news-card-body">
                <span class="news-tag"><?= $item['tag'] ?></span>
                <h4><?= $item['title'] ?></h4>
                <?php if (!empty($item['date'])): ?>
                <div class="news-meta">
                  <i class="bi bi-calendar3"></i>
                  <?= $item['date'] ?>
                </div>
                <?php endif; ?>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <?php include __DIR__ . '/includes/sections/faq-section.php'; ?>

    <!-- ══════════════════════════════════════
         GUEST CTA BAND
    ══════════════════════════════════════ -->
    <section id="guestCtaBand" class="guest-cta-band" style="display:none">
      <div class="container">
        <div class="gcta-inner">
          <div class="gcta-icon"><i class="bi bi-rocket-takeoff-fill"></i></div>
          <div class="gcta-text">
            <h3>انضم للمنصة واستفد من جميع الخدمات</h3>
            <p>أضف احتياجات مشروعك، تقدّم لبرامج التمويل والحاضنات، واحصل على استشارة متخصصة — كل ذلك مجاناً.</p>
          </div>
          <div class="gcta-actions">
            <a href="<?= $basePath ?>register.php" class="gcta-btn-primary">
              <i class="bi bi-person-plus-fill"></i> إنشاء حساب مجاناً
            </a>
            <a href="<?= $basePath ?>login.php" class="gcta-btn-outline">
              <i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════
         CONTACT
    ══════════════════════════════════════ -->
    <section class="section contact-section" id="contact">
      <div class="container">

        <div class="text-center mb-5" data-reveal>
          <span class="sec-badge"><i class="bi bi-envelope-fill"></i> <?php echo htmlspecialchars(__('contact_badge')); ?></span>
          <h2 class="sec-title mt-2"><?php echo htmlspecialchars(__('contact_title')); ?></h2>
          <p class="sec-sub mx-auto mt-2" style="color:rgba(255,255,255,.82)">
            <?php echo htmlspecialchars(__('contact_sub')); ?>
          </p>
        </div>

        <div class="row g-4 align-items-start">

          <div class="col-lg-5">
            <div class="d-flex flex-column gap-3" data-reveal>
              <div class="contact-info-item">
                <div class="contact-info-icon"><i class="bi bi-telephone-fill"></i></div>
                <div>
                  <div class="contact-info-label">الهاتف</div>
                  <div class="contact-info-val" dir="ltr">+963 60701377</div>
                </div>
              </div>
              <div class="contact-info-item">
                <div class="contact-info-icon"><i class="bi bi-envelope-fill"></i></div>
                <div>
                  <div class="contact-info-label">البريد الإلكتروني</div>
                  <div class="contact-info-val">acu-md@mail.sy</div>
                </div>
              </div>
              <div class="contact-info-item">
                <div class="contact-info-icon"><i class="bi bi-globe"></i></div>
                <div>
                  <div class="contact-info-label">الموقع الإلكتروني</div>
                  <div class="contact-info-val">www.smedc.gov.sy</div>
                </div>
              </div>
              <div class="contact-info-item">
                <div class="contact-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <div>
                  <div class="contact-info-label">العنوان</div>
                  <div class="contact-info-val">دمشق — أوتستراد بناء الإسكان</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-7" data-reveal data-reveal-delay="2">
            <div class="contact-form-card">
              <h3 style="font-weight:800;color:#fff;margin-bottom:22px;font-size:1.15rem;">
                <i class="bi bi-send-fill me-2" style="color:var(--c-accent)"></i>
                أرسل رسالتك
              </h3>
              <form id="contactForm" novalidate>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label>الاسم الكامل</label>
                    <input type="text" class="form-control" placeholder="أدخل اسمك" required>
                  </div>
                  <div class="col-md-6">
                    <label>البريد الإلكتروني</label>
                    <input type="email" class="form-control" placeholder="example@mail.com" required>
                  </div>
                  <div class="col-12">
                    <label>الموضوع</label>
                    <input type="text" class="form-control" placeholder="موضوع رسالتك" required>
                  </div>
                  <div class="col-12">
                    <label>الرسالة</label>
                    <textarea class="form-control" rows="4" placeholder="اكتب رسالتك هنا..." required></textarea>
                  </div>
                  <div class="col-12">
                    <button type="submit" class="btn-contact-submit">
                      <i class="bi bi-send-fill me-2"></i>
                      إرسال الرسالة
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>
    </section>

    <?php include __DIR__ . '/includes/sections/faq-accordion.php'; ?>

  </main>

  <?php include __DIR__ . '/includes/layout/footer.php'; ?>
  <?php include __DIR__ . '/includes/layout/scripts.php'; ?>

  <script>
  (function () {

    /* ── Remove splash after animation ── */
    var splash = document.getElementById('lp-splash');
    if (splash) {
      splash.addEventListener('animationend', function (e) {
        if (e.animationName === 'splashFadeOut') {
          splash.classList.add('hidden');
        }
      });
    }

    /* ── Scroll reveal ── */
    var revealEls = document.querySelectorAll('[data-reveal]');
    var revealObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { revealObs.observe(el); });

    /* ── Counter animation (rAF + cubic easing) ── */
    function animateCounter(el) {
      var target = parseInt(el.getAttribute('data-target'), 10);
      var duration = 1800;
      var start = performance.now();
      function tick(now) {
        var elapsed = now - start;
        var progress = Math.min(elapsed / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(eased * target).toLocaleString('ar-EG');
        if (progress < 1) requestAnimationFrame(tick);
        else el.textContent = target.toLocaleString('ar-EG');
      }
      requestAnimationFrame(tick);
    }

    var counterObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });
    document.querySelectorAll('.counter').forEach(function (el) { counterObs.observe(el); });

    /* ── Contact form feedback ── */
    var form = document.getElementById('contactForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i> تم الإرسال — سنتواصل معك قريباً';
        btn.style.background = 'linear-gradient(135deg,#198754,#28a745)';
        setTimeout(function () {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-send-fill me-2"></i> إرسال الرسالة';
          btn.style.background = '';
          form.reset();
        }, 4000);
      });
    }

  })();
  </script>

  <script>
  // Guest CTA band — يظهر فقط للزوار غير المسجلين
  (function () {
    var band = document.getElementById('guestCtaBand');
    if (!band) return;
    var token = localStorage.getItem('authority_token');
    if (!token) band.style.display = 'block';
  })();
  </script>

</body>
</html>
