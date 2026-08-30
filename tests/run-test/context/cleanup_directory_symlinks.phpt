--TEST--
Workspace cleanup removes directory symlinks without following their targets
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') {
    require dirname(__DIR__, 3) . '/ext/standard/tests/file/windows_links/common.inc';
    skipIfSeCreateSymbolicLinkPrivilegeIsDisabled(__FILE__);
}
?>
--FILE--
<?php
require_once dirname(__DIR__, 3) . '/testing/Context/Filesystem/Remove.php';

$target = TestPath::directory()->create();
$targetPermissions = fileperms($target);
$sentinel = $target->file();
file_put_contents($sentinel, 'preserved');

$directory = TestPath::directory()->create();
$link = $directory->file();
symlink($target, $link);

$danglingTarget = TestPath::directory()->create();
$danglingLink = $directory->file();
symlink($danglingTarget, $danglingLink);
rmdir($danglingTarget);

var_dump(is_link($link));
var_dump(is_link($danglingLink));
var_dump(!file_exists($danglingLink));

TestSuite\Context\Filesystem\Remove::directory($directory->path);
clearstatcache();

var_dump(!is_dir($directory));
var_dump(!is_link($link));
var_dump(!is_link($danglingLink));
var_dump(is_dir($target));
var_dump(fileperms($target) === $targetPermissions);
var_dump(file_get_contents($sentinel));
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
string(9) "preserved"
