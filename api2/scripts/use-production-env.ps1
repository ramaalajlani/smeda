# Restore .env from .env.production.backup
$root = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $root '.env'
$backup = Join-Path $root '.env.production.backup'

if (-not (Test-Path $backup)) {
    Write-Error ".env.production.backup not found"
    exit 1
}

Copy-Item $backup $envFile -Force
Write-Host "Restored production .env from backup"
Write-Host "Run: php artisan config:clear"
