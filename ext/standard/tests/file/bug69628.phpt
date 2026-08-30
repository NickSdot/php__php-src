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

// temp dir created
$directory = TestPath::directory('bug69628')->create();

// temp files created
$directory->file('image.jPg')->create();
$directory->file('image.gIf')->create();
$directory->file('image.png')->create();

sort_var_dump(glob("$directory/*.{[jJ][pP][gG],[gG][iI][fF]}", GLOB_BRACE));

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
