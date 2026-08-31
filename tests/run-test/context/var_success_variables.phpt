--TEST--
Workspace variables shared from SETUP
--SETUP--
<?php

$outfile = Workspace::file();

file_put_contents($outfile, 'setup');
Workspace::share([
    'outfile',
    'values' => ['answer' => 42],
]);

?>
--FILE--
<?php

var_dump($outfile instanceof TestSuite\Context\Filesystem\File);
var_dump($outfile->name);
var_dump(file_get_contents($outfile));
var_dump($values);

?>
--EXPECT--
bool(true)
string(6) "file-1"
string(5) "setup"
array(1) {
  ["answer"]=>
  int(42)
}
