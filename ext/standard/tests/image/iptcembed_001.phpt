--TEST--
iptcembed() and wrong file
--FILE--
<?php

$file = TestPath::file();
$fp = fopen($file, "w");
fwrite($fp, "-1-1");
fclose($fp);

var_dump(iptcembed(-1, $file, -1));

echo "Done\n";
?>
--EXPECT--
bool(false)
Done	
