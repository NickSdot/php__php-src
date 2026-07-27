--TEST--
CLI test fork server isolates requests and continues after a nonzero exit
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows' || PHP_ZTS) {
    die('skip requires a non-ZTS build with fork');
}

$process = proc_open(
    [getenv('TEST_PHP_EXECUTABLE'), '-n', '--test-fork-server', 'TESTTOKEN'],
    [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['redirect', 1],
    ],
    $pipes,
);
fclose($pipes[0]);
stream_get_contents($pipes[1]);
fclose($pipes[1]);
if (proc_close($process) !== 0) {
    die('skip fork server is not available');
}
?>
--FILE--
<?php
$php = getenv('TEST_PHP_EXECUTABLE');
$scripts = [
    __DIR__ . '/test_fork_server_1.php' => <<<'PHP'
<?php
class TestForkServerIsolation {}
$GLOBALS['test_fork_server_global'] = true;
echo "first\n";
$stat = fstat(STDIN);
echo ($stat['mode'] & 0170000) === 0010000 ? "pipe\n" : "not pipe\n";
echo fread(STDIN, 1) === '' ? "empty\n" : "not empty\n";
PHP,
    __DIR__ . '/test_fork_server_2.php' => <<<'PHP'
<?php
class TestForkServerIsolation {}
echo isset($GLOBALS['test_fork_server_global']) ? "leaked\n" : "isolated\n";
PHP,
    __DIR__ . '/test_fork_server_3.php' => <<<'PHP'
<?php
exit(23);
PHP,
    __DIR__ . '/test_fork_server_4.php' => <<<'PHP'
<?php
echo "after\n";
PHP,
];

foreach ($scripts as $file => $code) {
    file_put_contents($file, $code);
}

$token = 'TESTTOKEN';
$process = proc_open(
    [$php, '-n', '--test-fork-server', $token],
    [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['redirect', 1],
    ],
    $pipes,
);
foreach (array_keys($scripts) as $file) {
    fwrite($pipes[0], "$file\n");
}
fclose($pipes[0]);
$output = stream_get_contents($pipes[1]);
fclose($pipes[1]);
$exitCode = proc_close($process);

foreach (array_keys($scripts) as $file) {
    unlink($file);
}

echo str_replace("\0", '|', $output);
var_dump($exitCode);
?>
--EXPECT--
|TESTTOKEN:B:0|first
pipe
empty
|TESTTOKEN:E:0:0||TESTTOKEN:B:1|isolated
|TESTTOKEN:E:1:0||TESTTOKEN:B:2||TESTTOKEN:E:2:23||TESTTOKEN:B:3|after
|TESTTOKEN:E:3:0|int(0)
