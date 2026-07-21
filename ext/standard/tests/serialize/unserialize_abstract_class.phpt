--TEST--
Unserializing an abstract class should fail
--FILE--
<?php

$payload = 'O:23:"RecursiveFilterIterator":0:{}';
try {
    var_dump(unserialize($payload));
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Cannot instantiate abstract class RecursiveFilterIterator
