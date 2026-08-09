$ErrorActionPreference = "Stop"

$artifactDirectory = Join-Path $env:RUNNER_TEMP "php-build-artifact"
if (Test-Path $artifactDirectory) {
    throw "Artifact directory already exists: $artifactDirectory"
}
New-Item -ItemType Directory -Path $artifactDirectory | Out-Null

$sourceSha = (& git -C $env:GITHUB_WORKSPACE rev-parse HEAD).Trim()
if ($LASTEXITCODE -ne 0) {
    throw "Unable to determine the checked-out source commit"
}
Set-Content -Path (Join-Path $artifactDirectory "source-sha") -Value $sourceSha -Encoding ascii

& tar.exe -cf (Join-Path $artifactDirectory "workspace.tar") `
    --exclude=.git -C $env:GITHUB_WORKSPACE .
if ($LASTEXITCODE -ne 0) {
    throw "Unable to package the PHP workspace"
}

& tar.exe -cf (Join-Path $artifactDirectory "objects.tar") -C C:\ obj
if ($LASTEXITCODE -ne 0) {
    throw "Unable to package the PHP object directory"
}

& tar.exe -cf (Join-Path $artifactDirectory "build-cache.tar") `
    --exclude=build-cache/sdk/.git -C C:\ build-cache
if ($LASTEXITCODE -ne 0) {
    throw "Unable to package the PHP SDK and dependencies"
}

$checksums = foreach ($name in @("workspace.tar", "objects.tar", "build-cache.tar")) {
    $hash = (Get-FileHash (Join-Path $artifactDirectory $name) -Algorithm SHA256).Hash
    "$hash  $name"
}
Set-Content -Path (Join-Path $artifactDirectory "SHA256SUMS") -Value $checksums -Encoding ascii
