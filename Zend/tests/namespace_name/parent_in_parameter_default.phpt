--TEST--
namespace name as scalar from ::namespace using parent in parameter default
--FILE--
<?php

namespace Foo\Bar {
    class One {}
    class Two extends One {
        public function baz($x = parent::namespace) {
            echo 'Foo\\Bar\\Two::baz() default $x = parent::namespace => ';
            var_dump($x);
        }
    }
    (new Two)->baz();
}
?>
--EXPECT--
Foo\Bar\Two::baz() default $x = parent::namespace => string(7) "Foo\Bar"
