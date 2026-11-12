# Deploy slava-ads.ru to the VPS from Windows.
# Usage: powershell -ExecutionPolicy Bypass -File deploy\deploy.ps1
# Requires: ssh + scp in PATH, key access to the VPS.

$ErrorActionPreference = "Stop"

$VPS = "root@162.35.161.95"
$REMOTE = "/var/www/slava-ads.ru"
$ROOT = Split-Path -Parent $PSScriptRoot

Write-Host "==> Building site..." -ForegroundColor Cyan
Push-Location $ROOT
npm run build
if ($LASTEXITCODE -ne 0) { Pop-Location; throw "build failed ($LASTEXITCODE)" }
Pop-Location

Write-Host "==> Packing artifact..." -ForegroundColor Cyan
$tmp = Join-Path $env:TEMP "slava-ads-deploy"
if (Test-Path $tmp) { Remove-Item $tmp -Recurse -Force }
New-Item -ItemType Directory -Path $tmp | Out-Null

Copy-Item "$ROOT\dist\*" $tmp -Recurse -Force
Copy-Item "$ROOT\api" $tmp -Recurse -Force
Copy-Item "$ROOT\admin" $tmp -Recurse -Force
Copy-Item "$ROOT\deploy" $tmp -Recurse -Force
# Never ship a local .env
Remove-Item "$tmp\api\.env" -ErrorAction SilentlyContinue

$archive = Join-Path $env:TEMP "slava-ads.tar.gz"
tar -czf $archive -C $tmp .
if ($LASTEXITCODE -ne 0) { throw "tar failed ($LASTEXITCODE)" }

# BatchMode=yes forbids interactive prompts (password / host-key confirm) that make
# scp/ssh hang forever; ConnectTimeout bounds the initial TCP connect. Pass them as
# an array splat: a single string would reach ssh.exe as one malformed argument.
$SSHOPTS = @("-o", "BatchMode=yes", "-o", "ConnectTimeout=15")

Write-Host "==> Uploading..." -ForegroundColor Cyan
ssh @SSHOPTS $VPS "mkdir -p $REMOTE"
if ($LASTEXITCODE -ne 0) { throw "ssh mkdir failed ($LASTEXITCODE)" }
scp @SSHOPTS $archive "${VPS}:/tmp/slava-ads.tar.gz"
if ($LASTEXITCODE -ne 0) { throw "scp failed ($LASTEXITCODE)" }

Write-Host "==> Extracting on server (preserving api/.env)..." -ForegroundColor Cyan
ssh @SSHOPTS $VPS "cd $REMOTE && tar --exclude='./api/.env' -xzf /tmp/slava-ads.tar.gz && chown -R www-data:www-data $REMOTE && rm /tmp/slava-ads.tar.gz && systemctl reload apache2"
if ($LASTEXITCODE -ne 0) { throw "remote extract failed ($LASTEXITCODE)" }

Write-Host "==> Done." -ForegroundColor Green
