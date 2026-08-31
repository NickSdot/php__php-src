--TEST--
Bug #46542 Extending PDO class with a __call() function
--EXTENSIONS--
pdo_sqlite
--FILE--
<?php
class A extends PDO
{ function __call($m, $p) {print __CLASS__."::$m\n";} }

$dbFile = Workspace::file();
$a = new A('sqlite:' . $dbFile);

$a->truc();
$a->TRUC();

?>
--EXPECT--
A::truc
A::TRUC
