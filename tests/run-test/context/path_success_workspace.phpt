--TEST--
Test Workspace::path() - creates only the workspace and returns the same path on repeated calls
--FILE--
<?php

use TestSuite\Context\TestContext;

var_dump(TestContext::instance()->workspaceDirectory);

$path = Workspace::path();
echo $path, "\n";
var_dump(is_dir($path));
var_dump(scandir($path));
var_dump(Workspace::path() === $path);

$file = Workspace::file();
echo $file, "\n";
var_dump($file->directory === $path);
var_dump(file_exists($file));

?>
--EXPECTF--
NULL
%s%ephp-src%epath_success_workspace.%d.%x_ws
bool(true)
array(2) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
}
bool(true)
%s%ephp-src%epath_success_workspace.%d.%x_ws%efile-1
bool(true)
bool(false)
