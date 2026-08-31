--TEST--
Test fileinode() function: Basic functionality
--FILE--
<?php
echo "*** Testing fileinode() with file, directory ***\n";

/* Getting inode of created file */
$file = Workspace::file();
$file_path = $file->directory;
fopen($file->path, "w");
print( fileinode($file) )."\n";

/* Getting inode of current file */
print( fileinode(__FILE__) )."\n";

/* Getting inode of directories */
print( fileinode(".") )."\n";
print( fileinode("./..") )."\n";

echo "\n*** Done ***";
?>
--EXPECTF--
*** Testing fileinode() with file, directory ***
%i
%i
%i
%i

*** Done ***
