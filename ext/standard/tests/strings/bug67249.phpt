--TEST--
Bug #67249 (printf out-of-bounds read)
--FILE--
<?php
try {
    var_dump(sprintf("%'", "foo"));
} catch (ValueError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
ValueError: Missing padding character
