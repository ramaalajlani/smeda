<?php
$basePath = '../../';
$activePage = 'finance';
$pageTitle = 'طلب تمويل';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>

  <style>
    #finance-apply-page .finance-hero{
      position:relative;
      overflow:hidden;
      padding:92px 0 82px;
      background:
        linear-gradient(135deg, rgba(6,40,36,.96) 0%, rgba(15,79,71,.92) 52%, rgba(6,170,137,.88) 100%);
    }

    #finance-apply-page .finance-hero::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(circle at top right, rgba(255,255,255,.10), transparent 32%),
        radial-gradient(circle at bottom left, rgba(255,255,255,.06), transparent 28%);
      pointer-events:none;
    }

    #finance-apply-page .finance-hero .container{
      position:relative;
      z-index:2;
    }

    #finance-apply-page .finance-hero h1{
      color:#fff !important;
      font-weight:800;
      font-size:clamp(2rem,4vw,3.1rem);
      margin-bottom:.75rem;
      text-shadow:0 2px 10px rgba(0,0,0,.22);
    }

    #finance-apply-page .finance-hero p{
      color:rgba(255,255,255,.92) !important;
      font-size:1.04rem;
      margin-bottom:0;
      line-height:1.9;
      max-width:900px;
      text-shadow:0 2px 8px rgba(0,0,0,.18);
    }

    #finance-apply-page .roadmap-shell{
      position:relative;
      z-index:4;
      margin-top:-44px;
    }

    #finance-apply-page .ui-card,
    #finance-apply-page .roadmap-card,
    #finance-apply-page .summary-card{
      border:1px solid rgba(23,148,123,.10);
      border-radius:22px;
      background:#fff;
      box-shadow:0 18px 45px rgba(6,40,36,.07);
    }

    #finance-apply-page .summary-card{
      padding:1.1rem 1.2rem;
      margin-bottom:1.2rem;
    }

    #finance-apply-page .summary-label{
      font-size:.88rem;
      color:#64748b;
      margin-bottom:.25rem;
      font-weight:700;
    }

    #finance-apply-page .summary-value{
      font-size:1.02rem;
      color:#062824;
      font-weight:800;
    }

    #finance-apply-page .progress-track{
      width:100%;
      height:10px;
      background:#e8f3f0;
      border-radius:999px;
      overflow:hidden;
      margin-top:.75rem;
    }

    #finance-apply-page .progress-bar-custom{
      height:100%;
      width:11%;
      border-radius:999px;
      background:linear-gradient(90deg, #17947B 0%, #06AA89 100%);
      transition:width .25s ease;
    }

    #finance-apply-page .roadmap-card{
      padding:1.15rem;
      position:sticky;
      top:96px;
    }

    #finance-apply-page .roadmap-title{
      font-size:1.08rem;
      font-weight:800;
      color:#062824;
      margin-bottom:.95rem;
    }

    #finance-apply-page .roadmap-note{
      color:#64748b;
      font-size:.88rem;
      line-height:1.85;
      margin-bottom:1rem;
    }

    #finance-apply-page .roadmap-list{
      list-style:none;
      margin:0;
      padding:0;
      display:flex;
      flex-direction:column;
      gap:12px;
    }

    #finance-apply-page .roadmap-item{
      display:flex;
      gap:12px;
      align-items:flex-start;
      padding:12px;
      border:1px solid rgba(23,148,123,.10);
      border-radius:16px;
      background:#fbfefd;
      cursor:pointer;
      transition:.2s ease;
    }

    #finance-apply-page .roadmap-item.active{
      border-color:rgba(23,148,123,.34);
      background:linear-gradient(180deg, rgba(23,148,123,.08) 0%, rgba(6,170,137,.04) 100%);
      box-shadow:inset 0 0 0 1px rgba(23,148,123,.08);
    }

    #finance-apply-page .roadmap-item.done{
      border-color:rgba(22,163,74,.18);
      background:rgba(22,163,74,.05);
    }

    #finance-apply-page .roadmap-item.locked{
      opacity:.72;
      cursor:not-allowed;
    }

    #finance-apply-page .roadmap-badge{
      min-width:36px;
      width:36px;
      height:36px;
      border-radius:12px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      font-size:.92rem;
      font-weight:800;
      color:#0F4F47;
      background:#eaf8f4;
      border:1px solid rgba(23,148,123,.18);
      flex-shrink:0;
    }

    #finance-apply-page .roadmap-item.active .roadmap-badge{
      background:#17947B;
      border-color:#17947B;
      color:#fff;
    }

    #finance-apply-page .roadmap-item.done .roadmap-badge{
      background:#16a34a;
      border-color:#16a34a;
      color:#fff;
    }

    #finance-apply-page .roadmap-text strong{
      display:block;
      color:#062824;
      font-size:.96rem;
      font-weight:800;
      margin-bottom:2px;
    }

    #finance-apply-page .roadmap-text span{
      display:block;
      color:#64748b;
      font-size:.845rem;
      line-height:1.7;
    }

    #finance-apply-page .ui-card{
      padding:1.35rem;
    }

    #finance-apply-page .step-pane{
      display:none;
    }

    #finance-apply-page .step-pane.active{
      display:block;
      animation:fadeStep .22s ease;
    }

    @keyframes fadeStep{
      from{opacity:0;transform:translateY(6px)}
      to{opacity:1;transform:translateY(0)}
    }

    #finance-apply-page .step-head{
      margin-bottom:1.35rem;
      padding-bottom:1rem;
      border-bottom:1px solid rgba(23,148,123,.10);
    }

    #finance-apply-page .step-overline{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:6px 12px;
      border-radius:999px;
      background:rgba(23,148,123,.08);
      color:#0F4F47;
      font-size:.82rem;
      font-weight:800;
      margin-bottom:.75rem;
    }

    #finance-apply-page .step-title{
      font-size:1.45rem;
      font-weight:800;
      color:#062824;
      margin-bottom:.45rem;
    }

    #finance-apply-page .step-desc{
      color:#526173;
      font-size:.98rem;
      line-height:1.9;
      margin-bottom:.8rem;
    }

    #finance-apply-page .step-checklist{
      margin:0;
      padding:0;
      list-style:none;
      display:grid;
      gap:7px;
    }

    #finance-apply-page .step-checklist li{
      position:relative;
      padding-right:18px;
      color:#0F4F47;
      font-size:.91rem;
      font-weight:700;
      line-height:1.8;
    }

    #finance-apply-page .step-checklist li::before{
      content:"•";
      position:absolute;
      right:0;
      top:0;
      color:#17947B;
      font-size:1.1rem;
      line-height:1;
    }

    #finance-apply-page .form-section-title{
      font-size:1.12rem;
      font-weight:800;
      color:#062824;
      margin-bottom:1rem;
      padding-bottom:.65rem;
      border-bottom:1px solid rgba(23,148,123,.10);
    }

    #finance-apply-page .form-label{
      font-weight:800;
      color:#062824;
      margin-bottom:.55rem;
    }

    #finance-apply-page .req-mark{
      display:inline-flex;
      align-items:center;
      margin-inline-start:6px;
      padding:2px 8px;
      border-radius:999px;
      background:rgba(217,45,32,.1);
      color:#b42318;
      font-size:.72rem;
      font-weight:800;
      vertical-align:middle;
    }

    #finance-apply-page .is-invalid-field,
    #finance-apply-page .form-check-wrap.is-invalid-field{
      border-color:#f97066 !important;
      box-shadow:0 0 0 .15rem rgba(249,112,102,.18);
    }

    #finance-apply-page .ai-help-note{
      display:flex;
      flex-wrap:wrap;
      align-items:center;
      gap:10px;
      margin-top:8px;
      padding:10px 12px;
      border-radius:12px;
      background:#f0f7ff;
      border:1px solid rgba(91,141,255,.22);
      color:#1e3a5f;
      font-size:.86rem;
      font-weight:700;
      line-height:1.7;
    }

    #finance-apply-page .ai-help-note .btn{
      white-space:nowrap;
    }

    #finance-apply-page .form-control,
    #finance-apply-page .form-select{
      min-height:48px;
      border-radius:14px;
      border:1px solid rgba(23,148,123,.16);
      background:#fff;
      color:#062824;
      box-shadow:none;
    }

    #finance-apply-page textarea.form-control{
      min-height:120px;
      resize:vertical;
    }

    #finance-apply-page .form-control:focus,
    #finance-apply-page .form-select:focus{
      border-color:#17947B;
      box-shadow:0 0 0 .2rem rgba(23,148,123,.14);
    }

    #finance-apply-page .helper-note{
      display:block;
      margin-top:6px;
      font-size:.875rem;
      color:#64748b;
      line-height:1.8;
    }

    #finance-apply-page .field-hint{
      display:block;
      margin-top:4px;
      color:#94a3b8;
      font-size:.83rem;
      line-height:1.75;
    }

    #finance-apply-page .form-check-wrap{
      display:flex;
      flex-wrap:wrap;
      gap:10px 18px;
      padding:12px 14px;
      border:1px solid rgba(23,148,123,.12);
      border-radius:14px;
      background:#f8fcfb;
      min-height:48px;
      align-items:center;
    }

    #finance-apply-page .form-check{
      margin:0;
      display:flex;
      align-items:center;
      gap:6px;
    }

    #finance-apply-page .form-check-input{
      margin:0;
      border-color:rgba(23,148,123,.35);
    }

    #finance-apply-page .form-check-input:checked{
      background-color:#17947B;
      border-color:#17947B;
    }

    #finance-apply-page .form-check-label{
      font-weight:700;
      color:#0F4F47;
      cursor:pointer;
    }

    #finance-apply-page .info-alert{
      border-radius:16px;
      padding:14px 16px;
      background:#f7fcfa;
      border:1px solid rgba(23,148,123,.14);
      color:#0F4F47;
      line-height:1.9;
      font-size:.92rem;
      margin-bottom:1rem;
    }

    #finance-apply-page .info-alert strong{
      color:#062824;
    }

    #finance-apply-page .status-chip{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:7px 12px;
      border-radius:999px;
      font-size:.82rem;
      font-weight:800;
    }

    #finance-apply-page .status-chip.pending{
      background:rgba(245,158,11,.12);
      color:#92400e;
    }

    #finance-apply-page .status-chip.ready{
      background:rgba(22,163,74,.12);
      color:#166534;
    }

    #finance-apply-page .status-chip.neutral{
      background:rgba(15,79,71,.08);
      color:#0F4F47;
    }

    #finance-apply-page .upload-box{
      padding:16px;
      border-radius:16px;
      background:#f7fcfa;
      border:1px dashed rgba(23,148,123,.22);
      height:100%;
    }

    #finance-apply-page .upload-box .form-label{
      font-size:.95rem;
      line-height:1.8;
    }

    #finance-apply-page .financial-table-wrap{
      overflow-x:auto;
      -webkit-overflow-scrolling:touch;
      border:1px solid rgba(23,148,123,.10);
      border-radius:18px;
      background:#fff;
      padding:0;
    }

    #finance-apply-page .financial-table{
      width:100%;
      min-width:0;
      margin:0;
      vertical-align:middle;
      table-layout:auto;
    }

    #finance-apply-page .financial-table thead th{
      background:#eef8f5;
      color:#062824;
      font-weight:800;
      white-space:nowrap;
      border-color:rgba(23,148,123,.10);
      text-align:center;
      font-size:.88rem;
      padding:.75rem .55rem;
    }

    #finance-apply-page .financial-table tbody td,
    #finance-apply-page .financial-table tbody th{
      border-color:rgba(23,148,123,.08);
      vertical-align:middle;
      padding:.55rem;
    }

    #finance-apply-page .financial-table tbody th{
      color:#0F4F47;
      font-weight:800;
      white-space:normal;
      min-width:160px;
      max-width:220px;
      background:#fcfefd;
      font-size:.86rem;
      line-height:1.55;
      text-align:start;
    }

    #finance-apply-page .financial-table input.form-control,
    #finance-apply-page .financial-table select.form-select{
      min-width:0;
      width:100%;
      min-height:44px;
      font-size:.9rem;
      border-radius:12px;
      padding:.45rem .7rem;
    }

    #finance-apply-page .financial-table input.form-control{
      text-align:center;
    }

    #finance-apply-page .financial-table .year-col{
      min-width:110px;
      width:14%;
    }

    #finance-apply-page .financial-table .audit-col{
      min-width:120px;
      width:16%;
    }

    #finance-apply-page .financial-table .notes-col{
      min-width:140px;
      width:18%;
    }

    @media (max-width:991.98px){
      #finance-apply-page .financial-table,
      #finance-apply-page .financial-table thead,
      #finance-apply-page .financial-table tbody,
      #finance-apply-page .financial-table tr,
      #finance-apply-page .financial-table th,
      #finance-apply-page .financial-table td{
        display:block;
        width:100%;
      }

      #finance-apply-page .financial-table{
        min-width:0;
      }

      #finance-apply-page .financial-table thead{
        display:none;
      }

      #finance-apply-page .financial-table tbody tr{
        border-bottom:1px solid rgba(23,148,123,.12);
        padding:14px 12px;
        background:#fff;
      }

      #finance-apply-page .financial-table tbody tr:nth-child(even){
        background:#f8fcfb;
      }

      #finance-apply-page .financial-table tbody th{
        max-width:none;
        min-width:0;
        background:transparent;
        border:0;
        padding:0 0 10px;
        font-size:.95rem;
      }

      #finance-apply-page .financial-table tbody td{
        border:0;
        padding:6px 0;
      }

      #finance-apply-page .financial-table tbody td::before{
        content:attr(data-label);
        display:block;
        font-size:.78rem;
        font-weight:800;
        color:#64748b;
        margin-bottom:4px;
      }

      #finance-apply-page .financial-table .year-col,
      #finance-apply-page .financial-table .audit-col,
      #finance-apply-page .financial-table .notes-col{
        min-width:0;
        width:100%;
      }
    }

    #finance-apply-page .step-actions{
      display:flex;
      flex-wrap:wrap;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-top:1.65rem;
      padding-top:1rem;
      border-top:1px solid rgba(23,148,123,.10);
    }

    #finance-apply-page .step-actions .btn-group-custom{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
    }

    #finance-apply-page .review-list{
      display:grid;
      gap:12px;
      margin:0;
      padding:0;
      list-style:none;
    }

    #finance-apply-page .review-list li{
      padding:14px 16px;
      border-radius:16px;
      background:#f8fcfb;
      border:1px solid rgba(23,148,123,.10);
      color:#062824;
      font-weight:700;
      line-height:1.85;
    }

    #finance-apply-page .mini-grid{
      display:grid;
      grid-template-columns:repeat(4, minmax(0,1fr));
      gap:14px;
    }

    #finance-apply-page .mini-card{
      border:1px solid rgba(23,148,123,.10);
      border-radius:16px;
      background:#fbfefd;
      padding:14px;
      height:100%;
    }

    #finance-apply-page .mini-card strong{
      display:block;
      color:#062824;
      margin-bottom:4px;
      font-weight:800;
      font-size:.95rem;
    }

    #finance-apply-page .mini-card span{
      color:#64748b;
      font-size:.87rem;
      line-height:1.75;
      display:block;
    }

    /* Consultant Offices Linking */
    #finance-apply-page .consultant-link-card{
      border:1px solid rgba(23,148,123,.12);
      border-radius:20px;
      background:linear-gradient(180deg, #ffffff 0%, #f8fcfb 100%);
      padding:1rem;
      margin-top:1.15rem;
    }

    #finance-apply-page .consultant-link-head{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:14px;
      margin-bottom:1rem;
      padding-bottom:.85rem;
      border-bottom:1px solid rgba(23,148,123,.10);
    }

    #finance-apply-page .consultant-link-title{
      display:flex;
      align-items:center;
      gap:10px;
      color:#062824;
      font-weight:800;
      font-size:1.08rem;
      margin-bottom:.25rem;
    }

    #finance-apply-page .consultant-link-title i{
      width:38px;
      height:38px;
      border-radius:13px;
      background:rgba(23,148,123,.10);
      color:#17947B;
      display:inline-flex;
      align-items:center;
      justify-content:center;
    }

    #finance-apply-page .consultant-link-desc{
      color:#64748b;
      font-size:.9rem;
      line-height:1.85;
      margin:0;
    }

    #finance-apply-page .office-match-grid{
      display:grid;
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:12px;
      margin-top:1rem;
    }

    #finance-apply-page .office-match-card{
      border:1px solid rgba(23,148,123,.12);
      border-radius:16px;
      background:#fff;
      padding:14px;
      height:100%;
    }

    #finance-apply-page .office-match-card strong{
      display:block;
      color:#062824;
      font-weight:800;
      margin-bottom:5px;
      font-size:.95rem;
    }

    #finance-apply-page .office-match-card span{
      display:block;
      color:#64748b;
      font-size:.84rem;
      line-height:1.75;
    }

    #finance-apply-page .office-rule-box{
      border-radius:16px;
      padding:14px 16px;
      background:rgba(23,148,123,.07);
      border:1px dashed rgba(23,148,123,.26);
      color:#0F4F47;
      font-size:.9rem;
      line-height:1.9;
      margin-top:1rem;
    }

    #finance-apply-page .office-status-line{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-top:.75rem;
    }

    #finance-apply-page .office-status-line .status-chip{
      white-space:nowrap;
    }


    /* Official comfortable interface refinements */
    #finance-apply-page{
      background:#f4faf8;
    }

    #finance-apply-page .finance-hero{
      padding:76px 0 70px;
      background:
        linear-gradient(135deg, rgba(6,40,36,.98) 0%, rgba(15,79,71,.94) 58%, rgba(19,157,129,.86) 100%);
    }

    #finance-apply-page .summary-card{
      background:rgba(255,255,255,.98);
      border:1px solid rgba(19,157,129,.14);
      box-shadow:0 16px 38px rgba(6,40,36,.075);
    }

    #finance-apply-page .ui-card,
    #finance-apply-page .roadmap-card{
      border-color:rgba(19,157,129,.13);
      box-shadow:0 14px 34px rgba(6,40,36,.065);
    }

    #finance-apply-page .step-head{
      background:linear-gradient(180deg, #ffffff 0%, #f8fcfb 100%);
      border:1px solid rgba(19,157,129,.10);
      border-radius:20px;
      padding:1rem 1.1rem;
      margin-bottom:1.35rem;
    }

    #finance-apply-page .form-label{
      font-size:.93rem;
    }

    #finance-apply-page .form-control,
    #finance-apply-page .form-select{
      background:#fff;
      border-color:rgba(19,157,129,.18);
    }

    #finance-apply-page .form-control[readonly]{
      background:#f8fcfb;
      color:#0F4F47;
      font-weight:800;
    }

    #finance-apply-page .syrsic-sector-note{
      border:1px solid rgba(19,157,129,.13);
      border-radius:18px;
      padding:14px 16px;
      background:#f8fcfb;
      color:#0F4F47;
      line-height:1.9;
      font-size:.91rem;
    }

    #finance-apply-page .syrsic-sector-pills{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-top:.65rem;
    }

    #finance-apply-page .syrsic-sector-pill{
      display:inline-flex;
      align-items:center;
      gap:7px;
      padding:7px 11px;
      border-radius:999px;
      background:#fff;
      border:1px solid rgba(19,157,129,.18);
      color:#0F4F47;
      font-size:.82rem;
      font-weight:800;
      white-space:nowrap;
    }

    #finance-apply-page .consultant-link-card{
      background:linear-gradient(180deg, #ffffff 0%, #f7fbfa 100%);
      border-color:rgba(19,157,129,.16);
      box-shadow:0 12px 28px rgba(6,40,36,.055);
    }

    #finance-apply-page .office-match-grid{
      grid-template-columns:repeat(4, minmax(0, 1fr));
    }


    @media (max-width:1199.98px){
      #finance-apply-page .mini-grid{
        grid-template-columns:repeat(2, minmax(0,1fr));
      }

      #finance-apply-page .office-match-grid{
        grid-template-columns:repeat(2, minmax(0,1fr));
      }
    }

    @media (max-width:991.98px){
      #finance-apply-page .roadmap-card{
        position:static;
        margin-bottom:1rem;
      }

      #finance-apply-page .roadmap-shell{
        margin-top:-28px;
      }

      #finance-apply-page .roadmap-shell > .container{
        padding-inline:12px;
      }
    }

    @media (max-width:767.98px){
      #finance-apply-page .finance-hero{
        padding:56px 0 48px;
      }

      #finance-apply-page .finance-hero h1{
        font-size:clamp(1.55rem, 7vw, 2rem);
      }

      #finance-apply-page .finance-hero p{
        font-size:.92rem;
        line-height:1.75;
      }

      #finance-apply-page .ui-card,
      #finance-apply-page .roadmap-card,
      #finance-apply-page .summary-card{
        border-radius:16px;
      }

      #finance-apply-page .ui-card{
        padding:1rem .9rem;
      }

      #finance-apply-page .summary-card{
        padding:.95rem .9rem;
      }

      #finance-apply-page .summary-card .row > [class*="col-"]{
        flex:0 0 50%;
        max-width:50%;
      }

      #finance-apply-page .summary-label{
        font-size:.78rem;
      }

      #finance-apply-page .summary-value{
        font-size:.95rem;
      }

      #finance-apply-page .step-head{
        padding:.85rem .9rem;
        border-radius:16px;
      }

      #finance-apply-page .form-check-wrap{
        flex-direction:column;
        align-items:flex-start;
      }

      #finance-apply-page .step-actions{
        flex-direction:column;
        align-items:stretch;
      }

      #finance-apply-page .step-actions .btn-group-custom{
        width:100%;
      }

      #finance-apply-page .step-actions .btn{
        width:100%;
      }

      #finance-apply-page .mini-grid{
        grid-template-columns:1fr;
      }

      #finance-apply-page .consultant-link-head{
        flex-direction:column;
      }

      #finance-apply-page .office-match-grid{
        grid-template-columns:1fr;
      }

      #finance-apply-page .form-control,
      #finance-apply-page .form-select{
        font-size:16px;
        min-height:46px;
      }

      #finance-apply-page textarea.form-control{
        min-height:96px;
      }

      #finance-apply-page .roadmap-list{
        display:grid;
        grid-auto-flow:column;
        grid-auto-columns:minmax(210px, 78vw);
        overflow-x:auto;
        gap:10px;
        padding-bottom:6px;
        scroll-snap-type:x mandatory;
        -webkit-overflow-scrolling:touch;
      }

      #finance-apply-page .roadmap-item{
        scroll-snap-align:start;
        min-height:100%;
      }
    }

    @media (max-width:575.98px){
      #finance-apply-page .summary-card .row > [class*="col-"]{
        flex:0 0 100%;
        max-width:100%;
      }

      #finance-apply-page .roadmap-shell{
        margin-top:-18px;
      }

      #finance-apply-page .step-badge,
      #finance-apply-page .step-chip{
        font-size:.78rem;
      }
    }
  </style>
</head>
<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>




<div id="finance-apply-page">

  <section class="finance-hero">
    <div class="container">
      <h1>طلب تمويل</h1>
      <p>
        صُممت هذه الرحلة ليقوم مقدم الطلب بإدخال بياناته وبيانات مشروعه واحتياجه التمويلي،
        ثم تتولى المنظومة بعد الإرسال مراجعة الملف ومتابعته ضمن مسار التمويل المعتمد.
      </p>
    </div>
  </section>

  <section class="section roadmap-shell">
    <div class="container">

      <div class="summary-card">
        <div class="row g-3 align-items-center">
          <div class="col-lg-3 col-md-6">
            <div class="summary-label">حالة الطلب</div>
            <div class="summary-value" id="applicationStatusLabel">مسودة</div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="summary-label">المرحلة الحالية</div>
            <div class="summary-value" id="currentStepLabel">تعريف الطلب</div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="summary-label">نسبة الإنجاز</div>
            <div class="summary-value"><span id="progressText">11%</span></div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="summary-label">حالة الجاهزية</div>
            <div class="summary-value" id="readinessStatusLabel">قيد الاستكمال</div>
          </div>
        </div>
        <div class="progress-track">
          <div class="progress-bar-custom" id="progressBar"></div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-4 col-xl-3">
          <aside class="roadmap-card">
            <div class="roadmap-title">خارطة الرحلة</div>
            <p class="roadmap-note">
              أكمل المراحل بالترتيب. الحقول الأساسية مطلوبة قبل حفظ المسودة أو إرسال الطلب.
            </p>

            <ul class="roadmap-list" id="roadmapList">
              <li class="roadmap-item active" data-step="1" data-title="تعريف الطلب">
                <div class="roadmap-badge">1</div>
                <div class="roadmap-text">
                  <strong>تعريف الطلب</strong>
                  <span>نوع التمويل، حالة المشروع، وقطاع النشاط.</span>
                </div>
              </li>

              <li class="roadmap-item locked" data-step="2" data-title="بيانات مقدم الطلب">
                <div class="roadmap-badge">2</div>
                <div class="roadmap-text">
                  <strong>بيانات مقدم الطلب</strong>
                  <span>الاسم، المحافظة، وبيانات التواصل.</span>
                </div>
              </li>

              <li class="roadmap-item locked" data-step="3" data-title="بيانات التمويل">
                <div class="roadmap-badge">3</div>
                <div class="roadmap-text">
                  <strong>بيانات التمويل</strong>
                  <span>الغاية، السقف، العملة، الهيكل المقترح للسداد.</span>
                </div>
              </li>

              <li class="roadmap-item locked" data-step="4" data-title="مرحلة المشروع والخبرة">
                <div class="roadmap-badge">4</div>
                <div class="roadmap-text">
                  <strong>مرحلة المشروع والخبرة</strong>
                  <span>مرحلة العمل، الخبرة، السوق والتحديات.</span>
                </div>
              </li>

              <li class="roadmap-item locked" data-step="5" data-title="النشاط والفواتير">
                <div class="roadmap-badge">5</div>
                <div class="roadmap-text">
                  <strong>النشاط والفواتير</strong>
                  <span>إثبات النشاط وبيانات الشركة والفواتير والمستندات.</span>
                </div>
              </li>

              <li class="roadmap-item locked" data-step="6" data-title="البيانات المالية">
                <div class="roadmap-badge">6</div>
                <div class="roadmap-text">
                  <strong>البيانات المالية</strong>
                  <span>الميزانية وقائمة الدخل بشكل منظم وقابل للمراجعة.</span>
                </div>
              </li>

              <li class="roadmap-item locked" data-step="7" data-title="العمالة والتأهيل">
                <div class="roadmap-badge">7</div>
                <div class="roadmap-text">
                  <strong>العمالة والتأهيل</strong>
                  <span>القوى العاملة، المؤهلات، والتوزيع العددي.</span>
                </div>
              </li>

              <li class="roadmap-item locked" data-step="8" data-title="الاحتياجات التدريبية">
                <div class="roadmap-badge">8</div>
                <div class="roadmap-text">
                  <strong>الاحتياجات التدريبية</strong>
                  <span>الفجوات التدريبية المطلوبة للإداريين والفنيين والعمال.</span>
                </div>
              </li>

              <li class="roadmap-item locked" data-step="9" data-title="المراجعة والإرسال">
                <div class="roadmap-badge">9</div>
                <div class="roadmap-text">
                  <strong>المراجعة والإرسال</strong>
                  <span>فحص اكتمال الملف قبل الإرسال الرسمي.</span>
                </div>
              </li>
            </ul>
          </aside>
        </div>

        <div class="col-lg-8 col-xl-9">
            <div id="financeApplyMessage" class="alert d-none mb-3"></div>
          <form class="ui-card" id="fundingApplicationForm" method="post" enctype="multipart/form-data">

            <!-- STEP 1 -->
            <section class="step-pane active" data-step-pane="1">
              <div class="step-head">
                <div class="step-overline">الخطوة 1 من 9</div>
                <h2 class="step-title">تعريف الطلب</h2>
                <p class="step-desc">
                  في هذه المرحلة يتم تحديد الإطار العام للطلب. هذه الاختيارات تؤثر على بقية الرحلة، وعلى شكل
                  البيانات المالية والمرفقات والاعتمادات المطلوبة لاحقًا.
                </p>
                <ul class="step-checklist">
                  <li>حدد نوع التمويل: إسلامي أو تقليدي أو معاً.</li>
                  <li>حدد ما إذا كان المشروع قائمًا أو غير قائم.</li>
                  <li>اختر القطاع الأقرب لطبيعة النشاط.</li>
                </ul>
              </div>

              <div class="mini-grid mb-4">
                <div class="mini-card">
                  <strong>تمويل إسلامي</strong>
                  <span>يظهر لاحقًا ضمنه هيكل مالي متوافق مع طبيعة التمويل الإسلامي والمرابحة أو الصياغات المشابهة.</span>
                </div>
                <div class="mini-card">
                  <strong>تمويل تقليدي</strong>
                  <span>يستخدم لغة تمويل تقليدي من حيث الهامش أو التكلفة أو بنية السداد المقترحة.</span>
                </div>
                <div class="mini-card">
                  <strong>مشروع قائم</strong>
                  <span>يُتوقع معه وجود نشاط سابق وبيانات مالية أو فواتير أو إثباتات تشغيل.</span>
                </div>
                <div class="mini-card">
                  <strong>مشروع غير قائم</strong>
                  <span>يركز أكثر على الجدوى والتقديرات والخبرة والاستعداد للتنفيذ.</span>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">نوع التمويل <span class="req-mark">مطلوب</span></label>
                  <div class="form-check-wrap" data-required-group="financing_mode">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="financing_mode" id="financingModeIslamic" value="islamic" required>
                      <label class="form-check-label" for="financingModeIslamic">إسلامي</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="financing_mode" id="financingModeConventional" value="conventional">
                      <label class="form-check-label" for="financingModeConventional">تقليدي</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="financing_mode" id="financingModeBoth" value="both">
                      <label class="form-check-label" for="financingModeBoth">معاً</label>
                    </div>
                  </div>
                  <span class="helper-note">هذا الاختيار يحدد اللغة المالية الظاهرة في المراحل التالية.</span>
                </div>

                <div class="col-md-6">
                  <label class="form-label">طبيعة المشروع <span class="req-mark">مطلوب</span></label>
                  <div class="form-check-wrap" data-required-group="project_status">
                    <div class="form-check">
                      <input class="form-check-input project-status-radio" type="radio" name="project_status" id="projectStatusExisting" value="existing" required>
                      <label class="form-check-label" for="projectStatusExisting">قائم</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input project-status-radio" type="radio" name="project_status" id="projectStatusNew" value="new">
                      <label class="form-check-label" for="projectStatusNew">غير قائم</label>
                    </div>
                  </div>
                  <span class="helper-note">المشروع القائم يحتاج عادةً بيانات تشغيلية تاريخية، بينما غير القائم يركز على الجاهزية والدراسة.</span>
                </div>

                <div class="col-md-6">
                  <label class="form-label">القطاع الاقتصادي حسب SYRSIC <span class="req-mark">مطلوب</span></label>
                  <select name="project_sector" id="projectSector" class="form-select" required>
                    <option value="">اختر القطاع</option>
                    <option value="agricultural">نشاط زراعي</option>
                    <option value="industrial">نشاط صناعي</option>
                    <option value="commercial">نشاط تجاري</option>
                    <option value="service">نشاط خدمي</option>
                  </select>
                  <span class="field-hint">يعتمد الاختيار هنا على العائدية القطاعية في التصنيف الوطني للأنشطة الاقتصادية SYRSIC.</span>
                </div>

                <div class="col-md-6">
                  <label class="form-label">رمز النشاط الاقتصادي SYRSIC <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="syrsic_activity_code" id="syrsicActivityCode" class="form-control" placeholder="مثال: 011303 أو 620101" required>
                  <div class="ai-help-note">
                    <span>يمكنك الاستعانة بالمستشار الذكي لتحديد رمز النشاط حسب SYRSIC بدقة.</span>
                    <button type="button" class="btn btn-sm btn-brand" id="openAiForSyrsicBtn">
                      <i class="bi bi-robot"></i> فتح المستشار الذكي
                    </button>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">اسم المشروع / النشاط <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="project_name" id="projectName" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">حجم المشروع <span class="req-mark">مطلوب</span></label>
                  <select name="project_size" id="projectSize" class="form-select" required>
                    <option value="micro">متناهي الصغر</option>
                    <option value="small" selected>صغير</option>
                    <option value="medium">متوسط</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">وصف موجز للمشروع <span class="req-mark">مطلوب</span></label>
                  <textarea name="description" id="projectDescription" class="form-control" rows="3" placeholder="صف النشاط والهدف من التمويل باختصار" required></textarea>
                </div>

                <div class="col-md-6">
                  <label class="form-label">ملخص المسار المتوقع</label>
                  <input type="text" id="expectedPathSummary" class="form-control" value="سيتم توليده لاحقًا وفق الاختيارات" readonly>
                  <span class="field-hint">مثال: تمويل إسلامي أو تقليدي أو معاً / مشروع قائم / نشاط صناعي / رمز SYRSIC.</span>
                </div>
              </div>
            </section>

            <!-- STEP 2 -->
            <section class="step-pane" data-step-pane="2">
              <div class="step-head">
                <div class="step-overline">الخطوة 2 من 9</div>
                <h2 class="step-title">بيانات مقدم الطلب</h2>
                <p class="step-desc">
                  تُستخدم هذه البيانات في تعريف الملف رسميًا، وربطه بصاحبه أو بالجهة المتقدمة بالطلب.
                  يجب أن تكون المعلومات واضحة ومتسقة مع الصفة القانونية المستخدمة في المعاملة.
                </p>
                <ul class="step-checklist">
                  <li>أدخل الاسم الكامل لمقدم الطلب.</li>
                  <li>حدد الصفة القانونية أو شكل التقدم.</li>
                  <li>أدخل وسيلة تواصل مباشرة وواضحة.</li>
                </ul>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">الاسم الثلاثي لطالب التمويل <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="applicant_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">رقم التواصل <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="phone" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">البريد الإلكتروني <span class="req-mark">مطلوب</span></label>
                  <input type="email" name="email" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">الرقم الوطني <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="national_id" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">المحافظة <span class="req-mark">مطلوب</span></label>
                  <select name="governorate_id" id="governorateId" class="form-select" required>
                    <option value="">اختر المحافظة</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">الفرع</label>
                  <select name="branch_id" id="branchId" class="form-select">
                    <option value="">يُحدَّد تلقائياً حسب المحافظة</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">الصفة القانونية <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="legal_status" class="form-control" placeholder="فرد - مؤسسة - شركة - شراكة ..." required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">المهنة <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="profession" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">الجنسية السورية <span class="req-mark">مطلوب</span></label>
                  <select name="syrian_nationality" class="form-select" required>
                    <option value="">اختر</option>
                    <option value="yes">نعم</option>
                    <option value="no">لا</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">المدينة / مكان الإقامة أو النشاط <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="city_or_location" class="form-control" required>
                </div>
              </div>
            </section>

            <!-- STEP 3 -->
            <section class="step-pane" data-step-pane="3">
              <div class="step-head">
                <div class="step-overline">الخطوة 3 من 9</div>
                <h2 class="step-title">بيانات التمويل</h2>
                <p class="step-desc">
                  توضح هذه المرحلة طبيعة الاحتياج المالي، والغرض من التمويل، وبنية السداد المقترحة والضمانات.
                  كلما كانت هذه البيانات أوضح وأكثر واقعية، كانت جودة التقييم أعلى.
                </p>
                <ul class="step-checklist">
                  <li>حدد الغاية من التمويل بدقة.</li>
                  <li>أدخل القيمة المطلوبة والعملة.</li>
                  <li>أدخل هيكل السداد والدفعة الأولى والضمانات عند توفرها.</li>
                </ul>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">الغاية من التمويل <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="purpose" class="form-control" placeholder="تشغيل - توسعة - تجهيز - شراء أصول ..." required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">سقف التمويل المطلوب <span class="req-mark">مطلوب</span></label>
                  <input type="number" name="requested_amount" class="form-control" min="0" step="0.01" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">عملة التمويل <span class="req-mark">مطلوب</span></label>
                  <select name="currency" class="form-select" required>
                    <option value="SYP" selected>ليرة سورية</option>
                    <option value="USD">دولار أمريكي</option>
                    <option value="EUR">يورو</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">نوع التمويل التشغيلي <span class="req-mark">مطلوب</span></label>
                  <select name="financing_type" class="form-select" required>
                    <option value="capital">رأسمالي</option>
                    <option value="working_capital">رأس مال عامل</option>
                    <option value="mixed">مختلط</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">نسبة المرابحة / الهامش / التكلفة المقترحة</label>
                  <input type="text" name="proposed_margin" class="form-control" placeholder="%">
                  <span class="helper-note">الاسم المعروض هنا مرن ليخدم المسارات: الإسلامي والتقليدي أو معاً.</span>
                </div>

                <div class="col-md-6">
                  <label class="form-label">مدة السداد المقترحة (بالأشهر) <span class="req-mark">مطلوب</span></label>
                  <input type="number" name="repayment_period_months" class="form-control" min="1" placeholder="مثال: 24" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">تاريخ الدفعة الأولى</label>
                  <input type="date" name="first_payment_date" class="form-control">
                </div>

                <div class="col-md-6">
                  <label class="form-label">قيمة الدفعة الأولى</label>
                  <input type="number" name="first_payment_value" class="form-control">
                </div>

                <div class="col-md-6">
                  <label class="form-label">هامش الضمان المقترح</label>
                  <input type="text" name="proposed_guarantee_margin" class="form-control">
                </div>

                <div class="col-md-12">
                  <label class="form-label">الضمانات المقدمة إن وجدت</label>
                  <textarea name="provided_guarantees" class="form-control" placeholder="اذكر نوع الضمانات المتاحة أو المقترحة"></textarea>
                </div>

                <div class="col-md-12">
                  <label class="form-label">في حال التعثر أو التعسر</label>
                  <textarea name="default_case_action" class="form-control" placeholder="اذكر الإجراء المقترح أو البديل المتوقع"></textarea>
                </div>
              </div>
            </section>

            <!-- STEP 4 -->
            <section class="step-pane" data-step-pane="4">
              <div class="step-head">
                <div class="step-overline">الخطوة 4 من 9</div>
                <h2 class="step-title">مرحلة المشروع والخبرة</h2>
                <p class="step-desc">
                  وضّح مرحلة المشروع وخبرتك في المجال وأي تحديات أو دعم مطلوب. هذه البيانات تُحفظ مع تفاصيل الطلب للمراجعة.
                </p>
                <ul class="step-checklist">
                  <li>حدد مرحلة العمل الحالية.</li>
                  <li>اذكر خبرة مالك المشروع إن وجدت.</li>
                  <li>أضف ملاحظات عن السوق أو التحديات عند توفرها.</li>
                </ul>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">مرحلة المشروع <span class="req-mark">مطلوب</span></label>
                  <select name="business_stage" class="form-select" required>
                    <option value="idea">فكرة</option>
                    <option value="startup">ناشئ</option>
                    <option value="existing">قائم</option>
                    <option value="expansion">توسعة</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">خبرة مالك المشروع <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="owner_experience" class="form-control" placeholder="سنوات الخبرة / مجال التخصص" required>
                </div>

                <div class="col-md-12">
                  <label class="form-label">وصف السوق / العملاء</label>
                  <textarea name="market_description" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-12">
                  <label class="form-label">التحديات الحالية</label>
                  <textarea name="challenges" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-12">
                  <label class="form-label">الدعم المطلوب</label>
                  <textarea name="requested_support" class="form-control" rows="3"></textarea>
                </div>
              </div>
            </section>

            <!-- STEP 5 -->
            <section class="step-pane" data-step-pane="5">
              <div class="step-head">
                <div class="step-overline">الخطوة 5 من 9</div>
                <h2 class="step-title">النشاط والفواتير</h2>
                <p class="step-desc">
                  تُستخدم هذه المرحلة لإثبات النشاط أو ربط الملف ببيانات تشغيلية حقيقية مثل الفواتير،
                  السجل التجاري، وبيانات الشركة أو الجهة ذات العلاقة.
                </p>
                <ul class="step-checklist">
                  <li>أدخل اسم الشركة المرتبطة بالفواتير أو النشاط.</li>
                  <li>أدخل السجل التجاري ورقم التواصل.</li>
                  <li>ارفع فواتير أو وثائق إثبات النشاط أو الترخيص.</li>
                </ul>
              </div>

              <div id="existingProjectFields" class="mb-4" style="display:none;">
                <div class="info-alert">
                  لأن المشروع <strong>قائم</strong>، يُفضل إرفاق ترخيص العمل وكشف الحساب وآخر الفواتير لإظهار النشاط السابق بصورة أوضح.
                </div>
              </div>

              <div id="newProjectFields" class="mb-4" style="display:none;">
                <div class="info-alert">
                  لأن المشروع <strong>غير قائم</strong>، يفضل دعم الملف بما يثبت الخبرة، الترخيص أو طلبه، والجاهزية التشغيلية أو الضمانات ذات العلاقة.
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">اسم الشركة <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="company_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">السجل التجاري <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="commercial_register" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">رقم التواصل المرتبط بالنشاط <span class="req-mark">مطلوب</span></label>
                  <input type="text" name="business_phone" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">نوع الفاتورة / المستند</label>
                  <select name="invoice_type" class="form-select">
                    <option value="">اختر</option>
                    <option value="sale">فاتورة بيع</option>
                    <option value="purchase">فاتورة شراء</option>
                    <option value="activity-proof">إثبات نشاط</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <div class="upload-box">
                    <label class="form-label">تحميل فواتير البيع / الشراء أو إثبات النشاط</label>
                    <input type="file" name="activity_invoices" class="form-control" multiple>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="upload-box">
                    <label class="form-label">ترخيص العمل أو طلب الترخيص</label>
                    <input type="file" name="work_license_or_request" class="form-control">
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="upload-box">
                    <label class="form-label">بيان قيد عقاري للعقار موضوع الضمان إن وجد</label>
                    <input type="file" name="real_estate_record" class="form-control">
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="upload-box">
                    <label class="form-label">كشف حساب مصرفي يفيد النشاط إن وجد</label>
                    <input type="file" name="bank_statement" class="form-control">
                  </div>
                </div>

                <div class="col-md-12">
                  <label class="form-label">تفاصيل الكفيل الشخصي إن وجد</label>
                  <textarea name="guarantor_details" class="form-control" placeholder="الاسم الثلاثي - رقم التواصل - المهنة أو المنصب"></textarea>
                </div>
              </div>
            </section>

            <!-- STEP 6 -->
            <section class="step-pane" data-step-pane="6">
              <div class="step-head">
                <div class="step-overline">الخطوة 6 من 9</div>
                <h2 class="step-title">البيانات المالية</h2>
                <p class="step-desc">
                  صُممت هذه الجداول لتكون أوضح وأكثر مهنية وقابلة للمراجعة. الترتيب هنا قريب من القوائم
                  المحاسبية المتعارف عليها: ميزانية وقائمة دخل مع سنوات مقارنة وحقل للملاحظات وحالة التدقيق.
                </p>
                <ul class="step-checklist">
                  <li>أدخل القيم المالية على سنوات المقارنة.</li>
                  <li>حدد ما إذا كانت القيمة مدققة أو غير مدققة.</li>
                  <li>أضف ملاحظات مختصرة عند البنود المهمة.</li>
                </ul>
              </div>

              <div class="form-section-title">الميزانية / المركز المالي</div>
              <div class="financial-table-wrap mb-4">
                <table class="table financial-table">
                  <thead>
                    <tr>
                      <th>البند</th>
                      <th class="year-col">2023</th>
                      <th class="year-col">2024</th>
                      <th class="year-col">2025</th>
                      <th class="audit-col">حالة التدقيق</th>
                      <th class="notes-col">ملاحظات</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $balanceItems = [
                      'النقد في الصندوق',
                      'مدينون',
                      'بضاعة جاهزة وتحت التشغيل ومواد خام',
                      'أخرى',
                      'مجموع الموجودات المتداولة',
                      'أراضي / مباني (بالصافي)',
                      'آلات ومعدات وأثاث وسيارات (بالصافي)',
                      'مجموع الموجودات الثابتة',
                      'مجموع الموجودات',
                      'دائنون',
                      'قروض / مطلوبات طويلة الأجل',
                      'مجموع المطلوبات',
                      'رأس المال المدفوع',
                      'جاري شريك',
                      'أرباح / خسائر مجمعة',
                      'أرباح الفترة',
                      'صافي حقوق الملكية',
                      'إجمالي المطلوبات وحقوق الملكية',
                    ];
                    foreach ($balanceItems as $item):
                    ?>
                    <tr>
                      <th><?php echo $item; ?></th>
                      <td class="year-col" data-label="2023"><input type="text" inputmode="decimal" class="form-control" name="balance_2023[]" placeholder="اكتب القيمة" autocomplete="off"></td>
                      <td class="year-col" data-label="2024"><input type="text" inputmode="decimal" class="form-control" name="balance_2024[]" placeholder="اكتب القيمة" autocomplete="off"></td>
                      <td class="year-col" data-label="2025"><input type="text" inputmode="decimal" class="form-control" name="balance_2025[]" placeholder="اكتب القيمة" autocomplete="off"></td>
                      <td class="audit-col" data-label="حالة التدقيق">
                        <select class="form-select" name="balance_audited[]">
                          <option value="">اختر</option>
                          <option value="audited">مدققة</option>
                          <option value="unaudited">غير مدققة</option>
                        </select>
                      </td>
                      <td class="notes-col" data-label="ملاحظات"><input type="text" class="form-control" name="balance_notes[]" placeholder="ملاحظة اختيارية" autocomplete="off"></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="form-section-title">قائمة الدخل</div>
              <div class="financial-table-wrap">
                <table class="table financial-table">
                  <thead>
                    <tr>
                      <th>البند</th>
                      <th class="year-col">2023</th>
                      <th class="year-col">2024</th>
                      <th class="year-col">2025</th>
                      <th class="audit-col">حالة التدقيق</th>
                      <th class="notes-col">ملاحظات</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $incomeItems = [
                      'المبيعات',
                      'كلفة المبيعات',
                      'مجمل الربح',
                      'مصاريف البيع والتوزيع',
                      'المصاريف الإدارية والعمومية',
                      'إطفاءات واستهلاكات ومخصصات',
                      'مصاريف أخرى',
                      'صافي ربح العمليات',
                      'صافي الربح / الخسارة قبل الضريبة',
                      'ضريبة الدخل',
                      'صافي الربح / الخسارة بعد الضريبة',
                    ];
                    foreach ($incomeItems as $item):
                    ?>
                    <tr>
                      <th><?php echo $item; ?></th>
                      <td class="year-col" data-label="2023"><input type="text" inputmode="decimal" class="form-control" name="income_2023[]" placeholder="اكتب القيمة" autocomplete="off"></td>
                      <td class="year-col" data-label="2024"><input type="text" inputmode="decimal" class="form-control" name="income_2024[]" placeholder="اكتب القيمة" autocomplete="off"></td>
                      <td class="year-col" data-label="2025"><input type="text" inputmode="decimal" class="form-control" name="income_2025[]" placeholder="اكتب القيمة" autocomplete="off"></td>
                      <td class="audit-col" data-label="حالة التدقيق">
                        <select class="form-select" name="income_audited[]">
                          <option value="">اختر</option>
                          <option value="audited">مدققة</option>
                          <option value="unaudited">غير مدققة</option>
                        </select>
                      </td>
                      <td class="notes-col" data-label="ملاحظات"><input type="text" class="form-control" name="income_notes[]" placeholder="ملاحظة اختيارية" autocomplete="off"></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </section>

            <!-- STEP 7 -->
            <section class="step-pane" data-step-pane="7">
              <div class="step-head">
                <div class="step-overline">الخطوة 7 من 9</div>
                <h2 class="step-title">العمالة والتأهيل</h2>
                <p class="step-desc">
                  في هذه المرحلة يتم توصيف البنية البشرية للمشروع. يجب أن تكون الأعداد مترابطة ومقنعة، وأن يتطابق
                  العدد الإجمالي مع التوزيع الوظيفي والتأهيلي.
                </p>
                <ul class="step-checklist">
                  <li>أدخل إجمالي القوى العاملة.</li>
                  <li>وزع العدد بين إداريين وفنيين وعمال صناعيين.</li>
                  <li>أدخل التوزيع التأهيلي وتأكد من منطق الأرقام.</li>
                </ul>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-3">
                  <label class="form-label">عدد القوى العاملة <span class="req-mark">مطلوب</span></label>
                  <input type="number" name="total_workforce" class="form-control" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">عدد الموظفين الإداريين</label>
                  <input type="number" name="admin_employees" class="form-control">
                </div>
                <div class="col-md-3">
                  <label class="form-label">عدد الفنيين التقنيين</label>
                  <input type="number" name="technical_employees" class="form-control">
                </div>
                <div class="col-md-3">
                  <label class="form-label">عدد العمال الصناعيين</label>
                  <input type="number" name="industrial_workers" class="form-control">
                </div>
              </div>

              <div class="form-section-title">التوزيع التأهيلي</div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">عدد الشهادات فوق الجامعية</label>
                  <input type="number" name="postgraduate_count" class="form-control">
                </div>
                <div class="col-md-4">
                  <label class="form-label">عدد الشهادات الجامعية</label>
                  <input type="number" name="university_count" class="form-control">
                </div>
                <div class="col-md-4">
                  <label class="form-label">عدد شهادات المعاهد المتوسطة</label>
                  <input type="number" name="institute_count" class="form-control">
                </div>
                <div class="col-md-4">
                  <label class="form-label">عدد شهادات الثانوي</label>
                  <input type="number" name="secondary_count" class="form-control">
                </div>
                <div class="col-md-4">
                  <label class="form-label">عدد العاملين ما دون الثانوي</label>
                  <input type="number" name="below_secondary_count" class="form-control">
                </div>
                <div class="col-md-4">
                  <label class="form-label">حالة المطابقة</label>
                  <input type="text" class="form-control" value="يشترط تطابق الأعداد" readonly>
                  <span class="field-hint">سيتم لاحقًا ربط هذه المرحلة بتحقق تلقائي من تطابق الإجمالي مع مجموع الفئات.</span>
                </div>
              </div>
            </section>

            <!-- STEP 8 -->
            <section class="step-pane" data-step-pane="8">
              <div class="step-head">
                <div class="step-overline">الخطوة 8 من 9</div>
                <h2 class="step-title">الاحتياجات التدريبية</h2>
                <p class="step-desc">
                  تركز هذه المرحلة على الفجوات التدريبية ذات الصلة باستقرار المشروع أو تطويره. وجود توصيف تدريبي
                  واضح يعطي صورة أفضل عن جاهزية التشغيل أو التوسع.
                </p>
                <ul class="step-checklist">
                  <li>اذكر ما إذا كان لديك دعم تدريبي سابق.</li>
                  <li>صف احتياجات الإداريين.</li>
                  <li>صف احتياجات الفنيين والعمال الصناعيين.</li>
                </ul>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">هل لديك دعم تدريبي حالي أو سابق؟ <span class="req-mark">مطلوب</span></label>
                  <select name="has_training_support" class="form-select" required>
                    <option value="">اختر</option>
                    <option value="yes">نعم</option>
                    <option value="no">لا</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">اسم التدريب والجهة المنفذة</label>
                  <input type="text" name="training_history" class="form-control" placeholder="اسم التدريب - الجهة المنفذة - التاريخ">
                </div>

                <div class="col-md-4">
                  <label class="form-label">الاحتياج لتدريب الإداريين</label>
                  <textarea name="admin_training_need" class="form-control" placeholder="عدد / اختصاص / نوع التدريب"></textarea>
                </div>

                <div class="col-md-4">
                  <label class="form-label">الاحتياج لتدريب الفنيين</label>
                  <textarea name="technical_training_need" class="form-control" placeholder="عدد / اختصاص / نوع التدريب"></textarea>
                </div>

                <div class="col-md-4">
                  <label class="form-label">الاحتياج لتدريب العمال الصناعيين</label>
                  <textarea name="industrial_training_need" class="form-control" placeholder="عدد / اختصاص / نوع التدريب"></textarea>
                </div>
              </div>
            </section>

            <!-- STEP 9 -->
            <section class="step-pane" data-step-pane="9">
              <div class="step-head">
                <div class="step-overline">الخطوة 9 من 9</div>
                <h2 class="step-title">المراجعة والإرسال</h2>
                <p class="step-desc">
                  قبل الإرسال النهائي، راجع الملف كاملًا، وتأكد من أن المعلومات الأساسية والمالية والمستندات
                  الجوهرية كلها ضمن الحالة المطلوبة، وأنك على دراية بمسار الإحالة للمكاتب الاستشارية بعد الإرسال.
                </p>
              </div>

              <div class="info-alert">
                هذه النسخة تعرض مراجعة نهائية مبسطة للمستخدم، تمهيدًا لاحقًا لربطها بحالة اكتمال كل مرحلة
                والمرفقات والاعتمادات الفعلية.
              </div>

              <ul class="review-list">
                <li>مراجعة نوع التمويل، طبيعة المشروع، وقطاع النشاط.</li>
                <li>تأكيد معرفة مقدم الطلب بأن الملف قد يمر بمكتب استشاري مؤهل بعد الإرسال، وأن عرض السعر لا يطلب في بداية النموذج.</li>
                <li>مراجعة بيانات مقدم الطلب والصفة القانونية وبيانات التواصل.</li>
                <li>مراجعة الغاية من التمويل، القيمة، العملة، هيكل السداد، والضمانات.</li>
                <li>التأكد من إدخال اسم المشروع والمحافظة والقطاع.</li>
                <li>التأكد من إدخال بيانات الشركة والسجل التجاري ورقم التواصل والفواتير ذات العلاقة.</li>
                <li>مراجعة القوائم المالية والعمالة والاحتياجات التدريبية.</li>
              </ul>

              <div class="mt-4">
                <label class="form-label">إقرار مقدم الطلب <span class="req-mark">مطلوب</span></label>
                <div class="form-check-wrap" data-required-group="acknowledge_info">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="acknowledgeInfo" name="acknowledge_info" value="1" required>
                    <label class="form-check-label" for="acknowledgeInfo">
                      أقر بأن البيانات المدخلة صحيحة بحسب علمي، وأوافق على إحالة الطلب للمراجعة ضمن مسار التمويل المعتمد.
                    </label>
                  </div>
                </div>
              </div>
            </section>

            <div class="step-actions">
              <div class="btn-group-custom">
                <button type="button" class="btn btn-outline-secondary" id="prevStepBtn">السابق</button>
                <button type="button" class="btn btn-brand" id="nextStepBtn">التالي</button>
              </div>

              <div class="btn-group-custom">
                <button type="button" class="btn btn-light border" id="saveDraftBtn">حفظ كمسودة</button>
                <button type="button" class="btn btn-brand" id="submitApplicationBtn" style="display:none;">إرسال الطلب</button>
              </div>
            </div>

          </form>
        </div>
      </div>

    </div>
  </section>

</div>


<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>

<script>
(function () {
  const totalSteps = 9;
  let currentStep = 1;

  const stepPanes = document.querySelectorAll('[data-step-pane]');
  const roadmapItems = document.querySelectorAll('.roadmap-item');
  const nextBtn = document.getElementById('nextStepBtn');
  const prevBtn = document.getElementById('prevStepBtn');
  const submitBtn = document.getElementById('submitApplicationBtn');
  const progressBar = document.getElementById('progressBar');
  const progressText = document.getElementById('progressText');
  const currentStepLabel = document.getElementById('currentStepLabel');

  const projectStatusRadios = document.querySelectorAll('.project-status-radio');
  const existingBlock = document.getElementById('existingProjectFields');
  const newBlock = document.getElementById('newProjectFields');
  const projectSector = document.getElementById('projectSector');
  const syrsicActivityCode = document.getElementById('syrsicActivityCode');
  const expectedPathSummary = document.getElementById('expectedPathSummary');

  function updatePathSummary() {
    if (!expectedPathSummary) return;
    const sector = projectSector ? projectSector.value : '';
    const code = syrsicActivityCode ? syrsicActivityCode.value.trim() : '';
    const sectorLabels = {
      agricultural: 'نشاط زراعي',
      industrial: 'نشاط صناعي',
      commercial: 'نشاط تجاري',
      service: 'نشاط خدمي'
    };
    const sectorText = sectorLabels[sector] || 'قطاع غير محدد';
    const codeText = code ? ' / رمز SYRSIC: ' + code : '';
    expectedPathSummary.value = sector
      ? 'مسار الطلب: ' + sectorText + codeText + ' / مراجعة بعد الإرسال'
      : 'سيتم توليده بعد اختيار القطاع الاقتصادي حسب SYRSIC';
  }

  function updateProjectStatusInfo() {
    const selected = document.querySelector('.project-status-radio:checked');
    if (existingBlock) existingBlock.style.display = 'none';
    if (newBlock) newBlock.style.display = 'none';

    if (!selected) return;

    if (selected.value === 'existing' && existingBlock) {
      existingBlock.style.display = 'block';
    } else if (selected.value === 'new' && newBlock) {
      newBlock.style.display = 'block';
    }

    const stage = document.querySelector('[name="business_stage"]');
    if (stage && !stage.dataset.touched) {
      stage.value = selected.value === 'existing' ? 'existing' : 'startup';
    }
  }

  function updateRoadmapStates() {
    roadmapItems.forEach((item) => {
      const itemStep = Number(item.dataset.step);
      item.classList.remove('active', 'locked', 'done');

      if (itemStep < currentStep) {
        item.classList.add('done');
      } else if (itemStep === currentStep) {
        item.classList.add('active');
      } else {
        item.classList.add('locked');
      }
    });
  }

  function showStepMessage(text, type = 'error') {
    const box = document.getElementById('financeApplyMessage');
    if (box) {
      box.className = `alert alert-${type === 'error' ? 'danger' : 'success'} mb-3`;
      box.textContent = text;
      box.classList.remove('d-none');
    }
    if (window.AppFeedback) {
      if (type === 'error') {
        window.AppFeedback.error('تحقق من البيانات', text);
      } else {
        window.AppFeedback.success('تم بنجاح', text);
      }
    } else if (box) {
      box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  function clearStepMessage() {
    const box = document.getElementById('financeApplyMessage');
    if (!box) return;
    box.classList.add('d-none');
    box.textContent = '';
  }

  function fieldLabel(el) {
    const wrap = el.closest('.col-md-6, .col-md-4, .col-md-3, .col-12, .mb-3, td, .form-check-wrap') || el.parentElement;
    const label = wrap?.querySelector?.('.form-label, .form-check-label');
    if (label) return label.childNodes[0]?.textContent?.trim() || label.textContent.trim();
    return el.getAttribute('name') || 'حقل مطلوب';
  }

  function clearInvalidMarks(pane) {
    pane.querySelectorAll('.is-invalid-field').forEach((el) => el.classList.remove('is-invalid-field'));
  }

  function validateStep(step) {
    const pane = document.querySelector(`[data-step-pane="${step}"]`);
    if (!pane) return { ok: true, missing: [] };

    clearInvalidMarks(pane);
    const missing = [];

    pane.querySelectorAll('[data-required-group]').forEach((group) => {
      const name = group.getAttribute('data-required-group');
      const checked = pane.querySelector(`[name="${name}"]:checked`);
      if (!checked) {
        group.classList.add('is-invalid-field');
        const label = group.closest('.col-md-6, .col-12')?.querySelector('.form-label');
        missing.push((label?.childNodes?.[0]?.textContent || name).trim());
      }
    });

    pane.querySelectorAll('input[required], select[required], textarea[required]').forEach((el) => {
      if (el.type === 'radio' || el.type === 'checkbox') return;
      const value = String(el.value || '').trim();
      const invalidNumber = el.type === 'number' && value !== '' && Number(value) <= 0 && el.name === 'requested_amount';
      if (!value || invalidNumber) {
        el.classList.add('is-invalid-field');
        missing.push(fieldLabel(el));
      }
    });

    if (step === 3) {
      const amount = pane.querySelector('[name="requested_amount"]');
      if (amount && !(Number(amount.value) > 0)) {
        amount.classList.add('is-invalid-field');
        if (!missing.includes('سقف التمويل المطلوب')) missing.push('سقف التمويل المطلوب');
      }
    }

    if (step === 6) {
      const hasBalance = Array.from(pane.querySelectorAll('[name="balance_2023[]"], [name="balance_2024[]"], [name="balance_2025[]"]'))
        .some((el) => String(el.value || '').trim() !== '');
      const hasIncome = Array.from(pane.querySelectorAll('[name="income_2023[]"], [name="income_2024[]"], [name="income_2025[]"]'))
        .some((el) => String(el.value || '').trim() !== '');
      if (!hasBalance || !hasIncome) {
        missing.push('إدخال قيمة واحدة في الميزانية وقائمة الدخل');
      }
    }

    if (step === 9) {
      const ack = document.getElementById('acknowledgeInfo');
      if (!ack?.checked) {
        ack?.closest('.form-check-wrap')?.classList.add('is-invalid-field');
        missing.push('إقرار مقدم الطلب');
      }
    }

    return { ok: missing.length === 0, missing: [...new Set(missing)] };
  }

  function goToStep(step, { force = false } = {}) {
    if (step < 1 || step > totalSteps) return;

    if (!force && step > currentStep) {
      for (let s = currentStep; s < step; s += 1) {
        const result = validateStep(s);
        if (!result.ok) {
          showStepMessage('لا يمكن تجاوز هذه المرحلة قبل إكمال الحقول المطلوبة: ' + result.missing.join('، '));
          goToStep(s, { force: true });
          return;
        }
      }
    }

    clearStepMessage();
    currentStep = step;

    stepPanes.forEach((pane) => {
      pane.classList.toggle('active', Number(pane.dataset.stepPane) === currentStep);
    });

    updateRoadmapStates();

    const activeItem = document.querySelector('.roadmap-item.active');
    if (activeItem && currentStepLabel) {
      currentStepLabel.textContent = activeItem.dataset.title || ('الخطوة ' + currentStep);
    }

    prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
    nextBtn.style.display = currentStep === totalSteps ? 'none' : 'inline-flex';
    submitBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';

    const progress = Math.round((currentStep / totalSteps) * 100);
    progressBar.style.width = progress + '%';
    progressText.textContent = progress + '%';

    const shell = document.querySelector('.roadmap-shell');
    if (shell) {
      window.scrollTo({
        top: shell.offsetTop - 85,
        behavior: 'smooth'
      });
    }
  }

  roadmapItems.forEach((item) => {
    item.addEventListener('click', function () {
      const targetStep = Number(this.dataset.step);
      goToStep(targetStep);
    });
  });

  nextBtn.addEventListener('click', function () {
    const result = validateStep(currentStep);
    if (!result.ok) {
      showStepMessage('أكمل الحقول المطلوبة في هذه المرحلة قبل المتابعة: ' + result.missing.join('، '));
      return;
    }
    if (currentStep < totalSteps) {
      goToStep(currentStep + 1, { force: true });
    }
  });

  prevBtn.addEventListener('click', function () {
    if (currentStep > 1) {
      goToStep(currentStep - 1, { force: true });
    }
  });

  document.getElementById('openAiForSyrsicBtn')?.addEventListener('click', () => {
    if (window.AiChatFab?.open) window.AiChatFab.open();
    else document.querySelector('#aiChatFabRoot .aic-fab')?.click();
  });

  window.FinanceApplySteps = {
    validateStep,
    validateAll() {
      for (let s = 1; s <= totalSteps; s += 1) {
        const result = validateStep(s);
        if (!result.ok) {
          goToStep(s, { force: true });
          showStepMessage('أكمل الحقول المطلوبة قبل الإرسال: ' + result.missing.join('، '));
          return false;
        }
      }
      return true;
    }
  };

  projectStatusRadios.forEach((radio) => {
    radio.addEventListener('change', updateProjectStatusInfo);
  });

  [projectSector, syrsicActivityCode].forEach((field) => {
    if (field) {
      field.addEventListener('change', updatePathSummary);
      field.addEventListener('input', updatePathSummary);
    }
  });

  document.querySelector('[name="business_stage"]')?.addEventListener('change', function () {
    this.dataset.touched = '1';
  });

  updateProjectStatusInfo();
  updatePathSummary();
  goToStep(1, { force: true });
})();
</script>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.3"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-apply.js?v=2.5"></script>

</body>
</html>