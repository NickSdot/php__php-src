--TEST--
Test TestContext workspace - relative selection persists from SETUP to FILE without moving state
--SETUP--
<?php

use TestSuite\Context\TestContext;

Workspace::useRelative();

if (!is_dir(TestContext::instance()->workspaceDirectory)) {
    throw new Exception('Relative workspace was not created in SETUP');
}

?>
--FILE--
<?php

use TestSuite\Context\TestContext;

$context = TestContext::instance();
$storage = (PHP_OS_FAMILY === 'Windows' ? sys_get_temp_dir() : '/tmp')
    . DIRECTORY_SEPARATOR . 'php-src';

$workspace = $context->workspaceDirectory;

echo $context->stateFile, "\n";
echo $workspace, "\n";

var_dump(realpath(dirname($workspace)) === __DIR__);
var_dump(Workspace::path() === $workspace);
var_dump(scandir($workspace));
var_dump(is_file($context->stateFile));
var_dump(realpath(dirname($context->stateFile)) === realpath($storage));

$file = Workspace::file('fixture.txt');
echo $file, "\n";
var_dump($file->directory === $workspace);
var_dump(file_exists($file));

?>
--EXPECTF--
%s%ephp-src%eworkspace_success_relative.%d.%x.state
%s%eworkspace_success_relative.%d.%x_ws
bool(true)
bool(true)
array(2) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
}
bool(true)
bool(true)
%s%efixture.txt
bool(true)
bool(false)
