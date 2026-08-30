--TEST--
Bug #61139 (gzopen leaks when specifying invalid mode)
--EXTENSIONS--
zlib
--FILE--
<?php
$file = TestPath::file('someFile');

gzopen($file, 'c');
?>
--EXPECTF--
Warning: gzopen(): gzopen failed in %s on line %d
