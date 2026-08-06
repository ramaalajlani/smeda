<?php
$basePath = '../../';
$activePage = 'need-create';
$pageTitle = 'تسجيل احتياج';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <style>
    /* ── خطوات الفورم ── */
    .form-steps {
      display: flex; gap: 0; margin-bottom: 28px;
      overflow-x: auto; -webkit-overflow-scrolling: touch;
      scrollbar-width: none;
    }
    .form-steps::-webkit-scrollbar { display: none; }
    .form-step {
      flex: 1; min-width: 90px; text-align: center; padding: 12px 6px 10px;
      border-bottom: 3px solid #e5e7eb; cursor: pointer;
      font-size: 12px; color: #9ca3af; transition: all .25s; white-space: nowrap;
    }
    .form-step.active { border-color: var(--brand-color,#3b82f6); color: var(--brand-color,#3b82f6); font-weight: 700; }
    .form-step.done   { border-color: #22c55e; color: #16a34a; }
    .form-step .step-num {
      display: inline-flex; align-items: center; justify-content: center;
      width: 22px; height: 22px; border-radius: 50%; background: #e5e7eb;
      font-size: 11px; font-weight: 700; margin-left: 5px; color: #9ca3af;
      transition: all .25s;
    }
    .form-step.active .step-num { background: var(--brand-color,#3b82f6); color: #fff; }
    .form-step.done   .step-num { background: #22c55e; color: #fff; }
    @media (max-width: 480px) {
      .form-step { font-size: 11px; min-width: 70px; padding: 10px 4px 8px; }
    }

    /* ── لوحات الأقسام ── */
    .form-panel { display: none; animation: fadeIn .2s ease; }
    .form-panel.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    /* ── بطاقة القسم ── */
    .section-card {
      background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
      padding: 20px 24px; margin-bottom: 16px;
      box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    @media (max-width: 575px) { .section-card { padding: 16px; } }
    .section-card-title {
      font-size: 14px; font-weight: 700; color: #1e40af;
      margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
    }
    .section-card-title .icon { font-size: 18px; }

    /* ── بطاقات اختيار النوع ── */
    .complexity-card {
      display: block; border: 2px solid #e5e7eb; border-radius: 14px;
      padding: 20px 18px; cursor: pointer; transition: all .2s;
      background: #fff; height: 100%;
    }
    .complexity-card:hover { border-color: var(--brand-color,#3b82f6); background: #eff6ff; }
    .complexity-card.selected { border-color: var(--brand-color,#3b82f6); background: #eff6ff; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
    .cc-icon { font-size: 30px; margin-bottom: 8px; }
    .cc-title { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .cc-desc { font-size: 12px; color: #6b7280; line-height: 1.6; }

    /* ── أزرار اختيار الجهة ── */
    .owner-btn {
      display: inline-flex; align-items: center; gap: 6px;
      border: 2px solid #e5e7eb; border-radius: 10px;
      padding: 10px 20px; cursor: pointer; font-size: 14px; font-weight: 600;
      color: #374151; background: #fff; transition: all .2s;
    }
    .owner-btn:hover { border-color: var(--brand-color,#3b82f6); color: var(--brand-color,#3b82f6); }
    .owner-btn.selected { border-color: var(--brand-color,#3b82f6); background: #eff6ff; color: var(--brand-color,#3b82f6); }

    /* ── خريطة تحديد الموقع ── */
    #locationPickerMap {
      height: 340px; border-radius: 10px;
      border: 2px dashed #93c5fd; cursor: crosshair;
    }
    @media (max-width: 576px) { #locationPickerMap { height: 260px; } }
    #locationPickerMap.has-pin { border: 2px solid #22c55e; }
    .map-tip { font-size: 12px; color: #6b7280; margin-top: 6px; }
    .map-tip.ok { color: #16a34a; font-weight: 600; }
    .coords-badge {
      display: none; background: #f0fdf4; border: 1px solid #bbf7d0;
      border-radius: 8px; padding: 6px 12px; font-size: 12px;
      font-family: monospace; margin-top: 8px;
    }
    .coords-badge.show { display: block; }

    /* ── بحث النشاط ── */
    .syrsic-search-wrap { position: relative; }
    .syrsic-results {
      position: absolute; z-index: 9999; width: 100%;
      background: #fff; border: 1px solid #d1d5db; border-top: none;
      border-radius: 0 0 10px 10px; max-height: 320px; overflow-y: auto;
      box-shadow: 0 8px 24px rgba(0,0,0,.12); display: none;
    }
    .syrsic-results.open { display: block; }
    .syrsic-result-item {
      padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f3f4f6;
      transition: background .15s;
    }
    .syrsic-result-item:hover, .syrsic-result-item.focused { background: #eff6ff; }
    .syrsic-result-item .rc { font-size: 16px; font-weight: 800; color: #1d4ed8; font-family: monospace; }
    .syrsic-result-item .rn { font-size: 13px; color: #111; margin-top: 1px; }
    .syrsic-result-item .rp { font-size: 11px; color: #9ca3af; margin-top: 2px; direction: rtl; }
    .syrsic-result-item .badge-level {
      font-size: 10px; padding: 1px 7px; border-radius: 10px;
      margin-inline-start: auto; flex-shrink: 0;
    }
    .syrsic-result-item { display: flex; align-items: flex-start; flex-wrap: wrap; gap: 4px; }
    .badge-activity { background: #dcfce7; color: #15803d; }
    .badge-class    { background: #dbeafe; color: #1d4ed8; }
    .badge-group    { background: #fef9c3; color: #854d0e; }
    .badge-division { background: #f3e8ff; color: #7e22ce; }
    .badge-section  { background: #f1f5f9; color: #475569; }
    .syrsic-no-result { padding: 14px; color: #9ca3af; font-size: 13px; text-align: center; }

    /* ── كود النشاط المختار ── */
    .activity-code-box {
      background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;
      padding: 14px 16px; margin-top: 12px;
      display: none; opacity: 0; transform: translateY(4px);
      transition: opacity .2s ease, transform .2s ease;
    }
    .activity-code-box.show { display: block; }
    .activity-code-box.visible { opacity: 1; transform: none; }
    .activity-code-box .code { font-size: 22px; font-weight: 800; color: #1d4ed8; letter-spacing: 1px; }
    .activity-code-box .label { font-size: 13px; color: #374151; margin-top: 2px; }

    /* ── تقدير المستفيدين ── */
    .jobs-hint {
      font-size: 12px; color: #6b7280;
      background: #fefce8; border: 1px solid #fde68a;
      border-radius: 6px; padding: 6px 10px; margin-top: 6px; display: none;
    }
    .jobs-hint.show { display: block; }

    /* ── أزرار التنقل ── */
    .nav-btns { display: flex; justify-content: space-between; margin-top: 24px; }

    /* ── تلميحات الحقول ── */
    .field-hint {
      font-size: 11.5px; color: #64748b; margin-top: 4px; line-height: 1.7;
      display: flex; gap: 4px; align-items: flex-start;
    }
    .field-hint::before { content: '💡'; font-size: 11px; flex-shrink: 0; }

    /* ── شارات اختيار القطاعات ── */
    .sector-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .sector-chip {
      display: inline-flex; align-items: center; gap: 6px;
      border: 1.5px solid #e5e7eb; border-radius: 20px;
      padding: 6px 14px; cursor: pointer; font-size: 13px; font-weight: 600;
      color: #374151; background: #fff; transition: all .15s; user-select: none;
    }
    .sector-chip:hover { border-color: var(--brand-color,#3b82f6); }
    .sector-chip.selected { border-color: var(--brand-color,#3b82f6); background: #eff6ff; color: var(--brand-color,#3b82f6); }
    .sector-chip.chip-all.selected { border-color: #16a34a; background: #f0fdf4; color: #15803d; }
    .sector-chip input { display: none; }
    .sector-chip.disabled { opacity: .4; cursor: not-allowed; }

    .taxonomy-error {
      display: none; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
      border-radius: 8px; padding: 8px 12px; font-size: 12.5px; margin-top: 10px;
    }
    .taxonomy-error.show { display: block; }

    .gov-label {
      background: transparent; border: none;
      font-size: 11px; font-weight: 700; color: #1e3a5f;
      text-shadow: 0 1px 2px #fff, 0 -1px 2px #fff, 1px 0 2px #fff, -1px 0 2px #fff;
      white-space: nowrap;
    }
    .district-tooltip {
      font-size: 11px; color: #374151; background: rgba(255,255,255,.9);
      border: 1px solid #d1d5db; border-radius: 4px; padding: 2px 6px;
      box-shadow: none;
    }

    /* ── قائمة بحث منسدلة منسّقة (searchable select) ── */
    .ss-wrap { position: relative; }
    .ss-toggle {
      width: 100%; text-align: start; white-space: nowrap; overflow: hidden;
      text-overflow: ellipsis; cursor: pointer;
    }
    .ss-toggle.is-placeholder { color: #9ca3af; }
    .ss-wrap.disabled .ss-toggle { background-color: #e9ecef; cursor: not-allowed; opacity: 1; }
    .ss-menu {
      position: absolute; z-index: 1060; top: calc(100% + 4px); inset-inline: 0;
      background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,.14); padding: 8px;
      display: none; flex-direction: column; max-height: 300px;
    }
    .ss-wrap.open .ss-menu { display: flex; }
    .ss-search { margin-bottom: 6px; }
    .ss-search input { font-size: 13px; border-radius: 8px; }
    .ss-list { list-style: none; margin: 0; padding: 0; overflow-y: auto; max-height: 232px; }
    .ss-opt {
      padding: 8px 11px; border-radius: 8px; cursor: pointer;
      font-size: 13.5px; color: #1f2937;
    }
    .ss-opt:hover, .ss-opt.active { background: #f0fdf9; }
    .ss-opt.selected { background: #EAF8F4; font-weight: 700; color: #0f5132; }
    .ss-opt.ss-custom { color: #17947B; font-weight: 700; border-bottom: 1px dashed #d1e7dd; }
    .ss-empty { padding: 12px; color: #9ca3af; font-size: 13px; text-align: center; }

    /* ── إشعار / نافذة الحفظ ── */
    #needSaveToast {
      position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(20px);
      padding: 14px 28px; border-radius: 14px; font-weight: 700; font-size: 15px;
      z-index: 10000; display: none; align-items: center; gap: 10px;
      box-shadow: 0 10px 32px rgba(0,0,0,.18); white-space: nowrap;
    }
    #needSaveToast.is-visible { display: flex; }
    #needSaveToast.toast-ok {
      background: #dcfce7; color: #15803d; border: 1.5px solid #4ade80;
    }
    #needSaveToast.toast-err {
      background: #fee2e2; color: #991b1b; border: 1.5px solid #f87171;
    }

    #needSaveModal {
      position: fixed; inset: 0; z-index: 10001;
      display: none; align-items: center; justify-content: center;
      background: rgba(15, 23, 42, .55); padding: 20px;
    }
    #needSaveModal.is-visible { display: flex; }
    #needSaveModal .nsm-box {
      background: #fff; border-radius: 18px; padding: 32px 28px 24px;
      max-width: 400px; width: 100%; text-align: center;
      box-shadow: 0 20px 50px rgba(0,0,0,.25);
      animation: nsmPop .25s ease;
    }
    @keyframes nsmPop {
      from { opacity: 0; transform: scale(.92) translateY(8px); }
      to { opacity: 1; transform: none; }
    }
    #needSaveModal .nsm-icon {
      width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 16px;
      display: flex; align-items: center; justify-content: center; font-size: 32px;
    }
    #needSaveModal.nsm-ok .nsm-icon { background: #dcfce7; color: #16a34a; }
    #needSaveModal.nsm-err .nsm-icon { background: #fee2e2; color: #dc2626; }
    #needSaveModal .nsm-title {
      font-size: 20px; font-weight: 800; color: #111827; margin-bottom: 8px;
    }
    #needSaveModal .nsm-text {
      font-size: 14px; color: #6b7280; margin-bottom: 22px; line-height: 1.7;
    }
    #needSaveModal .nsm-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
  </style>
</head>
<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>
<section class="services-hero pb-3">
    <div class="container">
      <h1 class="fw-bold mb-1">تسجيل احتياج جديد</h1>
      <p class="section-subtitle mb-0">أدخل بيانات الاحتياج بالكامل واختر موقعه على الخريطة.</p>
    </div>
  </section>

  <section class="section pt-2">
    <div class="container" style="max-width:860px">

      <div id="needCreateMessage" class="alert d-none mb-3"></div>

      <!-- شاشة اختيار النوع محذوفة — الدخول مباشرة للاستبيان -->
      <div id="complexityPicker" class="d-none" aria-hidden="true"></div>

      <!-- ── خطوات الاستبيان ── -->
      <div id="formStepsWrapper">

      <div class="form-steps">
        <div class="form-step active" data-step="1">
          <span class="step-num">١</span><span class="step-label">المعلومات الأساسية</span>
        </div>
        <div class="form-step step-specific" data-step="2">
          <span class="step-num">٢</span><span class="step-label">تصنيف الاحتياج</span>
        </div>
        <div class="form-step step-specific" data-step="3">
          <span class="step-num">٣</span><span class="step-label">التصنيف الوطني</span>
        </div>
        <div class="form-step" data-step="4">
          <span class="step-num">٤</span><span class="step-label">الموقع الجغرافي</span>
        </div>
        <div class="form-step step-specific" data-step="5">
          <span class="step-num">٥</span><span class="step-label">المستفيدون والتفاصيل</span>
        </div>
      </div>

      <form id="needCreateForm" novalidate>
        <input type="hidden" name="need_complexity" id="hComplexity" value="specific">
        <input type="hidden" name="need_owner_type" id="hOwnerType" value="citizen">

        <!-- ══════════ الخطوة 1: المعلومات الأساسية ══════════ -->
        <div class="form-panel active" data-panel="1">

          <div id="generalFields" class="d-none" aria-hidden="true"></div>
          <div id="stateFields" class="d-none" aria-hidden="true"></div>

          <div class="section-card" id="citizenFields">
            <div class="section-card-title"><span class="icon">👤</span>بيانات مقدم الاحتياج (مواطن / قطاع خاص)</div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">اسم الاحتياج <span class="text-danger">*</span></label>
                <input name="title" class="form-control" placeholder="اسم مختصر وواضح للاحتياج" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">الجهة الطالبة <span class="text-danger">*</span></label>
                <select name="responsible_entity" class="form-select" required>
                  <option value="">-- اختر --</option>
                  <option value="هيئة">هيئة</option>
                  <option value="محافظة">محافظة</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">نوع الاحتياج</label>
                <select name="need_type" class="form-select">
                  <option value="">-- اختر --</option>
                  <option value="تدريب">تدريب وتأهيل</option>
                  <option value="تمويل">تمويل</option>
                  <option value="استشارة">استشارة</option>
                  <option value="دراسة">دراسة جدوى</option>
                  <option value="حاضنة">حاضنة أعمال</option>
                  <option value="تسويق">تسويق</option>
                  <option value="دعم فني">دعم فني</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">وصف الاحتياج</label>
                <textarea name="description" class="form-control" rows="3" placeholder="اشرح الاحتياج بالتفصيل..."></textarea>
              </div>
            </div>
          </div>

          <div class="nav-btns">
            <div></div>
            <button type="button" class="btn btn-brand btn-next" data-next="2">
              التالي: تصنيف الاحتياج <i class="bi bi-arrow-left ms-1"></i>
            </button>
          </div>
        </div>

        <!-- ══════════ الخطوة 2: تصنيف الاحتياج ══════════ -->
        <div class="form-panel" data-panel="2">

          <div class="section-card">
            <div class="section-card-title"><span class="icon">🏷️</span>تصنيف الاحتياج</div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">تصنيف الاحتياج <span class="text-danger">*</span></label>
                <select id="needCategorySelect" name="need_category" class="form-select">
                  <option value="">-- اختر التصنيف --</option>
                </select>
                <div class="field-hint">اختر الفئة العامة للاحتياج — مثال: "إنشاء منشأة أو مركز" لطلب مركز جديد، أو "دعم قطاع محدد" لدعم قطاع مثل الزراعة.</div>
              </div>

              <div class="col-md-6 d-none" id="categoryOtherWrap">
                <label class="form-label">حدد التصنيف <span class="text-danger">*</span></label>
                <input type="text" id="needCategoryOtherInput" class="form-control" placeholder="اكتب التصنيف هنا..." autocomplete="off">
              </div>

              <div class="col-md-6 d-none" id="facilityTypeWrap">
                <label class="form-label">نوع المنشأة <span class="text-danger">*</span></label>
                <select id="facilityTypeSelect" name="facility_type" class="form-select">
                  <option value="">-- اختر نوع المنشأة --</option>
                </select>
                <div class="field-hint">حدد نوع المركز أو المنشأة المطلوب إنشاؤها أو تطويرها — مثال: حاضنة أعمال، مركز تنمية أسرية، مساحة عمل حرة.</div>
              </div>

              <div class="col-md-6 d-none" id="facilitySubtypeWrap">
                <label class="form-label">نوع الحاضنة <span class="text-danger">*</span></label>
                <select id="facilitySubtypeSelect" name="facility_subtype" class="form-select">
                  <option value="">-- اختر نوع الحاضنة --</option>
                </select>
                <div class="field-hint">يظهر فقط لحاضنات الأعمال — مثال: تقنية لمشاريع البرمجيات، حرفية للصناعات اليدوية، متعددة القطاعات إذا لم تكن متخصصة.</div>
              </div>

              <div class="col-12">
                <label class="form-label">القطاعات المستهدفة <span class="text-danger">*</span></label>
                <div id="sectorChips" class="sector-chips"></div>
                <div class="field-hint">يمكنك اختيار قطاع واحد أو عدة قطاعات — مثال: زراعي + غذائي لمشروع تصنيع غذائي. اختيار "جميع القطاعات" يلغي بقية الاختيارات.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">نوع الاستهداف</label>
                <select id="targetingTypeSelect" name="targeting_type" class="form-select">
                  <option value="">-- اختر نوع الاستهداف --</option>
                </select>
                <div class="field-hint">من المستفيد الأساسي؟ — مثال: "تنمية مشروع قائم" لمشروع موجود، "دعم منطقة" لتنمية منطقة جغرافية كاملة.</div>
              </div>

              <div class="col-md-6 d-none" id="targetingOtherWrap">
                <label class="form-label">حدد نوع الاستهداف <span class="text-danger">*</span></label>
                <input type="text" id="targetingTypeOtherInput" class="form-control" placeholder="اكتب نوع الاستهداف هنا..." autocomplete="off">
              </div>

              <div class="col-md-6 d-none" id="existingProjectWrap">
                <label class="form-label">اسم المشروع القائم <span class="text-danger">*</span></label>
                <input name="organization_name" id="existingProjectInput" class="form-control" placeholder="اسم المشروع أو الجهة المراد تنميتها">
                <div class="field-hint">إلزامي عند اختيار "تنمية مشروع قائم" — اكتب اسم المشروع كما هو معروف — مثال: معمل ألبان الوعر.</div>
              </div>
            </div>

            <div id="taxonomyError" class="taxonomy-error"></div>
          </div>

          <div class="nav-btns">
            <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="1">
              <i class="bi bi-arrow-right me-1"></i>السابق
            </button>
            <button type="button" class="btn btn-brand btn-next" data-next="3">
              التالي: التصنيف الوطني <i class="bi bi-arrow-left ms-1"></i>
            </button>
          </div>
        </div>

        <!-- ══════════ الخطوة 3: التصنيف الوطني SyrSIC ══════════ -->
        <div class="form-panel" data-panel="3">

          <div class="section-card">
            <div class="section-card-title">
              <span class="icon">🏭</span>التصنيف الوطني للأنشطة الاقتصادية (SyrSIC)
            </div>
            <p class="text-muted small mb-3">
              ابحث باسم النشاط أو بالكود الرقمي مباشرةً — مثلاً: اكتب "<strong>مساحيق</strong>" أو "<strong>10801</strong>".
              تظهر النتائج فوراً مع مسار الهرمية الكاملة.
            </p>

            <!-- حقل البحث الذكي -->
            <div class="syrsic-search-wrap mb-3">
              <label class="form-label">ابحث عن النشاط <span class="text-danger">*</span></label>
              <input
                type="text"
                id="syrsicSearchInput"
                class="form-control form-control-lg"
                placeholder="اكتب اسم النشاط أو رقم الكود... (مثال: مساحيق، 108، بناء، مطعم)"
                autocomplete="off"
                dir="rtl"
              >
              <div id="syrsicResults" class="syrsic-results" role="listbox"></div>
            </div>

            <!-- الحقول المخفية للبيانات -->
            <input type="hidden" name="syrsic_section"  id="hSyrsicSection">
            <input type="hidden" name="syrsic_division" id="hSyrsicDivision">
            <input type="hidden" name="syrsic_group"    id="hSyrsicGroup">
            <input type="hidden" name="syrsic_class"    id="hSyrsicClass">
            <input type="hidden" name="syrsic_activity" id="hSyrsicActivity">
            <input type="hidden" name="sector"          id="hSector">
            <input type="hidden" name="economic_sector" id="hEconomicSector">

            <!-- عرض النشاط المختار -->
            <div id="activityCodeBox" class="activity-code-box">
              <div class="d-flex align-items-start flex-wrap gap-3">
                <div>
                  <div class="small text-muted mb-1">كود النشاط (SyrSIC)</div>
                  <div class="code" id="activityCodeDisplay">—</div>
                </div>
                <div style="flex:1; min-width:160px">
                  <div class="small text-muted mb-1">اسم النشاط التفصيلي</div>
                  <div class="fw-semibold" id="activityNameDisplay" style="font-size:14px">—</div>
                </div>
                <div>
                  <div class="small text-muted mb-1">الباب / القطاع</div>
                  <div class="label" id="activitySectionDisplay">—</div>
                </div>
                <div>
                  <div class="small text-muted mb-1">المسار الكامل</div>
                  <div class="label text-muted" id="activityPathDisplay" style="font-size:11px">—</div>
                </div>
              </div>
              <button type="button" id="clearActivityBtn" class="btn btn-sm btn-outline-secondary mt-2">
                ✕ تغيير النشاط
              </button>
            </div>
          </div>

          <div class="nav-btns">
            <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="2">
              <i class="bi bi-arrow-right me-1"></i>السابق
            </button>
            <button type="button" class="btn btn-brand btn-next" data-next="4">
              التالي: الموقع الجغرافي <i class="bi bi-arrow-left ms-1"></i>
            </button>
          </div>
        </div>

        <!-- ══════════ الخطوة 4: الموقع الجغرافي ══════════ -->
        <div class="form-panel" data-panel="4">

          <div class="section-card">
            <div class="section-card-title"><span class="icon">📍</span>الموقع الإداري</div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">المحافظة <span class="text-danger">*</span></label>
                <select id="govSelect" name="governorate_id" class="form-select" required>
                  <option value="">-- اختر المحافظة --</option>
                </select>
                <div class="field-hint" id="govHint">اختر المحافظة التي يقع فيها الاحتياج — مثال: حمص.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">المنطقة (district) <span class="text-danger d-none" id="districtRequiredMark">*</span></label>
                <div id="districtSelect"></div>
                <div class="field-hint" id="districtHint">اختر أو ابحث عن المنطقة الإدارية — تصبح إلزامية عند اختيار "دعم منطقة" كنوع استهداف.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">الناحية / البلدة</label>
                <div id="countrysideSelect"></div>
              </div>
              <div class="col-md-6">
                <label class="form-label">القرية / الحي</label>
                <input name="village_or_neighborhood" class="form-control" placeholder="اسم القرية أو الحي">
              </div>
              <div class="col-12">
                <label class="form-label">العنوان التفصيلي</label>
                <input name="address_details" class="form-control" placeholder="الشارع، رقم العمارة، علامة مميزة...">
              </div>
            </div>
          </div>

          <div class="section-card">
            <div class="section-card-title"><span class="icon">🗺️</span>تحديد الموقع على الخريطة</div>
            <p class="text-muted small mb-2">انقر على خريطة سوريا لتحديد الموقع بدقة. يمكن سحب المؤشر لتعديل الموقع.</p>
            <div id="locationPickerMap"></div>
            <div id="mapTip" class="map-tip">🖱️ انقر على الخريطة لتثبيت الموقع</div>
            <input type="hidden" name="latitude" id="latInput">
            <input type="hidden" name="longitude" id="lngInput">
          </div>

          <div class="nav-btns">
            <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="3">
              <i class="bi bi-arrow-right me-1"></i>السابق
            </button>
            <button type="button" class="btn btn-brand btn-next" data-next="5">
              التالي: المستفيدون <i class="bi bi-arrow-left ms-1"></i>
            </button>
          </div>
        </div>

        <!-- ══════════ الخطوة 5: المستفيدون والتفاصيل ══════════ -->
        <div class="form-panel" data-panel="5">

          <div class="section-card">
            <div class="section-card-title"><span class="icon">👥</span>المستفيدون</div>
            <p class="text-muted small mb-3">عدد المستفيدين مرتبط بكود النشاط المختار — راجع التقدير أدناه وعدّله حسب الحاجة.</p>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">إجمالي المستفيدين المتوقع</label>
                <input type="number" name="beneficiaries_count" id="beneficiariesInput" class="form-control" min="0" placeholder="0">
                <div class="field-hint">عدد الأشخاص المتوقع استفادتهم من تلبية الاحتياج — مثال: 150.</div>
                <div id="beneficiariesHint" class="jobs-hint"></div>
              </div>
              <div class="col-md-4">
                <label class="form-label">فرص العمل المتوقعة</label>
                <input type="number" name="expected_jobs_count" id="jobsInput" class="form-control" min="0" placeholder="0">
                <div class="field-hint">عدد فرص العمل الجديدة المتوقعة — مثال: 20 فرصة عمل.</div>
                <div id="jobsHint" class="jobs-hint"></div>
              </div>
              <div class="col-md-4">
                <label class="form-label">عدد المشروعات المتوقعة</label>
                <input type="number" name="expected_projects_count" class="form-control" min="0" placeholder="0">
                <div class="field-hint">عدد المشروعات التي يُتوقع إطلاقها أو تطويرها — مثال: 5 مشروعات.</div>
              </div>
            </div>
          </div>

          <div class="section-card">
            <div class="section-card-title"><span class="icon">📝</span>وصف الاحتياج وسببه</div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">سبب الاحتياج</label>
                <textarea name="need_reason" class="form-control" rows="2" placeholder="لماذا هذا الاحتياج مطلوب في هذه المنطقة؟"></textarea>
                <div class="field-hint">اشرح المشكلة أو الفجوة التي يعالجها الاحتياج — مثال: "لا توجد حاضنة أعمال في المحافظة رغم وجود عدد كبير من رواد الأعمال".</div>
              </div>
            </div>
          </div>

          <div class="section-card">
            <div class="section-card-title"><span class="icon">⚙️</span>تفاصيل إضافية</div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">الأولوية <span class="text-danger">*</span></label>
                <select name="priority" class="form-select" required>
                  <option value="">-- اختر الأولوية --</option>
                  <option value="عاجلة">🔴 عاجلة</option>
                  <option value="عالية">🟠 عالية</option>
                  <option value="متوسطة" selected>🔵 متوسطة</option>
                  <option value="منخفضة">⚪ منخفضة</option>
                </select>
                <div class="field-hint">حدد مدى إلحاح الاحتياج — "عاجلة" للاحتياجات التي لا تحتمل التأجيل.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">التدخل المقترح</label>
                <select name="proposed_intervention" class="form-select">
                  <option value="">-- اختر --</option>
                  <option value="دراسة">دراسة</option>
                  <option value="تدريب">تدريب</option>
                  <option value="استشارات">استشارات</option>
                  <option value="تمويل">تمويل</option>
                  <option value="حاضنة">حاضنة</option>
                  <option value="بنية تحتية">بنية تحتية</option>
                </select>
                <div class="field-hint">ما نوع التدخل الأنسب برأيك لتلبية الاحتياج؟ — مثال: "تمويل" لمشروع يحتاج رأس مال.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">الجهة المسؤولة عن التنفيذ</label>
                <input name="responsible_entity" class="form-control" placeholder="مثال: فرع الهيئة في المحافظة، البلدية...">
                <div class="field-hint">الجهة المقترحة لتنفيذ أو متابعة الاحتياج — مثال: فرع حمص، مجلس المدينة.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">مستوى الاستعجال</label>
                <select name="urgency_level" class="form-select">
                  <option value="">-- اختر --</option>
                  <option value="immediate">فوري (خلال شهر)</option>
                  <option value="short_term">قصير المدى (3 أشهر)</option>
                  <option value="medium_term">متوسط المدى (6 أشهر)</option>
                  <option value="long_term">طويل المدى (سنة+)</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">مدة التنفيذ المتوقعة</label>
                <input name="expected_duration" class="form-control" placeholder="مثال: 6 أشهر، سنة واحدة...">
              </div>
              <div class="col-md-6">
                <label class="form-label">مستوى الأثر</label>
                <select name="impact_level" class="form-select">
                  <option value="">-- اختر --</option>
                  <option value="low">منخفض</option>
                  <option value="medium">متوسط</option>
                  <option value="high">عالي</option>
                  <option value="critical">حرج / استراتيجي</option>
                </select>
                <div class="field-hint">حجم الأثر المتوقع على المنطقة أو القطاع — "حرج / استراتيجي" للاحتياجات ذات الأثر الواسع.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">الشركاء المتاحون</label>
                <input name="available_partners" class="form-control" placeholder="جهات أو منظمات يمكن الشراكة معها">
                <div class="field-hint">جهات يمكن أن تشارك في التنفيذ — مثال: غرفة تجارة حمص، منظمات محلية.</div>
              </div>
              <div class="col-12">
                <label class="form-label">العوائق والتحديات</label>
                <textarea name="obstacles" class="form-control" rows="2" placeholder="ما هي أبرز العوائق التي تحول دون تلبية هذا الاحتياج؟"></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">متطلبات التنفيذ</label>
                <textarea name="requirements" class="form-control" rows="2" placeholder="ما الذي يحتاجه تنفيذ هذا الاحتياج من موارد وكفاءات؟"></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">ملاحظات إضافية</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="أي معلومات إضافية مفيدة..."></textarea>
              </div>
            </div>
          </div>

          <!-- ملخص قبل الإرسال -->
          <div id="submitSummary" class="section-card" style="background:#f8fafc">
            <div class="section-card-title"><span class="icon">✅</span>ملخص الاحتياج</div>
            <div id="summaryContent" class="small text-muted">...</div>
          </div>

          <div class="nav-btns">
            <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="4">
              <i class="bi bi-arrow-right me-1"></i>السابق
            </button>
            <button type="submit" class="btn btn-brand px-5" id="submitBtn">
              <i class="bi bi-check-circle me-1"></i>حفظ الاحتياج
            </button>
          </div>
        </div>

      </form>
      </div><!-- /formStepsWrapper -->
    </div>
  </section>
</main>
<div id="needSaveToast" role="status" aria-live="polite"></div>
<div id="needSaveModal" role="dialog" aria-modal="true" aria-labelledby="needSaveModalTitle">
  <div class="nsm-box">
    <div class="nsm-icon"><i class="bi bi-check-circle-fill" id="needSaveModalIcon"></i></div>
    <div class="nsm-title" id="needSaveModalTitle">تم الحفظ بنجاح</div>
    <div class="nsm-text" id="needSaveModalText">تم تسجيل الاحتياج بنجاح.</div>
    <div class="nsm-actions">
      <button type="button" class="btn btn-brand px-4" id="needSaveModalOk">حسناً</button>
    </div>
  </div>
</div>
<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/syria-geo-config.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/syrsic-data.js?v=2.1"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-platform.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/need-create.js?v=4.20"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-ai-fab.js?v=1.0"></script>
</body>
</html>
