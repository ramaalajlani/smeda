/**

 * Site-wide i18n helpers (PHP renders strings; JS syncs language persistence).

 */

(function (global) {

  'use strict';



  var STORAGE_KEY = 'site_lang';

  var VALID = { ar: true, en: true };



  function getCurrentLanguage() {

    if (global.__SITE_LANG && VALID[global.__SITE_LANG]) {

      return global.__SITE_LANG;

    }

    try {

      var stored = localStorage.getItem(STORAGE_KEY);

      if (stored && VALID[stored]) return stored;

    } catch (e) {}

    return 'ar';

  }



  function applyDirection(lang) {

    lang = VALID[lang] ? lang : 'ar';

    var dir = lang === 'ar' ? 'rtl' : 'ltr';

    var html = document.documentElement;

    html.setAttribute('lang', lang);

    html.setAttribute('dir', dir);

    document.body && document.body.setAttribute('dir', dir);

    global.__SITE_LANG = lang;

    global.__SITE_DIR = dir;

    return { lang: lang, dir: dir };

  }



  function buildLangUrl(lang) {

    try {

      var u = new URL(window.location.href);

      u.searchParams.set('lang', lang);

      return u.toString();

    } catch (e) {

      return window.location.pathname + '?lang=' + lang;

    }

  }



  function setLanguage(lang) {

    if (!VALID[lang]) return;

    try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) {}

    document.cookie = STORAGE_KEY + '=' + lang + ';path=/;max-age=' + (86400 * 365) + ';SameSite=Lax';

    window.location.href = buildLangUrl(lang);

  }



  function t(key, fallback) {

    if (global.__SITE_I18N && global.__SITE_I18N[key] != null) {

      return global.__SITE_I18N[key];

    }

    return fallback != null ? fallback : key;

  }



  function applyDataI18n(root) {

    var scope = root || document;

    scope.querySelectorAll('[data-i18n]').forEach(function (el) {

      var key = el.getAttribute('data-i18n');

      if (!key) return;

      var val = t(key);

      if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {

        if (el.hasAttribute('placeholder')) el.placeholder = val;

      } else {

        el.textContent = val;

      }

    });

    scope.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {

      el.placeholder = t(el.getAttribute('data-i18n-placeholder'));

    });

    scope.querySelectorAll('[data-i18n-title]').forEach(function (el) {

      el.title = t(el.getAttribute('data-i18n-title'));

    });

  }



  function bindLangDropdowns() {

    document.querySelectorAll('.lang-dropdown-item[data-lang]').forEach(function (link) {

      link.addEventListener('click', function (ev) {

        var lang = link.getAttribute('data-lang');

        if (!lang || !VALID[lang]) return;

        if (getCurrentLanguage() === lang) {

          ev.preventDefault();

          return;

        }

        ev.preventDefault();

        setLanguage(lang);

      });

    });

  }



  function ta(ar, fallback) {
    if (getCurrentLanguage() === 'ar') {
      return ar;
    }
    if (global.__SITE_AR_TO_EN && global.__SITE_AR_TO_EN[ar] != null) {
      return global.__SITE_AR_TO_EN[ar];
    }
    return fallback != null ? fallback : ar;
  }

  function init() {

    applyDirection(getCurrentLanguage());

    applyDataI18n(document);

    bindLangDropdowns();

  }



  global.SiteI18n = {

    getCurrentLanguage: getCurrentLanguage,

    setLanguage: setLanguage,

    applyDirection: applyDirection,

    t: t,

    ta: ta,

    applyDataI18n: applyDataI18n,

  };



  if (document.readyState === 'loading') {

    document.addEventListener('DOMContentLoaded', init);

  } else {

    init();

  }

})(window);

