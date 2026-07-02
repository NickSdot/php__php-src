--TEST--
namespace name as scalar from ::namespace on named class-like names
--FILE--
<?php

namespace Foo\Bar {
    use Vendor\Package\Thing as ImportedThing;

    spl_autoload_register(static function (string $class): void {
        echo "autoload $class\n";
    });

    class ParentClass {}

    echo "named class: ";
    var_dump(ParentClass::namespace);
    echo "case-insensitive keyword: ";
    var_dump(ParentClass::NAMESPACE);
    echo "import alias: ";
    var_dump(ImportedThing::namespace);
    echo "namespace-relative one segment: ";
    var_dump(namespace\RelativeThing::namespace);
    echo "namespace-relative nested marker: ";
    var_dump(namespace\Nested\Marker::namespace);
    echo "missing name without autoload: ";
    var_dump(Does\Not\Exist::namespace);
}
?>
--EXPECT--
named class: string(7) "Foo\Bar"
case-insensitive keyword: string(7) "Foo\Bar"
import alias: string(14) "Vendor\Package"
namespace-relative one segment: string(7) "Foo\Bar"
namespace-relative nested marker: string(14) "Foo\Bar\Nested"
missing name without autoload: string(16) "Foo\Bar\Does\Not"
