--TEST--
Test Directory child allocation - anonymous counters - independent parents
--FILE--
<?php

$first = Workspace::directory('first');
$second = Workspace::directory('second');

foreach ([$first, $second] as $directory) {
    printf("%s: %s, %s\n", $directory->name, $directory->file()->name, $directory->directory()->name);
}

?>
--EXPECT--
first: file-1, directory-1
second: file-1, directory-1
