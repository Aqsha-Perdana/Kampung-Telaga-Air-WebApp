param(
    [string]$ProjectRoot = "",
    [string]$PhpExecutable = "php"
)

$ErrorActionPreference = "Stop"

if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path
}

$artisanPath = Join-Path $ProjectRoot "artisan"
$logDirectory = Join-Path $ProjectRoot "storage\logs"
$logPath = Join-Path $logDirectory "scheduler-run.log"

if (-not (Test-Path $artisanPath)) {
    Write-Error "Laravel artisan file not found at $artisanPath"
}

if (-not (Test-Path $logDirectory)) {
    New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
}

$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

Push-Location $ProjectRoot

try {
    $output = & $PhpExecutable $artisanPath schedule:run --no-interaction 2>&1
    $exitCode = $LASTEXITCODE

    Add-Content -Path $logPath -Value "[$timestamp] php artisan schedule:run"

    if ($output) {
        $output | ForEach-Object {
            Add-Content -Path $logPath -Value $_
        }
    }

    Add-Content -Path $logPath -Value ""

    exit $exitCode
} finally {
    Pop-Location
}
