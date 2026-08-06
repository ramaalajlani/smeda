<?php

declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$root = __DIR__;
$file = $root . str_replace('/', DIRECTORY_SEPARATOR, $uri);

if ($uri !== '/' && is_file($file)) {
    return false;
}

if ($uri === '/' || $uri === '') {
    require $root . DIRECTORY_SEPARATOR . 'index.php';
    return true;
}

$candidate = $root . str_replace('/', DIRECTORY_SEPARATOR, rtrim($uri, '/')) . '.php';
if (is_file($candidate)) {
    require $candidate;
    return true;
}

http_response_code(404);
echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>404</title></head><body>';
echo '<h1>الصفحة غير موجودة</h1>';
echo '<p>تأكد أنك تشغّل الفرونت إند من مجلد <code>front</code>.</p>';
echo '<p><a href="/login.php">تسجيل الدخول</a></p>';
echo '</body></html>';
