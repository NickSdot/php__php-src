--TEST--
Phar: Phar::extractTo() - check that phar exists
--EXTENSIONS--
phar
--INI--
phar.readonly=0
--FILE--
<?php

$fname   = __DIR__ . '/files/bogus.zip';
$fname2  = __DIR__ . '/files/notbogus.zip';
$extractDir = Workspace::directory('test-extract3');
$extract = $extractDir->path;

$phar = new PharData($fname);

try {
    $phar->extractTo($extract);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

$phar = new PharData($fname2);
foreach ($phar as $filename) {
    echo "$filename\n";
}

try {
    $phar->extractTo($extract);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECTF--
InvalidArgumentException: Invalid argument, %sfiles/bogus.zip cannot be found
phar://%sfiles/notbogus.zip%cnonsense.txt
phar://%sfiles/notbogus.zip%cstuff.txt
