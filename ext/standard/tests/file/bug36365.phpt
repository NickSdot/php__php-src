--TEST--
Bug #36365 (scandir duplicates file name at every 65535th file)
--SKIPIF--
<?php
if (PHP_OS_FAMILY !== 'Windows') die('skip Windows only');
if (getenv("SKIP_SLOW_TESTS")) die('skip slow test');
?>
--FILE--
<?php
$testdir = TestPath::directory();

mkdir($testdir);
for ($i = 0; $i < 70000; $i++) {
    touch(sprintf("$testdir/%05d.txt", $i));
}

var_dump(count(scandir($testdir)));
?>
--EXPECT--
int(70002)
