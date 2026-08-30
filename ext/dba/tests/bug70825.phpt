--TEST--
Bug #70825 (Cannot fetch multiple values with group in ini file)
--EXTENSIONS--
dba
--SKIPIF--
<?php
if (!in_array('inifile', dba_handlers())) die('skip inifile handler not available');
?>
--FILE--
<?php
$filename = TestPath::file();

$db = dba_open($filename, 'n', 'inifile');
dba_insert('foo', 23, $db);
dba_insert('foo', 42, $db);
dba_insert('foo', 1337, $db);
var_dump(dba_fetch('foo', $db, -1));
var_dump(dba_fetch('foo', $db, -1));
var_dump(dba_fetch('foo', $db, -1));
dba_close($db);

$db = dba_open($filename, 'n', 'inifile');
dba_insert(['foo', 'bar'], 23, $db);
dba_insert(['foo', 'bar'], 42, $db);
dba_insert(['foo', 'bar'], 1337, $db);
var_dump(dba_fetch(['foo', 'bar'], $db, -1));
var_dump(dba_fetch(['foo', 'bar'], $db, -1));
var_dump(dba_fetch(['foo', 'bar'], $db, -1));
dba_close($db);

$db = dba_open($filename, 'n', 'inifile');
dba_insert('[foo]bar', 23, $db);
dba_insert('[foo]bar', 42, $db);
dba_insert('[foo]bar', 1337, $db);
var_dump(dba_fetch('[foo]bar', $db, -1));
var_dump(dba_fetch('[foo]bar', $db, -1));
var_dump(dba_fetch('[foo]bar', $db, -1));
dba_close($db);

$db = dba_open($filename, 'n', 'inifile');
dba_insert('[foo]bar', 23, $db);
dba_insert('[foo]bar', 42, $db);
dba_insert('[foo]bar', 1337, $db);
var_dump(dba_fetch('[foo]bar', $db, 0));
var_dump(dba_fetch('[foo]bar', $db, 1));
var_dump(dba_fetch('[foo]bar', $db, 2));
dba_close($db);
?>
--EXPECT--
string(2) "23"
string(2) "42"
string(4) "1337"
string(2) "23"
string(2) "42"
string(4) "1337"
string(2) "23"
string(2) "42"
string(4) "1337"
string(2) "23"
string(2) "42"
string(4) "1337"
