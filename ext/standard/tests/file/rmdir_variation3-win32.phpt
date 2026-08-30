--TEST--
Test rmdir() function : variation: various valid and invalid paths
--CREDITS--
Dave Kelsey <d_kelsey@uk.ibm.com>
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) != "WIN")
  die("skip Only valid for Windows");
?>
--FILE--
<?php
echo "*** Testing rmdir() : variation ***\n";

TestContext::useRelativeWorkspace();
$workDir = TestPath::directory('rmdirVar3Win32.tmp');
$workDirName = $workDir->name;
$subDir = "aSubDir";
chdir(dirname($workDir->path));
mkdir($workDir);
$cwd = getcwd();

$unixifiedDir = '/'.substr(str_replace('\\','/',$cwd).'/'.$workDir.'/'.$subDir, 3);

$dirs = array(
             // relative
             $workDirName.'\\'.$subDir,
             '.\\'.$workDirName.'\\'.$subDir,
             $workDirName.'\\..\\'.$workDirName.'\\'.$subDir,

             // relative bad path
             $workDirName.'\\..\\BADDIR\\'.$subDir,
             'BADDIR\\'.$subDir,

             //absolute
             $cwd.'\\'.$workDirName.'\\'.$subDir,
             $cwd.'\\.\\'.$workDirName.'\\'.$subDir,
             $cwd.'\\'.$workDirName.'\\..\\'.$workDirName.'\\'.$subDir,

             //absolute bad path
             $cwd.'\\BADDIR\\'.$subDir,

             //trailing separators
             $workDirName.'\\'.$subDir.'\\',
             $cwd.'\\'.$workDirName.'\\'.$subDir.'\\',

             // multiple separators
             $workDirName.'\\\\'.$subDir,
             $cwd.'\\\\'.$workDirName.'\\\\'.$subDir,

             // Unixified Dir
             $unixifiedDir,

             );


foreach($dirs as $dir) {
   mkdir($workDir . '/' . $subDir);
   echo "-- removing $dir --\n";
   $res = rmdir($dir);
   if ($res === true) {
      echo "Directory removed\n";
   }
   else {
      rmdir($workDir . '/' . $subDir);
   }
}

?>
--EXPECTF--
*** Testing rmdir() : variation ***
-- removing rmdirVar3Win32.tmp\aSubDir --
Directory removed
-- removing .\rmdirVar3Win32.tmp\aSubDir --
Directory removed
-- removing rmdirVar3Win32.tmp\..\rmdirVar3Win32.tmp\aSubDir --
Directory removed
-- removing rmdirVar3Win32.tmp\..\BADDIR\aSubDir --

Warning: rmdir(): No such file or directory in %s on line %d
-- removing BADDIR\aSubDir --

Warning: rmdir(): No such file or directory in %s on line %d
-- removing %s\rmdirVar3Win32.tmp\aSubDir --
Directory removed
-- removing %s\.\rmdirVar3Win32.tmp\aSubDir --
Directory removed
-- removing %s\rmdirVar3Win32.tmp\..\rmdirVar3Win32.tmp\aSubDir --
Directory removed
-- removing %s\BADDIR\aSubDir --

Warning: rmdir(): No such file or directory in %s on line %d
-- removing rmdirVar3Win32.tmp\aSubDir\ --
Directory removed
-- removing %s\rmdirVar3Win32.tmp\aSubDir\ --
Directory removed
-- removing rmdirVar3Win32.tmp\\aSubDir --
Directory removed
-- removing %s\\rmdirVar3Win32.tmp\\aSubDir --
Directory removed
-- removing /%s/rmdirVar3Win32.tmp/aSubDir --
Directory removed
