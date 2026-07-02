--TEST--
namespace name as scalar from ::namespace in the global namespace
--FILE--
<?php

class GlobalClass {}

echo "global class: ";
var_dump(GlobalClass::namespace);
echo "global anonymous class: ";
var_dump((new class {})::namespace);
echo "fully qualified namespaced class: ";
var_dump(\Foo\Bar\ChildClass::namespace);
?>
--EXPECT--
global class: string(0) ""
global anonymous class: string(0) ""
fully qualified namespaced class: string(7) "Foo\Bar"
