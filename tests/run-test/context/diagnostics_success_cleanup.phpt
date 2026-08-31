--TEST--
Test runner diagnostics - a new result removes files from the previous result
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$directory = Workspace::directory()->create();
$testFile = $directory->file('nested.phpt');
$outputFile = $directory->file('nested.out');
$scriptFile = $directory->file('nested.sh');

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested failing test
    --FILE--
    <?php echo "actual\n"; ?>
    --EXPECT--
    expected
    PHPT);

[$exitCode, $output] = run_test_harness($testFile->path);

var_dump($exitCode !== 0);
var_dump(str_contains($output, 'FAIL'));
var_dump(is_file($outputFile));
var_dump(is_file($scriptFile));

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested passing test
    --FILE--
    <?php echo "actual\n"; ?>
    --EXPECT--
    actual
    PHPT);

[$exitCode, $output] = run_test_harness($testFile->path);

var_dump($exitCode);
var_dump(str_contains($output, 'PASS'));
var_dump(is_file($outputFile));
var_dump(is_file($scriptFile));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
int(0)
bool(true)
bool(false)
bool(false)
