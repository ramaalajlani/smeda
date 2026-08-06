<?php
$basePath = '../';
$pageTitle = 'التصنيف الوطني للأنشطة الاقتصادية SYRSIC — الجمهورية العربية السورية';
?>
<?php include __DIR__ . '/../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../includes/layout/head.php'; ?>
  <style>
    :root { --c-dark:#062824; --c-deep:#0F4F47; --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#f2fbf8; --c-line:rgba(23,148,123,.14); --c-text:#1a3530; --c-body:#4b5563; }
    body { background:#f8fffe; color:var(--c-text); }

    .doc-hero {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, var(--c-dark) 100%);
      padding: 80px 0 60px; color: #fff; position: relative; overflow: hidden;
    }
    .doc-hero::before {
      content: ""; position: absolute; inset: 0;
      background: url("https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1400&q=50") center/cover no-repeat;
      opacity: .05;
    }
    .doc-hero-inner { position: relative; z-index: 1; }
    .doc-hero .badge-pill {
      display: inline-flex; align-items: center; gap: 7px;
      background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22);
      color: rgba(255,255,255,.9); padding: 6px 16px; border-radius: 999px;
      font-size: .82rem; font-weight: 700; margin-bottom: 20px;
    }
    .doc-hero h1 { font-size: clamp(1.5rem,4vw,2.4rem); font-weight: 900; line-height: 1.35; margin-bottom: 16px; color: #fff !important; }
    .doc-hero p { font-size: 1rem; color: rgba(255,255,255,.78); max-width: 640px; line-height: 1.85; }
    .syrsic-badge {
      display: inline-block; background: var(--c-accent);
      color: #fff; padding: 4px 14px; border-radius: 8px;
      font-weight: 900; font-size: 1.1rem; letter-spacing: 2px;
      margin-bottom: 16px;
    }
    .doc-meta { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 28px; }
    .doc-meta-item {
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.15);
      padding: 8px 16px; border-radius: 12px; font-size: .9375rem; font-weight: 700;
      color: rgba(255,255,255,.9);
    }
    .doc-meta-item i { color: var(--c-accent); }

    .back-bar { padding: 14px 0 0; }
    .back-btn {
      display: inline-flex; align-items: center; gap: 8px;
      color: rgba(255,255,255,.75); font-weight: 700; font-size: .875rem;
      text-decoration: none; transition: gap .2s, color .2s;
    }
    .back-btn:hover { gap: 12px; color: #fff; }

    .doc-content { padding: 60px 0 80px; }

    .doc-toc {
      background: #fff; border: 1px solid var(--c-line);
      border-radius: 20px; padding: 24px; margin-bottom: 32px;
      box-shadow: 0 4px 18px rgba(6,40,36,.06);
      position: sticky; top: 90px;
    }
    .doc-toc h2 { font-size: .95rem; font-weight: 900; margin-bottom: 14px; }
    .toc-list { list-style: none; padding: 0; margin: 0; max-height: 60vh; overflow-y: auto; }
    .toc-list::-webkit-scrollbar { width: 4px; }
    .toc-list::-webkit-scrollbar-thumb { background: rgba(23,148,123,.25); border-radius: 999px; }
    .toc-list li { border-bottom: 1px solid rgba(23,148,123,.08); }
    .toc-list li:last-child { border-bottom: none; }
    .toc-list a {
      display: flex; align-items: center; gap: 8px;
      padding: 8px 2px; text-decoration: none;
      color: var(--c-body); font-weight: 700; font-size: .9375rem;
      transition: color .18s, padding-right .18s;
    }
    .toc-list a:hover { color: var(--c-primary); padding-right: 5px; }
    .toc-list a i { color: var(--c-primary); font-size: .75rem; flex-shrink: 0; }

    .doc-section { margin-bottom: 52px; scroll-margin-top: 100px; }
    .doc-section-title {
      font-size: 1.2rem; font-weight: 900; color: var(--c-text);
      padding-bottom: 12px; margin-bottom: 22px;
      border-bottom: 2px solid var(--c-primary);
      display: flex; align-items: center; gap: 10px;
    }
    .doc-section-title .title-icon {
      width: 36px; height: 36px; border-radius: 11px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      display: flex; align-items: center; justify-content: center;
      font-size: .9rem; color: #fff; flex-shrink: 0;
    }
    .prose { font-size: 1rem; color: var(--c-body); line-height: 2; }
    .prose p { margin-bottom: 14px; }
    .prose strong { color: var(--c-text); }

    /* Chapters grid */
    .chapters-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap: 14px; margin: 20px 0; }
    .chapter-card {
      background: #fff; border: 1px solid var(--c-line); border-radius: 16px;
      padding: 18px 20px; display: flex; align-items: flex-start; gap: 14px;
      transition: transform .22s, box-shadow .22s;
    }
    .chapter-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(6,40,36,.09); }
    .chapter-letter {
      width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; font-weight: 900; color: #fff;
    }
    .chapter-card h4 { font-size: 1rem; font-weight: 900; margin: 0 0 5px; }
    .chapter-card p { font-size: .875rem; color: var(--c-muted); margin: 0; line-height: 1.7; }

    /* Info box */
    .info-box {
      background: var(--c-soft); border: 1px solid rgba(23,148,123,.18);
      border-radius: 16px; padding: 20px 22px; margin: 18px 0;
      display: flex; align-items: flex-start; gap: 14px;
    }
    .info-box i { font-size: 1.3rem; color: var(--c-primary); flex-shrink: 0; margin-top: 2px; }
    .info-box p { font-size: .89rem; color: var(--c-text); font-weight: 700; margin: 0; line-height: 1.85; }

    /* Stats strip */
    .stats-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px,1fr)); gap: 14px; margin: 24px 0; }
    .stat-box {
      background: #fff; border: 1px solid var(--c-line); border-radius: 16px;
      padding: 20px; text-align: center;
    }
    .stat-box .num { font-size: 1.9rem; font-weight: 900; color: var(--c-primary); line-height: 1; margin-bottom: 8px; }
    .stat-box .lbl { font-size: .82rem; color: var(--c-body); font-weight: 700; }

    @media (max-width: 767.98px) {
      .doc-hero { padding: 50px 0 40px; }
      .chapters-grid { grid-template-columns: 1fr; }
      .stats-strip { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/layout/header.php'; ?>

  <header class="doc-hero">
  <div class="back-bar">
    <div class="container">
      <a href="<?= $basePath ?>index.php" class="back-btn">
        <i class="bi bi-arrow-right"></i>
        العودة إلى الرئيسية
      </a>
    </div>
  </div>
    <div class="container doc-hero-inner">
      <div class="syrsic-badge">SYRSIC — V.1</div>
      <div class="badge-pill">
        <i class="bi bi-file-earmark-ruled-fill"></i>
        الإصدار الأول
      </div>
      <h1>التصنيف الوطني للأنشطة الاقتصادية<br>في الجمهورية العربية السورية</h1>
      <p>
        إطار شامل ومتسق لتصنيف جميع الأنشطة الاقتصادية في سورية، مستنداً إلى التصنيف
        الصناعي الدولي الموحّد ISIC4، يُستخدم لأغراض الإحصاء والتخطيط وإصدار التراخيص.
      </p>
      <div class="doc-meta">
        <div class="doc-meta-item"><i class="bi bi-building"></i> وزارة الاقتصاد والتجارة الخارجية</div>
        <div class="doc-meta-item"><i class="bi bi-globe2"></i> مستند إلى ISIC4 — الأمم المتحدة</div>
        <div class="doc-meta-item"><i class="bi bi-layers-fill"></i> 18 باباً اقتصادياً</div>
      </div>
    </div>
  </header>

  <main class="doc-content">
    <div class="container">
      <div class="row g-5">

        <div class="col-lg-3 order-lg-last">
          <div class="doc-toc">
            <h2><i class="bi bi-list-ul me-2"></i>أبواب التصنيف</h2>
            <ul class="toc-list">
              <li><a href="#about"><i class="bi bi-chevron-left"></i> عن SYRSIC</a></li>
              <li><a href="#stats"><i class="bi bi-chevron-left"></i> أرقام ومعطيات</a></li>
              <li><a href="#chap-a"><i class="bi bi-chevron-left"></i> أ — الزراعة والحراجة</a></li>
              <li><a href="#chap-b"><i class="bi bi-chevron-left"></i> ب — التعدين والمحاجر</a></li>
              <li><a href="#chap-c"><i class="bi bi-chevron-left"></i> ج — الصناعة التحويلية</a></li>
              <li><a href="#chap-d"><i class="bi bi-chevron-left"></i> د — الكهرباء والغاز</a></li>
              <li><a href="#chap-e"><i class="bi bi-chevron-left"></i> هـ — المياه والصرف</a></li>
              <li><a href="#chap-f"><i class="bi bi-chevron-left"></i> و — التشييد</a></li>
              <li><a href="#chap-g"><i class="bi bi-chevron-left"></i> ز — تجارة الجملة والتجزئة</a></li>
              <li><a href="#chap-h"><i class="bi bi-chevron-left"></i> ح — النقل والتخزين</a></li>
              <li><a href="#chap-i"><i class="bi bi-chevron-left"></i> ط — الإقامة والطعام</a></li>
              <li><a href="#chap-j"><i class="bi bi-chevron-left"></i> ي — المعلومات والاتصالات</a></li>
              <li><a href="#chap-k"><i class="bi bi-chevron-left"></i> ك — المالية والتأمين</a></li>
              <li><a href="#chap-l"><i class="bi bi-chevron-left"></i> ل — العقارات</a></li>
              <li><a href="#chap-m"><i class="bi bi-chevron-left"></i> م — المهنية والعلمية</a></li>
              <li><a href="#chap-n"><i class="bi bi-chevron-left"></i> ن — الخدمات الإدارية</a></li>
              <li><a href="#chap-o"><i class="bi bi-chevron-left"></i> س — الإدارة العامة</a></li>
              <li><a href="#chap-p"><i class="bi bi-chevron-left"></i> ع — التعليم</a></li>
              <li><a href="#chap-q"><i class="bi bi-chevron-left"></i> ف — الصحة والعمل الاجتماعي</a></li>
              <li><a href="#chap-r"><i class="bi bi-chevron-left"></i> ص — الفنون والترفيه</a></li>
            </ul>
          </div>
        </div>

        <div class="col-lg-9">

          <section class="doc-section" id="about">
            <h2 class="doc-section-title">
              <div class="title-icon"><i class="bi bi-info-circle-fill"></i></div>
              ما هو SYRSIC؟
            </h2>
            <div class="prose">
              <p>
                <strong>Syrian Standard Industrial Classification (SYRSIC)</strong> هو التصنيف الوطني للأنشطة الاقتصادية
                في الجمهورية العربية السورية، الإصدار الأول V.1.
              </p>
              <p>
                يمثّل هذا التصنيف إطاراً شاملاً لأغراض جمع الإحصاءات والتحليل الاقتصادي لصنع السياسات
                واتخاذ القرارات، كما يُستخدم لأغراض إدارية مثل جمع الضرائب وإصدار التراخيص التجارية.
              </p>
            </div>
            <div class="info-box">
              <i class="bi bi-globe2"></i>
              <p>
                يستند SYRSIC إلى التصنيف الصناعي الدولي الموحّد لجميع الأنشطة الاقتصادية
                <strong>ISIC4</strong> الصادر عن المجلس الاقتصادي والاجتماعي في الأمم المتحدة،
                مع مراعاة خصوصية البنية الاقتصادية السورية والقوانين المعمول بها.
              </p>
            </div>
          </section>

          <section class="doc-section" id="stats">
            <h2 class="doc-section-title">
              <div class="title-icon"><i class="bi bi-bar-chart-fill"></i></div>
              التصنيف بالأرقام
            </h2>
            <div class="stats-strip">
              <div class="stat-box"><div class="num">18</div><div class="lbl">بابًا رئيسياً</div></div>
              <div class="stat-box"><div class="num">+80</div><div class="lbl">قسمًا فرعياً</div></div>
              <div class="stat-box"><div class="num">V.1</div><div class="lbl">الإصدار الأول</div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-a">
            <h2 class="doc-section-title">
              <div class="title-icon">أ</div>
              الباب ألف — الزراعة والحراجة وصيد الأسماك
            </h2>
            <div class="chapters-grid">
              <div class="chapter-card">
                <div class="chapter-letter">A1</div>
                <div><h4>أنشطة زراعة المحاصيل والإنتاج الحيواني</h4><p>الزراعة النباتية والحيوانية والصيد والخدمات ذات الصلة</p></div>
              </div>
              <div class="chapter-card">
                <div class="chapter-letter">A2</div>
                <div><h4>الحراجة وقطع الأخشاب</h4><p>أنشطة الغابات وإنتاج الأخشاب</p></div>
              </div>
              <div class="chapter-card">
                <div class="chapter-letter">A3</div>
                <div><h4>صيد الأسماك وتربية المائيات</h4><p>صيد الأسماك في المياه الداخلية والبحرية وتربية الأحياء المائية</p></div>
              </div>
            </div>
          </section>

          <section class="doc-section" id="chap-b">
            <h2 class="doc-section-title">
              <div class="title-icon">ب</div>
              الباب باء — التعدين واستغلال المحاجر
            </h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">B1</div><div><h4>تعدين الفحم واللغنيت</h4><p>استخراج الفحم الحجري والبني</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">B2</div><div><h4>استخراج النفط الخام والغاز الطبيعي</h4><p>عمليات الحفر والاستخراج</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">B3</div><div><h4>تعدين ركازات الفلزات</h4><p>استخراج خامات المعادن</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">B4</div><div><h4>الأنشطة الأخرى للتعدين والمحاجر</h4><p>استخراج الحجارة والرمل والطين</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">B5</div><div><h4>خدمات دعم التعدين</h4><p>أنشطة دعم استخراج النفط والغاز والمعادن</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-c">
            <h2 class="doc-section-title">
              <div class="title-icon">ج</div>
              الباب جيم — الصناعة التحويلية
            </h2>
            <div class="info-box">
              <i class="bi bi-info-circle-fill"></i>
              <p>أكبر أبواب التصنيف يحتوي على أكثر من 25 قسماً تغطي جميع أوجه التصنيع والتحويل الصناعي.</p>
            </div>
            <div class="chapters-grid">
              <?php $mfg = [
                ['صنع المنتجات الغذائية','تحويل المواد الزراعية إلى منتجات غذائية'],
                ['صنع المشروبات','إنتاج المياه والعصائر والمشروبات'],
                ['صنع المنسوجات والملبوسات','النسيج والخياطة والأزياء'],
                ['الصناعات الجلدية','الجلود والأحذية والحقائب'],
                ['صنع الخشب والأثاث','الخشب والفلين والأثاث'],
                ['الطباعة والنشر','المطابع والإعلام المطبوع'],
                ['الصناعات الكيميائية','المواد الكيميائية والصيدلانية'],
                ['صنع المطاط واللدائن','منتجات البلاستيك والمطاط'],
                ['صنع مواد البناء','الإسمنت والزجاج والسيراميك'],
                ['صنع الفلزات القاعدية','الحديد والصلب والألومنيوم'],
                ['صنع الآلات والمعدات','المعدات الصناعية والكهربائية'],
                ['صنع المركبات','السيارات ومعدات النقل'],
              ]; ?>
              <?php foreach($mfg as $i=>[$t,$d]): ?>
              <div class="chapter-card">
                <div class="chapter-letter" style="font-size:.75rem">C<?= $i+1 ?></div>
                <div><h4><?= $t ?></h4><p><?= $d ?></p></div>
              </div>
              <?php endforeach; ?>
            </div>
          </section>

          <section class="doc-section" id="chap-d">
            <h2 class="doc-section-title"><div class="title-icon">د</div> الباب دال — إمدادات الكهرباء والغاز والبخار وتكييف الهواء</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">D1</div><div><h4>توصيل الكهرباء والغاز والبخار</h4><p>شبكات توزيع الطاقة وتكييف الهواء</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-e">
            <h2 class="doc-section-title"><div class="title-icon">هـ</div> الباب هاء — إمدادات المياه والصرف الصحي وإدارة النفايات</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">E1</div><div><h4>تجميع المياه ومعالجتها وتوصيلها</h4><p>شبكات المياه ومحطات المعالجة</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">E2</div><div><h4>الصرف الصحي</h4><p>شبكات الصرف والمياه العادمة</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">E3</div><div><h4>جمع النفايات ومعالجتها واسترجاع المواد</h4><p>إدارة النفايات الصلبة وإعادة التدوير</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-f">
            <h2 class="doc-section-title"><div class="title-icon">و</div> الباب واو — التشييد</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">F1</div><div><h4>تشييد المباني</h4><p>البناء السكني والتجاري وإعادة التأهيل</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">F2</div><div><h4>الهندسة المدنية</h4><p>الطرق والجسور والأنفاق والبنية التحتية</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">F3</div><div><h4>أنشطة التشييد المتخصصة</h4><p>الكهرباء والسباكة والتشطيب</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-g">
            <h2 class="doc-section-title"><div class="title-icon">ز</div> الباب زاي — تجارة الجملة والتجزئة</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">G1</div><div><h4>تجارة المركبات وإصلاحها</h4><p>بيع السيارات والدراجات والقطع</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">G2</div><div><h4>تجارة الجملة</h4><p>توزيع البضائع بكميات كبيرة للتجار</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">G3</div><div><h4>تجارة التجزئة</h4><p>البيع المباشر للمستهلك النهائي</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-h">
            <h2 class="doc-section-title"><div class="title-icon">ح</div> الباب حاء — النقل والتخزين</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">H1</div><div><h4>النقل البري والأنابيب</h4><p>الحافلات والشاحنات وأنابيب النفط</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">H2</div><div><h4>النقل المائي</h4><p>النقل النهري والبحري</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">H3</div><div><h4>النقل الجوي</h4><p>شركات الطيران والمطارات</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">H4</div><div><h4>التخزين وخدمات الدعم</h4><p>المستودعات والموانئ والخدمات اللوجستية</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">H5</div><div><h4>خدمات البريد والشحن</h4><p>البريد ومندوبو التوصيل والطرود</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-i">
            <h2 class="doc-section-title"><div class="title-icon">ط</div> الباب طاء — أنشطة الإقامة والطعام</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">I1</div><div><h4>الإقامة</h4><p>الفنادق والنزل وبيوت الضيافة والمخيمات</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">I2</div><div><h4>خدمات الأطعمة والمشروبات</h4><p>المطاعم والمقاهي والوجبات الجاهزة</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-j">
            <h2 class="doc-section-title"><div class="title-icon">ي</div> الباب ياء — المعلومات والاتصالات</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">J1</div><div><h4>أنشطة النشر</h4><p>نشر الكتب والمجلات والصحف</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">J2</div><div><h4>إنتاج الأفلام والبرامج التلفزيونية</h4><p>الإنتاج السينمائي والتلفزيوني والموسيقى</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">J3</div><div><h4>الاتصالات</h4><p>شبكات الاتصالات اللاسلكية والإنترنت</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">J4</div><div><h4>البرمجة الحاسوبية والاستشارات</h4><p>تطوير البرمجيات والخدمات التقنية</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">J5</div><div><h4>خدمات المعلومات</h4><p>معالجة البيانات والاستضافة وبوابات الإنترنت</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-k">
            <h2 class="doc-section-title"><div class="title-icon">ك</div> الباب كاف — الأنشطة المالية والتأمين</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">K1</div><div><h4>الخدمات المالية</h4><p>البنوك والقروض والاستثمار</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">K2</div><div><h4>التأمين وإعادة التأمين</h4><p>شركات التأمين وصناديق المعاشات</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">K3</div><div><h4>الأنشطة المساعدة المالية</h4><p>الصرافة والوساطة المالية والتمويل الأصغر</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-l">
            <h2 class="doc-section-title"><div class="title-icon">ل</div> الباب لام — الأنشطة العقارية</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">L1</div><div><h4>الأنشطة العقارية</h4><p>شراء وبيع وتأجير العقارات والوساطة العقارية</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-m">
            <h2 class="doc-section-title"><div class="title-icon">م</div> الباب ميم — الأنشطة المهنية والعلمية والتقنية</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">M1</div><div><h4>الأنشطة القانونية والمحاسبة</h4><p>المحامون والمحاسبون والموثقون</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">M2</div><div><h4>الاستشارات الإدارية</h4><p>إدارة الأعمال والاستشارات التنظيمية</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">M3</div><div><h4>الهندسة والمعمار</h4><p>التصميم والاختبارات الفنية والتحليل</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">M4</div><div><h4>البحث والتطوير العلمي</h4><p>الأبحاث التطبيقية والأساسية</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">M5</div><div><h4>الإعلان وأبحاث السوق</h4><p>الإعلانات والتسويق ودراسة السوق</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">M6</div><div><h4>الأنشطة البيطرية</h4><p>رعاية الحيوانات والخدمات البيطرية</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-n">
            <h2 class="doc-section-title"><div class="title-icon">ن</div> الباب نون — الخدمات الإدارية وخدمات الدعم</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">N1</div><div><h4>الأنشطة الإيجارية</h4><p>تأجير السيارات والمعدات والآلات</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">N2</div><div><h4>الاستخدام والتوظيف</h4><p>وكالات التوظيف وتوفير العمالة</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">N3</div><div><h4>السياحة والسفر</h4><p>وكالات السفر ومشغلو الجوالت السياحية</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">N4</div><div><h4>الأمن والتحقيقات</h4><p>الحراسة وخدمات الأمن</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">N5</div><div><h4>خدمات المباني والتجميل</h4><p>النظافة والصيانة وتنسيق المواقع</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-o">
            <h2 class="doc-section-title"><div class="title-icon">س</div> الباب سين — الإدارة العامة والدفاع والضمان الاجتماعي</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">O1</div><div><h4>الإدارة العامة والدفاع</h4><p>الوزارات والمؤسسات الحكومية والجيش</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-p">
            <h2 class="doc-section-title"><div class="title-icon">ع</div> الباب عين — التعليم</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">P1</div><div><h4>التعليم بجميع مستوياته</h4><p>الحضانات والمدارس والجامعات ومراكز التدريب المهني</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-q">
            <h2 class="doc-section-title"><div class="title-icon">ف</div> الباب فاء — أنشطة صحة الإنسان والعمل الاجتماعي</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">Q1</div><div><h4>الأنشطة في مجال صحة الإنسان</h4><p>المستشفيات والعيادات والمختبرات</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">Q2</div><div><h4>أنشطة الرعاية مع الإقامة</h4><p>دور رعاية المسنين والمرضى</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">Q3</div><div><h4>العمل الاجتماعي بدون إقامة</h4><p>الخدمات الاجتماعية للأسر والأفراد</p></div></div>
            </div>
          </section>

          <section class="doc-section" id="chap-r">
            <h2 class="doc-section-title"><div class="title-icon">ص</div> الباب صاد — الفنون والترفيه والتسلية</h2>
            <div class="chapters-grid">
              <div class="chapter-card"><div class="chapter-letter">R1</div><div><h4>الأنشطة الإبداعية والفنون</h4><p>المسارح ودور الفنون والموسيقى</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">R2</div><div><h4>المكتبات والمتاحف والأنشطة الثقافية</h4><p>المكتبات العامة والمتاحف والمواقع التراثية</p></div></div>
              <div class="chapter-card"><div class="chapter-letter">R3</div><div><h4>الأنشطة الرياضية والترفيهية</h4><p>الأندية الرياضية وملاعب الترفيه</p></div></div>
            </div>
          </section>

        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
</body>
</html>
