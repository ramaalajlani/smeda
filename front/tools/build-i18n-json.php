<?php
/**
 * Build flat ar.json / en.json from all PHP translation sources.
 * Run: php front/tools/build-i18n-json.php
 */
$root = dirname(__DIR__);
$outDir = $root . '/assets/i18n';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$ar = [];
$en = [];
$i18nDir = $root . '/includes/i18n';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($i18nDir));
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
        if (!is_array($vals)) {
            continue;
        }
        if (isset($vals['ar'])) {
            $ar[$key] = $vals['ar'];
        }
        if (isset($vals['en'])) {
            $en[$key] = $vals['en'];
        }
    }
}

// Include by-ar flat pairs as auto keys
$byAr = $i18nDir . '/by-ar.php';
if (is_file($byAr)) {
    $pairs = include $byAr;
    if (is_array($pairs)) {
        $i = 0;
        foreach ($pairs as $arText => $enText) {
            $key = 'auto_' . substr(md5($arText), 0, 10);
            if (!isset($ar[$key])) {
                $ar[$key] = $arText;
                $en[$key] = $enText;
            }
        }
    }
}

ksort($ar);
ksort($en);

$flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
file_put_contents($outDir . '/ar.json', json_encode($ar, $flags) . "\n");
file_put_contents($outDir . '/en.json', json_encode($en, $flags) . "\n");

echo 'Built ' . count($ar) . ' AR keys and ' . count($en) . ' EN keys → assets/i18n/' . "\n";
