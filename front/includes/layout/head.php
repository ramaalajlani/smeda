<?php
require_once __DIR__ . '/../i18n/bootstrap.php';
global $siteLang, $siteIsRtl, $siteHtmlDir;
$bp = isset($basePath) ? $basePath : '';
$bootstrapCss = $siteIsRtl
    ? 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css'
    : 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';
?>
<meta charset="UTF-8" />
<?php
require_once __DIR__ . '/paths.php';
$frontBaseUrl = resolve_front_base_url();
$apiBaseUrl = resolve_api_base_url();
$backendBaseUrl = resolve_backend_base_url();
?>
<meta name="frontend-base-url" content="<?php echo htmlspecialchars($frontBaseUrl, ENT_QUOTES, 'UTF-8'); ?>" />
<meta name="api-base-url" content="<?php echo htmlspecialchars($apiBaseUrl, ENT_QUOTES, 'UTF-8'); ?>" />
<meta name="backend-base-url" content="<?php echo htmlspecialchars($backendBaseUrl, ENT_QUOTES, 'UTF-8'); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
  <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : __('brand_name'); ?>
</title>

<meta name="description" content="<?php echo $siteLang === 'en'
    ? 'Official digital platform of SMEDC — training, finance, incubation, and entrepreneurship support'
    : 'المنصة الرسمية لهيئة تنمية المشروعات الصغيرة والمتوسطة - خدمات، تدريب، دعم، وتمكين رواد الأعمال'; ?>" />
<meta name="keywords" content="SMEDC, SME, entrepreneurship, training, finance, Syria" />
<meta name="author" content="SMEDC" />

<meta property="og:title" content="<?php echo htmlspecialchars(__('brand_name'), ENT_QUOTES, 'UTF-8'); ?>" />
<meta property="og:description" content="<?php echo $siteLang === 'en'
    ? 'Digital platform empowering SMEs and entrepreneurs'
    : 'منصة رقمية لدعم وتمكين رواد الأعمال والمشاريع الصغيرة والمتوسطة'; ?>" />
<meta property="og:image" content="<?php echo $bp; ?>assets/images/cover.png" />
<meta property="og:type" content="website" />
<meta property="og:locale" content="<?php echo $siteLang === 'en' ? 'en_US' : 'ar_AR'; ?>" />

<meta name="theme-color" content="#062824" />

<link rel="icon" type="image/png" href="<?php echo $bp; ?>assets/images/favicon.png" />
<link rel="apple-touch-icon" href="<?php echo $bp; ?>assets/images/favicon.png" />

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet" />

<link rel="stylesheet" href="<?php echo $bootstrapCss; ?>" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

<style>
  :root {
    --font-heading: "IBM Plex Sans Arabic", "IBM Plex Sans", sans-serif;
    --font-body: "IBM Plex Sans Arabic", "IBM Plex Sans", sans-serif;
    --c-dark: #062824;
    --c-deep: #0F4F47;
    --c-primary: #17947B;
    --c-accent: #06AA89;
    --c-soft: #f2fbf8;
  }

  html { font-size: 16px; scroll-behavior: smooth; }
  html, body { overflow-x: hidden; }

  body {
    direction: <?php echo $siteIsRtl ? 'rtl' : 'ltr'; ?>;
    text-align: start;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
  }
</style>

<?php include __DIR__ . '/../partials/i18n-head.php'; ?>

<link rel="stylesheet" href="<?php echo $bp; ?>assets/css/style.css?v=1.5" />
<link rel="stylesheet" href="<?php echo $bp; ?>assets/css/i18n.css?v=1.0" />
<link rel="stylesheet" href="<?php echo $bp; ?>assets/css/global-motion.css?v=1.1" />

<?php /* مزامنة كوكي قوقعة الدور قبل الرسم — يمنع تبدّل السايدبار بين الداشبورد وصفحات الخدمات */ ?>
<script>
(function () {
  var KEY = 'authority_shell_role';
  var DASH = [
    'project_services_manager', 'training_manager', 'branch_manager',
    'incubator_manager', 'media_manager', 'entrepreneur_manager', 'general_director'
  ];
  try {
    var raw = localStorage.getItem('authority_user') || localStorage.getItem('authority_api_user') || '{}';
    var u = JSON.parse(raw);
    var roles = (u.roles || []).map(function (r) {
      return typeof r === 'string' ? r : ((r && r.name) || '');
    }).filter(Boolean);
    var found = null;
    for (var i = 0; i < DASH.length; i++) {
      if (roles.indexOf(DASH[i]) >= 0) { found = DASH[i]; break; }
    }
    var m = document.cookie.match(new RegExp('(?:^|; )' + KEY + '=([^;]*)'));
    var cur = m ? decodeURIComponent(m[1]) : '';
    var next = found || '';
    if (next === cur) {
      try { sessionStorage.removeItem('_shell_sync'); } catch (e) {}
      return;
    }
    if (next) {
      document.cookie = KEY + '=' + encodeURIComponent(next) + ';path=/;max-age=2592000;SameSite=Lax';
    } else {
      document.cookie = KEY + '=;path=/;max-age=0;SameSite=Lax';
    }
    try {
      if (!sessionStorage.getItem('_shell_sync')) {
        sessionStorage.setItem('_shell_sync', '1');
        location.reload();
      } else {
        sessionStorage.removeItem('_shell_sync');
      }
    } catch (e) {}
  } catch (e) {}
})();
</script>
