--TEST--
Phar: tar with bad checksum
--EXTENSIONS--
phar
--FILE--
<?php
$fname = Workspace::file('badchecksum.tar')->path;
$pname = 'phar://' . $fname;

include __DIR__ . '/files/corrupt_tarmaker.php.inc';
$a = new corrupt_tarmaker($fname, 'none');
$a->init();
$a->addFile('hithere', 'contents', null, 'checksum');
$a->close();

try {
    $p = new PharData($fname);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECTF--
UnexpectedValueException: phar error: "%sbadchecksum.tar" is a corrupted tar file (checksum mismatch of file "hithere")
