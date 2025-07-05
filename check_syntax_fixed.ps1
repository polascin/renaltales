# PowerShell script to check PHP syntax of all language files

$languageFiles = Get-ChildItem -Path "languages" -Filter "*.php"
$errorCount = 0

Write-Host "Checking PHP syntax for all language files..." -ForegroundColor Yellow

foreach ($file in $languageFiles) {
    $result = php -l $file.FullName 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "OK: $($file.Name)" -ForegroundColor Green
    } else {
        Write-Host "ERROR: $($file.Name)" -ForegroundColor Red
        Write-Host "  $result" -ForegroundColor Red
        $errorCount++
    }
}

Write-Host "`nSyntax check completed!" -ForegroundColor Magenta
if ($errorCount -eq 0) {
    Write-Host "All $($languageFiles.Count) language files have valid PHP syntax!" -ForegroundColor Green
} else {
    Write-Host "$errorCount files have syntax errors." -ForegroundColor Red
}
