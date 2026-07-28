--TEST--
Phar: SLOW TEST bug #13727: addFile() closes source streams
--EXTENSIONS--
phar
--SKIPIF--
<?php if (getenv('SKIP_SLOW_TESTS')) die('skip'); ?>
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php
$archive = __DIR__ . '/bug13727.phar.php';
$source = __DIR__ . '/bug13727.tmp';
file_put_contents($source, '');

$phar = new Phar($archive, 0, 'DataArchive.phar');
for ($i = 0; $i < 4 * 1024; $i++) {
    $phar->addFile($source, __DIR__);
}

var_dump($i);
?>
--CLEAN--
<?php
unlink(__DIR__ . '/bug13727.tmp');
unlink(__DIR__ . '/bug13727.phar.php');
?>
--EXPECT--
int(4096)
