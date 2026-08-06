#Requires -Version 5.1
<#
.SYNOPSIS
  Builds Hostinger upload ZIPs from the repo.

.EXAMPLE
  .\deploy\hostinger\build-upload.ps1
#>
$ErrorActionPreference = 'Stop'
$Api2Root = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$RepoRoot = Split-Path $Api2Root -Parent
$OutDir = Join-Path $PSScriptRoot 'out'
$LegacyDist = Join-Path $PSScriptRoot 'dist'
$Stamp = Get-Date -Format 'yyyyMMdd-HHmm'

function Remove-DirForce {
    param([Parameter(Mandatory = $true)][string]$Path)

    if (-not (Test-Path -LiteralPath $Path)) {
        return
    }

    $longPath = if ($Path -match '^\\\\\?\\') { $Path } else { "\\?\$((Resolve-Path -LiteralPath $Path).Path)" }

    try {
        Remove-Item -LiteralPath $Path -Recurse -Force -ErrorAction Stop
        return
    } catch {
        # Windows long-path fallback
    }

    cmd /c "rd /s /q `"$longPath`"" | Out-Null
    if (Test-Path -LiteralPath $Path) {
        $empty = Join-Path $env:TEMP ("smeda-empty-" + [guid]::NewGuid().ToString('N'))
        New-Item -ItemType Directory -Path $empty | Out-Null
        robocopy $empty $Path /MIR /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
        Remove-Item -LiteralPath $empty -Force -ErrorAction SilentlyContinue
        cmd /c "rd /s /q `"$longPath`"" | Out-Null
    }
}

Remove-DirForce -Path $OutDir
if (Test-Path -LiteralPath $LegacyDist) {
    Write-Host "Note: old deploy/hostinger/dist still exists (safe to ignore or delete manually)."
}
New-Item -ItemType Directory -Path $OutDir | Out-Null

Write-Host "Building front zip..."
$frontZip = Join-Path $OutDir "smeda-front-$Stamp.zip"
$frontSrc = Join-Path $RepoRoot 'front'
if (-not (Test-Path -LiteralPath $frontSrc)) {
    throw "Front folder not found: $frontSrc (expected sibling of api2/)"
}
$frontItems = Get-ChildItem -LiteralPath $frontSrc -Force |
    Where-Object { $_.Name -ne 'nul' } |
    ForEach-Object { $_.FullName }
Compress-Archive -Path $frontItems -DestinationPath $frontZip -Force

Write-Host "Building api2 zip (may take a minute)..."
$api2Zip = Join-Path $OutDir "smeda-api2-$Stamp.zip"
# Build outside dist/ to avoid recursive copy into deploy/hostinger/dist
$api2Temp = Join-Path $env:TEMP "smeda-api2-$Stamp"
Remove-DirForce -Path $api2Temp
New-Item -ItemType Directory -Path $api2Temp | Out-Null

# /XD matches directory NAMES — do not use generic names like "views" (would skip resources/views)
$excludeDirs = @(
    '.git',
    'node_modules',
    'tests',
    'front',
    'deploy',
    'dist',
    'out'
)

robocopy $Api2Root $api2Temp /E /XD $excludeDirs /XF '.env' '.env.*' /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
if ($LASTEXITCODE -ge 16) {
    throw "robocopy failed with exit code $LASTEXITCODE"
}

$nulArtifact = Join-Path $api2Temp 'nul'
if (Test-Path -LiteralPath $nulArtifact) {
    Remove-Item -LiteralPath $nulArtifact -Force -ErrorAction SilentlyContinue
}

foreach ($rel in @(
    'storage\logs',
    'storage\framework\cache\data',
    'storage\framework\sessions',
    'storage\framework\views'
)) {
    $p = Join-Path $api2Temp $rel
    if (Test-Path -LiteralPath $p) {
        Remove-Item -LiteralPath "$p\*" -Recurse -Force -ErrorAction SilentlyContinue
    }
}

# Windows may create a reserved "nul" artifact during robocopy — exclude it from the zip.
$api2Items = Get-ChildItem -LiteralPath $api2Temp -Force |
    Where-Object { $_.Name -ne 'nul' } |
    ForEach-Object { $_.FullName }
if (-not $api2Items) {
    throw "No files found in api2 temp folder: $api2Temp"
}
Compress-Archive -Path $api2Items -DestinationPath $api2Zip -Force
Remove-DirForce -Path $api2Temp

Write-Host "Copying Hostinger entry files..."
$entryZip = Join-Path $OutDir "smeda-api-entry-$Stamp.zip"
$entryTemp = Join-Path $env:TEMP "smeda-api-entry-$Stamp"
Remove-DirForce -Path $entryTemp
New-Item -ItemType Directory -Path (Join-Path $entryTemp 'api') | Out-Null
Copy-Item (Join-Path $PSScriptRoot 'public_html\api\*') (Join-Path $entryTemp 'api') -Force
Copy-Item (Join-Path $PSScriptRoot 'public_html\.htaccess') $entryTemp -Force
Copy-Item (Join-Path $PSScriptRoot 'public_html\config.php') $entryTemp -Force
Copy-Item (Join-Path $PSScriptRoot 'public_html\api2.htaccess.deny-all') $entryTemp -Force
Copy-Item (Join-Path $PSScriptRoot 'env.production.template') $entryTemp -Force
Compress-Archive -Path (Join-Path $entryTemp '*') -DestinationPath $entryZip -Force
Remove-DirForce -Path $entryTemp

Write-Host ""
Write-Host "Done. Upload packages:"
Write-Host "  1) Extract smeda-front-*.zip  -> public_html/"
Write-Host "  2) Extract smeda-api2-*.zip     -> public_html/api2/"
Write-Host "  3) Extract smeda-api-entry-*.zip -> merge into public_html/"
Write-Host "  4) Rename api2.htaccess.deny-all -> api2/.htaccess"
Write-Host "  5) Copy env.production.template -> api2/.env"
Write-Host ""
Write-Host "Output: $OutDir"
