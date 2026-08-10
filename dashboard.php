<?php
$basePath = '';
$pageTitle = 'لوحة التحكم';
$activePage = 'dashboard';
?>
<?php include __DIR__ . '/includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <?php include __DIR__ . '/includes/layout/app-shell-styles.php'; ?>
</head>
<body>

<?php include __DIR__ . '/includes/layout/app-shell-open.php'; ?>

      <!-- Banner -->
      <div class="dash-banner mb-4">
        <i class="bi bi-speedometer2 dash-banner-icon"></i>
        <h1 class="dash-banner-title" id="dashBannerTitle">لوحة التحكم الرئيسية</h1>
        <p class="dash-banner-sub" id="dashBannerSub">
          عرض موحد للإحصائيات والبيانات — مخصص حسب صلاحيات حسابك الحالي.
        </p>
      </div>

      <!-- Stats -->
      <div id="dashboardLoadingBox" class="dash-loading">
        <i class="bi bi-hourglass-split me-2"></i>جاري تحميل الإحصائيات...
      </div>

      <div class="row g-3 mb-4" id="dashboardCards"></div>
      <div id="dashboardQuickLinks" class="d-none d-flex gap-2 flex-wrap mb-4"></div>

      <!-- تحليلات المدير العام / الإدارة الوطنية -->
      <div id="nationalAnalytics" class="d-none mb-4">
        <div class="dash-block mb-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
            <h3 class="dash-block-title mb-0"><i class="bi bi-bar-chart-line-fill"></i> لوحة المؤشرات التحليلية</h3>
            <span class="badge rounded-pill text-bg-light border" style="font-weight:700;color:#0f5e4f">مباشر من قاعدة البيانات</span>
          </div>
          <p class="dash-block-sub mb-0">مخططات تفاعلية لأداء المنصة على المستوى الوطني — متجاوبة مع الموبايل.</p>
        </div>

        <div class="row g-3">
          <div class="col-12 col-xl-8">
            <div class="dash-block h-100">
              <h3 class="dash-block-title"><i class="bi bi-geo-alt-fill"></i> الأداء حسب المحافظة</h3>
              <p class="dash-block-sub">مقارنة الدورات والمتدربين والاحتياجات والتمويل.</p>
              <div class="bi-chart-wrap bi-chart-lg"><canvas id="chartGovernorates"></canvas></div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="dash-block h-100">
              <h3 class="dash-block-title"><i class="bi bi-pie-chart-fill"></i> توزيع وحدات المنصة</h3>
              <p class="dash-block-sub">حجم النشاط عبر الوحدات الرئيسية.</p>
              <div class="bi-chart-wrap"><canvas id="chartModules"></canvas></div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="dash-block h-100">
              <h3 class="dash-block-title"><i class="bi bi-award-fill"></i> مسار الشهادات</h3>
              <p class="dash-block-sub">معتمدة / قيد الاعتماد / مرفوضة.</p>
              <div class="bi-chart-wrap"><canvas id="chartCertificates"></canvas></div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="dash-block h-100">
              <h3 class="dash-block-title"><i class="bi bi-cash-coin"></i> حالات التمويل</h3>
              <p class="dash-block-sub">توزيع طلبات التمويل حسب الحالة.</p>
              <div class="bi-chart-wrap"><canvas id="chartFunding"></canvas></div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="dash-block h-100">
              <h3 class="dash-block-title"><i class="bi bi-clipboard2-data-fill"></i> حالات الاحتياجات</h3>
              <p class="dash-block-sub">توزيع احتياجات GIS حسب الحالة.</p>
              <div class="bi-chart-wrap"><canvas id="chartNeeds"></canvas></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom blocks -->
      <div class="row g-3">
        <div class="col-xl-5" id="dashActivityCol">
          <div class="dash-block">
            <h3 class="dash-block-title"><i class="bi bi-lightning-charge-fill"></i> نظرة سريعة</h3>
            <p class="dash-block-sub">ملخص أهم ما يمكنك القيام به حسب صلاحياتك.</p>
            <div class="activity-list" id="dashboardActivityList">
              <div class="activity-item">
                <div class="activity-item-icon"><i class="bi bi-hourglass-split"></i></div>
                <div>
                  <h4>جاري التحميل</h4>
                  <p>يتم تجهيز الأنشطة وفقًا لصلاحياتك.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-7" id="dashShortcutsCol">
          <div class="dash-block">
            <h3 class="dash-block-title"><i class="bi bi-grid-fill"></i> اختصارات العمل</h3>
            <p class="dash-block-sub">روابط سريعة لأكثر الصفحات استخدامًا.</p>
            <div class="quick-grid" id="quickLinksGrid">
              <a href="<?php echo $basePath; ?>services/training/training-centers-list.php" class="quick-link" data-permission="view_centers">
                <i class="bi bi-buildings-fill"></i>
                <h4>المراكز التدريبية</h4>
                <p>استعراض المراكز المعتمدة وحالتها.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/training/training-trainers-list.php" class="quick-link" data-permission="view_trainers">
                <i class="bi bi-person-workspace"></i>
                <h4>المدربون</h4>
                <p>استعراض المدربين والتخصصات.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/training/training-courses-list.php" class="quick-link" data-permission="view_courses">
                <i class="bi bi-book-half"></i>
                <h4>الدورات التدريبية</h4>
                <p>متابعة الدورات ومواعيدها.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/training/training-trainees-list.php" class="quick-link" data-permission="view_trainees">
                <i class="bi bi-people-fill"></i>
                <h4>المتدربون</h4>
                <p>عرض المتدربين المرتبطين بالدورات.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/training/training-certificates-list.php" class="quick-link" data-permission="view_certificates">
                <i class="bi bi-award-fill"></i>
                <h4>الشهادات</h4>
                <p>عرض الشهادات الصادرة وحالاتها.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/training/training-certificates-approve.php" class="quick-link" data-any-permission="approve_center_certificates,approve_training_certificates,approve_deputy_certificates,approve_general_director_certificates">
                <i class="bi bi-check2-all"></i>
                <h4>اعتماد الشهادات</h4>
                <p>اعتماد الشهادات حسب مرحلتك.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/training/training-verification.php" class="quick-link" data-permission="verify_certificates">
                <i class="bi bi-shield-check"></i>
                <h4>التحقق من الشهادات</h4>
                <p>التحقق من صحة الشهادة.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/training/training-kits-list.php" class="quick-link" data-permission="view_kits">
                <i class="bi bi-briefcase-fill"></i>
                <h4>الحقائب التدريبية</h4>
                <p>استعراض الحقائب والمستويات.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/gis/needs-map.php" class="quick-link" data-permission="needs.map">
                <i class="bi bi-globe-americas"></i>
                <h4>خريطة الاحتياجات</h4>
                <p>عرض الاحتياجات على خريطة GIS.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/finance/finance.php" class="quick-link" data-permission="finance.metrics.view">
                <i class="bi bi-bank2"></i>
                <h4>منظومة التمويل</h4>
                <p>استعراض طلبات وقرارات التمويل.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/admin/admin-users.php" class="quick-link" data-access-admin>
                <i class="bi bi-person-fill-gear"></i>
                <h4>إدارة المستخدمين</h4>
                <p>إدارة الحسابات والأدوار والصلاحيات.</p>
              </a>
              <a href="<?php echo $basePath; ?>services/training/registration-requests-review.php" class="quick-link" data-permission="view_registration_requests">
                <i class="bi bi-clipboard2-check-fill"></i>
                <h4>مراجعة الطلبات</h4>
                <p>استعراض ومراجعة طلبات التسجيل.</p>
              </a>
            </div>
          </div>
        </div>
      </div>


<?php include __DIR__ . '/includes/layout/app-shell-close.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/dashboard.js?v=3.0"></script>

<script>
/* نشاط لوحة التحكم — خاص بهذه الصفحة فقط */
(function () {
  function fillActivity(){
    const box = document.getElementById('dashboardActivityList');
    if(!box) return;
    const hp = p => !!(window.AppAuth?.hasPermission?.(p));
    const items=[];
    const ACT = [
      ['view_centers',      'bi-buildings',          'متابعة المراكز',          'استعراض المراكز التدريبية ومراجعة حالة الاعتماد.'],
      ['view_trainers',     'bi-person-workspace',   'متابعة المدربين',         'استعراض المدربين المعتمدين والتخصصات.'],
      ['view_courses',      'bi-book-half',          'إدارة الدورات',           'متابعة الدورات وحالة التنفيذ والمدربين.'],
      ['issue_certificates','bi-award-fill',         'إصدار الشهادات',          'إصدار شهادات الحضور أو الاجتياز للمتدربين.'],
      ['approve_center_certificates','bi-check2-circle','اعتماد المركز',        'اعتماد الشهادات ضمن مرحلة المركز.'],
      ['approve_training_certificates','bi-check-all','اعتماد قسم التدريب',    'اعتماد الشهادات ضمن مرحلة قسم التدريب.'],
      ['approve_deputy_certificates','bi-patch-check-fill','اعتماد نائب المدير', 'اعتماد الشهادات وتوقيع نائب المدير العام.'],
      ['approve_general_director_certificates','bi-patch-check-fill','اعتماد المدير العام', 'الاعتماد النهائي والتوقيع الإلكتروني.'],
      ['verify_certificates','bi-shield-check',      'التحقق من الشهادات',      'التحقق من صحة الشهادات.'],
      ['nominate_training_kits','bi-star-fill',      'ترشيح الحقائب',           'ترشيح حقيبة تدريبية جديدة.'],
      ['view_registration_requests','bi-clipboard2-check-fill','مراجعة الطلبات','استعراض طلبات التسجيل ومتابعتها.'],
    ];
    ACT.forEach(([perm,icon,title,desc])=>{ if(hp(perm)) items.push([icon,title,desc]); });
    if(!items.length) items.push(['bi-info-circle-fill','أهلاً بك','استخدم الروابط السريعة حسب صلاحياتك.']);
    box.innerHTML = items.map(([icon,title,desc])=>`
      <div class="activity-item">
        <div class="activity-item-icon"><i class="bi ${icon}"></i></div>
        <div><h4>${title}</h4><p>${desc}</p></div>
      </div>`).join('');
  }
  document.addEventListener('DOMContentLoaded', fillActivity);
})();
</script>

</body>
</html>
