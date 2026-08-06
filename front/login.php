<?php
require_once __DIR__ . '/includes/i18n/bootstrap.php';
require_once __DIR__ . '/includes/layout/paths.php';
$basePath = '';
$pageTitle = __('auth_login_title');
global $siteLang;
?>
<?php include __DIR__ . '/includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/includes/layout/head.php'; ?>
  <link href="<?php echo $basePath; ?>assets/css/auth-pages.css?v=1.1" rel="stylesheet">
</head>

<body class="auth-page">

  <div class="auth-card auth-card--narrow">
    <div class="d-flex justify-content-end mb-2">
      <?php $langSwitchLight = true; include __DIR__ . '/includes/partials/lang-switch.php'; ?>
    </div>

    <h1 class="auth-title"><?php echo htmlspecialchars(__('auth_login_title')); ?></h1>
    <p class="auth-subtitle"><?php echo htmlspecialchars(__('auth_login_sub')); ?></p>

    <div id="loginMessage" class="login-message d-none"></div>

    <form id="loginForm" novalidate>
      <div class="mb-3">
        <label class="form-label fw-bold" for="emailInput"><?php echo htmlspecialchars(__('auth_email')); ?></label>
        <input type="email" id="emailInput" class="form-control" placeholder="name@example.com" autocomplete="username">
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold" for="passwordInput"><?php echo htmlspecialchars(__('auth_password')); ?></label>
        <input type="password" id="passwordInput" class="form-control" placeholder="********" autocomplete="current-password">
      </div>


      <div class="auth-actions">
        <button type="submit" id="loginBtn" class="btn btn-brand w-100">
          <?php echo htmlspecialchars(__('auth_submit_login')); ?>
        </button>
        <a href="<?php echo front_url('index.php'); ?>" class="btn btn-outline-brand w-100">
          <?php echo htmlspecialchars(__('auth_back_home')); ?>
        </a>
      </div>
    </form>

    <div class="auth-footer-links">
      <?php echo htmlspecialchars(__('auth_no_account')); ?>
      <a href="<?php echo front_url('register.php'); ?>"><?php echo htmlspecialchars(__('register')); ?></a>
    </div>
  </div>

  <script src="<?php echo $basePath; ?>assets/js/core/config.js?v=2.0"></script>
  <script src="<?php echo $basePath; ?>assets/js/core/routes.js?v=2.0"></script>
  <script src="<?php echo $basePath; ?>assets/js/core/api.js?v=2.1"></script>
  <script src="<?php echo $basePath; ?>assets/js/core/auth.js?v=3.0"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo $basePath; ?>assets/js/core/i18n.js?v=1.0"></script>
  <script src="<?php echo $basePath; ?>assets/js/pages/login.js?v=2.1"></script>
</body>
</html>
