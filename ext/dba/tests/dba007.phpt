--TEST--
DBA Multiple File Creation Test
--EXTENSIONS--
dba
--SKIPIF--
<?php
    require_once(__DIR__ .'/skipif.inc');
    if (!function_exists('dba_list')) die('skip dba_list() not available');
    die("info $HND handler used");
?>
--CONFLICTS--
dba
--FILE--
<?php
    require_once(__DIR__ .'/test.inc');
    $db_file = TestPath::file('test0.dbm');
    echo "database handler: $handler\n";
    $db_file1 = $db_filename1 = TestPath::file('test1.dbm');
    $db_file2 = $db_filename2 = TestPath::file('test2.dbm');
    if (($db_file=dba_open($db_file, "n", $handler))!==FALSE) {
        echo "database file created\n";
    } else {
        echo "$db_file does not exist\n";
    }
    if (($db_file1=dba_open($db_file1, "n", $handler))!==FALSE) {
        echo "database file created\n";
    } else {
        echo "$db_file does not exist\n";
    }
    if (($db_file2=dba_open($db_file2, "n", $handler))!==FALSE) {
        echo "database file created\n";
    } else {
        echo "$db_file does not exist\n";
    }
    var_dump(dba_list());
    dba_close($db_file);

?>
--EXPECTF--
database handler: %s
database file created
database file created
database file created
array(3) {
  [%d]=>
  string(%d) "%stest0.dbm"
  [%d]=>
  string(%d) "%stest1.dbm"
  [%d]=>
  string(%d) "%stest2.dbm"
}
