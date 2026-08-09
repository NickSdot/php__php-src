param(
    [Parameter(Mandatory)]
    [string] $ArtifactName,

    [Parameter(Mandatory)]
    [string] $SourceSha,

    [Parameter(Mandatory)]
    [string] $ProducerJobName,

    [ValidateRange(60, 1800)]
    [int] $TimeoutSeconds = 900
)

$ErrorActionPreference = "Stop"

if ($ArtifactName -notmatch '^[A-Za-z0-9._-]+$') {
    throw "Invalid build artifact name: $ArtifactName"
}
if ($SourceSha -notmatch '^[0-9a-f]{40}$') {
    throw "Invalid source commit: $SourceSha"
}
if ($ProducerJobName -notmatch '^[A-Za-z0-9._-]+$') {
    throw "Invalid producer job name: $ProducerJobName"
}
if ($env:GITHUB_REPOSITORY -notmatch '^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$') {
    throw "Invalid GitHub repository: $env:GITHUB_REPOSITORY"
}
if ($env:GITHUB_RUN_ID -notmatch '^[0-9]+$') {
    throw "Invalid GitHub workflow run ID: $env:GITHUB_RUN_ID"
}
if ([string]::IsNullOrWhiteSpace($env:GH_TOKEN)) {
    throw "GH_TOKEN is required to download the build artifact"
}
$headers = @{
    Authorization = "Bearer $($env:GH_TOKEN)"
    Accept = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
}

function Invoke-GitHubApi {
    param([Parameter(Mandatory)][string] $Endpoint)

    try {
        return Invoke-RestMethod `
            -Uri "https://api.github.com/$Endpoint" `
            -Headers $headers `
            -TimeoutSec 30
    } catch {
        Write-Warning "GitHub API request failed and will be retried: $($_.Exception.Message)"
        return $null
    }
}

$encodedArtifactName = [Uri]::EscapeDataString($ArtifactName)
$artifactListEndpoint = "repos/$($env:GITHUB_REPOSITORY)/actions/runs/$($env:GITHUB_RUN_ID)/artifacts?per_page=100&name=$encodedArtifactName"
$jobListEndpoint = "repos/$($env:GITHUB_REPOSITORY)/actions/runs/$($env:GITHUB_RUN_ID)/jobs?filter=all&per_page=100"
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
$pollCount = 0
$artifact = $null

Write-Host "Waiting up to $TimeoutSeconds seconds for $ArtifactName from this workflow run"
while ($stopwatch.Elapsed.TotalSeconds -lt $TimeoutSeconds) {
    $artifacts = Invoke-GitHubApi $artifactListEndpoint
    if ($null -eq $artifacts) {
        Start-Sleep -Seconds 5
        $pollCount++
        continue
    }
    $matchingArtifacts = @($artifacts.artifacts | Where-Object {
        $_.name -eq $ArtifactName -and -not $_.expired
    })
    if ($matchingArtifacts.Count -gt 1) {
        throw "Multiple artifacts named $ArtifactName exist in this workflow run"
    }
    if ($matchingArtifacts.Count -eq 1) {
        $artifact = $matchingArtifacts[0]
        break
    }

    if ($pollCount % 3 -eq 0) {
        $jobs = Invoke-GitHubApi $jobListEndpoint
        if ($null -ne $jobs) {
            $producer = @($jobs.jobs | Where-Object {
                $_.name -eq $ProducerJobName -or $_.name.EndsWith(" / $ProducerJobName")
            })
            if ($producer.Count -gt 1) {
                throw "Multiple jobs match build producer $ProducerJobName"
            }
            if ($producer.Count -eq 1 -and $producer[0].status -eq "completed" -and $producer[0].conclusion -ne "success") {
                throw "Build producer $ProducerJobName finished with $($producer[0].conclusion)"
            }
        }
    }

    Start-Sleep -Seconds 5
    $pollCount++
}

if ($null -eq $artifact) {
    throw "Timed out waiting for build artifact $ArtifactName"
}
if ($artifact.workflow_run.head_sha -ne $SourceSha) {
    throw "Build artifact source mismatch: expected $SourceSha, got $($artifact.workflow_run.head_sha)"
}
if ("$($artifact.id)" -notmatch '^[0-9]+$') {
    throw "Invalid build artifact ID: $($artifact.id)"
}

$artifactDirectory = Join-Path $env:RUNNER_TEMP "php-build-artifact"
$artifactArchive = Join-Path $env:RUNNER_TEMP "php-build-artifact.zip"
if ((Test-Path $artifactDirectory) -or (Test-Path $artifactArchive)) {
    throw "Build artifact destination already exists"
}

$downloadUri = "https://api.github.com/repos/$($env:GITHUB_REPOSITORY)/actions/artifacts/$($artifact.id)/zip"
$downloaded = $false
for ($attempt = 1; $attempt -le 3; $attempt++) {
    try {
        Invoke-WebRequest `
            -Uri $downloadUri `
            -Headers $headers `
            -OutFile $artifactArchive `
            -TimeoutSec 120
        $downloaded = $true
        break
    } catch {
        Remove-Item -ErrorAction SilentlyContinue $artifactArchive
        Write-Warning "Build artifact download attempt $attempt failed: $($_.Exception.Message)"
        if ($attempt -lt 3) {
            Start-Sleep -Seconds 5
        }
    }
}
if (-not $downloaded) {
    throw "Unable to download build artifact $ArtifactName after 3 attempts"
}
New-Item -ItemType Directory -Path $artifactDirectory | Out-Null
Expand-Archive -Path $artifactArchive -DestinationPath $artifactDirectory
Remove-Item $artifactArchive

& (Join-Path $PSScriptRoot "restore_build_artifact.ps1") -SourceSha $SourceSha
$stopwatch.Stop()
Write-Host "Build artifact wait, download, and restore duration: $($stopwatch.Elapsed)"
Write-Host ("Compressed artifact size: {0:N1} MiB" -f ($artifact.size_in_bytes / 1MB))
