--TEST--
SNMP test helper caches agent availability for one test run
--EXTENSIONS--
snmp
--SKIPIF--
<?php
if (!getenv('TEST_PHP_SHARED_CACHE_DIR')) {
    die('skip shared test cache is disabled');
}
?>
--FILE--
<?php
function run_snmp_skip_check(string $code): string
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

putenv('SNMP_HOSTNAME=127.0.0.1');
putenv('SNMP_PORT=65000');
putenv('SNMP_COMMUNITY=php_test_cache');
putenv('SNMP_TIMEOUT=1');
putenv('SNMP_RETRIES=0');

$helper = var_export(__DIR__ . '/skipif.inc', true);
$code = "require $helper; echo \"available\\n\";";
$first = run_snmp_skip_check($code);

$configuration = ['127.0.0.1:65000', 'php_test_cache', '1'];
$cacheFile = getenv('TEST_PHP_SHARED_CACHE_DIR')
    . DIRECTORY_SEPARATOR
    . 'snmp-'
    . hash('sha256', serialize($configuration));
$cachedReason = file_get_contents($cacheFile);
file_put_contents($cacheFile, 'cached agent failure');

$second = run_snmp_skip_check($code);
echo "$first\n";
echo "$cachedReason\n";
echo $second;
?>
--EXPECT--
skip NO SNMPD on this host or community invalid
NO SNMPD on this host or community invalid
skip cached agent failure
