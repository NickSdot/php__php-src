--TEST--
namespace name as scalar from ::namespace in traits
--FILE--
<?php

namespace Foo\Bar {
    trait NamespaceTrait {
        public const TRAIT_SELF_NS = self::namespace;

        public static function dumpTraitFetches(string $label): void {
            echo "$label self: ";
            var_dump(self::namespace);
            echo "$label parent: ";
            var_dump(parent::namespace);
            echo "$label static: ";
            var_dump(static::namespace);
        }
    }

    class ParentClass {}
    class ChildClass extends ParentClass {
        use NamespaceTrait;
    }

    echo "same namespace constant: ";
    var_dump(ChildClass::TRAIT_SELF_NS);
    ChildClass::dumpTraitFetches('same namespace method');
}

namespace Other\Child {
    class ChildClass extends \Foo\Bar\ParentClass {
        use \Foo\Bar\NamespaceTrait;
    }

    echo "other namespace constant: ";
    var_dump(ChildClass::TRAIT_SELF_NS);
    ChildClass::dumpTraitFetches('other namespace method');
}
?>
--EXPECT--
same namespace constant: string(7) "Foo\Bar"
same namespace method self: string(7) "Foo\Bar"
same namespace method parent: string(7) "Foo\Bar"
same namespace method static: string(7) "Foo\Bar"
other namespace constant: string(11) "Other\Child"
other namespace method self: string(11) "Other\Child"
other namespace method parent: string(7) "Foo\Bar"
other namespace method static: string(11) "Other\Child"
