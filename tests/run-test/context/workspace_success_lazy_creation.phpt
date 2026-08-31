--TEST--
Test TestContext workspace - lazy creation - first allocation creates only the workspace
--FILE--
<?php

use TestSuite\Context\TestContext;

$context = TestContext::instance();
$workspace = dirname($context->stateFile)
    . DIRECTORY_SEPARATOR
    . basename($context->stateFile, '.state') . '_ws';

echo "Before allocation:\n";
var_dump($context->workspaceDirectory, is_dir($workspace));

$file = Workspace::file();

echo "After allocation:\n";
var_dump($context->workspaceDirectory === $workspace);
var_dump(is_dir($workspace), file_exists($file));

?>
--EXPECT--
Before allocation:
NULL
bool(false)
After allocation:
bool(true)
bool(true)
bool(false)
