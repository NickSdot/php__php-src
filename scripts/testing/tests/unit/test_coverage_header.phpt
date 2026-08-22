--TEST--
Test coverage header formats single and multiple selections
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\TestCoverageHeader;
use PHP\Testing\TestCoverageOptions;

function render(array $sources = [], array $tests = [], bool $global = false): void
{
    $options = new TestCoverageOptions('master', null, $sources, $tests, $global, 10, false);
    $header = new TestCoverageHeader($options, 'base-revision', 'tree-revision');

    echo implode("\n", $header->lines()), "\n";
}

render(
    sources: ['ext/standard/scanf.c'],
);

render(
    tests: ['ext/uri/tests'],
);

render(
    sources: ['ext/standard/scanf.c'],
    tests: ['ext/standard/tests/file', 'ext/standard/tests/strings']
);

render(
    global: true,
);
?>
--EXPECT--
Coverage: ext/standard/scanf.c

Base: base-revision master
Tree: tree-revision working tree

Coverage: ext/uri/tests

Base: base-revision master
Tree: tree-revision working tree

Coverage:
  ext/standard/scanf.c
  ext/standard/tests/file, ext/standard/tests/strings

Base: base-revision master
Tree: tree-revision working tree

Coverage: global

Base: base-revision master
Tree: tree-revision working tree
