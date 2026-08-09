--TEST--
Source diff maps unchanged lines around changed hunks
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\SourceDiff;

$diff = SourceDiff::fromPatch(<<<'DIFF'
diff --git a/example.c b/example.c
@@ -2,2 +2,3 @@
\ No newline at end of file
@@ -7,0 +9,2 @@
@@ -10,2 +12,0 @@
DIFF);

foreach ([1, 2, 3, 4, 7, 8, 10, 11, 12] as $line) {
    printf("base %d -> %s\n", $line, var_export($diff->treeLine($line), true));
}

foreach ([1, 2, 3, 4, 5, 8, 9, 10, 13, 14] as $line) {
    printf("tree %d -> %s\n", $line, var_export($diff->baseLine($line), true));
}

var_dump(SourceDiff::fromPatch("@@ -2 +2 @@\n")->baseLine(2));
var_dump(SourceDiff::fromPatch("@@ -2,2 +2,0 @@\n")->baseLine(2));
var_dump(SourceDiff::added()->baseLine(100));
?>
--EXPECT--
base 1 -> 1
base 2 -> NULL
base 3 -> NULL
base 4 -> 5
base 7 -> 8
base 8 -> 11
base 10 -> NULL
base 11 -> NULL
base 12 -> 13
tree 1 -> 1
tree 2 -> NULL
tree 3 -> NULL
tree 4 -> NULL
tree 5 -> 4
tree 8 -> 7
tree 9 -> NULL
tree 10 -> NULL
tree 13 -> 12
tree 14 -> 13
NULL
int(2)
NULL
