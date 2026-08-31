--TEST--
RecursiveDirectoryIterator::getBasePath() - basic test
--CREDITS--
Pawel Krynicki <pawel [dot] krynicki [at] xsolve [dot] pl>
#testfest AmsterdamPHP 2012-06-23
--FILE--
<?php
$root = Workspace::directory()->create();
$depth1 = 'depth1';
$depth2 = 'depth2';
$level1 = $root->directory($depth1)->create();
$targetDir = $level1->directory($depth2)->create();
$targetDir->file()->create();
$iterator = new RecursiveDirectoryIterator($root);
$it = new RecursiveIteratorIterator($iterator);

$list = [];
while($it->valid()) {
  $list[] = $it->getSubPath();
  $it->next();
}
asort($list);
foreach ($list as $item) {
    echo $item . "\n";
}
?>
--EXPECTF--
depth1
depth1
depth1%cdepth2
depth1%cdepth2
depth1%cdepth2
