--TEST--
PDO test helper caches connection failures for one test run
--EXTENSIONS--
pdo
--SKIPIF--
<?php
if (!getenv('TEST_PHP_SHARED_CACHE_DIR')) {
    die('skip shared test cache is disabled');
}
?>
--FILE--
<?php
function run_pdo_skip_check(string $code): string
{
    $command = getenv('TEST_PHP_EXECUTABLE_ESCAPED')
        . ' '
        . getenv('TEST_PHP_EXTRA_ARGS')
        . ' -r '
        . escapeshellarg($code);
    $process = proc_open(
        $command,
        [
            1 => ['pipe', 'w'],
            2 => ['redirect', 1],
        ],
        $pipes,
    );
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($process);
    return $output;
}

$dsn = 'missing_' . getmypid() . ':';
putenv("PDOTEST_DSN=$dsn");
putenv('PDOTEST_USER=test');
putenv('PDOTEST_PASS=test');
putenv('PDOTEST_ATTR');

$helperDirectory = getenv('REDIR_TEST_DIR') ?: __DIR__;
$helper = var_export($helperDirectory . '/pdo_test.inc', true);
$code = "require $helper; PDOTest::skip();";
$first = run_pdo_skip_check($code);

$configuration = [$dsn, 'test', 'test', false, getenv('PDOTEST_SKIP_ATTR')];
$cacheFile = getenv('TEST_PHP_SHARED_CACHE_DIR')
    . DIRECTORY_SEPARATOR
    . 'pdo-'
    . hash('sha256', serialize($configuration));
$cachedReason = file_get_contents($cacheFile);
file_put_contents($cacheFile, 'cached connection failure');

$second = run_pdo_skip_check($code);
echo "$first\n$cachedReason\n$second\n";
?>
--EXPECT--
skip could not find driver
could not find driver
skip cached connection failure
