--TEST--
Test session_save_path() function : variation
--EXTENSIONS--
session
--SKIPIF--
<?php include('skipif.inc');?>
--INI--
open_basedir=.
session.save_handler=files
session.save_path=
session.name=PHPSESSID
--FILE--
<?php

ob_start();

echo "*** Testing session_save_path() : variation ***\n";
$initdir = TestPath::directory()->create();
$sessions = $initdir->directory();

chdir($initdir);

var_dump(mkdir($sessions));
var_dump(chdir($sessions));
ini_set("session.save_path", $initdir->path);
var_dump(session_save_path());
var_dump(session_start());
var_dump(session_save_path());
var_dump(session_destroy());
var_dump(session_save_path());

echo "Done";
ob_end_flush();
?>
--EXPECTF--
*** Testing session_save_path() : variation ***
bool(true)
bool(true)

Warning: ini_set(): open_basedir restriction in effect. File(%s) is not within the allowed path(s): (.) in %s on line %d
string(0) ""

Warning: session_start(): open_basedir restriction in effect. File(%s) is not within the allowed path(s): (.) in %s on line %d

Warning: session_start(): Failed to initialize storage module: files (path: ) in %s on line %d
bool(false)
string(0) ""

Warning: session_destroy(): Trying to destroy uninitialized session in %s on line %d
bool(false)
string(0) ""
Done
