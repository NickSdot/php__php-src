--TEST--
Bug #76737: Unserialized reflection objects are broken, they shouldn't be serializable
--FILE--
<?php

try {
    $r = new ReflectionClass('stdClass');
    var_dump(serialize($r));
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $s = 'C:15:"ReflectionClass":0:{}';
    var_dump(unserialize($s));
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $s = 'O:15:"ReflectionClass":0:{}';
    var_dump(unserialize($s));
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECTF--
Exception: Serialization of 'ReflectionClass' is not allowed
Exception: Unserialization of 'ReflectionClass' is not allowed
Exception: Unserialization of 'ReflectionClass' is not allowed
