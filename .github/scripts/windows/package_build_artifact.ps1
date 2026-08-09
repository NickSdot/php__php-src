$ErrorActionPreference = "Stop"

$artifactDirectory = Join-Path $env:RUNNER_TEMP "php-build-artifact"
if (Test-Path $artifactDirectory) {
    throw "Artifact directory already exists: $artifactDirectory"
}

$sourceSha = $env:PHP_BUILD_SOURCE_SHA
if ($sourceSha -notmatch '^[0-9a-f]{40}$') {
    throw "Invalid source commit: $sourceSha"
}
$checkedOutSha = (& git rev-parse HEAD).Trim()
if ($LASTEXITCODE -ne 0 -or $checkedOutSha -ne $sourceSha) {
    throw "Checked-out commit does not match the requested source: expected $sourceSha, got $checkedOutSha"
}

foreach ($path in @($env:GITHUB_WORKSPACE, $env:PHP_BUILD_OBJ_DIR, $env:PHP_BUILD_CACHE_BASE_DIR)) {
    if (-not (Test-Path -PathType Container $path)) {
        throw "Required build directory does not exist: $path"
    }
}

New-Item -ItemType Directory -Path $artifactDirectory | Out-Null
Set-Content -Path (Join-Path $artifactDirectory "source-sha") -Value $sourceSha -Encoding ascii

& tar.exe -cf (Join-Path $artifactDirectory "workspace.tar") `
    --exclude=.git -C $env:GITHUB_WORKSPACE .
if ($LASTEXITCODE -ne 0) {
    throw "Unable to package the PHP workspace"
}

$objectParent = Split-Path -Parent $env:PHP_BUILD_OBJ_DIR
$objectName = Split-Path -Leaf $env:PHP_BUILD_OBJ_DIR
& tar.exe -cf (Join-Path $artifactDirectory "runtime.tar") `
    "--exclude=*.exp" `
    "--exclude=*.iobj" `
    "--exclude=*.ipdb" `
    "--exclude=*.lib" `
    "--exclude=*.obj" `
    "--exclude=*.pch" `
    "--exclude=*.pdb" `
    -C $objectParent $objectName
if ($LASTEXITCODE -ne 0) {
    throw "Unable to package the PHP runtime files"
}

$cacheParent = Split-Path -Parent $env:PHP_BUILD_CACHE_BASE_DIR
$cacheName = Split-Path -Leaf $env:PHP_BUILD_CACHE_BASE_DIR
$dependencyDirectories = @(Get-ChildItem -Path $env:PHP_BUILD_CACHE_BASE_DIR -Directory -Filter "deps-*")
if ($dependencyDirectories.Count -ne 1) {
    throw "Expected exactly one PHP dependency directory, found $($dependencyDirectories.Count)"
}
$dependencyName = $dependencyDirectories[0].Name
$dependencyPath = "$cacheName/$dependencyName"
foreach ($path in @(
    "$dependencyPath/bin",
    "$dependencyPath/share",
    "$dependencyPath/template",
    "$cacheName/sdk"
)) {
    if (-not (Test-Path -PathType Container (Join-Path $cacheParent $path))) {
        throw "Required test-time build cache path does not exist: $path"
    }
}
& tar.exe -cf (Join-Path $artifactDirectory "build-cache.tar") `
    "--exclude=$cacheName/sdk/.git" `
    "--exclude=$dependencyPath/include" `
    "--exclude=$dependencyPath/lib" `
    -C $cacheParent $cacheName
if ($LASTEXITCODE -ne 0) {
    throw "Unable to package the PHP SDK and test-time dependencies"
}

$archiveNames = @("workspace.tar", "runtime.tar", "build-cache.tar")
$checksums = foreach ($name in $archiveNames) {
    $hash = (Get-FileHash (Join-Path $artifactDirectory $name) -Algorithm SHA256).Hash.ToLowerInvariant()
    "$hash  $name"
}
Set-Content -Path (Join-Path $artifactDirectory "SHA256SUMS") -Value $checksums -Encoding ascii

$archiveSizes = $archiveNames | ForEach-Object {
    $size = (Get-Item (Join-Path $artifactDirectory $_)).Length
    Write-Host ("${_}: {0:N1} MiB" -f ($size / 1MB))
    $size
}
$artifactSize = ($archiveSizes | Measure-Object -Sum).Sum
Write-Host ("Packaged Windows build: {0:N1} MiB" -f ($artifactSize / 1MB))
