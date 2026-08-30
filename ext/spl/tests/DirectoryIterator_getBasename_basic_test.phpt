--TEST--
DirectoryIterator::getBasename() - Basic Test
--CREDITS--
PHPNW Testfest 2009 - Adrian Hardy
--FILE--
<?php
   $targetDir = TestPath::directory()->create();
   $targetDir->file('getBasename_test.txt')->create();
   $dir = new DirectoryIterator($targetDir);
   while(!$dir->isFile()) {
      $dir->next();
   }
   echo $dir->getBasename('.txt');
?>
--EXPECT--
getBasename_test
