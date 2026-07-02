--TEST--
namespace name as scalar from ::namespace on self, parent, and static
--FILE--
<?php

namespace Foo\Bar {
    class ParentClass {}

    class ChildClass extends ParentClass {
        public static function dump(string $label): void {
            echo "$label self: ";
            var_dump(self::namespace);
            echo "$label parent: ";
            var_dump(parent::namespace);
            echo "$label static: ";
            var_dump(static::namespace);
        }
    }

    class GrandChildClass extends ChildClass {}

    ChildClass::dump('ChildClass');
    GrandChildClass::dump('GrandChildClass');
}
?>
--EXPECT--
ChildClass self: string(7) "Foo\Bar"
ChildClass parent: string(7) "Foo\Bar"
ChildClass static: string(7) "Foo\Bar"
GrandChildClass self: string(7) "Foo\Bar"
GrandChildClass parent: string(7) "Foo\Bar"
GrandChildClass static: string(7) "Foo\Bar"
