# Hostinger — أوامر ما بعد الرفع (SSH / Terminal في hPanel)
# نفّذ من مجلد Laravel: public_html/api2

cd ~/domains/smeda.gov.sy/public_html/api2

# 1) ملف البيئة
cp ../deploy/hostinger/env.production.template .env
# أو انسخ env.production.template يدوياً إلى api2/.env

# 2) Composer (إن لم ترفع vendor/)
composer install --no-dev --optimize-autoloader

# 3) صلاحيات
chmod -R 775 storage bootstrap/cache
chmod -R 775 storage/app/public

# 4) رابط الملفات العامة
php artisan storage:link

# 5) قاعدة البيانات
php artisan migrate --force

# 6) بيانات أساسية (مرة واحدة — اختر ما يناسبك)
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=NeedsGisAccountsSeeder --force
# php artisan syria-locations:import

# 7) Cache إنتاج
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8) تحقق
php artisan about
curl -s -o /dev/null -w "%{http_code}" https://smeda.gov.sy/api/api/me
