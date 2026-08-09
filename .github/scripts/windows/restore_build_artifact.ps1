$ErrorActionPreference = "Stop"

$artifactDirectory = Join-Path $env:RUNNER_TEMP "php-build-artifact"
$expectedSha = $env:PHP_BUILD_SOURCE_SHA
if ($expectedSha -notmatch '^[0-9a-f]{40}$') {
    throw "Invalid source commit: $expectedSha"
}
$artifactSha = (Get-Content (Join-Path $artifactDirectory "source-sha") -Raw).Trim()
if ($artifactSha -notmatch '^[0-9a-f]{40}$' -or $artifactSha -ne $expectedSha) {
    throw "Build artifact source mismatch: expected $expectedSha, got $artifactSha"
}

foreach ($line in Get-Content (Join-Path $artifactDirectory "SHA256SUMS")) {
    $expectedHash, $name = $line -split '  ', 2
    if ($name -notin @("workspace.tar", "objects.tar", "build-cache.tar")) {
        throw "Unexpected file in build artifact checksum list: $name"
    }
    $actualHash = (Get-FileHash (Join-Path $artifactDirectory $name) -Algorithm SHA256).Hash
    if ($actualHash -ne $expectedHash) {
        throw "Build artifact checksum mismatch for $name"
    }
}

& tar.exe -xf (Join-Path $artifactDirectory "workspace.tar") -C $env:GITHUB_WORKSPACE
if ($LASTEXITCODE -ne 0) {
    throw "Unable to restore the PHP workspace"
}
& tar.exe -xf (Join-Path $artifactDirectory "objects.tar") -C C:\
if ($LASTEXITCODE -ne 0) {
    throw "Unable to restore the PHP object directory"
}
& tar.exe -xf (Join-Path $artifactDirectory "build-cache.tar") -C C:\
if ($LASTEXITCODE -ne 0) {
    throw "Unable to restore the PHP SDK and dependencies"
}

$buildDirectory = Join-Path $env:PHP_BUILD_OBJ_DIR "Release"
if ($env:THREAD_SAFE -eq "1") {
    $buildDirectory += "_TS"
}
if (-not (Test-Path (Join-Path $buildDirectory "php.exe"))) {
    throw "Build artifact does not contain $buildDirectory\php.exe"
}

& (Join-Path $buildDirectory "php.exe") -n -v
if ($LASTEXITCODE -ne 0) {
    throw "The restored PHP executable cannot run on this Windows runner"
}
