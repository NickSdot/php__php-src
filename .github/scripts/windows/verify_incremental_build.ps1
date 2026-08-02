param(
    [string] $Compiler = ""
)

$ErrorActionPreference = "Stop"

function Get-ObjectState {
    $state = @{}
    Get-ChildItem -Path $env:PHP_BUILD_OBJ_DIR -Filter *.obj -File -Recurse |
        ForEach-Object {
            $state[$_.FullName] = "$($_.Length):$($_.LastWriteTimeUtc.Ticks)"
        }
    return $state
}

$before = Get-ObjectState
if ($before.Count -eq 0) {
    throw "The completed build did not produce any object files"
}

$arguments = @("/NOLOGO")
if ($Compiler) {
    $arguments += "CC=$Compiler"
}

& jom @arguments
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

$after = Get-ObjectState
$changed = @(
    $before.Keys | Where-Object {
        -not $after.ContainsKey($_) -or $after[$_] -ne $before[$_]
    }
    $after.Keys | Where-Object { -not $before.ContainsKey($_) }
)

if ($changed.Count -ne 0) {
    $changed | Sort-Object | ForEach-Object { Write-Host "Unexpected rebuild: $_" }
    throw "The second jom invocation rebuilt $($changed.Count) object file(s)"
}

Write-Host "Incremental jom build is up to date ($($after.Count) objects unchanged)"
