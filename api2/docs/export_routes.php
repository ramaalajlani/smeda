<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = app('router')->getRoutes();
$export = [];

foreach ($routes as $route) {
    $middleware = $route->gatherMiddleware();
    $action = $route->getActionName();
    $export[] = [
        'domain' => $route->getDomain(),
        'method' => implode('|', $route->methods()),
        'uri' => $route->uri(),
        'name' => $route->getName(),
        'action' => $action,
        'middleware' => $middleware,
    ];
}

file_put_contents(__DIR__ . '/_routes_export.json', json_encode($export, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo count($export) . ' routes exported.' . PHP_EOL;
