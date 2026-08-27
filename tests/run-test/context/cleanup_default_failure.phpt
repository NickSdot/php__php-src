--TEST--
RunnerContext removes the default workspace and state after a test failure
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = TestPath::file('nested.phpt');
$workspaceRecord = TestPath::file('workspace.txt');
$stateRecord = TestPath::file('state.txt');

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested default workspace failure
    --FILE--
    <?php

    TestPath::file();

    $directory = TestPath::directory('fixtures')->create();
    $fixture = $directory->file('fixture.txt');
    file_put_contents($fixture, 'contents');

    file_put_contents(
        getenv('PHP_TEST_WORKSPACE_RECORD'),
        dirname($directory->path),
    );
    file_put_contents(
        getenv('PHP_TEST_STATE_RECORD'),
        getenv('PHP_TEST_CONTEXT'),
    );

    chmod($fixture, 0444);
    chmod($directory, 0555);

    trigger_error('test process failure', E_USER_ERROR);

    ?>
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
var_dump(str_contains($output, 'FAIL'));
var_dump(!is_dir($workspace));
var_dump(!is_file($stateFile));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
