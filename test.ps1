# Run Laravel tests with PHP 8.3+ (Windows)
$phpCandidates = @(
    "C:\Program Files\php-8.5.8\php.exe",
    "$env:LOCALAPPDATA\Programs\PHP\8.3\php.exe",
    "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
)

$php = $phpCandidates | Where-Object { Test-Path $_ } | Select-Object -First 1

if (-not $php) {
    Write-Error @"
PHP 8.3+ not found. Install PHP 8.5+ or run: winget install PHP.PHP.8.3
Or see DEVELOPER_CONTEXT.md for setup.
"@
    exit 1
}

Write-Host "Using: $php"
& $php --ini
& $php artisan test @args
