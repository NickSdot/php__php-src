--TEST--
Leaking ZipArchive destructor
--EXTENSIONS--
zip
--FILE--
<?php

class MyZipArchive extends ZipArchive {
    public function __destruct() {
        global $leak;
        $leak = $this;
    }
}

new MyZipArchive;
$file = TestPath::file();
$leak->open($file, ZIPARCHIVE::CREATE);
$leak->addFromString('test', 'test');

?>
===DONE===
--EXPECT--
===DONE===
