--TEST--
Test file_put_contents() function : usage variation - various absolute and relative paths
--CREDITS--
Dave Kelsey <d_kelsey@uk.ibm.com>
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) != "WIN")
  die("skip Only run on Windows");
?>
--FILE--
<?php
echo "*** Testing file_put_contents() : usage variation ***\n";

$mainDir = TestPath::directory('filePutContentsVar7.dir');
$absMainDir = $mainDir;
mkdir($absMainDir);
$subDir = $mainDir->directory('filePutContentsVar7Sub');
$absSubDir = $subDir;
mkdir($absSubDir);

$old_dir_path = getcwd();
chdir(dirname($mainDir->path));
$unixifiedDir = '/'.substr(str_replace('\\','/',$absSubDir),3);


// Note invalid dirs in p8 result in (The system cannot find the path specified.)
// rather than No Such File or Directory in php.net
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
$data = "This was the written data";

for($i = 0; $i<count($allDirs); $i++) {
  $j = $i+1;
  $dir = $allDirs[$i];
  echo "\n-- Iteration $j --\n";
  $res = file_put_contents($dir."\\".$filename->name, ($data . $i));
  if ($res !== false) {
      $in = file_get_contents($absFile);
      if ($in == ($data . $i)) {
         echo "Data written correctly\n";
      }
      else {
         echo "Data not written correctly or to correct place\n";
      }
      unlink($dir."/".$filename->name);
  }
  else {
     echo "No data written\n";
  }

}

chdir($old_dir_path);

echo "\n*** Done ***\n";
?>
--EXPECTF--
*** Testing file_put_contents() : usage variation ***

-- Iteration 1 --
Data written correctly

-- Iteration 2 --
Data written correctly

-- Iteration 3 --
Data written correctly

-- Iteration 4 --
Data written correctly

-- Iteration 5 --

Warning: file_put_contents(): Failed to open stream: %s in %s on line %d
No data written

-- Iteration 6 --

Warning: file_put_contents(): Failed to open stream: %s in %s on line %d
No data written

-- Iteration 7 --
Data written correctly

-- Iteration 8 --
Data written correctly

-- Iteration 9 --
Data written correctly

-- Iteration 10 --
Data written correctly

-- Iteration 11 --

Warning: file_put_contents(): Failed to open stream: %s in %s on line %d
No data written

-- Iteration 12 --
Data written correctly

*** Done ***
