--TEST--
Directory::file() allocates separately numbered anonymous child file paths
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.context');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$directory = TestPath::directory('fixtures');
$first = $directory->file();
$second = $directory->file();

var_dump(
    $first->path
    === $workspace . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'file-1',
);
var_dump(
    $second->path
    === $workspace . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'file-2',
);
var_dump(!file_exists($directory->path));
var_dump(!file_exists($first->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
