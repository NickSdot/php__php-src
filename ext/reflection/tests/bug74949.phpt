--TEST--
Bug #74949 (null pointer dereference in _function_string)
--FILE--
<?php

$f = function () {};

$r = new ReflectionMethod($f, "__invoke");

unset($f);

echo $r, "\n";

try  {
    echo $r->getPrototype();
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
Method [ <internal> public method __invoke ] {
}

ReflectionException: Method Closure::__invoke does not have a prototype
