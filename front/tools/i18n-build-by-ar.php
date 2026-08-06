<?php
/**
 * Build includes/i18n/by-ar.php — Arabic text → English lookup for __t() and output buffer.
 * php front/tools/i18n-build-by-ar.php
 */
$root = dirname(__DIR__);
$byAr = [];

function merge_pairs(array &$byAr, array $data): void
{
    foreach ($data as $key => $vals) {
        if (!is_array($vals) || !isset($vals['ar'], $vals['en'])) {
            continue;
        }
        $ar = trim((string) $vals['ar']);
        $en = trim((string) $vals['en']);
        if ($ar !== '' && $en !== '' && $ar !== $en) {
            $byAr[$ar] = $en;
        }
    }
}

function merge_faq(array &$byAr, array $sections): void
{
    foreach ($sections as $sec) {
        if (isset($sec['label']['ar'], $sec['label']['en'])) {
            $byAr[trim($sec['label']['ar'])] = trim($sec['label']['en']);
        } elseif (isset($sec['label']) && is_string($sec['label'])) {
            $byAr[trim($sec['label'])] = auto_en(trim($sec['label']), $GLOBALS['phraseMapForBuild'] ?? []);
        }
        foreach ($sec['items'] ?? [] as $item) {
            foreach (['q', 'a'] as $field) {
                if (isset($item[$field]['ar'], $item[$field]['en'])) {
                    $byAr[trim($item[$field]['ar'])] = trim($item[$field]['en']);
                } elseif (isset($item[$field]) && is_string($item[$field])) {
                    $byAr[trim($item[$field])] = auto_en(trim($item[$field]), $GLOBALS['phraseMapForBuild'] ?? []);
                }
            }
        }
    }
}

// Load all structured i18n PHP files
$i18nDir = $root . '/includes/i18n';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($i18nDir));
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $name = $file->getFilename();
    if (in_array($name, ['bootstrap.php', 'lang.php', 'by-ar.php'], true)) {
        continue;
    }
    $data = include $file->getPathname();
    if (is_array($data)) {
        merge_pairs($byAr, $data);
    }
}

// FAQ bilingual data
$faqFile = $root . '/includes/sections/faq-data.php';
if (is_file($faqFile)) {
    require_once $faqFile;
    if (function_exists('landing_faq_sections')) {
        merge_faq($byAr, landing_faq_sections());
    }
}

// Word/phrase dictionary for auto-translate fallback
$phraseMap = include __DIR__ . '/i18n-phrase-map.php';
$GLOBALS['phraseMapForBuild'] = $phraseMap;

function auto_en(string $ar, array $phraseMap): string
{
    if (isset($phraseMap[$ar])) {
        return $phraseMap[$ar];
    }
    $en = $ar;
    uksort($phraseMap, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
    foreach ($phraseMap as $from => $to) {
        if (mb_strlen($from) >= 2) {
            $en = str_replace($from, $to, $en);
        }
    }
    return $en;
}

// Clean extracted strings
$extracted = json_decode((string) file_get_contents($root . '/tools/i18n-extracted-strings.json'), true) ?: [];
foreach ($extracted as $ar) {
    $ar = trim($ar);
    if ($ar === '' || isset($byAr[$ar])) {
        continue;
    }
    if (mb_strlen($ar) < 3 || mb_strlen($ar) > 600) {
        continue;
    }
    if (!preg_match('/^[\x{0600}-\x{06FF}]/u', $ar)) {
        continue;
    }
    if (preg_match('/[\$\{\}<>\[\]`]|__\(|SiteI18n|function\s|var\s|const\s|=>/u', $ar)) {
        continue;
    }
    $arabicRatio = preg_match_all('/[\x{0600}-\x{06FF}]/u', $ar) / max(1, mb_strlen($ar));
    if ($arabicRatio < 0.35) {
        continue;
    }
    $byAr[$ar] = auto_en($ar, $phraseMap);
}

uksort($byAr, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

$out = "<?php\n\nreturn [\n";
foreach ($byAr as $ar => $en) {
    $out .= '    ' . var_export($ar, true) . ' => ' . var_export($en, true) . ",\n";
}
$out .= "];\n";

file_put_contents($i18nDir . '/by-ar.php', $out);
echo 'Built by-ar.php with ' . count($byAr) . " entries\n";
