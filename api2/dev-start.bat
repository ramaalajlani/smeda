@echo off

setlocal

cd /d "%~dp0"



echo.

echo [0/3] Clearing Laravel caches (ensures .env DB settings are used)...

php artisan config:clear

php artisan cache:clear

php artisan route:clear

php artisan permission:cache-reset >nul 2>&1



echo.

echo [1/3] Starting Laravel API on http://127.0.0.1:8000 (MySQL: authority3)...

start "Laravel API" cmd /k "cd /d "%~dp0" && set DB_CONNECTION=mysql && set DB_HOST=127.0.0.1 && set DB_PORT=3306 && set DB_DATABASE=authority3 && set DB_USERNAME=root && set DB_PASSWORD= && php artisan serve --host=127.0.0.1 --port=8000"



echo [2/3] Starting Frontend on http://127.0.0.1:8080 ...

start "Frontend" cmd /k "cd /d "%~dp0" && php -S 127.0.0.1:8080 -t front front/router.php"



echo.

echo Open: http://127.0.0.1:8080/register.php

echo DB check: php artisan tinker --execute="echo config('database.default').' / '.DB::connection()->getDatabaseName();"

echo.

pause

