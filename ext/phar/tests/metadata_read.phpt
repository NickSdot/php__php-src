--TEST--
Phar with meta-data (read)
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$fname = TestPath::file('metadata_read.phar.php')->path;
$pname = 'phar://' . $fname;
$file = "<?php __HALT_COMPILER(); ?>";

$files = array();
$files['a'] = array('cont' => 'a');
$files['b'] = array('cont' => 'b', 'meta' => 'hi there');
$files['c'] = array('cont' => 'c', 'meta' => array('hi', 'there'));
$files['d'] = array('cont' => 'd', 'meta' => array('hi'=>'there','foo'=>'bar'));
include 'files/phar_test.inc';

foreach($files as $name => $cont) {
    var_dump(file_get_contents($pname.'/'.$name));
}

$phar = new Phar($fname);
foreach($files as $name => $cont) {
    var_dump($phar[$name]->getMetadata());
}

unset($phar);

foreach($files as $name => $cont) {
    var_dump(file_get_contents($pname.'/'.$name));
}
?>
--EXPECT--
string(1) "a"
string(1) "b"
string(1) "c"
string(1) "d"
NULL
string(8) "hi there"
array(2) {
  [0]=>
  string(2) "hi"
  [1]=>
  string(5) "there"
}
array(2) {
  ["hi"]=>
  string(5) "there"
  ["foo"]=>
  string(3) "bar"
}
string(1) "a"
string(1) "b"
string(1) "c"
string(1) "d"
