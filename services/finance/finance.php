<?php
$basePath = '../../';
$activePage = 'finance';
$pageTitle = 'منظومة التمويل';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>
  <!-- Font Awesome fallback لضمان ظهور الأيقونات حتى لو لم تكن محملة في القالب العام -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">

  <style>
    :root{
      --fg-brand:#139D81;
      --fg-brand-2:#20B99B;
      --fg-dark:#062824;
      --fg-ink:#0F4F47;
      --fg-soft:#EAF8F4;
      --fg-soft-2:#F4FAF8;
      --fg-line:rgba(19,157,129,.16);
      --fg-muted:#64748B;
      --fg-danger:#D92D20;
      --fg-warning:#F59E0B;
      --fg-success:#16A34A;
      --fg-shadow:0 22px 60px rgba(6,40,36,.10);
      --fg-gold:#C8A45D;
      --fg-navy:#123B35;
      --fg-shadow-soft:0 14px 36px rgba(6,40,36,.07);
      --fg-radius:28px;
    }

    #finance-global-page{
      background:
        radial-gradient(circle at top left, rgba(19,157,129,.10), transparent 30%),
        linear-gradient(180deg, #ffffff 0%, #F4FAF8 45%, #ffffff 100%);
      color:var(--fg-dark);
      overflow:hidden;
    }

    #finance-global-page .fg-hero{
      position:relative;
      overflow:hidden;
      padding:96px 0 88px;
      background:
        radial-gradient(circle at 14% 18%, rgba(255,255,255,.10), transparent 27%),
        radial-gradient(circle at 82% 74%, rgba(255,255,255,.08), transparent 30%),
        linear-gradient(135deg, #062824 0%, #0F4F47 58%, #139D81 100%);
    }

    #finance-global-page .fg-hero::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px),
        linear-gradient(180deg, rgba(255,255,255,.05) 1px, transparent 1px);
      background-size:46px 46px;
      opacity:.16;
      pointer-events:none;
    }

    #finance-global-page .fg-hero::after{
      content:"";
      position:absolute;
      width:520px;
      height:520px;
      border-radius:50%;
      left:-220px;
      bottom:-250px;
      border:1px solid rgba(255,255,255,.18);
      box-shadow:0 0 0 44px rgba(255,255,255,.03), 0 0 0 90px rgba(255,255,255,.025);
      pointer-events:none;
    }

    #finance-global-page .fg-hero .container{
      position:relative;
      z-index:2;
    }

    #finance-global-page .fg-eyebrow{
      display:inline-flex;
      align-items:center;
      gap:9px;
      padding:9px 16px;
      border-radius:999px;
      color:#fff;
      background:rgba(255,255,255,.11);
      border:1px solid rgba(255,255,255,.22);
      font-weight:900;
      font-size:.9rem;
      margin-bottom:18px;
      box-shadow:inset 0 1px 0 rgba(255,255,255,.12);
    }

    #finance-global-page .fg-official-mark{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding:10px 14px;
      border-radius:18px;
      color:#fff;
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.18);
      margin-bottom:14px;
      font-weight:900;
      width:max-content;
      max-width:100%;
    }

    #finance-global-page .fg-official-mark i{
      width:36px;
      height:36px;
      border-radius:13px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:rgba(255,255,255,.14);
      color:#fff;
      flex-shrink:0;
    }

    #finance-global-page .fg-hero h1{
      color:#fff !important;
      font-size:clamp(2.25rem, 5vw, 4.35rem);
      line-height:1.28;
      font-weight:900;
      letter-spacing:-.5px;
      margin-bottom:18px;
      text-shadow:0 2px 16px rgba(0,0,0,.18);
    }

    #finance-global-page .fg-hero p{
      color:rgba(255,255,255,.92) !important;
      font-size:1.08rem;
      line-height:2;
      max-width:780px;
      margin-bottom:28px;
    }

    #finance-global-page .fg-actions{
      display:flex;
      flex-wrap:wrap;
      gap:11px;
      align-items:center;
    }

    #finance-global-page .fg-btn-primary,
    #finance-global-page .fg-btn-soft,
    #finance-global-page .fg-btn-outline{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:9px;
      min-height:48px;
      padding:12px 20px;
      border-radius:999px;
      font-weight:900;
      transition:.2s ease;
      border:1px solid transparent;
      text-decoration:none;
    }

    #finance-global-page .fg-btn-primary{
      background:linear-gradient(135deg, var(--fg-brand), var(--fg-brand-2));
      color:#fff !important;
      box-shadow:0 16px 34px rgba(19,157,129,.26);
    }

    #finance-global-page .fg-btn-primary:hover{
      transform:translateY(-2px);
      box-shadow:0 20px 42px rgba(19,157,129,.32);
    }

    #finance-global-page .fg-btn-soft{
      background:#fff;
      color:var(--fg-ink) !important;
      border-color:rgba(255,255,255,.40);
    }

    #finance-global-page .fg-btn-outline{
      color:#fff !important;
      border-color:rgba(255,255,255,.28);
      background:rgba(255,255,255,.08);
    }

    #finance-global-page .fg-dashboard-preview{
      border-radius:34px;
      border:1px solid rgba(255,255,255,.22);
      background:rgba(255,255,255,.12);
      backdrop-filter:blur(16px);
      padding:18px;
      box-shadow:0 30px 70px rgba(0,0,0,.20);
      position:relative;
    }

    #finance-global-page .fg-preview-window{
      overflow:hidden;
      border-radius:26px;
      background:#F8FCFB;
      border:1px solid rgba(255,255,255,.35);
    }

    #finance-global-page .fg-preview-office{
      display:flex;
      align-items:center;
      gap:10px;
      color:var(--fg-ink);
      font-weight:900;
      font-size:.88rem;
    }

    #finance-global-page .fg-preview-office i{
      width:34px;
      height:34px;
      border-radius:12px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:var(--fg-soft);
      color:var(--fg-brand);
    }

    #finance-global-page .fg-window-top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:13px 15px;
      border-bottom:1px solid rgba(19,157,129,.13);
      background:#fff;
    }

    #finance-global-page .fg-window-dots{
      display:flex;
      gap:6px;
    }

    #finance-global-page .fg-window-dots span{
      width:9px;
      height:9px;
      border-radius:50%;
      background:rgba(19,157,129,.35);
    }

    #finance-global-page .fg-preview-body{
      padding:16px;
    }

    #finance-global-page .fg-preview-kpis{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:12px;
      margin-bottom:14px;
    }

    #finance-global-page .fg-preview-kpi,
    #finance-global-page .fg-preview-card{
      background:#fff;
      border:1px solid var(--fg-line);
      border-radius:20px;
      padding:14px;
    }

    #finance-global-page .fg-preview-kpi span{
      display:block;
      color:var(--fg-muted);
      font-size:.78rem;
      font-weight:800;
      margin-bottom:5px;
    }

    #finance-global-page .fg-preview-kpi strong{
      color:var(--fg-dark);
      font-size:1.32rem;
      font-weight:900;
    }

    #finance-global-page .fg-preview-card{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-bottom:10px;
    }

    #finance-global-page .fg-chip{
      display:inline-flex;
      align-items:center;
      gap:7px;
      padding:7px 11px;
      border-radius:999px;
      font-size:.80rem;
      font-weight:900;
      white-space:nowrap;
    }

    #finance-global-page .fg-chip.success{
      background:rgba(22,163,74,.12);
      color:#166534;
    }

    #finance-global-page .fg-chip.warning{
      background:rgba(245,158,11,.13);
      color:#92400E;
    }

    #finance-global-page .fg-chip.danger{
      background:rgba(217,45,32,.11);
      color:#991B1B;
    }

    #finance-global-page .fg-chip.neutral{
      background:rgba(15,79,71,.09);
      color:var(--fg-ink);
    }

    #finance-global-page .fg-floating-strip{
      position:relative;
      z-index:5;
      margin-top:-42px;
    }

    #finance-global-page .fg-strip-card{
      border-radius:var(--fg-radius);
      background:#fff;
      border:1px solid var(--fg-line);
      box-shadow:var(--fg-shadow);
      padding:20px;
    }

    #finance-global-page .fg-mini-stat{
      display:flex;
      align-items:flex-start;
      gap:13px;
      padding:12px;
      border-radius:20px;
      background:#FBFEFD;
      height:100%;
    }

    #finance-global-page .fg-mini-stat i{
      width:42px;
      height:42px;
      border-radius:16px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:var(--fg-soft);
      color:var(--fg-brand);
      flex-shrink:0;
    }

    #finance-global-page .fg-mini-stat strong{
      display:block;
      color:var(--fg-dark);
      font-weight:900;
      font-size:1.08rem;
      margin-bottom:2px;
    }

    #finance-global-page .fg-mini-stat span{
      color:var(--fg-muted);
      font-size:.88rem;
      line-height:1.65;
      font-weight:700;
    }

    #finance-global-page .fg-section{
      padding:78px 0;
    }

    #finance-global-page .fg-section-title{
      max-width:820px;
      margin:0 auto 38px;
      text-align:center;
    }

    #finance-global-page .fg-label{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:7px 13px;
      border-radius:999px;
      background:var(--fg-soft);
      color:var(--fg-ink);
      font-size:.86rem;
      font-weight:900;
      margin-bottom:13px;
    }

    #finance-global-page .fg-section-title h2{
      color:var(--fg-dark);
      font-weight:900;
      font-size:clamp(1.7rem, 3vw, 2.7rem);
      margin-bottom:12px;
      line-height:1.45;
    }

    #finance-global-page .fg-section-title p{
      color:var(--fg-muted);
      line-height:1.95;
      margin:0;
      font-size:1rem;
    }

    #finance-global-page .fg-card{
      height:100%;
      background:#fff;
      border:1px solid var(--fg-line);
      border-radius:var(--fg-radius);
      padding:24px;
      box-shadow:var(--fg-shadow-soft);
      transition:.22s ease;
      position:relative;
      overflow:hidden;
    }

    #finance-global-page .fg-card:hover{
      transform:translateY(-5px);
      box-shadow:var(--fg-shadow);
    }

    #finance-global-page .fg-icon{
      width:56px;
      height:56px;
      border-radius:20px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:linear-gradient(135deg, rgba(19,157,129,.14), rgba(19,157,129,.04));
      color:var(--fg-brand);
      font-size:1.35rem;
      margin-bottom:16px;
    }

    #finance-global-page .fg-card h3{
      color:var(--fg-dark);
      font-size:1.18rem;
      font-weight:900;
      margin-bottom:9px;
    }

    #finance-global-page .fg-card p{
      color:var(--fg-muted);
      line-height:1.9;
      margin:0;
      font-size:.96rem;
    }

    #finance-global-page .fg-number-card{
      text-align:center;
      padding:24px 18px;
    }

    #finance-global-page .fg-number-card strong{
      display:block;
      color:var(--fg-dark);
      font-size:2.1rem;
      font-weight:900;
      margin-bottom:4px;
    }

    #finance-global-page .fg-number-card span{
      color:var(--fg-muted);
      font-weight:800;
      line-height:1.65;
    }

    #finance-global-page .fg-alert-card{
      background:
        linear-gradient(135deg, rgba(217,45,32,.08), rgba(255,255,255,.95));
      border-color:rgba(217,45,32,.15);
    }

    #finance-global-page .fg-flow{
      position:relative;
      display:grid;
      grid-template-columns:repeat(5,minmax(0,1fr));
      gap:14px;
    }

    #finance-global-page .fg-step{
      background:#fff;
      border:1px solid var(--fg-line);
      border-radius:24px;
      padding:20px;
      box-shadow:0 12px 30px rgba(6,40,36,.055);
      position:relative;
    }

    #finance-global-page .fg-step-num{
      width:42px;
      height:42px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border-radius:15px;
      background:var(--fg-soft);
      color:var(--fg-ink);
      font-weight:900;
      margin-bottom:12px;
    }

    #finance-global-page .fg-step h3{
      font-size:1.02rem;
      color:var(--fg-dark);
      font-weight:900;
      margin-bottom:7px;
    }

    #finance-global-page .fg-step p{
      color:var(--fg-muted);
      font-size:.9rem;
      line-height:1.8;
      margin:0;
    }

    #finance-global-page .fg-sector-panel,
    #finance-global-page .fg-consultant-panel,
    #finance-global-page .fg-cloud-panel{
      background:#fff;
      border:1px solid var(--fg-line);
      border-radius:34px;
      padding:30px;
      box-shadow:var(--fg-shadow);
    }

    #finance-global-page .fg-sector-pill{
      display:flex;
      align-items:center;
      gap:11px;
      min-height:62px;
      padding:14px 16px;
      border-radius:20px;
      background:#FBFEFD;
      border:1px solid var(--fg-line);
      font-weight:900;
      color:var(--fg-dark);
    }

    #finance-global-page .fg-sector-pill i{
      width:38px;
      height:38px;
      border-radius:14px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:var(--fg-soft);
      color:var(--fg-brand);
      flex-shrink:0;
    }

    #finance-global-page .fg-consultant-route{
      display:grid;
      gap:14px;
    }

    #finance-global-page .fg-route-row{
      display:flex;
      gap:13px;
      align-items:flex-start;
      padding:16px;
      border-radius:22px;
      background:#FBFEFD;
      border:1px solid var(--fg-line);
    }

    #finance-global-page .fg-route-row i{
      width:42px;
      height:42px;
      border-radius:15px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:#fff;
      color:var(--fg-brand);
      border:1px solid var(--fg-line);
      flex-shrink:0;
    }

    #finance-global-page .fg-route-row strong{
      display:block;
      color:var(--fg-dark);
      font-weight:900;
      margin-bottom:4px;
    }

    #finance-global-page .fg-route-row span{
      display:block;
      color:var(--fg-muted);
      line-height:1.75;
      font-size:.92rem;
    }

    #finance-global-page .fg-table-wrap{
      overflow-x:auto;
      border-radius:24px;
      border:1px solid var(--fg-line);
      background:#fff;
      box-shadow:var(--fg-shadow-soft);
    }

    #finance-global-page .fg-table{
      min-width:860px;
      margin:0;
      vertical-align:middle;
    }

    #finance-global-page .fg-table thead th{
      background:#EEF8F5;
      color:var(--fg-dark);
      font-weight:900;
      border-color:var(--fg-line);
      padding:15px;
      white-space:nowrap;
    }

    #finance-global-page .fg-table tbody td{
      padding:15px;
      border-color:rgba(19,157,129,.10);
      color:var(--fg-dark);
      font-weight:700;
    }

    #finance-global-page .fg-cloud-panel{
      background:
        radial-gradient(circle at top left, rgba(19,157,129,.12), transparent 28%),
        linear-gradient(135deg, #ffffff 0%, #F7FCFA 100%);
    }

    #finance-global-page .fg-bank-card{
      background:#fff;
      border:1px solid var(--fg-line);
      border-radius:24px;
      padding:20px;
      height:100%;
    }

    #finance-global-page .fg-bank-card .fg-match{
      width:72px;
      height:72px;
      border-radius:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      flex-direction:column;
      margin-bottom:12px;
      background:
        radial-gradient(circle at center, #fff 54%, transparent 55%),
        conic-gradient(var(--fg-brand) var(--score), #E8F3F0 0);
      border:1px solid var(--fg-line);
    }

    #finance-global-page .fg-bank-card .fg-match strong{
      font-size:1.05rem;
      line-height:1;
      color:var(--fg-dark);
      font-weight:900;
    }

    #finance-global-page .fg-bank-card .fg-match span{
      color:var(--fg-muted);
      font-size:.72rem;
      font-weight:800;
      margin-top:3px;
    }

    #finance-global-page .fg-bank-card h3{
      color:var(--fg-dark);
      font-size:1.07rem;
      font-weight:900;
      margin-bottom:6px;
    }

    #finance-global-page .fg-bank-card p{
      color:var(--fg-muted);
      line-height:1.8;
      margin:0 0 12px;
      font-size:.92rem;
    }

    #finance-global-page .fg-tags{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
    }

    #finance-global-page .fg-tag{
      padding:6px 10px;
      border-radius:999px;
      background:var(--fg-soft);
      color:var(--fg-ink);
      font-weight:900;
      font-size:.78rem;
    }

    #finance-global-page .fg-cta{
      position:relative;
      overflow:hidden;
      padding:82px 0;
      color:#fff;
      text-align:center;
      background:
        radial-gradient(circle at 15% 20%, rgba(255,255,255,.12), transparent 28%),
        linear-gradient(135deg, var(--fg-dark), var(--fg-ink) 55%, var(--fg-brand));
    }

    #finance-global-page .fg-cta h2{
      color:#fff;
      font-size:clamp(1.8rem, 4vw, 3rem);
      font-weight:900;
      line-height:1.45;
      margin-bottom:14px;
    }

    #finance-global-page .fg-cta p{
      color:rgba(255,255,255,.88);
      max-width:760px;
      margin:0 auto 26px;
      line-height:1.95;
    }



    #finance-global-page .fg-page-nav{
      position:relative;
      z-index:6;
      margin-top:18px;
    }

    #finance-global-page .fg-nav-card{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      align-items:center;
      justify-content:center;
      padding:14px;
      border-radius:24px;
      background:rgba(255,255,255,.94);
      border:1px solid var(--fg-line);
      box-shadow:0 16px 42px rgba(6,40,36,.08);
    }

    #finance-global-page .fg-nav-link{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:9px;
      min-height:44px;
      padding:10px 15px;
      border-radius:16px;
      color:var(--fg-dark) !important;
      background:#FBFEFD;
      border:1px solid rgba(19,157,129,.14);
      text-decoration:none;
      font-weight:900;
      font-size:.92rem;
      transition:.2s ease;
    }

    #finance-global-page .fg-nav-link i{
      color:var(--fg-brand);
      font-size:1rem;
    }

    #finance-global-page .fg-nav-link:hover,
    #finance-global-page .fg-nav-link.active{
      color:#fff !important;
      background:linear-gradient(135deg, var(--fg-brand), var(--fg-brand-2));
      border-color:transparent;
      transform:translateY(-2px);
      box-shadow:0 14px 30px rgba(19,157,129,.20);
    }

    #finance-global-page .fg-nav-link:hover i,
    #finance-global-page .fg-nav-link.active i{
      color:#fff;
    }

    @media (max-width:1199.98px){
      #finance-global-page .fg-flow{
        grid-template-columns:repeat(3,minmax(0,1fr));
      }
    }

    @media (max-width:991.98px){
      #finance-global-page .fg-hero{
        padding:74px 0 70px;
      }

      #finance-global-page .fg-dashboard-preview{
        margin-top:28px;
      }

      #finance-global-page .fg-flow{
        grid-template-columns:repeat(2,minmax(0,1fr));
      }
    }

    @media (max-width:767.98px){
      #finance-global-page .fg-hero h1{
        font-size:2.15rem;
      }

      #finance-global-page .fg-hero p{
        font-size:1rem;
      }

      #finance-global-page .fg-section{
        padding:58px 0;
      }

      #finance-global-page .fg-strip-card,
      #finance-global-page .fg-sector-panel,
      #finance-global-page .fg-consultant-panel,
      #finance-global-page .fg-cloud-panel{
        border-radius:20px;
        padding:18px;
      }

      #finance-global-page .fg-preview-kpis,
      #finance-global-page .fg-flow{
        grid-template-columns:1fr;
      }



      #finance-global-page .fg-nav-card{
        justify-content:stretch;
      }

      #finance-global-page .fg-nav-link{
        flex:1 1 100%;
      }

      #finance-global-page .fg-actions a{
        width:100%;
      }
    }
  </style>
</head>

<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>




<div id="finance-global-page">

  <section class="fg-hero">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <div class="fg-official-mark">
            <i class="fa-solid fa-landmark"></i>
            <span>واجهة رسمية لإدارة التمويل المؤسسي</span>
          </div>

          <div class="fg-eyebrow">
            <i class="fa-solid fa-landmark"></i>
            منظومة حكومية لتنظيم التمويل
          </div>

          <h1>منصة حكومية لإدارة مسار التمويل من التقديم حتى المتابعة</h1>

          <p>
            واجهة مؤسسية رسمية تنظّم طلبات التمويل ضمن مسار واضح يبدأ من تقديم الطلب،
            ثم ينتقل إلى مراجعة الفرع والاعتماد والتمويل، مع مؤشرات تنفيذية تساعد الإدارة والجهات التمويلية على المتابعة واتخاذ القرار.
          </p>

          <div class="fg-actions" id="financeRoleNav">
            <a href="finance-apply.php" class="fg-btn-primary" data-finance-permission="finance.applications.create">
              <i class="fa-solid fa-file-contract"></i>
              تقديم طلب تمويل
            </a>

            <a href="finance-cloud.php" class="fg-btn-soft" data-finance-permission="finance.applications.view">
              <i class="fa-solid fa-building-columns"></i>
              سحابة التمويل
            </a>

            <a href="finance-metrics.php" class="fg-btn-outline" data-finance-permission="finance.metrics.view">
              <i class="fa-solid fa-chart-simple"></i>
              المؤشرات
            </a>

            <a href="finance-funded.php" class="fg-btn-outline" data-finance-permission="finance.loans.view">
              <i class="fa-solid fa-file-invoice-dollar"></i>
              القروض الممولة
            </a>

            <a href="finance-defaulted.php" class="fg-btn-outline d-none" data-finance-permission="finance.loans.view">
              <i class="fa-solid fa-folder-minus"></i>
              القروض المتعثرة
            </a>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="fg-dashboard-preview">
            <div class="fg-preview-window">
              <div class="fg-window-top">
                <div class="fg-preview-office"><i class="fa-solid fa-building-columns"></i><strong>لوحة التمويل الحكومية</strong></div>
                <div class="fg-window-dots">
                  <span></span><span></span><span></span>
                </div>
              </div>

              <div class="fg-preview-body">
                <div class="fg-preview-kpis">
                  <div class="fg-preview-kpi">
                    <span>إجمالي الطلبات</span>
                    <strong data-finance-metric="total_applications">—</strong>
                  </div>

                  <div class="fg-preview-kpi">
                    <span>طلبات ممولة</span>
                    <strong data-finance-metric="funded_applications">—</strong>
                  </div>

                  <div class="fg-preview-kpi">
                    <span>قروض ممولة</span>
                    <strong data-finance-metric="active_loans">—</strong>
                  </div>

                  <div class="fg-preview-kpi">
                    <span>نسبة السداد</span>
                    <strong data-finance-metric="repayment_rate">—</strong>
                  </div>
                </div>

                <div class="fg-preview-card">
                  <div>
                    <strong class="d-block">طلبات قيد المعالجة</strong>
                    <small class="text-muted fw-bold">متابعة مكتبية وتمويلية</small>
                  </div>
                  <span class="fg-chip warning"><i class="fa-solid fa-clock"></i> <span data-finance-metric="pending_applications">—</span></span>
                </div>

                <div class="fg-preview-card mb-0">
                  <div>
                    <strong class="d-block">قروض متعثرة</strong>
                    <small class="text-muted fw-bold">بحاجة متابعة وإجراءات</small>
                  </div>
                  <span class="fg-chip danger"><i class="fa-solid fa-triangle-exclamation"></i> <span data-finance-metric="defaulted_loans">—</span></span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <section class="fg-floating-strip">
    <div class="container">
      <div class="fg-strip-card">
        <div class="row g-3">
          <div class="col-lg-3 col-md-6">
            <div class="fg-mini-stat">
              <i class="fa-solid fa-clipboard-check"></i>
              <div>
                <strong>مراجعة واعتماد رسمي</strong>
                <span>كل طلب يمر بمراجعة الفرع ثم اعتماد التمويل قبل السحابة.</span>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="fg-mini-stat">
              <i class="fa-solid fa-building-columns"></i>
              <div>
                <strong>إسلامي / تقليدي / معاً</strong>
                <span>مسارات تمويل واضحة بصياغة رسمية ومفهومة.</span>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="fg-mini-stat">
              <i class="fa-solid fa-folder-tree"></i>
              <div>
                <strong>مسار عرض التمويل</strong>
                <span>عرض الملفات الجاهزة للبنوك والجهات التمويلية.</span>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="fg-mini-stat">
              <i class="fa-solid fa-chart-simple"></i>
              <div>
                <strong>مؤشرات متابعة</strong>
                <span>ممول، قيد المعالجة، متعثر، ونسبة السداد.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>



  <section class="fg-page-nav" aria-label="تنقلات منظومة التمويل">
    <div class="container">
      <div class="fg-nav-card">
        <a href="finance.php" class="fg-nav-link active">
          <i class="fa-solid fa-house-chimney-window"></i>
          الرئيسية
        </a>
        <a href="finance-apply.php" class="fg-nav-link">
          <i class="fa-solid fa-file-signature"></i>
          تقديم طلب تمويل
        </a>
        <a href="finance-cloud.php" class="fg-nav-link">
          <i class="fa-solid fa-building-columns"></i>
          سحابة التمويل
        </a>
        <a href="finance-funded.php" class="fg-nav-link">
          <i class="fa-solid fa-file-invoice-dollar"></i>
          القروض الممولة
        </a>
        <a href="finance-defaulted.php" class="fg-nav-link">
          <i class="fa-solid fa-folder-minus"></i>
          القروض المتعثرة
        </a>
        <a href="finance-metrics.php" class="fg-nav-link">
          <i class="fa-solid fa-chart-simple"></i>
          المؤشرات
        </a>
      </div>
    </div>
  </section>

  <section class="fg-section" id="finance-indicators">
    <div class="container">
      <div class="fg-section-title">
        <span class="fg-label"><i class="fa-solid fa-chart-simple"></i> مؤشرات التمويل</span>
        <h2>أرقام تنفيذية واضحة للإدارة والجهات التمويلية</h2>
        <p>
          تعرض الصفحة أهم مؤشرات التمويل بشكل سريع: إجمالي الطلبات، الطلبات الممولة، الطلبات قيد المعالجة،
          والقروض المتعثرة التي تحتاج متابعة.
        </p>
      </div>

      <div class="row g-3">
        <div class="col-lg-3 col-md-6">
          <div class="fg-card fg-number-card">
            <div class="fg-icon mx-auto"><i class="fa-solid fa-folder-tree"></i></div>
            <strong data-finance-metric="total_applications">—</strong>
            <span>إجمالي الطلبات</span>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="fg-card fg-number-card">
            <div class="fg-icon mx-auto"><i class="fa-solid fa-clipboard-check"></i></div>
            <strong data-finance-metric="funded_applications">—</strong>
            <span>طلبات ممولة</span>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="fg-card fg-number-card">
            <div class="fg-icon mx-auto"><i class="fa-solid fa-business-time"></i></div>
            <strong data-finance-metric="pending_applications">—</strong>
            <span>طلبات قيد المعالجة</span>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="fg-card fg-number-card fg-alert-card">
            <div class="fg-icon mx-auto"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <strong data-finance-metric="defaulted_loans">—</strong>
            <span>قروض متعثرة بحاجة متابعة</span>
          </div>
        </div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-lg-6">
          <div class="fg-card">
            <div class="fg-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <h3>القروض الممولة</h3>
            <p>
              يتم عرض عدد القروض الممولة ونسبة السداد، مع إمكانية ربطها لاحقاً بصفحة تفصيلية تعرض القرض، المستفيد،
              الدفعات، حالة السداد، والجهة التمويلية.
            </p>
            <div class="d-flex flex-wrap gap-2 mt-3">
              <span class="fg-chip success"><i class="fa-solid fa-check"></i> 120 قرض ممول</span>
              <span class="fg-chip neutral"><i class="fa-solid fa-percent"></i> 85% نسبة السداد</span>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="fg-card fg-alert-card">
            <div class="fg-icon"><i class="fa-solid fa-file-shield"></i></div>
            <h3>القروض المتعثرة</h3>
            <p>
              لا يتم التعامل معها كرقم فقط، بل كمركز متابعة يتضمن تنبيهات، إجراءات، خطة معالجة، مواعيد تواصل،
              وحالة كل قرض حتى لا تضيع المتابعة الإدارية.
            </p>
            <div class="d-flex flex-wrap gap-2 mt-3">
              <span class="fg-chip danger"><i class="fa-solid fa-triangle-exclamation"></i> 12 قرض متعثر</span>
              <span class="fg-chip warning"><i class="fa-solid fa-phone"></i> بحاجة متابعة</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="fg-section pt-0" id="finance-journey">
    <div class="container">
      <div class="fg-section-title">
        <span class="fg-label"><i class="fa-solid fa-diagram-project"></i> رحلة التمويل</span>
        <h2>مسار مؤسسي منظم من الطلب حتى التمويل والمتابعة</h2>
        <p>
          يتم تبسيط الرحلة أمام مقدم الطلب، بينما تبقى خلفية النظام قادرة على التصنيف، الإحالة، التدقيق،
          عرض الملف للبنوك، ومتابعة القروض بعد التمويل.
        </p>
      </div>

      <div class="fg-flow">
        <div class="fg-step">
          <div class="fg-step-num">1</div>
          <h3>تقديم الطلب</h3>
          <p>اختيار نوع التمويل، طبيعة المشروع، القطاع، رمز SYRSIC، والغاية من التمويل.</p>
        </div>

        <div class="fg-step">
          <div class="fg-step-num">2</div>
          <h3>مراجعة الفرع</h3>
          <p>يتابع مدير الفرع الطلب حسب المحافظة ويحيله لمسار الاعتماد.</p>
        </div>

        <div class="fg-step">
          <div class="fg-step-num">3</div>
          <h3>اعتماد التمويل</h3>
          <p>يعتمد مدير التمويل أو المدير العام الطلب قبل ظهوره في السحابة.</p>
        </div>

        <div class="fg-step">
          <div class="fg-step-num">4</div>
          <h3>سحابة التمويل</h3>
          <p>عرض الطلب المعتمد للبنوك والجهات التمويلية حسب معايير الفرز والمطابقة.</p>
        </div>

        <div class="fg-step">
          <div class="fg-step-num">5</div>
          <h3>المتابعة</h3>
          <p>تحويل القرض إلى ممول، مراقبة السداد، ورصد التعثر عند حدوثه.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="fg-section pt-0" id="finance-types">
    <div class="container">
      <div class="fg-section-title">
        <span class="fg-label"><i class="fa-solid fa-building-columns"></i> خيارات التمويل</span>
        <h2>ثلاثة مسارات واضحة بدون إرباك للمستخدم</h2>
        <p>
          لا يتم إجبار مقدم الطلب على مسار واحد، بل يحدد ما يناسبه من البداية، ويظهر ذلك لاحقاً في صياغة الدراسة والملف.
        </p>
      </div>

      <div class="row g-3">
        <div class="col-lg-4">
          <div class="fg-card">
            <div class="fg-icon"><i class="fa-solid fa-file-contract"></i></div>
            <h3>تمويل إسلامي</h3>
            <p>مسار مناسب للطلبات التي ترغب بصيغ تمويل إسلامية مثل المرابحة أو الصيغ المتوافقة المشابهة.</p>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="fg-card">
            <div class="fg-icon"><i class="fa-solid fa-landmark"></i></div>
            <h3>تمويل تقليدي</h3>
            <p>مسار يعتمد لغة التمويل التقليدي من حيث التكلفة، الهامش، مدة السداد، والضمانات.</p>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="fg-card">
            <div class="fg-icon"><i class="fa-solid fa-folder-tree"></i></div>
            <h3>معاً</h3>
            <p>إتاحة عرض الطلب على أكثر من مسار تمويلي، ثم اختيار الأنسب بعد الدراسة والمطابقة.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="fg-section pt-0" id="syrsic">
    <div class="container">
      <div class="fg-sector-panel">
        <div class="row g-4 align-items-center">
          <div class="col-lg-5">
            <span class="fg-label"><i class="fa-solid fa-table-cells-large"></i> SYRSIC</span>
            <h2 class="fw-bold mb-3" style="font-weight:900;color:var(--fg-dark);line-height:1.45;">
              تصنيف النشاط بدقة قبل عرضه على المكاتب والبنوك
            </h2>
            <p class="text-muted mb-0" style="line-height:1.95;">
              يعتمد النظام على العائدية القطاعية للنشاط: زراعي، صناعي، تجاري، خدمي.
              أما التفاصيل مثل التعليم، الصحة، التقنية أو السياحة فتحدد عبر رمز النشاط الاقتصادي.
            </p>
          </div>

          <div class="col-lg-7">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="fg-sector-pill"><i class="fa-solid fa-seedling"></i> نشاط زراعي</div>
              </div>

              <div class="col-md-6">
                <div class="fg-sector-pill"><i class="fa-solid fa-industry"></i> نشاط صناعي</div>
              </div>

              <div class="col-md-6">
                <div class="fg-sector-pill"><i class="fa-solid fa-store"></i> نشاط تجاري</div>
              </div>

              <div class="col-md-6">
                <div class="fg-sector-pill"><i class="fa-solid fa-briefcase"></i> نشاط خدمي</div>
              </div>
            </div>

            <div class="mt-3 p-3 rounded-4 bg-white border" style="border-color:var(--fg-line)!important;">
              <strong class="d-block mb-1" style="color:var(--fg-dark)">قاعدة مهمة:</strong>
              <span class="text-muted" style="line-height:1.85;">
                التصنيف يعتمد على القطاع مع رمز SYRSIC لتحديد مسار التمويل المناسب بدقة.
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="fg-section pt-0" id="finance-cloud">
    <div class="container">
      <div class="fg-cloud-panel">
        <div class="fg-section-title mb-4">
          <span class="fg-label"><i class="fa-solid fa-building-columns"></i> سحابة التمويل</span>
          <h2>مساحة مؤسسية تربط الملفات الجاهزة بالبنوك</h2>
          <p>
            بعد استكمال الطلب ومراجعته، ينتقل الملف إلى مساحة عرض منظمة تتيح للبنوك والجهات التمويلية فرز الملفات حسب القطاع،
            نوع التمويل، قيمة التمويل، الجاهزية، مستوى المخاطر، والضمانات.
          </p>
        </div>

        <div class="row g-3">
          <div class="col-lg-4">
            <div class="fg-bank-card">
              <div class="fg-match" style="--score:92%;"><strong>92%</strong><span>مطابقة</span></div>
              <h3>شركة صناعات غذائية</h3>
              <p>طلب جاهز للتمويل، قوائم مدققة، ضمان عقاري، ونشاط قائم.</p>
              <div class="fg-tags">
                <span class="fg-tag">صناعي</span>
                <span class="fg-tag">إسلامي</span>
                <span class="fg-tag">جاهز</span>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="fg-bank-card">
              <div class="fg-match" style="--score:84%;"><strong>84%</strong><span>مطابقة</span></div>
              <h3>مشروع زراعي قائم</h3>
              <p>طلب توسعة زراعية مع إثبات نشاط وكشف حساب وضمان شخصي.</p>
              <div class="fg-tags">
                <span class="fg-tag">زراعي</span>
                <span class="fg-tag">تقليدي</span>
                <span class="fg-tag">قيد المراجعة</span>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="fg-bank-card">
              <div class="fg-match" style="--score:88%;"><strong>88%</strong><span>مطابقة</span></div>
              <h3>مركز خدمات تعليمي</h3>
              <p>مشروع خدمي قائم، دخل شهري، ترخيص متوفر، وطلب توسعة.</p>
              <div class="fg-tags">
                <span class="fg-tag">خدمي</span>
                <span class="fg-tag">معاً</span>
                <span class="fg-tag">جاهز</span>
              </div>
            </div>
          </div>
        </div>

        <div class="fg-table-wrap mt-4">
          <table class="table fg-table">
            <thead>
              <tr>
                <th>المؤشر</th>
                <th>القيمة</th>
                <th>المعنى داخل سحابة التمويل</th>
                <th>الإجراء المقترح</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>إجمالي الطلبات المتاحة</td>
                <td><strong>128 طلب</strong></td>
                <td>طلبات يمكن للبنوك استعراضها وفق الفلاتر.</td>
                <td><span class="fg-chip neutral">فرز وتحليل</span></td>
              </tr>
              <tr>
                <td>طلبات جاهزة للتمويل</td>
                <td><strong>43 طلب</strong></td>
                <td>ملفات مكتملة بعد الدراسة والتحقق.</td>
                <td><span class="fg-chip success">عرض للبنوك</span></td>
              </tr>
              <tr>
                <td>طلبات ببيانات مدققة</td>
                <td><strong>71 طلب</strong></td>
                <td>ملفات تحتوي بيانات مالية أو مرفقات قابلة للمراجعة.</td>
                <td><span class="fg-chip warning">استكمال تقييم</span></td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </section>

  <section class="fg-cta">
    <div class="container">
      <h2>منظومة حكومية لا تكتفي باستقبال الطلبات، بل تدير دورة التمويل كاملة</h2>
      <p>
        من تقديم الطلب، إلى مراجعة الفرع والاعتماد، إلى عرض الملف على الجهات التمويلية، ثم متابعة القروض الممولة والمتعثرة ضمن لوحة مؤشرات واضحة.
      </p>

      <div class="fg-actions justify-content-center">
        <a href="finance-apply.php" class="fg-btn-soft">
          <i class="fa-solid fa-file-pen"></i>
          تقديم طلب جديد
        </a>

        <a href="finance-cloud.php" class="fg-btn-outline">
          <i class="fa-solid fa-building-columns"></i>
          الدخول إلى سحابة التمويل
        </a>
      </div>
    </div>
  </section>

</div>


<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-home.js?v=1.0"></script>
</body>
</html>
