--TEST--
Test parse_ini_file() function : variation: include path searching
--CREDITS--
Dave Kelsey <d_kelsey@uk.ibm.com>
--FILE--
<?php
echo "*** Testing parse_ini_file() : variation ***\n";
$f = "parse_ini_file_variation3";
$dir1 = Workspace::directory($f.".dir1");
$dir2 = Workspace::directory($f.".dir2");
$dir3 = Workspace::directory($f.".dir3");
$iniFile = "php.ini";

$newdirs = array($dir1, $dir2, $dir3);
$pathSep = ":";
$newIncludePath = "";
if(substr(PHP_OS, 0, 3) == 'WIN' ) {
   $pathSep = ";";
}
foreach($newdirs as $newdir) {
   $newdir->create();
   $newIncludePath .= $newdir.$pathSep;
}

set_include_path($newIncludePath);
$path = get_include_path();
echo "New include path is : " . $path . "\n";

$output_file = $dir2->file($iniFile);
$iniContent = <<<FILE
error_reporting  =  E_ALL
display_errors = On
display_startup_errors = Off
log_errors = Off
ignore_repeated_errors = Off
ignore_repeated_source = Off
docref_root = "/phpmanual/"
docref_ext = .html

FILE;

file_put_contents($output_file, $iniContent);
var_dump(parse_ini_file($iniFile));

?>
--EXPECTF--
*** Testing parse_ini_file() : variation ***
New include path is : %sparse_ini_file_variation3.dir1%sparse_ini_file_variation3.dir2%sparse_ini_file_variation3.dir3%S
array(8) {
  ["error_reporting"]=>
  string(5) "30719"
  ["display_errors"]=>
  string(1) "1"
  ["display_startup_errors"]=>
  string(0) ""
  ["log_errors"]=>
  string(0) ""
  ["ignore_repeated_errors"]=>
  string(0) ""
  ["ignore_repeated_source"]=>
  string(0) ""
  ["docref_root"]=>
  string(11) "/phpmanual/"
  ["docref_ext"]=>
  string(5) ".html"
}
