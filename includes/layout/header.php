<?php
$basePath   = isset($basePath) ? $basePath : '';
$activePage = isset($activePage) ? $activePage : '';
require_once __DIR__ . '/paths.php';
require_once __DIR__ . '/../i18n/bootstrap.php';
global $siteLang;
$loginUrl = front_url('login.php');
$registerUrl = front_url('register.php');
$dashboardUrl = front_url('dashboard.php');
$homeUrl = front_url('index.php');
?><header class="site-header adidas-like-header">

  <style>
    /*
      ملاحظة مهمة:
      هذه القواعد للديسكتوب فقط.
      لا يوجد أي تعديل على سلوك الموبايل حتى يبقى مثل الكود القديم تماماً.
    */
    @media (min-width: 1200px) {
      .site-header .mega-menu-item {
        position: static;
      }

      .site-header .mega-menu-item > .mega-menu-panel {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
      }

      .site-header .mega-menu-item.mega-open > .mega-menu-panel {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
      }

      /*
        منع الفتح بالمرور على الماوس حتى لا تغلق وتفتح بشكل مزعج.
        الفتح على الديسكتوب صار بالكبس فقط.
      */
      .site-header .mega-menu-item:hover > .mega-menu-panel {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
      }

      .site-header .mega-menu-item.mega-open:hover > .mega-menu-panel {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
      }

      .site-header .mega-toggle-link {
        cursor: pointer;
      }

      .site-header .entrepreneurship-menu-panel .container {
        display: flex;
        justify-content: center;
      }

      .site-header .entrepreneurship-menu-grid {
        grid-template-columns: repeat(3, minmax(200px, 1fr));
        width: min(100%, 720px);
        max-width: 720px;
        margin-inline: auto;
      }

      .site-header .incubation-menu-panel .container {
        display: flex;
        justify-content: center;
      }

      .site-header .incubation-menu-grid {
        grid-template-columns: repeat(3, minmax(200px, 1fr));
        width: min(100%, 720px);
        max-width: 720px;
        margin-inline: auto;
      }

      .site-header .consulting-menu-panel .container {
        display: flex;
        justify-content: center;
      }

      .site-header .consulting-menu-grid {
        grid-template-columns: repeat(2, minmax(220px, 1fr));
        width: min(100%, 480px);
        max-width: 480px;
        margin-inline: auto;
      }

      .site-header .finance-menu-panel .container {
        display: flex;
        justify-content: center;
      }

      .site-header .finance-menu-grid {
        grid-template-columns: repeat(3, minmax(200px, 1fr));
        width: min(100%, 720px);
        max-width: 720px;
        margin-inline: auto;
      }
    }

  </style>

  <div class="top-strip">
    <div class="container">
      <div class="top-strip-inner">
        <span><?php echo htmlspecialchars(__('brand_name')); ?></span>

        <div class="top-strip-actions">

          <?php include __DIR__ . '/../partials/lang-switch.php'; ?>

          <a
            href="<?php echo front_url('login.php'); ?>"
            id="guestLoginLink"
            class="top-strip-link"
          >
            <?php echo htmlspecialchars(__('login')); ?>
          </a>

          <a
            href="<?php echo front_url('register.php'); ?>"
            id="guestRegisterLink"
            class="top-strip-link"
          >
            <?php echo htmlspecialchars(__('register')); ?>
          </a>

          <a
            href="<?php echo front_url('dashboard.php'); ?>"
            id="dashboardLinkTop"
            class="top-strip-link d-none"
          >
            <?php echo htmlspecialchars(__('dashboard')); ?>
          </a>

          <a
            href="#"
            id="logoutLinkTop"
            class="top-strip-link d-none"
          >
            <?php echo htmlspecialchars(__('logout')); ?>
          </a>
        </div>
      </div>
    </div>
  </div>

  <nav class="navbar navbar-expand-xl main-navbar adidas-navbar">
    <div class="container nav-shell">
      <a class="navbar-brand brand brand-adidas-like" href="<?php echo front_url('index.php#home'); ?>">
        <img
          src="<?php echo $basePath; ?>assets/images/Logo.png"
          alt="شعار الهيئة"
          class="brand-logo-img"
          onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        />
        <div class="brand-logo-fallback" style="display:none;">MR</div>
      </a>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNavbar"
        aria-controls="mainNavbar"
        aria-expanded="false"
        aria-label="<?php echo htmlspecialchars(__('nav_toggle')); ?>"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="nav-lang-slot nav-lang-slot--mobile d-xl-none ms-2">
        <?php $langSwitchLight = true; $langSwitchCompact = true; include __DIR__ . '/../partials/lang-switch.php'; ?>
      </div>

      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav mx-auto main-nav mega-main-nav">

          <li class="nav-item">
            <a
              class="nav-link <?php echo ($activePage === 'home') ? 'active' : ''; ?>"
              href="<?php echo front_url('index.php#home'); ?>"
            >
              <?php echo htmlspecialchars(__('nav_home')); ?>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php echo ($activePage === 'user-guide') ? 'active' : ''; ?>"
               href="<?php echo front_url('user-guide.php'); ?>">
              <?php echo htmlspecialchars(__('nav_guide')); ?>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="<?php echo front_url('index.php#faq'); ?>">
              <?php echo htmlspecialchars(__('nav_faq')); ?>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="<?php echo front_url('index.php#news'); ?>">
              <?php echo htmlspecialchars(__('nav_news')); ?>
            </a>
          </li>

          <!-- رواد الأعمال -->
          <li class="nav-item mega-menu-item">
            <a
              class="nav-link dropdown-toggle mega-toggle-link <?php echo ($activePage === 'entrepreneurship') ? 'active' : ''; ?>"
              href="<?php echo front_url('index.php#entrepreneurs-window'); ?>"
              aria-expanded="false"
            >
              <?php echo htmlspecialchars(__('nav_entrepreneurs')); ?>
            </a>

            <div class="mega-menu-panel entrepreneurship-menu-panel">
              <div class="container">
                <div class="mega-menu-grid entrepreneurship-menu-grid">

                  <div class="mega-col">
                    <h6>بوابة ريادة الأعمال</h6>
                    <a href="<?php echo front_url('index.php#entrepreneurs-window'); ?>">على الصفحة الرئيسية</a>
                    <a href="<?php echo front_url('services/incubation/entrepreneurship-hub.php'); ?>">البوابة الرئيسية</a>
                    <a href="<?php echo front_url('services/incubation/entrepreneur-profile.php'); ?>">استبيان المشروع التقني</a>
                    <a href="<?php echo front_url('services/incubation/incubators.php'); ?>">استعراض الحاضنات</a>
                    <a href="<?php echo front_url('services/incubation/incubation-apply.php'); ?>">التقدّم للاحتضان</a>
                  </div>

                  <div class="mega-col">
                    <h6>حسابي</h6>
                    <a href="<?php echo front_url('services/incubation/my-applications.php'); ?>">طلبات الانضمام</a>
                    <a href="<?php echo front_url('services/incubation/success-stories.php'); ?>">قصص النجاح</a>
                  </div>

                  <div class="mega-col">
                    <h6>الإدارة</h6>
                    <a href="<?php echo front_url('services/admin/entrepreneur-manager-dashboard.php'); ?>">لوحة مدير ريادة الأعمال</a>
                  </div>

                </div>
              </div>
            </div>
          </li>

          <!-- حاضنات الأعمال -->
          <li class="nav-item mega-menu-item">
            <a
              class="nav-link dropdown-toggle mega-toggle-link <?php echo in_array($activePage, ['incubation', 'incubators'], true) ? 'active' : ''; ?>"
              href="<?php echo front_url('index.php#incubators-window'); ?>"
              aria-expanded="false"
            >
              <?php echo htmlspecialchars(__('nav_incubators')); ?>
            </a>

            <div class="mega-menu-panel incubation-menu-panel">
              <div class="container">
                <div class="mega-menu-grid incubation-menu-grid">

                  <div class="mega-col">
                    <h6>اكتشف</h6>
                    <a href="<?php echo front_url('index.php#incubators-window'); ?>">على الصفحة الرئيسية</a>
                    <a href="<?php echo front_url('services/incubation/entrepreneurship-hub.php'); ?>">بوابة ريادة الأعمال</a>
                    <a href="<?php echo front_url('services/incubation/incubators.php'); ?>">استعراض الحاضنات</a>
                    <a href="<?php echo front_url('services/incubation/incubation-apply.php'); ?>">التقدّم للاحتضان</a>
                    <a href="<?php echo front_url('services/incubation/success-stories.php'); ?>">قصص النجاح</a>
                  </div>

                  <div class="mega-col">
                    <h6>حسابي</h6>
                    <a href="<?php echo front_url('services/incubation/my-applications.php'); ?>">طلبات الانضمام</a>
                    <a href="<?php echo front_url('services/incubation/entrepreneur-profile.php'); ?>">سجّل مشروعك</a>
                    <a href="<?php echo front_url('services/incubation/project-mentoring.php'); ?>">الإرشاد والتوجيه</a>
                  </div>

                  <div class="mega-col">
                    <h6>الإدارة</h6>
                    <a href="<?php echo front_url('services/incubation/incubators-list.php'); ?>">إدارة الحاضنات</a>
                    <a href="<?php echo front_url('services/incubation/incubated-projects.php'); ?>">المشاريع المحتضنة</a>
                    <a href="<?php echo front_url('services/incubation/incubation-reports.php'); ?>">تقارير الحضانة</a>
                    <a href="<?php echo front_url('services/admin/incubator-manager-dashboard.php'); ?>">لوحة مدير الحاضنة</a>
                  </div>

                </div>
              </div>
            </div>
          </li>

          <!-- التمويل -->
          <li class="nav-item mega-menu-item">
            <a
              class="nav-link dropdown-toggle mega-toggle-link <?php echo ($activePage === 'finance') ? 'active' : ''; ?>"
              href="<?php echo front_url('index.php#finance-window'); ?>"
              aria-expanded="false"
            >
              <?php echo htmlspecialchars(__('nav_finance')); ?>
            </a>

            <div class="mega-menu-panel finance-menu-panel">
              <div class="container">
                <div class="mega-menu-grid finance-menu-grid">

                  <div class="mega-col">
                    <h6>منظومة التمويل</h6>
                    <a href="<?php echo front_url('index.php#finance-window'); ?>">على الصفحة الرئيسية</a>
                    <a href="<?php echo front_url('services/finance/finance.php'); ?>">البوابة الرئيسية</a>
                    <a href="<?php echo front_url('services/finance/finance-apply.php'); ?>">طلب تمويل جديد</a>
                  </div>

                  <div class="mega-col">
                    <h6>السحابة والمؤشرات</h6>
                    <a href="<?php echo front_url('services/finance/finance-cloud.php'); ?>">سحابة القروض غير الممولة</a>
                    <a href="<?php echo front_url('services/finance/finance-metrics.php'); ?>">مؤشرات التمويل</a>
                    <a href="<?php echo front_url('services/finance/finance-funded.php'); ?>">القروض الممولة</a>
                  </div>

                  <div class="mega-col">
                    <h6>حسابي</h6>
                    <a href="<?php echo front_url('services/finance/finance-apply.php'); ?>">متابعة طلب التمويل</a>
                    <a href="<?php echo front_url('dashboard.php'); ?>">لوحة التحكم</a>
                  </div>

                </div>
              </div>
            </div>
          </li>

          <!-- الاستشارات -->
          <li class="nav-item mega-menu-item">
            <a
              class="nav-link dropdown-toggle mega-toggle-link <?php echo ($activePage === 'consulting') ? 'active' : ''; ?>"
              href="<?php echo front_url('services/consulting/consulting-offices-list.php'); ?>"
              aria-expanded="false"
            >
              <?php echo htmlspecialchars(__('nav_consulting')); ?>
            </a>

            <div class="mega-menu-panel consulting-menu-panel">
              <div class="container">
                <div class="mega-menu-grid consulting-menu-grid">

                  <div class="mega-col">
                    <h6>الخدمة</h6>
                    <a href="<?php echo front_url('services/consulting/consulting-offices-list.php'); ?>">مكاتب الاستشارات</a>
                    <a href="<?php echo front_url('services/consulting/consulting-request-create.php'); ?>">طلب استشارة جديد</a>
                    <a href="<?php echo front_url('services/consulting/consulting-requests-list.php'); ?>">طلبات الاستشارة</a>
                  </div>

                  <div class="mega-col">
                    <h6>الإدارة</h6>
                    <a href="<?php echo front_url('services/consulting/consulting-admin-dashboard.php'); ?>">لوحة تحكم الاستشارات</a>
                    <a href="<?php echo front_url('services/consulting/consulting-office-create.php'); ?>">تسجيل مكتب جديد</a>
                  </div>

                </div>
              </div>
            </div>
          </li>

          <li class="nav-item mega-menu-item">
            <a
              class="nav-link dropdown-toggle mega-toggle-link <?php echo ($activePage === 'services') ? 'active' : ''; ?>"
              href="<?php echo front_url('services/index.php'); ?>"
              aria-expanded="false"
            >
              <?php echo htmlspecialchars(__('nav_services_hub')); ?>
            </a>

            <div class="mega-menu-panel">
              <div class="container">
                <div class="mega-menu-grid">

                  <div class="mega-col">
                    <h6>التدريب والتأهيل</h6>
                    <a href="<?php echo front_url('services/training/training-verification.php'); ?>">التحقق من الشهادات</a>
                    <a href="<?php echo front_url('services/training/signature-verification.php'); ?>">التحقق من التوقيع ESIG</a>
                    <a href="<?php echo front_url('services/training/training-trainees-list.php'); ?>">مجتمع المتدربون</a>
                    <a href="<?php echo front_url('services/training/training-trainers-list.php'); ?>">مجتمع المدربون</a>
                    <a href="<?php echo front_url('services/training/training-kits-list.php'); ?>">الحقائب التدريبية</a>
                    <a href="<?php echo front_url('services/training/training-programs-list.php'); ?>">البرامج التدريبية</a>
                    <a href="<?php echo front_url('services/training/training-centers-list.php'); ?>">المراكز التدريبية</a>
                    <a href="<?php echo front_url('services/training/training-certificates-list.php'); ?>">الشهادات التدريبية</a>
                    <a href="<?php echo front_url('services/training/training-certificates-approve.php'); ?>">اعتماد الشهادات</a>
                    <a href="<?php echo front_url('services/training/my-electronic-signature.php'); ?>">التوقيع الإلكتروني</a>
                    <a href="<?php echo front_url('services/training/center-registration-request.php'); ?>">طلب تسجيل مركز</a>
                    <a href="<?php echo front_url('services/training/trainer-registration-request.php'); ?>">طلب تسجيل مدرب</a>
                    <a href="<?php echo front_url('services/training/trainee-registration-request.php'); ?>">طلب تسجيل متدرب</a>
                    <a href="<?php echo front_url('services/training/course-registration-request.php'); ?>">طلب تسجيل دورة</a>
                    <a href="<?php echo front_url('services/training/registration-requests-review.php'); ?>">مراجعة طلبات التسجيل</a>
                  </div>

                  <div class="mega-col d-none" aria-hidden="true">
                    <h6>التراخيص</h6>
                    <span class="text-muted small">قريباً</span>
                  </div>

                  <div class="mega-col">
                    <h6>الاستشارات</h6>
                    <a href="<?php echo front_url('services/consulting/consulting-requests-list.php'); ?>">طلبات الاستشارة</a>
                    <a href="<?php echo front_url('services/consulting/consulting-request-create.php'); ?>">طلب استشارة جديد</a>
                    <a href="<?php echo front_url('services/consulting/consulting-offices-list.php'); ?>">مكاتب الاستشارات</a>
                  </div>

                  <div class="mega-col">
                    <h6>التمويل وريادة الأعمال</h6>
                    <a href="<?php echo front_url('index.php#finance-window'); ?>">منظومة التمويل</a>
                    <a href="<?php echo front_url('services/finance/finance-apply.php'); ?>">طلب تمويل</a>
                    <a href="<?php echo front_url('index.php#entrepreneurs-window'); ?>">رواد الأعمال</a>
                    <a href="<?php echo front_url('index.php#incubators-window'); ?>">حاضنات الأعمال</a>
                  </div>

                  <div class="mega-col d-none" aria-hidden="true">
                    <h6>البرامج</h6>
                    <span class="text-muted small">قريباً</span>
                  </div>

                  <div class="mega-col d-none" aria-hidden="true">
                    <h6>الدراسات والخبرات</h6>
                    <span class="text-muted small">قريباً</span>
                  </div>

                  <div class="mega-col">
                    <h6>القوى العاملة</h6>
                    <a href="<?php echo front_url('services/workforce/jobs-list.php'); ?>">فرص العمل</a>
                    <a href="<?php echo front_url('services/workforce/job-request.php'); ?>">تقديم طلب عمل</a>
                    <a href="<?php echo front_url('services/workforce/job-post.php'); ?>">إضافة فرصة عمل</a>
                    <a href="<?php echo front_url('services/workforce/candidates-list.php'); ?>">الكفاءات المتاحة</a>
                    <a href="<?php echo front_url('services/workforce/staff-training-request.php'); ?>">تدريب الكوادر</a>
                  </div>

                  <div class="mega-col">
                    <h6>منصة الاحتياجات</h6>
                    <a href="<?php echo front_url('services/gis/needs-map.php'); ?>">خريطة الاحتياجات</a>
                    <a href="<?php echo front_url('services/gis/need-create.php'); ?>" data-permission="needs.create">تسجيل احتياج جديد</a>
                    <a href="<?php echo front_url('services/gis/needs-list.php'); ?>">سجل الاحتياجات</a>
                    <a href="<?php echo front_url('services/gis/needs-dashboard.php'); ?>">لوحة مؤشرات الاحتياجات</a>
                  </div>

                  <div class="mega-col">
                    <h6>المجتمع والدعم</h6>
                    <a href="<?php echo front_url('user-guide.php'); ?>">دليل المستخدم — من A إلى Z</a>
                    <a href="<?php echo front_url('index.php#contact'); ?>">الدعم والتواصل</a>
                  </div>

                </div>
              </div>
            </div>
          </li>

          <li class="nav-item">
            <a
              class="nav-link <?php echo in_array($activePage, ['needs-map', 'need-create', 'needs-list', 'needs-dashboard'], true) ? 'active' : ''; ?>"
              href="<?php echo front_url('services/gis/needs-map.php'); ?>"
            >
              <?php echo htmlspecialchars(__('nav_needs_map')); ?>
            </a>
          </li>

          <li class="nav-item d-none" id="profileNavItem">
            <a
              class="nav-link <?php echo ($activePage === 'profile') ? 'active' : ''; ?>"
              href="<?php echo front_url('my-profile.php'); ?>"
            >
              <?php echo htmlspecialchars(__('nav_my_profile')); ?>
            </a>
          </li>

          <li class="nav-item" id="dashboardNavItem" style="display:none;">
            <a
              class="nav-link <?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>"
              href="<?php echo front_url('dashboard.php'); ?>"
            >
              <?php echo htmlspecialchars(__('dashboard')); ?>
            </a>
          </li>

        </ul>

        <!--
        <div class="header-cta-desktop d-none d-xl-flex" data-permission="needs.create">
          <a
            href="<?php echo front_url('services/gis/needs-map.php'); ?>"
            class="header-book-btn"
            title="<?php echo htmlspecialchars(__t('الانتقال إلى خريطة الاحتياجات وتسجيل احتياج جديد')); ?>"
          >
            <i class="bi bi-geo-alt-fill ms-1"></i>
            <?php echo htmlspecialchars(__('add_need')); ?>
          </a>
        </div>
        -->

        <div class="nav-auth-actions d-flex align-items-center flex-wrap gap-2 ms-xl-2">

          <a
            href="<?php echo front_url('login.php'); ?>"
            id="guestLoginBtn"
            class="soft-btn"
          >
            <?php echo htmlspecialchars(__('login')); ?>
          </a>

          <a
            href="<?php echo front_url('register.php'); ?>"
            id="guestRegisterBtn"
            class="btn btn-outline-secondary d-none d-md-inline-flex"
          >
            <?php echo htmlspecialchars(__('register')); ?>
          </a>

          <a
            href="<?php echo front_url('dashboard.php'); ?>"
            id="dashboardBtn"
            class="soft-btn d-none"
          >
            <?php echo htmlspecialchars(__('dashboard')); ?>
          </a>

          <a
            href="#"
            id="logoutBtn"
            class="btn btn-brand d-none"
          >
            <?php echo htmlspecialchars(__('logout')); ?>
          </a>

          <a
            href="<?php echo front_url('services/gis/needs-map.php'); ?>"
            class="header-book-btn d-xl-none"
                     >
            <i class="bi bi-geo-alt-fill ms-1"></i>
            <?php echo htmlspecialchars(__('add_need')); ?>
          </a>
        </div>
      </div>
    </div>
  </nav>

</header>

<script>
(function () {
  function getToken() {
    try {
      return localStorage.getItem('authority_token') || '';
    } catch (e) {
      return '';
    }
  }

  function isLoggedIn() {
    return !!getToken();
  }

  function isDesktop() {
    return window.matchMedia('(min-width: 1200px)').matches;
  }

  function isDashboardContext() {
    const path = (window.location.pathname || '').toLowerCase();
    return path.endsWith('/dashboard.php')
      || path.includes('/services/admin/')
      || /\/services\/.+-dashboard\.php$/.test(path);
  }

  function shouldUseDashboardMenu(loggedIn) {
    return !!loggedIn && isDashboardContext();
  }

  function showIfExists(id, show, displayValue = '') {
    const el = document.getElementById(id);
    if (!el) return;

    if (show) {
      el.classList.remove('d-none');
      if (displayValue) {
        el.style.display = displayValue;
      } else {
        el.style.removeProperty('display');
      }
    } else {
      el.classList.add('d-none');
      if (displayValue === 'block') {
        el.style.display = 'none';
      }
    }
  }

  function closeAllMegaMenus() {
    const openedMenus = document.querySelectorAll('.site-header .mega-menu-item.mega-open');

    openedMenus.forEach(function (item) {
      item.classList.remove('mega-open');

      const toggle = item.querySelector('.mega-toggle-link');
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function openDashboardMenu() {
    const shellSidebar = document.getElementById('appSidebar');
    const shellOverlay = document.getElementById('sidebarOverlay');
    if (shellSidebar) {
      shellSidebar.classList.add('open');
      if (shellOverlay) shellOverlay.classList.add('open');
      document.body.style.overflow = 'hidden';
      return true;
    }

    const roleSidebar = document.getElementById('dsSidebar');
    const roleOverlay = document.getElementById('dsOverlay');
    if (roleSidebar) {
      roleSidebar.classList.add('open');
      if (roleOverlay) roleOverlay.classList.add('visible');
      document.body.style.overflow = 'hidden';
      return true;
    }

    return false;
  }

  function buildDashboardRedirectUrl() {
    const fallback = '<?php echo $dashboardUrl; ?>';
    const homeUrl = window.AppAuth?.resolveHomePage?.() || fallback;
    const url = new URL(homeUrl, window.location.origin);
    url.searchParams.set('open_sidebar', '1');
    return url.toString();
  }

  function bindDashboardMenuShortcut() {
    const dashboardLinks = [
      document.getElementById('dashboardLinkTop'),
      document.getElementById('dashboardBtn'),
      document.querySelector('#dashboardNavItem a'),
    ].filter(Boolean);

    dashboardLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        const currentPath = window.location.pathname.replace(/\/+$/, '').toLowerCase();
        const targetPath = new URL(link.href, window.location.origin).pathname.replace(/\/+$/, '').toLowerCase();
        if (currentPath !== targetPath) return;
        if (!openDashboardMenu()) return;
        e.preventDefault();
        e.stopPropagation();
      });
    });
  }

  function applyDashboardOnlyNavigation(loggedIn) {
    if (!shouldUseDashboardMenu(loggedIn)) return;

    closeAllMegaMenus();

    const mainNav = document.querySelector('.site-header .main-nav');
    if (mainNav) {
      mainNav.querySelectorAll(':scope > .nav-item').forEach(function (item) {
        const keep = item.id === 'dashboardNavItem' || item.id === 'profileNavItem';
        if (keep) {
          item.classList.remove('d-none');
          item.style.display = 'block';
        } else {
          item.classList.add('d-none');
          item.style.display = 'none';
        }
      });
    }

    // داخل صفحات الداشبورد نلغي إظهار CTA العامة الخاصة بالموقع.
    document.querySelectorAll('.header-cta-desktop, .header-book-btn').forEach(function (el) {
      el.style.display = 'none';
    });
  }

  function bindMobileDashboardMenuOverride() {
    const toggler = document.querySelector('.site-header .navbar-toggler');
    if (!toggler) return;

    // للحسابات الإدارية: نفصل الزر عن Bootstrap collapse نهائياً
    // حتى لا تظهر منيو الهيدر المختصرة قبل فتح/التوجيه للسايدبار.
    if (shouldUseDashboardMenu(isLoggedIn())) {
      toggler.removeAttribute('data-bs-toggle');
      toggler.removeAttribute('data-bs-target');
    }

    toggler.addEventListener('click', function (e) {
      if (!shouldUseDashboardMenu(isLoggedIn())) return;

      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      if (openDashboardMenu()) {
        return;
      }

      window.location.assign(buildDashboardRedirectUrl());
    }, true);
  }

  const loggedIn = isLoggedIn();

  // زائر: تسجيل دخول + إنشاء حساب
  showIfExists('guestLoginLink', !loggedIn);
  showIfExists('guestLoginBtn', !loggedIn);
  showIfExists('guestRegisterLink', !loggedIn);
  showIfExists('guestRegisterBtn', !loggedIn);

  // مسجل: لوحة التحكم + تسجيل خروج
  showIfExists('dashboardLinkTop', loggedIn);
  showIfExists('dashboardBtn', loggedIn);
  showIfExists('logoutLinkTop', loggedIn);
  showIfExists('logoutBtn', loggedIn);
  showIfExists('dashboardNavItem', loggedIn, 'block');
  showIfExists('profileNavItem', loggedIn, 'block');
  bindDashboardMenuShortcut();
  applyDashboardOnlyNavigation(loggedIn);
  bindMobileDashboardMenuOverride();

  // زر "إضافة احتياج" — يظهر للمسجلين فقط
  document.querySelectorAll('.header-cta-desktop, .header-book-btn').forEach(function(el) {
    el.style.display = (loggedIn && !shouldUseDashboardMenu(loggedIn)) ? '' : 'none';
  });

  if (window.AppAccess && typeof window.AppAccess.toggleByPermission === 'function') {
    window.AppAccess.toggleByPermission(document.querySelector('.site-header') || document);
  }

  /*
    الديسكتوب فقط:
    فتح الميغا منيو بالكبس وتبقى مفتوحة.
    الموبايل لا نلمسه نهائياً حتى يبقى مثل الكود القديم.
  */
  const megaToggleLinks = document.querySelectorAll('.site-header .mega-toggle-link');

  megaToggleLinks.forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      if (!isDesktop()) {
        return;
      }

      e.preventDefault();
      e.stopPropagation();

      const currentItem = toggle.closest('.mega-menu-item');
      if (!currentItem) return;

      const isOpen = currentItem.classList.contains('mega-open');

      closeAllMegaMenus();

      if (!isOpen) {
        currentItem.classList.add('mega-open');
        toggle.setAttribute('aria-expanded', 'true');
      }
    });
  });

  /*
    الديسكتوب فقط:
    الضغط داخل الميغا منيو لا يغلقها.
  */
  const megaPanels = document.querySelectorAll('.site-header .mega-menu-panel');

  megaPanels.forEach(function (panel) {
    panel.addEventListener('click', function (e) {
      if (isDesktop()) {
        e.stopPropagation();
      }
    });
  });

  /*
    الديسكتوب فقط:
    تغلق الميغا منيو عند الضغط خارج الهيدر.
  */
  document.addEventListener('click', function (e) {
    if (!isDesktop()) {
      return;
    }

    const header = document.querySelector('.site-header');
    if (!header) return;

    if (!header.contains(e.target)) {
      closeAllMegaMenus();
    }
  });

  /*
    زر Escape يغلق الميغا منيو.
  */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeAllMegaMenus();
    }
  });

  /*
    عند تغيير قياس الشاشة ننظف حالة الديسكتوب فقط.
    لا نغلق قائمة الموبايل ولا نتدخل بسلوك Bootstrap.
  */
  window.addEventListener('resize', function () {
    if (isDesktop()) {
      closeAllMegaMenus();
    }
  });

  const logoutHandlers = ['logoutBtn', 'logoutLinkTop'];
  logoutHandlers.forEach(function (id) {
    const el = document.getElementById(id);
    if (!el) return;

    el.addEventListener('click', async function (e) {
      e.preventDefault();

      if (window.AppAuth && typeof window.AppAuth.logout === 'function') {
        await window.AppAuth.logout();
      } else {
        try {
          localStorage.removeItem('authority_token');
          localStorage.removeItem('authority_user');
        } catch (err) {}
        window.location.href = '<?php echo $loginUrl; ?>';
      }
    });
  });
})();
</script>