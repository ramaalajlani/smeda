<?php

/**
 * Copy to config.php and adjust if needed.
 */
return [
    // null = auto-detect from current domain (recommended on Hostinger)
    // 'base_url' => 'http://127.0.0.1:8080',
    'base_url' => null,

    // Optional overrides (usually leave null — auto-detect handles both):
    // Production (smeda.gov.sy Hostinger layout):
    // 'api_base_url' => 'https://smeda.gov.sy/api/api',
    // 'backend_base_url' => 'https://smeda.gov.sy/api',
    // Staging (new.smeda.gov.sy — Laravel at api2/public):
    // 'api_base_url' => 'https://new.smeda.gov.sy/api2/public/api',
    // 'backend_base_url' => 'https://new.smeda.gov.sy/api2/public',
    'api_base_url' => null,
    'backend_base_url' => null,
];
