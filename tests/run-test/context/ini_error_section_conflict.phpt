--TEST--
Workspace INI settings conflict with the INI section
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Conflicting INI configuration
    --SETUP--
    <?php Workspace::ini(['memory_limit' => '-1']); ?>
    --INI--
    memory_limit=128M
    --FILE--
    <?php echo "unreachable\n"; ?>
    --EXPECT--
    unreachable
    PHPT);

[$exitCode, $output] = run_test_harness($testFile->path);

var_dump($exitCode !== 0);
var_dump(str_contains($output, 'ERROR'));
var_dump(str_contains($output, 'Workspace::ini() cannot be used together with --INI--'));
var_dump(!str_contains($output, 'unreachable'));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
