--TEST--
Shared test probe cache only caches failures for the same configuration
--FILE--
<?php
require __DIR__ . '/test_probe_cache.inc';

$cacheDirectory = __DIR__ . '/test_probe_cache_dir_' . getmypid();
mkdir($cacheDirectory);
putenv("TEST_PHP_SHARED_CACHE_DIR=$cacheDirectory");

$failureCalls = 0;
$failureProbe = static function () use (&$failureCalls): ?string {
    $failureCalls++;
    return "failure $failureCalls";
};

var_dump(run_test_cache_probe_failure('service', ['first'], $failureProbe));
var_dump(run_test_cache_probe_failure('service', ['first'], $failureProbe));
var_dump(run_test_cache_probe_failure('service', ['second'], $failureProbe));
var_dump($failureCalls);

$successCalls = 0;
$successProbe = static function () use (&$successCalls): ?string {
    $successCalls++;
    return null;
};

var_dump(run_test_cache_probe_failure('service', ['available'], $successProbe));
var_dump(run_test_cache_probe_failure('service', ['available'], $successProbe));
var_dump($successCalls);

putenv('TEST_PHP_SHARED_CACHE_DIR');
var_dump(run_test_cache_probe_failure('service', ['uncached'], static fn(): ?string => 'uncached failure'));
?>
--CLEAN--
<?php
foreach (glob(__DIR__ . '/test_probe_cache_dir_*') ?: [] as $directory) {
    foreach (glob($directory . '/*') ?: [] as $file) {
        @unlink($file);
    }
    @rmdir($directory);
}
?>
--EXPECT--
string(9) "failure 1"
string(9) "failure 1"
string(9) "failure 2"
int(2)
NULL
NULL
int(2)
string(16) "uncached failure"
