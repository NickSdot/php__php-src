--TEST--
Test is_file() function: usage variations - links
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip Do not run on Windows');
}
?>
--FILE--
<?php
/* Creating soft and hard links to a file and applying is_file() on links */

$file = TestPath::file();
$symlink = TestPath::file();
$hardlink = TestPath::file();
fclose( fopen($file, "w") );

echo "*** Testing is_file() with links ***\n";
/* With symlink */
symlink($file, $symlink);
var_dump( is_file($symlink) ); //expected true
clearstatcache();

/* With hardlink */
link($file, $hardlink);
var_dump( is_file($hardlink) );  // expected: true
clearstatcache();

echo "\n*** Done ***";
?>
--EXPECT--
*** Testing is_file() with links ***
bool(true)
bool(true)

*** Done ***
