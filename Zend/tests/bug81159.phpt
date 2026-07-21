--TEST--
Bug #81159: Object to int warning when using an object as a string offset
--FILE--
<?php

$s = 'Hello';
$o = new stdClass();
try {
    $s[$o] = 'A';
} catch (\Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    var_dump($s[$o]);
} catch (\Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
TypeError: Cannot access offset of type stdClass on string
TypeError: Cannot access offset of type stdClass on string
