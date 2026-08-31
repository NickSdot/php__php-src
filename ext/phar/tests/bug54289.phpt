--TEST--
Bug #54289 Phar::extractTo() does not accept specific directories to be extracted
--EXTENSIONS--
phar
--INI--
phar.readonly = 0
--FILE--
<?php
// put our test fixtures into a far
$inDir = __DIR__.DIRECTORY_SEPARATOR.'bug54289'.DIRECTORY_SEPARATOR.'in';
$archive = Workspace::file('archive.phar');
$phar  = $archive->path;
$pharA = new Phar($phar);
$pharA->buildFromDirectory($inDir);

// we should be able to pull out a directory that's there, but none that share
// the same prefix
$outDir = Workspace::directory()->create();
$pharB = new Phar($phar);
$pharB->extractTo($outDir, 'dirA/', true);
var_dump(file_exists($outDir.DIRECTORY_SEPARATOR.'dirA'.DIRECTORY_SEPARATOR.'fileA'));
var_dump(file_exists($outDir.DIRECTORY_SEPARATOR.'dirA'.DIRECTORY_SEPARATOR.'fileB'));
var_dump(is_dir($outDir.DIRECTORY_SEPARATOR.'dirAB'));

// should also not be able to pull out non-existent ones
try {
  $pharB->extractTo($outDir, 'dirX/', true);
  echo 'failed to throw expected exception';
} catch (PharException $ex) {
}

// should also not be able to pull out /, because paths are not "rooted" that way
try {
  $pharB->extractTo($outDir, '/', true);
  echo 'failed to throw expected exception';
} catch (PharException $ex) {
}

// should be able to do by array, too
$pharB = new Phar($phar);
$pharB->extractTo($outDir, [ 'dirA/', 'dirAB/' ], true);

// but not an array with a bad member in it
try {
  $pharB = new Phar($phar);
  $pharB->extractTo($outDir, [ 'dirA/', 'dirX/' ], true);
  echo 'failed to throw expected exception';
} catch (PharException $ex) {
}

echo 'done';
?>
--EXPECT--
bool(true)
bool(true)
bool(false)
done
