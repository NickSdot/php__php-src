--TEST--
Test rmdir() function : variation: various valid and invalid paths
--CREDITS--
Dave Kelsey <d_kelsey@uk.ibm.com>
--FILE--
<?php
echo "*** Testing rmdir() : variation ***\n";

Workspace::useRelative();
$workDir = Workspace::directory('rmdirVar3.tmp');
$workDirName = $workDir->name;
$subDir = "aSubDir";
chdir(dirname($workDir->path));
mkdir($workDir);
$cwd = getcwd();

$dirs = array(
             // relative
             $workDirName.'/'.$subDir,
             './'.$workDirName.'/'.$subDir,
             $workDirName.'/../'.$workDirName.'/'.$subDir,

             // relative bad path
             $workDirName.'/../BADDIR/'.$subDir,
             'BADDIR/'.$subDir,

             //absolute
             $cwd.'/'.$workDirName.'/'.$subDir,
             $cwd.'/./'.$workDirName.'/'.$subDir,
             $cwd.'/'.$workDirName.'/../'.$workDirName.'/'.$subDir,

             //absolute bad path
             $cwd.'/BADDIR/'.$subDir,

             //trailing separators
             $workDirName.'/'.$subDir.'/',
             $cwd.'/'.$workDirName.'/'.$subDir.'/',

             // multiple separators
             $workDirName.'//'.$subDir,
             $cwd.'//'.$workDirName.'//'.$subDir,

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
-- removing rmdirVar3.tmp/aSubDir --
Directory removed
-- removing ./rmdirVar3.tmp/aSubDir --
Directory removed
-- removing rmdirVar3.tmp/../rmdirVar3.tmp/aSubDir --
Directory removed
-- removing rmdirVar3.tmp/../BADDIR/aSubDir --

Warning: rmdir(): No such file or directory in %s on line %d
-- removing BADDIR/aSubDir --

Warning: rmdir(): No such file or directory in %s on line %d
-- removing %s/rmdirVar3.tmp/aSubDir --
Directory removed
-- removing %s/./rmdirVar3.tmp/aSubDir --
Directory removed
-- removing %s/rmdirVar3.tmp/../rmdirVar3.tmp/aSubDir --
Directory removed
-- removing %s/BADDIR/aSubDir --

Warning: rmdir(): No such file or directory in %s on line %d
-- removing rmdirVar3.tmp/aSubDir/ --
Directory removed
-- removing %s/rmdirVar3.tmp/aSubDir/ --
Directory removed
-- removing rmdirVar3.tmp//aSubDir --
Directory removed
-- removing %s//rmdirVar3.tmp//aSubDir --
Directory removed
