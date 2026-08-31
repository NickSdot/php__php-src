--TEST--
Test closedir() function : usage variations - close directory handle twice
--FILE--
<?php
/*
 * close the directory handle twice using closedir() to test behaviour
 */

echo "*** Testing closedir() : usage variations ***\n";

//create temporary directory for test
$directory = Workspace::directory()->create();

$dh = opendir($directory);

echo "\n-- Close directory handle first time: --\n";
var_dump(closedir($dh));
echo "Directory Handle: ";
var_dump($dh);

echo "\n-- Close directory handle second time: --\n";
try {
    var_dump(closedir($dh));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
echo "Directory Handle: ";
var_dump($dh);
?>
--EXPECTF--
*** Testing closedir() : usage variations ***

-- Close directory handle first time: --
NULL
Directory Handle: resource(%d) of type (Unknown)

-- Close directory handle second time: --
closedir(): Argument #1 ($dir_handle) must be an open stream resource
Directory Handle: resource(%d) of type (Unknown)
