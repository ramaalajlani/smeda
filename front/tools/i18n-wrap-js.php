<?php
/**
 * @deprecated Broken — do not run. Use i18n-fix-js-corruption.php to repair instead.
 * Wrap Arabic string literals in JS files with SiteI18n.ta() — manual review required. * php front/tools/i18n-wrap-js.php
 */
fwrite(STDERR, "ERROR: i18n-wrap-js.php is disabled (corrupts template literals).\n");
exit(1);

$root = dirname(__DIR__);
$jsDir = $root . '/assets/js';
$updated = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($jsDir));
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'js' || $file->getFilename() === 'i18n.js') {
        continue;
    }
    $path = $file->getPathname();
    $content = file_get_contents($path);
    if ($content === false || !preg_match('/[\x{0600}-\x{06FF}]/u', $content)) {
        continue;
    }
    $new = preg_replace_callback(
        "/'((?:[^'\\\\]|\\\\.)*[\x{0600}-\x{06FF}](?:[^'\\\\]|\\\\.)*)'/u",
        static function (array $m): string {
            $inner = $m[1];
            if (str_contains($inner, 'SiteI18n.ta(')) {
                return $m[0];
            }
            return "SiteI18n.ta('" . str_replace("'", "\\'", $inner) . "')";
        },
        $content
    );
    $new = preg_replace_callback(
        '/"((?:[^"\\\\]|\\\\.)*[\x{0600}-\x{06FF}](?:[^"\\\\]|\\\\.)*)"/u',
        static function (array $m): string {
            $inner = $m[1];
            if (str_contains($inner, 'SiteI18n.ta(')) {
                return $m[0];
            }
            return 'SiteI18n.ta("' . str_replace('"', '\\"', $inner) . '")';
        },
        $new ?? $content
    );
    if ($new !== null && $new !== $content) {
        file_put_contents($path, $new);
        $updated++;
        echo 'Updated: ' . str_replace($root . DIRECTORY_SEPARATOR, '', $path) . "\n";
    }
}

echo "\nJS files updated: {$updated}\n";
