--TEST--
Build changes distinguish tests from possible build inputs
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\BuildChanges;

$tests = new BuildChanges([
    'Zend/tests/example.phpt',
    'ext/example/tests/example.inc',
    'tests/example.php',
]);

var_dump($tests->onlyTests());
var_dump($tests->nonTestPaths());

$sources = new BuildChanges([
    'ext/example/example.c',
    'ext/example/example.stub.php',
    'ext/example/tests/example.phpt',
]);

var_dump($sources->onlyTests());
var_dump($sources->nonTestPaths());
var_dump((new BuildChanges([]))->onlyTests());
?>
--EXPECT--
bool(true)
array(0) {
}
bool(false)
array(2) {
  [0]=>
  string(21) "ext/example/example.c"
  [1]=>
  string(28) "ext/example/example.stub.php"
}
bool(true)
