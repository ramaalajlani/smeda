<?php
$basePath = '../../';
$pageTitle = 'طلب تسجيل مركز تدريبي';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />

  <style>
    .registration-message{
      display:none;
      margin-bottom:20px;
      padding:14px 16px;
      border-radius:14px;
      font-weight:700;
      line-height:1.8;
    }

    .registration-message.success{
      display:block;
      background:rgba(25,135,84,.08);
      color:#198754;
      border:1px solid rgba(25,135,84,.16);
    }

    .registration-message.error{
      display:block;
      background:rgba(220,53,69,.08);
      color:#dc3545;
      border:1px solid rgba(220,53,69,.16);
    }

    .registration-card{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:22px;
      padding:24px;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
    }

    .registration-note{
      color:#6b7280;
      line-height:1.9;
      margin-bottom:18px;
    }

    .section-mini-title{
      font-size:1.05rem;
      font-weight:800;
      color:#0f172a;
      margin:8px 0 14px;
    }

    .map-box{
      border:1px solid #dbe4ee;
      border-radius:18px;
      overflow:hidden;
      background:#fff;
    }

    #centerLocationMap{
      width:100%;
      height:360px;
    }

    .map-help{
      padding:12px 14px;
      border-top:1px solid #eef2f7;
      color:#64748b;
      font-size:.95rem;
      line-height:1.8;
      background:#f8fafc;
    }

    .coords-box{
      background:#f8fafc;
      border:1px solid #e2e8f0;
      border-radius:14px;
      padding:14px 16px;
      color:#334155;
      font-weight:700;
      line-height:1.9;
      min-height:100%;
    }

    .upload-note{
      color:#64748b;
      font-size:.9rem;
      margin-top:8px;
      line-height:1.8;
    }

    .file-preview-box{
      margin-top:12px;
      padding:12px 14px;
      border:1px dashed #cbd5e1;
      border-radius:14px;
      background:#f8fafc;
      color:#334155;
      font-weight:700;
      display:none;
    }

    .file-preview-box.show{
      display:block;
    }

    .form-control,
    .form-select{
      min-height:48px;
    }

    .form-control[type="file"]{
      padding-top:10px;
      padding-bottom:10px;
      min-height:auto;
    }

    .action-row{
      display:flex;
      gap:12px;
      justify-content:flex-end;
      flex-wrap:wrap;
      margin-top:8px;
    }

    @media (max-width: 767.98px){
      #centerLocationMap{
        height:300px;
      }

      .action-row{
        flex-direction:column;
      }

      .action-row .btn{
        width:100%;
      }
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
        <span>طلب تسجيل مركز تدريبي</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">طلب تسجيل مركز تدريبي</h1>
          <p class="section-subtitle">
            إرسال طلب اعتماد وتسجيل مركز تدريبي جديد، مع بيانات الترخيص والموقع الجغرافي
            ليتم مراجعته من الجهة المخولة.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">يتضمن الطلب</h3>
            <ul class="feature-list mb-0">
              <li>بيانات المركز الأساسية</li>
              <li>الموقع على الخريطة</li>
              <li>بيانات الترخيص النظامي</li>
              <li>رفع صورة عن الترخيص</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="centerRegistrationMessage" class="registration-message"></div>

      <div class="registration-card">
        <h3 class="mb-3">نموذج الطلب</h3>
        <p class="registration-note">
          يرجى تعبئة بيانات المركز بدقة. يمكن تحديد الموقع بالنقر على الخريطة،
          مع إدخال بيانات الترخيص ورفع صورة واضحة عنه.
        </p>

        <form id="centerRegistrationRequestForm" enctype="multipart/form-data">
          <div class="row g-3">
            <div class="col-lg-6">
              <label class="form-label">اسم المركز</label>
              <input
                type="text"
                id="centerName"
                class="form-control"
                placeholder="أدخل اسم المركز التدريبي"
                required
              />
            </div>

            <div class="col-lg-6">
              <label class="form-label">المدينة</label>
              <input
                type="text"
                id="centerCity"
                class="form-control"
                placeholder="مثال: إدلب"
                required
              />
            </div>

            <div class="col-lg-6">
              <label class="form-label">العنوان</label>
              <input
                type="text"
                id="centerAddress"
                class="form-control"
                placeholder="أدخل العنوان التفصيلي"
                required
              />
            </div>

            <div class="col-lg-6">
              <label class="form-label">الهاتف</label>
              <input
                type="text"
                id="centerPhone"
                class="form-control"
                placeholder="أدخل رقم الهاتف"
                required
              />
            </div>

            <div class="col-lg-6">
              <label class="form-label">البريد الإلكتروني</label>
              <input
                type="email"
                id="centerEmail"
                class="form-control"
                placeholder="name@example.com"
              />
            </div>

            <div class="col-lg-6">
              <label class="form-label">التصنيف المطلوب</label>
              <input
                type="text"
                id="centerClassification"
                class="form-control"
                placeholder="مثال: first_class"
              />
            </div>

            <div class="col-lg-6">
              <label class="form-label">يدعم التدريب الحضوري</label>
              <select id="supportsOfflineTraining" class="form-select">
                <option value="1">نعم</option>
                <option value="0">لا</option>
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">يدعم التدريب الأونلاين</label>
              <select id="supportsOnlineTraining" class="form-select">
                <option value="1">نعم</option>
                <option value="0">لا</option>
              </select>
            </div>
          </div>

          <hr class="my-4">

          <div class="section-mini-title">موقع المركز على الخريطة</div>

          <div class="row g-3 align-items-stretch">
            <div class="col-lg-8">
              <div class="map-box">
                <div id="centerLocationMap"></div>
                <div class="map-help">
                  انقر على الخريطة لتحديد موقع المركز بدقة، ويمكنك سحب العلامة بعد وضعها.
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="coords-box">
                <div class="mb-2">خط العرض: <span id="latitudePreview">—</span></div>
                <div class="mb-2">خط الطول: <span id="longitudePreview">—</span></div>
                <div class="small text-muted mt-3">
                  سيتم حفظ الإحداثيات ضمن الطلب عند إرسال النموذج.
                </div>
              </div>
            </div>

            <input type="hidden" id="centerLatitude" />
            <input type="hidden" id="centerLongitude" />
          </div>

          <hr class="my-4">

          <div class="section-mini-title">بيانات الترخيص</div>

          <div class="row g-3">
            <div class="col-lg-6">
              <label class="form-label">رقم الترخيص</label>
              <input
                type="text"
                id="licenseNumber"
                class="form-control"
                placeholder="أدخل رقم الترخيص"
                required
              />
            </div>

            <div class="col-lg-6">
              <label class="form-label">تاريخ الترخيص</label>
              <input
                type="date"
                id="licenseIssueDate"
                class="form-control"
                required
              />
            </div>

            <div class="col-lg-12">
              <label class="form-label">الجهة الصادرة عنها</label>
              <input
                type="text"
                id="licenseIssuedBy"
                class="form-control"
                placeholder="مثال: مديرية التعليم / الجهة المخولة"
                required
              />
            </div>

            <div class="col-lg-12">
              <label class="form-label">صورة الترخيص</label>
              <input
                type="file"
                id="licenseImage"
                class="form-control"
                accept=".jpg,.jpeg,.png,.pdf,.webp"
                required
              />
              <div class="upload-note">
                الملفات المسموحة: JPG / PNG / WEBP / PDF
              </div>
              <div id="licenseFilePreview" class="file-preview-box"></div>
            </div>
          </div>

          <div class="action-row">
            <button type="button" id="resetCenterRegistrationBtn" class="btn btn-outline-secondary">
              إعادة تعيين
            </button>

            <button type="submit" id="submitCenterRegistrationBtn" class="btn btn-brand">
              إرسال الطلب
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/center-registration-request.js?v=2.0"></script>

</body>
</html>