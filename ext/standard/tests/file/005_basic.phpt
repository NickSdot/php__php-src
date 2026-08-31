--TEST--
Test fileatime(), filemtime(), filectime() & touch() functions : basic functionality
--FILE--
<?php
$file = Workspace::file();
$directory_path = Workspace::file();

echo "*** Testing the basic functionality with file ***\n";
print( @date('Y:M:D:H:i:s', fileatime(__FILE__)) )."\n";
print( @date('Y:M:D:H:i:s', filemtime(__FILE__)) )."\n";
print( @date('Y:M:D:H:i:s', filectime(__FILE__)) )."\n";
print( @date('Y:M:D:H:i:s', touch($file)) )."\n";

echo "*** Testing the basic functionality with dir ***\n";
print( @date('Y:M:D:H:i:s', fileatime(".")) )."\n";
print( @date('Y:M:D:H:i:s', filemtime(".")) )."\n";
print( @date('Y:M:D:H:i:s', filectime(".")) )."\n";
print( @date('Y:M:D:H:i:s', touch($directory_path)) )."\n";

echo "\n*** Done ***\n";
?>
--EXPECTF--
*** Testing the basic functionality with file ***
%d:%s:%s:%d:%d:%d
%d:%s:%s:%d:%d:%d
%d:%s:%s:%d:%d:%d
%d:%s:%s:%d:%d:%d
*** Testing the basic functionality with dir ***
%d:%s:%s:%d:%d:%d
%d:%s:%s:%d:%d:%d
%d:%s:%s:%d:%d:%d
%d:%s:%s:%d:%d:%d

*** Done ***
