--TEST--
SPL: glob wrapper interactions with DirectoryIterator
--FILE--
<?php
$file = Workspace::file('bug.51068')->create();
$dir = Workspace::directory('bug.51068.dir')->create();
$dir->file('lvl2.bug.51068')->create();
$iter = new DirectoryIterator("glob://{$file->directory}/*.51068");
foreach ($iter as $f) {
    var_dump($f->getFilename());
    var_dump($f->getSize());
}
$iter = new DirectoryIterator("glob://$dir/*.51068");
foreach ($iter as $f) {
  var_dump($f->getFilename());
  var_dump($f->getSize());
}
$iter = new DirectoryIterator("glob://$dir");
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
