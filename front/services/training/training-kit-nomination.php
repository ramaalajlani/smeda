<?php
$basePath = '../../';
$pageTitle = 'ترشيح حقيبة تدريبية';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .kit-nomination-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .kit-nomination-message{
      display:none;
      margin-bottom:20px;
      padding:14px 16px;
      border-radius:14px;
      font-weight:700;
      font-size:.95rem;
      line-height:1.8;
    }

    .kit-nomination-message.success{
      display:block;
      background:rgba(25,135,84,.08);
      color:#198754;
      border:1px solid rgba(25,135,84,.16);
    }

    .kit-nomination-message.error{
      display:block;
      background:rgba(220,53,69,.08);
      color:#dc3545;
      border:1px solid rgba(220,53,69,.16);
    }

    .kit-nomination-card{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:24px;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .kit-nomination-note{
      color:#6b7280;
      line-height:1.9;
      margin-bottom:16px;
    }

    .kit-nomination-hint{
      background:#f8fafc;
      border:1px dashed #d7dee8;
      border-radius:14px;
      padding:14px 16px;
      color:#64748b;
      line-height:1.9;
      margin-top:12px;
    }

    textarea.form-control{
      min-height:120px;
      resize:vertical;
    }
  </style>
</head>
<body>

<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="services-hero">
    <div class="container">
      <div class="breadcrumb-soft">
        <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
        <span>/</span>
        <a href="<?php echo $basePath; ?>services/index.php">خدمات المشروعات</a>
        <span>/</span>
        <span>ترشيح حقيبة تدريبية</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">ترشيح حقيبة تدريبية</h1>
          <p class="section-subtitle">
            تتيح هذه الصفحة للمدرب المعتمد ترشيح حقيبة تدريبية جديدة أو اقتراح تطوير حقيبة قائمة،
            مع إرسال تفاصيلها إلى الإدارة للمراجعة والاعتماد.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">يتضمن الترشيح</h3>
            <ul class="feature-list mb-0">
              <li>اختيار حقيبة قائمة أو كتابة اسم مقترح</li>
              <li>وصف الحقيبة التدريبية</li>
              <li>القطاع والتصنيف</li>
              <li>عدد الساعات المقترح</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="kitNominationMessage" class="kit-nomination-message"></div>

      <div id="kitNominationLoadingBox" class="kit-nomination-loading-box">
        جاري تجهيز نموذج ترشيح الحقيبة...
      </div>

      <div id="kitNominationContentWrap" class="d-none">
        <div class="kit-nomination-card">
          <h3 class="mb-3">نموذج الترشيح</h3>
          <p class="kit-nomination-note">
            يمكنك اختيار حقيبة موجودة مسبقًا إذا كان الترشيح متعلقًا بها، أو تركها فارغة وكتابة اسم حقيبة جديدة ضمن الحقل المخصص.
          </p>

          <form id="trainingKitNominationForm">
            <div class="row g-3">
              <div class="col-lg-6">
                <label class="form-label">حقيبة موجودة مسبقًا (اختياري)</label>
                <select id="trainingKitId" class="form-select">
                  <option value="">— لا يوجد / حقيبة جديدة —</option>
                </select>
              </div>

              <div class="col-lg-6">
                <label class="form-label">اسم الحقيبة المقترحة</label>
                <input
                  type="text"
                  id="proposedName"
                  class="form-control"
                  placeholder="مثال: حقيبة إدارة المشاريع الصغيرة"
                />
              </div>

              <div class="col-lg-6">
                <label class="form-label">القطاع</label>
                <input
                  type="text"
                  id="sector"
                  class="form-control"
                  placeholder="مثال: business / digital / management"
                />
              </div>

              <div class="col-lg-6">
                <label class="form-label">التصنيف</label>
                <input
                  type="text"
                  id="category"
                  class="form-control"
                  placeholder="مثال: management / marketing / entrepreneurship"
                />
              </div>

              <div class="col-lg-6">
                <label class="form-label">عدد الساعات</label>
                <input
                  type="number"
                  id="hours"
                  min="1"
                  max="500"
                  class="form-control"
                  placeholder="مثال: 24"
                />
              </div>

              <div class="col-12">
                <label class="form-label">وصف الحقيبة</label>
                <textarea
                  id="description"
                  class="form-control"
                  placeholder="اكتب وصفًا واضحًا لمحتوى الحقيبة التدريبية وأهدافها والفئة المستهدفة"
                ></textarea>
              </div>

              <div class="col-12">
                <div class="kit-nomination-hint">
                  ملاحظة: يجب تعبئة اسم حقيبة مقترحة أو اختيار حقيبة قائمة واحدة على الأقل، مع وصف واضح وعدد الساعات المقترح.
                </div>
              </div>

              <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" id="submitNominationBtn" class="btn btn-brand">
                  إرسال الترشيح
                </button>

                <button type="button" id="resetNominationBtn" class="btn btn-outline-secondary">
                  إعادة تعيين
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-kit-nomination.js?v=1.0"></script>

</body>
</html>