--TEST--
Runner assigns each TestContext state file to the .suite directory
--FILE--
<?php

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = basename($stateFile, '.state');
$expected = getcwd()
    . DIRECTORY_SEPARATOR
    . '.suite'
    . DIRECTORY_SEPARATOR
    . $id
    . '.state';

var_dump($stateFile === $expected);
var_dump(str_starts_with($id, 'state_location.phpt.h'));
var_dump(TestContext::instance()->id === $id);

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
