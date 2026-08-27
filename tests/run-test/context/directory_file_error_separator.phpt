--TEST--
Directory::file() rejects a name containing a directory separator
--FILE--
<?php

$directory = TestPath::directory('fixtures');

try {
    $directory->file('nested/file');
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
ValueError: Name cannot contain directory separators
