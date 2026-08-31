--TEST--
Test symlink(), linkinfo(), link() and is_link() functions : error conditions - symlink & linkinfo
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) == 'SUN') {
  die('skip Not valid for Sun Solaris');
}
?>
--FILE--
<?php
$file = Workspace::file();
$link = Workspace::file();
// create temp $filename and create link $linkname to it
$filename = $file;
$fp = fopen($filename, "w");  // create temp file
fclose($fp);

// linkname used to create soft/hard link
$linkname = $link;

echo "*** Testing symlink() for error conditions ***\n";

//invalid arguments
var_dump( symlink('', $linkname) );  // empty string as filename
var_dump( symlink(false, $linkname) );  // boolean false as filename
var_dump( symlink($filename, '') );  // '' as linkname
var_dump( symlink($filename, false) );  // false as linkname

echo "\n*** Testing linkinfo() for error conditions ***\n";

//invalid arguments
try {
    var_dump(linkinfo(''));  // empty string as linkname
} catch (ValueError $e) {
    echo $e->getMessage() . "\n";
}

echo "Done\n";
?>
--EXPECTF--
*** Testing symlink() for error conditions ***

Warning: symlink(): %s in %s on line %d
bool(false)

Warning: symlink(): %s in %s on line %d
bool(false)

Warning: symlink(): %s in %s on line %d
bool(false)

Warning: symlink(): %s in %s on line %d
bool(false)

*** Testing linkinfo() for error conditions ***
linkinfo(): Argument #1 ($path) must not be empty
Done
