--TEST--
Test coverage command argument errors
--FILE--
<?php
function run(array $arguments): void
{
    $repo = dirname(__DIR__, 4);
    $command = [PHP_BINARY, $repo . '/scripts/testing/validate_test_coverage.php', ...$arguments];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    var_dump(proc_close($process));
    echo $output;
}

run(['--base']);
run(['--tree']);
run(['--wat', 'value']);
run(['--wat']);
run(['--global', '--source', 'ext/uri']);
run(['--global=yes']);
?>
--EXPECT--
int(1)
Error: --base requires value
int(1)
Error: --tree requires value
int(1)
Error: Unknown option: --wat
int(1)
Error: Unknown option: --wat
int(1)
Error: --global cannot be combined with --source
int(1)
Error: --global does not take value
