<?php
$basePath  = '../../';
$pageTitle = 'استبيان توصيف المشروع التقني الابتكاري';
$activePage= 'entrepreneurship';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#7c3aed;--c-accent:#a78bfa;--c-soft:#f5f3ff;--c-border:rgba(124,58,237,.15);--c-text:#1e1b4b;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(124,58,237,.08);}
    *{box-sizing:border-box;}
    body{background:linear-gradient(160deg,#f5f3ff,#ede9fe);font-family:'Segoe UI',Tahoma,sans-serif;color:var(--c-text);}
    .pw{max-width:820px;margin:auto;padding:22px 14px 60px;}

    /* Header */
    .page-header{background:linear-gradient(135deg,#1e1b4b,#4c1d95,#7c3aed);color:#fff;border-radius:22px;padding:28px 32px;margin-bottom:24px;text-align:center;position:relative;overflow:hidden;}
    .page-header::before{content:"✦";position:absolute;font-size:18rem;opacity:.03;top:-60px;right:-40px;line-height:1;}
    .page-header h1{font-size:1.45rem;font-weight:900;margin:0 0 6px;}
    .page-header p{opacity:.8;font-size:.88rem;margin:0;}
    .header-badge{display:inline-block;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:4px 14px;font-size:.78rem;font-weight:700;margin-bottom:12px;}

    /* Progress */
    .progress-wrap{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:16px 20px;margin-bottom:20px;box-shadow:var(--c-shadow);}
    .progress-steps{display:flex;gap:0;overflow-x:auto;padding-bottom:4px;}
    .ps{flex:1;min-width:32px;text-align:center;position:relative;cursor:pointer;}
    .ps-dot{width:28px;height:28px;border-radius:50%;border:2px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;margin:0 auto 4px;transition:all .2s;color:var(--c-muted);}
    .ps.done .ps-dot{background:#dcfce7;border-color:#15803d;color:#15803d;}
    .ps.active .ps-dot{background:var(--c-primary);border-color:var(--c-primary);color:#fff;box-shadow:0 0 0 4px rgba(124,58,237,.2);}
    .ps-label{font-size:.63rem;color:var(--c-muted);font-weight:600;white-space:nowrap;}
    .ps.active .ps-label{color:var(--c-primary);font-weight:800;}
    .ps.done .ps-label{color:#15803d;}
    .ps::after{content:"";position:absolute;top:14px;right:-50%;width:100%;height:2px;background:#e5e7eb;z-index:-1;}
    .ps:last-child::after{display:none;}
    .ps.done::after,.ps.active::after{background:var(--c-primary);}
    .progress-bar-wrap{margin-top:12px;background:#f3f4f6;border-radius:20px;height:6px;}
    .progress-bar-fill{height:100%;border-radius:20px;background:linear-gradient(90deg,var(--c-primary),var(--c-accent));transition:width .4s;}
    .progress-text{font-size:.78rem;color:var(--c-muted);font-weight:700;text-align:center;margin-top:6px;}

    /* Card */
    .step-card{background:#fff;border:1px solid var(--c-border);border-radius:22px;padding:28px;box-shadow:var(--c-shadow);display:none;}
    .step-card.active{display:block;}
    .step-num{font-size:.75rem;font-weight:800;color:var(--c-primary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;display:flex;align-items:center;gap:6px;}
    .step-title{font-size:1.15rem;font-weight:900;color:var(--c-text);margin-bottom:18px;padding-bottom:14px;border-bottom:2px solid var(--c-soft);}

    /* Form */
    .fg{margin-bottom:16px;}
    .fl{display:block;font-size:.82rem;font-weight:700;color:var(--c-text);margin-bottom:6px;}
    .fl span{color:#dc2626;}
    .fi,.fs{width:100%;padding:10px 13px;border:1.5px solid var(--c-border);border-radius:12px;font-size:.88rem;color:var(--c-text);background:#fff;transition:border-color .15s;}
    .fi:focus,.fs:focus{outline:none;border-color:var(--c-primary);box-shadow:0 0 0 3px rgba(124,58,237,.1);}
    textarea.fi{resize:vertical;min-height:90px;}
    .fr{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    @media(max-width:560px){.fr{grid-template-columns:1fr;}}
    .hint{font-size:.76rem;color:var(--c-muted);margin-top:4px;}

    /* Checkbox grid */
    .cb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-top:4px;}
    .cb-item{position:relative;}
    .cb-item input[type=checkbox]{position:absolute;opacity:0;width:0;height:0;}
    .cb-item label{display:flex;align-items:center;gap:9px;padding:10px 14px;border:1.5px solid var(--c-border);border-radius:12px;cursor:pointer;font-size:.84rem;font-weight:700;color:var(--c-muted);transition:all .15s;background:#fafaf9;}
    .cb-item input:checked + label{border-color:var(--c-primary);background:var(--c-soft);color:var(--c-primary);}
    .cb-item label i{font-size:1rem;flex-shrink:0;}

    /* Radio grid */
    .rb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:4px;}
    .rb-item{position:relative;}
    .rb-item input[type=radio]{position:absolute;opacity:0;width:0;height:0;}
    .rb-item label{display:flex;align-items:center;gap:9px;padding:11px 14px;border:1.5px solid var(--c-border);border-radius:12px;cursor:pointer;font-size:.84rem;font-weight:700;color:var(--c-muted);transition:all .15s;background:#fafaf9;}
    .rb-item input:checked + label{border-color:var(--c-primary);background:var(--c-soft);color:var(--c-primary);}
    .rb-dot{width:16px;height:16px;border-radius:50%;border:2px solid currentColor;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .rb-item input:checked + label .rb-dot::after{content:"";width:8px;height:8px;border-radius:50%;background:var(--c-primary);display:block;}

    /* Textarea counter */
    .ta-wrap{position:relative;}
    .ta-count{position:absolute;bottom:8px;left:10px;font-size:.72rem;color:var(--c-muted);font-weight:700;}

    /* Section label */
    .sec-label{background:var(--c-soft);color:var(--c-primary);border-radius:10px;padding:8px 14px;font-size:.82rem;font-weight:800;margin-bottom:14px;display:flex;align-items:center;gap:8px;}

    /* Nav buttons */
    .nav-btns{display:flex;justify-content:space-between;align-items:center;margin-top:24px;flex-wrap:wrap;gap:10px;}
    .btn{display:inline-flex;align-items:center;gap:7px;padding:11px 24px;border-radius:13px;border:none;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .18s;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}
    .btn-primary:hover{opacity:.9;}
    .btn-prev{background:#f3f4f6;color:var(--c-muted);}
    .btn-prev:hover{background:#e5e7eb;}
    .btn-save{background:#dcfce7;color:#15803d;}
    .btn-save:hover{background:#bbf7d0;}

    /* Success */
    .success-box{text-align:center;padding:40px 20px;}
    .success-icon{width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--c-primary),var(--c-accent));display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2.5rem;color:#fff;}
    .success-title{font-size:1.4rem;font-weight:900;color:var(--c-text);margin-bottom:8px;}
    .success-sub{color:var(--c-muted);font-size:.9rem;line-height:1.7;}
    .success-ref{background:var(--c-soft);border-radius:12px;padding:12px 20px;margin:16px 0;font-size:1rem;font-weight:800;color:var(--c-primary);}

    /* Alert */
    .err-box{background:#fee2e2;color:#991b1b;border-radius:12px;padding:11px 14px;font-weight:700;font-size:.85rem;margin-bottom:14px;display:none;}

    .spin{display:inline-block;width:18px;height:18px;border:2.5px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg);}}

    /* ── LANDING ── */
    .tech-landing { display: block; }
    .tech-landing.hidden { display: none; }
    .tech-hero {
      min-height: 72vh; position: relative; overflow: hidden;
      background: #0f172a url('<?php echo $basePath; ?>assets/images/hero3.webp') center / cover no-repeat;
      display: flex; align-items: center;
    }
    .tech-hero::before {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(135deg, rgba(15,23,42,.93) 0%, rgba(30,27,75,.85) 40%, rgba(76,29,149,.6) 100%);
    }
    .tech-hero-inner {
      position: relative; z-index: 1; max-width: 1100px;
      margin: 0 auto; padding: 80px 24px;
      display: grid; grid-template-columns: 1fr auto; gap: 60px; align-items: center;
    }
    @media(max-width:860px){ .tech-hero-inner{ grid-template-columns:1fr; gap:40px; } }
    .tech-hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(124,58,237,.25); border: 1px solid rgba(167,139,250,.4);
      border-radius: 30px; padding: 6px 16px;
      font-size: .8rem; font-weight: 700; color: #c4b5fd; margin-bottom: 18px;
    }
    .tech-hero h1 { font-size: clamp(1.8rem,4.5vw,2.8rem); font-weight: 900; color: #fff; line-height: 1.2; margin-bottom: 14px; }
    .tech-hero h1 span { color: #a78bfa; }
    .tech-hero p { font-size: 1rem; color: rgba(255,255,255,.75); line-height: 1.75; margin-bottom: 28px; max-width: 480px; }
    .tech-hero-btns { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-tech-start {
      display: inline-flex; align-items: center; gap: 8px;
      background: linear-gradient(135deg,#7c3aed,#a78bfa); color: #fff;
      font-weight: 800; font-size: .95rem; padding: 13px 28px; border-radius: 14px;
      box-shadow: 0 8px 24px rgba(124,58,237,.4);
      border: none; cursor: pointer; transition: transform .2s, opacity .2s;
    }
    .btn-tech-start:hover { transform: translateY(-2px); opacity: .9; }
    .btn-tech-back {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.1); color: rgba(255,255,255,.85);
      font-weight: 700; font-size: .93rem; padding: 13px 22px; border-radius: 14px;
      border: 1.5px solid rgba(255,255,255,.25); text-decoration: none;
      transition: background .2s;
    }
    .btn-tech-back:hover { background: rgba(255,255,255,.18); color: #fff; }

    /* TRL visual */
    .trl-visual {
      background: rgba(255,255,255,.07); backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,.12); border-radius: 22px;
      padding: 24px; min-width: 220px;
    }
    .trl-title { font-size: .75rem; font-weight: 800; color: rgba(255,255,255,.5); letter-spacing: .5px; text-transform: uppercase; margin-bottom: 14px; }
    .trl-bar { display: flex; flex-direction: column; gap: 5px; }
    .trl-level { display: flex; align-items: center; gap: 10px; }
    .trl-num { font-size: .7rem; font-weight: 800; color: rgba(255,255,255,.4); width: 18px; flex-shrink: 0; }
    .trl-fill { height: 6px; border-radius: 99px; flex: 1; }
    .trl-lbl { font-size: .68rem; color: rgba(255,255,255,.35); width: 70px; flex-shrink: 0; }

    /* What this survey covers */
    .tc-section { max-width: 1100px; margin: 0 auto; padding: 64px 24px; }
    .tc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-top: 36px; }
    .tc-card {
      background: #fff; border: 1px solid rgba(124,58,237,.13); border-radius: 18px;
      padding: 22px; box-shadow: 0 6px 20px rgba(124,58,237,.07);
      transition: transform .2s;
    }
    .tc-card:hover { transform: translateY(-3px); }
    .tc-icon { width: 44px; height: 44px; border-radius: 13px; background: #f5f3ff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: var(--c-primary); margin-bottom: 12px; }
    .tc-title { font-size: .92rem; font-weight: 800; color: var(--c-text); margin-bottom: 4px; }
    .tc-desc  { font-size: .78rem; color: var(--c-muted); line-height: 1.6; }
    .tc-badge { display: inline-block; background: var(--c-soft); color: var(--c-primary); font-size: .68rem; font-weight: 800; padding: 2px 9px; border-radius: 20px; margin-bottom: 8px; }

    /* CTA before form */
    .tc-cta {
      background: linear-gradient(135deg,#1e1b4b,#4c1d95);
      border-radius: 22px; padding: 40px 32px; text-align: center;
      margin: 0 24px 64px;
    }
    .tc-cta h3 { font-size: 1.3rem; font-weight: 900; color: #fff; margin-bottom: 8px; }
    .tc-cta p  { color: rgba(255,255,255,.75); font-size: .93rem; margin-bottom: 22px; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>

<!-- ══ LANDING (shown first) ══ -->
<div class="tech-landing" id="techLanding">

  <!-- Hero -->
  <div class="tech-hero">
    <div class="tech-hero-inner">
      <div>
        <div class="tech-hero-badge"><i class="bi bi-cpu-fill"></i> رواد الأعمال التقني — SMEDC</div>
        <h1>سجّل مشروعك<br><span>التقني الابتكاري</span></h1>
        <p>
          استبيان متخصص في 14 محوراً يغطي كل جانب من جوانب مشروعك التقني —
          من الفكرة والتقنية إلى السوق والتمويل والفريق.
          يُستخدم لتقييم مشروعك ودعمه بالطريقة الصحيحة.
        </p>
        <div class="tech-hero-btns">
          <button class="btn-tech-start" onclick="startSurvey()">
            <i class="bi bi-clipboard2-check-fill"></i> ابدأ الاستبيان الآن
          </button>
          <a href="<?php echo $basePath; ?>services/incubation/entrepreneurship-hub.php" class="btn-tech-back">
            <i class="bi bi-arrow-right"></i> العودة للبوابة
          </a>
        </div>
      </div>

      <!-- TRL visual -->
      <div class="trl-visual">
        <div class="trl-title">مستوى الجاهزية التقنية TRL</div>
        <div class="trl-bar">
          <?php
          $levels = [
            ['١','10%','rgba(220,38,38,.7)','فكرة بحثية'],
            ['٢','18%','rgba(234,88,12,.7)','مفهوم التقنية'],
            ['٣','28%','rgba(202,138,4,.7)','إثبات المفهوم'],
            ['٤','40%','rgba(101,163,13,.7)','نموذج مختبر'],
            ['٥','52%','rgba(22,163,74,.7)','محاكاة'],
            ['٦','62%','rgba(5,150,105,.7)','بيئة حقيقية'],
            ['٧','72%','rgba(6,182,212,.7)','Beta'],
            ['٨','85%','rgba(37,99,235,.7)','جاهز للإطلاق'],
            ['٩','100%','rgba(124,58,237,.9)','في السوق'],
          ];
          foreach ($levels as $l): ?>
          <div class="trl-level">
            <span class="trl-num"><?= $l[0] ?></span>
            <div class="trl-fill" style="width:<?= $l[1] ?>;background:<?= $l[2] ?>"></div>
            <span class="trl-lbl"><?= $l[3] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- What the survey covers -->
  <div class="tc-section">
    <div style="text-align:center;margin-bottom:8px">
      <span style="display:inline-flex;align-items:center;gap:8px;background:#f5f3ff;color:#7c3aed;border-radius:30px;padding:6px 16px;font-size:.8rem;font-weight:800">
        <i class="bi bi-list-check"></i> محاور الاستبيان
      </span>
    </div>
    <h2 style="text-align:center;font-size:clamp(1.4rem,3vw,1.9rem);font-weight:900;color:#1e1b4b;margin:10px 0 0">ماذا يغطي الاستبيان؟</h2>
    <p style="text-align:center;color:#6b7280;font-size:.93rem;margin:10px auto 0;max-width:520px;line-height:1.7">
      14 محوراً شاملاً لتوصيف مشروعك التقني وتحديد احتياجاته الدقيقة
    </p>
    <div class="tc-grid">
      <div class="tc-card">
        <div class="tc-badge">المحور ١-٢</div>
        <div class="tc-icon"><i class="bi bi-person-fill"></i></div>
        <div class="tc-title">المعلومات الأساسية</div>
        <div class="tc-desc">بيانات رائد الأعمال، سيرته الذاتية، وخلفيته الأكاديمية والمهنية</div>
      </div>
      <div class="tc-card">
        <div class="tc-badge">المحور ٣-٤</div>
        <div class="tc-icon"><i class="bi bi-lightbulb-fill"></i></div>
        <div class="tc-title">الفكرة والمشكلة</div>
        <div class="tc-desc">وصف الفكرة التقنية، المشكلة التي تحلها، والحل المبتكر المقترح</div>
      </div>
      <div class="tc-card">
        <div class="tc-badge">المحور ٥</div>
        <div class="tc-icon"><i class="bi bi-cpu-fill"></i></div>
        <div class="tc-title">التقنية والمنتج</div>
        <div class="tc-desc">مستوى TRL، Tech Stack المستخدم، ونموذج الإيرادات التقني</div>
      </div>
      <div class="tc-card">
        <div class="tc-badge">المحور ٦-٧</div>
        <div class="tc-icon"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="tc-title">السوق والمنافسة</div>
        <div class="tc-desc">حجم السوق المستهدف، العملاء، والميزة التنافسية</div>
      </div>
      <div class="tc-card">
        <div class="tc-badge">المحور ٨</div>
        <div class="tc-icon"><i class="bi bi-people-fill"></i></div>
        <div class="tc-title">الفريق والكفاءات</div>
        <div class="tc-desc">تكوين الفريق التقني، أدوار الأعضاء، والكفاءات الأساسية</div>
      </div>
      <div class="tc-card">
        <div class="tc-badge">المحور ٩-١٠</div>
        <div class="tc-icon"><i class="bi bi-cash-stack"></i></div>
        <div class="tc-title">التمويل والجدوى</div>
        <div class="tc-desc">الوضع المالي الحالي، التمويل المطلوب، والجدوى الاقتصادية</div>
      </div>
      <div class="tc-card">
        <div class="tc-badge">المحور ١١-١٢</div>
        <div class="tc-icon"><i class="bi bi-shield-check-fill"></i></div>
        <div class="tc-title">الملكية الفكرية</div>
        <div class="tc-desc">حماية الابتكار، براءات الاختراع، وحقوق الملكية</div>
      </div>
      <div class="tc-card">
        <div class="tc-badge">المحور ١٣-١٤</div>
        <div class="tc-icon"><i class="bi bi-rocket-takeoff-fill"></i></div>
        <div class="tc-title">الأهداف والدعم</div>
        <div class="tc-desc">أهداف المشروع، احتياجات الدعم من الهيئة، وخطة التوسع</div>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div style="max-width:1100px;margin:0 auto">
  <div class="tc-cta">
    <h3>مستعد للبدء؟ الاستبيان لا يستغرق أكثر من 15 دقيقة</h3>
    <p>إجاباتك ستساعدنا في تقديم الدعم المناسب لمشروعك التقني بدقة</p>
    <button class="btn-tech-start" onclick="startSurvey()" style="font-size:1rem;padding:14px 36px">
      <i class="bi bi-clipboard2-check-fill"></i> ابدأ الاستبيان الآن
    </button>
  </div>
  </div>

</div><!-- /tech-landing -->

<!-- ══ SURVEY (hidden initially) ══ -->
<div id="surveySection" style="display:none">
<div class="pw">

  <!-- Header -->
  <div class="page-header">
    <div class="header-badge"><i class="bi bi-cpu-fill"></i> استبيان توصيف المشاريع التقنية الابتكارية</div>
    <h1>سجّل مشروعك التقني مع SMEDA</h1>
    <p>أجب على 14 محوراً لنتمكن من تقديم أفضل دعم لمشروعك الابتكاري</p>
  </div>

  <!-- Progress -->
  <div class="progress-wrap">
    <div class="progress-steps" id="progressSteps"></div>
    <div class="progress-bar-wrap"><div class="progress-bar-fill" id="progressBar" style="width:7%"></div></div>
    <div class="progress-text" id="progressText">الخطوة 1 من 14</div>
  </div>

  <!-- Error -->
  <div class="err-box" id="errBox"></div>

  <!-- ══════════════════════════════════════
       STEP 1 — معلومات رائد الأعمال
  ══════════════════════════════════════ -->
  <div class="step-card active" data-step="1">
    <div class="step-num"><i class="bi bi-person-fill"></i> المحور الأول</div>
    <div class="step-title">معلومات رائد الأعمال</div>
    <div class="fr">
      <div class="fg"><label class="fl">الاسم الثلاثي <span>*</span></label><input type="text" id="full_name" class="fi" placeholder="الاسم الكامل"></div>
      <div class="fg"><label class="fl">المحافظة</label>
        <select id="governorate" class="fs">
          <option value="">-- اختر --</option>
          <?php foreach(['دمشق','ريف دمشق','حلب','حمص','حماة','اللاذقية','طرطوس','درعا','السويداء','القنيطرة','دير الزور','الرقة','الحسكة','إدلب'] as $g): ?>
          <option value="<?php echo $g;?>"><?php echo $g;?></option>
          <?php endforeach;?>
        </select>
      </div>
    </div>
    <div class="fr">
      <div class="fg"><label class="fl">رقم الهاتف</label><input type="tel" id="phone" class="fi" placeholder="+963 9xx xxx xxxx"></div>
      <div class="fg"><label class="fl">البريد الإلكتروني</label><input type="email" id="email" class="fi" placeholder="email@example.com"></div>
    </div>
    <div class="fr">
      <div class="fg"><label class="fl">العمر</label><input type="number" id="age" class="fi" placeholder="25" min="16" max="80"></div>
      <div class="fg"><label class="fl">المؤهل العلمي</label>
        <select id="education_level" class="fs">
          <option value="">-- اختر --</option>
          <option value="diploma">دبلوم</option>
          <option value="bachelor">بكالوريوس</option>
          <option value="master">ماجستير</option>
          <option value="phd">دكتوراه</option>
          <option value="other">أخرى</option>
        </select>
      </div>
    </div>
    <div class="fg"><label class="fl">الاختصاص</label><input type="text" id="specialization" class="fi" placeholder="مثال: هندسة حاسوب، علم بيانات، ذكاء اصطناعي..."></div>
  </div>

  <!-- STEP 2 — معلومات المشروع -->
  <div class="step-card" data-step="2">
    <div class="step-num"><i class="bi bi-rocket-fill"></i> المحور الثاني</div>
    <div class="step-title">معلومات المشروع</div>
    <div class="fg"><label class="fl">اسم المشروع <span>*</span></label><input type="text" id="project_name" class="fi" placeholder="اسم مشروعك..."></div>
    <div class="fg">
      <label class="fl">مجال المشروع</label>
      <div class="cb-grid">
        <?php
        $fields = [
          'ai'=>['bi-robot','الذكاء الاصطناعي'],
          'mobile_apps'=>['bi-phone-fill','التطبيقات الذكية'],
          'software'=>['bi-code-slash','البرمجيات'],
          'games'=>['bi-controller','الألعاب الإلكترونية'],
          'ecommerce'=>['bi-cart-fill','التجارة الإلكترونية'],
          'cybersecurity'=>['bi-shield-fill','الأمن السيبراني'],
          'fintech'=>['bi-currency-bitcoin','التكنولوجيا المالية (FinTech)'],
          'elearning'=>['bi-book-fill','التعليم الإلكتروني'],
          'digital_health'=>['bi-heart-pulse-fill','الصحة الرقمية'],
          'iot'=>['bi-cpu-fill','إنترنت الأشياء (IoT)'],
          'blockchain'=>['bi-link-45deg','البلوك تشين'],
          'other'=>['bi-three-dots','غير ذلك'],
        ];
        foreach($fields as $val=>[$icon,$lbl]):?>
        <div class="cb-item">
          <input type="radio" name="project_field" id="pf_<?php echo $val;?>" value="<?php echo $val;?>">
          <label for="pf_<?php echo $val;?>"><div class="rb-dot"></div><i class="bi <?php echo $icon;?>"></i><?php echo $lbl;?></label>
        </div>
        <?php endforeach;?>
      </div>
    </div>
    <div class="fg" id="field_other_wrap" style="display:none">
      <label class="fl">اذكر المجال</label>
      <input type="text" id="project_field_other" class="fi" placeholder="اذكر مجال مشروعك...">
    </div>
    <div class="fg"><label class="fl">سنة تأسيس المشروع</label><input type="number" id="founding_year" class="fi" placeholder="2024" min="2000" max="2030"></div>
  </div>

  <!-- STEP 3 — الملخص التنفيذي -->
  <div class="step-card" data-step="3">
    <div class="step-num"><i class="bi bi-file-earmark-text-fill"></i> المحور الثالث</div>
    <div class="step-title">الملخص التنفيذي للمشروع</div>
    <div class="sec-label"><i class="bi bi-info-circle-fill"></i> يرجى توضيح: المشكلة التي يعالجها — الحل — الفئة المستهدفة — القيمة المضافة — المرحلة الحالية — وصف المشروع</div>
    <div class="fg">
      <label class="fl">عرّف مشروعك (150 – 250 كلمة) <span>*</span></label>
      <div class="ta-wrap">
        <textarea id="executive_summary" class="fi" rows="7" placeholder="اشرح مشروعك بشكل شامل: المشكلة التي تحلها، الحل الذي تقدمه، الفئة المستهدفة، القيمة المضافة، وأين أنت الآن..." oninput="countWords('executive_summary','wc1',150,250)"></textarea>
        <div class="ta-count" id="wc1">0 كلمة</div>
      </div>
    </div>
    <div class="fg">
      <label class="fl"><i class="bi bi-lightning-fill" style="color:var(--c-accent)"></i> إذا كان لديك دقيقة واحدة فقط أمام مستثمر، ماذا ستقول؟ <span>*</span></label>
      <div class="ta-wrap">
        <textarea id="elevator_pitch" class="fi" rows="4" placeholder="اجعلها مقنعة وموجزة — المشكلة، الحل، السوق، لماذا أنت..."></textarea>
      </div>
    </div>
  </div>

  <!-- STEP 4 — مستوى الجاهزية -->
  <div class="step-card" data-step="4">
    <div class="step-num"><i class="bi bi-speedometer2"></i> المحور الرابع</div>
    <div class="step-title">مستوى جاهزية المشروع</div>
    <div class="fg">
      <label class="fl">ما المرحلة الحالية للمشروع؟</label>
      <div class="rb-grid">
        <?php foreach([
          'idea'=>['bi-lightbulb-fill','فكرة فقط','#6b7280'],
          'mvp'=>['bi-tools','نموذج أولي MVP','#b45309'],
          'usable'=>['bi-box-fill','منتج قابل للاستخدام','#0369a1'],
          'has_users'=>['bi-people-fill','لديه مستخدمون فعليون','#7c3aed'],
          'revenue'=>['bi-cash-coin','يحقق إيرادات','#15803d'],
          'scalable'=>['bi-graph-up-arrow','شركة ناشئة قابلة للتوسع','#be123c'],
        ] as $v=>[$ic,$lbl,$col]):?>
        <div class="rb-item">
          <input type="radio" name="readiness_stage" id="rs_<?php echo $v;?>" value="<?php echo $v;?>">
          <label for="rs_<?php echo $v;?>"><div class="rb-dot"></div><i class="bi <?php echo $ic;?>" style="color:<?php echo $col;?>"></i><?php echo $lbl;?></label>
        </div>
        <?php endforeach;?>
      </div>
    </div>
    <div class="fr" style="margin-top:8px">
      <div class="fg">
        <label class="fl">هل يوجد نموذج أولي؟</label>
        <div style="display:flex;gap:10px;margin-top:4px">
          <div class="rb-item"><input type="radio" name="has_prototype" id="hp_yes" value="1"><label for="hp_yes"><div class="rb-dot"></div> نعم</label></div>
          <div class="rb-item"><input type="radio" name="has_prototype" id="hp_no" value="0"><label for="hp_no"><div class="rb-dot"></div> لا</label></div>
        </div>
      </div>
      <div class="fg">
        <label class="fl">هل تم اختبار المنتج مع مستخدمين حقيقيين؟</label>
        <div style="display:flex;gap:10px;margin-top:4px">
          <div class="rb-item"><input type="radio" name="tested_with_users" id="tu_yes" value="1"><label for="tu_yes"><div class="rb-dot"></div> نعم</label></div>
          <div class="rb-item"><input type="radio" name="tested_with_users" id="tu_no" value="0"><label for="tu_no"><div class="rb-dot"></div> لا</label></div>
        </div>
      </div>
    </div>
    <div class="fg" id="testing_results_wrap" style="display:none">
      <label class="fl">إذا كانت الإجابة نعم، اذكر أهم النتائج:</label>
      <textarea id="testing_results" class="fi" rows="3" placeholder="النتائج والأرقام..."></textarea>
    </div>
  </div>

  <!-- STEP 5 — المشكلة والحل -->
  <div class="step-card" data-step="5">
    <div class="step-num"><i class="bi bi-patch-question-fill"></i> المحور الخامس</div>
    <div class="step-title">المشكلة والحل</div>
    <div class="fg"><label class="fl">ما المشكلة التي يعمل المشروع على حلها؟ <span>*</span></label><textarea id="problem_description" class="fi" rows="4" placeholder="صف المشكلة بوضوح وتحقق من حجمها..."></textarea></div>
    <div class="fg"><label class="fl">من هم العملاء المستهدفون؟</label><textarea id="target_customers" class="fi" rows="3" placeholder="حدد الفئة بدقة — العمر، الموقع، الاحتياج..."></textarea></div>
    <div class="fg"><label class="fl">ما الذي يجعل مشروعك مختلفاً عن الحلول الموجودة؟</label><textarea id="differentiation" class="fi" rows="3" placeholder="ما لا يستطيع المنافسون تقديمه؟"></textarea></div>
    <div class="fg">
      <label class="fl">ما أهم ميزة تنافسية يمتلكها مشروعك؟ (اختر ما ينطبق)</label>
      <div class="cb-grid">
        <?php foreach(['tech'=>'تقنية','price'=>'سعر','speed'=>'سرعة','ux'=>'تجربة مستخدم','ai'=>'ذكاء اصطناعي','ease'=>'سهولة الاستخدام','other'=>'أخرى'] as $v=>$l):?>
        <div class="cb-item"><input type="checkbox" name="competitive_advantages" id="ca_<?php echo $v;?>" value="<?php echo $v;?>"><label for="ca_<?php echo $v;?>"><i class="bi bi-check-circle"></i><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
  </div>

  <!-- STEP 6 — الفريق -->
  <div class="step-card" data-step="6">
    <div class="step-num"><i class="bi bi-people-fill"></i> المحور السادس</div>
    <div class="step-title">الفريق</div>
    <div class="fg">
      <label class="fl">عدد أعضاء الفريق:</label>
      <div class="rb-grid">
        <?php foreach(['solo'=>'مؤسس واحد','2-5'=>'2 – 5','6-10'=>'6 – 10','10+'=>'أكثر من 10'] as $v=>$l):?>
        <div class="rb-item"><input type="radio" name="team_size_range" id="ts_<?php echo preg_replace('/[^a-z0-9]/i','_',$v);?>" value="<?php echo $v;?>"><label for="ts_<?php echo preg_replace('/[^a-z0-9]/i','_',$v);?>"><div class="rb-dot"></div><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
    <div class="fg">
      <label class="fl">يتضمن الفريق: (اختر ما ينطبق)</label>
      <div class="cb-grid">
        <?php foreach(['developers'=>['bi-code-slash','مطورون'],'ai'=>['bi-robot','مختصو ذكاء اصطناعي'],'designers'=>['bi-palette-fill','مصممون'],'data'=>['bi-database-fill','مختصو بيانات'],'marketing'=>['bi-megaphone-fill','مختصو تسويق'],'sales'=>['bi-bag-fill','مختصو مبيعات'],'management'=>['bi-briefcase-fill','إدارة أعمال']] as $v=>[$ic,$l]):?>
        <div class="cb-item"><input type="checkbox" name="team_roles" id="tr_<?php echo $v;?>" value="<?php echo $v;?>"><label for="tr_<?php echo $v;?>"><i class="bi <?php echo $ic;?>"></i><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
  </div>

  <!-- STEP 7 — التقنيات -->
  <div class="step-card" data-step="7">
    <div class="step-num"><i class="bi bi-cpu-fill"></i> المحور السابع</div>
    <div class="step-title">التقنيات المستخدمة في المشروع</div>
    <div class="cb-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
      <?php foreach(['ai'=>['bi-robot','الذكاء الاصطناعي (AI)'],'ml'=>['bi-graph-up','Machine Learning'],'llm'=>['bi-chat-dots-fill','نماذج لغوية (LLM)'],'mobile'=>['bi-phone-fill','تطبيقات جوال'],'cloud'=>['bi-cloud-fill','Cloud Computing'],'cybersecurity'=>['bi-shield-fill','الأمن السيبراني'],'blockchain'=>['bi-link-45deg','Blockchain'],'data'=>['bi-bar-chart-fill','تحليل البيانات'],'iot'=>['bi-cpu-fill','إنترنت الأشياء (IoT)'],'other'=>['bi-three-dots','أخرى']] as $v=>[$ic,$l]):?>
      <div class="cb-item"><input type="checkbox" name="technologies" id="tech_<?php echo $v;?>" value="<?php echo $v;?>"><label for="tech_<?php echo $v;?>"><i class="bi <?php echo $ic;?>"></i><?php echo $l;?></label></div>
      <?php endforeach;?>
    </div>
  </div>

  <!-- STEP 8 — السوق والعملاء -->
  <div class="step-card" data-step="8">
    <div class="step-num"><i class="bi bi-globe2"></i> المحور الثامن</div>
    <div class="step-title">السوق والعملاء</div>
    <div class="fg">
      <label class="fl">كيف تأكدت من وجود حاجة حقيقية للمشروع؟ (اختر ما ينطبق)</label>
      <div class="cb-grid">
        <?php foreach(['interviews'=>'مقابلات عملاء','surveys'=>'استبيانات','actual_use'=>'تجربة فعلية','usage_data'=>'بيانات استخدام','market_study'=>'دراسة سوق','not_yet'=>'لم يتم التحقق بعد'] as $v=>$l):?>
        <div class="cb-item"><input type="checkbox" name="market_validation_methods" id="mv_<?php echo $v;?>" value="<?php echo $v;?>"><label for="mv_<?php echo $v;?>"><i class="bi bi-check-circle"></i><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
    <div class="fg">
      <label class="fl">السوق المستهدف:</label>
      <div class="rb-grid">
        <?php foreach(['syria'=>['bi-geo-fill','سوريا'],'arab'=>['bi-map-fill','المنطقة العربية'],'global'=>['bi-globe2','السوق العالمي']] as $v=>[$ic,$l]):?>
        <div class="rb-item"><input type="radio" name="target_market" id="tm_<?php echo $v;?>" value="<?php echo $v;?>"><label for="tm_<?php echo $v;?>"><div class="rb-dot"></div><i class="bi <?php echo $ic;?>"></i><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
    <div class="fr">
      <div class="fg">
        <label class="fl">عدد المستخدمين الحاليين:</label>
        <select id="current_users_range" class="fs">
          <option value="none">لا يوجد</option>
          <option value="<100">أقل من 100</option>
          <option value="100-1000">100 – 1,000</option>
          <option value="1000-10000">1,000 – 10,000</option>
          <option value=">10000">أكثر من 10,000</option>
        </select>
      </div>
      <div class="fg">
        <label class="fl">عدد العملاء الحاليين (يدفعون):</label>
        <select id="current_customers_range" class="fs">
          <option value="none">لا يوجد</option>
          <option value="<10">أقل من 10</option>
          <option value="10-50">10 – 50</option>
          <option value="50-200">50 – 200</option>
          <option value=">200">أكثر من 200</option>
        </select>
      </div>
    </div>
  </div>

  <!-- STEP 9 — الإيرادات -->
  <div class="step-card" data-step="9">
    <div class="step-num"><i class="bi bi-cash-coin"></i> المحور التاسع</div>
    <div class="step-title">الإيرادات والاستدامة</div>
    <div class="fg">
      <label class="fl">هل يحقق المشروع إيرادات؟</label>
      <div style="display:flex;gap:10px;margin-top:4px">
        <div class="rb-item"><input type="radio" name="has_revenue" id="hr_yes" value="1" onchange="toggleRevSources(true)"><label for="hr_yes"><div class="rb-dot"></div><i class="bi bi-check-circle-fill" style="color:#15803d"></i> نعم</label></div>
        <div class="rb-item"><input type="radio" name="has_revenue" id="hr_no" value="0" onchange="toggleRevSources(false)"><label for="hr_no"><div class="rb-dot"></div><i class="bi bi-x-circle-fill" style="color:#dc2626"></i> لا</label></div>
      </div>
    </div>
    <div class="fg" id="rev_sources_wrap" style="display:none">
      <label class="fl">مصدر الإيرادات: (اختر ما ينطبق)</label>
      <div class="cb-grid">
        <?php foreach(['subscriptions'=>'اشتراكات','services'=>'خدمات','direct_sales'=>'مبيعات مباشرة','commissions'=>'عمولات','ads'=>'إعلانات','other'=>'أخرى'] as $v=>$l):?>
        <div class="cb-item"><input type="checkbox" name="revenue_sources" id="rs2_<?php echo $v;?>" value="<?php echo $v;?>"><label for="rs2_<?php echo $v;?>"><i class="bi bi-check-circle"></i><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
  </div>

  <!-- STEP 10 — التمويل -->
  <div class="step-card" data-step="10">
    <div class="step-num"><i class="bi bi-bank2"></i> المحور العاشر</div>
    <div class="step-title">التمويل والاستثمار</div>
    <div class="fg">
      <label class="fl">كيف تم تمويل المشروع حتى الآن؟ (اختر ما ينطبق)</label>
      <div class="cb-grid">
        <?php foreach(['personal'=>'تمويل شخصي','partner'=>'شريك','investor'=>'مستثمر','grant'=>'منحة','incubator'=>'حاضنة أعمال','accelerator'=>'مسرّعة أعمال','none'=>'لم يحصل على تمويل'] as $v=>$l):?>
        <div class="cb-item"><input type="checkbox" name="funding_sources" id="fs_<?php echo $v;?>" value="<?php echo $v;?>"><label for="fs_<?php echo $v;?>"><i class="bi bi-check-circle"></i><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
    <div class="fg">
      <label class="fl">هل تبحث حالياً عن استثمار؟</label>
      <div style="display:flex;gap:10px;margin-top:4px">
        <div class="rb-item"><input type="radio" name="seeking_investment" id="si_yes" value="1" onchange="toggleInvSize(true)"><label for="si_yes"><div class="rb-dot"></div> نعم</label></div>
        <div class="rb-item"><input type="radio" name="seeking_investment" id="si_no" value="0" onchange="toggleInvSize(false)"><label for="si_no"><div class="rb-dot"></div> لا</label></div>
      </div>
    </div>
    <div class="fg" id="inv_size_wrap" style="display:none">
      <label class="fl">حجم الاستثمار المطلوب:</label>
      <div class="rb-grid">
        <?php foreach(['<10k'=>'أقل من 10,000 $','10-50k'=>'10,000 – 50,000 $','50-250k'=>'50,000 – 250,000 $','>250k'=>'أكثر من 250,000 $'] as $v=>$l):?>
        <div class="rb-item"><input type="radio" name="investment_needed_range" id="ir_<?php echo preg_replace('/[^a-z0-9]/i','_',$v);?>" value="<?php echo $v;?>"><label for="ir_<?php echo preg_replace('/[^a-z0-9]/i','_',$v);?>"><div class="rb-dot"></div><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
  </div>

  <!-- STEP 11 — التحديات -->
  <div class="step-card" data-step="11">
    <div class="step-num"><i class="bi bi-exclamation-triangle-fill"></i> المحور الحادي عشر</div>
    <div class="step-title">التحديات التي تواجه مشروعك</div>
    <div class="cb-grid" style="grid-template-columns:repeat(auto-fill,minmax(190px,1fr))">
      <?php foreach(['funding'=>['bi-bank2','التمويل'],'marketing'=>['bi-megaphone-fill','التسويق'],'customers'=>['bi-people-fill','الوصول للعملاء'],'tech'=>['bi-code-slash','التطوير التقني'],'team'=>['bi-person-plus-fill','بناء الفريق'],'regulations'=>['bi-file-earmark-fill','التشريعات'],'expansion'=>['bi-arrows-expand','التوسع'],'export'=>['bi-globe2','التصدير'],'other'=>['bi-three-dots','أخرى']] as $v=>[$ic,$l]):?>
      <div class="cb-item"><input type="checkbox" name="challenges" id="ch_<?php echo $v;?>" value="<?php echo $v;?>"><label for="ch_<?php echo $v;?>"><i class="bi <?php echo $ic;?>"></i><?php echo $l;?></label></div>
      <?php endforeach;?>
    </div>
  </div>

  <!-- STEP 12 — الأثر والنمو -->
  <div class="step-card" data-step="12">
    <div class="step-num"><i class="bi bi-graph-up-arrow"></i> المحور الثاني عشر</div>
    <div class="step-title">الأثر والنمو</div>
    <div class="fg">
      <label class="fl">كم فرصة عمل يمكن أن يوفرها مشروعك خلال 3 سنوات قادمة؟</label>
      <div class="rb-grid">
        <?php foreach(['<5'=>'أقل من 5','5-20'=>'5 – 20','20-50'=>'20 – 50','>50'=>'أكثر من 50'] as $v=>$l):?>
        <div class="rb-item"><input type="radio" name="jobs_3years_range" id="jr_<?php echo preg_replace('/[^a-z0-9]/i','_',$v);?>" value="<?php echo $v;?>"><label for="jr_<?php echo preg_replace('/[^a-z0-9]/i','_',$v);?>"><div class="rb-dot"></div><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
    <div class="fg">
      <label class="fl">هل يمتلك مشروعك قابلية للتوسع خارج سوريا؟</label>
      <div class="rb-grid" style="grid-template-columns:repeat(3,1fr)">
        <?php foreach(['yes'=>'نعم','no'=>'لا','studying'=>'قيد الدراسة'] as $v=>$l):?>
        <div class="rb-item"><input type="radio" name="scalability_outside_syria" id="sos_<?php echo $v;?>" value="<?php echo $v;?>"><label for="sos_<?php echo $v;?>"><div class="rb-dot"></div><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
  </div>

  <!-- STEP 13 — احتياجات المشروع -->
  <div class="step-card" data-step="13">
    <div class="step-num"><i class="bi bi-hand-index-fill"></i> المحور الثالث عشر</div>
    <div class="step-title">ما نوع الدعم الذي تحتاجه حالياً؟</div>
    <div class="cb-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
      <?php foreach(['funding'=>['bi-bank2','تمويل'],'incubation'=>['bi-rocket-fill','احتضان'],'acceleration'=>['bi-fast-forward-fill','تسريع أعمال'],'investor_network'=>['bi-people-fill','تشبيك مع مستثمرين'],'training'=>['bi-mortarboard-fill','تدريب'],'tech_consulting'=>['bi-code-slash','استشارات تقنية'],'business_consulting'=>['bi-briefcase-fill','استشارات أعمال'],'marketing'=>['bi-megaphone-fill','تسويق'],'partnerships'=>['bi-diagram-3-fill','شراكات']] as $v=>[$ic,$l]):?>
      <div class="cb-item"><input type="checkbox" name="support_needed" id="sn_<?php echo $v;?>" value="<?php echo $v;?>"><label for="sn_<?php echo $v;?>"><i class="bi <?php echo $ic;?>"></i><?php echo $l;?></label></div>
      <?php endforeach;?>
    </div>
  </div>

  <!-- STEP 14 — معلومات إضافية -->
  <div class="step-card" data-step="14">
    <div class="step-num"><i class="bi bi-info-circle-fill"></i> المحور الرابع عشر</div>
    <div class="step-title">معلومات إضافية</div>
    <div class="fg">
      <label class="fl">هل شارك مشروعك سابقاً في: (اختر ما ينطبق)</label>
      <div class="cb-grid">
        <?php foreach(['incubation'=>'برامج احتضان','competitions'=>'مسابقات ريادية','exhibitions'=>'معارض تقنية','funding_programs'=>'برامج تمويل','accelerators'=>'مسرّعات أعمال','none'=>'لم يشارك'] as $v=>$l):?>
        <div class="cb-item"><input type="checkbox" name="previous_participation" id="pp_<?php echo $v;?>" value="<?php echo $v;?>"><label for="pp_<?php echo $v;?>"><i class="bi bi-check-circle"></i><?php echo $l;?></label></div>
        <?php endforeach;?>
      </div>
    </div>
    <div class="fg">
      <label class="fl">أي معلومات إضافية ترغب بمشاركتها؟</label>
      <textarea id="additional_notes" class="fi" rows="4" placeholder="روابط، ملاحظات، أي شيء تريد إضافته..."></textarea>
    </div>
    <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;border-radius:16px;padding:16px 18px;margin-top:8px">
      <div style="font-weight:800;color:#15803d;font-size:.9rem;margin-bottom:6px"><i class="bi bi-check-circle-fill"></i> أنت على وشك إرسال استبيانك</div>
      <div style="font-size:.83rem;color:#166534;line-height:1.7">بعد الإرسال سيقوم فريق SMEDA بمراجعة طلبك والتواصل معك خلال 5-7 أيام عمل.</div>
    </div>
  </div>

  <!-- SUCCESS -->
  <div class="step-card" data-step="15">
    <div class="success-box">
      <div class="success-icon"><i class="bi bi-check-lg"></i></div>
      <div class="success-title">تم استلام استبيانك بنجاح!</div>
      <div class="success-ref" id="submittedRef"></div>
      <div class="success-sub">شكراً لك! سيقوم فريق SMEDA بمراجعة بيانات مشروعك والتواصل معك<br>خلال <strong>5–7 أيام عمل</strong> على البريد الإلكتروني ورقم الهاتف المدخلين.</div>
      <div style="display:flex;gap:10px;justify-content:center;margin-top:24px;flex-wrap:wrap">
        <a href="<?php echo $basePath;?>services/incubation/incubation-apply.php" class="btn btn-primary">
          <i class="bi bi-rocket-takeoff-fill"></i> تقديم طلب انضمام لحاضنة
        </a>
        <a href="<?php echo $basePath;?>" class="btn btn-prev">
          <i class="bi bi-house-fill"></i> الصفحة الرئيسية
        </a>
      </div>
    </div>
  </div>

  <!-- Nav buttons -->
  <div class="nav-btns" id="navBtns">
    <button class="btn btn-prev" id="btnPrev" onclick="prevStep()" style="display:none"><i class="bi bi-arrow-right-circle-fill"></i> السابق</button>
    <div style="display:flex;gap:10px">
      <button class="btn btn-save" id="btnSave" onclick="saveDraft()"><i class="bi bi-floppy-fill"></i> حفظ مسودة</button>
      <button class="btn btn-primary" id="btnNext" onclick="nextStep()">التالي <i class="bi bi-arrow-left-circle-fill"></i></button>
    </div>
  </div>

</div><!-- /pw -->

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const BASE  = window.APP_CONFIG.API_BASE_URL;
  const H     = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, 'Content-Type':'application/json', Accept:'application/json' });

  const TOTAL = 14;
  let currentStep = 1;

  /* ── Step labels ── */
  const STEP_LABELS = [
    'رائد الأعمال','المشروع','الملخص','الجاهزية','المشكلة والحل',
    'الفريق','التقنيات','السوق','الإيرادات','التمويل',
    'التحديات','الأثر','الاحتياجات','معلومات إضافية'
  ];

  /* ── Build progress dots ── */
  const stepsEl = document.getElementById('progressSteps');
  STEP_LABELS.forEach((lbl, i) => {
    stepsEl.innerHTML += `<div class="ps ${i===0?'active':''}" id="psDot${i+1}">
      <div class="ps-dot">${i+1}</div>
      <div class="ps-label">${lbl}</div>
    </div>`;
  });

  /* ── Navigation ── */
  function updateUI() {
    document.querySelectorAll('.step-card').forEach(c => c.classList.remove('active'));
    const card = document.querySelector(`.step-card[data-step="${currentStep}"]`);
    if (card) card.classList.add('active');

    document.querySelectorAll('.ps').forEach((dot, i) => {
      dot.classList.remove('active','done');
      if (i+1 < currentStep)  dot.classList.add('done');
      if (i+1 === currentStep) dot.classList.add('active');
    });

    const pct = Math.min(100, Math.round((currentStep-1)/TOTAL*100));
    document.getElementById('progressBar').style.width = (pct||4)+'%';
    document.getElementById('progressText').textContent = currentStep <= TOTAL
      ? `الخطوة ${currentStep} من ${TOTAL}`
      : 'مكتمل ✅';

    document.getElementById('btnPrev').style.display = currentStep > 1 && currentStep <= TOTAL ? '' : 'none';
    document.getElementById('navBtns').style.display  = currentStep > TOTAL ? 'none' : '';
    document.getElementById('btnNext').innerHTML      = currentStep === TOTAL
      ? '<i class="bi bi-send-fill"></i> إرسال الاستبيان'
      : 'التالي <i class="bi bi-arrow-left-circle-fill"></i>';

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  /* ── Validation per step ── */
  function validateStep(step) {
    const err = document.getElementById('errBox');
    err.style.display = 'none';
    const show = (msg) => { err.textContent = msg; err.style.display='block'; window.scrollTo({top:0,behavior:'smooth'}); return false; };

    if (step === 1) {
      if (!document.getElementById('full_name').value.trim()) return show('يرجى إدخال الاسم الثلاثي');
    }
    if (step === 2) {
      if (!document.getElementById('project_name').value.trim()) return show('يرجى إدخال اسم المشروع');
    }
    if (step === 3) {
      const summary = document.getElementById('executive_summary').value.trim();
      if (!summary) return show('يرجى كتابة الملخص التنفيذي');
      const wc = summary.split(/\s+/).filter(Boolean).length;
      if (wc < 50) return show(`الملخص قصير جداً (${wc} كلمة). يرجى كتابة 150 كلمة على الأقل`);
      if (!document.getElementById('elevator_pitch').value.trim()) return show('يرجى كتابة عرض الدقيقة الواحدة');
    }
    if (step === 5) {
      if (!document.getElementById('problem_description').value.trim()) return show('يرجى وصف المشكلة التي يعالجها مشروعك');
    }
    return true;
  }

  window.nextStep = function() {
    if (!validateStep(currentStep)) return;
    if (currentStep === TOTAL) { submitForm(); return; }
    currentStep++;
    updateUI();
  };

  window.prevStep = function() {
    if (currentStep > 1) { currentStep--; updateUI(); }
  };

  /* ── Collect data ── */
  function collectData(status='draft') {
    const checked = (name) => [...document.querySelectorAll(`input[name="${name}"]:checked`)].map(i=>i.value);
    const radio   = (name) => document.querySelector(`input[name="${name}"]:checked`)?.value || null;

    return {
      status,
      full_name:          document.getElementById('full_name').value.trim(),
      governorate:        document.getElementById('governorate').value||null,
      phone:              document.getElementById('phone').value.trim()||null,
      email:              document.getElementById('email').value.trim()||null,
      age:                parseInt(document.getElementById('age').value)||null,
      education_level:    document.getElementById('education_level').value||null,
      specialization:     document.getElementById('specialization').value.trim()||null,
      project_name:       document.getElementById('project_name').value.trim(),
      project_field:      radio('project_field'),
      project_field_other:document.getElementById('project_field_other').value.trim()||null,
      founding_year:      parseInt(document.getElementById('founding_year').value)||null,
      executive_summary:  document.getElementById('executive_summary').value.trim()||null,
      elevator_pitch:     document.getElementById('elevator_pitch').value.trim()||null,
      readiness_stage:    radio('readiness_stage'),
      has_prototype:      radio('has_prototype') === '1',
      tested_with_users:  radio('tested_with_users') === '1',
      testing_results:    document.getElementById('testing_results').value.trim()||null,
      problem_description:document.getElementById('problem_description').value.trim()||null,
      target_customers:   document.getElementById('target_customers').value.trim()||null,
      differentiation:    document.getElementById('differentiation').value.trim()||null,
      competitive_advantages: checked('competitive_advantages'),
      team_size_range:    radio('team_size_range'),
      team_roles:         checked('team_roles'),
      technologies:       checked('technologies'),
      market_validation_methods: checked('market_validation_methods'),
      target_market:      radio('target_market'),
      current_users_range:    document.getElementById('current_users_range').value||null,
      current_customers_range:document.getElementById('current_customers_range').value||null,
      has_revenue:        radio('has_revenue') === '1',
      revenue_sources:    checked('revenue_sources'),
      funding_sources:    checked('funding_sources'),
      seeking_investment: radio('seeking_investment') === '1',
      investment_needed_range: radio('investment_needed_range'),
      challenges:         checked('challenges'),
      jobs_3years_range:  radio('jobs_3years_range'),
      scalability_outside_syria: radio('scalability_outside_syria'),
      support_needed:     checked('support_needed'),
      previous_participation: checked('previous_participation'),
      additional_notes:   document.getElementById('additional_notes').value.trim()||null,
    };
  }

  /* ── Save draft ── */
  window.saveDraft = async function() {
    const btn = document.getElementById('btnSave');
    btn.disabled=true; btn.innerHTML='<div class="spin" style="border-top-color:#15803d;border-color:rgba(21,128,61,.3)"></div> جاري الحفظ...';

    const res = await fetch(`${BASE}/entrepreneur/profile`, {
      method:'POST', headers:H(), body:JSON.stringify(collectData('draft'))
    });
    btn.disabled=false; btn.innerHTML='<i class="bi bi-floppy-fill"></i> حفظ مسودة';

    if (res.ok) {
      const err = document.getElementById('errBox');
      err.textContent='✅ تم حفظ المسودة بنجاح';
      err.style.background='#dcfce7'; err.style.color='#14532d'; err.style.display='block';
      setTimeout(()=>err.style.display='none', 3000);
    }
  };

  /* ── Submit ── */
  async function submitForm() {
    const btn = document.getElementById('btnNext');
    btn.disabled=true; btn.innerHTML='<div class="spin"></div> جاري الإرسال...';

    const res = await fetch(`${BASE}/entrepreneur/profile`, {
      method:'POST', headers:H(), body:JSON.stringify(collectData('submitted'))
    });

    btn.disabled=false;
    if (res.ok) {
      const data = await res.json();
      document.getElementById('submittedRef').textContent =
        `رقم الطلب: #${data.id} — مشروع: ${data.project_name}`;
      currentStep = 15;
      updateUI();
    } else {
      const j = await res.json();
      const err = document.getElementById('errBox');
      err.textContent = Object.values(j.errors??{}).flat()[0] || j.message || 'حدث خطأ في الإرسال';
      err.style.background='#fee2e2'; err.style.color='#991b1b'; err.style.display='block';
    }
  }

  /* ── Word counter ── */
  window.countWords = function(id, counterId, min, max) {
    const val = document.getElementById(id).value.trim();
    const wc  = val ? val.split(/\s+/).filter(Boolean).length : 0;
    const el  = document.getElementById(counterId);
    el.textContent = `${wc} كلمة`;
    el.style.color = wc < min ? '#dc2626' : wc > max ? '#b45309' : '#15803d';
  };

  /* ── Conditional reveals ── */
  document.querySelectorAll('input[name="project_field"]').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('field_other_wrap').style.display = r.value==='other' ? '' : 'none';
    });
  });

  document.querySelectorAll('input[name="tested_with_users"]').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('testing_results_wrap').style.display = r.value==='1' ? '' : 'none';
    });
  });

  window.toggleRevSources = (show) => {
    document.getElementById('rev_sources_wrap').style.display = show ? '' : 'none';
  };
  window.toggleInvSize = (show) => {
    document.getElementById('inv_size_wrap').style.display = show ? '' : 'none';
  };

  /* ── Pre-fill from saved profile ── */
  fetch(`${BASE}/entrepreneur/my-profile`, { headers: H() })
    .then(r => r.ok ? r.json() : null)
    .then(p => {
      if (!p || p.status === 'submitted') return;
      // Pre-fill simple fields
      const fill = (id, val) => { const el=document.getElementById(id); if(el&&val) el.value=val; };
      fill('full_name', p.full_name); fill('governorate', p.governorate);
      fill('phone', p.phone); fill('email', p.email); fill('age', p.age);
      fill('education_level', p.education_level); fill('specialization', p.specialization);
      fill('project_name', p.project_name); fill('founding_year', p.founding_year);
      fill('executive_summary', p.executive_summary); fill('elevator_pitch', p.elevator_pitch);
      fill('testing_results', p.testing_results);
      fill('problem_description', p.problem_description); fill('target_customers', p.target_customers);
      fill('differentiation', p.differentiation);
      fill('current_users_range', p.current_users_range); fill('current_customers_range', p.current_customers_range);
      fill('additional_notes', p.additional_notes);

      // Radios
      const setRadio = (name, val) => {
        const el = document.querySelector(`input[name="${name}"][value="${val}"]`);
        if (el) el.checked = true;
      };
      setRadio('project_field', p.project_field);
      setRadio('readiness_stage', p.readiness_stage);
      setRadio('has_prototype', p.has_prototype ? '1' : '0');
      setRadio('tested_with_users', p.tested_with_users ? '1' : '0');
      setRadio('target_market', p.target_market);
      setRadio('has_revenue', p.has_revenue ? '1' : '0');
      setRadio('seeking_investment', p.seeking_investment ? '1' : '0');
      setRadio('investment_needed_range', p.investment_needed_range);
      setRadio('jobs_3years_range', p.jobs_3years_range);
      setRadio('scalability_outside_syria', p.scalability_outside_syria);
      setRadio('team_size_range', p.team_size_range);

      // Checkboxes
      const setChecks = (name, arr) => {
        (arr||[]).forEach(v => {
          const el = document.querySelector(`input[name="${name}"][value="${v}"]`);
          if (el) el.checked = true;
        });
      };
      setChecks('competitive_advantages', p.competitive_advantages);
      setChecks('team_roles', p.team_roles);
      setChecks('technologies', p.technologies);
      setChecks('market_validation_methods', p.market_validation_methods);
      setChecks('revenue_sources', p.revenue_sources);
      setChecks('funding_sources', p.funding_sources);
      setChecks('challenges', p.challenges);
      setChecks('support_needed', p.support_needed);
      setChecks('previous_participation', p.previous_participation);

      if (p.project_field === 'other') document.getElementById('field_other_wrap').style.display='';
      if (p.tested_with_users) document.getElementById('testing_results_wrap').style.display='';
      if (p.has_revenue) document.getElementById('rev_sources_wrap').style.display='';
      if (p.seeking_investment) document.getElementById('inv_size_wrap').style.display='';
    });

  updateUI();
});
</script>
</body>
</html>
