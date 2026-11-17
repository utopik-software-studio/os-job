param(
    [Parameter(Mandatory=$true)][string]$PhpPath,
    [Parameter(Mandatory=$true)][string]$WorkerPath,
    [Parameter(Mandatory=$true)][string]$JobPath,
    [Parameter(Mandatory=$true)][string]$LogPath
)
$ErrorActionPreference = 'Stop'
try {
    if (-not (Test-Path -LiteralPath $PhpPath)) { throw "PHP CLI introuvable: $PhpPath" }
    if (-not (Test-Path -LiteralPath $WorkerPath)) { throw "Worker introuvable: $WorkerPath" }
    $argsForPhp = '"' + $WorkerPath + '" "' + $JobPath + '"'
    $process = Start-Process -FilePath $PhpPath -ArgumentList $argsForPhp -WindowStyle Hidden -RedirectStandardError $LogPath -RedirectStandardOutput ($LogPath + '.out') -PassThru
    if (-not $process -or -not $process.Id) { throw 'Start-Process ne retourne aucun identifiant.' }
    Write-Output $process.Id
    exit 0
} catch {
    [Console]::Error.WriteLine($_.Exception.Message)
    exit 1
}
