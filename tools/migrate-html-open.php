<?php
/**
 * One-time migration: replace hardcoded <!DOCTYPE html><html lang="ar"> with html-open.php include.
 */
$frontRoot = realpath(dirname(__DIR__));
$htmlOpen = $frontRoot . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'html-open.php';

function relativeHtmlOpenInclude(string $fromDir, string $htmlOpenPath): string
{
    $from = str_replace('\\', '/', realpath($fromDir));
    $toDir = str_replace('\\', '/', dirname(realpath($htmlOpenPath)));

    $fromParts = explode('/', $from);
    $toParts = explode('/', $toDir);

    while ($fromParts && $toParts && $fromParts[0] === $toParts[0]) {
        array_shift($fromParts);
        array_shift($toParts);
    }

    $rel = array_merge(
        array_fill(0, count($fromParts), '..'),
        $toParts
    );

    $path = implode('/', $rel) . '/html-open.php';
    return "__DIR__ . '/" . $path . "'";
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($frontRoot, RecursiveDirectoryIterator::SKIP_DOTS)
);

$updated = 0;
$skipped = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();

    if (str_contains($path, 'html-open.php') || str_contains($path, 'migrate-html-open.php')) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false || !preg_match('/<!DOCTYPE\s+html>/i', $content)) {
        continue;
    }

    if (str_contains($content, 'html-open.php')) {
        $skipped++;
        continue;
    }

    $include = relativeHtmlOpenInclude(dirname($path), $htmlOpen);
    $replacement = "<?php include {$include}; ?>\n";

    $newContent = preg_replace(
        '/<!DOCTYPE\s+html>\s*\r?\n<html[^>]*>\s*\r?\n/i',
        $replacement,
        $content,
        1,
        $count
    );

    if ($count === 0) {
        $skipped++;
        continue;
    }

    file_put_contents($path, $newContent);
    $updated++;
    echo "Updated: " . str_replace($frontRoot . DIRECTORY_SEPARATOR, '', $path) . "\n";
}

echo "\nDone. Updated: {$updated}, Skipped: {$skipped}\n";
