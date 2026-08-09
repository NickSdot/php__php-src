--TEST--
Gcov coverage parses exact line and branch outcomes
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\GcovParser;
use PHP\Testing\SourceCoverage;

$repo = dirname(__DIR__, 4);

$coverage = (new GcovParser('/build', $repo))->parse(<<<GCOV
        -:    0:Source:$repo/ext/standard/string.c
        3:   10:first();
branch  0 taken 2
    #####:   11:second();
branch  1 never executed
        -:   12:comment
        -:    0:Source:$repo/Zend/zend_types.h
        1:    1:ignored();
        -:    0:Source:/usr/include/stdio.h
        1:    1:external();
GCOV);

$source = $coverage->find('ext/standard/string.c');
$header = $coverage->find('Zend/zend_types.h');

assert($source instanceof SourceCoverage);
assert($header instanceof SourceCoverage);

var_dump($coverage->paths());
var_dump(array_keys($source->coveredLines()));
var_dump(array_keys($source->executableLines()));
var_dump(array_keys($source->coveredBranches()));
var_dump(array_keys($source->executableBranches()));
var_dump(array_keys($header->coveredLines()));
?>
--EXPECT--
array(2) {
  [0]=>
  string(17) "Zend/zend_types.h"
  [1]=>
  string(21) "ext/standard/string.c"
}
array(1) {
  [0]=>
  int(10)
}
array(2) {
  [0]=>
  int(10)
  [1]=>
  int(11)
}
array(1) {
  [0]=>
  string(4) "10:0"
}
array(2) {
  [0]=>
  string(4) "10:0"
  [1]=>
  string(4) "11:1"
}
array(1) {
  [0]=>
  int(1)
}
