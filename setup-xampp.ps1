# One-time setup for Teacher Audit System on XAMPP (Windows)
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

Write-Host "Installing Composer dependencies..."
composer install --no-interaction

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example"
}

Write-Host "Generating application key..."
php artisan key:generate --force

$mysql = "C:\xampp\mysql\bin\mysql.exe"
if (Test-Path $mysql) {
    Write-Host "Creating MySQL database thesis_capstone..."
    & $mysql -u root -e "SOURCE database/setup_xampp.sql"
} else {
    Write-Host "MySQL CLI not found. Create database 'thesis_capstone' manually in phpMyAdmin."
}

Write-Host "Running migrations and seeders..."
php artisan migrate --seed --force

Write-Host ""
Write-Host "Setup complete."
Write-Host "  Login: admin@deped.gov.ph / password"
Write-Host "  Run:   php artisan serve"
Write-Host "  Or:    http://localhost/thesis_capstone/public"
