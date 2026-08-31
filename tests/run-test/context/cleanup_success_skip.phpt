--TEST--
Test RunnerContext cleanup - skipped test - SKIPIF fixtures and state are removed
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');
$workspaceRecord = Workspace::file();
$stateRecord = Workspace::file();

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested skipped test
    --SKIPIF--
    <?php

    $file = Workspace::file()->create();
    file_put_contents(getenv('PHP_TEST_WORKSPACE_RECORD'), $file->directory);
    file_put_contents(getenv('PHP_TEST_STATE_RECORD'), getenv('PHP_TEST_CONTEXT'));

    die('skip fixture created');

    ?>
    --FILE--
    <?php echo 'unreachable'; ?>
    --EXPECT--
    unreachable
    PHPT);

[$exitCode, $output] = run_test_harness(
    $testFile->path,
    environment: [
        'PHP_TEST_WORKSPACE_RECORD' => $workspaceRecord->path,
        'PHP_TEST_STATE_RECORD' => $stateRecord->path,
    ],
);

$workspace = file_get_contents($workspaceRecord);
$stateFile = file_get_contents($stateRecord);

var_dump($exitCode);
var_dump(str_contains($output, 'SKIP'));
var_dump(!is_dir($workspace));
var_dump(!is_file($stateFile));

?>
--EXPECT--
int(0)
bool(true)
bool(true)
bool(true)
