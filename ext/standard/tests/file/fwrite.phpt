--TEST--
fwrite() tests
--FILE--
<?php

$filename = TestPath::file('fwrite.dat')->path;

$fp = fopen($filename, "w");
var_dump(fwrite($fp, ""));
fclose($fp);

$fp = fopen($filename, "r");
var_dump(fwrite($fp, "data"));

$fp = fopen($filename, "w");
var_dump(fwrite($fp, "data", -1));
var_dump(fwrite($fp, "data", 100000));
fclose($fp);

var_dump(file_get_contents($filename));

echo "Done\n";
?>
--EXPECTF--
int(0)

Notice: fwrite(): Write of 4 bytes failed with errno=9 Bad file %r(descriptor|number)%r in %s on line %d
bool(false)
int(0)
int(4)
string(4) "data"
Done
