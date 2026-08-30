--TEST--
Bug #69628: GLOB_BRACE with multiple brackets within the braces fails
--SKIPIF--
<?php
if (!defined('GLOB_BRACE')) {
    die('skip this test requires GLOB_BRACE support');
}
?>
--FILE--
<?php

$directory = TestPath::directory('bug69628');
$dirname = $directory->path;

// temp dir created
mkdir($dirname);

// temp files created
file_put_contents($directory->file('image.jPg'), '');
file_put_contents($directory->file('image.gIf'), '');
file_put_contents($directory->file('image.png'), '');

sort_var_dump(glob("$dirname/*.{[jJ][pP][gG],[gG][iI][fF]}", GLOB_BRACE));

function sort_var_dump($results) {
   sort($results);
   var_dump($results);
}

?>
--EXPECTF--
array(2) {
  [0]=>
  string(%d) "%s/bug69628/image.gIf"
  [1]=>
  string(%d) "%s/bug69628/image.jPg"
}
