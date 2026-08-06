<?php



require_once __DIR__ . '/lang.php';



if (!function_exists('site_load_translations')) {

    function site_load_translations(): array

    {

        static $cache = null;

        if ($cache !== null) {

            return $cache;

        }



        $merged = [];

        $jsonDir = dirname(__DIR__, 2) . '/assets/i18n';



        foreach (['ar', 'en'] as $lang) {

            $path = $jsonDir . '/' . $lang . '.json';

            if (!is_file($path)) {

                continue;

            }

            $flat = json_decode((string) file_get_contents($path), true);

            if (!is_array($flat)) {

                continue;

            }

            foreach ($flat as $key => $value) {

                if (is_string($value)) {

                    $merged[$key][$lang] = $value;

                }

            }

        }



        if ($merged === []) {
            $phpI18nDir = __DIR__;
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($phpI18nDir));
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                if (in_array($file->getFilename(), ['bootstrap.php', 'lang.php', 'by-ar.php'], true)) {
                    continue;
                }
                $data = include $file->getPathname();
                if (!is_array($data)) {
                    continue;
                }
                foreach ($data as $key => $vals) {
                    if (is_array($vals)) {
                        $merged[$key] = array_merge($merged[$key] ?? [], $vals);
                    }
                }
            }
        } else {
            $phpI18nDir = __DIR__;
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($phpI18nDir));
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                if (in_array($file->getFilename(), ['bootstrap.php', 'lang.php', 'by-ar.php'], true)) {
                    continue;
                }
                $data = include $file->getPathname();
                if (!is_array($data)) {
                    continue;
                }
                foreach ($data as $key => $vals) {
                    if (is_array($vals) && !isset($merged[$key])) {
                        $merged[$key] = $vals;
                    }
                }
            }
        }



        $cache = $merged;

        return $cache;

    }

}



if (!function_exists('site_load_ar_to_en_map')) {
    /** @return array<string, string> */
    function site_load_ar_to_en_map(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $map = [];
        foreach (site_load_translations() as $vals) {
            if (isset($vals['ar'], $vals['en']) && $vals['ar'] !== $vals['en']) {
                $map[trim($vals['ar'])] = trim($vals['en']);
            }
        }
        $byArFile = __DIR__ . '/by-ar.php';
        if (is_file($byArFile)) {
            $extra = include $byArFile;
            if (is_array($extra)) {
                foreach ($extra as $ar => $en) {
                    if (is_string($ar) && is_string($en) && $ar !== '' && $en !== '') {
                        $map[$ar] = $en;
                    }
                }
            }
        }
        uksort($map, static fn($a, $b) => mb_strlen($b) - mb_strlen($a));
        $cache = $map;
        return $cache;
    }
}



if (!function_exists('site_translate_buffer')) {
    function site_translate_buffer(string $html): string
    {
        global $siteLang;
        if ($siteLang === 'ar') {
            return $html;
        }
        $map = site_load_ar_to_en_map();
        foreach ($map as $ar => $en) {
            if ($ar === '' || $en === '' || $ar === $en) {
                continue;
            }
            $html = str_replace($ar, $en, $html);
        }
        return $html;
    }
}



if (!function_exists('site_merge_page_i18n')) {

    /** @param array<string, array{ar?: string, en?: string}> $extra */

    function site_merge_page_i18n(array $extra): void

    {

        global $siteTranslations;

        $siteTranslations = array_merge($siteTranslations, $extra);

    }

}



if (!defined('SITE_I18N_BOOTSTRAPPED')) {

    define('SITE_I18N_BOOTSTRAPPED', true);



    $siteLang = site_resolve_lang();

    $siteIsRtl = $siteLang === 'ar';

    $siteHtmlDir = $siteIsRtl ? 'rtl' : 'ltr';

    $siteTranslations = site_load_translations();



    if (!function_exists('__')) {

        function __(string $key, ?string $fallback = null): string

        {

            global $siteTranslations, $siteLang;

            if (isset($siteTranslations[$key][$siteLang])) {

                return $siteTranslations[$key][$siteLang];

            }

            if ($fallback !== null) {

                return $fallback;

            }

            if (isset($siteTranslations[$key]['ar'])) {

                return $siteTranslations[$key]['ar'];

            }

            return $key;

        }

    }



    if (!function_exists('t')) {

        function t(string $key, ?string $fallback = null): string

        {

            return __($key, $fallback);

        }

    }



    if (!function_exists('__t')) {
        function __t(string $arabic): string
        {
            global $siteLang;
            if ($siteLang === 'ar') {
                return $arabic;
            }
            $map = site_load_ar_to_en_map();
            return $map[$arabic] ?? $arabic;
        }
    }



    if (!function_exists('getCurrentLanguage')) {

        function getCurrentLanguage(): string

        {

            global $siteLang;

            return $siteLang;

        }

    }



    if (!function_exists('applyDirection')) {

        function applyDirection(?string $lang = null): array

        {

            global $siteLang, $siteIsRtl, $siteHtmlDir;

            if ($lang !== null && in_array($lang, ['ar', 'en'], true)) {

                $siteLang = $lang;

            }

            $siteIsRtl = $siteLang === 'ar';

            $siteHtmlDir = $siteIsRtl ? 'rtl' : 'ltr';

            return ['lang' => $siteLang, 'dir' => $siteHtmlDir, 'rtl' => $siteIsRtl];

        }

    }



    if (!function_exists('setLanguage')) {

        function setLanguage(string $lang): never

        {

            if (!in_array($lang, ['ar', 'en'], true)) {

                $lang = 'ar';

            }

            if (!headers_sent()) {

                setcookie('site_lang', $lang, time() + 86400 * 365, '/');

            }

            header('Location: ' . site_lang_url($lang));

            exit;

        }

    }



    if (!function_exists('site_html_attrs')) {

        function site_html_attrs(): string

        {

            global $siteLang, $siteHtmlDir;

            return 'lang="' . htmlspecialchars($siteLang, ENT_QUOTES, 'UTF-8') . '" dir="' . htmlspecialchars($siteHtmlDir, ENT_QUOTES, 'UTF-8') . '"';

        }

    }



    if (!function_exists('site_i18n_flat')) {

        /** @return array<string, string> */

        function site_i18n_flat(?string $lang = null): array

        {

            global $siteTranslations, $siteLang;

            $lang = $lang ?? $siteLang;

            $out = [];

            foreach ($siteTranslations as $key => $vals) {

                if (isset($vals[$lang])) {

                    $out[$key] = $vals[$lang];

                } elseif (isset($vals['ar'])) {

                    $out[$key] = $vals['ar'];

                }

            }

            return $out;

        }

    }

    if (isset($_GET['set_lang']) && in_array($_GET['set_lang'], ['ar', 'en'], true)) {
        setLanguage($_GET['set_lang']);
    }

    if ($siteLang === 'en' && !headers_sent()) {
        ob_start('site_translate_buffer');
    }
}


