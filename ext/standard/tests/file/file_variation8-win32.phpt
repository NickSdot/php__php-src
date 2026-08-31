--TEST--
Test file() function : variation - various absolute and relative paths
--CREDITS--
Dave Kelsey <d_kelsey@uk.ibm.com>
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) != "WIN")
  die("skip Only run on Windows");
?>
--FILE--
<?php
echo "*** Testing file() : variation ***\n";
$absMainDir = Workspace::directory()->create();
$mainDir = $absMainDir->name;
$absSubDir = $absMainDir->directory('subdir')->create();
$subDir = $absSubDir->name;

$old_dir_path = getcwd();
chdir(dirname($absMainDir));
$unixifiedDir = '/'.substr(str_replace('\\','/',$absSubDir),3);

$allDirs = array(
  // absolute paths
  "$absSubDir\\",
  "$absSubDir\\..\\".$subDir,
  "$absSubDir\\\\..\\.\\".$subDir,
  "$absSubDir\\..\\..\\".$mainDir."\\.\\".$subDir,
  "$absSubDir\\..\\\\\\".$subDir."\\\\..\\\\..\\".$subDir,
  "$absSubDir\\BADDIR",

  // relative paths
  $mainDir."\\".$subDir,
  $mainDir."\\\\".$subDir,
   $mainDir."\\\\\\".$subDir,
  ".\\".$mainDir."\\..\\".$mainDir."\\".$subDir,
  "BADDIR",

  // unixifed path
  $unixifiedDir,
);

$absFile = $absSubDir->file();
$filename = $absFile->name;
$h = fopen($absFile,"w");
fwrite($h, "contents read");
fclose($h);

for($i = 0; $i<count($allDirs); $i++) {
  $j = $i+1;
  $dir = $allDirs[$i];
  echo "\n-- Iteration $j --\n";
  var_dump(file($dir."\\".$filename));
}

chdir($old_dir_path);

echo "\n*** Done ***\n";
?>
--EXPECTF--
*** Testing file() : variation ***

-- Iteration 1 --
array(1) {
  [0]=>
  string(13) "contents read"
}

-- Iteration 2 --
array(1) {
  [0]=>
  string(13) "contents read"
}

-- Iteration 3 --
array(1) {
  [0]=>
  string(13) "contents read"
}

-- Iteration 4 --
array(1) {
  [0]=>
  string(13) "contents read"
}

-- Iteration 5 --

Warning: file(): Failed to open stream: No such file or directory in %s on line %d
bool(false)

-- Iteration 6 --

Warning: file(): Failed to open stream: No such file or directory in %s on line %d
bool(false)

-- Iteration 7 --
array(1) {
  [0]=>
  string(13) "contents read"
}

-- Iteration 8 --
array(1) {
  [0]=>
  string(13) "contents read"
}

-- Iteration 9 --
array(1) {
  [0]=>
  string(13) "contents read"
}

-- Iteration 10 --
array(1) {
  [0]=>
  string(13) "contents read"
}

-- Iteration 11 --

Warning: file(): Failed to open stream: No such file or directory in %s on line %d
bool(false)

-- Iteration 12 --
array(1) {
  [0]=>
  string(13) "contents read"
}

*** Done ***
