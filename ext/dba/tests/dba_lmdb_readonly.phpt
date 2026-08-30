--TEST--
DBA LMDB handler readonly test
--EXTENSIONS--
dba
--SKIPIF--
<?php
    $handler = 'lmdb';
    require_once __DIR__ .'/skipif.inc';
?>
--FILE--
<?php
$handler = 'lmdb';
$db_filename = TestPath::file();

// Create DB
$db_file = dba_open($db_filename, "c", $handler);
assert($db_file !== false);
// Close handler
dba_close($db_file);

// Open in read only mode
$db_file = dba_open($db_filename, "r", $handler);
assert($db_file !== false);

// Try inserting
dba_insert("key1", "This is a test insert", $db_file);
dba_close($db_file);
?>
--EXPECTF--
Warning: dba_insert(): Cannot perform a modification on a readonly database in %s on line %d
