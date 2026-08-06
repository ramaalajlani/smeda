<?php
/**
 * Extract unique Arabic strings from front PHP/JS files.
 * php front/tools/i18n-extract.php
 */
$root = dirname(__DIR__);
$strings = [];

$skipDirs = ['vendor', 'node_modules', 'assets/i18n', 'tools'];

function walk(string $dir, array &$strings, array $skipDirs): void
{
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $entry;
        foreach ($skipDirs as $skip) {
            if (str_contains(str_replace('\\', '/', $path), $skip)) {
                continue 2;
            }
        }
        if (is_dir($path)) {
            walk($path, $strings, $skipDirs);
            continue;
        }
        if (!preg_match('/\.(php|js)$/i', $entry)) {
            continue;
        }
        if (str_contains($path, 'includes' . DIRECTORY_SEPARATOR . 'i18n')) {
            continue;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }
        // Skip lines already using __(
        preg_match_all('/[\x{0600}-\x{06FF}][\x{0600}-\x{06FF}\s\d\.,؛:!\?\-\(\)«»\/\[\]@#%\+="' . "'" . ']{1,500}/u', $content, $m);
        foreach ($m[0] as $raw) {
            $s = trim(preg_replace('/\s+/u', ' ', $raw));
            if (mb_strlen($s) < 2) {
                continue;
            }
            if (preg_match('/^[\d\s\.,]+$/u', $s)) {
                continue;
            }
            if (str_contains($s, '__(') || str_contains($s, 'SiteI18n')) {
                continue;
            }
            $strings[$s] = true;
        }
    }
}

walk($root, $strings, $skipDirs);
$list = array_keys($strings);
sort($list, SORT_STRING);

echo count($list) . " unique Arabic strings\n";
file_put_contents($root . '/tools/i18n-extracted-strings.json', json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Written tools/i18n-extracted-strings.json\n";
