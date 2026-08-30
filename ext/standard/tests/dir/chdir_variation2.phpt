--TEST--
Test chdir() function : usage variations - relative paths
--FILE--
<?php
/*
 * Test chdir() with variations of relative paths
 */

echo "*** Testing chdir() : usage variations ***\n";

$base_dir_path = TestPath::directory()->create();

$level2_one_dir_name = "level2_one";
$level2_one_dir_path = $base_dir_path->directory($level2_one_dir_name)->create();

$level2_two_dir_name = "level2_two";
$level2_two_dir_path = $level2_one_dir_path->directory($level2_two_dir_name)->create();

echo "\n-- \$directory = './level2_one': --\n";
var_dump(chdir($base_dir_path));
var_dump(chdir("./$level2_one_dir_name"));
var_dump(getcwd());

echo "\n-- \$directory = 'level2_one/level2_two': --\n";
var_dump(chdir($base_dir_path));
var_dump(chdir("$level2_one_dir_name/$level2_two_dir_name"));
var_dump(getcwd());

echo "\n-- \$directory = '..': --\n";
var_dump(chdir('..'));
var_dump(getcwd());

echo "\n-- \$directory = 'level2_two', '.': --\n";
var_dump(chdir($level2_two_dir_path));
var_dump(chdir('.'));
var_dump(getcwd());

echo "\n-- \$directory = '../': --\n";
var_dump(chdir('../'));
var_dump(getcwd());

echo "\n-- \$directory = './': --\n";
var_dump(chdir($level2_two_dir_path));
var_dump(chdir('./'));
var_dump(getcwd());

echo "\n-- \$directory = '../../'level2_one': --\n";
var_dump(chdir($level2_two_dir_path));
var_dump(chdir("../../$level2_one_dir_name"));
var_dump(getcwd());

?>
--EXPECTF--
*** Testing chdir() : usage variations ***

-- $directory = './level2_one': --
bool(true)
bool(true)
string(%d) "%slevel2_one"

-- $directory = 'level2_one/level2_two': --
bool(true)
bool(true)
string(%d) "%slevel2_one%elevel2_two"

-- $directory = '..': --
bool(true)
string(%d) "%slevel2_one"

-- $directory = 'level2_two', '.': --
bool(true)
bool(true)
string(%d) "%slevel2_one%elevel2_two"

-- $directory = '../': --
bool(true)
string(%d) "%slevel2_one"

-- $directory = './': --
bool(true)
bool(true)
string(%d) "%slevel2_one%elevel2_two"

-- $directory = '../../'level2_one': --
bool(true)
bool(true)
string(%d) "%slevel2_one"
