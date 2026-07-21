--TEST--
Hash: bug #12186 - segfault when cloning a finalized context
--FILE--
<?php

$c = hash_init('sha1');
hash_final($c);

try {
    clone $c;
} catch (Throwable $ex) {
    echo $ex::class, ': ', $ex->getMessage(), PHP_EOL;
}

?>
--EXPECTF--
ValueError: Cannot clone a finalized HashContext
