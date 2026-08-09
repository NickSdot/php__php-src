param(
    [Parameter(Mandatory)]
    [string] $ArtifactName,

    [Parameter(Mandatory)]
    [string] $SourceSha,

    [Parameter(Mandatory)]
    [string] $ProducerJobName
)

$ErrorActionPreference = "Stop"
$databaseSetup = Join-Path $PSScriptRoot "setup_test_databases.ps1"
$databaseJob = Start-Job -FilePath $databaseSetup

try {
    $parameters = @{
        ArtifactName = $ArtifactName
        SourceSha = $SourceSha
        ProducerJobName = $ProducerJobName
    }
    & (Join-Path $PSScriptRoot "download_build_artifact.ps1") @parameters

    $databaseWaitStopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    Wait-Job -Job $databaseJob | Out-Null
    $databaseWaitStopwatch.Stop()
    Write-Host "Database wait after restoring PHP build: $($databaseWaitStopwatch.Elapsed)"

    $databaseState = $databaseJob.State
    $databaseError = $databaseJob.ChildJobs[0].JobStateInfo.Reason
    Receive-Job -Job $databaseJob -ErrorAction Continue
    if ($databaseState -ne "Completed") {
        throw "Test database setup failed: $databaseError"
    }
} finally {
    if ($databaseJob.State -eq "Running") {
        Stop-Job -Job $databaseJob
    }
    Remove-Job -Job $databaseJob
}
