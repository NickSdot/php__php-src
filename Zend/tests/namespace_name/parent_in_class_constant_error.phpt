--TEST--
namespace name as scalar from ::namespace error using parent in class constant
--FILE--
<?php

namespace Foo\Bar {
    class One {
        const Baz = parent::namespace;
    }
    var_dump(One::Baz);
}
?>
--EXPECTF--
Fatal error: Cannot use "parent" when current class scope has no parent in %s on line %d
