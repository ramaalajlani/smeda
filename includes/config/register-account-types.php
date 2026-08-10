<?php

// موقع تطبيق Laravel يختلف بين البيئات:
//   - الإنتاج: app/ بجانب front/           => ../../../app
//   - التطوير المحلي: app/ داخل api2/       => ../../../api2/app
// نجرّب المسارات المحتملة بالترتيب حتى نجد الملف.
if (!class_exists(\App\Support\SelfRegistrationCatalog::class, false)) {
    $selfRegCatalogCandidates = [
        __DIR__ . '/../../../app/Support/SelfRegistrationCatalog.php',
        __DIR__ . '/../../../api2/app/Support/SelfRegistrationCatalog.php',
        __DIR__ . '/../../../../app/Support/SelfRegistrationCatalog.php',
    ];
    foreach ($selfRegCatalogCandidates as $selfRegCatalogPath) {
        if (is_file($selfRegCatalogPath)) {
            require_once $selfRegCatalogPath;
            break;
        }
    }
    unset($selfRegCatalogCandidates, $selfRegCatalogPath);
}

use App\Support\SelfRegistrationCatalog;

/**
 * @deprecated Use SelfRegistrationCatalog directly — kept for front includes.
 */
function register_account_types_catalog(): array
{
    return SelfRegistrationCatalog::groups();
}

function register_account_type_keys(): array
{
    return SelfRegistrationCatalog::accountTypeKeys();
}

function register_account_type_meta(string $key): ?array
{
    return SelfRegistrationCatalog::meta($key);
}

function register_account_type_normalize(string $key): string
{
    return SelfRegistrationCatalog::normalizeAccountType($key);
}
