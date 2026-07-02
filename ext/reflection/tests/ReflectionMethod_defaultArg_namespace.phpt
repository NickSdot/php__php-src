--TEST--
ReflectionMethod and RECV_INIT for self::namespace
--FILE--
<?php
namespace Foo\Bar;

abstract class F {
    private function bar($a = self::namespace) {}
}

trait T
{
    private function bar($a = self::namespace) {}
}

class B {
    use T;
}

echo "Foo\\Bar\\F::bar() default from class method\n";
echo new \ReflectionMethod('Foo\\Bar\\F', 'bar');
echo "Foo\\Bar\\T::bar() default from trait method\n";
echo new \ReflectionMethod('Foo\\Bar\\T', 'bar');
echo "Foo\\Bar\\B::bar() default from imported trait method\n";
echo new \ReflectionMethod('Foo\\Bar\\B', 'bar');
?>
--EXPECTF--
Foo\Bar\F::bar() default from class method
Method [ <user> private method bar ] {
  @@ %s

  - Parameters [1] {
    Parameter #0 [ <optional> $a = 'Foo\\Bar' ]
  }
}
Foo\Bar\T::bar() default from trait method
Method [ <user> private method bar ] {
  @@ %s

  - Parameters [1] {
    Parameter #0 [ <optional> $a = self::namespace ]
  }
}
Foo\Bar\B::bar() default from imported trait method
Method [ <user> private method bar ] {
  @@ %s

  - Parameters [1] {
    Parameter #0 [ <optional> $a = self::namespace ]
  }
}
