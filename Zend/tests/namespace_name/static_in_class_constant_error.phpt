--TEST--
namespace name as scalar from ::namespace error using static in class constant
--FILE--
<?php

namespace Foo\Bar {
    class One {
        const Baz = static::namespace;
    }
}
?>
--EXPECTF--
Fatal error: static::namespace cannot be used for compile-time class namespace resolution in %s on line %d
