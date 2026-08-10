<?php

$basePath = '';

require_once __DIR__ . '/includes/layout/paths.php';
require_once __DIR__ . '/includes/config/register-account-types.php';

$frontBaseUrl = resolve_front_base_url();
$apiBaseUrl = resolve_api_base_url();
$backendBaseUrl = resolve_backend_base_url();
$registerCatalog = register_account_types_catalog();
$registerPreselect = isset($_GET['type']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['type']) : '';
$registerPreselect = $registerPreselect ? register_account_type_normalize($registerPreselect) : '';
if ($registerPreselect && !register_account_type_meta($registerPreselect)) {
    $registerPreselect = '';
}

$pageTitle = 'إنشاء حساب';

?>

<?php include __DIR__ . '/includes/layout/html-open.php'; ?>
<head>

  <meta charset="UTF-8">

  <meta name="frontend-base-url" content="<?php echo htmlspecialchars($frontBaseUrl, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta name="api-base-url" content="<?php echo htmlspecialchars($apiBaseUrl, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta name="backend-base-url" content="<?php echo htmlspecialchars($backendBaseUrl, ENT_QUOTES, 'UTF-8'); ?>" />

  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">



  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

  <link href="<?php echo $basePath; ?>assets/css/auth-pages.css?v=1.1" rel="stylesheet">

</head>

<body class="auth-page auth-page--wizard">

  <div class="auth-card auth-card--wizard">

    <h1 class="auth-title">إنشاء حساب جديد</h1>

    <p class="auth-subtitle">أكمل الخطوات التالية لإنشاء حسابك وإرسال بياناتك التفصيلية</p>



    <div class="wizard-progress" aria-label="تقدم التسجيل">

      <div class="wizard-progress-label" id="wizardProgressLabel">الخطوة 1 من 3</div>

      <div class="wizard-steps">

        <div class="wizard-step-dot active" data-step-dot="1">1</div>

        <div class="wizard-step-line" data-step-line="1"></div>

        <div class="wizard-step-dot" data-step-dot="2">2</div>

        <div class="wizard-step-line" data-step-line="2"></div>

        <div class="wizard-step-dot" data-step-dot="3">3</div>

      </div>

      <div class="wizard-step-names">

        <span class="active" data-step-name="1">بيانات الحساب</span>

        <span data-step-name="2">البيانات التفصيلية</span>

        <span data-step-name="3">المراجعة</span>

      </div>

    </div>



    <div id="registerMessage" class="register-message"></div>



    <form id="registerForm" novalidate>

      <div class="wizard-panel active" data-step="1">

        <h2 class="wizard-section-title">بيانات الحساب الأساسية</h2>



        <div class="row g-3">

          <div class="col-md-6">

            <label class="form-label fw-bold" for="registerName">الاسم الكامل</label>

            <input type="text" id="registerName" class="form-control" placeholder="أدخل الاسم الكامل" autocomplete="name">

            <div class="invalid-feedback"></div>

          </div>



          <div class="col-md-6">

            <label class="form-label fw-bold" for="registerEmail">البريد الإلكتروني</label>

            <input type="text" id="registerEmail" class="form-control" placeholder="مثال: ahmad" autocomplete="username" dir="ltr">

            <div class="form-text">سيُكمَّل تلقائياً بنطاق الهيئة <bdi>@smedc.gov.sy</bdi></div>

            <div class="invalid-feedback"></div>

          </div>



          <div class="col-md-6">

            <label class="form-label fw-bold" for="registerPassword">كلمة المرور</label>

            <input type="password" id="registerPassword" class="form-control" placeholder="8 أحرف على الأقل" autocomplete="new-password">

            <div class="invalid-feedback"></div>

          </div>



          <div class="col-md-6">

            <label class="form-label fw-bold" for="registerPasswordConfirmation">تأكيد كلمة المرور</label>

            <input type="password" id="registerPasswordConfirmation" class="form-control" placeholder="أعد إدخال كلمة المرور" autocomplete="new-password">

            <div class="invalid-feedback"></div>

          </div>



          <div class="col-12">

            <label class="form-label fw-bold" for="registerAccountType">نوع الحساب</label>

            <select id="registerAccountType" class="form-select">

              <option value="">— اختر نوع الحساب —</option>

              <?php foreach ($registerCatalog as $groupKey => $group): ?>
              <optgroup label="<?php echo htmlspecialchars($group['label']['ar']); ?>">
                <?php foreach ($group['types'] as $typeKey => $typeMeta): ?>
                <option value="<?php echo htmlspecialchars($typeKey); ?>"<?php echo $registerPreselect === $typeKey ? ' selected' : ''; ?>>
                  <?php echo htmlspecialchars($typeMeta['label']['ar']); ?>
                </option>
                <?php endforeach; ?>
              </optgroup>
              <?php endforeach; ?>

            </select>

            <div class="invalid-feedback"></div>

          </div>

        </div>



        <div class="wizard-nav">

          <button type="button" class="btn btn-brand" id="wizardNextBtn1">التالي</button>

        </div>

      </div>



      <div class="wizard-panel" data-step="2" data-type-panel="trainee">

        <h2 class="wizard-section-title">بيانات المتدرب</h2>



        <div class="row g-3">

          <div class="col-md-6">

            <label class="form-label fw-bold" for="tFullName">الاسم الكامل</label>

            <input type="text" id="tFullName" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="tNationalId">الرقم الوطني</label>

            <input type="text" id="tNationalId" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="tPhone">الهاتف</label>

            <input type="text" id="tPhone" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="tEmail">البريد الإلكتروني</label>

            <input type="email" id="tEmail" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="tCity">المدينة</label>

            <select id="tCity" class="form-select">
              <option value="">— اختر المدينة —</option>
              <option value="دمشق">دمشق</option>
              <option value="ريف دمشق">ريف دمشق</option>
              <option value="حلب">حلب</option>
              <option value="حمص">حمص</option>
              <option value="حماة">حماة</option>
              <option value="اللاذقية">اللاذقية</option>
              <option value="طرطوس">طرطوس</option>
              <option value="إدلب">إدلب</option>
              <option value="درعا">درعا</option>
              <option value="السويداء">السويداء</option>
              <option value="القنيطرة">القنيطرة</option>
              <option value="دير الزور">دير الزور</option>
              <option value="الرقة">الرقة</option>
              <option value="الحسكة">الحسكة</option>
            </select>

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="tEducationLevel">المستوى التعليمي</label>

            <select id="tEducationLevel" class="form-select">
              <option value="">— اختر المستوى التعليمي —</option>
              <option value="ابتدائي">ابتدائي</option>
              <option value="إعدادي">إعدادي</option>
              <option value="ثانوي">ثانوي</option>
              <option value="معهد متوسط">معهد متوسط</option>
              <option value="جامعي">جامعي</option>
              <option value="دبلوم">دبلوم</option>
              <option value="ماجستير">ماجستير</option>
              <option value="دكتوراه">دكتوراه</option>
              <option value="أخرى">أخرى</option>
            </select>

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="tRegistrationMode">نمط التسجيل</label>

            <select id="tRegistrationMode" class="form-select">

              <option value="self" selected>ذاتي</option>

              <option value="guardian">ولي أمر / جهة أعلى</option>

            </select>

            <div class="invalid-feedback"></div>

          </div>

        </div>



        <div class="wizard-nav">

          <button type="button" class="btn btn-outline-secondary" data-wizard-prev="1">السابق</button>

          <button type="button" class="btn btn-brand" data-wizard-next="3">التالي</button>

        </div>

      </div>



      <div class="wizard-panel" data-step="2" data-type-panel="trainer">

        <h2 class="wizard-section-title">بيانات المدرب</h2>



        <div class="row g-3">

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trTrainingCenterId">المركز التدريبي</label>

            <select id="trTrainingCenterId" class="form-select">

              <option value="">— بدون مركز —</option>

            </select>

            <div class="form-text">يمكن اختيار المركز بعد إنشاء الحساب إن لم تظهر القائمة.</div>

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trFullName">الاسم الكامل</label>

            <input type="text" id="trFullName" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trNationalId">الرقم الوطني</label>

            <input type="text" id="trNationalId" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trPhone">الهاتف</label>

            <input type="text" id="trPhone" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trEmail">البريد الإلكتروني</label>

            <input type="email" id="trEmail" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trSpecialization">التخصص</label>

            <input type="text" id="trSpecialization" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trClassificationRequested">التصنيف المطلوب</label>

            <input type="text" id="trClassificationRequested" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trHasTot">هل يحمل ToT؟</label>

            <select id="trHasTot" class="form-select">

              <option value="1">نعم</option>

              <option value="0" selected>لا</option>

            </select>

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trTotCertificateNumber">رقم شهادة ToT</label>

            <input type="text" id="trTotCertificateNumber" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="trTotCertificateSource">جهة إصدار ToT</label>

            <input type="text" id="trTotCertificateSource" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

        </div>



        <div class="wizard-nav">

          <button type="button" class="btn btn-outline-secondary" data-wizard-prev="1">السابق</button>

          <button type="button" class="btn btn-brand" data-wizard-next="3">التالي</button>

        </div>

      </div>



      <div class="wizard-panel" data-step="2" data-type-panel="center">

        <h2 class="wizard-section-title">بيانات المركز التدريبي</h2>



        <div class="row g-3">

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cCenterName">اسم المركز</label>

            <input type="text" id="cCenterName" class="form-control" placeholder="أدخل اسم المركز التدريبي">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cCenterCity">المدينة</label>

            <input type="text" id="cCenterCity" class="form-control" placeholder="مثال: إدلب">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cCenterAddress">العنوان</label>

            <input type="text" id="cCenterAddress" class="form-control" placeholder="العنوان التفصيلي">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cCenterPhone">الهاتف</label>

            <input type="text" id="cCenterPhone" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cCenterEmail">البريد الإلكتروني</label>

            <input type="email" id="cCenterEmail" class="form-control" placeholder="name@example.com">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cCenterClassification">التصنيف المطلوب</label>

            <input type="text" id="cCenterClassification" class="form-control" placeholder="مثال: first_class">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cSupportsOfflineTraining">يدعم التدريب الحضوري</label>

            <select id="cSupportsOfflineTraining" class="form-select">

              <option value="1">نعم</option>

              <option value="0">لا</option>

            </select>

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cSupportsOnlineTraining">يدعم التدريب الأونلاين</label>

            <select id="cSupportsOnlineTraining" class="form-select">

              <option value="1">نعم</option>

              <option value="0">لا</option>

            </select>

            <div class="invalid-feedback"></div>

          </div>

        </div>



        <hr class="my-4">



        <h3 class="wizard-section-title">موقع المركز على الخريطة</h3>

        <div class="row g-3 align-items-stretch mb-3">

          <div class="col-lg-8">

            <div class="wizard-map-box">

              <div id="wizardCenterMap"></div>

              <div class="wizard-map-help">انقر على الخريطة لتحديد موقع المركز، ويمكنك سحب العلامة بعد وضعها.</div>

            </div>

          </div>

          <div class="col-lg-4">

            <div class="wizard-coords-box">

              <div class="mb-2">خط العرض: <span id="cLatitudePreview">—</span></div>

              <div class="mb-2">خط الطول: <span id="cLongitudePreview">—</span></div>

            </div>

            <input type="hidden" id="cCenterLatitude">

            <input type="hidden" id="cCenterLongitude">

            <div class="invalid-feedback d-block" id="cMapError"></div>

          </div>

        </div>



        <hr class="my-4">



        <h3 class="wizard-section-title">بيانات الترخيص</h3>

        <div class="row g-3">

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cLicenseNumber">رقم الترخيص</label>

            <input type="text" id="cLicenseNumber" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-md-6">

            <label class="form-label fw-bold" for="cLicenseIssueDate">تاريخ الترخيص</label>

            <input type="date" id="cLicenseIssueDate" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-12">

            <label class="form-label fw-bold" for="cLicenseIssuedBy">الجهة الصادرة عنها</label>

            <input type="text" id="cLicenseIssuedBy" class="form-control">

            <div class="invalid-feedback"></div>

          </div>

          <div class="col-12">

            <label class="form-label fw-bold" for="cLicenseImage">صورة الترخيص</label>

            <input type="file" id="cLicenseImage" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp">

            <div class="wizard-file-preview" id="cLicenseFilePreview"></div>

            <div class="invalid-feedback"></div>

          </div>

        </div>



        <div class="wizard-nav">

          <button type="button" class="btn btn-outline-secondary" data-wizard-prev="1">السابق</button>

          <button type="button" class="btn btn-brand" data-wizard-next="3">التالي</button>

        </div>

      </div>



      <?php
      foreach ($registerCatalog as $group) {
          foreach ($group['types'] as $typeKey => $typeMeta) {
              if (!empty($typeMeta['has_detail_wizard'])) {
                  continue;
              }
              $intro = $typeMeta['step2_intro']['ar'] ?? '';
              ?>
      <div class="wizard-panel" data-step="2" data-type-panel="<?php echo htmlspecialchars($typeKey); ?>">

        <h2 class="wizard-section-title"><?php echo htmlspecialchars($typeMeta['label']['ar']); ?></h2>

        <?php if ($intro !== ''): ?>
        <p class="text-muted mb-4" style="line-height:1.85;font-size:0.92rem;"><?php echo htmlspecialchars($intro); ?></p>
        <?php endif; ?>

        <div class="alert alert-light border" style="border-radius:14px;font-size:0.92rem;line-height:1.8;">
          <i class="bi bi-info-circle-fill text-success me-1"></i>
          لا حاجة لبيانات إضافية هنا — راجع بيانات الحساب في الخطوة التالية ثم أكمل ملفك من الصفحة المخصصة لدورك.
        </div>

        <div class="wizard-nav">

          <button type="button" class="btn btn-outline-secondary" data-wizard-prev="1">السابق</button>

          <button type="button" class="btn btn-brand" data-wizard-next="3">التالي</button>

        </div>

      </div>
      <?php
          }
      }
      ?>



      <div class="wizard-panel" data-step="3">

        <h2 class="wizard-section-title">مراجعة البيانات</h2>

        <p class="text-muted mb-3" style="line-height:1.85;font-size:0.92rem;">راجع بياناتك قبل إنشاء الحساب. لن يتم إرسال أي طلب حتى تضغط «إنشاء الحساب».</p>



        <div id="reviewSummary"></div>



        <div class="col-12 mt-3">


        </div>



        <div class="wizard-nav">

          <button type="button" class="btn btn-outline-secondary" data-wizard-prev="2">السابق</button>

          <button type="submit" class="btn btn-brand" id="registerSubmitBtn">إنشاء الحساب</button>

          <button type="button" class="btn btn-outline-brand" id="retryDetailBtn">إعادة محاولة حفظ البيانات</button>

        </div>

      </div>

    </form>



    <div class="auth-footer-links">

      لديك حساب بالفعل؟

      <a href="<?php echo front_url('login.php'); ?>">تسجيل الدخول</a>

    </div>

  </div>



  <script src="<?php echo $basePath; ?>assets/js/core/config.js?v=2.0"></script>

  <script src="<?php echo $basePath; ?>assets/js/core/routes.js?v=2.0"></script>

  <script src="<?php echo $basePath; ?>assets/js/core/api.js?v=2.1"></script>


  <script src="<?php echo $basePath; ?>assets/js/core/auth.js?v=20260810-4"></script>

  <!-- يعرّف window.SiteI18n المطلوب في register.js (تحقّق الحقول والرسائل) -->
  <script src="<?php echo $basePath; ?>assets/js/core/i18n.js?v=1.0"></script>

  <?php
  $registerJsLabels = [];
  $registerJsRedirects = [];
  $registerJsSkipDetail = [];
  foreach ($registerCatalog as $group) {
      foreach ($group['types'] as $key => $meta) {
          $registerJsLabels[$key] = $meta['label']['ar'];
          if (!empty($meta['redirect_after'])) {
              $registerJsRedirects[$key] = $meta['redirect_after'];
          }
          if (empty($meta['has_detail_wizard'])) {
              $registerJsSkipDetail[] = $key;
          }
      }
  }
  ?>
  <script>
    window.__REGISTER_LABELS = <?php echo json_encode($registerJsLabels, JSON_UNESCAPED_UNICODE); ?>;
    window.__REGISTER_REDIRECTS = <?php echo json_encode($registerJsRedirects, JSON_UNESCAPED_UNICODE); ?>;
    window.__REGISTER_SKIP_DETAIL = <?php echo json_encode($registerJsSkipDetail, JSON_UNESCAPED_UNICODE); ?>;
    window.__REGISTER_PRESELECT = <?php echo json_encode($registerPreselect, JSON_UNESCAPED_UNICODE); ?>;
  </script>

  <script src="<?php echo $basePath; ?>assets/js/pages/register.js?v=10.0"></script>

</body>

</html>


