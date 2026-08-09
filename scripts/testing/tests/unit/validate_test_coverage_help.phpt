--TEST--
Test coverage command help
--FILE--
<?php
$repo = dirname(__DIR__, 4);

$command = [PHP_BINARY, $repo . '/scripts/testing/validate_test_coverage.php', '--help'];
$process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

fclose($pipes[1]);
fclose($pipes[2]);

var_dump(proc_close($process));
var_dump(str_starts_with($stdout, 'Usage:'));
var_dump(str_contains($stdout, '--global'));
var_dump(str_contains($stdout, 'Limit source scope'));
var_dump($stderr);
?>
--EXPECT--
int(0)
bool(true)
bool(true)
bool(true)
string(0) ""
