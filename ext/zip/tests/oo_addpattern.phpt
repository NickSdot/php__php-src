--TEST--
ZipArchive::addPattern() method
--CREDITS--
Sammy Kaye Powers <sammyk@sammykmedia.com>
w/Kenzo over the shoulder
#phptek Chicago 2014
--EXTENSIONS--
zip
--FILE--
<?php
include __DIR__ . '/utils.inc';

TestContext::useRelativeWorkspace();
$directory = TestPath::directory();
$dirname = $directory->path . '/';
$file = $directory->file('tmp.zip');
$foo = $directory->file('foo.txt');
$bar = $directory->file('bar.txt');

@mkdir($dirname);
copy(__DIR__ . '/test.zip', $file);
touch($foo);
touch($bar);

$zip = new ZipArchive();
if (!$zip->open($file)) {
        exit('failed');
}
$dir = realpath($dirname);
$options = array('add_path' => 'baz/', 'remove_path' => $dir);
if (!$zip->addPattern('/\.txt$/', $dir, $options)) {
    echo "failed 1\n";
}
$options['flags'] = 0; // clean FL_OVERWRITE
if (!$zip->addPattern('/\.txt$/', $dir, $options)) {
    var_dump($zip->getStatusString());
}
$options['flags'] = ZipArchive::FL_OVERWRITE;
if (!$zip->addPattern('/\.txt$/', $dir, $options)) {
    echo "failed 2\n";
}

if ($zip->status == ZIPARCHIVE::ER_OK) {
        if (!verify_entries($zip, [
            "bar",
            "foobar/",
            "foobar/baz",
            "entry1.txt",
            "baz/foo.txt",
            "baz/bar.txt"
        ])) {
            echo "failed\n";
        } else {
            echo "OK";
        }
        $zip->close();
} else {
        echo "failed3\n";
}
?>
--EXPECT--
string(19) "File already exists"
OK
