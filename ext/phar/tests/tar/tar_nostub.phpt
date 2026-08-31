--TEST--
Phar: tar-based phar, third-party tar with no stub, Phar->getStub()
--EXTENSIONS--
phar
--INI--
phar.readonly=1
phar.require_hash=0
--FILE--
<?php
include __DIR__ . '/files/tarmaker.php.inc';
$archive = Workspace::file('tar_nostub.phar.tar');
$fname = $archive->path;
$alias = 'phar://' . $fname;
$archive2 = Workspace::file('tar_nostub.tar');
$fname2 = $archive2->path;

$tar = new tarmaker($fname, 'none');
$tar->init();
$tar->addFile('tar_004.php', '<?php var_dump(__FILE__);');
$tar->addFile('internal/file/here', "hi there!\n");
$tar->close();

try {
    $phar = new Phar($fname);
    var_dump($phar->getStub());
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

copy($fname, $fname2);

try {
    $phar = new PharData($fname2);
    var_dump($phar->getStub());
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECTF--
UnexpectedValueException: RecursiveDirectoryIterator::__construct(): Failed to open directory: '%star_nostub.phar.tar' is not a phar archive. Use PharData::__construct() for a standard zip or tar archive
phar url "phar://%star_nostub.phar.tar/" is unknown
string(0) ""
