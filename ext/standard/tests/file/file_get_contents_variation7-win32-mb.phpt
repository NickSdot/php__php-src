--TEST--
Test file_get_contents() function : variation - various absolute and relative paths
--CREDITS--
Dave Kelsey <d_kelsey@uk.ibm.com>
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) != "WIN")
  die("skip Only run on Windows");
?>
--FILE--
<?php
echo "*** Testing file_get_contents() : variation ***\n";
$mainDir = TestPath::directory('fileGetContentsVar7私はガラスを食べられます.dir');
$absMainDir = $mainDir;
mkdir($absMainDir);
$subDir = $mainDir->directory('fileGetContentsVar7Sub私はガラスを食べられます');
$absSubDir = $subDir;
mkdir($absSubDir);

$old_dir_path = getcwd();
chdir(dirname($mainDir->path));
$unixifiedDir = '/'.substr(str_replace('\\','/',$absSubDir),3);

$allDirs = array(
  // absolute paths
  "$absSubDir\\",
  "$absSubDir\\..\\".$subDir->name,
  "$absSubDir\\\\..\\.\\".$subDir->name,
  "$absSubDir\\..\\..\\".$mainDir->name."\\.\\".$subDir->name,
  "$absSubDir\\..\\\\\\".$subDir->name."\\\\..\\\\..\\".$subDir->name,
  "$absSubDir\\BADDIR",

  // relative paths
  $mainDir->name."\\".$subDir->name,
  $mainDir->name."\\\\".$subDir->name,
   $mainDir->name."\\\\\\".$subDir->name,
  ".\\".$mainDir->name."\\..\\".$mainDir->name."\\".$subDir->name,
  "BADDIR",

  // unixifed path
  $unixifiedDir,
);

$filename = $subDir->file();
$absFile = $filename;
$h = fopen($absFile,"w");
fwrite($h, "contents read");
fclose($h);

for($i = 0; $i<count($allDirs); $i++) {
  $j = $i+1;
  $dir = $allDirs[$i];
  echo "\n-- Iteration $j --\n";
  var_dump(file_get_contents($dir."\\".$filename->name));
}

chdir($old_dir_path);

echo "\n*** Done ***\n";
?>
--EXPECTF--
*** Testing file_get_contents() : variation ***

-- Iteration 1 --
string(%d) "contents read"

-- Iteration 2 --
string(%d) "contents read"

-- Iteration 3 --
string(%d) "contents read"

-- Iteration 4 --
string(%d) "contents read"

-- Iteration 5 --

Warning: file_get_contents(): Failed to open stream: No such file or directory in %s on line %d
bool(false)

-- Iteration 6 --

Warning: file_get_contents(): Failed to open stream: No such file or directory in %s on line %d
bool(false)

-- Iteration 7 --
string(%d) "contents read"

-- Iteration 8 --
string(%d) "contents read"

-- Iteration 9 --
string(%d) "contents read"

-- Iteration 10 --
string(%d) "contents read"

-- Iteration 11 --

Warning: file_get_contents(): Failed to open stream: No such file or directory in %s on line %d
bool(false)

-- Iteration 12 --
string(%d) "contents read"

*** Done ***
