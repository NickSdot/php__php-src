--TEST--
Workspace variables shared from SETUP are available to CGI
--SETUP--
<?php

$file = Workspace::file();

file_put_contents($file, 'setup');
Workspace::share('file');

?>
--CGI--
--FILE--
<?php

var_dump($file instanceof TestSuite\Context\Filesystem\File);
var_dump(file_get_contents($file));

?>
--EXPECT--
bool(true)
string(5) "setup"
