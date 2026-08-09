param(
    [Parameter(Mandatory)]
    [string] $SourceSha
)

$ErrorActionPreference = "Stop"

$artifactDirectory = Join-Path $env:RUNNER_TEMP "php-build-artifact"
if ($SourceSha -notmatch '^[0-9a-f]{40}$') {
    throw "Invalid expected source commit: $SourceSha"
}

$artifactShaPath = Join-Path $artifactDirectory "source-sha"
if (-not (Test-Path -PathType Leaf $artifactShaPath)) {
    throw "Build artifact has no source commit"
}
$artifactSha = (Get-Content $artifactShaPath -Raw).Trim()
if ($artifactSha -notmatch '^[0-9a-f]{40}$' -or $artifactSha -ne $SourceSha) {
    throw "Build artifact source mismatch: expected $SourceSha, got $artifactSha"
}

$expectedArchives = @("workspace.tar", "runtime.tar", "build-cache.tar")
$checksumPath = Join-Path $artifactDirectory "SHA256SUMS"
if (-not (Test-Path -PathType Leaf $checksumPath)) {
    throw "Build artifact has no checksum manifest"
}

$verifiedArchives = @{}
foreach ($line in Get-Content $checksumPath) {
    if ($line -notmatch '^([0-9a-f]{64})  ([A-Za-z0-9._-]+)$') {
        throw "Invalid build artifact checksum entry: $line"
    }
    $expectedHash = $Matches[1]
    $name = $Matches[2]
    if ($name -notin $expectedArchives -or $verifiedArchives.ContainsKey($name)) {
        throw "Unexpected or duplicate file in build artifact checksum list: $name"
    }
    $archivePath = Join-Path $artifactDirectory $name
    if (-not (Test-Path -PathType Leaf $archivePath)) {
        throw "Build artifact is missing $name"
    }
    $actualHash = (Get-FileHash $archivePath -Algorithm SHA256).Hash.ToLowerInvariant()
    if ($actualHash -ne $expectedHash) {
        throw "Build artifact checksum mismatch for $name"
    }
    $verifiedArchives[$name] = $true
}
if ($verifiedArchives.Count -ne $expectedArchives.Count) {
    throw "Build artifact checksum list is incomplete"
}

& tar.exe -xf (Join-Path $artifactDirectory "workspace.tar") -C $env:GITHUB_WORKSPACE
if ($LASTEXITCODE -ne 0) {
    throw "Unable to restore the PHP workspace"
}
& tar.exe -xf (Join-Path $artifactDirectory "runtime.tar") -C (Split-Path -Parent $env:PHP_BUILD_OBJ_DIR)
if ($LASTEXITCODE -ne 0) {
    throw "Unable to restore the PHP runtime files"
}
& tar.exe -xf (Join-Path $artifactDirectory "build-cache.tar") -C (Split-Path -Parent $env:PHP_BUILD_CACHE_BASE_DIR)
if ($LASTEXITCODE -ne 0) {
    throw "Unable to restore the PHP SDK and dependencies"
}

$buildDirectory = Join-Path $env:PHP_BUILD_OBJ_DIR "Release"
if ($env:THREAD_SAFE -eq "1") {
    $buildDirectory += "_TS"
}
$phpExecutable = Join-Path $buildDirectory "php.exe"
if (-not (Test-Path -PathType Leaf $phpExecutable)) {
    throw "Build artifact does not contain $phpExecutable"
}

& $phpExecutable -n -v
if ($LASTEXITCODE -ne 0) {
    throw "The restored PHP executable cannot run on this Windows runner"
}
