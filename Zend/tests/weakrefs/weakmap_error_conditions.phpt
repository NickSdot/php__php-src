--TEST--
WeakMap error conditions
--FILE--
<?php

$map = new WeakMap;
try {
    $map[1] = 2;
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    var_dump($map[1]);
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    isset($map[1]);
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    unset($map[1]);
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $map[] = 1;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $map[][1] = 1;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    var_dump($map[new stdClass]);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

var_dump($map->prop);
var_dump(isset($map->prop));
unset($map->prop);

try {
    $map->prop = 1;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $map->prop[] = 1;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $r =& $map->prop;
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    serialize($map);
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    unserialize('C:7:"WeakMap":0:{}');
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECTF--
TypeError: WeakMap key must be an object
TypeError: WeakMap key must be an object
TypeError: WeakMap key must be an object
TypeError: WeakMap key must be an object
Error: Cannot append to WeakMap
Error: Cannot append to WeakMap
Error: Object stdClass#2 not contained in WeakMap

Warning: Undefined property: WeakMap::$prop in %s on line %d
NULL
bool(false)
Error: Cannot create dynamic property WeakMap::$prop
Error: Cannot create dynamic property WeakMap::$prop
Error: Cannot create dynamic property WeakMap::$prop
Exception: Serialization of 'WeakMap' is not allowed
Exception: Unserialization of 'WeakMap' is not allowed
