--TEST--
namespace name as scalar from ::namespace on objects and anonymous classes
--FILE--
<?php

namespace Foo\Bar {
    class ParentClass {}
    class ChildClass extends ParentClass {}

    $child = new ChildClass;

    echo "object: ";
    var_dump($child::namespace);
    echo "anonymous without parent: ";
    var_dump((new class {})::namespace);
    echo "anonymous extending namespaced parent: ";
    var_dump((new class extends ParentClass {})::namespace);
}
?>
--EXPECT--
object: string(7) "Foo\Bar"
anonymous without parent: string(0) ""
anonymous extending namespaced parent: string(7) "Foo\Bar"
