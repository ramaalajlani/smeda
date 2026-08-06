# Switch .env to local development (backs up current file first).
$root = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $root '.env'
$backup = Join-Path $root '.env.production.backup'

if (-not (Test-Path $envFile)) {
    Write-Error ".env not found at $envFile"
    exit 1
}

Copy-Item $envFile $backup -Force
Write-Host "Backed up to .env.production.backup"

$content = Get-Content $envFile -Raw
$replacements = @{
    'APP_ENV=production' = 'APP_ENV=local'
    'APP_DEBUG=false' = 'APP_DEBUG=true'
    'APP_URL=https://smeda.gov.sy' = 'APP_URL=http://127.0.0.1:8000'
    'FRONTEND_URL=https://smeda.gov.sy' = 'FRONTEND_URL=http://127.0.0.1:8080'
    'LOG_LEVEL=error' = 'LOG_LEVEL=debug'
    'DB_HOST=localhost' = 'DB_HOST=127.0.0.1'
    'SESSION_DOMAIN=.smeda.gov.sy' = 'SESSION_DOMAIN=null'
    'SANCTUM_STATEFUL_DOMAINS=smeda.gov.sy,www.smeda.gov.sy' = 'SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1'
    'CORS_ALLOWED_ORIGINS=https://smeda.gov.sy,https://www.smeda.gov.sy' = 'CORS_ALLOWED_ORIGINS=http://127.0.0.1:8080,http://localhost:8080,http://127.0.0.1:8000,http://localhost:8000'
}

foreach ($pair in $replacements.GetEnumerator()) {
    $content = $content.Replace($pair.Key, $pair.Value)
}

# Local DB defaults (edit if your MySQL differs)
$content = $content -replace 'DB_DATABASE=.*', 'DB_DATABASE=authority3'
$content = $content -replace 'DB_USERNAME=.*', 'DB_USERNAME=root'
$content = $content -replace 'DB_PASSWORD=.*', 'DB_PASSWORD='

Set-Content -Path $envFile -Value $content -NoNewline
Write-Host "Switched to local .env"
Write-Host "Run: php artisan config:clear && php artisan migrate && php artisan db:seed"
Write-Host "Then: dev-start.bat"
