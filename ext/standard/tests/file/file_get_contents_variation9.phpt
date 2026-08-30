--TEST--
Test file_get_contents() function : variation - linked files
--CREDITS--
Dave Kelsey <d_kelsey@uk.ibm.com>
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) == "WIN")
  die("skip Do not run on Windows");
?>
--FILE--
<?php
echo "*** Testing file_get_contents() : variation ***\n";
$filename = TestPath::file();
$softlink = TestPath::file();
$hardlink = TestPath::file();
$chainlink = TestPath::file();

// create file
$h = fopen($filename,"w");
//Data should be more than the size of a link.
for ($i = 1; $i <= 10; $i++) {
   fwrite($h, "Here is a repeated amount of data");
}
fclose($h);

// link files
link($filename, $hardlink);
symlink($filename, $softlink);
symlink($softlink, $chainlink);

// perform tests
var_dump(file_get_contents($chainlink));
var_dump(file_get_contents($softlink));
var_dump(file_get_contents($hardlink));

echo "\n*** Done ***\n";
?>
--EXPECT--
*** Testing file_get_contents() : variation ***
string(330) "Here is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of data"
string(330) "Here is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of data"
string(330) "Here is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of dataHere is a repeated amount of data"

*** Done ***
