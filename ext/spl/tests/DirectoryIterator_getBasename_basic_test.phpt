--TEST--
DirectoryIterator::getBasename() - Basic Test
--CREDITS--
PHPNW Testfest 2009 - Adrian Hardy
--FILE--
<?php
   TestContext::useRelativeWorkspace();
   $targetDir = TestPath::directory(md5('directoryIterator::getbasename1'));
   mkdir($targetDir->path);
   touch($targetDir->file('getBasename_test.txt')->path);
   $dir = new DirectoryIterator($targetDir);
   while(!$dir->isFile()) {
      $dir->next();
   }
   echo $dir->getBasename('.txt');
?>
--EXPECT--
getBasename_test
