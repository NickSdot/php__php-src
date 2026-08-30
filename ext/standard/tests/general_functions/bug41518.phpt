--TEST--
Bug #41518 (file_exists() warns of open_basedir restriction on non-existent file)
--INI--
open_basedir=.
--SKIPIF--
<?php
$probe = tempnam(__DIR__, 'bug41518-');
if ($probe === false) {
    die('skip Test directory is not writable');
}
unlink($probe);
?>
--FILE--
<?php
TestContext::useRelativeWorkspace();
$tmp_file = TestPath::file()->create();
var_dump(file_exists($tmp_file)); //exists
var_dump(file_exists($tmp_file."nosuchfile")); //doesn't exist

echo "Done\n";
?>
--EXPECT--
bool(true)
bool(false)
Done
