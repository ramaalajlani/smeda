<?php
$basePath = '../../';
$activePage = 'finance-cloud';
$pageTitle = 'سحابة التمويل';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>

  <!-- FontAwesome احتياطي لضمان ظهور الأيقونات في حال لم يكن محملاً من head.php -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <style>
    :root{
      --fc-brand:#139D81;
      --fc-brand-2:#20B99B;
      --fc-dark:#062824;
      --fc-ink:#0F4F47;
      --fc-soft:#EAF8F4;
      --fc-soft-2:#F6FCFA;
      --fc-line:rgba(19,157,129,.16);
      --fc-line-2:rgba(15,79,71,.10);
      --fc-muted:#64748B;
      --fc-warning:#F59E0B;
      --fc-danger:#D92D20;
      --fc-success:#16A34A;
      --fc-card:#FFFFFF;
      --fc-shadow:0 22px 60px rgba(6,40,36,.10);
      --fc-shadow-soft:0 14px 36px rgba(6,40,36,.065);
      --fc-radius:26px;
    }

    #finance-cloud-page{
      background:
        radial-gradient(circle at top left, rgba(19,157,129,.08), transparent 32%),
        linear-gradient(180deg,#ffffff 0%,#F6FCFA 46%,#ffffff 100%);
      color:var(--fc-dark);
      overflow:hidden;
    }

    #finance-cloud-page .cloud-hero{
      position:relative;
      overflow:hidden;
      padding:86px 0 118px;
      background:
        radial-gradient(circle at 15% 20%, rgba(255,255,255,.13), transparent 28%),
        linear-gradient(135deg, rgba(6,40,36,.99) 0%, rgba(15,79,71,.96) 54%, rgba(19,157,129,.90) 100%);
    }

    #finance-cloud-page .cloud-hero::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        linear-gradient(90deg, rgba(255,255,255,.055) 1px, transparent 1px),
        linear-gradient(180deg, rgba(255,255,255,.055) 1px, transparent 1px);
      background-size:48px 48px;
      opacity:.22;
      pointer-events:none;
    }

    #finance-cloud-page .cloud-hero::after{
      content:"";
      position:absolute;
      width:560px;
      height:560px;
      border-radius:50%;
      left:-250px;
      bottom:-290px;
      border:1px solid rgba(255,255,255,.18);
      box-shadow:0 0 0 48px rgba(255,255,255,.035),0 0 0 98px rgba(255,255,255,.025);
      pointer-events:none;
    }

    #finance-cloud-page .cloud-hero .container{
      position:relative;
      z-index:2;
    }

    #finance-cloud-page .gov-badge{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding:9px 15px;
      border-radius:999px;
      color:#fff;
      background:rgba(255,255,255,.13);
      border:1px solid rgba(255,255,255,.18);
      font-weight:900;
      font-size:.9rem;
      margin-bottom:18px;
    }

    #finance-cloud-page .cloud-hero h1{
      color:#fff !important;
      font-weight:900;
      font-size:clamp(2.15rem,4.5vw,3.9rem);
      margin-bottom:14px;
      line-height:1.35;
      letter-spacing:-.4px;
      text-shadow:0 2px 14px rgba(0,0,0,.20);
    }

    #finance-cloud-page .cloud-hero p{
      color:rgba(255,255,255,.92) !important;
      font-size:1.04rem;
      margin-bottom:26px;
      line-height:2;
      max-width:940px;
    }

    #finance-cloud-page .hero-actions{
      display:flex;
      flex-wrap:wrap;
      gap:11px;
      align-items:center;
    }

    #finance-cloud-page .fc-btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:9px;
      min-height:46px;
      padding:11px 18px;
      border-radius:999px;
      font-weight:900;
      text-decoration:none;
      transition:.2s ease;
      border:1px solid transparent;
      cursor:pointer;
    }

    #finance-cloud-page .fc-btn-primary,
    #finance-cloud-page .btn-brand{
      background:linear-gradient(135deg,var(--fc-brand),var(--fc-brand-2));
      color:#fff !important;
      border-color:transparent;
      box-shadow:0 14px 32px rgba(19,157,129,.24);
    }

    #finance-cloud-page .fc-btn-primary:hover,
    #finance-cloud-page .btn-brand:hover{
      transform:translateY(-2px);
      box-shadow:0 18px 42px rgba(19,157,129,.30);
      color:#fff !important;
    }

    #finance-cloud-page .fc-btn-light{
      background:#fff;
      color:var(--fc-ink) !important;
      border-color:rgba(255,255,255,.38);
    }

    #finance-cloud-page .fc-btn-outline{
      color:#fff !important;
      border-color:rgba(255,255,255,.30);
      background:rgba(255,255,255,.08);
    }

    #finance-cloud-page .cloud-shell{
      position:relative;
      z-index:5;
      margin-top:-74px;
      padding-bottom:80px;
    }

    #finance-cloud-page .cloud-nav{
      border:1px solid var(--fc-line);
      border-radius:var(--fc-radius);
      background:#fff;
      box-shadow:var(--fc-shadow);
      padding:14px;
      margin-bottom:18px;
    }

    #finance-cloud-page .cloud-nav-inner{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      align-items:center;
      justify-content:space-between;
    }

    #finance-cloud-page .nav-pills-local{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
    }

    #finance-cloud-page .nav-pills-local a{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:10px 14px;
      border-radius:999px;
      background:#F8FCFB;
      color:var(--fc-ink);
      border:1px solid var(--fc-line);
      text-decoration:none;
      font-weight:900;
      font-size:.9rem;
    }

    #finance-cloud-page .nav-pills-local a.active,
    #finance-cloud-page .nav-pills-local a:hover{
      background:var(--fc-soft);
      color:var(--fc-brand);
    }

    #finance-cloud-page .summary-card,
    #finance-cloud-page .filter-card,
    #finance-cloud-page .ui-card,
    #finance-cloud-page .office-card{
      border:1px solid var(--fc-line);
      border-radius:var(--fc-radius);
      background:#fff;
      box-shadow:var(--fc-shadow-soft);
    }

    #finance-cloud-page .summary-card{
      padding:18px;
      margin-bottom:18px;
    }

    #finance-cloud-page .summary-item{
      height:100%;
      display:flex;
      gap:12px;
      align-items:flex-start;
      padding:14px;
      border-radius:20px;
      background:#FBFEFD;
      border:1px solid var(--fc-line-2);
    }

    #finance-cloud-page .summary-icon,
    #finance-cloud-page .panel-icon{
      width:44px;
      height:44px;
      border-radius:16px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:var(--fc-soft);
      color:var(--fc-brand);
      flex-shrink:0;
      font-size:1.05rem;
    }

    #finance-cloud-page .summary-label{
      color:var(--fc-muted);
      font-weight:800;
      font-size:.82rem;
      margin-bottom:3px;
    }

    #finance-cloud-page .summary-value{
      color:var(--fc-dark);
      font-weight:900;
      font-size:1.1rem;
    }

    #finance-cloud-page .filter-card{
      padding:18px;
      position:sticky;
      top:96px;
    }

    #finance-cloud-page .filter-title,
    #finance-cloud-page .panel-title{
      display:flex;
      align-items:center;
      gap:9px;
      font-size:1.08rem;
      font-weight:900;
      color:var(--fc-dark);
      margin-bottom:8px;
    }

    #finance-cloud-page .filter-note,
    #finance-cloud-page .section-desc{
      color:var(--fc-muted);
      font-size:.92rem;
      line-height:1.9;
    }

    #finance-cloud-page .form-label{
      font-weight:900;
      color:var(--fc-dark);
      margin-bottom:.45rem;
      font-size:.9rem;
    }

    #finance-cloud-page .form-control,
    #finance-cloud-page .form-select{
      min-height:48px;
      border-radius:15px;
      border:1px solid var(--fc-line);
      background:#fff;
      color:var(--fc-dark);
      box-shadow:none;
      font-weight:700;
    }

    #finance-cloud-page .form-control:focus,
    #finance-cloud-page .form-select:focus{
      border-color:var(--fc-brand);
      box-shadow:0 0 0 .2rem rgba(19,157,129,.13);
    }

    #finance-cloud-page .filter-mini{
      margin-top:14px;
      padding:13px;
      border-radius:18px;
      background:#F8FCFB;
      border:1px dashed var(--fc-line);
      color:var(--fc-muted);
      font-size:.86rem;
      line-height:1.85;
      font-weight:700;
    }

    #finance-cloud-page .ui-card{
      padding:20px;
    }

    #finance-cloud-page .section-title{
      font-size:1.45rem;
      font-weight:900;
      color:var(--fc-dark);
      margin-bottom:6px;
      line-height:1.5;
    }

    #finance-cloud-page .cloud-toolbar{
      display:flex;
      flex-wrap:wrap;
      justify-content:space-between;
      align-items:flex-start;
      gap:14px;
      padding-bottom:16px;
      margin-bottom:18px;
      border-bottom:1px solid var(--fc-line-2);
    }

    #finance-cloud-page .view-switch{
      display:flex;
      gap:8px;
      background:#F8FCFB;
      border:1px solid var(--fc-line);
      border-radius:999px;
      padding:5px;
    }

    #finance-cloud-page .view-btn{
      border:0;
      border-radius:999px;
      background:transparent;
      color:var(--fc-muted);
      padding:8px 12px;
      font-weight:900;
      cursor:pointer;
    }

    #finance-cloud-page .view-btn.active{
      background:#fff;
      color:var(--fc-brand);
      box-shadow:0 6px 18px rgba(6,40,36,.08);
    }

    #finance-cloud-page .status-chip{
      display:inline-flex;
      align-items:center;
      gap:7px;
      padding:7px 12px;
      border-radius:999px;
      font-size:.80rem;
      font-weight:900;
      white-space:nowrap;
    }

    #finance-cloud-page .status-chip.ready{background:rgba(22,163,74,.12);color:#166534;}
    #finance-cloud-page .status-chip.pending{background:rgba(245,158,11,.13);color:#92400e;}
    #finance-cloud-page .status-chip.review{background:rgba(14,165,233,.12);color:#075985;}
    #finance-cloud-page .status-chip.neutral{background:rgba(15,79,71,.09);color:var(--fc-ink);}
    #finance-cloud-page .status-chip.danger{background:rgba(217,45,32,.10);color:#991B1B;}

    #finance-cloud-page .applicant-grid{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:18px;
    }

    #finance-cloud-page .applicant-card{
      border:1px solid var(--fc-line);
      border-radius:24px;
      background:#fff;
      padding:18px;
      transition:.22s ease;
      position:relative;
      overflow:hidden;
      box-shadow:0 12px 32px rgba(6,40,36,.055);
    }

    #finance-cloud-page .applicant-card::before{
      content:"";
      position:absolute;
      top:0;
      right:0;
      width:5px;
      height:100%;
      background:linear-gradient(180deg,var(--fc-brand),var(--fc-brand-2));
    }

    #finance-cloud-page .applicant-card:hover{
      transform:translateY(-4px);
      box-shadow:var(--fc-shadow);
    }

    #finance-cloud-page .applicant-head{
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:12px;
      margin-bottom:12px;
    }

    #finance-cloud-page .applicant-name{
      font-size:1.12rem;
      font-weight:900;
      color:var(--fc-dark);
      margin-bottom:5px;
      line-height:1.55;
    }

    #finance-cloud-page .applicant-meta{
      color:var(--fc-muted);
      font-size:.87rem;
      line-height:1.75;
      font-weight:700;
    }

    #finance-cloud-page .match-circle{
      min-width:76px;
      width:76px;
      height:76px;
      border-radius:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      flex-direction:column;
      background:
        radial-gradient(circle at center,#fff 55%,transparent 56%),
        conic-gradient(var(--fc-brand) var(--score),#E8F3F0 0);
      border:1px solid var(--fc-line);
    }

    #finance-cloud-page .match-circle strong{
      color:var(--fc-dark);
      font-size:1.05rem;
      font-weight:900;
      line-height:1;
    }

    #finance-cloud-page .match-circle span{
      color:var(--fc-muted);
      font-size:.70rem;
      font-weight:800;
      margin-top:3px;
    }

    #finance-cloud-page .info-grid{
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      gap:10px;
      margin:14px 0;
    }

    #finance-cloud-page .info-box{
      padding:12px;
      border-radius:16px;
      background:#F8FCFB;
      border:1px solid var(--fc-line-2);
    }

    #finance-cloud-page .info-box span{
      display:block;
      color:var(--fc-muted);
      font-size:.76rem;
      font-weight:800;
      margin-bottom:4px;
    }

    #finance-cloud-page .info-box strong{
      display:block;
      color:var(--fc-dark);
      font-size:.89rem;
      font-weight:900;
      line-height:1.6;
    }

    #finance-cloud-page .tag-list{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin:14px 0;
    }

    #finance-cloud-page .tag{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:6px 10px;
      border-radius:999px;
      background:var(--fc-soft);
      color:var(--fc-ink);
      font-size:.78rem;
      font-weight:900;
    }

    #finance-cloud-page .risk-row{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:10px;
      margin-top:12px;
    }

    #finance-cloud-page .risk-item{
      padding:11px 12px;
      border-radius:15px;
      background:#FBFEFD;
      border:1px solid var(--fc-line-2);
      color:var(--fc-muted);
      font-size:.83rem;
      line-height:1.75;
      font-weight:700;
    }

    #finance-cloud-page .risk-item strong{
      color:var(--fc-dark);
      font-weight:900;
    }

    #finance-cloud-page .applicant-actions{
      display:flex;
      flex-wrap:wrap;
      justify-content:space-between;
      align-items:center;
      gap:9px;
      margin-top:15px;
      padding-top:14px;
      border-top:1px solid var(--fc-line-2);
    }

    #finance-cloud-page .applicant-actions .btn{
      border-radius:999px;
      font-weight:900;
      padding:.45rem .85rem;
    }

    #finance-cloud-page .table-view{
      display:none;
    }

    #finance-cloud-page .cloud-table-wrap{
      overflow-x:auto;
      border:1px solid var(--fc-line);
      border-radius:22px;
      background:#fff;
    }

    #finance-cloud-page .cloud-table{
      min-width:980px;
      margin:0;
      vertical-align:middle;
    }

    #finance-cloud-page .cloud-table thead th{
      background:#EEF8F5;
      color:var(--fc-dark);
      font-weight:900;
      padding:14px;
      border-color:var(--fc-line);
      white-space:nowrap;
    }

    #finance-cloud-page .cloud-table tbody td{
      padding:14px;
      border-color:var(--fc-line-2);
      color:var(--fc-dark);
      font-weight:800;
    }

    #finance-cloud-page .empty-note{
      display:none;
      padding:24px;
      text-align:center;
      border-radius:20px;
      border:1px dashed rgba(19,157,129,.28);
      background:#F7FCFA;
      color:var(--fc-muted);
      font-weight:800;
      line-height:1.9;
    }

    #finance-cloud-page .office-card{
      padding:20px;
      margin-top:18px;
    }

    #finance-cloud-page .office-grid{
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      gap:14px;
    }

    #finance-cloud-page .office-step{
      display:flex;
      gap:12px;
      align-items:flex-start;
      padding:15px;
      border-radius:20px;
      background:#FBFEFD;
      border:1px solid var(--fc-line-2);
      height:100%;
    }

    #finance-cloud-page .office-step i{
      width:42px;
      height:42px;
      border-radius:15px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:var(--fc-soft);
      color:var(--fc-brand);
      flex-shrink:0;
    }

    #finance-cloud-page .office-step strong{
      display:block;
      color:var(--fc-dark);
      font-weight:900;
      margin-bottom:4px;
    }

    #finance-cloud-page .office-step span{
      display:block;
      color:var(--fc-muted);
      font-size:.87rem;
      line-height:1.75;
      font-weight:700;
    }

    /* Modal */
    #fileModalBackdrop{
      position:fixed;
      inset:0;
      background:rgba(6,40,36,.58);
      backdrop-filter:blur(5px);
      z-index:1050;
      opacity:0;
      visibility:hidden;
      transition:.2s ease;
    }

    #fileModalBackdrop.show{
      opacity:1;
      visibility:visible;
    }

    #fileModal{
      position:fixed;
      inset:24px;
      max-width:1180px;
      margin:auto;
      height:calc(100vh - 48px);
      background:#fff;
      border-radius:28px;
      z-index:1051;
      box-shadow:0 30px 90px rgba(0,0,0,.25);
      opacity:0;
      visibility:hidden;
      transform:translateY(18px) scale(.985);
      transition:.2s ease;
      display:flex;
      flex-direction:column;
      overflow:hidden;
      border:1px solid rgba(255,255,255,.60);
    }

    #fileModal.show{
      opacity:1;
      visibility:visible;
      transform:translateY(0) scale(1);
    }

    #finance-cloud-page .modal-gov-head{
      padding:18px 20px;
      background:
        radial-gradient(circle at top left, rgba(255,255,255,.12), transparent 28%),
        linear-gradient(135deg,var(--fc-dark),var(--fc-ink) 56%,var(--fc-brand));
      color:#fff;
    }

    #finance-cloud-page .modal-head-row{
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:14px;
    }

    #finance-cloud-page .modal-title{
      font-size:1.35rem;
      font-weight:900;
      color:#fff;
      margin:0 0 5px;
      line-height:1.45;
    }

    #finance-cloud-page .modal-subtitle{
      color:rgba(255,255,255,.84);
      font-weight:700;
      line-height:1.7;
      margin:0;
    }

    #finance-cloud-page .modal-close-btn{
      width:42px;
      height:42px;
      border-radius:14px;
      border:1px solid rgba(255,255,255,.25);
      background:rgba(255,255,255,.12);
      color:#fff;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      flex-shrink:0;
    }

    #finance-cloud-page .modal-body-scroll{
      padding:18px;
      overflow:auto;
    }

    #finance-cloud-page .file-tabs{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-bottom:16px;
      position:sticky;
      top:-18px;
      z-index:4;
      background:#fff;
      padding:10px 0;
      border-bottom:1px solid var(--fc-line-2);
    }

    #finance-cloud-page .file-tab{
      border:1px solid var(--fc-line);
      background:#F8FCFB;
      color:var(--fc-ink);
      border-radius:999px;
      padding:9px 13px;
      font-weight:900;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      gap:7px;
      font-size:.88rem;
    }

    #finance-cloud-page .file-tab.active{
      background:var(--fc-soft);
      color:var(--fc-brand);
      border-color:rgba(19,157,129,.24);
    }

    #finance-cloud-page .file-pane{
      display:none;
    }

    #finance-cloud-page .file-pane.active{
      display:block;
    }

    #finance-cloud-page .detail-card{
      border:1px solid var(--fc-line);
      border-radius:22px;
      background:#fff;
      box-shadow:0 12px 30px rgba(6,40,36,.045);
      padding:18px;
      height:100%;
    }

    #finance-cloud-page .detail-card h4{
      display:flex;
      align-items:center;
      gap:9px;
      color:var(--fc-dark);
      font-size:1.05rem;
      font-weight:900;
      margin-bottom:14px;
    }

    #finance-cloud-page .detail-list{
      display:grid;
      gap:10px;
    }

    #finance-cloud-page .detail-row{
      display:grid;
      grid-template-columns:170px 1fr;
      gap:10px;
      padding:11px 12px;
      border-radius:15px;
      background:#F8FCFB;
      border:1px solid var(--fc-line-2);
    }

    #finance-cloud-page .detail-row span{
      color:var(--fc-muted);
      font-weight:900;
      font-size:.84rem;
    }

    #finance-cloud-page .detail-row strong{
      color:var(--fc-dark);
      font-weight:900;
      line-height:1.7;
    }

    #finance-cloud-page .journey{
      display:grid;
      grid-template-columns:repeat(5,minmax(0,1fr));
      gap:12px;
    }

    #finance-cloud-page .journey-step{
      padding:15px;
      border-radius:20px;
      border:1px solid var(--fc-line);
      background:#FBFEFD;
      position:relative;
      min-height:150px;
    }

    #finance-cloud-page .journey-step .num{
      width:38px;
      height:38px;
      border-radius:14px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:var(--fc-soft);
      color:var(--fc-brand);
      font-weight:900;
      margin-bottom:10px;
    }

    #finance-cloud-page .journey-step strong{
      display:block;
      color:var(--fc-dark);
      font-weight:900;
      margin-bottom:6px;
    }

    #finance-cloud-page .journey-step span{
      display:block;
      color:var(--fc-muted);
      font-size:.86rem;
      line-height:1.75;
      font-weight:700;
    }

    #finance-cloud-page .doc-list{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:12px;
    }

    #finance-cloud-page .doc-item{
      display:flex;
      align-items:flex-start;
      gap:12px;
      padding:14px;
      border-radius:18px;
      border:1px solid var(--fc-line);
      background:#FBFEFD;
    }

    #finance-cloud-page .doc-item i{
      width:40px;
      height:40px;
      border-radius:14px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      color:var(--fc-brand);
      background:var(--fc-soft);
      flex-shrink:0;
    }

    #finance-cloud-page .doc-item strong{
      display:block;
      color:var(--fc-dark);
      font-weight:900;
      margin-bottom:3px;
    }

    #finance-cloud-page .doc-item span{
      color:var(--fc-muted);
      font-size:.84rem;
      line-height:1.7;
      font-weight:700;
    }

    #finance-cloud-page .modal-footer-actions{
      padding:14px 18px;
      border-top:1px solid var(--fc-line);
      background:#FBFEFD;
      display:flex;
      flex-wrap:wrap;
      justify-content:space-between;
      gap:10px;
    }

    @media(max-width:1199.98px){
      #finance-cloud-page .applicant-grid,
      #finance-cloud-page .office-grid{
        grid-template-columns:1fr;
      }

      #finance-cloud-page .journey{
        grid-template-columns:repeat(2,minmax(0,1fr));
      }
    }

    @media(max-width:991.98px){
      #finance-cloud-page .filter-card{
        position:static;
        margin-bottom:18px;
      }
    }

    @media(max-width:767.98px){
      #finance-cloud-page .cloud-hero{
        padding:68px 0 96px;
      }

      #finance-cloud-page .cloud-shell{
        margin-top:-56px;
      }

      #finance-cloud-page .cloud-nav,
      #finance-cloud-page .summary-card,
      #finance-cloud-page .filter-card,
      #finance-cloud-page .ui-card,
      #finance-cloud-page .office-card{
        border-radius:18px;
      }

      #finance-cloud-page .hero-actions a,
      #finance-cloud-page .hero-actions button,
      #finance-cloud-page .applicant-actions .btn,
      #finance-cloud-page .modal-footer-actions .btn{
        width:100%;
      }

      #finance-cloud-page .info-grid,
      #finance-cloud-page .risk-row,
      #finance-cloud-page .doc-list,
      #finance-cloud-page .journey{
        grid-template-columns:1fr;
      }

      #finance-cloud-page .applicant-head{
        flex-direction:column;
      }

      #fileModal{
        inset:8px;
        height:calc(100vh - 16px);
        border-radius:18px;
      }

      #finance-cloud-page .detail-row{
        grid-template-columns:1fr;
      }
    }
  </style>
</head>

<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>




<div id="finance-cloud-page">

  <section class="cloud-hero">
    <div class="container">
      <div class="gov-badge">
        <i class="fa-solid fa-building-columns"></i>
        بوابة مؤسسية لإدارة فرص التمويل
      </div>

      <h1>سحابة التمويل للطلبات الجاهزة</h1>

      <p>
        واجهة حكومية منظمة تجمع ملفات طالبي التمويل المعتمدة بعد المراجعة،
        وتعرضها للجهات التمويلية ضمن بطاقات قابلة للفرز والفتح والتقييم، مع رحلة واضحة للمستخدم،
        وملف طلب كامل يشبه نموذج تقديم التمويل من حيث البيانات والمرفقات والمؤشرات.
      </p>

      <div class="hero-actions">
        <a href="finance-apply.php" class="fc-btn fc-btn-primary">
          <i class="fa-solid fa-file-signature"></i>
          تقديم طلب تمويل
        </a>

        <a href="finance.php" class="fc-btn fc-btn-light">
          <i class="fa-solid fa-house-chimney-window"></i>
          العودة لمنظومة التمويل
        </a>

        <a href="finance-metrics.php" class="fc-btn fc-btn-outline">
          <i class="fa-solid fa-chart-simple"></i>
          مؤشرات التمويل
        </a>
      </div>
    </div>
  </section>

  <section class="section cloud-shell">
    <div class="container">

      <nav class="cloud-nav">
        <div class="cloud-nav-inner">
          <div class="nav-pills-local">
            <a href="finance.php"><i class="fa-solid fa-landmark"></i> الرئيسية</a>
            <a href="finance-apply.php"><i class="fa-solid fa-file-pen"></i> تقديم طلب</a>
            <a href="finance-cloud.php" class="active"><i class="fa-solid fa-folder-tree"></i> سحابة التمويل</a>
            <a href="finance-funded.php"><i class="fa-solid fa-hand-holding-dollar"></i> القروض الممولة</a>
            <a href="finance-defaulted.php"><i class="fa-solid fa-triangle-exclamation"></i> القروض المتعثرة</a>
            <a href="finance-metrics.php"><i class="fa-solid fa-chart-pie"></i> المؤشرات</a>
          </div>

          <span class="status-chip neutral">
            <i class="fa-solid fa-shield-halved"></i>
            عرض بيانات تمويلية لأغراض الفرز والتقييم
          </span>
        </div>
      </nav>

      <div class="summary-card">
        <div class="row g-3">
          <div class="col-lg-3 col-md-6">
            <div class="summary-item">
              <div class="summary-icon"><i class="fa-solid fa-folder-open"></i></div>
              <div>
                <div class="summary-label">إجمالي الطلبات المتاحة</div>
                <div class="summary-value" id="totalRequests">128 طلب</div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="summary-item">
              <div class="summary-icon"><i class="fa-solid fa-file-circle-check"></i></div>
              <div>
                <div class="summary-label">طلبات جاهزة للتمويل</div>
                <div class="summary-value">43 طلب</div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="summary-item">
              <div class="summary-icon"><i class="fa-solid fa-scale-balanced"></i></div>
              <div>
                <div class="summary-label">متوسط المطابقة</div>
                <div class="summary-value">82%</div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="summary-item">
              <div class="summary-icon"><i class="fa-solid fa-building-columns"></i></div>
              <div>
                <div class="summary-label">جهات تمويل مستهدفة</div>
                <div class="summary-value">بنوك وشركاء</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">

        <div class="col-lg-4 col-xl-3">
          <aside class="filter-card">
            <div class="filter-title">
              <span class="panel-icon"><i class="fa-solid fa-filter-circle-dollar"></i></span>
              فلترة الفرص
            </div>

            <p class="filter-note">
              الفلاتر تساعد الجهة التمويلية على الوصول إلى الملفات الأقرب لسياساتها حسب القطاع ونوع التمويل والجاهزية والمخاطر.
            </p>

            <div class="mb-3">
              <label class="form-label">بحث عام</label>
              <input type="text" class="form-control cloud-filter" id="searchInput" placeholder="اسم مقدم الطلب / المشروع / المحافظة">
            </div>

            <div class="mb-3">
              <label class="form-label">القطاع حسب التصنيف</label>
              <select class="form-select cloud-filter" id="sectorFilter">
                <option value="">كل القطاعات</option>
                <option value="agricultural">زراعي</option>
                <option value="industrial">صناعي</option>
                <option value="commercial">تجاري</option>
                <option value="service">خدمي</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">نوع التمويل</label>
              <select class="form-select cloud-filter" id="financeModeFilter">
                <option value="">كل الأنواع</option>
                <option value="islamic">إسلامي</option>
                <option value="conventional">تقليدي</option>
                <option value="both">إسلامي / تقليدي</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">حالة المشروع</label>
              <select class="form-select cloud-filter" id="projectStatusFilter">
                <option value="">كل الحالات</option>
                <option value="existing">قائم</option>
                <option value="new">قيد التأسيس</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">جاهزية الملف</label>
              <select class="form-select cloud-filter" id="readinessFilter">
                <option value="">كل الملفات</option>
                <option value="ready">جاهز للتمويل</option>
                <option value="review">قيد المراجعة</option>
                <option value="pending">بحاجة استكمال</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">مستوى المخاطر</label>
              <select class="form-select cloud-filter" id="riskFilter">
                <option value="">كل المستويات</option>
                <option value="low">منخفض</option>
                <option value="medium">متوسط</option>
                <option value="high">مرتفع</option>
              </select>
            </div>

            <div class="d-grid gap-2 mt-4">
              <button type="button" class="btn btn-brand" id="applyFiltersBtn">
                <i class="fa-solid fa-magnifying-glass-chart"></i>
                تطبيق الفلاتر
              </button>
              <button type="button" class="btn btn-light border" id="resetFiltersBtn">
                <i class="fa-solid fa-rotate-left"></i>
                إعادة ضبط
              </button>
            </div>

            <div class="filter-mini">
              <i class="fa-solid fa-circle-info"></i>
              زر “فتح الملف” يعرض نموذجاً كاملاً لبيانات الطلب، رحلة المستخدم، المرفقات، والتقييم الأولي.
            </div>
          </aside>
        </div>

        <div class="col-lg-8 col-xl-9">
          <section class="ui-card">

            <div class="cloud-toolbar">
              <div>
                <h2 class="section-title">ملفات التمويل المرشحة للجهات التمويلية</h2>
                <p class="section-desc mb-0">
                  كل بطاقة تمثل ملفاً معتمداً جاهزاً للفرز من الجهات التمويلية، ويمكن فتح الملف للاطلاع على نموذج الطلب الكامل ورحلة المعالجة.
                </p>
              </div>

              <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="status-chip neutral" id="visibleCount">6 طلبات معروضة</span>
                <div class="view-switch">
                  <button class="view-btn active" type="button" data-view="cards"><i class="fa-solid fa-grip"></i></button>
                  <button class="view-btn" type="button" data-view="table"><i class="fa-solid fa-table-list"></i></button>
                </div>
              </div>
            </div>

            <div class="applicant-grid" id="applicantGrid"></div>

            <div class="table-view" id="tableView">
              <div class="cloud-table-wrap">
                <table class="table cloud-table">
                  <thead>
                    <tr>
                      <th>الملف</th>
                      <th>القطاع</th>
                      <th>نوع التمويل</th>
                      <th>قيمة التمويل</th>
                      <th>الجاهزية</th>
                      <th>المطابقة</th>
                      <th>الإجراء</th>
                    </tr>
                  </thead>
                  <tbody id="tableBody"></tbody>
                </table>
              </div>
            </div>

            <div class="empty-note" id="emptyNote">
              <i class="fa-solid fa-folder-minus d-block mb-2 fs-3"></i>
              لا توجد ملفات مطابقة للفلاتر الحالية. جرّب تعديل القطاع أو نوع التمويل أو حالة الجاهزية.
            </div>

          </section>

          <section class="office-card">
            <div class="cloud-toolbar mb-2 pb-2">
              <div>
                <h2 class="section-title mb-1">رحلة المستخدم داخل سحابة التمويل</h2>
                <p class="section-desc mb-0">
                  هذا المسار يوضح أين يبدأ الطلب، وكيف يُراجع داخل الهيئة ثم يُعرض على الجهات التمويلية بعد الاعتماد.
                </p>
              </div>
            </div>

            <div class="office-grid">
              <div class="office-step">
                <i class="fa-solid fa-file-pen"></i>
                <div>
                  <strong>1. تعبئة الطلب</strong>
                  <span>مقدم الطلب يدخل بيانات المشروع والقطاع ونوع التمويل والغاية والمرفقات الأولية.</span>
                </div>
              </div>

              <div class="office-step">
                <i class="fa-solid fa-clipboard-check"></i>
                <div>
                  <strong>2. مراجعة واعتماد</strong>
                  <span>يراجع مدير الفرع الطلب حسب المحافظة، ثم يعتمده مدير التمويل أو المدير العام.</span>
                </div>
              </div>

              <div class="office-step">
                <i class="fa-solid fa-folder-tree"></i>
                <div>
                  <strong>3. عرض في سحابة التمويل</strong>
                  <span>بعد الاعتماد يصبح الملف قابلاً للفرز والفتح من الجهات التمويلية.</span>
                </div>
              </div>
            </div>
          </section>
        </div>

      </div>
    </div>
  </section>

  <div id="fileModalBackdrop"></div>

  <section id="fileModal" aria-hidden="true">
    <div class="modal-gov-head">
      <div class="modal-head-row">
        <div>
          <h3 class="modal-title" id="modalTitle">ملف التمويل</h3>
          <p class="modal-subtitle" id="modalSubtitle">بيانات تفصيلية</p>
        </div>

        <button type="button" class="modal-close-btn" id="closeModalBtn" aria-label="إغلاق">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body-scroll">
      <div class="file-tabs">
        <button class="file-tab active" type="button" data-pane="overviewPane"><i class="fa-solid fa-id-card-clip"></i> ملخص الملف</button>
        <button class="file-tab" type="button" data-pane="applicationPane"><i class="fa-solid fa-file-lines"></i> نموذج الطلب</button>
        <button class="file-tab" type="button" data-pane="journeyPane"><i class="fa-solid fa-route"></i> رحلة المستخدم</button>
        <button class="file-tab" type="button" data-pane="documentsPane"><i class="fa-solid fa-folder-open"></i> المرفقات</button>
        <button class="file-tab" type="button" data-pane="evaluationPane"><i class="fa-solid fa-scale-balanced"></i> التقييم</button>
      </div>

      <div class="file-pane active" id="overviewPane"></div>
      <div class="file-pane" id="applicationPane"></div>
      <div class="file-pane" id="journeyPane"></div>
      <div class="file-pane" id="documentsPane"></div>
      <div class="file-pane" id="evaluationPane"></div>
    </div>

    <div class="modal-footer-actions">
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-brand" id="markInternalBtn">
          <i class="fa-solid fa-bookmark"></i>
          حفظ الملف كفرصة مهمة
        </button>
        <button type="button" class="btn btn-light border" id="requestContactBtn">
          <i class="fa-solid fa-building-circle-arrow-right"></i>
          طلب تواصل رسمي
        </button>
      </div>

      <button type="button" class="btn btn-outline-secondary" id="printFileBtn">
        <i class="fa-solid fa-print"></i>
        طباعة ملخص الملف
      </button>
    </div>
  </section>

</div>


<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<div id="financeCloudMessage" class="alert d-none container mt-3"></div>
<div id="financeCloudLoading" class="text-center py-3 d-none">جاري تحميل الطلبات...</div>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.3"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-cloud.js?v=1.4"></script>

</body>
</html>
