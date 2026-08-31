--TEST--
ZipArchive::addGlob() method
--CREDITS--
Sammy Kaye Powers <sammyk@sammykmedia.com>
w/Kenzo over the shoulder
#phptek Chicago 2014
--EXTENSIONS--
zip
--SKIPIF--
<?php
if(!defined("GLOB_BRACE")) die ('skip');
?>
--FILE--
<?php
include __DIR__ . '/utils.inc';
$directory = Workspace::directory()->create();
$dirname = $directory->path . '/';
$file = $directory->file('tmp.zip');

copy(__DIR__ . '/test.zip', $file);
$directory->file('foo.txt')->create();
$directory->file('bar.baz')->create();

$zip = new ZipArchive();
if (!$zip->open($file)) {
        exit('failed');
}
$options = array('add_path' => 'baz/', 'remove_all_path' => TRUE);
if (!$zip->addGlob($dirname . '*.{txt,baz}', GLOB_BRACE, $options)) {
    echo "failed 1\n";
}
if (!$zip->addGlob($dirname . '*.{txt,baz}', GLOB_BRACE, $options)) {
    echo "failed 2\n";
}
$options['flags'] = 0; // clean FL_OVERWRITE
if (!$zip->addGlob($dirname . '*.{txt,baz}', GLOB_BRACE, $options)) {
    var_dump($zip->getStatusString());
}
$options['flags'] = ZipArchive::FL_OVERWRITE;
if (!$zip->addGlob($dirname . '*.{txt,baz}', GLOB_BRACE, $options)) {
    echo "failed 3\n";
}
if ($zip->status == ZIPARCHIVE::ER_OK) {
        if (!verify_entries($zip, [
            "bar",
            "foobar/",
            "foobar/baz",
            "entry1.txt",
            "baz/foo.txt",
            "baz/bar.baz"
        ])) {
            echo "failed\n";
        } else {
            echo "OK";
        }
        $zip->close();
} else {
        echo "failed 4\n";
}
?>
--EXPECT--
string(19) "File already exists"
OK
