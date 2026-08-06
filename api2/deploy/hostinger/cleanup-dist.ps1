#Requires -Version 5.1
<#
.SYNOPSIS
  Deletes the broken deploy/hostinger/dist folder (Windows long-path safe).

.EXAMPLE
  .\deploy\hostinger\cleanup-dist.ps1
#>
$ErrorActionPreference = 'Stop'
$Dist = Join-Path $PSScriptRoot 'dist'

if (-not (Test-Path -LiteralPath $Dist)) {
    Write-Host "Nothing to clean: $Dist"
    exit 0
}

$longPath = "\\?\$((Resolve-Path -LiteralPath $Dist).Path)"
Write-Host "Cleaning: $Dist"

cmd /c "rd /s /q `"$longPath`"" | Out-Null

if (Test-Path -LiteralPath $Dist) {
    Write-Warning "Could not fully delete dist. Reboot PC, then delete this folder manually:"
    Write-Warning $Dist
    exit 1
}

Write-Host "Done."
