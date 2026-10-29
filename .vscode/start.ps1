# Launch helpers live in .runtime; the app itself is in public/index.php.
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$runtime = Join-Path $projectRoot '.runtime'
$php = Join-Path $runtime 'php\php.exe'
$mysql = Join-Path $runtime 'mysql\bin\mysqld.exe'
if (!(Test-Path -LiteralPath $php) -or !(Test-Path -LiteralPath $mysql)) {
    throw 'The project-local PHP or MySQL files are missing from .runtime.'
}

# Generate paths from this folder so AppData settings are not needed.
$mysqlConfig = Join-Path $runtime 'mysql.ini'
$base = (Join-Path $runtime 'mysql') -replace '\\', '/'
$data = (Join-Path $runtime 'mysql-data') -replace '\\', '/'
$log = (Join-Path $runtime 'mysql-error.log') -replace '\\', '/'
$settings = "[mysqld]`r`nbasedir=$base`r`ndatadir=$data`r`nbind-address=127.0.0.1`r`nport=3306`r`nmysqlx=0`r`nlog-error=$log`r`n"
[IO.File]::WriteAllText($mysqlConfig, $settings)

if (!(Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $mysql -ArgumentList "--defaults-file=`"$mysqlConfig`"" -WindowStyle Hidden
    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        if (Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue) { break }
        Start-Sleep -Milliseconds 250
    }
    if (!(Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue)) {
        throw "MySQL did not start. Check $log"
    }
}

$httpPort = 8000
while (Get-NetTCPConnection -LocalPort $httpPort -State Listen -ErrorAction SilentlyContinue) {
    $httpPort++
}
Write-Host "Open http://localhost:$httpPort in your browser. Press Ctrl+C to stop PHP."
& $php -n -d "extension_dir=$runtime\php\ext" -d extension=pdo_mysql -S "localhost:$httpPort" -t "$projectRoot\src\public"
