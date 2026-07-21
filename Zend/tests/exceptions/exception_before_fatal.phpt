--TEST--
Exceptions before fatal error
--FILE--
<?php
function exception_error_handler($code, $msg) {
    throw new Exception($msg);
}

set_error_handler("exception_error_handler");

try {
    $foo->a();
} catch(Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    new $foo();
} catch(Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    throw $foo;
} catch(Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $foo();
} catch(Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    $foo::b();
} catch(Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}


try {
    $b = clone $foo;
} catch(Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

class b {
}

try {
    b::$foo();
} catch(Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
Exception: Undefined variable $foo
Exception: Undefined variable $foo
Exception: Undefined variable $foo
Exception: Undefined variable $foo
Exception: Undefined variable $foo
Exception: Undefined variable $foo
Exception: Undefined variable $foo
