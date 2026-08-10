<?php
$basePath = '../../';
$pageTitle = 'تفاصيل الدورة التدريبية';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .course-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .course-info-card,
    .course-trainees-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:24px;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .course-info-grid{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:16px;
    }

    .course-info-item{
      background:#f8fafc;
      border:1px solid #e2e8f0;
      border-radius:14px;
      padding:14px;
      min-height:88px;
    }

    .course-info-item small{
      display:block;
      color:#64748b;
      margin-bottom:6px;
      font-weight:700;
    }

    .course-info-item strong{
      color:#0f172a;
      font-weight:800;
      line-height:1.8;
      word-break:break-word;
    }

    .course-stats-strip{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      margin-bottom:18px;
    }

    .course-stat-chip{
      background:#f8fafc;
      border:1px solid #e2e8f0;
      border-radius:999px;
      padding:10px 16px;
      font-weight:800;
      color:#0f172a;
    }

    .course-header-actions{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
    }

    .course-empty-note{
      background:#fff;
      border:1px dashed #d7dee8;
      border-radius:16px;
      padding:32px 20px;
      text-align:center;
      color:#6c757d;
    }

    .course-action-message{
      display:none;
      margin-bottom:18px;
      padding:14px 16px;
      border-radius:14px;
      font-weight:700;
      font-size:.95rem;
      line-height:1.8;
    }

    .course-action-message.success{
      display:block;
      background:rgba(25,135,84,.08);
      color:#198754;
      border:1px solid rgba(25,135,84,.16);
    }

    .course-action-message.error{
      display:block;
      background:rgba(220,53,69,.08);
      color:#dc3545;
      border:1px solid rgba(220,53,69,.16);
    }

    .table td,.table th{
      vertical-align:middle;
      white-space:normal;
    }

    .issue-action-cell{
      min-width:150px;
    }

    .issue-help-note{
      font-size:.9rem;
      color:#6b7280;
      margin-top:10px;
      line-height:1.9;
    }

    @media (max-width: 991.98px){
      .course-info-grid{
        grid-template-columns:1fr;
      }
    }

    .course-modal{
      position:fixed; inset:0; z-index:1050;
      display:flex; align-items:center; justify-content:center; padding:20px;
    }
    .course-modal.d-none{ display:none !important; }
    .course-modal-backdrop{ position:absolute; inset:0; background:rgba(15,23,42,.45); }
    .course-modal-dialog{
      position:relative; z-index:2; width:min(760px,100%); max-height:90vh; overflow:auto;
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
        <a href="training-courses-list.php">الدورات التدريبية</a>
        <span>/</span>
        <span>تفاصيل الدورة</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">تفاصيل الدورة التدريبية</h1>
          <p class="section-subtitle">
            عرض بيانات الدورة التدريبية، المدرب، المركز، الحقيبة، والمتدربين داخل الدورة
            مع الحضور والنتائج والساعات وإصدار شهادات الحضور أو الاجتياز وفق شروط الاعتماد.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">تتضمن الصفحة</h3>
            <ul class="feature-list mb-0">
              <li>بيانات الدورة الأساسية</li>
              <li>قائمة المتدربين داخل الدورة</li>
              <li>حضور / نجاح / رسوب / ساعات</li>
              <li>إصدار شهادة حضور أو اجتياز</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="courseActionMessage" class="course-action-message d-none"></div>

      <div id="courseLoadingBox" class="course-loading-box">
        جاري تحميل تفاصيل الدورة التدريبية...
      </div>

      <div id="courseDetailsWrap" class="d-none">
        <div class="course-info-card">
          <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h3 class="mb-0">البيانات الأساسية للدورة</h3>

            <div class="course-header-actions">
              <div id="courseManageToolbar" class="d-flex flex-wrap gap-2"></div>
              <a href="training-courses-list.php" class="soft-btn">العودة إلى الدورات</a>
              <a href="training-certificates-list.php" class="soft-btn">الشهادات</a>
              <a href="training-certificates-approve.php" class="soft-btn">اعتماد الشهادات</a>
              <a href="training-verification.php" class="soft-btn">التحقق من الشهادات</a>
            </div>
          </div>

          <div class="course-info-grid" id="courseInfoGrid"></div>
        </div>

        <div class="course-trainees-box">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="mb-0">المتدربون داخل الدورة</h3>
            <div id="courseStatsBox" class="course-stats-strip"></div>
          </div>

          <div class="issue-help-note">
            يظهر زر إصدار الشهادة فقط للمتدرب المؤهل ضمن دورة مكتملة ومستوفية لشروط الاعتماد.
            إذا كان المتدرب ناجحًا تظهر شهادة اجتياز، وإذا كان حاضرًا فقط تظهر شهادة حضور.
          </div>

          <div class="data-table-wrap mt-3">
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>رقم المتدرب</th>
                    <th>اسم المتدرب</th>
                    <th>الحضور</th>
                    <th>النتيجة</th>
                    <th>العلامة</th>
                    <th>الساعات</th>
                    <th>الحالة العامة</th>
                    <th class="issue-action-cell">الإجراءات</th>
                  </tr>
                </thead>
                <tbody id="courseTraineesTableBody">
                  <tr>
                    <td colspan="9" class="text-center text-muted">جاري التحميل...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div id="courseTraineesEmptyNote" class="course-empty-note d-none mt-3">
            لا يوجد متدربون مرتبطون بهذه الدورة حاليًا.
          </div>
        </div>
      </div>

      <div id="editCourseModal" class="course-modal d-none">
        <div class="course-modal-backdrop" data-close-modal="1"></div>
        <div class="course-modal-dialog">
          <button type="button" class="course-modal-close" data-close-modal="1">×</button>
          <h3 class="mb-3">تعديل الدورة</h3>
          <form id="editCourseForm">
            <div class="row g-3">
              <div class="col-12"><label class="form-label">العنوان</label><input id="editTitle" class="form-control" required></div>
              <div class="col-md-4"><label class="form-label">الساعات المخططة</label><input type="number" min="1" id="editPlannedHours" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">الساعات الفعلية</label><input type="number" min="0" id="editActualHours" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">السعة</label><input type="number" min="1" id="editCapacity" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">الحالة</label><select id="editStatus" class="form-select"><option value="draft">مسودة</option><option value="scheduled">مجدولة</option><option value="ongoing">جارية</option></select></div>
              <div class="col-12"><label class="form-label">ملاحظات</label><textarea id="editNotes" class="form-control" rows="3"></textarea></div>
              <div class="col-12"><button type="submit" class="btn btn-brand">حفظ التعديلات</button></div>
            </div>
          </form>
        </div>
      </div>

      <div id="editTraineeResultModal" class="course-modal d-none">
        <div class="course-modal-backdrop" data-close-modal="1"></div>
        <div class="course-modal-dialog">
          <button type="button" class="course-modal-close" data-close-modal="1">×</button>
          <h3 class="mb-3">تعديل نتيجة المتدرب</h3>
          <form id="editTraineeResultForm">
            <input type="hidden" id="editTraineeId">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">الحضور</label><select id="editAttendanceStatus" class="form-select"><option value="registered">مسجل</option><option value="attended">حضر</option><option value="absent">غائب</option><option value="withdrawn">منسحب</option><option value="completed">مكتمل</option></select></div>
              <div class="col-md-6"><label class="form-label">النتيجة</label><select id="editResult" class="form-select"><option value="pending">قيد الانتظار</option><option value="passed">ناجح</option><option value="failed">راسب</option><option value="attendance_only">حضور فقط</option></select></div>
              <div class="col-md-6"><label class="form-label">العلامة</label><input type="number" min="0" max="100" step="0.01" id="editScore" class="form-control"></div>
              <div class="col-md-6"><label class="form-label">ساعات الحضور</label><input type="number" min="0" id="editAttendedHours" class="form-control"></div>
              <div class="col-12"><label class="form-label">ملاحظات</label><textarea id="editTraineeNotes" class="form-control" rows="2"></textarea></div>
              <div class="col-12"><button type="submit" class="btn btn-brand">حفظ النتيجة</button></div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-course-show.js?v=1.3"></script>

</body>
</html>