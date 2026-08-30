--TEST--
SPL: glob wrapper interactions with DirectoryIterator
--FILE--
<?php
TestContext::useRelativeWorkspace();
$file = TestPath::file('bug.51068')->create();
chdir($file->directory);
$dir = TestPath::directory('bug.51068.dir')->create();
$nested = $dir->file('lvl2.bug.51068')->create();
$iter = new DirectoryIterator('glob://*.51068');
foreach ($iter as $f) {
    var_dump($f->getFilename());
    var_dump($f->getSize());
}
$iter = new DirectoryIterator('glob://bug.51068.dir/*.51068');
foreach ($iter as $f) {
  var_dump($f->getFilename());
  var_dump($f->getSize());
}
$iter = new DirectoryIterator('glob://bug.51068.dir');
foreach ($iter as $f) {
  var_dump($f->getFilename());
  var_dump($f->getSize() >= 0);
}
?>
--EXPECT--
string(9) "bug.51068"
int(0)
string(14) "lvl2.bug.51068"
int(0)
string(13) "bug.51068.dir"
bool(true)
