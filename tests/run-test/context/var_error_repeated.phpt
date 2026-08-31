--TEST--
Workspace variables can only be shared once
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Repeated variable sharing
    --SETUP--
    <?php
    $first = 1;
    $second = 2;
    Workspace::share('first');
    try {
        Workspace::share('second');
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
var_dump(str_contains($output, 'Workspace::share() may only be called once'));
var_dump(!str_contains($output, 'unreachable'));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
