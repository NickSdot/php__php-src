--TEST--
Repeated executions of one PHPT receive distinct TestContext state files
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = TestPath::file('nested.phpt');
$stateRecord = TestPath::file('states.txt');

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested state identity
    --FILE--
    <?php

    file_put_contents(
        getenv('PHP_TEST_STATE_RECORD'),
        getenv('PHP_TEST_CONTEXT') . "\n",
        FILE_APPEND,
    );

    echo "ok\n";

    ?>
    --EXPECT--
    ok
    PHPT);

[$firstExitCode, $firstOutput] = run_test_harness(
    $testFile->path,
    environment: ['PHP_TEST_STATE_RECORD' => $stateRecord->path],
);
[$secondExitCode, $secondOutput] = run_test_harness(
    $testFile->path,
    environment: ['PHP_TEST_STATE_RECORD' => $stateRecord->path],
);

$stateFiles = file($stateRecord->path, FILE_IGNORE_NEW_LINES);

var_dump($firstExitCode);
var_dump(str_contains($firstOutput, 'PASS'));
var_dump($secondExitCode);
var_dump(str_contains($secondOutput, 'PASS'));
var_dump(count(array_unique($stateFiles)));
var_dump(!is_file($stateFiles[0]));
var_dump(!is_file($stateFiles[1]));

?>
--EXPECT--
int(0)
bool(true)
int(0)
bool(true)
int(2)
bool(true)
bool(true)
