--TEST--
Test context bootstrap - SKIPIF - runner prepend is preserved without applying test INI
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');
$prependFile = Workspace::file('prepend.php');
$testPrependFile = Workspace::file('test-prepend.php');

file_put_contents($prependFile, <<<'PHP'
    <?php

    file_put_contents(Workspace::file('fixture.txt'), 'skipif');
    PHP);

file_put_contents($testPrependFile, <<<'PHP'
    <?php

    echo "file prepend\n";
    PHP);

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested SKIPIF auto prepend test
    --INI--
    auto_prepend_file={PWD}/test-prepend.php
    --SKIPIF--
    <?php

    if (file_get_contents(Workspace::file('fixture.txt')) !== 'skipif') {
        throw new Exception('Runner prepend did not create the fixture');
    }

    echo 'nocache';

    ?>
    --FILE--
    <?php

    var_dump(file_get_contents(Workspace::file('fixture.txt')));

    ?>
    --EXPECT--
    file prepend
    string(6) "skipif"
    PHPT);

[$exitCode, $output] = run_test_harness(
    $testFile->path,
    options: ['-d', "auto_prepend_file=$prependFile->path"],
);

var_dump($exitCode);
var_dump(str_contains($output, 'PASS'));

?>
--EXPECT--
int(0)
bool(true)
