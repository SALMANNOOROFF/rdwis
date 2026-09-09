# ==============================================================================
# RDWIS 2.0 - Storage Synchronization Utility for Secondary PC
# Copies storage/app/public folders (aud, hr, prj, pur, purchase) to PC 2
# ==============================================================================

param (
    [string]$DestinationPath
)

$SourcePath = "H:\RDWIS 2.0\RDWIS APP 2.0\storage\app\public"

Clear-Host
Write-Host "==================================================================" -ForegroundColor Cyan
Write-Host "   RDWIS 2.0 - STORAGE SYNC UTILITY (MAIN PC -> SECOND PC)        " -ForegroundColor Yellow
Write-Host "==================================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Source Folder: $SourcePath" -ForegroundColor Green

if (-not $DestinationPath) {
    Write-Host "Please enter the path to 'storage\app\public' on your second PC." -ForegroundColor White
    Write-Host "Examples:" -ForegroundColor Gray
    Write-Host "  Network Share: \\192.168.1.160\c$\RDWIS 2.0\RDWIS APP 2.0\storage\app\public" -ForegroundColor Gray
    Write-Host "  USB Drive:     E:\RDWIS_STORAGE\public" -ForegroundColor Gray
    Write-Host ""
    $DestinationPath = Read-Host "Destination Path"
}

if (-not (Test-Path $DestinationPath)) {
    Write-Host "Destination path does not exist. Creating it now: $DestinationPath" -ForegroundColor Yellow
    New-Item -ItemType Directory -Force -Path $DestinationPath | Out-Null
}

$folders = @('aud', 'hr', 'ina', 'prj', 'pur', 'purchase')

foreach ($f in $folders) {
    $src = Join-Path $SourcePath $f
    $dst = Join-Path $DestinationPath $f
    if (Test-Path $src) {
        Write-Host "Syncing folder [$f]..." -ForegroundColor Cyan
        robocopy $src $dst /E /MT:16 /R:2 /W:2 /NP
    }
}

Write-Host ""
Write-Host "==================================================================" -ForegroundColor Green
Write-Host "  SUCCESS: All storage folders have been synced to the destination!" -ForegroundColor Green
Write-Host "==================================================================" -ForegroundColor Green
