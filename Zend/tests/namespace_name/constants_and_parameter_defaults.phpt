--TEST--
namespace name as scalar from ::namespace in constants and parameter defaults
--FILE--
<?php

namespace Foo\Bar {
    class ParentClass {}

    class ConstantCases extends ParentClass {
        public const SELF_NS = self::namespace;
        public const NAMED_PARENT_NS = ParentClass::namespace;
    }

    class DefaultArgumentCases extends ParentClass {
        public static function dump(
            $self = self::namespace,
            $parent = parent::namespace,
            $named = ParentClass::namespace,
        ): void {
            echo "default self: ";
            var_dump($self);
            echo "default parent: ";
            var_dump($parent);
            echo "default named: ";
            var_dump($named);
        }
    }

    echo "constant self: ";
    var_dump(ConstantCases::SELF_NS);
    echo "constant named parent: ";
    var_dump(ConstantCases::NAMED_PARENT_NS);
    DefaultArgumentCases::dump();
}
?>
--EXPECT--
constant self: string(7) "Foo\Bar"
constant named parent: string(7) "Foo\Bar"
default self: string(7) "Foo\Bar"
default parent: string(7) "Foo\Bar"
default named: string(7) "Foo\Bar"
