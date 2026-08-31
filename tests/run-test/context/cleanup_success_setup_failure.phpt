--TEST--
Test RunnerContext cleanup - SETUP failure - managed files and state are removed
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');
$workspaceRecord = Workspace::file();
$stateRecord = Workspace::file();

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested SETUP failure
    --SETUP--
    <?php

    use TestSuite\Context\TestContext;

    file_put_contents(Workspace::file('fixture.txt'), 'setup');
    file_put_contents(getenv('PHP_TEST_WORKSPACE_RECORD'), Workspace::path());
    file_put_contents(getenv('PHP_TEST_STATE_RECORD'), TestContext::instance()->stateFile);

    trigger_error('setup failure', E_USER_ERROR);

    ?>
    --FILE--
    <?php echo "unreachable\n"; ?>
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

var_dump($exitCode !== 0);
var_dump(str_contains($output, 'BORK'));
var_dump(!is_dir($workspace));
var_dump(!is_file($stateFile));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
