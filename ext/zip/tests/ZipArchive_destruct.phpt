--TEST--
Leaking ZipArchive destructor
--EXTENSIONS--
zip
--FILE--
<?php
$file = TestPath::file('archive.zip');

class MyZipArchive extends ZipArchive {
    public function __destruct() {
        global $leak;
        $leak = $this;
    }
}

new MyZipArchive;
$leak->open($file, ZIPARCHIVE::CREATE);
$leak->addFromString('test', 'test');

?>
===DONE===
--EXPECT--
===DONE===
