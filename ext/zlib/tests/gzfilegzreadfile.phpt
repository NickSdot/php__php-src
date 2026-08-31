--TEST--
gzfile(), gzreadfile()
--EXTENSIONS--
zlib
--FILE--
<?php
$original = <<<EOD
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah

EOD;

$filename = Workspace::file();

$fp = gzopen($filename, "wb");
gzwrite($fp, $original);
var_dump(strlen($original));
fclose($fp);

readgzfile($filename);

echo "\n";

$lines = gzfile($filename);

foreach ($lines as $line) {
    echo $line;
}

?>
--EXPECT--
int(560)
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah

blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
blah blah blah blah blah blah blah
