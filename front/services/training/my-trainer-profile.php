<?php
$basePath = '../../';
$pageTitle = 'ملفي المهني';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .profile-loading-box{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:28px 20px;
      text-align:center;
      color:#6c757d;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .profile-message{
      display:none;
      margin-bottom:20px;
      padding:14px 16px;
      border-radius:14px;
      font-weight:700;
      font-size:.95rem;
      line-height:1.8;
    }

    .profile-message.success{
      display:block;
      background:rgba(25,135,84,.08);
      color:#198754;
      border:1px solid rgba(25,135,84,.16);
    }

    .profile-message.error{
      display:block;
      background:rgba(220,53,69,.08);
      color:#dc3545;
      border:1px solid rgba(220,53,69,.16);
    }

    .profile-card{
      background:#fff;
      border:1px solid #e9eef5;
      border-radius:18px;
      padding:24px;
      box-shadow:0 10px 30px rgba(15,23,42,.04);
      margin-bottom:24px;
    }

    .profile-summary-grid{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:16px;
    }

    .profile-summary-item{
      background:#f8fafc;
      border:1px solid #e2e8f0;
      border-radius:14px;
      padding:14px;
      min-height:88px;
    }

    .profile-summary-item small{
      display:block;
      color:#64748b;
      margin-bottom:6px;
      font-weight:700;
    }

    .profile-summary-item strong{
      color:#0f172a;
      font-weight:800;
      line-height:1.8;
      word-break:break-word;
    }

    .profile-form-note{
      color:#6b7280;
      line-height:1.9;
      margin-bottom:16px;
    }

    .profile-readonly-badge{
      display:inline-flex;
      align-items:center;
      gap:8px;
      background:#f8fafc;
      border:1px solid #e2e8f0;
      border-radius:999px;
      padding:8px 14px;
      font-weight:700;
      color:#334155;
    }

    textarea.form-control{
      min-height:120px;
      resize:vertical;
    }

    @media (max-width: 991.98px){
      .profile-summary-grid{
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
        <span>ملفي المهني</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">ملفي المهني</h1>
          <p class="section-subtitle">
            إدارة الملف المهني الخاص بالمدرب، بما يتضمن النبذة المهنية، سنوات الخبرة، المهارات، والاهتمامات التخصصية.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">يتضمن الملف</h3>
            <ul class="feature-list mb-0">
              <li>البيانات الرسمية للمدرب</li>
              <li>الحالة المهنية والأهلية</li>
              <li>نبذة مهنية شبيهة بملف LinkedIn</li>
              <li>مهارات واهتمامات قابلة للتحديث</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section pt-0">
    <div class="container">
      <div id="profileMessage" class="profile-message"></div>

      <div id="profileLoadingBox" class="profile-loading-box">
        جاري تحميل الملف المهني...
      </div>

      <div id="profileContentWrap" class="d-none">
        <div class="profile-card">
          <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h3 class="mb-0">البيانات الرسمية</h3>
            <div class="profile-readonly-badge">هذه البيانات الرسمية للعرض فقط</div>
          </div>

          <div class="profile-summary-grid" id="trainerSummaryGrid"></div>
        </div>

        <div class="profile-card">
          <h3 class="mb-3">تعديل الملف المهني</h3>
          <p class="profile-form-note">
            يمكنك تعديل الملف المهني الخاص بك من هنا. هذا الجزء مخصص للنبذة المهنية والخبرات والمهارات،
            ولا يؤثر على بيانات الاعتماد الرسمية أو صلاحيات التدريب.
          </p>

          <form id="myTrainerProfileForm">
            <div class="row g-3">
              <div class="col-lg-6">
                <label class="form-label">العنوان المهني</label>
                <input type="text" id="headline" class="form-control" placeholder="مثال: مدرب معتمد في إدارة المشاريع" />
              </div>

              <div class="col-lg-6">
                <label class="form-label">سنوات الخبرة</label>
                <input type="number" id="experienceYears" class="form-control" min="0" max="60" placeholder="مثال: 5" />
              </div>

              <div class="col-12">
                <label class="form-label">نبذة مهنية</label>
                <textarea id="bio" class="form-control" placeholder="اكتب نبذة مختصرة عن خبرتك المهنية ومسارك التدريبي"></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">المهارات</label>
                <textarea id="skills" class="form-control" placeholder="مثال: إدارة مشاريع، تدريب، تسويق، إعداد حقائب"></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">الاهتمامات التخصصية</label>
                <textarea id="specialInterests" class="form-control" placeholder="مثال: تطوير الحقائب التدريبية، التدريب الرقمي، ريادة الأعمال"></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">الملخص المهني</label>
                <textarea id="linkedinSummary" class="form-control" placeholder="اكتب ملخصًا مهنيًا أعمق يعكس توجهك التدريبي وخبراتك"></textarea>
              </div>

              <div class="col-lg-4">
                <label class="form-label">إظهار الملف</label>
                <select id="visibility" class="form-select">
                  <option value="internal">داخلي</option>
                  <option value="public">عام</option>
                </select>
              </div>

              <div class="col-lg-8 d-flex align-items-end">
                <button type="submit" id="saveProfileBtn" class="btn btn-brand">
                  حفظ الملف المهني
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/training-my-profile.js?v=1.0"></script>

</body>
</html>