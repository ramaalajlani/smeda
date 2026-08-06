<?php
$basePath = '../../';
$pageTitle = 'الدورات التدريبية';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .courses-empty-state{
      background:#fff;
      border:1px dashed #d7dee8;
      border-radius:16px;
      padding:32px 20px;
      text-align:center;
      color:#6c757d;
    }

    .courses-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .table td, .table th{
      vertical-align:middle;
      white-space:normal;
    }

    .course-modal{
      position:fixed; inset:0; z-index:1050;
      display:flex; align-items:center; justify-content:center; padding:20px;
    }
    .course-modal.d-none{ display:none !important; }
    .course-modal-backdrop{ position:absolute; inset:0; background:rgba(15,23,42,.45); }
    .course-modal-dialog{
      position:relative; z-index:2; width:min(920px,100%); max-height:90vh; overflow:auto;
      background:#fff; border-radius:18px; padding:24px; box-shadow:0 20px 50px rgba(15,23,42,.15);
    }
    .course-modal-close{
      position:absolute; top:12px; left:12px; border:none; background:#f1f5f9;
      width:36px; height:36px; border-radius:50%; font-size:1.4rem; line-height:1;
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
        <span>الدورات التدريبية</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">الدورات التدريبية</h1>
          <p class="section-subtitle">
            استعراض جميع الدورات التدريبية المعتمدة مع المركز، المدرب، الحقيبة، نمط التنفيذ، المدة، والحالة العامة.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">يعرض الملف</h3>
            <ul class="feature-list mb-0">
              <li>كود الدورة وعنوانها</li>
              <li>المركز التدريبي</li>
              <li>المدرب والحقيبة</li>
              <li>النمط الزمني وحالة الدورة</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">

      <div class="d-flex justify-content-end mb-3">
        <button type="button" id="createCourseBtn" class="btn btn-brand" data-permission="manage_courses">
          إنشاء دورة
        </button>
      </div>

      <div id="coursesLoadingBox" class="courses-loading-box">
        جاري تحميل بيانات الدورات التدريبية...
      </div>

      <div id="coursesEmptyState" class="courses-empty-state d-none mb-4">
        لا توجد دورات تدريبية متاحة حالياً.
      </div>

      <div class="data-table-wrap">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>كود الدورة</th>
                <th>عنوان الدورة</th>
                <th>المركز</th>
                <th>المدرب</th>
                <th>الحقيبة</th>
                <th>نوع التنفيذ</th>
                <th>الفترة</th>
                <th>الساعات</th>
                <th>الحالة</th>
                <th>الإجراء</th>
              </tr>
            </thead>
            <tbody id="trainingCoursesTableBody">
              <tr>
                <td colspan="11" class="text-center text-muted">جاري التحميل...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>

  <div id="createCourseModal" class="course-modal d-none">
    <div class="course-modal-backdrop" data-close-modal="1"></div>
    <div class="course-modal-dialog">
      <button type="button" class="course-modal-close" data-close-modal="1">×</button>
      <h3 class="mb-3">إنشاء دورة تدريبية</h3>
      <div id="createCourseMessage" class="small mb-2"></div>
      <form id="createCourseForm">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">المركز</label><select id="createCenterId" class="form-select" required></select></div>
          <div class="col-md-6"><label class="form-label">المدرب</label><select id="createTrainerId" class="form-select" required></select></div>
          <div class="col-md-6"><label class="form-label">الحقيبة</label><select id="createKitId" class="form-select" required></select></div>
          <div class="col-md-6"><label class="form-label">البرنامج (اختياري)</label><select id="createProgramId" class="form-select"></select></div>
          <div class="col-12"><label class="form-label">عنوان الدورة</label><input id="createTitle" class="form-control" required></div>
          <div class="col-md-4"><label class="form-label">نوع التنفيذ</label><select id="createDeliveryMode" class="form-select"><option value="offline">حضوري</option><option value="online">أونلاين</option></select></div>
          <div class="col-md-4"><label class="form-label">المنصة</label><input id="createPlatform" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">الحالة</label><select id="createStatus" class="form-select"><option value="scheduled">مجدولة</option><option value="draft">مسودة</option><option value="ongoing">جارية</option></select></div>
          <div class="col-md-4"><label class="form-label">تاريخ البدء</label><input type="date" id="createStartDate" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">تاريخ الانتهاء</label><input type="date" id="createEndDate" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">الساعات المخططة</label><input type="number" min="1" id="createPlannedHours" class="form-control" required></div>
          <div class="col-md-4"><label class="form-label">السعة</label><input type="number" min="1" id="createCapacity" class="form-control" required></div>
          <div class="col-12"><label class="form-label">ملاحظات</label><textarea id="createNotes" class="form-control" rows="3"></textarea></div>
          <div class="col-12"><button type="submit" class="btn btn-brand">حفظ الدورة</button></div>
        </div>
      </form>
    </div>
  </div>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-courses-list.js?v=1.3"></script>

</body>
</html>