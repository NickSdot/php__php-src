--TEST--
Workspace INI settings are unavailable in FILE
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = Workspace::file('nested.phpt');
$workspaceRecord = Workspace::file();
$stateRecord = Workspace::file();

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    INI configuration in FILE
    --FILE--
    <?php
    file_put_contents(getenv('PHP_TEST_WORKSPACE_RECORD'), Workspace::path());
    file_put_contents(getenv('PHP_TEST_STATE_RECORD'), getenv('PHP_TEST_CONTEXT'));

    try {
        Workspace::ini(['memory_limit' => '-1']);
    } catch (Throwable) {
    }
    echo "unexpectedly continued\n";
    ?>
    --EXPECT--
    unexpectedly continued
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
var_dump(str_contains($output, 'ERROR'));
var_dump(str_contains($output, 'Workspace::ini() is only available in SETUP'));
var_dump(!str_contains($output, 'unexpectedly continued'));
var_dump(!is_dir($workspace));
var_dump(!is_file($stateFile));

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
