--TEST--
ReflectionParameter ::namespace default values
--FILE--
<?php

class C {
    public function method($a = self::namespace) {}
}

$param = (new ReflectionMethod(C::class, 'method'))->getParameters()[0];
echo 'C::method() $a = self::namespace isDefaultValueConstant() => ';
var_dump($param->isDefaultValueConstant());
echo 'C::method() $a = self::namespace getDefaultValueConstantName() => ';
var_dump($param->getDefaultValueConstantName());
echo 'C::method() $a = self::namespace getDefaultValue() => ';
var_dump($param->getDefaultValue());
?>
--EXPECT--
C::method() $a = self::namespace isDefaultValueConstant() => bool(false)
C::method() $a = self::namespace getDefaultValueConstantName() => NULL
C::method() $a = self::namespace getDefaultValue() => string(0) ""
