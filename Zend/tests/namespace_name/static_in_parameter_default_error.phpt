--TEST--
namespace name as scalar from ::namespace error using static in parameter default
--FILE--
<?php

namespace Foo\Bar {
    class One {
        public function baz($x = static::namespace) {}
    }
}
?>
--EXPECTF--
Fatal error: static::namespace cannot be used for compile-time class namespace resolution in %s on line %d
