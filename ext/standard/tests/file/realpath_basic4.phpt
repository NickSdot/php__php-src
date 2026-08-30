--TEST--
Test realpath() with relative paths
--FILE--
<?php
$file_path = TestPath::directory();
$root = $file_path->directory('realpath_basic4');
$home = $root->directory('home');
$test = $home->directory('test');
$link1 = $root->file();
$link2 = $root->file();
@mkdir($test, 0777, true);
@symlink($home, $link1);
@symlink($link1, $link2);
echo "1. " . realpath($link2) . "\n";
echo "2. " . realpath($link2 . '/test') . "\n";
?>
--EXPECTF--
1. %s%erealpath_basic4%ehome
2. %s%erealpath_basic4%ehome%etest
