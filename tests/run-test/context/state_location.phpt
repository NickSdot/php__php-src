--TEST--
Runner assigns each TestContext state file to the .suite directory
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.context');
$expected = getcwd()
    . DIRECTORY_SEPARATOR
    . '.suite'
    . DIRECTORY_SEPARATOR
    . $id
    . '.context';

var_dump($stateFile === $expected);
var_dump(str_starts_with($id, 'state_location.phpt.r'));

?>
--EXPECT--
bool(true)
bool(true)
