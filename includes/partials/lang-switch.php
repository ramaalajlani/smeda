<?php
global $siteLang;
if (!function_exists('site_lang_url')) {
    require_once __DIR__ . '/../i18n/lang.php';
}
if (!function_exists('__')) {
    require_once __DIR__ . '/../i18n/bootstrap.php';
}
$currentLang = $siteLang ?? site_resolve_lang();
$isLight = !empty($langSwitchLight);
$isCompact = !empty($langSwitchCompact);
$dropdownId = 'langDropdown_' . substr(md5(uniqid('', true)), 0, 8);
$currentLabel = $currentLang === 'en' ? __('lang_en') : __('lang_ar');
?>
<div class="dropdown lang-dropdown<?= $isLight ? ' lang-dropdown--light' : '' ?><?= $isCompact ? ' lang-dropdown--compact' : '' ?>">
  <button
    class="btn lang-dropdown-toggle dropdown-toggle"
    type="button"
    id="<?= htmlspecialchars($dropdownId) ?>"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    aria-label="<?= htmlspecialchars(__('lang_select')) ?>"
  >
    <i class="bi bi-globe2" aria-hidden="true"></i>
    <span class="lang-dropdown-label"><?= htmlspecialchars($currentLabel) ?></span>
  </button>
  <ul class="dropdown-menu lang-dropdown-menu" aria-labelledby="<?= htmlspecialchars($dropdownId) ?>">
    <li>
      <a
        class="dropdown-item lang-dropdown-item<?= $currentLang === 'ar' ? ' active' : '' ?>"
        href="<?= htmlspecialchars(site_lang_url('ar')) ?>"
        data-lang="ar"
        hreflang="ar"
      >العربية</a>
    </li>
    <li>
      <a
        class="dropdown-item lang-dropdown-item<?= $currentLang === 'en' ? ' active' : '' ?>"
        href="<?= htmlspecialchars(site_lang_url('en')) ?>"
        data-lang="en"
        hreflang="en"
      >English</a>
    </li>
  </ul>
</div>
