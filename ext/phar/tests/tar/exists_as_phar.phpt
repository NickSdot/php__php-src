--TEST--
Phar: phar-based phar named with ".tar" fails
--EXTENSIONS--
phar
--INI--
phar.readonly=0
phar.require_hash=0
--FILE--
<?php

$archive = TestPath::file('exists_as_phar.phar');
$tarArchive = TestPath::file('exists_as_phar.phar.tar');
$fname = $archive->path;
$tname = $tarArchive->path;
$alias = 'phar://hio';

$phar = new Phar($fname);
$phar['a.php'] = '<?php echo "This is a\n"; include "'.$alias.'/b.php"; ?>';
$phar->setAlias('hio');
$phar->addEmptyDir('test');
$phar->stopBuffering();
copy($fname, $tname);
$phar->setAlias('hio2');

try {
    $p = new Phar($tname);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECTF--
UnexpectedValueException: phar tar error: "%sexists_as_phar.phar.tar" already exists as a regular phar and must be deleted from disk prior to creating as a tar-based phar
