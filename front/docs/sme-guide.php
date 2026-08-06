<?php
$basePath = '../';
$pageTitle = 'دليل تعريف المشروعات متناهية الصغر والصغيرة والمتوسطة';
?>
<?php include __DIR__ . '/../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../includes/layout/head.php'; ?>
  <style>
    :root { --c-dark:#062824; --c-deep:#0F4F47; --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#f2fbf8; --c-line:rgba(23,148,123,.14); --c-text:#1a3530; --c-body:#4b5563; }
    body { background:#f8fffe; color:var(--c-text); }

    /* Hero */
    .doc-hero {
      background: linear-gradient(135deg, var(--c-dark) 0%, var(--c-deep) 100%);
      padding: 80px 0 60px; color: #fff; position: relative; overflow: hidden;
    }
    .doc-hero::before {
      content: ""; position: absolute; inset: 0;
      background: url("https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200&q=60") center/cover no-repeat;
      opacity: .06;
    }
    .doc-hero-inner { position: relative; z-index: 1; }
    .doc-hero .badge-pill {
      display: inline-flex; align-items: center; gap: 7px;
      background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22);
      color: rgba(255,255,255,.9); padding: 6px 16px; border-radius: 999px;
      font-size: .82rem; font-weight: 700; margin-bottom: 20px;
    }
    .doc-hero h1 { font-size: clamp(1.6rem,4vw,2.4rem); font-weight: 900; line-height: 1.35; margin-bottom: 16px; color: #fff !important; }
    .doc-hero p { font-size: 1rem; color: rgba(255,255,255,.78); max-width: 640px; line-height: 1.85; }
    .doc-meta { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 28px; }
    .doc-meta-item {
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.15);
      padding: 8px 16px; border-radius: 12px; font-size: .85rem; font-weight: 700;
      color: rgba(255,255,255,.9);
    }
    .doc-meta-item i { color: var(--c-accent); }

    /* Content */
    .doc-content { padding: 60px 0 80px; }
    .doc-toc {
      background: #fff; border: 1px solid var(--c-line);
      border-radius: 20px; padding: 24px 26px; margin-bottom: 40px;
      box-shadow: 0 4px 18px rgba(6,40,36,.06);
    }
    .doc-toc h2 { font-size: 1rem; font-weight: 900; margin-bottom: 16px; color: var(--c-text); }
    .toc-list { list-style: none; padding: 0; margin: 0; }
    .toc-list li { border-bottom: 1px solid var(--c-line); }
    .toc-list li:last-child { border-bottom: none; }
    .toc-list a {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 0; text-decoration: none;
      color: var(--c-body); font-weight: 700; font-size: .9rem;
      transition: color .18s, padding-right .18s;
    }
    .toc-list a:hover { color: var(--c-primary); padding-right: 6px; }
    .toc-list a i { color: var(--c-primary); font-size: .8rem; }

    /* Sections */
    .doc-section { margin-bottom: 48px; }
    .doc-section-title {
      font-size: 1.25rem; font-weight: 900; color: var(--c-text);
      padding-bottom: 12px; margin-bottom: 20px;
      border-bottom: 2px solid var(--c-primary);
      display: flex; align-items: center; gap: 10px;
    }
    .doc-section-title i {
      width: 36px; height: 36px; border-radius: 11px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      display: flex; align-items: center; justify-content: center;
      font-size: .95rem; color: #fff; flex-shrink: 0;
    }
    .prose { font-size: 1rem; color: var(--c-body); line-height: 2; }
    .prose p { margin-bottom: 14px; }
    .prose strong { color: var(--c-text); }

    /* Quote block */
    .quote-block {
      background: var(--c-soft); border-right: 4px solid var(--c-primary);
      border-radius: 0 14px 14px 0; padding: 18px 22px; margin: 20px 0;
      font-style: italic; font-size: .93rem; color: var(--c-text); font-weight: 700;
    }
    .quote-block cite { display: block; font-style: normal; font-size: .82rem; color: var(--c-primary); margin-top: 8px; font-weight: 800; }

    /* Sectors grid */
    .sectors-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 16px; margin: 24px 0; }
    .sector-box {
      background: #fff; border: 1px solid var(--c-line); border-radius: 16px;
      padding: 20px; text-align: center;
    }
    .sector-box .icon { font-size: 2rem; margin-bottom: 10px; display: block; }
    .sector-box h4 { font-size: 1rem; font-weight: 900; margin-bottom: 6px; }
    .sector-box p { font-size: .83rem; color: var(--c-body); line-height: 1.7; margin: 0; }

    /* Classification table */
    .table-wrap {
      background: #fff; border-radius: 20px; overflow: hidden;
      border: 1px solid var(--c-line); box-shadow: 0 4px 18px rgba(6,40,36,.07);
      margin: 24px 0;
    }
    .table-title {
      background: linear-gradient(135deg, var(--c-dark), var(--c-deep));
      color: #fff; padding: 16px 22px; font-weight: 800; font-size: .95rem;
      display: flex; align-items: center; gap: 9px;
    }
    .ctable { width: 100%; border-collapse: collapse; font-size: .88rem; }
    .ctable thead { background: var(--c-soft); }
    .ctable th { padding: 12px 14px; font-weight: 800; color: var(--c-text); text-align: center; border-bottom: 2px solid var(--c-line); white-space: nowrap; }
    .ctable th:first-child, .ctable th:nth-child(2) { text-align: right; }
    .ctable td { padding: 10px 14px; border-bottom: 1px solid rgba(23,148,123,.08); text-align: center; color: var(--c-body); font-weight: 600; }
    .ctable td:first-child, .ctable td:nth-child(2) { text-align: right; }
    .ctable .sector-cell { font-weight: 900; color: var(--c-text); background: rgba(23,148,123,.04) !important; border-right: 3px solid var(--c-primary); }
    .ctable .alt { background: rgba(23,148,123,.025); }
    .badge-n { background: #dcfce7; color: #15803d; padding: 2px 9px; border-radius: 999px; font-size: .78rem; white-space: nowrap; }
    .badge-s { background: #dbeafe; color: #1d4ed8; padding: 2px 9px; border-radius: 999px; font-size: .78rem; white-space: nowrap; }
    .badge-m { background: #fef9c3; color: #854d0e; padding: 2px 9px; border-radius: 999px; font-size: .78rem; white-space: nowrap; }
    .table-note { padding: 10px 20px; font-size: .79rem; color: var(--c-muted); font-weight: 600; border-top: 1px solid var(--c-line); }

    /* ERI formula */
    .formula-box {
      background: linear-gradient(135deg, var(--c-dark), var(--c-deep));
      border-radius: 18px; padding: 28px; color: #fff; margin: 24px 0; text-align: center;
    }
    .formula-box h4 { font-size: 1rem; font-weight: 800; margin-bottom: 16px; color: rgba(255,255,255,.8); }
    .formula { font-size: 1.1rem; font-weight: 900; font-family: monospace; color: var(--c-accent); letter-spacing: 1px; line-height: 2; }
    .formula-desc { font-size: .83rem; color: rgba(255,255,255,.65); margin-top: 12px; line-height: 1.9; }

    /* Criteria boxes */
    .criteria-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 14px; margin: 20px 0; }
    .crit-box {
      background: #fff; border: 1px solid var(--c-line); border-radius: 14px;
      padding: 16px; text-align: center;
    }
    .crit-box .num { font-size: 1.8rem; font-weight: 900; color: var(--c-primary); line-height: 1; margin-bottom: 6px; }
    .crit-box .lbl { font-size: .82rem; color: var(--c-body); font-weight: 700; }

    /* Back button */
    .back-bar { padding: 14px 0 0; }
    .back-btn {
      display: inline-flex; align-items: center; gap: 8px;
      color: rgba(255,255,255,.75); font-weight: 700; font-size: .875rem;
      text-decoration: none; transition: gap .2s, color .2s;
    }
    .back-btn:hover { gap: 12px; color: #fff; }

    @media (max-width: 767.98px) {
      .doc-hero { padding: 50px 0 40px; }
      .doc-meta { gap: 10px; }
      .doc-meta-item { font-size: .8rem; padding: 6px 12px; }
      .sectors-grid { grid-template-columns: 1fr 1fr; }
      .criteria-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 575.98px) {
      .sectors-grid { grid-template-columns: 1fr; }
      .criteria-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/layout/header.php'; ?>

  <!-- Hero -->
  <header class="doc-hero">
  <!-- Back bar -->
  <div class="back-bar">
    <div class="container">
      <a href="<?= $basePath ?>index.php" class="back-btn">
        <i class="bi bi-arrow-right"></i>
        العودة إلى الرئيسية
      </a>
    </div>
  </div>
    <div class="container doc-hero-inner">
      <div class="badge-pill">
        <i class="bi bi-file-earmark-check-fill"></i>
        دليل تعريف المشروعات — وزارة الاقتصاد والتجارة الخارجية
      </div>
      <h1>دليل تعريف المشروعات<br>متناهية الصغر والصغيرة والمتوسطة</h1>
      <p>
        وثيقة رسمية موحّدة تُحدّد معايير تصنيف المشروعات في الجمهورية العربية السورية، ملزمة التطبيق
        من جميع الجهات الحكومية وغير الحكومية المعنية بهذا القطاع.
      </p>
      <div class="doc-meta">
<div class="doc-meta-item"><i class="bi bi-building"></i> وزارة الاقتصاد والتجارة الخارجية</div>
        <div class="doc-meta-item"><i class="bi bi-grid-3x3-gap-fill"></i> 4 قطاعات اقتصادية</div>
        <div class="doc-meta-item"><i class="bi bi-bar-chart-fill"></i> 3 معايير تصنيف</div>
      </div>
    </div>
  </header>

  <!-- Content -->
  <main class="doc-content">
    <div class="container">
      <div class="row g-5">

        <!-- TOC -->
        <div class="col-lg-3 order-lg-last">
          <div class="doc-toc sticky-top" style="top: 90px;">
            <h2><i class="bi bi-list-ul me-2"></i>محتويات الوثيقة</h2>
            <ul class="toc-list">
              <li><a href="#intro"><i class="bi bi-chevron-left"></i> المقدمة</a></li>
              <li><a href="#importance"><i class="bi bi-chevron-left"></i> أهمية التعريف</a></li>
              <li><a href="#scope"><i class="bi bi-chevron-left"></i> نطاق التطبيق</a></li>
              <li><a href="#sectors"><i class="bi bi-chevron-left"></i> القطاعات الاقتصادية</a></li>
              <li><a href="#criteria"><i class="bi bi-chevron-left"></i> معايير التصنيف</a></li>
              <li><a href="#table"><i class="bi bi-chevron-left"></i> جدول الحدود</a></li>
              <li><a href="#eri"><i class="bi bi-chevron-left"></i> مؤشر ERI</a></li>
              <li><a href="#example"><i class="bi bi-chevron-left"></i> مثال توضيحي</a></li>
            </ul>
          </div>
        </div>

        <!-- Main content -->
        <div class="col-lg-9">

          <!-- Intro -->
          <section class="doc-section" id="intro">
            <h2 class="doc-section-title">
              <i class="bi bi-info-circle-fill"></i>
              المقدمة
            </h2>
            <div class="prose">
              <p>
                تُعدّ المشروعات متناهية الصغر والصغيرة والمتوسطة إحدى أهم مرتكزات عملية التنمية
                الاقتصادية والاجتماعية، حيث أنّ العديد من الدول التي حققت نجاحاً على الصعيد التنموي
                كانت قد أولت اهتماماً خاصاً لهذا النوع من المشروعات.
              </p>
              <p>
                بالنسبة للجمهورية العربية السورية، أطلقت وزارة الاقتصاد والتجارة الخارجية حواراً
                ضمّ طيفاً واسعاً من الجهات الحكومية وغير الحكومية، انبثق عنه وضع تعريف خاص بالمشروعات
                يأخذ بعين الاعتبار مجموعة من المعايير التي تتناسب مع الحالة الاقتصادية السورية.
              </p>
            </div>
          </section>

          <!-- Importance -->
          <section class="doc-section" id="importance">
            <h2 class="doc-section-title">
              <i class="bi bi-star-fill"></i>
              أهمية التعريف وخصائصه
            </h2>
            <div class="prose">
              <p>يعدّ التعريف مرجعاً لكافة الجهات المعنية بقطاع المشروعات، حيث:</p>
              <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
                <?php foreach([
                  'تصنيف المشروعات وفقاً لحجمها ونوع نشاطها',
                  'وضع وجمع ونشر الإحصائيات الكمية والنوعية',
                  'تمكين الجهات المحلية والدولية من تبادل المعلومات',
                  'توجيه عمليات وبرامج الدعم والتمويل المتاحة بالشكل الأمثل',
                  'تصميم البرامج الاستهدافية لشرائح محددة من المشروعات',
                ] as $item): ?>
                <li style="display:flex;align-items:flex-start;gap:10px;font-size:.91rem;color:var(--c-body);font-weight:700">
                  <i class="bi bi-check-circle-fill" style="color:var(--c-primary);margin-top:3px;flex-shrink:0"></i>
                  <?= $item ?>
                </li>
                <?php endforeach; ?>
              </ul>
              <p><strong>خصائص التعريف:</strong> يتسم بالوضوح والقابلية للقياس، ويتمتع بالمرونة لمراجعته دورياً، ومُعتمد ملزم التطبيق.</p>
            </div>
          </section>

          <!-- Scope -->
          <section class="doc-section" id="scope">
            <h2 class="doc-section-title">
              <i class="bi bi-bullseye"></i>
              نطاق التطبيق
            </h2>
            <div class="prose">
              <p>
                يطبّق التعريف على كلّ كيانٍ قيد التأسيس أو قائم يمارس نشاطاً اقتصادياً، بغضّ النظر
                عن شكله القانوني، سواءً أكان مرخّصاً أم غير مرخّص.
              </p>
              <p>يُستخدم التعريف من قبل:</p>
              <div class="criteria-grid">
                <?php foreach([
                  ['bi-bank2','المؤسسات الحكومية'],
                  ['bi-people-fill','الهيئات والمنظمات'],
                  ['bi-graph-up','مراكز الإحصاء'],
                  ['bi-briefcase-fill','قطاع الأعمال'],
                  ['bi-cash-stack','مقدمو التمويل'],
                  ['bi-building','جهات دعم الأعمال'],
                ] as [$icon, $label]): ?>
                <div class="crit-box">
                  <i class="bi <?= $icon ?>" style="font-size:1.6rem;color:var(--c-primary);display:block;margin-bottom:8px"></i>
                  <div class="lbl"><?= $label ?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </section>

          <!-- Sectors -->
          <section class="doc-section" id="sectors">
            <h2 class="doc-section-title">
              <i class="bi bi-diagram-3-fill"></i>
              القطاعات الاقتصادية الأربعة
            </h2>
            <div class="sectors-grid">
              <div class="sector-box">
                <span class="icon">🌾</span>
                <h4>الزراعي</h4>
                <p>الإنتاج النباتي والحيواني وصيد الأسماك والحراجة وما يتصل بها</p>
              </div>
              <div class="sector-box">
                <span class="icon">⚙️</span>
                <h4>الصناعي</h4>
                <p>تحويل المواد إلى منتجات نهائية وتجهيز المواد الخام الطبيعية</p>
              </div>
              <div class="sector-box">
                <span class="icon">🏪</span>
                <h4>التجاري</h4>
                <p>شراء وبيع وتوزيع السلع بهدف تحقيق الربح وإعادة الاستثمار</p>
              </div>
              <div class="sector-box">
                <span class="icon">🎓</span>
                <h4>الخدمي</h4>
                <p>التعليم والصحة والنقل والاتصالات والسياحة والطاقة وغيرها</p>
              </div>
            </div>
          </section>

          <!-- Criteria -->
          <section class="doc-section" id="criteria">
            <h2 class="doc-section-title">
              <i class="bi bi-sliders"></i>
              معايير التصنيف الثلاثة
            </h2>
            <div class="prose">
              <p>اعتمد الدليل ثلاثة معايير أساسية تختلف أهميتها النسبية حسب القطاع:</p>
            </div>
            <div class="criteria-grid">
              <div class="crit-box" style="border-top:3px solid var(--c-primary)">
                <div class="num">1</div>
                <i class="bi bi-people-fill" style="font-size:1.4rem;color:var(--c-primary);margin-bottom:8px;display:block"></i>
                <div class="lbl" style="font-size:.9rem;font-weight:900;color:var(--c-text)">عدد العمال</div>
                <div class="lbl" style="margin-top:6px">جميع العاملين بأجر أو بدون أجر، دائمين ومؤقتين</div>
              </div>
              <div class="crit-box" style="border-top:3px solid #d97706">
                <div class="num" style="color:#d97706">2</div>
                <i class="bi bi-cash-stack" style="font-size:1.4rem;color:#d97706;margin-bottom:8px;display:block"></i>
                <div class="lbl" style="font-size:.9rem;font-weight:900;color:var(--c-text)">قيمة المبيعات</div>
                <div class="lbl" style="margin-top:6px">إجمالي الدخل السنوي من النشاط الرئيسي والثانوي (ل.س)</div>
              </div>
              <div class="crit-box" style="border-top:3px solid #7c3aed">
                <div class="num" style="color:#7c3aed">3</div>
                <i class="bi bi-bank2" style="font-size:1.4rem;color:#7c3aed;margin-bottom:8px;display:block"></i>
                <div class="lbl" style="font-size:.9rem;font-weight:900;color:var(--c-text)">رأس المال المستثمر</div>
                <div class="lbl" style="margin-top:6px">رأس المال العامل + جميع الأصول طويلة الأجل (ل.س)</div>
              </div>
            </div>
          </section>

          <!-- Table -->
          <section class="doc-section" id="table">
            <h2 class="doc-section-title">
              <i class="bi bi-table"></i>
              جدول الحدود العليا للتصنيف
            </h2>
            <div class="table-wrap">
              <div class="table-title"><i class="bi bi-table"></i> الجدول رقم (1) — معايير تصنيف المشروعات وحدودها</div>
              <div class="table-responsive">
                <table class="ctable">
                  <thead>
                    <tr>
                      <th>القطاع</th>
                      <th>المعيار</th>
                      <th><span class="badge-n">متناهي الصغر</span></th>
                      <th><span class="badge-s">صغير</span></th>
                      <th><span class="badge-m">متوسط</span></th>
                      <th>وحدة القياس</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td rowspan="3" class="sector-cell">🌾 زراعي</td><td>عدد العمال</td><td>≤ 5</td><td>≤ 20</td><td>≤ 70</td><td>عامل</td></tr>
                    <tr><td>قيمة المبيعات</td><td>≤ 75</td><td>≤ 900</td><td>≤ 3,000</td><td>م.ل.س</td></tr>
                    <tr><td>رأس المال المستثمر (عدا الأراضي)</td><td>≤ 50</td><td>≤ 750</td><td>≤ 2,250</td><td>م.ل.س</td></tr>

                    <tr class="alt"><td rowspan="3" class="sector-cell">⚙️ صناعي</td><td>عدد العمال</td><td>≤ 5</td><td>≤ 35</td><td>≤ 100</td><td>عامل</td></tr>
                    <tr class="alt"><td>قيمة المبيعات</td><td>≤ 110</td><td>≤ 2,250</td><td>≤ 7,500</td><td>م.ل.س</td></tr>
                    <tr class="alt"><td>رأس المال المستثمر</td><td>≤ 75</td><td>≤ 1,500</td><td>≤ 6,750</td><td>م.ل.س</td></tr>

                    <tr><td rowspan="3" class="sector-cell">🏪 تجاري</td><td>عدد العمال</td><td>≤ 3</td><td>≤ 10</td><td>≤ 30</td><td>عامل</td></tr>
                    <tr><td>قيمة المبيعات</td><td>≤ 150</td><td>≤ 1,500</td><td>≤ 3,000</td><td>م.ل.س</td></tr>
                    <tr><td>رأس المال العامل</td><td>≤ 60</td><td>≤ 600</td><td>≤ 2,250</td><td>م.ل.س</td></tr>

                    <tr class="alt"><td rowspan="3" class="sector-cell">🎓 خدمي</td><td>عدد العمال</td><td>≤ 5</td><td>≤ 40</td><td>≤ 100</td><td>عامل</td></tr>
                    <tr class="alt"><td>قيمة المبيعات</td><td>≤ 110</td><td>≤ 1,500</td><td>≤ 4,500</td><td>م.ل.س</td></tr>
                    <tr class="alt"><td>رأس المال المستثمر</td><td>≤ 150</td><td>≤ 2,250</td><td>≤ 7,500</td><td>م.ل.س</td></tr>
                  </tbody>
                </table>
              </div>
              <p class="table-note">م.ل.س = مليون ليرة سورية</p>
            </div>
          </section>

          <!-- ERI -->
          <section class="doc-section" id="eri">
            <h2 class="doc-section-title">
              <i class="bi bi-calculator-fill"></i>
              مؤشر تصنيف المشروع ERI
            </h2>
            <div class="prose">
              <p>
                اعتُمد مؤشر إحصائي يُتيح تصنيف المشروع وفقاً للمعايير الثلاث مجتمعةً مع مراعاة
                الأهمية النسبية لكل منها بحسب القطاع — يُعرف بـ <strong>Enterprise Rating Index (ERI)</strong>.
              </p>
            </div>
            <div class="formula-box">
              <h4>معادلة مؤشر تصنيف المشروع</h4>
              <div class="formula">
                ERI = (WL × LI + WS × SI + WC × CI) × 100 ÷ ΣWi
              </div>
              <div class="formula-desc">
                WL = وزن معيار العمال &nbsp;|&nbsp; WS = وزن معيار المبيعات &nbsp;|&nbsp; WC = وزن رأس المال<br>
                LI = مؤشر العمال &nbsp;|&nbsp; SI = مؤشر المبيعات &nbsp;|&nbsp; CI = مؤشر رأس المال
              </div>
            </div>
            <div class="table-wrap">
              <div class="table-title"><i class="bi bi-bar-chart-steps"></i> الجدول رقم (3) — عتبات تصنيف المشروعات</div>
              <div class="table-responsive">
                <table class="ctable">
                  <thead>
                    <tr>
                      <th>القطاع</th>
                      <th><span class="badge-n">متناهي الصغر</span></th>
                      <th><span class="badge-s">صغير</span></th>
                      <th><span class="badge-m">متوسط</span></th>
                      <th>كبير</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td class="sector-cell">🌾 زراعي</td><td>ERI ≤ 0</td><td>0 &lt; ERI ≤ 29.16</td><td>29.16 &lt; ERI ≤ 100</td><td>ERI &gt; 100</td></tr>
                    <tr class="alt"><td class="sector-cell">⚙️ صناعي</td><td>ERI ≤ 0</td><td>0 &lt; ERI ≤ 25.59</td><td>25.59 &lt; ERI ≤ 100</td><td>ERI &gt; 100</td></tr>
                    <tr><td class="sector-cell">🏪 تجاري</td><td>ERI ≤ 0</td><td>0 &lt; ERI ≤ 36.22</td><td>36.22 &lt; ERI ≤ 100</td><td>ERI &gt; 100</td></tr>
                    <tr class="alt"><td class="sector-cell">🎓 خدمي</td><td>ERI ≤ 0</td><td>0 &lt; ERI ≤ 32.87</td><td>32.87 &lt; ERI ≤ 100</td><td>ERI &gt; 100</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>

          <!-- Example -->
          <section class="doc-section" id="example">
            <h2 class="doc-section-title">
              <i class="bi bi-lightbulb-fill"></i>
              مثال توضيحي — مشروع صناعي
            </h2>
            <div style="background:#fff;border:1px solid var(--c-line);border-radius:18px;padding:28px;font-size:.9rem">
              <p style="font-weight:800;color:var(--c-text);margin-bottom:16px">بيانات المشروع الصناعي الافتراضي:</p>
              <div class="criteria-grid" style="margin-bottom:20px">
                <div class="crit-box"><div class="num">3</div><div class="lbl">عدد العمال</div></div>
                <div class="crit-box"><div class="num" style="color:#d97706">600</div><div class="lbl">قيمة المبيعات (م.ل.س)</div></div>
                <div class="crit-box"><div class="num" style="color:#7c3aed">60</div><div class="lbl">رأس المال (م.ل.س)</div></div>
              </div>
              <div class="formula-box" style="margin:0">
                <h4>نتيجة الحساب</h4>
                <div class="formula">LI = (3-5)÷(100-5) = -0.021</div>
                <div class="formula">SI = (600-110)÷(7500-110) = 0.066</div>
                <div class="formula">CI = (60-75)÷(6750-75) = -0.002</div>
                <div class="formula" style="margin-top:16px;color:#fff">ERI = 1.74 → مشروع صناعي صغير ✓</div>
              </div>
            </div>
          </section>

        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
</body>
</html>
