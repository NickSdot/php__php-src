--TEST--
ArrayObject illegal offset
--FILE--
<?php

$ao = new ArrayObject([1, 2, 3]);
try {
    var_dump($ao[[]]);
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $ao[[]] = new stdClass;
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    $ref =& $ao[[]];
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    var_dump(isset($ao[[]]));
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
try {
    unset($ao[[]]);
} catch (TypeError $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
TypeError: Cannot access offset of type array on ArrayObject
TypeError: Cannot access offset of type array on ArrayObject
TypeError: Cannot access offset of type array on ArrayObject
TypeError: Cannot access offset of type array in isset or empty
TypeError: Cannot unset offset of type array on ArrayObject
