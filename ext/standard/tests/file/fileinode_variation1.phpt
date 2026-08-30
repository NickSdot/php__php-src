--TEST--
Test fileinode() function: usage variations - links
--CREDITS--
Dave Kelsey <d_kelsey@uk.ibm.com>
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip Do not run on Windows');
}
?>
--FILE--
<?php
/* Creating soft and hard links to a file and applying fileinode() on links */

$file = TestPath::file()->create();

echo "*** Testing fileinode() with links ***\n";
/* With symlink */
$symlink = TestPath::file();
symlink($file, $symlink);
var_dump( fileinode($symlink) ); //expected true
clearstatcache();

/* With hardlink */
$link = TestPath::file();
link($file, $link);
var_dump( fileinode($link) );  // expected: true
clearstatcache();

echo "\n*** Done ***";
?>
--EXPECTF--
*** Testing fileinode() with links ***
int(%d)
int(%d)

*** Done ***
