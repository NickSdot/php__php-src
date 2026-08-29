--TEST--
RecursiveDirectoryIterator::getBasePath() - basic test
--CREDITS--
Pawel Krynicki <pawel [dot] krynicki [at] xsolve [dot] pl>
#testfest AmsterdamPHP 2012-06-23
--FILE--
<?php
TestContext::useRelativeWorkspace();
$depth0 = "depth01";
$depth1 = 'depth1';
$depth2 = 'depth2';
$root = TestPath::directory($depth0);
$targetDir = $root->directory($depth1)->directory($depth2);
mkdir($targetDir->path, 0777, true);
touch($targetDir->file('getSubPath_test.tmp')->path);
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
