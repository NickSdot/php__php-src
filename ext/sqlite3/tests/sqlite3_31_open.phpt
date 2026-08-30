--TEST--
SQLite3::re-initialize object tests
--CREDITS--
Jelle Lampaert
#Belgian Testfest 2009
--EXTENSIONS--
sqlite3
--FILE--
<?php
$db_file = TestPath::file();

try {
  $db = new SQLite3($db_file);
  $db->open($db_file);
} catch (Exception $ex) {
  echo $ex::class, ': ', $ex->getMessage(), "\n";
}

?>
--EXPECT--
Exception: Already initialised DB Object
