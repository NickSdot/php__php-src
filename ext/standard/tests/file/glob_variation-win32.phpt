--TEST--
Test glob() function: usage variations
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN') {
    die('skip.. only for Windows');
}
?>
--FILE--
<?php
echo "*** Testing glob() : usage variations ***\n";

$directory = Workspace::directory('glob_variation');
$wonder = $directory->directory('wonder');
$first = $directory->file('wonder12345');
$second = $directory->file('wonder;123456');

// temp dir created
mkdir($directory);
mkdir($wonder);

// temp files created
$fp = fopen($first, "w");
fclose($fp);
$fp = fopen($second, "w");
fclose($fp);

$patterns = array (
  "$directory/*der*",
  "$directory/?onder*",
  "$directory/w*der?*",
  "$directory/*der5",
  "$directory/??onder*",
  "$directory/***der***",
  "$directory/++onder*",
  "$directory/WONDER5\0",
  '$file_path/glob_variation/wonder5',
  "$directory/?wonder?",
  "$directory/wonder?",
  TRUE  // boolean true
);
$counter = 1;
/* loop through $patterns to match each $pattern with the files created
   using glob() */
foreach($patterns as $pattern) {
  echo "\n-- Iteration $counter --\n";
  try {
    var_dump( glob($pattern) );  // default arguments
    var_dump( glob($pattern, GLOB_MARK) );
    var_dump( glob($pattern, GLOB_NOSORT) );
    var_dump( glob($pattern, GLOB_NOCHECK) );
    var_dump( glob($pattern, GLOB_NOESCAPE) );
    var_dump( glob($pattern, GLOB_ERR) );
  } catch (Error $e) {
    echo $e->getMessage(), "\n";
  }
  $counter++;
}

echo "\n*** Testing glob() with pattern within braces ***\n";
var_dump( glob("$directory/*{5}", GLOB_BRACE) );

// delete temp files and dir
unlink($first);
unlink($second);
rmdir($wonder);
rmdir($directory);

echo "\n*** Testing glob() on directories ***\n";
// temp dir created to check for pattern matching the sub dir created in it
$wonder2 = $directory->directory('wonder1')->directory('wonder2');
mkdir($wonder2, 0777, true);

$counter = 1;
/* loop through $patterns to match each $pattern with the directories created
   using glob() */
foreach($patterns as $pattern) {
  echo "-- Iteration $counter --\n";
  try {
    var_dump( glob($pattern, GLOB_ONLYDIR) );
  } catch (Error $e) {
    echo $e->getMessage(), "\n";
  }
  $counter++;
}

echo "Done\n";
?>
--EXPECTF--
*** Testing glob() : usage variations ***

-- Iteration 1 --
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
  [2]=>
  string(%d) "%s/glob_variation/wonder\"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}

-- Iteration 2 --
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
  [2]=>
  string(%d) "%s/glob_variation/wonder\"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}

-- Iteration 3 --
array(2) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(2) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(2) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(2) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(2) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(2) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
}

-- Iteration 4 --
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/*der5"
}
array(0) {
}
array(0) {
}

-- Iteration 5 --
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/??onder*"
}
array(0) {
}
array(0) {
}

-- Iteration 6 --
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
  [1]=>
  string(%d) "%s/glob_variation/wonder;123456"
  [2]=>
  string(%d) "%s/glob_variation/wonder\"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}
array(3) {
  [0]=>
  string(%d) "%s/glob_variation/wonder"
  [1]=>
  string(%d) "%s/glob_variation/wonder12345"
  [2]=>
  string(%d) "%s/glob_variation/wonder;123456"
}

-- Iteration 7 --
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/++onder*"
}
array(0) {
}
array(0) {
}

-- Iteration 8 --
glob(): Argument #1 ($pattern) must not contain any null bytes

-- Iteration 9 --
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(%d) "$file_path/glob_variation/wonder5"
}
array(0) {
}
array(0) {
}

-- Iteration 10 --
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/?wonder?"
}
array(0) {
}
array(0) {
}

-- Iteration 11 --
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/wonder?"
}
array(0) {
}
array(0) {
}

-- Iteration 12 --
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(%d) "1"
}
array(0) {
}
array(0) {
}

*** Testing glob() with pattern within braces ***
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/wonder12345"
}

*** Testing glob() on directories ***
-- Iteration 1 --
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/wonder1"
}
-- Iteration 2 --
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/wonder1"
}
-- Iteration 3 --
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/wonder1"
}
-- Iteration 4 --
array(0) {
}
-- Iteration 5 --
array(0) {
}
-- Iteration 6 --
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/wonder1"
}
-- Iteration 7 --
array(0) {
}
-- Iteration 8 --
glob(): Argument #1 ($pattern) must not contain any null bytes
-- Iteration 9 --
array(0) {
}
-- Iteration 10 --
array(0) {
}
-- Iteration 11 --
array(1) {
  [0]=>
  string(%d) "%s/glob_variation/wonder1"
}
-- Iteration 12 --
array(0) {
}
Done
