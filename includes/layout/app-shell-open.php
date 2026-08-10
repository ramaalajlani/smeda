<?php
/* قوقعة لوحة التحكم الموحّدة — التوبار + السايدبار + فتح main. تتطلب $basePath. */
$basePath = isset($basePath) ? $basePath : "";
require_once __DIR__ . '/shell-preference.php';

/* أدوار لها قوقعة داشبورد مخصّصة: أبقِ نفس السايدبار عند الانتقال للخريطة/التدريب */
if (empty($forceAppShell) && authority_use_dashboard_shell()) {
    if (empty($dashboardRole)) {
        $dashboardRole = authority_shell_role();
    }
    include __DIR__ . '/dashboard-shell-open.php';
    return;
}
?>
  <!-- TOPBAR -->
  <header class="app-topbar">
    <button class="btn-sidebar-toggle" id="sidebarToggleBtn" aria-label="القائمة">
      <i class="bi bi-list"></i>
    </button>

    <div class="app-topbar-brand">
      <div class="brand-badge">SME</div>
      <div>
        <div style="line-height:1.2">SMEDA</div>
        <div style="font-size:.76rem;font-weight:600;color:var(--c-muted)">نظام إدارة المشاريع الصغيرة</div>
      </div>
    </div>

    <div class="app-topbar-user">
      <div class="d-none d-sm-block" style="text-align:left">
        <div class="topbar-user-name" id="topbarUserName">جاري التحميل...</div>
        <div class="topbar-user-role" id="topbarUserRole">—</div>
      </div>
      <div class="topbar-avatar" id="topbarAvatar">م</div>
      <button class="btn-logout-top" id="topbarLogoutBtn">
        <i class="bi bi-box-arrow-right"></i>
        <span class="d-none d-sm-inline">خروج</span>
      </button>
    </div>
  </header>

  <!-- SIDEBAR OVERLAY -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="app-shell">

    <!-- SIDEBAR -->
    <aside class="app-sidebar" id="appSidebar">

      <!-- User card -->
      <div class="sb-user-card">
        <div class="sb-user-top">
          <div class="sb-user-avatar" id="sbAvatar">م</div>
          <div>
            <p class="sb-user-name" id="sbUserName">جاري التحميل...</p>
            <p class="sb-user-role" id="sbUserRole">—</p>
          </div>
        </div>
        <div class="sb-user-meta">
          <div class="sb-user-meta-item">
            <div class="sb-user-meta-label">البريد</div>
            <div class="sb-user-meta-value" id="sbUserEmail">—</div>
          </div>
          <div class="sb-user-meta-item">
            <div class="sb-user-meta-label">نوع الحساب</div>
            <div class="sb-user-meta-value" id="sbUserType">—</div>
          </div>
        </div>
      </div>

      <!-- الرئيسية -->
      <div class="sb-section">
        <a href="<?php echo $basePath; ?>dashboard.php" class="sb-link active" data-hide-role="center_user,trainer_user">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>لوحة التحكم</span>
        </a>
        <a href="<?php echo $basePath; ?>services/training/center-app.php" class="sb-link" data-any-role="center_user,trainer_user">
          <i class="bi bi-phone-fill"></i>
          <span>تطبيق المركز</span>
        </a>
        <a href="<?php echo $basePath; ?>services/training/center-profile.php" class="sb-link" data-any-role="center_user,trainer_user">
          <i class="bi bi-person-circle"></i>
          <span>حسابي</span>
        </a>
        <a href="<?php echo front_url('index.php'); ?>" class="sb-link" data-hide-role="center_user,trainer_user">
          <i class="bi bi-house-door-fill"></i>
          <span>الصفحة الرئيسية</span>
        </a>
      </div>

      <div class="sb-divider"></div>

      <!-- التدريب -->
      <div class="sb-section" data-any-permission="view_centers,view_trainers,view_kits,view_programs,view_courses,view_trainees,view_trainer_profiles,edit_own_trainer_profile,nominate_training_kits">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbTraining" aria-expanded="true">
          <span class="sb-section-icon"><i class="bi bi-mortarboard-fill"></i></span>
          <span class="sb-section-label">التدريب</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse show" id="sbTraining">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/training/center-app.php" class="sb-link" data-any-role="center_user,trainer_user">
              <i class="bi bi-mortarboard"></i><span>دوراتي (تطبيق المركز)</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/center-trainers.php" class="sb-link" data-any-role="center_user">
              <i class="bi bi-person-workspace"></i><span>مدربو المركز</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/center-trainees-list.php" class="sb-link" data-any-role="center_user">
              <i class="bi bi-people"></i><span>متدربو المركز</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/center-kits.php" class="sb-link" data-any-role="center_user">
              <i class="bi bi-briefcase-fill"></i><span>حقائب المركز</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-centers-list.php" class="sb-link" data-permission="view_centers" data-hide-role="center_user,trainer_user,trainee_user">
              <i class="bi bi-buildings"></i><span>المراكز التدريبية</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-trainers-list.php" class="sb-link" data-permission="view_trainers" data-hide-role="center_user,trainer_user">
              <i class="bi bi-person-workspace"></i><span>المدربون</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-kits-list.php" class="sb-link" data-permission="view_kits" data-hide-role="center_user,trainer_user">
              <i class="bi bi-briefcase-fill"></i><span>الحقائب التدريبية</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-programs-list.php" class="sb-link" data-permission="view_programs" data-hide-role="center_user,trainer_user">
              <i class="bi bi-collection-fill"></i><span>البرامج التدريبية</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-courses-list.php" class="sb-link" data-permission="view_courses" data-hide-role="center_user,trainer_user">
              <i class="bi bi-book-half"></i><span>الدورات التدريبية</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-courses-list.php" class="sb-link" data-role="trainer_user">
              <i class="bi bi-journal-text"></i><span>دوراتي</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-trainees-list.php" class="sb-link" data-permission="view_trainees" data-hide-role="center_user,trainer_user">
              <i class="bi bi-people-fill"></i><span>المتدربون</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/my-trainer-profile.php" class="sb-link" data-any-permission="view_trainer_profiles,edit_own_trainer_profile">
              <i class="bi bi-person-badge-fill"></i><span>ملفي المهني</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/my-training-kit-nominations.php" class="sb-link" data-permission="nominate_training_kits">
              <i class="bi bi-bookmarks-fill"></i><span>ترشيحاتي</span>
            </a>
          </div>
        </div>
      </div>

      <!-- الشهادات -->
      <div class="sb-section" data-permission="view_certificates">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbCerts" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-patch-check-fill"></i></span>
          <span class="sb-section-label">الشهادات</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbCerts">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/training/training-certificates-list.php" class="sb-link" data-permission="view_certificates" data-hide-role="center_user,trainer_user">
              <i class="bi bi-award-fill"></i><span>سجل الشهادات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/my-electronic-signature.php" class="sb-link" data-any-permission="approve_center_certificates,approve_training_certificates,approve_deputy_certificates,approve_general_director_certificates">
              <i class="bi bi-pen-fill"></i><span>التوقيع الإلكتروني</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-certificates-approve.php" class="sb-link" data-any-permission="approve_center_certificates,approve_training_certificates,approve_deputy_certificates,approve_general_director_certificates">
              <i class="bi bi-check2-circle"></i><span>اعتماد الشهادات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-verification.php" class="sb-link" data-permission="verify_certificates">
              <i class="bi bi-shield-check"></i><span>التحقق من الشهادات</span>
            </a>
          </div>
        </div>
      </div>

      <!-- طلبات التسجيل -->
      <div class="sb-section" data-permission="view_registration_requests">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbRequests" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-clipboard2-check-fill"></i></span>
          <span class="sb-section-label">طلبات التسجيل</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbRequests">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/training/registration-requests-review.php" class="sb-link" data-permission="view_registration_requests">
              <i class="bi bi-list-check"></i><span>مراجعة الطلبات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/center-registration-request.php" class="sb-link" data-permission="create_center_registration_requests">
              <i class="bi bi-building-add"></i><span>طلب تسجيل مركز</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/trainer-registration-request.php" class="sb-link" data-permission="create_trainer_registration_requests">
              <i class="bi bi-person-plus-fill"></i><span>طلب تسجيل مدرب</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/trainee-registration-request.php" class="sb-link" data-permission="create_trainee_registration_requests">
              <i class="bi bi-person-badge-fill"></i><span>طلب تسجيل متدرب</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/course-registration-request.php" class="sb-link" data-permission="create_course_registration_requests">
              <i class="bi bi-journal-plus"></i><span>طلب تسجيل دورة</span>
            </a>
          </div>
        </div>
      </div>

      <!-- الحقائب والترشيحات -->
      <div class="sb-section" data-permission="nominate_training_kits">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbKitNom" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-star-fill"></i></span>
          <span class="sb-section-label">الترشيحات</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbKitNom">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/training/training-kit-nomination.php" class="sb-link" data-permission="nominate_training_kits">
              <i class="bi bi-send-fill"></i><span>رشح حقيبة</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/my-training-kit-nominations.php" class="sb-link" data-permission="nominate_training_kits">
              <i class="bi bi-bookmarks-fill"></i><span>ترشيحاتي</span>
            </a>
            <a href="<?php echo $basePath; ?>services/training/training-kit-nominations-review.php" class="sb-link" data-permission="review_training_kit_nominations">
              <i class="bi bi-clipboard-data-fill"></i><span>مراجعة الترشيحات</span>
            </a>
          </div>
        </div>
      </div>

      <div class="sb-divider"></div>

      <!-- التمويل -->
      <div class="sb-section" data-permission="finance.applications.view">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbFinance" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-bank2"></i></span>
          <span class="sb-section-label">التمويل</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbFinance">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/finance/finance.php" class="sb-link" data-permission="finance.metrics.view">
              <i class="bi bi-graph-up-arrow"></i><span>منظومة التمويل</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-applications-list.php" class="sb-link" data-permission="finance.applications.view">
              <i class="bi bi-inboxes-fill"></i><span>طلبات التمويل</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-cloud.php" class="sb-link" data-permission="finance.applications.view">
              <i class="bi bi-cloud-fill"></i><span>سحابة التمويل</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-apply.php" class="sb-link" data-permission="finance.applications.create">
              <i class="bi bi-file-earmark-plus-fill"></i><span>تقديم طلب تمويل</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-funded.php" class="sb-link" data-permission="finance.loans.view">
              <i class="bi bi-cash-coin"></i><span>القروض الممولة</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-defaulted.php" class="sb-link" data-permission="finance.loans.view">
              <i class="bi bi-exclamation-triangle-fill"></i><span>القروض المتعثرة</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-metrics.php" class="sb-link" data-permission="finance.metrics.view">
              <i class="bi bi-bar-chart-fill"></i><span>مؤشرات التمويل</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-manager-dashboard.php" class="sb-link" data-any-permission="finance.metrics.view,finance.metrics.national">
              <i class="bi bi-speedometer2"></i><span>لوحة المالية</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/funding-partner-dashboard.php" class="sb-link" data-permission="finance.funding_partner.dashboard">
              <i class="bi bi-bank"></i><span>لوحة شريك التمويل</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/my-partner-assignments.php" class="sb-link" data-permission="finance.partner_assignments.view_own">
              <i class="bi bi-inboxes-fill"></i><span>إحالات الشريك</span>
            </a>
          </div>
        </div>
      </div>

      <!-- البنوك وجهات التمويل -->
      <div class="sb-section" data-permission="finance.central_bank.dashboard">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbBanks" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-safe2-fill"></i></span>
          <span class="sb-section-label">البنوك والتمويل</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbBanks">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/finance/central-bank-dashboard.php" class="sb-link" data-permission="finance.central_bank.dashboard">
              <i class="bi bi-bank"></i><span>لوحة المصرف المركزي</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/funding-partners.php" class="sb-link" data-permission="finance.partners.view_all">
              <i class="bi bi-handshake-fill"></i><span>جهات التمويل</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-funded.php" class="sb-link" data-permission="finance.loans.view">
              <i class="bi bi-credit-card-2-front-fill"></i><span>قرارات التمويل</span>
            </a>
            <a href="<?php echo $basePath; ?>services/finance/finance-metrics.php" class="sb-link" data-permission="finance.bank_metrics.view">
              <i class="bi bi-speedometer2"></i><span>مؤشرات البنوك</span>
            </a>
          </div>
        </div>
      </div>

      <div class="sb-divider"></div>

      <!-- خريطة GIS -->
      <div class="sb-section" data-permission="needs.view">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbGIS" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-geo-alt-fill"></i></span>
          <span class="sb-section-label">خريطة GIS</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbGIS">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/gis/needs-dashboard.php" class="sb-link" data-permission="needs.dashboard">
              <i class="bi bi-map-fill"></i><span>لوحة الاحتياجات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/gis/needs-map.php" class="sb-link" data-permission="needs.map">
              <i class="bi bi-globe-americas"></i><span>خريطة الاحتياجات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/gis/needs-list.php" class="sb-link" data-permission="needs.view">
              <i class="bi bi-list-ul"></i><span>سجل الاحتياجات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/gis/need-create.php" class="sb-link" data-permission="needs.create">
              <i class="bi bi-plus-circle-fill"></i><span>تسجيل احتياج</span>
            </a>
            <a href="<?php echo $basePath; ?>services/gis/admin-units.php" class="sb-link" data-permission="needs.manage_admin_units">
              <i class="bi bi-layers-fill"></i><span>الوحدات الإدارية</span>
            </a>
            <a href="<?php echo $basePath; ?>services/gis/needs-lookups.php" class="sb-link" data-permission="needs.manage_lookups">
              <i class="bi bi-tags-fill"></i><span>إدارة قوائم الاحتياجات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/gis/data-entry-dashboard.php" class="sb-link" data-role="data_entry">
              <i class="bi bi-pencil-square"></i><span>لوحة الإدخال</span>
            </a>
            <a href="<?php echo $basePath; ?>services/gis/data-reviewer-dashboard.php" class="sb-link" data-role="data_reviewer">
              <i class="bi bi-clipboard-check-fill"></i><span>لوحة المراجعة</span>
            </a>
          </div>
        </div>
      </div>

      <!-- الرسائل والإشعارات -->
      <div class="sb-section">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbInbox" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-envelope-fill"></i></span>
          <span class="sb-section-label">الرسائل والإشعارات</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbInbox">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>inbox/inbox-list.php" class="sb-link">
              <i class="bi bi-inbox-fill"></i><span>صندوق الرسائل</span>
            </a>
            <a href="<?php echo $basePath; ?>notifications/notifications-list.php" class="sb-link">
              <i class="bi bi-bell-fill"></i><span>الإشعارات</span>
            </a>
          </div>
        </div>
      </div>

      <!-- الاستشارات -->
      <div class="sb-section" data-any-role="admin,super_admin,system_admin,general_director,branch_manager,branch_officer,governor,project_owner,consultant_union_admin,consultant_office">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbConsulting" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-headset"></i></span>
          <span class="sb-section-label">الاستشارات</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbConsulting">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/consulting/consulting-admin-dashboard.php" class="sb-link" data-any-role="admin,super_admin,system_admin,general_director,consultant_union_admin,branch_manager">
              <i class="bi bi-speedometer2"></i><span>لوحة التحكم</span>
            </a>
            <a href="<?php echo $basePath; ?>services/consulting/consulting-requests-list.php" class="sb-link" data-any-role="admin,super_admin,system_admin,general_director,branch_manager,branch_officer,governor,project_owner,consultant_union_admin,consultant_office">
              <i class="bi bi-list-check"></i><span>طلبات الاستشارة</span>
            </a>
            <a href="<?php echo $basePath; ?>services/consulting/consulting-request-create.php" class="sb-link" data-any-role="project_owner,admin,super_admin,system_admin,general_director,branch_manager,governor">
              <i class="bi bi-plus-circle-fill"></i><span>طلب استشارة جديدة</span>
            </a>
            <a href="<?php echo $basePath; ?>services/consulting/consulting-offices-list.php" class="sb-link" data-any-role="admin,super_admin,system_admin,general_director,branch_manager,branch_officer,governor,project_owner,consultant_union_admin,consultant_office">
              <i class="bi bi-buildings-fill"></i><span>المكاتب الاستشارية</span>
            </a>
          </div>
        </div>
      </div>

      <!-- الملف المهني -->
      <div class="sb-section" data-any-permission="view_trainer_profiles,edit_own_trainer_profile">
        <a href="<?php echo $basePath; ?>services/training/my-trainer-profile.php" class="sb-link">
          <i class="bi bi-person-badge-fill"></i><span>ملفي المهني</span>
        </a>
      </div>

      <!-- القوى العاملة -->
      <div class="sb-section" data-platform-admin>
        <a href="<?php echo $basePath; ?>services/workforce/candidates-list.php" class="sb-link">
          <i class="bi bi-person-lines-fill"></i><span>القوى العاملة</span>
        </a>
      </div>

      <div class="sb-divider"></div>

      <!-- الإدارة الوطنية -->
      <div class="sb-section" data-national-admin>
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbNational" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-flag-fill"></i></span>
          <span class="sb-section-label">الإدارة الوطنية</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbNational">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/admin/admin-branches.php" class="sb-link">
              <i class="bi bi-diagram-2-fill"></i><span>المحافظات والفروع</span><span class="sb-badge">14</span>
            </a>
            <a href="<?php echo $basePath; ?>services/admin/agreements.php" class="sb-link" data-permission="manage_agreements">
              <i class="bi bi-file-earmark-check-fill"></i><span>الاتفاقيات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/admin/finance.php" class="sb-link" data-permission="view_finance">
              <i class="bi bi-pie-chart-fill"></i><span>المالية</span>
            </a>
          </div>
        </div>
      </div>

      <!-- إدارة الحسابات (مدير خدمات المشروعات ومن لديه manage_user_access) -->
      <div class="sb-section" data-permission="manage_user_access">
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbUserAccess" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-person-gear"></i></span>
          <span class="sb-section-label">إدارة الحسابات</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbUserAccess">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/admin/admin-users.php" class="sb-link">
              <i class="bi bi-people-fill"></i><span>المستخدمون</span>
            </a>
            <a href="<?php echo $basePath; ?>services/admin/my-children.php" class="sb-link">
              <i class="bi bi-person-plus-fill"></i><span>إنشاء حسابات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/admin/admin-branches.php" class="sb-link" data-permission="view_branches">
              <i class="bi bi-building"></i><span>المحافظات والفروع</span>
            </a>
          </div>
        </div>
      </div>

      <!-- إدارة النظام -->
      <div class="sb-section" data-access-admin>
        <button class="sb-section-btn" data-bs-toggle="collapse" data-bs-target="#sbSystem" aria-expanded="false">
          <span class="sb-section-icon"><i class="bi bi-gear-wide-connected"></i></span>
          <span class="sb-section-label">إدارة النظام</span>
          <i class="bi bi-chevron-left sb-chevron"></i>
        </button>
        <div class="collapse" id="sbSystem">
          <div class="sb-links">
            <a href="<?php echo $basePath; ?>services/admin/admin-users.php" class="sb-link">
              <i class="bi bi-people-fill"></i><span>المستخدمون</span>
            </a>
            <a href="<?php echo $basePath; ?>services/admin/admin-roles.php" class="sb-link">
              <i class="bi bi-shield-lock-fill"></i><span>الأدوار</span>
            </a>
            <a href="<?php echo $basePath; ?>services/admin/admin-permissions.php" class="sb-link">
              <i class="bi bi-key-fill"></i><span>الصلاحيات</span>
            </a>
            <a href="<?php echo $basePath; ?>services/admin/admin-activity-logs.php" class="sb-link">
              <i class="bi bi-activity"></i><span>سجل النشاط</span>
            </a>
          </div>
        </div>
      </div>

      <div class="sb-divider"></div>

      <a href="<?php echo $basePath; ?>my-profile.php" class="sb-link" style="background:rgba(23,148,123,.06);border:1px solid var(--c-border)">
        <i class="bi bi-person-circle" style="color:var(--c-primary)"></i>
        <span>حسابي الشخصي</span>
      </a>

      <a href="<?php echo $basePath; ?>services/admin/my-children.php" class="sb-link" data-any-role="admin,super_admin,system_admin,general_director,deputy_general_director,deputy_director,branch_manager,branch_officer,training_manager,center_user,project_services_manager" style="background:rgba(23,148,123,.06);border:1px solid var(--c-border)">
        <i class="bi bi-diagram-3-fill" style="color:var(--c-primary)"></i>
        <span>مستخدميَّ</span>
      </a>

      <div class="sb-divider"></div>

      <button type="button" class="sb-link" id="sbLogoutBtn" style="background:rgba(220,53,69,.06);border:1px solid rgba(220,53,69,.15);color:#dc3545;width:100%;margin-top:4px;cursor:pointer">
        <i class="bi bi-box-arrow-right" style="color:#dc3545"></i>
        <span>تسجيل الخروج</span>
      </button>

    </aside>

    <!-- MAIN -->
    <main class="app-main">
