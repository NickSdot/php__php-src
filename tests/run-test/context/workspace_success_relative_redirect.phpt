--TEST--
Test TestContext workspace - redirected tests - relative location follows the generated script
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$root = Workspace::directory()->create();
$targets = $root->directory('targets')->create();
$redirect = $root->file('redirect.phpt');
$target = $targets->file('target.phpt');
$workspaceRecord = Workspace::file('workspace.txt');

file_put_contents($target, <<<'PHPT'
    --TEST--
    Redirected relative workspace
    --SETUP--
    <?php

    Workspace::useRelative();

    ?>
    --FILE--
    <?php

    $file = Workspace::file('fixture.txt');

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
var_dump(realpath(dirname($workspace)) === realpath($root));
var_dump(realpath(dirname($workspace)) !== realpath($targets));
var_dump(str_starts_with(basename($workspace), 'target.'));
var_dump(!is_dir($workspace));

?>
--EXPECT--
int(0)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
