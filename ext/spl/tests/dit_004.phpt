--TEST--
SPL: DirectoryIterator and clone
--FILE--
<?php

$dir = TestPath::directory('dit_004');
@mkdir($dir->path);
touch($dir->file('file1')->path);
touch($dir->file('file2')->path);
touch($dir->file('file3')->path);

$a = new DirectoryIterator($dir);
$b = clone $a;
var_dump((string)$b == (string)$a);
var_dump($a->key(), $b->key());
$a->next();
$a->next();
$a->next();
$c = clone $a;
var_dump((string)$c == (string)$a);
var_dump($a->key(), $c->key());
?>
--EXPECT--
bool(true)
int(0)
int(0)
bool(true)
int(3)
int(3)
