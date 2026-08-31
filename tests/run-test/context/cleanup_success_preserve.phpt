--TEST--
Test RunnerContext cleanup - --no-clean preserves fixture contents and state
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');
$workspaceRecord = Workspace::file('workspace.txt');
$stateRecord = Workspace::file('state.txt');

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested preserved context
    --FILE--
    <?php

    $file = Workspace::file('fixture.txt');
    file_put_contents($file, 'preserved');

    file_put_contents(
        getenv('PHP_TEST_WORKSPACE_RECORD'),
        $file->directory,
    );
    file_put_contents(
        getenv('PHP_TEST_STATE_RECORD'),
        getenv('PHP_TEST_CONTEXT'),
    );

    echo "ok\n";

    ?>
    --EXPECT--
    ok
    PHPT);

[$exitCode, $output] = run_test_harness(
    $testFile->path,
    options: ['--no-clean'],
    environment: [
        'PHP_TEST_WORKSPACE_RECORD' => $workspaceRecord->path,
        'PHP_TEST_STATE_RECORD' => $stateRecord->path,
    ],
);

$workspace = file_get_contents($workspaceRecord);
$stateFile = file_get_contents($stateRecord);

var_dump($exitCode);
var_dump(str_contains($output, 'PASS'));
var_dump(is_dir($workspace));
var_dump(is_file($stateFile));
var_dump(file_get_contents($workspace . '/fixture.txt'));

unlink($workspace . '/fixture.txt');
rmdir($workspace);
unlink($stateFile);

?>
--EXPECT--
int(0)
bool(true)
bool(true)
bool(true)
string(9) "preserved"
