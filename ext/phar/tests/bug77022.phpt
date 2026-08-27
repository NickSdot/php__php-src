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

$source = TestPath::touch(permissions: 0600);
var_dump(decoct(stat($source)['mode']));

foreach([
    Phar::TAR => TestPath::file('archive.tar'),
    Phar::ZIP => TestPath::file('archive.zip'),
] as $format => $archive) {

    $fromFile = TestPath::file();
    $fromString = TestPath::file();

    clearstatcache();
    $phar = new PharData($archive->path, format: $format);
    $phar->addFile($source->path, $fromFile->name);
    $phar->addFromString($fromString->name, 'contents');
    $phar->extractTo($archive->directory);
    var_dump(decoct(stat($fromFile->path)['mode']));
    var_dump(decoct(stat($fromString->path)['mode']));
}
?>
--EXPECT--
string(2) "22"
string(6) "100600"
string(6) "100600"
string(6) "100644"
string(6) "100600"
string(6) "100644"
