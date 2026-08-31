--TEST--
Test TestContext state location - system storage and short process-based execution names
--FILE--
<?php

use TestSuite\Context\TestContext;

$storage = (PHP_OS_FAMILY === 'Windows' ? sys_get_temp_dir() : '/tmp')
    . DIRECTORY_SEPARATOR
    . 'php-src';

$stateFile = getenv('PHP_TEST_CONTEXT');
$id = TestContext::instance()->id;

var_dump(realpath(dirname($stateFile)) === realpath($storage));
var_dump(is_file($stateFile));
echo $id, "\n";
echo $stateFile, "\n";

?>
--EXPECTF--
bool(true)
bool(true)
state_success_location.%d
%s%ephp-src%estate_success_location.%d.%r[0-9a-f]{8}%r.state
