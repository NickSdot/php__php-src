--TEST--
Bug #65069: GlobIterator fails to access files inside an open_basedir restricted dir
--FILE--
<?php
TestContext::useRelativeWorkspace();
$file_path = __DIR__;
// temp dirname used here
$directory = TestPath::directory('bug65069');
$dirname = $directory->path;
// temp dir created
mkdir($dirname);

ini_set('open_basedir', $dirname);

// temp files created
$wonder12345 = $directory->file('wonder12345');
$wonder = $directory->file('wonder.txt');
$file = $directory->file('file.text');
touch($wonder12345->path);
touch($wonder->path);
touch($file->path);

$spl_glob_it = new \GlobIterator("$dirname/*.txt");
foreach ($spl_glob_it as $file_info) {
    echo $file_info->getFilename() . "\n";
}

$spl_glob_it = new \GlobIterator(dirname(dirname($dirname)) . "/*/*/*");
foreach ($spl_glob_it as $file_info) {
    echo $file_info->getFilename() . "\n";
}

$spl_glob_empty = new \GlobIterator("$dirname/*.php");
var_dump($spl_glob_empty->count());

// top directory
var_dump(iterator_to_array(new \GlobIterator(dirname(dirname($dirname)))));

// not existing file
var_dump(iterator_to_array(new \GlobIterator("$file_path/bug65069-this-will-never-exists")));


?>
--EXPECT--
wonder.txt
file.text
wonder.txt
wonder12345
int(0)
array(0) {
}
array(0) {
}
