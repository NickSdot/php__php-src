--TEST--
FILE bootstrap invokes the auto_prepend_file configured by the PHPT
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = TestPath::file('nested.phpt');
$prependFile = TestPath::file('prepend.php');

file_put_contents($prependFile, <<<'PHP'
    <?php

    if (!class_exists('TestPath', false)) {
        echo "TestPath is unavailable\n";
    }

    echo "prepended\n";
    PHP);

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested auto prepend test
    --INI--
    auto_prepend_file={PWD}/prepend.php
    --FILE--
    <?php

    echo "file\n";

    ?>
    --EXPECT--
    prepended
    file
    PHPT);

[$exitCode, $output] = run_test_harness($testFile->path);

var_dump($exitCode);
var_dump(str_contains($output, 'PASS'));

?>
--EXPECT--
int(0)
bool(true)
