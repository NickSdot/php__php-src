--TEST--
Phar: Bug #77022: PharData always creates new files with mode 0666
--EXTENSIONS--
phar
--SKIPIF--
<?php
if (defined("PHP_WINDOWS_VERSION_MAJOR")) die("skip not for Windows")
?>
--FILE--
<?php
umask(022);
var_dump(decoct(umask()));

$source = Workspace::file()->create(permissions: 0600);
var_dump(decoct(stat($source)['mode']));

foreach([
    Phar::TAR => Workspace::file('archive.tar'),
    Phar::ZIP => Workspace::file('archive.zip'),
] as $format => $archive) {

    $fromFile = Workspace::file();
    $fromString = Workspace::file();

    clearstatcache();
    $phar = new PharData($archive, format: $format);
    $phar->addFile($source, $fromFile->name);
    $phar->addFromString($fromString->name, 'contents');
    $phar->extractTo($archive->directory);
    var_dump(decoct(stat($fromFile)['mode']));
    var_dump(decoct(stat($fromString)['mode']));
}
?>
--EXPECT--
string(2) "22"
string(6) "100600"
string(6) "100600"
string(6) "100644"
string(6) "100600"
string(6) "100644"
