--TEST--
Test scandir() function : usage variations - different relative paths
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
  die("skip Valid only on Windows");
}
?>
--FILE--
<?php
/*
 * Test scandir() with relative paths as $dir argument
 */

echo "*** Testing scandir() : usage variations ***\n";

// include for create_files/delete_files functions
include (__DIR__ . '/../file/file.inc');

$base_dir_path = TestPath::directory()->create();

$level_one_dir_path = $base_dir_path->directory('私はガラスを食べられますlevel_one')->create();
$level_two_dir_path = $level_one_dir_path->directory('私はガラスを食べられますlevel_two')->create();

// create directories and files
create_files($level_one_dir_path, 2, 'numeric', 0755, 1, 'w', '私はガラスを食べられますlevel_one', 1);
create_files($level_two_dir_path, 2, 'numeric', 0755, 1, 'w', '私はガラスを食べられますlevel_two', 1);

echo "\n-- \$path = './私はガラスを食べられますlevel_one': --\n";
var_dump(chdir($base_dir_path));
var_dump(scandir('./私はガラスを食べられますlevel_one'));

echo "\n-- \$path = 'level_one/私はガラスを食べられますlevel_two': --\n";
var_dump(chdir($base_dir_path));
var_dump(scandir('私はガラスを食べられますlevel_one/私はガラスを食べられますlevel_two'));

echo "\n-- \$path = '..': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('..'));

echo "\n-- \$path = '私はガラスを食べられますlevel_two', '.': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('.'));

echo "\n-- \$path = '../': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('../'));

echo "\n-- \$path = './': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('./'));

echo "\n-- \$path = '../../'私はガラスを食べられますlevel_one': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('../../私はガラスを食べられますlevel_one'));

?>
--EXPECT--
*** Testing scandir() : usage variations ***

-- $path = './私はガラスを食べられますlevel_one': --
bool(true)
array(5) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
  [2]=>
  string(50) "私はガラスを食べられますlevel_one1.tmp"
  [3]=>
  string(50) "私はガラスを食べられますlevel_one2.tmp"
  [4]=>
  string(45) "私はガラスを食べられますlevel_two"
}

-- $path = 'level_one/私はガラスを食べられますlevel_two': --
bool(true)
array(4) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
  [2]=>
  string(50) "私はガラスを食べられますlevel_two1.tmp"
  [3]=>
  string(50) "私はガラスを食べられますlevel_two2.tmp"
}

-- $path = '..': --
bool(true)
array(5) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
  [2]=>
  string(50) "私はガラスを食べられますlevel_one1.tmp"
  [3]=>
  string(50) "私はガラスを食べられますlevel_one2.tmp"
  [4]=>
  string(45) "私はガラスを食べられますlevel_two"
}

-- $path = '私はガラスを食べられますlevel_two', '.': --
bool(true)
array(4) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
  [2]=>
  string(50) "私はガラスを食べられますlevel_two1.tmp"
  [3]=>
  string(50) "私はガラスを食べられますlevel_two2.tmp"
}

-- $path = '../': --
bool(true)
array(5) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
  [2]=>
  string(50) "私はガラスを食べられますlevel_one1.tmp"
  [3]=>
  string(50) "私はガラスを食べられますlevel_one2.tmp"
  [4]=>
  string(45) "私はガラスを食べられますlevel_two"
}

-- $path = './': --
bool(true)
array(4) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
  [2]=>
  string(50) "私はガラスを食べられますlevel_two1.tmp"
  [3]=>
  string(50) "私はガラスを食べられますlevel_two2.tmp"
}

-- $path = '../../'私はガラスを食べられますlevel_one': --
bool(true)
array(5) {
  [0]=>
  string(1) "."
  [1]=>
  string(2) ".."
  [2]=>
  string(50) "私はガラスを食べられますlevel_one1.tmp"
  [3]=>
  string(50) "私はガラスを食べられますlevel_one2.tmp"
  [4]=>
  string(45) "私はガラスを食べられますlevel_two"
}
