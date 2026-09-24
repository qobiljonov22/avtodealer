# OpenServersiz sayt + admin (statik)
# Ishlatish:  .\serve-static.ps1
# Keyin: http://127.0.0.1:8080/   va   http://127.0.0.1:8080/admin/

$root = Join-Path $PSScriptRoot 'public'
$port = 8080

Write-Host "Avtodealer static → http://127.0.0.1:$port/" -ForegroundColor Cyan
Write-Host "Admin           → http://127.0.0.1:$port/admin/" -ForegroundColor Yellow
Write-Host "Stop: Ctrl+C" -ForegroundColor DarkGray

if (Get-Command py -ErrorAction SilentlyContinue) {
  Set-Location $root
  py -m http.server $port
} elseif (Get-Command python -ErrorAction SilentlyContinue) {
  Set-Location $root
  python -m http.server $port
} elseif (Get-Command npx -ErrorAction SilentlyContinue) {
  npx --yes serve $root -l $port
} else {
  Write-Host "Python yoki Node (npx) kerak." -ForegroundColor Red
  exit 1
}
