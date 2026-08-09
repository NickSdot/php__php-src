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
if (-not (Get-Command gh -ErrorAction SilentlyContinue)) {
    throw "GitHub CLI is not available on this runner"
}

function Invoke-GitHubApi {
    param([Parameter(Mandatory)][string] $Endpoint)

    $output = & gh api $Endpoint
    if ($LASTEXITCODE -ne 0) {
        throw "GitHub API request failed for $Endpoint"
    }
    return ($output -join "`n") | ConvertFrom-Json
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

    Start-Sleep -Seconds 5
    $pollCount++
}

if ($null -eq $artifact) {
    throw "Timed out waiting for build artifact $ArtifactName"
}
if ($artifact.workflow_run.head_sha -ne $SourceSha) {
    throw "Build artifact source mismatch: expected $SourceSha, got $($artifact.workflow_run.head_sha)"
}

$artifactDirectory = Join-Path $env:RUNNER_TEMP "php-build-artifact"
if (Test-Path $artifactDirectory) {
    throw "Artifact directory already exists: $artifactDirectory"
}

& gh run download $env:GITHUB_RUN_ID `
    --repo $env:GITHUB_REPOSITORY `
    --name $ArtifactName `
    --dir $artifactDirectory
if ($LASTEXITCODE -ne 0) {
    throw "Unable to download build artifact $ArtifactName"
}

& (Join-Path $PSScriptRoot "restore_build_artifact.ps1") -SourceSha $SourceSha
$stopwatch.Stop()
Write-Host "Build artifact wait, download, and restore duration: $($stopwatch.Elapsed)"
Write-Host ("Compressed artifact size: {0:N1} MiB" -f ($artifact.size_in_bytes / 1MB))
