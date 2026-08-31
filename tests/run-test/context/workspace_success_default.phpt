--TEST--
Test TestContext workspace - default location - beside the state file in system storage
--FILE--
<?php

use TestSuite\Context\TestContext;

$context = TestContext::instance();
$file = Workspace::file();
$directory = Workspace::directory('fixtures');
$workspace = $context->workspaceDirectory;

echo $context->stateFile, "\n";
echo $workspace, "\n";
echo $file, "\n";
echo $directory, "\n";

var_dump($file->directory === $workspace);
var_dump(dirname($workspace) === dirname($context->stateFile));
var_dump(is_dir($workspace));

?>
--EXPECTF--
%s%ephp-src%eworkspace_success_default.%d.%x.state
%s%ephp-src%eworkspace_success_default.%d.%x_ws
%s%ephp-src%eworkspace_success_default.%d.%x_ws%efile-1
%s%ephp-src%eworkspace_success_default.%d.%x_ws%efixtures
bool(true)
bool(true)
bool(true)
