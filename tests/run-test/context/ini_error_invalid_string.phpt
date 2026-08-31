--TEST--
Workspace INI settings reject malformed strings
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Malformed INI configuration
    --SETUP--
    <?php
    try {
        Workspace::ini('[');
    } catch (Throwable) {
    }
    ?>
    --FILE--
    <?php echo "unreachable\n"; ?>
    --EXPECT--
    unreachable
    PHPT);

[$exitCode, $output] = run_test_harness($testFile->path);

var_dump($exitCode !== 0);
var_dump(str_contains($output, 'ERROR'));
var_dump(str_contains($output, 'Invalid INI settings'));
var_dump(!str_contains($output, 'unreachable'));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
