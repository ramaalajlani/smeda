<?php
/**
 * Repair JS files corrupted by i18n-wrap-js.php (SiteI18n.ta inserted inside identifiers/quotes).
 * php front/tools/i18n-fix-js-corruption.php
 */
$root = dirname(__DIR__);
$jsDir = $root . '/assets/js';
$fixed = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($jsDir));
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'js' || $file->getFilename() === 'i18n.js') {
        continue;
    }
    $path = $file->getPathname();
    $original = file_get_contents($path);
    if ($original === false) {
        continue;
    }
    $content = $original;

    // Object literal keys: SiteI18n.ta('label'): → 'label':
    $content = preg_replace(
        "/SiteI18n\.ta\('((?:[^'\\\\]|\\\\.)*)'\)\s*:/u",
        "'$1':",
        $content
    );
    $content = preg_replace(
        '/SiteI18n\.ta\("((?:[^"\\\\]|\\\\.)*)"\)\s*:/u',
        "'$1':",
        $content
    );

    // Broken identifier + SiteI18n.ta(') → identifier')  (opening quote already present)
    $content = preg_replace(
        "/([A-Za-z0-9_.-]+)SiteI18n\.ta\('\)/",
        "$1')",
        $content
    );

    // Double-quote artifact from prior repair: ''word' → 'word'
    $content = preg_replace("/''([a-zA-Z0-9_.-]+)'/", "'$1'", $content);

    // Broken default param: fallback = '—' { → fallback = '—') {
    $content = preg_replace("/fallback = '—' \{/", "fallback = '—') {", $content);

    // Broken: ')SiteI18n.ta(' or similar stray
    $content = str_replace("')SiteI18n.ta('", "')", $content);
    $content = str_replace(")SiteI18n.ta('", ")", $content);
    $content = str_replace("'.SiteI18n.ta('", "'.", $content);

    // class="fooSiteI18n.ta("> → class="foo">
    $content = preg_replace('/(\w+)SiteI18n\.ta\("\>/', '$1">', $content);
    $content = preg_replace("/(\w+)SiteI18n\.ta\('\>/", "$1'>", $content);

    // classList.remove('d-noneSiteI18n.ta(') already fixed by identifier rule

    // permission strings: 'perm.nameSiteI18n.ta(') → 'perm.name')
    // (covered by identifier rule)

    // Stray paren in string literals: '')word' → 'word'
    $content = preg_replace("/'\)'([^']+)'/", "'$1'", $content);

    // Do NOT rewrite '—'} inside template literals — that breaks ${expr || '—'}.

    // .join(')') → .join('')
    $content = preg_replace("/\.join\('\)'\)/", ".join('')", $content);

    // Empty-string fallback: || ')' → || ''
    $content = preg_replace("/\|\|\s*'\)'(?=[\s;\)\}])/", "|| ''", $content);

    // Ternary empty branch corrupted: : '} → : ''}
    $content = preg_replace("/:\s*'\}/", ": ''}", $content);

    // type=")button" → type="button"
    $content = str_replace('type=")button"', 'type="button"', $content);

    // Remaining SiteI18n.ta( without closing in comparisons
    $content = preg_replace("/!==\s*'(\w+)SiteI18n\.ta\(\s*\?/", "!== '$1' ?", $content);
    $content = preg_replace("/(\w+)SiteI18n\.ta\(\"/", '$1"', $content);

    // classList.remove('open') style: ')'open' → 'open'
    $content = preg_replace("/'\)'(\w+)'/", "'$1'", $content);

    // value || 'SiteI18n.ta(') → value || '')
    $content = preg_replace("/\|\|\s*'SiteI18n\.ta\('\)/", "|| '')", $content);

    // !== ')completedSiteI18n.ta(') → !== 'completed')
    $content = preg_replace("/'(\))(\w+)SiteI18n\.ta\('\)/", "'$1$2')", $content);

    // type === 'courseSiteI18n.ta(') → type === 'course')
    $content = preg_replace("/'(\w+)SiteI18n\.ta\('\)/", "'$1')", $content);

    // href=")${ → href="${  (broken template)
    $content = str_replace('href=")${', 'href="${', $content);
    $content = str_replace("href=')\${", "href='\${", $content);

    // <div class=")col → <div class="col
    $content = preg_replace('/class="\)(\w)/', 'class="$1', $content);
    $content = preg_replace("/class='\)(\w)/", "class='$1", $content);

    // createElement('trSiteI18n.ta(') → createElement('tr')
    // (identifier rule)

    // successSiteI18n.ta(') → success')
    // (word rule above)

    if ($content !== $original) {
        file_put_contents($path, $content);
        $fixed++;
        echo 'Fixed: ' . str_replace($root . DIRECTORY_SEPARATOR, '', $path) . "\n";
    }
}

echo "\nFiles repaired: {$fixed}\n";
