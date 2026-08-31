--TEST--
Test RunnerContext identity - preserved fixtures for same-named tests in different directories stay separate
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';
require_once dirname(__DIR__, 3) . '/testing/Context/Filesystem/Remove.php';

$root = Workspace::directory()->create();
$record = Workspace::file();

foreach (['first', 'second'] as $name) {
    $testFile = $root->directory($name)->create()->file('nested.phpt');
    file_put_contents($testFile, <<<'PHPT'
        --TEST--
        Nested state identity
        --FILE--
        <?php

        use TestSuite\Context\TestContext;

        $context = TestContext::instance();
        $file = Workspace::file('fixture');
        file_put_contents($file, basename(__DIR__));
        file_put_contents(
            getenv('PHP_TEST_STATE_RECORD'),
            json_encode([$context->id, $context->stateFile, $file->directory]) . "\n",
            FILE_APPEND,
        );

        echo "ok\n";

        ?>
        --EXPECT--
        ok
        PHPT);
}

[$exitCode, $output] = run_test_harness(
    $root->path,
    options: ['-j1', '--no-clean'],
    environment: ['PHP_TEST_STATE_RECORD' => $record->path],
);

$contexts = array_map(json_decode(...), file($record, FILE_IGNORE_NEW_LINES));

var_dump($exitCode);
var_dump(substr_count($output, 'PASS'));
echo 'Execution names: ', count(array_unique(array_column($contexts, 0))), "\n";
echo 'State files: ', count(array_unique(array_column($contexts, 1))), "\n";
echo 'Workspaces: ', count(array_unique(array_column($contexts, 2))), "\n";
foreach ($contexts as [$id, $stateFile, $workspace]) {
    var_dump(is_file($stateFile), file_get_contents($workspace . '/fixture'));
    TestSuite\Context\Filesystem\Remove::directory($workspace);
    unlink($stateFile);
}

?>
--EXPECT--
int(0)
int(2)
Execution names: 1
State files: 2
Workspaces: 2
bool(true)
string(5) "first"
bool(true)
string(6) "second"
