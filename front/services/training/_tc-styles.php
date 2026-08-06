<?php
/** تصميم تطبيق المركز — حاسوب: سايدبار ثابت | موبايل: درج منيو */
?>
<style>
  :root{
    --ref-bar:#4aa39a;
    --ref-bar-2:#3d8f87;
    --ref-ink:#2d6f69;
    --ref-card:#f0faf9;
    --ref-bg:#f5f6f8;
    --ref-btn:#fcfafd;
    --ref-btn-border:#eadff0;
    --ref-num:#9aa6a5;
    --ref-line:#dceeee;
    --ref-gold:#d4b03a;
    --ref-side:280px;
  }
  *,*::before,*::after{box-sizing:border-box}
  html{overflow-x:hidden}
  html,body{
    margin:0;background:var(--ref-bg);color:var(--ref-ink);
    font-family:'IBM Plex Sans Arabic','Tajawal',Tahoma,sans-serif;
  }
  body.tc-app{overflow-x:hidden;min-height:100vh}
  body.tc-nav-open{overflow:hidden}

  .tc-phone,.tc-main{
    min-height:100vh;background:var(--ref-bg);width:100%;
    position:relative;transition:margin .2s ease;
  }

  /* ===== Sidebar shell ===== */
  aside.tc-sidebar#tcSidebar{
    position:fixed!important;top:0;right:0;bottom:0;
    width:var(--ref-side);max-width:100%;
    height:100%!important;height:100dvh!important;
    background:#fff!important;z-index:1200!important;
    display:flex!important;flex-direction:column!important;
    border-left:1px solid var(--ref-line);
    box-shadow:-8px 0 28px rgba(45,111,105,.12);
    overflow:hidden;
    -webkit-overflow-scrolling:touch;
  }
  .tc-sb-brand{
    background:linear-gradient(165deg,#9ad9d2 0%,#dff7f4 48%,#fff 100%);
    padding:26px 16px 18px;text-align:center;flex:0 0 auto;
  }
  .tc-sb-brand .av{
    width:74px;height:74px;border-radius:50%;margin:0 auto 10px;
    background:#fff;color:var(--ref-ink);border:3px solid #fff;
    display:grid;place-items:center;font-size:26px;font-weight:800;
    box-shadow:0 6px 16px rgba(45,111,105,.12);
  }
  .tc-sb-brand .nm{font-weight:800;font-size:.95rem;color:#1b2b2a}
  .tc-sb-brand .rl{font-size:.75rem;color:#6a8080;margin-top:3px}
  .tc-sb-label{
    font-size:.68rem;font-weight:800;color:#8aa;margin:12px 12px 4px;
    letter-spacing:.02em;text-transform:none;
  }
  .tc-sb-nav{
    padding:8px;display:flex!important;flex-direction:column!important;
    gap:2px;align-items:stretch;
  }
  .tc-sb-nav.grow{flex:1;overflow:auto;min-height:0}
  .tc-sb-nav a,.tc-sb-nav button{
    display:flex!important;align-items:center;gap:12px;width:100%;
    padding:12px 13px;border:0;background:none;border-radius:12px;
    color:#243838;font:inherit;font-size:.9rem;font-weight:650;
    text-decoration:none;cursor:pointer;text-align:right;white-space:nowrap;
  }
  .tc-sb-nav a i,.tc-sb-nav button i{
    order:-1;width:22px;text-align:center;color:var(--ref-ink);font-size:1.1rem;flex:0 0 auto;
  }
  .tc-sb-nav a:hover,.tc-sb-nav button:hover{background:#f3fbfa}
  .tc-sb-nav a.active{background:#e8f6f4;color:var(--ref-ink);font-weight:800}
  .tc-sb-foot{border-top:1px solid var(--ref-line);padding-bottom:8px;flex:0 0 auto}
  .tc-overlay{
    position:fixed!important;inset:0;background:rgba(20,40,40,.4);
    z-index:1100!important;opacity:0;visibility:hidden;transition:.2s;pointer-events:none;
  }
  .tc-overlay.open{opacity:1;visibility:visible;pointer-events:auto}
  .tc-burger{display:grid!important;place-items:center}

  /* موبايل: درج منيو */
  @media (max-width:899.98px){
    aside.tc-sidebar#tcSidebar{
      width:min(300px,88vw);
      border-radius:18px 0 0 18px;
      transform:translateX(105%)!important;
      transition:transform .25s ease;
      pointer-events:none;
    }
    aside.tc-sidebar#tcSidebar.open{
      transform:translateX(0)!important;
      pointer-events:auto;
    }
    .tc-phone,.tc-main{margin-right:0!important}
    .tc-burger{display:grid!important}
  }

  /* حاسوب: سايدبار ثابت */
  @media (min-width:900px){
    aside.tc-sidebar#tcSidebar{
      transform:none!important;
      transition:none;
      pointer-events:auto;
      border-radius:0;
      box-shadow:none;
    }
    .tc-phone,.tc-main{margin-right:var(--ref-side)!important;width:auto}
    .tc-burger{display:none!important}
    .tc-overlay{display:none!important}
    body.tc-nav-open{overflow:auto}
  }

  /* ===== Top bar ===== */
  .tc-bar{
    background:linear-gradient(135deg,#56b3aa,#3f978f);
    color:#fff;min-height:58px;
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    padding:10px 14px;position:sticky;top:0;z-index:40;
    box-shadow:0 3px 14px rgba(63,151,143,.25);
  }
  .tc-bar .ic{
    width:42px;height:42px;display:grid;place-items:center;color:#fff;
    background:rgba(255,255,255,.14);border:0;border-radius:12px;
    font-size:1.25rem;cursor:pointer;text-decoration:none;padding:0;flex:0 0 auto;
  }
  .tc-bar .ic:hover{background:rgba(255,255,255,.22)}
  .tc-bar .ttl{
    flex:1;text-align:center;font-weight:800;font-size:1.08rem;line-height:1.35;
    padding:0 8px;min-width:0;
  }
  .tc-bar .ttl small{
    display:block;font-size:.74rem;font-weight:600;opacity:.95;margin-top:2px;line-height:1.4;
  }
  .tc-bar-start{display:flex;align-items:center;gap:6px;flex:0 0 auto}

  /* ===== Content ===== */
  .tc-content{
    width:100%;max-width:760px;margin:0 auto;padding:14px 14px 28px;
  }
  @media (min-width:900px){
    .tc-content{
      max-width:920px;margin:0;margin-left:auto;padding:22px 28px 36px;
    }
  }
  .tc-hello,.tc-section-title{display:none!important}

  /* ===== Cards ===== */
  #tcCourses,#tcKits,#tcTrainers,#tcTrainees,.tc-list{
    display:flex;flex-direction:column;gap:14px;
  }
  .tc-item{display:flex;align-items:stretch;gap:10px;direction:rtl}
  .tc-item-num{
    width:22px;flex:0 0 22px;display:flex;align-items:center;justify-content:center;
    font-size:.9rem;font-weight:800;color:var(--ref-num);
  }
  .tc-item-card{
    flex:1;background:var(--ref-card);
    border-radius:14px;border:1px solid #dff0ee;
    border-right:5px solid var(--ref-bar-2);
    padding:18px 16px 14px;min-width:0;
    box-shadow:0 6px 18px rgba(45,111,105,.06);
  }
  .tc-item-title{
    text-align:center;color:var(--ref-ink);font-weight:800;font-size:1rem;
    line-height:1.6;margin:0 0 14px;
  }
  .tc-item-btn,.tc-open-btn,.tc-soft-btn,.tc-fab-add{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;max-width:250px;margin:0 auto;padding:12px 16px;
    background:var(--ref-btn);border:1px solid var(--ref-btn-border);
    border-radius:12px;color:var(--ref-ink);text-decoration:none;
    font-weight:800;font-size:.92rem;
    box-shadow:0 2px 8px rgba(45,111,105,.05);
    transition:.15s ease;
  }
  .tc-item-btn:hover,.tc-open-btn:hover,.tc-fab-add:hover{
    background:#f6f0fa;transform:translateY(-1px);
  }
  .tc-fab-add{margin:0 auto 8px}
  .tc-row{display:none}
  .tc-muted{color:#6a8080}

  /* ===== Hub ===== */
  .tc-hub{
    background:#fff;margin:12px;border-radius:16px;padding:18px 14px;
    border:1px solid #e8efef;box-shadow:0 6px 18px rgba(45,111,105,.05);
  }
  @media (min-width:900px){
    .tc-hub{margin:18px 28px;max-width:920px;margin-left:auto;margin-right:28px}
  }
  .tc-hub h2{margin:0 0 14px;text-align:center;font-size:1.08rem;font-weight:800;color:var(--ref-ink)}
  .tc-hub-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;max-width:520px;margin:0 auto}
  .tc-hub-actions a,.tc-menu-item{
    display:flex;align-items:center;justify-content:center;gap:8px;
    padding:13px 10px;background:#fafbfd;border:1px solid #e5e7ee;
    border-radius:12px;color:var(--ref-ink);text-decoration:none;font-weight:800;font-size:.9rem;
  }
  .tc-menu-item .mi-ic,.tc-menu-item .mi-sub,.tc-menu-item .mi-chev{display:none}
  #tcMenu{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px}
  .tc-chead{display:none}

  /* ===== Table ===== */
  .tc-table-wrap{
    overflow:auto;background:#fff;margin:0;
    border-radius:0 0 12px 12px;box-shadow:0 4px 14px rgba(0,0,0,.04);
  }
  .tc-table{width:100%;border-collapse:collapse;background:#fff;min-width:360px}
  .tc-table thead th{
    background:var(--ref-bar-2);color:#fff;font-size:.84rem;font-weight:800;
    padding:13px 8px;text-align:center;border:1px solid #2f2f2f;white-space:nowrap;
  }
  .tc-table td{
    background:#fff;color:#222;font-size:.86rem;padding:12px 8px;text-align:center;
    border:1px solid #6e6e6e;vertical-align:middle;
  }
  .tc-gold-btn{
    width:36px;height:36px;border-radius:50%;background:var(--ref-gold);color:#fff;
    display:inline-grid;place-items:center;text-decoration:none;border:0;
    font-size:1.15rem;font-weight:900;box-shadow:0 2px 6px rgba(212,176,58,.35);
  }

  /* ===== Scope + search ===== */
  .tc-scope{
    display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;
    padding:10px 12px;border-radius:12px;margin:0 0 14px;font-size:.84rem;font-weight:650;
  }
  .tc-scope.is-group{background:#fdf3dd;color:#7a5200;border:1px solid #f0dba0}
  .tc-scope.is-course{background:#e8f6f4;color:#2d6f69;border:1px solid #cfe8e4}
  .tc-scope-txt{line-height:1.5}
  .tc-scope-txt a{color:inherit;font-weight:800;text-decoration:underline}
  .tc-scope-acts{display:flex;flex-wrap:wrap;gap:8px}
  .tc-scope-acts a{
    padding:6px 10px;border-radius:9px;background:#fff;border:1px solid #e0c98a;
    color:#7a5200;text-decoration:none;font-weight:800;font-size:.78rem;
  }
  .tc-scope-acts a.alt{border-color:#8fc4bf;color:#2d6f69}
  .tc-search{
    display:flex;align-items:center;gap:8px;background:#fff;border:1.5px solid #8fc4bf;
    border-radius:12px;padding:8px 12px;margin:0 0 14px;
  }
  .tc-search i{color:var(--ref-ink);opacity:.7}
  .tc-search input{
    flex:1;border:0;outline:0;background:transparent;font:inherit;font-size:.92rem;min-width:0;
  }
  .tc-collapse-btn{
    display:inline-flex;align-items:center;gap:8px;margin:0 0 12px;padding:10px 14px;
    border:1px dashed #8fc4bf;border-radius:12px;background:#fff;color:var(--ref-ink);
    font:inherit;font-weight:800;cursor:pointer;
  }
  .tc-collapse-panel[hidden]{display:none!important}
  .tc-hub-guide{display:grid;gap:10px;margin-top:6px}
  .tc-hub-guide a{
    display:grid;grid-template-columns:auto 1fr;gap:4px 12px;align-items:center;
    background:var(--ref-btn);border:1px solid var(--ref-btn-border);border-radius:14px;
    padding:12px 14px;text-decoration:none;color:var(--ref-ink);
  }
  .tc-hub-guide a i{grid-row:span 2;font-size:1.25rem;color:var(--ref-bar-2)}
  .tc-hub-guide a .lab{font-weight:800;font-size:.92rem}
  .tc-hub-guide a small{grid-column:2;color:#6a8080;font-size:.78rem;font-weight:600}
  .tc-sum{
    display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:0 0 14px;
  }
  .tc-sum .cell{
    background:#fff;border:1px solid #e7f1f0;border-radius:12px;padding:10px 8px;text-align:center;
  }
  .tc-sum .n{display:block;font-size:1.2rem;font-weight:900;color:var(--ref-ink)}
  .tc-sum .l{display:block;font-size:.72rem;font-weight:700;color:#6a8080;margin-top:2px}
  @media(max-width:520px){ .tc-sum{grid-template-columns:1fr} }

  /* ===== Mobile-friendly data cards ===== */
  .tc-mlist{display:flex;flex-direction:column;gap:10px;padding:0 0 8px}
  .tc-mcard{
    background:var(--ref-card);border:1px solid #dff0ee;border-right:4px solid var(--ref-bar-2);
    border-radius:12px;padding:12px;box-shadow:0 4px 12px rgba(45,111,105,.05);
  }
  .tc-mcard-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:8px}
  .tc-mcard-title{font-weight:800;font-size:.92rem;color:var(--ref-ink);line-height:1.4;margin:0}
  .tc-mcard-sub{font-size:.76rem;color:#6a8080;font-weight:600;margin-top:2px}
  .tc-mcard-num{
    flex:0 0 auto;min-width:26px;height:26px;border-radius:999px;background:#fff;
    border:1px solid var(--ref-line);display:grid;place-items:center;
    font-size:.74rem;font-weight:800;color:var(--ref-num);
  }
  .tc-mcard-rows{display:grid;gap:6px}
  .tc-mcard-row{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    background:#fff;border:1px solid #e7f1f0;border-radius:9px;padding:8px 10px;
  }
  .tc-mcard-row .k{font-size:.75rem;font-weight:700;color:#6a8080}
  .tc-mcard-row .v{font-size:.86rem;font-weight:800;color:#1b2b2a;text-align:left}
  .tc-mcard-row .v input,.tc-mcard-row .v select{
    width:100%;min-width:110px;max-width:160px;padding:8px 10px;
    border:1.5px solid #8fc4bf;border-radius:10px;font:inherit;background:#fff;text-align:center;
  }
  .tc-mcard-acts{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
  .tc-mcard-acts a,.tc-mcard-acts button{
    flex:1;min-width:120px;display:inline-flex;align-items:center;justify-content:center;gap:6px;
    padding:10px 12px;border-radius:11px;font-weight:800;font-size:.84rem;text-decoration:none;border:1px solid var(--ref-btn-border);
    background:var(--ref-btn);color:var(--ref-ink);cursor:pointer;
  }
  .tc-mcard-acts a.pdf,.tc-mcard-acts .pdf{
    background:var(--ref-bar-2);color:#fff;border-color:var(--ref-bar-2);
  }
  .tc-mcard-acts a.open{
    background:#fff;border-color:#8fc4bf;
  }
  @media (min-width:760px){
    .tc-mlist.two-col{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  }

  /* ===== Forms ===== */
  .tc-form-card{
    background:#fff;margin:14px auto;border-radius:16px;padding:18px 16px 20px;
    box-shadow:0 8px 24px rgba(45,111,105,.08);border:1px solid #e7f1f0;
    max-width:640px;
  }
  .tc-form-card label{display:block;font-size:.78rem;font-weight:700;color:var(--ref-ink);margin:0 4px 6px}
  .tc-form-card .fld{margin-bottom:14px}
  .tc-form-card input,.tc-form-card select,.tc-form-card textarea{
    width:100%;padding:12px;border:1.5px solid #8fc4bf;border-radius:10px;font:inherit;background:#fff;
  }
  .tc-form-grid{display:grid;gap:12px;grid-template-columns:1fr}
  @media(min-width:520px){ .tc-form-grid.two{grid-template-columns:1fr 1fr} }
  .tc-save{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:min(230px,100%);margin:10px auto 0;padding:12px 18px;
    background:var(--ref-bar-2);color:#fff;border:0;border-radius:999px;
    font:inherit;font-weight:800;cursor:pointer;
  }
  .tc-form-msg{text-align:center;font-weight:700;margin:8px 14px}
  .tc-form-msg.err{color:#b91c1c}.tc-form-msg.ok{color:#15803d}
  .tc-chips{display:flex;flex-wrap:wrap;gap:8px}
  .tc-chip{border:1px solid var(--ref-line);background:#fff;border-radius:10px;padding:8px 12px;font-weight:700;cursor:pointer}
  .tc-chip.active{background:var(--ref-bar-2);color:#fff;border-color:var(--ref-bar-2)}
  .tc-empty,.tc-spin{text-align:center;color:#889;padding:48px 16px}
  .tc-input,.tc-inp,.tc-num{border:1.5px solid #8fc4bf;border-radius:10px;padding:10px 12px;font:inherit;background:#fff}
  .tc-num{width:74px;text-align:center}
  .tc-bar-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:12px}
  .tc-bar-row .tc-inp{flex:1;min-width:140px}
  .tc-btn-teal,.tc-btn-primary{background:var(--ref-bar-2);color:#fff;border:0;border-radius:10px;padding:10px 14px;font-weight:800}
  .tc-badge{
    display:inline-block;padding:3px 9px;border-radius:999px;font-size:.72rem;font-weight:800;
  }
  .tc-badge.b-green{background:#e8f7ed;color:#15803d}
  .tc-badge.b-red{background:#fdecec;color:#b91c1c}
  .tc-badge.b-gold{background:#fdf3dd;color:#9a6500}
  .tc-badge.b-gray{background:#eef2f4;color:#546e7a}
  .tc-badge.b-blue{background:#e8f1fb;color:#1d4ed8}

  .tc-toast-wrap{position:fixed;top:14px;left:50%;transform:translateX(-50%);z-index:9999;width:min(92vw,400px);pointer-events:none}
  .tc-toast{pointer-events:auto;display:flex;gap:10px;align-items:center;padding:12px 14px;border-radius:12px;color:#fff;font-weight:700;margin-bottom:8px}
  .tc-toast.ok{background:#15803d}.tc-toast.err{background:#b91c1c}.tc-toast.info{background:var(--ref-bar-2)}
  .tc-modal-ov{position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9998;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;visibility:hidden}
  .tc-modal-ov.open{opacity:1;visibility:visible}
  .tc-modal{background:#fff;border-radius:14px;padding:20px;width:min(92vw,360px);text-align:center}
  .tc-modal .acts{display:flex;gap:8px}.tc-modal .acts button{flex:1;border:0;border-radius:10px;padding:11px;font-weight:800}
  .tc-modal .yes{background:var(--ref-bar-2);color:#fff}.tc-modal .no{background:#eee}
  @media(max-width:600px){
    .tc-bar .ttl{font-size:.95rem}
    .tc-hub-actions{grid-template-columns:1fr}
    .tc-bar-row{padding:10px 0}
    .tc-num{width:100%;max-width:100%}
  }
</style>
<script>
document.documentElement.classList.add('tc-app-html');
document.addEventListener('DOMContentLoaded', function(){
  document.body.classList.add('tc-app');
});
</script>
