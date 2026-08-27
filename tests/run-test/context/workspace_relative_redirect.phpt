--TEST--
Relative workspaces for redirected tests use the generated script directory
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$root = TestPath::directory('redirect');
$targets = $root->directory('targets');
$redirect = $root->file('redirect.phpt');
$target = $targets->file('target.phpt');
$workspaceRecord = TestPath::file('workspace.txt');

mkdir($targets->path, recursive: true);

file_put_contents($target, <<<'PHPT'
    --TEST--
    Redirected relative workspace
    --SETUP--
    <?php

    TestContext::useRelativeWorkspace();

    ?>
    --FILE--
    <?php

    $file = TestPath::file('fixture.txt');

    file_put_contents(
        getenv('PHP_TEST_WORKSPACE_RECORD'),
        $file->directory,
    );

    echo "ok\n";

    ?>
    --EXPECT--
    ok
    PHPT);

$targetDirectory = var_export($targets->path, true);

file_put_contents($redirect, <<<PHPT
    --TEST--
    Redirect wrapper
    --REDIRECTTEST--
    return ['ENV' => [], 'TESTS' => $targetDirectory];
    PHPT);

[$exitCode, $output] = run_test_harness(
    $redirect->path,
    environment: ['PHP_TEST_WORKSPACE_RECORD' => $workspaceRecord->path],
);

$workspace = file_get_contents($workspaceRecord);

var_dump($exitCode);
var_dump(str_contains($output, 'PASS'));
var_dump(dirname($workspace) === $root->path);
var_dump(dirname($workspace) !== $targets->path);
var_dump(str_starts_with(basename($workspace), 'target.phpt.h'));
var_dump(!is_dir($workspace));

?>
--EXPECT--
int(0)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
