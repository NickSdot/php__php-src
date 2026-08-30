--TEST--
PDO_sqlite: Testing open flags
--EXTENSIONS--
pdo_sqlite
--FILE--
<?php

$filename = TestPath::file();

// Default open flag is read-write|create
$db = new PDO('sqlite:' . $filename, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

var_dump($db->exec('CREATE TABLE test_sqlite_open_flags (id INT);'));

$db = new PDO('sqlite:' . $filename, null, null, [Pdo\Sqlite::ATTR_OPEN_FLAGS => Pdo\Sqlite::OPEN_READONLY, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

var_dump($db->exec('CREATE TABLE test_sqlite_open_flags_2 (id INT);'));
?>
--EXPECTF--
int(0)

Fatal error: Uncaught PDOException: SQLSTATE[HY000]: General error: 8 attempt to write a readonly database in %s
Stack trace:
%s
#1 {main}
  thrown in %s
