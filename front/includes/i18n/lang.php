<?php



if (!function_exists('site_resolve_lang')) {

    function site_resolve_lang(): string

    {

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'], true)) {

            $lang = $_GET['lang'];

            if (!headers_sent()) {

                setcookie('site_lang', $lang, time() + 86400 * 365, '/', '', false, false);

            }

            return $lang;

        }



        $cookie = $_COOKIE['site_lang'] ?? null;

        if (is_string($cookie) && in_array($cookie, ['ar', 'en'], true)) {

            return $cookie;

        }



        return 'ar';

    }

}



if (!function_exists('site_lang_url')) {

    function site_lang_url(string $lang): string

    {

        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $parts = parse_url($uri);

        $path = $parts['path'] ?? '/';

        $qs = [];

        if (!empty($parts['query'])) {

            parse_str($parts['query'], $qs);

        }

        $qs['lang'] = $lang;

        $query = http_build_query($qs);

        return $query === '' ? $path : $path . '?' . $query;

    }

}



if (!function_exists('site_t')) {

    function site_t(array $dict, string $key, ?string $lang = null): string

    {

        $lang = $lang ?? site_resolve_lang();

        if (isset($dict[$key][$lang])) {

            return $dict[$key][$lang];

        }

        return $dict[$key]['ar'] ?? $key;

    }

}


