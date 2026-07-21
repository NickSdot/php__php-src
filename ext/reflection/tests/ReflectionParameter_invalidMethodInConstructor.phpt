--TEST--
ReflectionParameter::__construct(): Invalid method as constructor
--FILE--
<?php

// Invalid class name
try {
    new ReflectionParameter (array ('A', 'b'), 0);
} catch (ReflectionException $e) { echo $e::class, ': ', $e->getMessage(), PHP_EOL; }

// Invalid class method
try {
    new ReflectionParameter (array ('C', 'b'), 0);
} catch (ReflectionException $e) { echo $e::class, ': ', $e->getMessage(), PHP_EOL; }

// Invalid object method
try {
    new ReflectionParameter (array (new C, 'b'), 0);
} catch (ReflectionException $e) { echo $e::class, ': ', $e->getMessage(), PHP_EOL; }


class C {
}

try {
    new ReflectionParameter(array ('A', 'b'));
}
catch(TypeError $e) {
    printf( "Ok - %s\n", $e->getMessage());
}

try {
    new ReflectionParameter(0, 0);
}
catch(ReflectionException $e) {
    printf( "Ok - %s\n", $e->getMessage());
}

echo "Done.\n";

?>
--EXPECT--
ReflectionException: Class "A" does not exist
ReflectionException: Method C::b() does not exist
ReflectionException: Method C::b() does not exist
Ok - ReflectionParameter::__construct() expects exactly 2 arguments, 1 given
Ok - ReflectionParameter::__construct(): Argument #1 ($function) must be a string, an array(class, method), or a callable object, int given
Done.
