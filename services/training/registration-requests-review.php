<?php
$basePath = '../../';
$pageTitle = 'مراجعة طلبات التسجيل';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .review-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .review-message{
      display:none;
      margin-bottom:20px;
      padding:14px 16px;
      border-radius:14px;
      font-weight:700;
      line-height:1.8;
    }

    .review-message.success{
      display:block;
      background:rgba(25,135,84,.08);
      color:#198754;
      border:1px solid rgba(25,135,84,.16);
    }

    .review-message.error{
      display:block;
      background:rgba(220,53,69,.08);
      color:#dc3545;
      border:1px solid rgba(220,53,69,.16);
    }

    .review-note{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:16px 18px;
      color:#64748b;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
      line-height:1.9;
      font-weight:600;
    }

    .request-details-card{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:24px;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-top:24px;
    }

    .request-details-card h3{
      font-size:1.1rem;
      font-weight:800;
      margin-bottom:18px;
      color:#0f172a;
    }

    .request-details-grid{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:14px;
    }

    .request-details-item{
      background:#f8fafc;
      border:1px solid #e2e8f0;
      border-radius:14px;
      padding:14px;
      min-height:86px;
    }

    .request-details-item small{
      display:block;
      color:#64748b;
      margin-bottom:6px;
      font-weight:700;
    }

    .request-details-item strong{
      display:block;
      color:#0f172a;
      line-height:1.9;
      font-weight:800;
      word-break:break-word;
      white-space:pre-wrap;
    }

    .request-details-empty{
      text-align:center;
      color:#64748b;
      padding:18px;
      background:#f8fafc;
      border:1px dashed #d7dee8;
      border-radius:14px;
      line-height:1.9;
    }

    .table td,
    .table th{
      vertical-align:middle;
      white-space:normal;
    }

    .review-action-cell{
      min-width:180px;
    }

    .review-row-actions{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
    }

    .review-small-note{
      font-size:.9rem;
      color:#6b7280;
      line-height:1.8;
    }

    .review-dialog-overlay{
      position:fixed;
      inset:0;
      background:rgba(2, 6, 23, .45);
      display:none;
      align-items:center;
      justify-content:center;
      padding:16px;
      z-index:1200;
    }

    .review-dialog-overlay.active{
      display:flex;
    }

    .review-dialog{
      width:100%;
      max-width:520px;
      background:#fff;
      border:1px solid #e2e8f0;
      border-radius:18px;
      box-shadow:0 20px 60px rgba(2, 6, 23, .2);
      overflow:hidden;
    }

    .review-dialog-header{
      padding:16px 18px;
      border-bottom:1px solid #edf2f7;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
    }

    .review-dialog-title{
      margin:0;
      font-size:1rem;
      font-weight:800;
      color:#0f172a;
    }

    .review-dialog-close{
      border:0;
      background:transparent;
      color:#64748b;
      font-size:1.3rem;
      line-height:1;
      cursor:pointer;
    }

    .review-dialog-body{
      padding:16px 18px 8px;
    }

    .review-dialog-label{
      display:block;
      margin-bottom:8px;
      color:#334155;
      font-size:.9rem;
      font-weight:700;
    }

    .review-dialog-textarea{
      width:100%;
      min-height:130px;
      border:1px solid #cbd5e1;
      border-radius:12px;
      padding:12px;
      font-size:.92rem;
      line-height:1.7;
      resize:vertical;
    }

    .review-dialog-footer{
      display:flex;
      justify-content:flex-end;
      gap:10px;
      padding:12px 18px 18px;
    }

    @media (max-width: 991.98px){
      .request-details-grid{
        grid-template-columns:1fr;
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
        <span>مراجعة طلبات التسجيل</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">مراجعة طلبات التسجيل</h1>
          <p class="section-subtitle">
            عرض الطلبات الإدارية الخاصة بالمراكز والمدربين والمتدربين والدورات،
            مع إظهار التفاصيل الكاملة لكل طلب وإتاحة الموافقة أو الرفض بحسب صلاحيات الحساب الحالي.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">ما الذي تعرضه الصفحة؟</h3>
            <ul class="feature-list mb-0">
              <li>فلترة حسب نوع الطلب</li>
              <li>فلترة حسب الحالة</li>
              <li>عرض الطلبات باختصار داخل الجدول</li>
              <li>عرض التفاصيل الكاملة أسفل الجدول</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">

      <div class="review-note">
        ملاحظة: تختلف تفاصيل العرض حسب نوع الطلب المحدد.
        عند الضغط على زر <strong>عرض التفاصيل</strong> سيتم إظهار كامل بيانات الطلب أسفل الجدول.
      </div>

      <div id="registrationReviewMessage" class="review-message"></div>

      <div id="registrationReviewLoadingBox" class="review-loading-box">
        جاري تحميل الطلبات...
      </div>

      <div class="services-grid-card mb-4">
        <div class="row g-3">
          <div class="col-lg-4">
            <label class="form-label">نوع الطلب</label>
            <select id="registrationRequestType" class="form-select">
              <option value="center">طلبات المراكز</option>
              <option value="trainer">طلبات المدربين</option>
              <option value="trainee">طلبات المتدربين</option>
              <option value="course">طلبات الدورات</option>
            </select>
          </div>

          <div class="col-lg-4">
            <label class="form-label">الحالة</label>
            <select id="registrationRequestStatus" class="form-select">
              <option value="">الكل</option>
              <option value="pending">قيد الانتظار</option>
              <option value="under_review">قيد المراجعة</option>
              <option value="submitted">مرسل</option>
              <option value="guardian_confirmed">مؤكد من الجهة الأعلى</option>
              <option value="completed">مكتمل</option>
              <option value="approved">مقبول</option>
              <option value="rejected">مرفوض</option>
              <option value="cancelled">ملغي</option>
            </select>
          </div>

          <div class="col-lg-4 d-flex align-items-end">
            <button type="button" id="loadRegistrationRequestsBtn" class="btn btn-brand w-100">
              تحميل
            </button>
          </div>
        </div>
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead id="registrationRequestsTableHead">
              <tr>
                <th>#</th>
                <th>رقم الطلب</th>
                <th>الاسم</th>
                <th>الحالة</th>
                <th>الملاحظات</th>
                <th class="review-action-cell">إجراء</th>
              </tr>
            </thead>
            <tbody id="registrationRequestsTableBody">
              <tr>
                <td colspan="6" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="request-details-card" id="requestDetailsCard">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
          <h3 class="mb-0">تفاصيل الطلب المحدد</h3>
          <div class="review-small-note">يتم تحديث هذا القسم عند الضغط على "عرض التفاصيل".</div>
        </div>

        <div id="requestDetailsContent" class="request-details-empty">
          لم يتم تحديد أي طلب بعد.
        </div>
      </div>

    </div>
  </section>
</main>

<div id="reviewNotesDialogOverlay" class="review-dialog-overlay" aria-hidden="true">
  <div class="review-dialog" role="dialog" aria-modal="true" aria-labelledby="reviewNotesDialogTitle">
    <div class="review-dialog-header">
      <h3 id="reviewNotesDialogTitle" class="review-dialog-title">ملاحظات الإجراء</h3>
      <button type="button" id="reviewNotesDialogCloseBtn" class="review-dialog-close" aria-label="إغلاق">×</button>
    </div>
    <div class="review-dialog-body">
      <label for="reviewNotesDialogInput" id="reviewNotesDialogLabel" class="review-dialog-label">الملاحظات (اختياري)</label>
      <textarea id="reviewNotesDialogInput" class="review-dialog-textarea" placeholder="اكتب الملاحظات هنا..."></textarea>
    </div>
    <div class="review-dialog-footer">
      <button type="button" id="reviewNotesDialogCancelBtn" class="btn btn-outline-secondary">إلغاء</button>
      <button type="button" id="reviewNotesDialogConfirmBtn" class="btn btn-brand">تأكيد</button>
    </div>
  </div>
</div>

<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/registration-requests-review.js?v=2.1"></script>
</body>
</html>