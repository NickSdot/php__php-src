--TEST--
Bug #68825 (Exception in DirectoryIterator::getLinkTarget())
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows') {
    include_once __DIR__ . '/../../standard/tests/file/windows_links/common.inc';
    skipIfSeCreateSymbolicLinkPrivilegeIsDisabled(__FILE__);
}
?>
--FILE--
<?php

TestContext::useRelativeWorkspace();

$dir = TestPath::directory()->create();

if (!symlink(TestPath::file('bug68825.php')->create(), $dir->file('bug'))) {
    die('Failed to create symbolic link');
}

$di = new \DirectoryIterator($dir);
foreach ($di as $entry) {
    if ('bug' === $entry->getFilename()) {
        var_dump($entry->getLinkTarget());
    }
}
?>
--EXPECTF--
string(%d) "%s%eext%espl%etests%e%s%ebug68825.php"
