<?php
$basePath  = '../../';
$pageTitle = 'بوابة ريادة الأعمال';
$activePage= 'entrepreneurship';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <style>
    /* ── TOKENS — ألوان الهيئة العامة ── */
    :root {
      --ep: #17947B; --ea: #06AA89; --ed: #062824;
      --es: #f2fbf8; --eb: rgba(23,148,123,.13);
      --em: #6b7280; --esh: 0 12px 36px rgba(6,40,36,.10);
    }
    *, *::before, *::after { box-sizing: border-box; }
    body { background: #fff; color: #1a3530; overflow-x: hidden; }
    a { text-decoration: none; color: inherit; }

    /* ── HERO ── */
    .ep-hero {
      position: relative; min-height: 92vh;
      display: flex; align-items: center;
      background: #062824 url('<?= $basePath ?>assets/images/community-entrepreneurs.jpg') center center / cover no-repeat;
      overflow: hidden;
    }
    .ep-hero::before {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(135deg,
        rgba(6,40,36,.92) 0%,
        rgba(15,79,71,.78) 45%,
        rgba(23,148,123,.55) 100%);
    }
    .ep-hero-inner {
      position: relative; z-index: 2;
      max-width: 1100px; margin: 0 auto; padding: 80px 24px;
      display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;
    }
    @media (max-width: 900px) { .ep-hero-inner { grid-template-columns: 1fr; gap: 40px; } }

    .ep-hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25);
      border-radius: 30px; padding: 7px 18px;
      font-size: .83rem; font-weight: 700; color: rgba(255,255,255,.9);
      margin-bottom: 18px;
    }
    .ep-hero h1 {
      font-size: clamp(1.9rem, 5vw, 3rem); font-weight: 900;
      color: #fff; line-height: 1.2; margin-bottom: 16px;
    }
    .ep-hero h1 span { color: var(--ea); }
    .ep-hero p {
      font-size: 1.05rem; color: rgba(255,255,255,.8);
      line-height: 1.75; margin-bottom: 32px; max-width: 480px;
    }
    .ep-hero-btns { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-ep-main {
      display: inline-flex; align-items: center; gap: 8px;
      background: #fff; color: var(--ep);
      font-weight: 800; font-size: .95rem;
      padding: 13px 28px; border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0,0,0,.2);
      transition: transform .2s, box-shadow .2s;
    }
    .btn-ep-main:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,0,0,.25); color: var(--ep); }
    .btn-ep-ghost {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.1); color: #fff;
      font-weight: 700; font-size: .93rem;
      padding: 13px 24px; border-radius: 14px;
      border: 1.5px solid rgba(255,255,255,.3);
      transition: background .2s;
    }
    .btn-ep-ghost:hover { background: rgba(255,255,255,.18); color: #fff; }

    /* Stats bubble */
    .hero-stats-card {
      background: rgba(255,255,255,.09); backdrop-filter: blur(14px);
      border: 1px solid rgba(255,255,255,.15); border-radius: 24px;
      padding: 28px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
    }
    .hs-item { text-align: center; }
    .hs-val { font-size: 2rem; font-weight: 900; color: #fff; }
    .hs-lbl { font-size: .78rem; color: rgba(255,255,255,.65); font-weight: 600; margin-top: 4px; }

    /* Scroll cue */
    .scroll-cue {
      position: absolute; bottom: 28px; left: 50%; transform: translateX(-50%);
      z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 6px;
      color: rgba(255,255,255,.5); font-size: .75rem; font-weight: 600;
      animation: bounce 2s ease infinite;
    }
    .scroll-cue i { font-size: 1.2rem; }
    @keyframes bounce { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(6px)} }

    /* ── SECTION COMMON ── */
    .ep-section { padding: 80px 0; }
    .ep-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }
    .ep-section-label {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--es); color: var(--ep);
      border-radius: 30px; padding: 6px 16px;
      font-size: .8rem; font-weight: 800; margin-bottom: 12px;
    }
    .ep-section-title { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 900; color: var(--ed); margin-bottom: 12px; }
    .ep-section-sub { font-size: 1rem; color: var(--em); line-height: 1.7; max-width: 560px; }
    .ep-section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 40px; flex-wrap: wrap; }
    .ep-see-all {
      display: inline-flex; align-items: center; gap: 6px;
      color: var(--ep); font-weight: 700; font-size: .88rem;
      border: 1.5px solid var(--eb); border-radius: 10px; padding: 8px 16px;
      transition: background .2s;
    }
    .ep-see-all:hover { background: var(--es); color: var(--ep); }

    /* ── HOW IT WORKS ── */
    .ep-how { background: var(--es); }
    .how-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0; position: relative; }
    .how-step {
      padding: 32px 24px; text-align: center; position: relative;
    }
    .how-step:not(:last-child)::after {
      content: ''; position: absolute;
      top: 52px; left: 0; width: 100%; height: 2px;
      background: linear-gradient(90deg, transparent, var(--eb), transparent);
    }
    @media (max-width: 640px) { .how-step::after { display: none; } }
    .how-num {
      width: 56px; height: 56px; border-radius: 50%;
      background: linear-gradient(135deg, var(--ep), var(--ea));
      color: #fff; font-size: 1.2rem; font-weight: 900;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px; box-shadow: 0 6px 18px rgba(23,148,123,.30);
      position: relative; z-index: 1;
    }
    .how-icon { font-size: 1.6rem; color: var(--ep); margin-bottom: 12px; display: block; }
    .how-step h3 { font-size: .97rem; font-weight: 800; color: var(--ed); margin-bottom: 6px; }
    .how-step p  { font-size: .82rem; color: var(--em); line-height: 1.6; }

    /* ── JOURNEY ── */
    .journey-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 16px; }
    .journey-card {
      background: #fff; border: 1.5px solid var(--eb);
      border-radius: 20px; padding: 24px 20px; text-align: center;
      box-shadow: var(--esh); transition: transform .2s, box-shadow .2s;
      display: block; position: relative; overflow: hidden;
    }
    .journey-card:hover { transform: translateY(-5px); box-shadow: 0 20px 48px rgba(6,40,36,.12); }
    .journey-card::before {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(180deg, transparent 70%, rgba(23,148,123,.03) 100%);
    }
    .jc-step {
      display: inline-block; padding: 3px 12px; border-radius: 20px;
      font-size: .72rem; font-weight: 800; margin-bottom: 14px;
    }
    .jc-icon {
      width: 60px; height: 60px; border-radius: 18px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; margin: 0 auto 14px;
    }
    .jc-title { font-size: .97rem; font-weight: 800; color: var(--ed); margin-bottom: 6px; }
    .jc-desc  { font-size: .78rem; color: var(--em); line-height: 1.6; }

    /* ── INCUBATORS ── */
    .ep-inc-bg { background: linear-gradient(180deg, #fff 0%, var(--es) 100%); }
    .inc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    .inc-card {
      background: #fff; border: 1px solid var(--eb);
      border-radius: 22px; overflow: hidden;
      box-shadow: var(--esh); transition: transform .2s, box-shadow .2s;
    }
    .inc-card:hover { transform: translateY(-4px); box-shadow: 0 20px 48px rgba(6,40,36,.12); }
    .inc-card-img {
      height: 160px; overflow: hidden; position: relative;
      background: linear-gradient(135deg, var(--ed), var(--ep));
    }
    .inc-card-img img { width: 100%; height: 100%; object-fit: cover; }
    .inc-card-img-ph {
      position: absolute; inset: 0;
      display: flex; align-items: center; justify-content: center;
      font-size: 3rem; color: rgba(255,255,255,.3);
    }
    .inc-sector-badge {
      position: absolute; top: 12px; right: 12px;
      background: rgba(255,255,255,.92); color: var(--ep);
      font-size: .73rem; font-weight: 800;
      padding: 4px 12px; border-radius: 20px;
    }
    .inc-card-body { padding: 18px 20px; }
    .inc-card-name { font-size: 1rem; font-weight: 800; color: var(--ed); margin-bottom: 5px; }
    .inc-card-loc  { font-size: .8rem; color: var(--em); display: flex; align-items: center; gap: 5px; margin-bottom: 12px; }
    .inc-card-stats { display: flex; gap: 12px; }
    .inc-stat { text-align: center; }
    .inc-stat-val { font-size: 1.1rem; font-weight: 800; color: var(--ep); }
    .inc-stat-lbl { font-size: .68rem; color: var(--em); font-weight: 600; }

    /* ── WHAT YOU GET ── */
    .benefits-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
    .benefit-card {
      background: #fff; border: 1px solid var(--eb);
      border-radius: 20px; padding: 28px 22px;
      box-shadow: var(--esh); position: relative; overflow: hidden;
    }
    .benefit-card::after {
      content: ''; position: absolute;
      top: 0; right: 0; width: 4px; height: 100%;
      background: linear-gradient(180deg, var(--ep), var(--ea));
      border-radius: 0 20px 20px 0;
    }
    .benefit-icon {
      width: 52px; height: 52px; border-radius: 16px;
      background: var(--es); display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; color: var(--ep); margin-bottom: 16px;
    }
    .benefit-title { font-size: .97rem; font-weight: 800; color: var(--ed); margin-bottom: 6px; }
    .benefit-desc  { font-size: .82rem; color: var(--em); line-height: 1.65; }

    /* ── SUCCESS STORIES ── */
    .ep-stories-bg { background: linear-gradient(135deg, #062824 0%, #0F4F47 100%); }
    .stories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .story-card {
      background: rgba(255,255,255,.07); backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 20px; overflow: hidden;
      transition: transform .2s, background .2s;
    }
    .story-card:hover { transform: translateY(-4px); background: rgba(255,255,255,.11); }
    .story-img { height: 150px; background: rgba(255,255,255,.08); position: relative; overflow: hidden; }
    .story-img img { width: 100%; height: 100%; object-fit: cover; }
    .story-img-ph { display: flex; align-items: center; justify-content: center; font-size: 3.5rem; height: 100%; }
    .story-body { padding: 16px 18px; }
    .story-title { font-size: .95rem; font-weight: 800; color: #fff; margin-bottom: 6px; line-height: 1.4; }
    .story-hero  { font-size: .8rem; color: rgba(255,255,255,.6); display: flex; align-items: center; gap: 5px; }
    .story-jobs  { margin-top: 10px; font-size: .78rem; font-weight: 700; color: #4ade80; display: flex; align-items: center; gap: 5px; }

    /* ── CTA BAND ── */
    .ep-cta-band {
      background: linear-gradient(135deg, var(--ep), var(--ea));
      padding: 70px 0; text-align: center; position: relative; overflow: hidden;
    }
    .ep-cta-band::before {
      content: ''; position: absolute; inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .ep-cta-band h2 { font-size: clamp(1.5rem, 3vw, 2.2rem); font-weight: 900; color: #fff; margin-bottom: 14px; position: relative; }
    .ep-cta-band p  { font-size: 1rem; color: rgba(255,255,255,.82); margin-bottom: 32px; position: relative; }
    .ep-cta-btns { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; position: relative; }
    .btn-cta-w {
      display: inline-flex; align-items: center; gap: 8px;
      background: #fff; color: var(--ep); font-weight: 800; font-size: .95rem;
      padding: 13px 30px; border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0,0,0,.15);
      transition: transform .2s;
    }
    .btn-cta-w:hover { transform: translateY(-2px); color: var(--ep); }
    .btn-cta-outline {
      display: inline-flex; align-items: center; gap: 8px;
      background: transparent; color: #fff; font-weight: 700; font-size: .93rem;
      padding: 13px 24px; border-radius: 14px;
      border: 1.5px solid rgba(255,255,255,.4);
      transition: background .2s;
    }
    .btn-cta-outline:hover { background: rgba(255,255,255,.12); color: #fff; }

    /* ── SKELETON ── */
    .skel { background: #e5e7eb; border-radius: 8px; animation: skel-pulse 1.2s ease infinite; }
    @keyframes skel-pulse { 0%,100% { opacity: 1 } 50% { opacity: .5 } }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .ep-section { padding: 56px 0; }
      .ep-hero { min-height: auto; }
      .ep-hero-inner { padding: 60px 20px; }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>

<!-- ══ HERO ══════════════════════════════════════════ -->
<section class="ep-hero">
  <div class="ep-hero-inner">
    <div>
      <div class="ep-hero-badge">
        <i class="bi bi-rocket-takeoff-fill"></i>
        بوابة ريادة الأعمال — SMEDC
      </div>
      <h1>اصنع مستقبلك الريادي<br><span>من الفكرة إلى المشروع</span></h1>
      <p>
        هيئة تنمية المشروعات ترافقك في كل خطوة — حاضنات متخصصة، مرشدون خبراء،
        تمويل مناسب، وشبكة رواد أعمال حقيقية.
      </p>
      <div class="ep-hero-btns">
        <a href="<?= $basePath ?>services/incubation/incubation-hub.php" class="btn-ep-main">
          <i class="bi bi-cloud-upload-fill"></i> ارفع مشروعك الآن
        </a>
        <a href="<?= $basePath ?>services/incubation/incubators.php" class="btn-ep-ghost">
          <i class="bi bi-building"></i> استعرض الحاضنات
        </a>
      </div>
    </div>

    <div class="hero-stats-card">
      <div class="hs-item">
        <div class="hs-val" id="hIncs">—</div>
        <div class="hs-lbl">حاضنة متخصصة</div>
      </div>
      <div class="hs-item">
        <div class="hs-val" id="hStories">—</div>
        <div class="hs-lbl">قصة نجاح</div>
      </div>
      <div class="hs-item">
        <div class="hs-val" id="hProfiles">—</div>
        <div class="hs-lbl">رائد أعمال مسجل</div>
      </div>
      <div class="hs-item">
        <div class="hs-val">مجاناً</div>
        <div class="hs-lbl">جميع الخدمات</div>
      </div>
    </div>
  </div>
  <div class="scroll-cue">
    <span>اكتشف أكثر</span>
    <i class="bi bi-chevron-double-down"></i>
  </div>
</section>

<!-- ══ HOW IT WORKS ══════════════════════════════════ -->
<section class="ep-section ep-how">
  <div class="ep-container">
    <div style="text-align:center;margin-bottom:48px">
      <div class="ep-section-label"><i class="bi bi-map-fill"></i> كيف تعمل المنظومة</div>
      <h2 class="ep-section-title" style="margin:0 auto;text-align:center">أربع خطوات من الفكرة إلى الاحتضان</h2>
    </div>
    <div class="how-steps">
      <div class="how-step">
        <div class="how-num">١</div>
        <i class="bi bi-cloud-upload-fill how-icon"></i>
        <h3>ارفع مشروعك</h3>
        <p>أرسل طلب احتضان بسيط يصف مشروعك وفكرتك — الهيئة تتكفل بالباقي</p>
      </div>
      <div class="how-step">
        <div class="how-num">٢</div>
        <i class="bi bi-search how-icon"></i>
        <h3>التقييم والمراجعة</h3>
        <p>فريق متخصص يراجع طلبك ويجري مقابلة تقييمية خلال 5-7 أيام عمل</p>
      </div>
      <div class="how-step">
        <div class="how-num">٣</div>
        <i class="bi bi-building how-icon"></i>
        <h3>الحاضنة المناسبة</h3>
        <p>الهيئة تختار لك الحاضنة والمرشد الأنسب بناءً على قطاعك ومرحلتك</p>
      </div>
      <div class="how-step">
        <div class="how-num">٤</div>
        <i class="bi bi-graph-up-arrow how-icon"></i>
        <h3>ابدأ رحلة النمو</h3>
        <p>تتبع مراحل مشروعك، اجلس مع مرشدك، وانمُ داخل منظومة الدعم الكاملة</p>
      </div>
    </div>
  </div>
</section>

<!-- ══ JOURNEY CARDS ══════════════════════════════════ -->
<section class="ep-section" style="background:#fff">
  <div class="ep-container">
    <div class="ep-section-head">
      <div>
        <div class="ep-section-label"><i class="bi bi-signpost-2-fill"></i> رحلتك الريادية</div>
        <h2 class="ep-section-title">كل ما تحتاجه في مكان واحد</h2>
        <p class="ep-section-sub">من تسجيل فكرتك حتى نجاح مشروعك — منظومة متكاملة تدعمك في كل مرحلة</p>
      </div>
    </div>
    <div class="journey-grid">
      <a href="<?= $basePath ?>services/incubation/incubation-hub.php" class="journey-card">
        <div class="jc-step" style="background:#fef3c7;color:#b45309">الخطوة ١</div>
        <div class="jc-icon" style="background:#fef3c7"><i class="bi bi-cloud-upload-fill" style="color:#b45309"></i></div>
        <div class="jc-title">ارفع طلب الاحتضان</div>
        <div class="jc-desc">نموذج بسيط عن مشروعك — الهيئة تختار لك الحاضنة المناسبة</div>
      </a>
      <a href="<?= $basePath ?>services/incubation/entrepreneur-profile.php" class="journey-card">
        <div class="jc-step" style="background:#dbeafe;color:#1d4ed8">رواد تقنيون</div>
        <div class="jc-icon" style="background:#dbeafe"><i class="bi bi-cpu-fill" style="color:#1d4ed8"></i></div>
        <div class="jc-title">رواد الأعمال التقني</div>
        <div class="jc-desc">استبيان توصيف متخصص للمشاريع التقنية والابتكارية في 14 محوراً</div>
      </a>
      <a href="<?= $basePath ?>services/incubation/incubators.php" class="journey-card">
        <div class="jc-step" style="background:rgba(23,148,123,.12);color:#17947B">الخطوة ٢</div>
        <div class="jc-icon" style="background:rgba(23,148,123,.12)"><i class="bi bi-building" style="color:#17947B"></i></div>
        <div class="jc-title">استعرض الحاضنات</div>
        <div class="jc-desc">تصفح الحاضنات المتخصصة واعرف التفاصيل والبرامج المتاحة</div>
      </a>
      <a href="<?= $basePath ?>services/incubation/incubation-hub.php" class="journey-card">
        <div class="jc-step" style="background:#dcfce7;color:#15803d">الخطوة ٣</div>
        <div class="jc-icon" style="background:#dcfce7"><i class="bi bi-speedometer2" style="color:#15803d"></i></div>
        <div class="jc-title">تابع مشروعك</div>
        <div class="jc-desc">لوحة Cloud لمتابعة مراحل مشروعك والجلسات والوثائق</div>
      </a>
      <a href="<?= $basePath ?>services/incubation/success-stories.php" class="journey-card">
        <div class="jc-step" style="background:#fce7f3;color:#be185d">إلهام</div>
        <div class="jc-icon" style="background:#fce7f3"><i class="bi bi-trophy-fill" style="color:#be185d"></i></div>
        <div class="jc-title">قصص نجاح</div>
        <div class="jc-desc">اقرأ عن رواد أعمال حوّلوا أفكارهم إلى مشاريع ناجحة</div>
      </a>
    </div>
  </div>
</section>

<!-- ══ BENEFITS ══════════════════════════════════════ -->
<section class="ep-section" style="background:var(--es)">
  <div class="ep-container">
    <div style="text-align:center;margin-bottom:48px">
      <div class="ep-section-label"><i class="bi bi-gift-fill"></i> ماذا تحصل</div>
      <h2 class="ep-section-title" style="margin:0 auto;text-align:center">منظومة دعم متكاملة لمشروعك</h2>
    </div>
    <div class="benefits-grid">
      <div class="benefit-card">
        <div class="benefit-icon"><i class="bi bi-person-badge-fill"></i></div>
        <div class="benefit-title">مرشد متخصص</div>
        <div class="benefit-desc">يُعيَّن لك مرشد خبير في قطاع مشروعك يرافقك طوال رحلة الاحتضان</div>
      </div>
      <div class="benefit-card">
        <div class="benefit-icon"><i class="bi bi-cash-coin"></i></div>
        <div class="benefit-title">دعم تمويلي</div>
        <div class="benefit-desc">إمكانية الوصول إلى برامج التمويل والشركاء الماليين عبر الهيئة</div>
      </div>
      <div class="benefit-card">
        <div class="benefit-icon"><i class="bi bi-people-fill"></i></div>
        <div class="benefit-title">شبكة علاقات</div>
        <div class="benefit-desc">انضم لمجتمع رواد الأعمال والحاضنات والشركاء الاستراتيجيين</div>
      </div>
      <div class="benefit-card">
        <div class="benefit-icon"><i class="bi bi-tools"></i></div>
        <div class="benefit-title">توجيه قانوني وإداري</div>
        <div class="benefit-desc">دعم في التسجيل، التراخيص، والإجراءات القانونية اللازمة لمشروعك</div>
      </div>
      <div class="benefit-card">
        <div class="benefit-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="benefit-title">برامج تدريبية</div>
        <div class="benefit-desc">دورات ومسارات تدريبية متخصصة لتطوير مهاراتك الريادية والتقنية</div>
      </div>
      <div class="benefit-card">
        <div class="benefit-icon"><i class="bi bi-lightning-charge-fill"></i></div>
        <div class="benefit-title">تتبع مستمر</div>
        <div class="benefit-desc">لوحة تحكم Cloud تعرض مراحل مشروعك وإنجازاتك وموعد جلساتك</div>
      </div>
    </div>
  </div>
</section>

<!-- ══ INCUBATORS ══════════════════════════════════════ -->
<section class="ep-section ep-inc-bg">
  <div class="ep-container">
    <div class="ep-section-head">
      <div>
        <div class="ep-section-label"><i class="bi bi-building"></i> الحاضنات</div>
        <h2 class="ep-section-title">الحاضنات المتاحة</h2>
        <p class="ep-section-sub">اختر من بين حاضنات متخصصة تغطي مختلف القطاعات في أنحاء سورية</p>
      </div>
      <a href="<?= $basePath ?>services/incubation/incubators.php" class="ep-see-all">
        عرض الكل <i class="bi bi-arrow-left-circle-fill"></i>
      </a>
    </div>
    <div class="inc-grid" id="incGrid">
      <?php for ($i=0;$i<3;$i++): ?>
      <div class="inc-card">
        <div class="inc-card-img"><div class="skel" style="position:absolute;inset:0;border-radius:0"></div></div>
        <div class="inc-card-body">
          <div class="skel" style="height:18px;width:60%;margin-bottom:8px"></div>
          <div class="skel" style="height:14px;width:40%;margin-bottom:16px"></div>
          <div style="display:flex;gap:16px">
            <div class="skel" style="height:32px;width:60px"></div>
            <div class="skel" style="height:32px;width:60px"></div>
          </div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ══ SUCCESS STORIES ════════════════════════════════ -->
<section class="ep-section ep-stories-bg">
  <div class="ep-container">
    <div class="ep-section-head">
      <div>
        <div class="ep-section-label" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.9)">
          <i class="bi bi-trophy-fill"></i> قصص ملهمة
        </div>
        <h2 class="ep-section-title" style="color:#fff">رواد غيّروا واقعهم</h2>
        <p class="ep-section-sub" style="color:rgba(255,255,255,.7)">قصص حقيقية من رواد أعمال احتضنتهم الهيئة وحققوا النجاح</p>
      </div>
      <a href="<?= $basePath ?>services/incubation/success-stories.php" class="ep-see-all" style="color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.2)">
        عرض الكل <i class="bi bi-arrow-left-circle-fill"></i>
      </a>
    </div>
    <div class="stories-grid" id="storiesGrid">
      <?php for ($i=0;$i<3;$i++): ?>
      <div class="story-card">
        <div class="story-img"><div class="skel" style="position:absolute;inset:0;border-radius:0;background:rgba(255,255,255,.08)"></div></div>
        <div class="story-body">
          <div class="skel" style="height:16px;width:80%;margin-bottom:8px;background:rgba(255,255,255,.1)"></div>
          <div class="skel" style="height:12px;width:50%;background:rgba(255,255,255,.1)"></div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ══ CTA BAND ════════════════════════════════════════ -->
<section class="ep-cta-band">
  <div class="ep-container">
    <h2>مستعد تحوّل فكرتك إلى مشروع حقيقي؟</h2>
    <p>انضم الآن إلى مجتمع رواد الأعمال في الهيئة — التسجيل مجاني</p>
    <div class="ep-cta-btns">
      <a href="<?= $basePath ?>services/incubation/incubation-hub.php" class="btn-cta-w">
        <i class="bi bi-cloud-upload-fill"></i> ارفع مشروعك الآن
      </a>
      <a href="<?= $basePath ?>services/incubation/entrepreneur-profile.php" class="btn-cta-outline">
        <i class="bi bi-cpu-fill"></i> استبيان تقني متخصص
      </a>
    </div>
  </div>
</section>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  await window.AppBootstrapAuth.init({ requireAuth: false });
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => {
    const t = window.AppAuth?.getToken?.();
    return t ? { Authorization:`Bearer ${t}`, Accept:'application/json' } : { Accept:'application/json' };
  };
  const E = s => String(s??'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  const SECTOR_ICON  = { tech:'bi-cpu-fill', industrial:'bi-gear-fill', agricultural:'bi-tree-fill', services:'bi-briefcase-fill', creative:'bi-palette-fill' };
  const SECTOR_LABEL = { tech:'تقنية', industrial:'صناعية', agricultural:'زراعية', services:'خدمات', creative:'إبداعية', health:'صحة', education:'تعليم' };
  const INC_IMAGES   = [
    '<?= $basePath ?>assets/images/incubators.jpg',
    '<?= $basePath ?>assets/images/incubators2.jpg',
    '<?= $basePath ?>assets/images/services.jpg',
  ];

  const [rInc, rStory, rProf] = await Promise.allSettled([
    fetch(`${BASE}/incubators?per_page=6`, { headers: H() }),
    fetch(`${BASE}/success-stories?status=published&per_page=3`, { headers: H() }),
    fetch(`${BASE}/entrepreneur/profiles/public-stats`, { headers: H() }).catch(() => null),
  ]);

  /* ── Incubators ── */
  const incGrid = document.getElementById('incGrid');
  if (rInc.status === 'fulfilled' && rInc.value.ok) {
    const d = await rInc.value.json();
    const incs = d.data || d || [];
    document.getElementById('hIncs').textContent = d.total ?? incs.length;
    incGrid.innerHTML = incs.length ? incs.map((inc, idx) => `
      <a href="<?= $basePath ?>services/incubation/incubators.php" class="inc-card" style="display:block">
        <div class="inc-card-img">
          <img src="${INC_IMAGES[idx % INC_IMAGES.length]}" alt="${E(inc.name)}" loading="lazy">
          <span class="inc-sector-badge">${SECTOR_LABEL[inc.sector] ?? inc.sector ?? 'عام'}</span>
        </div>
        <div class="inc-card-body">
          <div class="inc-card-name">${E(inc.name)}</div>
          <div class="inc-card-loc"><i class="bi bi-geo-alt-fill" style="color:var(--ep)"></i>${E(inc.governorate?.name_ar ?? inc.location ?? '—')}</div>
          <div class="inc-card-stats">
            <div class="inc-stat"><div class="inc-stat-val">${inc.active_projects_count ?? '—'}</div><div class="inc-stat-lbl">مشروع نشط</div></div>
            <div class="inc-stat"><div class="inc-stat-val">${inc.capacity ?? '—'}</div><div class="inc-stat-lbl">سعة الاحتضان</div></div>
            <div class="inc-stat"><div class="inc-stat-val">${inc.success_rate ?? '—'}${inc.success_rate ? '%' : ''}</div><div class="inc-stat-lbl">معدل نجاح</div></div>
          </div>
        </div>
      </a>`).join('')
    : '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--em)"><i class="bi bi-building" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.3"></i>لا توجد حاضنات متاحة حالياً</div>';
  } else {
    incGrid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--em)"><i class="bi bi-building" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.3"></i>تعذر تحميل الحاضنات</div>';
  }

  /* ── Success stories ── */
  const STORY_IMGS = ['<?= $basePath ?>assets/images/j1.jpg','<?= $basePath ?>assets/images/j2.jpg','<?= $basePath ?>assets/images/j3.jpg'];
  const storiesGrid = document.getElementById('storiesGrid');
  if (rStory.status === 'fulfilled' && rStory.value.ok) {
    const d = await rStory.value.json();
    const stories = d.data || d || [];
    document.getElementById('hStories').textContent = d.total ?? stories.length;
    storiesGrid.innerHTML = stories.length ? stories.map((s, idx) => `
      <div class="story-card">
        <div class="story-img">
          ${s.cover_image_url
            ? `<img src="${E(s.cover_image_url)}" alt="${E(s.title)}" loading="lazy">`
            : `<img src="${STORY_IMGS[idx % STORY_IMGS.length]}" alt="${E(s.title)}" loading="lazy">`}
        </div>
        <div class="story-body">
          <div class="story-title">${E(s.title)}</div>
          <div class="story-hero"><i class="bi bi-person-fill"></i>${E(s.hero_name ?? '')}${s.sector ? ` — ${E(s.sector)}` : ''}</div>
          ${s.jobs_created ? `<div class="story-jobs"><i class="bi bi-people-fill"></i>${s.jobs_created} فرصة عمل أُنشئت</div>` : ''}
        </div>
      </div>`).join('')
    : '<div style="grid-column:1/-1;text-align:center;padding:40px;color:rgba(255,255,255,.4)"><i class="bi bi-trophy" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>لا توجد قصص نجاح حالياً</div>';
  } else {
    storiesGrid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:rgba(255,255,255,.4)"><i class="bi bi-trophy" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>تعذر تحميل القصص</div>';
  }

  /* ── Stats ── */
  if (rProf.status === 'fulfilled' && rProf.value?.ok) {
    const s = await rProf.value.json();
    document.getElementById('hProfiles').textContent = s.total ?? '—';
  }
});
</script>
</body>
</html>
