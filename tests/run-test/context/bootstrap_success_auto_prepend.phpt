--TEST--
Test context bootstrap - FILE - configured auto_prepend_file runs after Workspace is available
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');
$prependFile = Workspace::file('prepend.php');

file_put_contents($prependFile, <<<'PHP'
    <?php

    if (!class_exists('Workspace', false)) {
        echo "Workspace is unavailable\n";
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
