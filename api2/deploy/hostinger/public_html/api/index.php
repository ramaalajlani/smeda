<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Detect Laravel Path (Hostinger: public_html/api → public_html/api2)
|--------------------------------------------------------------------------
*/
$candidatePaths = [
    __DIR__ . '/../api2',        // public_html/api2  (recommended layout)
    __DIR__ . '/../../api2',     // sibling of public_html
    __DIR__ . '/../../../api2',  // fallback
];

$laravelPath = null;

foreach ($candidatePaths as $path) {
    if (
        file_exists($path . '/vendor/autoload.php') &&
        file_exists($path . '/bootstrap/app.php')
    ) {
        $laravelPath = realpath($path);
        break;
    }
}

if (!$laravelPath) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');

    echo "Laravel path not found.\n\n";
    echo "Checked paths:\n";

    foreach ($candidatePaths as $path) {
        echo "- {$path}\n";
        echo '  autoload: ' . (file_exists($path . '/vendor/autoload.php') ? 'YES' : 'NO') . "\n";
        echo '  bootstrap: ' . (file_exists($path . '/bootstrap/app.php') ? 'YES' : 'NO') . "\n";
        echo '  .env: ' . (file_exists($path . '/.env') ? 'YES' : 'NO') . "\n\n";
    }

    exit;
}

if (file_exists($maintenance = $laravelPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelPath . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelPath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
