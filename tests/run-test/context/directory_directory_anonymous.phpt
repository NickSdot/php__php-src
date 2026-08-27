--TEST--
Directory::directory() allocates separately numbered anonymous child directory paths
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$workspace = dirname($stateFile) . DIRECTORY_SEPARATOR . $id;
$directory = TestPath::directory('fixtures');
$first = $directory->directory();
$second = $directory->directory();

var_dump(
    $first->path
    === $workspace . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'directory-1',
);
var_dump(
    $second->path
    === $workspace . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'directory-2',
);
var_dump(!file_exists($directory->path));
var_dump(!file_exists($first->path));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
