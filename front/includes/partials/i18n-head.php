<?php

require_once __DIR__ . '/../i18n/bootstrap.php';

global $siteLang, $siteIsRtl;

$bp = isset($basePath) ? $basePath : '';

$flat = site_i18n_flat();
$arToEn = ($siteLang === 'en') ? site_load_ar_to_en_map() : [];

?>

<script>

(function () {

  var KEY = 'site_lang';

  var VALID = { ar: 1, en: 1 };

  var cookieMatch = document.cookie.match(/(?:^|;\s*)site_lang=([^;]+)/);

  var cookieLang = cookieMatch ? decodeURIComponent(cookieMatch[1]) : null;

  var stored = null;

  try { stored = localStorage.getItem(KEY); } catch (e) {}

  var urlLang = null;

  try {

    urlLang = new URL(window.location.href).searchParams.get('lang');

  } catch (e2) {}

  if (urlLang && VALID[urlLang]) {

    try { localStorage.setItem(KEY, urlLang); } catch (e3) {}

    return;

  }

  if (stored && VALID[stored] && stored !== cookieLang && !window.location.search.includes('lang=')) {

    var u = new URL(window.location.href);

    u.searchParams.set('lang', stored);

    window.location.replace(u.toString());

  } else if (cookieLang && VALID[cookieLang] && !stored) {

    try { localStorage.setItem(KEY, cookieLang); } catch (e4) {}

  }

})();

</script>

<script>

window.__SITE_LANG = <?= json_encode($siteLang, JSON_UNESCAPED_UNICODE) ?>;

window.__SITE_DIR = <?= json_encode($siteIsRtl ? 'rtl' : 'ltr', JSON_UNESCAPED_UNICODE) ?>;

window.__SITE_I18N = <?= json_encode($flat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

window.__SITE_AR_TO_EN = <?= json_encode($arToEn, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

</script>

