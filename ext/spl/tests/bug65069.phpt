--TEST--
Bug #65069: GlobIterator fails to access files inside an open_basedir restricted dir
--FILE--
<?php
$baseDirectory = TestPath::directory()->create();
// temp dirname used here
$childDir = $baseDirectory->directory()->create();

ini_set('open_basedir', $childDir->path);

// temp files created
$childDir->file('wonder12345')->create();
$childDir->file('wonder.txt')->create();
$childDir->file('file.text')->create();

$spl_glob_it = new \GlobIterator("$childDir/*.txt");
foreach ($spl_glob_it as $file_info) {
    echo $file_info->getFilename() . "\n";
}

$spl_glob_it = new \GlobIterator(dirname(dirname($childDir)) . "/*/*/*");
foreach ($spl_glob_it as $file_info) {
    echo $file_info->getFilename() . "\n";
}

$spl_glob_empty = new \GlobIterator("$childDir/*.php");
var_dump($spl_glob_empty->count());

// top directory
var_dump(iterator_to_array(new \GlobIterator(dirname(dirname($childDir)))));

// not existing file
var_dump(iterator_to_array(new \GlobIterator($baseDirectory->file('this-will-never-exists'))));


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
