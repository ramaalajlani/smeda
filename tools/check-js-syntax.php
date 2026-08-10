<?php
/**
 * Scan all JavaScript files under front/ and verify syntax with `node --check`.
 *
 * Usage:
 *   php front/tools/check-js-syntax.php
 *   php front/tools/check-js-syntax.php --dir=assets/js/pages
 *
 * Exit code: 0 if all files pass, 1 if any file fails.
 */
$frontRoot = dirname(__DIR__);
$scanDir = $frontRoot;

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--dir=')) {
        $scanDir = $frontRoot . '/' . ltrim(substr($arg, 6), '/');
        break;
    }
}

if (!is_dir($scanDir)) {
    fwrite(STDERR, "Directory not found: {$scanDir}\n");
    exit(1);
}

$node = trim((string) shell_exec(PHP_OS_FAMILY === 'Windows' ? 'where node 2>nul' : 'which node 2>/dev/null'));
if ($node === '') {
    fwrite(STDERR, "Node.js is required. Install Node and ensure `node` is on PATH.\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanDir));
$checked = 0;
$failures = [];

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'js') {
        continue;
    }

    $path = $file->getPathname();
    $checked++;

    $cmd = 'node --check ' . escapeshellarg($path) . ' 2>&1';
    $output = shell_exec($cmd);
    $exitCode = 0;
    if (PHP_OS_FAMILY === 'Windows') {
        // shell_exec does not return exit code on Windows; re-check via proc_open when needed
        $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (is_resource($proc)) {
            $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($proc);
        }
    } else {
        $exitCode = $output === null ? 1 : 0;
        exec($cmd, $lines, $exitCode);
        if ($output === null) {
            $output = implode("\n", $lines);
        }
    }

    if ($exitCode !== 0) {
        $relative = str_replace($frontRoot . DIRECTORY_SEPARATOR, '', $path);
        $failures[$relative] = trim((string) $output);
    }
}

echo "Checked {$checked} JavaScript file(s) under " . str_replace($frontRoot . DIRECTORY_SEPARATOR, '', $scanDir) . "\n";

if ($failures === []) {
    echo "OK — no syntax errors.\n";
    exit(0);
}

echo count($failures) . " file(s) with syntax errors:\n\n";
foreach ($failures as $file => $error) {
    echo "  {$file}\n";
    if ($error !== '') {
        echo "    " . str_replace("\n", "\n    ", $error) . "\n";
    }
    echo "\n";
}

exit(1);
