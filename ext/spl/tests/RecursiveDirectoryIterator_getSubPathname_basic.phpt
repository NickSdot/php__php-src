--TEST--
RecursiveDirectoryIterator::getBasePathname() - basic test
--CREDITS--
Pawel Krynicki <pawel [dot] krynicki [at] xsolve [dot] pl>
#testfest AmsterdamPHP 2012-06-23
--FILE--
<?php
$depth1 = "depth1";
$depth2 = "depth2";
$root = TestPath::directory()->create();
$level1 = $root->directory($depth1)->create();
$targetDir = $level1->directory($depth2)->create();
$targetDir->file('getSubPathname_test_2.tmp')->create();
$level1->file('getSubPathname_test_3.tmp')->create();
$root->file('getSubPathname_test_1.tmp')->create();
$iterator = new RecursiveDirectoryIterator($root);
$it = new RecursiveIteratorIterator($iterator);

$list = [];
$it->rewind(); //see https://bugs.php.net/bug.php?id=62914
while($it->valid()) {
  $list[] = $it->getSubPathname();
  $it->next();
}
asort($list);
foreach ($list as $item) {
    echo $item . "\n";
}
?>
--EXPECTF--
.
..
depth1%c.
depth1%c..
depth1%cdepth2%c.
depth1%cdepth2%c..
depth1%cdepth2%cgetSubPathname_test_2.tmp
depth1%cgetSubPathname_test_3.tmp
getSubPathname_test_1.tmp
