<?php
if (!function_exists('front_url')) {
  require_once __DIR__ . '/paths.php';
}
require_once __DIR__ . '/../i18n/bootstrap.php';
?><footer class="site-footer">
  <div class="container">
    <div class="row g-4 align-items-start">

      <!-- LOGO + BRAND -->
      <div class="col-lg-4">
        <div class="footer-brand">
          <div class="footer-logo-wrap">
            <img
              src="<?php echo isset($basePath) ? $basePath : ''; ?>assets/images/logo.png"
              alt="شعار الهيئة"
              class="footer-logo-img"
              onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            />
            <div class="brand-logo-fallback footer-logo-fallback" style="display: none;">
              SME
            </div>
          </div>

          <div class="footer-brand-text">
  <strong><?php echo htmlspecialchars(__('brand_name')); ?></strong>
<span><?php echo $siteLang === 'en' ? 'SMEDC — Official digital platform' : 'Small and Medium Enterprises Development Commission'; ?></span>
          </div>
        </div>

        <div class="social-links mt-4">
          <a href="https://www.facebook.com/profile.php?id=100064510910481" target="_blank" rel="noopener">
            <i class="bi bi-facebook"></i>
          </a>
          <a href="#"><i class="bi bi-instagram"></i></a>
          <a href="#"><i class="bi bi-twitter-x"></i></a>
          <?php $langSwitchLight = true; include __DIR__ . '/../partials/lang-switch.php'; ?>
        </div>
      </div>

      <div class="col-sm-6 col-lg-2">
        <h4 class="footer-title"><?php echo htmlspecialchars(__('footer_quick_links')); ?></h4>
        <ul class="footer-links">
          <li><a href="<?php echo front_url('index.php#faq'); ?>"><?php echo htmlspecialchars(__('footer_faq')); ?></a></li>
          <li><a href="<?php echo front_url('user-guide.php'); ?>"><?php echo htmlspecialchars(__('footer_guide')); ?></a></li>
          <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php#home"><?php echo htmlspecialchars(__('footer_home')); ?></a></li>
          <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php#news"><?php echo htmlspecialchars(__('footer_news')); ?></a></li>
          <li><a href="<?php echo front_url('login.php'); ?>"><?php echo htmlspecialchars(__('login')); ?></a></li>
          <li><a href="<?php echo front_url('register.php'); ?>"><?php echo htmlspecialchars(__('register')); ?></a></li>
          <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php#contact"><?php echo htmlspecialchars(__('footer_contact_us')); ?></a></li>
        </ul>
      </div>

      <div class="col-sm-6 col-lg-3">
        <h4 class="footer-title"><?php echo htmlspecialchars(__('footer_services')); ?></h4>
        <ul class="footer-links">
          <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>services/index.php"><?php echo htmlspecialchars(__('footer_services_hub')); ?></a></li>
          <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php#finance-window"><?php echo htmlspecialchars(__('footer_finance')); ?></a></li>
          <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php#incubators-window"><?php echo htmlspecialchars(__('footer_incubators')); ?></a></li>
          <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php#entrepreneurs-window"><?php echo htmlspecialchars(__('footer_entrepreneurs')); ?></a></li>
        </ul>
      </div>

      <div class="col-lg-3">
        <h4 class="footer-title"><?php echo htmlspecialchars(__('footer_contact')); ?></h4>
        <ul class="footer-contact">

          <li>
            <i class="bi bi-telephone-fill"></i>
            <span>+963 60701377</span>
          </li>

          <li>
            <i class="bi bi-envelope-fill"></i>
            <span>acu-md@mail.sy</span>
          </li>

          <li>
            <i class="bi bi-globe"></i>
            <span>
              <a href="http://www.sme.gov.sy" target="_blank" style="color: inherit;">
                www.smedc.gov.sy
              </a>
            </span>
          </li>

          <li>
            <i class="bi bi-geo-alt-fill"></i>
            <span>دمشق - منطقة أوتستراد بناء الإسكان</span>
          </li>

        </ul>
      </div>

    </div>
  </div>

  <div class="footer-bottom">
    <?php echo htmlspecialchars(__('footer_rights')); ?> — 2026
  </div>
</footer>

<!-- Bootstrap JS Bundle (ضروري لتفعيل القوائم المنسدلة والـ Collapse) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>