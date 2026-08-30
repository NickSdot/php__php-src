--TEST--
Test vfprintf() function : error conditions (various conditions)
--CREDITS--
Felix De Vliegher <felix.devliegher@gmail.com>
--INI--
precision=14
--FILE--
<?php
// Open handle
$file = TestPath::file();
$fp = fopen( $file, "a+" );

echo "\n-- Testing vfprintf() function with other strangeties  --\n";
try {
    var_dump( vfprintf( 'foo', 'bar', array( 'baz' ) ) );
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    var_dump( vfprintf( $fp, 'Foo %$c-0202Sd', array( 2 ) ) );
} catch(\ValueError $e) {
    print('Error found: '.$e->getMessage().".\n");
}
// Close handle
fclose( $fp );

?>
--EXPECT--
-- Testing vfprintf() function with other strangeties  --
vfprintf(): Argument #1 ($stream) must be of type resource, string given
Error found: Argument number specifier must be greater than zero and less than 2147483647.
